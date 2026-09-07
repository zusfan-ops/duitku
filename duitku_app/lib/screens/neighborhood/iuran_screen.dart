import 'package:flutter/material.dart';

import '../../models/iuran.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class IuranScreen extends StatefulWidget {
  const IuranScreen({super.key});

  @override
  State<IuranScreen> createState() => _IuranScreenState();
}

class _IuranScreenState extends State<IuranScreen> {
  bool _loading = true;
  IuranData? _data;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final data = await ApiService.instance.iuran();
      if (!mounted) return;
      setState(() {
        _data = data;
        _loading = false;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.message;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _error = 'Terjadi kesalahan saat memuat data.';
        _loading = false;
      });
    }
  }

  Future<void> _openConfigSheet() async {
    final data = _data;
    if (data == null) return;
    final config = data.config;
    final saved = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => _ConfigSheet(initialConfig: config),
    );
    if (saved == null) return;
    try {
      await ApiService.instance.storeIuranConfig(
        periodType: saved['period_type'] as String,
        amount: (saved['amount'] as num).toDouble(),
        description: saved['description'] as String,
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Konfigurasi iuran berhasil diperbarui.')),
      );
      _load();
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  Future<void> _openPaySheet(IuranPayment p) async {
    final saved = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => _PaySheet(payment: p),
    );
    if (saved == null) return;
    try {
      await ApiService.instance.payIuran(
        paymentId: p.id,
        amount: (saved['amount'] as num).toDouble(),
        paidVia: saved['paid_via'] as String,
        notes: saved['notes'] as String?,
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pembayaran iuran berhasil dicatat.')),
      );
      _load();
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(title: const Text('Iuran & Kas RT')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null && _data == null
              ? _ErrorView(message: _error!, onRetry: _load)
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView(
                    padding: const EdgeInsets.fromLTRB(14, 6, 14, 110),
                    children: [
                      if (_data == null || !_data!.joined) _NotJoinedView(onRetry: _load)
                      else ...[
                        _buildHero(),
                        if (_data!.summary != null) _buildSummaryGrid(),
                        _buildPaymentCard(),
                      ],
                    ],
                  ),
                ),
    );
  }

  Widget _buildHero() {
    final data = _data!;
    final config = data.config;
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF0F766E), Color(0xFF0D9488), Color(0xFF14B8A6)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(24),
        boxShadow: const [
          BoxShadow(color: Color(0x470D9488), blurRadius: 24, offset: Offset(0, 10)),
        ],
      ),
      child: Stack(
        children: [
          Positioned(
            right: -5,
            bottom: -20,
            child: Text('💰',
                style: TextStyle(fontSize: 90, color: Colors.white.withValues(alpha: 0.12))),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Text('🏛️ Iuran Warga',
                        style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: Colors.white)),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      border: Border.all(color: Colors.white.withValues(alpha: 0.35)),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text('Periode: ${data.periodMonth}',
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Colors.white)),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Text(
                config != null ? Fmt.money(config.amount) : '—',
                style: const TextStyle(fontSize: 30, fontWeight: FontWeight.w900, letterSpacing: -0.5, color: Colors.white),
              ),
              const SizedBox(height: 4),
              Text(
                config != null ? config.description : (data.canManage ? 'Iuran belum diatur pengurus RT' : 'Menunggu pengurus RT mengatur nominal iuran'),
                style: TextStyle(fontSize: 12.5, color: Colors.white.withValues(alpha: 0.9)),
              ),
              if (data.canManage) ...[
                const SizedBox(height: 14),
                OutlinedButton.icon(
                  onPressed: _openConfigSheet,
                  style: OutlinedButton.styleFrom(
                    foregroundColor: Colors.white,
                    side: BorderSide(color: Colors.white.withValues(alpha: 0.4)),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  icon: const Icon(Icons.settings_rounded, size: 18),
                  label: const Text('Atur Nominal Iuran',
                      style: TextStyle(fontWeight: FontWeight.w800)),
                ),
              ],
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryGrid() {
    final s = _data!.summary!;
    return Padding(
      padding: const EdgeInsets.only(top: 16, bottom: 18),
      child: Row(
        children: [
          Expanded(
            child: _SummaryCard(
              value: Fmt.money0(s.totalPaid),
              label: 'Terkumpul ${s.paidCount}/${s.totalWarga} warga',
              valueColor: const Color(0xFF059669),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: _SummaryCard(
              value: Fmt.money0(s.totalArrears),
              label: 'Total Tunggakan',
              valueColor: const Color(0xFFEF4444),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: _SummaryCard(
              value: '${s.unpaidCount}',
              label: 'Belum Bayar',
              valueColor: const Color(0xFFD97706),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentCard() {
    final data = _data!;
    final config = data.config;
    final payments = data.payments;
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Text('📋', style: TextStyle(fontSize: 16)),
              SizedBox(width: 8),
              Text('Pembayaran Iuran Periode Ini',
                  style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            ],
          ),
          const SizedBox(height: 4),
          if (config == null)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 18),
              child: Center(
                child: Text(
                  data.canManage
                      ? 'Belum ada konfigurasi iuran.\nKlik "Atur Nominal Iuran" untuk memulai.'
                      : 'Menunggu pengurus RT mengatur nominal iuran.',
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 12.5, color: AppColors.textSecondary, height: 1.5),
                ),
              ),
            )
          else if (payments.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 18),
              child: Center(
                child: Text('Belum ada catatan pembayaran.',
                    style: TextStyle(fontSize: 12.5, color: AppColors.textSecondary)),
              ),
            )
          else
            ...payments.map((p) => _PaymentRow(
                  payment: p,
                  canManage: data.canManage,
                  onPay: () => _openPaySheet(p),
                )),
        ],
      ),
    );
  }
}

class _ConfigSheet extends StatefulWidget {
  final IuranConfig? initialConfig;
  const _ConfigSheet({this.initialConfig});

  @override
  State<_ConfigSheet> createState() => _ConfigSheetState();
}

class _ConfigSheetState extends State<_ConfigSheet> {
  late final TextEditingController _amountCtrl;
  late final TextEditingController _descCtrl;
  late String _period = widget.initialConfig?.periodType ?? 'monthly';

  @override
  void initState() {
    super.initState();
    _amountCtrl = TextEditingController(
      text: widget.initialConfig != null ? Fmt.money0(widget.initialConfig!.amount) : '',
    );
    _descCtrl = TextEditingController(
      text: widget.initialConfig?.description ?? 'Iuran Warga Bulanan',
    );
  }

  @override
  void dispose() {
    _amountCtrl.dispose();
    _descCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: AppColors.border,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 12),
            const Text('Atur Iuran Warga',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            const SizedBox(height: 16),
            DropdownButtonFormField<String>(
              initialValue: _period,
              decoration: const InputDecoration(labelText: 'JENIS PERIODE'),
              items: const [
                DropdownMenuItem(value: 'monthly', child: Text('Bulanan')),
                DropdownMenuItem(value: 'weekly', child: Text('Mingguan')),
                DropdownMenuItem(value: 'yearly', child: Text('Tahunan')),
              ],
              onChanged: (v) => setState(() => _period = v!),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _amountCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'NOMINAL IURAN (RP)', hintText: '50.000', prefixText: 'Rp '),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _descCtrl,
              decoration: const InputDecoration(labelText: 'DESKRIPSI'),
            ),
            const SizedBox(height: 20),
            FilledButton(
              onPressed: () {
                final amount = double.tryParse(Fmt.parseAmount(_amountCtrl.text)) ?? 0;
                if (amount <= 0) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Nominal iuran harus lebih dari Rp 0.')),
                  );
                  return;
                }
                Navigator.pop(context, {
                  'period_type': _period,
                  'amount': amount,
                  'description': _descCtrl.text.trim(),
                });
              },
              child: const Text('Simpan'),
            ),
          ],
        ),
      ),
    );
  }
}

