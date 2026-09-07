<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.policy-page {
    max-width: 720px;
    margin: 0 auto;
    padding: 24px 16px 120px;
}
.policy-hero {
    background: linear-gradient(135deg, #064E3B 0%, #059669 60%, #10B981 100%);
    border-radius: var(--radius-xl, 24px);
    padding: 28px 24px;
    color: #fff;
    margin-bottom: 24px;
    box-shadow: 0 12px 32px rgba(5,150,105,0.28);
    position: relative;
    overflow: hidden;
}
.policy-hero::after {
    content: '';
    position: absolute;
    right: -25px;
    bottom: -25px;
    width: 140px;
    height: 140px;
    background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
}
.policy-hero h1 {
    font-size: 24px;
    font-weight: 900;
    margin-bottom: 6px;
    letter-spacing: -0.4px;
}
.policy-hero p {
    font-size: 13px;
    opacity: 0.9;
    line-height: 1.5;
}
.policy-hero .pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.35);
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.4px;
    margin-bottom: 10px;
    color: #fff;
}
.policy-section {
    background: var(--bg-card, #fff);
    border: 1px solid var(--border, #E5E7EB);
    border-radius: 18px;
    padding: 22px 20px;
    margin-bottom: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.policy-section h2 {
    font-size: 16px;
    font-weight: 800;
    margin-bottom: 10px;
    color: var(--text-primary, #0F172A);
}
.policy-section h3 {
    font-size: 14px;
    font-weight: 700;
    margin: 14px 0 6px;
    color: var(--text-primary, #0F172A);
}
.policy-section p, .policy-section li {
    font-size: 13px;
    line-height: 1.7;
    color: var(--text-secondary, #475569);
    margin-bottom: 8px;
}
.policy-section ul {
    padding-left: 20px;
    margin-bottom: 10px;
}
.policy-section li {
    margin-bottom: 4px;
}
.policy-section .highlight {
    background: #F0FDF4;
    border-left: 4px solid #059669;
    padding: 12px 16px;
    border-radius: 0 12px 12px 0;
    margin: 12px 0;
}
.policy-section .highlight p {
    margin: 0;
    font-weight: 600;
    color: #065F46;
}
.policy-footer {
    text-align: center;
    padding: 20px 0;
    font-size: 12px;
    color: var(--text-muted, #94A3B8);
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="policy-page">

    <div class="policy-hero">
        <div class="pill">🔒 PRIVASI</div>
        <h1>Kebijakan Privasi</h1>
        <p>Terakhir diperbarui: <?= date('d F Y') ?></p>
        <p style="margin-top:8px;">Kami menghargai privasi kamu. Kebijakan ini menjelaskan bagaimana DuitKu mengumpulkan, menggunakan, dan melindungi data pribadimu.</p>
    </div>

    <div class="policy-section">
        <h2>1. Informasi yang Kami Kumpulkan</h2>
        <h3>Data yang kamu berikan langsung:</h3>
        <ul>
            <li><strong>Akun:</strong> Nama, email, nomor telepon, dan password (dienkripsi).</li>
            <li><strong>Profil:</strong> Foto avatar, mata uang, dan preferensi.</li>
            <li><strong>Transaksi:</strong> Data pemasukan, pengeluaran, transfer, dan catatan keuangan yang kamu input.</li>
            <li><strong>Marketplace:</strong> Foto produk, deskripsi, harga, dan lokasi COD yang kamu unggah.</li>
            <li><strong>Pesan:</strong> Pesan teks dan gambar yang kamu kirim melalui fitur chat.</li>
            <li><strong>Komunitas RT:</strong> Data keanggotaan, iuran kas, dan diskusi lingkungan.</li>
        </ul>
        <h3>Data yang dikumpulkan otomatis:</h3>
        <ul>
            <li><strong>Perangkat:</strong> Model device, versi OS, dan token FCM untuk push notification.</li>
            <li><strong>Log:</strong> Waktu login, aktivitas API, dan error logs (untuk debugging).</li>
            <li><strong>Widget:</strong> Data saldo yang ditampilkan di home screen widget Android.</li>
        </ul>

        <div class="highlight">
            <p>💡 Kami TIDAK mengumpulkan data lokasi GPS, riwayat browsing, atau data biometrik.</p>
        </div>
    </div>

    <div class="policy-section">
        <h2>2. Bagaimana Kami Menggunakan Data</h2>
        <ul>
            <li>Menyediakan dan mengoperasikan layanan DuitKu.</li>
            <li>Menyinkronkan data keuangan kamu antar perangkat.</li>
            <li>Mengirim push notification untuk pengingat tagihan dan pesan baru.</li>
            <li>Menampilkan widget saldo di layar beranda Android.</li>
            <li>Memproses OCR untuk scan struk belanja (diproses di server).</li>
            <li>Menjalankan transaksi di marketplace dan POS.</li>
            <li>Memverifikasi akun dan mencegah penipuan.</li>
            <li>Meningkatkan kualitas layanan dan memperbaiki bug.</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>3. Penyimpanan & Keamanan Data</h2>
        <ul>
            <li>Data tersimpan di server berlokasi di <strong>Indonesia</strong>.</li>
            <li>Password dienkripsi menggunakan <strong>bcrypt</strong> (tidak bisa dibaca siapapun, termasuk admin).</li>
            <li>Koneksi API menggunakan <strong>Bearer Token</strong> authentication.</li>
            <li>Data keuangan hanya bisa diakses oleh akun yang bersangkutan (isolasi per-user).</li>
            <li>Data cadangan (backup) tersimpan terenkripsi dan hanya bisa diakses oleh kamu.</li>
            <li>Kami melakukan backup berkala untuk mencegah kehilangan data.</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>4. Berbagi Data dengan Pihak Ketiga</h2>
        <p>Kami <strong>TIDAK menjual</strong> atau menyewakan data pribadimu kepada pihak ketiga.</p>
        <p>Data hanya dibagikan dalam situasi berikut:</p>
        <ul>
            <li><strong>Firebase Cloud Messaging:</strong> Token perangkat dikirim ke Google untuk pengiriman push notification.</li>
            <li><strong>QR Code:</strong> Data transaksi QRIS ditampilkan sebagai QR statis untuk pembayaran manual.</li>
            <li><strong>Kepatuhan hukum:</strong> Jika diwajibkan oleh hukum atau perintah pengadilan Indonesia.</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>5. Hak Kamu atas Data</h2>
        <p>Kamu memiliki hak penuh atas data pribadimu:</p>
        <ul>
            <li><strong>Akses:</strong> Lihat semua data kamu melalui menu Export (PDF/CSV) atau Backup (JSON).</li>
            <li><strong>Ubah:</strong> Edit profil, kategori, dan semua data keuangan kapan saja.</li>
            <li><strong>Hapus:</strong> Hapus akun dan semua data secara permanen melalui menu Pengaturan.</li>
            <li><strong>Eksport:</strong> Unduh semua data dalam format PDF, CSV, atau JSON.</li>
        </ul>
        <div class="highlight">
            <p>🗑️ Saat kamu menghapus akun, SEMUA data akan dihapus permanen dari server dalam 30 hari. Tidak ada data yang disimpan setelah itu.</p>
        </div>
    </div>

    <div class="policy-section">
        <h2>6. Marketplace & Chat</h2>
        <ul>
            <li>Listing marketplace kamu hanya terlihat oleh pengguna DuitKu yang login.</li>
            <li>Pesan chat hanya tersimpan di server dan hanya bisa diakses oleh peserta obrolan.</li>
            <li>Foto profil dan nama ditampilkan ke pengguna lain saat kamu berinteraksi di marketplace atau chat.</li>
            <li>Kamu bisa menghapus percakapan dan listing kapan saja.</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>7. Anak di Bawah Umur</h2>
        <p>DuitKu tidak dikhususkan untuk anak di bawah usia 13 tahun. Kami tidak secara sengaja mengumpulkan data dari anak di bawah umur.</p>
    </div>

    <div class="policy-section">
        <h2>8. Cookie & Teknologi Serupa</h2>
        <p>DuitKu menggunakan <strong>Service Worker</strong> untuk fitur PWA (Progressive Web App) dan widget home screen. Service Worker hanya menyimpan data secara lokal di perangkat kamu dan tidak mengirim data ke pihak ketiga.</p>
    </div>

    <div class="policy-section">
        <h2>9. Perubahan Kebijakan</h2>
        <p>Kami dapat memperbarui kebijakan privasi ini dari waktu ke waktu. Perubahan akan diinformasikan melalui push notification dan/atau banner di aplikasi.</p>
    </div>

    <div class="policy-section">
        <h2>10. Hubungi Kami</h2>
        <p>Jika kamu memiliki pertanyaan tentang kebijakan privasi ini, silakan hubungi:</p>
        <ul>
            <li><strong>Developer:</strong> Zusfan Mashuri</li>
            <li><strong>Email:</strong> support@duitku.ordr.my.id</li>
            <li><strong>Website:</strong> <a href="https://duitku.ordr.my.id" style="color:#059669;font-weight:700;">duitku.ordr.my.id</a></li>
        </ul>
    </div>

    <div class="policy-footer">
        <p>© <?= date('Y') ?> DuitKu — Dibuat untuk keluarga Indonesia.</p>
        <p style="margin-top:4px;">Data kamu adalah milik kamu sepenuhnya.</p>
    </div>

</div>
<?= $this->endSection() ?>
