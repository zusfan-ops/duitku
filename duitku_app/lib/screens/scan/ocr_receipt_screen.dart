import 'dart:io';
import 'package:flutter/material.dart';
import 'package:google_mlkit_text_recognition/google_mlkit_text_recognition.dart';
import 'package:image_picker/image_picker.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class OcrReceiptResult {
  final double amount;
  final String note;
  final String date;
  final String? imagePath;

  const OcrReceiptResult({
    required this.amount,
    required this.note,
    required this.date,
    this.imagePath,
  });
}

class OcrReceiptScreen extends StatefulWidget {
  final String symbol;
  const OcrReceiptScreen({super.key, this.symbol = 'Rp'});

  @override
  State<OcrReceiptScreen> createState() => _OcrReceiptScreenState();
}

class _OcrReceiptScreenState extends State<OcrReceiptScreen> {
  File? _imageFile;
  final _amountCtrl = TextEditingController();
  final _merchantCtrl = TextEditingController();
  final _dateCtrl = TextEditingController();
  String _extractedRawText = '';
  bool _scanning = false;
  String _scanStatus = '';

  @override
  void initState() {
    super.initState();
    _dateCtrl.text = DateTime.now().toIso8601String().substring(0, 10);
  }

  @override
  void dispose() {
    _amountCtrl.dispose();
    _merchantCtrl.dispose();
    _dateCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickAndScan(ImageSource source) async {
    final picker = ImagePicker();
    final img = await picker.pickImage(
      source: source,
      maxWidth: 1600,
      imageQuality: 90,
    );
    if (img == null) return;

    setState(() {
      _imageFile = File(img.path);
      _scanning = true;
      _scanStatus = 'Membaca teks dari foto struk (OCR)...';
    });

    try {
      // Step 1: On-device ML Kit OCR Recognition
      final inputImage = InputImage.fromFilePath(img.path);
      final textRecognizer = TextRecognizer(script: TextRecognitionScript.latin);
      final RecognizedText recognizedText = await textRecognizer.processImage(inputImage);
      await textRecognizer.close();

      final ocrText = recognizedText.text.trim();
      _extractedRawText = ocrText;

      setState(() {
        _scanStatus = 'Menganalisis total & merchant...';
      });

      // Step 2: Parse text using smart local regex and server validation
      _parseReceiptText(ocrText);

      // Step 3: Send to server in background to save receipt image to backend
      try {
        final base64 = await ApiService.instance.base64FromFile(img.path);
        if (base64 != null) {
          final serverRes = await ApiService.instance.ocrScanReceipt(
            imageBase64: base64,
            receiptText: ocrText,
          );
          if (serverRes['success'] == true && serverRes['parsed'] != null) {
            final p = serverRes['parsed'];
            if (_amountCtrl.text.isEmpty || _amountCtrl.text == '0') {
              if (p['amount'] != null && (p['amount'] as num) > 0) {
                _amountCtrl.text = Fmt.money0((p['amount'] as num).toDouble());
              }
            }
            if (_merchantCtrl.text.isEmpty) {
              if (p['merchant'] != null && p['merchant'].toString().isNotEmpty) {
                _merchantCtrl.text = p['merchant'].toString();
              }
            }
          }
        }
      } catch (_) {}

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('✨ Struk berhasil dipindai! Rincian telah terisi otomatis.'),
            backgroundColor: Color(0xFF059669),
            duration: Duration(seconds: 2),
          ),
        );
      }
    } catch (e) {
      debugPrint('OCR Scanning error: $e');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Gagal memindai otomatis: $e. Silakan isi nominal manual.'),
            backgroundColor: AppColors.expense,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _scanning = false;
          _scanStatus = '';
        });
      }
    }
  }

  void _parseReceiptText(String text) {
    if (text.isEmpty) return;

    final lines = text
        .split('\n')
        .map((l) => l.trim())
        .where((l) => l.isNotEmpty)
        .toList();

    // ── 1. Merchant Detection ──────────────────────────────────
    final knownMerchants = [
      'Indomaret', 'Alfamart', 'Alfamidi', 'Superindo', 'Hypermart',
      'Transmart', 'Carrefour', 'Hero', 'Lotte Mart', 'Starbucks',
      'Janji Jiwa', 'Kopi Kenangan', 'KFC', 'McDonald\'s', 'McD',
      'Burger King', 'A&W', 'Pizza Hut', 'Dominos', 'HokBen', 'J.CO',
      'Chatime', 'Haus', 'Fore Coffee', 'Mie Gacoan', 'Solaria',
      'SPBU', 'Pertamina', 'Shell', 'BP AKR', 'Guardian', 'Watsons',
      'Century', 'Kimia Farma', 'Apotek K-24', 'Apotek', 'Gramedia',
      'Ace Hardware', 'Informa', 'Uniqlo', 'H&M', 'Zara', 'Miniso'
    ];

    String detectedMerchant = '';
    for (final line in lines) {
      for (final km in knownMerchants) {
        if (line.toLowerCase().contains(km.toLowerCase())) {
          detectedMerchant = km;
          break;
        }
      }
      if (detectedMerchant.isNotEmpty) break;
    }

    if (detectedMerchant.isEmpty) {
      for (final line in lines.take(5)) {
        final cleaned = line.replaceAll(RegExp(r'[^a-zA-Z0-9\s\.\-]'), '').trim();
        if (cleaned.length >= 3 &&
            !RegExp(r'^\d+$').hasMatch(cleaned) &&
            !RegExp(r'^(jl|jalan|no|telp|struk|nota|receipt|selamat|kasir|pos|tanggal|waktu|invoice)', caseSensitive: false).hasMatch(cleaned)) {
          detectedMerchant = cleaned;
          break;
        }
      }
    }

    // ── 2. Total Amount Detection ──────────────────────────────
    double detectedAmount = 0;
    final totalKeywords = [
      'GRAND TOTAL', 'TOTAL AKHIR', 'TOTAL BAYAR', 'TOTAL BELANJA',
      'TOTAL HARGA', 'TOTAL TAGIHAN', 'JUMLAH TOTAL', 'TAGIHAN',
      'BAYAR TUNAI', 'TOTAL', 'JUMLAH', 'BAYAR', 'NETTO', 'NET AMOUNT',
      'SUBTOTAL', 'CASH', 'TUNAI', 'DEBIT', 'QRIS'
    ];

    for (int i = 0; i < lines.length; i++) {
      final line = lines[i].toUpperCase();
      for (final kw in totalKeywords) {
        if (line.contains(kw)) {
          // Cari angka pada baris yang sama
          final matches = RegExp(r'(\d[\d\.\,\s]*\d|\d+)').allMatches(lines[i]);
          for (final m in matches) {
            final val = _cleanNumber(m.group(0) ?? '');
            if (val > detectedAmount && val < 500000000) {
              detectedAmount = val;
            }
          }

          // Jika tidak ada di baris yang sama, cek baris persis di bawahnya (i + 1)
          if (detectedAmount == 0 && i + 1 < lines.length) {
            final nextMatches = RegExp(r'(\d[\d\.\,\s]*\d|\d+)').allMatches(lines[i + 1]);
            for (final m in nextMatches) {
              final val = _cleanNumber(m.group(0) ?? '');
              if (val > detectedAmount && val < 500000000) {
                detectedAmount = val;
              }
            }
          }
        }
      }
    }

    // Fallback: Cari angka terbesar setelah kata Rp
    if (detectedAmount == 0) {
      final rpMatches = RegExp(r'(?:Rp\.?|IDR)\s*(\d[\d\.\,\s]*\d|\d+)', caseSensitive: false).allMatches(text);
      for (final m in rpMatches) {
        final val = _cleanNumber(m.group(1) ?? '');
        if (val > detectedAmount && val < 500000000) {
          detectedAmount = val;
        }
      }
    }

    // ── 3. Date Detection ──────────────────────────────────────
    String detectedDate = '';
    // Format DD/MM/YYYY atau DD-MM-YYYY atau YYYY-MM-DD
    final dateMatch1 = RegExp(r'(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})').firstMatch(text);
    if (dateMatch1 != null) {
      final d = int.tryParse(dateMatch1.group(1) ?? '') ?? 0;
      final m = int.tryParse(dateMatch1.group(2) ?? '') ?? 0;
      final y = int.tryParse(dateMatch1.group(3) ?? '') ?? 0;
      if (d >= 1 && d <= 31 && m >= 1 && m <= 12 && y >= 2020 && y <= 2030) {
        detectedDate = '$y-${m.toString().padLeft(2, '0')}-${d.toString().padLeft(2, '0')}';
      }
    }

    if (detectedDate.isEmpty) {
      final dateMatch2 = RegExp(r'(\d{4})[\/\-\.](\d{1,2})[\/\-\.](\d{1,2})').firstMatch(text);
      if (dateMatch2 != null) {
        final y = int.tryParse(dateMatch2.group(1) ?? '') ?? 0;
        final m = int.tryParse(dateMatch2.group(2) ?? '') ?? 0;
        final d = int.tryParse(dateMatch2.group(3) ?? '') ?? 0;
        if (d >= 1 && d <= 31 && m >= 1 && m <= 12 && y >= 2020 && y <= 2030) {
          detectedDate = '$y-${m.toString().padLeft(2, '0')}-${d.toString().padLeft(2, '0')}';
        }
      }
    }

    // Apply to UI Controllers
    if (detectedMerchant.isNotEmpty) {
      _merchantCtrl.text = detectedMerchant;
    }
    if (detectedAmount > 0) {
      _amountCtrl.text = Fmt.money0(detectedAmount);
    }
    if (detectedDate.isNotEmpty) {
      _dateCtrl.text = detectedDate;
    }
  }

  double _cleanNumber(String str) {
    var s = str.trim().replaceAll(' ', '');
    s = s.replaceAll(RegExp(r'[^0-9\.\,]'), '');
    if (s.isEmpty) return 0;

    // Jika format 150.000,00 atau 150,000.00
    if (s.contains('.') && s.contains(',')) {
      if (s.indexOf('.') < s.indexOf(',')) {
        // 150.000,00 -> ribuan titik, desimal koma
        s = s.replaceAll('.', '').replaceAll(',', '.');
      } else {
        // 150,000.00 -> ribuan koma, desimal titik
        s = s.replaceAll(',', '');
      }
    } else if (s.contains('.')) {
      // 150.000 -> ribuan
      final parts = s.split('.');
      if (parts.last.length == 3) {
        s = s.replaceAll('.', '');
      }
    } else if (s.contains(',')) {
      // 150,000 -> ribuan
      final parts = s.split(',');
      if (parts.last.length == 3) {
        s = s.replaceAll(',', '');
      } else {
        s = s.replaceAll(',', '.');
      }
    }

    return double.tryParse(s) ?? 0;
  }

  void _submit() {
    final rawAmount = _amountCtrl.text.replaceAll(RegExp(r'[^\d]'), '');
    final amount = double.tryParse(rawAmount) ?? 0;
    if (amount <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Nominal total belanja struk wajib diisi.')),
      );
      return;
    }

    final result = OcrReceiptResult(
      amount: amount,
      note: _merchantCtrl.text.trim().isNotEmpty ? _merchantCtrl.text.trim() : 'Belanja Struk / Nota',
      date: _dateCtrl.text.trim().isNotEmpty ? _dateCtrl.text.trim() : DateTime.now().toIso8601String().substring(0, 10),
      imagePath: _imageFile?.path,
    );

    Navigator.pop(context, result);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('📷 Smart Scan Struk / Nota (OCR)'),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 40),
        children: [
          // Image Preview / OCR Scanner Card
          Container(
            height: 230,
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(22),
              border: Border.all(color: AppColors.border),
              boxShadow: AppColors.cardShadow,
            ),
            child: _imageFile != null
                ? ClipRRect(
                    borderRadius: BorderRadius.circular(22),
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        Image.file(_imageFile!, fit: BoxFit.cover),
                        if (_scanning)
                          Container(
                            color: Colors.black.withValues(alpha: 0.6),
                            child: Center(
                              child: Column(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const CircularProgressIndicator(color: Colors.white),
                                  const SizedBox(height: 14),
                                  Text(
                                    _scanStatus.isNotEmpty ? _scanStatus : 'Memindai teks struk...',
                                    textAlign: TextAlign.center,
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontWeight: FontWeight.bold,
                                      fontSize: 13,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                      ],
                    ),
                  )
                : Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        width: 64,
                        height: 64,
                        decoration: BoxDecoration(
                          color: const Color(0xFF2563EB).withValues(alpha: 0.1),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.document_scanner_rounded, size: 32, color: Color(0xFF2563EB)),
                      ),
                      const SizedBox(height: 12),
                      const Text(
                        'Foto atau Unggah Struk / Nota Fisik',
                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
                      ),
                      const SizedBox(height: 4),
                      const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 20),
                        child: Text(
                          'Indomaret, Alfamart, SPBU, Restoran, Swalayan, atau Nota Toko',
                          textAlign: TextAlign.center,
                          style: TextStyle(fontSize: 12, color: AppColors.textMuted),
                        ),
                      ),
                    ],
                  ),
          ),
          const SizedBox(height: 14),

          // Camera & Gallery Buttons
          Row(
            children: [
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: _scanning ? null : () => _pickAndScan(ImageSource.camera),
                  icon: const Icon(Icons.camera_alt_rounded, size: 18),
                  label: const Text('Buka Kamera', style: TextStyle(fontWeight: FontWeight.w800)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF2563EB),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: _scanning ? null : () => _pickAndScan(ImageSource.gallery),
                  icon: const Icon(Icons.photo_library_rounded, size: 18),
                  label: const Text('Pilih Galeri', style: TextStyle(fontWeight: FontWeight.w700)),
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),

          OutlinedButton.icon(
            onPressed: () async {
              final textCtrl = TextEditingController(text: _extractedRawText);
              final ok = await showDialog<bool>(
                context: context,
                builder: (ctx) => AlertDialog(
                  title: const Text('Tempel Teks Struk / SMS Notif'),
                  content: TextField(
                    controller: textCtrl,
                    maxLines: 5,
                    decoration: const InputDecoration(
                      hintText: 'Contoh: TOTAL Rp 85.000 di Alfamart...',
                      labelText: 'TEKS NOTA',
                    ),
                  ),
                  actions: [
                    TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
                    FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Ekstrak')),
                  ],
                ),
              );
              if (ok == true) {
                _parseReceiptText(textCtrl.text);
              }
            },
            icon: const Icon(Icons.paste_rounded, size: 18),
            label: const Text('Tempel Teks Manual / SMS Bank', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 10),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
          ),
          const SizedBox(height: 16),

          // Extracted Result Card
          Container(
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: AppColors.border),
              boxShadow: AppColors.cardShadow,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(6),
                      decoration: BoxDecoration(
                        color: const Color(0xFF10B981).withValues(alpha: 0.12),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.auto_awesome, color: Color(0xFF059669), size: 18),
                    ),
                    const SizedBox(width: 8),
                    const Text('Hasil Ekstraksi Data Struk', style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.w800)),
                  ],
                ),
                const SizedBox(height: 16),

                // Amount
                TextField(
                  controller: _amountCtrl,
                  keyboardType: TextInputType.number,
                  decoration: InputDecoration(
                    labelText: 'TOTAL NOMINAL BELANJA',
                    prefixText: '${widget.symbol} ',
                    hintText: '0',
                  ),
                ),
                const SizedBox(height: 12),

                // Merchant
                TextField(
                  controller: _merchantCtrl,
                  decoration: const InputDecoration(
                    labelText: 'NAMA TOKO / MERCHANT / KETERANGAN',
                    hintText: 'Contoh: Indomaret, SPBU Pertamina',
                  ),
                ),
                const SizedBox(height: 12),

                // Date
                TextField(
                  controller: _dateCtrl,
                  decoration: const InputDecoration(
                    labelText: 'TANGGAL NOTA (YYYY-MM-DD)',
                  ),
                ),
                const SizedBox(height: 20),

                FilledButton.icon(
                  onPressed: _submit,
                  icon: const Icon(Icons.arrow_forward_rounded, size: 18),
                  label: const Text('Gunakan untuk Transaksi Pengeluaran', style: TextStyle(fontWeight: FontWeight.w800)),
                  style: FilledButton.styleFrom(
                    backgroundColor: const Color(0xFF2563EB),
                    minimumSize: const Size.fromHeight(50),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
