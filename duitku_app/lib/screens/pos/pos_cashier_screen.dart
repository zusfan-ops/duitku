import 'package:flutter/material.dart';
import '../../models/pos_order.dart';
import '../../models/pos_product.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';
import 'pos_orders_screen.dart';
import 'pos_products_screen.dart';
import 'pos_qr_screen.dart';
import 'pos_receipt_sheet.dart';
import 'pos_reports_screen.dart';

class PosCashierScreen extends StatefulWidget {
  final String symbol;
  const PosCashierScreen({super.key, this.symbol = 'Rp'});

  @override
  State<PosCashierScreen> createState() => _PosCashierScreenState();
}

class _PosCashierScreenState extends State<PosCashierScreen> {
  bool _loading = true;
  String? _error;
  List<PosProduct> _products = [];
  List<String> _categories = ['Semua'];
  Map<String, dynamic> _summary = {};
  List<dynamic> _wallets = [];

  String _selectedCategory = 'Semua';
  String _searchQuery = '';
  final TextEditingController _searchCtrl = TextEditingController();

  // Cart state: productId -> qty
  final Map<int, int> _cart = {};

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.instance.posCashier(
        category: _selectedCategory,
        search: _searchQuery,
      );
      final rawProds = res['products'] as List<dynamic>? ?? [];
      final rawCats = res['categories'] as List<dynamic>? ?? [];
      setState(() {
        _products = rawProds.map((e) => PosProduct.fromJson(e as Map<String, dynamic>)).toList();
        _categories = rawCats.map((e) => e.toString()).toList();
        _summary = res['summary'] as Map<String, dynamic>? ?? {};
        _wallets = res['wallets'] as List<dynamic>? ?? [];
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

  int get _totalCartItems => _cart.values.fold(0, (sum, q) => sum + q);

  double get _totalCartAmount {
    double total = 0;
    _cart.forEach((id, qty) {
      final p = _products.cast<PosProduct?>().firstWhere((prod) => prod?.id == id, orElse: () => null);
      if (p != null) total += (p.sellingPrice * qty);
    });
    return total;
  }

  void _addToCart(PosProduct p) {
    setState(() {
      _cart[p.id] = (_cart[p.id] ?? 0) + 1;
    });
  }

  void _removeFromCart(int productId) {
    setState(() {
      if (_cart.containsKey(productId)) {
        if (_cart[productId]! > 1) {
          _cart[productId] = _cart[productId]! - 1;
        } else {
          _cart.remove(productId);
        }
      }
    });
  }

  String _getIconEmoji(String icon) {
    switch (icon) {
      case 'coffee': return '☕';
      case 'tea': return '🍵';
      case 'drink': return '🧃';
      case 'food': return '🥐';
      case 'snack': return '🍟';
      case 'groceries': return '🛒';
      case 'rice': return '🌾';
      case 'cigarette': return '🚬';
      default: return '📦';
    }
  }

  void _openCartSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) {
        return StatefulBuilder(builder: (c, setSheetState) {
          final cartItems = _cart.entries.map((entry) {
            final p = _products.firstWhere((prod) => prod.id == entry.key);
            return {'product': p, 'qty': entry.value};
          }).toList();

          return DraggableScrollableSheet(
            initialChildSize: 0.65,
            minChildSize: 0.4,
            maxChildSize: 0.9,
            expand: false,
            builder: (context, scrollCtrl) {
              return Column(
                children: [
                  const SizedBox(height: 12),
                  Container(
                    width: 36,
                    height: 4,
                    decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(2)),
                  ),
                  Padding(
                    padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          '🛒 Keranjang ($_totalCartItems item)',
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
                        ),
                        IconButton(
                          icon: const Icon(Icons.close, size: 20),
                          onPressed: () => Navigator.pop(ctx),
                        ),
                      ],
                    ),
                  ),
                  const Divider(height: 1),
                  Expanded(
                    child: ListView.separated(
                      controller: scrollCtrl,
                      padding: const EdgeInsets.all(16),
                      itemCount: cartItems.length,
                      separatorBuilder: (_, _) => const SizedBox(height: 10),
                      itemBuilder: (context, idx) {
                        final item = cartItems[idx];
                        final p = item['product'] as PosProduct;
                        final qty = item['qty'] as int;
                        final subtotal = p.sellingPrice * qty;

                        return Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: AppColors.bg,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: AppColors.border),
                          ),
                          child: Row(
                            children: [
                              Text(_getIconEmoji(p.icon), style: const TextStyle(fontSize: 22)),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      p.name,
                                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
                                    ),
                                    Text(
                                      '${Fmt.money(p.sellingPrice, symbol: widget.symbol)} / ${p.unit}',
                                      style: const TextStyle(fontSize: 11.5, color: AppColors.textMuted),
                                    ),
                                  ],
                                ),
                              ),
                              Row(
                                children: [
                                  IconButton(
                                    icon: const Icon(Icons.remove_circle_outline, size: 22, color: AppColors.expense),
                                    onPressed: () {
                                      _removeFromCart(p.id);
                                      setSheetState(() {});
                                      setState(() {});
                                      if (_cart.isEmpty) Navigator.pop(ctx);
                                    },
                                  ),
                                  Text('$qty', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                                  IconButton(
                                    icon: const Icon(Icons.add_circle_outline, size: 22, color: AppColors.primary),
                                    onPressed: () {
                                      _addToCart(p);
                                      setSheetState(() {});
                                      setState(() {});
                                    },
                                  ),
                                ],
                              ),
                              Text(
                                Fmt.money(subtotal, symbol: widget.symbol),
                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Color(0xFFEA580C)),
                              ),
                            ],
                          ),
                        );
                      },
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: const BoxDecoration(
                      color: AppColors.card,
                      border: Border(top: BorderSide(color: AppColors.border)),
                    ),
                    child: Column(
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Total Tagihan:', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700)),
                            Text(
                              Fmt.money(_totalCartAmount, symbol: widget.symbol),
                              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: Color(0xFFEA580C)),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        SizedBox(
                          width: double.infinity,
                          height: 48,
                          child: ElevatedButton(
                            onPressed: () {
                              Navigator.pop(ctx);
                              _openCheckoutSheet();
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF10B981),
                              foregroundColor: Colors.white,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                            ),
                            child: const Text('Lanjut ke Pembayaran 💳', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              );
            },
          );
        });
      },
    );
  }

  void _openCheckoutSheet() {
    final total = _totalCartAmount;
    String payMethod = 'cash';
    int? selectedWalletId = _wallets.isNotEmpty ? (_wallets.first['id'] as num?)?.toInt() : null;
    final cashCtrl = TextEditingController(text: '${total.toInt()}');
    final nameCtrl = TextEditingController();
    final phoneCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) {
        return StatefulBuilder(builder: (c, setCheckoutState) {
          double cashReceived = double.tryParse(cashCtrl.text.replaceAll(RegExp(r'\D'), '')) ?? 0;
          double change = (cashReceived > total) ? (cashReceived - total) : 0;

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
                      const Text('💳 Pembayaran Kasir', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800)),
                      IconButton(icon: const Icon(Icons.close, size: 20), onPressed: () => Navigator.pop(ctx)),
                    ],
                  ),
                  const Divider(height: 1),
                  const SizedBox(height: 12),

                  // Total Box
                  Container(
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    decoration: BoxDecoration(
                      color: AppColors.bg,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Column(
                      children: [
                        const Text('TOTAL TAGIHAN', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textMuted)),
                        const SizedBox(height: 2),
                        Text(
                          Fmt.money(total, symbol: widget.symbol),
                          style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w900, color: Color(0xFFEA580C)),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Payment Method Segment
                  const Text('Metode Pembayaran', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.textMuted)),
                  const SizedBox(height: 6),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      _buildPayMethodChip('cash', '💵 Tunai', payMethod, (m) => setCheckoutState(() => payMethod = m)),
                      _buildPayMethodChip('qris', '📱 QRIS', payMethod, (m) => setCheckoutState(() => payMethod = m)),
                      _buildPayMethodChip('transfer', '💳 Transfer', payMethod, (m) => setCheckoutState(() => payMethod = m)),
                      _buildPayMethodChip('kasbon', '📒 Kasbon (Hutang)', payMethod, (m) => setCheckoutState(() => payMethod = m)),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Cash input & Presets
                  if (payMethod == 'cash') ...[
                    const Text('Uang Diterima', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.textMuted)),
                    const SizedBox(height: 6),
                    TextField(
                      controller: cashCtrl,
                      keyboardType: TextInputType.number,
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
                      decoration: const InputDecoration(
                        prefixText: 'Rp ',
                        hintText: '0',
                      ),
                      onChanged: (_) => setCheckoutState(() {}),
                    ),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 6,
                      runSpacing: 6,
                      children: [
                        _buildPresetBtn('Uang Pas', total.toInt(), cashCtrl, setCheckoutState),
                        _buildPresetBtn('10rb', 10000, cashCtrl, setCheckoutState),
                        _buildPresetBtn('20rb', 20000, cashCtrl, setCheckoutState),
                        _buildPresetBtn('50rb', 50000, cashCtrl, setCheckoutState),
                        _buildPresetBtn('100rb', 100000, cashCtrl, setCheckoutState),
                        _buildPresetBtn('200rb', 200000, cashCtrl, setCheckoutState),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      decoration: BoxDecoration(
                        color: AppColors.income.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.income),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('Kembalian:', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.income)),
                          Text(
                            Fmt.money(change, symbol: widget.symbol),
                            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: AppColors.income),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
                  ],

                  // Wallet selector
                  if (payMethod != 'kasbon' && _wallets.isNotEmpty) ...[
                    const Text('Masuk ke Rekening', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.textMuted)),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<int>(
                      initialValue: selectedWalletId,
                      items: _wallets.map((w) {
                        return DropdownMenuItem<int>(
                          value: (w['id'] as num).toInt(),
                          child: Text('${w['name']} (${Fmt.money(w['balance'], symbol: widget.symbol)})'),
                        );
                      }).toList(),
                      onChanged: (val) => setCheckoutState(() => selectedWalletId = val),
                    ),
                    const SizedBox(height: 14),
                  ],

                  // Customer Name & Phone
                  TextField(
                    controller: nameCtrl,
                    decoration: InputDecoration(
                      labelText: payMethod == 'kasbon' ? 'Nama Pelanggan (Wajib Kasbon) *' : 'Nama Pelanggan (Opsional)',
                      hintText: 'Contoh: Mas Budi / Meja 03',
                    ),
                  ),
                  const SizedBox(height: 10),
                  TextField(
                    controller: phoneCtrl,
                    keyboardType: TextInputType.phone,
                    decoration: const InputDecoration(
                      labelText: 'No. WhatsApp (Kirim Struk)',
                      hintText: 'Contoh: 08123456789',
                    ),
                  ),
                  const SizedBox(height: 18),

                  SizedBox(
                    height: 50,
                    child: ElevatedButton(
                      onPressed: () async {
                        if (payMethod == 'kasbon' && nameCtrl.text.trim().isEmpty) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('Nama pelanggan wajib diisi untuk transaksi kasbon!')),
                          );
                          return;
                        }

                        final itemsPayload = _cart.entries.map((e) {
                          final p = _products.firstWhere((prod) => prod.id == e.key);
                          return {
                            'product_id': p.id,
                            'name': p.name,
                            'price': p.sellingPrice,
                            'cost_price': p.costPrice,
                            'qty': e.value,
                          };
                        }).toList();

                        final payload = {
                          'items': itemsPayload,
                          'payment_method': payMethod,
                          'wallet_id': selectedWalletId,
                          'cash_received': cashReceived,
                          'customer_name': nameCtrl.text.trim(),
                          'customer_phone': phoneCtrl.text.trim(),
                        };

                        try {
                          final res = await ApiService.instance.posCheckout(payload);
                          if (!ctx.mounted) return;
                          Navigator.pop(ctx);
                          if (!mounted) return;
                          setState(() => _cart.clear());
                          _load();

                          final orderJson = res['order'] as Map<String, dynamic>?;
                          if (orderJson != null && mounted) {
                            final order = PosOrder.fromJson(orderJson);
                            showModalBottomSheet(
                              context: context,
                              isScrollControlled: true,
                              backgroundColor: AppColors.card,
                              shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
                              builder: (_) => PosReceiptSheet(
                                order: order,
                                symbol: widget.symbol,
                                onNewOrder: () {},
                              ),
                            );
                          }
                        } catch (err) {
                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(content: Text('Gagal: $err')),
                            );
                          }
                        }
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF10B981),
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                      child: const Text('Selesaikan Transaksi & Struk 🖨️', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800)),
                    ),
                  ),
                ],
              ),
            ),
          );
        });
      },
    );
  }

  Widget _buildPayMethodChip(String val, String label, String current, Function(String) onSelect) {
    final active = val == current;
    return ChoiceChip(
      label: Text(label, style: TextStyle(fontWeight: FontWeight.w800, color: active ? Colors.white : AppColors.textPrimary, fontSize: 12)),
      selected: active,
      selectedColor: const Color(0xFFEA580C),
      backgroundColor: AppColors.bg,
      onSelected: (_) => onSelect(val),
    );
  }

  Widget _buildPresetBtn(String label, int val, TextEditingController ctrl, StateSetter setCheckoutState) {
    return ActionChip(
      label: Text(label, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700)),
      backgroundColor: AppColors.bg,
      onPressed: () {
        ctrl.text = '$val';
        setCheckoutState(() {});
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('☕ Kasir Mini POS'),
        actions: [
          IconButton(
            icon: const Icon(Icons.receipt_long_rounded),
            tooltip: 'Pesanan Masuk (Live)',
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PosOrdersScreen())).then((_) => _load()),
          ),
          IconButton(
            icon: const Icon(Icons.qr_code_2_rounded),
            tooltip: 'QR Menu Standee',
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PosQrScreen())),
          ),
          IconButton(
            icon: const Icon(Icons.inventory_2_outlined),
            tooltip: 'Katalog & Stok',
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => PosProductsScreen(symbol: widget.symbol))).then((_) => _load()),
          ),
          IconButton(
            icon: const Icon(Icons.bar_chart_rounded),
            tooltip: 'Laporan Laba Rugi',
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => PosReportsScreen(symbol: widget.symbol))),
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text(_error!))
              : Stack(
                  children: [
                    RefreshIndicator(
                      onRefresh: _load,
                      child: ListView(
                        padding: const EdgeInsets.fromLTRB(14, 8, 14, 110),
                        children: [
                          // Top Shift Stats
                          Container(
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [Color(0xFFEA580C), Color(0xFFFB923C)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              borderRadius: BorderRadius.circular(16),
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(0xFFEA580C).withValues(alpha: 0.25),
                                  blurRadius: 10,
                                  offset: const Offset(0, 4),
                                ),
                              ],
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text('OMSET HARI INI', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: Colors.white70)),
                                    const SizedBox(height: 2),
                                    Text(
                                      Fmt.money(_summary['total_sales'] ?? 0, symbol: widget.symbol),
                                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: Colors.white),
                                    ),
                                  ],
                                ),
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.end,
                                  children: [
                                    const Text('LABA BERSIH', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: Colors.white70)),
                                    const SizedBox(height: 2),
                                    Text(
                                      Fmt.money(_summary['total_profit'] ?? 0, symbol: widget.symbol),
                                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: Color(0xFFFEF08A)),
                                    ),
                                    Text(
                                      '${_summary['total_orders'] ?? 0} Transaksi',
                                      style: const TextStyle(fontSize: 10.5, color: Colors.white70),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 12),

                          // Search Bar
                          TextField(
                            controller: _searchCtrl,
                            decoration: InputDecoration(
                              hintText: 'Cari menu / produk...',
                              prefixIcon: const Icon(Icons.search, size: 20),
                              suffixIcon: _searchQuery.isNotEmpty
                                  ? IconButton(
                                      icon: const Icon(Icons.clear, size: 18),
                                      onPressed: () {
                                        _searchCtrl.clear();
                                        setState(() => _searchQuery = '');
                                        _load();
                                      },
                                    )
                                  : null,
                              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                            ),
                            onSubmitted: (val) {
                              setState(() => _searchQuery = val.trim());
                              _load();
                            },
                          ),
                          const SizedBox(height: 10),

                          // Category Horizontal Scroll
                          SizedBox(
                            height: 36,
                            child: ListView.separated(
                              scrollDirection: Axis.horizontal,
                              itemCount: _categories.length,
                              separatorBuilder: (_, _) => const SizedBox(width: 8),
                              itemBuilder: (context, idx) {
                                final cat = _categories[idx];
                                final active = cat == _selectedCategory;
                                return ChoiceChip(
                                  label: Text(cat, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: active ? Colors.white : AppColors.textSecondary)),
                                  selected: active,
                                  selectedColor: const Color(0xFFEA580C),
                                  backgroundColor: AppColors.card,
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20), side: BorderSide(color: active ? const Color(0xFFEA580C) : AppColors.border)),
                                  onSelected: (_) {
                                    setState(() => _selectedCategory = cat);
                                    _load();
                                  },
                                );
                              },
                            ),
                          ),
                          const SizedBox(height: 14),

                          // 2-Column Responsive Product Grid
                          if (_products.isEmpty)
                            Center(
                              child: Padding(
                                padding: const EdgeInsets.symmetric(vertical: 40),
                                child: Column(
                                  children: [
                                    const Text('☕', style: TextStyle(fontSize: 40)),
                                    const SizedBox(height: 10),
                                    const Text('Belum ada menu / produk', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800)),
                                    const SizedBox(height: 4),
                                    const Text('Tambahkan menu usaha Anda di kelola produk.', style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                                    const SizedBox(height: 14),
                                    ElevatedButton(
                                      onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => PosProductsScreen(symbol: widget.symbol))).then((_) => _load()),
                                      style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFEA580C), foregroundColor: Colors.white),
                                      child: const Text('+ Tambah Produk Baru'),
                                    ),
                                  ],
                                ),
                              ),
                            )
                          else
                            GridView.builder(
                              shrinkWrap: true,
                              physics: const NeverScrollableScrollPhysics(),
                              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                crossAxisCount: 2,
                                childAspectRatio: 1.15,
                                crossAxisSpacing: 10,
                                mainAxisSpacing: 10,
                              ),
                              itemCount: _products.length,
                              itemBuilder: (context, idx) {
                                final p = _products[idx];
                                final qtyInCart = _cart[p.id] ?? 0;
                                final inCart = qtyInCart > 0;

                                return GestureDetector(
                                  onTap: () => _addToCart(p),
                                  child: Container(
                                    padding: const EdgeInsets.all(12),
                                    decoration: BoxDecoration(
                                      color: inCart ? const Color(0xFFEA580C).withValues(alpha: 0.08) : AppColors.card,
                                      borderRadius: BorderRadius.circular(16),
                                      border: Border.all(
                                        color: inCart ? const Color(0xFFEA580C) : AppColors.border,
                                        width: inCart ? 1.5 : 1,
                                      ),
                                    ),
                                    child: Stack(
                                      children: [
                                        Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            Text(_getIconEmoji(p.icon), style: const TextStyle(fontSize: 24)),
                                            Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Text(
                                                  p.name,
                                                  maxLines: 2,
                                                  overflow: TextOverflow.ellipsis,
                                                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: AppColors.textPrimary, height: 1.2),
                                                ),
                                                const SizedBox(height: 3),
                                                Text(
                                                  Fmt.money(p.sellingPrice, symbol: widget.symbol),
                                                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w900, color: Color(0xFFEA580C)),
                                                ),
                                                Text(
                                                  'Stok: ${p.stock} ${p.unit}',
                                                  style: TextStyle(
                                                    fontSize: 10.5,
                                                    fontWeight: FontWeight.w600,
                                                    color: p.isLowStock ? AppColors.expense : AppColors.textMuted,
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ],
                                        ),
                                        if (inCart)
                                          Positioned(
                                            top: 0,
                                            right: 0,
                                            child: Container(
                                              padding: const EdgeInsets.all(5),
                                              decoration: const BoxDecoration(color: Color(0xFFEA580C), shape: BoxShape.circle),
                                              constraints: const BoxConstraints(minWidth: 22, minHeight: 22),
                                              child: Text(
                                                '$qtyInCart',
                                                textAlign: TextAlign.center,
                                                style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w900, height: 1),
                                              ),
                                            ),
                                          ),
                                      ],
                                    ),
                                  ),
                                );
                              },
                            ),
                        ],
                      ),
                    ),

                    // Sticky Bottom Cart Bar
                    if (_totalCartItems > 0)
                      Positioned(
                        bottom: 16,
                        left: 16,
                        right: 16,
                        child: GestureDetector(
                          onTap: _openCartSheet,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                            decoration: BoxDecoration(
                              color: const Color(0xFF18181B),
                              borderRadius: BorderRadius.circular(16),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.35),
                                  blurRadius: 16,
                                  offset: const Offset(0, 6),
                                ),
                              ],
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFEA580C),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Text('$_totalCartItems Item', style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w900)),
                                    ),
                                    const SizedBox(width: 10),
                                    Text(
                                      Fmt.money(_totalCartAmount, symbol: widget.symbol),
                                      style: const TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.w900),
                                    ),
                                  ],
                                ),
                                ElevatedButton(
                                  onPressed: _openCheckoutSheet,
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: const Color(0xFF10B981),
                                    foregroundColor: Colors.white,
                                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                  ),
                                  child: const Text('Bayar →', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
    );
  }
}
