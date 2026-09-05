import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';

import '../main.dart';
import '../screens/marketplace/market_chat_screen.dart';
import 'update_checker_service.dart';

class LocalNotificationService {
  LocalNotificationService._();
  static final LocalNotificationService instance = LocalNotificationService._();

  final FlutterLocalNotificationsPlugin _plugin = FlutterLocalNotificationsPlugin();
  bool _isInitialized = false;

  static const String _channelId = 'duitku_broadcast_channel';
  static const String _channelName = 'Notifikasi & Pengumuman DuitKu';
  static const String _channelDesc = 'Menerima pemberitahuan pembaruan aplikasi dan pengumuman resmi';

  static const String _chatChannelId = 'duitku_chat_channel';
  static const String _chatChannelName = 'Pesan & Chat Marketplace';
  static const String _chatChannelDesc = 'Pemberitahuan pesan chat masuk langsung seperti WhatsApp';

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

    // Create Notification Channels for Android
    final androidPlugin = _plugin.resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();
    if (androidPlugin != null) {
      await androidPlugin.createNotificationChannel(
        const AndroidNotificationChannel(
          _channelId,
          _channelName,
          description: _channelDesc,
          importance: Importance.max,
          enableVibration: true,
          playSound: true,
        ),
      );

      // Channel khusus chat berprioritas maksimal (Heads-up pop-up + Suara + Getar layaknya WhatsApp)
      await androidPlugin.createNotificationChannel(
        const AndroidNotificationChannel(
          _chatChannelId,
          _chatChannelName,
          description: _chatChannelDesc,
          importance: Importance.max,
          enableVibration: true,
          playSound: true,
          showBadge: true,
        ),
      );
    }

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

    final androidDetails = AndroidNotificationDetails(
      _channelId,
      _channelName,
      channelDescription: _channelDesc,
      importance: Importance.max,
      priority: Priority.high,
      playSound: true,
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

    final androidDetails = AndroidNotificationDetails(
      _chatChannelId,
      _chatChannelName,
      channelDescription: _chatChannelDesc,
      importance: Importance.max,
      priority: Priority.max,
      playSound: true,
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

  /// Memproses payload chat marketplace dan langsung membuka room chat
  bool _handleChatPayload(String payload) {
    int listingId = 0;
    int buyerId = 0;
    String? title;
    String? senderName;

    if (payload.startsWith('{') && payload.endsWith('}')) {
      try {
        final data = jsonDecode(payload) as Map<String, dynamic>;
        if (data['type'] == 'marketplace_chat') {
          listingId = int.tryParse('${data['listing_id']}') ?? 0;
          buyerId = int.tryParse('${data['buyer_id']}') ?? 0;
          title = data['listing_title']?.toString() ?? data['title']?.toString();
          senderName = data['sender_name']?.toString();
        }
      } catch (_) {}
    }

    if (listingId == 0) {
      // Coba parse format URL internal: /marketplace?tab=chat&listing_id=123&buyer_id=456
      final uri = Uri.tryParse(payload.startsWith('/') ? 'app://duitku$payload' : payload);
      if (uri != null && (uri.path.contains('marketplace') || uri.queryParameters.containsKey('listing_id'))) {
        listingId = int.tryParse(uri.queryParameters['listing_id'] ?? '') ?? 0;
        buyerId = int.tryParse(uri.queryParameters['buyer_id'] ?? '') ?? 0;
      }
    }

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
    return false;
  }

  /// Notifikasi uji coba (test push notification)
  Future<void> showTestNotification() async {
    await showNotification(
      id: 99999,
      title: '🔔 Uji Coba Push Notifikasi',
      body: 'Push notifikasi sistem DuitKu telah aktif dan bekerja dengan normal!',
      subText: 'DuitKu System',
    );
  }
}
