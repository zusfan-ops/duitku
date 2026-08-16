import 'package:flutter/material.dart';

import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class PosIngredientsScreen extends StatefulWidget {
  const PosIngredientsScreen({super.key});

  @override
  State<PosIngredientsScreen> createState() => _PosIngredientsScreenState();
}

class _PosIngredientsScreenState extends State<PosIngredientsScreen> {
  bool _loading = true;
  String? _error;
  List<dynamic> _ingredients = [];
  int _lowStockCount = 0;

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
      final res = await ApiService.instance.getPosIngredients();
      if (!mounted) return;
      setState(() {
        _ingredients = (res['ingredients'] as List?) ?? [];
        _lowStockCount = int.tryParse('${res['low_stock_count']}') ?? 0;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  void _openAddEditDialog({Map<String, dynamic>? ingredient}) {
    final nameCtrl = TextEditingController(text: ingredient?['name']?.toString() ?? '');
    final unitCtrl = TextEditingController(text: ingredient?['unit']?.toString() ?? 'gram');
    final stockCtrl = TextEditingController(text: ingredient != null ? '${ingredient['stock']}' : '0');
    final minStockCtrl = TextEditingController(text: ingredient != null ? '${ingredient['min_stock']}' : '10');
    final costCtrl = TextEditingController(text: ingredient != null ? '${ingredient['cost_per_unit']}' : '0');

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
          left: 20,
          right: 20,
          top: 16,
        ),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(ingredient == null ? 'Tambah Bahan Baku Baru' : 'Edit Bahan Baku',
                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
                  IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                ],
              ),
              const SizedBox(height: 12),
              TextField(
                controller: nameCtrl,
                decoration: const InputDecoration(labelText: 'Nama Bahan (cth: Biji Kopi Arabica, Susu Full Cream)'),
              ),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: unitCtrl,
                      decoration: const InputDecoration(labelText: 'Satuan (gram, ml, pcs, kg)'),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: TextField(
                      controller: stockCtrl,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      decoration: const InputDecoration(labelText: 'Stok Saat Ini'),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: minStockCtrl,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      decoration: const InputDecoration(labelText: 'Batas Minimum Stok'),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: TextField(
                      controller: costCtrl,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      decoration: const InputDecoration(labelText: 'Harga Beli per Satuan (Rp)'),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              FilledButton.icon(
                onPressed: () async {
                  if (nameCtrl.text.trim().isEmpty) return;
                  try {
                    await ApiService.instance.storePosIngredient(
                      id: ingredient != null ? int.tryParse('${ingredient['id']}') ?? 0 : 0,
                      name: nameCtrl.text.trim(),
                      unit: unitCtrl.text.trim(),
                      stock: double.tryParse(stockCtrl.text) ?? 0,
                      minStock: double.tryParse(minStockCtrl.text) ?? 10,
                      costPerUnit: double.tryParse(costCtrl.text) ?? 0,
                    );
                    if (ctx.mounted) Navigator.pop(ctx);
                    _load();
                  } catch (e) {
                    if (ctx.mounted) {
                      ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(content: Text('Gagal: $e')));
                    }
                  }
                },
                icon: const Icon(Icons.check_circle_rounded),
                label: const Text('Simpan Bahan Baku'),
                style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(44)),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _openRestockDialog(Map<String, dynamic> ingredient) {
    final addStockCtrl = TextEditingController();
    final costCtrl = TextEditingController(text: '${ingredient['cost_per_unit']}');

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
          left: 20,
          right: 20,
          top: 16,
        ),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Restock: ${ingredient['name']}', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
                  IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                ],
              ),
              const SizedBox(height: 10),
              Text('Stok saat ini: ${ingredient['stock']} ${ingredient['unit']}',
                  style: const TextStyle(color: AppColors.textMuted, fontSize: 12)),
              const SizedBox(height: 12),
              TextField(
                controller: addStockCtrl,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                decoration: InputDecoration(
                  labelText: 'Jumlah Tambah Stok (${ingredient['unit']})',
                  hintText: 'cth: 500',
                ),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: costCtrl,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                decoration: InputDecoration(
                  labelText: 'Harga Beli Terbaru per ${ingredient['unit']} (Opsional)',
                ),
              ),
              const SizedBox(height: 16),
              FilledButton.icon(
                onPressed: () async {
                  final addStock = double.tryParse(addStockCtrl.text) ?? 0;
                  if (addStock <= 0) return;
                  try {
                    await ApiService.instance.restockPosIngredient(
                      id: int.tryParse('${ingredient['id']}') ?? 0,
                      addStock: addStock,
                      costPerUnit: double.tryParse(costCtrl.text),
                    );
                    if (ctx.mounted) Navigator.pop(ctx);
                    _load();
                  } catch (e) {
                    if (ctx.mounted) {
                      ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(content: Text('Gagal: $e')));
                    }
                  }
                },
                icon: const Icon(Icons.add_shopping_cart_rounded),
                label: const Text('Tambah Stok Masuk'),
                style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(44)),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Bahan Baku & Resep (BOM)'),
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _load),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _openAddEditDialog(),
        icon: const Icon(Icons.add_rounded),
        label: const Text('Bahan Baru', style: TextStyle(fontWeight: FontWeight.bold)),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text('Error: $_error'))
              : ListView(
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 100),
                  children: [
                    if (_lowStockCount > 0)
                      Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFEF2F2),
                          border: Border.all(color: const Color(0xFFFCA5A5)),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.warning_amber_rounded, color: Color(0xFFDC2626)),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                '$_lowStockCount bahan baku mendekati batas minimum stok!',
                                style: const TextStyle(color: Color(0xFFDC2626), fontWeight: FontWeight.bold, fontSize: 12),
                              ),
                            ),
                          ],
                        ),
                      ),

                    if (_ingredients.isEmpty)
                      const Padding(
                        padding: EdgeInsets.symmetric(vertical: 60),
                        child: Center(
                          child: Column(
                            children: [
                              Icon(Icons.inventory_2_outlined, size: 48, color: AppColors.textMuted),
                              SizedBox(height: 10),
                              Text('Belum ada data bahan baku', style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.textMuted)),
                              SizedBox(height: 4),
                              Text('Tekan tombol "Bahan Baru" untuk menambahkan.', style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                            ],
                          ),
                        ),
                      )
                    else
                      ..._ingredients.map((ing) {
                        final stock = double.tryParse('${ing['stock']}') ?? 0;
                        final minStock = double.tryParse('${ing['min_stock']}') ?? 10;
                        final isLow = stock <= minStock;
                        final unit = ing['unit'] ?? 'gram';
                        final cost = double.tryParse('${ing['cost_per_unit']}') ?? 0;

                        return Container(
                          margin: const EdgeInsets.only(bottom: 10),
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: AppColors.card,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: isLow ? const Color(0xFFFCA5A5) : AppColors.border),
                          ),
                          child: Row(
                            children: [
                              Container(
                                width: 44,
                                height: 44,
                                decoration: BoxDecoration(
                                  color: isLow ? const Color(0xFFFEE2E2) : AppColors.primaryLight.withValues(alpha: .12),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Center(
                                  child: Icon(
                                    Icons.grain_rounded,
                                    color: isLow ? const Color(0xFFDC2626) : AppColors.primary,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        Flexible(
                                          child: Text(
                                            ing['name'] ?? '',
                                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                        if (isLow) ...[
                                          const SizedBox(width: 6),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                                            decoration: BoxDecoration(
                                              color: const Color(0xFFDC2626),
                                              borderRadius: BorderRadius.circular(4),
                                            ),
                                            child: const Text('KRITIS', style: TextStyle(color: Colors.white, fontSize: 8, fontWeight: FontWeight.bold)),
                                          ),
                                        ],
                                      ],
                                    ),
                                    const SizedBox(height: 2),
                                    Text('Stok: $stock $unit (Min: $minStock $unit) · Rp ${Fmt.money0(cost)}/$unit',
                                        style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                                  ],
                                ),
                              ),
                              IconButton(
                                icon: const Icon(Icons.add_shopping_cart_rounded, size: 20, color: AppColors.primary),
                                tooltip: 'Restock',
                                onPressed: () => _openRestockDialog(ing as Map<String, dynamic>),
                              ),
                              PopupMenuButton<String>(
                                onSelected: (v) {
                                  if (v == 'edit') _openAddEditDialog(ingredient: ing as Map<String, dynamic>);
                                  if (v == 'delete') {
                                    ApiService.instance.deletePosIngredient(int.tryParse('${ing['id']}') ?? 0).then((_) => _load());
                                  }
                                },
                                itemBuilder: (_) => const [
                                  PopupMenuItem(value: 'edit', child: Text('Edit')),
                                  PopupMenuItem(value: 'delete', child: Text('Hapus', style: TextStyle(color: Colors.red))),
                                ],
                              ),
                            ],
                          ),
                        );
                      }),
                  ],
                ),
    );
  }
}
