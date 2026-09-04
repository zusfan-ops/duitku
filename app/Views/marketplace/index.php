<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.marketplace-page {
    padding: 14px 16px 110px;
    max-width: 900px;
    margin: 0 auto;
}

/* Hero Section */
.market-hero {
    background: linear-gradient(135deg, #1E1B4B 0%, #312E81 50%, #4338CA 100%);
    border-radius: 22px;
    padding: 22px 20px;
    color: #fff;
    margin-bottom: 16px;
    box-shadow: 0 10px 28px rgba(67, 56, 202, 0.28);
    position: relative;
    overflow: hidden;
}
.market-hero::before {
    content: '';
    position: absolute;
    top: -30px;
    right: -30px;
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
}
.market-hero-title {
    font-size: 20px;
    font-weight: 800;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
    letter-spacing: -0.3px;
}
.market-hero-sub {
    font-size: 13px;
    opacity: 0.9;
    line-height: 1.45;
    max-width: 520px;
    margin-bottom: 16px;
}
.market-hero-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.btn-post-ad {
    background: #10B981;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    padding: 10px 18px;
    border-radius: 12px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    transition: transform 0.15s ease, background 0.15s ease;
}
.btn-post-ad:hover {
    background: #059669;
    transform: translateY(-1px);
    color: #fff;
}
.btn-my-store {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(8px);
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    padding: 10px 16px;
    border-radius: 12px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: background 0.15s ease;
}
.btn-my-store:hover {
    background: rgba(255, 255, 255, 0.25);
    color: #fff;
}

/* Safe Trading Alert */
.safety-banner {
    background: #FFFBEB;
    border: 1px solid #FDE68A;
    border-radius: 16px;
    padding: 12px 14px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 18px;
}
[data-theme="dark"] .safety-banner {
    background: rgba(245, 158, 11, 0.1);
    border-color: rgba(245, 158, 11, 0.3);
}
.safety-icon {
    font-size: 22px;
    flex-shrink: 0;
    line-height: 1;
}
.safety-text h4 {
    font-size: 12.5px;
    font-weight: 800;
    color: #B45309;
    margin: 0 0 3px;
}
[data-theme="dark"] .safety-text h4 {
    color: #FBBF24;
}
.safety-text p {
    font-size: 11.5px;
    color: var(--text-muted);
    margin: 0;
    line-height: 1.4;
}

/* Segmented Tabs */
.market-nav-tabs {
    display: flex;
    gap: 6px;
    background: var(--card);
    padding: 5px;
    border-radius: 14px;
    border: 1px solid var(--border);
    margin-bottom: 16px;
}
.market-nav-tab {
    flex: 1;
    text-align: center;
    padding: 9px 12px;
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--text-muted);
    text-decoration: none;
    transition: all 0.15s ease;
}
.market-nav-tab.active {
    background: var(--primary);
    color: #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}

/* Search & Filter Toolbar */
.filter-box {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 14px;
    margin-bottom: 18px;
}
.search-input-wrapper {
    position: relative;
    margin-bottom: 12px;
}
.search-input-wrapper svg {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    stroke: var(--text-muted);
    width: 17px;
    height: 17px;
}
.search-input {
    width: 100%;
    padding: 11px 14px 11px 40px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    font-size: 13.5px;
    color: var(--text);
    outline: none;
    box-sizing: border-box;
}
.search-input:focus {
    border-color: var(--primary);
}
.filter-row {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}
.type-pills {
    display: flex;
    gap: 6px;
    background: var(--bg);
    padding: 4px;
    border-radius: 10px;
    border: 1px solid var(--border);
}
.type-pill {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    color: var(--text-muted);
    transition: all 0.15s;
}
.type-pill.active {
    background: #4338CA;
    color: #fff;
}
.sort-select {
    padding: 7px 12px;
    border-radius: 10px;
    background: var(--bg);
    border: 1px solid var(--border);
    font-size: 12px;
    font-weight: 600;
    color: var(--text);
    outline: none;
    margin-left: auto;
}

/* Category Horizontal Chips */
.cat-scroll {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding: 4px 0 14px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.cat-scroll::-webkit-scrollbar {
    display: none;
}
.cat-chip {
    flex-shrink: 0;
    padding: 7px 14px;
    border-radius: 999px;
    background: var(--card);
    border: 1px solid var(--border);
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    text-decoration: none;
    transition: all 0.15s;
}
.cat-chip:hover {
    border-color: #6366F1;
    color: var(--text);
}
.cat-chip.active {
    background: #4338CA;
    border-color: #4338CA;
    color: #fff;
    box-shadow: 0 2px 8px rgba(67, 56, 202, 0.25);
}

/* Listings Grid */
.listings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
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
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    position: relative;
}
.listing-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.09);
    border-color: #818CF8;
}
.listing-thumb-wrap {
    width: 100%;
    height: 165px;
    background: #1F2937;
    position: relative;
    overflow: hidden;
}
.listing-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.25s ease;
}
.listing-card:hover .listing-thumb {
    transform: scale(1.04);
}
.listing-thumb-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 38px;
    background: linear-gradient(135deg, #374151 0%, #1F2937 100%);
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
    letter-spacing: 0.4px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.25);
}
.badge-type.sale {
    background: #059669;
    color: #fff;
}
.badge-type.rent {
    background: #6D28D9;
    color: #fff;
}
.badge-type.service {
    background: #2563EB;
    color: #fff;
}

