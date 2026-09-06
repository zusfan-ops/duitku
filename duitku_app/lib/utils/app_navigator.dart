import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../services/api_service.dart';
import '../services/update_checker_service.dart';
import '../screens/belanja/belanja_screen.dart';
import '../screens/bills_screen.dart';
import '../screens/chat/direct_chat_screen.dart';
import '../screens/debt_screen.dart';
import '../screens/marketplace/market_chat_screen.dart';
import '../screens/marketplace/market_conversations_screen.dart';
import '../screens/marketplace/market_screen.dart';
import '../screens/notifications/notifications_screen.dart';
import '../screens/recurring/recurring_screen.dart';
import '../screens/savings/savings_screen.dart';
import '../screens/todo/todo_list_screen.dart';
import '../screens/vehicle/vehicle_screen.dart';

class AppNavigator {
  /// Membuka target dari notifikasi atau tautan aksi (internal app routing & external links)
  static Future<bool> openTarget(
    BuildContext context, {
    required String? actionUrl,
    String? type,
    String? title,
    String? message,
    int? notifId,
    VoidCallback? onRefresh,
  }) async {
    // 1. Tandai sudah dibaca jika notifId ada
    if (notifId != null && notifId > 0) {
      try {
        await ApiService.instance.markNotificationRead(notifId);
      } catch (_) {}
    }
    if (!context.mounted) return false;

    final rawUrl = (actionUrl ?? '').trim();
    final lowerUrl = rawUrl.toLowerCase();
    final lowerType = (type ?? '').toLowerCase();

    // 2. Pembaruan Aplikasi APK In-App
    final isUpdate = lowerType == 'update' ||
        lowerUrl.endsWith('.apk') ||
        lowerUrl.contains('.apk?') ||
        lowerUrl.contains('/releases/download/');
    if (isUpdate && rawUrl.isNotEmpty) {
      UpdateCheckerService.instance.showUpdateFromNotification(
        context,
        title: title ?? 'Pembaruan Aplikasi DuitKu',
        message: message ?? 'Pembaruan aplikasi telah tersedia.',
        apkUrl: rawUrl,
        autoStart: true,
      );
      return true;
    }

    // 3. Parse URI (mendukung relative path seperti /chat?status_id=123)
    final uri = Uri.tryParse(rawUrl.startsWith('/') ? 'app://duitku$rawUrl' : rawUrl);

    // A. Komentar Status (/chat?status_id=123 atau type status_comment)
    int statusId = 0;
    if (uri != null && uri.queryParameters.containsKey('status_id')) {
      statusId = int.tryParse(uri.queryParameters['status_id'] ?? '') ?? 0;
    }
    if (statusId <= 0 && lowerType == 'status_comment' && uri != null) {
      statusId = int.tryParse(uri.queryParameters['id'] ?? '') ?? 0;
    }
    if (statusId > 0) {
      await Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => MarketConversationsScreen(
            initialStatusId: statusId,
          ),
        ),
      );
      onRefresh?.call();
      return true;
    }

    // B. Direct Chat Teman (/chat?direct_user=123 atau direct_chat)
    int directUserId = 0;
    if (uri != null && uri.queryParameters.containsKey('direct_user')) {
      directUserId = int.tryParse(uri.queryParameters['direct_user'] ?? '') ?? 0;
    }
    if (directUserId <= 0 && lowerType == 'direct_chat' && uri != null) {
      directUserId = int.tryParse(uri.queryParameters['sender_id'] ?? uri.queryParameters['user_id'] ?? '') ?? 0;
    }
    if (directUserId > 0) {
      String cleanName = (title ?? 'Teman').replaceAll('💬', '').trim();
      if (cleanName.isEmpty) cleanName = 'Teman';
      await Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => DirectChatScreen(
            friendId: directUserId,
            friendName: cleanName,
            friendUsername: '',
          ),
        ),
      );
      onRefresh?.call();
      return true;
    }

    // C. Chat Marketplace (/marketplace?tab=chat&listing_id=123&buyer_id=456)
    if (uri != null && (uri.path.contains('marketplace') || uri.queryParameters.containsKey('listing_id'))) {
      final listingId = int.tryParse(uri.queryParameters['listing_id'] ?? '') ?? 0;
      final buyerId = int.tryParse(uri.queryParameters['buyer_id'] ?? '') ?? 0;
      if (listingId > 0) {
        await Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => MarketChatScreen(
              listingId: listingId,
              buyerId: buyerId > 0 ? buyerId : null,
              initialListingTitle: title ?? 'Produk Marketplace',
            ),
          ),
        );
        onRefresh?.call();
        return true;
      }
    }

    // D. Tab Chat / Teman (/chat atau /chat?tab=friends)
    if (uri != null && uri.path.contains('chat')) {
      final tab = uri.queryParameters['tab'];
      await Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => MarketConversationsScreen(
            initialTab: tab,
          ),
        ),
      );
      onRefresh?.call();
      return true;
    }

    // E. Rute Fitur Internal DuitKu
    if (uri != null) {
      final path = uri.path.toLowerCase();
      if (path.contains('belanja') || lowerType == 'belanja') {
        await Navigator.push(context, MaterialPageRoute(builder: (_) => const BelanjaScreen()));
        onRefresh?.call();
        return true;
      }
      if (path.contains('bill') || lowerType == 'bill') {
        await Navigator.push(context, MaterialPageRoute(builder: (_) => const BillsScreen()));
        onRefresh?.call();
        return true;
      }
      if (path.contains('debt') || lowerType == 'debt') {
        await Navigator.push(context, MaterialPageRoute(builder: (_) => const DebtScreen()));
        onRefresh?.call();
        return true;
      }
      if (path.contains('savings') || path.contains('tabungan') || lowerType == 'savings') {
        await Navigator.push(context, MaterialPageRoute(builder: (_) => const SavingsScreen()));
        onRefresh?.call();
        return true;
      }
      if (path.contains('recurring') || lowerType == 'recurring') {
        await Navigator.push(context, MaterialPageRoute(builder: (_) => const RecurringScreen()));
        onRefresh?.call();
        return true;
      }
      if (path.contains('vehicle') || path.contains('pajak') || lowerType == 'tax') {
        await Navigator.push(context, MaterialPageRoute(builder: (_) => const VehicleScreen()));
        onRefresh?.call();
        return true;
      }
      if (path.contains('todo') || lowerType == 'todo') {
        await Navigator.push(context, MaterialPageRoute(builder: (_) => const TodoListScreen()));
        onRefresh?.call();
        return true;
      }
      if (path.contains('marketplace') || lowerType == 'marketplace') {
        await Navigator.push(context, MaterialPageRoute(builder: (_) => const MarketScreen()));
        onRefresh?.call();
        return true;
      }
    }

    // F. External URL (http / https)
    if (rawUrl.startsWith('http://') || rawUrl.startsWith('https://')) {
      final extUri = Uri.tryParse(rawUrl);
      if (extUri != null && await canLaunchUrl(extUri)) {
        await launchUrl(extUri, mode: LaunchMode.externalApplication);
        onRefresh?.call();
        return true;
      }
    }

    if (!context.mounted) return false;

    // G. Fallback: Buka Halaman Notifikasi jika aksi belum dikenal
    await Navigator.push(context, MaterialPageRoute(builder: (_) => const NotificationsScreen()));
    onRefresh?.call();
    return true;
  }
}
