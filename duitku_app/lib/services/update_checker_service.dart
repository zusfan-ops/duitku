import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:package_info_plus/package_info_plus.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';

import '../widgets/update_dialog.dart';

/// Model representasi data Release dari GitHub API
class GitHubRelease {
  final String tagName;
  final String name;
  final String body;
  final DateTime publishedAt;
  final String htmlUrl;
  final String? apkDownloadUrl;
  final String? apkFileName;
  final int? apkSizeBytes;

  GitHubRelease({
    required this.tagName,
    required this.name,
    required this.body,
    required this.publishedAt,
    required this.htmlUrl,
    this.apkDownloadUrl,
    this.apkFileName,
    this.apkSizeBytes,
  });

  factory GitHubRelease.fromJson(Map<String, dynamic> json) {
    final assets = (json['assets'] as List<dynamic>?) ?? [];
    String? apkUrl;
    String? apkName;
    int? apkSize;

    // Prioritaskan arsitektur arm64-v8a, lalu universal apk, atau apk apa pun yang ditemukan
    for (final asset in assets) {
      if (asset is Map<String, dynamic>) {
        final name = (asset['name'] ?? '').toString().toLowerCase();
        final url = (asset['browser_download_url'] ?? '').toString();
        final size = asset['size'] as int?;

        if (name.endsWith('.apk')) {
          if (name.contains('arm64-v8a') || apkUrl == null) {
            apkUrl = url;
            apkName = asset['name'] as String?;
            apkSize = size;
          }
        }
      }
    }

    return GitHubRelease(
      tagName: (json['tag_name'] ?? 'v1.2.1') as String,
      name: (json['name'] ?? json['tag_name'] ?? 'Pembaruan Aplikasi') as String,
      body: (json['body'] ?? '') as String,
      publishedAt: DateTime.tryParse(json['published_at'] ?? '') ?? DateTime.now(),
      htmlUrl: (json['html_url'] ?? 'https://github.com/zusfan-ops/duitku/releases') as String,
      apkDownloadUrl: apkUrl,
      apkFileName: apkName,
      apkSizeBytes: apkSize,
    );
  }

  String get versionClean {
    return tagName.startsWith('v') || tagName.startsWith('V')
        ? tagName.substring(1)
        : tagName;
  }
}

/// Service untuk memeriksa rilis APK baru di GitHub Releases secara otomatis
class UpdateCheckerService {
  UpdateCheckerService._();
  static final UpdateCheckerService instance = UpdateCheckerService._();

  /// Versi aplikasi fallback jika package_info belum termuat
  static const String fallbackVersion = '1.2.32';
  String _cachedVersion = fallbackVersion;

  /// Versi aplikasi saat ini
  String get currentVersion => _cachedVersion;

  /// Repositori GitHub resmi DuitKu
  static const String _repo = 'zusfan-ops/duitku';

  static const String _prefDismissedTag = 'duitku_dismissed_update_tag';
  static const String _prefDismissedTime = 'duitku_dismissed_update_time';

  DateTime? _lastCheckedAt;
  GitHubRelease? _latestRelease;
  bool _isChecking = false;
  bool _hasPromptedThisSession = false;

  /// Ambil versi aplikasi secara dinamis dari package runtime
  Future<String> getAppVersion() async {
    try {
      final info = await PackageInfo.fromPlatform();
      if (info.version.isNotEmpty) {
        _cachedVersion = info.version;
      }
    } catch (e) {
      debugPrint('UpdateChecker package_info error: $e');
    }
    return _cachedVersion;
  }

