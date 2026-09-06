import 'package:flutter/material.dart';
import '../../widgets/onboarding_dialog.dart';

class FaqScreen extends StatefulWidget {
  const FaqScreen({super.key});

  @override
  State<FaqScreen> createState() => _FaqScreenState();
}

class _FaqScreenState extends State<FaqScreen> {
  final TextEditingController _searchCtrl = TextEditingController();
  String _selectedCategory = 'all';
  int? _expandedIndex;

  final List<Map<String, dynamic>> _faqs = [
    {
      'cat': 'rt',
      'tag': 'Sistem RT',
      'q': 'Bagaimana cara bergabung ke lingkungan RT saya?',
      'a': '1. Minta Kode Unik RT ke Ketua RT (contoh: RT04-RW02-GRIYA-2026).\n2. Buka menu Komunitas RT > Gabung RT.\n3. Masukkan kode unik dan nomor rumah Anda. Jika RT mengaktifkan auto-approval, akun langsung aktif.'
    },
    {
      'cat': 'rt',
      'tag': 'Sistem RT',
      'q': 'Apa bedanya status Warga Tetap dan Warga Domisili/Kontrak?',
      'a': 'Warga Tetap adalah warga ber-KTP di RT setempat atau pemilik rumah tetap.\nWarga Domisili adalah penyewa/kontrak/kost. Keduanya memiliki akses penuh, namun Ketua RT dapat menentukan limit batas nilai pinjam alat untuk warga non-permanen.'
    },
    {
      'cat': 'rt',
      'tag': 'Sistem RT',
      'q': 'Apa itu Sistem Penjamin (Vouching) Tetangga?',
      'a': 'Jika Ketua RT sedang berhalangan memverifikasi, akun warga baru akan aktif otomatis jika sudah dijamin (vouched) oleh minimal 2 tetangga terverifikasi di RT tersebut.'
    },
    {
      'cat': 'tools',
      'tag': 'Pinjam Alat',
      'q': 'Bagaimana alur meminjam alat warga di aplikasi?',
      'a': '1. Pilih alat dari katalog RT.\n2. Tentukan durasi pinjam dan ajukan.\n3. Dapatkan Token Serah Terima (6 Digit).\n4. Tunjukkan token saat mengambil alat untuk divalidasi oleh petugas/pemilik.\n5. Saat selesai, gunakan Token Pengembalian untuk mengakhiri sesi pinjam.'
    },
    {
      'cat': 'tools',
      'tag': 'Pinjam Alat',
      'q': 'Bagaimana uang deposit & biaya sewa dicatat ke keuangan?',
      'a': 'Sistem langsung otomatis mencatat biaya sewa & deposit pada buku kas pengeluaran peminjam saat barang diserahkan. Ketika alat dikembalikan utuh, uang deposit langsung di-refund otomatis sebagai pemasukan saldo kas peminjam.'
    },
    {
      'cat': 'errands',
      'tag': 'Titip Belanja',
      'q': 'Bagaimana cara membuka sesi titip belanja untuk tetangga?',
      'a': 'Buka menu Titip Belanja > Buka Titipan. Tentukan nama toko (misal: Pasar Pagi / Supermarket) dan batas waktu menitip. Tetangga satu RT akan menerima info dan dapat menitip pesanan barang.'
    },
    {
      'cat': 'errands',
      'tag': 'Titip Belanja',
      'q': 'Bagaimana perhitungan harga barang & fee jasa belanja?',
      'a': 'Pembelanja memasukkan harga riil sesuai nota/struk belanja asli saat serah terima barang. Sistem langsung mencatat pengeluaran (harga barang + tip jasa) di pemesan dan mencatat pemasukan di pembelanja.'
    },
    {
      'cat': 'finance',
      'tag': 'Keuangan',
      'q': 'Apakah transaksi RT merusak data catatan keuangan pribadi saya?',
      'a': 'Tidak sama sekali. Semua transaksi sewa alat dan titip belanja terintegrasi mulus ke buku kas utama tanpa mengubah struktur data yang sedang berjalan.'
    },
    {
      'cat': 'marketplace',
      'tag': 'Jual Beli',
      'q': 'Bagaimana cara menjual barang atau menyewakan properti?',
      'a': 'Buka menu Jual Beli & Sewa > Pasang Iklan. Anda juga mendapatkan link etalase toko gratis (domain/username) yang bisa dibagikan ke WhatsApp dan media sosial.'
    },
  ];

