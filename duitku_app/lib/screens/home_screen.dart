import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/app_data_provider.dart';
import '../services/update_checker_service.dart';
import 'activity_screen.dart';
import 'dashboard_screen.dart';
import 'features_screen.dart';
import 'marketplace/market_conversations_screen.dart';
import 'settings_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _index = 0;
  final _dashKey = GlobalKey<DashboardScreenState>();
  Timer? _unreadTimer;

  @override
  void initState() {
    super.initState();
    // Pengecekan otomatis update APK GitHub Releases setelah aplikasi siap
    WidgetsBinding.instance.addPostFrameCallback((_) {
      UpdateCheckerService.instance.checkPendingUpdate(context);
      context.read<AppDataProvider>().refreshMarketChatUnread();

      Future.delayed(const Duration(seconds: 2), () {
        if (mounted) {
          UpdateCheckerService.instance.checkAndShowUpdateDialog(context);
        }
      });
    });

    _unreadTimer = Timer.periodic(const Duration(seconds: 8), (_) {
      if (mounted) {
        context.read<AppDataProvider>().refreshMarketChatUnread();
      }
    });
  }

  @override
  void dispose() {
    _unreadTimer?.cancel();
    super.dispose();
  }

  final List<GlobalKey<NavigatorState>> _navigatorKeys = [
    GlobalKey<NavigatorState>(),
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
      if (index == 2) {
        context.read<AppDataProvider>().refreshMarketChatUnread();
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final unreadChat = context.watch<AppDataProvider>().marketChatUnread;

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
        extendBody: true,
        body: IndexedStack(
          index: _index,
          children: [
            _buildTabNavigator(0, DashboardScreen(key: _dashKey)),
            _buildTabNavigator(1, const ActivityScreen()),
            _buildTabNavigator(2, const MarketConversationsScreen(isRootTab: true)),
            _buildTabNavigator(3, const FeaturesScreen()),
            _buildTabNavigator(4, const SettingsScreen()),
          ],
        ),
        bottomNavigationBar: SafeArea(
          bottom: true,
          minimum: const EdgeInsets.fromLTRB(16, 0, 16, 14),
          child: Container(
            height: 64.0,
            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 6),
            decoration: BoxDecoration(
              color: const Color(0xFF1E2B1A),
              borderRadius: BorderRadius.circular(36),
              border: Border.all(
                color: const Color(0xFF283A23),
                width: 1,
              ),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF000000).withValues(alpha: 0.35),
                  blurRadius: 24,
                  offset: const Offset(0, 8),
                ),
                BoxShadow(
                  color: const Color(0xFF000000).withValues(alpha: 0.18),
                  blurRadius: 6,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                _buildNavItem(0, Icons.grid_view_rounded, 'Home'),
                _buildNavItem(1, Icons.receipt_long_rounded, 'Aktivitas'),
                _buildNavItem(2, Icons.chat_bubble_rounded, 'Pesan', badge: unreadChat),
                _buildNavItem(3, Icons.widgets_rounded, 'Fitur'),
                _buildNavItem(4, Icons.person_rounded, 'Akun'),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem(int index, IconData icon, String label, {int badge = 0}) {
    final active = _index == index;
    const limeColor = Color(0xFF10B981);
    const darkCapsuleBg = Color(0xFF283A23);
    const inactiveIconBg = Color(0xFF24351F);
    const inactiveIconColor = Color(0xFF94A3B8);

    return GestureDetector(
      onTap: () => _onTabSelected(index),
      behavior: HitTestBehavior.opaque,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 260),
        curve: Curves.easeInOutCubic,
        padding: EdgeInsets.only(
          left: 4,
          right: active ? 14 : 4,
          top: 4,
          bottom: 4,
        ),
        height: 48,
        decoration: BoxDecoration(
          color: active ? darkCapsuleBg : Colors.transparent,
          borderRadius: BorderRadius.circular(24),
          border: active
              ? Border.all(color: Color(0xFF3A4F32), width: 1)
              : null,
          boxShadow: active
              ? [
                  BoxShadow(
                    color: const Color(0xFF000000).withValues(alpha: 0.25),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Circle Icon Badge
            AnimatedContainer(
              duration: const Duration(milliseconds: 240),
              curve: Curves.easeOut,
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: active ? limeColor : inactiveIconBg,
                shape: BoxShape.circle,
                boxShadow: active
                    ? [
                        BoxShadow(
                          color: limeColor.withValues(alpha: 0.4),
                          blurRadius: 10,
                          spreadRadius: 1,
                        ),
                      ]
                    : null,
              ),
              child: Stack(
                clipBehavior: Clip.none,
                alignment: Alignment.center,
                children: [
                  Icon(
                    icon,
                    size: 19,
                    color: active ? Colors.white : inactiveIconColor,
                  ),
                  if (badge > 0)
                    Positioned(
                      top: -2,
                      right: -2,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1.5),
                        decoration: BoxDecoration(
                          color: const Color(0xFFEF4444),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(
                            color: const Color(0xFF1E2B1A),
                            width: 1.5,
                          ),
                        ),
                        constraints: const BoxConstraints(minWidth: 14, minHeight: 14),
                        child: Center(
                          child: Text(
                            badge > 99 ? '99+' : '$badge',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 8,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
            // Text Label
            AnimatedSize(
              duration: const Duration(milliseconds: 260),
              curve: Curves.easeInOutCubic,
              child: active
                  ? Padding(
                      padding: const EdgeInsets.only(left: 8),
                      child: Text(
                        label,
                        maxLines: 1,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 12.5,
                          fontWeight: FontWeight.w800,
                          letterSpacing: -0.2,
                        ),
                      ),
                    )
                  : const SizedBox.shrink(),
            ),
          ],
        ),
      ),
    );
  }
}
