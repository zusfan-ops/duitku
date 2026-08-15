import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../models/transaction.dart';
import '../providers/app_data_provider.dart';
import '../services/api_service.dart';
import '../theme.dart';
import '../widgets/transaction_tile.dart';
import 'transaction_sheet.dart';

class ActivityScreen extends StatefulWidget {
  const ActivityScreen({super.key});

  @override
  State<ActivityScreen> createState() => _ActivityScreenState();
}

class _ActivityScreenState extends State<ActivityScreen> {
  final _searchCtrl = TextEditingController();
  String _type = 'all';
  List<Transaction> _items = [];
  int _page = 1;
  int _totalPages = 1;
  bool _loading = true;
  bool _loadingMore = false;
  bool _hasError = false;
  String _symbol = 'Rp';

  final _scrollCtrl = ScrollController();

  @override
  void initState() {
    super.initState();
    _scrollCtrl.addListener(_onScroll);
    _load(reset: true);
  }

  @override
  void dispose() {
    _scrollCtrl.dispose();
    _searchCtrl.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollCtrl.position.pixels >= _scrollCtrl.position.maxScrollExtent - 200) {
      _loadMore();
    }
  }

  Future<void> _load({bool reset = false}) async {
    if (reset) {
      setState(() {
        _loading = true;
        _hasError = false;
        _page = 1;
      });
    }
    try {
      final res = await ApiService.instance.activity(
        type: _type,
        page: reset ? 1 : _page,
        search: _searchCtrl.text.trim(),
      );
      if (!mounted) return;
      final list = (res['transactions'] as List<dynamic>? ?? [])
          .map((e) => Transaction.fromJson(e as Map<String, dynamic>))
          .toList();
      _symbol = res['symbol']?.toString() ?? _symbol;
      final totalPages = res['totalPages'] is int ? res['totalPages'] as int : int.tryParse('${res['totalPages']}') ?? 1;
      setState(() {
        _items = reset ? list : [..._items, ...list];
        _page = reset ? 2 : _page + 1;
        _totalPages = totalPages;
        _loading = false;
        _loadingMore = false;
        _hasError = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _loading = false;
        _loadingMore = false;
        if (reset) _hasError = true;
      });
    }
  }

  void _loadMore() {
    if (_loading || _loadingMore || _page > _totalPages) return;
    setState(() => _loadingMore = true);
    _load();
  }

  Future<void> _edit(Transaction tx) async {
    final data = context.read<AppDataProvider>();
    await data.ensureLoaded();
    if (!mounted) return;
    final saved = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => TransactionSheet(
        categories: data.categories,
        wallets: data.wallets,
        transaction: tx,
      ),
    );
    if (saved == true) _load(reset: true);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(title: const Text('Aktivitas')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchCtrl,
                    onSubmitted: (_) => _load(reset: true),
                    decoration: InputDecoration(
                      hintText: 'Cari transaksi...',
                      prefixIcon: const Icon(Icons.search, size: 20),
                      isDense: true,
                      contentPadding: const EdgeInsets.symmetric(vertical: 4),
                      suffixIcon: _searchCtrl.text.isEmpty
                          ? null
                          : IconButton(
                              icon: const Icon(Icons.close, size: 18),
                              onPressed: () {
                                _searchCtrl.clear();
                                _load(reset: true);
                              },
                            ),
                    ),
                  ),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              children: [
                _filterChip('Semua', 'all'),
                const SizedBox(width: 8),
                _filterChip('Pemasukan', 'income'),
                const SizedBox(width: 8),
                _filterChip('Pengeluaran', 'expense'),
              ],
            ),
          ),
          const SizedBox(height: 8),
          Expanded(
            child: _buildBody(),
          ),
        ],
      ),
    );
  }

  Widget _filterChip(String label, String value) {
    final active = _type == value;
    return ChoiceChip(
      label: Text(label),
      selected: active,
      onSelected: (_) {
        setState(() => _type = value);
        _load(reset: true);
      },
      selectedColor: AppColors.primary,
      labelStyle: TextStyle(
        fontSize: 12,
        fontWeight: FontWeight.w700,
        color: active ? Colors.white : AppColors.textSecondary,
      ),
      backgroundColor: AppColors.card,
      side: BorderSide(color: active ? AppColors.primary : AppColors.border),
      showCheckmark: false,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_hasError) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.wifi_off, size: 44, color: AppColors.textMuted),
            const SizedBox(height: 12),
            const Text('Gagal memuat aktivitas.', style: TextStyle(fontSize: 14, color: AppColors.textSecondary)),
            const SizedBox(height: 16),
            FilledButton(onPressed: () => _load(reset: true), child: const Text('Coba Lagi')),
          ],
        ),
      );
    }
    if (_items.isEmpty) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.receipt_long, size: 44, color: AppColors.textMuted),
            SizedBox(height: 10),
            Text('Belum ada transaksi',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
          ],
        ),
      );
    }
    return RefreshIndicator(
      onRefresh: () => _load(reset: true),
      child: ListView.builder(
        controller: _scrollCtrl,
        padding: const EdgeInsets.fromLTRB(16, 4, 16, 100),
        itemCount: _items.length + (_loadingMore ? 1 : 0),
        itemBuilder: (context, i) {
          if (i >= _items.length) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Center(
                child: SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2)),
              ),
            );
          }
          return TransactionTile(
            tx: _items[i],
            symbol: _symbol,
            onTap: () => _edit(_items[i]),
          );
        },
      ),
    );
  }
}
