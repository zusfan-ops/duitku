import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../theme.dart';

class TvChatSheet extends StatefulWidget {
  const TvChatSheet({super.key});

  static Future<void> show(BuildContext context) {
    return showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => const TvChatSheet(),
    );
  }

  @override
  State<TvChatSheet> createState() => _TvChatSheetState();
}

class _TvChatSheetState extends State<TvChatSheet> {
  final List<Map<String, dynamic>> _messages = [];
  final TextEditingController _textController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  Timer? _pollTimer;
  int _lastId = 0;
  bool _isLoading = true;
  bool _isSending = false;

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
    _textController.dispose();
    _scrollController.dispose();
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
      final res = await ApiService.instance.getTvChats(afterId: _lastId);
      if (!mounted) return;

      final list = (res['messages'] as List<dynamic>?) ?? [];
      if (list.isNotEmpty) {
        setState(() {
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
          _isLoading = false;
        });

        // Scroll to bottom smoothly
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (_scrollController.hasClients) {
            _scrollController.animateTo(
              _scrollController.position.maxScrollExtent,
              duration: const Duration(milliseconds: 250),
              curve: Curves.easeOut,
            );
          }
        });
      } else {
        if (!isPolling && mounted) {
          setState(() => _isLoading = false);
        }
      }
    } catch (_) {
      if (!isPolling && mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _sendMessage(String text) async {
    final cleanText = text.trim();
    if (cleanText.isEmpty || _isSending) return;

    _textController.clear();
    setState(() {
      _isSending = true;
    });

    try {
      final res = await ApiService.instance.sendTvChat(cleanText);
      final chatData = res['chat'] as Map<String, dynamic>?;
      if (chatData != null && mounted) {
        final id = _parseInt(chatData['id']);
        if (id > _lastId) {
          _lastId = id;
        }
        setState(() {
          final exists = _messages.any((x) => _parseInt(x['id']) == id);
          if (!exists) {
            _messages.add(chatData);
          }
        });

        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (_scrollController.hasClients) {
            _scrollController.animateTo(
              _scrollController.position.maxScrollExtent,
              duration: const Duration(milliseconds: 200),
              curve: Curves.easeOut,
            );
          }
        });
      }
      await _fetchMessages(isPolling: true);
    } catch (_) {
    } finally {
      if (mounted) {
        setState(() => _isSending = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottomInset = MediaQuery.of(context).viewInsets.bottom;
    final auth = context.watch<AuthProvider>();
    final curUserId = auth.user?.id ?? 0;

    return Container(
      height: MediaQuery.of(context).size.height * 0.7 + bottomInset,
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.3),
            blurRadius: 20,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: Column(
          children: [
            // Top Drag Handle
            Container(
              margin: const EdgeInsets.only(top: 10, bottom: 6),
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: AppColors.border,
                borderRadius: BorderRadius.circular(2),
              ),
            ),

            // Header
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      const Text('💬', style: TextStyle(fontSize: 18)),
                      const SizedBox(width: 8),
                      const Text(
                        'Live Chat TV',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w900,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.red.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.circle, color: Colors.red, size: 7),
                            SizedBox(width: 4),
                            Text(
                              'LIVE ROOM',
                              style: TextStyle(
                                fontSize: 9.5,
                                fontWeight: FontWeight.w900,
                                color: Colors.red,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  IconButton(
                    icon: const Icon(Icons.close_rounded, size: 22),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
            ),
            const Divider(height: 1, color: AppColors.border),

            // Message Stream
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _messages.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Text('📺', style: TextStyle(fontSize: 36)),
                              const SizedBox(height: 8),
                              const Text(
                                'Belum ada obrolan siaran.',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w700,
                                  color: AppColors.textSecondary,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Kirim pesan pertama untuk memulai interaksi!',
                                style: TextStyle(
                                  fontSize: 11.5,
                                  color: Colors.grey.shade500,
                                ),
                              ),
                            ],
                          ),
                        )
                      : ListView.builder(
                          controller: _scrollController,
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                          itemCount: _messages.length,
                          itemBuilder: (context, idx) {
                            final msg = _messages[idx];
                            final msgUserId = _parseInt(msg['user_id']);
                            final isMine = curUserId > 0 && msgUserId == curUserId;
                            final userName = (msg['user_name'] as String?) ?? 'Pengguna';
                            final text = (msg['message'] as String?) ?? '';
                            final createdAt = msg['created_at'] as String?;
                            final timeStr = createdAt != null && createdAt.length >= 16
                                ? createdAt.substring(11, 16)
                                : '';

                            return Align(
                              alignment: isMine ? Alignment.centerRight : Alignment.centerLeft,
                              child: Container(
                                margin: const EdgeInsets.only(bottom: 8),
                                constraints: BoxConstraints(
                                  maxWidth: MediaQuery.of(context).size.width * 0.78,
                                ),
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                decoration: BoxDecoration(
                                  color: isMine
                                      ? AppColors.primary.withValues(alpha: 0.14)
                                      : AppColors.bg,
                                  borderRadius: BorderRadius.only(
                                    topLeft: const Radius.circular(14),
                                    topRight: const Radius.circular(14),
                                    bottomLeft: Radius.circular(isMine ? 14 : 2),
                                    bottomRight: Radius.circular(isMine ? 2 : 14),
                                  ),
                                  border: Border.all(
                                    color: isMine
                                        ? AppColors.primary.withValues(alpha: 0.3)
                                        : AppColors.borderLight,
                                  ),
                                ),
                                child: Column(
                                  crossAxisAlignment: isMine
                                      ? CrossAxisAlignment.end
                                      : CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Text(
                                          isMine ? 'Anda' : userName,
                                          style: TextStyle(
                                            fontSize: 10.5,
                                            fontWeight: FontWeight.w800,
                                            color: isMine
                                                ? AppColors.primary
                                                : const Color(0xFF0284C7),
                                          ),
                                        ),
                                        if (timeStr.isNotEmpty) ...[
                                          const SizedBox(width: 6),
                                          Text(
                                            timeStr,
                                            style: TextStyle(
                                              fontSize: 9,
                                              color: Colors.grey.shade500,
                                            ),
                                          ),
                                        ],
                                      ],
                                    ),
                                    const SizedBox(height: 3),
                                    Text(
                                      text,
                                      style: const TextStyle(
                                        fontSize: 13,
                                        color: AppColors.textPrimary,
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
            ),

            // Quick Emojis Row
            Container(
              height: 38,
              padding: const EdgeInsets.symmetric(horizontal: 12),
              color: AppColors.bg,
              child: ListView(
                scrollDirection: Axis.horizontal,
                children: ['🔥', '👏', '⚽', '🤣', '👍', '❤️', '🎉', '⭐'].map((emoji) {
                  return Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 4),
                    child: InkWell(
                      onTap: () => _sendMessage(emoji),
                      borderRadius: BorderRadius.circular(8),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: AppColors.card,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Center(
                          child: Text(emoji, style: const TextStyle(fontSize: 14)),
                        ),
                      ),
                    ),
                  );
                }).toList(),
              ),
            ),

            // Input Area
            Container(
              padding: EdgeInsets.fromLTRB(12, 8, 12, bottomInset > 0 ? 8 : 12),
              decoration: const BoxDecoration(
                color: AppColors.card,
                border: Border(top: BorderSide(color: AppColors.border)),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _textController,
                      textInputAction: TextInputAction.send,
                      onSubmitted: _sendMessage,
                      decoration: InputDecoration(
                        hintText: 'Tulis komentar live TV...',
                        hintStyle: TextStyle(fontSize: 12.5, color: Colors.grey.shade500),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        filled: true,
                        fillColor: AppColors.bg,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: const BorderSide(color: AppColors.border),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: const BorderSide(color: AppColors.border),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  InkWell(
                    onTap: () => _sendMessage(_textController.text),
                    borderRadius: BorderRadius.circular(14),
                    child: Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFF10B981), Color(0xFF059669)],
                        ),
                        borderRadius: BorderRadius.circular(14),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFF10B981).withValues(alpha: 0.35),
                            blurRadius: 8,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: const Icon(
                        Icons.send_rounded,
                        color: Colors.white,
                        size: 20,
                      ),
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
