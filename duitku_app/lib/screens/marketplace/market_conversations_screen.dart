import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../config/api_config.dart';
import '../../models/friend.dart';
import '../../providers/app_data_provider.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import 'package:image_picker/image_picker.dart';
import '../chat/direct_chat_screen.dart';
import 'market_chat_screen.dart';

class MarketConversationsScreen extends StatefulWidget {
  final bool isRootTab;
  final int? initialStatusId;
  final int? initialDirectUserId;
  final String? initialTab;

  const MarketConversationsScreen({
    super.key,
    this.isRootTab = false,
    this.initialStatusId,
    this.initialDirectUserId,
    this.initialTab,
  });

  @override
  State<MarketConversationsScreen> createState() => _MarketConversationsScreenState();
}

class _MarketConversationsScreenState extends State<MarketConversationsScreen> {
  bool _isLoading = true;
  String? _errorMessage;
  List<Map<String, dynamic>> _conversations = [];
  List<FriendRequest> _incomingRequests = [];
  List<Friend> _friends = [];
  List<Map<String, dynamic>> _statuses = [];
  int _myId = 0;
  int _archivedCount = 0;
  Timer? _refreshTimer;
  String _activeFilter = 'all'; // 'all', 'direct', 'marketplace', 'archived'
  bool _hasHandledInitialStatus = false;
  bool _hasHandledInitialDirectUser = false;

  @override
  void initState() {
    super.initState();
    if (widget.initialTab != null) {
      if (widget.initialTab == 'friends' || widget.initialTab == 'direct') {
        _activeFilter = 'direct';
      } else if (widget.initialTab == 'marketplace') {
        _activeFilter = 'marketplace';
      }
    }
    _loadData();
    _startAutoRefresh();
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    super.dispose();
  }

  void _startAutoRefresh() {
    _refreshTimer?.cancel();
    _refreshTimer = Timer.periodic(const Duration(seconds: 4), (_) {
      if (mounted) {
        _loadData(isSilent: true);
      }
    });
  }

  Future<void> _loadData({bool isSilent = false}) async {
    if (!isSilent) {
      setState(() {
        _isLoading = true;
        _errorMessage = null;
      });
    }

    try {
      // 1. Ambil seluruh percakapan gabungan (Direct Friends + Marketplace)
      final res = await ApiService.instance.getAllConversations();
      _myId = int.tryParse('${res['my_id']}') ?? 0;
      final list = (res['conversations'] as List<dynamic>?) ?? [];

      // 2. Ambil permintaan pertemanan & daftar teman
      final reqRes = await ApiService.instance.getFriendRequests();
      final incList = (reqRes['incoming'] as List<dynamic>?) ?? [];
      final parsedReqs = incList.map((e) => FriendRequest.fromJson(Map<String, dynamic>.from(e as Map))).toList();

      final friendsList = await ApiService.instance.getFriends();
      final parsedFriends = friendsList.map((e) => Friend.fromJson(Map<String, dynamic>.from(e as Map))).toList();

      // 3. Ambil feed status teman
      List<Map<String, dynamic>> parsedStatuses = [];
      try {
        final statusRes = await ApiService.instance.getStatusFeed();
        final sList = (statusRes['statuses'] as List<dynamic>?) ?? [];
        parsedStatuses = sList.map((e) => Map<String, dynamic>.from(e as Map)).toList();
      } catch (_) {}

      if (mounted) {
        final totalUnread = int.tryParse('${res['total_unread']}') ?? 0;
        final archived = int.tryParse('${res['archived_count']}') ?? 0;
        context.read<AppDataProvider>().setMarketChatUnread(totalUnread);

        final rawList = list.map((e) => Map<String, dynamic>.from(e as Map)).toList();
        final Set<String> seenKeys = {};
        final List<Map<String, dynamic>> uniqueConvs = [];
        for (final item in rawList) {
          final type = item['type']?.toString() ?? 'direct';
          final targetId = item['target_id']?.toString() ?? (type == 'direct' ? item['partner_id']?.toString() : item['listing_id']?.toString()) ?? '0';
          final subId = item['target_sub_id']?.toString() ?? (type == 'direct' ? '0' : item['buyer_id']?.toString()) ?? '0';
          final key = '${type}_${targetId}_$subId';
          if (!seenKeys.contains(key)) {
            seenKeys.add(key);
            uniqueConvs.add(item);
          }
        }

        setState(() {
          _conversations = uniqueConvs;
          _incomingRequests = parsedReqs;
          _friends = parsedFriends;
          _statuses = parsedStatuses;
          _archivedCount = archived;
          _isLoading = false;
        });

        // ── Auto-Open Target dari Notifikasi (Status / Direct Chat) ──
        if (widget.initialStatusId != null && !_hasHandledInitialStatus) {
          _hasHandledInitialStatus = true;
          final targetIdx = _statuses.indexWhere((s) => int.tryParse('${s['id']}') == widget.initialStatusId);
          WidgetsBinding.instance.addPostFrameCallback((_) {
            if (mounted) {
              if (targetIdx >= 0) {
                _openStatusViewer(_statuses, targetIdx, autoOpenComments: true);
              } else {
                _showInstagramCommentsSheet(
                  parentCtx: context,
                  statusId: widget.initialStatusId!,
                  authorName: 'Status',
                  authorAvatar: '',
                  caption: '',
                  timeStr: '',
                  onCountUpdated: (_) {},
                );
              }
            }
          });
        }

        if (widget.initialDirectUserId != null && !_hasHandledInitialDirectUser) {
          _hasHandledInitialDirectUser = true;
          final friend = _friends.firstWhere(
            (f) => f.friendId == widget.initialDirectUserId,
            orElse: () => Friend(friendId: widget.initialDirectUserId!, name: 'Teman', username: ''),
          );
          WidgetsBinding.instance.addPostFrameCallback((_) {
            if (mounted) {
              Navigator.of(context, rootNavigator: true).push(
                MaterialPageRoute(
                  builder: (_) => DirectChatScreen(
                    friendId: friend.friendId,
                    friendName: friend.name,
                    friendUsername: friend.username,
                    friendAvatar: friend.avatarImage,
                  ),
                ),
              );
            }
          });
        }
      }
    } catch (e) {
      if (mounted && !isSilent) {
        setState(() {
          _isLoading = false;
          _errorMessage = 'Gagal memuat obrolan: $e';
        });
      }
    }
  }

