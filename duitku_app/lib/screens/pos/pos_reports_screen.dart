import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class PosReportsScreen extends StatefulWidget {
  final String symbol;
  const PosReportsScreen({super.key, this.symbol = 'Rp'});

  @override
  State<PosReportsScreen> createState() => _PosReportsScreenState();
}

class _PosReportsScreenState extends State<PosReportsScreen> {
  bool _loading = true;
  String? _error;
  Map<String, dynamic> _report = {};
  String _month = '';
  late String _monthKey;

  @override
  void initState() {
    super.initState();
    final now = DateTime.now();
    _monthKey = '${now.year}-${now.month.toString().padLeft(2, '0')}';
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.instance.posReports(month: _monthKey);
      setState(() {
        _report = res['report'] as Map<String, dynamic>? ?? {};
        _month = res['month']?.toString() ?? '';
        _loading = false;
        _error = null;
      });
    } catch (e) {
      setState(() {
        _loading = false;
        _error = '$e';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final summary = _report['summary'] as Map<String, dynamic>? ?? {};
    final bestSellers = _report['bestSellers'] as List<dynamic>? ?? [];
    final payments = _report['payments'] as List<dynamic>? ?? [];

    return Scaffold(
      appBar: AppBar(
        title: const Text('📊 Laporan Laba Rugi POS'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text(_error!))
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      // Month Selector & Hero P&L
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFF1E1B4B), Color(0xFF312E81)],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(20),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(0xFF1E1B4B).withValues(alpha: 0.35),
                              blurRadius: 14,
                              offset: const Offset(0, 5),
                            ),
                          ],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  'LABA BERSIH ($monthStr)',
                                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.white70),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withValues(alpha: 0.15),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Text(
                                    '${summary['total_orders'] ?? 0} Transaksi',
                                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Colors.white),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 4),
                            Text(
                              Fmt.money(summary['total_profit'] ?? 0, symbol: widget.symbol),
                              style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w900, color: Color(0xFF34D399)),
                            ),
                            const SizedBox(height: 14),
                            Row(
                              children: [
                                Expanded(
                                  child: Container(
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: Colors.white.withValues(alpha: 0.08),
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        const Text('Omset Penjualan', style: TextStyle(fontSize: 10.5, color: Colors.white70)),
                                        const SizedBox(height: 2),
                                        Text(
                                          Fmt.money(summary['total_sales'] ?? 0, symbol: widget.symbol),
                                          style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: Colors.white),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Container(
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: Colors.white.withValues(alpha: 0.08),
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        const Text('Total Modal (HPP)', style: TextStyle(fontSize: 10.5, color: Colors.white70)),
                                        const SizedBox(height: 2),
                                        Text(
                                          Fmt.money(summary['total_cost'] ?? 0, symbol: widget.symbol),
                                          style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: Color(0xFFFCA5A5)),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Top 5 Best Sellers
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: AppColors.card,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('🏆 5 Produk Terlaris (Best Seller)', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
                            const SizedBox(height: 10),
                            if (bestSellers.isEmpty)
                              const Padding(
                                padding: EdgeInsets.symmetric(vertical: 16),
                                child: Center(
                                  child: Text('Belum ada transaksi pada bulan ini.', style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                                ),
                              )
                            else
                              ...bestSellers.asMap().entries.map((entry) {
                                final idx = entry.key;
                                final item = entry.value as Map<String, dynamic>;
                                final rankColor = idx == 0
                                    ? const Color(0xFFF59E0B)
                                    : (idx == 1 ? const Color(0xFF94A3B8) : (idx == 2 ? const Color(0xFFB45309) : AppColors.primary));

                                return Padding(
                                  padding: const EdgeInsets.symmetric(vertical: 6),
                                  child: Row(
                                    children: [
                                      Container(
                                        width: 22,
                                        height: 22,
                                        decoration: BoxDecoration(color: rankColor, shape: BoxShape.circle),
                                        child: Center(
                                          child: Text('${idx + 1}', style: const TextStyle(color: Colors.white, fontSize: 10.5, fontWeight: FontWeight.w900)),
                                        ),
                                      ),
                                      const SizedBox(width: 10),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(item['product_name']?.toString() ?? '', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                                            Text('${item['total_qty']} terjual', style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                                          ],
                                        ),
                                      ),
                                      Text(
                                        Fmt.money(item['total_revenue'], symbol: widget.symbol),
                                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Color(0xFFEA580C)),
                                      ),
                                    ],
                                  ),
                                );
                              }),
                          ],
                        ),
                      ),
                      const SizedBox(height: 14),

                      // Peak Hours Section
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: AppColors.card,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('⏰ Analisis Jam Sibuk (Peak Hours)', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
                            const SizedBox(height: 4),
                            const Text('Distribusi jam transaksi untuk optimasi operasional & staf.', style: TextStyle(fontSize: 11, color: AppColors.textMuted)),
                            const SizedBox(height: 10),
                            if ((_report['peakHours'] as List?)?.isEmpty ?? true)
                              const Padding(
                                padding: EdgeInsets.symmetric(vertical: 14),
                                child: Center(
                                  child: Text('Belum ada data transaksi per jam.', style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                                ),
                              )
                            else
                              ...((_report['peakHours'] as List?) ?? []).map((ph) {
                                final h = (ph['hour_num'] as num?)?.toInt() ?? 0;
                                final count = (ph['count'] as num?)?.toInt() ?? 0;
                                final sales = (ph['total_sales'] as num?)?.toDouble() ?? 0.0;
                                final hLabel = '${h.toString().padLeft(2, '0')}:00 - ${((h + 1) % 24).toString().padLeft(2, '0')}:00';

                                return Container(
                                  margin: const EdgeInsets.only(bottom: 6),
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                                  decoration: BoxDecoration(
                                    color: AppColors.bg,
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(hLabel, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, fontFamily: 'monospace')),
                                      Row(
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                            decoration: BoxDecoration(
                                              color: const Color(0xFFEA580C).withValues(alpha: 0.15),
                                              borderRadius: BorderRadius.circular(6),
                                            ),
                                            child: Text('$count trx', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFFEA580C))),
                                          ),
                                          const SizedBox(width: 8),
                                          Text(Fmt.money(sales, symbol: widget.symbol), style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
                                        ],
                                      ),
                                    ],
                                  ),
                                );
                              }),
                          ],
                        ),
                      ),
                      const SizedBox(height: 14),

                      // Product Margins Section
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: AppColors.card,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('📊 Analisis Margin Keuntungan', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
                            const SizedBox(height: 4),
                            const Text('Persentase profit bersih per produk terjual.', style: TextStyle(fontSize: 11, color: AppColors.textMuted)),
                            const SizedBox(height: 10),
                            if ((_report['margins'] as List?)?.isEmpty ?? true)
                              const Padding(
                                padding: EdgeInsets.symmetric(vertical: 14),
                                child: Center(
                                  child: Text('Belum ada data margin produk.', style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                                ),
                              )
                            else
                              ...((_report['margins'] as List?) ?? []).map((m) {
                                final name = m['product_name']?.toString() ?? '';
                                final qty = (m['total_qty'] as num?)?.toInt() ?? 0;
                                final profit = (m['total_profit'] as num?)?.toDouble() ?? 0.0;
                                final marginPct = (m['margin_pct'] as num?)?.toDouble() ?? 0.0;

                                return Container(
                                  margin: const EdgeInsets.only(bottom: 6),
                                  padding: const EdgeInsets.all(10),
                                  decoration: BoxDecoration(
                                    color: AppColors.bg,
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Row(
                                    children: [
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(name, style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800)),
                                            Text('$qty terjual • Laba ${Fmt.money(profit, symbol: widget.symbol)}', style: const TextStyle(fontSize: 11, color: Color(0xFF10B981), fontWeight: FontWeight.w600)),
                                          ],
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: const Color(0xFFEA580C).withValues(alpha: 0.12),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          '${marginPct.toStringAsFixed(1)}%',
                                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w900, color: Color(0xFFEA580C)),
                                        ),
                                      ),
                                    ],
                                  ),
                                );
                              }),
                          ],
                        ),
                      ),
                      const SizedBox(height: 14),

                      // Payment Breakdown
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: AppColors.card,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('💳 Rincian Metode Pembayaran', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
                            const SizedBox(height: 10),
                            if (payments.isEmpty)
                              const Padding(
                                padding: EdgeInsets.symmetric(vertical: 16),
                                child: Center(
                                  child: Text('Belum ada data pembayaran.', style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                                ),
                              )
                            else
                              ...payments.map((p) {
                                String label;
                                switch (p['payment_method']) {
                                  case 'cash': label = '💵 Tunai / Cash'; break;
                                  case 'qris': label = '📱 QRIS'; break;
                                  case 'transfer': label = '💳 Transfer Bank'; break;
                                  case 'kasbon': label = '📒 Kasbon Pelanggan'; break;
                                  default: label = '${p['payment_method']}'.toUpperCase();
                                }

                                return Container(
                                  margin: const EdgeInsets.only(bottom: 6),
                                  padding: const EdgeInsets.all(10),
                                  decoration: BoxDecoration(
                                    color: AppColors.bg,
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(label, style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700)),
                                      Text(
                                        '${Fmt.money(p['total'], symbol: widget.symbol)} (${p['count']}x)',
                                        style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800),
                                      ),
                                    ],
                                  ),
                                );
                              }),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
    );
  }

  String get monthStr => _month.isNotEmpty ? _month : _monthKey;
}
