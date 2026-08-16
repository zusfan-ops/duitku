import 'package:flutter/material.dart';

import '../theme.dart';
import 'activity_screen.dart';
import 'barang/barang_screen.dart';
import 'belanja/belanja_screen.dart';
import 'bills_screen.dart';
import 'debt_screen.dart';
import 'export/export_screen.dart';
import 'recurring/recurring_screen.dart';
import 'savings/savings_screen.dart';
import 'stats_screen.dart';
import 'traveling/traveling_screen.dart';
import 'wallet_screen.dart';

class FeaturesScreen extends StatelessWidget {
  const FeaturesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Layanan & Fitur'),
        centerTitle: false,
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(14, 6, 14, 110),
        children: [
          // Section: Transaksi & Pengeluaran
          _buildSectionHeader('MANAJEMEN KEUANGAN'),
          const SizedBox(height: 8),
          _buildFeatureGrid(context, [
            _FeatureItem(
              title: 'Statistik & Analisis',
              subtitle: 'Grafik tren & kategori',
              icon: Icons.pie_chart_rounded,
              gradient: const [Color(0xFF047857), Color(0xFF10B981)],
              shadowColor: const Color(0xFF10B981),
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const StatsScreen()),
              ),
            ),
            _FeatureItem(
              title: 'Daftar Tagihan',
              subtitle: 'Pengingat tagihan rutin',
              icon: Icons.receipt_long_rounded,
              gradient: const [Color(0xFF1D4ED8), Color(0xFF3B82F6)],
              shadowColor: const Color(0xFF3B82F6),
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const BillsScreen()),
              ),
            ),
            _FeatureItem(
              title: 'Hutang & Piutang',
              subtitle: 'Catatan pinjaman & tempo',
              icon: Icons.account_balance_wallet_rounded,
              gradient: const [Color(0xFFB45309), Color(0xFFF59E0B)],
              shadowColor: const Color(0xFFF59E0B),
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const DebtScreen()),
              ),
            ),
            _FeatureItem(
              title: 'Kelola Rekening',
              subtitle: 'Bank, e-wallet & dompet',
              icon: Icons.account_balance_rounded,
              gradient: const [Color(0xFF6D28D9), Color(0xFF8B5CF6)],
              shadowColor: const Color(0xFF8B5CF6),
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const WalletScreen()),
              ),
            ),
            _FeatureItem(
              title: 'Transaksi Rutin',
              subtitle: 'Otomatisasi berkala',
              icon: Icons.sync_rounded,
              gradient: const [Color(0xFF0F766E), Color(0xFF14B8A6)],
              shadowColor: const Color(0xFF14B8A6),
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const RecurringScreen()),
              ),
            ),
            _FeatureItem(
              title: 'Target Tabungan',
              subtitle: 'Multi-goal & progres',
              icon: Icons.savings_rounded,
              gradient: const [Color(0xFFC026D3), Color(0xFFE879F9)],
              shadowColor: const Color(0xFFE879F9),
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const SavingsScreen()),
              ),
            ),
            _FeatureItem(
              title: 'Export Laporan',
              subtitle: 'Cetak PDF & CSV Excel',
              icon: Icons.description_rounded,
              gradient: const [Color(0xFFE11D48), Color(0xFFFB7185)],
              shadowColor: const Color(0xFFFB7185),
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const ExportScreen()),
              ),
            ),
          ]),

          const SizedBox(height: 18),

          // Section: Gaya Hidup & Rencana
          _buildSectionHeader('GAYA HIDUP & BELANJA'),
          const SizedBox(height: 8),
          _buildFeatureGrid(context, [
            _FeatureItem(
              title: 'Daftar Belanja',
              subtitle: 'Checklist & budget belanja',
              icon: Icons.shopping_bag_rounded,
              gradient: const [Color(0xFFBE185D), Color(0xFFF43F5E)],
              shadowColor: const Color(0xFFF43F5E),
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const BelanjaScreen()),
              ),
            ),
            _FeatureItem(
              title: 'Traveling & Trip',
              subtitle: 'Liburan & tiket digital',
              icon: Icons.flight_takeoff_rounded,
              gradient: const [Color(0xFF0E7490), Color(0xFF06B6D4)],
              shadowColor: const Color(0xFF06B6D4),
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const TravelingScreen()),
              ),
            ),
            _FeatureItem(
              title: 'Stok Barang',
              subtitle: 'Inventaris & aset fisik',
              icon: Icons.inventory_2_rounded,
              gradient: const [Color(0xFF4338CA), Color(0xFF6366F1)],
              shadowColor: const Color(0xFF6366F1),
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const BarangScreen()),
              ),
            ),
            _FeatureItem(
              title: 'Semua Mutasi',
              subtitle: 'Riwayat & pencarian data',
              icon: Icons.history_rounded,
              gradient: const [Color(0xFF334155), Color(0xFF64748B)],
              shadowColor: const Color(0xFF64748B),
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const ActivityScreen()),
              ),
            ),
          ]),
        ],
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4),
      child: Text(
        title,
        style: const TextStyle(
          fontSize: 10.5,
          fontWeight: FontWeight.w800,
          letterSpacing: 0.8,
          color: AppColors.textMuted,
        ),
      ),
    );
  }

  Widget _buildFeatureGrid(BuildContext context, List<_FeatureItem> items) {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        mainAxisSpacing: 10,
        crossAxisSpacing: 10,
        childAspectRatio: 2.1,
      ),
      itemCount: items.length,
      itemBuilder: (context, index) {
        final item = items[index];
        return Material(
          color: Colors.transparent,
          child: InkWell(
            onTap: item.onTap,
            borderRadius: BorderRadius.circular(16),
            child: Ink(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: item.gradient,
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: item.shadowColor.withValues(alpha: 0.25),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(16),
                child: Stack(
                  children: [
                    // Watermark vector icon in background
                    Positioned(
                      right: -10,
                      bottom: -10,
                      child: Transform.rotate(
                        angle: -0.15,
                        child: Icon(
                          item.icon,
                          size: 58,
                          color: Colors.white.withValues(alpha: 0.16),
                        ),
                      ),
                    ),
                    // Ambient light ring
                    Positioned(
                      right: -20,
                      top: -20,
                      child: Container(
                        width: 60,
                        height: 60,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: Colors.white.withValues(alpha: 0.08),
                        ),
                      ),
                    ),
                    // Content
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              Container(
                                width: 26,
                                height: 26,
                                decoration: BoxDecoration(
                                  color: Colors.white.withValues(alpha: 0.22),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Icon(item.icon, size: 15, color: Colors.white),
                              ),
                            ],
                          ),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(
                                item.title,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w800,
                                  color: Colors.white,
                                  letterSpacing: -0.2,
                                ),
                              ),
                              const SizedBox(height: 1),
                              Text(
                                item.subtitle,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(
                                  fontSize: 9.5,
                                  fontWeight: FontWeight.w500,
                                  color: Colors.white.withValues(alpha: 0.82),
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
            ),
          ),
        );
      },
    );
  }
}

class _FeatureItem {
  final String title;
  final String subtitle;
  final IconData icon;
  final List<Color> gradient;
  final Color shadowColor;
  final VoidCallback onTap;

  const _FeatureItem({
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.gradient,
    required this.shadowColor,
    required this.onTap,
  });
}
