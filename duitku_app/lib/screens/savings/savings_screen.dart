import 'package:flutter/material.dart';

import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';
import '../../widgets/category_icon.dart';

class SavingsScreen extends StatefulWidget {
  const SavingsScreen({super.key});

  @override
  State<SavingsScreen> createState() => _SavingsScreenState();
}

class _SavingsScreenState extends State<SavingsScreen> {
  bool _loading = true;
  List<dynamic> _goals = [];
  String _symbol = 'Rp';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await Future.wait([
        ApiService.instance.savingsGoals(),
        ApiService.instance.settings(),
      ]);
      if (!mounted) return;
      setState(() {
        _goals = (res[0]['goals'] as List<dynamic>? ?? []);
        _symbol = res[1]['symbol']?.toString() ?? 'Rp';
        _loading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _topUpGoal(Map<String, dynamic> goal) async {
    final amtCtrl = TextEditingController();
    final id = int.tryParse('${goal['id']}') ?? 0;

    final ok = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
        child: Padding(
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
              Text('💸 Setor ke "${goal['name']}"',
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                      fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
              const SizedBox(height: 16),
              TextField(
                controller: amtCtrl,
                keyboardType: TextInputType.number,
                autofocus: true,
                decoration: InputDecoration(
                  labelText: 'NOMINAL SETORAN',
                  prefixText: '$_symbol ',
                ),
              ),
              const SizedBox(height: 20),
              FilledButton(
                onPressed: () async {
                  final amount = double.tryParse(Fmt.parseAmount(amtCtrl.text)) ?? 0;
                  if (amount <= 0) return;
                  try {
                    await ApiService.instance.topUpSavingsGoal(id, amount);
                    if (ctx.mounted) Navigator.pop(ctx, true);
                  } on ApiException catch (e) {
                    if (ctx.mounted) ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(content: Text(e.message)));
                  }
                },
                child: const Text('Setor Sekarang'),
              ),
            ],
          ),
        ),
      ),
    );

    if (ok == true) _load();
  }

  Future<void> _deleteGoal(int id) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Hapus target tabungan?'),
        content: const Text('Target tabungan ini akan dihapus secara permanen.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: TextButton.styleFrom(foregroundColor: AppColors.expense),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );
    if (ok != true) return;
    try {
      await ApiService.instance.deleteSavingsGoal(id);
      _load();
    } on ApiException catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  Future<void> _showGoalSheet([Map<String, dynamic>? goal]) async {
    final isEdit = goal != null;
    final id = isEdit ? int.tryParse('${goal['id']}') : null;
    String icon = goal?['icon']?.toString() ?? '🎯';
    String color = goal?['color']?.toString() ?? '#0AA956';
    final nameCtrl = TextEditingController(text: goal?['name']?.toString() ?? '');
    final targetCtrl = TextEditingController(
      text: goal != null ? Fmt.money0(Fmt.toDouble(goal['target_amount'])) : '',
    );
    final savedCtrl = TextEditingController(
      text: goal != null ? Fmt.money0(Fmt.toDouble(goal['saved_amount'])) : '',
    );
    DateTime? deadline = goal?['deadline'] != null && goal!['deadline'].toString().isNotEmpty
        ? DateTime.tryParse(goal['deadline'].toString())
        : null;

    final icons = ['🎯', '🏠', '🚗', '✈️', '💍', '📱', '💊', '🎓', '🏋️', '💰', '🏖️', '🎮'];
    final colors = ['#0AA956', '#2563EB', '#8B5CF6', '#DC2626', '#F59E0B', '#0D9488', '#EC4899', '#F97316'];

    final saved = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSS) => Padding(
          padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
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
                Text(isEdit ? 'Edit Target Tabungan' : 'Target Menabung Baru',
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                const SizedBox(height: 16),
                const Text('PILIH IKON', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textMuted)),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: icons.map((ico) {
                    final sel = icon == ico;
                    return GestureDetector(
                      onTap: () => setSS(() => icon = ico),
                      child: Container(
                        width: 38,
                        height: 38,
                        decoration: BoxDecoration(
                          color: AppColors.bg,
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: sel ? AppColors.primary : AppColors.border, width: sel ? 2 : 1),
                        ),
                        child: Center(child: Text(ico, style: const TextStyle(fontSize: 18))),
                      ),
                    );
                  }).toList(),
                ),
                const SizedBox(height: 14),
                const Text('PILIH WARNA', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textMuted)),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: colors.map((c) {
                    final sel = color == c;
                    return GestureDetector(
                      onTap: () => setSS(() => color = c),
                      child: Container(
                        width: 30,
                        height: 30,
                        decoration: BoxDecoration(
                          color: parseColor(c),
                          shape: BoxShape.circle,
                          border: Border.all(color: sel ? AppColors.textPrimary : Colors.transparent, width: 2.5),
                        ),
                      ),
                    );
                  }).toList(),
                ),
                const SizedBox(height: 14),
                TextField(controller: nameCtrl, decoration: const InputDecoration(labelText: 'NAMA TARGET', hintText: 'cth. Liburan, Beli Gadget')),
                const SizedBox(height: 14),
                TextField(
                  controller: targetCtrl,
                  keyboardType: TextInputType.number,
                  decoration: InputDecoration(labelText: 'TARGET NOMINAL', prefixText: '$_symbol '),
                ),
                const SizedBox(height: 14),
                TextField(
                  controller: savedCtrl,
                  keyboardType: TextInputType.number,
                  decoration: InputDecoration(labelText: 'SUDAH TERKUMPUL (OPSIONAL)', prefixText: '$_symbol '),
                ),
                const SizedBox(height: 14),
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  title: const Text('TENGGAT WAKTU (OPSIONAL)', style: TextStyle(fontSize: 12, color: AppColors.textMuted, fontWeight: FontWeight.w600)),
                  subtitle: Text(
                    deadline != null ? Fmt.dateDay(deadline!.toIso8601String().substring(0, 10)) : 'Tidak diatur',
                    style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
                  ),
                  trailing: deadline != null
                      ? IconButton(
                          icon: const Icon(Icons.clear, size: 18),
                          onPressed: () => setSS(() => deadline = null),
                        )
                      : const Icon(Icons.calendar_today_outlined, size: 18, color: AppColors.primary),
                  onTap: () async {
                    final picked = await showDatePicker(
                      context: ctx,
                      initialDate: deadline ?? DateTime.now().add(const Duration(days: 30)),
                      firstDate: DateTime.now(),
                      lastDate: DateTime(2035),
                    );
                    if (picked != null) setSS(() => deadline = picked);
                  },
                ),
                const SizedBox(height: 20),
                FilledButton(
                  onPressed: () async {
                    final name = nameCtrl.text.trim();
                    final target = double.tryParse(Fmt.parseAmount(targetCtrl.text)) ?? 0;
                    final saved = double.tryParse(Fmt.parseAmount(savedCtrl.text)) ?? 0;
                    if (name.isEmpty || target <= 0) return;
                    try {
                      await ApiService.instance.storeSavingsGoal(
                        id: id,
                        name: name,
                        targetAmount: target,
                        savedAmount: saved,
                        icon: icon,
                        color: color,
                        deadline: deadline?.toIso8601String().substring(0, 10),
                      );
                      if (ctx.mounted) Navigator.pop(ctx, true);
                    } on ApiException catch (e) {
                      if (ctx.mounted) ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(content: Text(e.message)));
                    }
                  },
                  child: const Text('Simpan'),
                ),
              ],
            ),
          ),
        ),
      ),
    );

    if (saved == true) _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Target Menabung'),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _showGoalSheet(),
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: const Text('Target Baru', style: TextStyle(fontWeight: FontWeight.w700)),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: _goals.isEmpty
                  ? ListView(
                      children: const [
                        SizedBox(height: 80),
                        Center(
                          child: Column(
                            children: [
                              Text('🎯', style: TextStyle(fontSize: 56)),
                              SizedBox(height: 12),
                              Text('Belum ada target tabungan',
                                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                              SizedBox(height: 6),
                              Padding(
                                padding: EdgeInsets.symmetric(horizontal: 40),
                                child: Text('Mulai buat rencana menabung untuk berbagai impian dan tujuan finansialmu.',
                                    textAlign: TextAlign.center,
                                    style: TextStyle(fontSize: 13, color: AppColors.textMuted)),
                              ),
                            ],
                          ),
                        ),
                      ],
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.fromLTRB(16, 12, 16, 100),
                      itemCount: _goals.length,
                      itemBuilder: (context, index) {
                        final g = _goals[index] as Map<String, dynamic>;
                        final id = int.tryParse('${g['id']}') ?? 0;
                        final target = Fmt.toDouble(g['target_amount']);
                        final saved = Fmt.toDouble(g['saved_amount']);
                        final pct = target > 0 ? (saved / target).clamp(0.0, 1.0) : 0.0;
                        final reached = saved >= target && target > 0;
                        final color = parseColor(g['color']?.toString() ?? '#0AA956');
                        final deadlineStr = g['deadline']?.toString();

                        int? daysLeft;
                        if (deadlineStr != null && deadlineStr.isNotEmpty) {
                          final d = DateTime.tryParse(deadlineStr);
                          if (d != null) {
                            daysLeft = d.difference(DateTime.now()).inDays + 1;
                          }
                        }

                        return Container(
                          margin: const EdgeInsets.only(bottom: 12),
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: AppColors.card,
                            borderRadius: BorderRadius.circular(18),
                            border: Border.all(color: AppColors.border),
                            boxShadow: AppColors.cardShadow,
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Container(
                                    width: 44,
                                    height: 44,
                                    decoration: BoxDecoration(
                                      color: color.withValues(alpha: .12),
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    child: Center(
                                      child: Text(g['icon']?.toString() ?? '🎯', style: const TextStyle(fontSize: 22)),
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          g['name']?.toString() ?? 'Tabungan',
                                          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
                                        ),
                                        if (deadlineStr != null && deadlineStr.isNotEmpty)
                                          Text(
                                            daysLeft != null && daysLeft >= 0
                                                ? 'Tenggat ${Fmt.dateDay(deadlineStr)} ($daysLeft hari lagi)'
                                                : 'Tenggat ${Fmt.dateDay(deadlineStr)} (lewat)',
                                            style: TextStyle(
                                              fontSize: 11,
                                              fontWeight: FontWeight.w600,
                                              color: daysLeft != null && daysLeft <= 7 ? AppColors.expense : AppColors.textMuted,
                                            ),
                                          ),
                                      ],
                                    ),
                                  ),
                                  PopupMenuButton<String>(
                                    icon: const Icon(Icons.more_vert, size: 20, color: AppColors.textMuted),
                                    onSelected: (v) {
                                      if (v == 'edit') _showGoalSheet(g);
                                      if (v == 'delete') _deleteGoal(id);
                                    },
                                    itemBuilder: (ctx) => [
                                      const PopupMenuItem(value: 'edit', child: Row(children: [Icon(Icons.edit_outlined, size: 16), SizedBox(width: 8), Text('Edit')])),
                                      const PopupMenuItem(value: 'delete', child: Row(children: [Icon(Icons.delete_outline, color: AppColors.expense, size: 16), SizedBox(width: 8), Text('Hapus', style: TextStyle(color: AppColors.expense))])),
                                    ],
                                  ),
                                ],
                              ),
                              const SizedBox(height: 14),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    Fmt.money(saved, symbol: _symbol),
                                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: color),
                                  ),
                                  Text(
                                    'Target ${Fmt.money(target, symbol: _symbol)}',
                                    style: const TextStyle(fontSize: 12, color: AppColors.textSecondary, fontWeight: FontWeight.w600),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),
                              ClipRRect(
                                borderRadius: BorderRadius.circular(6),
                                child: LinearProgressIndicator(
                                  value: pct,
                                  minHeight: 8,
                                  backgroundColor: AppColors.border,
                                  valueColor: AlwaysStoppedAnimation(color),
                                ),
                              ),
                              const SizedBox(height: 8),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    reached
                                        ? '🎉 Target tercapai!'
                                        : 'Sisa ${Fmt.money((target - saved).clamp(0, double.infinity), symbol: _symbol)}',
                                    style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                                  ),
                                  Text(
                                    '${(pct * 100).round()}%',
                                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: color),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 12),
                              SizedBox(
                                width: double.infinity,
                                child: OutlinedButton.icon(
                                  onPressed: reached ? null : () => _topUpGoal(g),
                                  style: OutlinedButton.styleFrom(
                                    foregroundColor: color,
                                    side: BorderSide(color: color.withValues(alpha: .5)),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                  ),
                                  icon: const Icon(Icons.add_circle_outline, size: 16),
                                  label: Text(reached ? 'Tercapai' : 'Setor Tabungan', style: const TextStyle(fontWeight: FontWeight.w700)),
                                ),
                              ),
                            ],
                          ),
                        );
                      },
                    ),
            ),
    );
  }
}
