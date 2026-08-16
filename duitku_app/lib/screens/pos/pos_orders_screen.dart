import 'dart:async';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import 'pos_qr_screen.dart';

class PosOrdersScreen extends StatefulWidget {
  const PosOrdersScreen({super.key});

  @override
  State<PosOrdersScreen> createState() => _PosOrdersScreenState();
}

class _PosOrdersScreenState extends State<PosOrdersScreen> {
  final ApiService _api = ApiService.instance;
  bool _loading = true;
  String _selectedTab = 'all'; // all, pending, processing, served_unpaid, paid, cancelled

  List<dynamic> _orders = [];
  Map<String, dynamic> _counts = {};
  Map<String, dynamic> _store = {};
  List<dynamic> _wallets = [];
  String _symbol = 'Rp';

  Timer? _pollTimer;

  @override
  void initState() {
    super.initState();
    _loadData();
    _loadWallets();
    // Auto-poll every 5 seconds
    _pollTimer = Timer.periodic(const Duration(seconds: 5), (_) => _pollOrders());
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadWallets() async {
    try {
      final res = await _api.wallets();
      if (res['wallets'] is List) {
        setState(() {
          _wallets = res['wallets'];
        });
      }
    } catch (_) {}
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    try {
      final res = await _api.posOrders(status: _selectedTab);
      if (mounted) {
        setState(() {
          _orders = (res['orders'] as List?) ?? [];
          _counts = (res['counts'] as Map<String, dynamic>?) ?? {};
          _store = (res['store'] as Map<String, dynamic>?) ?? {};
          _symbol = res['symbol']?.toString() ?? 'Rp';
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _loading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memuat pesanan: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  Future<void> _pollOrders() async {
    if (!mounted) return;
    try {
      final res = await _api.posOrders(status: _selectedTab);
      if (mounted) {
        setState(() {
          _orders = (res['orders'] as List?) ?? [];
          _counts = (res['counts'] as Map<String, dynamic>?) ?? {};
          _store = (res['store'] as Map<String, dynamic>?) ?? {};
        });
      }
    } catch (_) {}
  }

  Future<void> _updateStatus(int orderId, String status) async {
    try {
      final res = await _api.updatePosOrderStatus(orderId, status);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Status berhasil diperbarui'),
            backgroundColor: const Color(0xFF10B981),
          ),
        );
        _loadData();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  String _formatCurrency(num value) {
    final fmt = NumberFormat('#,###', 'id_ID');
    return '$_symbol ${fmt.format(value)}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Pesanan Masuk (Live)', style: TextStyle(fontWeight: FontWeight.w800)),
        backgroundColor: AppColors.bg,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.qr_code_2_rounded, color: Color(0xFFEA580C)),
            tooltip: 'Cetak QR Standee Toko',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const PosQrScreen()),
              ).then((_) => _loadData());
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _loadData,
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: Column(
          children: [
            // Top Store info banner
            if (_store.isNotEmpty)
              Container(
                margin: const EdgeInsets.fromLTRB(16, 12, 16, 8),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: AppColors.card,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: AppColors.border),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.storefront_rounded, color: Color(0xFFEA580C), size: 20),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            _store['store_name'] ?? 'Toko POS',
                            style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5),
                          ),
                          Text(
                            '/menu/${_store['store_slug'] ?? ''}',
                            style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                          ),
                        ],
                      ),
                    ),
                    TextButton.icon(
                      onPressed: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => const PosQrScreen()),
                        ).then((_) => _loadData());
                      },
                      icon: const Icon(Icons.qr_code_rounded, size: 16),
                      label: const Text('QR Meja', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
                      style: TextButton.styleFrom(
                        foregroundColor: const Color(0xFFEA580C),
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      ),
                    ),
                  ],
                ),
              ),

            // Tab Filter Scroll
            _buildTabFilters(),

            // Orders list
            Expanded(
              child: _loading
                  ? const Center(child: CircularProgressIndicator())
                  : _orders.isEmpty
                      ? _buildEmptyState()
                      : ListView.separated(
                          padding: const EdgeInsets.all(16),
                          itemCount: _orders.length,
                          separatorBuilder: (_, _) => const SizedBox(height: 12),
                          itemBuilder: (context, index) {
                            return _buildOrderCard(_orders[index]);
                          },
                        ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTabFilters() {
    final tabs = [
      {'key': 'all', 'label': 'Semua', 'count': _counts['all'] ?? 0},
      {'key': 'pending', 'label': '🔔 Baru', 'count': _counts['pending'] ?? 0},
      {'key': 'processing', 'label': '🍳 Diproses', 'count': _counts['processing'] ?? 0},
      {'key': 'delivering', 'label': '🛵 Dikirim', 'count': _counts['delivering'] ?? 0},
      {'key': 'served_unpaid', 'label': '⚠️ Belum Bayar (COD/Meja)', 'count': ((_counts['served_unpaid'] as num?)?.toInt() ?? 0) + ((_counts['delivered_unpaid'] as num?)?.toInt() ?? 0), 'isWarning': true},
      {'key': 'paid', 'label': '✅ Selesai', 'count': _counts['paid'] ?? 0},
      {'key': 'cancelled', 'label': '❌ Batal', 'count': _counts['cancelled'] ?? 0},
    ];

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      child: Row(
        children: tabs.map((t) {
          final isSelected = _selectedTab == t['key'];
          final isWarning = t['isWarning'] == true;

          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: ChoiceChip(
              selected: isSelected,
              onSelected: (selected) {
                if (selected) {
                  setState(() {
                    _selectedTab = t['key'] as String;
                  });
                  _loadData();
                }
              },
              backgroundColor: AppColors.card,
              selectedColor: isWarning ? const Color(0xFFD97706) : const Color(0xFFEA580C),
              side: BorderSide(
                color: isWarning ? const Color(0xFFF59E0B) : (isSelected ? const Color(0xFFEA580C) : AppColors.border),
                width: isWarning ? 1.5 : 1,
              ),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              label: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    t['label'] as String,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: isSelected ? Colors.white : (isWarning ? const Color(0xFFD97706) : AppColors.textSecondary),
                    ),
                  ),
                  const SizedBox(width: 6),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: isSelected ? Colors.black26 : AppColors.borderLight,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      '${t['count']}',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w800,
                        color: isSelected ? Colors.white : AppColors.textMuted,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildEmptyState() {
    return const Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.receipt_long_rounded, size: 56, color: AppColors.textMuted),
          SizedBox(height: 12),
          Text(
            'Tidak ada pesanan aktif',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
          ),
          SizedBox(height: 4),
          Text(
            'Pesanan masuk dari Online Shop & QR Meja akan muncul di sini.',
            style: TextStyle(fontSize: 12.5, color: AppColors.textMuted),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildOrderCard(Map<String, dynamic> order) {
    final status = order['status']?.toString() ?? 'paid';
    final isUnpaid = status == 'served_unpaid' || status == 'delivered_unpaid';
    final isPending = status == 'pending';
    final orderType = order['order_type']?.toString() ?? 'dine_in';
    final items = (order['items'] as List?) ?? [];
    final tableNo = order['table_no']?.toString() ?? '';
    final customerName = order['customer_name']?.toString() ?? 'Pelanggan';
    final customerPhone = order['customer_phone']?.toString() ?? '';
    final deliveryAddress = order['delivery_address']?.toString() ?? '';
    final deliveryNotes = order['delivery_notes']?.toString() ?? '';
    final pickupTime = order['pickup_time']?.toString() ?? '';
    final orderNum = order['order_number']?.toString() ?? '';
    final totalAmount = (order['total_amount'] as num?)?.toDouble() ?? 0.0;
    final deliveryFee = (order['delivery_fee'] as num?)?.toDouble() ?? 0.0;

    return Container(
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: isUnpaid
              ? const Color(0xFFF59E0B)
              : (isPending ? const Color(0xFFEA580C) : AppColors.border),
          width: (isUnpaid || isPending) ? 2 : 1,
        ),
        boxShadow: isUnpaid
            ? [BoxShadow(color: const Color(0xFFF59E0B).withValues(alpha: 0.15), blurRadius: 10, offset: const Offset(0, 4))]
            : AppColors.cardShadow,
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Row
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  gradient: orderType == 'delivery'
                      ? const LinearGradient(colors: [Color(0xFF9333EA), Color(0xFFC084FC)])
                      : (orderType == 'takeaway'
                          ? const LinearGradient(colors: [Color(0xFF2563EB), Color(0xFF60A5FA)])
                          : const LinearGradient(colors: [Color(0xFFEA580C), Color(0xFFFB923C)])),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      orderType == 'delivery'
                          ? Icons.delivery_dining_rounded
                          : (orderType == 'takeaway' ? Icons.shopping_bag_rounded : Icons.table_restaurant_rounded),
                      color: Colors.white,
                      size: 14,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      orderType == 'delivery' ? 'DELIVERY' : (orderType == 'takeaway' ? 'TAKEAWAY' : (tableNo.isNotEmpty ? 'Meja $tableNo' : 'DINE IN')),
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 11.5),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '#$orderNum',
                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                    ),
                    Text(
                      customerName + (customerPhone.isNotEmpty ? ' ($customerPhone)' : ''),
                      style: const TextStyle(fontSize: 11.5, color: AppColors.textMuted),
                    ),
                  ],
                ),
              ),
              _buildStatusPill(status),
            ],
          ),

          // Delivery Address if delivery
          if (orderType == 'delivery' && deliveryAddress.isNotEmpty) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: const Color(0xFF9333EA).withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFF9333EA).withValues(alpha: 0.25)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('📍 Alamat Pengantaran:', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFFC084FC))),
                  Text(deliveryAddress, style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600)),
                  if (deliveryNotes.isNotEmpty)
                    Text('🏡 Patokan: $deliveryNotes', style: const TextStyle(fontSize: 10.5, color: AppColors.textMuted)),
                ],
              ),
            ),
          ] else if (orderType == 'takeaway' && pickupTime.isNotEmpty) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: const Color(0xFF2563EB).withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text('⏰ Jam Ambil: $pickupTime', style: const TextStyle(fontSize: 11, color: Color(0xFF60A5FA), fontWeight: FontWeight.w700)),
            ),
          ],

          const SizedBox(height: 10),

          // Items Box
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: AppColors.borderLight,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(
              children: [
                ...items.map((it) {
                  final note = it['notes']?.toString();
                  return Padding(
                    padding: const EdgeInsets.symmetric(vertical: 3),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                '${it['product_name']} x${it['qty']}',
                                style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5),
                              ),
                              if (note != null && note.isNotEmpty)
                                Text(
                                  '📝 $note',
                                  style: const TextStyle(fontSize: 11, color: Color(0xFFEA580C), fontWeight: FontWeight.w600),
                                ),
                            ],
                          ),
                        ),
                        Text(
                          _formatCurrency((it['subtotal'] as num?) ?? 0),
                          style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12.5),
                        ),
                      ],
                    ),
                  );
                }),
                if (deliveryFee > 0)
                  Padding(
                    padding: const EdgeInsets.only(top: 4),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('🛵 Ongkir Delivery', style: TextStyle(fontSize: 11.5, color: AppColors.textMuted)),
                        Text(_formatCurrency(deliveryFee), style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700)),
                      ],
                    ),
                  ),
              ],
            ),
          ),

          const SizedBox(height: 12),

          // Total & Actions
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Total (${(order['payment_method']?.toString() ?? 'COD').toUpperCase()})', style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                  Text(
                    _formatCurrency(totalAmount),
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: Color(0xFFEA580C)),
                  ),
                ],
              ),
              _buildActionButtons(order),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStatusPill(String status) {
    Color bg;
    Color fg;
    String label;

    switch (status) {
      case 'pending':
        bg = const Color(0xFFFEF3C7);
        fg = const Color(0xFFD97706);
        label = '🔔 Baru';
        break;
      case 'processing':
        bg = const Color(0xFFDBEAFE);
        fg = const Color(0xFF2563EB);
        label = '🍳 Diproses';
        break;
      case 'delivering':
        bg = const Color(0xFFF3E8FF);
        fg = const Color(0xFF9333EA);
        label = '🛵 Dikirim';
        break;
      case 'served_unpaid':
        bg = const Color(0xFFFEF3C7);
        fg = const Color(0xFFB45309);
        label = '⚠️ SAJIKAN (BELUM BAYAR)';
        break;
      case 'delivered_unpaid':
        bg = const Color(0xFFFEF3C7);
        fg = const Color(0xFFB45309);
        label = '⚠️ SAMPAI / COD';
        break;
      case 'paid':
        bg = const Color(0xFFDCFCE7);
        fg = const Color(0xFF16A34A);
        label = '✅ Selesai';
        break;
      case 'cancelled':
        bg = const Color(0xFFFEE2E2);
        fg = const Color(0xFFDC2626);
        label = '❌ Batal';
        break;
      default:
        bg = AppColors.borderLight;
        fg = AppColors.textSecondary;
        label = status;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: fg.withValues(alpha: 0.3)),
      ),
      child: Text(
        label,
        style: TextStyle(color: fg, fontSize: 10, fontWeight: FontWeight.w800),
      ),
    );
  }

  Widget _buildActionButtons(Map<String, dynamic> order) {
    final status = order['status']?.toString() ?? 'paid';
    final orderType = order['order_type']?.toString() ?? 'dine_in';
    final orderId = (order['id'] as num?)?.toInt() ?? 0;
    final totalAmount = (order['total_amount'] as num?)?.toDouble() ?? 0.0;
    final orderNum = order['order_number']?.toString() ?? '';

    if (status == 'pending') {
      return Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          OutlinedButton(
            onPressed: () => _updateStatus(orderId, 'cancelled'),
            style: OutlinedButton.styleFrom(
              foregroundColor: Colors.red,
              side: const BorderSide(color: Colors.red),
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              minimumSize: Size.zero,
            ),
            child: const Text('Tolak', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700)),
          ),
          const SizedBox(width: 6),
          ElevatedButton.icon(
            onPressed: () => _updateStatus(orderId, 'processing'),
            icon: const Icon(Icons.check_rounded, size: 14),
            label: const Text('Siapkan', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800)),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF2563EB),
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              minimumSize: Size.zero,
            ),
          ),
        ],
      );
    } else if (status == 'processing') {
      return Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (orderType == 'delivery')
            ElevatedButton.icon(
              onPressed: () => _updateStatus(orderId, 'delivering'),
              icon: const Icon(Icons.delivery_dining_rounded, size: 14),
              label: const Text('Kirim Kurir', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800)),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF9333EA),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                minimumSize: Size.zero,
              ),
            )
          else if (orderType == 'takeaway')
            ElevatedButton.icon(
              onPressed: () => _updateStatus(orderId, 'delivering'),
              icon: const Icon(Icons.shopping_bag_rounded, size: 14),
              label: const Text('Siap Ambil', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800)),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF2563EB),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                minimumSize: Size.zero,
              ),
            )
          else
            ElevatedButton.icon(
              onPressed: () => _updateStatus(orderId, 'served_unpaid'),
              icon: const Icon(Icons.room_service_rounded, size: 14),
              label: const Text('Sajikan', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800)),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFD97706),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                minimumSize: Size.zero,
              ),
            ),
          const SizedBox(width: 6),
          ElevatedButton(
            onPressed: () => _showPaymentDialog(orderId, orderNum, totalAmount),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF10B981),
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              minimumSize: Size.zero,
            ),
            child: const Text('Bayar', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800)),
          ),
        ],
      );
    } else if (status == 'delivering') {
      return Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (orderType == 'delivery')
            ElevatedButton(
              onPressed: () => _updateStatus(orderId, 'delivered_unpaid'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFD97706),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                minimumSize: Size.zero,
              ),
              child: const Text('Sampai (COD)', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
            ),
          const SizedBox(width: 6),
          ElevatedButton.icon(
            onPressed: () => _showPaymentDialog(orderId, orderNum, totalAmount),
            icon: const Icon(Icons.check_circle_rounded, size: 14),
            label: const Text('Lunas', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800)),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF10B981),
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              minimumSize: Size.zero,
            ),
          ),
        ],
      );
    } else if (status == 'served_unpaid' || status == 'delivered_unpaid') {
      return ElevatedButton.icon(
        onPressed: () => _showPaymentDialog(orderId, orderNum, totalAmount),
        icon: const Icon(Icons.payments_rounded, size: 16),
        label: const Text('Terima Bayar & Selesai', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w900)),
        style: ElevatedButton.styleFrom(
          backgroundColor: const Color(0xFF10B981),
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
          minimumSize: Size.zero,
        ),
      );
    }

    return const SizedBox.shrink();
  }

  void _showPaymentDialog(int orderId, String orderNum, double totalAmount) {
    String selectedMethod = 'cash';
    int? selectedWalletId = _wallets.isNotEmpty ? (_wallets.first['id'] as int?) : null;
    final cashCtrl = TextEditingController(text: NumberFormat('#,###', 'id_ID').format(totalAmount));
    double cashReceived = totalAmount;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            final change = (cashReceived - totalAmount).clamp(0, double.infinity);

            return Padding(
              padding: EdgeInsets.fromLTRB(20, 16, 20, MediaQuery.of(ctx).viewInsets.bottom + 20),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Bayar Pesanan #$orderNum', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
                      IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                    ],
                  ),
                  const SizedBox(height: 8),

                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: AppColors.borderLight,
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Column(
                      children: [
                        const Text('Total Tagihan', style: TextStyle(fontSize: 11, color: AppColors.textMuted)),
                        Text(
                          _formatCurrency(totalAmount),
                          style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900, color: Color(0xFFEA580C)),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 14),
                  const Text('Metode Pembayaran', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                  const SizedBox(height: 6),

                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      {'key': 'cash', 'label': '💵 Tunai'},
                      {'key': 'qris', 'label': '📱 QRIS'},
                      {'key': 'transfer', 'label': '🏦 Transfer'},
                      {'key': 'kasbon', 'label': '📝 Kasbon'},
                    ].map((m) {
                      final sel = selectedMethod == m['key'];
                      return ChoiceChip(
                        label: Text(m['label']!),
                        selected: sel,
                        selectedColor: const Color(0xFFEA580C),
                        backgroundColor: AppColors.borderLight,
                        onSelected: (s) {
                          if (s) setModalState(() => selectedMethod = m['key']!);
                        },
                      );
                    }).toList(),
                  ),

                  if (selectedMethod == 'cash') ...[
                    const SizedBox(height: 12),
                    const Text('Uang Diterima', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                    const SizedBox(height: 4),
                    TextField(
                      controller: cashCtrl,
                      keyboardType: TextInputType.number,
                      decoration: InputDecoration(
                        prefixText: '$_symbol ',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      ),
                      onChanged: (val) {
                        final parsed = double.tryParse(val.replaceAll(RegExp(r'[^0-9]'), '')) ?? totalAmount;
                        setModalState(() => cashReceived = parsed);
                      },
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Kembalian: ${_formatCurrency(change)}',
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFF10B981)),
                    ),
                  ],

                  if (selectedMethod != 'kasbon' && _wallets.isNotEmpty) ...[
                    const SizedBox(height: 12),
                    const Text('Masuk ke Rekening / Kas', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                    const SizedBox(height: 4),
                    DropdownButtonFormField<int>(
                      initialValue: selectedWalletId,
                      decoration: InputDecoration(
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      ),
                      items: _wallets.map<DropdownMenuItem<int>>((w) {
                        return DropdownMenuItem<int>(
                          value: w['id'] as int,
                          child: Text('${w['name']} (${_formatCurrency(w['balance'] ?? 0)})'),
                        );
                      }).toList(),
                      onChanged: (val) => setModalState(() => selectedWalletId = val),
                    ),
                  ],

                  const SizedBox(height: 18),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () async {
                        final messenger = ScaffoldMessenger.of(context);
                        Navigator.pop(ctx);
                        try {
                          await _api.payPosOrder(
                            orderId: orderId,
                            paymentMethod: selectedMethod,
                            walletId: selectedWalletId,
                            cashReceived: selectedMethod == 'cash' ? cashReceived : totalAmount,
                          );
                          if (mounted) {
                            messenger.showSnackBar(
                              const SnackBar(
                                content: Text('Pembayaran berhasil disimpan! Pesanan selesai.'),
                                backgroundColor: Color(0xFF10B981),
                              ),
                            );
                            _loadData();
                          }
                        } catch (e) {
                          if (mounted) {
                            messenger.showSnackBar(
                              SnackBar(content: Text('Gagal: $e'), backgroundColor: Colors.red),
                            );
                          }
                        }
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF10B981),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      child: const Text('Konfirmasi & Selesaikan', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }
}
