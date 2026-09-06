import 'package:flutter/material.dart';
import '../../theme.dart';
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
    // ── 1. KEUANGAN & DOMPET ──
    {
      'cat': 'finance',
      'tag': 'Keuangan',
      'q': 'Bagaimana cara mencatat transaksi pemasukan atau pengeluaran?',
      'a': 'Ketuk tombol bulat biru (+) di bilah bawah layar Dashboard. Pilih jenis transaksi (Keluar/Masuk/Transfer), isi nominal, kategori, dan dompet yang digunakan. Saldo buku kas Anda akan otomatis terupdate.'
    },
    {
      'cat': 'finance',
      'tag': 'Scan OCR AI',
      'q': 'Bagaimana cara kerja pemindai Scan Struk / Nota (OCR)?',
      'a': 'Pilih fitur Scan Struk di kartu Hero Dashboard. Arahkan kamera ke struk belanja Anda atau ambil dari galeri. AI DuitKu otomatis membaca total nominal, nama merchant, dan tanggal belanja.'
    },
    {
      'cat': 'finance',
      'tag': 'Multi-Dompet',
      'q': 'Bagaimana cara memisahkan saldo rekening bank dan cash?',
      'a': 'Buka menu Dompet untuk menambahkan rekening (BCA, Mandiri, Gopay, OVO, Dompet Tunai). Anda dapat melakukan mutasi transfer antar-dompet secara rapi.'
    },
    {
      'cat': 'finance',
      'tag': 'Budgeting',
      'q': 'Bagaimana cara membatasi pengeluaran dengan Budget Bulanan?',
      'a': 'Di menu Pengaturan > Budget Bulan Ini, tentukan batas maksimal anggaran bulanan. Kartu budget di Dashboard akan menampilkan persentase pemakaian saldo Anda.'
    },

    // ── 2. KASIR POS BISNIS ──
    {
      'cat': 'pos',
      'tag': 'Kasir POS',
      'q': 'Bagaimana cara beralih ke Mode Bisnis / Kasir Toko (POS)?',
      'a': 'Gunakan Mode Switcher di bagian atas Dashboard (pilih "Bisnis"). Anda bisa menambahkan produk jualan, menjalankan kasir POS cepat, mencetak struk, dan memantau laba bersih terpisah dari keuangan pribadi.'
    },
    {
      'cat': 'pos',
      'tag': 'Kasir POS',
      'q': 'Apakah transaksi kasir toko otomatis masuk ke saldo rekening saya?',
      'a': 'Ya. Saat membuka sesi kasir atau mendaftarkan toko, Anda dapat memilih dompet default penampung hasil penjualan.'
    },

    // ── 3. KOMUNITAS RT ──
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
      'a': 'Warga Tetap adalah warga ber-KTP di RT setempat atau pemilik rumah tetap.\nWarga Domisili adalah penyewa/kontrak/kost. Keduanya memiliki akses penuh meminjam alat & titip belanja, namun Ketua RT dapat menentukan limit batas pinjam untuk warga domisili.'
    },
    {
      'cat': 'rt',
      'tag': 'Sistem RT',
      'q': 'Apa itu Sistem Penjamin (Vouching) Tetangga?',
      'a': 'Jika Ketua RT sedang berhalangan memverifikasi, akun warga baru akan aktif otomatis jika sudah dijamin (vouched) oleh minimal 2 tetangga terverifikasi di RT tersebut.'
    },
    {
      'cat': 'rt',
      'tag': 'Sistem RT',
      'q': 'Bagaimana cara mendaftarkan RT baru jika saya adalah Pengurus RT?',
      'a': 'Di halaman Komunitas RT, pilih Daftarkan RT Baru. Isi detail wilayah (Provinsi, Kota, Kecamatan, Kelurahan, RW, RT). Anda akan langsung menjadi Ketua RT dan mendapatkan Kode Unik RT.'
    },

    // ── 4. PINJAM ALAT WARGA ──
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

    // ── 5. TITIP BELANJA ──
    {
      'cat': 'errands',
      'tag': 'Titip Belanja',
      'q': 'Bagaimana cara membuka sesi titip belanja untuk tetangga?',
      'a': 'Buka menu Titip Belanja > Buka Sesi Belanja. Masukkan toko tujuan (misal: Pasar Tradisional) dan batas waktu (cutoff). Tetangga satu RT dapat menitip barang sebelum batas waktu berakhir.'
    },
    {
      'cat': 'errands',
      'tag': 'Titip Belanja',
      'q': 'Bagaimana perhitungan harga riil belanjaan & biaya tip jasa?',
      'a': 'Pembelanja menginput harga nota belanja riil setelah belanja. Saat barang diserahkan, sistem otomatis mencatat pengeluaran penitip sebesar (Harga Riil + Biaya Jasa) dan pemasukan bagi pembelanja.'
    },

    // ── 6. JUAL BELI & MARKETPLACE ──
    {
      'cat': 'market',
      'tag': 'Marketplace',
      'q': 'Bagaimana cara menjual barang bekas atau menyewakan properti?',
      'a': 'Buka menu Pasar / Marketplace, ketuk Jual Barang. Masukkan foto, deskripsi, harga, dan lokasi. Pengguna sekitar dapat melihat barang Anda dan menghubungi lewat chat langsung.'
    },
    {
      'cat': 'market',
      'tag': 'Domain Toko',
      'q': 'Apakah saya mendapatkan domain etalase website toko gratis?',
      'a': 'Ya! Setiap pengguna memiliki link etalase toko publik yang dapat dibagikan ke calon pembeli di WhatsApp/Instagram.'
    },

    // ── 7. TAGIHAN & HUTANG ──
    {
      'cat': 'debt',
      'tag': 'Tagihan & Hutang',
      'q': 'Bagaimana cara mengaktifkan pengingat jatuh tempo tagihan & hutang?',
      'a': 'Catat tagihan bulanan (listrik, air, wifi) atau hutang piutang. Dashboard akan otomatis memunculkan kartu peringatan jatuh tempo saat mendekati tanggal batas bayar.'
    },

    // ── 8. TRAVELING & VALAS ──
    {
      'cat': 'travel',
      'tag': 'Traveling & Valas',
      'q': 'Bagaimana mencatat pengeluaran trip liburan & kalkulator kurs valas?',
      'a': 'Gunakan menu Traveling untuk membuat grup pengeluaran per liburan. Gunakan shortcut Kurs Terkini di Dashboard untuk menghitung konversi mata uang asing (USD, SGD, MYR ke IDR).'
    },

    // ── 9. TV & HIBURAN ──
    {
      'cat': 'media',
      'tag': 'Multimedia',
      'q': 'Bagaimana cara menonton TV streaming dan memainkan game?',
      'a': 'Di Dashboard tersedia kartu TV Streaming Digital untuk siaran nasional langsung dan Game Hub untuk mini-game santai pengisi waktu luang.'
    },

    // ── 10. KEAMANAN & CADANGAN ──
    {
      'cat': 'secure',
      'tag': 'Keamanan & Backup',
      'q': 'Apakah data saya aman dan bisa dicadangkan jika berganti HP?',
      'a': 'Aplikasi bekerja secara Offline-First dan otomatis sinkron saat online. Anda juga dapat mengekspor file cadangan JSON di menu Pengaturan > Cadangan & Pemulihan.'
    },
  ];

  final List<Map<String, dynamic>> _categories = [
    {'id': 'all', 'label': 'Semua Topik', 'icon': '🌟'},
    {'id': 'finance', 'label': 'Keuangan & Dompet', 'icon': '💵'},
    {'id': 'pos', 'label': 'Kasir POS Bisnis', 'icon': '🛒'},
    {'id': 'rt', 'label': 'Komunitas RT', 'icon': '🏘️'},
    {'id': 'tools', 'label': 'Pinjam Alat', 'icon': '🔨'},
    {'id': 'errands', 'label': 'Titip Belanja', 'icon': '🛍️'},
    {'id': 'market', 'label': 'Jual Beli & Toko', 'icon': '🏷️'},
    {'id': 'debt', 'label': 'Tagihan & Hutang', 'icon': '⏰'},
    {'id': 'travel', 'label': 'Traveling & Valas', 'icon': '✈️'},
    {'id': 'media', 'label': 'TV & Hiburan', 'icon': '🎬'},
    {'id': 'secure', 'label': 'Cadangan & Akun', 'icon': '🛡️'},
  ];

  Color _getTagColor(String cat) {
    switch (cat) {
      case 'finance': return const Color(0xFF059669);
      case 'pos':     return const Color(0xFF4F46E5);
      case 'rt':      return const Color(0xFF0D9488);
      case 'tools':   return const Color(0xFFD97706);
      case 'errands': return const Color(0xFF0284C7);
      case 'market':  return const Color(0xFF9333EA);
      case 'debt':    return const Color(0xFFE11D48);
      case 'travel':  return const Color(0xFF0891B2);
      case 'media':   return const Color(0xFFEA580C);
      default:        return const Color(0xFF475569);
    }
  }

  @override
  Widget build(BuildContext context) {
    final query = _searchCtrl.text.toLowerCase().trim();
    final filteredFaqs = _faqs.where((f) {
      final matchesCat = _selectedCategory == 'all' || f['cat'] == _selectedCategory;
      final text = '${f['q']} ${f['a']} ${f['tag']}'.toLowerCase();
      final matchesQuery = query.isEmpty || text.contains(query);
      return matchesCat && matchesQuery;
    }).toList();

    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        titleSpacing: 0,
        title: const Text('Pusat Bantuan & FAQ', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 18)),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
        children: [
          // ── Hero Banner (Emerald Signature) ──
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF064E3B), Color(0xFF059669), Color(0xFF10B981)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(22),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF059669).withValues(alpha: 0.3),
                  blurRadius: 16,
                  offset: const Offset(0, 6),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.22),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: const Text(
                    '✨ PANDUAN LENGKAP SEMUA FITUR',
                    style: TextStyle(color: Colors.white, fontSize: 10.5, fontWeight: FontWeight.w800, letterSpacing: 0.4),
                  ),
                ),
                const SizedBox(height: 10),
                const Text(
                  'Pertanyaan Sering Diajukan',
                  style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w900, letterSpacing: -0.3),
                ),
                const SizedBox(height: 4),
                Text(
                  'Temukan solusi cepat seputar Keuangan, Scan OCR Struk, Kasir POS, Komunitas RT, Pinjam Alat, dan Titip Belanja.',
                  style: TextStyle(color: Colors.white.withValues(alpha: 0.9), fontSize: 12.5, height: 1.45),
                ),
                const SizedBox(height: 14),
                Material(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(30),
                  child: InkWell(
                    onTap: () => OnboardingDialog.showManual(context),
                    borderRadius: BorderRadius.circular(30),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 9),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: const [
                          Text('🚀', style: TextStyle(fontSize: 14)),
                          SizedBox(width: 6),
                          Text(
                            'Buka Panduan & Tour Interaktif',
                            style: TextStyle(color: Color(0xFF065F46), fontWeight: FontWeight.w800, fontSize: 12),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),

          // ── Search Input ──
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14),
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.border, width: 1.5),
              boxShadow: AppColors.cardShadow,
            ),
            child: Row(
              children: [
                const Icon(Icons.search_rounded, color: AppColors.textMuted, size: 20),
                const SizedBox(width: 10),
                Expanded(
                  child: TextField(
                    controller: _searchCtrl,
                    onChanged: (_) => setState(() => _expandedIndex = null),
                    style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                    decoration: const InputDecoration(
                      hintText: 'Cari kata kunci (misal: scan nota, kode RT, POS)...',
                      hintStyle: TextStyle(color: AppColors.textMuted, fontSize: 13),
                      border: InputBorder.none,
                    ),
                  ),
                ),
                if (_searchCtrl.text.isNotEmpty)
                  GestureDetector(
                    onTap: () {
                      _searchCtrl.clear();
                      setState(() => _expandedIndex = null);
                    },
                    child: const Icon(Icons.cancel, color: AppColors.textMuted, size: 18),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 12),

          // ── Category Pills (Horizontal Scroll) ──
          SizedBox(
            height: 38,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: _categories.length,
              separatorBuilder: (_, _) => const SizedBox(width: 8),
              itemBuilder: (ctx, idx) {
                final c = _categories[idx];
                final isSelected = _selectedCategory == c['id'];
                return Material(
                  color: isSelected ? AppColors.primary : AppColors.card,
                  borderRadius: BorderRadius.circular(30),
                  child: InkWell(
                    borderRadius: BorderRadius.circular(30),
                    onTap: () {
                      setState(() {
                        _selectedCategory = c['id'] as String;
                        _expandedIndex = null;
                      });
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(30),
                        border: Border.all(
                          color: isSelected ? AppColors.primary : AppColors.border,
                          width: 1.5,
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(c['icon'] as String, style: const TextStyle(fontSize: 13)),
                          const SizedBox(width: 6),
                          Text(
                            c['label'] as String,
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                              color: isSelected ? Colors.white : AppColors.textSecondary,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 14),

          // ── FAQ Accordion List (Auto Collapse) ──
          if (filteredFaqs.isEmpty)
            Container(
              padding: const EdgeInsets.all(32),
              decoration: BoxDecoration(
                color: AppColors.card,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.border),
              ),
              child: Column(
                children: [
                  const Text('🔍', style: TextStyle(fontSize: 32)),
                  const SizedBox(height: 8),
                  const Text(
                    'Tidak ada jawaban ditemukan',
                    style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: AppColors.textPrimary),
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    'Coba kata kunci lain atau pilih kategori topik di atas.',
                    style: TextStyle(color: AppColors.textMuted, fontSize: 12),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            )
          else
            ...List.generate(filteredFaqs.length, (idx) {
              final item = filteredFaqs[idx];
              final isExpanded = _expandedIndex == idx;
              final tagColor = _getTagColor(item['cat'] as String);

              return Container(
                margin: const EdgeInsets.only(bottom: 10),
                decoration: BoxDecoration(
                  color: AppColors.card,
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(
                    color: isExpanded ? AppColors.primary : AppColors.border,
                    width: isExpanded ? 1.5 : 1.0,
                  ),
                  boxShadow: AppColors.cardShadow,
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(18),
                  child: Column(
                    children: [
                      InkWell(
                        onTap: () {
                          setState(() {
                            // Auto-collapse behavior
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
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2.5),
                                      decoration: BoxDecoration(
                                        color: tagColor.withValues(alpha: 0.12),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(
                                        item['tag'] as String,
                                        style: TextStyle(
                                          fontSize: 10,
                                          fontWeight: FontWeight.w800,
                                          color: tagColor,
                                        ),
                                      ),
                                    ),
                                    const SizedBox(height: 6),
                                    Text(
                                      item['q'] as String,
                                      style: const TextStyle(
                                        fontSize: 13.5,
                                        fontWeight: FontWeight.w800,
                                        color: AppColors.textPrimary,
                                        height: 1.35,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(width: 8),
                              AnimatedRotation(
                                duration: const Duration(milliseconds: 250),
                                turns: isExpanded ? 0.5 : 0.0,
                                child: Container(
                                  width: 28,
                                  height: 28,
                                  decoration: BoxDecoration(
                                    color: isExpanded
                                        ? AppColors.primary.withValues(alpha: 0.12)
                                        : AppColors.bg,
                                    shape: BoxShape.circle,
                                  ),
                                  child: Icon(
                                    Icons.keyboard_arrow_down_rounded,
                                    size: 18,
                                    color: isExpanded ? AppColors.primary : AppColors.textMuted,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      if (isExpanded) ...[
                        Container(
                          height: 1,
                          color: AppColors.border,
                        ),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(16),
                          color: AppColors.bg,
                          child: Text(
                            item['a'] as String,
                            style: const TextStyle(
                              fontSize: 13,
                              color: AppColors.textSecondary,
                              height: 1.55,
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              );
            }),
        ],
      ),
    );
  }
}
