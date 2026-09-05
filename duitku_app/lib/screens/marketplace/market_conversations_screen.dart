import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../config/api_config.dart';
import '../../models/friend.dart';
import '../../providers/app_data_provider.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../chat/direct_chat_screen.dart';
import 'market_chat_screen.dart';

class MarketConversationsScreen extends StatefulWidget {
  final bool isRootTab;
  const MarketConversationsScreen({super.key, this.isRootTab = false});

  @override
  State<MarketConversationsScreen> createState() => _MarketConversationsScreenState();
}

class _MarketConversationsScreenState extends State<MarketConversationsScreen> {
  bool _isLoading = true;
  String? _errorMessage;
  List<Map<String, dynamic>> _conversations = [];
  List<FriendRequest> _incomingRequests = [];
  List<Friend> _friends = [];
  int _myId = 0;
  int _archivedCount = 0;
  Timer? _refreshTimer;
  String _activeFilter = 'all'; // 'all', 'direct', 'marketplace', 'archived'

  @override
  void initState() {
    super.initState();
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

      if (mounted) {
        final totalUnread = int.tryParse('${res['total_unread']}') ?? 0;
        final archived = int.tryParse('${res['archived_count']}') ?? 0;
        context.read<AppDataProvider>().setMarketChatUnread(totalUnread);

        setState(() {
          _conversations = list.map((e) => Map<String, dynamic>.from(e as Map)).toList();
          _incomingRequests = parsedReqs;
          _friends = parsedFriends;
          _archivedCount = archived;
          _isLoading = false;
        });
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
                              Navigator.push(
                                context,
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
      floatingActionButton: FloatingActionButton(
        heroTag: 'fab_whatsapp_new_chat',
        backgroundColor: const Color(0xFF00A884), // WhatsApp Green
        tooltip: 'Chat Baru',
        onPressed: () => _showContactsSheet(context),
        child: const Icon(Icons.chat_rounded, color: Colors.white, size: 24),
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
                          CircleAvatar(
                            radius: 16,
                            backgroundColor: const Color(0xFF3B82F6),
                            child: Text(rInit, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 12)),
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
                _buildFilterChip('Diarsipkan${_archivedCount > 0 ? ' ($_archivedCount)' : ''}', 'archived'),
              ],
            ),
          ),
          const SizedBox(height: 10),

          // 3. Empty State or List
          if (filtered.isEmpty)
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
                      await Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => DirectChatScreen(
                            friendId: partnerId,
                            friendName: partnerName,
                            friendUsername: partnerUsername,
                            friendAvatar: partnerAvatar,
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
                          CircleAvatar(
                            radius: 24,
                            backgroundColor: const Color(0xFF2563EB),
                            child: Text(init, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 18)),
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
}
