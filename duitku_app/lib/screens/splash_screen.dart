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

  @override
  void initState() {
    super.initState();
    _tipIndex = Random().nextInt(_financialTips.length);
    _fadeCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 600))..forward();
    _fade = CurvedAnimation(parent: _fadeCtrl, curve: Curves.easeOut);
    _slide = Tween<Offset>(begin: const Offset(0, .04), end: Offset.zero)
        .animate(CurvedAnimation(parent: _fadeCtrl, curve: Curves.easeOut));
    _tipTimer = Timer.periodic(const Duration(milliseconds: 1250), (_) {
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
      backgroundColor: const Color(0xFF0A2A1F),
      body: Stack(
        fit: StackFit.expand,
        children: [
          // Base gradient — same greens used across the app's hero cards.
          const DecoratedBox(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [Color(0xFF043D22), Color(0xFF076836), Color(0xFF0AA956)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
          ),
          // Soft radial glows for texture, echoing the liqo-app splash motif.
          Positioned(
            top: -80,
            left: -60,
            child: _Glow(color: const Color(0xFFF59E0B).withValues(alpha: .18), size: 260),
          ),
          Positioned(
            bottom: -100,
            right: -70,
            child: _Glow(color: Colors.white.withValues(alpha: .10), size: 300),
          ),
          SafeArea(
            child: Center(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 32),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // Identical to what the native pre-Flutter splash already shows —
                    // rendered statically (no fade-in) so there's no visible flicker
                    // at the handoff between native splash and this Dart screen.
                    ClipRRect(
                      borderRadius: BorderRadius.circular(20),
                      child: Image.asset('assets/icon/logo.png', width: 92, height: 92),
                    ),
                    const SizedBox(height: 16),
                    const Text(
                      'DuitKu',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 28,
                        fontWeight: FontWeight.w800,
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'Pencatat keuangan pribadi',
                      style: TextStyle(color: Colors.white70, fontSize: 13),
                    ),
                    const SizedBox(height: 40),
                    FadeTransition(
                      opacity: _fade,
                      child: SlideTransition(
                        position: _slide,
                        child: Column(
                          children: [
                            const Text('💡', style: TextStyle(fontSize: 20)),
                            const SizedBox(height: 10),
                            AnimatedSwitcher(
                              duration: const Duration(milliseconds: 450),
                              child: Text(
                                _financialTips[_tipIndex],
                                key: ValueKey(_tipIndex),
                                textAlign: TextAlign.center,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 14,
                                  height: 1.55,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ),
                            const SizedBox(height: 6),
                            const Text('TIPS KEUANGAN',
                                style: TextStyle(
                                  color: Color(0xFFFBBF24),
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 1.2,
                                )),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          const Positioned(
            left: 0,
            right: 0,
            bottom: 36,
            child: Center(child: _RepeatingDots()),
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
  const _RepeatingDots();

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
            final opacity = 0.3 + 0.7 * (0.5 + 0.5 * sin(t * 2 * pi));
            return Padding(
              padding: const EdgeInsets.symmetric(horizontal: 3),
              child: Opacity(
                opacity: opacity,
                child: Container(
                  width: 6,
                  height: 6,
                  decoration: const BoxDecoration(color: Color(0xFFFBBF24), shape: BoxShape.circle),
                ),
              ),
            );
          }),
        );
      },
    );
  }
}
