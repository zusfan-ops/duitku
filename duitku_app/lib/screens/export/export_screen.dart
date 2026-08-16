import 'package:flutter/material.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../config/api_config.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class ExportScreen extends StatefulWidget {
  const ExportScreen({super.key});

  @override
  State<ExportScreen> createState() => _ExportScreenState();
}

class _ExportScreenState extends State<ExportScreen> {
  DateTime _selectedDate = DateTime.now();
  bool _loading = false;
  Map<String, dynamic>? _stats;
  String _symbol = 'Rp';

  String get _monthKey => _selectedDate.toIso8601String().substring(0, 7);

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    try {
      final res = await Future.wait([
        ApiService.instance.stats(_monthKey),
        ApiService.instance.settings(),
      ]);
      if (!mounted) return;
      setState(() {
        _stats = res[0];
        _symbol = res[1]['symbol']?.toString() ?? 'Rp';
        _loading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _changeMonth() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime(2020),
      lastDate: DateTime(2030),
      helpText: 'PILIH BULAN LAPORAN',
    );
    if (picked != null && (picked.year != _selectedDate.year || picked.month != _selectedDate.month)) {
      setState(() => _selectedDate = picked);
      _loadData();
    }
  }

  Future<void> _generatePdf() async {
    setState(() => _loading = true);
    try {
      // Fetch activity/transactions for this month
      final actRes = await ApiService.instance.activity(type: 'all', page: 1);
      final rawTx = (actRes['transactions'] as List<dynamic>? ?? []);

      final pdf = pw.Document();

      final summary = _stats?['summary'] as Map<String, dynamic>? ?? {};
      final income = Fmt.toDouble(summary['income'] ?? summary['total_income']);
      final expense = Fmt.toDouble(summary['expense'] ?? summary['total_expense']);
      final balance = income - expense;

      pdf.addPage(
        pw.MultiPage(
          pageFormat: PdfPageFormat.a4,
          margin: const pw.EdgeInsets.all(32),
          header: (pw.Context context) {
            return pw.Container(
              padding: const pw.EdgeInsets.only(bottom: 12),
              decoration: const pw.BoxDecoration(
                border: pw.Border(bottom: pw.BorderSide(color: PdfColors.grey300, width: 1)),
              ),
              child: pw.Row(
                mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                children: [
                  pw.Column(
                    crossAxisAlignment: pw.CrossAxisAlignment.start,
                    children: [
                      pw.Text('DuitKu — Laporan Keuangan',
                          style: pw.TextStyle(fontSize: 16, fontWeight: pw.FontWeight.bold, color: PdfColors.green800)),
                      pw.Text('Periode: ${Fmt.monthLabel(_monthKey)}',
                          style: const pw.TextStyle(fontSize: 11, color: PdfColors.grey600)),
                    ],
                  ),
                  pw.Text('Dicetak: ${Fmt.dateDay(DateTime.now().toIso8601String().substring(0, 10))}',
                      style: const pw.TextStyle(fontSize: 9, color: PdfColors.grey500)),
                ],
              ),
            );
          },
          build: (pw.Context context) => [
            pw.SizedBox(height: 16),
            // Ringkasan Finansial Card
            pw.Container(
              padding: const pw.EdgeInsets.all(14),
              decoration: pw.BoxDecoration(
                color: PdfColors.grey100,
                borderRadius: pw.BorderRadius.circular(8),
              ),
              child: pw.Row(
                mainAxisAlignment: pw.MainAxisAlignment.spaceAround,
                children: [
                  _pdfSummaryBox('Total Pemasukan', '+ $_symbol ${Fmt.money0(income)}', PdfColors.green700),
                  _pdfSummaryBox('Total Pengeluaran', '- $_symbol ${Fmt.money0(expense)}', PdfColors.red700),
                  _pdfSummaryBox('Sisa Saldo Bersih', '$_symbol ${Fmt.money0(balance)}', balance >= 0 ? PdfColors.blue700 : PdfColors.red700),
                ],
              ),
            ),
            pw.SizedBox(height: 24),
            pw.Text('Daftar Transaksi', style: pw.TextStyle(fontSize: 13, fontWeight: pw.FontWeight.bold)),
            pw.SizedBox(height: 8),
            // Table
            pw.TableHelper.fromTextArray(
              headers: ['Tanggal', 'Tipe', 'Kategori', 'Catatan', 'Nominal'],
              data: rawTx.map((t) {
                final isInc = t['type']?.toString() == 'income';
                return [
                  t['date']?.toString() ?? '',
                  isInc ? 'Masuk' : 'Keluar',
                  t['category_name']?.toString() ?? 'Lainnya',
                  t['note']?.toString() ?? '-',
                  '${isInc ? '+' : '-'} $_symbol ${Fmt.money0(Fmt.toDouble(t['amount']))}',
                ];
              }).toList(),
              headerStyle: pw.TextStyle(fontSize: 9, fontWeight: pw.FontWeight.bold, color: PdfColors.white),
              headerDecoration: const pw.BoxDecoration(color: PdfColors.green800),
              cellStyle: const pw.TextStyle(fontSize: 8.5),
              cellHeight: 22,
              cellAlignments: {
                0: pw.Alignment.centerLeft,
                1: pw.Alignment.center,
                2: pw.Alignment.centerLeft,
                3: pw.Alignment.centerLeft,
                4: pw.Alignment.centerRight,
              },
            ),
          ],
        ),
      );

      setState(() => _loading = false);

      await Printing.layoutPdf(
        onLayout: (PdfPageFormat format) async => pdf.save(),
        name: 'duitku-laporan-$_monthKey.pdf',
      );
    } catch (e) {
      if (mounted) {
        setState(() => _loading = false);
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal export PDF: $e')));
      }
    }
  }

  pw.Widget _pdfSummaryBox(String label, String value, PdfColor color) {
    return pw.Column(
      children: [
        pw.Text(label, style: const pw.TextStyle(fontSize: 9, color: PdfColors.grey600)),
        pw.SizedBox(height: 4),
        pw.Text(value, style: pw.TextStyle(fontSize: 12, fontWeight: pw.FontWeight.bold, color: color)),
      ],
    );
  }

  Future<void> _openWebPdf() async {
    final url = Uri.parse(ApiService.instance.exportPdfUrl(_monthKey));
    if (await canLaunchUrl(url)) {
      await launchUrl(url, mode: LaunchMode.externalApplication);
    } else {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Tidak dapat membuka browser.')));
    }
  }

  Future<void> _downloadCsv() async {
    final url = Uri.parse('${ApiConfig.baseUrl}/export/csv?month=$_monthKey');
    if (await canLaunchUrl(url)) {
      await launchUrl(url, mode: LaunchMode.externalApplication);
    } else {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Tidak dapat mengunduh CSV.')));
    }
  }

  @override
  Widget build(BuildContext context) {
    final summary = _stats?['summary'] as Map<String, dynamic>? ?? {};
    final income = Fmt.toDouble(summary['income'] ?? summary['total_income']);
    final expense = Fmt.toDouble(summary['expense'] ?? summary['total_expense']);
    final balance = income - expense;

    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Export Laporan'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
              children: [
                // Month selector Card
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.card,
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(color: AppColors.border),
                    boxShadow: AppColors.cardShadow,
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('PERIODE LAPORAN',
                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textMuted)),
                          const SizedBox(height: 4),
                          Text(Fmt.monthLabel(_monthKey),
                              style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                        ],
                      ),
                      OutlinedButton.icon(
                        onPressed: _changeMonth,
                        icon: const Icon(Icons.calendar_month_outlined, size: 16),
                        label: const Text('Ganti Bulan', style: TextStyle(fontWeight: FontWeight.w700)),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // Summary preview Card
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
                      const Text('RINGKASAN PERIODE',
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textMuted)),
                      const SizedBox(height: 14),
                      Row(
                        children: [
                          Expanded(
                            child: _statItem('Pemasukan', '+ ${Fmt.money0(income)}', AppColors.income),
                          ),
                          Expanded(
                            child: _statItem('Pengeluaran', '- ${Fmt.money0(expense)}', AppColors.expense),
                          ),
                        ],
                      ),
                      const Divider(height: 24),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('Arus Kas Bersih', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                          Text(
                            Fmt.money(balance, symbol: _symbol),
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w800,
                              color: balance >= 0 ? AppColors.income : AppColors.expense,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),

                const Text('PILIHAN EKSPOR',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
                const SizedBox(height: 10),

                // Option 1: Native PDF (Print / Save)
                _exportOptionCard(
                  icon: Icons.picture_as_pdf_rounded,
                  iconColor: const Color(0xFFDC2626),
                  iconBg: const Color(0xFFFEE2E2),
                  title: 'Cetak & Simpan PDF Native',
                  subtitle: 'Format siap cetak, simpan langsung ke perangkat',
                  badge: 'Rekomendasi',
                  onTap: _generatePdf,
                ),
                const SizedBox(height: 10),

                // Option 2: Web PDF Preview
                _exportOptionCard(
                  icon: Icons.open_in_browser_rounded,
                  iconColor: const Color(0xFF2563EB),
                  iconBg: const Color(0xFFDBEAFE),
                  title: 'Buka Web Report (HTML)',
                  subtitle: 'Tampilkan laporan interaktif di browser web',
                  onTap: _openWebPdf,
                ),
                const SizedBox(height: 10),

                // Option 3: Export CSV
                _exportOptionCard(
                  icon: Icons.table_chart_rounded,
                  iconColor: const Color(0xFF16A34A),
                  iconBg: const Color(0xFFDCFCE7),
                  title: 'Export Data Excel / CSV',
                  subtitle: 'Unduh file CSV untuk diolah di Microsoft Excel / Sheets',
                  onTap: _downloadCsv,
                ),
              ],
            ),
    );
  }

  Widget _statItem(String label, String value, Color color) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
        const SizedBox(height: 4),
        Text(
          value,
          style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: color),
        ),
      ],
    );
  }

  Widget _exportOptionCard({
    required IconData icon,
    required Color iconColor,
    required Color iconBg,
    required String title,
    required String subtitle,
    String? badge,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.border),
          boxShadow: AppColors.cardShadow,
        ),
        child: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: iconBg,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: iconColor, size: 22),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Text(title, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                      if (badge != null) ...[
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: AppColors.primary.withValues(alpha: .1),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(badge, style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: AppColors.primary)),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 2),
                  Text(subtitle, style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                ],
              ),
            ),
            const Icon(Icons.chevron_right, color: AppColors.textMuted),
          ],
        ),
      ),
    );
  }
}
