<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="/belanja_assets/css/style.css?v=22">
<style>
/* ── hide DuitKu FAB, fix belanja wrapper ── */
#fabBtn { display: none !important; }
.belanja-wrapper { padding-bottom: 100px; }

/* ── hide old belanja bottom-nav & install btn ── */
.belanja-wrapper .bottom-nav,
#btn-install { display: none !important; }

/* ── guard DuitKu dynamic-island nav from belanja CSS ── */
#bottomNav.bottom-nav {
    left: 50% !important; transform: translateX(-50%) !important;
    right: auto !important; bottom: 20px !important;
    max-width: 432px !important; height: 64px !important;
    display: grid !important; border-radius: 32px !important;
    padding: 0 8px !important; border-top: none !important;
}

/* ── guard belanja modals from DuitKu opacity rules ── */
.belanja-wrapper .modal-overlay:not(.hidden) {
    opacity: 1 !important; pointer-events: auto !important;
}
@media (max-width: 600px) {
    .belanja-wrapper .modal-overlay:not(.hidden) {
        overflow-y: auto !important; -webkit-overflow-scrolling: touch;
        align-items: flex-start !important; padding-top: 48px !important;
    }
    .belanja-wrapper .modal-content {
        max-height: none !important; overflow-y: visible !important;
        border-radius: 20px !important; margin-bottom: 10px;
    }
}

/* ── page container ── */
.blj-wrap { display: flex; flex-direction: column; height: 100%; }
.belanja-wrapper .app-container { height: auto; min-height: unset; padding-bottom: 0; }

