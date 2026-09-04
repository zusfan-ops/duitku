import 'dart:convert';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';

import '../main.dart';
import 'update_checker_service.dart';

class LocalNotificationService {
  LocalNotificationService._();
  static final LocalNotificationService instance = LocalNotificationService._();

  final FlutterLocalNotificationsPlugin _plugin = FlutterLocalNotificationsPlugin();
  bool _isInitialized = false;

  static const String _channelId = 'duitku_broadcast_channel';
  static const String _channelName = 'Notifikasi & Pengumuman DuitKu';
  static const String _channelDesc = 'Menerima pemberitahuan pembaruan aplikasi dan pengumuman resmi';

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

    // Create Notification Channel for Android
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

    // Bukan update APK: buka link standar di browser jika merupakan URL valid
    final uri = Uri.tryParse(payload);
    if (uri != null && await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
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