  @override
  Widget build(BuildContext context) {
    final query = _searchCtrl.text.toLowerCase().trim();
    final filteredFaqs = _faqs.where((f) {
      final matchesCat = _selectedCategory == 'all' || f['cat'] == _selectedCategory;
      final matchesQuery = query.isEmpty ||
          f['q'].toString().toLowerCase().contains(query) ||
          f['a'].toString().toLowerCase().contains(query);
      return matchesCat && matchesQuery;
    }).toList();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Pusat Bantuan & FAQ', style: TextStyle(fontWeight: FontWeight.w800)),
        centerTitle: false,
        actions: [
          IconButton(
            icon: const Icon(Icons.play_circle_outline),
            tooltip: 'Mulai Tour Aplikasi',
            onPressed: () => OnboardingDialog.showManual(context),
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Search Box
          TextField(
            controller: _searchCtrl,
            decoration: InputDecoration(
              hintText: 'Cari pertanyaan (misal: deposit, kode RT)...',
              prefixIcon: const Icon(Icons.search),
              suffixIcon: query.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear),
                      onPressed: () {
                        setState(() => _searchCtrl.clear());
                      },
                    )
                  : null,
              filled: true,
              fillColor: const Color(0xFFF3F4F6),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(16),
                borderSide: BorderSide.none,
              ),
              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            ),
            onChanged: (val) => setState(() {}),
          ),
          const SizedBox(height: 12),

          // Category Pills
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildPill('all', 'Semua'),
                _buildPill('rt', '🏘️ Sistem RT'),
                _buildPill('tools', '🔨 Pinjam Alat'),
                _buildPill('errands', '🛍️ Titip Belanja'),
                _buildPill('finance', '💰 Keuangan'),
                _buildPill('marketplace', '📦 Jual Beli'),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Auto-Collapsing Accordion List
          if (filteredFaqs.isEmpty)
            Container(
              padding: const EdgeInsets.all(32),
              alignment: Alignment.center,
              child: const Column(
                children: [
                  Text('🔍', style: TextStyle(fontSize: 32)),
                  SizedBox(height: 8),
                  Text('Pertanyaan tidak ditemukan', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey)),
                ],
              ),
            )
          else
            ...List.generate(filteredFaqs.length, (idx) {
              final item = filteredFaqs[idx];
              final isExpanded = _expandedIndex == idx;

              return Container(
                margin: const EdgeInsets.only(bottom: 10),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(color: isExpanded ? const Color(0xFF2563EB) : const Color(0xFFE5E7EB)),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.02),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    InkWell(
                      borderRadius: BorderRadius.circular(18),
                      onTap: () {
                        setState(() {
                          // Auto-collapse behavior: if tapping currently open item, close it; else open this and close others
                          _expandedIndex = isExpanded ? null : idx;
                        });
                      },
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFEFF6FF),
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: Text(
                                      item['tag'] as String,
                                      style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: Color(0xFF2563EB)),
                                    ),
                                  ),
                                  const SizedBox(height: 6),
                                  Text(
                                    item['q'] as String,
                                    style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: Color(0xFF111827)),
                                  ),
                                ],
                              ),
                            ),
                            Icon(
                              isExpanded ? Icons.keyboard_arrow_up : Icons.keyboard_arrow_down,
                              color: isExpanded ? const Color(0xFF2563EB) : Colors.grey,
                            ),
                          ],
                        ),
                      ),
                    ),
                    if (isExpanded)
                      Container(
                        padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                        width: double.infinity,
                        child: Text(
                          item['a'] as String,
                          style: const TextStyle(fontSize: 13, color: Color(0xFF4B5563), height: 1.5),
                        ),
                      ),
                  ],
                ),
              );
            }),
        ],
      ),
    );
  }

  Widget _buildPill(String id, String label) {
    final active = _selectedCategory == id;
    return GestureDetector(
      onTap: () {
        setState(() {
          _selectedCategory = id;
          _expandedIndex = null;
        });
      },
      child: Container(
        margin: const EdgeInsets.only(right: 8),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
        decoration: BoxDecoration(
          color: active ? const Color(0xFF2563EB) : const Color(0xFFF3F4F6),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w700,
            color: active ? Colors.white : const Color(0xFF4B5563),
          ),
        ),
      ),
    );
  }
}
