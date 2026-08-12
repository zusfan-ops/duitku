import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../config/api_config.dart';
import '../models/bill.dart';
import '../models/transaction.dart';
import '../models/wallet.dart';
import '../providers/app_data_provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../theme.dart';
import '../utils/format.dart';
import '../widgets/calculator_sheet.dart';
import '../widgets/category_icon.dart';
import '../widgets/transaction_tile.dart';
import 'barang/barang_screen.dart';
import 'bills_screen.dart';
import 'debt_screen.dart';
import 'note_sheet.dart';
import 'stats_screen.dart';
import 'transaction_sheet.dart';
import 'wallet_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => DashboardScreenState();
}

class DashboardScreenState extends State<DashboardScreen> {
  Map<String, dynamic>? _data;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> refresh() async {
    await _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final data = await ApiService.instance.dashboard();
      if (!mounted) return;
      setState(() {
        _data = {
          'dashboard': data,
          'recent': data.recent
              .map((e) => Transaction.fromJson(e as Map<String, dynamic>))
              .toList(),
        };
        _loading = false;
      });
      context.read<AppDataProvider>().ensureLoaded(force: true);
    } on ApiException catch (e) {
      if (!mounted) return;
      if (e.status == 401) {
        context.read<AuthProvider>().logout();
        return;
      }
      setState(() {
        _error = e.message;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = 'Tidak dapat terhubung ke server.\n(${e.runtimeType}: $e)';
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    return Scaffold(
      appBar: AppBar(
        title: Text('Halo, ${auth.user?.name.split(' ').first ?? ''} 👋'),
        actions: [
          PopupMenuButton<String>(
            onSelected: (v) {
              if (v == 'logout') auth.logout();
            },
            itemBuilder: (_) => [
              const PopupMenuItem(value: 'logout', child: Text('Keluar')),
            ],
            child: Padding(
              padding: const EdgeInsets.only(right: 12),
              child: _Avatar(),
            ),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: refresh,
        child: _buildBody(),
      ),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_error != null) {
      return _ErrorView(message: _error!, onRetry: _load);
    }
    final data = _data!['dashboard'] as dynamic;
    final recent = _data!['recent'] as List<Transaction>;
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
      children: [
        _HeroCard(data: data),
        if ((data.wallets as List).isNotEmpty) _WalletStrip(wallets: data.wallets as List<Wallet>),
        if ((data.dailyBalance as List).length > 1)
          _DailyChart(data: data),
        _ReminderCard(data: data),
        if (data.budget > 0) _BudgetCard(data: data),
        if ((data.topCategories as List).isNotEmpty) _TopCategoriesCard(data: data),
        _QuickActions(data: data),
        if (data.savingsTarget > 0) _SavingsCard(data: data),
        if ((data.monthNote ?? '').isNotEmpty) _NotePreview(data: data),
        if ((data.debtSummary.activeCount as int) > 0) _DebtSummaryCard(data: data),
        const SizedBox(height: 8),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text('Aktivitas Terbaru',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            Text('${recent.length} transaksi',
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.primary)),
          ],
        ),
        const SizedBox(height: 4),
        if (recent.isEmpty)
          const _EmptyRecent()
        else
          ...recent.map((tx) => TransactionTile(
                tx: tx,
                symbol: data.symbol as String,
                onTap: () => _editTransaction(context, tx),
              )),
      ],
    );
  }

  Future<void> _editTransaction(BuildContext context, Transaction tx) async {
    final appData = context.read<AppDataProvider>();
    final saved = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) => TransactionSheet(
        categories: appData.categories,
        wallets: appData.wallets,
        transaction: tx,
      ),
    );
    if (saved == true) refresh();
  }
}

// ── Hero balance card ──────────────────────────────────────────
class _HeroCard extends StatelessWidget {
  final dynamic data;
  const _HeroCard({required this.data});

