import 'dart:async';
import 'dart:ui';
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
          minimum: const EdgeInsets.fromLTRB(16, 0, 16, 12),
          child: SizedBox(
            height: 88.0,
            child: Stack(
                    clipBehavior: Clip.none,
                    alignment: Alignment.bottomCenter,
                    children: [
                // ── Floating Frosted Glass Capsule Bar ────────────────────────
                Positioned(
                  left: 0,
                  right: 0,
                  bottom: 0,
                  height: 64.0,
                  child: Container(
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(32),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF0F172A).withValues(alpha: 0.12),
                          blurRadius: 28,
                          spreadRadius: 0,
                          offset: const Offset(0, 8),
                        ),
                        BoxShadow(
                          color: const Color(0xFF0F172A).withValues(alpha: 0.04),
                          blurRadius: 6,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(32),
                      child: BackdropFilter(
                        filter: ImageFilter.blur(sigmaX: 24, sigmaY: 24),
                        child: Container(
                          decoration: BoxDecoration(
                            color: Theme.of(context).brightness == Brightness.dark
                                ? const Color(0xFF1E293B).withValues(alpha: 0.90)
                                : Colors.white.withValues(alpha: 0.88),
                            borderRadius: BorderRadius.circular(32),
                            border: Border.all(
                              color: Theme.of(context).brightness == Brightness.dark
                                  ? const Color(0xFF334155).withValues(alpha: 0.8)
                                  : Colors.white.withValues(alpha: 0.9),
                              width: 1.5,
                            ),
                          ),
                          child: Row(
                            children: [
                              Expanded(child: _normalNavBtn(0, Icons.grid_view_outlined, Icons.grid_view_rounded, 'Dashboard')),
                              Expanded(child: _normalNavBtn(1, Icons.receipt_long_outlined, Icons.receipt_long_rounded, 'Aktivitas')),
                              const Expanded(child: SizedBox()), // Center space for 3D button
                              Expanded(child: _normalNavBtn(3, Icons.widgets_outlined, Icons.widgets_rounded, 'Fitur')),
                              Expanded(child: _normalNavBtn(4, Icons.person_outline_rounded, Icons.person_rounded, 'Akun')),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                ),

                // ── Center 3D Floating Action Button (Menu 2: Pesan / Chat & Status) ──
                Positioned(
                  top: 0,
                  child: _center3DNavBtn(unreadChat),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _normalNavBtn(int index, IconData icon, IconData activeIcon, String label) {
    final active = _index == index;
    const activeColor = Color(0xFF2563EB);

    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () => _onTabSelected(index),
        borderRadius: BorderRadius.circular(24),
        splashColor: const Color(0xFF2563EB).withValues(alpha: 0.1),
        highlightColor: Colors.transparent,
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            AnimatedContainer(
              duration: const Duration(milliseconds: 220),
              curve: Curves.easeOutCubic,
              width: active ? 18 : 0,
              height: 3,
              margin: const EdgeInsets.only(bottom: 4),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF2563EB), Color(0xFF3B82F6)],
                ),
                borderRadius: BorderRadius.circular(3),
              ),
            ),
            AnimatedScale(
              duration: const Duration(milliseconds: 200),
              scale: active ? 1.12 : 1.0,
              child: Icon(
                active ? activeIcon : icon,
                size: 22,
                color: active ? activeColor : const Color(0xFF64748B),
              ),
            ),
            const SizedBox(height: 3),
            Text(
              label,
              style: TextStyle(
                fontSize: 10.5,
                fontWeight: active ? FontWeight.w800 : FontWeight.w600,
                color: active ? activeColor : const Color(0xFF64748B),
                letterSpacing: -0.2,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _center3DNavBtn(int unreadCount) {
    final active = _index == 2;
    const size = 58.0;

    return GestureDetector(
      onTap: () => _onTabSelected(2),
      behavior: HitTestBehavior.opaque,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          AnimatedScale(
            duration: const Duration(milliseconds: 220),
            curve: Curves.easeOutBack,
            scale: active ? 1.08 : 1.0,
            child: Stack(
              clipBehavior: Clip.none,
              children: [
                Container(
                  width: size,
                  height: size,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    boxShadow: [
                      // Vibrant Ambient Glow
                      BoxShadow(
                        color: const Color(0xFF2563EB).withValues(alpha: 0.45),
                        blurRadius: 18,
                        spreadRadius: 1,
                        offset: const Offset(0, 8),
                      ),
                      // Deep Contact Shadow
                      BoxShadow(
                        color: const Color(0xFF1E3A8A).withValues(alpha: 0.35),
                        blurRadius: 8,
                        offset: const Offset(0, 4),
                      ),
                      // Outer rim
                      BoxShadow(
                        color: Colors.white.withValues(alpha: 0.85),
                        blurRadius: 0,
                        spreadRadius: 2.5,
                      ),
                    ],
                  ),
                  child: ClipOval(
                    child: Stack(
                      children: [
                        // Rich Multi-stop Gradient
                        Container(
                          decoration: const BoxDecoration(
                            gradient: LinearGradient(
                              colors: [Color(0xFF1D4ED8), Color(0xFF2563EB), Color(0xFF38BDF8)],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                          ),
                        ),
                        // Top Glossy 3D Highlight Reflection
                        Positioned(
                          top: 0,
                          left: 0,
                          right: 0,
                          child: Container(
                            height: size * 0.48,
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                begin: Alignment.topCenter,
                                end: Alignment.bottomCenter,
                                colors: [
                                  Colors.white.withValues(alpha: 0.45),
                                  Colors.white.withValues(alpha: 0.0),
                                ],
                              ),
                            ),
                          ),
                        ),
                        // Inner Bottom Rim Shadow
                        Positioned(
                          bottom: 0,
                          left: 0,
                          right: 0,
                          child: Container(
                            height: size * 0.25,
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                begin: Alignment.bottomCenter,
                                end: Alignment.topCenter,
                                colors: [
                                  Colors.black.withValues(alpha: 0.25),
                                  Colors.transparent,
                                ],
                              ),
                            ),
                          ),
                        ),
                        // Center Icon
                        Center(
                          child: Icon(
                            active ? Icons.forum_rounded : Icons.forum_outlined,
                            size: 26,
                            color: Colors.white,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                // Badge Notifikasi
                if (unreadCount > 0)
                  Positioned(
                    top: -2,
                    right: -2,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEF4444),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: Colors.white, width: 2),
                        boxShadow: const [
                          BoxShadow(
                            color: Color(0x33EF4444),
                            blurRadius: 4,
                            offset: Offset(0, 1),
                          ),
                        ],
                      ),
                      constraints: const BoxConstraints(minWidth: 18, minHeight: 18),
                      child: Text(
                        unreadCount > 99 ? '99+' : '$unreadCount',
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
          ),
          const SizedBox(height: 4),
          Text(
            'Pesan',
            style: TextStyle(
              fontSize: 10.5,
              fontWeight: active ? FontWeight.w800 : FontWeight.w600,
              color: active ? const Color(0xFF2563EB) : const Color(0xFF64748B),
              letterSpacing: -0.2,
            ),
          ),
        ],
      ),
    );
  }
}
