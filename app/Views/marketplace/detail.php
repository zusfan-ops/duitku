<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.market-detail-page {
    padding: 14px 16px 110px;
    max-width: 760px;
    margin: 0 auto;
}

/* Breadcrumb */
.detail-breadcrumb {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    margin-bottom: 12px;
}
.detail-breadcrumb a {
    color: var(--text-muted);
    text-decoration: none;
}
.detail-breadcrumb a:hover {
    color: var(--primary);
}

/* Gallery Section */
.gallery-container {
    background: #111827;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 16px;
    border: 1px solid var(--border);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}
.main-image-wrap {
    width: 100%;
    height: 380px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #000;
}
.main-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: opacity 0.2s ease;
}
.gallery-badge-type {
    position: absolute;
    top: 14px;
    left: 14px;
    padding: 6px 12px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}
.gallery-badge-type.sale {
    background: #059669;
    color: #fff;
}
.gallery-badge-type.rent {
    background: #6D28D9;
    color: #fff;
}
.gallery-badge-type.service {
    background: #2563EB;
    color: #fff;
}


/* Thumbnails row */
.thumb-row {
    display: flex;
    gap: 8px;
    padding: 10px 14px;
    background: #1F2937;
    overflow-x: auto;
}
.thumb-item {
    width: 64px;
    height: 64px;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    opacity: 0.7;
    transition: all 0.15s ease;
    flex-shrink: 0;
}
.thumb-item.active {
    border-color: #10B981;
    opacity: 1;
    transform: scale(1.05);
}
.thumb-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Main Details Card */
.detail-main-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.04);
}
.detail-cat-cond {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    flex-wrap: wrap;
}
.detail-pill {
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 11.5px;
    font-weight: 700;
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text-muted);
}
.detail-title {
    font-size: 22px;
    font-weight: 900;
    color: var(--text);
    margin: 0 0 10px;
    line-height: 1.3;
}
.detail-price {
    font-size: 24px;
    font-weight: 900;
    color: #059669;
    margin-bottom: 14px;
    display: flex;
    align-items: baseline;
    gap: 6px;
}
[data-theme="dark"] .detail-price {
    color: #34D399;
}
.detail-period {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-muted);
}

.detail-meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    padding: 12px 14px;
    background: var(--bg);
    border-radius: 14px;
    border: 1px solid var(--border);
    margin-bottom: 16px;
}
.meta-item {
    font-size: 12px;
    color: var(--text-muted);
}
.meta-item strong {
    color: var(--text);
    display: block;
    font-size: 13px;
    margin-top: 2px;
}

.detail-desc-title {
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    margin-bottom: 6px;
}
.detail-desc-text {
    font-size: 14px;
    line-height: 1.65;
    color: var(--text);
    word-break: break-word;
}
.detail-desc-text p {
    margin: 0 0 10px;
}
.detail-desc-text p:last-child {
    margin-bottom: 0;
}
.detail-desc-text ul, .detail-desc-text ol {
    margin: 6px 0 10px 22px;
    padding: 0;
}
.detail-desc-text li {
    margin-bottom: 3px;
}
.detail-desc-text h1, .detail-desc-text h2, .detail-desc-text h3 {
    margin: 14px 0 6px;
    color: var(--text);
    font-weight: 800;
}
.detail-desc-text blockquote {
    border-left: 3px solid #059669;
    background: rgba(5, 150, 105, 0.08);
    padding: 6px 12px;
    margin: 10px 0;
    border-radius: 4px;
    font-style: italic;
    color: var(--text);
}
.detail-desc-text code {
    background: var(--card);
    border: 1px solid var(--border);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12.5px;
    font-family: monospace;
}
.detail-desc-text hr {
    border: none;
    border-top: 1px dashed var(--border);
    margin: 14px 0;
}

