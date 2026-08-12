import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';

import '../services/api_service.dart';
import '../theme.dart';
import '../utils/format.dart';
import '../widgets/category_icon.dart';

class StatsScreen extends StatefulWidget {
  const StatsScreen({super.key});

  @override
  State<StatsScreen> createState() => _StatsScreenState();
}

class _StatsScreenState extends State<StatsScreen> {
  String _month = DateTime.now().toIso8601String().substring(0, 7);
  bool _loading = true;
  String? _error;
  Map<String, dynamic>? _data;

  List<Map<String, dynamic>> get _catStats =>
      (_data?['catStats'] as List<dynamic>? ?? []).cast<Map<String, dynamic>>();
  List<Map<String, dynamic>> get _trend =>
      (_data?['trend'] as List<dynamic>? ?? []).cast<Map<String, dynamic>>();
  Map<String, dynamic> get _monthly =>
      (_data?['monthly'] as Map<String, dynamic>?) ?? {};
  String get _symbol => _data?['symbol']?.toString() ?? 'Rp';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final res = await ApiService.instance.stats(_month);
      if (!mounted) return;
      setState(() {
        _data = res;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _error = 'Tidak dapat memuat statistik.';
        _loading = false;
      });
    }
  }

  void _shift(int delta) {
    final parts = _month.split('-');
    final y = int.parse(parts[0]);
    final m = int.parse(parts[1]);
    final total = y * 12 + (m - 1) + delta;
    final ny = total ~/ 12;
    final nm = total % 12 + 1;
    setState(() {
      _month = '${ny.toString().padLeft(4, '0')}-${nm.toString().padLeft(2, '0')}';
    });
    _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(title: const Text('Statistik')),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.wifi_off, size: 44, color: AppColors.textMuted),
            const SizedBox(height: 12),
            Text(_error!, style: const TextStyle(fontSize: 14, color: AppColors.textSecondary)),
            const SizedBox(height: 16),
            FilledButton(onPressed: _load, child: const Text('Coba Lagi')),
          ],
        ),
      );
    }

    final income = Fmt.toDouble(_monthly['income']);
    final expense = Fmt.toDouble(_monthly['expense']);
    final balance = Fmt.toDouble(_monthly['balance']);

    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
        children: [
          // Month navigation
          Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
            ),
            child: Row(
              children: [
                IconButton(
                  onPressed: () => _shift(-1),
                  icon: const Icon(Icons.chevron_left),
                ),
                Expanded(
                  child: Column(
                    children: [
                      Text(Fmt.monthLabel(_month),
                          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                      const Text('Pilih bulan',
                          style: TextStyle(fontSize: 10, color: AppColors.textMuted)),
                    ],
                  ),
                ),
                IconButton(
                  onPressed: () => _shift(1),
                  icon: const Icon(Icons.chevron_right),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          // Summary cards
          Row(
            children: [
              Expanded(
                child: _SumCard(
                  label: 'Pemasukan',
                  value: Fmt.money(income, symbol: _symbol),
                  color: AppColors.income,
                  icon: Icons.south_west,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _SumCard(
                  label: 'Pengeluaran',
                  value: Fmt.money(expense, symbol: _symbol),
                  color: AppColors.expense,
                  icon: Icons.north_east,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
            ),
            child: Row(
              children: [
                const Icon(Icons.account_balance_wallet_outlined, color: AppColors.primary),
                const SizedBox(width: 10),
                const Text('Selisih', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                const Spacer(),
                Text(Fmt.money(balance, symbol: _symbol),
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: balance >= 0 ? AppColors.income : AppColors.expense,
                    )),
              ],
            ),
          ),
          const SizedBox(height: 18),
          // Trend chart
          if (_trend.length > 1) ...[
            const Text('TREN 6 BULAN',
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
            const SizedBox(height: 8),
            _TrendChart(trend: _trend, symbol: _symbol),
            const SizedBox(height: 18),
          ],
          // Category breakdown
          if (_catStats.isNotEmpty) ...[
            const Text('PENGELUARAN PER KATEGORI',
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
            const SizedBox(height: 8),
            _CategoryBreakdown(stats: _catStats, symbol: _symbol),
          ] else ...[
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 40),
              child: Column(
                children: [
                  Icon(Icons.pie_chart_outline, size: 44, color: AppColors.textMuted),
                  SizedBox(height: 10),
                  Text('Belum ada data bulan ini',
                      style: TextStyle(fontSize: 14, color: AppColors.textSecondary)),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _SumCard extends StatelessWidget {
  final String label;
  final String value;
  final Color color;
  final IconData icon;
  const _SumCard({required this.label, required this.value, required this.color, required this.icon});

  @override
  Widget build(BuildContext context) {
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
          Container(
            width: 30,
            height: 30,
            decoration: BoxDecoration(color: color.withValues(alpha: .12), borderRadius: BorderRadius.circular(9)),
            child: Icon(icon, size: 16, color: color),
          ),
          const SizedBox(height: 8),
          Text(label, style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
          const SizedBox(height: 2),
          FittedBox(
            fit: BoxFit.scaleDown,
            child: Text(value,
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: color)),
          ),
        ],
      ),
    );
  }
}

class _TrendChart extends StatelessWidget {
  final List<Map<String, dynamic>> trend;
  final String symbol;
  const _TrendChart({required this.trend, required this.symbol});

  @override
  Widget build(BuildContext context) {
    final income = trend.map((e) => Fmt.toDouble(e['income'])).toList();
    final expense = trend.map((e) => Fmt.toDouble(e['expense'])).toList();
    final maxV = [
      ...income,
      ...expense,
    ].fold<double>(0, (m, v) => v > m ? v : m);

    return Container(
      padding: const EdgeInsets.all(14),
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
              _LegendDot(color: AppColors.income, label: 'Pemasukan'),
              SizedBox(width: 14),
              _LegendDot(color: AppColors.expense, label: 'Pengeluaran'),
            ],
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 160,
            child: BarChart(
              BarChartData(
                gridData: const FlGridData(show: false),
                borderData: FlBorderData(show: false),
                barTouchData: BarTouchData(
                  touchTooltipData: BarTouchTooltipData(
                    getTooltipItem: (group, gi, rod, ri) {
                      final m = trend[group.x.toInt()];
                      final label = m['month_label']?.toString() ?? '';
                      final value = Fmt.money(rod.toY, symbol: symbol);
                      return BarTooltipItem('$label\n$value',
                          const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700));
                    },
                  ),
                ),
                titlesData: FlTitlesData(
                  topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  leftTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  bottomTitles: AxisTitles(
                    sideTitles: SideTitles(
                      showTitles: true,
                      reservedSize: 20,
                      getTitlesWidget: (v, meta) {
                        final i = v.toInt();
                        if (i < 0 || i >= trend.length) return const SizedBox.shrink();
                        return Padding(
                          padding: const EdgeInsets.only(top: 4),
                          child: Text(trend[i]['month_label']?.toString() ?? '',
                              style: const TextStyle(fontSize: 10, color: AppColors.textMuted)),
                        );
                      },
                    ),
                  ),
                ),
                maxY: maxV <= 0 ? 1 : maxV * 1.15,
                barGroups: List.generate(trend.length, (i) {
                  return BarChartGroupData(
                    x: i,
                    barsSpace: 3,
                    barRods: [
                      BarChartRodData(
                        toY: income[i],
                        width: 7,
                        color: AppColors.income,
                        borderRadius: const BorderRadius.vertical(top: Radius.circular(3)),
                      ),
                      BarChartRodData(
                        toY: expense[i],
                        width: 7,
                        color: AppColors.expense,
                        borderRadius: const BorderRadius.vertical(top: Radius.circular(3)),
                      ),
                    ],
                  );
                }),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _LegendDot extends StatelessWidget {
  final Color color;
  final String label;
  const _LegendDot({required this.color, required this.label});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(width: 8, height: 8, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
        const SizedBox(width: 5),
        Text(label, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
      ],
    );
  }
}

class _CategoryBreakdown extends StatelessWidget {
  final List<Map<String, dynamic>> stats;
  final String symbol;
  const _CategoryBreakdown({required this.stats, required this.symbol});

  @override
  Widget build(BuildContext context) {
    final expenses = stats.where((s) => s['type']?.toString() == 'expense').toList();
    final incomes = stats.where((s) => s['type']?.toString() == 'income').toList();

    return Column(
      children: [
        if (expenses.isNotEmpty) _CatGroup(title: 'PENGELUARAN', items: expenses, symbol: symbol),
        const SizedBox(height: 10),
        if (incomes.isNotEmpty) _CatGroup(title: 'PEMASUKAN', items: incomes, symbol: symbol),
      ],
    );
  }
}

class _CatGroup extends StatelessWidget {
  final String title;
  final List<Map<String, dynamic>> items;
  final String symbol;
  const _CatGroup({required this.title, required this.items, required this.symbol});

  @override
  Widget build(BuildContext context) {
    final total = items.fold<double>(0, (s, e) => s + Fmt.toDouble(e['total']));
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title,
              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
          const SizedBox(height: 8),
                    ...items.map((e) {
            final val = Fmt.toDouble(e['total']);            final pct = total <= 0 ? 0.0 : val / total;
            final color = parseColor(e['color']?.toString() ?? '#6B7280');
            return Padding(
              padding: const EdgeInsets.symmetric(vertical: 6),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 26,
                        height: 26,
                        decoration: BoxDecoration(
                          color: color.withValues(alpha: .12),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Icon(categoryIcon(e['icon']?.toString() ?? 'other'), size: 14, color: color),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(e['category']?.toString() ?? 'Lainnya',
                            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
                      ),
                      Text(Fmt.money(val, symbol: symbol),
                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                      const SizedBox(width: 6),
                      SizedBox(
                        width: 34,
                        child: Text('${(pct * 100).round()}%',
                            textAlign: TextAlign.end,
                            style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(3),
                    child: LinearProgressIndicator(
                      value: pct,
                      minHeight: 5,
                      backgroundColor: AppColors.bg,
                      valueColor: AlwaysStoppedAnimation(color),
                    ),
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }
}
