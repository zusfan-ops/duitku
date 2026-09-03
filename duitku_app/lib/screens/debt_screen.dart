import 'package:flutter/material.dart';

import '../models/debt.dart';
import '../services/api_service.dart';
import '../services/sync_service.dart';
import '../theme.dart';
import '../utils/format.dart';
import '../widgets/sync_status_banner.dart';

class DebtScreen extends StatefulWidget {
  const DebtScreen({super.key});

  @override
  State<DebtScreen> createState() => _DebtScreenState();
}

class _DebtScreenState extends State<DebtScreen> {
  String _status = 'active';
  bool _loading = true;
  List<Debt> _debts = [];
  DebtSummary _summary = const DebtSummary();
  String _symbol = 'Rp';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.instance.debts(_status);
      if (!mounted) return;
      setState(() {
        _debts = (res['debts'] as List<dynamic>? ?? [])
            .map((e) => Debt.fromJson(e as Map<String, dynamic>))
            .toList();
        _summary = DebtSummary.fromJson(res['summary'] as Map<String, dynamic>? ?? {});
        _symbol = res['symbol']?.toString() ?? _symbol;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loading = false);
    }
  }

  Future<void> _openAdd() async {
    String type = 'hutang';
    final personCtrl = TextEditingController();
    final amountCtrl = TextEditingController();
    final descCtrl = TextEditingController();
    String? dueDate;
    bool isPast = false;

    final saved = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheetState) => Padding(
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
                    decoration: BoxDecoration(
                      color: AppColors.border,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                const Text('Catat Hutang / Piutang',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(4),
                  decoration: BoxDecoration(
                    color: AppColors.bg,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    children: [
                      Expanded(child: _typeBtn('Hutang', 'hutang', type, () => setSheetState(() => type = 'hutang'))),
                      Expanded(child: _typeBtn('Piutang', 'piutang', type, () => setSheetState(() => type = 'piutang'))),
                    ],
                  ),
                ),
                const SizedBox(height: 14),
                TextField(
                  controller: personCtrl,
                  decoration: InputDecoration(
                    labelText: type == 'hutang' ? 'NAMA PEMBERI HUTANG' : 'NAMA YANG BERHUTANG',
                    hintText: 'contoh: Budi',
                  ),
                ),
                const SizedBox(height: 14),
                TextField(
                  controller: amountCtrl,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(labelText: 'JUMLAH', prefixText: 'Rp '),
                ),
                const SizedBox(height: 14),
                InkWell(
                  onTap: () async {
                    final picked = await showDatePicker(
                      context: ctx,
                      initialDate: DateTime.now(),
                      firstDate: DateTime(2000),
                      lastDate: DateTime.now().add(const Duration(days: 365 * 5)),
                    );
                    if (picked != null) {
                      setSheetState(() => dueDate =
                          '${picked.year.toString().padLeft(4, '0')}-${picked.month.toString().padLeft(2, '0')}-${picked.day.toString().padLeft(2, '0')}');
                    }
                  },
                  borderRadius: BorderRadius.circular(12),
                  child: InputDecorator(
                    decoration: const InputDecoration(labelText: 'JATUH TEMPO (OPSIONAL)'),
                    child: Row(
                      children: [
                        const Icon(Icons.event, size: 18, color: AppColors.textMuted),
                        const SizedBox(width: 10),
                        Text(dueDate == null
                            ? 'Pilih tanggal'
                            : Fmt.dateDay(dueDate!)),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 14),
                TextField(
                  controller: descCtrl,
                  decoration: const InputDecoration(labelText: 'KETERANGAN (OPSIONAL)'),
                ),
                const SizedBox(height: 6),
                SwitchListTile(
                  contentPadding: EdgeInsets.zero,
                  title: const Text('Hutang sudah terjadi (tidak dicatat sebagai transaksi baru)',
                      style: TextStyle(fontSize: 12)),
                  value: isPast,
                  activeThumbColor: AppColors.primary,
                  onChanged: (v) => setSheetState(() => isPast = v),
                ),
                const SizedBox(height: 12),
                FilledButton(
                  onPressed: () {
                    final person = personCtrl.text.trim();
                    final amount = double.tryParse(Fmt.parseAmount(amountCtrl.text)) ?? 0;
                    if (person.isEmpty || amount <= 0) return;
                    Navigator.pop(ctx, {
                      'type': type,
                      'person': person,
                      'amount': amount,
                      'description': descCtrl.text,
                      'due_date': dueDate,
                      'is_past': isPast,
                    });
                  },
                  child: const Text('Simpan'),
                ),
              ],
            ),
          ),
        ),
      ),
    );

    if (saved != null) {
      final isOnline = SyncService.instance.isOnline;
      if (!isOnline) {
        await SyncService.instance.enqueue('debt_store', {
          'type': saved['type'],
          'person': saved['person'],
          'amount': saved['amount'],
          'description': (saved['description'] as String).trim(),
          'due_date': saved['due_date'],
          'is_past': saved['is_past'],
        });
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Hutang/Piutang dicatat offline. Akan disinkronkan saat online.'),
            backgroundColor: Color(0xFFD97706),
          ),
        );
        _load();
        return;
      }

      try {
        await ApiService.instance.storeDebt(
          type: saved['type'] as String,
          person: saved['person'] as String,
          amount: saved['amount'] as double,
          description: (saved['description'] as String).trim(),
          dueDate: saved['due_date'] as String?,
          isPast: saved['is_past'] as bool,
        );
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Tersimpan')));
      } on ApiException catch (e) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
      } catch (e) {
        await SyncService.instance.enqueue('debt_store', {
          'type': saved['type'],
          'person': saved['person'],
          'amount': saved['amount'],
          'description': (saved['description'] as String).trim(),
          'due_date': saved['due_date'],
          'is_past': saved['is_past'],
        });
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Koneksi terputus. Hutang/Piutang dicatat offline.'),
            backgroundColor: Color(0xFFD97706),
          ),
        );
      }
      _load();
    }
  }

  Widget _typeBtn(String label, String value, String current, VoidCallback onTap) {
    final active = current == value;
    final color = value == 'hutang' ? AppColors.expense : AppColors.income;
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10),
        decoration: BoxDecoration(
          color: active ? color : Colors.transparent,
          borderRadius: BorderRadius.circular(9),
        ),
        child: Text(label,
            textAlign: TextAlign.center,
            style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: active ? Colors.white : AppColors.textSecondary)),
      ),
    );
  }

  Future<void> _pay(Debt d) async {
    final remaining = d.remaining;
    if (remaining <= 0) return;
    final amountCtrl = TextEditingController(text: Fmt.money0(remaining));

    final ok = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => Padding(
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
                  decoration: BoxDecoration(
                    color: AppColors.border,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 12),
              Text(
                d.type == 'hutang' ? 'Bayar Hutang ke ${d.person}' : 'Terima Piutang dari ${d.person}',
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
              ),
              const SizedBox(height: 6),
              Text('Sisa ${Fmt.money(remaining, symbol: _symbol)}',
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
              const SizedBox(height: 16),
              TextField(
                controller: amountCtrl,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(labelText: 'NOMINAL', prefixText: 'Rp '),
              ),
              const SizedBox(height: 20),
              FilledButton(
                onPressed: () async {
                  final amount = double.tryParse(Fmt.parseAmount(amountCtrl.text)) ?? 0;
                  if (amount <= 0) return;
                  try {
                    await ApiService.instance.payDebt(d.id, amount);
                    if (ctx.mounted) Navigator.pop(ctx, true);
                  } on ApiException catch (e) {
                    if (ctx.mounted) {
                      ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(content: Text(e.message)));
                    }
                  }
                },
                child: const Text('Bayar'),
              ),
              const SizedBox(height: 8),
              if (remaining > 0)
                TextButton(
                  onPressed: () async {
                    try {
                      await ApiService.instance.settleDebt(d.id);
                      if (ctx.mounted) Navigator.pop(ctx, true);
                    } on ApiException catch (e) {
                      if (ctx.mounted) {
                        ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(content: Text(e.message)));
                      }
                    }
                  },
                  child: const Text('Lunasi Sekaligus',
                      style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.w700)),
                ),
            ],
          ),
        ),
      ),
    );
    if (ok == true) _load();
  }

  Future<void> _delete(Debt d) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Hapus?'),
        content: Text('Hutang/piutang dengan ${d.person} akan dihapus.'),
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
      await ApiService.instance.deleteDebt(d.id);
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
      appBar: AppBar(title: const Text('Hutang & Piutang')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _openAdd,
        backgroundColor: AppColors.primary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Catat', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
      ),
      body: _loading && _debts.isEmpty
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
                children: [
                  const SyncStatusBanner(),
                  _SummaryCard(summary: _summary, symbol: _symbol),
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      color: AppColors.bg,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Row(
                      children: [
                        _tab('Aktif', 'active'),
                        _tab('Lunas', 'settled'),
                      ],
                    ),
                  ),
                  const SizedBox(height: 8),
                  if (_debts.isEmpty)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 60),
                      child: Column(
                        children: [
                          Icon(Icons.people_outline, size: 44, color: AppColors.textMuted),
                          SizedBox(height: 10),
                          Text('Belum ada data.',
                              style: TextStyle(fontSize: 14, color: AppColors.textSecondary)),
                        ],
                      ),
                    )
                  else
                    ..._debts.map((d) => _DebtCard(
                          debt: d,
                          symbol: _symbol,
                          onPay: () => _pay(d),
                          onDelete: () => _delete(d),
                        )),
                ],
              ),
            ),
    );
  }

  Widget _tab(String label, String value) {
    final active = _status == value;
    return Expanded(
      child: GestureDetector(
        onTap: () {
          setState(() => _status = value);
          _load();
        },
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: active ? AppColors.card : Colors.transparent,
            borderRadius: BorderRadius.circular(9),
            boxShadow: active
                ? [BoxShadow(color: Colors.black.withValues(alpha: .04), blurRadius: 6)]
                : null,
          ),
          child: Text(label,
              textAlign: TextAlign.center,
              style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: active ? AppColors.primary : AppColors.textSecondary)),
        ),
      ),
    );
  }
}

