import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../config/api_config.dart';
import '../../services/api_service.dart';
import '../../theme.dart';

class MarketDetailScreen extends StatefulWidget {
  final int listingId;
  const MarketDetailScreen({super.key, required this.listingId});

  @override
  State<MarketDetailScreen> createState() => _MarketDetailScreenState();
}

class _MarketDetailScreenState extends State<MarketDetailScreen> {
  final PageController _pageController = PageController();
  final TextEditingController _commentCtrl = TextEditingController();

  Map<String, dynamic>? _listing;
  bool _isLoading = true;
  String? _errorMessage;
  int _activePhotoIndex = 0;
  bool _isPostingComment = false;

  @override
  void initState() {
    super.initState();
    _loadDetail();
  }

  @override
  void dispose() {
    _pageController.dispose();
    _commentCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadDetail() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final res = await ApiService.instance.getMarketplaceDetail(widget.listingId);
      if (mounted) {
        setState(() {
          _listing = res['listing'] as Map<String, dynamic>?;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _errorMessage = 'Gagal memuat detail produk: $e';
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

  Future<void> _sendComment() async {
    final text = _commentCtrl.text.trim();
    if (text.isEmpty) return;

    setState(() => _isPostingComment = true);
    try {
      final res = await ApiService.instance.postMarketplaceComment(widget.listingId, text);
      _commentCtrl.clear();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message']?.toString() ?? 'Komentar terkirim!')),
        );
        _loadDetail();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal mengirim komentar: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _isPostingComment = false);
    }
  }

  void _openOrderDialog() {
    final notesCtrl = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: const Text('Ajukan Minat Transaksi', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Catatan atau ajakan COD untuk penjual:',
              style: TextStyle(fontSize: 12, color: Colors.grey),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: notesCtrl,
              maxLines: 3,
              decoration: InputDecoration(
                hintText: 'Contoh: Saya berminat, apakah bisa janjian COD di mall hari Sabtu besok?',
                hintStyle: TextStyle(fontSize: 12, color: AppColors.textMuted),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF4338CA),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () async {
              Navigator.pop(ctx);
              try {
                final res = await ApiService.instance.postMarketplaceOrder(widget.listingId, notesCtrl.text.trim());
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text(res['message']?.toString() ?? 'Pengajuan minat terkirim!')),
                  );
                }
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Gagal mengajukan minat: $e')),
                  );
                }
              }
            },
            child: const Text('Kirim', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800)),
          ),
        ],
      ),
    );
  }

  void _openWhatsApp(String phone, String title) async {
    String clean = phone.replaceAll(RegExp(r'[^0-9]'), '');
    if (clean.startsWith('0')) clean = '62${clean.substring(1)}';

    final shareUrl = ApiConfig.baseUrl + '/marketplace/item/${widget.listingId}';
    final msg = Uri.encodeComponent('Halo, saya melihat produk "$title" di DuitKu ($shareUrl). Apakah barang masih tersedia?');
    final uri = Uri.parse('https://wa.me/$clean?text=$msg');

    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Tidak dapat membuka WhatsApp.')),
        );
      }
    }
  }

  void _shareProduct() {
    final shareUrl = ApiConfig.baseUrl + '/marketplace/item/${widget.listingId}';
    Clipboard.setData(ClipboardData(text: shareUrl));
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Tautan produk disalin ke clipboard! Siap dibagikan ke WhatsApp.')),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        backgroundColor: AppColors.bg,
        appBar: AppBar(title: const Text('Memuat Produk...')),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    if (_errorMessage != null || _listing == null) {
      return Scaffold(
        backgroundColor: AppColors.bg,
        appBar: AppBar(title: const Text('Error')),
        body: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline_rounded, size: 48, color: Colors.red),
              const SizedBox(height: 10),
              Text(_errorMessage ?? 'Produk tidak ditemukan'),
              const SizedBox(height: 14),
              ElevatedButton(onPressed: _loadDetail, child: const Text('Coba Lagi')),
            ],
          ),
        ),
      );
    }

    final item = _listing!;
    final images = (item['images'] as List<dynamic>?) ?? [];
    final comments = (item['comments'] as List<dynamic>?) ?? [];
    final isRent = item['type'] == 'rent';
    final sellerName = (item['seller_name'] ?? 'Penjual').toString();
    final sellerPhone = (item['whatsapp'] ?? item['seller_phone_registered'] ?? '').toString();
    final thirdPartyUrl = (item['third_party_url'] ?? '').toString();

    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: Text(item['title']?.toString() ?? 'Detail Iklan'),
        actions: [
          IconButton(
            icon: const Icon(Icons.share_rounded),
            tooltip: 'Bagikan Link',
            onPressed: _shareProduct,
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.only(bottom: 100),
        children: [
          // 1. Photo Gallery Carousel
          _buildGallery(images, isRent),

          Padding(
            padding: const EdgeInsets.all(14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Category & Condition Tags
                Row(
                  children: [
                    _buildPill(item['category']?.toString() ?? 'Barang'),
                    const SizedBox(width: 6),
                    _buildPill(_formatCondition(item['condition']?.toString())),
                    const SizedBox(width: 6),
                    _buildPill('👁️ ${item['views_count'] ?? 0} views'),
                  ],
                ),

                const SizedBox(height: 10),

                // Title
                Text(
                  item['title']?.toString() ?? '',
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, height: 1.3),
                ),

                const SizedBox(height: 8),

                // Price
                Text(
                  _formatRupiah(item['price']) +
                      (isRent && item['rent_period'] != null ? '/${item['rent_period']}' : ''),
                  style: const TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                    color: Color(0xFF059669),
                  ),
                ),

                const SizedBox(height: 12),

                // Location Box
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppColors.card,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.location_on_rounded, size: 18, color: Colors.red),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          'Lokasi COD: ${item['location'] ?? '-'}',
                          style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700),
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 16),

                // 2. ANTI-SCAM WARNING BOX (REQUIREMENT 2)
                _buildAntiScamCard(),

                const SizedBox(height: 16),

                // Description
                const Text(
                  'DESKRIPSI PRODUK',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Colors.grey),
                ),
                const SizedBox(height: 6),
                Text(
                  (item['description'] ?? 'Tidak ada deskripsi.').toString(),
                  style: const TextStyle(fontSize: 13.5, height: 1.5),
                ),

                const SizedBox(height: 18),

                // 3. Seller Profile Card (REQUIREMENT 4)
                _buildSellerCard(sellerName, item['seller_username']?.toString()),

                const SizedBox(height: 18),

                // 4. Action Buttons
                _buildActionButtons(sellerPhone, item['title']?.toString() ?? '', thirdPartyUrl),

                const SizedBox(height: 24),

                // 5. Comments Section (REQUIREMENT 3)
                _buildCommentsSection(comments),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildGallery(List<dynamic> images, bool isRent) {
    if (images.isEmpty) {
      return Container(
        height: 240,
        color: Colors.black12,
        child: const Center(child: Text('🏷️', style: TextStyle(fontSize: 60))),
      );
    }

    return Stack(
      children: [
        SizedBox(
          height: 280,
          child: PageView.builder(
            controller: _pageController,
            itemCount: images.length,
            onPageChanged: (idx) => setState(() => _activePhotoIndex = idx),
            itemBuilder: (context, index) {
              final imgUrl = images[index]['image_url']?.toString();
              return Image.network(
                _fullImageUrl(imgUrl),
                fit: BoxFit.contain,
                errorBuilder: (_, __, ___) => const Center(child: Icon(Icons.broken_image, size: 40)),
              );
            },
          ),
        ),
        // Badge Type
        Positioned(
          top: 14,
          left: 14,
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: isRent ? const Color(0xFF6D28D9) : const Color(0xFF059669),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Text(
              isRent ? 'SEWA' : 'JUAL',
              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 11),
            ),
          ),
        ),
        // Counter indicator
        if (images.length > 1)
          Positioned(
            bottom: 12,
            right: 14,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
              decoration: BoxDecoration(
                color: Colors.black.withOpacity(0.65),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                '${_activePhotoIndex + 1} / ${images.length}',
                style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w800),
              ),
            ),
          ),
      ],
    );
  }

  Widget _buildAntiScamCard() {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFFEF2F2),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFFCA5A5), width: 1.5),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('🛡️', style: TextStyle(fontSize: 24)),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: const [
                Text(
                  'Panduan Transaksi Aman (Anti-Penipuan)',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w900,
                    color: Color(0xFFB91C1C),
                  ),
                ),
                SizedBox(height: 4),
                Text(
                  '• JANGAN PERNAH transfer DP sebelum cek fisik barang!\n'
                  '• Utamakan COD (Ketemu Langsung) di tempat umum.\n'
                  '• Gunakan Shopee / Tokopedia jika transaksi jarak jauh agar bergaransi aman.',
                  style: TextStyle(fontSize: 11.5, color: Color(0xFF7F1D1D), height: 1.4),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSellerCard(String name, String? username) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 22,
            backgroundColor: const Color(0xFF4338CA),
            child: Text(
              name.isNotEmpty ? name[0].toUpperCase() : 'U',
              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(name, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5)),
                if (username != null && username.isNotEmpty)
                  Text(
                    'domain/$username',
                    style: const TextStyle(color: Color(0xFF6366F1), fontSize: 11.5, fontWeight: FontWeight.w700),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActionButtons(String phone, String title, String thirdPartyUrl) {
    return Column(
      children: [
        Row(
          children: [
            if (phone.isNotEmpty)
              Expanded(
                child: ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF22C55E),
                    padding: const EdgeInsets.symmetric(vertical: 13),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  icon: const Icon(Icons.chat_rounded, color: Colors.white, size: 18),
                  label: const Text('Chat WA', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800)),
                  onPressed: () => _openWhatsApp(phone, title),
                ),
              ),
            if (phone.isNotEmpty) const SizedBox(width: 8),
            Expanded(
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4338CA),
                  padding: const EdgeInsets.symmetric(vertical: 13),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                icon: const Icon(Icons.shopping_bag_outlined, color: Colors.white, size: 18),
                label: const Text('Ajukan Minat', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800)),
                onPressed: _openOrderDialog,
              ),
            ),
          ],
        ),
        if (thirdPartyUrl.isNotEmpty) ...[
          const SizedBox(height: 8),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFEE4D2D),
                padding: const EdgeInsets.symmetric(vertical: 13),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              icon: const Icon(Icons.shopping_cart_rounded, color: Colors.white, size: 18),
              label: const Text('Beli Aman di Shopee / Tokopedia', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800)),
              onPressed: () async {
                final uri = Uri.parse(thirdPartyUrl);
                if (await canLaunchUrl(uri)) {
                  await launchUrl(uri, mode: LaunchMode.externalApplication);
                }
              },
            ),
          ),
        ],
      ],
    );
  }

  Widget _buildCommentsSection(List<dynamic> comments) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Text('💬', style: TextStyle(fontSize: 16)),
              const SizedBox(width: 6),
              Text(
                'Tanya Jawab & Komentar (${comments.length})',
                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Input comment
          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _commentCtrl,
                  decoration: InputDecoration(
                    hintText: 'Tanyakan kondisi atau janjian COD...',
                    hintStyle: TextStyle(fontSize: 12, color: AppColors.textMuted),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              IconButton(
                icon: _isPostingComment
                    ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2))
                    : const Icon(Icons.send_rounded, color: Color(0xFF4338CA)),
                onPressed: _isPostingComment ? null : _sendComment,
              ),
            ],
          ),

          const SizedBox(height: 14),

          // List comments
          if (comments.isEmpty)
            Center(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Text('Belum ada komentar. Jadilah yang pertama bertanya!',
                    style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
              ),
            )
          else
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: comments.length,
              separatorBuilder: (_, __) => const Divider(height: 18),
              itemBuilder: (context, index) {
                final c = comments[index] as Map<String, dynamic>;
                return Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    CircleAvatar(
                      radius: 14,
                      backgroundColor: const Color(0xFF6366F1),
                      child: Text(
                        (c['user_name'] ?? 'U')[0].toUpperCase(),
                        style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w800),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            (c['user_name'] ?? 'Pengguna').toString(),
                            style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12),
                          ),
                          const SizedBox(height: 2),
                          Text((c['comment'] ?? '').toString(), style: const TextStyle(fontSize: 12.5)),
                        ],
                      ),
                    ),
                  ],
                );
              },
            ),
        ],
      ),
    );
  }

  Widget _buildPill(String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: AppColors.bg,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: AppColors.border),
      ),
      child: Text(
        text,
        style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: AppColors.textMuted),
      ),
    );
  }

  String _formatCondition(String? cond) {
    switch (cond) {
      case 'new': return 'Baru';
      case 'like_new': return 'Seperti Baru';
      case 'used_good': return 'Bekas Mulus';
      case 'used_fair': return 'Bekas Layak';
      default: return 'Bekas';
    }
  }
}
