import 'dart:async';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../config/api_config.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/whatsapp_launcher.dart';
import 'market_detail_screen.dart';

class MarketChatScreen extends StatefulWidget {
  final int listingId;
  final int? buyerId;
  final String? initialListingTitle;
  final String? initialListingPrice;
  final String? initialListingImage;
  final String? targetUserName;
  final String? targetUserPhone;

  const MarketChatScreen({
    super.key,
    required this.listingId,
    this.buyerId,
    this.initialListingTitle,
    this.initialListingPrice,
    this.initialListingImage,
    this.targetUserName,
    this.targetUserPhone,
  });

  @override
  State<MarketChatScreen> createState() => _MarketChatScreenState();
}

class _MarketChatScreenState extends State<MarketChatScreen> {
  final List<Map<String, dynamic>> _messages = [];
  final TextEditingController _textCtrl = TextEditingController();
  final ScrollController _scrollCtrl = ScrollController();

  Timer? _pollTimer;
  int _lastId = 0;
  bool _isLoading = true;
  bool _isSending = false;

  Map<String, dynamic>? _listingInfo;
  Map<String, dynamic>? _buyerInfo;
  Map<String, dynamic>? _sellerInfo;
  int _myId = 0;

  int _parseInt(dynamic val) {
    if (val == null) return 0;
    if (val is num) return val.toInt();
    return int.tryParse(val.toString()) ?? 0;
  }

  @override
  void initState() {
    super.initState();
    _fetchMessages();
    _startPolling();
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    _textCtrl.dispose();
    _scrollCtrl.dispose();
    super.dispose();
  }

  void _startPolling() {
    _pollTimer?.cancel();
    _pollTimer = Timer.periodic(const Duration(seconds: 3), (_) {
      if (mounted) {
        _fetchMessages(isPolling: true);
      }
    });
  }

  Future<void> _fetchMessages({bool isPolling = false}) async {
    try {
      final res = await ApiService.instance.getMarketplaceChatMessages(
        listingId: widget.listingId,
        buyerId: widget.buyerId,
        afterId: _lastId,
      );
      if (!mounted) return;

      final list = (res['messages'] as List<dynamic>?) ?? [];
      final listing = res['listing'] as Map<String, dynamic>?;
      final buyer = res['buyer'] as Map<String, dynamic>?;
      final seller = res['seller'] as Map<String, dynamic>?;
      final myId = _parseInt(res['my_id']);

      setState(() {
        _myId = myId;
        if (listing != null) _listingInfo = listing;
        if (buyer != null) _buyerInfo = buyer;
        if (seller != null) _sellerInfo = seller;

        if (list.isNotEmpty) {
          for (final item in list) {
            final m = Map<String, dynamic>.from(item as Map);
            final id = _parseInt(m['id']);
            if (id > _lastId) {
              _lastId = id;
            }
            final exists = _messages.any((x) => _parseInt(x['id']) == id);
            if (!exists) {
              _messages.add(m);
            }
          }
        }
        _isLoading = false;
      });

      if (list.isNotEmpty) {
        WidgetsBinding.instance.addPostFrameCallback((_) => _scrollToBottom());
      }
    } catch (e) {
      if (!isPolling && mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _scrollToBottom() {
    if (_scrollCtrl.hasClients) {
      _scrollCtrl.animateTo(
        _scrollCtrl.position.maxScrollExtent + 60,
        duration: const Duration(milliseconds: 250),
        curve: Curves.easeOut,
      );
    }
  }

  Future<void> _sendMessage([String? quickText]) async {
    final text = (quickText ?? _textCtrl.text).trim();
    if (text.isEmpty || _isSending) return;

    if (quickText == null) {
      _textCtrl.clear();
    }

    setState(() => _isSending = true);

    try {
      final res = await ApiService.instance.sendMarketplaceChatMessage(
        listingId: widget.listingId,
        buyerId: widget.buyerId,
        message: text,
      );

      final chat = res['chat'];
      if (chat is Map && mounted) {
        final m = Map<String, dynamic>.from(chat);
        final id = _parseInt(m['id']);
        if (id > _lastId) _lastId = id;

        setState(() {
          final exists = _messages.any((x) => _parseInt(x['id']) == id);
          if (!exists) {
            _messages.add(m);
          }
        });
        WidgetsBinding.instance.addPostFrameCallback((_) => _scrollToBottom());
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal mengirim pesan: $e')),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isSending = false);
      }
    }
  }

  String _fullImageUrl(String? url) {
    if (url == null || url.isEmpty) return '';
    if (url.startsWith('http://') || url.startsWith('https://')) return url;
    return ApiConfig.baseUrl + (url.startsWith('/') ? '' : '/') + url;
  }

  String _formatRupiah(dynamic val) {
    if (val == null) return 'Rp 0';
    final n = num.tryParse(val.toString()) ?? 0;
    final s = n.toStringAsFixed(0).replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]}.',
    );
    return 'Rp $s';
  }