/* ANTI-SCAM SAFETY WARNING (CRITICAL REQUIREMENT) */
.anti-scam-box {
    background: #FEF2F2;
    border: 2px solid #FCA5A5;
    border-radius: 18px;
    padding: 16px;
    margin-bottom: 18px;
    display: flex;
    gap: 14px;
    align-items: flex-start;
}
[data-theme="dark"] .anti-scam-box {
    background: rgba(220, 38, 38, 0.12);
    border-color: rgba(220, 38, 38, 0.4);
}
.anti-scam-icon {
    font-size: 26px;
    line-height: 1;
    flex-shrink: 0;
}
.anti-scam-content h4 {
    font-size: 13.5px;
    font-weight: 900;
    color: #B91C1C;
    margin: 0 0 4px;
}
[data-theme="dark"] .anti-scam-content h4 {
    color: #F87171;
}
.anti-scam-content ul {
    margin: 6px 0 0;
    padding-left: 18px;
    font-size: 12.5px;
    line-height: 1.5;
    color: var(--text);
}
.anti-scam-content li {
    margin-bottom: 3px;
}

/* Seller Card */
.seller-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 16px;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.seller-info-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.seller-avatar-wrap {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    color: #fff;
    font-size: 18px;
    flex-shrink: 0;
}
.seller-details h4 {
    font-size: 14.5px;
    font-weight: 800;
    margin: 0 0 3px;
    color: var(--text);
}
.seller-domain-link {
    font-size: 12px;
    font-weight: 700;
    color: #6366F1;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.seller-domain-link:hover {
    text-decoration: underline;
}

/* Action Buttons Bar */
.action-buttons-wrap {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 18px;
}
.btn-action-primary {
    padding: 14px;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 800;
    text-align: center;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: none;
    cursor: pointer;
    transition: transform 0.15s ease;
}
.btn-action-primary:active {
    transform: scale(0.98);
}
.btn-wa {
    background: #22C55E;
    color: #fff;
    box-shadow: 0 4px 14px rgba(34, 197, 94, 0.3);
}
.btn-order {
    background: #4338CA;
    color: #fff;
    box-shadow: 0 4px 14px rgba(67, 56, 202, 0.3);
}
.btn-shopee {
    grid-column: 1 / -1;
    background: #EE4D2D;
    color: #fff;
    box-shadow: 0 4px 14px rgba(238, 77, 45, 0.3);
}

/* Share Section */
.share-section {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 14px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
}
.btn-share-wa {
    background: #25D366;
    color: #fff;
    padding: 8px 14px;
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-copy-link {
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text);
    padding: 8px 14px;
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* Comments Section */
.comments-section {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 18px;
    margin-bottom: 24px;
}
.comments-title {
    font-size: 15px;
    font-weight: 800;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.comment-form {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
}
.comment-input {
    flex: 1;
    padding: 10px 14px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    font-size: 13px;
    color: var(--text);
    outline: none;
}
.btn-send-comment {
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 10px 18px;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
}
.comment-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.comment-item {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border);
}
.comment-item:last-child {
    border-bottom: none;
}
.comment-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    flex-shrink: 0;
}
.comment-author {
    font-size: 12.5px;
    font-weight: 800;
    color: var(--text);
}
.comment-time {
    font-size: 11px;
    color: var(--text-muted);
    margin-left: 6px;
}
.comment-body {
    font-size: 13px;
    color: var(--text);
    margin-top: 2px;
    line-height: 1.4;
}

/* SMART MODAL POPUP: RECOMMEND INSTALL APK (REQUIREMENT 5) */
.apk-popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
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
    top: 14px;
    right: 14px;
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text-muted);
    width: 32px;
    height: 32px;
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
.install-step-item:last-child {
    margin-bottom: 0;
}
.step-num {
    background: #4338CA;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    width: 18px;
    height: 18px;
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
.btn-release-page {
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text);
    font-size: 12.5px;
    font-weight: 700;
    padding: 10px;
    border-radius: 12px;
    text-align: center;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
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

/* Order Modal */
.order-modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.order-modal-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    max-width: 440px;
    width: 100%;
    padding: 20px;
}

