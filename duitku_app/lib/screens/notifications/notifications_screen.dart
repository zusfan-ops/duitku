import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../models/app_notification.dart';
import '../../services/api_service.dart';
import '../../services/local_notification_service.dart';
import '../../theme.dart';

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
    if (notif.actionUrl != null && notif.actionUrl!.isNotEmpty) {
      final uri = Uri.tryParse(notif.actionUrl!);
      if (uri != null && await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      }
    }
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
    Color typeColor = Colors.blue;
    IconData typeIcon = Icons.info_outline_rounded;
    String typeLabel = 'INFO';

    switch (notif.type.toLowerCase()) {
      case 'announcement':
        typeColor = const Color(0xFF10B981);
        typeIcon = Icons.campaign_rounded;
        typeLabel = 'PENGUMUMAN';
        break;
      case 'promo':
        typeColor = const Color(0xFF8B5CF6);
        typeIcon = Icons.card_giftcard_rounded;
        typeLabel = 'PROMO';
        break;
      case 'warning':
        typeColor = const Color(0xFFEF4444);
        typeIcon = Icons.warning_amber_rounded;
        typeLabel = 'PERINGATAN';
        break;
      case 'system':
        typeColor = const Color(0xFFF59E0B);
        typeIcon = Icons.settings_rounded;
        typeLabel = 'SISTEM';
        break;
    }

    return InkWell(
      onTap: () => _handleAction(notif),
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: notif.isRead ? AppColors.card : typeColor.withValues(alpha: 0.04),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: notif.isPinned
                ? const Color(0xFFF59E0B)
                : (notif.isRead ? AppColors.border : typeColor.withValues(alpha: 0.3)),
            width: notif.isPinned ? 1.5 : 1,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.02),
              blurRadius: 6,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Icon
            Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                color: typeColor.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(typeIcon, color: typeColor, size: 20),
            ),
            const SizedBox(width: 12),
            // Body
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: typeColor.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          typeLabel,
                          style: TextStyle(
                            fontSize: 9.5,
                            fontWeight: FontWeight.w800,
                            color: typeColor,
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
                            'PINNED',
                            style: TextStyle(
                              fontSize: 9.5,
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
                            color: typeColor,
                            shape: BoxShape.circle,
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(
                    notif.title,
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: notif.isRead ? FontWeight.w600 : FontWeight.w800,
                      color: AppColors.textPrimary,
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
                          'Buka tautan →',
                          style: TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.w700,
                            color: typeColor,
                          ),
                        ),
                      ],
                    ),
                  ],
                  const SizedBox(height: 6),
                  Text(
                    notif.createdAt,
                    style: TextStyle(fontSize: 10.5, color: Colors.grey.shade500),
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
