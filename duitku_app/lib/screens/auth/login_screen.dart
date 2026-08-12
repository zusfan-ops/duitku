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

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  bool _obscure = true;

  @override
  void dispose() {
    _emailCtrl.dispose();
    _passCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    final auth = context.read<AuthProvider>();
    final err = await auth.login(
      _emailCtrl.text.trim(),
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
      backgroundColor: AppColors.bg,
      body: SafeArea(
        bottom: false,
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              _Hero(),
              const Padding(
                padding: EdgeInsets.fromLTRB(20, 18, 20, 4),
                child: _BenefitsGrid(),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(24, 18, 24, 28),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      const Text(
                        'Masuk ke akun Anda',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: AppColors.textPrimary,
                          fontSize: 16,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _emailCtrl,
                        keyboardType: TextInputType.emailAddress,
                        autocorrect: false,
                        decoration: const InputDecoration(labelText: 'Email', prefixIcon: Icon(Icons.mail_outline)),
                        validator: (v) =>
                            (v == null || !v.contains('@')) ? 'Email tidak valid' : null,
                      ),
                      const SizedBox(height: 14),
                      TextFormField(
                        controller: _passCtrl,
                        obscureText: _obscure,
                        decoration: InputDecoration(
                          labelText: 'Password',
                          prefixIcon: const Icon(Icons.lock_outline),
                          suffixIcon: IconButton(
                            icon: Icon(_obscure ? Icons.visibility_off : Icons.visibility),
                            onPressed: () => setState(() => _obscure = !_obscure),
                          ),
                        ),
                        validator: (v) => (v == null || v.isEmpty) ? 'Password wajib diisi' : null,
                        onFieldSubmitted: (_) => _submit(),
                      ),
                      const SizedBox(height: 20),
                      FilledButton(
                        onPressed: auth.busy ? null : _submit,
                        child: auth.busy
                            ? const SizedBox(
                                width: 22, height: 22,
                                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                              )
                            : const Text('Masuk'),
                      ),
                      const SizedBox(height: 14),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Text('Belum punya akun?',
                              style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
                          TextButton(
                            onPressed: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const RegisterScreen()),
                              );
                            },
                            child: const Text('Daftar Gratis',
                                style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.primary)),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Hero header ────────────────────────────────────────────────
class _Hero extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(24, 26, 24, 26),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [Color(0xFF043D22), Color(0xFF076836), Color(0xFF0AA956)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Column(
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(18),
            child: Image.asset(
              'assets/icon/logo.png',
              width: 72,
              height: 72,
            ),
          ),
          const SizedBox(height: 10),
          const Text(
            'DuitKu',
            style: TextStyle(color: Colors.white, fontSize: 26, fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 4),
          const Text(
            'Satu aplikasi buat catat pemasukan,\npengeluaran, dan hutang-piutangmu.',
            textAlign: TextAlign.center,
            style: TextStyle(color: Colors.white70, fontSize: 12.5, height: 1.35),
          ),
        ],
      ),
    );
  }
}

// ── Benefits grid ─────────────────────────────────────────────
class _BenefitsGrid extends StatelessWidget {
  const _BenefitsGrid();

  static const _items = [
    (
      Icons.receipt_long_rounded,
      'Catat Transaksi',
      'Otomatis kehitung, rapi',
      [Color(0xFF1D4ED8), Color(0xFF3B82F6)],
    ),
    (
      Icons.pie_chart_rounded,
      'Atur Budget',
      'Biar pengeluaran gak boncos',
      [Color(0xFF7C3AED), Color(0xFFC084FC)],
    ),
    (
      Icons.account_balance_wallet_rounded,
      'Multi Dompet',
      'Bank, e-wallet & tunai jadi satu',
      [Color(0xFFB45309), Color(0xFFFBBF24)],
    ),
    (
      Icons.handshake_rounded,
      'Hutang & Piutang',
      'Jangan sampai lupa nagih',
      [Color(0xFFBE123C), Color(0xFFFB7185)],
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Kenapa daftar di DuitKu?',
          style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
        ),
        const SizedBox(height: 12),
        for (var i = 0; i < _items.length; i += 2)
          Padding(
            padding: EdgeInsets.only(bottom: i + 2 < _items.length ? 12 : 0),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(child: _BenefitTile(item: _items[i])),
                const SizedBox(width: 12),
                Expanded(child: _BenefitTile(item: _items[i + 1])),
              ],
            ),
          ),
      ],
    );
  }
}

class _BenefitTile extends StatelessWidget {
  final (IconData, String, String, List<Color>) item;
  const _BenefitTile({required this.item});

  @override
  Widget build(BuildContext context) {
    final (icon, title, desc, colors) = item;
    return Container(
      height: 128,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        gradient: LinearGradient(colors: colors, begin: Alignment.topLeft, end: Alignment.bottomRight),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(color: colors.last.withValues(alpha: .35), blurRadius: 14, offset: const Offset(0, 6)),
        ],
      ),
      child: Stack(
        clipBehavior: Clip.none,
        children: [
          Positioned(
            right: -14,
            top: -14,
            child: Icon(icon, size: 68, color: Colors.white.withValues(alpha: .16)),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 30,
                height: 30,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: .22),
                  borderRadius: BorderRadius.circular(9),
                ),
                child: Icon(icon, size: 16, color: Colors.white),
              ),
              const SizedBox(height: 10),
              Text(title,
                  style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: Colors.white)),
              const SizedBox(height: 3),
              Text(
                desc,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(fontSize: 10.5, color: Colors.white.withValues(alpha: .85), height: 1.3),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
