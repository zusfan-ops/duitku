import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

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
  final PageController _pageController = PageController();
  int _currentPage = 0;

  final List<Map<String, dynamic>> _slides = [
    {
      'icon': '✨',
      'title': 'Selamat Datang di DuitKu',
      'badge': 'Langkah 1 dari 5',
      'desc': 'Aplikasi terpadu manajemen keuangan pribadi, bisnis kasir POS, dan komunitas RT.',
      'color': const Color(0xFF059669),
      'bullets': [
        '💵 Catatan pemasukan, pengeluaran & scan nota OCR otomatis.',
        '💳 Multi-dompet untuk rekening bank, e-wallet & tabungan.',
      ]
    },
    {
      'icon': '🏘️',
      'title': 'Sistem Komunitas RT',
      'badge': 'Langkah 2 dari 5',
      'desc': 'Hubungkan akun Anda dengan lingkungan RT setempat untuk verifikasi warga & transparansi kas.',
      'color': const Color(0xFF047857),
      'bullets': [
        '🔑 Masuk dengan Kode Unik RT atau pindai QR dari Ketua RT.',
        '🏠 Klasifikasi Warga Tetap (KTP) dan Warga Domisili/Kontrak.',
        '🤝 Vouching tetangga untuk aktivasi otomatis oleh 2 warga.',
      ]
    },
    {
      'icon': '🔨',
      'title': 'Pinjam Alat Warga (Sharing)',
      'badge': 'Langkah 3 dari 5',
      'desc': 'Pinjam mesin rumput, bor listrik, tangga lipat, atau tenda RT secara praktis.',
      'color': const Color(0xFFD97706),
      'bullets': [
        '🔐 Validasi Token QR serah terima & pengembalian yang aman.',
        '💰 Uang jaminan sewa otomatis dicatat di kas & di-refund saat alat kembali utuh.',
      ]
    },
    {
      'icon': '🛍️',
      'title': 'Titip Belanja Antar-Tetangga',
      'badge': 'Langkah 4 dari 5',
      'desc': 'Mau ke pasar atau supermarket? Buka sesi belanja dan bantu tetangga yang membutuhkan kebutuhan harian.',
      'color': const Color(0xFF2563EB),
      'bullets': [
        '🛒 Warga satu RT dapat menitip barang sebelum batas waktu (cutoff).',
        '🧾 Biaya riil belanjaan + tip jasa otomatis dicatat ke buku keuangan.',
      ]
    },
    {
      'icon': '📦',
      'title': 'Pasar Jual Beli & Domain Toko',
      'badge': 'Langkah 5 dari 5',
      'desc': 'Jual barang bekas Anda dan dapatkan domain etalase toko pribadi secara gratis.',
      'color': const Color(0xFF7C3AED),
      'bullets': [
        '🌐 Dapatkan link etalase toko gratis (domain/username).',
        '💬 Chat langsung & tombol WhatsApp untuk transaksi COD aman.',
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
      backgroundColor: Colors.white,
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
                    colors: [color, color.withOpacity(0.75)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                child: Center(
                  child: Container(
                    width: 72,
                    height: 72,
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.25),
                      borderRadius: BorderRadius.circular(22),
                      border: Border.all(color: Colors.white.withOpacity(0.4), width: 1.5),
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
                        color: color.withOpacity(0.12),
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
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: Color(0xFF111827)),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 6),
                    Text(
                      slide['desc'] as String,
                      style: const TextStyle(fontSize: 13, color: Color(0xFF4B5563), height: 1.4),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 12),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF9FAFB),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: const Color(0xFFE5E7EB)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: (slide['bullets'] as List<String>).map((b) => Padding(
                          padding: const EdgeInsets.symmetric(vertical: 2.5),
                          child: Text(b, style: const TextStyle(fontSize: 12, color: Color(0xFF374151), height: 1.35)),
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
                          color: _currentPage == i ? color : const Color(0xFFD1D5DB),
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
                          child: const Text('Lewati', style: TextStyle(color: Color(0xFF9CA3AF), fontWeight: FontWeight.bold)),
                        ),
                        Row(
                          children: [
                            if (_currentPage > 0)
                              TextButton(
                                onPressed: () {
                                  setState(() => _currentPage--);
                                },
                                child: const Text('Kembali', style: TextStyle(fontWeight: FontWeight.bold)),
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
