import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../config/api_config.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class TaxReportScreen extends StatefulWidget {
  const TaxReportScreen({super.key});

  @override
  State<TaxReportScreen> createState() => _TaxReportScreenState();
}

class _TaxReportScreenState extends State<TaxReportScreen> {
  int _selectedYear = DateTime.now().year;
  bool _loading = false;
  Map<String, dynamic>? _data;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.instance.taxReport(year: _selectedYear);
      if (!mounted) return;
      setState(() {
        _data = res;
        _loading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _changeYear() async {
    final picked = await showDialog<int>(
      context: context,
      builder: (ctx) {
        final currentYear = DateTime.now().year;
        return SimpleDialog(
          title: const Text('Pilih Tahun Pajak'),
          children: List.generate(6, (i) {
            final y = currentYear - i;
            return SimpleDialogOption(
              onPressed: () => Navigator.pop(ctx, y),
              child: Text('$y', style: const TextStyle(fontWeight: FontWeight.w700)),
            );
          }),
        );
      },
    );
    if (picked != null && picked != _selectedYear) {
      setState(() => _selectedYear = picked);
      _loadData();
    }
  }

  Future<void> _openWebReport() async {
    final url = Uri.parse(ApiService.instance.taxReportPdfUrl(_selectedYear));
    if (await canLaunchUrl(url)) {
      await launchUrl(url, mode: LaunchMode.externalApplication);
    }
  }

  Future<void> _downloadCsv() async {
    final url = Uri.parse(ApiService.instance.taxReportCsvUrl(_selectedYear));
    if (await canLaunchUrl(url)) {
      await launchUrl(url, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    final pphFinal = _data?['pph_final'] as Map<String, dynamic>? ?? {};
    final pph21 = _data?['pph_21'] as Map<String, dynamic>? ?? {};
    final yearly = _data?['yearly'] as Map<String, dynamic>? ?? {};
    final symbol = _data?['symbol']?.toString() ?? 'Rp';
    final months = yearly['months'] as List<dynamic>? ?? [];
    final totalIncome = Fmt.toDouble(yearly['total_income'] ?? 0);
    final totalExpense = Fmt.toDouble(yearly['total_expense'] ?? 0);

    final isFree = pphFinal['is_free'] == true;
    final umkmTax = Fmt.toDouble(pphFinal['yearly_tax'] ?? 0);
    final pph21Yearly = Fmt.toDouble(pph21['yearly_tax'] ?? 0);
    final pph21Monthly = Fmt.toDouble(pph21['monthly_tax'] ?? 0);

    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Laporan Pajak'),
        actions: [
          IconButton(
            onPressed: _openWebReport,
            icon: const Icon(Icons.open_in_browser_rounded),
            tooltip: 'Buka Laporan di Browser',
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
              children: [
                // Year selector
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
                          const Text('TAHUN PAJAK',
                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textMuted)),
                          const SizedBox(height: 4),
                          Text('$_selectedYear',
                              style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                        ],
                      ),
                      OutlinedButton.icon(
                        onPressed: _changeYear,
                        icon: const Icon(Icons.calendar_month_outlined, size: 16),
                        label: const Text('Ganti Tahun', style: TextStyle(fontWeight: FontWeight.w700)),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // Summary cards
                Row(
                  children: [
                    Expanded(child: _summaryCard('Pemasukan', '+ $symbol ${Fmt.money0(totalIncome)}', AppColors.income)),
                    const SizedBox(width: 10),
                    Expanded(child: _summaryCard('Pengeluaran', '- $symbol ${Fmt.money0(totalExpense)}', AppColors.expense)),
                  ],
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(child: _summaryCard(
                      isFree ? 'PPh Final (BEBAS)' : 'PPh Final UMKM',
                      isFree ? 'Rp 0' : '$symbol ${Fmt.money0(umkmTax)}',
                      isFree ? AppColors.income : const Color(0xFF7C3AED),
                    )),
                    const SizedBox(width: 10),
                    Expanded(child: _summaryCard('PPh 21/bulan', '$symbol ${Fmt.money0(pph21Monthly)}', const Color(0xFF7C3AED))),
                  ],
                ),
                const SizedBox(height: 24),

                // ── PPh Final UMKM Section ──
                const Text('PPH FINAL UMKM (0.5%)',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
                const SizedBox(height: 10),

                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: isFree ? const Color(0xFFECFDF5) : const Color(0xFFFEF3C7),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: isFree ? const Color(0xFFA7F3D0) : const Color(0xFFFDE68A)),
                  ),
                  child: Row(
                    children: [
                      Text(isFree ? '✅' : '⚠️', style: const TextStyle(fontSize: 20)),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              isFree ? 'Bebas PPh Final (UMKM OP)' : 'Kena PPh Final 0.5%',
                              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: isFree ? const Color(0xFF059669) : const Color(0xFFD97706)),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              isFree
                                  ? 'Omset tahunan di bawah Rp 500 Juta, sesuai UU HPP No. 7/2021.'
                                  : 'Omset tahunan melebihi Rp 500 Juta. PPh Final 0.5% dikenakan.',
                              style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // Monthly breakdown table
                Container(
                  decoration: BoxDecoration(
                    color: AppColors.card,
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(color: AppColors.border),
                    boxShadow: AppColors.cardShadow,
                  ),
                  child: Column(
                    children: [
                      // Table header
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                        child: Row(
                          children: [
                            const Expanded(flex: 3, child: Text('Bulan', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textMuted))),
                            Expanded(flex: 4, child: Text('Omset', textAlign: TextAlign.right, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textMuted))),
                            Expanded(flex: 3, child: Text('PPh 0.5%', textAlign: TextAlign.right, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textMuted))),
                          ],
                        ),
                      ),
                      const Divider(height: 1),
                      ...months.map((m) {
                        final income = Fmt.toDouble(m['income'] ?? 0);
                        final tax = income * 0.005;
                        return Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                          child: Row(
                            children: [
                              Expanded(
                                flex: 3,
                                child: Text(m['label']?.toString() ?? '', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                              ),
                              Expanded(
                                flex: 4,
                                child: Text(
                                  income > 0 ? '$symbol ${Fmt.money0(income)}' : '—',
                                  textAlign: TextAlign.right,
                                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: income > 0 ? AppColors.textPrimary : AppColors.textMuted),
                                ),
                              ),
                              Expanded(
                                flex: 3,
                                child: Text(
                                  (isFree || income == 0) ? '—' : '$symbol ${Fmt.money0(tax)}',
                                  textAlign: TextAlign.right,
                                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: (!isFree && income > 0) ? const Color(0xFF7C3AED) : AppColors.textMuted),
                                ),
                              ),
                            ],
                          ),
                        );
                      }),
                    ],
                  ),
                ),
                const SizedBox(height: 24),