  @override
  Widget build(BuildContext context) {
    final symbol = data.symbol as String;
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF043D22), Color(0xFF076836), Color(0xFF0AA956)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(color: const Color(0xFF076836).withValues(alpha: .3), blurRadius: 24, offset: const Offset(0, 8)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('TOTAL SALDO',
              style: TextStyle(
                  fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .6,
                  color: Colors.white54)),
          const SizedBox(height: 4),
          Text(Fmt.money(data.balance as double, symbol: symbol),
              style: const TextStyle(
                  fontSize: 32, fontWeight: FontWeight.w800, color: Colors.white,
                  letterSpacing: -.5, height: 1.1)),
          const SizedBox(height: 4),
          const Text('Akumulasi semua transaksi', style: TextStyle(fontSize: 11, color: Colors.white38)),
          const SizedBox(height: 16),
          const Divider(color: Colors.white12, height: 1),
          const SizedBox(height: 12),
          Text('BULAN INI · ${(data.month as String).toUpperCase()}',
              style: const TextStyle(
                  fontSize: 10, fontWeight: FontWeight.w700, letterSpacing: .4, color: Colors.white38)),
          const SizedBox(height: 10),
          Row(
            children: [
              _Stat(icon: Icons.south_west, color: const Color(0xFF4ADE80), label: 'Pemasukan',
                  value: Fmt.money(data.monthlyIncome as double, symbol: symbol)),
              const SizedBox(width: 10),
              _Stat(icon: Icons.north_east, color: const Color(0xFFF87171), label: 'Pengeluaran',
                  value: Fmt.money(data.monthlyExpense as double, symbol: symbol)),
            ],
          ),
        ],
      ),
    );
  }
}

class _Stat extends StatelessWidget {
  final IconData icon;
  final Color color;
  final String label;
  final String value;
  const _Stat({required this.icon, required this.color, required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: Colors.black.withValues(alpha: .18),
          borderRadius: BorderRadius.circular(14),
        ),
        child: Row(
          children: [
            Container(
              width: 30,
              height: 30,
              decoration: BoxDecoration(color: color.withValues(alpha: .18), borderRadius: BorderRadius.circular(9)),
              child: Icon(icon, size: 16, color: color),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(label, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: Colors.white54)),
                  Text(value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Colors.white)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Wallet strip ───────────────────────────────────────────────
class _WalletStrip extends StatelessWidget {
  final List<Wallet> wallets;
  const _WalletStrip({required this.wallets});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const SizedBox(height: 16),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text('REKENING',
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
            GestureDetector(
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const WalletScreen())),
              child: const Text('Kelola →',
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.primary)),
            ),
          ],
        ),
        const SizedBox(height: 8),
        SizedBox(
          height: 86,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: wallets.length + 1,
            separatorBuilder: (_, _) => const SizedBox(width: 10),
            itemBuilder: (context, i) {
              if (i == wallets.length) {
                return GestureDetector(
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const WalletScreen())),
                  child: Container(
                    width: 110,
                    decoration: BoxDecoration(
                      color: AppColors.card,
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: AppColors.border, width: 1.5),
                    ),
                    child: const Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.add, color: AppColors.primary, size: 22),
                        SizedBox(height: 4),
                        Text('Tambah',
                            style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
                      ],
                    ),
                  ),
                );
              }
              final w = wallets[i];
              return _WalletCard(wallet: w);
            },
          ),
        ),
      ],
    );
  }
}

class _WalletCard extends StatelessWidget {
  final Wallet wallet;
  const _WalletCard({required this.wallet});

