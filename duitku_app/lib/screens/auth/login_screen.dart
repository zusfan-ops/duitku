import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../providers/auth_provider.dart';
import '../../theme.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen>
    with SingleTickerProviderStateMixin {
  final _formKey = GlobalKey<FormState>();
  final _phoneCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  bool _obscure = true;

  late final AnimationController _animCtrl = AnimationController(
    vsync: this,
    duration: const Duration(seconds: 8),
  )..repeat(reverse: true);

  @override
  void dispose() {
    _animCtrl.dispose();
    _phoneCtrl.dispose();
    _passCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    final auth = context.read<AuthProvider>();
    final err = await auth.login(
      _phoneCtrl.text.trim(),
      _passCtrl.text,
    );
    if (!mounted) return;
    if (err != null) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(err)));
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: Stack(
        children: [
          // ── Ambient Parallax Background Animation ──────────────────
          Positioned.fill(
            child: _AnimatedParallaxBackground(animation: _animCtrl),
          ),

          // ── Foreground Content ─────────────────────────────────────
          Positioned.fill(
            child: SingleChildScrollView(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Header with modern gradient and logo
                  _HeaderSection(animation: _animCtrl),

                  // Features Highlight horizontal strip
                  const Padding(
                    padding: EdgeInsets.fromLTRB(16, 16, 16, 8),
                    child: _FeatureHighlights(),
                  ),

                  // Login Form Card
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
                    child: Container(
                      padding: const EdgeInsets.all(22),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.94),
                        borderRadius: BorderRadius.circular(24),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFF0F172A).withValues(alpha: 0.07),
                            blurRadius: 24,
                            offset: const Offset(0, 8),
                          ),
                          BoxShadow(
                            color: const Color(0xFF059669).withValues(alpha: 0.04),
                            blurRadius: 10,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Form(
                        key: _formKey,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            const Text(
                              'Masuk ke Akun Anda',
                              style: TextStyle(
                                color: AppColors.textPrimary,
                                fontSize: 17,
                                fontWeight: FontWeight.w800,
                                letterSpacing: -0.3,
                              ),
                            ),
                            const SizedBox(height: 4),
                            const Text(
                              'Silakan masukkan No. WhatsApp / Email & kata sandi Anda',
                              style: TextStyle(
                                color: AppColors.textSecondary,
                                fontSize: 12,
                              ),
                            ),
                            const SizedBox(height: 20),

                            // WhatsApp phone number or Email field
                            TextFormField(
                              controller: _phoneCtrl,
                              keyboardType: TextInputType.emailAddress,
                              autocorrect: false,
                              decoration: InputDecoration(
                                labelText: 'No. WhatsApp / Email',
                                hintText: '08xxxxxxxxxx atau email@anda.com',
                                prefixIcon: Container(
                                  margin: const EdgeInsets.only(right: 8),
                                  child: const Icon(Icons.person_pin_rounded, size: 20, color: AppColors.primary),
                                ),
                              ),
                              validator: (v) {
                                if (v == null || v.trim().isEmpty) {
                                  return 'No. WhatsApp atau Email wajib diisi';
                                }
                                final val = v.trim();
                                if (val.contains('@')) {
                                  if (!val.contains('.') || val.length < 5) {
                                    return 'Format email tidak valid';
                                  }
                                } else {
                                  if (val.length < 8) {
                                    return 'Nomor WhatsApp minimal 8 digit';
                                  }
                                }
                                return null;
                              },
                            ),
                            const SizedBox(height: 14),

                            // Password field
                            TextFormField(
                              controller: _passCtrl,
                              obscureText: _obscure,
                              decoration: InputDecoration(
                                labelText: 'Kata Sandi',
                                hintText: '••••••••',
                                prefixIcon: Container(
                                  margin: const EdgeInsets.only(right: 8),
                                  child: const Icon(Icons.lock_outline_rounded, size: 20, color: AppColors.primary),
                                ),
                                suffixIcon: IconButton(
                                  icon: Icon(
                                    _obscure ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                                    size: 20,
                                    color: AppColors.textMuted,
                                  ),
                                  onPressed: () => setState(() => _obscure = !_obscure),
                                ),
                              ),
                              validator: (v) => (v == null || v.isEmpty) ? 'Kata sandi wajib diisi' : null,
                              onFieldSubmitted: (_) => _submit(),
                            ),
                            const SizedBox(height: 22),

                            // Submit button
                            Container(
                              decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(14),
                                boxShadow: auth.busy ? null : AppColors.fabShadow,
                              ),
                              child: FilledButton(
                                onPressed: auth.busy ? null : _submit,
                                style: FilledButton.styleFrom(
                                  backgroundColor: AppColors.primary,
                                  minimumSize: const Size.fromHeight(50),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                ),
                                child: auth.busy
                                    ? const SizedBox(
                                        width: 22,
                                        height: 22,
                                        child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white),
                                      )
                                    : const Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Text(
                                            'Masuk Sekarang',
                                            style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: Colors.white),
                                          ),
                                          SizedBox(width: 6),
                                          Icon(Icons.arrow_forward_rounded, size: 18, color: Colors.white),
                                        ],
                                      ),
                              ),
                            ),
                            const SizedBox(height: 18),

                            // Register link
                            Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                const Text(
                                  'Belum punya akun?',
                                  style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                                ),
                                const SizedBox(width: 4),
                                GestureDetector(
                                  onTap: () {
                                    Navigator.push(
                                      context,
                                      MaterialPageRoute(builder: (_) => const RegisterScreen()),
                                    );
                                  },
                                  child: const Text(
                                    'Daftar Gratis',
                                    style: TextStyle(
                                      fontWeight: FontWeight.w800,
                                      color: AppColors.primary,
                                      fontSize: 13,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),

                  // ── FAQ Accordion Section ───────────────────────────
                  const Padding(
                    padding: EdgeInsets.fromLTRB(16, 8, 16, 36),
                    child: _FaqAccordionSection(),
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

// ── FAQ Accordion Section ──────────────────────────────────────
class _FaqAccordionSection extends StatefulWidget {
  const _FaqAccordionSection();

  @override
  State<_FaqAccordionSection> createState() => _FaqAccordionSectionState();
}

class _FaqAccordionSectionState extends State<_FaqAccordionSection> {
  int? _expandedIndex = 0; // Default first FAQ open

  static const _faqs = [
    (
      'Apakah DuitKu bisa digunakan offline?',
      'Ya, tentu saja! Anda tetap bisa mencatat pengeluaran, pemasukan, dan checklist belanja secara offline kapan pun. Data akan otomatis disinkronkan ke server saat terhubung internet.',
      Icons.wifi_off_rounded,
      [Color(0xFF059669), Color(0xFF10B981)],
      Color(0xFFECFDF5),
    ),
    (
      'Bagaimana keamanan data keuangan saya?',
      'Data keuangan Anda dienkripsi secara aman dan hanya dapat diakses melalui nomor WhatsApp dan kata sandi akun pribadi Anda. Kami tidak membagikan data kepada pihak ketiga.',
      Icons.security_rounded,
      [Color(0xFF1D4ED8), Color(0xFF3B82F6)],
      Color(0xFFEFF6FF),
    ),
    (
      'Apakah aplikasi DuitKu 100% gratis?',
      'Ya! Fitur-fitur esensial seperti Multi Dompet, Pencatatan Transaksi, Anggaran Budget, Daftar Belanja, dan Pengingat Tagihan dapat digunakan secara gratis.',
      Icons.card_giftcard_rounded,
      [Color(0xFF6D28D9), Color(0xFF8B5CF6)],
      Color(0xFFF5F3FF),
    ),
    (
      'Berapa banyak akun dompet yang bisa dicatat?',
      'Anda dapat menambahkan dompet tanpa batas, mulai dari rekening Bank (BCA, Mandiri, BRI, BNI), E-Wallet (GoPay, OVO, DANA, ShopeePay), hingga uang tunai/cash fisik.',
      Icons.account_balance_wallet_rounded,
      [Color(0xFFB45309), Color(0xFFF59E0B)],
      Color(0xFFFFFBEB),
    ),
    (
      'Bagaimana jika saya ganti perangkat / HP baru?',
      'Cukup masuk menggunakan Nomor WhatsApp dan kata sandi Anda di HP baru, seluruh riwayat transaksi, saldo dompet, dan catatan belanja otomatis tersinkronisasi kembali.',
      Icons.phonelink_setup_rounded,
      [Color(0xFFBE185D), Color(0xFFF43F5E)],
      Color(0xFFFFF1F2),
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: AppColors.primary.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(Icons.help_outline_rounded, size: 16, color: AppColors.primary),
            ),
            const SizedBox(width: 8),
            const Text(
              'Pertanyaan Sering Diajukan (FAQ)',
              style: TextStyle(
                fontSize: 14.5,
                fontWeight: FontWeight.w800,
                color: AppColors.textPrimary,
                letterSpacing: -0.2,
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        ...List.generate(_faqs.length, (i) {
          final (question, answer, icon, gradientColors, bgLight) = _faqs[i];
          final isExpanded = _expandedIndex == i;

          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: isExpanded ? gradientColors.first.withValues(alpha: 0.4) : const Color(0xFFE2E8F0),
                width: isExpanded ? 1.4 : 1.0,
              ),
              boxShadow: [
                BoxShadow(
                  color: isExpanded
                      ? gradientColors.last.withValues(alpha: 0.12)
                      : const Color(0xFF0F172A).withValues(alpha: 0.03),
                  blurRadius: 10,
                  offset: const Offset(0, 3),
                ),
              ],
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: Material(
                color: Colors.transparent,
                child: InkWell(
                  onTap: () {
                    setState(() {
                      _expandedIndex = isExpanded ? null : i;
                    });
                  },
                  child: Column(
                    children: [
                      // Header Row
                      Padding(
                        padding: const EdgeInsets.all(12),
                        child: Row(
                          children: [
                            Container(
                              width: 32,
                              height: 32,
                              decoration: BoxDecoration(
                                gradient: LinearGradient(
                                  colors: gradientColors,
                                  begin: Alignment.topLeft,
                                  end: Alignment.bottomRight,
                                ),
                                borderRadius: BorderRadius.circular(9),
                                boxShadow: [
                                  BoxShadow(
                                    color: gradientColors.last.withValues(alpha: 0.3),
                                    blurRadius: 6,
                                    offset: const Offset(0, 2),
                                  ),
                                ],
                              ),
                              child: Icon(icon, size: 16, color: Colors.white),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Text(
                                question,
                                style: TextStyle(
                                  fontSize: 12.5,
                                  fontWeight: FontWeight.w700,
                                  color: isExpanded ? gradientColors.first : AppColors.textPrimary,
                                  letterSpacing: -0.1,
                                ),
                              ),
                            ),
                            AnimatedRotation(
                              turns: isExpanded ? 0.5 : 0.0,
                              duration: const Duration(milliseconds: 200),
                              child: Icon(
                                Icons.keyboard_arrow_down_rounded,
                                size: 20,
                                color: isExpanded ? gradientColors.first : AppColors.textMuted,
                              ),
                            ),
                          ],
                        ),
                      ),

                      // Expandable Answer Body
                      AnimatedCrossFade(
                        firstChild: const SizedBox.shrink(),
                        secondChild: Container(
                          width: double.infinity,
                          padding: const EdgeInsets.fromLTRB(14, 0, 14, 14),
                          child: Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: bgLight,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: gradientColors.last.withValues(alpha: 0.2)),
                            ),
                            child: Text(
                              answer,
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.blueGrey.shade800,
                                height: 1.45,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ),
                        ),
                        crossFadeState: isExpanded ? CrossFadeState.showSecond : CrossFadeState.showFirst,
                        duration: const Duration(milliseconds: 220),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          );
        }),
      ],
    );
  }
}

// ── Animated Parallax Background ───────────────────────────────
class _AnimatedParallaxBackground extends StatelessWidget {
  final Animation<double> animation;
  const _AnimatedParallaxBackground({required this.animation});

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: animation,
      builder: (context, _) {
        final t = animation.value;
        final sinT = math.sin(t * math.pi);
        final cosT = math.cos(t * math.pi);

        return Stack(
          children: [
            // Background mesh dot grid
            Positioned.fill(
              child: CustomPaint(
                painter: _DotGridPainter(),
              ),
            ),

            // Top-right floating emerald glow orb
            Positioned(
              right: -50 + (sinT * 25),
              top: 180 + (cosT * 20),
              child: Container(
                width: 220,
                height: 220,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: RadialGradient(
                    colors: [
                      const Color(0xFF10B981).withValues(alpha: 0.16),
                      const Color(0xFF10B981).withValues(alpha: 0.0),
                    ],
                  ),
                ),
              ),
            ),

            // Middle-left warm amber/rose glow orb
            Positioned(
              left: -60 + (cosT * 20),
              top: 380 + (sinT * 30),
              child: Container(
                width: 240,
                height: 240,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: RadialGradient(
                    colors: [
                      const Color(0xFFF43F5E).withValues(alpha: 0.12),
                      const Color(0xFFF43F5E).withValues(alpha: 0.0),
                    ],
                  ),
                ),
              ),
            ),

            // Bottom-right indigo/cyan glow orb
            Positioned(
              right: -30 + (sinT * -20),
              bottom: 40 + (cosT * -25),
              child: Container(
                width: 260,
                height: 260,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: RadialGradient(
                    colors: [
                      const Color(0xFF3B82F6).withValues(alpha: 0.13),
                      const Color(0xFF3B82F6).withValues(alpha: 0.0),
                    ],
                  ),
                ),
              ),
            ),

            // Floating decorative fintech vector icons
            Positioned(
              left: 20 + (cosT * 12),
              top: 310 + (sinT * -16),
              child: _FloatingGlyph(
                icon: Icons.auto_awesome_rounded,
                size: 22,
                color: const Color(0xFF10B981).withValues(alpha: 0.35),
                rotation: t * 0.4,
              ),
            ),
            Positioned(
              right: 28 + (sinT * -14),
              top: 360 + (cosT * 18),
              child: _FloatingGlyph(
                icon: Icons.monetization_on_outlined,
                size: 24,
                color: const Color(0xFFF59E0B).withValues(alpha: 0.3),
                rotation: -t * 0.3,
              ),
            ),
            Positioned(
              left: 36 + (sinT * 15),
              bottom: 120 + (cosT * -20),
              child: _FloatingGlyph(
                icon: Icons.savings_outlined,
                size: 26,
                color: const Color(0xFF6366F1).withValues(alpha: 0.28),
                rotation: t * 0.2,
              ),
            ),
            Positioned(
              right: 40 + (cosT * 10),
              bottom: 80 + (sinT * 16),
              child: _FloatingGlyph(
                icon: Icons.trending_up_rounded,
                size: 24,
                color: const Color(0xFF10B981).withValues(alpha: 0.32),
                rotation: sinT * 0.15,
              ),
            ),
          ],
        );
      },
    );
  }
}