  @override
  Widget build(BuildContext context) {
    final isSeller = _myId > 0 && _sellerInfo != null && _parseInt(_sellerInfo?['id']) == _myId;
    final partnerName = isSeller
        ? (_buyerInfo?['name'] ?? widget.targetUserName ?? 'Pembeli')
        : (_sellerInfo?['name'] ?? widget.targetUserName ?? 'Penjual');
    final partnerPhone = isSeller
        ? (_buyerInfo?['phone'] ?? widget.targetUserPhone ?? '')
        : (_sellerInfo?['phone'] ?? widget.targetUserPhone ?? '');

    final listingTitle = _listingInfo?['title'] ?? widget.initialListingTitle ?? 'Produk';
    final listingPrice = _listingInfo?['price'] != null
        ? _formatRupiah(_listingInfo!['price'])
        : (widget.initialListingPrice ?? '');
    final listingImg = (_listingInfo?['primary_image'] ?? widget.initialListingImage ?? '').toString();

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        titleSpacing: 0,
        backgroundColor: Colors.white,
        elevation: 0.5,
        title: Row(
          children: [
            CircleAvatar(
              radius: 18,
              backgroundColor: isSeller ? const Color(0xFF3B82F6) : const Color(0xFF059669),
              child: Text(
                partnerName.isNotEmpty ? partnerName[0].toUpperCase() : 'U',
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 14),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    partnerName,
                    style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  Text(
                    isSeller ? 'Calon Pembeli' : 'Penjual Produk',
                    style: TextStyle(fontSize: 11, color: AppColors.textMuted),
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          if (partnerPhone.isNotEmpty) ...[
            IconButton(
              icon: const Icon(Icons.chat_rounded, color: Color(0xFF25D366), size: 22),
              tooltip: 'Buka WhatsApp',
              onPressed: () {
                final msg = 'Halo $partnerName, saya terhubung dengan Anda melalui chat DuitKu mengenai produk "$listingTitle".';
                WhatsAppLauncher.launch(phone: partnerPhone, text: msg);
              },
            ),
            IconButton(
              icon: const Icon(Icons.phone_rounded, color: Color(0xFF0284C7), size: 22),
              tooltip: 'Telepon Langsung',
              onPressed: () async {
                final clean = partnerPhone.replaceAll(RegExp(r'[^0-9]'), '');
                final uri = Uri.parse('tel:$clean');
                if (await canLaunchUrl(uri)) {
                  await launchUrl(uri);
                }
              },
            ),
          ],
        ],
      ),
      body: Column(
        children: [
          // Pinned Product Summary Banner
          InkWell(
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => MarketDetailScreen(listingId: widget.listingId)),
              );
            },
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
              decoration: BoxDecoration(
                color: Colors.white,
                border: Border(bottom: BorderSide(color: Colors.grey.shade200)),
              ),
              child: Row(
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: SizedBox(
                      width: 44,
                      height: 44,
                      child: listingImg.isNotEmpty
                          ? Image.network(
                              _fullImageUrl(listingImg),
                              fit: BoxFit.cover,
                              errorBuilder: (_, _, _) => const Icon(Icons.image, color: Colors.grey),
                            )
                          : const Icon(Icons.inventory_2_outlined, color: Colors.grey),
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
                        if (listingPrice.isNotEmpty)
                          Text(
                            listingPrice,
                            style: const TextStyle(
                              color: Color(0xFF059669),
                              fontWeight: FontWeight.w800,
                              fontSize: 12,
                            ),
                          ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 6),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppColors.bg,
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: const Text('Detail ➔', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700)),
                  ),
                ],
              ),
            ),
          ),

          // Message List Area
          Expanded(
            child: _isLoading && _messages.isEmpty
                ? const Center(child: CircularProgressIndicator())
                : _messages.isEmpty
                    ? Center(
                        child: Padding(
                          padding: const EdgeInsets.all(32),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Text('💬', style: TextStyle(fontSize: 48)),
                              const SizedBox(height: 10),
                              const Text(
                                'Mulai Percakapan',
                                style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16),
                              ),
                              const SizedBox(height: 6),
                              Text(
                                'Kirim pesan atau gunakan pilihan cepat di bawah untuk menanyakan produk.',
                                textAlign: TextAlign.center,
                                style: TextStyle(color: AppColors.textMuted, fontSize: 12.5),
                              ),
                            ],
                          ),
                        ),
                      )
                    : ListView.builder(
                        controller: _scrollCtrl,
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        itemCount: _messages.length,
                        itemBuilder: (context, index) {
                          final msg = _messages[index];
                          final senderId = _parseInt(msg['sender_id']);
                          final isMine = _myId > 0 && senderId == _myId;
                          final text = (msg['message'] ?? '').toString();
                          final createdAt = (msg['created_at'] ?? '').toString();
                          final timeStr = createdAt.length >= 16 ? createdAt.substring(11, 16) : '';

                          return Align(
                            alignment: isMine ? Alignment.centerRight : Alignment.centerLeft,
                            child: Container(
                              margin: const EdgeInsets.only(bottom: 10),
                              constraints: BoxConstraints(
                                maxWidth: MediaQuery.of(context).size.width * 0.78,
                              ),
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                              decoration: BoxDecoration(
                                color: isMine ? const Color(0xFF059669) : Colors.white,
                                borderRadius: BorderRadius.only(
                                  topLeft: const Radius.circular(16),
                                  topRight: const Radius.circular(16),
                                  bottomLeft: Radius.circular(isMine ? 16 : 4),
                                  bottomRight: Radius.circular(isMine ? 4 : 16),
                                ),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withAlpha(isMine ? 20 : 10),
                                    blurRadius: 4,
                                    offset: const Offset(0, 1),
                                  ),
                                ],
                              ),
                              child: Column(
                                crossAxisAlignment:
                                    isMine ? CrossAxisAlignment.end : CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    text,
                                    style: TextStyle(
                                      color: isMine ? Colors.white : const Color(0xFF1E293B),
                                      fontSize: 13.5,
                                      height: 1.4,
                                    ),
                                  ),
                                  const SizedBox(height: 3),
                                  Text(
                                    timeStr,
                                    style: TextStyle(
                                      color: isMine ? Colors.white70 : Colors.grey.shade500,
                                      fontSize: 10,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
          ),

          // Quick Suggestion Chips (especially helpful when starting conversation)
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
            child: Row(
              children: [
                _buildQuickChip('Apakah barang masih ada?'),
                const SizedBox(width: 6),
                _buildQuickChip('Bisa nego harganya?'),
                const SizedBox(width: 6),
                _buildQuickChip('Bisa COD di mana?'),
                const SizedBox(width: 6),
                _buildQuickChip('Kondisi barangnya bagaimana?'),
              ],
            ),
          ),

          // Bottom Chat Input Bar
          Container(
            padding: const EdgeInsets.fromLTRB(12, 8, 12, 16),
            decoration: BoxDecoration(
              color: Colors.white,
              border: Border(top: BorderSide(color: Colors.grey.shade200)),
            ),
            child: SafeArea(
              top: false,
              child: Row(
                children: [
                  Expanded(
                    child: Container(
                      decoration: BoxDecoration(
                        color: const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(24),
                      ),
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: TextField(
                        controller: _textCtrl,
                        textInputAction: TextInputAction.send,
                        onSubmitted: (_) => _sendMessage(),
                        style: const TextStyle(
                          color: Color(0xFF0F172A),
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                        ),
                        cursorColor: const Color(0xFF059669),
                        decoration: const InputDecoration(
                          hintText: 'Tulis pesan atau penawaran...',
                          hintStyle: TextStyle(fontSize: 13, color: Color(0xFF94A3B8)),
                          border: InputBorder.none,
                          contentPadding: EdgeInsets.symmetric(vertical: 12),
                          filled: false,
                        ),
                        maxLines: null,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    decoration: const BoxDecoration(
                      shape: BoxShape.circle,
                      color: Color(0xFF059669),
                    ),
                    child: IconButton(
                      icon: _isSending
                          ? const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                            )
                          : const Icon(Icons.send_rounded, color: Colors.white, size: 20),
                      onPressed: _isSending ? null : () => _sendMessage(),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickChip(String text) {
    return ActionChip(
      label: Text(text, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600)),
      backgroundColor: Colors.white,
      side: BorderSide(color: Colors.grey.shade300),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      onPressed: () => _sendMessage(text),
    );
  }
}
