import 'dart:io';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:open_file/open_file.dart';
import 'package:path_provider/path_provider.dart';

import '../services/update_checker_service.dart';
import '../theme.dart';

class UpdateDialog extends StatefulWidget {
  final GitHubRelease release;
  final String currentVersion;

  const UpdateDialog({
    super.key,
    required this.release,
    required this.currentVersion,
  });

  @override
  State<UpdateDialog> createState() => _UpdateDialogState();
}

class _UpdateDialogState extends State<UpdateDialog> {
  bool _isDownloading = false;
  double _progress = 0.0;
  int _downloadedBytes = 0;
  int _totalBytes = 0;
  String? _downloadedApkPath;
  String? _errorMessage;
  http.Client? _httpClient;

  @override
  void dispose() {
    _httpClient?.close();
    super.dispose();
  }

  String _formatFileSize(int? bytes) {
    if (bytes == null || bytes <= 0) return '';
    final mb = bytes / (1024 * 1024);
    return '${mb.toStringAsFixed(1)} MB';
  }

  String _formatBytes(int bytes) {
    if (bytes <= 0) return '0 B';
    final mb = bytes / (1024 * 1024);
    if (mb >= 1.0) {
      return '${mb.toStringAsFixed(1)} MB';
    }
    final kb = bytes / 1024;
    return '${kb.toStringAsFixed(0)} KB';
  }