/* ══════════════════════════════════ NEW HEADER */
.blj-header {
    position: sticky; top: 0; z-index: 200;
    background: var(--bg-card, #fff);
    border-bottom: 1px solid var(--border-color, #E8EDF5);
    padding: 0 16px;
    display: flex; align-items: center; gap: 10px;
    min-height: 52px;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
}
[data-theme="dark"] .blj-header {
    background: #1A1D2E; border-bottom-color: #2A2D3E;
    box-shadow: 0 1px 6px rgba(0,0,0,.3);
}
.blj-header-title {
    flex: 1; font-size: 16px; font-weight: 700;
    color: var(--text-primary, #1A1D2E);
    display: flex; align-items: center; gap: 8px;
    overflow: hidden;
}
[data-theme="dark"] .blj-header-title { color: #F1F5FB; }
.blj-header-title .blj-list-name {
    font-size: 12px; font-weight: 500;
    color: var(--text-muted, #9CA3AF);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    max-width: 120px;
}
.blj-header-actions { display: flex; gap: 4px; flex-shrink: 0; }
.blj-hbtn {
    width: 36px; height: 36px; border-radius: 10px; border: none;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 17px; transition: background .15s;
    background: transparent; color: var(--text-secondary, #6B7280);
}
.blj-hbtn:hover, .blj-hbtn:active { background: var(--bg-hover, #F3F4F6); }
[data-theme="dark"] .blj-hbtn { color: #9CA3AF; }
[data-theme="dark"] .blj-hbtn:hover { background: #252840; }
.blj-hbtn.danger:hover { background: #FEE2E2; color: #DC2626; }
[data-theme="dark"] .blj-hbtn.danger:hover { background: #3B1212; color: #F87171; }

/* ══════════════════════════════════ LIST SELECTOR BAR */
.blj-list-bar {
    display: flex; align-items: center; gap: 6px;
    padding: 10px 16px 6px;
    background: var(--bg-card, #fff);
}
[data-theme="dark"] .blj-list-bar { background: #1A1D2E; }
.blj-list-bar .list-select-wrap {
    flex: 1; display: flex; align-items: center; gap: 8px;
    background: var(--bg-surface, #F8FAFC);
    border: 1.5px solid var(--border-color, #E8EDF5);
    border-radius: 10px; padding: 0 12px; height: 40px;
}
[data-theme="dark"] .blj-list-bar .list-select-wrap {
    background: #252840; border-color: #2A2D3E;
}
.blj-list-bar .list-select-wrap svg,
.blj-list-bar .list-select-wrap i { flex-shrink: 0; width: 16px; height: 16px; opacity: .5; }
#list-select {
    flex: 1; border: none; background: transparent; font-size: 14px;
    font-weight: 600; color: var(--text-primary, #1A1D2E); outline: none; cursor: pointer;
}
[data-theme="dark"] #list-select { color: #F1F5FB; }
.blj-list-action {
    width: 36px; height: 36px; border-radius: 10px; border: none;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 16px; transition: background .15s;
    background: var(--bg-surface, #F8FAFC);
    border: 1.5px solid var(--border-color, #E8EDF5);
    color: var(--text-secondary, #6B7280);
}
[data-theme="dark"] .blj-list-action { background: #252840; border-color: #2A2D3E; color: #9CA3AF; }
.blj-list-action:hover { background: #EEF2FF; color: #4F46E5; }
.blj-list-action.danger:hover { background: #FEE2E2; color: #DC2626; }
[data-theme="dark"] .blj-list-action:hover { background: #2D2F55; color: #818CF8; }
[data-theme="dark"] .blj-list-action.danger:hover { background: #3B1212; color: #F87171; }

/* ══════════════════════════════════ TAB BAR */
.blj-tabs {
    display: flex; gap: 6px; overflow-x: auto; scroll-snap-type: x mandatory;
    padding: 8px 16px 10px; background: var(--bg-card, #fff);
    border-bottom: 1px solid var(--border-color, #E8EDF5);
    scrollbar-width: none; -webkit-overflow-scrolling: touch;
    position: sticky; top: 52px; z-index: 190;
}
.blj-tabs::-webkit-scrollbar { display: none; }
[data-theme="dark"] .blj-tabs { background: #1A1D2E; border-bottom-color: #2A2D3E; }
.blj-tab {
    flex-shrink: 0; scroll-snap-align: start;
    display: flex; align-items: center; gap: 5px;
    padding: 7px 14px; border-radius: 20px; border: none;
    background: var(--bg-surface, #F3F4F6);
    color: var(--text-secondary, #6B7280);
    font-size: 12.5px; font-weight: 600; cursor: pointer;
    transition: background .18s, color .18s, transform .12s;
    white-space: nowrap; line-height: 1;
}
.blj-tab:active { transform: scale(.95); }
.blj-tab.active {
    background: var(--primary, #2D5A27);
    color: #fff;
    box-shadow: 0 2px 8px rgba(45,90,39,.3);
}
[data-theme="dark"] .blj-tab { background: #252840; color: #9CA3AF; }
[data-theme="dark"] .blj-tab.active { background: #2D5A27; color: #fff; }
.blj-tab-icon { font-size: 15px; line-height: 1; }

/* ══════════════════════════════════ FAVORITES BAR */
.belanja-wrapper #favorites-bar {
    padding: 6px 16px 2px; background: var(--bg-card, #fff);
}
[data-theme="dark"] .belanja-wrapper #favorites-bar { background: #1A1D2E; }

/* ══════════════════════════════════ SEARCH BAR */
.belanja-wrapper .shopping-search {
    padding: 8px 16px;
    background: var(--bg-card, #fff);
}
[data-theme="dark"] .belanja-wrapper .shopping-search { background: #1A1D2E; }
.belanja-wrapper .search-inline {
    display: flex; align-items: center; gap: 8px;
    background: var(--bg-surface, #F3F4F6);
    border: 1.5px solid var(--border-color, #E8EDF5);
    border-radius: 12px; padding: 0 12px; height: 42px;
}
[data-theme="dark"] .belanja-wrapper .search-inline {
    background: #252840; border-color: #2A2D3E;
}
.belanja-wrapper .search-inline i,
.belanja-wrapper .search-inline svg { opacity: .4; width: 16px; height: 16px; }
#item-search {
    flex: 1; border: none; background: transparent; font-size: 14px;
    color: var(--text-primary, #1A1D2E); outline: none;
}
[data-theme="dark"] #item-search { color: #F1F5FB; }
#item-search::placeholder { color: var(--text-muted, #9CA3AF); }

/* ══════════════════════════════════ CATEGORY PILLS */
.belanja-wrapper .filters-container {
    padding: 6px 16px 8px;
    background: var(--bg-card, #fff);
}
[data-theme="dark"] .belanja-wrapper .filters-container { background: #1A1D2E; }
.belanja-wrapper .category-pills {
    display: flex; gap: 6px; overflow-x: auto; scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
}
.belanja-wrapper .category-pills::-webkit-scrollbar { display: none; }
.belanja-wrapper .pill {
    flex-shrink: 0; padding: 5px 12px; border-radius: 16px; border: none;
    background: var(--bg-surface, #F3F4F6);
    color: var(--text-secondary, #6B7280);
    font-size: 12px; font-weight: 600; cursor: pointer;
    transition: background .15s, color .15s;
}
.belanja-wrapper .pill.active {
    background: #D4EDDA; color: #2D5A27;
}
[data-theme="dark"] .belanja-wrapper .pill { background: #252840; color: #9CA3AF; }
[data-theme="dark"] .belanja-wrapper .pill.active { background: #1C3A1E; color: #6EE7A3; }

/* ══════════════════════════════════ SHOPPING LIST ITEMS */
.belanja-wrapper .shopping-list {
    padding: 8px 12px; display: flex; flex-direction: column; gap: 6px;
}
.belanja-wrapper .shopping-item {
    display: flex; align-items: center; gap: 10px;
    background: var(--bg-card, #fff);
    border: 1.5px solid var(--border-color, #E8EDF5);
    border-radius: 14px; padding: 12px 14px;
    transition: all .2s;
}
[data-theme="dark"] .belanja-wrapper .shopping-item {
    background: #1E2035; border-color: #2A2D3E;
}
.belanja-wrapper .shopping-item.checked {
    opacity: .6; border-style: dashed;
}
.belanja-wrapper .item-check {
    width: 24px; height: 24px; border-radius: 50%; border: 2px solid var(--border-color, #CBD5E0);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    cursor: pointer; transition: all .15s; background: transparent;
}
.belanja-wrapper .shopping-item.checked .item-check {
    background: #2D5A27; border-color: #2D5A27; color: #fff;
}
.belanja-wrapper .item-info { flex: 1; min-width: 0; }
.belanja-wrapper .item-name {
    font-size: 14px; font-weight: 600;
    color: var(--text-primary, #1A1D2E); line-height: 1.3;
}
[data-theme="dark"] .belanja-wrapper .item-name { color: #F1F5FB; }
.belanja-wrapper .shopping-item.checked .item-name {
    text-decoration: line-through; color: var(--text-muted, #9CA3AF);
}
.belanja-wrapper .item-meta {
    font-size: 11.5px; color: var(--text-muted, #9CA3AF); margin-top: 2px;
    display: flex; gap: 8px; flex-wrap: wrap;
}
.belanja-wrapper .item-price-badge {
    font-size: 11.5px; font-weight: 700; color: #2D5A27;
    background: #D4EDDA; border-radius: 6px; padding: 1px 7px;
}
[data-theme="dark"] .belanja-wrapper .item-price-badge { background: #1C3A1E; color: #6EE7A3; }
.belanja-wrapper .item-actions {
    display: flex; gap: 2px; flex-shrink: 0;
}
.belanja-wrapper .item-btn {
    width: 32px; height: 32px; border-radius: 8px; border: none;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; background: transparent; color: var(--text-muted, #9CA3AF);
    font-size: 15px; transition: background .15s, color .15s;
}
.belanja-wrapper .item-btn:hover { background: #F3F4F6; color: #4F46E5; }
[data-theme="dark"] .belanja-wrapper .item-btn:hover { background: #252840; color: #818CF8; }
.belanja-wrapper .item-btn.delete:hover { background: #FEE2E2; color: #DC2626; }
[data-theme="dark"] .belanja-wrapper .item-btn.delete:hover { background: #3B1212; color: #F87171; }

/* ══════════════════════════════════ EMPTY STATE */
.belanja-wrapper .empty-state {
    text-align: center; padding: 48px 20px;
}
.belanja-wrapper .empty-illustration i,
.belanja-wrapper .empty-illustration svg {
    width: 64px; height: 64px; opacity: .2;
    color: var(--text-primary, #1A1D2E);
}
[data-theme="dark"] .belanja-wrapper .empty-illustration i { color: #F1F5FB; }
.belanja-wrapper .empty-state h2 {
    font-size: 17px; font-weight: 700; margin-top: 16px;
    color: var(--text-primary, #1A1D2E);
}
[data-theme="dark"] .belanja-wrapper .empty-state h2 { color: #F1F5FB; }
.belanja-wrapper .empty-state p {
    font-size: 13px; color: var(--text-muted, #9CA3AF); margin-top: 6px;
}

/* ══════════════════════════════════ CONTENT AREA */
.belanja-wrapper .content-area {
    padding-bottom: 16px; min-height: 40px;
}

/* ══════════════════════════════════ SUMMARY FOOTER */
.belanja-wrapper #summary-footer {
    position: fixed; bottom: 92px; left: 50%;
    transform: translateX(-50%);
    width: min(calc(100vw - 24px), 456px);
    border-radius: 16px; z-index: 180;
    background: var(--bg-card, #fff);
    box-shadow: 0 -2px 20px rgba(0,0,0,.12);
    border: 1px solid var(--border-color, #E8EDF5);
    overflow: hidden;
}
[data-theme="dark"] .belanja-wrapper #summary-footer {
    background: #1E2035; border-color: #2A2D3E;
}
.belanja-wrapper .summary-row {
    display: flex; align-items: center; padding: 10px 16px; gap: 12px;
}
.belanja-wrapper .summary-info .label {
    font-size: 11px; font-weight: 600; color: var(--text-muted, #9CA3AF);
    text-transform: uppercase; letter-spacing: .4px; display: block;
}
.belanja-wrapper .summary-info .value {
    font-size: 18px; font-weight: 800; color: #2D5A27;
}
.belanja-wrapper .summary-actions {
    display: flex; gap: 6px; margin-left: auto;
}
.belanja-wrapper .footer-action {
    width: 40px; height: 40px; border-radius: 12px; border: none;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 17px;
    background: var(--bg-surface, #F3F4F6);
    color: var(--text-secondary, #6B7280);
    transition: background .15s, color .15s;
}
.belanja-wrapper .footer-action:hover { background: #EEF2FF; color: #4F46E5; }
[data-theme="dark"] .belanja-wrapper .footer-action { background: #252840; color: #9CA3AF; }
.belanja-wrapper .share-button {
    display: flex; align-items: center; gap: 5px;
    padding: 0 14px; height: 40px; border-radius: 12px; border: none;
    background: #25D366; color: #fff; font-size: 13px; font-weight: 700;
    cursor: pointer; transition: opacity .15s;
}
.belanja-wrapper .share-button:hover { opacity: .88; }

/* ══════════════════════════════════ FAB (add item) */
.blj-fab-btn {
    position: fixed; z-index: 300;
    right: max(20px, calc((100vw - 480px) / 2 + 20px));
    bottom: calc(92px + 56px + 12px);
    width: 52px; height: 52px; border-radius: 50%; border: none;
    background: #2D5A27; color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; font-weight: 300; line-height: 1;
    cursor: pointer; box-shadow: 0 4px 16px rgba(45,90,39,.4);
    transition: transform .15s, box-shadow .15s;
}
.blj-fab-btn:active { transform: scale(.93); box-shadow: 0 2px 8px rgba(45,90,39,.3); }

/* ══════════════════════════════════ SCREEN HEADERS */
.belanja-wrapper .screen-header {
    padding: 16px 16px 8px;
}
.belanja-wrapper .screen-header h2 {
    font-size: 17px; font-weight: 700;
    color: var(--text-primary, #1A1D2E);
}
[data-theme="dark"] .belanja-wrapper .screen-header h2 { color: #F1F5FB; }
.belanja-wrapper .screen-header p,
.belanja-wrapper .last-updated {
    font-size: 12px; color: var(--text-muted, #9CA3AF); margin-top: 2px;
}
.belanja-wrapper .screen-header.with-back {
    display: flex; align-items: center; gap: 10px;
}
.belanja-wrapper .btn-back {
    width: 36px; height: 36px; border-radius: 10px; border: none;
    background: var(--bg-surface, #F3F4F6);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; flex-shrink: 0;
    color: var(--text-secondary, #6B7280);
}
[data-theme="dark"] .belanja-wrapper .btn-back { background: #252840; color: #9CA3AF; }

/* ══════════════════════════════════ INFO TABS */
.belanja-wrapper .info-tabs {
    display: flex; gap: 6px; padding: 10px 16px;
    overflow-x: auto; scrollbar-width: none;
}
.belanja-wrapper .info-tabs::-webkit-scrollbar { display: none; }
.belanja-wrapper .info-tab {
    flex-shrink: 0; display: flex; align-items: center; gap: 5px;
    padding: 7px 14px; border-radius: 20px; border: none;
    background: var(--bg-surface, #F3F4F6);
    color: var(--text-secondary, #6B7280);
    font-size: 12.5px; font-weight: 600; cursor: pointer;
    transition: background .15s, color .15s;
}
.belanja-wrapper .info-tab.active { background: #EEF2FF; color: #4F46E5; }
[data-theme="dark"] .belanja-wrapper .info-tab { background: #252840; color: #9CA3AF; }
[data-theme="dark"] .belanja-wrapper .info-tab.active { background: #2D2F55; color: #818CF8; }

/* ── budget bar ── */
.belanja-wrapper .budget-bar-wrap {
    height: 4px; background: var(--border-color, #E8EDF5);
}
.belanja-wrapper .budget-bar {
    height: 100%; background: #2D5A27; transition: width .4s;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="belanja-wrapper">
<div class="app-container blj-wrap">

    <!-- ═══════════════════ HEADER -->
    <header class="blj-header">
        <div class="blj-header-title">
            <span>🛒</span>
            <span>Belanja</span>
        </div>
        <div class="blj-header-actions">
            <button id="btn-calc"  class="blj-hbtn" title="Kalkulator">🧮</button>
            <button id="btn-menu"  class="blj-hbtn" title="Pengaturan">⚙️</button>
            <button id="btn-reset" class="blj-hbtn danger" title="Hapus Semua">🗑️</button>
        </div>
    </header>

    <!-- ═══════════════════ LIST SELECTOR -->
    <div id="list-selector-bar" class="blj-list-bar list-selector-bar">
        <div class="list-select-wrap">
            <i data-lucide="list"></i>
            <select id="list-select" aria-label="Pilih daftar belanja"></select>
        </div>
        <button id="btn-add-list"    class="blj-list-action" title="Tambah daftar baru"><i data-lucide="plus"></i></button>
        <button id="btn-rename-list" class="blj-list-action" title="Ubah nama"><i data-lucide="pencil"></i></button>
        <button id="btn-delete-list" class="blj-list-action danger" title="Hapus daftar"><i data-lucide="trash-2"></i></button>
    </div>

    <!-- ═══════════════════ TAB BAR -->
    <div class="blj-tabs">
        <button class="blj-tab nav-item active" data-screen="list">
            <span class="blj-tab-icon">🛒</span> Belanja
        </button>
        <button class="blj-tab nav-item" data-screen="info">
            <span class="blj-tab-icon">📊</span> Info
        </button>
        <button class="blj-tab nav-item" data-screen="pantry">
            <span class="blj-tab-icon">🥘</span> Stok Dapur
        </button>
        <button class="blj-tab nav-item" data-screen="notes">
            <span class="blj-tab-icon">📓</span> Catatan
        </button>
        <button class="blj-tab nav-item" data-screen="storage">
            <span class="blj-tab-icon">📍</span> Simpanan
        </button>
    </div>

    <!-- ═══════════════════ FAVORITES QUICK-ADD -->
    <section id="favorites-bar" class="favorites-bar hidden">
        <span class="favorites-label"><i data-lucide="star"></i> Sering dibeli:</span>
        <div id="favorites-chips" class="favorites-chips"></div>
    </section>

    <!-- ═══════════════════ SEARCH -->
    <section id="shopping-search" class="shopping-search">
        <div class="search-inline">
            <i data-lucide="search"></i>
            <input type="text" id="item-search" placeholder="Cari barang di daftar ini...">
            <button id="btn-clear-search" class="search-clear hidden" title="Hapus">&times;</button>
        </div>
    </section>

    <!-- ═══════════════════ CATEGORY FILTERS -->
    <section id="shopping-filters" class="filters-container">
        <div class="category-pills">
            <button class="pill active" data-category="all">Semua</button>
            <button class="pill" data-category="Sayur">🥬 Sayur</button>
            <button class="pill" data-category="Ikan">🐟 Ikan</button>
            <button class="pill" data-category="Buah">🍎 Buah</button>
            <button class="pill" data-category="Bumbu">🌶️ Bumbu</button>
            <button class="pill" data-category="Lainnya">📦 Lainnya</button>
        </div>
    </section>

    <!-- ═══════════════════ SHOPPING LIST SCREEN -->
    <main id="list-container" class="content-area">
        <div id="empty-state" class="empty-state hidden">
            <div class="empty-illustration"><i data-lucide="shopping-basket"></i></div>
            <h2>Belum ada barang</h2>
            <p>Ketuk tombol + untuk menambah barang belanjaan.</p>
        </div>
        <div id="shopping-list" class="shopping-list"></div>
    </main>

    <!-- ═══════════════════ INFO SCREEN -->
    <main id="info-screen" class="content-area hidden">
        <div class="info-tabs">
            <button class="info-tab active" data-tab="prices"><i data-lucide="line-chart"></i><span>Cek Harga</span></button>
            <button class="info-tab" data-tab="kurs"><i data-lucide="banknote"></i><span>Kurs</span></button>
            <button class="info-tab" data-tab="recipes"><i data-lucide="utensils"></i><span>Resep</span></button>
        </div>
        <div id="prices-panel" class="info-panel">
            <div class="screen-header">
                <h2>Cek Harga Update</h2>
                <p class="last-updated">Sumber: BPS & Pasar (Data Simulasi)</p>
            </div>
            <div class="price-tabs">
                <button class="price-tab active" data-group="sembako">Sembako</button>
                <button class="price-tab" data-group="logam">Logam Mulia</button>
                <button class="price-tab" data-group="bbm">BBM</button>
            </div>
            <div id="price-items-container" class="price-list-container">
                <div class="loading-state"><div class="spinner"></div><p>Mengambil data harga...</p></div>
            </div>
        </div>
        <div id="kurs-panel" class="info-panel hidden">
            <div class="screen-header">
                <h2>Kurs Terkini</h2>
                <p class="last-updated">Nilai tukar Rupiah (IDR) terhadap mata uang asing</p>
            </div>
            <div class="kurs-converter">
                <div class="kurs-conv-row">
                    <input type="number" id="kurs-conv-amount" placeholder="Jumlah" inputmode="decimal" value="1">
                    <select id="kurs-conv-currency"></select>
                </div>
                <div class="kurs-conv-result">
                    <i data-lucide="arrow-down"></i>
                    <span id="kurs-conv-output">Rp 0</span>
                </div>
            </div>
            <div id="kurs-items-container" class="kurs-list-container">
                <div class="loading-state"><div class="spinner"></div><p>Mengambil data kurs...</p></div>
            </div>
        </div>
        <div id="recipes-panel" class="info-panel hidden">
            <div class="screen-header">
                <h2>Inspirasi Resep</h2>
                <div class="search-container">
                    <input type="text" id="recipe-search" placeholder="Cari resep masakan...">
                    <button id="btn-search-recipe"><i data-lucide="search"></i></button>
                </div>
            </div>
            <div id="recipes-container" class="recipe-grid"></div>
        </div>
    </main>

    <!-- ═══════════════════ NOTES SCREEN -->
    <main id="notes-screen" class="content-area hidden">
        <div class="screen-header">
            <h2>Catatan Saya</h2>
            <p class="last-updated">Simpan daftar atau pengingat di sini</p>
        </div>
        <div id="notes-list-container" class="notes-list-container"></div>
    </main>

    <!-- ═══════════════════ STORAGE SCREEN -->
    <main id="storage-screen" class="content-area hidden">
        <div class="screen-header">
            <h2>Simpanan Barang</h2>
            <p class="last-updated">Catat di mana Anda menaruh barang penting</p>
            <div class="search-container">
                <input type="text" id="storage-search" placeholder="Cari barang atau tempat...">
                <button id="btn-search-storage"><i data-lucide="search"></i></button>
            </div>
        </div>
        <div id="storage-list-container" class="storage-list-container"></div>
    </main>

    <!-- ═══════════════════ PANTRY SCREEN -->
    <main id="pantry-screen" class="content-area hidden">
        <div class="screen-header">
            <h2>Stok Dapur</h2>
            <p class="last-updated">Pantau persediaan & kirim yang menipis ke daftar belanja</p>
            <div class="search-container">
                <input type="text" id="pantry-search" placeholder="Cari stok...">
                <button id="btn-search-pantry"><i data-lucide="search"></i></button>
            </div>
        </div>
        <div id="pantry-list-container" class="pantry-list-container"></div>
    </main>

    <!-- ═══════════════════ HISTORY SCREEN -->
    <main id="history-screen" class="content-area hidden">
        <div class="screen-header with-back">
            <button class="btn-back" data-back="list"><i data-lucide="arrow-left"></i></button>
            <div>
                <h2>Riwayat Belanja</h2>
                <p class="last-updated">Rekap pengeluaran belanja Anda</p>
            </div>
        </div>
        <div id="history-summary" class="history-summary"></div>
        <div id="history-chart" class="history-chart"></div>
        <div id="history-list-container" class="history-list-container"></div>
    </main>

    <!-- ═══════════════════ REMINDERS SCREEN -->
    <main id="reminders-screen" class="content-area hidden">
        <div class="screen-header with-back">
            <button class="btn-back" data-back="list"><i data-lucide="arrow-left"></i></button>
            <div>
                <h2>Pengingat</h2>
                <p class="last-updated">Tagihan, jadwal, atau hal penting lainnya</p>
            </div>
        </div>
        <div id="reminders-list-container" class="reminders-list-container"></div>
    </main>

    <!-- ═══════════════════ SUMMARY FOOTER -->
    <footer id="summary-footer" class="summary-footer hidden">
        <div id="budget-bar-wrap" class="budget-bar-wrap hidden">
            <div id="budget-bar" class="budget-bar"></div>
        </div>
        <div class="summary-row">
            <div class="summary-left">
                <div class="summary-info">
                    <span class="label">Total Estimasi</span>
                    <span id="total-price" class="value">Rp 0</span>
                </div>
                <div id="budget-info" class="budget-info hidden">
                    <span class="label">Sisa Anggaran:</span>
                    <span id="budget-value" class="budget-value">Rp 0</span>
                </div>
            </div>
            <div class="summary-actions">
                <button id="btn-print"  class="footer-action" title="Cetak / PDF"><i data-lucide="printer"></i></button>
                <button id="btn-finish" class="footer-action" title="Selesai & simpan riwayat"><i data-lucide="check-check"></i></button>
                <button id="btn-share"  class="share-button"><i data-lucide="share-2"></i><span>WA</span></button>
            </div>
        </div>
    </footer>

    <!-- hidden original nav (JS needs .nav-item[data-screen] for active state sync) -->
    <nav style="display:none!important">
        <button class="nav-item" data-screen="list"></button>
        <button class="nav-item" data-screen="info"></button>
        <button class="nav-item" data-screen="pantry"></button>
        <button class="nav-item" data-screen="notes"></button>
        <button class="nav-item" data-screen="storage"></button>
    </nav>

    <!-- ═══════════════════════════════════ MODALS ═══ -->

    <!-- Add/Edit Item Modal -->
    <div id="item-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Tambah Barang</h2>
                <button class="close-modal">&times;</button>
            </div>
            <form id="item-form">
                <input type="hidden" id="item-id">
                <div class="form-group">
                    <label for="item-name">Nama Barang</label>
                    <div class="input-with-mic">
                        <input type="text" id="item-name" placeholder="Misal: Tomat, Ayam, Cabai..." required>
                        <button type="button" id="btn-voice" class="mic-button" title="Input suara"><i data-lucide="mic"></i></button>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="item-qty">Jumlah</label>
                        <input type="text" id="item-qty" placeholder="Misal: 1kg, 2 ikat">
                    </div>
                    <div class="form-group">
                        <label for="item-price">Harga (Opsional)</label>
                        <input type="number" id="item-price" placeholder="Misal: 5000">
                    </div>
                </div>
                <div class="form-group">
                    <label for="item-category">Kategori</label>
                    <select id="item-category">
                        <option value="Sayur">🥬 Sayur</option>
                        <option value="Ikan">🐟 Ikan</option>
                        <option value="Buah">🍎 Buah</option>
                        <option value="Bumbu">🌶️ Bumbu</option>
                        <option value="Kebutuhan Rumah">🏠 Kebutuhan Rumah</option>
                        <option value="Lainnya" selected>📦 Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="item-notes">Catatan (Pesan)</label>
                    <textarea id="item-notes" placeholder="Misal: Yang sudah matang, jangan yang busuk..." rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Foto Barang (Opsional)</label>
                    <div class="image-upload-area">
                        <input type="file" id="item-image" accept="image/*" capture="environment" hidden>
                        <button type="button" id="btn-capture" class="btn-outline">
                            <i data-lucide="camera"></i><span>Ambil Foto</span>
                        </button>
                        <div id="image-preview" class="image-preview hidden">
                            <img src="" alt="Preview">
                            <button type="button" class="remove-image">&times;</button>
                        </div>
                    </div>
                </div>
                <button type="submit" id="btn-save" class="btn-primary">Simpan</button>
            </form>
        </div>
    </div>

    <!-- Note Modal -->
    <div id="note-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="note-modal-title">Buat Catatan</h2>
                <button class="close-note-modal close-modal">&times;</button>
            </div>
            <form id="note-form">
                <input type="hidden" id="note-id">
                <div class="form-group">
                    <label for="note-title">Judul (Opsional)</label>
                    <input type="text" id="note-title" placeholder="Misal: Resep Ayam Goreng">
                </div>
                <div class="form-group">
                    <label for="note-content">Isi Catatan</label>
                    <textarea id="note-content" placeholder="Tuliskan catatan Anda di sini..." rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <label>Foto (Opsional)</label>
                    <div class="image-upload-area">
                        <input type="file" id="note-image-input" accept="image/*" capture="environment" hidden>
                        <button type="button" id="btn-capture-note" class="btn-outline">
                            <i data-lucide="camera"></i><span>Ambil Foto</span>
                        </button>
                        <div id="note-image-preview" class="image-preview hidden">
                            <img src="" alt="Preview">
                            <button type="button" class="remove-note-image remove-image">&times;</button>
                        </div>
                    </div>
                </div>
                <button type="submit" id="btn-save-note" class="btn-primary">Simpan Catatan</button>
            </form>
        </div>
    </div>

    <!-- Storage Modal -->
    <div id="storage-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="storage-modal-title">Simpan Lokasi Barang</h2>
                <button class="close-storage-modal close-modal">&times;</button>
            </div>
            <form id="storage-form">
                <input type="hidden" id="storage-id">
                <div class="form-group">
                    <label for="storage-name">Nama Barang</label>
                    <input type="text" id="storage-name" placeholder="Misal: Kunci motor, Sertifikat rumah..." required>
                </div>
                <div class="form-group">
                    <label for="storage-location">Tempat Penyimpanan</label>
                    <input type="text" id="storage-location" placeholder="Misal: Laci meja kamar, Lemari atas..." required>
                </div>
                <div class="form-group">
                    <label for="storage-notes">Catatan (Opsional)</label>
                    <textarea id="storage-notes" placeholder="Misal: Di dalam kotak biru, sebelah dompet..." rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Foto (Opsional)</label>
                    <div class="image-upload-area">
                        <input type="file" id="storage-image-input" accept="image/*" capture="environment" hidden>
                        <button type="button" id="btn-capture-storage" class="btn-outline">
                            <i data-lucide="camera"></i><span>Ambil Foto</span>
                        </button>
                        <div id="storage-image-preview" class="image-preview hidden">
                            <img src="" alt="Preview">
                            <button type="button" class="remove-storage-image remove-image">&times;</button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Lokasi GPS (Opsional)</label>
                    <button type="button" id="btn-get-location" class="btn-outline">
                        <i data-lucide="map-pin"></i><span>Ambil Lokasi Saat Ini</span>
                    </button>
                    <div id="storage-location-preview" class="location-preview hidden">
                        <i data-lucide="map-pin"></i>
                        <span id="storage-location-text">Lokasi tersimpan</span>
                        <button type="button" class="remove-location remove-image">&times;</button>
                    </div>
                </div>
                <button type="submit" id="btn-save-storage" class="btn-primary">Simpan</button>
            </form>
        </div>
    </div>

    <!-- Settings Modal -->
    <div id="settings-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Menu &amp; Pengaturan</h2>
                <button id="close-settings-modal-x" class="close-modal">&times;</button>
            </div>
            <div class="settings-body">
                <div class="settings-section">
                    <label><i data-lucide="layout-grid"></i> Layar</label>
                    <button class="settings-link nav-item nav-item-btn" data-screen="list"><i data-lucide="shopping-cart"></i> Daftar Belanja</button>
                    <button class="settings-link nav-item nav-item-btn" data-screen="info"><i data-lucide="newspaper"></i> Info &amp; Harga</button>
                    <button class="settings-link nav-item nav-item-btn" data-screen="pantry"><i data-lucide="refrigerator"></i> Stok Dapur</button>
                    <button class="settings-link nav-item nav-item-btn" data-screen="notes"><i data-lucide="notebook-pen"></i> Catatan</button>
                    <button class="settings-link nav-item nav-item-btn" data-screen="storage"><i data-lucide="map-pin"></i> Simpanan Barang</button>
                </div>
                <div class="settings-section">
                    <label for="setting-budget"><i data-lucide="wallet"></i> Anggaran Belanja (per daftar)</label>
                    <div class="kurs-conv-row">
                        <input type="number" id="setting-budget" placeholder="Misal: 200000" inputmode="numeric">
                        <button id="btn-save-budget" class="btn-primary" style="width:auto;padding:0 18px;">Simpan</button>
                    </div>
                    <small class="settings-hint">Kosongkan lalu simpan untuk menghapus anggaran.</small>
                </div>
                <div class="settings-section">
                    <label><i data-lucide="palette"></i> Tampilan</label>
                    <div class="theme-toggle-row">
                        <span>Mode Terang</span>
                        <button id="btn-toggle-theme" class="theme-switch" role="switch" aria-checked="false">
                            <span class="theme-knob"></span>
                        </button>
                    </div>
                    <div class="autobackup-row">
                        <span><i data-lucide="type"></i> Ukuran Huruf</span>
                        <select id="setting-fontscale">
                            <option value="15">Kecil</option>
                            <option value="16">Normal</option>
                            <option value="18">Besar</option>
                            <option value="20">Sangat Besar</option>
                        </select>
                    </div>
                </div>
                <div class="settings-section">
                    <label><i data-lucide="layout-grid"></i> Fitur Lain</label>
                    <button id="btn-open-parking" class="settings-link"><i data-lucide="circle-parking"></i> Lokasi Parkir</button>
                    <button class="settings-link" data-open="history"><i data-lucide="history"></i> Riwayat Belanja</button>
                    <button class="settings-link" data-open="reminders"><i data-lucide="bell"></i> Pengingat</button>
                </div>
                <div class="settings-section">
                    <button id="btn-open-about" class="settings-link"><i data-lucide="info"></i> Tentang Aplikasi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pantry Modal -->
    <div id="pantry-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="pantry-modal-title">Tambah Stok</h2>
                <button class="close-pantry-modal close-modal">&times;</button>
            </div>
            <form id="pantry-form">
                <input type="hidden" id="pantry-id">
                <div class="form-group">
                    <label for="pantry-name">Nama Barang</label>
                    <input type="text" id="pantry-name" placeholder="Misal: Beras, Minyak goreng..." required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="pantry-qty">Jumlah/Sisa</label>
                        <input type="text" id="pantry-qty" placeholder="Misal: 2 kg, 1 botol">
                    </div>
                    <div class="form-group">
                        <label for="pantry-category">Kategori</label>
                        <select id="pantry-category">
                            <option value="Sayur">🥬 Sayur</option>
                            <option value="Ikan">🐟 Ikan</option>
                            <option value="Buah">🍎 Buah</option>
                            <option value="Bumbu">🌶️ Bumbu</option>
                            <option value="Kebutuhan Rumah">🏠 Kebutuhan Rumah</option>
                            <option value="Lainnya" selected>📦 Lainnya</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="pantry-status">Status Stok</label>
                    <select id="pantry-status">
                        <option value="cukup">✅ Cukup</option>
                        <option value="menipis">⚠️ Menipis</option>
                        <option value="habis">❌ Habis</option>
                    </select>
                </div>
                <button type="submit" id="btn-save-pantry" class="btn-primary">Simpan</button>
            </form>
        </div>
    </div>

    <!-- Reminder Modal -->
    <div id="reminder-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="reminder-modal-title">Tambah Pengingat</h2>
                <button class="close-reminder-modal close-modal">&times;</button>
            </div>
            <form id="reminder-form">
                <input type="hidden" id="reminder-id">
                <div class="form-group">
                    <label for="reminder-title">Pengingat</label>
                    <input type="text" id="reminder-title" placeholder="Misal: Bayar listrik, Arisan..." required>
                </div>
                <div class="form-group">
                    <label for="reminder-date">Tanggal</label>
                    <input type="date" id="reminder-date" required>
                </div>
                <div class="form-group">
                    <label for="reminder-note">Catatan (Opsional)</label>
                    <textarea id="reminder-note" placeholder="Detail tambahan..." rows="2"></textarea>
                </div>
                <button type="submit" id="btn-save-reminder" class="btn-primary">Simpan Pengingat</button>
            </form>
        </div>
    </div>

    <!-- Parking Modal -->
    <div id="parking-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🅿️ Lokasi Parkir</h2>
                <button class="close-parking-modal close-modal">&times;</button>
            </div>
            <button type="button" id="btn-save-parking" class="btn-primary" style="margin-bottom:14px;">
                <i data-lucide="map-pin" style="width:18px;height:18px;vertical-align:middle;margin-right:6px;"></i>
                Simpan Lokasi Parkir Sekarang
            </button>
            <div class="form-group">
                <label for="parking-note">Catatan (Opsional)</label>
                <input type="text" id="parking-note" placeholder="Misal: Lantai 2, Zona B, dekat lift">
            </div>
            <div class="form-group">
                <label>Foto (Opsional)</label>
                <div class="image-upload-area">
                    <input type="file" id="parking-image-input" accept="image/*" capture="environment" hidden>
                    <button type="button" id="btn-capture-parking" class="btn-outline">
                        <i data-lucide="camera"></i><span>Ambil Foto</span>
                    </button>
                    <div id="parking-image-preview" class="image-preview hidden">
                        <img src="" alt="Preview">
                        <button type="button" class="remove-parking-image remove-image">&times;</button>
                    </div>
                </div>
            </div>
            <div id="parking-saved" class="parking-saved hidden">
                <div class="parking-saved-head">
                    <i data-lucide="circle-parking"></i>
                    <span id="parking-saved-time">-</span>
                </div>
                <p id="parking-saved-note" class="parking-saved-note"></p>
                <img id="parking-saved-img" class="parking-saved-img hidden" src="" alt="Foto parkir">
                <div class="parking-saved-actions">
                    <button type="button" id="btn-parking-map" class="btn-primary"><i data-lucide="navigation"></i> Arahkan ke Lokasi</button>
                    <button type="button" id="btn-parking-delete" class="icon-button danger"><i data-lucide="trash-2"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- About Modal -->
    <div id="info-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Informasi Aplikasi</h2>
                <button id="close-info-modal-x" class="close-modal">&times;</button>
            </div>
            <div class="info-body" style="padding:10px 0 20px 0;display:flex;flex-direction:column;align-items:center;text-align:center;">
                <div class="profile-frame">
                    <img src="/belanja_assets/zusfan.png" alt="Developer" class="profile-img">
                </div>
                <p style="margin-bottom:5px;"><strong>Developer:</strong> Zusfan</p>
                <p><strong>Whatsapp:</strong> <a href="https://wa.me/628998813000" target="_blank" style="color:#2D5A27;font-weight:700;text-decoration:none;">08998813000</a></p>
            </div>
            <button id="btn-close-info" class="btn-primary">Tutup</button>
        </div>
    </div>

    <!-- Calculator Modal -->
    <div id="calc-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Kalkulator</h2>
                <button id="close-calc-modal-x" class="close-modal">&times;</button>
            </div>
            <div class="calc-tabs">
                <button class="calc-tab-btn active" data-tab="discount">Potongan %</button>
                <button class="calc-tab-btn" data-tab="standard">Tambah-Tambah</button>
                <button class="calc-tab-btn" data-tab="change">Kembalian</button>
                <button class="calc-tab-btn" data-tab="compare">Banding Harga</button>
            </div>
            <div id="tab-discount" class="calc-tab-content">
                <div class="calc-body">
                    <div class="form-group"><label>Harga Asli (Rp)</label><input type="number" id="calc-price" placeholder="Misal: 100000" inputmode="numeric"></div>
                    <div class="form-group">
                        <label>Potongan (%)</label>
                        <div class="percent-grid">
                            <button class="percent-btn" data-value="5">5%</button>
                            <button class="percent-btn" data-value="10">10%</button>
                            <button class="percent-btn" data-value="15">15%</button>
                            <button class="percent-btn" data-value="20">20%</button>
                            <button class="percent-btn" data-value="25">25%</button>
                            <button class="percent-btn" data-value="50">50%</button>
                        </div>
                        <input type="number" id="calc-percent" placeholder="Atau isi sendiri %" style="margin-top:10px;" inputmode="numeric">
                    </div>
                    <div id="calc-result-box" class="result-box hidden">
                        <div class="result-item"><span>Hemat (Potongan):</span><strong id="res-savings" class="text-success">Rp 0</strong></div>
                        <div class="result-item large"><span>Harga Jadi (Bayar):</span><strong id="res-final" class="text-primary">Rp 0</strong></div>
                    </div>
                </div>
            </div>
            <div id="tab-standard" class="calc-tab-content hidden">
                <div class="standard-calc">
                    <div class="calc-display" id="std-display">0</div>
                    <div class="calc-grid">
                        <button class="calc-btn danger" data-op="clear">C</button>
                        <button class="calc-btn" data-op="/">&divide;</button>
                        <button class="calc-btn" data-op="*">&times;</button>
                        <button class="calc-btn danger" data-op="back"><i data-lucide="delete"></i></button>
                        <button class="calc-btn" data-num="7">7</button>
                        <button class="calc-btn" data-num="8">8</button>
                        <button class="calc-btn" data-num="9">9</button>
                        <button class="calc-btn" data-op="-">-</button>
                        <button class="calc-btn" data-num="4">4</button>
                        <button class="calc-btn" data-num="5">5</button>
                        <button class="calc-btn" data-num="6">6</button>
                        <button class="calc-btn" data-op="+">+</button>
                        <button class="calc-btn" data-num="1">1</button>
                        <button class="calc-btn" data-num="2">2</button>
                        <button class="calc-btn" data-num="3">3</button>
                        <button class="calc-btn primary-btn-calc" data-op="=">=</button>
                        <button class="calc-btn span-2" data-num="0">0</button>
                        <button class="calc-btn" data-num=".">.</button>
                    </div>
                </div>
            </div>
            <div id="tab-change" class="calc-tab-content hidden">
                <div class="calc-body">
                    <div class="form-group"><label>Total Belanja (Rp)</label>
                        <input type="number" id="change-total" placeholder="Misal: 85000" inputmode="numeric">
                        <button type="button" id="btn-use-list-total" class="btn-outline" style="margin-top:8px;"><i data-lucide="clipboard-list"></i><span>Pakai total daftar aktif</span></button>
                    </div>
                    <div class="form-group"><label>Uang Dibayar (Rp)</label><input type="number" id="change-paid" placeholder="Misal: 100000" inputmode="numeric"></div>
                    <div id="change-result-box" class="result-box hidden">
                        <div class="result-item large"><span id="change-label">Kembalian:</span><strong id="change-result" class="text-primary">Rp 0</strong></div>
                    </div>
                </div>
            </div>
            <div id="tab-compare" class="calc-tab-content hidden">
                <div class="calc-body">
                    <p class="settings-hint" style="margin-bottom:10px;">Bandingkan 2 produk dengan satuan yang sama. Isi harga &amp; jumlahnya.</p>
                    <div class="compare-grid">
                        <div class="compare-col"><strong>Produk A</strong><input type="number" id="cmp-a-price" placeholder="Harga (Rp)" inputmode="numeric"><input type="number" id="cmp-a-qty" placeholder="Jumlah/isi (mis. 500)" inputmode="decimal"></div>
                        <div class="compare-col"><strong>Produk B</strong><input type="number" id="cmp-b-price" placeholder="Harga (Rp)" inputmode="numeric"><input type="number" id="cmp-b-qty" placeholder="Jumlah/isi (mis. 1000)" inputmode="decimal"></div>
                    </div>
                    <div id="compare-result-box" class="result-box hidden">
                        <div class="result-item"><span>Harga/satuan A:</span><strong id="cmp-a-unit">Rp 0</strong></div>
                        <div class="result-item"><span>Harga/satuan B:</span><strong id="cmp-b-unit">Rp 0</strong></div>
                        <div class="result-item large"><span>Lebih hemat:</span><strong id="cmp-winner" class="text-success">-</strong></div>
                    </div>
                </div>
            </div>
            <button id="btn-close-calc" class="btn-primary" style="margin-top:15px;">Tutup</button>
        </div>
    </div>

    <!-- Install Modal -->
    <div id="install-modal" class="modal-overlay hidden">
        <div class="modal-content install-modal-content">
            <div class="modal-header">
                <h2>Install Aplikasi</h2>
                <button id="close-install-modal-x" class="close-modal">&times;</button>
            </div>
            <div class="install-body" style="text-align:center;padding:10px 0 20px 0;">
                <div class="install-icon-container"><img src="/belanja_assets/logo.png" alt="App Logo" class="install-app-logo"></div>
                <h3 style="margin-bottom:10px;">Daftar Belanja Pasar</h3>
                <p style="color:var(--text-muted);font-size:.9rem;margin-bottom:20px;">Install aplikasi ini di HP Anda agar lebih cepat, mudah dibuka, dan bisa dipakai tanpa internet!</p>
                <button id="btn-install-now" class="btn-primary"><i data-lucide="download" style="width:18px;height:18px;vertical-align:middle;margin-right:5px;"></i>Install Sekarang</button>
                <button id="btn-later" style="background:none;border:none;color:var(--text-muted);margin-top:15px;font-size:.85rem;cursor:pointer;">Nanti Saja</button>
            </div>
        </div>
    </div>

</div><!-- .app-container -->
</div><!-- .belanja-wrapper -->

<!-- Recipe Detail Modal (outside .app-container to avoid z-index issues) -->
<div id="recipe-modal" class="modal-overlay hidden">
    <div class="modal-content recipe-detail">
        <div class="modal-header">
            <h2 id="recipe-modal-title">Detail Resep</h2>
            <button class="close-recipe-modal icon-button" id="close-recipe-modal-x"><i data-lucide="x"></i></button>
        </div>
        <div id="recipe-detail-content" class="modal-body">
            <div class="loading-state"><div class="spinner"></div><p>Memuat resep...</p></div>
        </div>
    </div>
</div>

<!-- Print area -->
<div id="print-area"></div>

<!-- FAB: Tambah barang (positioned above DuitKu nav) -->
<button id="fab-add" class="blj-fab-btn" title="Tambah Barang">+</button>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://unpkg.com/lucide@latest"></script>
<script src="/belanja_assets/js/app.js?v=24"></script>
<script>
    lucide.createIcons();

    // Sync blj-tabs active state when belanja JS switches screens
    (function() {
        const tabs = document.querySelectorAll('.blj-tabs .blj-tab');
        const observer = new MutationObserver(() => {
            tabs.forEach(t => {
                const screen = t.dataset.screen;
                const hiddenBtn = document.querySelector(`.app-container > nav .nav-item[data-screen="${screen}"]`);
                t.classList.toggle('active', hiddenBtn ? hiddenBtn.classList.contains('active') : false);
            });
        });
        document.querySelectorAll('.app-container > nav .nav-item').forEach(n =>
            observer.observe(n, { attributes: true, attributeFilter: ['class'] })
        );

        // Direct tab click also triggers the hidden nav-item click (belanja JS listens on those)
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const screen = tab.dataset.screen;
                const target = document.querySelector(`.app-container > nav .nav-item[data-screen="${screen}"]`);
                if (target) target.click();
            });
        });
    })();

    // Unregister old belanja SW
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(regs => {
            regs.forEach(reg => { if (reg.scope && reg.scope.includes('belanja')) reg.unregister(); });
        });
    }
</script>
<?= $this->endSection() ?>
