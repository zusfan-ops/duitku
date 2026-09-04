import 'package:flutter/material.dart';

import '../../config/api_config.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import 'market_create_screen.dart';
import 'market_detail_screen.dart';

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
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _errorMessage = 'Gagal memuat iklan saya: $e';
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
          tabs: const [
            Tab(text: 'Semua'),
            Tab(text: '🏷️ Jual'),
            Tab(text: '🔑 Sewa'),
            Tab(text: '📦 Iklan Saya'),
          ],
        ),
      ),
      body: _tabController.index == 3 ? _buildMyListingsView() : _buildBrowseView(),
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
        final item = items[index] as Map<String, dynamic>;
        final isRent = item['type'] == 'rent';
        final imgCount = item['image_count'] as int? ?? 1;

        return GestureDetector(
          onTap: () async {
            final id = item['id'] as int? ?? 0;
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

  Widget _buildMyListingsView() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }
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

    return RefreshIndicator(
      onRefresh: _loadMyListings,
      child: ListView.separated(
        padding: const EdgeInsets.fromLTRB(14, 14, 14, 90),
        itemCount: _myListings.length,
        separatorBuilder: (_, __) => const SizedBox(height: 10),
        itemBuilder: (context, index) {
          final item = _myListings[index] as Map<String, dynamic>;
          final id = item['id'] as int? ?? 0;
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
      ),
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