class _SummaryCard extends StatelessWidget {
  final DebtSummary summary;
  final String symbol;
  const _SummaryCard({required this.summary, required this.symbol});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('HUTANG', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
                const SizedBox(height: 4),
                Text(Fmt.money(summary.totalHutang, symbol: symbol),
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.expense)),
              ],
            ),
          ),
          Container(width: 1, height: 36, color: AppColors.border),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.only(left: 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('PIUTANG', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
                  const SizedBox(height: 4),
                  Text(Fmt.money(summary.totalPiutang, symbol: symbol),
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.income)),
                ],
              ),
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: AppColors.expense.withValues(alpha: .1),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text('${summary.activeCount} aktif',
                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.expense)),
          ),
        ],
      ),
    );
  }
}

class _DebtCard extends StatelessWidget {
  final Debt debt;
  final String symbol;
  final VoidCallback onPay;
  final VoidCallback onDelete;
  const _DebtCard({required this.debt, required this.symbol, required this.onPay, required this.onDelete});

  @override
  Widget build(BuildContext context) {
    final isHutang = debt.type == 'hutang';
    final color = isHutang ? AppColors.expense : AppColors.income;
    final pct = debt.amount <= 0 ? 0.0 : (debt.paid / debt.amount).clamp(0.0, 1.0);

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
      ),
      child: Column(
        children: [
          Row(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: .12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(isHutang ? Icons.arrow_downward : Icons.arrow_upward,
                    color: color, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(debt.person,
                        style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                    if ((debt.description ?? '').isNotEmpty)
                      Text(debt.description!,
                          maxLines: 1, overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                    if (debt.dueDate != null)
                      Text('Jatuh tempo ${Fmt.dateDay(debt.dueDate!)}',
                          style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(Fmt.money(debt.amount, symbol: symbol),
                      style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: color)),
                  if (debt.paid > 0)
                    Text('terbayar ${Fmt.money0(debt.paid)}',
                        style: const TextStyle(fontSize: 10, color: AppColors.textMuted)),
                ],
              ),
              PopupMenuButton<String>(
                onSelected: (v) {
                  if (v == 'delete') onDelete();
                },
                itemBuilder: (_) => const [
                  PopupMenuItem(value: 'delete', child: Text('Hapus')),
                ],
              ),
            ],
          ),
          if (debt.status == 'active') ...[
            const SizedBox(height: 10),
            ClipRRect(
              borderRadius: BorderRadius.circular(4),
              child: LinearProgressIndicator(
                value: pct,
                minHeight: 6,
                backgroundColor: AppColors.border,
                valueColor: AlwaysStoppedAnimation(color),
              ),
            ),
            const SizedBox(height: 10),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: onPay,
                style: OutlinedButton.styleFrom(
                  foregroundColor: color,
                  side: BorderSide(color: color.withValues(alpha: .5)),
                  minimumSize: const Size.fromHeight(38),
                ),
                icon: const Icon(Icons.payments_outlined, size: 16),
                label: Text(isHutang ? 'Bayar' : 'Terima',
                    style: const TextStyle(fontWeight: FontWeight.w700)),
              ),
            ),
          ] else
            const Padding(
              padding: EdgeInsets.only(top: 10),
              child: Row(
                children: [
                  Icon(Icons.check_circle, size: 16, color: AppColors.income),
                  SizedBox(width: 6),
                  Text('Lunas',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.income)),
                ],
              ),
            ),
        ],
      ),
    );
  }
}
