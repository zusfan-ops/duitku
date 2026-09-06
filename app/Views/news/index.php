<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.news-page {
    padding: 8px 16px 100px;
    max-width: 900px;
    margin: 0 auto;
}

/* ── Top Header Bar ────────────────────────────────────────── */
.news-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    padding-top: 4px;
}
.news-header-left {
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.news-header-title {
    font-size: 20px;
    font-weight: 800;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
    letter-spacing: -0.4px;
}
.news-live-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #EF4444;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 6px;
    letter-spacing: 0.5px;
    animation: newsPulse 1.8s infinite;
}
.news-live-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #fff;
}
@keyframes newsPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.82; transform: scale(0.97); }
}
.news-header-sub {
    font-size: 12px;
    color: var(--text-muted);
}
.news-refresh-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    padding: 7px 12px;
    border-radius: 12px;
    font-size: 11.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
    text-decoration: none;
}
.news-refresh-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
}
.news-refresh-btn:active {
    transform: scale(0.96);
}

/* ── Search Bar ────────────────────────────────────────────── */
.news-search-box {
    position: relative;
    margin-bottom: 14px;
}
.news-search-input {
    width: 100%;
    box-sizing: border-box;
    padding: 11px 16px 11px 40px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    font-size: 13px;
    color: var(--text-primary);
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.news-search-input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-dim);
}
.news-search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    pointer-events: none;
}

/* ── Filter Categories Tabs ────────────────────────────────── */
.news-cat-scroll {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 6px;
    margin-bottom: 10px;
    scrollbar-width: none;
}
.news-cat-scroll::-webkit-scrollbar { display: none; }
.news-cat-tab {
    padding: 7px 14px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--bg-card);
    color: var(--text-secondary);
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.news-cat-tab:hover {
    color: var(--text-primary);
    border-color: var(--text-muted);
}
.news-cat-tab.active {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(10, 169, 86, 0.28);
}
.news-cat-tab-badge {
    font-size: 10px;
    font-weight: 800;
    opacity: 0.85;
    padding: 1px 5px;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.12);
}
.news-cat-tab.active .news-cat-tab-badge {
    background: rgba(255, 255, 255, 0.25);
    color: #fff;
}

/* ── Source Media Pills ────────────────────────────────────── */
.news-source-scroll {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    padding-bottom: 6px;
    margin-bottom: 18px;
    scrollbar-width: none;
}
.news-source-scroll::-webkit-scrollbar { display: none; }
.news-source-pill {
    padding: 5px 11px;
    border-radius: 20px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text-secondary);
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.news-source-pill:hover {
    color: var(--text-primary);
    border-color: var(--text-muted);
}
.news-source-pill.active {
    background: var(--text-primary);
    color: var(--bg);
    border-color: var(--text-primary);
}

/* ── Hero Breaking News Card ───────────────────────────────── */
<?php if (!empty($headlines)): 
    $hero = $headlines[0]; 
?>
.news-hero-card {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 22px;
    border: 1px solid var(--border);
    background: #0F172A;
    color: #fff;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.16);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.news-hero-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.24);
}
.news-hero-bg {
    width: 100%;
    height: 220px;
    background-size: cover;
    background-position: center;
    position: relative;
}
.news-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.2) 0%, rgba(15, 23, 42, 0.85) 60%, #0F172A 100%);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 16px 18px;
}
.news-hero-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}
.news-hero-badge {
    background: <?= esc($hero['color'] ?? '#EF4444') ?>;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 6px;
    letter-spacing: 0.4px;
    text-transform: uppercase;
}
.news-hero-time {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.75);
    font-weight: 600;
}
.news-hero-title {
    font-size: 16px;
    font-weight: 800;
    line-height: 1.35;
    color: #ffffff;
    margin-bottom: 6px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.news-hero-desc {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.85);
    line-height: 1.45;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
<?php endif; ?>

/* ── News List Grid ────────────────────────────────────────── */
.news-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}
@media (min-width: 640px) {
    .news-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }
}

.news-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
}
.news-card:hover {
    transform: translateY(-2px);
    border-color: var(--primary);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.07);
}
.news-card:active {
    transform: scale(0.985);
}