  /// Memeriksa apakah ada versi baru di GitHub Releases
  Future<GitHubRelease?> checkLatestRelease({bool force = false}) async {
    await getAppVersion();

    // Jika baru saja diperiksa dalam 5 menit terakhir dan tidak force, gunakan cache
    if (!force && _lastCheckedAt != null && _latestRelease != null) {
      if (DateTime.now().difference(_lastCheckedAt!).inMinutes < 5) {
        return _latestRelease;
      }
    }

    if (_isChecking) return _latestRelease;
    _isChecking = true;

    try {
      final uri = Uri.parse('https://api.github.com/repos/$_repo/releases/latest');
      final response = await http.get(uri, headers: {
        'Accept': 'application/vnd.github.v3+json',
      }).timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;
        final release = GitHubRelease.fromJson(data);

        if (_isNewerVersion(release.versionClean, _cachedVersion)) {
          _latestRelease = release;
          _lastCheckedAt = DateTime.now();
          return release;
        }
      }
    } catch (e) {
      debugPrint('UpdateChecker error on repo $_repo: $e');
    } finally {
      _isChecking = false;
    }

    _lastCheckedAt = DateTime.now();
    return null;
  }

  /// Membandingkan dua string versi semantic (misal: "1.1.0" > "1.0.0")
  bool _isNewerVersion(String newVer, String currentVer) {
    try {
      final newParts = newVer.split('.').map((e) => int.tryParse(e.replaceAll(RegExp(r'[^0-9]'), '')) ?? 0).toList();
      final curParts = currentVer.split('.').map((e) => int.tryParse(e.replaceAll(RegExp(r'[^0-9]'), '')) ?? 0).toList();

      while (newParts.length < 3) {
        newParts.add(0);
      }
      while (curParts.length < 3) {
        curParts.add(0);
      }

      for (int i = 0; i < 3; i++) {
        if (newParts[i] > curParts[i]) return true;
        if (newParts[i] < curParts[i]) return false;
      }
    } catch (_) {}
    return false;
  }

  /// Abaikan rilis versi tertentu saat startup otomatis
  Future<void> dismissRelease(String tagName) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_prefDismissedTag, tagName);
      await prefs.setInt(_prefDismissedTime, DateTime.now().millisecondsSinceEpoch);
    } catch (_) {}
  }

  /// Memeriksa pembaruan dan menampilkan dialog jika tersedia
  Future<void> checkAndShowUpdateDialog(
    BuildContext context, {
    bool isManualCheck = false,
  }) async {
    if (kIsWeb && !isManualCheck) {
      // Pada Web browser, update APK tidak perlu diprompt otomatis saat startup
      return;
    }

    // Hindari memunculkan pop-up lebih dari sekali dalam satu sesi aplikasi yang sama
    if (!isManualCheck && _hasPromptedThisSession) {
      return;
    }

    if (isManualCheck) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Row(
            children: [
              SizedBox(
                width: 18,
                height: 18,
                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
              ),
              SizedBox(width: 12),
              Text('Memeriksa rilis terbaru di GitHub...'),
            ],
          ),
          duration: Duration(seconds: 2),
        ),
      );
    }

    final release = await checkLatestRelease(force: isManualCheck);

    if (!context.mounted) return;

    if (release != null) {
      // Cek apakah pengguna sebelumnya sudah mengabaikan / menutup dialog untuk rilis ini (hanya untuk startup check otomatis)
      if (!isManualCheck) {
        try {
          final prefs = await SharedPreferences.getInstance();
          final dismissed = prefs.getString(_prefDismissedTag);
          final dismissedTime = prefs.getInt(_prefDismissedTime) ?? 0;
          final diffHours = DateTime.now().difference(DateTime.fromMillisecondsSinceEpoch(dismissedTime)).inHours;

          // Jika versi yang sama sudah pernah diabaikan ATAU sudah di-dismiss dalam 24 jam terakhir, jangan tampilkan lagi
          if (dismissed == release.tagName || diffHours < 24) {
            return;
          }
        } catch (_) {}
      }

      if (!context.mounted) return;

      _hasPromptedThisSession = true;

      // Tampilkan dialog pembaruan dan tunggu hingga dialog ditutup
      await showDialog(
        context: context,
        barrierDismissible: true,
        builder: (ctx) => UpdateDialog(
          release: release,
          currentVersion: currentVersion,
        ),
      );

      // Begitu dialog ditutup oleh pengguna (baik via "Nanti Saja", tombol back HP, tap di luar modal, maupun klik download),
      // otomatis simpan status dismiss agar tidak terus-menerus muncul setiap kali buka aplikasi.
      if (!isManualCheck) {
        await dismissRelease(release.tagName);
      }
    } else if (isManualCheck) {
      // Beritahu pengguna bahwa aplikasi sudah versi terbaru
      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Row(
            children: [
              Icon(Icons.check_circle_outline, color: Color(0xFF10B981), size: 28),
              SizedBox(width: 10),
              Text('Versi Terbaru'),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Aplikasi DuitKu Anda sudah menggunakan versi paling mutakhir (v$currentVersion).',
                style: const TextStyle(fontSize: 14),
              ),
              const SizedBox(height: 12),
              const Text(
                'Tidak ada update APK baru di GitHub saat ini.',
                style: TextStyle(color: Colors.grey, fontSize: 12.5),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Tutup'),
            ),
          ],
        ),
      );
    }
  }

  /// Membuka link download file APK atau rilis GitHub di browser
  Future<void> openDownloadUrl(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  Map<String, dynamic>? _pendingUpdatePayload;

  /// Simpan payload update jika aplikasi belum siap menampilkan dialog (misal saat cold start / splash)
  void setPendingUpdate(Map<String, dynamic> payload) {
    _pendingUpdatePayload = payload;
  }

  /// Periksa dan tampilkan pending update jika ada (dipanggil saat HomeScreen siap)
  void checkPendingUpdate(BuildContext context) {
    if (_pendingUpdatePayload != null && context.mounted) {
      final p = _pendingUpdatePayload!;
      _pendingUpdatePayload = null;
      showUpdateFromNotification(
        context,
        title: p['title']?.toString() ?? 'Pembaruan Aplikasi',
        message: p['message']?.toString() ?? '',
        apkUrl: p['apk_url']?.toString() ?? p['action_url']?.toString() ?? '',
        version: p['version']?.toString(),
        autoStart: true,
      );
    }
  }

  /// Tampilkan dialog update langsung dari push notifikasi / dashboard dan langsung proses unduh in-app
  Future<void> showUpdateFromNotification(
    BuildContext context, {
    required String title,
    required String message,
    required String apkUrl,
    String? version,
    bool autoStart = true,
  }) async {
    if (apkUrl.isEmpty) return;
    await getAppVersion();

    String cleanVer = version?.trim() ?? '';
    if (cleanVer.isEmpty) {
      final match = RegExp(r'(v?[0-9]+\.[0-9]+(\.[0-9]+)?)', caseSensitive: false).firstMatch(apkUrl);
      cleanVer = match?.group(1) ?? 'Terbaru';
    }

    String fileName = 'duitku_update.apk';
    try {
      final uri = Uri.parse(apkUrl);
      final lastSeg = uri.pathSegments.isNotEmpty ? uri.pathSegments.last : 'duitku_update.apk';
      if (lastSeg.toLowerCase().endsWith('.apk')) {
        fileName = lastSeg;
      }
    } catch (_) {}

    final release = GitHubRelease(
      tagName: cleanVer.startsWith('v') || cleanVer.startsWith('V') ? cleanVer : 'v$cleanVer',
      name: title,
      body: message.isNotEmpty ? message : 'Pembaruan fitur baru dan perbaikan performa aplikasi DuitKu.',
      publishedAt: DateTime.now(),
      htmlUrl: apkUrl,
      apkDownloadUrl: apkUrl,
      apkFileName: fileName,
    );

    if (!context.mounted) return;

    await showDialog(
      context: context,
      barrierDismissible: true,
      builder: (ctx) => UpdateDialog(
        release: release,
        currentVersion: currentVersion,
        autoStartDownload: autoStart,
      ),
    );
  }
}
