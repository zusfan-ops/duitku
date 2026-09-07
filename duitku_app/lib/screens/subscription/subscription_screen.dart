import 'package:flutter/material.dart';

import '../../models/subscription.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class SubscriptionScreen extends StatefulWidget {
  const SubscriptionScreen({super.key});

  @override
  State<SubscriptionScreen> createState() => _SubscriptionScreenState();
}

class _SubscriptionScreenState extends State<SubscriptionScreen> {
  bool _loading = true;
  SubscriptionData? _data;
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
      final data = await ApiService.instance.subscriptions();
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

  Future<void> _snack(String msg) async {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
  }

  Future<void> _openCreateSheet() async {
    final saved = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => const _SubscriptionSheet(),
    );
    if (saved == null) return;
    try {
      await ApiService.instance.storeSubscription(
        name: saved['name'] as String,
        icon: (saved['icon'] as String?) ?? '🔁',
        category: (saved['category'] as String?) ?? 'Hiburan',
        amount: (saved['amount'] as num).toDouble(),
        billingCycle: (saved['billing_cycle'] as String?) ?? 'monthly',
        nextBillingDate: saved['next_billing_date'] as String?,
        notes: saved['notes'] as String?,
        isWaste: saved['is_waste'] == true,
      );
      await _snack('Langganan berhasil ditambahkan.');
      _load();
    } on ApiException catch (e) {
      await _snack(e.message);
    }
  }

  Future<void> _pay(Subscription s) async {
    try {
      await ApiService.instance.paySubscription(s.id);
      await _snack('${s.name} ditandai sudah dibayar.');
      _load();
    } on ApiException catch (e) {
      await _snack(e.message);
    }
  }

  Future<void> _toggleStatus(Subscription s) async {
    try {
      await ApiService.instance.updateSubscription(
        s.id,
        {'status': s.isActive ? 'paused' : 'active'},
      );
      await _snack(s.isActive ? '${s.name} dijeda.' : '${s.name} diaktifkan kembali.');
      _load();
    } on ApiException catch (e) {
      await _snack(e.message);
    }
  }

  Future<void> _toggleWaste(Subscription s) async {
    try {
      await ApiService.instance.updateSubscription(s.id, {'is_waste': s.isWaste ? 0 : 1});
      await _snack(s.isWaste ? 'Bukan lagi langganan tersia-sia.' : 'Ditandai sebagai langganan tersia-sia.');
      _load();
    } on ApiException catch (e) {
      await _snack(e.message);
    }
  }

  Future<void> _delete(Subscription s) async {
    try {
      await ApiService.instance.deleteSubscription(s.id);
      await _snack('${s.name} dihapus.');
      _load();
    } on ApiException catch (e) {
      await _snack(e.message);
    }
  }

  void _confirmDelete(Subscription s) {
    showDialog<void>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.card,
        title: const Text('Hapus Langganan?',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
        content: Text('Langganan "${s.name}" akan dihapus permanen.',
            style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              _delete(s);
            },
            child: const Text('Hapus', style: TextStyle(color: AppColors.expense)),
          ),
        ],
      ),
    );
  }

  void _openMenu(Subscription s) {
    showModalBottomSheet<void>(
      context: context,
      useRootNavigator: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const SizedBox(height: 12),
            Text(s.name,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: AppColors.textPrimary)),
            Text(Fmt.money(s.amount) + ' / ' + _cycleLabel(s.billingCycle),
                style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
            const SizedBox(height: 8),
            const Divider(height: 1, color: AppColors.border),
            ListTile(
              leading: const Icon(Icons.check_circle_outline, color: AppColors.primary),
              title: const Text('Tandai Sudah Dibayar', style: TextStyle(fontSize: 13.5)),
              onTap: () {
                Navigator.pop(ctx);
                _pay(s);
              },
            ),
            ListTile(
              leading: Icon(Icons.pause_circle_outline,
                  color: s.isActive ? const Color(0xFFD97706) : AppColors.primary),
              title: Text(s.isActive ? 'Jeda Langganan' : 'Aktifkan Kembali',
                  style: const TextStyle(fontSize: 13.5)),
              onTap: () {
                Navigator.pop(ctx);
                _toggleStatus(s);
              },
            ),
            ListTile(
              leading: Icon(Icons.flag_outlined,
                  color: s.isWaste ? AppColors.primary : const Color(0xFFD97706)),
              title: Text(s.isWaste ? 'Ingatkan Untuk Dipakai' : 'Tandai Tersia-sia',
                  style: const TextStyle(fontSize: 13.5)),
              onTap: () {
                Navigator.pop(ctx);
                _toggleWaste(s);
              },
            ),
            const Divider(height: 1, color: AppColors.border),
            ListTile(
              leading: const Icon(Icons.delete_outline, color: AppColors.expense),
              title: const Text('Hapus Langganan',
                  style: TextStyle(fontSize: 13.5, color: AppColors.expense)),
              onTap: () {
                Navigator.pop(ctx);
                _confirmDelete(s);
              },
            ),
          ],
        ),
      ),
    );
  }

  String _cycleLabel(String c) => c == 'weekly'
      ? 'minggu'
      : c == 'yearly'
          ? 'tahun'
          : 'bulan';

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Langganan'),
        actions: [
          IconButton(
            onPressed: _openCreateSheet,
            tooltip: 'Tambah langganan',
            icon: const Icon(Icons.add_circle_outline),
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null && _data == null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.cloud_off_rounded, size: 44, color: AppColors.textMuted.withValues(alpha: 0.7)),
                      const SizedBox(height: 12),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 32),
                        child: Text(_error!,
                            textAlign: TextAlign.center,
                            style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                      ),
                      const SizedBox(height: 14),
                      OutlinedButton.icon(
                        onPressed: _load,
                        icon: const Icon(Icons.refresh, size: 18),
                        label: const Text('Coba Lagi'),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView(
                    padding: const EdgeInsets.fromLTRB(14, 6, 14, 110),
                    children: [
                      _buildHero(),
                      if (_data!.upcoming.isNotEmpty) _buildUpcoming(),
                      _buildList(),
                    ],
                  ),
                ),
    );
  }

  Widget _buildHero() {
    final s = _data!.summary;
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF2563EB), Color(0xFF3B82F6), Color(0xFF60A5FA)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(24),
        boxShadow: const [
          BoxShadow(color: Color(0x473B82F6), blurRadius: 24, offset: Offset(0, 10)),
        ],
      ),
      child: Stack(
        children: [
          Positioned(
            right: -5,
            bottom: -20,
            child: Text('🔁',
                style: TextStyle(fontSize: 90, color: Colors.white.withValues(alpha: 0.12))),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Text('🧾 Langganan Tracker',
                    style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: Colors.white)),
              ),
              const SizedBox(height: 10),
              Text(Fmt.money(s.totalMonthly) + '/bln',
                  style: const TextStyle(fontSize: 28, fontWeight: FontWeight.w900, letterSpacing: -0.5, color: Colors.white)),
              const SizedBox(height: 4),
              Text(
                'Total ${s.totalCount} langganan • ${s.activeCount} aktif • ~${Fmt.money0(s.totalYearly)}/thn',
                style: TextStyle(fontSize: 12.5, color: Colors.white.withValues(alpha: 0.9)),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildUpcoming() {
    return Container(
      margin: const EdgeInsets.only(top: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFEFF6FF),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFBFDBFE)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Text('⏰', style: TextStyle(fontSize: 16)),
              SizedBox(width: 8),
              Text('Tagihan Mendatang',
                  style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            ],
          ),
          const SizedBox(height: 4),
          ..._data!.upcoming.map((s) => Padding(
                padding: const EdgeInsets.symmetric(vertical: 7),
                child: Row(
                  children: [
                    Text(s.icon ?? '🔁', style: const TextStyle(fontSize: 18)),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(s.name,
                              style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                          Text('${Fmt.dateDay(s.nextBillingDate)} • ${Fmt.money(s.amount)}',
                              style: const TextStyle(fontSize: 10.5, color: AppColors.textSecondary)),
                        ],
                      ),
                    ),
                    FilledButton(
                      onPressed: () => _pay(s),
                      style: FilledButton.styleFrom(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        minimumSize: const Size(0, 32),
                        backgroundColor: AppColors.primary,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        textStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800),
                      ),
                      child: const Text('Bayar'),
                    ),
                  ],
                ),
              )),
        ],
      ),
    );
  }

  Widget _buildList() {
    final subs = _data!.subscriptions;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(4, 18, 4, 8),
          child: Text(subs.isEmpty ? 'Semua Langganan' : 'Semua Langganan (${subs.length})',
              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
        ),
        if (subs.isEmpty)
          Container(
            padding: const EdgeInsets.symmetric(vertical: 30, horizontal: 16),
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: AppColors.border),
            ),
            child: const Column(
              children: [
                Icon(Icons.subscriptions_outlined, size: 40, color: AppColors.textMuted),
                SizedBox(height: 10),
                Text('Belum ada langganan.',
                    style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                SizedBox(height: 4),
                Text('Klik ikon + di kanan atas untuk menambahkan.',
                    style: TextStyle(fontSize: 12, color: AppColors.textSecondary)),
              ],
            ),
          )
        else
          ...subs.map((s) => _SubscriptionTile(
                subscription: s,
                onPay: () => _pay(s),
                onMenu: () => _openMenu(s),
              )),
      ],
    );
  }
}