  @override
  Widget build(BuildContext context) {
    final dark = Color.lerp(parseColor(wallet.color), Colors.black, .4)!;
    return Container(
      width: 140,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        gradient: LinearGradient(colors: [dark, parseColor(wallet.color)], begin: Alignment.topLeft, end: Alignment.bottomRight),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: .12), blurRadius: 12)],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(wallet.icon, style: const TextStyle(fontSize: 20)),
              Text(wallet.typeLabel,
                  style: const TextStyle(
                      fontSize: 9, fontWeight: FontWeight.w700, color: Colors.white70,
                      backgroundColor: Colors.black26)),
            ],
          ),
          const Spacer(),
          Text(wallet.name,
              maxLines: 1, overflow: TextOverflow.ellipsis,
              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.white70)),
          const SizedBox(height: 2),
          Text(Fmt.money0(wallet.balance),
              maxLines: 1, overflow: TextOverflow.ellipsis,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Colors.white)),
        ],
      ),
    );
  }
}

// ── Daily balance chart ────────────────────────────────────────
class _DailyChart extends StatelessWidget {
  final dynamic data;
  const _DailyChart({required this.data});

  @override
  Widget build(BuildContext context) {
    final points = (data.dailyBalance as List).map((e) {
      return FlSpot((e['d'] as num).toDouble(), (e['b'] as num).toDouble());
    }).toList();

    return Container(
      margin: const EdgeInsets.only(top: 16),
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
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Tren Saldo Bulan Ini',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
              Text(Fmt.shortMonthLabel((data.monthKey as String?) ?? ''),
                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.textMuted)),
            ],
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 88,
            child: LineChart(
              LineChartData(
                gridData: const FlGridData(show: false),
                titlesData: FlTitlesData(
                  leftTitles: AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  topTitles: AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  rightTitles: AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  bottomTitles: AxisTitles(
                    sideTitles: SideTitles(
                      showTitles: true,
                      reservedSize: 18,
                      getTitlesWidget: (v, meta) {
                        if (v == 1 || v == 15 || v == points.length.toDouble()) {
                          return Text(v.toInt().toString(),
                              style: const TextStyle(fontSize: 10, color: AppColors.textMuted));
                        }
                        return const SizedBox.shrink();
                      },
                    ),
                  ),
                ),
                borderData: FlBorderData(show: false),
                minY: points.map((p) => p.y).reduce((a, b) => a < b ? a : b),
                maxY: points.map((p) => p.y).reduce((a, b) => a > b ? a : b),
                lineBarsData: [
                  LineChartBarData(
                    spots: points,
                    isCurved: true,
                    color: AppColors.primaryLight,
                    barWidth: 2.5,
                    dotData: const FlDotData(show: false),
                    belowBarData: BarAreaData(
                      show: true,
                      color: AppColors.primaryLight.withValues(alpha: .12),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Reminders ──────────────────────────────────────────────────
class _ReminderCard extends StatelessWidget {
  final dynamic data;
  const _ReminderCard({required this.data});

  @override
  Widget build(BuildContext context) {
    final bills = data.upcomingBills as List;
    final debts = data.upcomingDebts as List;
    if (bills.isEmpty && debts.isEmpty) return const SizedBox.shrink();

    return Container(
      margin: const EdgeInsets.only(top: 16),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFF59E0B), width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('⏰ Pengingat Jatuh Tempo',
              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Color(0xFFD97706))),
          const SizedBox(height: 8),
          ...bills.map((b) => _ReminderRow(
                icon: '📋',
                title: (b as Bill).name,
                subtitle: 'Tagihan · tgl ${b.dueDay}',
                daysLeft: b.daysLeft ?? 0,
              )),
          ...debts.map((d) => _ReminderRow(
                icon: '${d['type']}' == 'hutang' ? '💸' : '💰',
                title: '${d['person']}',
                subtitle: '${d['type']}' == 'hutang' ? 'Bayar hutang' : 'Tagih piutang',
                daysLeft: (d['daysLeft'] as num?)?.toInt() ?? 0,
              )),
        ],
      ),
    );
  }
}

class _ReminderRow extends StatelessWidget {
  final String icon;
  final String title;
  final String subtitle;
  final int daysLeft;
  const _ReminderRow({required this.icon, required this.title, required this.subtitle, required this.daysLeft});

  @override
  Widget build(BuildContext context) {
    String label;
    Color color;
    if (daysLeft <= 0) {
      label = 'LEWAT';
      color = AppColors.expense;
    } else if (daysLeft == 0) {
      label = 'HARI INI';
      color = AppColors.expense;
    } else if (daysLeft == 1) {
      label = 'BESOK';
      color = const Color(0xFFD97706);
    } else {
      label = '$daysLeft hari';
      color = const Color(0xFFD97706);
    }
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 7),
      decoration: const BoxDecoration(border: Border(top: BorderSide(color: AppColors.border))),
      child: Row(
        children: [
          Text(icon, style: const TextStyle(fontSize: 16)),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                Text(subtitle, style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
              color: color.withValues(alpha: .12),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(label, style: TextStyle(fontSize: 9, fontWeight: FontWeight.w800, color: color)),
          ),
        ],
      ),
    );
  }
}

