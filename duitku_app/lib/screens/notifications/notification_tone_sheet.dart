import 'package:flutter/material.dart';
import '../../services/local_notification_service.dart';
import '../../theme.dart';

class NotificationToneSheet extends StatefulWidget {
  const NotificationToneSheet({super.key});

  static Future<void> show(BuildContext context) {
    return showModalBottomSheet(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => const NotificationToneSheet(),
    );
  }

  @override
  State<NotificationToneSheet> createState() => _NotificationToneSheetState();
}

class _NotificationToneSheetState extends State<NotificationToneSheet> {
  String _selectedKey = 'default';
  String? _playingKey;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadSelected();
  }

  @override
  void dispose() {
    LocalNotificationService.instance.stopTone();
    super.dispose();
  }

  Future<void> _loadSelected() async {
    final key = await LocalNotificationService.instance.getSelectedToneKey();
    if (mounted) {
      setState(() {
        _selectedKey = key;
        _isLoading = false;
      });
    }
  }

  Future<void> _selectTone(String key) async {
    setState(() {
      _selectedKey = key;
      _playingKey = key;
    });
    await LocalNotificationService.instance.setSelectedToneKey(key);
    await LocalNotificationService.instance.previewTone(key);

    // Reset playing indicator after a brief duration
    Future.delayed(const Duration(milliseconds: 1400), () {
      if (mounted && _playingKey == key) {
        setState(() => _playingKey = null);
      }
    });
  }

  Future<void> _playPreview(String key) async {
    if (_playingKey == key) {
      await LocalNotificationService.instance.stopTone();
      setState(() => _playingKey = null);
    } else {
      setState(() => _playingKey = key);
      await LocalNotificationService.instance.previewTone(key);
      Future.delayed(const Duration(milliseconds: 1400), () {
        if (mounted && _playingKey == key) {
          setState(() => _playingKey = null);
        }
      });
    }
  }

  Future<void> _testPushNotification() async {
    await LocalNotificationService.instance.showTestNotification(tone: _selectedKey);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Notifikasi uji coba dikirim! Periksa bar notifikasi HP Anda.'),
          duration: Duration(seconds: 2),
        ),
      );
    }
  }

  Future<void> _openSystemSettings() async {
    await LocalNotificationService.instance.openSystemNotificationSettings();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardColor = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderColor = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);

    return Container(
      constraints: BoxConstraints(
        maxHeight: MediaQuery.of(context).size.height * 0.88,
      ),
      decoration: BoxDecoration(
        color: cardColor,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x22000000),
            blurRadius: 24,
            offset: Offset(0, -6),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Drag Handle
            const SizedBox(height: 12),
            Center(
              child: Container(
                width: 44,
                height: 4.5,
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF475569) : const Color(0xFFCBD5E1),
                  borderRadius: BorderRadius.circular(3),
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Header
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: Row(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF8B5CF6), Color(0xFF6366F1)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(14),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF8B5CF6).withValues(alpha: 0.35),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: const Icon(Icons.music_note_rounded, color: Colors.white, size: 24),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Nada Notifikasi',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w800,
                            letterSpacing: -0.3,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Atur suara pesan chat, broadcast, & sistem',
                          style: TextStyle(
                            fontSize: 12,
                            color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                          ),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    onPressed: () => Navigator.pop(context),
                    icon: const Icon(Icons.close_rounded),
                    color: isDark ? Colors.white70 : const Color(0xFF64748B),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            Divider(height: 1, color: borderColor),

            // List of tone options
            Flexible(
              child: _isLoading
                  ? const Center(
                      child: Padding(
                        padding: EdgeInsets.all(40),
                        child: CircularProgressIndicator(),
                      ),
                    )
                  : ListView(
                      padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
                      shrinkWrap: true,
                      children: [
                        ...LocalNotificationService.toneOptions.map((opt) {
                          final isSelected = _selectedKey == opt.key;
                          final isPlaying = _playingKey == opt.key;

                          return Container(
                            margin: const EdgeInsets.only(bottom: 8),
                            decoration: BoxDecoration(
                              color: isSelected
                                  ? (isDark
                                      ? const Color(0xFF2563EB).withValues(alpha: 0.15)
                                      : const Color(0xFFEFF6FF))
                                  : (isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC)),
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(
                                color: isSelected
                                    ? const Color(0xFF3B82F6)
                                    : (isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
                                width: isSelected ? 1.6 : 1.0,
                              ),
                            ),
                            child: Material(
                              color: Colors.transparent,
                              child: InkWell(
                                onTap: () => _selectTone(opt.key),
                                borderRadius: BorderRadius.circular(16),
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                  child: Row(
                                    children: [
                                      // Icon
                                      Container(
                                        width: 38,
                                        height: 38,
                                        decoration: BoxDecoration(
                                          color: isSelected
                                              ? const Color(0xFF3B82F6).withValues(alpha: 0.18)
                                              : (isDark
                                                  ? const Color(0xFF1E293B)
                                                  : Colors.white),
                                          borderRadius: BorderRadius.circular(11),
                                        ),
                                        child: Icon(
                                          opt.icon,
                                          color: isSelected
                                              ? const Color(0xFF2563EB)
                                              : (isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B)),
                                          size: 20,
                                        ),
                                      ),
                                      const SizedBox(width: 12),

                                      // Texts
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              opt.title,
                                              style: TextStyle(
                                                fontSize: 14,
                                                fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                                                color: isSelected
                                                    ? (isDark ? Colors.white : const Color(0xFF1E3A8A))
                                                    : (isDark ? Colors.white : const Color(0xFF0F172A)),
                                              ),
                                            ),
                                            const SizedBox(height: 2),
                                            Text(
                                              opt.subtitle,
                                              style: TextStyle(
                                                fontSize: 11,
                                                color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),

                                      // Play Preview Button
                                      IconButton(
                                        tooltip: 'Dengarkan nada',
                                        onPressed: () => _playPreview(opt.key),
                                        icon: AnimatedSwitcher(
                                          duration: const Duration(milliseconds: 200),
                                          child: Icon(
                                            isPlaying
                                                ? Icons.volume_up_rounded
                                                : Icons.play_circle_outline_rounded,
                                            key: ValueKey('${opt.key}_$isPlaying'),
                                            color: isPlaying
                                                ? const Color(0xFF2563EB)
                                                : (isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B)),
                                            size: 24,
                                          ),
                                        ),
                                      ),

                                      // Selection radio indicator
                                      Container(
                                        width: 22,
                                        height: 22,
                                        decoration: BoxDecoration(
                                          shape: BoxShape.circle,
                                          border: Border.all(
                                            color: isSelected
                                                ? const Color(0xFF2563EB)
                                                : (isDark ? const Color(0xFF475569) : const Color(0xFFCBD5E1)),
                                            width: isSelected ? 6.5 : 2,
                                          ),
                                          color: isSelected ? Colors.white : Colors.transparent,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                          );
                        }),

                        const SizedBox(height: 8),

                        // System settings shortcut card
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                          decoration: BoxDecoration(
                            color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(
                              color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                            ),
                          ),
                          child: Row(
                            children: [
                              Container(
                                width: 36,
                                height: 36,
                                decoration: BoxDecoration(
                                  color: const Color(0xFF059669).withValues(alpha: 0.12),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: const Icon(
                                  Icons.settings_suggest_rounded,
                                  color: Color(0xFF059669),
                                  size: 20,
                                ),
                              ),
                              const SizedBox(width: 12),
                              const Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Nada Dering Sistem HP',
                                      style: TextStyle(
                                        fontSize: 13,
                                        fontWeight: FontWeight.w700,
                                      ),
                                    ),
                                    Text(
                                      'Buka pengaturan channel sistem untuk memilih nada dering bawaan HP',
                                      style: TextStyle(
                                        fontSize: 11,
                                        color: Color(0xFF64748B),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              TextButton(
                                onPressed: _openSystemSettings,
                                style: TextButton.styleFrom(
                                  padding: const EdgeInsets.symmetric(horizontal: 8),
                                  minimumSize: Size.zero,
                                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                ),
                                child: const Text(
                                  'Buka',
                                  style: TextStyle(
                                    fontWeight: FontWeight.w800,
                                    fontSize: 12,
                                    color: Color(0xFF059669),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
            ),

            // Bottom action buttons
            Container(
              padding: const EdgeInsets.fromLTRB(20, 12, 20, 16),
              decoration: BoxDecoration(
                color: cardColor,
                border: Border(top: BorderSide(color: borderColor)),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: _testPushNotification,
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                        side: BorderSide(
                          color: isDark ? const Color(0xFF475569) : const Color(0xFFCBD5E1),
                        ),
                      ),
                      icon: const Icon(Icons.notifications_active_outlined, size: 18),
                      label: const Text(
                        'Uji Coba Nada',
                        style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: FilledButton(
                      onPressed: () => Navigator.pop(context),
                      style: FilledButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                      ),
                      child: const Text(
                        'Simpan & Selesai',
                        style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13),
                      ),
                    ),
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