class _SubscriptionTile extends StatelessWidget {
  final Subscription subscription;
  final VoidCallback onPay;
  final VoidCallback onMenu;
  const _SubscriptionTile({required this.subscription, required this.onPay, required this.onMenu});

  @override
  Widget build(BuildContext context) {
    final s = subscription;
    final active = s.isActive;
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: active ? const Color(0xFFEFF6FF) : const Color(0xFFF3F4F6),
              borderRadius: BorderRadius.circular(13),
            ),
            alignment: Alignment.center,
            child: Text(s.icon ?? '🔁', style: const TextStyle(fontSize: 21)),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Flexible(
                      child: Text(s.name,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                    ),
                    const SizedBox(width: 6),
                    if (s.isWaste)
                      const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        child: Text('🤦',
                            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFFD97706))),
                      ),
                    if (!active)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFEF3C7),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Text('Dijeda',
                            style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w800, color: Color(0xFFB45309))),
                      ),
                  ],
                ),
                const SizedBox(height: 2),
                Text('${s.category} • ${s.categoryName ?? ''}'.trimRight(),
                    style: const TextStyle(fontSize: 10.5, color: AppColors.textSecondary)),
                const SizedBox(height: 2),
                Row(
                  children: [
                    Text(Fmt.money(s.amount) + ' / ${s.billingCycle == 'weekly' ? 'minggu' : s.billingCycle == 'yearly' ? 'tahun' : 'bulan'}',
                        style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: active ? AppColors.primary : AppColors.textMuted)),
                    if (s.nextBillingDate.isNotEmpty) ...[
                      const SizedBox(width: 6),
                      Text('• jatuh ${Fmt.dateDay(s.nextBillingDate)}',
                          style: const TextStyle(fontSize: 10.5, color: AppColors.textSecondary)),
                    ],
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(width: 6),
          if (active)
            FilledButton(
              onPressed: onPay,
              style: FilledButton.styleFrom(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                minimumSize: const Size(0, 32),
                backgroundColor: AppColors.primary,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                textStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800),
              ),
              child: const Text('Bayar'),
            ),
          IconButton(
            onPressed: onMenu,
            icon: const Icon(Icons.more_vert, size: 20, color: AppColors.textMuted),
          ),
        ],
      ),
    );
  }
}

