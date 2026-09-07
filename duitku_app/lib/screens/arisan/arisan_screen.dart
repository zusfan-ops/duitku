import 'package:flutter/material.dart';

import '../../models/arisan.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';
import 'arisan_detail_screen.dart';

class ArisanScreen extends StatefulWidget {
  const ArisanScreen({super.key});

  @override
  State<ArisanScreen> createState() => _ArisanScreenState();
}

class _ArisanScreenState extends State<ArisanScreen> {
  bool _loading = true;
  List<ArisanGroup> _groups = [];
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
      final groups = await ApiService.instance.arisan();
      if (!mounted) return;
      setState(() {
        _groups = groups;
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

  Future<void> _openCreateSheet() async {
    final saved = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => _CreateArisanSheet(),
    );
    if (saved == null) return;
    try {
      await ApiService.instance.storeArisan(
        name: saved['name'] as String,
        amount: (saved['amount'] as num).toDouble(),
        frequency: saved['frequency'] as String,
        startDate: saved['start_date'] as String?,
        ownerName: saved['owner_name'] as String?,
        description: saved['description'] as String?,
        ownerPhone: saved['owner_phone'] as String?,
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Arisan berhasil dibuat.')),
      );
      _load();
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  Future<void> _openDetail(ArisanGroup group) async {
    await Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => ArisanDetailScreen(groupId: group.id)),
    );
    _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(title: const Text('Arisan Komunitas')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.fromLTRB(14, 6, 14, 110),
                children: [
                  _buildHero(),
                  if (_error != null)
                    Padding(
                      padding: const EdgeInsets.only(top: 10),
                      child: Text(_error!,
                          textAlign: TextAlign.center,
                          style: const TextStyle(fontSize: 12.5, color: AppColors.textSecondary)),
                    ),
                  _buildList(),
                ],
              ),
            ),
    );
  }

  Widget _buildHero() {
    final active = _groups.where((g) => g.status == 'active').length;
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
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Text('🫂 Arisan Warga',
                    style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: Colors.white)),
              ),
              const SizedBox(height: 10),
              Text('$active grup aktif',
                  style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w900, letterSpacing: -0.5, color: Colors.white)),
              const SizedBox(height: 4),
              Text(
                'Total ${_groups.length} arisan • iuran berkala, giliran dapat diundi bersama',
                style: TextStyle(fontSize: 12.5, color: Colors.white.withValues(alpha: 0.9)),
              ),
              const SizedBox(height: 14),
              OutlinedButton.icon(
                onPressed: _openCreateSheet,
                style: OutlinedButton.styleFrom(
                  foregroundColor: Colors.white,
                  side: BorderSide(color: Colors.white.withValues(alpha: 0.4)),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
                icon: const Icon(Icons.add_rounded, size: 18),
                label: const Text('Buat Arisan', style: TextStyle(fontWeight: FontWeight.w800)),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildList() {
    if (_groups.isEmpty) {
      return Container(
        margin: const EdgeInsets.only(top: 16),
        padding: const EdgeInsets.symmetric(vertical: 30, horizontal: 16),
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: AppColors.border),
        ),
        child: const Column(
          children: [
            Icon(Icons.group_add_outlined, size: 40, color: AppColors.textMuted),
            SizedBox(height: 10),
            Text('Belum ada arisan.',
                style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            SizedBox(height: 4),
            Text('Klik "Buat Arisan" untuk membuat kelompok iuran pertemanan.',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 12, color: AppColors.textSecondary)),
          ],
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Padding(
          padding: EdgeInsets.fromLTRB(4, 18, 4, 8),
          child: Text('Kelompok Arisan',
              style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
        ),
        ..._groups.map((g) => _GroupCard(group: g, onTap: () => _openDetail(g))),
      ],
    );
  }
}

class _GroupCard extends StatelessWidget {
  final ArisanGroup group;
  final VoidCallback onTap;
  const _GroupCard({required this.group, required this.onTap});

  String get _freqLabel => group.frequency == 'weekly'
      ? 'Mingguan'
      : group.frequency == 'yearly'
          ? 'Tahunan'
          : 'Bulanan';

  @override
  Widget build(BuildContext context) {
    final progress = group.progress;
    final percent = progress.percent.clamp(0.0, 100.0);
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(18),
      child: Container(
        margin: const EdgeInsets.only(top: 10),
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
                Container(
                  width: 42,
                  height: 42,
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(colors: [Color(0xFF7C3AED), Color(0xFFA78BFA)]),
                    shape: BoxShape.circle,
                  ),
                  alignment: Alignment.center,
                  child: const Text('🫂', style: TextStyle(fontSize: 20)),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Flexible(
                            child: Text(group.name,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w900, color: AppColors.textPrimary)),
                          ),
                          const SizedBox(width: 6),
                          if (group.isOwner)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: const Color(0xFFEDE9FE),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: const Text('Pemilik',
                                  style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w800, color: Color(0xFF6D28D9))),
                            ),
                        ],
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${Fmt.money(group.amount)} • $_freqLabel • ${progress.totalMembers} anggota',
                        style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                const Icon(Icons.chevron_right_rounded, size: 20, color: AppColors.textMuted),
              ],
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
                      backgroundColor: const Color(0xFFEDE9FE),
                      valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFF8B5CF6)),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Text('Ronde ${progress.currentRound}/${progress.totalMembers}',
                    style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: Color(0xFF7C3AED))),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _CreateArisanSheet extends StatefulWidget {
  const _CreateArisanSheet();

  @override
  State<_CreateArisanSheet> createState() => _CreateArisanSheetState();
}

