import 'package:flutter/material.dart';
import 'package:qr_flutter/qr_flutter.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../services/api_service.dart';
import '../../theme.dart';

class PosQrScreen extends StatefulWidget {
  const PosQrScreen({super.key});

  @override
  State<PosQrScreen> createState() => _PosQrScreenState();
}

class _PosQrScreenState extends State<PosQrScreen> {
  final ApiService _api = ApiService();
  bool _loading = true;

  Map<String, dynamic> _store = {};
  String _menuUrl = '';
  String _tableNo = '';
  String _theme = 'modern'; // modern, vintage, dark

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    setState(() => _loading = true);
    try {
      final res = await _api.getPosStoreProfile();
      if (mounted) {
        setState(() {
          _store = (res['store'] as Map<String, dynamic>?) ?? {};
          _menuUrl = res['menu_url']?.toString() ?? '';
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _loading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memuat profil toko: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  String get _targetUrl {
    if (_menuUrl.isEmpty) return '';
    if (_tableNo.isNotEmpty) {
      return '$_menuUrl?table=${Uri.encodeComponent(_tableNo)}';
    }
    return _menuUrl;
  }

  Future<void> _printStandeePdf() async {
    final storeName = _store['store_name']?.toString() ?? 'Toko POS';
    final qrFooter = _store['store_qr_footer']?.toString() ?? 'Scan QR untuk melihat menu & memesan langsung.';
    final targetUrl = _targetUrl;
    final tableNo = _tableNo;

    final doc = pw.Document();

    doc.addPage(
      pw.Page(
        pageFormat: PdfPageFormat.a4,
        margin: const pw.EdgeInsets.all(32),
        build: (pw.Context context) {
          return pw.Center(
            child: pw.Container(
              width: 320,
              padding: const pw.EdgeInsets.all(24),
              decoration: pw.BoxDecoration(
                border: pw.Border.all(color: PdfColors.orange800, width: 4),
                borderRadius: pw.BorderRadius.circular(16),
                color: PdfColors.white,
              ),
              child: pw.Column(
                mainAxisSize: pw.MainAxisSize.min,
                crossAxisAlignment: pw.CrossAlignment.center,
                children: [
                  pw.Container(
                    padding: const pw.EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                    decoration: pw.BoxDecoration(
                      color: PdfColors.orange800,
                      borderRadius: pw.BorderRadius.circular(12),
                    ),
                    child: pw.Text(
                      'SCAN & PESAN DI SINI',
                      style: pw.TextStyle(
                        color: PdfColors.white,
                        fontSize: 10,
                        fontWeight: pw.FontWeight.bold,
                      ),
                    ),
                  ),
                  pw.SizedBox(height: 10),
                  pw.Text(
                    storeName,
                    style: pw.TextStyle(
                      fontSize: 20,
                      fontWeight: pw.FontWeight.bold,
                      color: PdfColors.grey900,
                    ),
                    textAlign: pw.TextAlign.center,
                  ),
                  if (tableNo.isNotEmpty) ...[
                    pw.SizedBox(height: 4),
                    pw.Container(
                      padding: const pw.EdgeInsets.symmetric(horizontal: 10, vertical: 2),
                      decoration: pw.BoxDecoration(
                        color: PdfColors.orange50,
                        borderRadius: pw.BorderRadius.circular(8),
                      ),
                      child: pw.Text(
                        'Nomor Meja $tableNo',
                        style: pw.TextStyle(color: PdfColors.orange900, fontSize: 11, fontWeight: pw.FontWeight.bold),
                      ),
                    ),
                  ],
                  pw.SizedBox(height: 14),
                  pw.Container(
                    padding: const pw.EdgeInsets.all(10),
                    decoration: pw.BoxDecoration(
                      border: pw.Border.all(color: PdfColors.orange300, width: 2),
                      borderRadius: pw.BorderRadius.circular(12),
                      color: PdfColors.orange50,
                    ),
                    child: pw.BarcodeWidget(
                      barcode: pw.Barcode.qrCode(),
                      data: targetUrl,
                      width: 150,
                      height: 150,
                    ),
                  ),
                  pw.SizedBox(height: 10),
                  pw.Container(
                    padding: const pw.EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: pw.BoxDecoration(
                      color: PdfColors.grey200,
                      borderRadius: pw.BorderRadius.circular(6),
                    ),
                    child: pw.Text(
                      targetUrl,
                      style: pw.TextStyle(fontSize: 9, color: PdfColors.grey800),
                    ),
                  ),
                  pw.SizedBox(height: 8),
                  pw.Text(
                    qrFooter,
                    style: const pw.TextStyle(fontSize: 10, color: PdfColors.grey700),
                    textAlign: pw.TextAlign.center,
                  ),
                  pw.SizedBox(height: 12),
                  pw.Text(
                    'Powered by DuitKu POS',
                    style: const pw.TextStyle(fontSize: 8, color: PdfColors.grey500),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );

    await Printing.layoutPdf(
      onLayout: (PdfPageFormat format) async => doc.save(),
      name: 'Standee_QR_${storeName.replaceAll(' ', '_')}.pdf',
    );
  }

  void _openStoreSettingsDialog() {
    final nameCtrl = TextEditingController(text: _store['store_name'] ?? '');
    final slugCtrl = TextEditingController(text: _store['store_slug'] ?? '');
    final taglineCtrl = TextEditingController(text: _store['store_tagline'] ?? '');
    final addressCtrl = TextEditingController(text: _store['store_address'] ?? '');
    final phoneCtrl = TextEditingController(text: _store['store_phone'] ?? '');
    final footerCtrl = TextEditingController(text: _store['store_qr_footer'] ?? '');
    bool isOpen = _store['store_is_open'] == true;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Padding(
              padding: EdgeInsets.fromLTRB(20, 16, 20, MediaQuery.of(ctx).viewInsets.bottom + 20),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('⚙️ Profil Toko & QR Menu', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
                        IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                      ],
                    ),
                    const SizedBox(height: 12),

                    TextField(
                      controller: nameCtrl,
                      decoration: const InputDecoration(
                        labelText: 'Nama Toko / Outlet *',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 10),

                    TextField(
                      controller: slugCtrl,
                      decoration: const InputDecoration(
                        labelText: 'URL Slug (/menu/namatoko)',
                        prefixText: '/menu/',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 10),

                    TextField(
                      controller: taglineCtrl,
                      decoration: const InputDecoration(
                        labelText: 'Slogan / Keterangan Toko',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 10),

                    TextField(
                      controller: addressCtrl,
                      decoration: const InputDecoration(
                        labelText: 'Alamat Outlet',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 10),

                    TextField(
                      controller: phoneCtrl,
                      decoration: const InputDecoration(
                        labelText: 'Nomor Telepon / WA',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 10),

                    TextField(
                      controller: footerCtrl,
                      maxLines: 2,
                      decoration: const InputDecoration(
                        labelText: 'Teks Keterangan di Bawah QR',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 10),

                    SwitchListTile(
                      title: const Text('Buka Penerimaan Pesanan', style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700)),
                      value: isOpen,
                      activeColor: AppColors.primary,
                      onChanged: (val) => setModalState(() => isOpen = val),
                    ),

                    const SizedBox(height: 14),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: () async {
                          Navigator.pop(ctx);
                          try {
                            await _api.savePosStoreProfile({
                              'store_name': nameCtrl.text.trim(),
                              'store_slug': slugCtrl.text.trim(),
                              'store_tagline': taglineCtrl.text.trim(),
                              'store_address': addressCtrl.text.trim(),
                              'store_phone': phoneCtrl.text.trim(),
                              'store_qr_footer': footerCtrl.text.trim(),
                              'store_is_open': isOpen,
                            });
                            _loadProfile();
                            if (mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(content: Text('Profil toko berhasil disimpan!'), backgroundColor: Color(0xFF10B981)),
                              );
                            }
                          } catch (e) {
                            if (mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text('Gagal menyimpan: $e'), backgroundColor: Colors.red),
                              );
                            }
                          }
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primary,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: const Text('Simpan Perubahan', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Standee QR Menu Meja', style: TextStyle(fontWeight: FontWeight.w800)),
        backgroundColor: AppColors.surface,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.settings_rounded),
            tooltip: 'Pengaturan Profil Toko',
            onPressed: _openStoreSettingsDialog,
          ),
          IconButton(
            icon: const Icon(Icons.open_in_browser_rounded),
            tooltip: 'Buka Menu di Browser',
            onPressed: () {
              if (_targetUrl.isNotEmpty) {
                launchUrl(Uri.parse(_targetUrl), mode: LaunchMode.externalApplication);
              }
            },
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  // Controls Card
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Kustomisasi Standee Meja', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5)),
                        const SizedBox(height: 10),

                        // Table Input
                        TextField(
                          decoration: InputDecoration(
                            labelText: 'Nomor Meja (Opsional)',
                            hintText: 'Contoh: 01, Meja 5, VIP...',
                            prefixIcon: const Icon(Icons.table_restaurant_rounded, size: 18),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          ),
                          onChanged: (val) => setState(() => _tableNo = val.trim()),
                        ),
                        const SizedBox(height: 10),

                        // Frame Style Selector
                        Row(
                          children: [
                            const Text('Bingkai: ', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                            const SizedBox(width: 8),
                            _buildThemeChip('modern', '🔥 Oranye'),
                            const SizedBox(width: 6),
                            _buildThemeChip('vintage', '☕ Klasik'),
                            const SizedBox(width: 6),
                            _buildThemeChip('dark', '🌑 Dark'),
                          ],
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 20),

                  // Standee Poster Preview
                  _buildStandeePreview(),

                  const SizedBox(height: 20),

                  // Action Print PDF
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: _printStandeePdf,
                      icon: const Icon(Icons.print_rounded),
                      label: const Text('Cetak / Simpan PDF Standee', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF10B981),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),

                  const SizedBox(height: 10),
                  TextButton.icon(
                    onPressed: _openStoreSettingsDialog,
                    icon: const Icon(Icons.edit_note_rounded, size: 18),
                    label: const Text('Edit Nama Toko & Keterangan QR'),
                    style: TextButton.styleFrom(foregroundColor: AppColors.primary),
                  ),
                ],
              ),
            ),
    );
  }

  Widget _buildThemeChip(String key, String label) {
    final sel = _theme == key;
    return InkWell(
      onTap: () => setState(() => _theme = key),
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: sel ? AppColors.primary.withValues(alpha: 0.15) : AppColors.surfaceVariant,
          border: Border.all(color: sel ? AppColors.primary : AppColors.border),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11.5,
            fontWeight: FontWeight.w700,
            color: sel ? AppColors.primary : AppColors.textSecondary,
          ),
        ),
      ),
    );
  }

  Widget _buildStandeePreview() {
    Color borderColor = const Color(0xFFEA580C);
    Color cardBg = Colors.white;
    Color textColor = const Color(0xFF0F172A);
    Color badgeColor = const Color(0xFFEA580C);
    Color qrFrameBg = const Color(0xFFFFF7ED);

    if (_theme == 'vintage') {
      borderColor = const Color(0xFF78350F);
      cardBg = const Color(0xFFFEF3C7);
      textColor = const Color(0xFF451A03);
      badgeColor = const Color(0xFF78350F);
      qrFrameBg = Colors.white;
    } else if (_theme == 'dark') {
      borderColor = const Color(0xFF38BDF8);
      cardBg = const Color(0xFF0F172A);
      textColor = Colors.white;
      badgeColor = const Color(0xFF38BDF8);
      qrFrameBg = Colors.white;
    }

    final storeName = _store['store_name']?.toString() ?? 'Toko POS';
    final qrFooter = _store['store_qr_footer']?.toString() ?? 'Scan QR untuk melihat menu & memesan langsung.';

    return Container(
      width: double.infinity,
      constraints: const BoxConstraints(maxWidth: 360),
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 28),
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: borderColor, width: 4),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.2),
            blurRadius: 20,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Column(
        children: [
          // Top Badge
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
            decoration: BoxDecoration(
              color: badgeColor,
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(
              '📱 SCAN & PESAN DI SINI',
              style: TextStyle(
                color: _theme == 'dark' ? const Color(0xFF0F172A) : Colors.white,
                fontSize: 10.5,
                fontWeight: FontWeight.w900,
                letterSpacing: 0.8,
              ),
            ),
          ),

          const SizedBox(height: 10),

          // Store Name
          Text(
            storeName,
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.w900,
              color: textColor,
            ),
            textAlign: TextAlign.center,
          ),

          // Table pill if selected
          if (_tableNo.isNotEmpty) ...[
            const SizedBox(height: 4),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 2),
              decoration: BoxDecoration(
                color: const Color(0xFFEA580C).withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                '🪑 Nomor Meja $_tableNo',
                style: const TextStyle(
                  color: Color(0xFFEA580C),
                  fontSize: 11.5,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ),
          ],

          const SizedBox(height: 14),

          // QR Code Frame
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: qrFrameBg,
              border: Border.all(color: borderColor, width: 2),
              borderRadius: BorderRadius.circular(16),
            ),
            child: _targetUrl.isNotEmpty
                ? QrImageView(
                    data: _targetUrl,
                    version: QrVersions.auto,
                    size: 160,
                    backgroundColor: Colors.white,
                    padding: const EdgeInsets.all(8),
                  )
                : const SizedBox(width: 160, height: 160, child: Center(child: CircularProgressIndicator())),
          ),

          const SizedBox(height: 10),

          // Target URL Pill
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: _theme == 'dark' ? const Color(0xFF1E293B) : const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: AppColors.border),
            ),
            child: Text(
              _targetUrl,
              style: TextStyle(
                fontSize: 10.5,
                fontWeight: FontWeight.w700,
                fontFamily: 'monospace',
                color: _theme == 'dark' ? const Color(0xFF38BDF8) : const Color(0xFF0F172A),
              ),
              textAlign: TextAlign.center,
            ),
          ),

          const SizedBox(height: 8),

          // Footer Text
          Text(
            qrFooter,
            style: TextStyle(
              fontSize: 11.5,
              color: _theme == 'dark' ? const Color(0xFF94A3B8) : const Color(0xFF475569),
              height: 1.4,
            ),
            textAlign: TextAlign.center,
          ),

          const SizedBox(height: 12),
          const Divider(height: 1),
          const SizedBox(height: 6),
          const Text(
            'Powered by DuitKu POS',
            style: TextStyle(fontSize: 9, color: AppColors.textMuted, fontWeight: FontWeight.w600),
          ),
        ],
      ),
    );
  }
}
