import 'package:flutter/material.dart';

import '../models/bill.dart';
import '../services/api_service.dart';
import '../theme.dart';
import '../utils/format.dart';

class BillsScreen extends StatefulWidget {
  final String symbol;
  const BillsScreen({super.key, this.symbol = 'Rp'});

  @override
  State<BillsScreen> createState() => _BillsScreenState();
}

class _BillsScreenState extends State<BillsScreen> {
  bool _loading = true;
  List<Bill> _bills = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final bills = await ApiService.instance.bills();
      if (!mounted) return;
      setState(() {
        _bills = bills;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loading = false);
    }
  }

  Future<void> _openAddEdit({Bill? bill}) async {
    final nameCtrl = TextEditingController(text: bill?.name ?? '');
    final amountCtrl = TextEditingController(text: bill == null ? '' : Fmt.money0(bill.amount));
    final notesCtrl = TextEditingController(text: bill?.notes ?? '');
    int dueDay = bill?.dueDay ?? 1;

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
                Text(bill == null ? 'Tagihan Baru' : 'Edit Tagihan',
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                const SizedBox(height: 16),
                TextField(
                  controller: nameCtrl,
                  decoration: const InputDecoration(labelText: 'NAMA TAGIHAN', hintText: 'contoh: Listrik'),
                ),
                const SizedBox(height: 14),
                TextField(
                  controller: amountCtrl,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(labelText: 'JUMLAH', prefixText: 'Rp '),
                ),
                const SizedBox(height: 14),
                DropdownButtonFormField<int>(
                  initialValue: dueDay,
                  decoration: const InputDecoration(labelText: 'TANGGAL JATUH TEMPO'),
                  items: List.generate(31, (i) => i + 1)
                      .map((d) => DropdownMenuItem<int>(value: d, child: Text('Tanggal $d')))
                      .toList(),
                  onChanged: (v) => setSheetState(() => dueDay = v!),
                ),
                const SizedBox(height: 14),
                TextField(
                  controller: notesCtrl,
                  decoration: const InputDecoration(labelText: 'CATATAN (OPSIONAL)'),
                ),
                const SizedBox(height: 20),
                FilledButton(
                  onPressed: () {
                    final name = nameCtrl.text.trim();
                    final amount = double.tryParse(Fmt.parseAmount(amountCtrl.text)) ?? 0;
                    if (name.isEmpty || amount <= 0) return;
                    Navigator.pop(ctx, {
                      'name': name,
                      'amount': amount,
                      'dueDay': dueDay,
                      'notes': notesCtrl.text,
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
      try {
        await ApiService.instance.storeBill(
          id: bill?.id,
          name: saved['name'] as String,
          amount: saved['amount'] as double,
          dueDay: saved['dueDay'] as int,
          notes: saved['notes'] as String,
        );
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Tagihan disimpan')));
      } on ApiException catch (e) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
      }
      _load();
    }
  }

  Future<void> _delete(Bill b) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Hapus tagihan?'),
        content: Text('${b.name} akan dihapus permanen.'),
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
      await ApiService.instance.deleteBill(b.id);
      _load();
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  @override
  Widget build(BuildContext context) {
    final sorted = [..._bills]..sort((a, b) => a.dueDay.compareTo(b.dueDay));
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(title: const Text('Tagihan Rutin')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _openAddEdit,
        backgroundColor: AppColors.primary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Tambah', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
                children: [
                  const Text('Jadwal tagihan bulanan yang harus dibayar rutin.',
                      style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                  const SizedBox(height: 12),
                  if (sorted.isEmpty)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 60),
                      child: Column(
                        children: [
                          Icon(Icons.receipt_long_outlined, size: 44, color: AppColors.textMuted),
                          SizedBox(height: 10),
                          Text('Belum ada tagihan',
                              style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
                          SizedBox(height: 4),
                          Text('Tambahkan tagihan rutin seperti listrik, internet, dll.',
                              textAlign: TextAlign.center,
                              style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                        ],
                      ),
                    )
                  else
                    ...sorted.map((b) => _BillCard(
                          bill: b,
                          symbol: widget.symbol,
                          onEdit: () => _openAddEdit(bill: b),
                          onDelete: () => _delete(b),
                        )),
                ],
              ),
            ),
    );
  }
}

class _BillCard extends StatelessWidget {
  final Bill bill;
  final String symbol;
  final VoidCallback onEdit;
  final VoidCallback onDelete;
  const _BillCard({required this.bill, required this.symbol, required this.onEdit, required this.onDelete});

  String get _daysLabel {
    final d = bill.daysLeft;
    if (d == null) return '';
    if (d < 0) return '${-d} hari lalu';
    if (d == 0) return 'Hari ini';
    return '$d hari lagi';
  }

  @override
  Widget build(BuildContext context) {
    final urgent = bill.daysLeft != null && bill.daysLeft! <= 2;
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
            color: urgent ? const Color(0xFFF59E0B) : AppColors.border, width: urgent ? 1.5 : 1),
        boxShadow: AppColors.cardShadow,
      ),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: AppColors.primaryLight.withValues(alpha: .1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Center(child: Text('📋', style: TextStyle(fontSize: 22))),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(bill.name,
                    style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                const SizedBox(height: 2),
                Text(
                  'Tiap tanggal ${bill.dueDay} · ${Fmt.money(bill.amount, symbol: symbol)}',
                  style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                ),
                if (bill.notes.isNotEmpty)
                  Text(bill.notes,
                      maxLines: 1, overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
              ],
            ),
          ),
          if (_daysLabel.isNotEmpty)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: (urgent ? const Color(0xFFF59E0B) : AppColors.textMuted).withValues(alpha: .12),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(_daysLabel,
                  style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w800,
                      color: urgent ? const Color(0xFFD97706) : AppColors.textSecondary)),
            ),
          PopupMenuButton<String>(
            onSelected: (v) {
              if (v == 'edit') onEdit();
              if (v == 'delete') onDelete();
            },
            itemBuilder: (_) => const [
              PopupMenuItem(value: 'edit', child: Text('Edit')),
              PopupMenuItem(value: 'delete', child: Text('Hapus')),
            ],
          ),
        ],
      ),
    );
  }
}
