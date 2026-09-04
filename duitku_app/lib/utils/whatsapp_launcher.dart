import 'dart:developer';
import 'package:url_launcher/url_launcher.dart';

class WhatsAppLauncher {
  WhatsAppLauncher._();

  /// Clean & format Indonesian/international phone numbers into pure digits with country code
  static String formatPhoneNumber(String phone) {
    String clean = phone.replaceAll(RegExp(r'[^0-9]'), '');
    if (clean.startsWith('0')) {
      clean = '62${clean.substring(1)}';
    } else if (clean.startsWith('8')) {
      clean = '62$clean';
    }
    return clean;
  }

  /// Launch WhatsApp directly into native app without triggering browser download pages
  static Future<bool> launch({
    required String phone,
    String text = '',
  }) async {
    final cleanPhone = formatPhoneNumber(phone);
    final encodedText = Uri.encodeComponent(text);

    // 1. Native app custom scheme (bypasses browser completely)
    final nativeUri = Uri.parse(
      'whatsapp://send?phone=$cleanPhone${encodedText.isNotEmpty ? '&text=$encodedText' : ''}',
    );

    // 2. Official web deep-link API (triggers app link intent)
    final apiUri = Uri.parse(
      'https://api.whatsapp.com/send?phone=$cleanPhone${encodedText.isNotEmpty ? '&text=$encodedText' : ''}',
    );

    // 3. Short wa.me link
    final waMeUri = Uri.parse(
      'https://wa.me/$cleanPhone${encodedText.isNotEmpty ? '?text=$encodedText' : ''}',
    );

    // Try 1: Native URI scheme via externalApplication
    try {
      if (await canLaunchUrl(nativeUri)) {
        final ok = await launchUrl(nativeUri, mode: LaunchMode.externalApplication);
        if (ok) return true;
      }
    } catch (e) {
      log('WhatsAppLauncher: nativeUri canLaunchUrl check failed: $e');
    }

    // Try 2: Direct launch without canLaunchUrl (handles strict ROM package visibility)
    try {
      final ok = await launchUrl(nativeUri, mode: LaunchMode.externalApplication);
      if (ok) return true;
    } catch (e) {
      log('WhatsAppLauncher: direct nativeUri launch failed: $e');
    }

    // Try 3: api.whatsapp.com
    try {
      if (await canLaunchUrl(apiUri)) {
        final ok = await launchUrl(apiUri, mode: LaunchMode.externalApplication);
        if (ok) return true;
      }
    } catch (e) {
      log('WhatsAppLauncher: apiUri launch failed: $e');
    }

    // Try 4: wa.me
    try {
      if (await canLaunchUrl(waMeUri)) {
        final ok = await launchUrl(waMeUri, mode: LaunchMode.externalApplication);
        if (ok) return true;
      }
    } catch (e) {
      log('WhatsAppLauncher: waMeUri launch failed: $e');
    }

    return false;
  }
}
