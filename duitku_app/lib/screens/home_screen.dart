import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/app_data_provider.dart';
import '../services/update_checker_service.dart';
import '../theme.dart';
import 'activity_screen.dart';
import 'dashboard_screen.dart';
import 'features_screen.dart';
import 'settings_screen.dart';
import 'transaction_sheet.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _index = 0;
  final _dashKey = GlobalKey<DashboardScreenState>();

  @override
  void initState() {
    super.initState();
    // Pengecekan otomatis update APK GitHub Releases setelah aplikasi siap
    WidgetsBinding.instance.addPostFrameCallback((_) {
      // Periksa apakah ada klik notifikasi pembaruan saat cold-start
      UpdateCheckerService.instance.checkPendingUpdate(context);

      Future.delayed(const Duration(seconds: 2), () {
        if (mounted) {
          UpdateCheckerService.instance.checkAndShowUpdateDialog(context);
        }
      });
    });
  }

  final List<GlobalKey<NavigatorState>> _navigatorKeys = [
    GlobalKey<NavigatorState>(),
    GlobalKey<NavigatorState>(),
    GlobalKey<NavigatorState>(),
    GlobalKey<NavigatorState>(),
  ];

  Widget _buildTabNavigator(int index, Widget root) {
    return Navigator(
      key: _navigatorKeys[index],
      onGenerateRoute: (settings) => MaterialPageRoute(
        builder: (_) => root,
        settings: settings,
      ),
    );
  }

  void _onTabSelected(int index) {
    if (_index == index) {
      _navigatorKeys[index].currentState?.popUntil((route) => route.isFirst);
    } else {
      setState(() => _index = index);
    }
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) async {
        if (didPop) return;
        final isFirstRouteInCurrentTab =
            !await _navigatorKeys[_index].currentState!.maybePop();
        if (isFirstRouteInCurrentTab) {
          if (_index != 0) {
            setState(() => _index = 0);
          }
        }
      },
      child: Scaffold(
        resizeToAvoidBottomInset: false,
        body: IndexedStack(
          index: _index,
          children: [
            _buildTabNavigator(0, DashboardScreen(key: _dashKey)),
            _buildTabNavigator(1, const ActivityScreen()),
            _buildTabNavigator(2, const FeaturesScreen()),
            _buildTabNavigator(3, const SettingsScreen()),
          ],
        ),
        floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
        floatingActionButton: Container(
          height: 56,
          width: 56,
          margin: const EdgeInsets.only(top: 24),
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            gradient: const LinearGradient(
              colors: [Color(0xFF3B82F6), Color(0xFF1D4ED8)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            boxShadow: AppColors.fabShadow,
          ),
          child: Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: _openTransactionSheet,
              customBorder: const CircleBorder(),
              child: const Center(
                child: Icon(Icons.qr_code_scanner_rounded, color: Colors.white, size: 26),
              ),
            ),
          ),
        ),
        bottomNavigationBar: Container(
          decoration: BoxDecoration(
            color: AppColors.card,
            border: const Border(top: BorderSide(color: AppColors.border, width: 1)),
            boxShadow: const [
              BoxShadow(
                color: Color(0x08000000),
                blurRadius: 20,
                offset: Offset(0, -4),
              ),
            ],
          ),
          child: SafeArea(
            top: false,
            child: SizedBox(
              height: 64,
              child: Row(
                children: [
                  _navItem(0, Icons.grid_view_rounded, Icons.grid_view_outlined, 'Dashboard'),
                  _navItem(1, Icons.receipt_long_rounded, Icons.receipt_long_outlined, 'Aktivitas'),
                  const SizedBox(width: 56), // Spacing for centered FAB
                  _navItem(2, Icons.widgets_rounded, Icons.widgets_outlined, 'Fitur'),
                  _navItem(3, Icons.person_rounded, Icons.person_outline_rounded, 'Akun'),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _navItem(int index, IconData activeIcon, IconData inactiveIcon, String label) {
    final active = _index == index;
    return Expanded(
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => _onTabSelected(index),
          splashColor: const Color(0xFF2563EB).withValues(alpha: 0.1),
          highlightColor: Colors.transparent,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 3),
                decoration: BoxDecoration(
                  color: active ? const Color(0xFFEFF6FF) : Colors.transparent,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Icon(
                  active ? activeIcon : inactiveIcon,
                  size: 22,
                  color: active ? const Color(0xFF2563EB) : AppColors.textMuted,
                ),
              ),
              const SizedBox(height: 3),
              Text(
                label,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: active ? FontWeight.w800 : FontWeight.w500,
                  color: active ? const Color(0xFF2563EB) : AppColors.textSecondary,
                  letterSpacing: -0.1,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _openTransactionSheet() async {
    final data = context.read<AppDataProvider>();
    await data.ensureLoaded();
    if (!mounted) return;
    final saved = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => TransactionSheet(categories: data.categories, wallets: data.wallets),
    );
    if (saved == true && mounted) {
      _dashKey.currentState?.refresh();
      await data.reloadWallets();
    }
  }
}
