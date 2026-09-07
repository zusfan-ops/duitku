import 'package:flutter/material.dart';

import '../../models/arisan.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class ArisanDetailScreen extends StatefulWidget {
  final int groupId;
  const ArisanDetailScreen({super.key, required this.groupId});

  @override
  State<ArisanDetailScreen> createState() => _ArisanDetailScreenState();
}

class _ArisanDetailScreenState extends State<ArisanDetailScreen> {
  bool _loading = true;
  ArisanGroup? _group;
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
      final group = await ApiService.instance.arisanDetail(widget.groupId);
      if (!mounted) return;
      setState(() {
        _group = group;
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

  Future<void> _markPaid(ArisanPayment payment) async {
    try {
      await ApiService.instance.arisanPayPayment(paymentId: payment.id, groupId: widget.groupId);
      await _snack('Pembayaran ${payment.memberName ?? 'anggota'} dicatat.');
      _load();
    } on ApiException catch (e) {
      await _snack(e.message);
    }
  }

  Future<void> _advanceRound() async {
    try {
      await ApiService.instance.arisanAdvance(widget.groupId);
      await _snack('Lanjut ke ronde berikutnya.');
      _load();
    } on ApiException catch (e) {
      await _snack(e.message);
    }
  }

  Future<void> _openAddMemberSheet() async {
    final saved = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => const _AddMemberSheet(),
    );
    if (saved == null) return;
    try {
      await ApiService.instance.arisanAddMember(
        widget.groupId,
        memberName: saved['member_name'] as String,
        phone: saved['phone'] as String?,
      );
      await _snack('Anggota berhasil ditambahkan.');
      _load();
    } on ApiException catch (e) {
      await _snack(e.message);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(title: const Text('Detail Arisan')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null && _group == null
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
                      _buildSummaryRow(),
                      _buildBeneficiary(),
                      _buildPayments(),
                      _buildMembers(),
                    ],
                  ),
                ),
    );
  }

  double get _totalCollected => _group!.payments
      .where((p) => p.isPaid)
      .fold(0.0, (sum, p) => sum + p.amount);

  List<ArisanPayment> get _currentRoundPayments => _group!.payments
      .where((p) => p.roundNumber == _group!.currentRound)
      .toList();

  bool get _allPaidCurrentRound =>
      _currentRoundPayments.isNotEmpty && _currentRoundPayments.every((p) => p.isPaid);

  Widget _buildHero() {
    final g = _group!;
    final progress = g.progress;
    final percent = progress.percent.clamp(0.0, 100.0);
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF7C3AED), Color(0xFF8B5CF6), Color(0xFFA78BFA)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(24),
        boxShadow: const [
          BoxShadow(color: Color(0x478B5CF6), blurRadius: 24, offset: Offset(0, 10)),
        ],
      ),
      child: Stack(
        children: [
          Positioned(
            right: -5,
            bottom: -20,
            child: Text('🫂',
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
                    child: Text('Arus ${g.frequency == 'weekly' ? 'Mingguan' : g.frequency == 'yearly' ? 'Tahunan' : 'Bulanan'}',
                        style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: Colors.white)),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Text(g.name,
                  style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w900, letterSpacing: -0.5, color: Colors.white)),
              const SizedBox(height: 4),
              Text(
                '${Fmt.money(g.amount)} / orang • ${g.description.isNotEmpty ? g.description : 'Dibuka sejak ${g.startDate}'}',
                style: TextStyle(fontSize: 12.5, color: Colors.white.withValues(alpha: 0.9)),
              ),
              const SizedBox(height: 14),
              Row(
                children: [
                  Expanded(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(6),
                      child: LinearProgressIndicator(
                        value: percent / 100,
                        minHeight: 8,
                        backgroundColor: Colors.white.withValues(alpha: 0.25),
                        valueColor: const AlwaysStoppedAnimation<Color>(Colors.white),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Text(
                    'Ronde ${progress.currentRound}/${progress.totalMembers} • ${percent.round()}%',
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Colors.white),
                  ),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryRow() {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 16),
      child: Row(
        children: [
          Expanded(child: _OverviewCard(value: '${_group!.totalMembers}', label: 'Anggota')),
          const SizedBox(width: 10),
          Expanded(child: _OverviewCard(value: 'Ronde ${_group!.currentRound}', label: 'Sedang Berjalan')),
          const SizedBox(width: 10),
          Expanded(child: _OverviewCard(value: Fmt.money0(_totalCollected), label: 'Total Terkumpul')),
        ],
      ),
    );
  }

  Widget _buildBeneficiary() {
    final g = _group!;
    final current = g.currentRound;
    ArisanMember? beneficiary;
    for (final m in g.members) {
      if (m.rotationOrder == current) {
        beneficiary = m;
        break;
      }
    }
    beneficiary ??= g.members.isEmpty ? null : g.members.first;
    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(colors: [Color(0xFFF5F3FF), Color(0xFFFFF7ED)]),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFEDE9FE)),
      ),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: const BoxDecoration(
              gradient: LinearGradient(colors: [Color(0xFF8B5CF6), Color(0xFFF59E0B)]),
              shape: BoxShape.circle,
            ),
            alignment: Alignment.center,
            child: const Text('🎁', style: TextStyle(fontSize: 20)),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('PENERIMA DANA • RONDE $current',
                    style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.w800, color: Color(0xFF7C3AED))),
                const SizedBox(height: 2),
                Text(beneficiary?.memberName ?? 'Menunggu anggota',
                    style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w900, color: AppColors.textPrimary)),
                Text(
                  beneficiary == null
                      ? 'Belum ada anggota'
                      : (beneficiary.hasReceived
                          ? 'Dana ronde ini sudah diterima'
                          : 'Menunggu pembayaran ronde ini selesai'),
                  style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                ),
              ],
            ),
          ),
          if (beneficiary != null)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: beneficiary.hasReceived ? const Color(0xFFD1FAE5) : const Color(0xFFFEF3C7),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                beneficiary.hasReceived ? 'Sudah Terima' : 'Bergilir',
                style: TextStyle(
                  fontSize: 10.5,
                  fontWeight: FontWeight.w800,
                  color: beneficiary.hasReceived ? const Color(0xFF047857) : const Color(0xFFB45309),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildPayments() {
    final g = _group!;
    final payments = _currentRoundPayments;
    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Text('💳', style: TextStyle(fontSize: 16)),
              const SizedBox(width: 8),
              Text('Pembayaran • Ronde ${g.currentRound}',
                  style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
              const Spacer(),
              if (g.isOwner && _allPaidCurrentRound)
                TextButton.icon(
                  onPressed: _advanceRound,
                  icon: const Icon(Icons.skip_next_rounded, size: 18, color: Color(0xFF7C3AED)),
                  label: const Text('Lanjut Ronde',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFF7C3AED))),
                ),
            ],
          ),
          const SizedBox(height: 4),
          if (payments.isEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 16),
              child: Center(
                child: Text(
                  g.isOwner ? 'Belum ada pembayaran untuk ronde ini.' : 'Menunggu pembayaran ronde ini.',
                  style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                ),
              ),
            )
          else
            ...payments.map((p) => _PaymentTile(
                  payment: p,
                  isOwner: g.isOwner,
                  onMark: () => _markPaid(p),
                )),
        ],
      ),
    );
  }

  Widget _buildMembers() {
    final g = _group!;
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
          Row(
            children: [
              const Text('👥', style: TextStyle(fontSize: 16)),
              const SizedBox(width: 8),
              Text('Anggota (${g.members.length})',
                  style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
              const Spacer(),
              if (g.isOwner)
                TextButton.icon(
                  onPressed: _openAddMemberSheet,
                  icon: const Icon(Icons.person_add_alt_1_rounded, size: 18, color: Color(0xFF7C3AED)),
                  label: const Text('Tambah',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFF7C3AED))),
                ),
            ],
          ),
          const SizedBox(height: 4),
          if (g.members.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 16),
              child: Center(
                child: Text('Belum ada anggota. Tambahkan anggota untuk memulai arisan.',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 12, color: AppColors.textSecondary)),
              ),
            )
          else
            ...g.members.map((m) => _MemberTile(member: m)),
        ],
      ),
    );
  }
}

