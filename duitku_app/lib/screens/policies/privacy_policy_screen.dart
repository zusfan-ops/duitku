import 'package:flutter/material.dart';
import '../../theme.dart';

class PrivacyPolicyScreen extends StatelessWidget {
  const PrivacyPolicyScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(title: const Text('Kebijakan Privasi')),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
        children: [
          _heroCard(),
          _section('1. Informasi yang Kami Kumpulkan', [
            'Data yang kamu berikan langsung: Nama, email, nomor telepon, password (dienkripsi), foto avatar, data transaksi keuangan, listing marketplace, dan pesan chat.',
            'Data yang dikumpulkan otomatis: Model device, versi OS, token FCM untuk push notification, waktu login, dan activity logs.',
            'Kami TIDAK mengumpulkan data lokasi GPS, riwayat browsing, atau data biometrik.',
          ]),
          _section('2. Bagaimana Kami Menggunakan Data', [
            'Menyediakan dan mengoperasikan layanan DuitKu.',
            'Menyinkronkan data keuangan kamu antar perangkat.',
            'Mengirim push notification untuk pengingat tagihan dan pesan baru.',
            'Menampilkan widget saldo di layar beranda Android.',
            'Memproses OCR untuk scan struk belanja.',
            'Memverifikasi akun dan mencegah penipuan.',
          ]),
          _section('3. Penyimpanan & Keamanan Data', [
            'Data tersimpan di server berlokasi di Indonesia.',
            'Password dienkripsi menggunakan bcrypt.',
            'Koneksi API menggunakan Bearer Token authentication.',
            'Data keuangan hanya bisa diakses oleh akun yang bersangkutan.',
            'Data cadangan tersimpan terenkripsi.',
          ]),
          _section('4. Berbagi Data dengan Pihak Ketiga', [
            'Kami TIDAK menjual atau menyewakan data pribadimu.',
            'Data hanya dibagikan untuk: Firebase Cloud Messaging (push notification), QR Code pembayaran, dan kepatuhan hukum yang diwajibkan.',
          ]),
          _section('5. Hak Kamu atas Data', [
            'Akses: Lihat semua data melalui menu Export atau Backup.',
            'Ubah: Edit profil, kategori, dan data keuangan kapan saja.',
            'Hapus: Hapus akun dan data permanen melalui Pengaturan.',
            'Eksport: Unduh data dalam format PDF, CSV, atau JSON.',
            'Saat hapus akun, semua data dihapus permanen dalam 30 hari.',
          ]),
          _section('6. Marketplace & Chat', [
            'Listing marketplace hanya terlihat oleh pengguna DuitKu yang login.',
            'Pesan chat hanya bisa diakses oleh peserta obrolan.',
            'Kamu bisa menghapus percakapan dan listing kapan saja.',
          ]),
          _section('7. Hubungi Kami', [
            'Developer: Zusfan Mashuri',
            'Website: duitku.ordr.my.id',
          ]),
          const SizedBox(height: 20),
          Center(
            child: Text(
              '© ${DateTime.now().year} DuitKu — Data kamu adalah milik kamu sepenuhnya.',
              style: TextStyle(fontSize: 11, color: AppColors.textMuted),
            ),
          ),
        ],
      ),
    );
  }

  Widget _heroCard() {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF064E3B), Color(0xFF059669), Color(0xFF10B981)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(22),
        boxShadow: [BoxShadow(color: const Color(0xFF059669).withValues(alpha: 0.3), blurRadius: 24, offset: const Offset(0, 8))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.2),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: Colors.white.withValues(alpha: 0.35)),
            ),
            child: const Text('🔒 PRIVASI', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w800)),
          ),
          const SizedBox(height: 10),
          const Text('Kebijakan Privasi', style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w900)),
          const SizedBox(height: 4),
          Text(
            'Terakhir diperbarui: ${DateTime.now().day} ${_monthName(DateTime.now().month)} ${DateTime.now().year}',
            style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 11),
          ),
          const SizedBox(height: 6),
          Text(
            'Kami menghargai privasi kamu. Kebijakan ini menjelaskan bagaimana DuitKu mengumpulkan, menggunakan, dan melindungi data pribadimu.',
            style: TextStyle(color: Colors.white.withValues(alpha: 0.9), fontSize: 12, height: 1.5),
          ),
        ],
      ),
    );
  }

  Widget _section(String title, List<String> points) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800)),
          const SizedBox(height: 8),
          ...points.map((p) => Padding(
            padding: const EdgeInsets.only(bottom: 6),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('• ', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Color(0xFF059669))),
                Expanded(
                  child: Text(p, style: TextStyle(fontSize: 12.5, height: 1.5, color: AppColors.textSecondary)),
                ),
              ],
            ),
          )),
        ],
      ),
    );
  }

  String _monthName(int m) {
    const names = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return names[m];
  }
}
