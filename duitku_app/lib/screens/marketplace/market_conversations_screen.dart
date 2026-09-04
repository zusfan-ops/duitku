import 'package:flutter/material.dart';

import '../../config/api_config.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import 'market_chat_screen.dart';

class MarketConversationsScreen extends StatefulWidget {
  const MarketConversationsScreen({super.key});

  @override
  State<MarketConversationsScreen> createState() => _MarketConversationsScreenState();
}

class _MarketConversationsScreenState extends State<MarketConversationsScreen> {
  bool _isLoading = true;
  String? _errorMessage;
  List<Map<String, dynamic>> _conversations = [];
  int _myId = 0;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final res = await ApiService.instance.getMarketplaceChatConversations();
      _myId = int.tryParse('${res['my_id']}') ?? 0;
      final list = (res['conversations'] as List<dynamic>?) ?? [];

      if (mounted) {
        setState(() {
          _conversations = list.map((e) => Map<String, dynamic>.from(e as Map)).toList();
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _errorMessage = 'Gagal memuat daftar obrolan: $e';
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
        title: const Text('Pesan & Chat'),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Segarkan',
            onPressed: _loadData,
          ),
        ],
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline_rounded, size: 48, color: Colors.redAccent),
              const SizedBox(height: 12),
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 14, color: Colors.black87),
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: _loadData,
                icon: const Icon(Icons.refresh_rounded),
                label: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      );
    }

    if (_conversations.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 80,
                height: 80,
                decoration: BoxDecoration(
                  color: AppColors.primary.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: Icon(Icons.forum_outlined, size: 40, color: AppColors.primary),
              ),
              const SizedBox(height: 20),
              const Text(
                'Belum Ada Pesan',
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800),
              ),
              const SizedBox(height: 8),
              Text(
                'Obrolan akan otomatis dimulai ketika ada calon pembeli yang mengajukan minat pada produk Anda, atau saat Anda menghubungi penjual.',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 13, color: AppColors.textMuted, height: 1.4),
              ),
            ],
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadData,
      child: ListView.separated(
        padding: const EdgeInsets.symmetric(vertical: 8),
        itemCount: _conversations.length,
        separatorBuilder: (_, _) => Divider(height: 1, color: Colors.grey.shade200),
        itemBuilder: (context, index) {
          final conv = _conversations[index];
          final listingId = int.tryParse('${conv['listing_id']}') ?? 0;
          final buyerId = int.tryParse('${conv['buyer_id']}') ?? 0;
          final sellerId = int.tryParse('${conv['seller_id']}') ?? 0;
          final isSeller = _myId > 0 && _myId == sellerId;

          final partnerName = isSeller
              ? (conv['buyer_name'] ?? 'Calon Pembeli').toString()
              : (conv['seller_name'] ?? 'Penjual').toString();
          final partnerPhone = isSeller
              ? (conv['buyer_phone'] ?? '').toString()
              : (conv['seller_phone'] ?? '').toString();

          final title = (conv['listing_title'] ?? 'Produk').toString();
          final price = _formatRupiah(conv['listing_price']);
          final img = (conv['listing_image'] ?? '').toString();
          final lastMsg = (conv['last_message'] ?? '').toString();
          final lastSenderId = int.tryParse('${conv['last_sender_id']}') ?? 0;
          final unreadCount = int.tryParse('${conv['unread_count']}') ?? 0;
          final timeStr = (conv['last_message_time'] ?? '').toString();

          final isMyMsg = _myId > 0 && lastSenderId == _myId;

          return InkWell(
            onTap: () async {
              await Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => MarketChatScreen(
                    listingId: listingId,
                    buyerId: buyerId,
                    initialListingTitle: title,
                    initialListingPrice: price,
                    initialListingImage: img,
                    targetUserName: partnerName,
                    targetUserPhone: partnerPhone,
                  ),
                ),
              );
              _loadData();
            },
            child: Container(
              color: unreadCount > 0 ? AppColors.primary.withValues(alpha: 0.04) : Colors.transparent,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Product / Avatar Stack
                  Stack(
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(10),
                        child: Container(
                          width: 52,
                          height: 52,
                          color: const Color(0xFFF1F5F9),
                          child: img.isNotEmpty
                              ? Image.network(
                                  _fullImageUrl(img),
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, _, _) => const Icon(
                                    Icons.shopping_bag_outlined,
                                    size: 26,
                                    color: Color(0xFF94A3B8),
                                  ),
                                )
                              : const Icon(
                                  Icons.shopping_bag_outlined,
                                  size: 26,
                                  color: Color(0xFF94A3B8),
                                ),
                        ),
                      ),
                      Positioned(
                        right: 0,
                        bottom: 0,
                        child: Container(
                          padding: const EdgeInsets.all(2),
                          decoration: BoxDecoration(
                            color: isSeller ? const Color(0xFF0284C7) : const Color(0xFF059669),
                            shape: BoxShape.circle,
                            border: Border.all(color: Colors.white, width: 1.5),
                          ),
                          child: Icon(
                            isSeller ? Icons.person_rounded : Icons.store_rounded,
                            size: 10,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(width: 12),

                  // Message Content
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Partner Name & Time
                        Row(
                          children: [
                            Expanded(
                              child: Row(
                                children: [
                                  Flexible(
                                    child: Text(
                                      partnerName,
                                      style: TextStyle(
                                        fontWeight: unreadCount > 0 ? FontWeight.w900 : FontWeight.w700,
                                        fontSize: 14,
                                        color: const Color(0xFF0F172A),
                                      ),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                  const SizedBox(width: 6),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                                    decoration: BoxDecoration(
                                      color: isSeller
                                          ? const Color(0xFFE0F2FE)
                                          : const Color(0xFFDCFCE7),
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: Text(
                                      isSeller ? 'Pembeli' : 'Penjual',
                                      style: TextStyle(
                                        fontSize: 9.5,
                                        fontWeight: FontWeight.w800,
                                        color: isSeller
                                            ? const Color(0xFF0284C7)
                                            : const Color(0xFF15803D),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              timeStr.length >= 16 ? timeStr.substring(11, 16) : timeStr,
                              style: TextStyle(
                                fontSize: 11,
                                color: unreadCount > 0 ? AppColors.primary : AppColors.textMuted,
                                fontWeight: unreadCount > 0 ? FontWeight.w800 : FontWeight.normal,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 2),

                        // Listing Title & Price
                        Text(
                          '$title • $price',
                          style: TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.w600,
                            color: AppColors.textMuted,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 4),

                        // Last Message & Badge
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                isMyMsg ? 'Anda: $lastMsg' : lastMsg,
                                style: TextStyle(
                                  fontSize: 12.5,
                                  color: unreadCount > 0 ? const Color(0xFF1E293B) : const Color(0xFF64748B),
                                  fontWeight: unreadCount > 0 ? FontWeight.w700 : FontWeight.normal,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            if (unreadCount > 0) ...[
                              const SizedBox(width: 8),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                decoration: BoxDecoration(
                                  color: AppColors.primary,
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Text(
                                  '$unreadCount',
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
                      ],
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