// ── Budget ─────────────────────────────────────────────────────
class _BudgetCard extends StatelessWidget {
  final dynamic data;
  const _BudgetCard({required this.data});

  @override
  Widget build(BuildContext context) {
    final symbol = data.symbol as String;
    final budget = data.budget as double;
    final expense = data.monthlyExpense as double;
    final pct = data.budgetPct as double;
    final over = pct >= 100;
    final remaining = over ? expense - budget : budget - expense;

    return Container(
      margin: const EdgeInsets.only(top: 16),
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
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('🎯 Budget Bulan Ini',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
              Text('${Fmt.money0(expense)} / ${Fmt.money0(budget)}',
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
            ],
          ),
          const SizedBox(height: 10),
          ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: LinearProgressIndicator(
              value: (pct / 100).clamp(0, 1),
              minHeight: 8,
              backgroundColor: AppColors.border,
              valueColor: AlwaysStoppedAnimation(over ? AppColors.expense : AppColors.primaryLight),
            ),
          ),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(over ? '⚠️ Over budget ${Fmt.money(remaining, symbol: symbol)}' : 'Sisa ${Fmt.money(remaining, symbol: symbol)}',
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: over ? AppColors.expense : AppColors.textSecondary)),
              Text('${pct.round()}%',
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: over ? AppColors.expense : AppColors.primary)),
            ],
          ),
        ],
      ),
    );
  }
}

// ── Top spending categories ──────────────────────────────────────
class _TopCategoriesCard extends StatelessWidget {
  final dynamic data;
  const _TopCategoriesCard({required this.data});

  @override
  Widget build(BuildContext context) {
    final symbol = data.symbol as String;
    final items = (data.topCategories as List).cast<Map<String, dynamic>>();

    return Container(
      margin: const EdgeInsets.only(top: 16),
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
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('🏆 Top Kategori Pengeluaran',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
              GestureDetector(
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const StatsScreen())),
                child: const Text('Detail →',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.primary)),
              ),
            ],
          ),
          const SizedBox(height: 12),
          for (final c in items)
            Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: _TopCategoryRow(item: c, symbol: symbol),
            ),
        ],
      ),
    );
  }
}

class _TopCategoryRow extends StatelessWidget {
  final Map<String, dynamic> item;
  final String symbol;
  const _TopCategoryRow({required this.item, required this.symbol});

