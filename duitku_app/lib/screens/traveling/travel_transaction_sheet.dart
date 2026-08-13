import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/category.dart';
import '../../models/wallet.dart';
import '../../providers/app_data_provider.dart';
import '../../providers/travel_provider.dart';
import '../../theme.dart';
import '../../utils/format.dart';
import '../../widgets/category_icon.dart';

class TravelTransactionSheet extends StatefulWidget {
  final String tripId;
  final List<Category> categories;
  final List<Wallet> wallets;

  const TravelTransactionSheet({
    super.key,
    required this.tripId,
    required this.categories,
    required this.wallets,
  });

  @override
  State<TravelTransactionSheet> createState() => _TravelTransactionSheetState();
}

class _TravelTransactionSheetState extends State<TravelTransactionSheet> {
  final _amountCtrl = TextEditingController();
  final _noteCtrl = TextEditingController();
  String _type = 'expense';
  int? _categoryId;
  int? _walletId;
  String _date = DateTime.now().toIso8601String().substring(0, 10);
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _categoryId = widget.categories.isEmpty
        ? null
        : widget.categories.where((c) => c.type == 'expense').firstOrNull?.id ?? widget.categories.first.id;
    _walletId = widget.wallets.isEmpty
        ? null
        : (widget.wallets.any((w) => w.isDefault)
            ? widget.wallets.firstWhere((w) => w.isDefault).id
            : widget.wallets.first.id);
  }

  @override
  void dispose() {
    _amountCtrl.dispose();
    _noteCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime.tryParse(_date) ?? now,
      firstDate: DateTime(now.year - 1),
      lastDate: DateTime(now.year + 2),
    );
    if (picked != null) {
      setState(() {
        _date =
            '${picked.year.toString().padLeft(4, '0')}-${picked.month.toString().padLeft(2, '0')}-${picked.day.toString().padLeft(2, '0')}';
      });
    }
  }

  Future<void> _save() async {
    final amountStr = Fmt.parseAmount(_amountCtrl.text);
    final amount = double.tryParse(amountStr) ?? 0;
    if (amount <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Masukkan nominal yang valid')));
      return;
    }

    setState(() => _saving = true);
    try {
      await context.read<TravelProvider>().addTransaction(
            tripId: widget.tripId,
            type: _type,
            amount: amount,
            categoryId: _categoryId,
            walletId: _walletId,
            note: _noteCtrl.text.trim(),
            date: _date,
          );
      if (!mounted) return;
      // Refresh wallets/categories di home jika perlu
      await context.read<AppDataProvider>().reloadWallets();
      if (!mounted) return;
      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal menyimpan: $e')));
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  List<Category> get _visibleCats => widget.categories.where((c) => c.type == _type).toList();

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
            Container(
              width: 40,
              height: 4,
              margin: const EdgeInsets.only(bottom: 14),
              decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(2)),
            ),
            const Text(
              'Transaksi Traveling',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
            ),
            const SizedBox(height: 14),
            Container(
              padding: const EdgeInsets.all(4),
              decoration: BoxDecoration(color: AppColors.bg, borderRadius: BorderRadius.circular(12)),
              child: Row(
                children: [
                  Expanded(child: _typeBtn('Pengeluaran', 'expense', AppColors.expense)),
                  Expanded(child: _typeBtn('Pemasukan', 'income', AppColors.income)),
                ],
              ),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _amountCtrl,
              keyboardType: TextInputType.number,
              style: const TextStyle(fontSize: 30, fontWeight: FontWeight.w800),
              textAlign: TextAlign.center,
              decoration: const InputDecoration(
                hintText: '0',
                prefixText: 'Rp ',
                hintStyle: TextStyle(color: AppColors.textMuted, fontWeight: FontWeight.w500),
              ),
            ),
            const SizedBox(height: 16),
            Text('KATEGORI', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: _visibleCats.map((c) {
                final selected = _categoryId == c.id;
                final color = parseColor(c.color);
                return ChoiceChip(
                  label: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(categoryIcon(c.icon), size: 16, color: selected ? Colors.white : color),
                      const SizedBox(width: 4),
                      Text(c.name),
                    ],
                  ),
                  selected: selected,
                  onSelected: (_) => setState(() => _categoryId = c.id),
                  selectedColor: color,
                  labelStyle: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: selected ? Colors.white : AppColors.textPrimary,
                  ),
                  backgroundColor: AppColors.bg,
                  side: BorderSide(color: selected ? color : AppColors.border),
                  showCheckmark: false,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                );
              }).toList(),
            ),
            const SizedBox(height: 16),
            Text('REKENING', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
            const SizedBox(height: 8),
            DropdownButtonFormField<int?>(
              initialValue: _walletId,
              items: [
                const DropdownMenuItem<int?>(value: null, child: Text('— Pilih rekening —')),
                ...widget.wallets.map((w) => DropdownMenuItem<int?>(
                      value: w.id,
                      child: Text('${w.icon} ${w.name}'),
                    )),
              ],
              onChanged: (v) => setState(() => _walletId = v),
            ),
            const SizedBox(height: 14),
            InkWell(
              onTap: _pickDate,
              borderRadius: BorderRadius.circular(12),
              child: InputDecorator(
                decoration: const InputDecoration(labelText: 'TANGGAL'),
                child: Row(
                  children: [
                    const Icon(Icons.calendar_today, size: 18, color: AppColors.textMuted),
                    const SizedBox(width: 10),
                    Text(Fmt.dateDay(_date), style: const TextStyle(fontWeight: FontWeight.w600)),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _noteCtrl,
              decoration: const InputDecoration(labelText: 'CATATAN (OPSIONAL)', hintText: 'Makan malam, tiket masuk, dll'),
            ),
            const SizedBox(height: 20),
            FilledButton(
              onPressed: _saving ? null : _save,
              child: _saving
                  ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : Text(_type == 'expense' ? 'Simpan Pengeluaran' : 'Simpan Pemasukan'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _typeBtn(String label, String type, Color color) {
    final active = _type == type;
    return GestureDetector(
      onTap: () => setState(() => _type = type),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10),
        decoration: BoxDecoration(
          color: active ? color : Colors.transparent,
          borderRadius: BorderRadius.circular(9),
        ),
        child: Text(
          label,
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w700,
            color: active ? Colors.white : AppColors.textSecondary,
          ),
        ),
      ),
    );
  }
}
