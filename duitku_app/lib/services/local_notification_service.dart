import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';

import '../main.dart';
import '../screens/chat/direct_chat_screen.dart';
import '../screens/marketplace/market_chat_screen.dart';
import 'update_checker_service.dart';

class NotificationToneOption {
  final String key;
  final String title;
  final String subtitle;
  final IconData icon;

  const NotificationToneOption({
    required this.key,
    required this.title,
    required this.subtitle,
    required this.icon,
  });
}

class LocalNotificationService {
  LocalNotificationService._();
  static final LocalNotificationService instance = LocalNotificationService._();

  final FlutterLocalNotificationsPlugin _plugin = FlutterLocalNotificationsPlugin();
  bool _isInitialized = false;

  static const String _toneChannelName = 'com.duitku.duitku_app/notification_settings';
  static const MethodChannel _nativeChannel = MethodChannel(_toneChannelName);
  static const String _tonePrefKey = 'duitku_notification_tone';

  static const List<NotificationToneOption> toneOptions = [
    NotificationToneOption(
      key: 'default',
      title: 'Bawaan Sistem (Default)',
      subtitle: 'Nada dering notifikasi standar bawaan ponsel',
      icon: Icons.phone_android_rounded,
    ),
    NotificationToneOption(
      key: 'tone_kaching',
      title: 'DuitKu Kasir (Kaching!)',
      subtitle: 'Efek koin & kasir belanja yang renyah',
      icon: Icons.paid_rounded,
    ),
    NotificationToneOption(
      key: 'tone_chime',
      title: 'Chime Modern',
      subtitle: 'Dua nada lembut & berkelas (A5-E6)',
      icon: Icons.music_note_rounded,
    ),
    NotificationToneOption(
      key: 'tone_ding',
      title: 'Kristal Ding',
      subtitle: 'Lonceng satu ketukan jernih & elegan',
      icon: Icons.notifications_active_rounded,
    ),
    NotificationToneOption(
      key: 'tone_pop',
      title: 'Bubble Pop',
      subtitle: 'Gelembung ceria dan ringan',
      icon: Icons.bubble_chart_rounded,
    ),
    NotificationToneOption(
      key: 'tone_melody',
      title: 'Harmoni Ceria',
      subtitle: 'Tiga nada harmonis naik (C-E-G)',
      icon: Icons.auto_awesome_rounded,
    ),
  ];

  static const String _baseChannelId = 'duitku_broadcast_channel';
  static const String _baseChannelName = 'Notifikasi & Pengumuman DuitKu';
  static const String _channelDesc = 'Menerima pemberitahuan pembaruan aplikasi dan pengumuman resmi';

  static const String _baseChatChannelId = 'duitku_chat_channel';
  static const String _baseChatChannelName = 'Pesan & Chat Marketplace';
  static const String _chatChannelDesc = 'Pemberitahuan pesan chat masuk langsung seperti WhatsApp';