class _OverviewCard extends StatelessWidget {
  final String value;
  final String label;
  const _OverviewCard({required this.value, required this.label});

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
              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: Color(0xFF7C3AED))),
          const SizedBox(height: 3),
          Text(label,
              style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
        ],
      ),
    );
  }
}

class _PaymentTile extends StatelessWidget {
  final ArisanPayment payment;
  final bool isOwner;
  final VoidCallback onMark;
  const _PaymentTile({required this.payment, required this.isOwner, required this.onMark});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 10),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: payment.isPaid ? const Color(0xFFD1FAE5) : const Color(0xFFF3F4F6),
              shape: BoxShape.circle,
            ),
            alignment: Alignment.center,
            child: Icon(
              payment.isPaid ? Icons.check_rounded : Icons.pending_outlined,
              size: 20,
              color: payment.isPaid ? const Color(0xFF059669) : AppColors.textMuted,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(payment.memberName ?? 'Anggota',
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                Text(Fmt.money(payment.amount),
                    style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
              ],
            ),
          ),
          if (payment.status != 'paid' && isOwner)
            FilledButton(
              onPressed: onMark,
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
    );
  }
}

class _MemberTile extends StatelessWidget {
  final ArisanMember member;
  const _MemberTile({required this.member});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 9),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: const BoxDecoration(
              gradient: LinearGradient(colors: [Color(0xFF8B5CF6), Color(0xFFA78BFA)]),
              shape: BoxShape.circle,
            ),
            alignment: Alignment.center,
            child: Text(
              (member.memberName.isNotEmpty ? member.memberName[0] : '?').toUpperCase(),
              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: Colors.white),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(member.memberName,
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                Text('Giliran ke-${member.rotationOrder}',
                    style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
              ],
            ),
          ),
          if (member.hasReceived)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: const Color(0xFFD1FAE5),
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Text('Terima 🎁',
                  style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: Color(0xFF047857))),
            ),
          if (member.phone != null && member.phone!.isNotEmpty) ...[
            const SizedBox(width: 4),
            const Icon(Icons.phone_outlined, size: 16, color: AppColors.textMuted),
          ],
        ],
      ),
    );
  }
}

class _AddMemberSheet extends StatefulWidget {
  const _AddMemberSheet();

  @override
  State<_AddMemberSheet> createState() => _AddMemberSheetState();
}

class _AddMemberSheetState extends State<_AddMemberSheet> {
  final _nameCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
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
                decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(2)),
              ),
            ),
            const SizedBox(height: 12),
            const Text('Tambah Anggota Arisan',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            const SizedBox(height: 16),
            TextField(
              controller: _nameCtrl,
              decoration: const InputDecoration(labelText: 'NAMA ANGGOTA', hintText: 'Budi Santoso'),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _phoneCtrl,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(labelText: 'NO. HP (OPSIONAL)'),
            ),
            const SizedBox(height: 20),
            FilledButton(
              onPressed: () {
                final name = _nameCtrl.text.trim();
                if (name.isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Nama anggota wajib diisi.')),
                  );
                  return;
                }
                Navigator.pop(context, {'member_name': name, 'phone': _phoneCtrl.text.trim()});
              },
              child: const Text('Simpan'),
            ),
          ],
        ),
      ),
    );
  }
}