.news-thumb-wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    background: #1E293B;
    overflow: hidden;
}
.news-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.3s ease;
}
.news-card:hover .news-thumb {
    transform: scale(1.04);
}
.news-thumb-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #1E293B, #334155);
    color: rgba(255, 255, 255, 0.7);
    font-size: 28px;
    gap: 6px;
}
.news-thumb-placeholder span {
    font-size: 11px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.85);
    text-transform: uppercase;
    letter-spacing: 0.6px;
}

.news-source-tag {
    position: absolute;
    top: 10px;
    left: 10px;
    font-size: 10px;
    font-weight: 800;
    color: #fff;
    padding: 3px 8px;
    border-radius: 6px;
    letter-spacing: 0.4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.news-card-body {
    padding: 14px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.news-card-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
    font-size: 11px;
    color: var(--text-muted);
}
.news-time-badge {
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.news-card-title {
    font-size: 14px;
    font-weight: 800;
    line-height: 1.35;
    color: var(--text-primary);
    margin-bottom: 6px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.news-card-desc {
    font-size: 12px;
    color: var(--text-secondary);
    line-height: 1.45;
    margin-bottom: 12px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}
.news-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 8px;
    border-top: 1px solid var(--border);
    margin-top: auto;
}
.news-read-btn {
    font-size: 11.5px;
    font-weight: 800;
    color: var(--primary);
    display: inline-flex;
    align-items: center;
    gap: 4px;
    text-decoration: none;
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
}
.news-share-icon-btn {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.15s ease;
}
.news-share-icon-btn:hover {
    color: var(--primary);
    border-color: var(--primary);
}

/* ── Empty State ───────────────────────────────────────────── */
.news-empty-state {
    text-align: center;
    padding: 48px 16px;
    background: var(--bg-card);
    border: 1px dashed var(--border);
    border-radius: 20px;
    margin-top: 10px;
}
.news-empty-icon {
    font-size: 40px;
    margin-bottom: 12px;
}
.news-empty-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 6px;
}
.news-empty-sub {
    font-size: 13px;
    color: var(--text-muted);
    max-width: 360px;
    margin: 0 auto 16px;
}

/* ── Modal In-App Reader ───────────────────────────────────── */
.news-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s ease;
}
.news-modal-overlay.active {
    opacity: 1;
    pointer-events: auto;
}
.news-modal-sheet {
    width: 100%;
    max-width: 680px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 24px 24px 0 0;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    transform: translateY(100%);
    transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
    overflow: hidden;
    box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.3);
}
@media (min-width: 640px) {
    .news-modal-overlay {
        align-items: center;
        padding: 20px;
    }
    .news-modal-sheet {
        border-radius: 24px;
        max-height: 85vh;
        transform: scale(0.94);
    }
    .news-modal-overlay.active .news-modal-sheet {
        transform: scale(1);
    }
}
.news-modal-overlay.active .news-modal-sheet {
    transform: translateY(0);
}

.news-modal-handle {
    width: 40px;
    height: 4px;
    background: var(--border);
    border-radius: 2px;
    margin: 10px auto 4px;
}
.news-modal-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 18px;
    border-bottom: 1px solid var(--border);
}
.news-modal-src-badge {
    font-size: 11px;
    font-weight: 800;
    color: #fff;
    padding: 3px 8px;
    border-radius: 6px;
    letter-spacing: 0.4px;
}
.news-modal-close {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 16px;
}

.news-modal-body {
    padding: 18px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.news-modal-img {
    width: 100%;
    max-height: 280px;
    object-fit: cover;
    border-radius: 14px;
    background: #1E293B;
}
.news-modal-title {
    font-size: 18px;
    font-weight: 800;
    line-height: 1.35;
    color: var(--text-primary);
}
.news-modal-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
    color: var(--text-muted);
}
.news-modal-text {
    font-size: 14px;
    line-height: 1.65;
    color: var(--text-secondary);
}

