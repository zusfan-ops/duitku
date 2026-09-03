import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../services/sync_service.dart';

class SyncStatusBanner extends StatelessWidget {
  const SyncStatusBanner({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<SyncService>(
      builder: (context, sync, _) {
        final isOffline = !sync.isOnline;
        final isSyncing = sync.isSyncing;
        final pending = sync.pendingCount;

        if (!isOffline && !isSyncing && pending == 0) {
          return const SizedBox.shrink();
        }

        Color bgColor;
        Color borderColor;
        Color textColor;
        Widget iconWidget;
        String title;
        String subtitle;

        if (isSyncing) {
          bgColor = const Color(0xFFEFF6FF);
          borderColor = const Color(0xFFBFDBFE);
          textColor = const Color(0xFF1D4ED8);
          iconWidget = const SizedBox(
            width: 16,
            height: 16,
            child: CircularProgressIndicator(
              strokeWidth: 2,
              valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF2563EB)),
            ),
          );
          title = pending > 0
              ? 'Menyinkronkan ($pending data)...'
              : 'Menyinkronkan data ke server...';
          subtitle = 'Memperbarui data terbaru...';
        } else if (isOffline) {
          bgColor = const Color(0xFFFFFBEB);
          borderColor = const Color(0xFFFDE68A);
          textColor = const Color(0xFFB45309);
          iconWidget = const Icon(
            Icons.cloud_off_rounded,
            color: Color(0xFFD97706),
            size: 18,
          );
          title = pending > 0
              ? 'Mode Offline ($pending tersimpan)'
              : 'Mode Offline';
          subtitle = pending > 0
              ? 'Otomatis di-sync saat online'
              : 'Menampilkan data dari cache lokal';
        } else {
          // Online but still has pending items to sync
          bgColor = const Color(0xFFF0FDF4);
          borderColor = const Color(0xFFBBF7D0);
          textColor = const Color(0xFF15803D);
          iconWidget = const Icon(
            Icons.sync_rounded,
            color: Color(0xFF16A34A),
            size: 18,
          );
          title = '$pending data siap disinkronkan';
          subtitle = 'Ketuk untuk sinkronkan sekarang';
        }

        return Container(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
          decoration: BoxDecoration(
            color: bgColor,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: borderColor, width: 1.2),
            boxShadow: const [
              BoxShadow(
                color: Color(0x06000000),
                blurRadius: 6,
                offset: Offset(0, 2),
              ),
            ],
          ),
          child: Row(
            children: [
              iconWidget,
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      title,
                      style: TextStyle(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w700,
                        color: textColor,
                      ),
                    ),
                    Text(
                      subtitle,
                      style: TextStyle(
                        fontSize: 11,
                        color: textColor.withValues(alpha: 0.8),
                      ),
                    ),
                  ],
                ),
              ),
              if (pending > 0 && !isSyncing)
                InkWell(
                  onTap: () => sync.syncAll(),
                  borderRadius: BorderRadius.circular(8),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
                    decoration: BoxDecoration(
                      color: textColor.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      'Sync',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        color: textColor,
                      ),
                    ),
                  ),
                ),
            ],
          ),
        );
      },
    );
  }
}