                // ── PPh 21 Section ──
                const Text('SIMULASI PPH 21 PRIBADI',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
                const SizedBox(height: 10),

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
                      const Text('Perhitungan PPh 21 (Status TK/0, PTKP Rp 54 Juta)',
                          style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
                      const SizedBox(height: 12),
                      _pph21Row('Penghasilan Kotor Tahunan', '$symbol ${Fmt.money0(Fmt.toDouble(pph21['total_income'] ?? 0))}'),
                      _pph21Row('Biaya Jabatan (5%, max Rp 6 Jt)', '$symbol ${Fmt.money0(Fmt.toDouble(pph21['biaya_jabatan'] ?? 0))}'),
                      _pph21Row('Penghasilan Netto Tahunan', '$symbol ${Fmt.money0(Fmt.toDouble(pph21['netto_tahunan'] ?? 0))}'),
                      _pph21Row('PTKP (TK/0)', '$symbol ${Fmt.money0(Fmt.toDouble(pph21['ptkp'] ?? 0))}'),
                      const Divider(height: 20),
                      _pph21Row('PKP (Penghasilan Kena Pajak)', '$symbol ${Fmt.money0(Fmt.toDouble(pph21['pkp'] ?? 0))}', bold: true),
                      const SizedBox(height: 12),
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: const Color(0xFF0F172A),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text('PPh 21 / Bulan', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Color(0xFF94A3B8))),
                                const SizedBox(height: 4),
                                Text('$symbol ${Fmt.money0(pph21Monthly)}',
                                    style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900, color: Color(0xFFA78BFA))),
                              ],
                            ),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                const Text('PPh 21 / Tahun', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Color(0xFF94A3B8))),
                                const SizedBox(height: 4),
                                Text('$symbol ${Fmt.money0(pph21Yearly)}',
                                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Color(0xFFCBD5E1))),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),

                // ── Export Options ──
                const Text('EKSPOR LAPORAN',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
                const SizedBox(height: 10),

                _exportOptionCard(
                  icon: Icons.open_in_browser_rounded,
                  iconColor: const Color(0xFF2563EB),
                  iconBg: const Color(0xFFDBEAFE),
                  title: 'Buka Laporan Pajak (HTML)',
                  subtitle: 'Tampilan resmi A4 untuk cetak / simpan PDF',
                  onTap: _openWebReport,
                ),
                const SizedBox(height: 10),
                _exportOptionCard(
                  icon: Icons.table_chart_rounded,
                  iconColor: const Color(0xFF16A34A),
                  iconBg: const Color(0xFFDCFCE7),
                  title: 'Download CSV',
                  subtitle: 'Data pajak bulanan untuk diolah di Excel',
                  onTap: _downloadCsv,
                ),
              ],
            ),
    );
  }

  Widget _summaryCard(String label, String value, Color color) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textMuted)),
          const SizedBox(height: 6),
          Text(value, style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: color)),
        ],
      ),
    );
  }

  Widget _pph21Row(String label, String value, {bool bold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(fontSize: 12, fontWeight: bold ? FontWeight.w800 : FontWeight.w600, color: AppColors.textSecondary)),
          Text(value, style: TextStyle(fontSize: 13, fontWeight: bold ? FontWeight.w800 : FontWeight.w700, color: AppColors.textPrimary)),
        ],
      ),
    );
  }

  Widget _exportOptionCard({
    required IconData icon,
    required Color iconColor,
    required Color iconBg,
    required String title,
    required String subtitle,
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
                  Text(title, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
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
