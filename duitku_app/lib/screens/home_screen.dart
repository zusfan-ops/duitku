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
            boxShadow: AppColors.fabShadow,
          ),
          child: FloatingActionButton(
            elevation: 0,
            highlightElevation: 0,
            onPressed: _openTransactionSheet,
            backgroundColor: AppColors.primary,
            shape: const CircleBorder(),
            child: const Icon(Icons.add_rounded, color: Colors.white, size: 30),
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
                  _navItem(0, Icons.home_rounded, Icons.home_outlined, 'Beranda'),
                  _navItem(1, Icons.receipt_long_rounded, Icons.receipt_long_outlined, 'Aktivitas'),
                  const SizedBox(width: 56), // Spacing for centered FAB
                  _navItem(2, Icons.grid_view_rounded, Icons.grid_view_outlined, 'Fitur'),
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
          splashColor: AppColors.primaryLight.withValues(alpha: 0.1),
          highlightColor: Colors.transparent,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 3),
                decoration: BoxDecoration(
                  color: active ? AppColors.primarySubtle : Colors.transparent,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Icon(
                  active ? activeIcon : inactiveIcon,
                  size: 22,
                  color: active ? AppColors.primary : AppColors.textMuted,
                ),
              ),
              const SizedBox(height: 3),
              Text(
                label,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: active ? FontWeight.w700 : FontWeight.w500,
                  color: active ? AppColors.primary : AppColors.textSecondary,
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
