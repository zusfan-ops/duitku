import 'dart:convert';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import '../firebase_options.dart';
import '../main.dart';
import 'local_notification_service.dart';
import 'update_checker_service.dart';

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  // Ditangani otomatis oleh Android jika membawa payload notification.
  // Jika membawa data-payload saat aplikasi tertutup:
  try {
    await Firebase.initializeApp(
      options: DefaultFirebaseOptions.currentPlatform,
    );
    final data = message.data;
    if (message.notification == null && data.isNotEmpty) {
      final title = data['title']?.toString() ?? 'Pemberitahuan DuitKu';
      final body = data['message']?.toString() ?? data['body']?.toString() ?? '';
      final rawType = (data['type'] ?? data['broadcast_type'] ?? 'info').toString().toLowerCase();
      final actionUrl = data['action_url']?.toString();
      final apkUrl = data['apk_url']?.toString() ?? actionUrl;

      String notifPayload = actionUrl ?? '';
      if (rawType == 'update' || (apkUrl != null && apkUrl.contains('.apk'))) {
        notifPayload = jsonEncode({
          'type': 'update',
          'title': title,
          'message': body,
          'apk_url': apkUrl,
          'action_url': actionUrl,
          'version': data['version'] ?? '',
        });
      }

      await LocalNotificationService.instance.showNotification(
        id: message.messageId.hashCode,
        title: title,
        body: body,
        payload: notifPayload,
        subText: 'DuitKu Push',
      );
    }
  } catch (e) {
    debugPrint('FCM background handler error: $e');
  }
}

class FcmService {
  FcmService._();
  static final FcmService instance = FcmService._();

  bool _initialized = false;

  /// Inisialisasi Firebase Cloud Messaging
  Future<void> init() async {
    if (_initialized) return;

    try {
      await Firebase.initializeApp(
        options: DefaultFirebaseOptions.currentPlatform,
      );
      FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

      // Request permission untuk Android 13+ dan iOS
      final settings = await FirebaseMessaging.instance.requestPermission(
        alert: true,
        badge: true,
        sound: true,
      );
      debugPrint('FCM AuthorizationStatus: ${settings.authorizationStatus}');

      // Subscribe ke topic broadcast umum agar notifikasi server langsung sampai ke seluruh pengguna
      try {
        await FirebaseMessaging.instance.subscribeToTopic('duitku_broadcasts');
        await FirebaseMessaging.instance.subscribeToTopic('all_users');
      } catch (e) {
        debugPrint('FCM subscribe topic error: $e');
      }

      // Ambil token perangkat (bisa dikirim ke server jika perlu kirim ke user spesifik)
      try {
        final token = await FirebaseMessaging.instance.getToken();
        debugPrint('FCM Token: $token');
      } catch (e) {
        debugPrint('FCM getToken error: $e');
      }

      // Tangani pesan saat aplikasi dibuka dari background (user tap push notification)
      FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
        _handleRemoteMessageClick(message);
      });

      // Tangani pesan saat aplikasi baru dibuka dari kondisi mati total (cold start)
      try {
        final initialMessage = await FirebaseMessaging.instance.getInitialMessage();
        if (initialMessage != null) {
          _handleRemoteMessageClick(initialMessage);
        }
      } catch (e) {
        debugPrint('FCM getInitialMessage error: $e');
      }

      // Tangani pesan saat aplikasi aktif di FOREGROUND (terbuka di layar)
      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        final notif = message.notification;
        final data = message.data;

        final title = notif?.title ?? data['title']?.toString() ?? 'Pemberitahuan DuitKu';
        final body = notif?.body ?? data['message']?.toString() ?? data['body']?.toString() ?? '';
        final rawType = (data['type'] ?? data['broadcast_type'] ?? 'info').toString().toLowerCase();
        final actionUrl = data['action_url']?.toString();
        final apkUrl = data['apk_url']?.toString() ?? actionUrl;

        String notifPayload = actionUrl ?? '';
        if (rawType == 'update' || (apkUrl != null && apkUrl.contains('.apk'))) {
          notifPayload = jsonEncode({
            'type': 'update',
            'title': title,
            'message': body,
            'apk_url': apkUrl,
            'action_url': actionUrl,
            'version': data['version'] ?? '',
          });
        }

        LocalNotificationService.instance.showNotification(
          id: message.messageId.hashCode,
          title: title,
          body: body,
          payload: notifPayload,
          subText: 'DuitKu Push',
        );
      });

      _initialized = true;
    } catch (e) {
      debugPrint('FCM init error (menunggu konfigurasi Firebase aktif): $e');
    }
  }

  /// Penanganan saat pengguna mengklik notifikasi FCM dari bilah status Android
  void _handleRemoteMessageClick(RemoteMessage message) {
    final data = message.data;
    final notif = message.notification;
    final title = notif?.title ?? data['title']?.toString() ?? 'Pemberitahuan DuitKu';
    final body = notif?.body ?? data['message']?.toString() ?? data['body']?.toString() ?? '';
    final rawType = (data['type'] ?? data['broadcast_type'] ?? '').toString().toLowerCase();
    final actionUrl = data['action_url']?.toString();
    final apkUrl = data['apk_url']?.toString() ?? actionUrl;
    final version = data['version']?.toString();

    final isUpdate = rawType == 'update' || (apkUrl != null && (apkUrl.endsWith('.apk') || apkUrl.contains('.apk?')));

    if (isUpdate && apkUrl != null && apkUrl.isNotEmpty) {
      final context = rootNavigatorKey.currentContext;
      if (context != null && context.mounted) {
        UpdateCheckerService.instance.showUpdateFromNotification(
          context,
          title: title,
          message: body,
          apkUrl: apkUrl,
          version: version,
          autoStart: true,
        );
      } else {
        UpdateCheckerService.instance.setPendingUpdate({
          'title': title,
          'message': body,
          'apk_url': apkUrl,
          'version': version,
        });
      }
      return;
    }

    if (actionUrl != null && actionUrl.isNotEmpty) {
      LocalNotificationService.instance.handleNotificationClick(actionUrl);
    }
  }

  int? _currentSubscribedUserId;

  /// Subscribe to user-specific topic (e.g. user_123) for personalized notifications like marketplace orders
  Future<void> subscribeToUserTopic(int userId) async {
    if (userId <= 0) return;
    try {
      if (_currentSubscribedUserId != null && _currentSubscribedUserId != userId) {
        await unsubscribeFromUserTopic(_currentSubscribedUserId!);
      }
      await FirebaseMessaging.instance.subscribeToTopic('user_$userId');
      _currentSubscribedUserId = userId;
      debugPrint('FCM subscribed to topic: user_$userId');
    } catch (e) {
      debugPrint('FCM subscribeToUserTopic error: $e');
    }
  }

  Future<void> unsubscribeFromUserTopic(int userId) async {
    if (userId <= 0) return;
    try {
      await FirebaseMessaging.instance.unsubscribeFromTopic('user_$userId');
      if (_currentSubscribedUserId == userId) {
        _currentSubscribedUserId = null;
      }
      debugPrint('FCM unsubscribed from topic: user_$userId');
    } catch (e) {
      debugPrint('FCM unsubscribeFromUserTopic error: $e');
    }
  }
}
