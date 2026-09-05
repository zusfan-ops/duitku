import 'dart:async';
import 'dart:math';

import 'package:flutter/material.dart';

const _financialTips = [
  'Catat setiap pengeluaran sekecil apa pun — dari situ kebocoran keuangan ketahuan.',
  'Sisihkan minimal 10% penghasilan untuk tabungan sebelum dibelanjakan.',
  'Buat anggaran bulanan dan patuhi — budget yang baik itu rencana, bukan batasan.',
  'Dana darurat idealnya 3–6 kali pengeluaran bulananmu.',
  'Bayar hutang tepat waktu, hindari bunga menumpuk yang menggerogoti keuangan.',
  'Pisahkan rekening kebutuhan, tabungan, dan hiburan biar nggak boros.',
  'Cek laporan keuanganmu tiap bulan — evaluasi rutin bikin lebih sadar uang.',
  'Hindari utang konsumtif untuk barang yang cepat kehilangan nilai.',
  'Investasi kecil tapi konsisten lebih baik daripada menunggu "uang lebih".',
  'Sebelum belanja, tanya diri sendiri: butuh, atau cuma pengen?',
  'Review langganan bulananmu — banyak yang lupa berhenti berlangganan.',
  'Uang yang bekerja untukmu, lebih baik dari kamu bekerja seumur hidup untuknya.',
];

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> with SingleTickerProviderStateMixin {
  late final AnimationController _fadeCtrl;
  late final Animation<double> _fade;
  late final Animation<Offset> _slide;
  late int _tipIndex;
  Timer? _tipTimer;

  // Material Design 3 Army Green Color Palette
  static const Color _armyBackground = Color(0xFF1E2B1A);
  static const Color _armyDark = Color(0xFF141E11);
  static const Color _armyLight = Color(0xFF283A23);
  static const Color _brandAccent = Color(0xFF96BC33); // Matched to new logo
  static const Color _brandAccentLight = Color(0xFFB5E048);

  @override
  void initState() {
    super.initState();
    _tipIndex = Random().nextInt(_financialTips.length);
    _fadeCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 650))..forward();
    _fade = CurvedAnimation(parent: _fadeCtrl, curve: Curves.easeOutCubic);
    _slide = Tween<Offset>(begin: const Offset(0, .05), end: Offset.zero)
        .animate(CurvedAnimation(parent: _fadeCtrl, curve: Curves.easeOutCubic));

    _tipTimer = Timer.periodic(const Duration(milliseconds: 1400), (_) {
      if (!mounted) return;
      setState(() => _tipIndex = (_tipIndex + 1) % _financialTips.length);
    });
  }

  @override
  void dispose() {
    _tipTimer?.cancel();
    _fadeCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _armyBackground,
      body: Stack(
        fit: StackFit.expand,
        children: [
          // Base Material 3 Atmospheric Army Green Gradient
          const DecoratedBox(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [_armyDark, _armyBackground, _armyLight],
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
              ),
            ),
          ),

          // Ambient Radial Glows
          Positioned(
            top: -60,
            right: -40,
            child: _Glow(color: _brandAccent.withValues(alpha: .14), size: 280),
          ),
          Positioned(
            bottom: -80,
            left: -50,
            child: _Glow(color: _armyLight.withValues(alpha: .30), size: 300),
          ),

          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                physics: const NeverScrollableScrollPhysics(),
                padding: const EdgeInsets.symmetric(horizontal: 28),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // M3 Surface Elevated Logo Squircle
                    Container(
                      width: 104,
                      height: 104,
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFF263721),
                        borderRadius: BorderRadius.circular(28),
                        border: Border.all(
                          color: _brandAccent.withValues(alpha: 0.28),
                          width: 1.5,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.35),
                            blurRadius: 24,
                            offset: const Offset(0, 10),
                          ),
                          BoxShadow(
                            color: _brandAccent.withValues(alpha: 0.12),
                            blurRadius: 18,
                            spreadRadius: 1,
                          ),
                        ],
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(18),
                        child: Image.asset(
                          'assets/icon/logo.png',
                          width: 80,
                          height: 80,
                          fit: BoxFit.contain,
                        ),
                      ),
                    ),
                    const SizedBox(height: 18),

                    // App Title
                    const Text(
                      'DuitKu',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 30,
                        fontWeight: FontWeight.w800,
                        letterSpacing: -0.5,
                      ),
                    ),
                    const SizedBox(height: 8),

                    // M3 Tonal Badge
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
                      decoration: BoxDecoration(
                        color: _brandAccent.withValues(alpha: 0.16),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(
                          color: _brandAccent.withValues(alpha: 0.32),
                          width: 1,
                        ),
                      ),
                      child: const Text(
                        'PENCATAT KEUANGAN PRIBADI',
                        style: TextStyle(
                          color: _brandAccentLight,
                          fontSize: 10.5,
                          fontWeight: FontWeight.w700,
                          letterSpacing: 1.1,
                        ),
                      ),
                    ),
                    const SizedBox(height: 36),

                    // Material 3 Financial Tips Card
                    FadeTransition(
                      opacity: _fade,
                      child: SlideTransition(
                        position: _slide,
                        child: Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                          decoration: BoxDecoration(
                            color: const Color(0xFF263721).withValues(alpha: 0.88),
                            borderRadius: BorderRadius.circular(22),
                            border: Border.all(
                              color: Colors.white.withValues(alpha: 0.12),
                              width: 1.2,
                            ),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.22),
                                blurRadius: 16,
                                offset: const Offset(0, 6),
                              ),
                            ],
                          ),
                          child: Column(
                            children: [
                              Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.lightbulb_rounded, size: 16, color: _brandAccent),
                                  const SizedBox(width: 6),
                                  const Text(
                                    'TIPS KEUANGAN',
                                    style: TextStyle(
                                      color: _brandAccent,
                                      fontSize: 11,
                                      fontWeight: FontWeight.w800,
                                      letterSpacing: 1.2,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 10),
                              AnimatedSwitcher(
                                duration: const Duration(milliseconds: 400),
                                transitionBuilder: (child, anim) => FadeTransition(
                                  opacity: anim,
                                  child: SlideTransition(
                                    position: Tween<Offset>(
                                      begin: const Offset(0, 0.08),
                                      end: Offset.zero,
                                    ).animate(anim),
                                    child: child,
                                  ),
                                ),
                                child: Text(
                                  _financialTips[_tipIndex],
                                  key: ValueKey(_tipIndex),
                                  textAlign: TextAlign.center,
                                  style: const TextStyle(
                                    color: Color(0xFFF1F5EE),
                                    fontSize: 13.5,
                                    height: 1.5,
                                    fontWeight: FontWeight.w500,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 48),

                    // Material Repeating Pulse Dots
                    const _RepeatingDots(dotColor: _brandAccent),
                  ],
                ),
              ),
            ),
          ),

          // Security / Integrity Footer
          Positioned(
            left: 0,
            right: 0,
            bottom: 24,
            child: Center(
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: const [
                  Icon(Icons.lock_outline_rounded, size: 13, color: Colors.white38),
                  SizedBox(width: 5),
                  Text(
                    'Aman & Terenkripsi',
                    style: TextStyle(
                      color: Colors.white38,
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                      letterSpacing: 0.3,
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

class _Glow extends StatelessWidget {
  final Color color;
  final double size;
  const _Glow({required this.color, required this.size});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        gradient: RadialGradient(colors: [color, color.withValues(alpha: 0)]),
      ),
    );
  }
}

class _RepeatingDots extends StatefulWidget {
  final Color dotColor;
  const _RepeatingDots({required this.dotColor});

  @override
  State<_RepeatingDots> createState() => _RepeatingDotsState();
}

class _RepeatingDotsState extends State<_RepeatingDots> with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 1200))..repeat();
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _ctrl,
      builder: (context, _) {
        return Row(
          mainAxisSize: MainAxisSize.min,
          children: List.generate(3, (i) {
            final t = (_ctrl.value + i * .2) % 1.0;
            final opacity = 0.25 + 0.75 * (0.5 + 0.5 * sin(t * 2 * pi));
            return Padding(
              padding: const EdgeInsets.symmetric(horizontal: 4),
              child: Opacity(
                opacity: opacity,
                child: Container(
                  width: 7,
                  height: 7,
                  decoration: BoxDecoration(
                    color: widget.dotColor,
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: widget.dotColor.withValues(alpha: 0.4),
                        blurRadius: 4,
                        spreadRadius: 0.5,
                      ),
                    ],
                  ),
                ),
              ),
            );
          }),
        );
      },
    );
  }
}
