import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../config/api_config.dart';
import '../models/bill.dart';
import '../models/transaction.dart';
import '../models/tv_channel.dart';
import '../models/wallet.dart';
import '../providers/app_data_provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../services/belanja_store.dart';
import '../services/widget_helper.dart';
import '../theme.dart';
import '../utils/format.dart';
import '../widgets/calculator_sheet.dart';
import '../widgets/category_icon.dart';
import '../widgets/nearby_services_card.dart';
import '../widgets/transaction_tile.dart';
import 'belanja/belanja_screen.dart';
import 'bills_screen.dart';
import 'debt_screen.dart';
import 'note_sheet.dart';
import 'todo/todo_list_screen.dart';
import '../models/todo_item.dart';
import 'recurring/recurring_screen.dart';
import 'pos/pos_cashier_screen.dart';
import 'pos/pos_products_screen.dart';
import 'pos/pos_reports_screen.dart';
import 'scan/notification_detector_screen.dart';
import 'scan/ocr_receipt_screen.dart';
import 'search/universal_search_screen.dart';
import 'notifications/notifications_screen.dart';
import 'stats_screen.dart';
import 'transaction_sheet.dart';
import 'vehicle/vehicle_screen.dart';
import 'wallet_screen.dart';
import 'zakat_pajak/zakat_pajak_screen.dart';
import 'emergency/emergency_screen.dart';
import '../services/offline_cache_service.dart';
import '../services/sync_service.dart';
import '../widgets/sync_status_banner.dart';
import '../widgets/tv_streaming_card.dart';
import '../widgets/my_home_card.dart';
import 'barang/barang_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => DashboardScreenState();
}

class DashboardScreenState extends State<DashboardScreen> {
  Map<String, dynamic>? _data;
  bool _loading = true;
  String? _error;
  int _recentPage = 1;
  static const int _recentPerPage = 5;
  String _workspaceMode = 'personal'; // 'personal' or 'business'

  @override
  void initState() {
    super.initState();
    _loadFromCacheFirst();
    _load();

    // Listen to background sync completions
    SyncService.instance.onSyncCompleted = () {
      if (mounted) {
        _load();
      }
    };
  }

  Future<void> _loadFromCacheFirst() async {
    final cached = await OfflineCacheService.instance.getDashboard();
    if (cached != null && mounted && _data == null) {
      setState(() {
        _data = {
          'dashboard': cached,
          'recent': cached.recent
              .map((e) => Transaction.fromJson(e as Map<String, dynamic>))
              .toList(),
        };
        _loading = false;
      });
    }
  }

  Future<void> refresh() async {
    await _load();
  }

