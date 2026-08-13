import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../models/category.dart';
import '../models/transaction.dart';
import '../models/wallet.dart';
import '../services/api_service.dart';
import '../services/widget_helper.dart';
import '../theme.dart';
import '../utils/format.dart';
import '../widgets/category_icon.dart';

class TransactionSheet extends StatefulWidget {
  final List<Category> categories;
  final List<Wallet> wallets;
  final Transaction? transaction;
  final bool isRecurring;

  const TransactionSheet({
    super.key,
    required this.categories,
    required this.wallets,
    this.transaction,
    this.isRecurring = false,
  });

  @override
  State<TransactionSheet> createState() => _TransactionSheetState();
}

class _TransactionSheetState extends State<TransactionSheet> {
  final _noteCtrl = TextEditingController();
  final _amountCtrl = TextEditingController();
  String _type = 'expense';
  int? _categoryId;
  int? _walletId;
  String _date = DateTime.now().toIso8601String().substring(0, 10);
  bool _recurring = false;
  bool _saving = false;
  File? _image;

  @override
  void initState() {
    super.initState();
    final t = widget.transaction;
    if (t != null) {
      _type = t.type;
      _categoryId = t.categoryId;
      _walletId = t.walletId;
      _date = t.date;
      _noteCtrl.text = t.note ?? '';
      _amountCtrl.text = Fmt.money0(t.amount);
      _recurring = widget.isRecurring;
    } else {
      _categoryId = widget.categories.isEmpty ? null : widget.categories.first.id;
      _walletId = widget.wallets.isEmpty
          ? null
          : (widget.wallets.any((w) => w.isDefault) ? widget.wallets.firstWhere((w) => w.isDefault).id : widget.wallets.first.id);
    }
  }

  @override
  void dispose() {
    _noteCtrl.dispose();
    _amountCtrl.dispose();
    super.dispose();
  }

  List<Category> get _visibleCats =>
      widget.categories.where((c) => c.type == _type).toList();

  Future<void> _pickDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime.tryParse(_date) ?? now,
      firstDate: DateTime(now.year - 5),
      lastDate: DateTime(now.year + 2),
    );
    if (picked != null) {
      setState(() {
        _date = '${picked.year.toString().padLeft(4, '0')}-${picked.month.toString().padLeft(2, '0')}-${picked.day.toString().padLeft(2, '0')}';
      });
    }
  }

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final img = await picker.pickImage(source: ImageSource.camera, maxWidth: 1280);
    if (img != null) {
      setState(() => _image = File(img.path));
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
    String? imageBase64;
    if (_image != null) {
      imageBase64 = await ApiService.instance.base64FromFile(_image!.path);
    }

    try {
      if (widget.transaction == null) {
        await ApiService.instance.storeTransaction(
          type: _type,
          amount: amount,
          categoryId: _categoryId,
          walletId: _walletId,
          note: _noteCtrl.text,
          date: _date,
          isRecurring: _recurring,
          imageBase64: imageBase64,
        );
      } else {
        await ApiService.instance.updateTransaction(
          widget.transaction!.id,
          type: _type,
          amount: amount,
          categoryId: _categoryId,
          walletId: _walletId,
          note: _noteCtrl.text,
          date: _date,
          imageBase64: imageBase64,
        );
      }
      if (!mounted) return;
      WidgetHelper.updateDashboardWidget();
      Navigator.pop(context, true);
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() => _saving = false);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
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
            Container(
              width: 40,
              height: 4,
              margin: const EdgeInsets.only(bottom: 14),
              decoration: BoxDecoration(
                color: AppColors.border,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            Text(
              widget.transaction == null ? 'Transaksi Baru' : 'Edit Transaksi',
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
            ),
            const SizedBox(height: 14),

            // Type toggle
            Container(
              padding: const EdgeInsets.all(4),
              decoration: BoxDecoration(
                color: AppColors.bg,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: _typeBtn('Pengeluaran', 'expense', AppColors.expense),
                  ),
                  Expanded(
                    child: _typeBtn('Pemasukan', 'income', AppColors.income),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Amount
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

            // Category chips
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

            // Wallet
            Text('REKENING', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
            const SizedBox(height: 8),
            DropdownButtonFormField<int?>(
              initialValue: _walletId,
              decoration: const InputDecoration(),
              items: [
                const DropdownMenuItem<int?>(value: null, child: Text('— Pilih rekening —')),
                ...widget.wallets.map((w) => DropdownMenuItem<int?>(
                      value: w.id,
                      child: Text('${w.icon} ${w.name}'),
                    )),
              ],
              onChanged: (v) => setState(() => _walletId = v),
            ),
            const SizedBox(height: 16),

            // Date
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
            const SizedBox(height: 16),

            // Note
            TextField(
              controller: _noteCtrl,
              decoration: const InputDecoration(labelText: 'CATATAN (OPSIONAL)', hintText: 'Tambahkan catatan...'),
            ),
            const SizedBox(height: 14),

            // Image
            if (_image == null)
              OutlinedButton.icon(
                onPressed: _pickImage,
                icon: const Icon(Icons.photo_camera_outlined, size: 18),
                label: const Text('Ambil Foto / Bukti'),
              )
            else
              Stack(
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: Image.file(_image!, height: 100, width: double.infinity, fit: BoxFit.cover),
                  ),
                  Positioned(
                    top: 4,
                    right: 4,
                    child: GestureDetector(
                      onTap: () => setState(() => _image = null),
                      child: Container(
                        padding: const EdgeInsets.all(4),
                        decoration: const BoxDecoration(color: Colors.black54, shape: BoxShape.circle),
                        child: const Icon(Icons.close, color: Colors.white, size: 16),
                      ),
                    ),
                  ),
                ],
              ),
            const SizedBox(height: 14),

            // Recurring
            if (widget.transaction == null)
              SwitchListTile(
                contentPadding: EdgeInsets.zero,
                title: const Text('🔁 Ulangi Setiap Bulan',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700)),
                subtitle: const Text('Otomatis dicatat tiap bulan berikutnya', style: TextStyle(fontSize: 12)),
                value: _recurring,
                activeThumbColor: AppColors.primary,
                onChanged: (v) => setState(() => _recurring = v),
              ),

            const SizedBox(height: 8),
            FilledButton(
              onPressed: _saving ? null : _save,
              child: _saving
                  ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : Text(widget.transaction == null
                      ? (_type == 'expense' ? 'Simpan Pengeluaran' : 'Simpan Pemasukan')
                      : 'Simpan Perubahan'),
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