  Future<void> _startDownload(String apkUrl) async {
    setState(() {
      _isDownloading = true;
      _progress = 0.0;
      _downloadedBytes = 0;
      _totalBytes = widget.release.apkSizeBytes ?? 0;
      _errorMessage = null;
    });

    try {
      final client = http.Client();
      _httpClient = client;
      final request = http.Request('GET', Uri.parse(apkUrl));
      request.headers['User-Agent'] = 'DuitKu-Mobile-App';

      final response = await client.send(request);

      if (response.statusCode >= 400) {
        throw Exception('Server merespons HTTP ${response.statusCode}');
      }

      final total = response.contentLength ?? (widget.release.apkSizeBytes ?? 0);
      if (total > 0) {
        _totalBytes = total;
      }

      Directory? dir;
      try {
        final extDirs = await getExternalCacheDirectories();
        if (extDirs != null && extDirs.isNotEmpty) {
          dir = extDirs.first;
        }
      } catch (_) {}
      dir ??= await getTemporaryDirectory();

      final cleanTag = widget.release.tagName.replaceAll(RegExp(r'[^a-zA-Z0-9_\-\.]'), '_');
      final filePath = '${dir.path}/duitku_update_$cleanTag.apk';
      final file = File(filePath);
      if (await file.exists()) {
        await file.delete();
      }

      final sink = file.openWrite();
      int received = 0;

      await for (final chunk in response.stream) {
        if (!_isDownloading) {
          await sink.close();
          if (await file.exists()) await file.delete();
          return;
        }
        sink.add(chunk);
        received += chunk.length;
        if (mounted) {
          setState(() {
            _downloadedBytes = received;
            if (_totalBytes > 0) {
              _progress = (received / _totalBytes).clamp(0.0, 1.0);
            }
          });
        }
      }

      await sink.flush();
      await sink.close();

      if (mounted) {
        setState(() {
          _isDownloading = false;
          _progress = 1.0;
          _downloadedApkPath = filePath;
        });

        // Langsung panggil package installer Android untuk konfirmasi pengguna
        await _installApk(filePath);
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isDownloading = false;
          _errorMessage = 'Gagal mengunduh: $e';
        });
      }
    }
  }

  void _cancelDownload() {
    _httpClient?.close();
    setState(() {
      _isDownloading = false;
      _progress = 0.0;
      _downloadedBytes = 0;
    });
  }

  Future<void> _installApk(String filePath) async {
    try {
      final file = File(filePath);
      if (!await file.exists()) {
        throw Exception('File APK tidak ditemukan di penyimpanan.');
      }

      final result = await OpenFile.open(
        filePath,
        type: 'application/vnd.android.package-archive',
      );

      if (result.type != ResultType.done && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              result.type == ResultType.permissionDenied
                  ? 'Izin instalasi aplikasi tidak dikenal diperlukan. Harap aktifkan "Izinkan dari sumber ini" di Pengaturan Android.'
                  : 'Installer: ${result.message}',
            ),
            backgroundColor: const Color(0xFFD97706),
            duration: const Duration(seconds: 5),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Gagal membuka installer paket: $e'),
            backgroundColor: const Color(0xFFDC2626),
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final release = widget.release;
    final hasDirectApk = release.apkDownloadUrl != null && release.apkDownloadUrl!.isNotEmpty;
    final downloadTargetUrl = hasDirectApk ? release.apkDownloadUrl! : release.htmlUrl;
    final fileSizeText = _formatFileSize(release.apkSizeBytes);

    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
      clipBehavior: Clip.antiAlias,
      child: Container(
        constraints: const BoxConstraints(maxWidth: 420),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Header Banner with emerald / teal gradient
            Container(
              padding: const EdgeInsets.all(20),
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [Color(0xFF064E3B), Color(0xFF059669)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.system_update_alt_rounded,
                      color: Colors.white,
                      size: 28,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Pembaruan Tersedia! 🎉',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 17,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          release.name.isNotEmpty ? release.name : 'Rilis Baru GitHub',
                          style: const TextStyle(
                            color: Colors.white70,
                            fontSize: 12.5,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            // Content Body
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Version Badges
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.bg,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Text(
                          'v${widget.currentVersion} (Saat Ini)',
                          style: const TextStyle(
                            fontSize: 11,
                            color: AppColors.textSecondary,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                      const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 6),
                        child: Icon(Icons.arrow_forward_rounded, size: 16, color: AppColors.textMuted),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.primarySubtle,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: AppColors.primary.withValues(alpha: 0.4)),
                        ),
                        child: Text(
                          '${release.tagName} (Terbaru)',
                          style: const TextStyle(
                            fontSize: 11,
                            color: AppColors.primaryDark,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Active Download Progress Box (jika sedang download)
                  if (_isDownloading) ...[
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: const Color(0xFF059669).withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFF059669).withValues(alpha: 0.25)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Row(
                                children: [
                                  SizedBox(
                                    width: 14,
                                    height: 14,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      color: AppColors.primary,
                                    ),
                                  ),
                                  SizedBox(width: 8),
                                  Text(
                                    'Mengunduh APK...',
                                    style: TextStyle(
                                      fontSize: 12.5,
                                      fontWeight: FontWeight.w700,
                                      color: AppColors.primaryDark,
                                    ),
                                  ),
                                ],
                              ),
                              Text(
                                '${(_progress * 100).toStringAsFixed(0)}%',
                                style: const TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w900,
                                  color: AppColors.primary,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          ClipRRect(
                            borderRadius: BorderRadius.circular(6),
                            child: LinearProgressIndicator(
                              value: _progress > 0 ? _progress : null,
                              minHeight: 8,
                              backgroundColor: Colors.black12,
                              color: AppColors.primary,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                _totalBytes > 0
                                    ? '${_formatBytes(_downloadedBytes)} / ${_formatBytes(_totalBytes)}'
                                    : _formatBytes(_downloadedBytes),
                                style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                              ),
                              GestureDetector(
                                onTap: _cancelDownload,
                                child: const Text(
                                  'Batalkan',
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w700,
                                    color: Color(0xFFDC2626),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),
                  ] else if (_downloadedApkPath != null) ...[
                    // Success Download Banner
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFF10B981).withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFF10B981).withValues(alpha: 0.3)),
                      ),
                      child: const Row(
                        children: [
                          Icon(Icons.check_circle_rounded, color: Color(0xFF059669), size: 20),
                          SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              'APK berhasil diunduh. Installer paket dibuka otomatis untuk persetujuan instalasi.',
                              style: TextStyle(fontSize: 11.5, color: Color(0xFF065F46), fontWeight: FontWeight.w600),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),
                  ],

                  // Error Message Box
                  if (_errorMessage != null) ...[
                    Container(
                      padding: const EdgeInsets.all(10),
                      margin: const EdgeInsets.only(bottom: 12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEF4444).withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFFEF4444).withValues(alpha: 0.3)),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.error_outline_rounded, color: Color(0xFFDC2626), size: 18),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              _errorMessage!,
                              style: const TextStyle(fontSize: 11.5, color: Color(0xFF991B1B)),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],

                  const Text(
                    'Catatan Rilis & Pembaruan:',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.textPrimary),
                  ),
                  const SizedBox(height: 6),

                  // Changelog box
                  Container(
                    width: double.infinity,
                    constraints: const BoxConstraints(maxHeight: 140),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.bg,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: SingleChildScrollView(
                      child: Text(
                        release.body.trim().isNotEmpty
                            ? release.body.trim()
                            : '• Pembaruan fitur terbaru & perbaikan performa aplikasi.',
                        style: const TextStyle(
                          fontSize: 12.5,
                          color: AppColors.textSecondary,
                          height: 1.4,
                        ),
                      ),
                    ),
                  ),

                  if (hasDirectApk && !_isDownloading) ...[
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        const Icon(Icons.android_rounded, size: 16, color: AppColors.primary),
                        const SizedBox(width: 6),
                        Expanded(
                          child: Text(
                            '${release.apkFileName ?? "APK Installer"} ${fileSizeText.isNotEmpty ? "($fileSizeText)" : ""}',
                            style: const TextStyle(
                              fontSize: 11.5,
                              color: AppColors.textSecondary,
                              fontWeight: FontWeight.w500,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ),

            // Action Buttons
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
              child: Column(
                children: [
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton.icon(
                      style: FilledButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        padding: const EdgeInsets.symmetric(vertical: 13),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      icon: Icon(
                        _isDownloading
                            ? Icons.hourglass_top_rounded
                            : (_downloadedApkPath != null ? Icons.install_mobile_rounded : Icons.system_update_alt_rounded),
                        size: 20,
                      ),
                      label: Text(
                        _isDownloading
                            ? 'Sedang Mengunduh (${(_progress * 100).toStringAsFixed(0)}%)...'
                            : (_downloadedApkPath != null
                                ? 'Pasang Ulang File APK'
                                : (hasDirectApk
                                    ? 'Perbarui Langsung Dari Aplikasi ($fileSizeText)'
                                    : 'Unduh di GitHub Releases')),
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                      ),
                      onPressed: _isDownloading
                          ? null
                          : () {
                              if (_downloadedApkPath != null) {
                                _installApk(_downloadedApkPath!);
                              } else if (hasDirectApk) {
                                _startDownload(release.apkDownloadUrl!);
                              } else {
                                UpdateCheckerService.instance.dismissRelease(release.tagName);
                                Navigator.pop(context);
                                UpdateCheckerService.instance.openDownloadUrl(downloadTargetUrl);
                              }
                            },
                    ),
                  ),
                  const SizedBox(height: 6),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      TextButton.icon(
                        icon: const Icon(Icons.open_in_browser_rounded, size: 16),
                        label: const Text('Buka Web / Browser', style: TextStyle(fontSize: 12)),
                        onPressed: () {
                          UpdateCheckerService.instance.dismissRelease(release.tagName);
                          Navigator.pop(context);
                          UpdateCheckerService.instance.openDownloadUrl(downloadTargetUrl);
                        },
                      ),
                      TextButton(
                        onPressed: () {
                          if (_isDownloading) _cancelDownload();
                          UpdateCheckerService.instance.dismissRelease(release.tagName);
                          Navigator.pop(context);
                        },
                        child: const Text(
                          'Nanti Saja',
                          style: TextStyle(color: AppColors.textMuted, fontSize: 12),
                        ),
                      ),
                    ],
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