class _SubscriptionSheet extends StatefulWidget {
  const _SubscriptionSheet();

  @override
  State<_SubscriptionSheet> createState() => _SubscriptionSheetState();
}

class _SubscriptionSheetState extends State<_SubscriptionSheet> {
  final _nameCtrl = TextEditingController();
  final _amountCtrl = TextEditingController();
  final _notesCtrl = TextEditingController();
  final _iconCtrl = TextEditingController(text: '🔁');
  String _billingCycle = 'monthly';
  DateTime? _nextBilling;
  bool _isWaste = false;

  @override
  void dispose() {
    _nameCtrl.dispose();
    _amountCtrl.dispose();
    _notesCtrl.dispose();
    _iconCtrl.dispose();
    super.dispose();
  }

  String? _fmtDate(DateTime d) =>
      '${d.year.toString().padLeft(4, '0')}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';

  Future<void> _pickDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: _nextBilling ?? now,
      firstDate: now.subtract(const Duration(days: 365)),
      lastDate: now.add(const Duration(days: 365 * 3)),
      helpText: 'Tanggal tagihan berikutnya',
    );
    if (picked != null) setState(() => _nextBilling = picked);
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
                decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(2)),
              ),
            ),
            const SizedBox(height: 12),
            const Text('Tambah Langganan',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            const SizedBox(height: 16),
            TextField(
              controller: _iconCtrl,
              decoration: const InputDecoration(labelText: 'IKON (EMOJI)', hintText: '🔁'),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _nameCtrl,
              decoration: const InputDecoration(labelText: 'NAMA LAYANAN', hintText: 'Netflix'),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _amountCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'NOMINAL / BULAN (RP)', hintText: '59.000', prefixText: 'Rp '),
            ),
            const SizedBox(height: 14),
            DropdownButtonFormField<String>(
              initialValue: _billingCycle,
              decoration: const InputDecoration(labelText: 'SIKLUS TAGIHAN'),
              items: const [
                DropdownMenuItem(value: 'monthly', child: Text('Bulanan')),
                DropdownMenuItem(value: 'weekly', child: Text('Mingguan')),
                DropdownMenuItem(value: 'yearly', child: Text('Tahunan')),
              ],
              onChanged: (v) => setState(() => _billingCycle = v!),
            ),
            const SizedBox(height: 14),
            InkWell(
              onTap: _pickDate,
              borderRadius: BorderRadius.circular(12),
              child: InputDecorator(
                decoration: const InputDecoration(labelText: 'TANGGAL TAGIHAN BERIKUTNYA'),
                child: Text(
                  _nextBilling == null
                      ? 'Pilih tanggal'
                      : '${_nextBilling!.day} ${Fmt.monthLabel(_fmtDate(_nextBilling!)!)}',
                  style: const TextStyle(fontSize: 14, color: AppColors.textPrimary),
                ),
              ),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _notesCtrl,
              maxLines: 2,
              decoration: const InputDecoration(labelText: 'CATATAN (OPSIONAL)'),
            ),
            const SizedBox(height: 6),
            SwitchListTile(
              value: _isWaste,
              onChanged: (v) => setState(() => _isWaste = v),
              contentPadding: EdgeInsets.zero,
              activeTrackColor: const Color(0xFFD97706),
              title: const Text('🤦 Jarang terpakai (ingatkan terus)',
                  style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700)),
            ),
            const SizedBox(height: 16),
            FilledButton(
              onPressed: () {
                final name = _nameCtrl.text.trim();
                final amount = double.tryParse(Fmt.parseAmount(_amountCtrl.text)) ?? 0;
                if (name.isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Nama layanan wajib diisi.')),
                  );
                  return;
                }
                if (amount <= 0) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Nominal harus lebih dari Rp 0.')),
                  );
                  return;
                }
                Navigator.pop(context, {
                  'name': name,
                  'icon': _iconCtrl.text.trim().isEmpty ? '🔁' : _iconCtrl.text.trim(),
                  'amount': amount,
                  'billing_cycle': _billingCycle,
                  'next_billing_date': _nextBilling != null ? _fmtDate(_nextBilling!) : null,
                  'notes': _notesCtrl.text.trim(),
                  'is_waste': _isWaste,
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