import 'package:flutter/material.dart';
import '../../models/pos_product.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class PosProductsScreen extends StatefulWidget {
  final String symbol;
  const PosProductsScreen({super.key, this.symbol = 'Rp'});

  @override
  State<PosProductsScreen> createState() => _PosProductsScreenState();
}

class _PosProductsScreenState extends State<PosProductsScreen> {
  bool _loading = true;
  String? _error;
  List<PosProduct> _products = [];
  List<PosProduct> _lowStock = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.instance.posProducts();
      final rawProds = res['products'] as List<dynamic>? ?? [];
      final rawLow = res['low_stock'] as List<dynamic>? ?? [];
      setState(() {
        _products = rawProds.map((e) => PosProduct.fromJson(e as Map<String, dynamic>)).toList();
        _lowStock = rawLow.map((e) => PosProduct.fromJson(e as Map<String, dynamic>)).toList();
        _loading = false;
        _error = null;
      });
    } catch (e) {
      setState(() {
        _loading = false;
        _error = '$e';
      });
    }
  }

  void _openProductForm([PosProduct? p]) {
    final isEdit = p != null;
    final nameCtrl = TextEditingController(text: p?.name ?? '');
    final catCtrl = TextEditingController(text: p?.category ?? 'Umum');
    final sellPriceCtrl = TextEditingController(text: p != null ? '${p.sellingPrice.toInt()}' : '');
    final costPriceCtrl = TextEditingController(text: p != null ? '${p.costPrice.toInt()}' : '');
    final stockCtrl = TextEditingController(text: p != null ? '${p.stock}' : '0');
    final unitCtrl = TextEditingController(text: p?.unit ?? 'pcs');
    final minStockCtrl = TextEditingController(text: p != null ? '${p.minStockAlert}' : '5');
    String icon = p?.icon ?? 'coffee';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) {
        return StatefulBuilder(builder: (c, setSheetState) {
          return Padding(
            padding: EdgeInsets.only(
              bottom: MediaQuery.of(ctx).viewInsets.bottom,
            ),
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 30),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Center(
                    child: Container(
                      width: 36,
                      height: 4,
                      decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(2)),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(isEdit ? '✏️ Edit Menu / Produk' : '➕ Tambah Menu Baru', style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800)),
                      IconButton(icon: const Icon(Icons.close, size: 20), onPressed: () => Navigator.pop(ctx)),
                    ],
                  ),
                  const Divider(height: 1),
                  const SizedBox(height: 14),

                  TextField(
                    controller: nameCtrl,
                    decoration: const InputDecoration(labelText: 'Nama Produk / Menu *', hintText: 'Contoh: Kopi Susu Aren / Beras 5kg'),
                  ),
                  const SizedBox(height: 12),

                  TextField(
                    controller: catCtrl,
                    decoration: const InputDecoration(labelText: 'Kategori', hintText: 'Kopi / Minuman / Makanan / Sembako'),
                  ),
                  const SizedBox(height: 12),

                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: sellPriceCtrl,
                          keyboardType: TextInputType.number,
                          decoration: const InputDecoration(labelText: 'Harga Jual *', prefixText: 'Rp '),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: TextField(
                          controller: costPriceCtrl,
                          keyboardType: TextInputType.number,
                          decoration: const InputDecoration(labelText: 'Harga Modal (HPP)', prefixText: 'Rp '),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: stockCtrl,
                          keyboardType: TextInputType.number,
                          decoration: const InputDecoration(labelText: 'Jumlah Stok'),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: TextField(
                          controller: unitCtrl,
                          decoration: const InputDecoration(labelText: 'Satuan', hintText: 'pcs/cup/kg'),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  TextField(
                    controller: minStockCtrl,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(labelText: 'Batas Peringatan Stok Menipis'),
                  ),
                  const SizedBox(height: 12),

                  DropdownButtonFormField<String>(
                    initialValue: icon,
                    decoration: const InputDecoration(labelText: 'Ikon Produk'),
                    items: const [
                      DropdownMenuItem(value: 'coffee', child: Text('☕ Kopi / Minuman Panas')),
                      DropdownMenuItem(value: 'tea', child: Text('🍵 Teh / Matcha')),
                      DropdownMenuItem(value: 'drink', child: Text('🧃 Jus / Minuman Dingin')),
                      DropdownMenuItem(value: 'food', child: Text('🥐 Makanan / Roti')),
                      DropdownMenuItem(value: 'snack', child: Text('🍟 Snack / Camilan')),
                      DropdownMenuItem(value: 'groceries', child: Text('🛒 Sembako / Toko')),
                      DropdownMenuItem(value: 'rice', child: Text('🌾 Beras / Gandum')),
                      DropdownMenuItem(value: 'cigarette', child: Text('🚬 Rokok / Tembakau')),
                      DropdownMenuItem(value: 'box', child: Text('📦 Umum / Lainnya')),
                    ],
                    onChanged: (val) => setSheetState(() => icon = val ?? 'coffee'),
                  ),
                  const SizedBox(height: 20),

                  ElevatedButton(
                    onPressed: () async {
                      final name = nameCtrl.text.trim();
                      final sellPrice = double.tryParse(sellPriceCtrl.text.replaceAll(RegExp(r'\D'), '')) ?? 0;
                      final costPrice = double.tryParse(costPriceCtrl.text.replaceAll(RegExp(r'\D'), '')) ?? 0;
                      final stock = int.tryParse(stockCtrl.text) ?? 0;
                      final minStock = int.tryParse(minStockCtrl.text) ?? 5;

                      if (name.isEmpty || sellPrice <= 0) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Nama produk dan harga jual wajib diisi!')),
                        );
                        return;
                      }

                      final payload = {
                        'id': p?.id ?? 0,
                        'name': name,
                        'category': catCtrl.text.trim().isEmpty ? 'Umum' : catCtrl.text.trim(),
                        'selling_price': sellPrice,
                        'cost_price': costPrice,
                        'stock': stock,
                        'unit': unitCtrl.text.trim().isEmpty ? 'pcs' : unitCtrl.text.trim(),
                        'min_stock_alert': minStock,
                        'icon': icon,
                      };

                      try {
                        await ApiService.instance.storePosProduct(payload);
                        if (!ctx.mounted) return;
                        Navigator.pop(ctx);
                        if (!mounted) return;
                        _load();
                      } catch (err) {
                        if (mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $err')));
                        }
                      }
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFEA580C),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    child: const Text('Simpan Produk', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800)),
                  ),

                  if (isEdit) ...[
                    const SizedBox(height: 10),
                    TextButton.icon(
                      onPressed: () async {
                        final confirm = await showDialog<bool>(
                          context: context,
                          builder: (c) => AlertDialog(
                            title: const Text('Hapus Produk'),
                            content: Text('Yakin ingin menghapus ${p.name}?'),
                            actions: [
                              TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Batal')),
                              ElevatedButton(
                                onPressed: () => Navigator.pop(c, true),
                                style: ElevatedButton.styleFrom(backgroundColor: AppColors.expense, foregroundColor: Colors.white),
                                child: const Text('Hapus'),
                              ),
                            ],
                          ),
                        );
                        if (confirm == true) {
                          await ApiService.instance.deletePosProduct(p.id);
                          if (!context.mounted) return;
                          Navigator.pop(ctx);
                          _load();
                        }
                      },
                      icon: const Icon(Icons.delete_outline, color: AppColors.expense),
                      label: const Text('Hapus Produk Ini', style: TextStyle(color: AppColors.expense, fontWeight: FontWeight.w700)),
                    ),
                  ],
                ],
              ),
            ),
          );
        });
      },
    );
  }

  void _openRestockDialog(PosProduct p) {
    final stockCtrl = TextEditingController(text: '${p.stock}');
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text('📦 Restock: ${p.name}'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Stok saat ini: ${p.stock} ${p.unit}', style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
            const SizedBox(height: 10),
            TextField(
              controller: stockCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'Total Stok Baru', hintText: '0'),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () async {
              final newStock = int.tryParse(stockCtrl.text) ?? p.stock;
              await ApiService.instance.adjustPosStock(p.id, newStock);
              if (!context.mounted) return;
              Navigator.pop(ctx);
              _load();
            },
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF059669), foregroundColor: Colors.white),
            child: const Text('Simpan'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('📦 Katalog & Stok Produk'),
        actions: [
          IconButton(
            icon: const Icon(Icons.add_circle_outline),
            tooltip: 'Tambah Produk',
            onPressed: () => _openProductForm(),
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text(_error!))
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      // Low Stock Alert Banner
                      if (_lowStock.isNotEmpty)
                        Container(
                          margin: const EdgeInsets.only(bottom: 14),
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFEF2F2),
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: const Color(0xFFFCA5A5)),
                          ),
                          child: Row(
                            children: [
                              const Text('⚠️', style: TextStyle(fontSize: 22)),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Text(
                                  '${_lowStock.length} produk memiliki stok menipis!',
                                  style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: Color(0xFF991B1B)),
                                ),
                              ),
                            ],
                          ),
                        ),

                      if (_products.isEmpty)
                        const Center(
                          child: Padding(
                            padding: EdgeInsets.symmetric(vertical: 40),
                            child: Text('Belum ada data produk.'),
                          ),
                        )
                      else
                        ..._products.map((p) {
                          return Container(
                            margin: const EdgeInsets.only(bottom: 10),
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: AppColors.card,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(
                                color: p.isLowStock ? const Color(0xFFFCA5A5) : AppColors.border,
                                width: p.isLowStock ? 1.5 : 1,
                              ),
                            ),
                            child: Row(
                              children: [
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        p.name,
                                        style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
                                      ),
                                      const SizedBox(height: 2),
                                      Row(
                                        children: [
                                          Text(
                                            p.category,
                                            style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                                          ),
                                          const Text(' · '),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                            decoration: BoxDecoration(
                                              color: p.isLowStock ? const Color(0xFFFEE2E2) : AppColors.income.withValues(alpha: 0.1),
                                              borderRadius: BorderRadius.circular(6),
                                            ),
                                            child: Text(
                                              'Stok: ${p.stock} ${p.unit} ${p.isLowStock ? '(Menipis!)' : ''}',
                                              style: TextStyle(
                                                fontSize: 10.5,
                                                fontWeight: FontWeight.w700,
                                                color: p.isLowStock ? AppColors.expense : AppColors.income,
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 6),
                                      Row(
                                        children: [
                                          Text(
                                            Fmt.money(p.sellingPrice, symbol: widget.symbol),
                                            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Color(0xFFEA580C)),
                                          ),
                                          const SizedBox(width: 8),
                                          Text(
                                            'HPP: ${Fmt.money(p.costPrice, symbol: widget.symbol)}',
                                            style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                                          ),
                                          const SizedBox(width: 6),
                                          Text(
                                            '(+${p.marginPct.toStringAsFixed(0)}%)',
                                            style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: AppColors.income),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                                Row(
                                  children: [
                                    ElevatedButton(
                                      onPressed: () => _openRestockDialog(p),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: AppColors.bg,
                                        foregroundColor: AppColors.textPrimary,
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                        minimumSize: const Size(60, 32),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                      ),
                                      child: const Text('Restock', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700)),
                                    ),
                                    const SizedBox(width: 6),
                                    IconButton(
                                      icon: const Icon(Icons.edit_outlined, size: 20),
                                      onPressed: () => _openProductForm(p),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          );
                        }),
                    ],
                  ),
                ),
    );
  }
}