.badge-img-count {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(4px);
    color: #fff;
    font-size: 10.5px;
    font-weight: 700;
    padding: 3px 7px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.listing-body {
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.listing-cat-cond {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    margin-bottom: 5px;
}
.listing-title {
    font-size: 14px;
    font-weight: 800;
    line-height: 1.35;
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
[data-theme="dark"] .listing-price {
    color: #34D399;
}
.listing-price-period {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
}
.listing-meta {
    margin-top: auto;
    padding-top: 8px;
    border-top: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11px;
    color: var(--text-muted);
}
.listing-location {
    display: flex;
    align-items: center;
    gap: 3px;
    max-width: 140px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.listing-seller {
    display: flex;
    align-items: center;
    gap: 4px;
    font-weight: 700;
    color: var(--text);
}

/* Empty State */
.market-empty {
    text-align: center;
    padding: 50px 20px;
    background: var(--card);
    border: 1px dashed var(--border);
    border-radius: 20px;
}
.market-empty-icon {
    font-size: 48px;
    margin-bottom: 12px;
}
.market-empty-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 6px;
}
.market-empty-sub {
    font-size: 13px;
    color: var(--text-muted);
    max-width: 380px;
    margin: 0 auto 16px;
}

/* My Listing Item */
.my-listing-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 12px 14px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 14px;
    justify-content: space-between;
    flex-wrap: wrap;
}
.my-listing-info {
    display: flex;
    align-items: center;
    gap: 12px;
}
.my-listing-img {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    object-fit: cover;
    background: #374151;
}
.my-listing-title {
    font-size: 14px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 2px;
}
.my-listing-sub {
    font-size: 12px;
    color: var(--text-muted);
}
.my-listing-actions {
    display: flex;
    gap: 6px;
}
.btn-sm-action {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11.5px;
    font-weight: 700;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text);
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.btn-sm-action:hover {
    background: var(--card);
    border-color: var(--primary);
}
.btn-sm-action.danger:hover {
    border-color: #EF4444;
    color: #EF4444;
}

@media (max-width: 600px) {
    .listings-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    .listing-thumb-wrap {
        height: 130px;
    }
    .listing-title {
        font-size: 12.5px;
    }
    .listing-price {
        font-size: 14px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="marketplace-page">

    <!-- HERO HEADER -->
    <div class="market-hero">
        <div class="market-hero-title">
            <span>🛍️</span> Jual Beli & Sewa
        </div>
        <div class="market-hero-sub">
            Pasar komunitas DuitKu. Jual atau sewakan motor, mobil, rumah, gadget, atau temukan barang berkualitas dari pengguna lain.
        </div>
        <div class="market-hero-actions">
            <a href="/marketplace/create" class="btn-post-ad">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Pasang Iklan Sekarang
            </a>
            <?php if ($userId && !empty($myUsername)): ?>
                <a href="/u/<?= esc($myUsername) ?>" class="btn-my-store" title="Buka etalase toko profil saya">
                    <span>🏪 Toko Saya</span>
                    <span style="font-size:11px;opacity:0.8;">(<?= esc($myUsername) ?>)</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- SAFETY WARNING BANNER (ANTI-SCAM ADVISORY) -->
    <div class="safety-banner">
        <div class="safety-icon">🛡️</div>
        <div class="safety-text">
            <h4>Hati-Hati Penipuan! Tips Transaksi Aman</h4>
            <p>
                <strong>DILARANG KERAS mentransfer uang muka (DP)</strong> sebelum melihat fisik barang. Dianjurkan selalu <strong>COD (Ketemu Langsung)</strong> di tempat umum atau gunakan link transaksi aman via <strong>Shopee / Tokopedia</strong>.
            </p>
        </div>
    </div>

    <!-- SEGMENTED TABS -->
    <div class="market-nav-tabs">
        <a href="/marketplace?tab=browse" class="market-nav-tab <?= $activeTab === 'browse' ? 'active' : '' ?>">
            Semua Produk
        </a>
        <a href="/marketplace?tab=my_listings" class="market-nav-tab <?= $activeTab === 'my_listings' ? 'active' : '' ?>">
            Iklan Saya (<?= count($myListings) ?>)
        </a>
        <a href="/marketplace?tab=orders" class="market-nav-tab <?= $activeTab === 'orders' ? 'active' : '' ?>">
            Minat & Pesanan (<?= count($ordersReceived) ?>)
        </a>
    </div>

    <?php if ($activeTab === 'browse'): ?>

        <!-- FILTER & SEARCH TOOLBAR -->
        <div class="filter-box">
            <form action="/marketplace" method="get" id="filterForm">
                <input type="hidden" name="tab" value="browse">
                <input type="hidden" name="category" id="catInput" value="<?= esc($selectedCategory) ?>">
                <input type="hidden" name="type" id="typeInput" value="<?= esc($selectedType) ?>">

                <div class="search-input-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="search" class="search-input" placeholder="Cari motor, mobil, rumah, iphone..." value="<?= esc($searchQuery) ?>">
                </div>

                <div class="filter-row">
                    <!-- Type Pills: Semua / Jual / Sewa / Jasa -->
                    <div class="type-pills">
                        <a href="javascript:void(0)" onclick="setTypeFilter('')" class="type-pill <?= empty($selectedType) ? 'active' : '' ?>">Semua</a>
                        <a href="javascript:void(0)" onclick="setTypeFilter('sale')" class="type-pill <?= $selectedType === 'sale' ? 'active' : '' ?>">🏷️ Jual</a>
                        <a href="javascript:void(0)" onclick="setTypeFilter('rent')" class="type-pill <?= $selectedType === 'rent' ? 'active' : '' ?>">🔑 Sewa</a>
                        <a href="javascript:void(0)" onclick="setTypeFilter('service')" class="type-pill <?= $selectedType === 'service' ? 'active' : '' ?>">🛠️ Layanan Jasa</a>
                    </div>

                    <!-- Sort -->
                    <select name="sort" class="sort-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="latest" <?= $currentSort === 'latest' ? 'selected' : '' ?>>Terbaru</option>
                        <option value="popular" <?= $currentSort === 'popular' ? 'selected' : '' ?>>Paling Dilihat</option>
                        <option value="price_low" <?= $currentSort === 'price_low' ? 'selected' : '' ?>>Harga Terendah</option>
                        <option value="price_high" <?= $currentSort === 'price_high' ? 'selected' : '' ?>>Harga Tertinggi</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- CATEGORIES HORIZONTAL SCROLL CHIPS -->
        <div class="cat-scroll">
            <a href="javascript:void(0)" onclick="setCategoryFilter('Semua')" class="cat-chip <?= $selectedCategory === 'Semua' ? 'active' : '' ?>">
                Semua Kategori
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="javascript:void(0)" onclick="setCategoryFilter('<?= esc($cat) ?>')" class="cat-chip <?= $selectedCategory === $cat ? 'active' : '' ?>">
                    <?= esc($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- LISTINGS GRID -->
        <?php if (empty($listings)): ?>
            <div class="market-empty">
                <div class="market-empty-icon">📦</div>
                <div class="market-empty-title">Belum ada listing di kategori ini</div>
                <div class="market-empty-sub">Jadilah yang pertama memasang iklan barang bekas, properti, atau penawaran jasa!</div>
                <a href="/marketplace/create" class="btn-post-ad">Pasang Iklan Sekarang</a>
            </div>
        <?php else: ?>
            <div class="listings-grid">
                <?php foreach ($listings as $item): ?>
                    <a href="/marketplace/item/<?= esc($item['id']) ?>" class="listing-card">
                        <div class="listing-thumb-wrap">
                            <?php if (!empty($item['primary_image'])): ?>
                                <img src="<?= esc($item['primary_image']) ?>" alt="<?= esc($item['title']) ?>" class="listing-thumb" loading="lazy">
                            <?php else: ?>
                                <div class="listing-thumb-placeholder"><?= $item['type'] === 'service' ? '🛠️' : '🏷️' ?></div>
                            <?php endif; ?>

                            <div class="badge-type <?= esc($item['type']) ?>">
                                <?= $item['type'] === 'service' ? '🛠️ JASA' : ($item['type'] === 'rent' ? 'SEWA' : 'JUAL') ?>
                            </div>

                            <?php if (!empty($item['image_count']) && $item['image_count'] > 1): ?>
                                <div class="badge-img-count">
                                    📷 <?= (int)$item['image_count'] ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="listing-body">
                            <div class="listing-cat-cond">
                                <span><?= esc($item['category']) ?></span>
                                <span>
                                    <?php
                                        if ($item['type'] === 'service') {
                                            $stMap = [
                                                'panggilan' => '🛵 Panggilan',
                                                'di_tempat' => '🏢 Di Tempat',
                                                'keduanya'  => '🔄 Fleksibel',
                                                'online'    => '💻 Online',
                                            ];
                                            echo $stMap[$item['service_type'] ?? ''] ?? '🛠️ Jasa';
                                        } else {
                                            $condMap = ['new' => 'Baru', 'like_new' => 'Seperti Baru', 'used_good' => 'Bekas Baik', 'used_fair' => 'Bekas Layak'];
                                            echo $condMap[$item['condition']] ?? 'Bekas';
                                        }
                                    ?>
                                </span>
                            </div>

                            <div class="listing-title"><?= esc($item['title']) ?></div>

                            <div class="listing-price">
                                <?= $symbol ?> <?= number_format($item['price'], 0, ',', '.') ?>
                                <?php if ($item['type'] === 'rent' && !empty($item['rent_period'])): ?>
                                    <span class="listing-price-period">/<?= esc($item['rent_period']) ?></span>
                                <?php elseif ($item['type'] === 'service' && !empty($item['rate_unit'])): ?>
                                    <?php
                                        $rateUnitsMap = [
                                            'per_sesi'       => '/sesi',
                                            'per_panggilan'  => '/panggilan',
                                            'per_jam'        => '/jam',
                                            'per_unit'       => '/unit',
                                            'per_pekerjaan'  => '/borong',
                                            'mulai_dari'     => '(mulai dari)',
                                        ];
                                        echo '<span class="listing-price-period">' . esc($rateUnitsMap[$item['rate_unit']] ?? ('/' . $item['rate_unit'])) . '</span>';
                                    ?>
                                <?php endif; ?>
                            </div>

                            <div class="listing-meta">
                                <div class="listing-location" title="<?= esc($item['location']) ?>">
                                    📍 <?= esc($item['location']) ?>
                                </div>
                                <div class="listing-seller">
                                    <span><?= esc($item['seller_name']) ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php elseif ($activeTab === 'my_listings'): ?>

        <!-- MY LISTINGS TAB -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <h3 style="margin:0;font-size:16px;font-weight:800;">Kelola Iklan Saya</h3>
            <a href="/marketplace/create" class="btn-post-ad" style="padding:7px 14px;font-size:12px;">+ Iklan Baru</a>
        </div>

        <?php if (empty($myListings)): ?>
            <div class="market-empty">
                <div class="market-empty-icon">🏷️</div>
                <div class="market-empty-title">Anda belum memasang iklan</div>
                <div class="market-empty-sub">Mulai tawarkan barang bekas, kendaraan, atau properti Anda ke ribuan pengguna DuitKu.</div>
                <a href="/marketplace/create" class="btn-post-ad">Pasang Iklan Sekarang</a>
            </div>
        <?php else: ?>
            <div>
                <?php foreach ($myListings as $my): ?>
                    <div class="my-listing-card" id="card-<?= esc($my['id']) ?>">
                        <div class="my-listing-info">
                            <?php if (!empty($my['primary_image'])): ?>
                                <img src="<?= esc($my['primary_image']) ?>" class="my-listing-img" alt="Foto">
                            <?php else: ?>
                                <div class="my-listing-img" style="display:flex;align-items:center;justify-content:center;font-size:22px;">🏷️</div>
                            <?php endif; ?>
                            <div>
                                <div class="my-listing-title"><?= esc($my['title']) ?></div>
                                <div class="my-listing-sub">
                                    <span style="font-weight:700;color:#059669;"><?= $symbol ?> <?= number_format($my['price'], 0, ',', '.') ?></span>
                                    • <?= $my['type'] === 'rent' ? 'Sewa' : 'Jual' ?>
                                    • Status: <strong id="status-text-<?= esc($my['id']) ?>"><?= ucfirst(esc($my['status'])) ?></strong>
                                    • 👁️ <?= (int)$my['views_count'] ?> tayangan
                                </div>
                            </div>
                        </div>
                        <div class="my-listing-actions">
                            <a href="/marketplace/item/<?= esc($my['id']) ?>" class="btn-sm-action">Buka</a>
                            <a href="/marketplace/edit/<?= esc($my['id']) ?>" class="btn-sm-action" style="color:#4338CA;border-color:rgba(67,56,202,0.35);">
                                ✏️ Edit
                            </a>
                            <?php if ($my['status'] === 'active'): ?>
                                <button onclick="changeStatus(<?= esc($my['id']) ?>, '<?= $my['type'] === 'rent' ? 'rented' : 'sold' ?>')" class="btn-sm-action">
                                    Tandai <?= $my['type'] === 'rent' ? 'Disewa' : 'Terjual' ?>
                                </button>
                            <?php else: ?>
                                <button onclick="changeStatus(<?= esc($my['id']) ?>, 'active')" class="btn-sm-action">Aktifkan Lagi</button>
                            <?php endif; ?>
                            <button onclick="deleteListing(<?= esc($my['id']) ?>)" class="btn-sm-action danger">Hapus</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php elseif ($activeTab === 'orders'): ?>

        <!-- ORDERS / INQUIRIES TAB -->
        <h3 style="margin:0 0 14px;font-size:16px;font-weight:800;">Minat & Pesanan Masuk dari Calon Pembeli</h3>
        <?php if (empty($ordersReceived)): ?>
            <div class="market-empty" id="ordersEmptyState">
                <div class="market-empty-icon">💬</div>
                <div class="market-empty-title">Belum ada minat atau pesanan masuk</div>
                <div class="market-empty-sub">Ketika ada pengguna yang mengajukan minat beli atau sewa, data kontaknya akan muncul di sini.</div>
            </div>
        <?php else: ?>
            <div id="ordersContainer">
                <?php foreach ($ordersReceived as $ord): ?>
                    <div class="my-listing-card" id="orderCard_<?= (int)$ord['id'] ?>">
                        <div class="my-listing-info" style="cursor:pointer;flex:1;" onclick="openMarketChat(<?= (int)$ord['listing_id'] ?>, <?= (int)$ord['buyer_id'] ?>, '<?= esc(addslashes($ord['buyer_name'])) ?>', '<?= esc(addslashes($ord['listing_title'])) ?>')">
                            <?php if (!empty($ord['listing_image'])): ?>
                                <img src="<?= esc($ord['listing_image']) ?>" class="my-listing-img" alt="Barang">
                            <?php else: ?>
                                <div class="my-listing-img" style="display:flex;align-items:center;justify-content:center;font-size:22px;">📦</div>
                            <?php endif; ?>
                            <div>
                                <div class="my-listing-title"><?= esc($ord['listing_title']) ?></div>
                                <div class="my-listing-sub">
                                    Calon Pembeli: <strong><?= esc($ord['buyer_name']) ?></strong> • 
                                    <span><?= date('d M Y H:i', strtotime($ord['created_at'])) ?></span>
                                </div>
                                <div style="font-size:12px;color:var(--text);margin-top:4px;font-style:italic;">
                                    "<?= esc($ord['notes']) ?>"
                                </div>
                            </div>
                        </div>
                        <div class="my-listing-actions">
                            <!-- CHAT APLIKASI (SELALU AKTIF) -->
                            <button type="button" onclick="openMarketChat(<?= (int)$ord['listing_id'] ?>, <?= (int)$ord['buyer_id'] ?>, '<?= esc(addslashes($ord['buyer_name'])) ?>', '<?= esc(addslashes($ord['listing_title'])) ?>')" class="btn-sm-action" style="background:#4338CA;color:#fff;border-color:#4338CA;" title="Buka obrolan langsung dengan calon pembeli">
                                <span>💬 Chat Pembeli</span>
                            </button>

                            <!-- CHAT WHATSAPP (JIKA NOMOR TERSEDIA) -->
                            <?php if (!empty($ord['buyer_phone'])): ?>
                                <?php
                                    $phoneClean = preg_replace('/[^0-9]/', '', $ord['buyer_phone']);
                                    if (str_starts_with($phoneClean, '0')) $phoneClean = '62' . substr($phoneClean, 1);
                                ?>
                                <a href="https://wa.me/<?= $phoneClean ?>?text=Halo%20<?= urlencode($ord['buyer_name']) ?>,%20mengenai%20minat%20Anda%20pada%20produk%20<?= urlencode($ord['listing_title']) ?>" target="_blank" class="btn-sm-action" style="background:#22C55E;color:#fff;border-color:#22C55E;" title="Hubungi calon pembeli via WhatsApp">
                                    <span>🟢 WA</span>
                                </a>
                            <?php endif; ?>

                            <!-- HAPUS MINAT / PESANAN -->
                            <button type="button" onclick="deleteMarketOrder(<?= (int)$ord['id'] ?>)" class="btn-sm-action danger" title="Hapus minat masuk ini">
                                <span>🗑️ Hapus</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <!-- REAL-TIME IN-APP MARKETPLACE CHAT MODAL -->
    <div class="market-chat-modal-overlay" id="marketChatModal" style="display:none;" onclick="if(event.target===this)closeMarketChat()">
        <div class="market-chat-modal-box">
            <!-- Header -->
            <div class="market-chat-header">
                <div class="market-chat-header-info">
                    <div class="market-chat-header-title" id="chatModalListingTitle">Obrolan Marketplace</div>
                    <div class="market-chat-header-sub">
                        <span class="market-chat-status-dot"></span>
                        <span id="chatModalPartnerName">Calon Pembeli</span>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <a href="#" id="chatModalWaBtn" target="_blank" class="chat-wa-header-btn" style="display:none;" title="Lanjutkan di WhatsApp">
                        🟢 WA
                    </a>
                    <button type="button" class="market-chat-close-btn" onclick="closeMarketChat()" title="Tutup Chat">✕</button>
                </div>
            </div>

            <!-- Messages List -->
            <div class="market-chat-body" id="chatModalMessages">
                <div style="text-align:center;padding:30px;color:var(--text-muted);font-size:12px;">
                    Memuat riwayat percakapan...
                </div>
            </div>

            <!-- Input Footer -->
            <form class="market-chat-footer" id="marketChatForm" onsubmit="sendChatMessage(event)">
                <input type="text" id="chatInputMessage" class="market-chat-input" placeholder="Tulis balasan pesan..." style="color: #0f172a !important; background-color: #ffffff !important;" autocomplete="off" required>
                <button type="submit" id="btnSendChatMsg" class="market-chat-send-btn" title="Kirim Pesan">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <!-- FLOATING CHAT BUTTON (MARKETPLACE) -->
    <a href="/marketplace?tab=orders" class="market-floating-chat-btn" id="marketFloatingChatBtn" title="Buka Pesan & Minat Masuk">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
        </svg>
        <span>Pesan & Minat</span>
        <?php if (!empty($ordersReceived)): ?>
            <span class="market-floating-chat-badge" id="marketFloatingChatBadge">
                <?= count($ordersReceived) ?>
            </span>
        <?php endif; ?>
    </a>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function setCategoryFilter(cat) {
    document.getElementById('catInput').value = cat;
    document.getElementById('filterForm').submit();
}

function setTypeFilter(type) {
    document.getElementById('typeInput').value = type;
    document.getElementById('filterForm').submit();
}

function changeStatus(id, newStatus) {
    if (!confirm('Ubah status iklan ini?')) return;

    const fd = new URLSearchParams();
    fd.append('status', newStatus);
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

    fetch('/marketplace/status/' + id, {
        method: 'POST',
        headers: headers,
        body: fd.toString()
    })
    .then(async r => {
        const text = await r.text();
        try { return JSON.parse(text); } catch(e) { throw new Error(text.replace(/<[^>]*>?/gm, '').substring(0, 100)); }
    })
    .then(res => {
        if (res.success) {
            alert('Status iklan berhasil diperbarui.');
            location.reload();
        } else {
            alert(res.message || 'Gagal mengubah status.');
        }
    })
    .catch(err => {
        alert('Gagal mengubah status: ' + (err.message || 'Kesalahan server.'));
    });
}

function deleteListing(id) {
    if (!confirm('Yakin ingin menghapus iklan ini? Tindakan ini tidak dapat dibatalkan.')) return;

    const fd = new URLSearchParams();
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

    fetch('/marketplace/delete/' + id, {
        method: 'POST',
        headers: headers,
        body: fd.toString()
    })
    .then(async r => {
        const text = await r.text();
        try { return JSON.parse(text); } catch(e) { throw new Error(text.replace(/<[^>]*>?/gm, '').substring(0, 100)); }
    })
    .then(res => {
        if (res.success) {
            document.getElementById('card-' + id)?.remove();
        } else {
            alert(res.message || 'Gagal menghapus iklan.');
        }
    })
    .catch(err => {
        alert('Gagal menghapus iklan: ' + (err.message || 'Kesalahan server.'));
    });
}

/* ══════════════════════════════════════════════════════════════
   MARKETPLACE INQUIRIES / ORDERS & IN-APP CHAT ACTIONS
   ══════════════════════════════════════════════════════════════ */
let currentChatListingId = 0;
let currentChatBuyerId   = 0;
let currentChatInterval  = null;
let currentChatMyId      = 0;

function deleteMarketOrder(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus minat / pesanan ini dari daftar Anda?')) return;

    const fd = new URLSearchParams();
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

    fetch('/marketplace/order/delete/' + id, {
        method: 'POST',
        headers: headers,
        body: fd.toString()
    })
    .then(async r => {
        const text = await r.text();
        try { return JSON.parse(text); } catch(e) { throw new Error(text.substring(0, 100)); }
    })
    .then(res => {
        if (res.success) {
            const el = document.getElementById('orderCard_' + id);
            if (el) {
                el.style.transition = 'all 0.3s ease';
                el.style.opacity = '0';
                el.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    el.remove();
                    // Update badge
                    const badge = document.getElementById('marketFloatingChatBadge');
                    if (badge) {
                        const count = parseInt(badge.textContent.trim()) - 1;
                        if (count > 0) {
                            badge.textContent = count;
                        } else {
                            badge.remove();
                        }
                    }
                    // Check if container is empty
                    const container = document.getElementById('ordersContainer');
                    if (container && container.querySelectorAll('.my-listing-card').length === 0) {
                        container.innerHTML = `
                            <div class="market-empty" id="ordersEmptyState">
                                <div class="market-empty-icon">💬</div>
                                <div class="market-empty-title">Belum ada minat atau pesanan masuk</div>
                                <div class="market-empty-sub">Ketika ada pengguna yang mengajukan minat beli atau sewa, data kontaknya akan muncul di sini.</div>
                            </div>
                        `;
                    }
                }, 300);
            }
        } else {
            alert(res.message || 'Gagal menghapus pesanan.');
        }
    })
    .catch(err => {
        alert('Gagal menghapus: ' + (err.message || 'Terjadi kesalahan.'));
    });
}

function openMarketChat(listingId, buyerId, partnerName, listingTitle) {
    currentChatListingId = listingId;
    currentChatBuyerId   = buyerId;

    const modal     = document.getElementById('marketChatModal');
    const titleEl   = document.getElementById('chatModalListingTitle');
    const partnerEl = document.getElementById('chatModalPartnerName');
    const msgsEl    = document.getElementById('chatModalMessages');
    const waBtn     = document.getElementById('chatModalWaBtn');

    if (titleEl)   titleEl.textContent = listingTitle || 'Obrolan Marketplace';
    if (partnerEl) partnerEl.textContent = partnerName || 'Calon Pembeli';
    if (msgsEl)    msgsEl.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-muted);font-size:12px;">Memuat obrolan...</div>';
    if (waBtn)     waBtn.style.display = 'none';

    if (modal) {
        modal.style.display = 'flex';
        modal.offsetHeight; // force reflow for smooth fade
        modal.classList.add('show');
    }

    loadChatMessages(true);

    if (currentChatInterval) clearInterval(currentChatInterval);
    currentChatInterval = setInterval(() => {
        loadChatMessages(false);
    }, 3500);

    setTimeout(() => {
        document.getElementById('chatInputMessage')?.focus();
    }, 200);
}

function closeMarketChat() {
    const modal = document.getElementById('marketChatModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 250);
    }
    if (currentChatInterval) {
        clearInterval(currentChatInterval);
        currentChatInterval = null;
    }
}

function loadChatMessages(isInitial = false) {
    if (!currentChatListingId) return;

    fetch('/marketplace/chat/messages?listing_id=' + currentChatListingId + '&buyer_id=' + currentChatBuyerId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(async r => {
        const text = await r.text();
        try { return JSON.parse(text); } catch(e) { throw new Error(text.substring(0, 100)); }
    })
    .then(res => {
        if (!res.success) return;

        currentChatMyId = res.my_id || 0;
        const msgsEl = document.getElementById('chatModalMessages');
        const waBtn  = document.getElementById('chatModalWaBtn');

        // Setup WhatsApp shortcut if available
        if (waBtn) {
            const partnerPhone = (res.my_id === res.seller.id) ? res.buyer.phone : res.seller.phone;
            const partnerName  = (res.my_id === res.seller.id) ? res.buyer.name : res.seller.name;
            if (partnerPhone) {
                waBtn.href = 'https://wa.me/' + partnerPhone + '?text=Halo%20' + encodeURIComponent(partnerName) + ',%20mengenai%20produk%20' + encodeURIComponent(res.listing.title);
                waBtn.style.display = 'inline-flex';
            } else {
                waBtn.style.display = 'none';
            }
        }

        if (!res.messages || res.messages.length === 0) {
            if (isInitial && msgsEl) {
                msgsEl.innerHTML = '<div style="text-align:center;padding:40px 20px;color:var(--text-muted);font-size:12.5px;">Belum ada riwayat pesan.<br>Ketik pesan di bawah untuk memulai obrolan.</div>';
            }
            return;
        }

        let html = '';
        res.messages.forEach(m => {
            const isMe = parseInt(m.sender_id) === currentChatMyId;
            const timeStr = m.created_at ? new Date(m.created_at.replace(/-/g, '/')).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '';
            html += `
                <div class="chat-bubble ${isMe ? 'me' : 'other'}">
                    <div style="font-size:11px;font-weight:700;opacity:0.85;margin-bottom:2px;">${escapeHtml(m.sender_name || 'Pengguna')}</div>
                    <div>${escapeHtml(m.message)}</div>
                    <div class="chat-bubble-time">${timeStr}</div>
                </div>
            `;
        });

        if (msgsEl) {
            const isScrolledToBottom = msgsEl.scrollHeight - msgsEl.clientHeight <= msgsEl.scrollTop + 60;
            msgsEl.innerHTML = html;
            if (isInitial || isScrolledToBottom) {
                msgsEl.scrollTop = msgsEl.scrollHeight;
            }
        }
    })
    .catch(err => {
        console.error('Chat load error:', err);
    });
}

function sendChatMessage(e) {
    if (e) e.preventDefault();

    const input = document.getElementById('chatInputMessage');
    const msg = (input ? input.value : '').trim();
    if (!msg || !currentChatListingId) return;

    const fd = new URLSearchParams();
    fd.append('listing_id', currentChatListingId);
    fd.append('buyer_id', currentChatBuyerId);
    fd.append('message', msg);
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

    if (input) input.value = '';

    // Optimistic message append
    const msgsEl = document.getElementById('chatModalMessages');
    if (msgsEl) {
        const timeNow = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        msgsEl.insertAdjacentHTML('beforeend', `
            <div class="chat-bubble me" style="opacity:0.85">
                <div>${escapeHtml(msg)}</div>
                <div class="chat-bubble-time">${timeNow} • Mengirim...</div>
            </div>
        `);
        msgsEl.scrollTop = msgsEl.scrollHeight;
    }

    fetch('/marketplace/chat/send', {
        method: 'POST',
        headers: headers,
        body: fd.toString()
    })
    .then(async r => {
        const text = await r.text();
        try { return JSON.parse(text); } catch(e) { throw new Error(text.substring(0, 100)); }
    })
    .then(res => {
        if (res.success) {
            loadChatMessages(true);
        } else {
            alert(res.message || 'Gagal mengirim pesan.');
        }
    })
    .catch(err => {
        alert('Gagal mengirim pesan: ' + (err.message || 'Kesalahan jaringan.'));
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>
<?= $this->endSection() ?>
