import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import '../firebase_options.dart';
import 'local_notification_service.dart';

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
      await LocalNotificationService.instance.showNotification(
        id: message.messageId.hashCode,
        title: title,
        body: body,
        payload: data['action_url']?.toString(),
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

      // Tangani pesan saat aplikasi aktif di FOREGROUND (terbuka di layar)
      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        final notif = message.notification;
        final data = message.data;

        final title = notif?.title ?? data['title']?.toString() ?? 'Pemberitahuan DuitKu';
        final body = notif?.body ?? data['message']?.toString() ?? data['body']?.toString() ?? '';
        final actionUrl = data['action_url']?.toString();

        LocalNotificationService.instance.showNotification(
          id: message.messageId.hashCode,
          title: title,
          body: body,
          payload: actionUrl,
          subText: 'DuitKu Push',
        );
      });

      _initialized = true;
    } catch (e) {
      debugPrint('FCM init error (menunggu konfigurasi Firebase aktif): $e');
    }
  }
}