class _PaySheet extends StatefulWidget {
  final IuranPayment payment;
  const _PaySheet({required this.payment});

  @override
  State<_PaySheet> createState() => _PaySheetState();
}

class _PaySheetState extends State<_PaySheet> {
  late final TextEditingController _amountCtrl;
  final TextEditingController _notesCtrl = TextEditingController();
  String _via = 'cash';

  double get _arrears =>
      (widget.payment.amountExpected - widget.payment.amountPaid).clamp(0.0, double.infinity);

  @override
  void initState() {
    super.initState();
    _amountCtrl = TextEditingController();
  }

  @override
  void dispose() {
    _amountCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: AppColors.border,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 12),
            const Text('Catat Pembayaran',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            const SizedBox(height: 12),
            Text('${widget.payment.residentName} — Tunggakan ${Fmt.money(_arrears)}',
                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: AppColors.textPrimary)),
            const SizedBox(height: 16),
            TextField(
              controller: _amountCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'NOMINAL BAYAR (RP)', hintText: '50.000', prefixText: 'Rp '),
            ),
            const SizedBox(height: 14),
            DropdownButtonFormField<String>(
              initialValue: _via,
              decoration: const InputDecoration(labelText: 'METODE'),
              items: const [
                DropdownMenuItem(value: 'cash', child: Text('Tunai')),
                DropdownMenuItem(value: 'transfer', child: Text('Transfer')),
                DropdownMenuItem(value: 'cashless', child: Text('E-Wallet')),
              ],
              onChanged: (v) => setState(() => _via = v!),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _notesCtrl,
              decoration: const InputDecoration(labelText: 'CATATAN (OPSIONAL)', hintText: 'Sudah dibayar tunai'),
            ),
            const SizedBox(height: 20),
            FilledButton(
              onPressed: () {
                final amount = double.tryParse(Fmt.parseAmount(_amountCtrl.text)) ?? 0;
                if (amount <= 0 || amount > _arrears + 0.01) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Nominal pembayaran tidak valid.')),
                  );
                  return;
                }
                Navigator.pop(context, {
                  'amount': amount,
                  'paid_via': _via,
                  'notes': _notesCtrl.text.trim(),
                });
              },
              child: const Text('Simpan'),
            ),
          ],
        ),
      ),
    );
  }
}

