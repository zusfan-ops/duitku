import 'dart:async';
import 'package:flutter/material.dart';

import '../../services/api_service.dart';
import '../../theme.dart';

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
            CircleAvatar(
              radius: 19,
              backgroundColor: isDark ? const Color(0xFF2A3942) : const Color(0xFF128C7E),
              child: Text(
                widget.friendName.isNotEmpty ? widget.friendName[0].toUpperCase() : 'T',
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 16),
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
                              child: Container(
                                margin: const EdgeInsets.all(24),
                                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                decoration: BoxDecoration(
                                  color: isDark ? const Color(0xFF1F2C34) : Colors.white,
                                  borderRadius: BorderRadius.circular(16),
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withValues(alpha: 0.06),
                                      blurRadius: 10,
                                      offset: const Offset(0, 2),
                                    ),
                                  ],
                                ),
                                child: Column(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Text('👋', style: TextStyle(fontSize: 32)),
                                    const SizedBox(height: 8),
                                    Text(
                                      'Mulai percakapan dengan ${widget.friendName}!',
                                      textAlign: TextAlign.center,
                                      style: TextStyle(
                                        fontSize: 13,
                                        fontWeight: FontWeight.w600,
                                        color: isDark ? Colors.white70 : AppColors.textSecondary,
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

                                return Align(
                                  alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
                                  child: Container(
                                    margin: const EdgeInsets.only(bottom: 6),
                                    constraints: BoxConstraints(
                                      maxWidth: MediaQuery.of(context).size.width * 0.78,
                                    ),
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
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
                                        Align(
                                          alignment: Alignment.centerLeft,
                                          child: Text(
                                            msg['message'] ?? '',
                                            style: TextStyle(
                                              fontSize: 14,
                                              color: isMe
                                                  ? (isDark ? Colors.white : const Color(0xFF111B21))
                                                  : (isDark ? const Color(0xFFE9EDEF) : const Color(0xFF111B21)),
                                            ),
                                          ),
                                        ),
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

            // Input Bar at bottom (WhatsApp Pill Style)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
              color: isDark ? const Color(0xFF202C33) : const Color(0xFFF0F2F5),
              child: Row(
                children: [
                  Expanded(
                    child: Container(
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0xFF2A3942) : Colors.white,
                        borderRadius: BorderRadius.circular(24),
                      ),
                      child: Row(
                        children: [
                          const SizedBox(width: 14),
                          Expanded(
                            child: TextField(
                              controller: _messageController,
                              textCapitalization: TextCapitalization.sentences,
                              decoration: const InputDecoration(
                                hintText: 'Ketik pesan...',
                                hintStyle: TextStyle(fontSize: 14, color: Colors.grey),
                                border: InputBorder.none,
                                isDense: true,
                                contentPadding: EdgeInsets.symmetric(vertical: 10),
                              ),
                              onSubmitted: (_) => _sendMessage(),
                            ),
                          ),
                          const SizedBox(width: 8),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  GestureDetector(
                    onTap: _isSending ? null : _sendMessage,
                    child: Container(
                      width: 44,
                      height: 44,
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
                          : const Icon(Icons.send_rounded, color: Colors.white, size: 20),
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
}
