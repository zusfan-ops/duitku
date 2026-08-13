# 🎉 DuitKu v1.2.0

### 🚀 Fitur Baru
- **REST API lengkap** untuk aplikasi mobile — autentikasi token, dashboard, aktivitas, transaksi, dompet, hutang/piutang, tagihan, kategori, pengaturan, statistik, dan sinkronisasi belanja.
- **Manajemen Barang** — catat lokasi penyimpanan barang lengkap dengan foto (item & lokasi), tersimpan offline di perangkat.
- **Traveling** — kelola perjalanan dalam satu fitur: checklist barang bawaan, scan & simpan tiket pesawat/kereta/bus/kapal, serta pencatatan pengeluaran/pemasukan khusus trip yang otomatis terintegrasi dengan keuangan utama dan menampilkan total cost per destinasi.
- **Kartu "🏆 Top Kategori Pengeluaran"** di Dashboard — lihat sekilas kategori pengeluaran terbesar bulan ini tanpa perlu buka halaman Statistik.
- **Ikon aplikasi & splash screen Android baru**, memakai logo resmi DuitKu (termasuk splash screen Android 12+).

### 🎨 Desain Ulang
- **Bottom navigation baru** bergaya *Dynamic Island* — pill mengambang berwarna gelap dengan highlight animasi pada tab aktif.
- **Halaman Login & Daftar didesain ulang** — hero header bergradasi hijau + kartu manfaat berwarna-warni yang menjelaskan alasan mendaftar di DuitKu.
- **Kartu di seluruh aplikasi** (Dashboard, Statistik, Pengaturan, Hutang & Piutang, Dompet, Tagihan, Belanja, daftar transaksi) kini memakai bayangan halus (elevation) menggantikan border datar, untuk tampilan yang lebih premium dan mudah dibaca.

### 🐛 Perbaikan Bug
- Dashboard tidak lagi *crash* akibat field `monthKey` yang hilang pada grafik tren saldo.
- Kartu pengingat tagihan tidak lagi *crash* saat menampilkan data tagihan jatuh tempo.
- Daftar transaksi di Aktivitas & Dashboard tidak lagi gagal tampil (gray screen) akibat locale tanggal Indonesia yang belum diinisialisasi.
- Tombol "+ Tambah" di halaman Belanja tidak lagi tersembunyi di balik navigasi bawah.

### 🧹 Lainnya
- README diperbarui dengan badge dan ringkasan sistem, ditambah bagian profil developer.
- Direktori `backupappconfig/` dikeluarkan dari version control.

**Full Changelog**: https://github.com/zusfan-ops/duitku/compare/v1.1.0...v1.2.0
