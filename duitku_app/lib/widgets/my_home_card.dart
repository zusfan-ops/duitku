import 'package:flutter/material.dart';

import '../screens/barang/barang_screen.dart';
import '../services/barang_store.dart';
import '../theme.dart';

class MyHomeDashboardCard extends StatefulWidget {
  final Map<String, dynamic> summary;

  const MyHomeDashboardCard({
    super.key,
    this.summary = const {},
  });

  @override
  State<MyHomeDashboardCard> createState() => _MyHomeDashboardCardState();
}

class _MyHomeDashboardCardState extends State<MyHomeDashboardCard> {
  int _roomsCount = 0;
  int _assetsCount = 0;
  int _maintenanceCount = 0;
  int _warrantiesActive = 0;
  int _healthScore = 85;
  String _healthStatus = 'Kondisi rumah & seluruh aset sangat prima.';
  int _attentionCount = 0;

  @override
  void initState() {
    super.initState();
    _resolveData();
  }

  @override
  void didUpdateWidget(covariant MyHomeDashboardCard oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.summary != oldWidget.summary) {
      _resolveData();
    }
  }

  Future<void> _resolveData() async {
    if (widget.summary.isNotEmpty) {
      final s = widget.summary;
      final assets = (s['assets_count'] as num?)?.toInt() ?? 0;
      setState(() {
        _roomsCount = (s['rooms_count'] as num?)?.toInt() ?? 0;
        _assetsCount = assets;
        _maintenanceCount = (s['maintenance_count'] as num?)?.toInt() ?? 0;
        _warrantiesActive = (s['warranties_active'] as num?)?.toInt() ?? (s['warranties_count'] as num?)?.toInt() ?? 0;
        _healthScore = assets == 0 ? 0 : ((s['health_score'] as num?)?.toInt() ?? 100);
        _healthStatus = s['health_status']?.toString() ??
            (assets == 0 ? 'Mulai catat aset rumah Anda untuk memantau kondisi.' : 'Kondisi rumah & seluruh aset sangat prima.');
        _attentionCount = (s['attention'] as List?)?.length ?? 0;
      });
      return;
    }

    // Fallback: calculate from local BarangStore
    try {
      final items = await BarangStore.loadAll();
      final rooms = items.map((e) => e.room.isNotEmpty ? e.room : e.location).toSet();
      int maintCount = 0;
      int warrCount = 0;
      for (final it in items) {
        maintCount += it.maintenance.length;
        warrCount += it.warranties.length;
      }
      if (!mounted) return;
      setState(() {
        _roomsCount = rooms.length;
        _assetsCount = items.length;
        _maintenanceCount = maintCount;
        _warrantiesActive = warrCount;
        _healthScore = items.isEmpty ? 0 : 100;
        _healthStatus = items.isEmpty
            ? 'Mulai catat aset rumah Anda untuk memantau kondisi.'
            : 'Kondisi rumah & seluruh aset terpelihara baik.';
        _attentionCount = 0;
      });
    } catch (_) {}
  }

  void _openMyHome() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const BarangScreen()),
    ).then((_) => _resolveData());
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: AppColors.border, width: 1),
        boxShadow: const [
          BoxShadow(
            color: Color(0x06000000),
            blurRadius: 16,
            offset: Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Row
          Row(
            children: [
              Container(
                width: 34,
                height: 34,
                decoration: BoxDecoration(
                  color: const Color(0xFFEEF2FF),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFC7D2FE)),
                ),
                child: const Center(
                  child: Icon(Icons.home_work_rounded, color: Color(0xFF4F46E5), size: 18),
                ),
              ),
              const SizedBox(width: 10),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'My Home',
                      style: TextStyle(
                        fontSize: 14.5,
                        fontWeight: FontWeight.w800,
                        color: AppColors.textPrimary,
                        letterSpacing: -0.2,
                      ),
                    ),
                    Text(
                      'Inventaris & Perawatan Rumah',
                      style: TextStyle(fontSize: 11, color: AppColors.textMuted),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFFEEF2FF),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  '$_assetsCount Aset',
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF4F46E5),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Hero Health Card
          InkWell(
            onTap: _openMyHome,
            borderRadius: BorderRadius.circular(18),
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 18, horizontal: 16),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFFF97316), Color(0xFFD946EF), Color(0xFF6366F1)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(18),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFFD946EF).withValues(alpha: 0.3),
                    blurRadius: 18,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: Column(
                children: [
                  Text(
                    _assetsCount == 0 ? '-' : '$_healthScore',
                    style: const TextStyle(
                      fontSize: 44,
                      fontWeight: FontWeight.w900,
                      color: Colors.white,
                      letterSpacing: -1,
                      height: 1.05,
                    ),
                  ),
                  const SizedBox(height: 4),
                  const Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.home_outlined, color: Colors.white, size: 16),
                      SizedBox(width: 5),
                      Text(
                        'Home Health',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w800,
                          color: Colors.white,
                          letterSpacing: 0.2,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    _healthStatus,
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 11.5,
                      color: Colors.white.withValues(alpha: 0.92),
                    ),
                  ),
                  const SizedBox(height: 10),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.22),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(_assetsCount == 0 ? '➕' : (_attentionCount > 0 ? '⚠️' : '✨'), style: const TextStyle(fontSize: 12)),
                        const SizedBox(width: 5),
                        Text(
                          _assetsCount == 0
                              ? 'Mulai catat aset pertama'
                              : (_attentionCount > 0
                                  ? '$_attentionCount tugas butuh perhatian'
                                  : 'Semua perawatan & garansi aman'),
                          style: const TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                            color: Colors.white,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 12),

          // 4-Tile Mini Grid
          Row(
            children: [
              Expanded(
                child: _buildTile(
                  icon: '🏢',
                  count: '$_roomsCount',
                  label: 'Ruangan',
                  bgIcon: const Color(0xFFEFF6FF),
                  textColor: const Color(0xFF2563EB),
                  onTap: _openMyHome,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _buildTile(
                  icon: '📦',
                  count: '$_assetsCount',
                  label: 'Aset Barang',
                  bgIcon: const Color(0xFFFEF3C7),
                  textColor: const Color(0xFFD97706),
                  onTap: _openMyHome,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: _buildTile(
                  icon: '🛠️',
                  count: '$_maintenanceCount',
                  label: 'Perawatan',
                  bgIcon: const Color(0xFFECFDF5),
                  textColor: const Color(0xFF059669),
                  onTap: _openMyHome,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _buildTile(
                  icon: '🛡️',
                  count: '$_warrantiesActive',
                  label: 'Garansi Aktif',
                  bgIcon: const Color(0xFFF5F3FF),
                  textColor: const Color(0xFF7C3AED),
                  onTap: _openMyHome,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Action Button
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: _openMyHome,
              style: OutlinedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 11),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                side: const BorderSide(color: AppColors.border),
                backgroundColor: AppColors.bg,
              ),
              icon: const Text(
                'Buka My Home (Aset & Ruangan)',
                style: TextStyle(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w800,
                  color: AppColors.textPrimary,
                ),
              ),
              label: const Icon(Icons.arrow_forward_rounded, size: 16, color: AppColors.textPrimary),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTile({
    required String icon,
    required String count,
    required String label,
    required Color bgIcon,
    required Color textColor,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(14),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
        decoration: BoxDecoration(
          color: AppColors.bg,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.border, width: 1),
        ),
        child: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: bgIcon,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Center(
                child: Text(icon, style: const TextStyle(fontSize: 18)),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    count,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w900,
                      color: AppColors.textPrimary,
                      height: 1.1,
                    ),
                  ),
                  Text(
                    label,
                    style: const TextStyle(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w600,
                      color: AppColors.textMuted,
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
