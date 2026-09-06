import 'package:flutter/material.dart';

import '../../models/app_notification.dart';
import '../../services/api_service.dart';
import '../../services/local_notification_service.dart';
import '../../theme.dart';
import '../../utils/app_navigator.dart';
import 'notification_tone_sheet.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  List<AppNotification> _notifications = [];
  int _unreadCount = 0;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final res = await ApiService.instance.getNotifications();
      final list = ((res['notifications'] as List<dynamic>?) ?? [])
          .map((e) => AppNotification.fromJson(e as Map<String, dynamic>))
          .toList();
      final unread = (res['unread_count'] as int?) ?? 0;

      setState(() {
        _notifications = list;
        _unreadCount = unread;
        _loading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Gagal memuat notifikasi: $e';
        _loading = false;
      });
    }
  }

  Future<void> _markAsRead(AppNotification notif) async {
    if (notif.isRead) return;
    try {
      await ApiService.instance.markNotificationRead(notif.id);
      setState(() {
        notif.isRead = true;
        if (_unreadCount > 0) _unreadCount--;
      });
    } catch (_) {}
  }

  Future<void> _markAllAsRead() async {
    try {
      await ApiService.instance.markAllNotificationsRead();
      setState(() {
        for (var n in _notifications) {
          n.isRead = true;
        }
        _unreadCount = 0;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Semua notifikasi telah ditandai dibaca')),
        );
      }
    } catch (_) {}
  }

  Future<void> _handleAction(AppNotification notif) async {
    await _markAsRead(notif);
    if (!mounted) return;
    await AppNavigator.openTarget(
      context,
      actionUrl: notif.actionUrl,
      type: notif.type,
      title: notif.title,
      message: notif.message,
      notifId: notif.id,
      onRefresh: _loadNotifications,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Pemberitahuan & Pesan'),
        centerTitle: false,
        actions: [
          IconButton(
            tooltip: 'Atur Nada Notifikasi',
            icon: const Icon(Icons.music_note_rounded, size: 20),
            onPressed: () => NotificationToneSheet.show(context),
          ),
          IconButton(
            tooltip: 'Uji Push Notifikasi HP',
            icon: const Icon(Icons.notifications_active_outlined, size: 20),
            onPressed: () async {
              await LocalNotificationService.instance.showTestNotification();
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Push notifikasi sistem dikirim ke bilah status HP Anda!')),
                );
              }
            },
          ),
          if (_unreadCount > 0)
            TextButton.icon(
              onPressed: _markAllAsRead,
              icon: const Icon(Icons.done_all_rounded, size: 18, color: AppColors.primary),
              label: const Text('Tandai Dibaca', style: TextStyle(color: AppColors.primary, fontSize: 12)),
            ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline_rounded, size: 48, color: Colors.red),
                      const SizedBox(height: 12),
                      Text(_error!, style: const TextStyle(color: Colors.red)),
                      const SizedBox(height: 12),
                      ElevatedButton(
                        onPressed: _loadNotifications,
                        style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary),
                        child: const Text('Coba Lagi'),
                      ),
                    ],
                  ),
                )
              : _notifications.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                            width: 80,
                            height: 80,
                            decoration: BoxDecoration(
                              color: AppColors.card,
                              shape: BoxShape.circle,
                              border: Border.all(color: AppColors.border),
                            ),
                            child: const Icon(Icons.notifications_none_rounded, size: 40, color: AppColors.textSecondary),
                          ),
                          const SizedBox(height: 16),
                          const Text(
                            'Belum Ada Pemberitahuan',
                            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.textPrimary),
                          ),
                          const SizedBox(height: 6),
                          const Text(
                            'Pesan pengumuman dari admin akan muncul di sini.',
                            style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
                          ),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: _loadNotifications,
                      child: ListView.separated(
                        padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
                        itemCount: _notifications.length,
                        separatorBuilder: (_, _) => const SizedBox(height: 10),
                        itemBuilder: (context, idx) {
                          final notif = _notifications[idx];
                          return _buildNotifCard(notif);
                        },
                      ),
                    ),
    );
  }

  Widget _buildNotifCard(AppNotification notif) {
    final rawType = notif.type.toLowerCase();
    final aUrl = (notif.actionUrl ?? '').toLowerCase();
    final isUpdateNotif = rawType == 'update' || aUrl.endsWith('.apk') || aUrl.contains('.apk?');

    Gradient avatarGradient;
    IconData typeIcon;
    String typeLabel;
    Color primaryColor;
    Color badgeBg;
    Color badgeTextColor;

    if (rawType == 'status_comment' || aUrl.contains('status_id')) {
      avatarGradient = const LinearGradient(
        colors: [Color(0xFF833AB4), Color(0xFFFD1D1D), Color(0xFFF77737)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
      typeIcon = Icons.auto_awesome_rounded;
      typeLabel = '📸 KOMENTAR STATUS';
      primaryColor = const Color(0xFFE1306C);
      badgeBg = const Color(0xFFFCE7F3);
      badgeTextColor = const Color(0xFFBE185D);
    } else if (rawType == 'direct_chat' || aUrl.contains('direct_user')) {
      avatarGradient = const LinearGradient(
        colors: [Color(0xFF1D4ED8), Color(0xFF3B82F6)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
      typeIcon = Icons.chat_bubble_rounded;
      typeLabel = '💬 CHAT TEMAN';
      primaryColor = const Color(0xFF2563EB);
      badgeBg = const Color(0xFFDBEAFE);
      badgeTextColor = const Color(0xFF1D4ED8);
    } else if (rawType == 'marketplace_chat' || aUrl.contains('marketplace')) {
      avatarGradient = const LinearGradient(
        colors: [Color(0xFF047857), Color(0xFF10B981)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
      typeIcon = Icons.storefront_rounded;
      typeLabel = '🛍️ MARKETPLACE';
      primaryColor = const Color(0xFF059669);
      badgeBg = const Color(0xFFD1FAE5);
      badgeTextColor = const Color(0xFF047857);
    } else if (rawType == 'friend_request' || rawType == 'friend_accepted') {
      avatarGradient = const LinearGradient(
        colors: [Color(0xFF6D28D9), Color(0xFF8B5CF6)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
      typeIcon = Icons.person_add_alt_1_rounded;
      typeLabel = '🤝 PERTEMANAN';
      primaryColor = const Color(0xFF7C3AED);
      badgeBg = const Color(0xFFEDE9FE);
      badgeTextColor = const Color(0xFF6D28D9);
    } else if (isUpdateNotif) {
      avatarGradient = const LinearGradient(
        colors: [Color(0xFF047857), Color(0xFF10B981)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
      typeIcon = Icons.system_update_rounded;
      typeLabel = '🚀 UPDATE APK';
      primaryColor = const Color(0xFF059669);
      badgeBg = const Color(0xFFD1FAE5);
      badgeTextColor = const Color(0xFF065F46);
    } else if (rawType == 'bill') {
      avatarGradient = const LinearGradient(
        colors: [Color(0xFFD97706), Color(0xFFF59E0B)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
      typeIcon = Icons.receipt_long_rounded;
      typeLabel = '⏰ TAGIHAN';
      primaryColor = const Color(0xFFD97706);
      badgeBg = const Color(0xFFFEF3C7);
      badgeTextColor = const Color(0xFFB45309);
    } else if (rawType == 'debt') {
      avatarGradient = const LinearGradient(
        colors: [Color(0xFF991B1B), Color(0xFFDC2626)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
      typeIcon = Icons.handshake_rounded;
      typeLabel = '🤝 HUTANG PIUTANG';
      primaryColor = const Color(0xFFDC2626);
      badgeBg = const Color(0xFFFEE2E2);
      badgeTextColor = const Color(0xFF991B1B);
    } else if (rawType == 'announcement') {
      avatarGradient = const LinearGradient(
        colors: [Color(0xFF0D9488), Color(0xFF14B8A6)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
      typeIcon = Icons.campaign_rounded;
      typeLabel = '📢 PENGUMUMAN';
      primaryColor = const Color(0xFF0D9488);
      badgeBg = const Color(0xFFCCFBF1);
      badgeTextColor = const Color(0xFF0F766E);
    } else if (rawType == 'promo') {
      avatarGradient = const LinearGradient(
        colors: [Color(0xFF7C3AED), Color(0xFFA855F7)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
      typeIcon = Icons.card_giftcard_rounded;
      typeLabel = '🎁 PROMO';
      primaryColor = const Color(0xFF7C3AED);
      badgeBg = const Color(0xFFF3E8FF);
      badgeTextColor = const Color(0xFF6B21A8);
    } else if (rawType == 'warning') {
      avatarGradient = const LinearGradient(
        colors: [Color(0xFFDC2626), Color(0xFFEF4444)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
      typeIcon = Icons.warning_amber_rounded;
      typeLabel = '⚠️ PERINGATAN';
      primaryColor = const Color(0xFFDC2626);
      badgeBg = const Color(0xFFFEE2E2);
      badgeTextColor = const Color(0xFF991B1B);
    } else if (rawType == 'system') {
      avatarGradient = const LinearGradient(
        colors: [Color(0xFF475569), Color(0xFF64748B)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
      typeIcon = Icons.settings_rounded;
      typeLabel = '⚙️ SISTEM';
      primaryColor = const Color(0xFF475569);
      badgeBg = const Color(0xFFF1F5F9);
      badgeTextColor = const Color(0xFF334155);
    } else {
      avatarGradient = const LinearGradient(
        colors: [Color(0xFF0284C7), Color(0xFF38BDF8)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
      typeIcon = Icons.info_outline_rounded;
      typeLabel = 'ℹ️ INFORMASI';
      primaryColor = const Color(0xFF0284C7);
      badgeBg = const Color(0xFFE0F2FE);
      badgeTextColor = const Color(0xFF0369A1);
    }

    return InkWell(
      onTap: () => _handleAction(notif),
      borderRadius: BorderRadius.circular(18),
      child: Container(
        decoration: BoxDecoration(
          color: notif.isRead ? Colors.white : primaryColor.withValues(alpha: 0.035),
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: notif.isPinned
                ? const Color(0xFFF59E0B)
                : (notif.isRead ? AppColors.border.withValues(alpha: 0.8) : primaryColor.withValues(alpha: 0.35)),
            width: notif.isPinned ? 1.5 : (notif.isRead ? 1 : 1.2),
          ),
          boxShadow: [
            BoxShadow(
              color: primaryColor.withValues(alpha: notif.isRead ? 0.03 : 0.08),
              blurRadius: 10,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(18),
          child: IntrinsicHeight(
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Garis aksen tepi kiri berwarna
                Container(
                  width: 4.5,
                  decoration: BoxDecoration(
                    gradient: notif.isRead
                        ? LinearGradient(colors: [primaryColor.withValues(alpha: 0.5), primaryColor.withValues(alpha: 0.3)])
                        : avatarGradient,
                  ),
                ),
                Expanded(
                  child: Padding(
                    padding: const EdgeInsets.all(14),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Avatar Ikon Gradien Mewah
                        Container(
                          width: 44,
                          height: 44,
                          decoration: BoxDecoration(
                            gradient: avatarGradient,
                            borderRadius: BorderRadius.circular(14),
                            boxShadow: [
                              BoxShadow(
                                color: primaryColor.withValues(alpha: 0.25),
                                blurRadius: 6,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: Center(
                            child: Icon(typeIcon, color: Colors.white, size: 22),
                          ),
                        ),
                        const SizedBox(width: 12),
                        // Isi
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                                    decoration: BoxDecoration(
                                      color: badgeBg,
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: Text(
                                      typeLabel,
                                      style: TextStyle(
                                        fontSize: 9,
                                        fontWeight: FontWeight.w800,
                                        color: badgeTextColor,
                                        letterSpacing: 0.3,
                                      ),
                                    ),
                                  ),
                                  if (notif.isPinned) ...[
                                    const SizedBox(width: 6),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFF59E0B).withValues(alpha: 0.15),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: const Text(
                                        '📌 PINNED',
                                        style: TextStyle(
                                          fontSize: 9,
                                          fontWeight: FontWeight.w800,
                                          color: Color(0xFFB45309),
                                        ),
                                      ),
                                    ),
                                  ],
                                  const Spacer(),
                                  if (!notif.isRead)
                                    Container(
                                      width: 8,
                                      height: 8,
                                      decoration: BoxDecoration(
                                        color: primaryColor,
                                        shape: BoxShape.circle,
                                        boxShadow: [
                                          BoxShadow(
                                            color: primaryColor.withValues(alpha: 0.5),
                                            blurRadius: 4,
                                            spreadRadius: 1,
                                          ),
                                        ],
                                      ),
                                    ),
                                ],
                              ),
                              const SizedBox(height: 7),
                              Text(
                                notif.title,
                                style: TextStyle(
                                  fontSize: 14,
                                  fontWeight: notif.isRead ? FontWeight.w600 : FontWeight.w800,
                                  color: AppColors.textPrimary,
                                  height: 1.25,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                notif.message,
                                style: const TextStyle(
                                  fontSize: 12.5,
                                  color: AppColors.textSecondary,
                                  height: 1.4,
                                ),
                              ),
                              if (notif.actionUrl != null && notif.actionUrl!.isNotEmpty) ...[
                                const SizedBox(height: 8),
                                Row(
                                  children: [
                                    Text(
                                      isUpdateNotif
                                          ? 'Perbarui Langsung di Aplikasi →'
                                          : (aUrl.contains('status_id')
                                              ? 'Lihat Status Teman →'
                                              : (aUrl.contains('direct_user')
                                                  ? 'Balas Chat Teman →'
                                                  : 'Buka tautan →')),
                                      style: TextStyle(
                                        fontSize: 11.5,
                                        fontWeight: FontWeight.w700,
                                        color: primaryColor,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                              const SizedBox(height: 6),
                              Row(
                                children: [
                                  Icon(Icons.access_time_rounded, size: 12, color: Colors.grey.shade400),
                                  const SizedBox(width: 4),
                                  Text(
                                    notif.createdAt,
                                    style: TextStyle(fontSize: 10.5, color: Colors.grey.shade500),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