  @override
  Widget build(BuildContext context) {
    final color = parseColor(item['color']?.toString() ?? '#6B7280');
    final total = Fmt.toDouble(item['total']);
    final pct = Fmt.toDouble(item['pct']);

    return Row(
      children: [
        Container(
          width: 30,
          height: 30,
          alignment: Alignment.center,
          decoration: BoxDecoration(color: color.withValues(alpha: .12), borderRadius: BorderRadius.circular(9)),
          child: Icon(categoryIcon(item['icon']?.toString() ?? 'other'), size: 15, color: color),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(item['category']?.toString() ?? 'Lainnya',
                      style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                  Text(Fmt.money(total, symbol: symbol),
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                ],
              ),
              const SizedBox(height: 4),
              ClipRRect(
                borderRadius: BorderRadius.circular(3),
                child: LinearProgressIndicator(
                  value: (pct / 100).clamp(0, 1),
                  minHeight: 5,
                  backgroundColor: AppColors.bg,
                  valueColor: AlwaysStoppedAnimation(color),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

// ── Quick actions ──────────────────────────────────────────────
class _QuickActions extends StatelessWidget {
  final dynamic data;
  const _QuickActions({required this.data});

  @override
  Widget build(BuildContext context) {
    final symbol = data.symbol as String;
    return Container(
      margin: const EdgeInsets.only(top: 16),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
      ),
      child: Row(
        children: [
          _QaBtn(icon: Icons.calculate_outlined, label: 'Kalkulator',
              onTap: () => _openCalculator(context)),
          _QaBtn(icon: Icons.receipt_long_outlined, label: 'Tagihan',
              onTap: () => _openBills(context, symbol)),
          _QaBtn(icon: Icons.inventory_2_outlined, label: 'Barang',
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const BarangScreen()))),
          _QaBtn(icon: Icons.edit_note, label: 'Catatan',
              onTap: () => _openNote(context)),
          _QaBtn(icon: Icons.people_outline, label: 'Hutang',
              badge: (data.debtSummary.activeCount as int) > 0 ? '${data.debtSummary.activeCount}' : null,
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const DebtScreen()))),
        ],
      ),
    );
  }
}

class _QaBtn extends StatelessWidget {
  final IconData icon;
  final String label;
  final String? badge;
  final VoidCallback onTap;
  const _QaBtn({required this.icon, required this.label, this.badge, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Column(
          children: [
            Stack(
              clipBehavior: Clip.none,
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: AppColors.primary.withValues(alpha: .08),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(icon, color: AppColors.primary, size: 20),
                ),
                if (badge != null)
                  Positioned(
                    right: -6,
                    top: -6,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                      decoration: const BoxDecoration(color: AppColors.expense, shape: BoxShape.circle),
                      child: Text(badge!,
                          style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w800, color: Colors.white)),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 6),
            Text(label, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.textSecondary)),
          ],
        ),
      ),
    );
  }
}

// ── Savings ────────────────────────────────────────────────────
class _SavingsCard extends StatelessWidget {
  final dynamic data;
  const _SavingsCard({required this.data});

  @override
  Widget build(BuildContext context) {
    final symbol = data.symbol as String;
    final target = data.savingsTarget as double;
    final saved = data.savingsSaved as double;
    final pct = (data.savingsPct as double) / 100;
    final reached = pct >= 1;

    return Container(
      margin: const EdgeInsets.only(top: 16),
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
          Row(
            children: [
              const Text('🎯', style: TextStyle(fontSize: 18)),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(data.savingsName as String,
                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                    Text('${Fmt.money0(saved)} / ${Fmt.money0(target)}',
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppColors.primary)),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: LinearProgressIndicator(
              value: pct.clamp(0, 1),
              minHeight: 8,
              backgroundColor: AppColors.border,
              valueColor: const AlwaysStoppedAnimation(AppColors.primaryLight),
            ),
          ),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(reached ? '🎉 Target tercapai!' : 'Sisa ${Fmt.money(target - saved, symbol: symbol)}',
                  style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
              Text('${(pct * 100).round()}%',
                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.primary)),
            ],
          ),
        ],
      ),
    );
  }
}

// ── Note preview ───────────────────────────────────────────────
class _NotePreview extends StatelessWidget {
  final dynamic data;
  const _NotePreview({required this.data});

