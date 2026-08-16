import 'dart:async';
import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';
import '../barang/barang_screen.dart';
import '../debt_screen.dart';
import '../pos/pos_products_screen.dart';
import '../vehicle/vehicle_screen.dart';

class UniversalSearchScreen extends StatefulWidget {
  final String symbol;
  const UniversalSearchScreen({super.key, this.symbol = 'Rp'});

  @override
  State<UniversalSearchScreen> createState() => _UniversalSearchScreenState();
}

class _UniversalSearchScreenState extends State<UniversalSearchScreen> {
  final _searchCtrl = TextEditingController();
  Timer? _debounce;
  bool _loading = false;
  String? _error;
  Map<String, dynamic> _results = {
    'transactions': [],
    'pos_products': [],
    'debts': [],
    'vehicles': [],
    'barang': [],
  };
  int _total = 0;

  @override
  void dispose() {
    _searchCtrl.dispose();
    _debounce?.cancel();
    super.dispose();
  }

  void _onQueryChanged(String text) {
    _debounce?.cancel();
    final q = text.trim();
    if (q.length < 2) {
      setState(() {
        _results = {
          'transactions': [],
          'pos_products': [],
          'debts': [],
          'vehicles': [],
          'barang': [],
        };
        _total = 0;
        _loading = false;
        _error = null;
      });
      return;
    }

    setState(() => _loading = true);
    _debounce = Timer(const Duration(milliseconds: 350), () async {
      try {
        final res = await ApiService.instance.searchGlobal(q);
        if (!mounted) return;
        setState(() {
          _results = res['results'] as Map<String, dynamic>? ?? {};
          _total = (res['total'] as num?)?.toInt() ?? 0;
          _loading = false;
          _error = null;
        });
      } catch (e) {
        if (!mounted) return;
        setState(() {
          _loading = false;
          _error = '$e';
        });
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final txs = _results['transactions'] as List<dynamic>? ?? [];
    final prods = _results['pos_products'] as List<dynamic>? ?? [];
    final debts = _results['debts'] as List<dynamic>? ?? [];
    final vehicles = _results['vehicles'] as List<dynamic>? ?? [];
    final barangs = _results['barang'] as List<dynamic>? ?? [];

    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        titleSpacing: 0,
        title: Padding(
          padding: const EdgeInsets.only(right: 16),
          child: TextField(
            controller: _searchCtrl,
            autofocus: true,
            onChanged: _onQueryChanged,
            decoration: InputDecoration(
              hintText: 'Cari transaksi, POS, hutang, kendaraan...',
              hintStyle: const TextStyle(fontSize: 13, color: AppColors.textMuted),
              prefixIcon: const Icon(Icons.search_rounded, size: 20, color: AppColors.primary),
              suffixIcon: _searchCtrl.text.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear, size: 18),
                      onPressed: () {
                        _searchCtrl.clear();
                        _onQueryChanged('');
                      },
                    )
                  : null,
              filled: true,
              fillColor: AppColors.card,
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: AppColors.border),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: AppColors.border),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
              ),
            ),
          ),
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text('Gagal mencari: $_error'))
              : _searchCtrl.text.trim().length < 2
                  ? _buildEmptyPrompt('🔍', 'Pencarian Universal', 'Ketik kata kunci untuk mencari transaksi, menu POS, armada kendaraan, kasbon, dan aset barang.')
                  : _total == 0
                      ? _buildEmptyPrompt('❌', 'Data Tidak Ditemukan', 'Tidak ada data yang cocok dengan "${_searchCtrl.text}".')
                      : ListView(
                          padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
                          children: [
                            // 1. Transactions
                            if (txs.isNotEmpty) ...[
                              _buildHeader('TRANSAKSI', txs.length, Icons.receipt_long_rounded),
                              ...txs.map((t) => _buildTxItem(t as Map<String, dynamic>)),
                              const SizedBox(height: 14),
                            ],

                            // 2. POS Products
                            if (prods.isNotEmpty) ...[
                              _buildHeader('PRODUK KASIR (POS)', prods.length, Icons.point_of_sale_rounded),
                              ...prods.map((p) => _buildPosItem(p as Map<String, dynamic>)),
                              const SizedBox(height: 14),
                            ],

                            // 3. Debts
                            if (debts.isNotEmpty) ...[
                              _buildHeader('HUTANG & KASBON', debts.length, Icons.account_balance_wallet_rounded),
                              ...debts.map((d) => _buildDebtItem(d as Map<String, dynamic>)),
                              const SizedBox(height: 14),
                            ],

                            // 4. Vehicles
                            if (vehicles.isNotEmpty) ...[
                              _buildHeader('KENDARAAN & SERVIS', vehicles.length, Icons.directions_car_filled_rounded),
                              ...vehicles.map((v) => _buildVehicleItem(v as Map<String, dynamic>)),
                              const SizedBox(height: 14),
                            ],

                            // 5. Barang
                            if (barangs.isNotEmpty) ...[
                              _buildHeader('ASET BARANG', barangs.length, Icons.inventory_2_rounded),
                              ...barangs.map((b) => _buildBarangItem(b as Map<String, dynamic>)),
                              const SizedBox(height: 14),
                            ],
                          ],
                        ),
    );
  }

  Widget _buildHeader(String title, int count, IconData icon) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8, left: 4),
      child: Row(
        children: [
          Icon(icon, size: 14, color: AppColors.primary),
          const SizedBox(width: 6),
          Text(
            '$title ($count)',
            style: const TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w800,
              letterSpacing: 0.6,
              color: AppColors.textMuted,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTxItem(Map<String, dynamic> t) {
    final isIncome = t['type'] == 'income';
    final amount = Fmt.money(t['amount'], symbol: widget.symbol);

    return Container(
      margin: const EdgeInsets.only(bottom: 6),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  t['description']?.toString().isNotEmpty == true ? t['description'] : (t['category_name'] ?? 'Transaksi'),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 2),
                Text(
                  '${t['date']} · ${t['wallet_name'] ?? 'Dompet'}',
                  style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                ),
              ],
            ),
          ),
          Text(
            '${isIncome ? '+' : '-'}$amount',
            style: TextStyle(
              fontSize: 13.5,
              fontWeight: FontWeight.w800,
              color: isIncome ? AppColors.income : AppColors.expense,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPosItem(Map<String, dynamic> p) {
    return InkWell(
      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PosProductsScreen())),
      borderRadius: BorderRadius.circular(12),
      child: Container(
        margin: const EdgeInsets.only(bottom: 6),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.border),
        ),
        child: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: const Color(0xFFEA580C).withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Center(child: Text('📦', style: TextStyle(fontSize: 18))),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(p['name']?.toString() ?? '', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                  Text(
                    '${p['category'] ?? 'Menu'} · Stok: ${p['stock']} ${p['unit'] ?? 'pcs'}',
                    style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                  ),
                ],
              ),
            ),
            Text(
              Fmt.money(p['selling_price'], symbol: widget.symbol),
              style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: Color(0xFFEA580C)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDebtItem(Map<String, dynamic> d) {
    final isHutang = d['type'] == 'hutang';
    return InkWell(
      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const DebtScreen())),
      borderRadius: BorderRadius.circular(12),
      child: Container(
        margin: const EdgeInsets.only(bottom: 6),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.border),
        ),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    '${isHutang ? '🔴 Hutang ke: ' : '🟢 Kasbon: '}${d['person']}',
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700),
                  ),
                  Text(
                    'Tempo: ${d['due_date'] ?? '-'} · ${d['is_settled'] == 1 ? 'Lunas' : 'Belum Lunas'}',
                    style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                  ),
                ],
              ),
            ),
            Text(
              Fmt.money(d['amount'], symbol: widget.symbol),
              style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildVehicleItem(Map<String, dynamic> v) {
    return InkWell(
      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const VehicleScreen())),
      borderRadius: BorderRadius.circular(12),
      child: Container(
        margin: const EdgeInsets.only(bottom: 6),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.border),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('🚗 ${v['name']}', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                Text('${v['plate_number']} · ${v['odometer_km'] ?? 0} KM', style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
              ],
            ),
            const Icon(Icons.chevron_right, size: 20, color: AppColors.textMuted),
          ],
        ),
      ),
    );
  }

  Widget _buildBarangItem(Map<String, dynamic> b) {
    return InkWell(
      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const BarangScreen())),
      borderRadius: BorderRadius.circular(12),
      child: Container(
        margin: const EdgeInsets.only(bottom: 6),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.border),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('📍 ${b['nama'] ?? b['name'] ?? 'Barang'}', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                Text('Lokasi: ${b['lokasi'] ?? b['location'] ?? 'Rumah'}', style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
              ],
            ),
            const Icon(Icons.chevron_right, size: 20, color: AppColors.textMuted),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyPrompt(String icon, String title, String subtitle) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(icon, style: const TextStyle(fontSize: 44)),
            const SizedBox(height: 12),
            Text(title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            const SizedBox(height: 6),
            Text(subtitle, textAlign: TextAlign.center, style: const TextStyle(fontSize: 12, color: AppColors.textMuted, height: 1.4)),
          ],
        ),
      ),
    );
  }
}