  Future<void> _load() async {
    if (_data == null) {
      setState(() {
        _loading = true;
        _error = null;
      });
    }

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
        _error = null;
      });
      context.read<AppDataProvider>().ensureLoaded(force: true);
      WidgetHelper.updateDashboardWidget(data);
    } on ApiException catch (e) {
      if (!mounted) return;
      if (e.status == 401) {
        context.read<AuthProvider>().logout();
        return;
      }
      if (_data == null) {
        setState(() {
          _error = e.message;
          _loading = false;
        });
      }
    } catch (e) {
      if (!mounted) return;
      // If offline or network error, fallback to cache if available
      final cached = await OfflineCacheService.instance.getDashboard();
      if (cached != null) {
        setState(() {
          _data = {
            'dashboard': cached,
            'recent': cached.recent
                .map((e) => Transaction.fromJson(e as Map<String, dynamic>))
                .toList(),
          };
          _loading = false;
          _error = null;
        });
      } else if (_data == null) {
        setState(() {
          _error = 'Tidak dapat terhubung ke server.\n(${e.runtimeType}: $e)';
          _loading = false;
        });
      } else {
        setState(() {
          _loading = false;
          _error = null;
        });
      }
    }
  }

  Future<void> _handleOcrScan() async {
    final sym = (_data?['dashboard'] as dynamic)?.symbol?.toString() ?? 'Rp';
    final nav = Navigator.of(context);
    final res = await nav.push<OcrReceiptResult>(
      MaterialPageRoute(builder: (_) => OcrReceiptScreen(symbol: sym)),
    );
    if (res == null || !mounted) return;
    final appData = context.read<AppDataProvider>();
    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => TransactionSheet(
        categories: appData.categories,
        wallets: appData.wallets,
        initialAmount: res.amount,
        initialNote: res.note,
        initialDate: res.date,
      ),
    );
    if (!mounted) return;
    refresh();
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    return Scaffold(
      appBar: AppBar(
        titleSpacing: 16,
        toolbarHeight: 68,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              Fmt.dateFull(DateTime.now().toIso8601String().substring(0, 10)),
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: AppColors.textMuted,
                letterSpacing: -0.1,
              ),
            ),
            const SizedBox(height: 2),
            const Text(
              'Dashboard',
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.w900,
                color: AppColors.textPrimary,
                letterSpacing: -0.5,
              ),
            ),
          ],
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 18, horizontal: 2),
            child: InkWell(
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const EmergencyScreen()),
              ),
              borderRadius: BorderRadius.circular(8),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFFDC2626),
                  borderRadius: BorderRadius.circular(8),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFFDC2626).withValues(alpha: 0.35),
                      blurRadius: 4,
                      offset: const Offset(0, 1.5),
                    ),
                  ],
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.emergency_rounded, size: 14, color: Colors.white),
                    SizedBox(width: 3),
                    Text(
                      'SOS',
                      style: TextStyle(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w900,
                        color: Colors.white,
                        letterSpacing: 0.3,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          IconButton(
            icon: const Icon(Icons.search_rounded, size: 22),
            tooltip: 'Cari Data (Universal)',
            onPressed: () {
              final sym = (_data?['dashboard'] as dynamic)?.symbol?.toString() ?? 'Rp';
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => UniversalSearchScreen(symbol: sym)),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.document_scanner_rounded, size: 22),
            tooltip: 'Smart Scan Struk (OCR)',
            onPressed: _handleOcrScan,
          ),
          if (_data?['dashboard'] != null)
            _NotificationBell(
              data: _data!['dashboard'],
              onRefresh: refresh,
            ),
          PopupMenuButton<String>(
            onSelected: (v) {
              if (v == 'logout') auth.logout();
            },
            itemBuilder: (_) => [
              const PopupMenuItem(value: 'logout', child: Text('Keluar')),
            ],
            child: Padding(
              padding: const EdgeInsets.only(right: 16, left: 4),
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
    final notifs = (data.notifications as List?) ?? [];
    final urgentCount = notifs.where((n) => (n['days_left'] as num?)?.toInt() != null && (n['days_left'] as num).toInt() <= 1).length;

    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
      children: [
        const SyncStatusBanner(),
        // Mode Switcher Pill
        _buildWorkspaceSwitcher(),
        const SizedBox(height: 10),

        if (_workspaceMode == 'business') ...[
          _BusinessWorkspace(data: data, onRefresh: refresh),
        ] else ...[
          const _EmergencyQuickBanner(),
          if (urgentCount > 0)
            _UrgentAlertBanner(
              count: urgentCount,
              onTap: () => _showNotificationsSheet(context, data, refresh),
            ),
          _HeroCard(
            data: data,
            onScanOcr: _handleOcrScan,
            onOpenStats: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const StatsScreen()),
              );
            },
          ),
          _ThreeColumnStatsCard(data: data, recentCount: recent.length),
          const _BelanjaHomeCard(),
          const _TodoHomeCard(),
          if ((data.wallets as List).isNotEmpty) _WalletStrip(wallets: data.wallets as List<Wallet>),
          if ((data.dailyBalance as List).length > 1)
            _DailyChart(data: data),
          _ReminderCard(data: data, onRefresh: _load),
          if (data.budget > 0) _BudgetCard(data: data),
          if ((data.topCategories as List).isNotEmpty) _TopCategoriesCard(data: data),
          _QuickActions(data: data),
          MyHomeDashboardCard(summary: (data.myHomeSummary as Map<String, dynamic>? ?? {})),
          const NearbyServicesCard(),
          TvStreamingCard(initialChannels: (data.tvChannels as List<TvChannel>? ?? [])),
          if (data.savingsTarget > 0) _SavingsCard(data: data),
          if ((data.monthNote ?? '').isNotEmpty) _NotePreview(data: data),
          if ((data.debtSummary.activeCount as int) > 0) _DebtSummaryCard(data: data),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Aktivitas Terbaru',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
              Text('${recent.length} total',
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.primary)),
            ],
          ),
          const SizedBox(height: 6),
          if (recent.isEmpty)
            const _EmptyRecent()
          else ...[
            ...() {
              final totalRecent = recent.length;
              final totalPages = (totalRecent / _recentPerPage).ceil().clamp(1, 999);
              final curPage = _recentPage.clamp(1, totalPages);
              final startIndex = (curPage - 1) * _recentPerPage;
              final pageItems = recent.skip(startIndex).take(_recentPerPage).toList();

              return [
                ...pageItems.map((tx) => TransactionTile(
                      tx: tx,
                      symbol: data.symbol as String,
                      onTap: () => _editTransaction(context, tx),
                    )),
                if (totalPages > 1)
                  Container(
                    margin: const EdgeInsets.only(top: 8),
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                    decoration: BoxDecoration(
                      color: AppColors.card,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppColors.borderLight),
                      boxShadow: AppColors.cardShadow,
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        OutlinedButton.icon(
                          onPressed: curPage > 1 ? () => setState(() => _recentPage = curPage - 1) : null,
                          icon: const Icon(Icons.chevron_left_rounded, size: 18),
                          label: const Text('Sebelumnya', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700)),
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                            visualDensity: VisualDensity.compact,
                            side: BorderSide(color: curPage > 1 ? AppColors.primary : AppColors.border),
                            foregroundColor: curPage > 1 ? AppColors.primary : AppColors.textMuted,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppColors.bg,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            '$curPage / $totalPages',
                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
                          ),
                        ),
                        OutlinedButton.icon(
                          onPressed: curPage < totalPages ? () => setState(() => _recentPage = curPage + 1) : null,
                          label: const Text('Selanjutnya', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700)),
                          icon: const Icon(Icons.chevron_right_rounded, size: 18),
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                            visualDensity: VisualDensity.compact,
                            side: BorderSide(color: curPage < totalPages ? AppColors.primary : AppColors.border),
                            foregroundColor: curPage < totalPages ? AppColors.primary : AppColors.textMuted,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                        ),
                      ],
                    ),
                  ),
              ];
            }(),
          ],
        ],
      ],
    );
  }

  Widget _buildWorkspaceSwitcher() {
    final isPersonal = _workspaceMode == 'personal';
    return Container(
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
      ),
      child: Row(
        children: [
          Expanded(
            child: InkWell(
              onTap: () => setState(() => _workspaceMode = 'personal'),
              borderRadius: BorderRadius.circular(12),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.symmetric(vertical: 8),
                decoration: BoxDecoration(
                  color: isPersonal ? AppColors.primary : Colors.transparent,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Text('👤', style: TextStyle(fontSize: 13)),
                    const SizedBox(width: 6),
                    Text(
                      'Mode Pribadi',
                      style: TextStyle(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w800,
                        color: isPersonal ? Colors.white : AppColors.textSecondary,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          const SizedBox(width: 4),
          Expanded(
            child: InkWell(
              onTap: () => setState(() => _workspaceMode = 'business'),
              borderRadius: BorderRadius.circular(12),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.symmetric(vertical: 8),
                decoration: BoxDecoration(
                  color: !isPersonal ? const Color(0xFF312E81) : Colors.transparent,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Text('☕', style: TextStyle(fontSize: 13)),
                    const SizedBox(width: 6),
                    Text(
                      'Mode Usaha POS',
                      style: TextStyle(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w800,
                        color: !isPersonal ? Colors.white : AppColors.textSecondary,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _editTransaction(BuildContext context, Transaction tx) async {
    final appData = context.read<AppDataProvider>();
    final saved = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
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

// ── Hero balance card (Card-in-Pocket style) ───────────────────
class _HeroCard extends StatefulWidget {
  final dynamic data;
  final VoidCallback onScanOcr;
  final VoidCallback onOpenStats;

  const _HeroCard({
    required this.data,
    required this.onScanOcr,
    required this.onOpenStats,
  });

  @override
  State<_HeroCard> createState() => _HeroCardState();
}

class _AppleDeckCardData {
  final String id;
  final String title;
  final String badge;
  final double balance;
  final IconData icon;
  final List<Color> gradient;
  final bool isTotal;

  _AppleDeckCardData({
    required this.id,
    required this.title,
    required this.badge,
    required this.balance,
    required this.icon,
    required this.gradient,
    this.isTotal = false,
  });
}

class _HeroCardState extends State<_HeroCard> {
  bool _hideBalance = false;
  int _activeCardIndex = 0;

  List<Color> _getGradientForType(String type) {
    switch (type.toLowerCase()) {
      case 'bank':
        return const [Color(0xFF0F172A), Color(0xFF1E3A8A), Color(0xFF2563EB)];
      case 'cash':
        return const [Color(0xFF064E3B), Color(0xFF047857), Color(0xFF10B981)];
      case 'ewallet':
      case 'e-wallet':
        return const [Color(0xFF312E81), Color(0xFF4338CA), Color(0xFF6366F1)];
      case 'credit_card':
        return const [Color(0xFF701A75), Color(0xFF86198F), Color(0xFFC026D3)];
      default:
        return const [Color(0xFF1E3A8A), Color(0xFF1D4ED8), Color(0xFF2563EB)];
    }
  }

  IconData _getIconForType(String type) {
    switch (type.toLowerCase()) {
      case 'bank':
        return Icons.account_balance_rounded;
      case 'cash':
        return Icons.payments_rounded;
      case 'ewallet':
      case 'e-wallet':
        return Icons.account_balance_wallet_rounded;
      case 'credit_card':
        return Icons.credit_card_rounded;
      default:
        return Icons.wallet_rounded;
    }
  }

  @override
  Widget build(BuildContext context) {
    final data = widget.data;
    final symbol = '${data.symbol ?? "Rp"}';
    final wallets = (data.wallets as List?) ?? [];

    final allWallets = wallets.whereType<Wallet>().toList();
    // Only show wallets with balance > 0 in the stacked deck
    final activeWallets = allWallets.where((w) => w.balance > 0).toList();

    final monthStr = '${data.month ?? ""}'.toUpperCase();
    final monthlyIncome = Fmt.toDouble(data.monthlyIncome);

    // Build Stacked Cards List
    final List<_AppleDeckCardData> cards = [
      _AppleDeckCardData(
        id: 'total',
        title: 'TOTAL BALANCE',
        badge: monthStr.isNotEmpty ? monthStr : 'TOTAL',
        balance: Fmt.toDouble(data.balance),
        icon: Icons.diamond_rounded,
        gradient: const [Color(0xFF1E3A8A), Color(0xFF1D4ED8), Color(0xFF2563EB)],
        isTotal: true,
      ),
      ...activeWallets.map((w) => _AppleDeckCardData(
        id: '${w.id}',
        title: 'SALDO ${w.name.toUpperCase()}',
        badge: w.type.toUpperCase(),
        balance: w.balance,
        icon: _getIconForType(w.type),
        gradient: _getGradientForType(w.type),
        isTotal: false,
      )),
    ];

    final int safeActiveIdx = _activeCardIndex.clamp(0, cards.length - 1);
    final double stackOffset = (cards.length - 1) * 36.0;
    final double totalDeckHeight = 205.0 + stackOffset;

    // Render background cards first, and active card LAST so it sits on top
    final List<int> renderOrder = [];
    for (int i = 0; i < cards.length; i++) {
      if (i != safeActiveIdx) renderOrder.add(i);
    }
    renderOrder.add(safeActiveIdx);

    return Container(
      margin: const EdgeInsets.only(top: 4, bottom: 4),
      height: totalDeckHeight,
      child: Stack(
        clipBehavior: Clip.none,
        children: renderOrder.map((idx) {
          final card = cards[idx];
          final bool isFront = (idx == safeActiveIdx);

          int stackOrder = 0;
          if (!isFront) {
            for (int i = 0; i < cards.length; i++) {
              if (i == safeActiveIdx) continue;
              if (i == idx) break;
              stackOrder++;
            }
          }

          final double targetTop = isFront ? stackOffset : (stackOrder * 36.0);
          final double targetScale = isFront ? 1.0 : (0.94 + (stackOrder * 0.018)).clamp(0.92, 0.98);

          return AnimatedPositioned(
            duration: const Duration(milliseconds: 380),
            curve: Curves.easeOutCubic,
            top: targetTop,
            left: 0,
            right: 0,
            child: AnimatedScale(
              duration: const Duration(milliseconds: 380),
              curve: Curves.easeOutCubic,
              scale: targetScale,
              child: GestureDetector(
                onTap: () {
                  if (!isFront) {
                    setState(() => _activeCardIndex = idx);
                  }
                },
                child: Container(
                  padding: const EdgeInsets.fromLTRB(20, 16, 20, 18),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: card.gradient,
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(24),
                    border: Border.all(
                      color: Colors.white.withValues(alpha: isFront ? 0.25 : 0.16),
                      width: 1,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: isFront ? 0.35 : 0.22),
                        blurRadius: isFront ? 28 : 12,
                        offset: Offset(0, isFront ? 14 : -3),
                      ),
                      if (isFront)
                        BoxShadow(
                          color: card.gradient.last.withValues(alpha: 0.35),
                          blurRadius: 16,
                          offset: const Offset(0, 4),
                        ),
                    ],
                  ),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // ── Header (Always visible in stack) ──────────
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(card.icon, size: 16, color: Colors.white70),
                              const SizedBox(width: 8),
                              Text(
                                card.title,
                                style: const TextStyle(
                                  fontSize: 11.5,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 0.6,
                                  color: Colors.white,
                                ),
                              ),
                            ],
                          ),
                          Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              if (!isFront) ...[
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2.5),
                                  decoration: BoxDecoration(
                                    color: Colors.black.withValues(alpha: 0.25),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Text(
                                    _hideBalance ? 'Rp ••••' : Fmt.money(card.balance, symbol: symbol),
                                    style: const TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w800,
                                      color: Colors.white,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 6),
                              ],
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: Colors.white.withValues(alpha: 0.2),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  card.badge,
                                  style: const TextStyle(
                                    fontSize: 9.5,
                                    fontWeight: FontWeight.w900,
                                    color: Colors.white,
                                    letterSpacing: 0.4,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),

                      // ── Expanded Body (Visible only on front card) ─
                      if (isFront) ...[
                        const SizedBox(height: 12),
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.baseline,
                          textBaseline: TextBaseline.alphabetic,
                          children: [
                            Expanded(
                              child: FittedBox(
                                fit: BoxFit.scaleDown,
                                alignment: Alignment.centerLeft,
                                child: Text(
                                  _hideBalance ? 'Rp •••••••••' : Fmt.money(card.balance, symbol: symbol),
                                  style: const TextStyle(
                                    fontSize: 32,
                                    fontWeight: FontWeight.w900,
                                    color: Colors.white,
                                    letterSpacing: -1,
                                    height: 1.1,
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: const Color(0xFF10B981).withValues(alpha: 0.2),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(Icons.trending_up_rounded, size: 14, color: Color(0xFF6EE7B7)),
                                  const SizedBox(width: 4),
                                  Text(
                                    card.isTotal ? '+${Fmt.money0(monthlyIncome)}' : card.badge,
                                    style: const TextStyle(
                                      fontSize: 10.5,
                                      fontWeight: FontWeight.w800,
                                      color: Color(0xFF6EE7B7),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 18),
                        Row(
                          children: [
                            Expanded(
                              child: Material(
                                color: Colors.transparent,
                                child: InkWell(
                                  onTap: widget.onOpenStats,
                                  borderRadius: BorderRadius.circular(16),
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                                    decoration: BoxDecoration(
                                      color: Colors.white.withValues(alpha: 0.16),
                                      borderRadius: BorderRadius.circular(16),
                                      border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
                                    ),
                                    child: const Row(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Icon(Icons.bar_chart_rounded, size: 18, color: Colors.white),
                                        SizedBox(width: 8),
                                        Text(
                                          'View Reports',
                                          style: TextStyle(
                                            fontSize: 12.5,
                                            fontWeight: FontWeight.w800,
                                            color: Colors.white,
                                            letterSpacing: -0.1,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Material(
                              color: Colors.transparent,
                              child: InkWell(
                                onTap: widget.onScanOcr,
                                borderRadius: BorderRadius.circular(14),
                                child: Container(
                                  width: 42,
                                  height: 42,
                                  decoration: BoxDecoration(
                                    color: Colors.white.withValues(alpha: 0.16),
                                    borderRadius: BorderRadius.circular(14),
                                    border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
                                  ),
                                  child: const Icon(Icons.document_scanner_rounded, size: 18, color: Colors.white),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Material(
                              color: Colors.transparent,
                              child: InkWell(
                                onTap: () => setState(() => _hideBalance = !_hideBalance),
                                borderRadius: BorderRadius.circular(14),
                                child: Container(
                                  width: 42,
                                  height: 42,
                                  decoration: BoxDecoration(
                                    color: Colors.white.withValues(alpha: 0.16),
                                    borderRadius: BorderRadius.circular(14),
                                    border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
                                  ),
                                  child: Icon(
                                    _hideBalance ? Icons.visibility_off_rounded : Icons.visibility_rounded,
                                    size: 18,
                                    color: Colors.white,
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }
}

// ── 3-Column Floating Summary Card ─────────────────────────────
class _ThreeColumnStatsCard extends StatelessWidget {
  final dynamic data;
  final int recentCount;

  const _ThreeColumnStatsCard({
    required this.data,
    required this.recentCount,
  });

  @override
  Widget build(BuildContext context) {
    final income = Fmt.toDouble(data.monthlyIncome);
    final expense = Fmt.toDouble(data.monthlyExpense);
    final budget = Fmt.toDouble(data.budget);
    final remainingBudget = budget > 0 ? (budget - expense) : 0.0;
    final wallets = (data.wallets as List?) ?? [];

    return Container(
      margin: const EdgeInsets.only(top: 10, bottom: 8),
      padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 8),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: AppColors.border.withValues(alpha: 0.8)),
        boxShadow: AppColors.cardShadow,
      ),
      child: Row(
        children: [
          // Column 1: Sales / Pemasukan
          Expanded(
            child: _buildCol(
              title: Fmt.money0(income),
              label: 'Pemasukan',
              subLabel: 'Bulan ini',
              color: AppColors.income,
            ),
          ),
          Container(width: 1, height: 38, color: AppColors.borderLight),

          // Column 2: Items / Pengeluaran
          Expanded(
            child: _buildCol(
              title: Fmt.money0(expense),
              label: 'Pengeluaran',
              subLabel: '$recentCount transaksi',
              color: AppColors.textPrimary,
            ),
          ),
          Container(width: 1, height: 38, color: AppColors.borderLight),

          // Column 3: Sisa Budget / Status
          Expanded(
            child: _buildCol(
              title: budget > 0 ? Fmt.money0(remainingBudget) : '${wallets.length} Dompet',
              label: budget > 0 ? 'Sisa Budget' : 'Akun Dompet',
              subLabel: budget > 0 ? 'Limit bulanan' : 'Aktif',
              color: const Color(0xFF2563EB),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCol({
    required String title,
    required String label,
    required String subLabel,
    required Color color,
  }) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        FittedBox(
          fit: BoxFit.scaleDown,
          child: Text(
            title,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w900,
              color: color,
              letterSpacing: -0.3,
            ),
          ),
        ),
        const SizedBox(height: 3),
        Text(
          label,
          style: const TextStyle(
            fontSize: 11.5,
            fontWeight: FontWeight.w700,
            color: AppColors.textSecondary,
          ),
        ),
        const SizedBox(height: 1),
        Text(
          subLabel,
          style: const TextStyle(
            fontSize: 10,
            fontWeight: FontWeight.w500,
            color: AppColors.textMuted,
          ),
        ),
      ],
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
  final VoidCallback onRefresh;
  const _ReminderCard({required this.data, required this.onRefresh});

  Future<void> _payBill(BuildContext context, Bill b) async {
    final appData = context.read<AppDataProvider>();
    await appData.ensureLoaded();
    if (!context.mounted) return;

    final dummyTx = Transaction(
      id: 0,
      type: 'expense',
      amount: b.amount,
      note: 'Bayar Tagihan: ${b.name}',
      date: DateTime.now().toIso8601String().substring(0, 10),
    );

    final res = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => TransactionSheet(
        categories: appData.categories,
        wallets: appData.wallets,
        transaction: dummyTx,
      ),
    );

    if (res == true) {
      onRefresh();
    }
  }

  @override
  Widget build(BuildContext context) {
    final bills = data.upcomingBills as List;
    final debts = data.upcomingDebts as List;
    final taxes = (data.upcomingTaxes as List?) ?? [];
    final recurring = (data.upcomingRecurring as List?) ?? [];

    if (bills.isEmpty && debts.isEmpty && taxes.isEmpty && recurring.isEmpty) return const SizedBox.shrink();

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
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('⏰ Pengingat Jatuh Tempo',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Color(0xFFD97706))),
              GestureDetector(
                onTap: () => _showNotificationsSheet(context, data, onRefresh),
                child: const Text('Semua →',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Color(0xFFD97706))),
              ),
            ],
          ),
          const SizedBox(height: 8),
          ...bills.map((b) {
            final bill = b as Bill;
            return _ReminderRow(
              icon: '📋',
              title: bill.name,
              subtitle: 'Tagihan · tgl ${bill.dueDay}',
              daysLeft: bill.daysLeft ?? 0,
              onPay: () => _payBill(context, bill),
            );
          }),
          ...debts.map((d) => _ReminderRow(
                icon: '${d['type']}' == 'hutang' ? '💸' : '💰',
                title: '${d['person']}',
                subtitle: '${d['type']}' == 'hutang' ? 'Bayar hutang' : 'Tagih piutang',
                daysLeft: (d['daysLeft'] as num?)?.toInt() ?? 0,
                onPay: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const DebtScreen())).then((_) => onRefresh()),
              )),
          ...taxes.map((t) => _ReminderRow(
                icon: '🚗',
                title: '${t['vehicle_name']}',
                subtitle: '${t['type']}',
                daysLeft: (t['days_left'] as num?)?.toInt() ?? 0,
                onPay: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const VehicleScreen())).then((_) => onRefresh()),
              )),
          ...recurring.map((r) => _ReminderRow(
                icon: '🔁',
                title: '${r['category_name'] ?? 'Transaksi Berulang'}',
                subtitle: r['note']?.toString() ?? 'Transaksi Rutin',
                daysLeft: (r['daysLeft'] as num?)?.toInt() ?? 0,
                onPay: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const RecurringScreen())).then((_) => onRefresh()),
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
  final VoidCallback onPay;
  const _ReminderRow({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.daysLeft,
    required this.onPay,
  });

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
    return InkWell(
      onTap: onPay,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 7, horizontal: 4),
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
            const SizedBox(width: 6),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: AppColors.primary.withValues(alpha: .1),
                borderRadius: BorderRadius.circular(6),
              ),
              child: const Text('Bayar', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.primary)),
            ),
          ],
        ),
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

// ── Emergency Quick Banner ─────────────────────────────────────
class _EmergencyQuickBanner extends StatelessWidget {
  const _EmergencyQuickBanner();

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: const Color(0xFFFEF2F2),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFFCA5A5)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFFDC2626).withValues(alpha: 0.08),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const EmergencyScreen()),
          ),
          borderRadius: BorderRadius.circular(16),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            child: Row(
              children: [
                Container(
                  width: 34,
                  height: 34,
                  decoration: BoxDecoration(
                    color: const Color(0xFFDC2626),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.emergency_rounded, color: Colors.white, size: 20),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Text(
                            'Layanan Darurat & Derek Tol',
                            style: TextStyle(
                              fontSize: 12.5,
                              fontWeight: FontWeight.w800,
                              color: Color(0xFF991B1B),
                              letterSpacing: -0.2,
                            ),
                          ),
                          SizedBox(width: 6),
                          Container(
                            padding: EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                            decoration: BoxDecoration(
                              color: Color(0xFFDC2626),
                              borderRadius: BorderRadius.all(Radius.circular(4)),
                            ),
                            child: Text(
                              '24 JAM',
                              style: TextStyle(
                                fontSize: 8.5,
                                fontWeight: FontWeight.w900,
                                color: Colors.white,
                                letterSpacing: 0.4,
                              ),
                            ),
                          ),
                        ],
                      ),
                      SizedBox(height: 2),
                      Text(
                        '112 · 14080 (Derek) · Damkar · Medis · Polisi',
                        style: TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFFB91C1C),
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFFDC2626).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'Buka',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFFDC2626),
                        ),
                      ),
                      SizedBox(width: 2),
                      Icon(Icons.arrow_forward_rounded, size: 12, color: Color(0xFFDC2626)),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// ── Quick actions ──────────────────────────────────────────────
class _QuickActions extends StatelessWidget {
  final dynamic data;
  const _QuickActions({required this.data});

  @override
  Widget build(BuildContext context) {
    final symbol = '${data.symbol ?? "Rp"}';
    return Container(
      margin: const EdgeInsets.only(top: 16),
      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 14),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
      ),
      child: Row(
        children: [
          _QaBtn(
            icon: Icons.auto_awesome_rounded,
            label: 'Deteksi SMS',
            onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const NotificationDetectorScreen())),
          ),
          _QaBtn(
            icon: Icons.volunteer_activism_rounded,
            label: 'Zakat & Pajak',
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => ZakatPajakScreen(
                  initialBalance: Fmt.toDouble(data.balance),
                  initialMonthlyIncome: Fmt.toDouble(data.monthlyIncome),
                  symbol: symbol,
                ),
              ),
            ),
          ),
          _QaBtn(
            icon: Icons.receipt_long_outlined,
            label: 'Tagihan',
            onTap: () => _openBills(context, symbol),
          ),
          _QaBtn(
            icon: Icons.calculate_outlined,
            label: 'Kalkulator',
            onTap: () => _openCalculator(context),
          ),
          _QaBtn(
            icon: Icons.home_work_rounded,
            label: 'My Home',
            onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const BarangScreen())),
          ),
          _QaBtn(
            icon: Icons.account_balance_wallet_outlined,
            label: 'Hutang',
            badge: (data.debtSummary.activeCount as int) > 0 ? '${data.debtSummary.activeCount}' : null,
            onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const DebtScreen())),
          ),
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
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(12),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 4, horizontal: 2),
            child: Column(
              children: [
                Stack(
                  clipBehavior: Clip.none,
                  children: [
                    Container(
                      width: 38,
                      height: 38,
                      decoration: BoxDecoration(
                        color: AppColors.primarySubtle,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(icon, color: AppColors.primary, size: 20),
                    ),
                    if (badge != null)
                      Positioned(
                        right: -4,
                        top: -4,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                          decoration: const BoxDecoration(color: AppColors.expense, shape: BoxShape.circle),
                          child: Text(
                            badge!,
                            style: const TextStyle(fontSize: 8.5, fontWeight: FontWeight.w800, color: Colors.white),
                          ),
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 6),
                FittedBox(
                  fit: BoxFit.scaleDown,
                  child: Text(
                    label,
                    maxLines: 1,
                    style: const TextStyle(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w600,
                      color: AppColors.textSecondary,
                    ),
                  ),
                ),
              ],
            ),
          ),
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

// ── Glowing Profile Avatar ─────────────────────────────────────
class _Avatar extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;
    if (user == null) return const SizedBox.shrink();

    return Container(
      width: 38,
      height: 38,
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF2563EB), Color(0xFF1D4ED8)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        shape: BoxShape.circle,
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF2563EB).withValues(alpha: 0.45),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: user.avatarImage != null
          ? ClipOval(
              child: Image.network(
                '${ApiConfig.baseUrl}${user.avatarImage}',
                fit: BoxFit.cover,
                width: 38,
                height: 38,
                errorBuilder: (_, _, _) => Center(
                  child: Text(user.initials,
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 13)),
                ),
              ),
            )
          : Center(
              child: Text(user.initials,
                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 13)),
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

// ── Belanja Home Card ──────────────────────────────────────────
class _BelanjaHomeCard extends StatefulWidget {
  const _BelanjaHomeCard();

  @override
  State<_BelanjaHomeCard> createState() => _BelanjaHomeCardState();
}

class _BelanjaHomeCardState extends State<_BelanjaHomeCard> {
  List<Map<String, dynamic>> _items = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final list = await BelanjaStore.localList('data');
      if (!mounted) return;
      setState(() {
        _items = list;
        _loading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _openBelanja() async {
    await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const BelanjaScreen()),
    );
    _load();
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const SizedBox.shrink();

    final unbought = _items.where((e) => (e['bought']?.toString() ?? 'false') != 'true').toList();
    final boughtCount = _items.length - unbought.length;
    final progress = _items.isEmpty ? 0.0 : (boughtCount / _items.length);

    return Container(
      margin: const EdgeInsets.only(top: 14),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF9D174D), Color(0xFFBE185D), Color(0xFFF43F5E)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFFF43F5E).withValues(alpha: 0.3),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(20),
        child: Material(
          color: Colors.transparent,
          child: InkWell(
            onTap: _openBelanja,
            child: Stack(
              children: [
                // Watermark Vector Icon
                Positioned(
                  right: -10,
                  bottom: -15,
                  child: Transform.rotate(
                    angle: -0.15,
                    child: Icon(
                      Icons.shopping_bag_rounded,
                      size: 90,
                      color: Colors.white.withValues(alpha: 0.12),
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              Container(
                                width: 36,
                                height: 36,
                                decoration: BoxDecoration(
                                  color: Colors.white.withValues(alpha: 0.22),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: const Icon(Icons.shopping_bag_rounded, color: Colors.white, size: 20),
                              ),
                              const SizedBox(width: 10),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'Daftar Belanja',
                                    style: TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w800,
                                      color: Colors.white,
                                      letterSpacing: -0.2,
                                    ),
                                  ),
                                  Text(
                                    _items.isEmpty
                                        ? 'Belum ada catatan belanja'
                                        : '${unbought.length} item perlu dibeli',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                      color: Colors.white.withValues(alpha: 0.85),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.22),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: const Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  'Buka',
                                  style: TextStyle(
                                    fontSize: 11.5,
                                    fontWeight: FontWeight.w800,
                                    color: Colors.white,
                                  ),
                                ),
                                SizedBox(width: 3),
                                Icon(Icons.arrow_forward_ios_rounded, size: 10, color: Colors.white),
                              ],
                            ),
                          ),
                        ],
                      ),
                      if (_items.isNotEmpty) ...[
                        const SizedBox(height: 12),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(4),
                          child: LinearProgressIndicator(
                            value: progress,
                            minHeight: 5,
                            backgroundColor: Colors.black.withValues(alpha: 0.2),
                            valueColor: const AlwaysStoppedAnimation(Colors.white),
                          ),
                        ),
                        if (unbought.isNotEmpty) ...[
                          const SizedBox(height: 10),
                          Column(
                            children: unbought.take(2).map((item) {
                              final name = item['name']?.toString() ?? '';
                              final qty = item['qty']?.toString() ?? '';
                              return Padding(
                                padding: const EdgeInsets.only(top: 3),
                                child: Row(
                                  children: [
                                    Container(
                                      width: 14,
                                      height: 14,
                                      margin: const EdgeInsets.only(right: 8),
                                      decoration: BoxDecoration(
                                        border: Border.all(color: Colors.white70, width: 1.5),
                                        borderRadius: BorderRadius.circular(4),
                                        color: Colors.white.withValues(alpha: 0.1),
                                      ),
                                    ),
                                    Expanded(
                                      child: Text(
                                        name,
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Colors.white),
                                      ),
                                    ),
                                    if (qty.isNotEmpty)
                                      Text(
                                        qty,
                                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Colors.white.withValues(alpha: 0.8)),
                                      ),
                                  ],
                                ),
                              );
                            }).toList(),
                          ),
                        ],
                      ] else ...[
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            Icon(Icons.add_circle_outline_rounded, size: 15, color: Colors.white.withValues(alpha: 0.9)),
                            const SizedBox(width: 6),
                            Text(
                              'Ketuk untuk membuat daftar belanjaan baru',
                              style: TextStyle(
                                fontSize: 11.5,
                                fontWeight: FontWeight.w600,
                                color: Colors.white.withValues(alpha: 0.9),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// ── Todo Home Card ─────────────────────────────────────────────
class _TodoHomeCard extends StatefulWidget {
  const _TodoHomeCard();

  @override
  State<_TodoHomeCard> createState() => _TodoHomeCardState();
}

class _TodoHomeCardState extends State<_TodoHomeCard> {
  final _api = ApiService.instance;
  TodoSummary _summary = TodoSummary();
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final res = await _api.getTodos();
      if (!mounted) return;
      if (res['success'] == true) {
        final sum = TodoSummary.fromJson(res['summary'] is Map<String, dynamic> ? res['summary'] : {});
        setState(() {
          _summary = sum;
          _loading = false;
        });
      } else {
        setState(() => _loading = false);
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _toggleTask(TodoItem task) async {
    final oldState = task.isCompleted;
    setState(() => task.isCompleted = !oldState);

    try {
      final res = await _api.toggleTodo(task.id);
      if (res['success'] != true) {
        setState(() => task.isCompleted = oldState);
      } else {
        _load();
      }
    } catch (_) {
      setState(() => task.isCompleted = oldState);
    }
  }

  Future<void> _openTodo() async {
    await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const TodoListScreen()),
    );
    _load();
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const SizedBox.shrink();

    final progress = _summary.totalAll > 0 ? (_summary.completedAll / _summary.totalAll) : 0.0;

    return Container(
      margin: const EdgeInsets.only(top: 14),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF4338CA), Color(0xFF6366F1), Color(0xFF8B5CF6)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF6366F1).withValues(alpha: 0.3),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(20),
        child: Material(
          color: Colors.transparent,
          child: InkWell(
            onTap: _openTodo,
            child: Stack(
              children: [
                Positioned(
                  right: -10,
                  bottom: -15,
                  child: Transform.rotate(
                    angle: -0.15,
                    child: Icon(
                      Icons.task_alt_rounded,
                      size: 90,
                      color: Colors.white.withValues(alpha: 0.12),
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              Container(
                                width: 36,
                                height: 36,
                                decoration: BoxDecoration(
                                  color: Colors.white.withValues(alpha: 0.22),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: const Icon(Icons.checklist_rounded, color: Colors.white, size: 20),
                              ),
                              const SizedBox(width: 10),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'Rencana & Todo-List',
                                    style: TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w800,
                                      color: Colors.white,
                                      letterSpacing: -0.2,
                                    ),
                                  ),
                                  Text(
                                    _summary.totalAll == 0
                                        ? 'Belum ada tugas target'
                                        : '${_summary.completedAll}/${_summary.totalAll} Selesai · ${_summary.pendingAll} Aktif',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                      color: Colors.white.withValues(alpha: 0.85),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.22),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: const Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  'Buka',
                                  style: TextStyle(
                                    fontSize: 11.5,
                                    fontWeight: FontWeight.w800,
                                    color: Colors.white,
                                  ),
                                ),
                                SizedBox(width: 3),
                                Icon(Icons.arrow_forward_ios_rounded, size: 10, color: Colors.white),
                              ],
                            ),
                          ),
                        ],
                      ),
                      if (_summary.totalAll > 0) ...[
                        const SizedBox(height: 12),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(4),
                          child: LinearProgressIndicator(
                            value: progress,
                            minHeight: 5,
                            backgroundColor: Colors.black.withValues(alpha: 0.2),
                            valueColor: const AlwaysStoppedAnimation(Colors.white),
                          ),
                        ),
                        if (_summary.previews.isNotEmpty) ...[
                          const SizedBox(height: 10),
                          Column(
                            children: _summary.previews.map((task) {
                              return Padding(
                                padding: const EdgeInsets.only(top: 3),
                                child: Row(
                                  children: [
                                    GestureDetector(
                                      onTap: () => _toggleTask(task),
                                      child: Container(
                                        width: 16,
                                        height: 16,
                                        margin: const EdgeInsets.only(right: 8),
                                        decoration: BoxDecoration(
                                          color: task.isCompleted ? Colors.white : Colors.transparent,
                                          borderRadius: BorderRadius.circular(8),
                                          border: Border.all(color: Colors.white.withValues(alpha: 0.8), width: 1.5),
                                        ),
                                        child: task.isCompleted
                                            ? const Icon(Icons.check, size: 10, color: Color(0xFF6366F1))
                                            : null,
                                      ),
                                    ),
                                    Expanded(
                                      child: Text(
                                        task.title,
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                        style: TextStyle(
                                          fontSize: 12,
                                          fontWeight: FontWeight.w700,
                                          color: Colors.white,
                                          decoration: task.isCompleted ? TextDecoration.lineThrough : null,
                                        ),
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                                      decoration: BoxDecoration(
                                        color: Colors.white.withValues(alpha: 0.2),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(
                                        task.category,
                                        style: TextStyle(
                                          fontSize: 10,
                                          fontWeight: FontWeight.w600,
                                          color: Colors.white.withValues(alpha: 0.9),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              );
                            }).toList(),
                          ),
                        ],
                      ] else ...[
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            Icon(Icons.add_task_rounded, size: 15, color: Colors.white.withValues(alpha: 0.9)),
                            const SizedBox(width: 6),
                            Text(
                              'Ketuk untuk membuat target tugas baru',
                              style: TextStyle(
                                fontSize: 11.5,
                                fontWeight: FontWeight.w600,
                                color: Colors.white.withValues(alpha: 0.9),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// ── Notification Bell ───────────────────────────────────────────
class _NotificationBell extends StatelessWidget {
  final dynamic data;
  final VoidCallback onRefresh;
  const _NotificationBell({required this.data, required this.onRefresh});

  @override
  Widget build(BuildContext context) {
    final notifs = (data.notifications as List?) ?? [];
    final count = notifs.length;

    return Stack(
      alignment: Alignment.center,
      children: [
        IconButton(
          tooltip: 'Notifikasi Pengingat',
          icon: const Icon(Icons.notifications_outlined, size: 24),
          onPressed: () => _showNotificationsSheet(context, data, onRefresh),
        ),
        if (count > 0)
          Positioned(
            top: 8,
            right: 8,
            child: Container(
              padding: const EdgeInsets.all(4),
              decoration: const BoxDecoration(
                color: AppColors.expense,
                shape: BoxShape.circle,
              ),
              constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
              child: Text(
                count > 9 ? '9+' : '$count',
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 9,
                  fontWeight: FontWeight.w900,
                  height: 1,
                ),
              ),
            ),
          ),
      ],
    );
  }
}

// ── Urgent Alert Banner ─────────────────────────────────────────
class _UrgentAlertBanner extends StatelessWidget {
  final int count;
  final VoidCallback onTap;
  const _UrgentAlertBanner({required this.count, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFFDC2626), Color(0xFFEF4444)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFFDC2626).withValues(alpha: 0.3),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(16),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    shape: BoxShape.circle,
                  ),
                  child: const Text('⏰', style: TextStyle(fontSize: 18)),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '$count Pengingat Jatuh Tempo Segera!',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 13,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Ketuk untuk melihat dan bayar tagihan hari ini.',
                        style: TextStyle(
                          color: Colors.white.withValues(alpha: 0.9),
                          fontSize: 11,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: const Text(
                    'Lihat',
                    style: TextStyle(
                      color: Color(0xFFDC2626),
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// ── Notifications Bottom Sheet ──────────────────────────────────
void _showNotificationsSheet(BuildContext context, dynamic data, VoidCallback onRefresh) {
  final notifs = (data.notifications as List?) ?? [];
  final symbol = data.symbol as String? ?? 'Rp';

  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    backgroundColor: AppColors.card,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
    ),
    builder: (ctx) {
      return DraggableScrollableSheet(
        initialChildSize: 0.65,
        minChildSize: 0.4,
        maxChildSize: 0.9,
        expand: false,
        builder: (c, scrollCtrl) {
          return Column(
            children: [
              const SizedBox(height: 12),
              Container(
                width: 36,
                height: 4,
                decoration: BoxDecoration(
                  color: AppColors.border,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        const Text(
                          '🔔 Notifikasi & Pengingat',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        if (notifs.isNotEmpty) ...[
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: AppColors.expense.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Text(
                              '${notifs.length}',
                              style: const TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w800,
                                color: AppColors.expense,
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                    Row(
                      children: [
                        IconButton(
                          icon: const Icon(Icons.campaign_rounded, size: 22, color: AppColors.primary),
                          tooltip: 'Pesan & Pengumuman',
                          onPressed: () {
                            Navigator.pop(ctx);
                            Navigator.push(
                              context,
                              MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                            );
                          },
                        ),
                        IconButton(
                          icon: const Icon(Icons.close, size: 20),
                          onPressed: () => Navigator.pop(ctx),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const Divider(height: 1),
              Expanded(
                child: notifs.isEmpty
                    ? const Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text('🎉', style: TextStyle(fontSize: 48)),
                            SizedBox(height: 12),
                            Text(
                              'Tidak Ada Pengingat',
                              style: TextStyle(
                                fontSize: 15,
                                fontWeight: FontWeight.w700,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            SizedBox(height: 4),
                            Text(
                              'Semua tagihan, hutang, dan pajak aman terkendali!',
                              style: TextStyle(
                                fontSize: 12,
                                color: AppColors.textMuted,
                              ),
                            ),
                          ],
                        ),
                      )
                    : ListView.separated(
                        controller: scrollCtrl,
                        padding: const EdgeInsets.all(16),
                        itemCount: notifs.length,
                        separatorBuilder: (_, _) => const SizedBox(height: 10),
                        itemBuilder: (context, idx) {
                          final item = notifs[idx] as Map<String, dynamic>;
                          final daysLeft = (item['days_left'] as num?)?.toInt() ?? 0;
                          final isUrgent = daysLeft <= 0;
                          final isSoon = daysLeft > 0 && daysLeft <= 2;
                          final amount = Fmt.toDouble(item['amount']);

                          return Container(
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: AppColors.bg,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(
                                color: isUrgent
                                    ? const Color(0xFFEF4444)
                                    : (isSoon ? const Color(0xFFF59E0B) : AppColors.border),
                                width: (isUrgent || isSoon) ? 1.5 : 1,
                              ),
                            ),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  item['icon']?.toString() ?? '⏰',
                                  style: const TextStyle(fontSize: 24),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        item['title']?.toString() ?? '',
                                        style: const TextStyle(
                                          fontSize: 13.5,
                                          fontWeight: FontWeight.w800,
                                          color: AppColors.textPrimary,
                                        ),
                                      ),
                                      const SizedBox(height: 3),
                                      Text(
                                        item['subtitle']?.toString() ?? '',
                                        style: TextStyle(
                                          fontSize: 11.5,
                                          fontWeight: FontWeight.w600,
                                          color: isUrgent
                                              ? const Color(0xFFDC2626)
                                              : (isSoon ? const Color(0xFFD97706) : AppColors.textSecondary),
                                        ),
                                      ),
                                      if (amount > 0) ...[
                                        const SizedBox(height: 4),
                                        Text(
                                          Fmt.money(amount, symbol: symbol),
                                          style: const TextStyle(
                                            fontSize: 12.5,
                                            fontWeight: FontWeight.w800,
                                            color: AppColors.textPrimary,
                                          ),
                                        ),
                                      ],
                                    ],
                                  ),
                                ),
                                const SizedBox(width: 8),
                                ElevatedButton(
                                  onPressed: () {
                                    Navigator.pop(ctx);
                                    final type = item['type']?.toString();
                                    if (type == 'bill') {
                                      Navigator.push(context, MaterialPageRoute(builder: (_) => BillsScreen(symbol: symbol))).then((_) => onRefresh());
                                    } else if (type == 'debt') {
                                      Navigator.push(context, MaterialPageRoute(builder: (_) => const DebtScreen())).then((_) => onRefresh());
                                    } else if (type == 'tax') {
                                      Navigator.push(context, MaterialPageRoute(builder: (_) => const VehicleScreen())).then((_) => onRefresh());
                                    } else if (type == 'recurring') {
                                      Navigator.push(context, MaterialPageRoute(builder: (_) => const RecurringScreen())).then((_) => onRefresh());
                                    }
                                  },
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: isUrgent ? const Color(0xFFDC2626) : AppColors.primary,
                                    foregroundColor: Colors.white,
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                    minimumSize: const Size(60, 32),
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                  ),
                                  child: Text(
                                    item['type'] == 'bill' || item['type'] == 'debt' ? 'Bayar' : 'Buka',
                                    style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800),
                                  ),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
              ),
            ],
          );
        },
      );
    },
  );
}

// ═════════════════════════════════════════════════════════════════════════════
// BUSINESS WORKSPACE WIDGET
// ═════════════════════════════════════════════════════════════════════════════
class _BusinessWorkspace extends StatelessWidget {
  final dynamic data;
  final VoidCallback onRefresh;

  const _BusinessWorkspace({
    required this.data,
    required this.onRefresh,
  });

  @override
  Widget build(BuildContext context) {
    final symbol = data.symbol?.toString() ?? 'Rp';
    final biz = (data.business as Map<String, dynamic>?) ?? {};
    final todaySales = Fmt.toDouble(biz['today_sales']);
    final todayProfit = Fmt.toDouble(biz['today_profit']);
    final todayOrders = (biz['today_orders'] as num?)?.toInt() ?? 0;
    final monthSales = Fmt.toDouble(biz['month_sales']);
    final lowStockCount = (biz['low_stock_count'] as num?)?.toInt() ?? 0;
    final kasbonCount = (biz['kasbon_unsettled_count'] as num?)?.toInt() ?? 0;
    final kasbonTotal = Fmt.toDouble(biz['kasbon_unsettled_total']);
    final bestSellers = (biz['best_sellers'] as List<dynamic>?) ?? [];
    final recentOrders = (biz['recent_orders'] as List<dynamic>?) ?? [];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // 1. Hero Business Card
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF1E1B4B), Color(0xFF312E81), Color(0xFF4338CA)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(24),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF312E81).withValues(alpha: 0.35),
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'OMSET HARI INI',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Colors.white70, letterSpacing: 0.8),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: Colors.white24,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      '$todayOrders Order',
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Colors.white),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Text(
                Fmt.money(todaySales, symbol: symbol),
                style: const TextStyle(fontSize: 30, fontWeight: FontWeight.w900, color: Colors.white, letterSpacing: -0.8),
              ),
              const SizedBox(height: 14),
              Row(
                children: [
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Laba Bersih Hari Ini', style: TextStyle(fontSize: 10.5, color: Colors.white70)),
                          const SizedBox(height: 2),
                          Text(
                            Fmt.money(todayProfit, symbol: symbol),
                            style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w900, color: Color(0xFF34D399)),
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
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Omset Bulan Ini', style: TextStyle(fontSize: 10.5, color: Colors.white70)),
                          const SizedBox(height: 2),
                          Text(
                            Fmt.money(monthSales, symbol: symbol),
                            style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w900, color: Color(0xFFFDE047)),
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
        const SizedBox(height: 14),

        // 2. Quick POS Actions Grid (3 Cards)
        Row(
          children: [
            Expanded(
              child: _buildPosActionCard(
                context,
                title: 'Kasir POS',
                icon: '☕',
                gradient: const [Color(0xFFEA580C), Color(0xFFFB923C)],
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PosCashierScreen())).then((_) => onRefresh()),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _buildPosActionCard(
                context,
                title: 'Stok Produk',
                icon: '📦',
                gradient: const [Color(0xFF059669), Color(0xFF34D399)],
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PosProductsScreen())).then((_) => onRefresh()),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _buildPosActionCard(
                context,
                title: 'Laba Rugi',
                icon: '📊',
                gradient: const [Color(0xFF4F46E5), Color(0xFF818CF8)],
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PosReportsScreen())).then((_) => onRefresh()),
              ),
            ),
          ],
        ),
        const SizedBox(height: 14),

        // 3. Low Stock Alert
        if (lowStockCount > 0)
          Container(
            margin: const EdgeInsets.only(bottom: 12),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFFFEF2F2),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFFCA5A5)),
            ),
            child: Row(
              children: [
                const Text('⚠️', style: TextStyle(fontSize: 20)),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '$lowStockCount Produk Stok Menipis',
                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Color(0xFFDC2626)),
                      ),
                      const Text(
                        'Segera lakukan restock stok barang dagangan.',
                        style: TextStyle(fontSize: 11, color: Color(0xFF7F1D1D)),
                      ),
                    ],
                  ),
                ),
                TextButton(
                  onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PosProductsScreen())).then((_) => onRefresh()),
                  style: TextButton.styleFrom(
                    backgroundColor: const Color(0xFFDC2626),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    minimumSize: Size.zero,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  child: const Text('Restock', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
                ),
              ],
            ),
          ),

        // 4. Unpaid Kasbon Alert
        if (kasbonCount > 0)
          Container(
            margin: const EdgeInsets.only(bottom: 12),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFFFFFBEB),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFFDE68A)),
            ),
            child: Row(
              children: [
                const Text('📒', style: TextStyle(fontSize: 20)),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '$kasbonCount Kasbon Belum Lunas',
                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Color(0xFFD97706)),
                      ),
                      Text(
                        'Total piutang: ${Fmt.money(kasbonTotal, symbol: symbol)}',
                        style: const TextStyle(fontSize: 11, color: Color(0xFF78350F)),
                      ),
                    ],
                  ),
                ),
                TextButton(
                  onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const DebtScreen())).then((_) => onRefresh()),
                  style: TextButton.styleFrom(
                    backgroundColor: const Color(0xFFD97706),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    minimumSize: Size.zero,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  child: const Text('Tagih', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
                ),
              ],
            ),
          ),

        // 5. Best Sellers Ranking Card
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
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('🏆 4 Menu Terlaris Bulan Ini', style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800)),
                  GestureDetector(
                    onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PosReportsScreen())),
                    child: const Text('Laporan →', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: AppColors.primary)),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              if (bestSellers.isEmpty)
                const Center(
                  child: Padding(
                    padding: EdgeInsets.symmetric(vertical: 12),
                    child: Text('Belum ada transaksi kasir bulan ini.', style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                  ),
                )
              else
                ...bestSellers.asMap().entries.map((entry) {
                  final idx = entry.key;
                  final item = entry.value as Map<String, dynamic>;
                  final revenue = Fmt.toDouble(item['total_revenue']);
                  final qty = item['total_qty'] ?? 0;

                  return Container(
                    margin: const EdgeInsets.only(bottom: 6),
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: AppColors.bg,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 22,
                          height: 22,
                          decoration: BoxDecoration(
                            color: AppColors.primary,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Center(
                            child: Text(
                              '${idx + 1}',
                              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w900, color: Colors.white),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(item['product_name']?.toString() ?? '', style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700)),
                              Text('$qty terjual', style: const TextStyle(fontSize: 10.5, color: AppColors.textMuted)),
                            ],
                          ),
                        ),
                        Text(
                          Fmt.money(revenue, symbol: symbol),
                          style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: Color(0xFFEA580C)),
                        ),
                      ],
                    ),
                  );
                }),
            ],
          ),
        ),
        const SizedBox(height: 14),

        // 6. Recent POS Orders Card
        if (recentOrders.isNotEmpty)
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
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('📋 Transaksi Kasir Terakhir', style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800)),
                    GestureDetector(
                      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PosReportsScreen())),
                      child: const Text('Semua →', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: AppColors.primary)),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                ...recentOrders.map((o) {
                  final order = o as Map<String, dynamic>;
                  final orderNo = order['order_number']?.toString() ?? '-';
                  final total = Fmt.toDouble(order['total_amount']);
                  final date = order['created_at']?.toString() ?? '-';
                  final method = (order['payment_method']?.toString() ?? 'cash').toUpperCase();

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
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('#$orderNo · $method', style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700)),
                            Text(date, style: const TextStyle(fontSize: 10.5, color: AppColors.textMuted)),
                          ],
                        ),
                        Text(
                          Fmt.money(total, symbol: symbol),
                          style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: AppColors.primary),
                        ),
                      ],
                    ),
                  );
                }),
              ],
            ),
          ),
      ],
    );
  }

  Widget _buildPosActionCard(
    BuildContext context, {
    required String title,
    required String icon,
    required List<Color> gradient,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          gradient: LinearGradient(colors: gradient, begin: Alignment.topLeft, end: Alignment.bottomRight),
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(color: gradient.first.withValues(alpha: 0.25), blurRadius: 10, offset: const Offset(0, 4)),
          ],
        ),
        child: Column(
          children: [
            Text(icon, style: const TextStyle(fontSize: 22)),
            const SizedBox(height: 4),
            Text(title, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Colors.white)),
          ],
        ),
      ),
    );
  }
}