/* Owner Manage Banner */
.owner-manage-banner {
    background: linear-gradient(135deg, rgba(5, 150, 105, 0.08) 0%, rgba(67, 56, 202, 0.08) 100%);
    border: 2px solid rgba(5, 150, 105, 0.35);
    border-radius: 18px;
    padding: 14px 16px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.owner-manage-info {
    display: flex;
    align-items: center;
    gap: 10px;
}
.owner-badge {
    background: #059669;
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 10px;
    border-radius: 999px;
}
.owner-manage-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.btn-owner-action {
    padding: 8px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    text-decoration: none;
    border: 1px solid var(--border);
    background: var(--card);
    color: var(--text);
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease;
}
.btn-owner-action.edit {
    background: #4338CA;
    color: #fff;
    border-color: #4338CA;
}
.btn-owner-action.edit:hover {
    background: #3730A3;
}
.btn-owner-action.status:hover {
    background: var(--border);
}
.btn-owner-action.delete {
    color: #DC2626;
    border-color: rgba(220, 38, 38, 0.3);
}
.btn-owner-action.delete:hover {
    background: #DC2626;
    color: #fff;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="market-detail-page">

    <!-- BREADCRUMB -->
    <div class="detail-breadcrumb">
        <a href="/marketplace">Jual Beli & Sewa</a>
        <span>›</span>
        <span><?= esc($listing['category']) ?></span>
        <span>›</span>
        <span style="color:var(--text);"><?= esc(mb_strimwidth($listing['title'], 0, 30, '...')) ?></span>
    </div>

    <?php if ($isOwner): ?>
        <!-- OWNER MANAGEMENT CARD -->
        <div class="owner-manage-banner">
            <div class="owner-manage-info">
                <span class="owner-badge">👑 Iklan Anda</span>
                <span style="font-size:12px;color:var(--text-muted);">
                    Status: <strong style="color:#059669;text-transform:capitalize;" id="ownerStatusBadge"><?= esc($listing['status']) ?></strong>
                </span>
            </div>
            <div class="owner-manage-actions">
                <a href="/marketplace/edit/<?= esc($listing['id']) ?>" class="btn-owner-action edit">
                    ✏️ Edit Iklan
                </a>
                <?php if ($listing['status'] === 'active'): ?>
                    <button type="button" class="btn-owner-action status" onclick="changeListingStatus(<?= esc($listing['id']) ?>, '<?= $listing['type'] === 'rent' ? 'rented' : 'sold' ?>')">
                        Tandai <?= $listing['type'] === 'rent' ? 'Disewa' : 'Terjual' ?>
                    </button>
                <?php else: ?>
                    <button type="button" class="btn-owner-action status" onclick="changeListingStatus(<?= esc($listing['id']) ?>, 'active')">
                        Aktifkan Lagi
                    </button>
                <?php endif; ?>
                <button type="button" class="btn-owner-action delete" onclick="deleteListingFromDetail(<?= esc($listing['id']) ?>)">
                    🗑️ Hapus
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- 1. IMAGE GALLERY (SUPPORTS > 1 PHOTO) -->
    <div class="gallery-container">
        <?php
            $images = $listing['images'] ?? [];
            $mainImg = !empty($images) ? $images[0]['image_url'] : '';
        ?>
        <div class="main-image-wrap">
            <?php if (!empty($mainImg)): ?>
                <img src="<?= esc($mainImg) ?>" id="mainGalleryImage" class="main-image" alt="<?= esc($listing['title']) ?>">
            <?php else: ?>
                <div style="font-size:54px;">🏷️</div>
            <?php endif; ?>

            <div class="gallery-badge-type <?= esc($listing['type']) ?>">
                <?= $listing['type'] === 'service' ? 'LAYANAN JASA' : ($listing['type'] === 'rent' ? 'SEWA' : 'JUAL') ?>
            </div>
        </div>

        <?php if (count($images) > 1): ?>
            <div class="thumb-row">
                <?php foreach ($images as $idx => $img): ?>
                    <div class="thumb-item <?= $idx === 0 ? 'active' : '' ?>" onclick="switchThumb(this, '<?= esc($img['image_url']) ?>')">
                        <img src="<?= esc($img['image_url']) ?>" alt="Thumbnail">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- 2. PRODUCT / SERVICE DETAILS -->
    <div class="detail-main-card">
        <div class="detail-cat-cond">
            <span class="detail-pill">📁 <?= esc($listing['category']) ?></span>
            <?php if ($listing['type'] === 'service'): ?>
                <span class="detail-pill" style="background:rgba(37,99,235,0.08);color:#2563EB;border-color:rgba(37,99,235,0.25);">
                    <?php
                        $stMap = [
                            'panggilan' => '🛵 Siap Panggilan (Home Service)',
                            'di_tempat' => '🏢 Di Tempat / Workshop',
                            'keduanya'  => '🔄 Panggilan & Di Tempat',
                            'online'    => '💻 Online / Jarak Jauh',
                        ];
                        echo esc($stMap[$listing['service_type'] ?? ''] ?? '🛠️ Layanan Jasa');
                    ?>
                </span>
            <?php else: ?>
                <span class="detail-pill">
                    <?php
                        $condMap = ['new' => 'Baru', 'like_new' => 'Seperti Baru', 'used_good' => 'Bekas Mulus', 'used_fair' => 'Bekas Layak'];
                        echo '✨ ' . ($condMap[$listing['condition']] ?? 'Bekas');
                    ?>
                </span>
            <?php endif; ?>
            <span class="detail-pill">👁️ <?= (int)$listing['views_count'] ?> dilihat</span>
            <span class="detail-pill">🕒 <?= date('d M Y', strtotime($listing['created_at'])) ?></span>
        </div>

        <h1 class="detail-title"><?= esc($listing['title']) ?></h1>

        <div class="detail-price">
            <?= $symbol ?> <?= number_format($listing['price'], 0, ',', '.') ?>
            <?php if ($listing['type'] === 'rent' && !empty($listing['rent_period'])): ?>
                <span class="detail-period">/ <?= esc($listing['rent_period']) ?></span>
            <?php elseif ($listing['type'] === 'service' && !empty($listing['rate_unit'])): ?>
                <?php
                    $rateNames = [
                        'per_sesi'       => 'per sesi (pijat/terapi)',
                        'per_panggilan'  => 'per panggilan',
                        'per_jam'        => 'per jam',
                        'per_unit'       => 'per unit / titik',
                        'per_pekerjaan'  => 'per pekerjaan / borongan',
                        'mulai_dari'     => '(mulai dari)',
                    ];
                    echo '<span class="detail-period">/ ' . esc($rateNames[$listing['rate_unit']] ?? $listing['rate_unit']) . '</span>';
                ?>
            <?php endif; ?>
        </div>

        <?php if ($listing['type'] === 'service'): ?>
            <!-- INFORMASI KHUSUS JASA -->
            <div style="background:rgba(37,99,235,0.04);border:1px solid rgba(37,99,235,0.18);border-radius:14px;padding:14px;margin-bottom:16px;">
                <div style="font-size:12.5px;font-weight:800;color:#2563EB;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                    🛠️ Ketentuan Layanan Jasa
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:12.5px;">
                    <div>
                        <span style="color:var(--text-muted);display:block;font-size:11px;">Jangkauan Area Panggilan:</span>
                        <strong>📍 <?= !empty($listing['service_area']) ? esc($listing['service_area']) : esc($listing['location']) ?></strong>
                    </div>
                    <div>
                        <span style="color:var(--text-muted);display:block;font-size:11px;">Jam Operasional / Ketersediaan:</span>
                        <strong>⏰ <?= !empty($listing['service_hours']) ? esc($listing['service_hours']) : 'Siap Dihubungi' ?></strong>
                    </div>
                    <div>
                        <span style="color:var(--text-muted);display:block;font-size:11px;">Sistem Layanan:</span>
                        <strong><?= esc($stMap[$listing['service_type'] ?? ''] ?? 'Panggilan / Di Tempat') ?></strong>
                    </div>
                    <div>
                        <span style="color:var(--text-muted);display:block;font-size:11px;">Keahlian / Garansi:</span>
                        <strong>🛡️ <?= !empty($listing['experience_years']) ? esc($listing['experience_years']) : 'Sesuai Kesepakatan' ?></strong>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="detail-meta-grid">
                <div class="meta-item">
                    Lokasi COD:
                    <strong>📍 <?= esc($listing['location']) ?></strong>
                </div>
                <div class="meta-item">
                    Status Produk:
                    <strong style="color:#059669;text-transform:capitalize;"><?= esc($listing['status']) ?></strong>
                </div>
            </div>
        <?php endif; ?>

        <div class="detail-desc-title"><?= $listing['type'] === 'service' ? 'Deskripsi Layanan Jasa:' : 'Deskripsi Produk:' ?></div>
        <div class="detail-desc-text" id="detailDescText">
            <?php
                $raw = $listing['description'] ?? '';
                if (!empty($raw)) {
                    if (strip_tags($raw) !== $raw) {
                        $allowedTags = '<p><br><b><strong><i><em><u><s><del><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><pre><code><hr><a>';
                        echo strip_tags($raw, $allowedTags);
                    } else {
                        echo nl2br(esc($raw));
                    }
                } else {
                    echo '<span style="color:var(--text-muted);font-style:italic;">Tidak ada deskripsi tambahan dari penyedia.</span>';
                }
            ?>
        </div>
    </div>

    <!-- 3. CRITICAL ANTI-SCAM WARNING BOX (REQUIREMENT 2) -->
    <div class="anti-scam-box">
        <div class="anti-scam-icon">🛡️</div>
        <div class="anti-scam-content">
            <h4>PANDUAN TRANSAKSI AMAN (ANTI-PENIPUAN)</h4>
            <ul>
                <?php if ($listing['type'] === 'service'): ?>
                    <li><strong>Pembayaran dilakukan setelah jasa/pekerjaan selesai</strong> atau sesuai kesepakatan tertulis yang jelas.</li>
                    <li>Pastikan identitas dan detail alamat sudah disepakati sebelum penyedia jasa berangkat ke lokasi Anda.</li>
                    <li>Simpan riwayat percakapan dan bukti pembayaran untuk kenyamanan bersama.</li>
                <?php else: ?>
                    <li><strong>JANGAN PERNAH mengirim DP / Uang Muka</strong> dalam bentuk apapun sebelum melihat dan mengecek langsung fisik barang!</li>
                    <li><strong>Wajib COD (Ketemu Langsung)</strong> di tempat umum yang aman (seperti SPBU ramai, mall, mini market, atau kantor polisi).</li>
                    <li>Jika berjarak jauh, <strong>gunakan transaksi lewat pihak ketiga (Shopee / Tokopedia)</strong> agar dana Anda aman tersimpan di rekening bersama.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- 4. SELLER PROFILE & UNIQUE DOMAIN LINK (REQUIREMENT 4) -->
    <div class="seller-card">
        <div class="seller-info-left">
            <?php
                $avatarJson = json_decode($listing['seller_avatar'] ?? '', true);
                $avatar = is_array($avatarJson) ? $avatarJson : ['initials' => 'U', 'color' => '#2D5A27'];
            ?>
            <div class="seller-avatar-wrap" style="background:<?= esc($avatar['color'] ?? '#2D5A27') ?>;">
                <?= esc($avatar['initials'] ?? 'U') ?>
            </div>
            <div class="seller-details">
                <h4><?= esc($listing['seller_name']) ?></h4>
                <a href="<?= esc($userStoreUrl) ?>" class="seller-domain-link" title="Buka etalase profil penjual">
                    <span>🏪 Kunjungi Profil (<?= esc($listing['seller_username'] ?: 'toko-' . $listing['user_id']) ?>)</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </a>
            </div>
        </div>
        <div style="font-size:11px;color:var(--text-muted);">
            Bergabung: <?= date('M Y', strtotime($listing['seller_joined_at'] ?? 'now')) ?>
        </div>
    </div>

    <!-- 5. CONTACT & TRANSACTION BUTTONS -->
    <div class="action-buttons-wrap">
        <?php
            $waNumber = preg_replace('/[^0-9]/', '', $listing['whatsapp'] ?: ($listing['seller_phone_registered'] ?? ''));
            if (str_starts_with($waNumber, '0')) $waNumber = '62' . substr($waNumber, 1);
            if ($listing['type'] === 'service') {
                $waMsg = 'Halo ' . $listing['seller_name'] . ', saya ingin bertanya / memesan layanan jasa "' . $listing['title'] . '" di DuitKu (Link: ' . $shareUrl . '). Apakah Anda siap melayani?';
            } else {
                $waMsg = 'Halo ' . $listing['seller_name'] . ', saya berminat dengan produk "' . $listing['title'] . '" di DuitKu (Link: ' . $shareUrl . '). Apakah barang masih tersedia?';
            }
        ?>
        <?php if (!empty($waNumber)): ?>
            <a href="https://wa.me/<?= $waNumber ?>?text=<?= urlencode($waMsg) ?>" target="_blank" class="btn-action-primary btn-wa">
                <span><?= $listing['type'] === 'service' ? '📞 Panggil / Hubungi WhatsApp' : '💬 Chat WhatsApp' ?></span>
            </a>
        <?php endif; ?>

        <?php if (!$isOwner): ?>
            <button onclick="openOrderModal()" class="btn-action-primary btn-order">
                <span><?= $listing['type'] === 'service' ? '📝 Pesan Layanan Jasa' : '📝 Ajukan Minat' ?></span>
            </button>
        <?php else: ?>
            <a href="/marketplace/edit/<?= esc($listing['id']) ?>" class="btn-action-primary btn-order" style="background:#4338CA;">
                <span>✏️ Edit Iklan Ini</span>
            </a>
        <?php endif; ?>

        <?php if (!empty($listing['third_party_url'])): ?>
            <a href="<?= esc($listing['third_party_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn-action-primary btn-shopee">
                <span>🛒 Beli Aman via Shopee / Tokopedia</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- 6. SHARE TO WHATSAPP & PLATFORMS (REQUIREMENT 5) -->
    <div class="share-section">
        <div style="font-size:13px;font-weight:700;color:var(--text);">
            📢 Bagikan Iklan Ini:
        </div>
        <div style="display:flex;gap:8px;">
            <?php
                $shareWaText = 'Lihat iklan "' . $listing['title'] . '" seharga ' . $symbol . ' ' . number_format($listing['price'], 0, ',', '.') . ' di DuitKu: ' . $shareUrl;
            ?>
            <a href="https://wa.me/?text=<?= urlencode($shareWaText) ?>" target="_blank" class="btn-share-wa">
                <span>Share ke WhatsApp</span>
            </a>
            <button onclick="copyShareLink('<?= esc($shareUrl) ?>')" class="btn-copy-link">
                <span>🔗 Salin Link</span>
            </button>
        </div>
    </div>

    <!-- 7. COMMENTS & DISKUSI PRODUK (REQUIREMENT 3) -->
    <div class="comments-section">
        <div class="comments-title">
            <span>💬 Tanya Jawab & Komentar</span>
            <span style="font-size:12px;color:var(--text-muted);">(<?= count($listing['comments'] ?? []) ?>)</span>
        </div>

        <?php if ($userId): ?>
            <form onsubmit="postComment(event, <?= (int)$listing['id'] ?>)" class="comment-form">
                <input type="text" id="commentText" class="comment-input" placeholder="Tanyakan kondisi barang, kelengkapan, atau janjian COD..." required>
                <button type="submit" id="btnComment" class="btn-send-comment">Kirim</button>
            </form>
        <?php else: ?>
            <div style="font-size:12.5px;color:var(--text-muted);background:var(--bg);padding:10px 14px;border-radius:12px;border:1px solid var(--border);margin-bottom:14px;">
                <a href="/login" style="color:var(--primary);font-weight:700;">Login</a> untuk ikut bertanya atau berkomentar mengenai barang ini.
            </div>
        <?php endif; ?>

        <div class="comment-list" id="commentList">
            <?php if (empty($listing['comments'])): ?>
                <div id="emptyComments" style="font-size:12.5px;color:var(--text-muted);text-align:center;padding:14px 0;">
                    Belum ada pertanyaan. Jadilah yang pertama bertanya!
                </div>
            <?php else: ?>
                <?php foreach ($listing['comments'] as $c): ?>
                    <?php
                        $cAvatar = json_decode($c['user_avatar'] ?? '', true);
                        $cColor = $cAvatar['color'] ?? '#4338CA';
                        $cInit  = $cAvatar['initials'] ?? 'U';
                    ?>
                    <div class="comment-item">
                        <div class="comment-avatar" style="background:<?= esc($cColor) ?>;">
                            <?= esc($cInit) ?>
                        </div>
                        <div>
                            <div>
                                <span class="comment-author"><?= esc($c['user_name']) ?></span>
                                <span class="comment-time"><?= date('d M Y H:i', strtotime($c['created_at'])) ?></span>
                            </div>
                            <div class="comment-body"><?= esc($c['comment']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ORDER / INQUIRY MODAL -->
<div class="order-modal-overlay" id="orderModal">
    <div class="order-modal-card">
        <h3 style="margin:0 0 8px;font-size:16px;font-weight:800;">Ajukan Minat Transaksi</h3>
        <p style="font-size:12.5px;color:var(--text-muted);margin:0 0 14px;">
            Sistem akan mencatat pengajuan Anda dan meneruskannya ke penjual (<strong><?= esc($listing['seller_name']) ?></strong>).
        </p>
        <div class="form-group" style="margin-bottom:14px;">
            <label class="form-label" style="font-size:12px;font-weight:700;display:block;margin-bottom:6px;">Catatan Tambahan untuk Penjual:</label>
            <textarea id="orderNotes" class="form-control" rows="3" placeholder="Contoh: Apakah bisa COD di mall sekitar Tebet hari Sabtu besok?"></textarea>
        </div>
        <div style="display:flex;gap:8px;justify-content:flex-end;">
            <button onclick="closeOrderModal()" style="padding:9px 16px;border-radius:10px;border:1px solid var(--border);background:var(--bg);color:var(--text);cursor:pointer;font-weight:700;font-size:12.5px;">Batal</button>
            <button onclick="sendOrder(<?= (int)$listing['id'] ?>)" id="btnSendOrder" style="padding:9px 18px;border-radius:10px;border:none;background:#4338CA;color:#fff;cursor:pointer;font-weight:800;font-size:12.5px;">Kirim Pengajuan</button>
        </div>
    </div>
</div>

<!-- SMART POPUP REKOMENDASI INSTALL APK & PANDUAN (REQUIREMENT 5) -->
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
// Switch main gallery image on thumb click
function switchThumb(elem, imgUrl) {
    document.getElementById('mainGalleryImage').src = imgUrl;
    document.querySelectorAll('.thumb-item').forEach(el => el.classList.remove('active'));
    elem.classList.add('active');
}

// Copy share link
function copyShareLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('Tautan produk berhasil disalin ke clipboard!');
    }).catch(() => {
        prompt('Salin link berikut:', url);
    });
}

