import 'dart:async';
import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/app_data_provider.dart';
import '../services/update_checker_service.dart';
import '../theme.dart';
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
          top: false,
          child: Padding(
            padding: const EdgeInsets.only(left: 14, right: 14, bottom: 12),
            child: Container(
              height: 62,
              decoration: BoxDecoration(
                color: AppColors.card.withValues(alpha: 0.95),
                borderRadius: BorderRadius.circular(32),
                border: Border.all(
                  color: AppColors.border.withValues(alpha: 0.8),
                  width: 1.2,
                ),
                boxShadow: const [
                  BoxShadow(
                    color: Color(0x1F0F172A),
                    blurRadius: 28,
                    offset: Offset(0, 10),
                    spreadRadius: -2,
                  ),
                  BoxShadow(
                    color: Color(0x0A0F172A),
                    blurRadius: 8,
                    offset: Offset(0, 2),
                  ),
                ],
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(32),
                child: BackdropFilter(
                  filter: ImageFilter.blur(sigmaX: 24, sigmaY: 24),
                  child: Row(
                    children: [
                      _navItem(0, Icons.grid_view_rounded, Icons.grid_view_outlined, 'Dashboard'),
                      _navItem(1, Icons.receipt_long_rounded, Icons.receipt_long_outlined, 'Aktivitas'),
                      _navItem(2, Icons.forum_rounded, Icons.forum_outlined, 'Pesan', badgeCount: unreadChat),
                      _navItem(3, Icons.widgets_rounded, Icons.widgets_outlined, 'Fitur'),
                      _navItem(4, Icons.person_rounded, Icons.person_outline_rounded, 'Akun'),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _navItem(
    int index,
    IconData activeIcon,
    IconData inactiveIcon,
    String label, {
    int badgeCount = 0,
  }) {
    final active = _index == index;
    return Expanded(
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => _onTabSelected(index),
          splashColor: const Color(0xFF2563EB).withValues(alpha: 0.08),
          highlightColor: Colors.transparent,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Stack(
                clipBehavior: Clip.none,
                children: [
                  AnimatedContainer(
                    duration: const Duration(milliseconds: 220),
                    curve: Curves.easeOutCubic,
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                    decoration: BoxDecoration(
                      color: active ? const Color(0xFFEFF6FF) : Colors.transparent,
                      borderRadius: BorderRadius.circular(18),
                      boxShadow: active
                          ? [
                              BoxShadow(
                                color: const Color(0xFF2563EB).withValues(alpha: 0.12),
                                blurRadius: 8,
                                offset: const Offset(0, 2),
                              )
                            ]
                          : null,
                    ),
                    child: AnimatedScale(
                      scale: active ? 1.08 : 1.0,
                      duration: const Duration(milliseconds: 200),
                      curve: Curves.easeOutBack,
                      child: Icon(
                        active ? activeIcon : inactiveIcon,
                        size: 21,
                        color: active ? const Color(0xFF2563EB) : AppColors.textMuted,
                      ),
                    ),
                  ),
                  if (badgeCount > 0)
                    Positioned(
                      top: -1,
                      right: 1,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                        decoration: BoxDecoration(
                          color: const Color(0xFFEF4444),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: Colors.white, width: 1.5),
                          boxShadow: const [
                            BoxShadow(
                              color: Color(0x33EF4444),
                              blurRadius: 4,
                              offset: Offset(0, 1),
                            ),
                          ],
                        ),
                        constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                        child: Text(
                          badgeCount > 99 ? '99+' : '$badgeCount',
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 9.5,
                            fontWeight: FontWeight.w900,
                            height: 1,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 2),
              AnimatedDefaultTextStyle(
                duration: const Duration(milliseconds: 200),
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: active ? FontWeight.w800 : FontWeight.w600,
                  color: active ? const Color(0xFF2563EB) : AppColors.textSecondary,
                  letterSpacing: -0.1,
                  fontFamily: 'sans-serif',
                ),
                child: Text(
                  label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