class _FloatingGlyph extends StatelessWidget {
  final IconData icon;
  final double size;
  final Color color;
  final double rotation;

  const _FloatingGlyph({
    required this.icon,
    required this.size,
    required this.color,
    required this.rotation,
  });

  @override
  Widget build(BuildContext context) {
    return Transform.rotate(
      angle: rotation,
      child: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.08),
          shape: BoxShape.circle,
        ),
        child: Icon(icon, size: size, color: color),
      ),
    );
  }
}

class _DotGridPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFF94A3B8).withValues(alpha: 0.12)
      ..style = PaintingStyle.fill;

    const step = 28.0;
    for (double x = 14; x < size.width; x += step) {
      for (double y = 14; y < size.height; y += step) {
        canvas.drawCircle(Offset(x, y), 1.2, paint);
      }
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

// ── Modern Header Section ──────────────────────────────────────
class _HeaderSection extends StatelessWidget {
  final Animation<double> animation;
  const _HeaderSection({required this.animation});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: EdgeInsets.fromLTRB(
        24,
        MediaQuery.of(context).padding.top + 20,
        24,
        28,
      ),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF064E3B), Color(0xFF047857), Color(0xFF059669)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: const BorderRadius.vertical(bottom: Radius.circular(32)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF059669).withValues(alpha: 0.25),
            blurRadius: 20,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Column(
        children: [
          AnimatedBuilder(
            animation: animation,
            builder: (context, _) {
              final scale = 1.0 + (math.sin(animation.value * math.pi) * 0.03);
              return Transform.scale(
                scale: scale,
                child: Container(
                  padding: const EdgeInsets.all(4),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: const [
                      BoxShadow(color: Color(0x26000000), blurRadius: 14, offset: Offset(0, 4)),
                    ],
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(16),
                    child: Image.asset(
                      'assets/icon/logo.png',
                      width: 60,
                      height: 60,
                    ),
                  ),
                ),
              );
            },
          ),
          const SizedBox(height: 12),
          const Text(
            'DuitKu',
            style: TextStyle(
              color: Colors.white,
              fontSize: 26,
              fontWeight: FontWeight.w900,
              letterSpacing: -0.5,
            ),
          ),
          const SizedBox(height: 4),
          const Text(
            'Aplikasi cerdas untuk catat keuangan,\nbudgeting, hutang, dan belanjaanmu.',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: Colors.white70,
              fontSize: 12.5,
              height: 1.35,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }
}

// ── Horizontal Feature Highlights ──────────────────────────────
class _FeatureHighlights extends StatelessWidget {
  const _FeatureHighlights();

  static const _highlights = [
    (
      Icons.receipt_long_rounded,
      'Catat Transaksi',
      'Pemasukan & pengeluaran rapi',
      [Color(0xFF1D4ED8), Color(0xFF3B82F6)],
    ),
    (
      Icons.shopping_bag_rounded,
      'Daftar Belanja',
      'Checklist & budget belanja',
      [Color(0xFFBE185D), Color(0xFFF43F5E)],
    ),
    (
      Icons.pie_chart_rounded,
      'Atur Budget',
      'Kontrol pengeluaran bulanan',
      [Color(0xFF6D28D9), Color(0xFF8B5CF6)],
    ),
    (
      Icons.account_balance_wallet_rounded,
      'Multi Dompet',
      'Bank, e-wallet, dan tunai',
      [Color(0xFFB45309), Color(0xFFF59E0B)],
    ),
    (
      Icons.handshake_rounded,
      'Hutang & Piutang',
      'Pengingat jatuh tempo tempo',
      [Color(0xFF0E7490), Color(0xFF06B6D4)],
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 4),
          child: Row(
            children: [
              Container(
                width: 6,
                height: 6,
                decoration: const BoxDecoration(
                  color: AppColors.primary,
                  shape: BoxShape.circle,
                ),
              ),
              const SizedBox(width: 6),
              const Text(
                'FITUR UNGGULAN DUITKU',
                style: TextStyle(
                  fontSize: 10.5,
                  fontWeight: FontWeight.w800,
                  letterSpacing: 0.8,
                  color: AppColors.textMuted,
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 6),
        SizedBox(
          height: 82,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: _highlights.length,
            separatorBuilder: (_, _) => const SizedBox(width: 10),
            itemBuilder: (context, i) {
              final (icon, title, desc, colors) = _highlights[i];
              return Container(
                width: 170,
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: colors,
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: colors.last.withValues(alpha: 0.25),
                      blurRadius: 8,
                      offset: const Offset(0, 3),
                    ),
                  ],
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(16),
                  child: Stack(
                    children: [
                      Positioned(
                        right: -8,
                        bottom: -8,
                        child: Transform.rotate(
                          angle: -0.15,
                          child: Icon(icon, size: 50, color: Colors.white.withValues(alpha: 0.18)),
                        ),
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Container(
                            width: 26,
                            height: 26,
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.22),
                              borderRadius: BorderRadius.circular(7),
                            ),
                            child: Icon(icon, size: 14, color: Colors.white),
                          ),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(
                                title,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontSize: 11.5,
                                  fontWeight: FontWeight.w800,
                                  color: Colors.white,
                                ),
                              ),
                              Text(
                                desc,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(
                                  fontSize: 9.5,
                                  fontWeight: FontWeight.w500,
                                  color: Colors.white.withValues(alpha: 0.8),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}