  Future<String> getSelectedToneKey() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_tonePrefKey) ?? 'default';
  }

  Future<void> setSelectedToneKey(String toneKey) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tonePrefKey, toneKey);
    await _ensureChannelsForTone(toneKey);
  }

  Future<void> previewTone(String toneKey) async {
    try {
      await _nativeChannel.invokeMethod('previewTone', {'tone': toneKey});
    } catch (_) {}
  }

  Future<void> stopTone() async {
    try {
      await _nativeChannel.invokeMethod('stopTone');
    } catch (_) {}
  }

  Future<void> openSystemNotificationSettings({String? channelId}) async {
    try {
      final activeTone = await getSelectedToneKey();
      final targetId = channelId ?? (activeTone == 'default' ? _baseChatChannelId : '${_baseChatChannelId}_$activeTone');
      await _nativeChannel.invokeMethod('openNotificationSettings', {
        'channelId': targetId,
      });
    } catch (_) {}
  }

  String _getBroadcastChannelId(String tone) {
    return tone == 'default' ? _baseChannelId : '${_baseChannelId}_$tone';
  }

  String _getChatChannelId(String tone) {
    return tone == 'default' ? _baseChatChannelId : '${_baseChatChannelId}_$tone';
  }

  Future<void> _ensureChannelsForTone(String tone) async {
    final androidPlugin = _plugin.resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();
    if (androidPlugin == null) return;

    final soundResource = tone == 'default' ? null : RawResourceAndroidNotificationSound(tone);
    final opt = toneOptions.firstWhere((o) => o.key == tone, orElse: () => toneOptions.first);

    await androidPlugin.createNotificationChannel(
      AndroidNotificationChannel(
        _getBroadcastChannelId(tone),
        tone == 'default' ? _baseChannelName : '$_baseChannelName (${opt.title})',
        description: _channelDesc,
        importance: Importance.max,
        enableVibration: true,
        playSound: true,
        sound: soundResource,
      ),
    );

    await androidPlugin.createNotificationChannel(
      AndroidNotificationChannel(
        _getChatChannelId(tone),
        tone == 'default' ? _baseChatChannelName : '$_baseChatChannelName (${opt.title})',
        description: _chatChannelDesc,
        importance: Importance.max,
        enableVibration: true,
        playSound: true,
        showBadge: true,
        sound: soundResource,
      ),
    );
  }

  Future<void> init() async {
    if (_isInitialized) return;

    const androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
    const initSettings = InitializationSettings(
      android: androidSettings,
    );

    await _plugin.initialize(
      settings: initSettings,
      onDidReceiveNotificationResponse: (NotificationResponse response) async {
        final payload = response.payload;
        if (payload != null && payload.isNotEmpty) {
          handleNotificationClick(payload);
        }
      },
    );

    // Periksa jika aplikasi dibuka dari notifikasi saat kondisi terminated (cold start)
    try {
      final launchDetails = await _plugin.getNotificationAppLaunchDetails();
      if (launchDetails?.didNotificationLaunchApp ?? false) {
        final payload = launchDetails?.notificationResponse?.payload;
        if (payload != null && payload.isNotEmpty) {
          handleNotificationClick(payload);
        }
      }
    } catch (_) {}

    // Request runtime permission for Android 13+ (POST_NOTIFICATIONS)
    await requestPermission();

    // Pastikan channel untuk nada yang sedang aktif terdaftar
    final activeTone = await getSelectedToneKey();
    await _ensureChannelsForTone(activeTone);

    _isInitialized = true;
  }

  Future<bool> requestPermission() async {
    final androidPlugin = _plugin.resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();
    if (androidPlugin != null) {
      final granted = await androidPlugin.requestNotificationsPermission();
      return granted ?? false;
    }
    return true;
  }

  Future<void> showNotification({
    required int id,
    required String title,
    required String body,
    String? payload,
    String? subText,
  }) async {
    await init();

    final tone = await getSelectedToneKey();
    await _ensureChannelsForTone(tone);
    final targetChannelId = _getBroadcastChannelId(tone);
    final sound = tone == 'default' ? null : RawResourceAndroidNotificationSound(tone);

    final androidDetails = AndroidNotificationDetails(
      targetChannelId,
      _baseChannelName,
      channelDescription: _channelDesc,
      importance: Importance.max,
      priority: Priority.high,
      playSound: true,
      sound: sound,
      enableVibration: true,
      subText: subText,
      styleInformation: BigTextStyleInformation(
        body,
        contentTitle: title,
        summaryText: subText,
      ),
      icon: '@mipmap/ic_launcher',
    );

    final details = NotificationDetails(android: androidDetails);
    await _plugin.show(
      id: id,
      title: title,
      body: body,
      notificationDetails: details,
      payload: payload,
    );
  }

  /// Menampilkan push notifikasi chat marketplace dengan prioritas tinggi & suara/getar seperti WhatsApp
  Future<void> showChatNotification({
    required int id,
    required String title,
    required String body,
    String? payload,
    String? subText,
  }) async {
    await init();

    final tone = await getSelectedToneKey();
    await _ensureChannelsForTone(tone);
    final targetChannelId = _getChatChannelId(tone);
    final sound = tone == 'default' ? null : RawResourceAndroidNotificationSound(tone);

    final androidDetails = AndroidNotificationDetails(
      targetChannelId,
      _baseChatChannelName,
      channelDescription: _chatChannelDesc,
      importance: Importance.max,
      priority: Priority.max,
      playSound: true,
      sound: sound,
      enableVibration: true,
      category: AndroidNotificationCategory.message,
      subText: subText ?? 'Chat Masuk',
      styleInformation: BigTextStyleInformation(
        body,
        contentTitle: title,
        summaryText: subText ?? 'Chat Masuk',
      ),
      icon: '@mipmap/ic_launcher',
    );

    final details = NotificationDetails(android: androidDetails);
    await _plugin.show(
      id: id,
      title: title,
      body: body,
      notificationDetails: details,
      payload: payload,
    );
  }

  /// Memeriksa notifikasi broadcast baru dari server dan memunculkan push notification HP
  Future<void> checkAndNotifyNewBroadcasts(List<dynamic> broadcasts) async {
    if (broadcasts.isEmpty) return;
    await init();

    final prefs = await SharedPreferences.getInstance();
    int lastSeenId = prefs.getInt('last_notified_broadcast_id') ?? 0;
    int maxId = lastSeenId;

    for (final item in broadcasts) {
      if (item is! Map<String, dynamic>) continue;
      final id = (item['id'] is int)
          ? item['id'] as int
          : (item['notif_id'] is int)
              ? item['notif_id'] as int
              : int.tryParse('${item['id'] ?? item['notif_id']}') ?? 0;

      if (id <= 0) continue;

      // Jika ID lebih besar dari yang pernah dinotifikasi ke sistem HP
      if (id > lastSeenId) {
        final title = item['title']?.toString() ?? 'Pemberitahuan DuitKu';
        final message = item['message']?.toString() ?? item['subtitle']?.toString() ?? '';
        final actionUrl = item['action_url']?.toString();
        final rawType = (item['broadcast_type'] ?? item['type'] ?? 'info').toString().toLowerCase();
        final type = rawType.toUpperCase();

        // Jika ini adalah rilis update APK, simpan payload JSON lengkap agar saat diklik langsung download in-app
        String notifPayload = actionUrl ?? '';
        if (rawType == 'update' || (actionUrl != null && (actionUrl.endsWith('.apk') || actionUrl.contains('.apk?')))) {
          notifPayload = jsonEncode({
            'type': 'update',
            'title': title,
            'message': message,
            'apk_url': actionUrl,
            'action_url': actionUrl,
            'version': item['version'] ?? '',
          });
        }

        await showNotification(
          id: id,
          title: title,
          body: message,
          payload: notifPayload,
          subText: 'DuitKu • $type',
        );

        if (id > maxId) {
          maxId = id;
        }
      }
    }

    if (maxId > lastSeenId) {
      await prefs.setInt('last_notified_broadcast_id', maxId);
    }
  }

  /// Tangani klik notifikasi: jika update APK maka unduh langsung di dalam aplikasi tanpa membuka browser
  Future<void> handleNotificationClick(String payload) async {
    bool isUpdate = false;
    String? apkUrl;
    String? title;
    String? message;
    String? version;

    if (payload.startsWith('{') && payload.endsWith('}')) {
      try {
        final data = jsonDecode(payload) as Map<String, dynamic>;
        if (data['type'] == 'update' ||
            data['apk_url'] != null ||
            (data['action_url']?.toString().toLowerCase().contains('.apk') ?? false)) {
          isUpdate = true;
          apkUrl = data['apk_url']?.toString() ?? data['action_url']?.toString();
          title = data['title']?.toString();
          message = data['message']?.toString();
          version = data['version']?.toString();
        }
      } catch (_) {}
    }

    if (!isUpdate) {
      final lower = payload.toLowerCase();
      if (lower.contains('.apk') || payload.startsWith('update:')) {
        isUpdate = true;
        apkUrl = payload.replaceFirst('update:', '').trim();
      }
    }

    // Jika ini adalah update APK, LANGSUNG BUKA IN-APP DOWNLOAD & INSTALL! (Jangan buka browser)
    if (isUpdate && apkUrl != null && apkUrl.isNotEmpty) {
      final context = rootNavigatorKey.currentContext;
      if (context != null && context.mounted) {
        UpdateCheckerService.instance.showUpdateFromNotification(
          context,
          title: title ?? 'Pembaruan Aplikasi DuitKu',
          message: message ?? 'Pembaruan aplikasi telah tersedia. Mengunduh file APK...',
          apkUrl: apkUrl,
          version: version,
          autoStart: true,
        );
      } else {
        UpdateCheckerService.instance.setPendingUpdate({
          'title': title,
          'message': message,
          'apk_url': apkUrl,
          'version': version,
        });
      }
      return;
    }

    // Bukan update APK: cek jika notifikasi adalah Chat Marketplace
    if (_handleChatPayload(payload)) {
      return;
    }

    // Buka link standar di browser jika merupakan URL valid
    final uri = Uri.tryParse(payload);
    if (uri != null && await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  /// Memproses payload chat (Direct Teman & Marketplace) dan langsung membuka room chat
  bool _handleChatPayload(String payload) {
    // 1. Coba decode JSON payload
    if (payload.startsWith('{') && payload.endsWith('}')) {
      try {
        final data = jsonDecode(payload) as Map<String, dynamic>;
        
        // A. Direct Chat Teman
        if (data['type'] == 'direct_chat') {
          final senderId = int.tryParse('${data['sender_id']}') ?? 0;
          final senderName = data['sender_name']?.toString() ?? 'Teman';
          final senderUsername = data['sender_username']?.toString() ?? '';
          final senderAvatar = data['sender_avatar']?.toString();

          if (senderId > 0) {
            final navContext = rootNavigatorKey.currentContext;
            if (navContext != null && navContext.mounted) {
              Navigator.push(
                navContext,
                MaterialPageRoute(
                  builder: (_) => DirectChatScreen(
                    friendId: senderId,
                    friendName: senderName,
                    friendUsername: senderUsername,
                    friendAvatar: senderAvatar,
                  ),
                ),
              );
              return true;
            }
          }
        }

        // B. Chat Marketplace
        if (data['type'] == 'marketplace_chat') {
          final listingId = int.tryParse('${data['listing_id']}') ?? 0;
          final buyerId = int.tryParse('${data['buyer_id']}') ?? 0;
          final title = data['listing_title']?.toString() ?? data['title']?.toString();
          final senderName = data['sender_name']?.toString();

          if (listingId > 0) {
            final navContext = rootNavigatorKey.currentContext;
            if (navContext != null && navContext.mounted) {
              Navigator.push(
                navContext,
                MaterialPageRoute(
                  builder: (_) => MarketChatScreen(
                    listingId: listingId,
                    buyerId: buyerId > 0 ? buyerId : null,
                    initialListingTitle: title,
                    targetUserName: senderName,
                  ),
                ),
              );
              return true;
            }
          }
        }
      } catch (_) {}
    }

    // 2. Coba parse format URL internal
    final uri = Uri.tryParse(payload.startsWith('/') ? 'app://duitku$payload' : payload);
    if (uri != null) {
      // Direct chat: /chat?direct_user=123
      if (uri.queryParameters.containsKey('direct_user')) {
        final dUid = int.tryParse(uri.queryParameters['direct_user'] ?? '') ?? 0;
        if (dUid > 0) {
          final navContext = rootNavigatorKey.currentContext;
          if (navContext != null && navContext.mounted) {
            Navigator.push(
              navContext,
              MaterialPageRoute(
                builder: (_) => DirectChatScreen(
                  friendId: dUid,
                  friendName: 'Teman',
                  friendUsername: '',
                ),
              ),
            );
            return true;
          }
        }
      }

      // Marketplace chat: /marketplace?tab=chat&listing_id=123&buyer_id=456
      if (uri.path.contains('marketplace') || uri.queryParameters.containsKey('listing_id')) {
        final listingId = int.tryParse(uri.queryParameters['listing_id'] ?? '') ?? 0;
        final buyerId = int.tryParse(uri.queryParameters['buyer_id'] ?? '') ?? 0;
        if (listingId > 0) {
          final navContext = rootNavigatorKey.currentContext;
          if (navContext != null && navContext.mounted) {
            Navigator.push(
              navContext,
              MaterialPageRoute(
                builder: (_) => MarketChatScreen(
                  listingId: listingId,
                  buyerId: buyerId > 0 ? buyerId : null,
                  initialListingTitle: 'Produk Marketplace',
                ),
              ),
            );
            return true;
          }
        }
      }
    }

    return false;
  }

  /// Notifikasi uji coba (test push notification)
  Future<void> showTestNotification({String? tone}) async {
    if (tone != null) {
      await setSelectedToneKey(tone);
    }
    final activeTone = tone ?? await getSelectedToneKey();
    final opt = toneOptions.firstWhere((o) => o.key == activeTone, orElse: () => toneOptions.first);
    await showNotification(
      id: 99999,
      title: '🔔 Uji Coba Nada Notifikasi',
      body: 'Nada "${opt.title}" berhasil diatur dan siap berbunyi!',
      subText: 'DuitKu Tone Test',
    );
  }
}
