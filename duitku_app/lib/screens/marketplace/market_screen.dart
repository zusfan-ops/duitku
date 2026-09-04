import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../config/api_config.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/whatsapp_launcher.dart';
import 'market_create_screen.dart';
import 'market_detail_screen.dart';
import 'market_chat_screen.dart';
import 'market_conversations_screen.dart';

class MarketScreen extends StatefulWidget {
  const MarketScreen({super.key});

  @override
  State<MarketScreen> createState() => _MarketScreenState();
}

class _MarketScreenState extends State<MarketScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final TextEditingController _searchCtrl = TextEditingController();

  List<dynamic> _listings = [];
  List<dynamic> _myListings = [];
  List<dynamic> _ordersReceived = [];
  int _sellerSubTab = 0; // 0 = Iklan Saya, 1 = Minat Masuk
  List<dynamic> _categories = [];
  String _selectedCategory = 'Semua';
  String _selectedType = ''; // '', 'sale', 'rent'
  String _currentSort = 'latest';

  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _tabController.addListener(() {
      if (!_tabController.indexIsChanging) {
        setState(() {
          if (_tabController.index == 0) _selectedType = '';
          else if (_tabController.index == 1) _selectedType = 'sale';
          else if (_tabController.index == 2) _selectedType = 'rent';
        });
        if (_tabController.index == 3) {
          _loadMyListings();
        } else {
          _loadListings();
        }
      }
    });
    _loadListings();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadListings() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final res = await ApiService.instance.getMarketplaceListings(
        type: _selectedType,
        category: _selectedCategory,
        search: _searchCtrl.text.trim(),
        sort: _currentSort,
      );

      if (mounted) {
        setState(() {
          _listings = (res['listings'] as List<dynamic>?) ?? [];
          _categories = (res['categories'] as List<dynamic>?) ?? [];
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _errorMessage = 'Gagal memuat pasar: $e';
        });
      }
    }
  }

  Future<void> _loadMyListings() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final res = await ApiService.instance.getMyMarketplaceListings();
      if (mounted) {
        setState(() {
          _myListings = (res['listings'] as List<dynamic>?) ?? [];
          _ordersReceived = (res['orders_received'] as List<dynamic>?) ?? [];
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _errorMessage = 'Gagal memuat data toko: $e';
        });
      }
    }
  }

  String _fullImageUrl(String? url) {
    if (url == null || url.isEmpty) return '';
    if (url.startsWith('http://') || url.startsWith('https://')) return url;
    return ApiConfig.baseUrl + (url.startsWith('/') ? url : '/$url');
  }

  String _formatRupiah(dynamic amount) {
    final num val = num.tryParse('$amount') ?? 0;
    final str = val.toStringAsFixed(0);
    final buffer = StringBuffer();
    int count = 0;
    for (int i = str.length - 1; i >= 0; i--) {
      buffer.write(str[i]);
      count++;
      if (count % 3 == 0 && i != 0) buffer.write('.');
    }
    return 'Rp ${buffer.toString().split('').reversed.join('')}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Jual Beli & Sewa'),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.forum_outlined),
            tooltip: 'Pesan & Chat',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const MarketConversationsScreen()),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.shield_outlined),
            tooltip: 'Panduan Keamanan',
            onPressed: () => _showSafetyDialog(context),
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          isScrollable: true,
          labelColor: AppColors.primary,
          unselectedLabelColor: AppColors.textMuted,
          indicatorColor: AppColors.primary,
          indicatorWeight: 3,
          labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
          tabs: [
            const Tab(text: 'Semua'),
            const Tab(text: '🏷️ Jual'),
            const Tab(text: '🔑 Sewa'),
            Tab(
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text('📦 Toko Saya'),
                  if (_ordersReceived.isNotEmpty) ...[
                    const SizedBox(width: 5),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEF4444),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        '${_ordersReceived.length}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 10,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
      body: _tabController.index == 3 ? _buildSellerHubView() : _buildBrowseView(),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: const Color(0xFF059669),
        icon: const Icon(Icons.add_photo_alternate_rounded, color: Colors.white),
        label: const Text(
          'Pasang Iklan',
          style: TextStyle(fontWeight: FontWeight.w800, color: Colors.white),
        ),
        onPressed: () async {
          final refresh = await Navigator.push<bool>(
            context,
            MaterialPageRoute(builder: (_) => const MarketCreateScreen()),
          );
          if (refresh == true) {
            _loadListings();
            _loadMyListings();
          }
        },
      ),
    );
  }

  Widget _buildBrowseView() {
    return RefreshIndicator(
      onRefresh: _loadListings,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(14, 10, 14, 90),
        children: [
          // Safety Banner (Anti-Scam Protocol)
          _buildSafetyBanner(),

          const SizedBox(height: 10),

          // Search Field
          Container(
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.border),
            ),
            child: TextField(
              controller: _searchCtrl,
              textInputAction: TextInputAction.search,
              onSubmitted: (_) => _loadListings(),
              decoration: InputDecoration(
                hintText: 'Cari motor, mobil, rumah, iphone...',
                hintStyle: TextStyle(fontSize: 13, color: AppColors.textMuted),
                prefixIcon: const Icon(Icons.search_rounded, size: 20),
                suffixIcon: _searchCtrl.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 18),
                        onPressed: () {
                          _searchCtrl.clear();
                          _loadListings();
                        },
                      )
                    : null,
                border: InputBorder.none,
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              ),
            ),
          ),

          const SizedBox(height: 10),

          // Horizontal Category Chips
          _buildCategoryChips(),

          const SizedBox(height: 12),

          // Listings Grid / List
          if (_isLoading)
            const Padding(
              padding: EdgeInsets.all(40),
              child: Center(child: CircularProgressIndicator()),
            )
          else if (_errorMessage != null)
            Padding(
              padding: const EdgeInsets.all(30),
              child: Column(
                children: [
                  const Icon(Icons.wifi_off_rounded, size: 44, color: Colors.red),
                  const SizedBox(height: 8),
                  Text(_errorMessage!, textAlign: TextAlign.center),
                  const SizedBox(height: 12),
                  ElevatedButton(onPressed: _loadListings, child: const Text('Coba Lagi')),
                ],
              ),
            )
          else if (_listings.isEmpty)
            _buildEmptyState()
          else
            _buildListingGrid(_listings),
        ],
      ),
    );
  }

  Widget _buildSafetyBanner() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFFEF3C7),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFFDE68A)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('🛡️', style: TextStyle(fontSize: 20)),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: const [
                Text(
                  'Hati-hati Penipuan! Transaksi Aman',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFFB45309),
                  ),
                ),
                SizedBox(height: 2),
                Text(
                  'Jangan pernah kirim DP sebelum cek barang. Dianjurkan COD (ketemu langsung) atau transaksi pihak ketiga (Shopee/Tokopedia).',
                  style: TextStyle(fontSize: 11, color: Color(0xFF78350F), height: 1.3),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCategoryChips() {
    final allCats = ['Semua', ..._categories];
    return SizedBox(
      height: 34,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: allCats.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          final cat = allCats[index].toString();
          final isSelected = cat == _selectedCategory;
          return GestureDetector(
            onTap: () {
              setState(() => _selectedCategory = cat);
              _loadListings();
            },
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
              decoration: BoxDecoration(
                color: isSelected ? const Color(0xFF4338CA) : AppColors.card,
                borderRadius: BorderRadius.circular(999),
                border: Border.all(
                  color: isSelected ? const Color(0xFF4338CA) : AppColors.border,
                ),
              ),
              alignment: Alignment.center,
              child: Text(
                cat,
                style: TextStyle(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w700,
                  color: isSelected ? Colors.white : AppColors.textMuted,
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildListingGrid(List<dynamic> items) {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        childAspectRatio: 0.68,
        crossAxisSpacing: 10,
        mainAxisSpacing: 10,
      ),
      itemCount: items.length,
      itemBuilder: (context, index) {
        final rawItem = items[index];
        if (rawItem is! Map) return const SizedBox.shrink();
        final item = Map<String, dynamic>.from(rawItem);
        final isRent = item['type'] == 'rent';
        final imgCount = int.tryParse('${item['image_count']}') ?? 1;

        return GestureDetector(
          onTap: () async {
            final id = int.tryParse('${item['id']}') ?? 0;
            if (id > 0) {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => MarketDetailScreen(listingId: id)),
              );
            }
          },
          child: Container(
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.border),
            ),
            clipBehavior: Clip.antiAlias,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Thumbnail with Badges
                Expanded(
                  flex: 5,
                  child: Stack(
                    fit: StackFit.expand,
                    children: [
                      if ((item['primary_image'] ?? '').toString().isNotEmpty)
                        Image.network(
                          _fullImageUrl(item['primary_image'].toString()),
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => _placeholderImage(),
                        )
                      else
                        _placeholderImage(),

                      // Type badge (Jual / Sewa)
                      Positioned(
                        top: 8,
                        left: 8,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                          decoration: BoxDecoration(
                            color: isRent ? const Color(0xFF6D28D9) : const Color(0xFF059669),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            isRent ? 'SEWA' : 'JUAL',
                            style: const TextStyle(
                              fontSize: 9.5,
                              fontWeight: FontWeight.w900,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ),

                      // Photos count indicator badge
                      if (imgCount > 1)
                        Positioned(
                          bottom: 6,
                          right: 6,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: Colors.black.withOpacity(0.65),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.camera_alt_rounded, size: 10, color: Colors.white),
                                const SizedBox(width: 3),
                                Text(
                                  '$imgCount',
                                  style: const TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w700,
                                    color: Colors.white,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                    ],
                  ),
                ),

                // Content
                Expanded(
                  flex: 5,
                  child: Padding(
                    padding: const EdgeInsets.all(10),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          (item['category'] ?? 'Barang').toString(),
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w700,
                            color: AppColors.textMuted,
                          ),
                          maxLines: 1,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          (item['title'] ?? '').toString(),
                          style: const TextStyle(
                            fontSize: 12.5,
                            fontWeight: FontWeight.w800,
                            height: 1.2,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const Spacer(),
                        Text(
                          _formatRupiah(item['price']) +
                              (isRent && item['rent_period'] != null
                                  ? '/${item['rent_period']}'
                                  : ''),
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w900,
                            color: Color(0xFF059669),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            const Icon(Icons.location_on_outlined, size: 12, color: Colors.grey),
                            const SizedBox(width: 2),
                            Expanded(
                              child: Text(
                                (item['location'] ?? '').toString(),
                                style: TextStyle(
                                  fontSize: 10.5,
                                  color: AppColors.textMuted,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _placeholderImage() {
    return Container(
      color: Colors.grey.shade900,
      child: const Center(
        child: Text('🏷️', style: TextStyle(fontSize: 32)),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Container(
      margin: const EdgeInsets.only(top: 30),
      padding: const EdgeInsets.all(30),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        children: [
          const Text('📦', style: TextStyle(fontSize: 44)),
          const SizedBox(height: 10),
          const Text(
            'Belum ada barang di kategori ini',
            style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14),
          ),
          const SizedBox(height: 4),
          Text(
            'Jadilah yang pertama memasang iklan barang bekas atau rental!',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 12, color: AppColors.textMuted),
          ),
        ],
      ),
    );
  }

  Widget _buildSellerHubView() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    return RefreshIndicator(
      onRefresh: _loadMyListings,
      child: Column(
        children: [
          // Sub-tab switcher: Iklan Saya vs Minat Masuk
          Container(
            margin: const EdgeInsets.fromLTRB(14, 12, 14, 6),
            padding: const EdgeInsets.all(4),
            decoration: BoxDecoration(
              color: const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.border),
            ),
            child: Row(
              children: [
                Expanded(
                  child: GestureDetector(
                    onTap: () => setState(() => _sellerSubTab = 0),
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      decoration: BoxDecoration(
                        color: _sellerSubTab == 0 ? Colors.white : Colors.transparent,
                        borderRadius: BorderRadius.circular(9),
                        boxShadow: _sellerSubTab == 0
                            ? [const BoxShadow(color: Colors.black12, blurRadius: 4, offset: Offset(0, 1))]
                            : null,
                      ),
                      child: Center(
                        child: Text(
                          '📦 Iklan Saya (${_myListings.length})',
                          style: TextStyle(
                            fontSize: 12.5,
                            fontWeight: _sellerSubTab == 0 ? FontWeight.w800 : FontWeight.w600,
                            color: _sellerSubTab == 0 ? const Color(0xFF0F172A) : const Color(0xFF64748B),
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
                Expanded(
                  child: GestureDetector(
                    onTap: () => setState(() => _sellerSubTab = 1),
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      decoration: BoxDecoration(
                        color: _sellerSubTab == 1 ? Colors.white : Colors.transparent,
                        borderRadius: BorderRadius.circular(9),
                        boxShadow: _sellerSubTab == 1
                            ? [const BoxShadow(color: Colors.black12, blurRadius: 4, offset: Offset(0, 1))]
                            : null,
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            '💬 Minat Masuk',
                            style: TextStyle(
                              fontSize: 12.5,
                              fontWeight: _sellerSubTab == 1 ? FontWeight.w800 : FontWeight.w600,
                              color: _sellerSubTab == 1 ? const Color(0xFF0F172A) : const Color(0xFF64748B),
                            ),
                          ),
                          if (_ordersReceived.isNotEmpty) ...[
                            const SizedBox(width: 5),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                              decoration: BoxDecoration(
                                color: const Color(0xFFEF4444),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Text(
                                '${_ordersReceived.length}',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 10,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),

          // Content based on selected sub-tab
          Expanded(
            child: _sellerSubTab == 0 ? _buildMyListingsContent() : _buildOrdersReceivedContent(),
          ),
        ],
      ),
    );
  }

  Widget _buildMyListingsContent() {
    if (_myListings.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(30),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text('🏷️', style: TextStyle(fontSize: 48)),
              const SizedBox(height: 12),
              const Text(
                'Anda belum memiliki iklan',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
              ),
              const SizedBox(height: 6),
              Text(
                'Mulai jual barang bekas atau sewakan properti Anda.',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 13, color: AppColors.textMuted),
              ),
            ],
          ),
        ),
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.fromLTRB(14, 10, 14, 90),
      itemCount: _myListings.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (context, index) {
        final rawItem = _myListings[index];
        if (rawItem is! Map) return const SizedBox.shrink();
        final item = Map<String, dynamic>.from(rawItem);
        final id = int.tryParse('${item['id']}') ?? 0;
        return Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: AppColors.card,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.border),
          ),
          child: Row(
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(10),
                child: SizedBox(
                  width: 65,
                  height: 65,
                  child: (item['primary_image'] ?? '').toString().isNotEmpty
                      ? Image.network(
                          _fullImageUrl(item['primary_image'].toString()),
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => _placeholderImage(),
                        )
                      : _placeholderImage(),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      (item['title'] ?? '').toString(),
                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 2),
                    Text(
                      _formatRupiah(item['price']),
                      style: const TextStyle(
                        color: Color(0xFF059669),
                        fontWeight: FontWeight.w900,
                        fontSize: 12.5,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Status: ${item['status']} • 👁️ ${item['views_count'] ?? 0} views',
                      style: TextStyle(fontSize: 11, color: AppColors.textMuted),
                    ),
                  ],
                ),
              ),
              PopupMenuButton<String>(
                onSelected: (val) async {
                  if (val == 'view') {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => MarketDetailScreen(listingId: id)),
                    );
                  } else if (val == 'sold') {
                    await ApiService.instance.updateMarketplaceStatus(id, 'sold');
                    _loadMyListings();
                  } else if (val == 'active') {
                    await ApiService.instance.updateMarketplaceStatus(id, 'active');
                    _loadMyListings();
                  } else if (val == 'delete') {
                    await ApiService.instance.deleteMarketplaceListing(id);
                    _loadMyListings();
                  }
                },
                itemBuilder: (_) => [
                  const PopupMenuItem(value: 'view', child: Text('Lihat Iklan')),
                  if (item['status'] == 'active')
                    const PopupMenuItem(value: 'sold', child: Text('Tandai Terjual'))
                  else
                    const PopupMenuItem(value: 'active', child: Text('Aktifkan Lagi')),
                  const PopupMenuItem(
                    value: 'delete',
                    child: Text('Hapus', style: TextStyle(color: Colors.red)),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildOrdersReceivedContent() {
    if (_ordersReceived.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(30),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text('💬', style: TextStyle(fontSize: 48)),
              const SizedBox(height: 12),
              const Text(
                'Belum Ada Minat Masuk',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
              ),
              const SizedBox(height: 6),
              Text(
                'Ketika ada pembeli yang mengajukan minat beli atau sewa, data kontak dan pesannya akan muncul di sini untuk Anda hubungi.',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 13, color: AppColors.textMuted),
              ),
            ],
          ),
        ),
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.fromLTRB(14, 10, 14, 90),
      itemCount: _ordersReceived.length,
      separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final rawOrder = _ordersReceived[index];
        if (rawOrder is! Map) return const SizedBox.shrink();
        final order = Map<String, dynamic>.from(rawOrder);

        final orderId = int.tryParse('${order['id']}') ?? 0;
        final buyerName = (order['buyer_name'] ?? 'Calon Pembeli').toString();
        final buyerPhone = (order['buyer_phone'] ?? '').toString();
        final listingTitle = (order['listing_title'] ?? 'Produk').toString();
        final listingImg = (order['listing_image'] ?? '').toString();
        final notes = (order['notes'] ?? '-').toString();
        final status = (order['status'] ?? 'pending').toString();
        final createdAt = (order['created_at'] ?? '').toString();
        final price = _formatRupiah(order['price']);

        Color statusBg;
        Color statusColor;
        String statusLabel;
        switch (status) {
          case 'contacted':
            statusBg = const Color(0xFFDBEAFE);
            statusColor = const Color(0xFF1D4ED8);
            statusLabel = 'Sudah Dihubungi';
            break;
          case 'completed':
            statusBg = const Color(0xFFDCFCE7);
            statusColor = const Color(0xFF15803D);
            statusLabel = 'Selesai Transaksi';
            break;
          case 'cancelled':
            statusBg = const Color(0xFFFEE2E2);
            statusColor = const Color(0xFFB91C1C);
            statusLabel = 'Dibatalkan';
            break;
          default:
            statusBg = const Color(0xFFFEF3C7);
            statusColor = const Color(0xFFB45309);
            statusLabel = 'Menunggu Follow-up';
        }

        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: AppColors.card,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.border),
            boxShadow: AppColors.cardShadow,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header: Status Badge & Date
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                    decoration: BoxDecoration(
                      color: statusBg,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      statusLabel,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        color: statusColor,
                      ),
                    ),
                  ),
                  Text(
                    createdAt.length >= 16 ? createdAt.substring(0, 16) : createdAt,
                    style: TextStyle(fontSize: 11, color: AppColors.textMuted),
                  ),
                ],
              ),
              const SizedBox(height: 10),

              // Product Info Row
              Row(
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: SizedBox(
                      width: 48,
                      height: 48,
                      child: listingImg.isNotEmpty
                          ? Image.network(
                              _fullImageUrl(listingImg),
                              fit: BoxFit.cover,
                              errorBuilder: (_, _, _) => _placeholderImage(),
                            )
                          : _placeholderImage(),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          listingTitle,
                          style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          price,
                          style: const TextStyle(
                            color: Color(0xFF059669),
                            fontWeight: FontWeight.w900,
                            fontSize: 12.5,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),

              // Buyer Note Box
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.person_outline_rounded, size: 15, color: Color(0xFF64748B)),
                        const SizedBox(width: 4),
                        Text(
                          buyerName,
                          style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 12),
                        ),
                        if (buyerPhone.isNotEmpty) ...[
                          const SizedBox(width: 6),
                          Text(
                            '($buyerPhone)',
                            style: TextStyle(fontSize: 11.5, color: AppColors.textMuted),
                          ),
                        ],
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '💬 "$notes"',
                      style: const TextStyle(fontSize: 12.5, fontStyle: FontStyle.italic, height: 1.3),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),

              // Action Buttons: Chat di App, WhatsApp, Phone, Status Menu
              Row(
                children: [
                  Expanded(
                    flex: 3,
                    child: ElevatedButton.icon(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        elevation: 0,
                      ),
                      icon: const Icon(Icons.chat_bubble_rounded, size: 15),
                      label: const Text(
                        'Buka Chat',
                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
                      ),
                      onPressed: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => MarketChatScreen(
                              listingId: int.tryParse('${order['listing_id']}') ?? 0,
                              buyerId: int.tryParse('${order['buyer_id']}') ?? 0,
                              initialListingTitle: listingTitle,
                              initialListingPrice: price,
                              initialListingImage: listingImg,
                              targetUserName: buyerName,
                              targetUserPhone: buyerPhone,
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                  if (buyerPhone.isNotEmpty) ...[
                    const SizedBox(width: 6),
                    Expanded(
                      flex: 3,
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF25D366),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 10),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          elevation: 0,
                        ),
                        icon: const Icon(Icons.send_rounded, size: 15),
                        label: const Text(
                          'WhatsApp',
                          style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
                        ),
                        onPressed: () async {
                          final messenger = ScaffoldMessenger.of(context);
                          final msg = 'Halo $buyerName, saya penjual produk "$listingTitle" di DuitKu. Menanggapi minat Anda: "$notes". Apakah Anda masih berminat?';
                          final ok = await WhatsAppLauncher.launch(phone: buyerPhone, text: msg);
                          if (!ok && mounted) {
                            messenger.showSnackBar(
                              const SnackBar(content: Text('Tidak dapat membuka WhatsApp. Pastikan aplikasi WhatsApp terpasang.')),
                            );
                          }
                        },
                      ),
                    ),
                    const SizedBox(width: 6),
                    IconButton.filledTonal(
                      tooltip: 'Telepon Langsung',
                      icon: const Icon(Icons.phone_rounded, size: 18),
                      onPressed: () async {
                        final clean = buyerPhone.replaceAll(RegExp(r'[^0-9]'), '');
                        final telUri = Uri.parse('tel:$clean');
                        if (await canLaunchUrl(telUri)) {
                          await launchUrl(telUri);
                        }
                      },
                    ),
                  ],
                  const SizedBox(width: 6),
                  PopupMenuButton<String>(
                    tooltip: 'Ubah Status',
                    icon: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Icon(Icons.more_vert_rounded, size: 18, color: Color(0xFF475569)),
                    ),
                    onSelected: (newStatus) async {
                      final messenger = ScaffoldMessenger.of(context);
                      try {
                        await ApiService.instance.updateMarketplaceOrderStatus(orderId, newStatus);
                        if (mounted) {
                          setState(() {
                            order['status'] = newStatus;
                          });
                          messenger.showSnackBar(
                            const SnackBar(content: Text('Status pesanan berhasil diperbarui!')),
                          );
                        }
                      } catch (e) {
                        if (mounted) {
                          messenger.showSnackBar(
                            SnackBar(content: Text('Gagal memperbarui status: $e')),
                          );
                        }
                      }
                    },
                    itemBuilder: (_) => [
                      const PopupMenuItem(
                        value: 'contacted',
                        child: Text('Tandai Sudah Dihubungi'),
                      ),
                      const PopupMenuItem(
                        value: 'completed',
                        child: Text('Tandai Selesai Transaksi', style: TextStyle(color: Colors.green)),
                      ),
                      const PopupMenuItem(
                        value: 'cancelled',
                        child: Text('Batalkan Minat', style: TextStyle(color: Colors.red)),
                      ),
                    ],
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  void _showSafetyDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: const Row(
          children: [
            Text('🛡️'),
            SizedBox(width: 8),
            Text('Panduan Transaksi Aman', style: TextStyle(fontSize: 16)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: const [
            Text('1. JANGAN mentransfer uang muka (DP) kepada penjual yang belum dikenal.',
                style: TextStyle(fontSize: 13, height: 1.4)),
            SizedBox(height: 8),
            Text('2. Wajib COD (Ketemu Langsung) di tempat ramai untuk cek fisik barang.',
                style: TextStyle(fontSize: 13, height: 1.4)),
            SizedBox(height: 8),
            Text('3. Gunakan transaksi Shopee / Tokopedia jika jarak jauh agar uang aman di rekening bersama.',
                style: TextStyle(fontSize: 13, height: 1.4)),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Mengerti'),
          ),
        ],
      ),
    );
  }
}