class _CreateArisanSheetState extends State<_CreateArisanSheet> {
  final _nameCtrl = TextEditingController();
  final _amountCtrl = TextEditingController();
  final _ownerNameCtrl = TextEditingController();
  final _ownerPhoneCtrl = TextEditingController();
  final _descCtrl = TextEditingController();
  String _frequency = 'monthly';
  DateTime? _startDate;

  @override
  void dispose() {
    _nameCtrl.dispose();
    _amountCtrl.dispose();
    _ownerNameCtrl.dispose();
    _ownerPhoneCtrl.dispose();
    _descCtrl.dispose();
    super.dispose();
  }

  String? _fmtDate(DateTime d) =>
      '${d.year.toString().padLeft(4, '0')}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';

  Future<void> _pickDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: _startDate ?? now,
      firstDate: DateTime(now.year - 5),
      lastDate: DateTime(now.year + 5),
      helpText: 'Tanggal mulai',
    );
    if (picked != null) setState(() => _startDate = picked);
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
            const Text('Buat Arisan Baru',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            const SizedBox(height: 16),
            TextField(
              controller: _nameCtrl,
              decoration: const InputDecoration(labelText: 'NAMA ARISAN', hintText: 'Arisan RT 04'),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _amountCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'NOMINAL PER RONDE (RP)', hintText: '100.000', prefixText: 'Rp '),
            ),
            const SizedBox(height: 14),
            DropdownButtonFormField<String>(
              initialValue: _frequency,
              decoration: const InputDecoration(labelText: 'PERIODE'),
              items: const [
                DropdownMenuItem(value: 'monthly', child: Text('Bulanan')),
                DropdownMenuItem(value: 'weekly', child: Text('Mingguan')),
                DropdownMenuItem(value: 'yearly', child: Text('Tahunan')),
              ],
              onChanged: (v) => setState(() => _frequency = v!),
            ),
            const SizedBox(height: 14),
            InkWell(
              onTap: _pickDate,
              borderRadius: BorderRadius.circular(12),
              child: InputDecorator(
                decoration: const InputDecoration(labelText: 'TANGGAL MULAI'),
                child: Text(
                  _startDate == null
                      ? 'Pilih tanggal'
                      : '${_startDate!.day} ${Fmt.monthLabel(_fmtDate(_startDate!)!)}',
                  style: const TextStyle(fontSize: 14, color: AppColors.textPrimary),
                ),
              ),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _ownerNameCtrl,
              decoration: const InputDecoration(labelText: 'NAMA PENGELOLA (OPSIONAL)'),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _ownerPhoneCtrl,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(labelText: 'NO. HP PENGELOLA (OPSIONAL)'),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _descCtrl,
              maxLines: 2,
              decoration: const InputDecoration(labelText: 'DESKRIPSI (OPSIONAL)', hintText: 'Keterangan tambahan'),
            ),
            const SizedBox(height: 20),
            FilledButton(
              onPressed: () {
                final name = _nameCtrl.text.trim();
                final amount = double.tryParse(Fmt.parseAmount(_amountCtrl.text)) ?? 0;
                if (name.isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Nama arisan wajib diisi.')),
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
                  'amount': amount,
                  'frequency': _frequency,
                  'start_date': _startDate != null ? _fmtDate(_startDate!) : null,
                  'owner_name': _ownerNameCtrl.text.trim(),
                  'owner_phone': _ownerPhoneCtrl.text.trim(),
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