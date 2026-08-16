import 'dart:io';
import 'package:flutter/material.dart';
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
  final _rawTextCtrl = TextEditingController();
  bool _scanning = false;

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
    _rawTextCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickImage(ImageSource source) async {
    final picker = ImagePicker();
    final img = await picker.pickImage(source: source, maxWidth: 1200, imageQuality: 85);
    if (img == null) return;

    setState(() {
      _imageFile = File(img.path);
      _scanning = true;
    });

    try {
      final base64 = await ApiService.instance.base64FromFile(img.path);
      final res = await ApiService.instance.ocrScanReceipt(imageBase64: base64);
      if (res['success'] == true && res['parsed'] != null) {
        final p = res['parsed'];
        if (p['amount'] != null && (p['amount'] as num) > 0) {
          _amountCtrl.text = Fmt.money0((p['amount'] as num).toDouble());
        }
        if (p['merchant'] != null && p['merchant'].toString().isNotEmpty) {
          _merchantCtrl.text = p['merchant'].toString();
        }
        if (p['date'] != null && p['date'].toString().isNotEmpty) {
          _dateCtrl.text = p['date'].toString();
        }
      }
    } catch (_) {
      // Fallback default
      final nowStr = DateTime.now().toIso8601String().substring(0, 10);
      _dateCtrl.text = nowStr;
      if (_merchantCtrl.text.isEmpty) {
        _merchantCtrl.text = 'Belanja Swalayan / Nota';
      }
    }

    setState(() {
      _scanning = false;
    });
  }

  Future<void> _parseManualText(String text) async {
    if (text.isEmpty) return;

    try {
      final res = await ApiService.instance.ocrScanReceipt(receiptText: text);
      if (res['success'] == true && res['parsed'] != null) {
        final p = res['parsed'];
        if (p['amount'] != null && (p['amount'] as num) > 0) {
          _amountCtrl.text = Fmt.money0((p['amount'] as num).toDouble());
        }
        if (p['merchant'] != null && p['merchant'].toString().isNotEmpty) {
          _merchantCtrl.text = p['merchant'].toString();
        }
        if (p['date'] != null && p['date'].toString().isNotEmpty) {
          _dateCtrl.text = p['date'].toString();
        }
        return;
      }
    } catch (_) {}

    // Local Regex Fallback
    final lines = text.split('\n');
    String detectedMerchant = '';
    double detectedAmount = 0;

    for (int i = 0; i < lines.length; i++) {
      final line = lines[i].trim();
      if (line.isEmpty) continue;

      if (detectedMerchant.isEmpty && !RegExp(r'^\d+$').hasMatch(line)) {
        detectedMerchant = line;
      }

      // Check for total keywords
      final upper = line.toUpperCase();
      if (upper.contains('TOTAL') || upper.contains('JUMLAH') || upper.contains('BAYAR') || upper.contains('RP')) {
        final matches = RegExp(r'(\d+[\.,]?\d*)').allMatches(line);
        for (final m in matches) {
          final raw = m.group(0)?.replaceAll(RegExp(r'[^\d]'), '');
          final val = double.tryParse(raw ?? '') ?? 0;
          if (val > detectedAmount) detectedAmount = val;
        }
      }
    }

    if (detectedMerchant.isNotEmpty) _merchantCtrl.text = detectedMerchant;
    if (detectedAmount > 0) _amountCtrl.text = Fmt.money0(detectedAmount);
  }

  void _submit() {
    final rawAmount = _amountCtrl.text.replaceAll(RegExp(r'[^\d]'), '');
    final amount = double.tryParse(rawAmount) ?? 0;
    if (amount <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Nominal transaksi belanja wajib diisi.')),
      );
      return;
    }

    final result = OcrReceiptResult(
      amount: amount,
      note: _merchantCtrl.text.trim().isNotEmpty ? _merchantCtrl.text.trim() : 'Belanja Nota/Struk',
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
        title: const Text('📷 Smart Scan Struk / Nota'),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Image Preview / Action Area
          Container(
            height: 220,
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: AppColors.border),
              boxShadow: AppColors.cardShadow,
            ),
            child: _imageFile != null
                ? ClipRRect(
                    borderRadius: BorderRadius.circular(20),
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        Image.file(_imageFile!, fit: BoxFit.cover),
                        if (_scanning)
                          Container(
                            color: Colors.black54,
                            child: const Center(
                              child: Column(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  CircularProgressIndicator(color: Colors.white),
                                  SizedBox(height: 12),
                                  Text(
                                    'Memindai struk belanja...',
                                    style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700),
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
                        width: 58,
                        height: 58,
                        decoration: BoxDecoration(
                          color: AppColors.primarySubtle,
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.receipt_long_rounded, size: 30, color: AppColors.primary),
                      ),
                      const SizedBox(height: 12),
                      const Text(
                        'Ambil Foto Struk / Nota Fisik',
                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800),
                      ),
                      const SizedBox(height: 4),
                      const Text(
                        'Indomaret, Alfamart, SPBU, Resto, atau Nota Toko',
                        style: TextStyle(fontSize: 11.5, color: AppColors.textMuted),
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
                  onPressed: () => _pickImage(ImageSource.camera),
                  icon: const Icon(Icons.camera_alt_rounded, size: 18),
                  label: const Text('Buka Kamera', style: TextStyle(fontWeight: FontWeight.w800)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () => _pickImage(ImageSource.gallery),
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
              final textCtrl = TextEditingController();
              final ok = await showDialog<bool>(
                context: context,
                builder: (ctx) => AlertDialog(
                  title: const Text('Tempel Teks Struk / Nota'),
                  content: TextField(
                    controller: textCtrl,
                    maxLines: 4,
                    decoration: const InputDecoration(
                      hintText: 'Contoh: TOTAL BELANJA Rp 125.000...',
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
                _parseManualText(textCtrl.text);
              }
            },
            icon: const Icon(Icons.paste_rounded, size: 18),
            label: const Text('Tempel Teks Nota / SMS Bank', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 10),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
          ),
          const SizedBox(height: 16),

          // Extracted Result Card
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: AppColors.border),
              boxShadow: AppColors.cardShadow,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Text('✨', style: TextStyle(fontSize: 16)),
                    SizedBox(width: 6),
                    Text('Hasil Ekstraksi Data Struk', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
                  ],
                ),
                const SizedBox(height: 14),

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
                const SizedBox(height: 16),

                FilledButton.icon(
                  onPressed: _submit,
                  icon: const Icon(Icons.check_circle_rounded, size: 18),
                  label: const Text('Gunakan untuk Transaksi', style: TextStyle(fontWeight: FontWeight.w800)),
                  style: FilledButton.styleFrom(
                    minimumSize: const Size.fromHeight(48),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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
