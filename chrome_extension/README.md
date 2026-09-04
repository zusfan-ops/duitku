# 🪙 DuitKu - Chrome Extension (Manifest V3)

Ekstensi resmi browser Google Chrome untuk aplikasi pencatatan keuangan **DuitKu**. Memungkinkan Anda memantau saldo, melihat ringkasan pengeluaran/pemasukan, mencatat transaksi cepat tanpa membuka tab baru, serta mencatat transaksi dari blok teks saat belanja online.

---

## 🌟 Fitur Utama

1. **📊 Mini Dashboard**:
   - Total saldo dompet (dengan fitur sembunyikan/tampilkan nominal).
   - Ringkasan pemasukan & pengeluaran bulan ini.
   - Progress bar budget bulanan.
   - 5 riwayat transaksi terbaru.

2. **⚡ Catat Cepat (Quick Add Transaction)**:
   - Input transaksi langsung dari popup ekstensi.
   - Pilihan tipe: Pengeluaran atau Pemasukan.
   - Dropdown kategori & dompet yang tersinkronisasi otomatis dari akun Anda.
   - Format rupiah otomatis saat mengetik nominal.
   - Saldo langsung diperbarui seketika setelah disimpan!

3. **🖱️ Context Menu (Klik Kanan Catat Belanja)**:
   - Blok teks angka harga saat belanja online di marketplace (misal: "Rp 150.000"), klik kanan -> pilih **"Catat ke DuitKu: 'Rp 150.000'"**.
   - Buka popup ekstensi, nominal dan catatan sudah otomatis terisi di form Catat Cepat!

4. **🚀 Pintasan Cepat (Quick Shortcuts)**:
   - Buka Web Dashboard, Riwayat Aktivitas, Statistik, Daftar Belanja, Tabungan, Tugas/Todo, dan modul lainnya dalam 1 klik.

5. **⚙️ Fleksibilitas Server (Cloud & Localhost)**:
   - Bawaan default: `https://duitku.ordr.my.id`.
   - Bisa disesuaikan dengan mudah ke server lokal (misal `http://localhost/duitku` atau `http://localhost:8080`) melalui tab **Opsi** atau saat Login.

---

## 🛠️ Cara Memasang (Instalasi) di Browser

Ekstensi ini dapat dipasang di **Google Chrome**, **Microsoft Edge**, **Brave**, atau browser berbasis Chromium lainnya:

### Langkah-langkah:
1. Buka browser **Google Chrome**.
2. Masuk ke halaman ekstensi dengan mengetik `chrome://extensions` pada address bar (atau klik ikon puzzle/titik tiga di kanan atas > *Extensions* > *Manage Extensions*).
3. Di pojok kanan atas halaman ekstensi, aktifkan toggle **"Developer mode"** (Mode Pengembang).
4. Klik tombol **"Load unpacked"** (Muat ekstensi yang belum dibongkar) di pojok kiri atas.
5. Pilih folder ekstensi ini:
   ```text
   c:\laragon\www\duitku\chrome_extension
   ```
6. Ekstensi **DuitKu** akan langsung muncul di daftar ekstensi!
7. Klik ikon Pin 📌 di menu ekstensi Chrome agar ikon DuitKu selalu muncul di toolbar atas browser.

---

## 📱 Cara Penggunaan

1. Klik ikon **DuitKu** di toolbar browser.
2. Masukkan **Email** dan **Kata Sandi** akun DuitKu Anda.
3. Klik **Masuk**.
4. Setelah masuk, Anda dapat langsung:
   - Memantau saldo dan riwayat transaksi di tab **Ringkasan**.
   - Menambahkan pengeluaran/pemasukan baru di tab **Catat**.
   - Melompat ke halaman web DuitKu di tab **Pintasan**.