class _PaymentRow extends StatelessWidget {
  final IuranPayment payment;
  final bool canManage;
  final VoidCallback onPay;
  const _PaymentRow({required this.payment, required this.canManage, required this.onPay});

  String get _statusLabel {
    if (payment.isPaid) return 'Lunas';
    if (payment.isPartial) return 'Sebagian';
    return 'Belum';
  }

  Color get _badgeBg => payment.isPaid
      ? const Color(0xFFD1FAE5)
      : payment.isPartial
          ? const Color(0xFFFEF3C7)
          : const Color(0xFFFEE2E2);

  Color get _badgeFg => payment.isPaid
      ? const Color(0xFF047857)
      : payment.isPartial
          ? const Color(0xFFB45309)
          : const Color(0xFFB91C1C);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 11),
      child: Row(
        children: [
          Container(
            width: 38,
            height: 38,
            decoration: const BoxDecoration(
              gradient: LinearGradient(colors: [Color(0xFF0D9488), Color(0xFF14B8A6)]),
              shape: BoxShape.circle,
            ),
            alignment: Alignment.center,
            child: Text(
              (payment.residentName.isNotEmpty ? payment.residentName[0] : '?').toUpperCase(),
              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: Colors.white),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Flexible(
                      child: Text(payment.residentName,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                    ),
                    if (payment.houseNumber != null && payment.houseNumber!.isNotEmpty) ...[
                      const SizedBox(width: 4),
                      Text('• No. ${payment.houseNumber}',
                          style: const TextStyle(fontSize: 10, color: AppColors.textSecondary)),
                    ],
                  ],
                ),
                const SizedBox(height: 2),
                Text('Terbayar: ${Fmt.money(payment.amountPaid)} / ${Fmt.money(payment.amountExpected)}',
                    style: const TextStyle(fontSize: 10.5, color: AppColors.textSecondary)),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(color: _badgeBg, borderRadius: BorderRadius.circular(20)),
            child: Text(_statusLabel,
                style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: _badgeFg)),
          ),
          if (canManage && !payment.isPaid) ...[
            const SizedBox(width: 8),
            FilledButton(
              onPressed: onPay,
              style: FilledButton.styleFrom(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                minimumSize: const Size(0, 32),
                backgroundColor: AppColors.primary,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                textStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800),
              ),
              child: const Text('Catat'),
            ),
          ],
        ],
      ),
    );
  }
}

class _SummaryCard extends StatelessWidget {
  final String value;
  final String label;
  final Color valueColor;
  const _SummaryCard({required this.value, required this.label, required this.valueColor});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 6),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        children: [
          Text(value,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: valueColor)),
          const SizedBox(height: 3),
          Text(label,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
        ],
      ),
    );
  }
}

class _NotJoinedView extends StatelessWidget {
  final VoidCallback onRetry;
  const _NotJoinedView({required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        const SizedBox(height: 60),
        const Icon(Icons.holiday_village_outlined, size: 46, color: AppColors.textMuted),
        const SizedBox(height: 10),
        const Text('Iuran & Kas RT',
            style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
        const SizedBox(height: 6),
        const Padding(
          padding: EdgeInsets.symmetric(horizontal: 24),
          child: Text('Anda belum terhubung ke komunitas RT mana pun.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 12.5, color: AppColors.textSecondary)),
        ),
        const SizedBox(height: 14),
        OutlinedButton.icon(
          onPressed: onRetry,
          icon: const Icon(Icons.refresh, size: 18),
          label: const Text('Muat Ulang'),
        ),
      ],
    );
  }
}

class _ErrorView extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;
  const _ErrorView({required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.cloud_off_rounded, size: 44, color: AppColors.textMuted.withValues(alpha: 0.7)),
          const SizedBox(height: 12),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Text(message,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
          ),
          const SizedBox(height: 14),
          OutlinedButton.icon(
            onPressed: onRetry,
            icon: const Icon(Icons.refresh, size: 18),
            label: const Text('Coba Lagi'),
          ),
        ],
      ),
    );
  }
}