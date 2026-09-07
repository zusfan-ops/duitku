import 'dart:async';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../../config/api_config.dart';
import '../../services/api_service.dart';
import 'chat_media_helpers.dart';

class DirectChatScreen extends StatefulWidget {
  final int friendId;
  final String friendName;
  final String friendUsername;
  final String? friendAvatar;

  const DirectChatScreen({
    super.key,
    required this.friendId,
    required this.friendName,
    required this.friendUsername,
    this.friendAvatar,
  });

  @override
  State<DirectChatScreen> createState() => _DirectChatScreenState();
}

class _DirectChatScreenState extends State<DirectChatScreen> {
  final _messageController = TextEditingController();
  final _scrollController = ScrollController();

  bool _isLoading = true;
  bool _isSending = false;
  String? _errorMessage;
  List<Map<String, dynamic>> _messages = [];
  int _myId = 0;
  Timer? _pollingTimer;

  bool _isPinned = false;
  bool _isArchived = false;

  @override
  void initState() {
    super.initState();
    _loadMessages();
    _startPolling();
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _startPolling() {
    _pollingTimer?.cancel();
    _pollingTimer = Timer.periodic(const Duration(seconds: 3), (_) {
      if (mounted) {
        _loadMessages(isSilent: true);
      }
    });
  }

  Future<void> _loadMessages({bool isSilent = false}) async {
    try {
      final res = await ApiService.instance.getDirectMessages(widget.friendId);
      final list = (res['messages'] as List<dynamic>?) ?? [];
      final myId = int.tryParse('${res['my_id']}') ?? 0;

      if (mounted) {
        final parsed = list.map((e) => Map<String, dynamic>.from(e as Map)).toList();
        final hadNewMessages = parsed.length > _messages.length;

        setState(() {
          _messages = parsed;
          _myId = myId;
          _isLoading = false;
          _errorMessage = null;
        });

        if (hadNewMessages || !isSilent) {
          _scrollToBottom();
        }
      }
    } catch (e) {
      if (mounted && !isSilent) {
        setState(() {
          _isLoading = false;
          _errorMessage = 'Gagal memuat pesan: $e';
        });
      }
    }
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 250),
          curve: Curves.easeOut,
        );
      }
    });
  }

  Future<void> _sendMessage() async {
    final text = _messageController.text.trim();
    if (text.isEmpty || _isSending) return;

    setState(() => _isSending = true);
    _messageController.clear();

    try {
      final res = await ApiService.instance.sendDirectMessage(widget.friendId, text);
      if (res['status'] == 'success' || res['success'] == true) {
        await _loadMessages(isSilent: true);
        _scrollToBottom();
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(res['message'] ?? 'Gagal mengirim pesan.')),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error mengirim: $e')),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isSending = false);
      }
    }
  }

  void _onSelectEmoji(String emoji) {
    final text = _messageController.text;
    final selection = _messageController.selection;
    final newText = selection.start >= 0
        ? text.replaceRange(selection.start, selection.end, emoji)
        : text + emoji;
    _messageController.value = TextEditingValue(
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
      if (b64 == null) {
        throw Exception('Gagal membaca berkas gambar.');
      }

      final uploadRes = await ApiService.instance.uploadChatImage(b64);
      final url = uploadRes['url'] as String?;
      if (url == null || url.isEmpty) {
        throw Exception(uploadRes['message'] ?? 'Gagal mengunggah foto.');
      }

      final msgPayload = '[img:$url]';
      await ApiService.instance.sendDirectMessage(widget.friendId, msgPayload);
      await _loadMessages(isSilent: true);
      _scrollToBottom();
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
    try {
      final res = await ApiService.instance.pinConversation(
        type: 'direct',
        targetId: widget.friendId,
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
    try {
      final res = await ApiService.instance.archiveConversation(
        type: 'direct',
        targetId: widget.friendId,
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
        content: Text('Semua riwayat obrolan dengan "${widget.friendName}" akan dihapus permanen.'),
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

    try {
      final res = await ApiService.instance.deleteConversation(
        type: 'direct',
        targetId: widget.friendId,
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

  Future<void> _blockUser() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Blokir Pengguna?'),
        content: Text('"${widget.friendName}" tidak akan bisa mengirim pesan kepada kamu lagi. Pemblokiran ini bersifat dua arah.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Blokir', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    try {
      final res = await ApiService.instance.blockUser(widget.friendId);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Pengguna berhasil diblokir')),
        );
        Navigator.pop(context, true);
      }
    } on ApiException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
      }
    }
  }

  Future<void> _showReportDialog() async {
    final reasonCtrl = TextEditingController();
    String selectedReason = 'spam';
    const reasons = [
      ('spam', 'Spam / Pesan Massal', Icons.campaign_outlined),
      ('scam', 'Penipuan / Scam', Icons.gpp_bad_outlined),
      ('inappropriate', 'Konten Tidak Pantas', Icons.visibility_off_outlined),
      ('harassment', 'Pelecehan / Ancaman', Icons.report_gmailerrorred_rounded),
      ('fake', 'Akun Palsu', Icons.person_off_outlined),
      ('illegal', 'Barang/Konten Ilegal', Icons.gavel_rounded),
      ('other', 'Lainnya', Icons.more_horiz),
    ];

    final submitted = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Text(
            'Laporkan Pengguna',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
          ),
          content: SizedBox(
            width: double.maxFinite,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Laporkan "${widget.friendName}" kepada tim moderasi DuitKu.',
                  style: const TextStyle(fontSize: 12.5, color: Colors.grey),
                ),
                const SizedBox(height: 14),
                const Text('ALASAN PELAPORAN', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Colors.grey)),
                const SizedBox(height: 8),
                ...reasons.map((r) => RadioListTile<String>(
                  dense: true,
                  visualDensity: VisualDensity.compact,
                  contentPadding: EdgeInsets.zero,
                  title: Row(
                    children: [
                      Icon(r.$3, size: 18, color: const Color(0xFFDC2626)),
                      const SizedBox(width: 8),
                      Text(r.$2, style: const TextStyle(fontSize: 13)),
                    ],
                  ),
                  value: r.$1,
                  groupValue: selectedReason,
                  onChanged: (v) => setDialogState(() => selectedReason = v ?? 'spam'),
                )),
                const SizedBox(height: 12),
                TextField(
                  controller: reasonCtrl,
                  maxLines: 3,
                  maxLength: 300,
                  decoration: const InputDecoration(
                    labelText: 'DETAIL TAMBAHAN (OPSIONAL)',
                    hintText: 'Jelaskan kronologi atau bukti pendukung...',
                    border: OutlineInputBorder(),
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Batal'),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFDC2626)),
              onPressed: () => Navigator.pop(ctx, true),
              child: const Text('Kirim Laporan', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
            ),
          ],
        ),
      ),
    );

    if (submitted != true) return;

    try {
      await ApiService.instance.reportContent(
        targetType: 'user',
        targetId: widget.friendId,
        reason: selectedReason,
        description: reasonCtrl.text.trim(),
      );
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Laporan terkirim. Tim moderasi akan meninjaunya dalam 1×24 jam. Terima kasih!')),
        );
      }
    } on ApiException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
      }
    }
  }

  String _formatTime(String? dateStr) {
    if (dateStr == null || dateStr.length < 16) return '';
    try {
      return dateStr.substring(11, 16);
    } catch (_) {
      return '';
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0B141A) : const Color(0xFFEFEAE2),
      appBar: AppBar(
        elevation: 1,
        backgroundColor: isDark ? const Color(0xFF1F2C34) : const Color(0xFF075E54),
        iconTheme: const IconThemeData(color: Colors.white),
        titleSpacing: 0,
        title: Row(
          children: [
            ClipOval(
              child: (widget.friendAvatar != null &&
                      widget.friendAvatar!.isNotEmpty &&
                      (widget.friendAvatar!.startsWith('http') ||
                       widget.friendAvatar!.contains('/') ||
                       widget.friendAvatar!.endsWith('.jpg') ||
                       widget.friendAvatar!.endsWith('.png') ||
                       widget.friendAvatar!.endsWith('.webp')))
                  ? Image.network(
                      widget.friendAvatar!.startsWith('http')
                          ? widget.friendAvatar!
                          : '${ApiConfig.baseUrl}${widget.friendAvatar!.startsWith('/') ? widget.friendAvatar! : '/${widget.friendAvatar!}'}',
                      width: 38,
                      height: 38,
                      fit: BoxFit.cover,
                      errorBuilder: (_, _, _) => CircleAvatar(
                        radius: 19,
                        backgroundColor: isDark ? const Color(0xFF2A3942) : const Color(0xFF128C7E),
                        child: Text(
                          widget.friendName.isNotEmpty ? widget.friendName[0].toUpperCase() : 'T',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 16),
                        ),
                      ),
                    )
                  : CircleAvatar(
                      radius: 19,
                      backgroundColor: isDark ? const Color(0xFF2A3942) : const Color(0xFF128C7E),
                      child: Text(
                        widget.friendName.isNotEmpty ? widget.friendName[0].toUpperCase() : 'T',
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 16),
                      ),
                    ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    widget.friendName,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      color: Colors.white,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  Text(
                    widget.friendUsername.isNotEmpty ? '@${widget.friendUsername}' : 'Online',
                    style: TextStyle(
                      fontSize: 11,
                      color: Colors.white.withValues(alpha: 0.82),
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          PopupMenuButton<String>(
            icon: const Icon(Icons.more_vert_rounded, color: Colors.white),
            tooltip: 'Opsi Obrolan',
            onSelected: (val) {
              switch (val) {
                case 'pin':
                  _togglePin();
                  break;
                case 'archive':
                  _toggleArchive();
                  break;
                case 'block':
                  _blockUser();
                  break;
                case 'report':
                  _showReportDialog();
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
                value: 'report',
                child: Row(
                  children: [
                    Icon(Icons.flag_outlined, size: 20, color: Colors.orange),
                    SizedBox(width: 12),
                    Text('Laporkan Pengguna', style: TextStyle(color: Colors.orange)),
                  ],
                ),
              ),
              PopupMenuItem(
                value: 'block',
                child: Row(
                  children: [
                    const Icon(Icons.block, size: 20, color: Colors.red),
                    const SizedBox(width: 12),
                    Text('Blokir ${widget.friendName}', style: const TextStyle(color: Colors.red)),
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
      body: SafeArea(
        child: Column(
          children: [
            // Chat Messages Feed
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator(color: Color(0xFF00A884)))
                  : _errorMessage != null
                      ? Center(
                          child: Padding(
                            padding: const EdgeInsets.all(20),
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(_errorMessage!, textAlign: TextAlign.center),
                                const SizedBox(height: 12),
                                ElevatedButton(
                                  onPressed: () => _loadMessages(),
                                  style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF00A884)),
                                  child: const Text('Coba Lagi', style: TextStyle(color: Colors.white)),
                                ),
                              ],
                            ),
                          ),
                        )
                      : _messages.isEmpty
                          ? Center(
                              child: Padding(
                                padding: const EdgeInsets.all(24),
                                child: Column(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(
                                      Icons.chat_bubble_outline_rounded,
                                      size: 52,
                                      color: isDark ? Colors.white24 : Colors.black26,
                                    ),
                                    const SizedBox(height: 12),
                                    Text(
                                      'Mulai percakapan langsung dengan ${widget.friendName}!',
                                      textAlign: TextAlign.center,
                                      style: TextStyle(
                                        color: isDark ? Colors.white54 : Colors.black45,
                                        fontSize: 13,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            )
                          : ListView.builder(
                              controller: _scrollController,
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
                              itemCount: _messages.length,
                              itemBuilder: (context, index) {
                                final msg = _messages[index];
                                final isMe = int.tryParse('${msg['sender_id']}') == _myId;
                                final isRead = (int.tryParse('${msg['is_read']}') ?? 0) == 1;
                                final time = _formatTime(msg['created_at']);
                                final rawText = (msg['message'] ?? '').toString();
                                final isImg = ChatMediaHelpers.isImageMessage(rawText);

                                return Align(
                                  alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
                                  child: Container(
                                    margin: const EdgeInsets.only(bottom: 6),
                                    constraints: BoxConstraints(
                                      maxWidth: MediaQuery.of(context).size.width * 0.78,
                                    ),
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                                    decoration: BoxDecoration(
                                      color: isMe
                                          ? (isDark ? const Color(0xFF005C4B) : const Color(0xFFD9FDD3))
                                          : (isDark ? const Color(0xFF202C33) : Colors.white),
                                      borderRadius: BorderRadius.only(
                                        topLeft: const Radius.circular(14),
                                        topRight: const Radius.circular(14),
                                        bottomLeft: Radius.circular(isMe ? 14 : 2),
                                        bottomRight: Radius.circular(isMe ? 2 : 14),
                                      ),
                                      boxShadow: [
                                        BoxShadow(
                                          color: Colors.black.withValues(alpha: 0.08),
                                          blurRadius: 4,
                                          offset: const Offset(0, 1),
                                        ),
                                      ],
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.end,
                                      children: [
                                        if (isImg) ...[
                                          _buildImageContent(rawText),
                                        ] else ...[
                                          Align(
                                            alignment: Alignment.centerLeft,
                                            child: Text(
                                              rawText,
                                              style: TextStyle(
                                                fontSize: 14.5,
                                                color: isMe
                                                    ? (isDark ? Colors.white : const Color(0xFF111B21))
                                                    : (isDark ? const Color(0xFFE9EDEF) : const Color(0xFF111B21)),
                                              ),
                                            ),
                                          ),
                                        ],
                                        const SizedBox(height: 3),
                                        Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            Text(
                                              time,
                                              style: TextStyle(
                                                fontSize: 10,
                                                color: isDark ? Colors.white54 : Colors.black45,
                                              ),
                                            ),
                                            if (isMe) ...[
                                              const SizedBox(width: 4),
                                              Icon(
                                                Icons.done_all_rounded,
                                                size: 14,
                                                color: isRead ? const Color(0xFF53BDEB) : const Color(0xFF8696A0),
                                              ),
                                            ],
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                );
                              },
                            ),
            ),

            // Input Bar at bottom (WhatsApp Pill Style with Emoji, Attachment, Camera)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
              color: isDark ? const Color(0xFF202C33) : const Color(0xFFF0F2F5),
              child: Row(
                children: [
                  Expanded(
                    child: Container(
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0xFF2A3942) : Colors.white,
                        borderRadius: BorderRadius.circular(26),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.04),
                            blurRadius: 3,
                            offset: const Offset(0, 1),
                          ),
                        ],
                      ),
                      child: Row(
                        children: [
                          IconButton(
                            icon: const Icon(Icons.sentiment_satisfied_alt_outlined, size: 23),
                            color: isDark ? const Color(0xFF8696A0) : const Color(0xFF54656F),
                            tooltip: 'Pilih Emoji',
                            onPressed: () => ChatMediaHelpers.showEmojiPicker(
                              context,
                              onEmojiSelected: _onSelectEmoji,
                            ),
                            padding: const EdgeInsets.all(8),
                            constraints: const BoxConstraints(),
                          ),
                          Expanded(
                            child: TextField(
                              controller: _messageController,
                              textCapitalization: TextCapitalization.sentences,
                              maxLines: 5,
                              minLines: 1,
                              decoration: const InputDecoration(
                                hintText: 'Ketik pesan...',
                                hintStyle: TextStyle(fontSize: 14.5, color: Colors.grey),
                                border: InputBorder.none,
                                isDense: true,
                                contentPadding: EdgeInsets.symmetric(vertical: 10, horizontal: 4),
                              ),
                              onSubmitted: (_) => _sendMessage(),
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.attach_file_rounded, size: 22),
                            color: isDark ? const Color(0xFF8696A0) : const Color(0xFF54656F),
                            tooltip: 'Lampirkan Berkas',
                            onPressed: () => ChatMediaHelpers.showAttachmentPicker(
                              context,
                              onCamera: () => _pickAndSendPhoto(ImageSource.camera),
                              onGallery: () => _pickAndSendPhoto(ImageSource.gallery),
                            ),
                            padding: const EdgeInsets.all(6),
                            constraints: const BoxConstraints(),
                          ),
                          IconButton(
                            icon: const Icon(Icons.camera_alt_rounded, size: 22),
                            color: isDark ? const Color(0xFF8696A0) : const Color(0xFF54656F),
                            tooltip: 'Ambil Foto',
                            onPressed: () => _pickAndSendPhoto(ImageSource.camera),
                            padding: const EdgeInsets.all(6),
                            constraints: const BoxConstraints(),
                          ),
                          const SizedBox(width: 6),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 6),
                  GestureDetector(
                    onTap: _isSending ? null : _sendMessage,
                    child: Container(
                      width: 46,
                      height: 46,
                      decoration: const BoxDecoration(
                        color: Color(0xFF00A884),
                        shape: BoxShape.circle,
                      ),
                      child: _isSending
                          ? const Center(
                              child: SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                              ),
                            )
                          : const Icon(Icons.send_rounded, color: Colors.white, size: 21),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildImageContent(String rawText) {
    final imgUrl = ChatMediaHelpers.extractImageUrl(rawText);
    final caption = ChatMediaHelpers.extractCaption(rawText);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(10),
          child: GestureDetector(
            onTap: () => ChatMediaHelpers.showImagePreview(context, imgUrl),
            child: Image.network(
              imgUrl,
              width: double.infinity,
              height: 200,
              fit: BoxFit.cover,
              loadingBuilder: (context, child, progress) {
                if (progress == null) return child;
                return Container(
                  height: 200,
                  color: Colors.black12,
                  child: const Center(child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF00A884))),
                );
              },
              errorBuilder: (context, error, stackTrace) => Container(
                height: 120,
                color: Colors.black12,
                alignment: Alignment.center,
                child: const Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.broken_image_rounded, color: Colors.grey, size: 36),
                    SizedBox(height: 4),
                    Text('Gagal memuat gambar', style: TextStyle(fontSize: 11, color: Colors.grey)),
                  ],
                ),
              ),
            ),
          ),
        ),
        if (caption.isNotEmpty) ...[
          const SizedBox(height: 6),
          Text(caption, style: const TextStyle(fontSize: 14)),
        ],
      ],
    );
  }
}
