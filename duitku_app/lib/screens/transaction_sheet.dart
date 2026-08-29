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
import 'scan/ocr_receipt_screen.dart';

class TransactionSheet extends StatefulWidget {
  final List<Category> categories;
  final List<Wallet> wallets;
  final Transaction? transaction;
  final bool isRecurring;
  final double? initialAmount;
  final String? initialNote;
  final String? initialDate;
  final String? initialImagePath;
  final String? initialType;
  final int? initialWalletId;
  final int? initialCategoryId;

  const TransactionSheet({
    super.key,
    required this.categories,
    required this.wallets,
    this.transaction,
    this.isRecurring = false,
    this.initialAmount,
    this.initialNote,
    this.initialDate,
    this.initialImagePath,
    this.initialType,
    this.initialWalletId,
    this.initialCategoryId,
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
      if (widget.initialType != null && widget.initialType!.isNotEmpty) {
        _type = widget.initialType!;
      }
      if (widget.initialWalletId != null) {
        _walletId = widget.initialWalletId;
      }
      if (widget.initialCategoryId != null) {
        _categoryId = widget.initialCategoryId;
      }
      if (widget.initialAmount != null && widget.initialAmount! > 0) {
        _amountCtrl.text = Fmt.money0(widget.initialAmount);
      }
      if (widget.initialNote != null && widget.initialNote!.isNotEmpty) {
        _noteCtrl.text = widget.initialNote!;
      }
      if (widget.initialDate != null && widget.initialDate!.isNotEmpty) {
        _date = widget.initialDate!;
      }
      if (widget.initialImagePath != null && widget.initialImagePath!.isNotEmpty) {
        final f = File(widget.initialImagePath!);
        if (f.existsSync()) {
          _image = f;
        }
      }
      _categoryId ??= widget.categories.isEmpty ? null : widget.categories.first.id;
      _walletId ??= widget.wallets.isEmpty
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

  void _addAmount(double add) {
    final currentStr = Fmt.parseAmount(_amountCtrl.text);
    final current = double.tryParse(currentStr) ?? 0;
    final total = current + add;
    _amountCtrl.text = Fmt.money0(total);
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: SafeArea(
        top: false,
        child: SingleChildScrollView(
          keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
            Center(
              child: Container(
                width: 36,
                height: 4,
                margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(
                  color: AppColors.border,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  widget.transaction == null ? 'Catat Transaksi' : 'Edit Transaksi',
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.textPrimary, letterSpacing: -0.3),
                ),
                Row(
                  children: [
                    TextButton.icon(
                      onPressed: () async {
                        final res = await Navigator.push<OcrReceiptResult>(
                          context,
                          MaterialPageRoute(builder: (_) => const OcrReceiptScreen()),
                        );
                        if (res != null) {
                          setState(() {
                            _amountCtrl.text = Fmt.money0(res.amount);
                            _noteCtrl.text = res.note;
                            _date = res.date;
                            _type = 'expense';
                            if (res.imagePath != null) _image = File(res.imagePath!);
                          });
                        }
                      },
                      icon: const Icon(Icons.camera_alt_rounded, size: 16, color: AppColors.primary),
                      label: const Text('Scan Nota', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppColors.primary)),
                      style: TextButton.styleFrom(
                        backgroundColor: AppColors.primarySubtle,
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        minimumSize: Size.zero,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                    ),
                    const SizedBox(width: 8),
                    IconButton(
                      onPressed: () => Navigator.pop(context),
                      icon: const Icon(Icons.close_rounded, color: AppColors.textMuted, size: 22),
                      padding: EdgeInsets.zero,
                      constraints: const BoxConstraints(),
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 14),

            // Type toggle
            Container(
              padding: const EdgeInsets.all(4),
              decoration: BoxDecoration(
                color: AppColors.bg,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.border),
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
            const SizedBox(height: 14),

            // Amount input
            TextField(
              controller: _amountCtrl,
              keyboardType: TextInputType.number,
              style: const TextStyle(fontSize: 28, fontWeight: FontWeight.w900, letterSpacing: -0.5),
              textAlign: TextAlign.center,
              decoration: InputDecoration(
                hintText: '0',
                prefixText: 'Rp ',
                prefixStyle: const TextStyle(fontSize: 20, fontWeight: FontWeight.w700, color: AppColors.textMuted),
                hintStyle: const TextStyle(color: AppColors.textMuted, fontWeight: FontWeight.w500),
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: AppColors.border)),
              ),
            ),
            const SizedBox(height: 10),

            // Quick add amount chips
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _quickAmountChip('+10rb', 10000),
                  _quickAmountChip('+20rb', 20000),
                  _quickAmountChip('+50rb', 50000),
                  _quickAmountChip('+100rb', 100000),
                  _quickAmountChip('+500rb', 500000),
                  ActionChip(
                    label: const Text('Reset', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.expense)),
                    padding: const EdgeInsets.symmetric(horizontal: 4),
                    visualDensity: VisualDensity.compact,
                    backgroundColor: AppColors.expenseSubtle,
                    side: BorderSide.none,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    onPressed: () => _amountCtrl.text = '',
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Category chips
            const Text('KATEGORI', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, letterSpacing: .6, color: AppColors.textMuted)),
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
                      Icon(categoryIcon(c.icon), size: 15, color: selected ? Colors.white : color),
                      const SizedBox(width: 4),
                      Text(c.name),
                    ],
                  ),
                  selected: selected,
                  onSelected: (_) => setState(() => _categoryId = c.id),
                  selectedColor: color,
                  labelStyle: TextStyle(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w600,
                    color: selected ? Colors.white : AppColors.textPrimary,
                  ),
                  backgroundColor: AppColors.bg,
                  side: BorderSide(color: selected ? color : AppColors.border),
                  showCheckmark: false,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                );
              }).toList(),
            ),
            const SizedBox(height: 16),