  @override
  Widget build(BuildContext context) {
    final note = data.monthNote as String;
    return Container(
      margin: const EdgeInsets.only(top: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
      ),
      child: InkWell(
        onTap: () => _openNote(context),
        borderRadius: BorderRadius.circular(10),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('📝 Catatan ${data.month as String}',
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
            const SizedBox(height: 6),
            Text(note.length > 120 ? '${note.substring(0, 120)}…' : note,
                style: const TextStyle(fontSize: 13, height: 1.5, color: AppColors.textPrimary)),
          ],
        ),
      ),
    );
  }
}

// ── Debt summary ───────────────────────────────────────────────
class _DebtSummaryCard extends StatelessWidget {
  final dynamic data;
  const _DebtSummaryCard({required this.data});

  @override
  Widget build(BuildContext context) {
    final symbol = data.symbol as String;
    final sum = data.debtSummary as dynamic;
    return Container(
      margin: const EdgeInsets.only(top: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
      ),
      child: InkWell(
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const DebtScreen())),
        borderRadius: BorderRadius.circular(10),
        child: Row(
          children: [
            Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: AppColors.primary.withValues(alpha: .08),
                borderRadius: BorderRadius.circular(14),
              ),
              child: const Icon(Icons.people_outline, color: AppColors.primary),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Hutang & Piutang',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      Text('Hutang ${Fmt.money(sum.totalHutang as double, symbol: symbol)}',
                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppColors.expense)),
                      const SizedBox(width: 12),
                      Text('Piutang ${Fmt.money(sum.totalPiutang as double, symbol: symbol)}',
                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppColors.income)),
                    ],
                  ),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: AppColors.expense.withValues(alpha: .1),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text('${sum.activeCount} aktif',
                  style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.expense)),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Empty recent ───────────────────────────────────────────────
class _EmptyRecent extends StatelessWidget {
  const _EmptyRecent();

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(top: 12),
      padding: const EdgeInsets.symmetric(vertical: 40),
      child: const Column(
        children: [
          Icon(Icons.receipt_long, size: 44, color: AppColors.textMuted),
          SizedBox(height: 10),
          Text('Belum ada transaksi',
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
          SizedBox(height: 4),
          Text('Tekan tombol + untuk mencatat transaksi pertama.',
              style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
        ],
      ),
    );
  }
}

// ── Error view ─────────────────────────────────────────────────
class _ErrorView extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;
  const _ErrorView({required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.wifi_off, size: 44, color: AppColors.textMuted),
            const SizedBox(height: 12),
            Text(message, textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 14, color: AppColors.textSecondary)),
            const SizedBox(height: 16),
            FilledButton(onPressed: onRetry, child: const Text('Coba Lagi')),
          ],
        ),
      ),
    );
  }
}

// ── Avatar ─────────────────────────────────────────────────────
class _Avatar extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;
    if (user == null) return const SizedBox.shrink();
    return Container(
      width: 36,
      height: 36,
      decoration: BoxDecoration(
        color: parseColor(user.color),
        shape: BoxShape.circle,
      ),
      child: user.avatarImage != null
          ? ClipOval(
              child: Image.network(
                '${ApiConfig.baseUrl}${user.avatarImage}',
                fit: BoxFit.cover,
                width: 36,
                height: 36,
                errorBuilder: (_, _, _) => Center(
                  child: Text(user.initials,
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 13)),
                ),
              ),
            )
          : Center(
              child: Text(user.initials,
                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 13)),
            ),
    );
  }
}

// ── Quick action helpers ───────────────────────────────────────
void _openCalculator(BuildContext context) {
  showCalculatorSheet(context);
}

void _openBills(BuildContext context, String symbol) {
  Navigator.push(context, MaterialPageRoute(builder: (_) => BillsScreen(symbol: symbol)));
}

void _openNote(BuildContext context) {
  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    backgroundColor: AppColors.card,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
    ),
    builder: (_) => const NoteSheet(),
  );
}
