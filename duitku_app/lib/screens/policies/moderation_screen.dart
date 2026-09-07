import 'package:flutter/material.dart';
import '../../theme.dart';

class ModerationScreen extends StatelessWidget {
  const ModerationScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(title: const Text('Kebijakan Moderasi Konten')),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
        children: [
          _heroCard(),
          _section('1. Standar Komunitas', [
            'Semua pengguna wajib mematuhi standar komunitas saat menggunakan marketplace, chat, status/stories, dan forum diskusi RT.',
            'Diperbolehkan: Jual beli barang legal, chat sopan, foto produk asli.',
            'Dilarang: Penipuan, konten dewasa, kekerasan, spam.',
          ]),
          _warningSection('2. Konten yang DILARANG', [
            ('Penipuan & Scam', 'Listing palsu, harga manipulatif, transfer DP palsu, identitas palsu.'),
            ('Konten Seksual', 'Foto/film dewasa, pornografi, eksploitasi seksual.'),
            ('Kekerasan', 'Ancaman, pelecehan, intimidasi, atau konten kekerasan.'),
            ('Hate Speech', 'Ujaran kebencian berdasarkan SARA.'),
            ('Spam & Phishing', 'Pesan massal yang tidak diinginkan, link phishing.'),
            ('Barang Ilegal', 'Narkoba, senjata api, barang curian, obat tanpa izin.'),
            ('Hak Cipta', 'Konten bajakan, software ilegal.'),
            ('Identitas Palsu', 'Mengaku sebagai orang lain.'),
          ]),
          _section('3. Sistem Pelaporan (Report)', [
            'Report Pengguna: Laporkan pengguna melalui menu obrolan → ⋮ → Laporkan Pengguna.',
            'Report Iklan/Listing: Laporkan iklan marketplace yang mencurigakan.',
            'Report Komentar: Laporkan komentar yang tidak pantas.',
            'Semua laporan ditinjau admin dalam 1×24 jam. Identitas pelapor dirahasiakan.',
          ]),
          _section('4. Sistem Pemblokiran (Block)', [
            'Buka obrolan → ⋮ → Blokir Pengguna.',
            'Pemblokiran bersifat dua arah: tidak bisa saling mengirim pesan.',
            'Bisa membuka blokir kapan saja melalui Pengaturan.',
          ]),
          _section('5. Sanksi & Hukuman', [
            'Peringatan Pertama: Pesan peringatan untuk pelanggaran ringan.',
            'Pemblokiran Sementara: 3-7 hari untuk pelanggaran berulang.',
            'Pemblokiran Permanen: Akun dihapus untuk pelanggaran berat.',
            'Laporan ke Pihak Berwajib: Untuk pelanggaran UU ITE / pidana.',
          ]),
          _section('6. Marketplace — Aturan Khusus', [
            'Foto produk harus asli (bukan screenshot dari toko lain).',
            'Harga harus realistis dan sesuai kondisi barang.',
            'Lokasi COD harus di tempat umum yang aman.',
            'Deskripsi harus jelas dan jujur.',
          ]),
          _section('7. Banding & Kontak', [
            'Jika akun dikunci secara tidak adil, ajukan banding ke moderasi@duitku.ordr.my.id',
            'Banding akan ditinjau dalam 3 hari kerja.',
          ]),
          const SizedBox(height: 20),
          Center(
            child: Text(
              '© ${DateTime.now().year} DuitKu — Bersama kita ciptakan komunitas yang aman.',
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
          colors: [Color(0xFF7C3AED), Color(0xFF6D28D9), Color(0xFF4C1D95)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(22),
        boxShadow: [BoxShadow(color: const Color(0xFF6D28D9).withValues(alpha: 0.3), blurRadius: 24, offset: const Offset(0, 8))],
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
            child: const Text('🛡️ MODERASI', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w800)),
          ),
          const SizedBox(height: 10),
          const Text('Kebijakan Moderasi Konten', style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w900)),
          const SizedBox(height: 6),
          Text(
            'DuitKu berkomitmen menciptakan lingkungan yang aman, saling menghormati, dan bebas dari penyalahgunaan untuk semua pengguna.',
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
                const Text('• ', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Color(0xFF6D28D9))),
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

  Widget _warningSection(String title, List<(String, String)> items) {
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
          ...items.map((item) => Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('❌ ', style: TextStyle(fontSize: 12)),
                Expanded(
                  child: RichText(
                    text: TextSpan(
                      style: TextStyle(fontSize: 12.5, height: 1.5, color: AppColors.textSecondary),
                      children: [
                        TextSpan(text: '${item.$1}: ', style: const TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF991B1B))),
                        TextSpan(text: item.$2),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          )),
        ],
      ),
    );
  }
}