  Future<void> _respondFriendRequest(int requestId, String action) async {
    try {
      final res = await ApiService.instance.respondFriendRequest(requestId, action);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Permintaan diproses.'),
            backgroundColor: action == 'accept' ? const Color(0xFF10B981) : Colors.black87,
          ),
        );
        _loadData(isSilent: true);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    }
  }

  void _showAddFriendDialog(BuildContext context) {
    final searchCtrl = TextEditingController();
    bool searching = false;
    List<UserSearchResult> searchResults = [];
    String? searchMsg;

    showModalBottomSheet(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).cardColor,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (ctx, setModalState) {
            void doSearch() async {
              final q = searchCtrl.text.trim();
              if (q.isEmpty) return;
              setModalState(() {
                searching = true;
                searchMsg = null;
              });

              try {
                final list = await ApiService.instance.searchUsers(q);
                final parsed = list.map((e) => UserSearchResult.fromJson(Map<String, dynamic>.from(e as Map))).toList();
                setModalState(() {
                  searchResults = parsed;
                  searching = false;
                  if (parsed.isEmpty) {
                    searchMsg = 'Pengguna dengan username tersebut tidak ditemukan.';
                  }
                });
              } catch (e) {
                setModalState(() {
                  searching = false;
                  searchMsg = 'Gagal mencari pengguna: $e';
                });
              }
            }

            void sendRequest(String username) async {
              try {
                final res = await ApiService.instance.sendFriendRequest(username);
                if (ctx.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(res['message'] ?? 'Permintaan terkirim!'),
                      backgroundColor: const Color(0xFF2563EB),
                    ),
                  );
                  Navigator.pop(ctx);
                  _loadData(isSilent: true);
                }
              } catch (e) {
                if (ctx.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Error: $e')),
                  );
                }
              }
            }

            return Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(ctx).viewInsets.bottom + 16,
                top: 20,
                left: 20,
                right: 20,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.person_add_alt_1_rounded, color: Color(0xFF2563EB)),
                          SizedBox(width: 8),
                          Text(
                            'Tambah Teman',
                            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
                          ),
                        ],
                      ),
                      IconButton(
                        icon: const Icon(Icons.close_rounded),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  const Text(
                    'Cari akun pengguna lain berdasarkan @username untuk mengirimkan permintaan pertemanan.',
                    style: TextStyle(fontSize: 12, color: Colors.grey),
                  ),
                  const SizedBox(height: 14),
                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: searchCtrl,
                          textInputAction: TextInputAction.search,
                          onSubmitted: (_) => doSearch(),
                          decoration: InputDecoration(
                            hintText: 'Ketik username, misal: budi',
                            hintStyle: const TextStyle(fontSize: 13),
                            prefixIcon: const Icon(Icons.search_rounded, size: 20),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                            isDense: true,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      ElevatedButton(
                        onPressed: searching ? null : doSearch,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF2563EB),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 11),
                        ),
                        child: searching
                            ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                            : const Text('Cari', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  if (searchMsg != null)
                    Padding(
                      padding: const EdgeInsets.symmetric(vertical: 20),
                      child: Text(searchMsg!, textAlign: TextAlign.center, style: const TextStyle(fontSize: 13, color: Colors.grey)),
                    ),
                  if (searchResults.isNotEmpty)
                    ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: searchResults.length,
                      itemBuilder: (c, idx) {
                        final u = searchResults[idx];
                        final init = u.name.isNotEmpty ? u.name[0].toUpperCase() : 'U';

                        Widget actionBtn;
                        if (u.friendStatus == 'friends') {
                          actionBtn = ElevatedButton(
                            onPressed: () {
                              Navigator.pop(ctx);
                              Navigator.of(context, rootNavigator: true).push(
                                MaterialPageRoute(
                                  builder: (_) => DirectChatScreen(
                                    friendId: u.id,
                                    friendName: u.name,
                                    friendUsername: u.username,
                                    friendAvatar: u.avatar,
                                  ),
                                ),
                              );
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF10B981),
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              minimumSize: Size.zero,
                              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                            ),
                            child: const Text('Chat', style: TextStyle(fontSize: 12, color: Colors.white)),
                          );
                        } else if (u.friendStatus == 'pending_sent') {
                          actionBtn = Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                            decoration: BoxDecoration(
                              color: Colors.amber.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Text(
                              'Menunggu',
                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.amber),
                            ),
                          );
                        } else if (u.friendStatus == 'pending_received') {
                          actionBtn = ElevatedButton(
                            onPressed: () {
                              if (u.requestId != null) {
                                Navigator.pop(ctx);
                                _respondFriendRequest(u.requestId!, 'accept');
                              }
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF10B981),
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              minimumSize: Size.zero,
                            ),
                            child: const Text('Terima', style: TextStyle(fontSize: 12, color: Colors.white)),
                          );
                        } else {
                          actionBtn = ElevatedButton(
                            onPressed: () => sendRequest(u.username),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF2563EB),
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              minimumSize: Size.zero,
                            ),
                            child: const Text('Tambah', style: TextStyle(fontSize: 12, color: Colors.white)),
                          );
                        }

                        return Container(
                          margin: const EdgeInsets.only(bottom: 8),
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            border: Border.all(color: Colors.grey.shade300),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Row(
                            children: [
                              CircleAvatar(
                                radius: 18,
                                backgroundColor: const Color(0xFF3B82F6),
                                child: Text(init, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(u.name, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                                    Text('@${u.username}', style: const TextStyle(color: Colors.grey, fontSize: 11)),
                                  ],
                                ),
                              ),
                              actionBtn,
                            ],
                          ),
                        );
                      },
                    ),
                  const SizedBox(height: 10),
                ],
              ),
            );
          },
        );
      },
    );
  }

  void _showContactsSheet(BuildContext context) {
    showModalBottomSheet(
      context: context,
      useRootNavigator: true,
      backgroundColor: Theme.of(context).cardColor,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 18),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Kontak Teman (${_friends.length})',
                      style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                if (_friends.isEmpty)
                  Padding(
                    padding: const EdgeInsets.symmetric(vertical: 36),
                    child: Column(
                      children: [
                        const Icon(Icons.people_outline_rounded, size: 48, color: Colors.grey),
                        const SizedBox(height: 10),
                        const Text('Belum Ada Teman', style: TextStyle(fontWeight: FontWeight.w700)),
                        const SizedBox(height: 4),
                        const Text(
                          'Gunakan Tambah Teman untuk mencari teman baru via username.',
                          textAlign: TextAlign.center,
                          style: TextStyle(fontSize: 12, color: Colors.grey),
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: () {
                            Navigator.pop(ctx);
                            _showAddFriendDialog(context);
                          },
                          icon: const Icon(Icons.person_add_rounded, size: 16),
                          label: const Text('Tambah Teman Sekarang'),
                          style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF2563EB)),
                        ),
                      ],
                    ),
                  )
                else
                  Expanded(
                    child: ListView.separated(
                      itemCount: _friends.length,
                      separatorBuilder: (_, _) => Divider(height: 1, color: Colors.grey.shade200),
                      itemBuilder: (c, idx) {
                        final f = _friends[idx];
                        final init = f.name.isNotEmpty ? f.name[0].toUpperCase() : 'T';

                        return ListTile(
                          contentPadding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          leading: CircleAvatar(
                            radius: 20,
                            backgroundColor: const Color(0xFF2563EB),
                            child: Text(init, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
                          ),
                          title: Text(f.name, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14)),
                          subtitle: Text('@${f.username}', style: const TextStyle(color: Colors.grey, fontSize: 12)),
                          trailing: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                            decoration: BoxDecoration(
                              color: const Color(0xFF10B981).withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Text('💬 Chat', style: TextStyle(color: Color(0xFF10B981), fontWeight: FontWeight.w700, fontSize: 12)),
                          ),
                          onTap: () {
                            Navigator.pop(ctx);
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => DirectChatScreen(
                                  friendId: f.friendId,
                                  friendName: f.name,
                                  friendUsername: f.username,
                                  friendAvatar: f.avatar,
                                ),
                              ),
                            );
                          },
                        );
                      },
                    ),
                  ),
              ],
            ),
          ),
        );
      },
    );
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
        automaticallyImplyLeading: !widget.isRootTab,
        title: const Text('Pesan & Obrolan'),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.person_add_rounded),
            tooltip: 'Tambah Teman',
            onPressed: () => _showAddFriendDialog(context),
          ),
          IconButton(
            icon: const Icon(Icons.contacts_rounded),
            tooltip: 'Kontak Teman',
            onPressed: () => _showContactsSheet(context),
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Segarkan',
            onPressed: () => _loadData(),
          ),
        ],
      ),
      floatingActionButton: Padding(
        padding: const EdgeInsets.only(bottom: 84),
        child: FloatingActionButton(
          heroTag: 'fab_whatsapp_new_chat',
          backgroundColor: const Color(0xFF00A884), // WhatsApp Green
          tooltip: 'Chat Baru',
          onPressed: () => _showContactsSheet(context),
          child: const Icon(Icons.chat_rounded, color: Colors.white, size: 24),
        ),
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

    // Filter conversations
    final filtered = _conversations.where((c) {
      final isArchived = (c['is_archived'] == true || c['is_archived'] == 1 || c['is_archived'] == '1');
      if (_activeFilter == 'archived') {
        return isArchived;
      }
      if (isArchived) return false;
      final type = c['type'] ?? 'marketplace';
      if (_activeFilter == 'direct') return type == 'direct';
      if (_activeFilter == 'marketplace') return type == 'marketplace';
      return true;
    }).toList();

    return RefreshIndicator(
      onRefresh: _loadData,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(14, 10, 14, 90),
        children: [
          // 0. Friend-Only Status / Stories Tray
          _buildStatusStoriesTray(),
          const SizedBox(height: 12),

          // 1. Incoming Friend Requests Banner
          if (_incomingRequests.isNotEmpty) ...[
            Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFEFF6FF),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFF93C5FD)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 8,
                        height: 8,
                        decoration: const BoxDecoration(
                          color: Color(0xFF2563EB),
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        'Permintaan Pertemanan (${_incomingRequests.length})',
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF1E40AF),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  ..._incomingRequests.map((req) {
                    final rInit = req.requesterName.isNotEmpty ? req.requesterName[0].toUpperCase() : 'U';

                    return Container(
                      margin: const EdgeInsets.only(top: 6),
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Row(
                        children: [
                          ClipOval(
                            child: (req.requesterAvatarImage != null && req.requesterAvatarImage!.isNotEmpty)
                                ? Image.network(
                                    _fullImageUrl(req.requesterAvatarImage),
                                    width: 32,
                                    height: 32,
                                    fit: BoxFit.cover,
                                    errorBuilder: (_, _, _) => CircleAvatar(
                                      radius: 16,
                                      backgroundColor: const Color(0xFF3B82F6),
                                      child: Text(rInit, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 12)),
                                    ),
                                  )
                                : CircleAvatar(
                                    radius: 16,
                                    backgroundColor: const Color(0xFF3B82F6),
                                    child: Text(rInit, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 12)),
                                  ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(req.requesterName, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                                Text('@${req.requesterUsername}', style: const TextStyle(color: Colors.grey, fontSize: 11)),
                              ],
                            ),
                          ),
                          ElevatedButton(
                            onPressed: () => _respondFriendRequest(req.requestId, 'accept'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF10B981),
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              minimumSize: Size.zero,
                              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                            ),
                            child: const Text('Terima', style: TextStyle(fontSize: 11, color: Colors.white)),
                          ),
                          const SizedBox(width: 6),
                          OutlinedButton(
                            onPressed: () => _respondFriendRequest(req.requestId, 'reject'),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: Colors.red,
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              minimumSize: Size.zero,
                              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                            ),
                            child: const Text('Tolak', style: TextStyle(fontSize: 11)),
                          ),
                        ],
                      ),
                    );
                  }),
                ],
              ),
            ),
          ],

          // 2. Filter Pills
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildFilterChip('Semua', 'all'),
                const SizedBox(width: 8),
                _buildFilterChip('Teman (Direct)', 'direct'),
                const SizedBox(width: 8),
                _buildFilterChip('Marketplace', 'marketplace'),
                const SizedBox(width: 8),
                _buildFilterChip('Status${_statuses.isNotEmpty ? ' (${_statuses.length})' : ''}', 'status'),
                const SizedBox(width: 8),
                _buildFilterChip('Diarsipkan${_archivedCount > 0 ? ' ($_archivedCount)' : ''}', 'archived'),
              ],
            ),
          ),
          const SizedBox(height: 10),

          // 3. Status View (WhatsApp Style) or Conversation List
          if (_activeFilter == 'status')
            Builder(
              builder: (_) {
                final myStatuses = _statuses.where((s) => s['is_mine'] == true).toList();
                final friendsStatuses = _statuses.where((s) => s['is_mine'] != true).toList();
                final Map<int, List<Map<String, dynamic>>> groupedFriends = {};
                for (final st in friendsStatuses) {
                  final uId = int.tryParse('${st['user_id']}') ?? 0;
                  groupedFriends.putIfAbsent(uId, () => []).add(st);
                }
                return _buildWhatsAppStatusView(myStatuses, groupedFriends);
              },
            )
          else if (filtered.isEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 40),
              child: Center(
                child: Column(
                  children: [
                    Icon(
                      _activeFilter == 'archived' ? Icons.archive_outlined : Icons.forum_outlined,
                      size: 48,
                      color: Colors.grey,
                    ),
                    const SizedBox(height: 12),
                    Text(
                      _activeFilter == 'archived' ? 'Belum Ada Obrolan Diarsipkan' : 'Belum Ada Percakapan',
                      style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      _activeFilter == 'archived'
                          ? 'Tekan lama atau gunakan ikon titik tiga pada obrolan untuk mengarsipkan.'
                          : 'Tambahkan teman untuk mulai mengobrol seperti WhatsApp!',
                      textAlign: TextAlign.center,
                      style: const TextStyle(color: Colors.grey, fontSize: 12),
                    ),
                    if (_activeFilter != 'archived') ...[
                      const SizedBox(height: 16),
                      ElevatedButton.icon(
                        onPressed: () => _showAddFriendDialog(context),
                        icon: const Icon(Icons.person_add_rounded, size: 16),
                        label: const Text('Tambah Teman'),
                        style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF2563EB)),
                      ),
                    ],
                  ],
                ),
              ),
            )
          else
            ...filtered.map((conv) {
              final type = conv['type'] ?? 'marketplace';
              final isDirect = (type == 'direct');

              if (isDirect) {
                // DIRECT CHAT ITEM (TEMAN)
                final partnerId = int.tryParse('${conv['partner_id']}') ?? 0;
                final partnerName = (conv['partner_name'] ?? 'Teman').toString();
                final partnerUsername = (conv['partner_username'] ?? '').toString();
                final partnerAvatar = conv['partner_avatar'];
                final partnerAvatarUrl = (conv['partner_avatar_url'] ?? conv['partner_avatar_image'] ?? '').toString();
                final lastMsg = (conv['last_message'] ?? '').toString();
                final unreadCount = int.tryParse('${conv['unread_count']}') ?? 0;
                final lastSenderId = int.tryParse('${conv['last_sender_id']}') ?? 0;
                final isMyMsg = _myId > 0 && lastSenderId == _myId;
                final init = partnerName.isNotEmpty ? partnerName[0].toUpperCase() : 'T';
                final isPinned = (conv['is_pinned'] == true || conv['is_pinned'] == 1 || conv['is_pinned'] == '1');

                return Card(
                  margin: const EdgeInsets.only(bottom: 8),
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                    side: BorderSide(
                      color: isPinned
                          ? const Color(0xFF2563EB).withValues(alpha: 0.4)
                          : (unreadCount > 0 ? const Color(0xFF2563EB).withValues(alpha: 0.3) : Colors.grey.shade200),
                    ),
                  ),
                  color: isPinned
                      ? const Color(0xFF2563EB).withValues(alpha: 0.05)
                      : (unreadCount > 0 ? const Color(0xFF2563EB).withValues(alpha: 0.03) : Theme.of(context).cardColor),
                  child: InkWell(
                    borderRadius: BorderRadius.circular(16),
                    onTap: () async {
                      await Navigator.of(context, rootNavigator: true).push(
                        MaterialPageRoute(
                          builder: (_) => DirectChatScreen(
                            friendId: partnerId,
                            friendName: partnerName,
                            friendUsername: partnerUsername,
                            friendAvatar: partnerAvatarUrl.isNotEmpty ? partnerAvatarUrl : partnerAvatar,
                          ),
                        ),
                      );
                      _loadData(isSilent: true);
                    },
                    onLongPress: () => _showConversationOptions(conv),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      child: Row(
                        children: [
                          ClipOval(
                            child: partnerAvatarUrl.isNotEmpty
                                ? Image.network(
                                    _fullImageUrl(partnerAvatarUrl),
                                    width: 48,
                                    height: 48,
                                    fit: BoxFit.cover,
                                    errorBuilder: (_, _, _) => CircleAvatar(
                                      radius: 24,
                                      backgroundColor: const Color(0xFF2563EB),
                                      child: Text(init, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 18)),
                                    ),
                                  )
                                : CircleAvatar(
                                    radius: 24,
                                    backgroundColor: const Color(0xFF2563EB),
                                    child: Text(init, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 18)),
                                  ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    if (isPinned) ...[
                                      const Icon(Icons.push_pin_rounded, size: 14, color: Color(0xFF2563EB)),
                                      const SizedBox(width: 3),
                                    ],
                                    Expanded(
                                      child: Text(
                                        partnerName,
                                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14),
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFDBEAFE),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: const Text('Teman', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Color(0xFF1D4ED8))),
                                    ),
                                  ],
                                ),
                                if (partnerUsername.isNotEmpty)
                                  Text('@$partnerUsername', style: const TextStyle(fontSize: 11, color: Colors.grey)),
                                const SizedBox(height: 4),
                                Row(
                                  children: [
                                    if (isMyMsg) ...[
                                      const Icon(Icons.done_all_rounded, size: 14, color: Color(0xFF2563EB)),
                                      const SizedBox(width: 4),
                                    ],
                                    Expanded(
                                      child: Text(
                                        lastMsg,
                                        style: TextStyle(
                                          fontSize: 12,
                                          fontWeight: unreadCount > 0 ? FontWeight.w700 : FontWeight.w400,
                                          color: unreadCount > 0 ? Colors.black87 : Colors.grey.shade600,
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
                          if (unreadCount > 0)
                            Container(
                              margin: const EdgeInsets.only(left: 8),
                              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                              decoration: const BoxDecoration(
                                color: Color(0xFF2563EB),
                                shape: BoxShape.circle,
                              ),
                              child: Text(
                                unreadCount > 99 ? '99+' : '$unreadCount',
                                style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800),
                              ),
                            ),
                          IconButton(
                            icon: const Icon(Icons.more_vert_rounded, size: 18, color: Colors.grey),
                            onPressed: () => _showConversationOptions(conv),
                            visualDensity: VisualDensity.compact,
                            padding: const EdgeInsets.only(left: 4),
                            constraints: const BoxConstraints(),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              } else {
                // MARKETPLACE CHAT ITEM
                final listingId = int.tryParse('${conv['listing_id']}') ?? 0;
                final buyerId = int.tryParse('${conv['buyer_id']}') ?? 0;
                final partnerName = (conv['partner_name'] ?? 'Penjual/Pembeli').toString();
                final partnerPhone = (conv['partner_phone'] ?? '').toString();
                final title = (conv['listing_title'] ?? 'Produk').toString();
                final price = _formatRupiah(conv['listing_price']);
                final img = (conv['listing_image'] ?? '').toString();
                final lastMsg = (conv['last_message'] ?? '').toString();
                final unreadCount = int.tryParse('${conv['unread_count']}') ?? 0;
                final lastSenderId = int.tryParse('${conv['last_sender_id']}') ?? 0;
                final isMyMsg = _myId > 0 && lastSenderId == _myId;
                final isPinned = (conv['is_pinned'] == true || conv['is_pinned'] == 1 || conv['is_pinned'] == '1');

                return Card(
                  margin: const EdgeInsets.only(bottom: 8),
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                    side: BorderSide(
                      color: isPinned
                          ? const Color(0xFF059669).withValues(alpha: 0.4)
                          : (unreadCount > 0 ? const Color(0xFF059669).withValues(alpha: 0.3) : Colors.grey.shade200),
                    ),
                  ),
                  color: isPinned ? const Color(0xFF059669).withValues(alpha: 0.05) : Theme.of(context).cardColor,
                  child: InkWell(
                    borderRadius: BorderRadius.circular(16),
                    onTap: () async {
                      await Navigator.of(context, rootNavigator: true).push(
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
                      _loadData(isSilent: true);
                    },
                    onLongPress: () => _showConversationOptions(conv),
                    child: Padding(
                      padding: const EdgeInsets.all(12),
                      child: Row(
                        children: [
                          ClipRRect(
                            borderRadius: BorderRadius.circular(10),
                            child: Container(
                              width: 48,
                              height: 48,
                              color: const Color(0xFFF1F5F9),
                              child: img.isNotEmpty
                                  ? Image.network(
                                      _fullImageUrl(img),
                                      fit: BoxFit.cover,
                                      errorBuilder: (_, _, _) => const Icon(Icons.shopping_bag_outlined, color: Colors.grey),
                                    )
                                  : const Icon(Icons.shopping_bag_outlined, color: Colors.grey),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    if (isPinned) ...[
                                      const Icon(Icons.push_pin_rounded, size: 14, color: Color(0xFF059669)),
                                      const SizedBox(width: 3),
                                    ],
                                    Expanded(
                                      child: Text(
                                        partnerName,
                                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14),
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFD1FAE5),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: const Text('Marketplace', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Color(0xFF047857))),
                                    ),
                                  ],
                                ),
                                Text('$title • $price', style: const TextStyle(fontSize: 11, color: Colors.grey), maxLines: 1, overflow: TextOverflow.ellipsis),
                                const SizedBox(height: 4),
                                Row(
                                  children: [
                                    if (isMyMsg) ...[
                                      const Icon(Icons.done_all_rounded, size: 14, color: Color(0xFF059669)),
                                      const SizedBox(width: 4),
                                    ],
                                    Expanded(
                                      child: Text(
                                        lastMsg,
                                        style: TextStyle(
                                          fontSize: 12,
                                          fontWeight: unreadCount > 0 ? FontWeight.w700 : FontWeight.w400,
                                          color: unreadCount > 0 ? Colors.black87 : Colors.grey.shade600,
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
                          if (unreadCount > 0)
                            Container(
                              margin: const EdgeInsets.only(left: 8),
                              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                              decoration: const BoxDecoration(
                                color: Color(0xFF059669),
                                shape: BoxShape.circle,
                              ),
                              child: Text(
                                unreadCount > 99 ? '99+' : '$unreadCount',
                                style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800),
                              ),
                            ),
                          IconButton(
                            icon: const Icon(Icons.more_vert_rounded, size: 18, color: Colors.grey),
                            onPressed: () => _showConversationOptions(conv),
                            visualDensity: VisualDensity.compact,
                            padding: const EdgeInsets.only(left: 4),
                            constraints: const BoxConstraints(),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              }
            }),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String label, String filterKey) {
    final isSelected = _activeFilter == filterKey;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (_) => setState(() => _activeFilter = filterKey),
      labelStyle: TextStyle(
        fontSize: 12,
        fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
        color: isSelected ? Colors.white : Colors.grey.shade700,
      ),
      selectedColor: const Color(0xFF2563EB),
      backgroundColor: Colors.grey.shade100,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
    );
  }

  String _formatTimeAgo(dynamic dateStr) {
    if (dateStr == null) return '';
    try {
      final dt = DateTime.parse(dateStr.toString().replaceAll('/', '-'));
      final now = DateTime.now();
      final diff = now.difference(dt);
      if (diff.inMinutes < 1) return 'Baru saja';
      if (diff.inMinutes < 60) return '${diff.inMinutes}m lalu';
      if (diff.inHours < 24) return '${diff.inHours}j lalu';
      return '${diff.inDays}h lalu';
    } catch (_) {
      return '';
    }
  }

  // ═════════════════════════════════════════════════════════════════════════
  // WHATSAPP STYLE STATUS PAGE VIEW
  // ═════════════════════════════════════════════════════════════════════════

  Widget _buildWhatsAppStatusView(List<Map<String, dynamic>> myStatuses, Map<int, List<Map<String, dynamic>>> groupedFriends) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // 1. Status Saya Card
        Container(
          margin: const EdgeInsets.only(bottom: 10),
          decoration: BoxDecoration(
            color: Theme.of(context).cardColor,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: Colors.grey.shade200),
            boxShadow: [
              BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 8, offset: const Offset(0, 2)),
            ],
          ),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            child: Row(
              children: [
                GestureDetector(
                  onTap: () {
                    if (myStatuses.isNotEmpty) {
                      _openStatusViewer(myStatuses, 0);
                    } else {
                      _showCreateStatusSheet();
                    }
                  },
                  child: Stack(
                    clipBehavior: Clip.none,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(2.5),
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: myStatuses.isNotEmpty ? const Color(0xFF10B981) : Colors.grey.shade400,
                            width: 2.5,
                          ),
                        ),
                        child: const CircleAvatar(
                          radius: 24,
                          backgroundColor: Color(0xFFEFF6FF),
                          child: Icon(Icons.person_rounded, color: Color(0xFF2563EB), size: 28),
                        ),
                      ),
                      if (myStatuses.length > 1)
                        Positioned(
                          top: -2,
                          right: -2,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFF10B981),
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(color: Colors.white, width: 1.5),
                            ),
                            child: Text(
                              '${myStatuses.length}',
                              style: const TextStyle(color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.w800),
                            ),
                          ),
                        )
                      else
                        Positioned(
                          bottom: 0,
                          right: 0,
                          child: GestureDetector(
                            onTap: _showCreateStatusSheet,
                            child: Container(
                              width: 20,
                              height: 20,
                              decoration: BoxDecoration(
                                color: const Color(0xFF10B981),
                                shape: BoxShape.circle,
                                border: Border.all(color: Colors.white, width: 2),
                              ),
                              child: const Icon(Icons.add, size: 13, color: Colors.white),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: GestureDetector(
                    onTap: () {
                      if (myStatuses.isNotEmpty) {
                        _openStatusViewer(myStatuses, 0);
                      } else {
                        _showCreateStatusSheet();
                      }
                    },
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          myStatuses.isNotEmpty ? 'Status Saya (${myStatuses.length})' : 'Status Saya',
                          style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15),
                        ),
                        const SizedBox(height: 3),
                        Text(
                          myStatuses.isNotEmpty
                              ? '${myStatuses.length} pembaruan aktif • Ketuk untuk melihat'
                              : 'Ketuk untuk menambahkan pembaruan status',
                          style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                        ),
                      ],
                    ),
                  ),
                ),
                if (myStatuses.isNotEmpty)
                  IconButton(
                    icon: const Icon(Icons.visibility_rounded, color: Color(0xFF2563EB), size: 22),
                    tooltip: 'Lihat Status Saya',
                    onPressed: () => _openStatusViewer(myStatuses, 0),
                  ),
                IconButton(
                  icon: const Icon(Icons.add_circle_rounded, color: Color(0xFF10B981), size: 28),
                  tooltip: 'Buat Status Baru',
                  onPressed: _showCreateStatusSheet,
                ),
              ],
            ),
          ),
        ),

        // 1.1 Daftar Rincian Status Saya
        if (myStatuses.isNotEmpty)
          Container(
            margin: const EdgeInsets.only(bottom: 16),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.grey.shade50,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: Colors.grey.shade200, style: BorderStyle.solid),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'DAFTAR STATUS ANDA (${myStatuses.length})',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Colors.grey.shade700, letterSpacing: 0.5),
                    ),
                    const Text('● Aktif 24 Jam', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: Color(0xFF10B981))),
                  ],
                ),
                const SizedBox(height: 8),
                ...myStatuses.asMap().entries.map((entry) {
                  final idx = entry.key;
                  final st = entry.value;
                  final isImg = st['media_type'] == 'image';
                  final cap = (st['caption'] ?? '').toString();
                  final preview = isImg ? '📷 [Foto] ${cap.isNotEmpty ? cap : 'Foto Status'}' : '📝 ${cap.isNotEmpty ? cap : 'Status Teks'}';
                  final stId = int.tryParse('${st['id']}') ?? 0;
                  final timeStr = _formatTimeAgo(st['created_at']);

                  return Container(
                    margin: const EdgeInsets.only(bottom: 6),
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                    decoration: BoxDecoration(
                      color: Theme.of(context).cardColor,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.grey.shade200),
                    ),
                    child: Row(
                      children: [
                        Expanded(
                          child: InkWell(
                            onTap: () => _openStatusViewer(myStatuses, idx),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Cerita ${idx + 1}: $preview',
                                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 3),
                                Row(
                                  children: [
                                    Text(
                                      timeStr.isNotEmpty ? timeStr : 'Baru saja',
                                      style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                                    ),
                                    if ((int.tryParse('${st['comment_count']}') ?? 0) > 0) ...[
                                      const SizedBox(width: 8),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                                        decoration: BoxDecoration(
                                          color: const Color(0xFFDBEAFE),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            const Icon(Icons.chat_bubble_rounded, size: 10, color: Color(0xFF1D4ED8)),
                                            const SizedBox(width: 3),
                                            Text(
                                              '${st['comment_count']} komentar',
                                              style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Color(0xFF1D4ED8)),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ),
                        IconButton(
                          icon: Stack(
                            clipBehavior: Clip.none,
                            children: [
                              const Icon(Icons.chat_bubble_outline_rounded, size: 18, color: Color(0xFF10B981)),
                              if ((int.tryParse('${st['comment_count']}') ?? 0) > 0)
                                Positioned(
                                  top: -4,
                                  right: -6,
                                  child: Container(
                                    padding: const EdgeInsets.all(2),
                                    decoration: const BoxDecoration(color: Color(0xFF10B981), shape: BoxShape.circle),
                                    constraints: const BoxConstraints(minWidth: 12, minHeight: 12),
                                    child: Text(
                                      '${st['comment_count']}',
                                      style: const TextStyle(color: Colors.white, fontSize: 8, fontWeight: FontWeight.w800),
                                      textAlign: TextAlign.center,
                                    ),
                                  ),
                                ),
                            ],
                          ),
                          tooltip: 'Lihat & Balas Komentar',
                          visualDensity: VisualDensity.compact,
                          onPressed: () => _openStatusViewer(myStatuses, idx, autoOpenComments: true),
                        ),
                        IconButton(
                          icon: const Icon(Icons.visibility_outlined, size: 18, color: Color(0xFF2563EB)),
                          tooltip: 'Lihat Cerita Ini',
                          visualDensity: VisualDensity.compact,
                          onPressed: () => _openStatusViewer(myStatuses, idx),
                        ),
                        IconButton(
                          icon: const Icon(Icons.delete_outline_rounded, size: 18, color: Colors.redAccent),
                          tooltip: 'Hapus Cerita Ini',
                          visualDensity: VisualDensity.compact,
                          onPressed: () async {
                            final confirm = await showDialog<bool>(
                              context: context,
                              builder: (dCtx) => AlertDialog(
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                                title: const Text('Hapus Cerita Ini?', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
                                content: const Text('Cerita status ini akan dihapus permanen.', style: TextStyle(fontSize: 13)),
                                actions: [
                                  TextButton(onPressed: () => Navigator.pop(dCtx, false), child: const Text('Batal')),
                                  ElevatedButton(
                                    style: ElevatedButton.styleFrom(backgroundColor: Colors.redAccent),
                                    onPressed: () => Navigator.pop(dCtx, true),
                                    child: const Text('Hapus', style: TextStyle(color: Colors.white)),
                                  ),
                                ],
                              ),
                            );
                            if (confirm == true) {
                              try {
                                await ApiService.instance.deleteStatus(stId);
                                if (mounted) {
                                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Cerita berhasil dihapus')));
                                  _loadData(isSilent: true);
                                }
                              } catch (e) {
                                if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                              }
                            }
                          },
                        ),
                      ],
                    ),
                  );
                }),
              ],
            ),
          ),

        // 2. Pembaruan Terkini Section Header
        Padding(
          padding: const EdgeInsets.only(left: 4, bottom: 8),
          child: Row(
            children: [
              Text(
                'Pembaruan Terkini',
                style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: Colors.grey.shade700),
              ),
              const SizedBox(width: 6),
              if (groupedFriends.isNotEmpty)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(
                    color: const Color(0xFFDBEAFE),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    '${groupedFriends.length}',
                    style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: Color(0xFF1D4ED8)),
                  ),
                ),
              const Spacer(),
              Text(
                'Hanya Teman',
                style: TextStyle(fontSize: 11, color: Colors.grey.shade500, fontWeight: FontWeight.w600),
              ),
            ],
          ),
        ),

        // 3. Friends Status List
        if (groupedFriends.isEmpty)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 36, horizontal: 20),
            decoration: BoxDecoration(
              color: Theme.of(context).cardColor,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Column(
              children: [
                const Icon(Icons.motion_photos_on_rounded, size: 44, color: Colors.grey),
                const SizedBox(height: 10),
                const Text(
                  'Belum Ada Status Teman',
                  style: TextStyle(fontWeight: FontWeight.w800, fontSize: 15),
                ),
                const SizedBox(height: 4),
                Text(
                  'Pembaruan status dari teman Anda yang aktif dalam 24 jam terakhir akan muncul di sini.',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                ),
                const SizedBox(height: 14),
                ElevatedButton.icon(
                  onPressed: _showCreateStatusSheet,
                  icon: const Icon(Icons.edit_rounded, size: 16),
                  label: const Text('Buat Status Sekarang'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF10B981),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                  ),
                ),
              ],
            ),
          )
        else
          ...groupedFriends.entries.map((entry) {
            final list = entry.value;
            final latest = list.first;
            final name = (latest['author_name'] ?? 'Teman').toString();
            final avatarUrl = (latest['author_avatar_url'] ?? '').toString();
            final init = name.isNotEmpty ? name[0].toUpperCase() : 'T';
            final timeStr = _formatTimeAgo(latest['created_at']);
            final count = list.length;
            int totalComments = 0;
            for (final s in list) {
              totalComments += (int.tryParse('${s['comment_count']}') ?? 0);
            }

            return Card(
              margin: const EdgeInsets.only(bottom: 8),
              elevation: 0,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
                side: BorderSide(color: Colors.grey.shade200),
              ),
              color: Theme.of(context).cardColor,
              child: InkWell(
                borderRadius: BorderRadius.circular(16),
                onTap: () => _openStatusViewer(list, 0),
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(2.5),
                        decoration: const BoxDecoration(
                          shape: BoxShape.circle,
                          gradient: LinearGradient(
                            colors: [Color(0xFF2563EB), Color(0xFF10B981)],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                        ),
                        child: ClipOval(
                          child: avatarUrl.isNotEmpty
                              ? Image.network(
                                  _fullImageUrl(avatarUrl),
                                  width: 46,
                                  height: 46,
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, _, _) => CircleAvatar(
                                    radius: 23,
                                    backgroundColor: const Color(0xFF3B82F6),
                                    child: Text(init, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 16)),
                                  ),
                                )
                              : CircleAvatar(
                                  radius: 23,
                                  backgroundColor: const Color(0xFF3B82F6),
                                  child: Text(init, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 16)),
                                ),
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              name,
                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 3),
                            Text(
                              (timeStr.isNotEmpty ? '$timeStr • $count cerita' : '$count cerita baru') +
                                  (totalComments > 0 ? ' • 💬 $totalComments komentar' : ''),
                              style: TextStyle(
                                fontSize: 12,
                                color: totalComments > 0 ? const Color(0xFF1D4ED8) : Colors.grey.shade600,
                                fontWeight: totalComments > 0 ? FontWeight.w700 : FontWeight.normal,
                              ),
                            ),
                          ],
                        ),
                      ),
                      if (totalComments > 0)
                        IconButton(
                          icon: const Icon(Icons.chat_bubble_outline_rounded, size: 18, color: Color(0xFF2563EB)),
                          tooltip: 'Lihat Komentar',
                          onPressed: () => _openStatusViewer(list, 0, autoOpenComments: true),
                        ),
                      const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: Colors.grey),
                    ],
                  ),
                ),
              ),
            );
          }),
      ],
    );
  }

  void _showConversationOptions(Map<String, dynamic> conv) {
    final type = (conv['type'] ?? 'marketplace').toString();
    final isDirect = (type == 'direct');
    final partnerName = (conv['partner_name'] ?? (isDirect ? 'Teman' : 'Pengguna')).toString();
    final isPinned = (conv['is_pinned'] == true || conv['is_pinned'] == 1 || conv['is_pinned'] == '1');
    final isArchived = (conv['is_archived'] == true || conv['is_archived'] == 1 || conv['is_archived'] == '1');

    final targetId = isDirect
        ? (int.tryParse('${conv['partner_id']}') ?? 0)
        : (int.tryParse('${conv['listing_id']}') ?? 0);
    final targetSubId = isDirect
        ? 0
        : (int.tryParse('${conv['buyer_id']}') ?? 0);

    final init = partnerName.isNotEmpty ? partnerName[0].toUpperCase() : 'T';

    showModalBottomSheet(
      context: context,
      useRootNavigator: true,
      backgroundColor: Theme.of(context).cardColor,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  children: [
                    CircleAvatar(
                      radius: 18,
                      backgroundColor: isDirect ? const Color(0xFF2563EB) : const Color(0xFF059669),
                      child: Text(init, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 13)),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            partnerName,
                            style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          Text(
                            isDirect ? 'Obrolan Teman' : 'Obrolan Marketplace',
                            style: const TextStyle(fontSize: 11, color: Colors.grey),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded, size: 20),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Divider(height: 1, color: Colors.grey.shade200),
                const SizedBox(height: 6),

                // Pin / Unpin
                ListTile(
                  leading: Icon(
                    isPinned ? Icons.push_pin_outlined : Icons.push_pin_rounded,
                    color: const Color(0xFF2563EB),
                  ),
                  title: Text(
                    isPinned ? 'Lepas Sematan' : 'Sematkan ke Atas',
                    style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
                  ),
                  onTap: () async {
                    Navigator.pop(ctx);
                    try {
                      final res = await ApiService.instance.pinConversation(
                        type: type,
                        targetId: targetId,
                        targetSubId: targetSubId,
                      );
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text(res['message'] ?? 'Status sematan diperbarui')),
                        );
                        _loadData(isSilent: true);
                      }
                    } catch (e) {
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                      }
                    }
                  },
                ),

                // Archive / Unarchive
                ListTile(
                  leading: Icon(
                    isArchived ? Icons.unarchive_rounded : Icons.archive_rounded,
                    color: const Color(0xFFD97706),
                  ),
                  title: Text(
                    isArchived ? 'Keluarkan dari Arsip' : 'Arsipkan Obrolan',
                    style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
                  ),
                  onTap: () async {
                    Navigator.pop(ctx);
                    try {
                      final res = await ApiService.instance.archiveConversation(
                        type: type,
                        targetId: targetId,
                        targetSubId: targetSubId,
                      );
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text(res['message'] ?? 'Status arsip diperbarui')),
                        );
                        _loadData(isSilent: true);
                      }
                    } catch (e) {
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                      }
                    }
                  },
                ),

                // Delete
                ListTile(
                  leading: const Icon(Icons.delete_outline_rounded, color: Colors.red),
                  title: const Text(
                    'Hapus Obrolan',
                    style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: Colors.red),
                  ),
                  onTap: () {
                    Navigator.pop(ctx);
                    _confirmDeleteConversation(type, targetId, targetSubId, partnerName);
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  void _confirmDeleteConversation(String type, int targetId, int targetSubId, String partnerName) {
    showDialog(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Hapus Obrolan?', style: TextStyle(fontWeight: FontWeight.w800)),
        content: Text(
          'Hapus seluruh riwayat obrolan dengan "$partnerName"? Pesan yang telah dihapus tidak dapat dipulihkan.',
          style: const TextStyle(fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogCtx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () async {
              Navigator.pop(dialogCtx);
              try {
                final res = await ApiService.instance.deleteConversation(
                  type: type,
                  targetId: targetId,
                  targetSubId: targetSubId,
                );
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text(res['message'] ?? 'Obrolan berhasil dihapus')),
                  );
                  _loadData(isSilent: true);
                }
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                }
              }
            },
            child: const Text('Hapus', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  // ═════════════════════════════════════════════════════════════════════════
  // STATUS / STORIES FEATURE (FRIEND-ONLY)
  // ═════════════════════════════════════════════════════════════════════════

  Widget _buildStatusStoriesTray() {
    final myStatuses = _statuses.where((s) => s['is_mine'] == true).toList();
    final friendsStatuses = _statuses.where((s) => s['is_mine'] != true).toList();

    // Group friends statuses by user_id
    final Map<int, List<Map<String, dynamic>>> groupedFriends = {};
    for (final st in friendsStatuses) {
      final uId = int.tryParse('${st['user_id']}') ?? 0;
      groupedFriends.putIfAbsent(uId, () => []).add(st);
    }

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.only(left: 6, bottom: 8),
            child: Row(
              children: [
                const Icon(Icons.circle_notifications_rounded, size: 16, color: Color(0xFF2563EB)),
                const SizedBox(width: 6),
                const Text(
                  'Status Teman',
                  style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                ),
                const Spacer(),
                Text(
                  'Hanya Teman',
                  style: TextStyle(fontSize: 10.5, color: Colors.grey.shade600, fontWeight: FontWeight.w600),
                ),
              ],
            ),
          ),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // 1. Always present: Standalone Buat Status (+) Button
                GestureDetector(
                  onTap: _showCreateStatusSheet,
                  child: Container(
                    width: 68,
                    margin: const EdgeInsets.only(right: 10),
                    child: Column(
                      children: [
                        Stack(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(2.5),
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                border: Border.all(
                                  color: Colors.grey.shade300,
                                  width: 2.2,
                                ),
                              ),
                              child: const CircleAvatar(
                                radius: 24,
                                backgroundColor: Color(0xFFEFF6FF),
                                child: Icon(Icons.add, color: Color(0xFF2563EB), size: 28),
                              ),
                            ),
                            Positioned(
                              bottom: 0,
                              right: 0,
                              child: Container(
                                width: 20,
                                height: 20,
                                decoration: BoxDecoration(
                                  color: const Color(0xFF2563EB),
                                  shape: BoxShape.circle,
                                  border: Border.all(color: Colors.white, width: 2),
                                ),
                                child: const Icon(Icons.add, size: 13, color: Colors.white),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 5),
                        const Text(
                          'Buat Status',
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ),
                  ),
                ),

                // 2. My Active Statuses (if any)
                if (myStatuses.isNotEmpty)
                  GestureDetector(
                    onTap: () => _openStatusViewer(myStatuses, 0),
                    child: Container(
                      width: 68,
                      margin: const EdgeInsets.only(right: 10),
                      child: Column(
                        children: [
                          Stack(
                            clipBehavior: Clip.none,
                            children: [
                              Container(
                                padding: const EdgeInsets.all(2.5),
                                decoration: const BoxDecoration(
                                  shape: BoxShape.circle,
                                  gradient: LinearGradient(
                                    colors: [Color(0xFF2563EB), Color(0xFF06B6D4)],
                                    begin: Alignment.topLeft,
                                    end: Alignment.bottomRight,
                                  ),
                                ),
                                child: const CircleAvatar(
                                  radius: 24,
                                  backgroundColor: Color(0xFFEFF6FF),
                                  child: Icon(Icons.person_rounded, color: Color(0xFF2563EB), size: 28),
                                ),
                              ),
                              if (myStatuses.length > 1)
                                Positioned(
                                  top: -2,
                                  right: -2,
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFF10B981),
                                      borderRadius: BorderRadius.circular(10),
                                      border: Border.all(color: Colors.white, width: 1.5),
                                    ),
                                    child: Text(
                                      '${myStatuses.length}',
                                      style: const TextStyle(color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.w800),
                                    ),
                                  ),
                                ),
                            ],
                          ),
                          const SizedBox(height: 5),
                          Text(
                            'Status Anda (${myStatuses.length})',
                            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            textAlign: TextAlign.center,
                          ),
                        ],
                      ),
                    ),
                  ),

                // 3. Friends Statuses
                ...groupedFriends.entries.map((entry) {
                  final list = entry.value;
                  final latest = list.first;
                  final name = (latest['author_name'] ?? 'Teman').toString();
                  final avatarUrl = (latest['author_avatar_url'] ?? '').toString();
                  final init = name.isNotEmpty ? name[0].toUpperCase() : 'T';
                  final fCount = list.length;

                  return GestureDetector(
                    onTap: () => _openStatusViewer(list, 0),
                    child: Container(
                      width: 68,
                      margin: const EdgeInsets.only(right: 10),
                      child: Column(
                        children: [
                          Stack(
                            clipBehavior: Clip.none,
                            children: [
                              Container(
                                padding: const EdgeInsets.all(2.5),
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  gradient: const LinearGradient(
                                    colors: [Color(0xFF2563EB), Color(0xFF10B981)],
                                    begin: Alignment.topLeft,
                                    end: Alignment.bottomRight,
                                  ),
                                ),
                                child: ClipOval(
                                  child: avatarUrl.isNotEmpty
                                      ? Image.network(
                                          _fullImageUrl(avatarUrl),
                                          width: 48,
                                          height: 48,
                                          fit: BoxFit.cover,
                                          errorBuilder: (_, _, _) => CircleAvatar(
                                            radius: 24,
                                            backgroundColor: const Color(0xFF3B82F6),
                                            child: Text(init, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 16)),
                                          ),
                                        )
                                      : CircleAvatar(
                                          radius: 24,
                                          backgroundColor: const Color(0xFF3B82F6),
                                          child: Text(init, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 16)),
                                        ),
                                ),
                              ),
                              if (fCount > 1)
                                Positioned(
                                  top: -2,
                                  right: -2,
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFF10B981),
                                      borderRadius: BorderRadius.circular(10),
                                      border: Border.all(color: Colors.white, width: 1.5),
                                    ),
                                    child: Text(
                                      '$fCount',
                                      style: const TextStyle(color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.w800),
                                    ),
                                  ),
                                ),
                            ],
                          ),
                          const SizedBox(height: 5),
                          Text(
                            fCount > 1 ? '$name ($fCount)' : name,
                            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            textAlign: TextAlign.center,
                          ),
                        ],
                      ),
                    ),
                  );
                }),
              ],
            ),
          ),
        ],
      ),
    );
  }

  void _showCreateStatusSheet() {
    final textCtrl = TextEditingController();
    String selectedColor = '#2563EB';
    bool isPosting = false;

    showModalBottomSheet(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (sheetCtx) => StatefulBuilder(
        builder: (ctx, setModalState) {
          final colors = ['#2563EB', '#7C3AED', '#059669', '#DC2626', '#D97706', '#0F172A'];

          return Container(
            padding: EdgeInsets.only(
              bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
              left: 18,
              right: 18,
              top: 18,
            ),
            decoration: BoxDecoration(
              color: Theme.of(ctx).scaffoldBackgroundColor,
              borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2)),
                    ),
                  ),
                  const SizedBox(height: 14),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Buat Status Baru',
                        style: TextStyle(fontWeight: FontWeight.w800, fontSize: 17),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: const Color(0xFFDBEAFE),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Text('Hanya Teman', style: TextStyle(color: Color(0xFF1D4ED8), fontSize: 11, fontWeight: FontWeight.w700)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Preview Box
                  Container(
                    height: 140,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Color(int.parse(selectedColor.replaceFirst('#', '0xFF'))),
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: Color(int.parse(selectedColor.replaceFirst('#', '0x40'))),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    alignment: Alignment.center,
                    child: TextField(
                      controller: textCtrl,
                      maxLines: 4,
                      textAlign: TextAlign.center,
                      style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w700),
                      cursorColor: Colors.white,
                      decoration: const InputDecoration(
                        hintText: 'Ketik apa yang Anda pikirkan...',
                        hintStyle: TextStyle(color: Colors.white70, fontSize: 16),
                        filled: false,
                        fillColor: Colors.transparent,
                        hoverColor: Colors.transparent,
                        border: InputBorder.none,
                        enabledBorder: InputBorder.none,
                        focusedBorder: InputBorder.none,
                        errorBorder: InputBorder.none,
                        disabledBorder: InputBorder.none,
                        contentPadding: EdgeInsets.zero,
                      ),
                      onChanged: (_) => setModalState(() {}),
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Color Picker
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: colors.map((c) {
                      final isSel = selectedColor == c;
                      return GestureDetector(
                        onTap: () => setModalState(() => selectedColor = c),
                        child: Container(
                          margin: const EdgeInsets.symmetric(horizontal: 5),
                          width: 28,
                          height: 28,
                          decoration: BoxDecoration(
                            color: Color(int.parse(c.replaceFirst('#', '0xFF'))),
                            shape: BoxShape.circle,
                            border: Border.all(
                              color: isSel ? Colors.white : Colors.transparent,
                              width: 2.5,
                            ),
                            boxShadow: isSel
                                ? [const BoxShadow(color: Colors.black26, blurRadius: 4, offset: Offset(0, 2))]
                                : null,
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                  const SizedBox(height: 18),

                  Row(
                    children: [
                      // Upload Foto Option
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: isPosting
                              ? null
                              : () async {
                                  final picker = ImagePicker();
                                  final picked = await picker.pickImage(source: ImageSource.gallery, imageQuality: 75);
                                  if (picked == null) return;
                                  final b64 = await ApiService.instance.base64FromFile(picked.path);
                                  if (b64 == null) return;

                                  if (sheetCtx.mounted) {
                                    Navigator.pop(sheetCtx);
                                  }
                                  setState(() => _isLoading = true);
                                  try {
                                    await ApiService.instance.createImageStatus(
                                      imageBase64: b64,
                                      caption: textCtrl.text.trim(),
                                    );
                                    if (mounted) {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        const SnackBar(content: Text('Foto status berhasil dibagikan ke teman!')),
                                      );
                                      _loadData(isSilent: true);
                                    }
                                  } catch (e) {
                                    if (mounted) {
                                      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                                      setState(() => _isLoading = false);
                                    }
                                  }
                                },
                          icon: const Icon(Icons.photo_camera_rounded, size: 18),
                          label: const Text('Foto'),
                        ),
                      ),
                      const SizedBox(width: 10),
                      // Post Text Status
                      Expanded(
                        flex: 2,
                        child: ElevatedButton.icon(
                          onPressed: isPosting || textCtrl.text.trim().isEmpty
                              ? null
                              : () async {
                                  setModalState(() => isPosting = true);
                                  try {
                                    await ApiService.instance.createTextStatus(
                                      text: textCtrl.text.trim(),
                                      backgroundColor: selectedColor,
                                    );
                                    if (sheetCtx.mounted) Navigator.pop(sheetCtx);
                                    if (mounted) {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        const SnackBar(content: Text('Status berhasil dibagikan ke teman!')),
                                      );
                                      _loadData(isSilent: true);
                                    }
                                  } catch (e) {
                                    if (sheetCtx.mounted) {
                                      setModalState(() => isPosting = false);
                                      ScaffoldMessenger.of(sheetCtx).showSnackBar(SnackBar(content: Text('Error: $e')));
                                    }
                                  }
                                },
                          icon: isPosting
                              ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                              : const Icon(Icons.send_rounded, size: 18),
                          label: const Text('Bagikan'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF2563EB),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 12),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  void _showInstagramCommentsSheet({
    required BuildContext parentCtx,
    required int statusId,
    required String authorName,
    required String authorAvatar,
    required String caption,
    required String timeStr,
    required ValueChanged<int> onCountUpdated,
  }) {
    showModalBottomSheet(
      context: parentCtx,
      isScrollControlled: true,
      useRootNavigator: true,
      backgroundColor: Colors.transparent,
      builder: (sheetCtx) => _InstagramCommentsModal(
        statusId: statusId,
        authorName: authorName,
        authorAvatar: authorAvatar,
        caption: caption,
        timeStr: timeStr,
        formatTimeAgo: _formatTimeAgo,
        fullImageUrl: _fullImageUrl,
        onCountUpdated: onCountUpdated,
      ),
    );
  }

  void _openStatusViewer(List<Map<String, dynamic>> statuses, int initialIndex, {bool autoOpenComments = false}) {
    int currentIndex = initialIndex;
    final commentCtrl = TextEditingController();
    bool hasAutoOpened = false;

    Navigator.of(context, rootNavigator: true).push(
      PageRouteBuilder(
        opaque: true,
        transitionDuration: const Duration(milliseconds: 250),
        reverseTransitionDuration: const Duration(milliseconds: 200),
        pageBuilder: (viewerCtx, anim, secAnim) => Scaffold(
          backgroundColor: Colors.black,
          resizeToAvoidBottomInset: false,
          body: StatefulBuilder(
            builder: (ctx, setViewerState) {
              final st = statuses[currentIndex];
              final authorName = (st['author_name'] ?? 'Teman').toString();
              final authorAvatar = (st['author_avatar_url'] ?? '').toString();
              final caption = (st['caption'] ?? '').toString();
              final mediaType = st['media_type'] ?? 'text';
              final mediaUrl = st['media_url'] ?? '';
              final bgColorStr = st['background_color'] ?? '#2563EB';
              final isMine = st['is_mine'] == true;
              final statusId = int.tryParse('${st['id']}') ?? 0;
              final timeStr = _formatTimeAgo(st['created_at']);
              final commentCount = int.tryParse('${st['comment_count']}') ?? 0;

              if (autoOpenComments && !hasAutoOpened) {
                hasAutoOpened = true;
                WidgetsBinding.instance.addPostFrameCallback((_) {
                  if (ctx.mounted) {
                    _showInstagramCommentsSheet(
                      parentCtx: ctx,
                      statusId: statusId,
                      authorName: authorName,
                      authorAvatar: authorAvatar,
                      caption: caption,
                      timeStr: timeStr,
                      onCountUpdated: (cnt) {
                        setViewerState(() => st['comment_count'] = cnt);
                      },
                    );
                  }
                });
              }

              Color bgColor = const Color(0xFF2563EB);
              try {
                bgColor = Color(int.parse(bgColorStr.replaceFirst('#', '0xFF')));
              } catch (_) {}

              return GestureDetector(
                onVerticalDragEnd: (details) {
                  if (details.primaryVelocity != null && details.primaryVelocity! > 250) {
                    Navigator.pop(viewerCtx);
                  }
                },
                child: Container(
                  width: double.infinity,
                  height: double.infinity,
                  color: Colors.black,
                  child: Column(
                    children: [
                // Top Progress Indicators with Safe Area
                Padding(
                  padding: EdgeInsets.only(
                    top: MediaQuery.of(ctx).padding.top > 0 ? MediaQuery.of(ctx).padding.top + 4 : 12,
                    left: 12,
                    right: 12,
                  ),
                  child: Row(
                    children: List.generate(statuses.length, (i) {
                      return Expanded(
                        child: Container(
                          height: 3,
                          margin: const EdgeInsets.symmetric(horizontal: 2),
                          decoration: BoxDecoration(
                            color: i <= currentIndex ? Colors.white : Colors.white24,
                            borderRadius: BorderRadius.circular(2),
                          ),
                        ),
                      );
                    }),
                  ),
                ),

                // Header
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  child: Row(
                    children: [
                      ClipOval(
                        child: authorAvatar.isNotEmpty
                            ? Image.network(
                                _fullImageUrl(authorAvatar),
                                width: 36,
                                height: 36,
                                fit: BoxFit.cover,
                                errorBuilder: (_, _, _) => const CircleAvatar(radius: 18, child: Icon(Icons.person)),
                              )
                            : const CircleAvatar(radius: 18, child: Icon(Icons.person)),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              isMine ? 'Status Anda' : authorName,
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 14),
                            ),
                            Text(
                              'Cerita ${currentIndex + 1} dari ${statuses.length} • ${timeStr.isNotEmpty ? timeStr : '24 Jam'}',
                              style: const TextStyle(color: Colors.white70, fontSize: 11),
                            ),
                          ],
                        ),
                      ),
                      if (isMine) ...[
                        IconButton(
                          icon: const Icon(Icons.add_circle_outline_rounded, color: Colors.white, size: 22),
                          tooltip: 'Tambah Cerita Baru',
                          onPressed: () {
                            Navigator.pop(viewerCtx);
                            _showCreateStatusSheet();
                          },
                        ),
                        IconButton(
                          icon: const Icon(Icons.delete_outline_rounded, color: Colors.redAccent, size: 22),
                          tooltip: 'Hapus Status Ini',
                          onPressed: () async {
                            Navigator.pop(viewerCtx);
                            try {
                              await ApiService.instance.deleteStatus(statusId);
                              if (mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Status dihapus')));
                                _loadData(isSilent: true);
                              }
                            } catch (_) {}
                          },
                        ),
                      ],
                      IconButton(
                        icon: const Icon(Icons.close_rounded, color: Colors.white, size: 24),
                        onPressed: () => Navigator.pop(viewerCtx),
                      ),
                    ],
                  ),
                ),

                // Status Body with Tap Left / Tap Right Navigation (Instagram Stories Style)
                Expanded(
                  child: GestureDetector(
                    onTapUp: (details) {
                      final width = MediaQuery.of(ctx).size.width;
                      if (details.localPosition.dx > width * 0.35) {
                        if (currentIndex < statuses.length - 1) {
                          setViewerState(() => currentIndex++);
                        } else {
                          Navigator.pop(viewerCtx);
                        }
                      } else {
                        if (currentIndex > 0) {
                          setViewerState(() => currentIndex--);
                        }
                      }
                    },
                    child: Container(
                      width: double.infinity,
                      color: mediaType == 'image' ? Colors.black : bgColor,
                      padding: const EdgeInsets.all(24),
                      alignment: Alignment.center,
                      child: mediaType == 'image'
                          ? Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Expanded(
                                  child: Image.network(
                                    _fullImageUrl(mediaUrl),
                                    fit: BoxFit.contain,
                                    errorBuilder: (_, _, _) => const Icon(Icons.broken_image_rounded, size: 64, color: Colors.white),
                                  ),
                                ),
                                if (caption.isNotEmpty) ...[
                                  const SizedBox(height: 12),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                                    decoration: BoxDecoration(
                                      color: Colors.black.withValues(alpha: 0.65),
                                      borderRadius: BorderRadius.circular(20),
                                      border: Border.all(color: Colors.white12),
                                    ),
                                    child: Text(
                                      caption,
                                      textAlign: TextAlign.center,
                                      style: const TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.w600),
                                    ),
                                  ),
                                ],
                              ],
                            )
                          : Text(
                              caption,
                              textAlign: TextAlign.center,
                              style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.w800, height: 1.4),
                            ),
                    ),
                  ),
                ),

                // Instagram Style Bottom Bar: Quick Reactions + Pill Comment Bar
                Container(
                  padding: EdgeInsets.only(
                    bottom: MediaQuery.of(ctx).viewInsets.bottom + MediaQuery.of(ctx).padding.bottom + 10,
                    left: 12,
                    right: 12,
                    top: 8,
                  ),
                  decoration: const BoxDecoration(
                    color: Color(0xFF0F172A),
                    border: Border(top: BorderSide(color: Colors.white12)),
                  ),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // Quick Emoji Reactions (Instagram Style: ❤️ 😂 🔥 👏 😮 😍)
                      if (!isMine)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceAround,
                            children: ['❤️', '😂', '🔥', '👏', '😮', '😍'].map((emoji) {
                              return GestureDetector(
                                onTap: () async {
                                  try {
                                    await ApiService.instance.commentStatus(statusId, emoji);
                                    if (ctx.mounted) {
                                      ScaffoldMessenger.of(ctx).showSnackBar(
                                        SnackBar(
                                          content: Text('Bereaksi $emoji pada status $authorName'),
                                          duration: const Duration(seconds: 1),
                                          backgroundColor: const Color(0xFF2563EB),
                                        ),
                                      );
                                      setViewerState(() => st['comment_count'] = commentCount + 1);
                                    }
                                  } catch (_) {}
                                },
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                  child: Text(emoji, style: const TextStyle(fontSize: 22)),
                                ),
                              );
                            }).toList(),
                          ),
                        ),

                      // Floating Instagram Comment Sheet Trigger Button
                      Padding(
                        padding: const EdgeInsets.only(bottom: 8),
                        child: InkWell(
                          borderRadius: BorderRadius.circular(20),
                          onTap: () {
                            _showInstagramCommentsSheet(
                              parentCtx: ctx,
                              statusId: statusId,
                              authorName: authorName,
                              authorAvatar: authorAvatar,
                              caption: caption,
                              timeStr: timeStr,
                              onCountUpdated: (cnt) {
                                setViewerState(() => st['comment_count'] = cnt);
                              },
                            );
                          },
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: Colors.white24),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.chat_bubble_outline_rounded, color: Color(0xFF38BDF8), size: 16),
                                const SizedBox(width: 8),
                                Text(
                                  commentCount > 0
                                      ? '💬 $commentCount Komentar • Ketuk untuk membaca/membalas'
                                      : '💬 Komentar Teman • Tulis balasan',
                                  style: const TextStyle(color: Colors.white, fontSize: 12.5, fontWeight: FontWeight.w700),
                                ),
                                const SizedBox(width: 4),
                                const Icon(Icons.keyboard_arrow_up_rounded, color: Colors.white70, size: 18),
                              ],
                            ),
                          ),
                        ),
                      ),

                      Row(
                        children: [
                          Expanded(
                            child: TextField(
                              controller: commentCtrl,
                              style: const TextStyle(color: Colors.white, fontSize: 14),
                              decoration: InputDecoration(
                                hintText: isMine ? 'Tulis komentar/catatan...' : 'Kirim balasan ke $authorName...',
                                hintStyle: const TextStyle(color: Colors.white54, fontSize: 13),
                                filled: true,
                                fillColor: Colors.white.withValues(alpha: 0.12),
                                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(24), borderSide: BorderSide.none),
                                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(24), borderSide: BorderSide.none),
                                focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(24), borderSide: const BorderSide(color: Color(0xFF38BDF8), width: 1.2)),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          IconButton(
                            icon: const Icon(Icons.send_rounded, color: Color(0xFF38BDF8)),
                            onPressed: () async {
                              final cText = commentCtrl.text.trim();
                              if (cText.isEmpty) return;
                              commentCtrl.clear();
                              try {
                                await ApiService.instance.commentStatus(statusId, cText);
                                setViewerState(() => st['comment_count'] = commentCount + 1);
                                if (ctx.mounted) {
                                  _showInstagramCommentsSheet(
                                    parentCtx: ctx,
                                    statusId: statusId,
                                    authorName: authorName,
                                    authorAvatar: authorAvatar,
                                    caption: caption,
                                    timeStr: timeStr,
                                    onCountUpdated: (cnt) {
                                      setViewerState(() => st['comment_count'] = cnt);
                                    },
                                  );
                                }
                              } catch (e) {
                                if (ctx.mounted) {
                                  ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(content: Text('Gagal: $e')));
                                }
                              }
                            },
                          ),
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
  ),
),
    );
  }
}

