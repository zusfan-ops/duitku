import 'package:flutter/material.dart';

import '../../models/category.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';
import '../../widgets/category_icon.dart';

class RecurringScreen extends StatefulWidget {
  const RecurringScreen({super.key});

  @override
  State<RecurringScreen> createState() => _RecurringScreenState();
}

class _RecurringScreenState extends State<RecurringScreen> {
  bool _loading = true;
  List<dynamic> _recurring = [];
  List<Category> _categories = [];
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
        ApiService.instance.recurringList(),
        ApiService.instance.settings(),
      ]);
      if (!mounted) return;
      setState(() {
        _recurring  = (res[0]['recurring'] as List<dynamic>? ?? []);
        _symbol     = res[1]['symbol']?.toString() ?? 'Rp';
        _categories = ((res[1]['categories'] as List<dynamic>?) ?? [])
            .map((e) => Category.fromJson(e as Map<String, dynamic>))
            .toList();
        _loading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _processRecurring() async {
    try {
      final res = await ApiService.instance.processRecurring();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res['message']?.toString() ?? 'Selesai.')),
      );
      final count = res['processed'] as int? ?? 0;
      if (count > 0) _load();
    } on ApiException catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  Future<void> _executeSingle(int id, String name) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Catat Transaksi Sekarang?'),
        content: Text('Transaksi "$name" akan langsung dicatat sebagai pengeluaran/pemasukan baru hari ini dan jadwal berikutnya akan dimajukan otomatis.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Catat Sekarang'),
          ),
        ],
      ),
    );
    if (ok != true) return;

    try {
      final res = await ApiService.instance.executeRecurring(id);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res['message']?.toString() ?? 'Transaksi berhasil dicatat!')),
      );
      _load();
    } on ApiException catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  Future<void> _delete(int id) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Hentikan transaksi berulang?'),
        content: const Text('Transaksi ini tidak akan diproses lagi secara otomatis.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: TextButton.styleFrom(foregroundColor: AppColors.expense),
            child: const Text('Hentikan'),
          ),
        ],
      ),
    );
    if (ok != true) return;
    try {
      await ApiService.instance.deleteRecurring(id);
      _load();
    } on ApiException catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  Future<void> _showAddSheet() async {
    String type      = 'expense';
    String frequency = 'monthly';
    int? categoryId;
    final amtCtrl  = TextEditingController();
    final noteCtrl = TextEditingController();
    DateTime startDate = DateTime.now();

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
                Center(child: Container(width: 40, height: 4,
                    decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(2)))),
                const SizedBox(height: 12),
                const Text('Transaksi Berulang Baru', textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(4),
                  decoration: BoxDecoration(color: AppColors.bg, borderRadius: BorderRadius.circular(12)),
                  child: Row(children: [
                    Expanded(child: _typeBtn('Pengeluaran', 'expense', type, () => setSS(() => type = 'expense'))),
                    Expanded(child: _typeBtn('Pemasukan', 'income', type, () => setSS(() => type = 'income'))),
                  ]),
                ),
                const SizedBox(height: 14),
                DropdownButtonFormField<int>(
                  initialValue: categoryId,
                  decoration: const InputDecoration(labelText: 'KATEGORI'),
                  items: [
                    const DropdownMenuItem(value: null, child: Text('— Tanpa Kategori —')),
                    ..._categories.where((c) => c.type == type).map((c) => DropdownMenuItem(
                          value: c.id,
                          child: Row(children: [
                            Icon(categoryIcon(c.icon), size: 16, color: parseColor(c.color)),
                            const SizedBox(width: 8), Text(c.name),
                          ]),
                        )),
                  ],
                  onChanged: (v) => setSS(() => categoryId = v),
                ),
                const SizedBox(height: 14),
                TextField(controller: amtCtrl, keyboardType: TextInputType.number,
                    decoration: InputDecoration(labelText: 'JUMLAH', prefixText: '$_symbol ')),
                const SizedBox(height: 14),
                DropdownButtonFormField<String>(
                  initialValue: frequency,
                  decoration: const InputDecoration(labelText: 'FREKUENSI'),
                  items: const [
                    DropdownMenuItem(value: 'monthly', child: Text('Bulanan')),
                    DropdownMenuItem(value: 'weekly',  child: Text('Mingguan')),
                    DropdownMenuItem(value: 'yearly',  child: Text('Tahunan')),
                  ],
                  onChanged: (v) => setSS(() => frequency = v ?? 'monthly'),
                ),
                const SizedBox(height: 14),
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  title: const Text('MULAI TANGGAL', style: TextStyle(fontSize: 12, color: AppColors.textMuted, fontWeight: FontWeight.w600)),
                  subtitle: Text(Fmt.dateDay(startDate.toIso8601String().substring(0, 10)),
                      style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
                  trailing: const Icon(Icons.calendar_today_outlined, size: 18, color: AppColors.primary),
                  onTap: () async {
                    final picked = await showDatePicker(
                      context: ctx, initialDate: startDate,
                      firstDate: DateTime(2020), lastDate: DateTime(2030),
                    );
                    if (picked != null) setSS(() => startDate = picked);
                  },
                ),
                const SizedBox(height: 8),
                TextField(controller: noteCtrl, decoration: const InputDecoration(labelText: 'CATATAN (OPSIONAL)')),
                const SizedBox(height: 20),
                FilledButton(
                  onPressed: () async {
                    final amount = double.tryParse(Fmt.parseAmount(amtCtrl.text)) ?? 0;
                    if (amount <= 0) return;
                    try {
                      await ApiService.instance.storeRecurring(
                        type: type, amount: amount, frequency: frequency,
                        startDate: startDate.toIso8601String().substring(0, 10),
                        categoryId: categoryId, note: noteCtrl.text,
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

  Widget _typeBtn(String label, String value, String current, VoidCallback onTap) {
    final active = current == value;
    final color  = value == 'expense' ? AppColors.expense : AppColors.income;
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10),
        decoration: BoxDecoration(color: active ? color : Colors.transparent, borderRadius: BorderRadius.circular(9)),
        child: Text(label, textAlign: TextAlign.center,
            style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700,
                color: active ? Colors.white : AppColors.textSecondary)),
      ),
    );
  }

  String _freqLabel(String? freq) => switch (freq) {
    'weekly' => 'Mingguan', 'yearly' => 'Tahunan', _ => 'Bulanan',
  };

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Transaksi Berulang'),
        actions: [
          TextButton.icon(
            onPressed: _processRecurring,
            icon: const Icon(Icons.play_circle_outline, size: 18),
            label: const Text('Proses'),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showAddSheet,
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: const Text('Tambah', style: TextStyle(fontWeight: FontWeight.w700)),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: _recurring.isEmpty
                  ? ListView(children: const [
                      SizedBox(height: 80),
                      Center(child: Column(children: [
                        Text('🔁', style: TextStyle(fontSize: 56)),
                        SizedBox(height: 12),
                        Text('Belum ada transaksi berulang',
                            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                        SizedBox(height: 6),
                        Padding(padding: EdgeInsets.symmetric(horizontal: 40),
                          child: Text('Tambah transaksi seperti gaji, cicilan, atau langganan agar diproses otomatis.',
                              textAlign: TextAlign.center,
                              style: TextStyle(fontSize: 13, color: AppColors.textMuted))),
                      ])),
                    ])
                  : ListView.builder(
                      padding: const EdgeInsets.fromLTRB(16, 12, 16, 100),
                      itemCount: _recurring.length,
                      itemBuilder: (context, index) {
                        final r  = _recurring[index] as Map<String, dynamic>;
                        final id = int.tryParse('${r['id']}') ?? 0;
                        final isIncome = r['type']?.toString() == 'income';
                        final color = parseColor(r['category_color']?.toString() ?? '#6B7280');
                        return Container(
                          margin: const EdgeInsets.only(bottom: 10),
                          decoration: BoxDecoration(color: AppColors.card, borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: AppColors.border), boxShadow: AppColors.cardShadow),
                          child: ListTile(
                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                            leading: Container(
                              width: 42, height: 42,
                              decoration: BoxDecoration(color: color.withValues(alpha: .12), borderRadius: BorderRadius.circular(12)),
                              child: Icon(categoryIcon(r['category_icon']?.toString() ?? 'other'), color: color, size: 20),
                            ),
                            title: Text(r['category_name']?.toString() ?? 'Transaksi',
                                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700)),
                            subtitle: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                              Text('${isIncome ? '+' : '-'} ${Fmt.money(Fmt.toDouble(r['amount']), symbol: _symbol)}',
                                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800,
                                      color: isIncome ? AppColors.income : AppColors.expense)),
                              Text('${_freqLabel(r['frequency']?.toString())} · Berikutnya ${Fmt.dateDay(r['next_date']?.toString() ?? '')}',
                                  style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                              if ((r['note']?.toString() ?? '').isNotEmpty)
                                Text(r['note'].toString(), style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                            ]),
                            trailing: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                IconButton(
                                  tooltip: 'Eksekusi & Catat Sekarang',
                                  onPressed: () => _executeSingle(id, r['category_name']?.toString() ?? 'Transaksi'),
                                  icon: const Icon(Icons.flash_on_rounded, color: AppColors.primary, size: 22),
                                ),
                                IconButton(
                                  tooltip: 'Hentikan',
                                  onPressed: () => _delete(id),
                                  icon: const Icon(Icons.stop_circle_outlined, color: AppColors.expense, size: 22),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
            ),
    );
  }
}
