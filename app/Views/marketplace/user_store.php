<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.user-store-page {
    padding: 14px 16px 110px;
    max-width: 860px;
    margin: 0 auto;
}

.store-hero-card {
    background: linear-gradient(135deg, #1E1B4B 0%, #312E81 50%, #4338CA 100%);
    border-radius: 22px;
    padding: 24px 20px;
    color: #fff;
    margin-bottom: 20px;
    box-shadow: 0 10px 28px rgba(67, 56, 202, 0.25);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    position: relative;
    overflow: hidden;
}
.store-hero-card::after {
    content: '';
    position: absolute;
    right: -20px;
    bottom: -20px;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
}

.store-profile-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.store-avatar-big {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid rgba(255, 255, 255, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 900;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
}
.store-name-title {
    font-size: 20px;
    font-weight: 900;
    margin: 0 0 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.store-domain-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(6px);
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 11.5px;
    font-weight: 700;
}

.store-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}
.btn-store-wa {
    background: #22C55E;
    color: #fff;
    padding: 10px 16px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}
.btn-share-store {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #fff;
    padding: 10px 14px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.store-listings-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Listings Grid */
.listings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
    gap: 14px;
}
.listing-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 18px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: inherit;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.listing-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}
.listing-thumb-wrap {
    width: 100%;
    height: 160px;
    background: #1F2937;
    position: relative;
    overflow: hidden;
}
.listing-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.badge-type {
    position: absolute;
    top: 10px;
    left: 10px;
    padding: 4px 9px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
}
.badge-type.sale { background: #059669; color: #fff; }
.badge-type.rent { background: #6D28D9; color: #fff; }

.listing-body {
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.listing-title {
    font-size: 14px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 6px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.listing-price {
    font-size: 16px;
    font-weight: 900;
    color: #059669;
    margin-bottom: 8px;
}
[data-theme="dark"] .listing-price { color: #34D399; }

/* APK RECOMMEND POPUP */
.apk-popup-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(6px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s ease;
}
.apk-popup-overlay.show {
    opacity: 1;
    pointer-events: auto;
}
.apk-popup-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 24px;
    max-width: 440px;
    width: 100%;
    padding: 24px;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35);
    position: relative;
    transform: translateY(20px);
    transition: transform 0.25s ease;
}
.apk-popup-overlay.show .apk-popup-card {
    transform: translateY(0);
}
.apk-popup-close {
    position: absolute;
    top: 14px; right: 14px;
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text-muted);
    width: 32px; height: 32px;
    border-radius: 50%;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.apk-badge-top {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(16, 185, 129, 0.12);
    color: #10B981;
    font-size: 11.5px;
    font-weight: 800;
    padding: 4px 10px;
    border-radius: 999px;
    margin-bottom: 12px;
}
.apk-popup-title {
    font-size: 18px;
    font-weight: 900;
    color: var(--text);
    margin: 0 0 6px;
    line-height: 1.3;
}
.apk-popup-sub {
    font-size: 12.5px;
    color: var(--text-muted);
    line-height: 1.45;
    margin-bottom: 16px;
}
.install-guide-steps {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 14px;
    margin-bottom: 18px;
}
.install-step-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 8px;
}
.install-step-item:last-child { margin-bottom: 0; }
.step-num {
    background: #4338CA;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    width: 18px; height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 2px;
}
.step-text {
    font-size: 12px;
    color: var(--text);
    line-height: 1.35;
}
.apk-actions-grid {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.btn-dl-apk {
    background: linear-gradient(135deg, #059669 0%, #10B981 100%);
    color: #fff;
    font-size: 14px;
    font-weight: 800;
    padding: 13px;
    border-radius: 14px;
    text-align: center;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.btn-stay-web {
    background: transparent;
    border: none;
    color: var(--text-muted);
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    text-align: center;
    margin-top: 4px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="user-store-page">

    <!-- SELLER HERO CARD -->
    <div class="store-hero-card">
        <div class="store-profile-left">
            <div class="store-avatar-big" style="background:<?= esc($avatar['color'] ?? '#2D5A27') ?>;">
                <?php if (!empty($avatarImg)): ?>
                    <img src="<?= esc($avatarImg) ?>" style="width:100%;height:100%;object-fit:cover;" alt="Avatar">
                <?php else: ?>
                    <?= esc($avatar['initials'] ?? 'U') ?>
                <?php endif; ?>
            </div>
            <div>
                <h1 class="store-name-title">
                    <?= esc($seller['name']) ?>
                    <span title="Terverifikasi">✨</span>
                </h1>
                <div class="store-domain-pill">
                    <span>🌐 <?= current_url() ?></span>
                </div>
            </div>
        </div>

        <div class="store-actions">
            <?php
                $waNum = preg_replace('/[^0-9]/', '', $seller['phone'] ?? '');
                if (str_starts_with($waNum, '0')) $waNum = '62' . substr($waNum, 1);
            ?>
            <?php if (!empty($waNum)): ?>
                <a href="https://wa.me/<?= $waNum ?>?text=Halo%20<?= urlencode($seller['name']) ?>,%20saya%20melihat%20katalog%20toko%20Anda%20di%20DuitKu" target="_blank" class="btn-store-wa">
                    <span>💬 Hubungi Penjual</span>
                </a>
            <?php endif; ?>

            <button onclick="copyStoreLink()" class="btn-share-store">
                <span>🔗 Bagikan Toko</span>
            </button>
        </div>
    </div>

    <!-- LISTINGS TITLE -->
    <div class="store-listings-title">
        <span>Katalog Produk & Iklan Aktif</span>
        <span style="font-size:13px;font-weight:700;color:var(--text-muted);"><?= count($listings) ?> Produk</span>
    </div>

    <?php if (empty($listings)): ?>
        <div style="text-align:center;padding:50px 20px;background:var(--card);border:1px dashed var(--border);border-radius:20px;">
            <div style="font-size:42px;margin-bottom:10px;">🏷️</div>
            <div style="font-size:16px;font-weight:800;color:var(--text);margin-bottom:4px;">Belum Ada Iklan Aktif</div>
            <div style="font-size:13px;color:var(--text-muted);">Pengguna ini belum memajang produk untuk dijual atau disewakan saat ini.</div>
        </div>
    <?php else: ?>
        <div class="listings-grid">
            <?php foreach ($listings as $item): ?>
                <a href="/marketplace/item/<?= esc($item['id']) ?>" class="listing-card">
                    <div class="listing-thumb-wrap">
                        <?php if (!empty($item['primary_image'])): ?>
                            <img src="<?= esc($item['primary_image']) ?>" alt="<?= esc($item['title']) ?>" class="listing-thumb" loading="lazy">
                        <?php else: ?>
                            <div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:32px;">🏷️</div>
                        <?php endif; ?>
                        <div class="badge-type <?= esc($item['type']) ?>">
                            <?= $item['type'] === 'rent' ? 'SEWA' : 'JUAL' ?>
                        </div>
                    </div>
                    <div class="listing-body">
                        <div style="font-size:11px;font-weight:700;color:var(--text-muted);margin-bottom:4px;"><?= esc($item['category']) ?></div>
                        <div class="listing-title"><?= esc($item['title']) ?></div>
                        <div class="listing-price">
                            <?= $symbol ?> <?= number_format($item['price'], 0, ',', '.') ?>
                            <?php if ($item['type'] === 'rent' && !empty($item['rent_period'])): ?>
                                <span style="font-size:12px;font-weight:600;color:var(--text-muted);">/<?= esc($item['rent_period']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:11.5px;color:var(--text-muted);margin-top:auto;">
                            📍 <?= esc($item['location']) ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- SMART POPUP REKOMENDASI INSTALL APK & PANDUAN -->
<div class="apk-popup-overlay" id="apkRecommendModal">
    <div class="apk-popup-card">
        <button class="apk-popup-close" onclick="closeApkModal()" title="Tutup">✕</button>
        
        <div class="apk-badge-top">
            <span>✨ Aplikasi Android Resmi</span>
        </div>
        
        <h3 class="apk-popup-title">Lebih Cepat & Praktis di Aplikasi DuitKu</h3>
        <p class="apk-popup-sub">
            Nikmati akses marketplace jual beli sewa, notifikasi chat, OCR scan struk, dan pencatatan keuangan otomatis tanpa lemot.
        </p>

        <!-- 3 Quick Installation Steps -->
        <div class="install-guide-steps">
            <div style="font-size:11.5px;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:8px;">
                📖 Panduan Singkat Pasang APK:
            </div>
            <div class="install-step-item">
                <div class="step-num">1</div>
                <div class="step-text">Klik tombol <strong>Buka Halaman Rilis</strong> di bawah.</div>
            </div>
            <div class="install-step-item">
                <div class="step-num">2</div>
                <div class="step-text">Pilih & unduh file <strong>.apk</strong> yang sesuai untuk HP Anda.</div>
            </div>
            <div class="install-step-item">
                <div class="step-num">3</div>
                <div class="step-text">Pilih <strong>Izinkan dari sumber ini</strong> jika diminta, lalu klik <strong>Install</strong>. Selesai!</div>
            </div>
        </div>

        <div class="apk-actions-grid">
            <a href="/release" class="btn-dl-apk">
                <span>🚀 Buka Halaman Rilis & Unduh APK</span>
            </a>
            <button class="btn-stay-web" onclick="closeApkModal()">
                Tetap Lanjut Membaca di Browser Web
            </button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function copyStoreLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Tautan toko berhasil disalin ke clipboard!');
    }).catch(() => {
        prompt('Salin link berikut:', url);
    });
}

function closeApkModal() {
    const modal = document.getElementById('apkRecommendModal');
    if (modal) modal.classList.remove('show');
    sessionStorage.setItem('duitku_apk_modal_dismissed', '1');
}

document.addEventListener('DOMContentLoaded', function() {
    const dismissed = sessionStorage.getItem('duitku_apk_modal_dismissed');
    if (!dismissed) {
        setTimeout(function() {
            const modal = document.getElementById('apkRecommendModal');
            if (modal) modal.classList.add('show');
        }, 1500);
    }
});
</script>
<?= $this->endSection() ?>
