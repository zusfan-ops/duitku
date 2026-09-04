<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.download-page {
    padding: 14px 16px 110px;
    max-width: 680px;
    margin: 0 auto;
}

.dl-hero-card {
    background: linear-gradient(135deg, #065F46 0%, #059669 50%, #10B981 100%);
    border-radius: 22px;
    padding: 26px 22px;
    color: #fff;
    margin-bottom: 20px;
    box-shadow: 0 10px 28px rgba(5, 150, 105, 0.3);
    text-align: center;
}
.dl-app-icon {
    width: 72px;
    height: 72px;
    border-radius: 18px;
    background: #fff;
    padding: 6px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    margin: 0 auto 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.dl-app-icon img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.dl-title {
    font-size: 22px;
    font-weight: 900;
    margin: 0 0 6px;
}
.dl-sub {
    font-size: 13px;
    opacity: 0.92;
    line-height: 1.45;
    max-width: 440px;
    margin: 0 auto 20px;
}
.dl-button-main {
    background: #fff;
    color: #065F46;
    font-size: 15px;
    font-weight: 900;
    padding: 14px 28px;
    border-radius: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    transition: transform 0.15s ease;
}
.dl-button-main:hover {
    transform: scale(1.03);
    color: #065F46;
}
.dl-meta-info {
    font-size: 12px;
    opacity: 0.85;
    margin-top: 10px;
}

.guide-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 22px;
    margin-bottom: 20px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
}
.guide-title {
    font-size: 16px;
    font-weight: 900;
    color: var(--text);
    margin: 0 0 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.guide-step {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    margin-bottom: 18px;
}
.guide-step:last-child {
    margin-bottom: 0;
}
.step-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #4338CA;
    color: #fff;
    font-size: 14px;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.step-desc h4 {
    font-size: 14px;
    font-weight: 800;
    color: var(--text);
    margin: 0 0 4px;
}
.step-desc p {
    font-size: 12.5px;
    color: var(--text-muted);
    margin: 0;
    line-height: 1.45;
}

.github-box {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 16px;
    text-align: center;
}
.btn-gh {
    background: var(--card);
    border: 1px solid var(--border);
    color: var(--text);
    padding: 10px 18px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="download-page">

    <!-- HERO DOWNLOAD -->
    <div class="dl-hero-card">
        <div class="dl-app-icon">
            <img src="/images/logo.png" alt="DuitKu Icon" onerror="this.src='/logo.png'">
        </div>
        <h1 class="dl-title">Unduh DuitKu Android</h1>
        <p class="dl-sub">
            Solusi lengkap pencatatan keuangan, deteksi mutasi bank, kasir POS UMKM, dan pasar komunitas Jual Beli & Sewa.
        </p>

        <a href="<?= esc($releaseInfo['apk_download']) ?>" class="dl-button-main">
            <span>📥 Unduh File APK (<?= esc($releaseInfo['version']) ?>)</span>
        </a>
        <div class="dl-meta-info">
            Ukuran: <?= esc($releaseInfo['file_size']) ?> • Kompatibel: Android 7.0+
        </div>
    </div>

    <!-- INSTALLATION GUIDE STEPS -->
    <div class="guide-card">
        <div class="guide-title">
            <span>📖</span> Panduan Cara Pasang APK di Android
        </div>

        <div class="guide-step">
            <div class="step-circle">1</div>
            <div class="step-desc">
                <h4>Unduh File APK</h4>
                <p>Klik tombol unduh di atas. Jika browser (Chrome) memunculkan peringatan <em>"File ini mungkin berbahaya"</em>, klik <strong>Tetap Unduh / Download Anyway</strong> karena APK ini berasal dari rilis resmi DuitKu.</p>
            </div>
        </div>

        <div class="guide-step">
            <div class="step-circle">2</div>
            <div class="step-desc">
                <h4>Buka Notifikasi Unduhan</h4>
                <p>Setelah selesai diunduh, usap layar dari atas ke bawah untuk membuka panel notifikasi HP Anda, lalu ketuk file APK yang baru diunduh.</p>
            </div>
        </div>

        <div class="guide-step">
            <div class="step-circle">3</div>
            <div class="step-desc">
                <h4>Izinkan Pemasangan dari Sumber Ini</h4>
                <p>Jika HP menampilkan peringatan keamanan, masuk ke <em>Pengaturan</em> dan aktifkan toggle <strong>"Izinkan pemasangan aplikasi dari sumber ini"</strong>.</p>
            </div>
        </div>

        <div class="guide-step">
            <div class="step-circle">4</div>
            <div class="step-desc">
                <h4>Selesai & Buka Aplikasi</h4>
                <p>Ketuk tombol <strong>Install</strong> dan tunggu beberapa detik hingga selesai. Aplikasi DuitKu kini siap digunakan langsung di layar beranda ponsel Anda!</p>
            </div>
        </div>
    </div>

    <!-- GITHUB RELEASES LINK -->
    <div class="github-box">
        <div style="font-size:13.5px;font-weight:800;color:var(--text);">Repositori Rilis Resmi:</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Lihat riwayat rilis, changelog, dan source code di GitHub.</div>
        <a href="<?= esc($releaseInfo['github_releases']) ?>" target="_blank" rel="noopener noreferrer" class="btn-gh">
            <span>🐙 Buka GitHub Releases (zusfan-ops/duitku)</span>
        </a>
    </div>

</div>
<?= $this->endSection() ?>