.news-modal-ftr {
    padding: 14px 18px;
    border-top: 1px solid var(--border);
    background: var(--bg);
    display: flex;
    align-items: center;
    gap: 10px;
}
.news-btn-source {
    flex: 1;
    background: var(--primary);
    color: #fff;
    text-decoration: none;
    font-size: 13px;
    font-weight: 800;
    padding: 12px 18px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: transform 0.15s ease, background 0.15s ease;
}
.news-btn-source:hover {
    background: var(--primary-light);
}
.news-btn-source:active {
    transform: scale(0.98);
}
.news-btn-modal-share {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    color: var(--text-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.15s ease;
}
.news-btn-modal-share:active {
    transform: scale(0.95);
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="news-page">

    <!-- Header -->
    <div class="news-header">
        <div class="news-header-left">
            <div class="news-header-title">
                <span>📰 Berita Terkini</span>
                <span class="news-live-pill"><span class="news-live-dot"></span> LIVE</span>
            </div>
            <div class="news-header-sub">Agregator 10+ Portal Berita Nasional &amp; Finansial Terpercaya</div>
        </div>
        <button type="button" class="news-refresh-btn" onclick="refreshNewsFeed(this)">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
            <span>Segarkan</span>
        </button>
    </div>

    <!-- Search Box -->
    <div class="news-search-box">
        <svg class="news-search-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="newsSearchInput" class="news-search-input"
               placeholder="Cari berita berdasarkan kata kunci..."
               value="<?= esc($searchQuery) ?>"
               oninput="filterNewsClient(this.value)">
    </div>

    <!-- Category Tabs -->
    <div class="news-cat-scroll">
        <?php foreach ($categories as $c): ?>
            <?php 
                $isActive = ($selectedCategory === $c['name']); 
                $catUrl = '/berita?category=' . urlencode($c['name']) . ($selectedSource ? '&source=' . urlencode($selectedSource) : '');
            ?>
            <a href="<?= $catUrl ?>" class="news-cat-tab <?= $isActive ? 'active' : '' ?>">
                <span><?= esc($c['name']) ?></span>
                <span class="news-cat-tab-badge"><?= $c['count'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Source Pills -->
    <div class="news-source-scroll">
        <a href="/berita?category=<?= urlencode($selectedCategory) ?>" class="news-source-pill <?= empty($selectedSource) ? 'active' : '' ?>">
            <span>🌟</span> Semua Media
        </a>
        <?php foreach ($sources as $s): ?>
            <?php 
                $isSrcActive = ($selectedSource === $s['key']);
                $srcUrl = '/berita?source=' . urlencode($s['key']) . ($selectedCategory !== 'Semua' ? '&category=' . urlencode($selectedCategory) : '');
            ?>
            <a href="<?= $srcUrl ?>" class="news-source-pill <?= $isSrcActive ? 'active' : '' ?>">
                <span><?= $s['icon'] ?></span>
                <span><?= esc($s['name']) ?></span>
                <span style="opacity:0.75;font-size:10px">(<?= $s['count'] ?>)</span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Hero Breaking News Card -->
    <?php if (!empty($headlines) && empty($searchQuery) && $selectedCategory === 'Semua' && empty($selectedSource)): 
        $hero = $headlines[0];
    ?>
    <div class="news-hero-card" onclick="openNewsReader(<?= esc(json_encode($hero), 'attr') ?>)">
        <div class="news-hero-bg" style="background-image: url('<?= esc($hero['has_image'] ? $hero['image'] : '/logo.png') ?>');">
            <div class="news-hero-overlay">
                <div class="news-hero-meta">
                    <span class="news-hero-badge"><?= esc($hero['source']) ?></span>
                    <span class="news-hero-time">⏱️ <?= esc($hero['time_ago']) ?> • <?= esc($hero['category']) ?></span>
                </div>
                <div class="news-hero-title"><?= esc($hero['title']) ?></div>
                <div class="news-hero-desc"><?= esc($hero['description']) ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- News Grid Container -->
    <div class="news-grid" id="newsGridContainer">
        <?php if (empty($news)): ?>
            <div class="news-empty-state" style="grid-column: 1 / -1;">
                <div class="news-empty-icon">📭</div>
                <div class="news-empty-title">Tidak ada berita ditemukan</div>
                <div class="news-empty-sub">Coba ubah kata kunci pencarian atau pilih kategori dan sumber media lainnya.</div>
                <a href="/berita" class="news-cat-tab active" style="display:inline-flex;margin:0 auto">Reset Filter</a>
            </div>
        <?php else: ?>
            <?php foreach ($news as $item): ?>
            <div class="news-card" data-title="<?= esc(strtolower($item['title'])) ?>" data-desc="<?= esc(strtolower($item['description'])) ?>"
                 onclick="openNewsReader(<?= esc(json_encode($item), 'attr') ?>)">
                <div class="news-thumb-wrap">
                    <?php if ($item['has_image']): ?>
                        <img src="<?= esc($item['image']) ?>" alt="<?= esc($item['title']) ?>" class="news-thumb" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="news-thumb-placeholder" style="display:none">
                            <span><?= $item['icon'] ?></span>
                            <span><?= esc($item['source']) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="news-thumb-placeholder">
                            <span><?= $item['icon'] ?></span>
                            <span><?= esc($item['source']) ?></span>
                        </div>
                    <?php endif; ?>
                    <span class="news-source-tag" style="background: <?= esc($item['color']) ?>;">
                        <?= esc($item['source']) ?>
                    </span>
                </div>
                <div class="news-card-body">
                    <div class="news-card-meta">
                        <span class="news-time-badge">⏱️ <?= esc($item['time_ago']) ?></span>
                        <span style="font-weight:700; color:var(--text-muted); font-size:10.5px"><?= esc($item['category']) ?></span>
                    </div>
                    <div class="news-card-title" title="<?= esc($item['title']) ?>"><?= esc($item['title']) ?></div>
                    <div class="news-card-desc"><?= esc($item['description']) ?></div>
                    <div class="news-card-footer">
                        <button type="button" class="news-read-btn">
                            <span>Baca Ringkasan</span>
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </button>
                        <button type="button" class="news-share-icon-btn" title="Bagikan Berita" onclick="event.stopPropagation(); shareNewsArticle(<?= esc(json_encode($item), 'attr') ?>)">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal In-App Reader -->
<div class="news-modal-overlay" id="newsModalOverlay" onclick="closeNewsReader(event)">
    <div class="news-modal-sheet" onclick="event.stopPropagation()">
        <div class="news-modal-handle"></div>
        <div class="news-modal-hdr">
            <span class="news-modal-src-badge" id="modalSourceBadge">Media</span>
            <button type="button" class="news-modal-close" onclick="closeNewsReaderDirect()">✕</button>
        </div>
        <div class="news-modal-body">
            <img src="" alt="" id="modalImage" class="news-modal-img" style="display:none">
            <div class="news-modal-title" id="modalTitle">Judul Berita</div>
            <div class="news-modal-meta">
                <span id="modalCategory">Nasional</span>
                <span>•</span>
                <span id="modalPubDate">Waktu Terbit</span>
            </div>
            <div class="news-modal-text" id="modalDescription">Ringkasan berita...</div>
        </div>
        <div class="news-modal-ftr">
            <a href="#" target="_blank" rel="noopener noreferrer" class="news-btn-source" id="modalLinkBtn">
                <span>Buka Berita di Situs Asli</span>
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/></svg>
            </a>
            <button type="button" class="news-btn-modal-share" id="modalShareBtn" title="Bagikan" onclick="shareCurrentModalNews()">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            </button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let currentModalItem = null;

function openNewsReader(item) {
    currentModalItem = item;
    const overlay = document.getElementById('newsModalOverlay');
    const badge = document.getElementById('modalSourceBadge');
    const img = document.getElementById('modalImage');
    const title = document.getElementById('modalTitle');
    const cat = document.getElementById('modalCategory');
    const pubDate = document.getElementById('modalPubDate');
    const desc = document.getElementById('modalDescription');
    const linkBtn = document.getElementById('modalLinkBtn');

    badge.textContent = item.source || 'Berita';
    badge.style.background = item.color || '#2563EB';

    if (item.has_image && item.image) {
        img.src = item.image;
        img.style.display = 'block';
    } else {
        img.style.display = 'none';
    }

    title.textContent = item.title || '';
    cat.textContent = (item.category || 'Nasional') + ' • ' + (item.time_ago || '');
    pubDate.textContent = item.pub_date || '';
    linkBtn.href = item.link || '#';

    // Tampilkan ringkasan awal dan loader teks lengkap
    desc.innerHTML = '<div style="margin-bottom:12px;font-style:italic;color:var(--text-muted);font-size:13px;border-left:3px solid var(--primary);padding-left:10px;">' + 
                     (item.description ? escapeHtml(item.description) : '') + 
                     '</div><div id="fullArticleLoader" style="display:flex;align-items:center;gap:8px;padding:12px 14px;background:var(--bg);border-radius:12px;font-size:12.5px;color:var(--text-muted);margin-bottom:10px;"><span style="display:inline-block;animation:spin 1s linear infinite">⏳</span> Sedang memuat isi berita lengkap...</div><div id="fullArticleContainer"></div>';

    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';

    // Ambil naskah lengkap dari endpoint grabber backend
    fetch('/api/berita/article?url=' + encodeURIComponent(item.link), { credentials: 'same-origin' })
        .then(r => r.json())
        .then(res => {
            const loader = document.getElementById('fullArticleLoader');
            const cont = document.getElementById('fullArticleContainer');
            if (loader) loader.style.display = 'none';
            if (res.success && res.paragraphs && res.paragraphs.length > 0) {
                let html = '<div style="display:flex;flex-direction:column;gap:14px;">';
                res.paragraphs.forEach(p => {
                    html += '<p style="font-size:14.5px;line-height:1.75;color:var(--text-primary);margin:0;letter-spacing:-0.1px;">' + escapeHtml(p) + '</p>';
                });
                html += '</div>';
                if (cont) cont.innerHTML = html;
            } else {
                if (cont) cont.innerHTML = '<div style="font-size:12px;color:var(--text-muted);padding:8px 0;">(Halaman asli memerlukan interaksi khusus atau berbayar, gunakan tombol di bawah untuk membuka artikel asli)</div>';
            }
        })
        .catch(() => {
            const loader = document.getElementById('fullArticleLoader');
            if (loader) loader.style.display = 'none';
        });
}

function escapeHtml(str) {
    return (str || '').replace(/[&<>'"]/g, 
        tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag)
    );
}

function closeNewsReader(e) {
    if (e.target === document.getElementById('newsModalOverlay')) {
        closeNewsReaderDirect();
    }
}

function closeNewsReaderDirect() {
    document.getElementById('newsModalOverlay').classList.remove('active');
    document.body.style.overflow = '';
}

function shareNewsArticle(item) {
    if (navigator.share) {
        navigator.share({
            title: item.title,
            text: item.description,
            url: item.link
        }).catch(() => {});
    } else {
        navigator.clipboard.writeText(item.title + '\n' + item.link);
        alert('Tautan berita berhasil disalin ke papan klip!');
    }
}

function shareCurrentModalNews() {
    if (currentModalItem) {
        shareNewsArticle(currentModalItem);
    }
}

function filterNewsClient(keyword) {
    const q = (keyword || '').toLowerCase().trim();
    const cards = document.querySelectorAll('#newsGridContainer .news-card');
    let visibleCount = 0;

    cards.forEach(c => {
        const title = c.getAttribute('data-title') || '';
        const desc = c.getAttribute('data-desc') || '';
        if (!q || title.includes(q) || desc.includes(q)) {
            c.style.display = '';
            visibleCount++;
        } else {
            c.style.display = 'none';
        }
    });
}

function refreshNewsFeed(btn) {
    btn.style.pointerEvents = 'none';
    btn.style.opacity = '0.6';
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span style="display:inline-block;animation:spin 0.8s linear infinite">⏳</span> Memperbarui...';

    fetch('/api/berita/refresh', { credentials: 'same-origin' })
        .then(r => r.json())
        .then(() => {
            window.location.reload();
        })
        .catch(() => {
            window.location.reload();
        });
}

// Close modal on Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeNewsReaderDirect();
    }
});
</script>
<?= $this->endSection() ?>
