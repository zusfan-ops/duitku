import 'dart:async';
import 'dart:convert';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import '../firebase_options.dart';
import '../main.dart';
import '../screens/marketplace/market_chat_screen.dart';
import 'local_notification_service.dart';
import 'session_manager.dart';
import 'update_checker_service.dart';

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  // Ditangani otomatis oleh Android jika membawa payload notification.
  // Jika membawa data-payload saat aplikasi tertutup / background:
  try {
    await Firebase.initializeApp(
      options: DefaultFirebaseOptions.currentPlatform,
    );
    final data = message.data;
    final title = message.notification?.title ?? data['title']?.toString() ?? 'Pemberitahuan DuitKu';
    final body = message.notification?.body ?? data['message']?.toString() ?? data['body']?.toString() ?? '';
    final rawType = (data['type'] ?? data['broadcast_type'] ?? 'info').toString().toLowerCase();
    final actionUrl = data['action_url']?.toString();

    // Jika ini adalah pesan chat marketplace masuk saat aplikasi tertutup
    if (rawType == 'marketplace_chat' || data['type'] == 'marketplace_chat') {
      final payloadJson = jsonEncode({
        'type': 'marketplace_chat',
        'listing_id': data['listing_id'],
        'buyer_id': data['buyer_id'],
        'seller_id': data['seller_id'],
        'sender_name': data['sender_name'],
        'title': title,
        'message': body,
        'action_url': actionUrl,
      });

      await LocalNotificationService.instance.showChatNotification(
        id: message.messageId.hashCode,
        title: title,
        body: body,
        payload: payloadJson,
        subText: 'Chat Baru',
      );
      return;
    }

    if (message.notification == null && data.isNotEmpty) {
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
  Completer<void>? _initCompleter;
  int? _currentSubscribedUserId;

  /// Inisialisasi Firebase Cloud Messaging
  Future<void> init() async {
    if (_initialized) return;
    if (_initCompleter != null) return _initCompleter!.future;
    _initCompleter = Completer<void>();

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
        provisional: false,
      );
      debugPrint('FCM AuthorizationStatus: ${settings.authorizationStatus}');

      // Subscribe ke topic broadcast umum agar notifikasi server langsung sampai ke seluruh pengguna
      try {
        await FirebaseMessaging.instance.subscribeToTopic('duitku_broadcasts');
        await FirebaseMessaging.instance.subscribeToTopic('all_users');
      } catch (e) {
        debugPrint('FCM subscribe topic error: $e');
      }

      // Auto-subscribe ke topic user jika sesi login sudah ada
      try {
        final user = await SessionManager.restore();
        if (user != null && user.id > 0) {
          await FirebaseMessaging.instance.subscribeToTopic('user_${user.id}');
          _currentSubscribedUserId = user.id;
          debugPrint('FCM auto-subscribed to topic: user_${user.id}');
        }
      } catch (e) {
        debugPrint('FCM restore user topic error: $e');
      }

      // Pantau perubahan token perangkat (re-subscribe jika token di-refresh)
      FirebaseMessaging.instance.onTokenRefresh.listen((token) async {
        debugPrint('FCM Token refreshed: $token');
        try {
          await FirebaseMessaging.instance.subscribeToTopic('duitku_broadcasts');
          await FirebaseMessaging.instance.subscribeToTopic('all_users');
          if (_currentSubscribedUserId != null && _currentSubscribedUserId! > 0) {
            await FirebaseMessaging.instance.subscribeToTopic('user_$_currentSubscribedUserId');
          }
        } catch (_) {}
      });

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

      // Tangani pesan saat aplikasi aktif di FOREGROUND (terbuka di layar pengguna)
      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        final notif = message.notification;
        final data = message.data;

        final title = notif?.title ?? data['title']?.toString() ?? 'Pemberitahuan DuitKu';
        final body = notif?.body ?? data['message']?.toString() ?? data['body']?.toString() ?? '';
        final rawType = (data['type'] ?? data['broadcast_type'] ?? 'info').toString().toLowerCase();
        final actionUrl = data['action_url']?.toString();
        final apkUrl = data['apk_url']?.toString() ?? actionUrl;

        // Notifikasi Chat Marketplace saat aplikasi sedang terbuka
        if (rawType == 'marketplace_chat' || data['type'] == 'marketplace_chat') {
          final payloadJson = jsonEncode({
            'type': 'marketplace_chat',
            'listing_id': data['listing_id'],
            'buyer_id': data['buyer_id'],
            'seller_id': data['seller_id'],
            'sender_name': data['sender_name'],
            'title': title,
            'message': body,
            'action_url': actionUrl,
          });

          LocalNotificationService.instance.showChatNotification(
            id: message.messageId.hashCode,
            title: title,
            body: body,
            payload: payloadJson,
            subText: 'Chat Masuk',
          );
          return;
        }

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
      _initCompleter?.complete();
    } catch (e) {
      _initCompleter?.complete();
      debugPrint('FCM init error: $e');
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

    // 1. Tangani klik notifikasi Chat Marketplace langsung ke Room Chat
    if (rawType == 'marketplace_chat' || data['type'] == 'marketplace_chat') {
      final listingId = int.tryParse('${data['listing_id']}') ?? 0;
      final buyerId = int.tryParse('${data['buyer_id']}') ?? 0;
      final senderName = data['sender_name']?.toString();
      final listingTitle = data['listing_title']?.toString() ?? title;

      if (listingId > 0) {
        final context = rootNavigatorKey.currentContext;
        if (context != null && context.mounted) {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => MarketChatScreen(
                listingId: listingId,
                buyerId: buyerId > 0 ? buyerId : null,
                initialListingTitle: listingTitle,
                targetUserName: senderName,
              ),
            ),
          );
          return;
        }
      }
    }

    // 2. Tangani Update APK
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

    // 3. Tangani Payload Umum
    if (actionUrl != null && actionUrl.isNotEmpty) {
      LocalNotificationService.instance.handleNotificationClick(actionUrl);
    }
  }

  /// Subscribe to user-specific topic (e.g. user_123) for personalized notifications like marketplace chats & orders
  Future<void> subscribeToUserTopic(int userId) async {
    if (userId <= 0) return;
    await init();
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