// ═════════════════════════════════════════════════════════════════════════════
// INSTAGRAM-STYLE INTERACTIVE COMMENTS MODAL (FULL EXPERIENCE)
// ═════════════════════════════════════════════════════════════════════════════

class _InstagramCommentsModal extends StatefulWidget {
  final int statusId;
  final String authorName;
  final String authorAvatar;
  final String caption;
  final String timeStr;
  final String Function(dynamic) formatTimeAgo;
  final String Function(String?) fullImageUrl;
  final ValueChanged<int> onCountUpdated;

  const _InstagramCommentsModal({
    required this.statusId,
    required this.authorName,
    required this.authorAvatar,
    required this.caption,
    required this.timeStr,
    required this.formatTimeAgo,
    required this.fullImageUrl,
    required this.onCountUpdated,
  });

  @override
  State<_InstagramCommentsModal> createState() => _InstagramCommentsModalState();
}

class _InstagramCommentsModalState extends State<_InstagramCommentsModal> {
  final TextEditingController _commentCtrl = TextEditingController();
  final ScrollController _scrollCtrl = ScrollController();
  List<Map<String, dynamic>> _comments = [];
  bool _loading = true;
  bool _sending = false;

  @override
  void initState() {
    super.initState();
    _loadComments();
  }

  @override
  void dispose() {
    _commentCtrl.dispose();
    _scrollCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadComments() async {
    try {
      final res = await ApiService.instance.getStatusComments(widget.statusId);
      if (mounted) {
        final cList = (res['comments'] as List<dynamic>?) ?? [];
        setState(() {
          _comments = cList.map((e) => Map<String, dynamic>.from(e as Map)).toList();
          _loading = false;
        });
        widget.onCountUpdated(_comments.length);
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _sendComment(String text) async {
    final clean = text.trim();
    if (clean.isEmpty || _sending) return;
    setState(() => _sending = true);
    _commentCtrl.clear();

    try {
      await ApiService.instance.commentStatus(widget.statusId, clean);
      await _loadComments();
      if (mounted) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (_scrollCtrl.hasClients) {
            _scrollCtrl.animateTo(
              _scrollCtrl.position.maxScrollExtent + 80,
              duration: const Duration(milliseconds: 300),
              curve: Curves.easeOut,
            );
          }
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal mengirim komentar: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      height: MediaQuery.of(context).size.height * 0.78,
      decoration: const BoxDecoration(
        color: Color(0xFF0F172A),
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        children: [
          // Drag handle
          Center(
            child: Container(
              margin: const EdgeInsets.only(top: 10, bottom: 6),
              width: 38,
              height: 4,
              decoration: BoxDecoration(
                color: Colors.white24,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),

          // Header
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
            child: Row(
              children: [
                const Text(
                  'Komentar',
                  style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 16),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: const Color(0xFF2563EB),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    '${_comments.length}',
                    style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w800),
                  ),
                ),
                const Spacer(),
                IconButton(
                  icon: const Icon(Icons.close_rounded, color: Colors.white70, size: 22),
                  onPressed: () => Navigator.pop(context),
                  visualDensity: VisualDensity.compact,
                ),
              ],
            ),
          ),

          // Pinned status preview (Instagram style)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            color: Colors.white.withValues(alpha: 0.04),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                CircleAvatar(
                  radius: 16,
                  backgroundColor: const Color(0xFF2563EB),
                  backgroundImage: widget.authorAvatar.isNotEmpty
                      ? NetworkImage(widget.fullImageUrl(widget.authorAvatar))
                      : null,
                  child: widget.authorAvatar.isEmpty
                      ? Text(widget.authorName.isNotEmpty ? widget.authorName[0].toUpperCase() : 'T', style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w700))
                      : null,
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Text(
                            widget.authorName,
                            style: const TextStyle(color: Color(0xFF38BDF8), fontWeight: FontWeight.w700, fontSize: 13),
                          ),
                          const SizedBox(width: 6),
                          Text(
                            widget.timeStr,
                            style: const TextStyle(color: Colors.white38, fontSize: 11),
                          ),
                        ],
                      ),
                      if (widget.caption.isNotEmpty) ...[
                        const SizedBox(height: 2),
                        Text(
                          widget.caption,
                          style: const TextStyle(color: Colors.white, fontSize: 13),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: Colors.white12),

          // Comments List
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator(color: Color(0xFF38BDF8), strokeWidth: 2.5))
                : _comments.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.chat_bubble_outline_rounded, size: 44, color: Colors.white24),
                            const SizedBox(height: 10),
                            const Text('Belum ada komentar', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 14)),
                            const SizedBox(height: 4),
                            const Text('Jadilah yang pertama mengomentari status ini!', style: TextStyle(color: Colors.white54, fontSize: 12)),
                          ],
                        ),
                      )
                    : ListView.builder(
                        controller: _scrollCtrl,
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                        itemCount: _comments.length,
                        itemBuilder: (ctx, i) {
                          final c = _comments[i];
                          final uName = (c['user_name'] ?? 'Teman').toString();
                          final uInit = uName.isNotEmpty ? uName[0].toUpperCase() : 'T';
                          final uAvatar = (c['user_avatar_url'] ?? '').toString();
                          final commentText = (c['comment'] ?? '').toString();
                          final cTime = widget.formatTimeAgo(c['created_at']);

                          return Padding(
                            padding: const EdgeInsets.only(bottom: 12),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                CircleAvatar(
                                  radius: 17,
                                  backgroundColor: const Color(0xFF2563EB),
                                  backgroundImage: uAvatar.isNotEmpty
                                      ? NetworkImage(widget.fullImageUrl(uAvatar))
                                      : null,
                                  child: uAvatar.isEmpty
                                      ? Text(uInit, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 13))
                                      : null,
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                    decoration: BoxDecoration(
                                      color: Colors.white.withValues(alpha: 0.08),
                                      borderRadius: BorderRadius.circular(16),
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            Text(
                                              uName,
                                              style: const TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF38BDF8), fontSize: 12.5),
                                            ),
                                            if (cTime.isNotEmpty)
                                              Text(
                                                cTime,
                                                style: const TextStyle(color: Colors.white38, fontSize: 10.5),
                                              ),
                                          ],
                                        ),
                                        const SizedBox(height: 3),
                                        SelectableText(
                                          commentText,
                                          style: const TextStyle(color: Colors.white, fontSize: 13.5, height: 1.3),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
          ),

          // Sticky Bottom Input Area
          Container(
            padding: EdgeInsets.only(
              left: 12,
              right: 12,
              top: 8,
              bottom: MediaQuery.of(context).viewInsets.bottom + MediaQuery.of(context).padding.bottom + 12,
            ),
            decoration: const BoxDecoration(
              color: Color(0xFF1E293B),
              border: Border(top: BorderSide(color: Colors.white12)),
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Quick Reaction Strip
                Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: ['❤️', '😂', '🔥', '👏', '😮', '😍'].map((emoji) {
                      return GestureDetector(
                        onTap: () => _sendComment(emoji),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          child: Text(emoji, style: const TextStyle(fontSize: 22)),
                        ),
                      );
                    }).toList(),
                  ),
                ),

                // Text field + Send button
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _commentCtrl,
                        style: const TextStyle(color: Colors.white, fontSize: 14),
                        decoration: InputDecoration(
                          hintText: 'Balas komentar untuk ${widget.authorName}...',
                          hintStyle: const TextStyle(color: Colors.white54, fontSize: 13),
                          filled: true,
                          fillColor: Colors.white.withValues(alpha: 0.12),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(24), borderSide: BorderSide.none),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(24), borderSide: BorderSide.none),
                          focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(24), borderSide: const BorderSide(color: Color(0xFF38BDF8), width: 1.2)),
                        ),
                        onSubmitted: _sendComment,
                      ),
                    ),
                    const SizedBox(width: 8),
                    IconButton(
                      icon: _sending
                          ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF38BDF8)))
                          : const Icon(Icons.send_rounded, color: Color(0xFF38BDF8)),
                      onPressed: _sending ? null : () => _sendComment(_commentCtrl.text),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
