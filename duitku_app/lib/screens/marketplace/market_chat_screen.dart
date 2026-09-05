import 'dart:async';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../config/api_config.dart';
import '../../services/api_service.dart';
import '../../utils/whatsapp_launcher.dart';
import '../chat/chat_media_helpers.dart';
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

    final effectiveBuyerId = widget.buyerId ?? _parseInt(_buyerInfo?['id']);

    try {
      final res = await ApiService.instance.sendMarketplaceChatMessage(
        listingId: widget.listingId,
        buyerId: effectiveBuyerId > 0 ? effectiveBuyerId : null,
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

  bool _isPinned = false;
  bool _isArchived = false;

  void _onSelectEmoji(String emoji) {
    final text = _textCtrl.text;
    final selection = _textCtrl.selection;
    final newText = selection.start >= 0
        ? text.replaceRange(selection.start, selection.end, emoji)
        : text + emoji;
    _textCtrl.value = TextEditingValue(
      text: newText,
      selection: TextSelection.collapsed(
        offset: (selection.start >= 0 ? selection.start : text.length) + emoji.length,
      ),
    );
  }

  Future<void> _pickAndSendPhoto(ImageSource source) async {
    try {
      final picker = ImagePicker();
      final picked = await picker.pickImage(
        source: source,
        maxWidth: 1200,
        maxHeight: 1200,
        imageQuality: 82,
      );
      if (picked == null) return;

      setState(() => _isSending = true);

      final b64 = await ApiService.instance.base64FromFile(picked.path);
      if (b64 == null) throw Exception('Gagal membaca berkas gambar.');

      final uploadRes = await ApiService.instance.uploadChatImage(b64);
      final url = uploadRes['url'] as String?;
      if (url == null || url.isEmpty) {
        throw Exception(uploadRes['message'] ?? 'Gagal mengunggah foto.');
      }

      await _sendMessage('[img:$url]');
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal mengirim foto: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _isSending = false);
    }
  }

  Future<void> _togglePin() async {
    final effectiveBuyerId = widget.buyerId ?? _parseInt(_buyerInfo?['id']);
    try {
      final res = await ApiService.instance.pinConversation(
        type: 'marketplace',
        targetId: widget.listingId,
        targetSubId: effectiveBuyerId,
      );
      if (mounted) {
        setState(() => _isPinned = (res['is_pinned'] == 1 || res['is_pinned'] == true));
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Status pin diperbarui')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal mengubah status pin: $e')),
        );
      }
    }
  }

  Future<void> _toggleArchive() async {
    final effectiveBuyerId = widget.buyerId ?? _parseInt(_buyerInfo?['id']);
    try {
      final res = await ApiService.instance.archiveConversation(
        type: 'marketplace',
        targetId: widget.listingId,
        targetSubId: effectiveBuyerId,
      );
      if (mounted) {
        setState(() => _isArchived = (res['is_archived'] == 1 || res['is_archived'] == true));
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Status arsip diperbarui')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal mengubah status arsip: $e')),
        );
      }
    }
  }

  Future<void> _deleteChat() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Hapus Seluruh Obrolan?'),
        content: const Text('Semua riwayat obrolan mengenai produk ini akan dihapus permanen.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Hapus', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    final effectiveBuyerId = widget.buyerId ?? _parseInt(_buyerInfo?['id']);
    try {
      final res = await ApiService.instance.deleteConversation(
        type: 'marketplace',
        targetId: widget.listingId,
        targetSubId: effectiveBuyerId,
      );
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Obrolan berhasil dihapus')),
        );
        Navigator.pop(context, true);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal menghapus obrolan: $e')),
        );
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

  String _formatDateChip(String dateStr) {
    if (dateStr.length < 10) return '';
    try {
      final dt = DateTime.parse(dateStr.substring(0, 10));
      final now = DateTime.now();
      final today = DateTime(now.year, now.month, now.day);
      final msgDay = DateTime(dt.year, dt.month, dt.day);
      final diff = today.difference(msgDay).inDays;
      if (diff == 0) return 'HARI INI';
      if (diff == 1) return 'KEMARIN';
      const months = [
        '', 'JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI',
        'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'
      ];
      return '${dt.day} ${months[dt.month]} ${dt.year}';
    } catch (_) {
      return dateStr.substring(0, 10);
    }
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
      backgroundColor: const Color(0xFFEFEAE2), // WhatsApp Classic Wallpaper Color
      appBar: AppBar(
        titleSpacing: 0,
        backgroundColor: Colors.white,
        elevation: 1,
        shadowColor: Colors.black.withValues(alpha: 0.08),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: Color(0xFF1E293B)),
          onPressed: () => Navigator.pop(context),
        ),
        title: Row(
          children: [
            Stack(
              clipBehavior: Clip.none,
              children: [
                CircleAvatar(
                  radius: 20,
                  backgroundColor: isSeller ? const Color(0xFF2563EB) : const Color(0xFF00A884),
                  child: Text(
                    partnerName.isNotEmpty ? partnerName[0].toUpperCase() : 'U',
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 15),
                  ),
                ),
                Positioned(
                  right: 0,
                  bottom: 0,
                  child: Container(
                    width: 11,
                    height: 11,
                    decoration: BoxDecoration(
                      color: const Color(0xFF22C55E),
                      shape: BoxShape.circle,
                      border: Border.all(color: Colors.white, width: 2),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    partnerName,
                    style: const TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF0F172A),
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 1),
                  Row(
                    children: [
                      Container(
                        width: 6,
                        height: 6,
                        decoration: const BoxDecoration(
                          color: Color(0xFF22C55E),
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 4),
                      Text(
                        isSeller ? 'Calon Pembeli • Online' : 'Penjual Marketplace • Online',
                        style: const TextStyle(
                          fontSize: 11,
                          color: Color(0xFF64748B),
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
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
          PopupMenuButton<String>(
            icon: const Icon(Icons.more_vert_rounded, color: Color(0xFF1E293B)),
            tooltip: 'Opsi Obrolan',
            onSelected: (val) {
              switch (val) {
                case 'pin':
                  _togglePin();
                  break;
                case 'archive':
                  _toggleArchive();
                  break;
                case 'delete':
                  _deleteChat();
                  break;
              }
            },
            itemBuilder: (ctx) => [
              PopupMenuItem(
                value: 'pin',
                child: Row(
                  children: [
                    Icon(
                      _isPinned ? Icons.push_pin_outlined : Icons.push_pin_rounded,
                      size: 20,
                      color: const Color(0xFF00A884),
                    ),
                    const SizedBox(width: 12),
                    Text(_isPinned ? 'Lepas Sematan' : 'Sematkan Obrolan'),
                  ],
                ),
              ),
              PopupMenuItem(
                value: 'archive',
                child: Row(
                  children: [
                    Icon(
                      _isArchived ? Icons.unarchive_outlined : Icons.archive_outlined,
                      size: 20,
                      color: Colors.blueAccent,
                    ),
                    const SizedBox(width: 12),
                    Text(_isArchived ? 'Buka dari Arsip' : 'Arsipkan Obrolan'),
                  ],
                ),
              ),
              const PopupMenuDivider(),
              const PopupMenuItem(
                value: 'delete',
                child: Row(
                  children: [
                    Icon(Icons.delete_outline_rounded, size: 20, color: Colors.red),
                    SizedBox(width: 12),
                    Text('Hapus Obrolan', style: TextStyle(color: Colors.red)),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
      body: Column(
        children: [
          // Pinned Product Summary Banner (WhatsApp / Marketplace style)
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
                color: Colors.white.withValues(alpha: 0.95),
                border: const Border(bottom: BorderSide(color: Color(0xFFE2E8F0), width: 0.8)),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.03),
                    blurRadius: 4,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Row(
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: SizedBox(
                      width: 42,
                      height: 42,
                      child: listingImg.isNotEmpty
                          ? Image.network(
                              _fullImageUrl(listingImg),
                              fit: BoxFit.cover,
                              errorBuilder: (_, _, _) => Container(
                                color: const Color(0xFFF1F5F9),
                                child: const Icon(Icons.image, color: Colors.grey, size: 20),
                              ),
                            )
                          : Container(
                              color: const Color(0xFFF1F5F9),
                              child: const Icon(Icons.inventory_2_outlined, color: Colors.grey, size: 20),
                            ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          listingTitle,
                          style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: Color(0xFF1E293B)),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        if (listingPrice.isNotEmpty)
                          Text(
                            listingPrice,
                            style: const TextStyle(
                              color: Color(0xFF00A884),
                              fontWeight: FontWeight.w800,
                              fontSize: 12,
                            ),
                          ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: const Color(0xFFCBD5E1)),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text('Detail', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Color(0xFF334155))),
                        SizedBox(width: 2),
                        Icon(Icons.chevron_right_rounded, size: 14, color: Color(0xFF64748B)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Message List Area (WhatsApp / Telegram style)
          Expanded(
            child: _isLoading && _messages.isEmpty
                ? const Center(child: CircularProgressIndicator(color: Color(0xFF00A884)))
                : _messages.isEmpty
                    ? Center(
                        child: Padding(
                          padding: const EdgeInsets.all(32),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Container(
                                width: 72,
                                height: 72,
                                decoration: BoxDecoration(
                                  color: Colors.white.withValues(alpha: 0.9),
                                  shape: BoxShape.circle,
                                  boxShadow: const [
                                    BoxShadow(
                                      color: Color(0x10000000),
                                      blurRadius: 8,
                                      offset: Offset(0, 2),
                                    ),
                                  ],
                                ),
                                child: const Center(
                                  child: Icon(Icons.chat_bubble_outline_rounded, size: 36, color: Color(0xFF00A884)),
                                ),
                              ),
                              const SizedBox(height: 14),
                              const Text(
                                'Belum Ada Pesan',
                                style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF1E293B)),
                              ),
                              const SizedBox(height: 6),
                              const Text(
                                'Kirim pesan atau gunakan pilihan cepat di bawah untuk menanyakan ketersediaan produk.',
                                textAlign: TextAlign.center,
                                style: TextStyle(color: Color(0xFF64748B), fontSize: 12.5, height: 1.4),
                              ),
                            ],
                          ),
                        ),
                      )
                    : ListView.builder(
                        controller: _scrollCtrl,
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        itemCount: _messages.length,
                        itemBuilder: (context, index) {
                          final msg = _messages[index];
                          final senderId = _parseInt(msg['sender_id']);
                          final isMine = _myId > 0 && senderId == _myId;
                          final createdAt = (msg['created_at'] ?? '').toString();

                          // Check if date chip is needed
                          bool showDateChip = false;
                          String dateChipText = '';
                          if (createdAt.length >= 10) {
                            dateChipText = _formatDateChip(createdAt);
                            if (index == 0) {
                              showDateChip = true;
                            } else {
                              final prevCreatedAt = (_messages[index - 1]['created_at'] ?? '').toString();
                              if (prevCreatedAt.length >= 10) {
                                final prevChipText = _formatDateChip(prevCreatedAt);
                                if (prevChipText != dateChipText) {
                                  showDateChip = true;
                                }
                              }
                            }
                          }

                          return Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              if (showDateChip && dateChipText.isNotEmpty)
                                _buildDateBadge(dateChipText),
                              _buildMessageBubble(msg, isMine),
                            ],
                          );
                        },
                      ),
          ),

          // Quick Suggestion Chips (WhatsApp / Telegram style)
          Container(
            color: Colors.transparent,
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              child: Row(
                children: [
                  _buildQuickChip('Apakah barang masih ada?'),
                  const SizedBox(width: 6),
                  _buildQuickChip('Bisa nego harganya?'),
                  const SizedBox(width: 6),
                  _buildQuickChip('Bisa COD di mana?'),
                  const SizedBox(width: 6),
                  _buildQuickChip('Kondisi barang bagaimana?'),
                ],
              ),
            ),
          ),

          // Bottom Chat Input Bar (WhatsApp / Telegram Style)
          Container(
            padding: const EdgeInsets.fromLTRB(8, 4, 8, 10),
            color: Colors.transparent,
            child: SafeArea(
              top: false,
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  // WhatsApp Rounded Capsule
                  Expanded(
                    child: Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(25),
                        boxShadow: const [
                          BoxShadow(
                            color: Color(0x18000000),
                            blurRadius: 4,
                            offset: Offset(0, 1),
                          ),
                        ],
                      ),
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          IconButton(
                            icon: const Icon(Icons.sentiment_satisfied_alt_outlined, color: Color(0xFF8696A0), size: 24),
                            tooltip: 'Pilih Emoji',
                            onPressed: () => ChatMediaHelpers.showEmojiPicker(
                              context,
                              onEmojiSelected: _onSelectEmoji,
                            ),
                            padding: const EdgeInsets.all(6),
                            constraints: const BoxConstraints(),
                          ),
                          const SizedBox(width: 4),
                          Expanded(
                            child: TextField(
                              controller: _textCtrl,
                              textInputAction: TextInputAction.send,
                              onSubmitted: (_) => _sendMessage(),
                              keyboardType: TextInputType.multiline,
                              maxLines: 5,
                              minLines: 1,
                              style: const TextStyle(
                                color: Color(0xFF111B21),
                                fontSize: 14.5,
                                height: 1.3,
                              ),
                              cursorColor: const Color(0xFF00A884),
                              decoration: const InputDecoration(
                                hintText: 'Ketik pesan...',
                                hintStyle: TextStyle(fontSize: 14, color: Color(0xFF8696A0)),
                                border: InputBorder.none,
                                contentPadding: EdgeInsets.symmetric(vertical: 9),
                                isDense: true,
                              ),
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.attach_file_rounded, color: Color(0xFF8696A0), size: 22),
                            tooltip: 'Lampirkan Berkas',
                            onPressed: () => ChatMediaHelpers.showAttachmentPicker(
                              context,
                              onCamera: () => _pickAndSendPhoto(ImageSource.camera),
                              onGallery: () => _pickAndSendPhoto(ImageSource.gallery),
                            ),
                            padding: const EdgeInsets.all(6),
                            constraints: const BoxConstraints(),
                          ),
                          const SizedBox(width: 2),
                          IconButton(
                            icon: const Icon(Icons.camera_alt_rounded, color: Color(0xFF8696A0), size: 22),
                            tooltip: 'Ambil Foto',
                            onPressed: () => _pickAndSendPhoto(ImageSource.camera),
                            padding: const EdgeInsets.all(6),
                            constraints: const BoxConstraints(),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 6),
                  // Detached Circular Send Button (WhatsApp Green)
                  Container(
                    width: 46,
                    height: 46,
                    decoration: const BoxDecoration(
                      color: Color(0xFF00A884),
                      shape: BoxShape.circle,
                      boxShadow: [
                        BoxShadow(
                          color: Color(0x3300A884),
                          blurRadius: 6,
                          offset: Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Material(
                      color: Colors.transparent,
                      child: InkWell(
                        onTap: _isSending ? null : () => _sendMessage(),
                        customBorder: const CircleBorder(),
                        child: Center(
                          child: _isSending
                              ? const SizedBox(
                                  width: 20,
                                  height: 20,
                                  child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.2),
                                )
                              : const Icon(Icons.send_rounded, color: Colors.white, size: 20),
                        ),
                      ),
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

  Widget _buildDateBadge(String label) {
    return Center(
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 8),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4.5),
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: 0.92),
          borderRadius: BorderRadius.circular(8),
          boxShadow: const [
            BoxShadow(
              color: Color(0x10000000),
              blurRadius: 3,
              offset: Offset(0, 1),
            ),
          ],
        ),
        child: Text(
          label,
          style: const TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w700,
            color: Color(0xFF54656F),
            letterSpacing: 0.2,
          ),
        ),
      ),
    );
  }

  Widget _buildMessageBubble(Map<String, dynamic> msg, bool isMine) {
    final text = (msg['message'] ?? '').toString();
    final createdAt = (msg['created_at'] ?? '').toString();
    final timeStr = createdAt.length >= 16 ? createdAt.substring(11, 16) : '';
    final isRead = _parseInt(msg['is_read']) == 1;
    final isImg = ChatMediaHelpers.isImageMessage(text);

    return Align(
      alignment: isMine ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(bottom: 6),
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.78,
        ),
        decoration: BoxDecoration(
          color: isMine ? const Color(0xFFE7FFDB) : Colors.white,
          borderRadius: BorderRadius.only(
            topLeft: const Radius.circular(12),
            topRight: Radius.circular(isMine ? 2 : 12),
            bottomLeft: Radius.circular(isMine ? 12 : 2),
            bottomRight: const Radius.circular(12),
          ),
          boxShadow: const [
            BoxShadow(
              color: Color(0x14000000),
              blurRadius: 2,
              offset: Offset(0, 1),
            ),
          ],
        ),
        padding: const EdgeInsets.fromLTRB(10, 8, 10, 6),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            if (isImg) ...[
              Builder(builder: (ctx) {
                final imgUrl = ChatMediaHelpers.extractImageUrl(text);
                final caption = ChatMediaHelpers.extractCaption(text);
                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: GestureDetector(
                        onTap: () => ChatMediaHelpers.showImagePreview(context, imgUrl),
                        child: Image.network(
                          imgUrl,
                          width: double.infinity,
                          height: 180,
                          fit: BoxFit.cover,
                          loadingBuilder: (context, child, progress) {
                            if (progress == null) return child;
                            return Container(
                              height: 180,
                              color: Colors.black12,
                              child: const Center(
                                child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF00A884)),
                              ),
                            );
                          },
                          errorBuilder: (context, error, stackTrace) => Container(
                            height: 120,
                            color: Colors.black12,
                            alignment: Alignment.center,
                            child: const Icon(Icons.broken_image_rounded, color: Colors.grey, size: 36),
                          ),
                        ),
                      ),
                    ),
                    if (caption.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Text(caption, style: const TextStyle(fontSize: 14)),
                    ],
                  ],
                );
              }),
            ] else ...[
              Align(
                alignment: Alignment.centerLeft,
                child: Text(
                  text,
                  style: const TextStyle(
                    fontSize: 14.5,
                    color: Color(0xFF111B21),
                    height: 1.35,
                  ),
                ),
              ),
            ],
            const SizedBox(height: 2),
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  timeStr,
                  style: const TextStyle(
                    fontSize: 10.5,
                    color: Color(0xFF667781),
                    fontWeight: FontWeight.w500,
                  ),
                ),
                if (isMine) ...[
                  const SizedBox(width: 3),
                  Icon(
                    isRead ? Icons.done_all_rounded : Icons.done_rounded,
                    size: 15,
                    color: isRead ? const Color(0xFF53BDEB) : const Color(0xFF8696A0),
                  ),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickChip(String text) {
    return ActionChip(
      label: Text(
        text,
        style: const TextStyle(
          fontSize: 11.5,
          fontWeight: FontWeight.w600,
          color: Color(0xFF334155),
        ),
      ),
      backgroundColor: Colors.white,
      side: const BorderSide(color: Color(0xFFCBD5E1), width: 0.8),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      elevation: 0.5,
      shadowColor: Colors.black.withValues(alpha: 0.08),
      onPressed: () => _sendMessage(text),
    );
  }
}
