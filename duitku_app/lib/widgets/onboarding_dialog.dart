import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../theme.dart';

class OnboardingDialog extends StatefulWidget {
  const OnboardingDialog({super.key});

  static Future<void> showIfNeeded(BuildContext context) async {
    final prefs = await SharedPreferences.getInstance();
    final seen = prefs.getBool('duitku_onboard_seen_v1') ?? false;
    if (!seen && context.mounted) {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => const OnboardingDialog(),
      );
    }
  }

  static void showManual(BuildContext context) {
    showDialog(
      context: context,
      barrierDismissible: true,
      builder: (ctx) => const OnboardingDialog(),
    );
  }

  @override
  State<OnboardingDialog> createState() => _OnboardingDialogState();
}

class _OnboardingDialogState extends State<OnboardingDialog> {
  int _currentPage = 0;

  final List<Map<String, dynamic>> _slides = [
    {
      'icon': '💵',
      'title': 'Keuangan & Scan Nota OCR',
      'badge': 'Langkah 1 dari 5 • Keuangan',
      'desc': 'Kendalikan arus kas harian, rekening bank, dan hemat waktu dengan pemindai AI.',
      'color': const Color(0xFF059669),
      'bullets': [
        '📸 Scan OCR Struk: Foto nota belanja dan total harga terisi otomatis.',
        '💳 Multi-Dompet: Kelola saldo rekening BCA, Mandiri, Cash, & E-Wallet.',
        '📊 Budgeting & Laporan: Monitor batas anggaran dan cetak laporan PDF/Excel.',
      ]
    },
    {
      'icon': '🛒',
      'title': 'Mode Bisnis & Kasir POS',
      'badge': 'Langkah 2 dari 5 • Bisnis',
      'desc': 'Ubah aplikasi menjadi mesin kasir digital untuk toko, warung, atau usaha UMKM Anda.',
      'color': const Color(0xFF4F46E5),
      'bullets': [
        '📦 Katalog Produk: Kelola harga modal, harga jual, dan stok barang.',
        '🧾 Kasir Cepat: Transaksi kilat tunai/QRIS dan cetak struk nota belanja.',
        '📈 Laporan Omzet: Pantau laba bersih terpisah dari kas pribadi Anda.',
      ]
    },
    {
      'icon': '🏘️',
      'title': 'Sistem RT & Pinjam Alat',
      'badge': 'Langkah 3 dari 5 • Komunitas',
      'desc': 'Terhubung dengan tetangga sekitar untuk saling bantu dan manfaatkan fasilitas RT.',
      'color': const Color(0xFF0D9488),
      'bullets': [
        '🔑 Grup RT Warga: Masuk dengan Kode Unik RT atau pindai QR Ketua RT.',
        '🔨 Pinjam Alat: Pinjam bor, mesin rumput, atau tenda dengan token QR aman.',
        '🛍️ Titip Belanja: Buka sesi belanja pasar dan bantu tetangga sekitar.',
      ]
    },
    {
      'icon': '🏷️',
      'title': 'Marketplace & Domain Toko',
      'badge': 'Langkah 4 dari 5 • Jual Beli',
      'desc': 'Jual barang bekas tak terpakai dan dapatkan website etalase toko pribadi gratis.',
      'color': const Color(0xFF9333EA),
      'bullets': [
        '🌐 Domain Toko: Dapatkan URL etalase toko publik gratis untuk jualan online.',
        '💬 Chat Langsung: Komunikasi aman antar penjual dan pembeli via chat / WA.',
      ]
    },
    {
      'icon': '⏰',
      'title': 'Tagihan, Tabungan & Traveling',
      'badge': 'Langkah 5 dari 5 • Gaya Hidup',
      'desc': 'Fitur pendukung lengkap untuk memenuhi seluruh kebutuhan gaya hidup Anda.',
      'color': const Color(0xFFE11D48),
      'bullets': [
        '⏰ Pengingat Tagihan: Reminder jatuh tempo listrik, cicilan & pajak kendaraan.',
        '🎯 Target Menabung: Capai impian kurban, liburan, atau dana darurat.',
        '✈️ Traveling & Valas: Trip budget organizer & kalkulator kurs valas.',
        '🎬 TV & Game: Streaming siaran TV digital dan game hub santai.',
      ]
    },
  ];

  Future<void> _finish() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('duitku_onboard_seen_v1', true);
    if (mounted) Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) {
    final slide = _slides[_currentPage];
    final color = slide['color'] as Color;

    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(28)),
      elevation: 16,
      backgroundColor: AppColors.card,
      insetPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(28),
        child: Container(
          constraints: const BoxConstraints(maxWidth: 440),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Top Banner Graphic
              Container(
                height: 140,
                width: double.infinity,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [color, color.withValues(alpha: 0.75)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                child: Center(
                  child: Container(
                    width: 72,
                    height: 72,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.25),
                      borderRadius: BorderRadius.circular(22),
                      border: Border.all(color: Colors.white.withValues(alpha: 0.4), width: 1.5),
                    ),
                    child: Center(
                      child: Text(slide['icon'] as String, style: const TextStyle(fontSize: 36)),
                    ),
                  ),
                ),
              ),

              // Content Carousel
              Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: color.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        slide['badge'] as String,
                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: color),
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      slide['title'] as String,
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: AppColors.textPrimary),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 6),
                    Text(
                      slide['desc'] as String,
                      style: const TextStyle(fontSize: 13, color: AppColors.textSecondary, height: 1.4),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 12),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppColors.bg,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: (slide['bullets'] as List<String>).map((b) => Padding(
                          padding: const EdgeInsets.symmetric(vertical: 2.5),
                          child: Text(b, style: const TextStyle(fontSize: 12, color: AppColors.textPrimary, height: 1.35)),
                        )).toList(),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Dot Indicators
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: List.generate(_slides.length, (i) => AnimatedContainer(
                        duration: const Duration(milliseconds: 250),
                        margin: const EdgeInsets.symmetric(horizontal: 3),
                        width: _currentPage == i ? 22 : 7,
                        height: 7,
                        decoration: BoxDecoration(
                          color: _currentPage == i ? color : AppColors.border,
                          borderRadius: BorderRadius.circular(4),
                        ),
                      )),
                    ),
                    const SizedBox(height: 16),

                    // Footer Buttons
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        TextButton(
                          onPressed: _finish,
                          child: const Text('Lewati', style: TextStyle(color: AppColors.textMuted, fontWeight: FontWeight.bold)),
                        ),
                        Row(
                          children: [
                            if (_currentPage > 0)
                              TextButton(
                                onPressed: () {
                                  setState(() => _currentPage--);
                                },
                                child: const Text('Kembali', style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                              ),
                            ElevatedButton(
                              style: ElevatedButton.styleFrom(
                                backgroundColor: color,
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                                elevation: 0,
                              ),
                              onPressed: () {
                                if (_currentPage < _slides.length - 1) {
                                  setState(() => _currentPage++);
                                } else {
                                  _finish();
                                }
                              },
                              child: Text(
                                _currentPage == _slides.length - 1 ? 'Mulai 🚀' : 'Lanjut →',
                                style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                              ),
                            ),
                          ],
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
    );
  }
}