            // Wallet
            const Text('REKENING / DOMPET', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, letterSpacing: .6, color: AppColors.textMuted)),
            const SizedBox(height: 8),
            DropdownButtonFormField<int?>(
              initialValue: _walletId,
              decoration: const InputDecoration(),
              items: [
                const DropdownMenuItem<int?>(value: null, child: Text('— Pilih rekening —')),
                ...widget.wallets.map((w) => DropdownMenuItem<int?>(
                      value: w.id,
                      child: Text('${w.icon} ${w.name} (${Fmt.money0(w.balance)})'),
                    )),
              ],
              onChanged: (v) => setState(() => _walletId = v),
            ),
            const SizedBox(height: 16),

            // Date
            InkWell(
              onTap: _pickDate,
              borderRadius: BorderRadius.circular(14),
              child: InputDecorator(
                decoration: const InputDecoration(labelText: 'TANGGAL TRANSAKSI'),
                child: Row(
                  children: [
                    const Icon(Icons.calendar_today_rounded, size: 18, color: AppColors.textMuted),
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
              decoration: const InputDecoration(labelText: 'CATATAN (OPSIONAL)', hintText: 'Misal: Beli makan siang...'),
            ),
            const SizedBox(height: 14),

            // Image
            if (_image == null)
              OutlinedButton.icon(
                onPressed: _pickImage,
                icon: const Icon(Icons.photo_camera_outlined, size: 18),
                label: const Text('Ambil Foto Struk / Bukti'),
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size.fromHeight(44),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  side: const BorderSide(color: AppColors.border),
                ),
              )
            else
              Stack(
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(14),
                    child: Image.file(_image!, height: 100, width: double.infinity, fit: BoxFit.cover),
                  ),
                  Positioned(
                    top: 6,
                    right: 6,
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
                    style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                subtitle: const Text('Otomatis dicatat pada bulan berikutnya', style: TextStyle(fontSize: 11.5, color: AppColors.textMuted)),
                value: _recurring,
                activeTrackColor: AppColors.primaryLight,
                onChanged: (v) => setState(() => _recurring = v),
              ),

            const SizedBox(height: 10),
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
    ),
  );
  }

  Widget _quickAmountChip(String label, double amount) {
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: ActionChip(
        label: Text(label, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.primary)),
        padding: const EdgeInsets.symmetric(horizontal: 4),
        visualDensity: VisualDensity.compact,
        backgroundColor: AppColors.primarySubtle,
        side: const BorderSide(color: AppColors.primaryLight, width: 0.8),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        onPressed: () => _addAmount(amount),
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
          borderRadius: BorderRadius.circular(10),
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
