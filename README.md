# DuitKu — Aplikasi Pencatat Keuangan Pribadi

**DuitKu** adalah sistem pencatatan keuangan pribadi yang terdiri dari **backend API berbasis CodeIgniter 4** dan **aplikasi mobile Flutter**. Dirancang untuk membantu user mencatat pemasukan & pengeluaran harian, mengelola dompet/rekening, memantau tagihan & hutang, menyusun daftar belanja, serta mencatat lokasi penyimpanan barang agar tidak lupa.

<p align="center">
  <img alt="CodeIgniter 4" src="https://img.shields.io/badge/CodeIgniter-4-EF4223?logo=codeigniter&logoColor=white">
  <img alt="PHP 8.2" src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white">
  <img alt="MySQL" src="https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white">
  <img alt="Flutter" src="https://img.shields.io/badge/Flutter-3.44+-02569B?logo=flutter&logoColor=white">
  <img alt="Dart" src="https://img.shields.io/badge/Dart-3.12+-0175C2?logo=dart&logoColor=white">
  <img alt="Android" src="https://img.shields.io/badge/Android-APK-3DDC84?logo=android&logoColor=white">
</p>

> 💰 Domain: personal finance tracking — multi-wallet, transaction logging, bill & debt reminders, shopping list, dan item storage tracker.

---

## ✨ Fitur Utama

### 💵 Pencatatan Transaksi
- Tambah transaksi **pemasukan** & **pengeluaran** dengan kategori, dompet, tanggal, dan catatan.
- Edit & hapus transaksi.
- Dashboard menampilkan total saldo, pemasukan & pengeluaran bulan ini, serta grafik harian.

### 👛 Manajemen Dompet / Rekening
- Kelola banyak dompet (contoh: Cash, Bank, E-Wallet).
- Pergerakan saldo real-time berdasarkan transaksi.

### 📊 Statistik & Laporan
- Grafik perbandingan pemasukan vs pengeluaran.
- Kategori pengeluaran terbesar.
- Ringkasan bulanan.

### 🔔 Tagihan & Hutang
- Catat tagihan yang akan jatuh tempo.
- Pantau hutang piutang pribadi.

### 🛒 Daftar Belanja
- Buat daftar belanja per lokasi (pasar, supermarket, warung).
- Tambah barang beserta estimasi harga.
- Tandai barang yang sudah dibeli.
- Sinkronisasi data belanja antara perangkat dan server.

### 📦 Manajemen Barang *(baru)*
- Catat barang yang disimpan beserta **lokasi penyimpanannya**.
- Lampirkan **foto barang** dan **foto lokasi**.
- Penyimpanan foto offline di perangkat.
- Fitur ini berguna agar user tidak lupa saat menyimpan barang.

### 🔐 Autentikasi
- Login & register user.
- Profil user dengan avatar.
- Sesi dikelola melalui API token.

---

## 🏗️ Arsitektur Sistem

DuitKu terdiri dari dua komponen utama yang saling terhubung melalui REST API:

| Komponen | Teknologi | Fungsi |
|---|---|---|
| **Backend API** | CodeIgniter 4, PHP 8.2+ | REST API, autentikasi, bisnis logic, sinkronisasi data. |
| **Database** | MySQL 8 | Menyimpan user, transaksi, dompet, kategori, tagihan, hutang, belanja. |
| **Mobile App** | Flutter 3.44+, Dart 3.12+ | Antarmuka utama untuk user. |
| **Offline Storage** | SharedPreferences | Menyimpan data belanja & barang secara lokal di perangkat. |
| **Image Storage** | Direktori aplikasi | Menyimpan foto barang & lokasi di perangkat. |

---

## 📁 Struktur Folder

```
duitku/                          # backend CodeIgniter 4
├── app/
│   ├── Config/                  # konfigurasi aplikasi
│   ├── Controllers/             # controller web & API
│   │   ├── Api/                 # REST API untuk Flutter
│   │   └── ...                  # controller halaman web
│   ├── Filters/                 # middleware autentikasi API
│   ├── Libraries/               # library custom (ApiAuth)
│   ├── Models/                  # model database
│   └── Database/Migrations/     # skema database
├── public/                      # document root
├── writable/                    # cache, logs, uploads
└── .env                         # konfigurasi environment

duitku_app/                      # aplikasi Flutter
├── android/                     # konfigurasi Android
├── lib/
│   ├── config/                  # konfigurasi API base URL
│   ├── models/                  # model data
│   ├── providers/               # state management (Provider)
│   ├── screens/                 # halaman aplikasi
│   │   ├── barang/              # manajemen barang
│   │   ├── belanja/             # daftar belanja
│   │   └── auth/                # login & register
│   ├── services/                # API service & local storage
│   ├── utils/                   # helper formatting
│   ├── widgets/                 # komponen UI reusable
│   └── main.dart                # entry point
├── pubspec.yaml                 # dependencies Flutter
└── test/                        # unit/widget test
```

---

## 🚀 Instalasi Backend (Lokal)

Prasyarat: **PHP 8.2+, Composer, MySQL 8** (misal via Laragon).

```bash
# 1. Clone repo
git clone https://github.com/zusfan-ops/duitku.git
cd duitku

# 2. Install dependency PHP
composer install

# 3. Salin environment
cp env .env

# 4. Sesuaikan konfigurasi database di .env
# DB_HOST=localhost
# DB_DATABASE=duitku
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Jalankan migrasi
php spark migrate

# 6. Jalankan server lokal
php spark serve
```

Backend akan berjalan di `http://localhost:8080`.

---

## 📱 Menjalankan Aplikasi Flutter

Prasyarat: **Flutter SDK 3.44+, Android SDK / emulator / perangkat**.

```bash
cd duitku_app

# 1. Install dependency
flutter pub get

# 2. Pastikan base URL API sesuai
# Edit: lib/config/api_config.dart

# 3. Jalankan di emulator / perangkat
flutter run
```

### Build APK Release

```bash
cd duitku_app
flutter build apk --release
```

Output APK:
```
build/app/outputs/flutter-apk/app-release.apk
```

---

## 🔒 Keamanan

- API menggunakan filter autentikasi berbasis token (`ApiAuthFilter`).
- Password user di-hash sebelum disimpan.
- File upload (avatar) disimpan di direktori privat dengan akses ber-otorisasi.
- Sebelum produksi: set `CI_ENVIRONMENT=production`, matikan `display_errors`, dan ganti semua kredensial default.

---

## 📄 Lisensi

Perangkat lunak **proprietary/internal**. Dibangun di atas:
- [CodeIgniter 4](https://codeigniter.com) (lisensi MIT)
- [Flutter](https://flutter.dev) (lisensi BSD-3-Clause)

Penggunaan, distribusi, atau modifikasi di luar pemilik repo memerlukan izin tertulis.

---

<p align="center"><sub>DuitKu · Personal Finance Tracker · Built with Flutter & CodeIgniter 4</sub></p>