// Post comment AJAX
function postComment(e, listingId) {
    e.preventDefault();
    const input = document.getElementById('commentText');
    const comment = input.value.trim();
    if (!comment) return;

    const btn = document.getElementById('btnComment');
    btn.disabled = true;

    const fd = new URLSearchParams();
    fd.append('comment', comment);
    if (window.DUITKU && window.DUITKU.csrfName && window.DUITKU.csrfToken) {
        fd.append(window.DUITKU.csrfName, window.DUITKU.csrfToken);
    }

    const headers = { 
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
    };
    if (window.DUITKU && window.DUITKU.csrfToken) {
        headers['X-CSRF-TOKEN'] = window.DUITKU.csrfToken;
    }

    fetch('/marketplace/comment/' + listingId, {
        method: 'POST',
        headers: headers,
        body: fd.toString()
    })
    .then(async r => {
        const text = await r.text();
        try { return JSON.parse(text); } catch(e) { throw new Error(text.replace(/<[^>]*>?/gm, '').substring(0, 100)); }
    })
    .then(res => {
        btn.disabled = false;
        if (res.success) {
            input.value = '';
            document.getElementById('emptyComments')?.remove();
            
            const list = document.getElementById('commentList');
            const item = document.createElement('div');
            item.className = 'comment-item';
            item.innerHTML = `
                <div class="comment-avatar" style="background:${res.comment.avatar.color || '#4338CA'};">
                    ${res.comment.avatar.initials || 'U'}
                </div>
                <div>
                    <div>
                        <span class="comment-author">${res.comment.user_name}</span>
                        <span class="comment-time">${res.comment.created_at}</span>
                    </div>
                    <div class="comment-body">${res.comment.comment}</div>
                </div>
            `;
            list.prepend(item);
        } else {
            alert(res.message || 'Gagal mengirim komentar.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        alert('Gagal mengirim komentar: ' + (err.message || 'Terjadi kesalahan sistem.'));
    });
}

// Order Modal
function openOrderModal() {
    <?php if (!$userId): ?>
        if (confirm('Anda harus login terlebih dahulu untuk mengajukan minat. Masuk sekarang?')) {
            window.location.href = '/login';
        }
        return;
    <?php endif; ?>
    document.getElementById('orderModal').style.display = 'flex';
}
function closeOrderModal() {
    document.getElementById('orderModal').style.display = 'none';
}

function sendOrder(listingId) {
    const notes = document.getElementById('orderNotes').value.trim();
    const btn = document.getElementById('btnSendOrder');
    btn.disabled = true;
    btn.innerText = 'Mengirim...';

    const fd = new URLSearchParams();
    fd.append('notes', notes);
    if (window.DUITKU && window.DUITKU.csrfName && window.DUITKU.csrfToken) {
        fd.append(window.DUITKU.csrfName, window.DUITKU.csrfToken);
    }

    const headers = { 
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
    };
    if (window.DUITKU && window.DUITKU.csrfToken) {
        headers['X-CSRF-TOKEN'] = window.DUITKU.csrfToken;
    }

    fetch('/marketplace/order/' + listingId, {
        method: 'POST',
        headers: headers,
        body: fd.toString()
    })
    .then(async r => {
        const text = await r.text();
        try { return JSON.parse(text); } catch(e) { throw new Error(text.replace(/<[^>]*>?/gm, '').substring(0, 100)); }
    })
    .then(res => {
        btn.disabled = false;
        btn.innerText = 'Kirim Pengajuan';
        closeOrderModal();
        if (res.success) {
            alert(res.message);
        } else {
            alert(res.message || 'Gagal mengirim pengajuan.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerText = 'Kirim Pengajuan';
        alert('Gagal mengirim pengajuan: ' + (err.message || 'Terjadi kesalahan sistem.'));
    });
}

// SMART APK RECOMMENDATION POPUP
function closeApkModal() {
    document.getElementById('apkRecommendModal').classList.remove('show');
    sessionStorage.setItem('duitku_apk_modal_dismissed', '1');
}

// Auto show APK install recommendation popup if in browser and not dismissed in current session
document.addEventListener('DOMContentLoaded', function() {
    const dismissed = sessionStorage.getItem('duitku_apk_modal_dismissed');
    // Show after slight delay for smooth impression
    if (!dismissed) {
        setTimeout(function() {
            document.getElementById('apkRecommendModal').classList.add('show');
        }, 1200);
    }
});

// Owner Actions: Status Change & Delete
function changeListingStatus(id, newStatus) {
    if (!confirm('Ubah status iklan ini?')) return;
    const fd = new URLSearchParams();
    fd.append('status', newStatus);
    if (window.DUITKU && window.DUITKU.csrfName && window.DUITKU.csrfToken) {
        fd.append(window.DUITKU.csrfName, window.DUITKU.csrfToken);
    }
    fetch('/marketplace/status/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': (window.DUITKU && window.DUITKU.csrfToken) ? window.DUITKU.csrfToken : ''
        },
        body: fd.toString()
    })
    .then(async r => {
        const text = await r.text();
        return JSON.parse(text);
    })
    .then(res => {
        if (res.success) {
            alert('Status iklan berhasil diperbarui!');
            location.reload();
        } else {
            alert(res.message || 'Gagal mengubah status.');
        }
    })
    .catch(err => alert('Kesalahan: ' + (err.message || 'Gagal menghubungi server.')));
}

function deleteListingFromDetail(id) {
    if (!confirm('Yakin ingin menghapus iklan ini secara permanen? Tindakan ini tidak dapat dibatalkan.')) return;
    const fd = new URLSearchParams();
    if (window.DUITKU && window.DUITKU.csrfName && window.DUITKU.csrfToken) {
        fd.append(window.DUITKU.csrfName, window.DUITKU.csrfToken);
    }
    fetch('/marketplace/delete/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': (window.DUITKU && window.DUITKU.csrfToken) ? window.DUITKU.csrfToken : ''
        },
        body: fd.toString()
    })
    .then(async r => {
        const text = await r.text();
        return JSON.parse(text);
    })
    .then(res => {
        if (res.success) {
            alert('Iklan berhasil dihapus.');
            window.location.href = '/marketplace?tab=my_listings';
        } else {
            alert(res.message || 'Gagal menghapus iklan.');
        }
    })
    .catch(err => alert('Kesalahan: ' + (err.message || 'Gagal menghubungi server.')));
}
</script>
<?= $this->endSection() ?>
