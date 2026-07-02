<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="/belanja_assets/css/style.css?v=22">
<style>
    /* Override belanja styles to integrate with DuitKu layout */
    .belanja-wrapper {
        padding-bottom: 108px; /* nav(64) + gap(20) + extra(24) */
    }
    /* Hide the belanja-specific bottom nav since DuitKu has its own */
    .belanja-wrapper .bottom-nav {
        display: none !important;
    }
    
    /* HIDE the global DuitKu FAB (for transactions) because Belanja needs its own */
    #fabBtn {
        display: none !important;
    }
    
    /* SHOW and position the Belanja FAB (#fab-add) above DuitKu Dynamic Island nav */
    @media (min-width: 481px) {
        .belanja-wrapper #fab-add {
            right: calc((100vw - 480px) / 2 + 20px) !important;
            bottom: calc(var(--nav-height) + 16px) !important;
            display: flex !important;
            z-index: 9999 !important;
        }
    }
    @media (max-width: 480px) {
        .belanja-wrapper #fab-add {
            right: 20px !important;
            bottom: calc(var(--nav-height) + 16px) !important;
            display: flex !important;
            z-index: 9999 !important;
        }
    }

    /* Adjust header to not overlap with DuitKu topbar */
    .belanja-wrapper .main-header {
        position: sticky;
        top: 0;
        z-index: 100;
    }
    /* Protect DuitKu's Dynamic Island nav from belanja style.css collision */
    #bottomNav.bottom-nav {
        left: 50% !important;
        transform: translateX(-50%) !important;
        right: auto !important;
        bottom: 20px !important;
        max-width: 432px !important;
        height: 64px !important;
        display: grid !important;
        border-radius: 32px !important;
        padding: 0 8px !important;
        border-top: none !important;
    }

    /* Fix Belanja modals being invisible due to DuitKu's opacity rules */
    .belanja-wrapper .modal-overlay:not(.hidden) {
        opacity: 1 !important;
        pointer-events: auto !important;
    }

    /* Ensure app-container fills available space */
    .belanja-wrapper .app-container {
        height: auto;
        min-height: unset;
        padding-bottom: 0;
    }
    /* Hide the old belanja fab wrapper since we style #fab-add directly now */
    .belanja-fab-wrapper {
        position: static;
    }
    
    /* Summary footer sits above DuitKu Dynamic Island nav */
    .belanja-wrapper #summary-footer {
        bottom: 92px;  /* nav(64) + gap(20) + margin(8) */
    }

    /* Fix: modal bisa di-scroll di mobile saat keyboard muncul */
    @media (max-width: 600px) {
        .belanja-wrapper .modal-overlay:not(.hidden) {
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch;
            align-items: flex-start !important;
            padding-top: 48px !important;
        }
        .belanja-wrapper .modal-content {
            max-height: none !important;
            overflow-y: visible !important;
            border-radius: 20px !important;
            margin-bottom: 10px;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="belanja-wrapper">
    <div id="app" class="app-container">

        <!-- Belanja Header -->
        <header class="main-header">
            <div class="header-content">
                <img src="/belanja_assets/logo.png" alt="Logo" class="app-logo">
                <div class="header-text">
                    <h1>Daftar Belanja</h1>
                    <p id="online-status" class="status-indicator online">Online</p>
                </div>
            </div>
            <div class="header-actions">
                <button id="btn-install" class="icon-button info hidden" title="Install Aplikasi">
                    <i data-lucide="download"></i>
                </button>
                <button id="btn-calc" class="icon-button warning" title="Kalkulator Diskon">
                    <i data-lucide="calculator"></i>
                </button>
                <button id="btn-menu" class="icon-button info" title="Menu & Pengaturan">
                    <i data-lucide="menu"></i>
                </button>
                <button id="btn-reset" class="icon-button danger" title="Hapus Semua">
                    <i data-lucide="trash-2"></i>
                </button>
            </div>
        </header>

        <!-- List Selector (Shopping List Only) -->
        <section id="list-selector-bar" class="list-selector-bar">
            <div class="list-select-wrap">
                <i data-lucide="list"></i>
                <select id="list-select" aria-label="Pilih daftar belanja"></select>
            </div>
            <button id="btn-add-list" class="icon-button info" title="Tambah daftar baru"><i data-lucide="plus"></i></button>
            <button id="btn-rename-list" class="icon-button info" title="Ubah nama daftar"><i data-lucide="pencil"></i></button>
            <button id="btn-delete-list" class="icon-button danger" title="Hapus daftar ini"><i data-lucide="trash-2"></i></button>
        </section>

        <!-- Favorites quick-add -->
        <section id="favorites-bar" class="favorites-bar hidden">
            <span class="favorites-label"><i data-lucide="star"></i> Sering dibeli:</span>
            <div id="favorites-chips" class="favorites-chips"></div>
        </section>

        <!-- Search -->
        <section id="shopping-search" class="shopping-search">
            <div class="search-inline">
                <i data-lucide="search"></i>
                <input type="text" id="item-search" placeholder="Cari barang di daftar ini...">
                <button id="btn-clear-search" class="search-clear hidden" title="Hapus pencarian">&times;</button>
            </div>
        </section>

        <!-- Filters & Categories -->
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

        <!-- Main Shopping List Content -->
        <main id="list-container" class="content-area">
            <div id="empty-state" class="empty-state hidden">
                <div class="empty-illustration">
                    <i data-lucide="shopping-basket"></i>
                </div>
                <h2>Belum ada daftar</h2>
                <p>Klik tombol + di bawah untuk menambah barang belanjaan Anda.</p>
            </div>
            <div id="shopping-list" class="shopping-list"></div>
        </main>

        <!-- Info Screen: Cek Harga + Resep (Initially Hidden) -->
        <main id="info-screen" class="content-area hidden">
            <div class="info-tabs">
                <button class="info-tab active" data-tab="prices">
                    <i data-lucide="line-chart"></i><span>Cek Harga</span>
                </button>
                <button class="info-tab" data-tab="kurs">
                    <i data-lucide="banknote"></i><span>Kurs</span>
                </button>
                <button class="info-tab" data-tab="recipes">
                    <i data-lucide="utensils"></i><span>Resep</span>
                </button>
            </div>

            <!-- Prices Panel -->
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
                    <div class="loading-state">
                        <div class="spinner"></div>
                        <p>Mengambil data harga...</p>
                    </div>
                </div>
            </div>

            <!-- Kurs Panel -->
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
                    <div class="loading-state">
                        <div class="spinner"></div>
                        <p>Mengambil data kurs...</p>
                    </div>
                </div>
            </div>

            <!-- Recipes Panel -->
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

        <!-- General Notes Screen -->
        <main id="notes-screen" class="content-area hidden">
            <div class="screen-header">
                <h2>Catatan Saya</h2>
                <p class="last-updated">Simpan daftar atau pengingat di sini</p>
            </div>
            <div id="notes-list-container" class="notes-list-container"></div>
        </main>

        <!-- Storage / Item Placement Screen -->
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

        <!-- Pantry / Stok Dapur Screen -->
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

        <!-- History Screen -->
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

        <!-- Reminders Screen -->
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

        <!-- Summary Footer -->
        <footer id="summary-footer" class="summary-footer hidden">
            <div id="budget-bar-wrap" class="budget-bar-wrap hidden">
                <div id="budget-bar" class="budget-bar"></div>
            </div>
            <div class="summary-row">
                <div class="summary-left">
                    <div class="summary-info">
                        <span class="label">Total Estimasi:</span>
                        <span id="total-price" class="value">Rp 0</span>
                    </div>
                    <div id="budget-info" class="budget-info hidden">
                        <span class="label">Sisa Anggaran:</span>
                        <span id="budget-value" class="budget-value">Rp 0</span>
                    </div>
                </div>
                <div class="summary-actions">
                    <button id="btn-print" class="footer-action" title="Cetak / PDF">
                        <i data-lucide="printer"></i>
                    </button>
                    <button id="btn-finish" class="footer-action" title="Selesai & simpan ke riwayat">
                        <i data-lucide="check-check"></i>
                    </button>
                    <button id="btn-share" class="share-button">
                        <i data-lucide="share-2"></i>
                        <span>WA</span>
                    </button>
                </div>
            </div>
        </footer>

        <!-- Bottom Nav for belanja internal screens (hidden, replaced by DuitKu nav) -->
        <nav class="bottom-nav" style="display:none!important">
            <button class="nav-item active" data-screen="list">
                <i data-lucide="shopping-cart"></i><span>Belanja</span>
            </button>
            <button class="nav-item" data-screen="info">
                <i data-lucide="newspaper"></i><span>Info</span>
            </button>
            <button class="nav-item" data-screen="pantry">
                <i data-lucide="refrigerator"></i><span>Dapur</span>
            </button>
            <button class="nav-item" data-screen="notes">
                <i data-lucide="notebook-pen"></i><span>Catatan</span>
            </button>
            <button class="nav-item" data-screen="storage">
                <i data-lucide="map-pin"></i><span>Simpanan</span>
            </button>
        </nav>

        <!-- ===== MODALS ===== -->

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
                    <!-- Navigation shortcuts for belanja sub-screens -->
                    <div class="settings-section">
                        <label><i data-lucide="layout-grid"></i> Layar Belanja</label>
                        <button class="settings-link nav-item nav-item-btn" data-screen="list"><i data-lucide="shopping-cart"></i> Daftar Belanja</button>
                        <button class="settings-link nav-item nav-item-btn" data-screen="info"><i data-lucide="newspaper"></i> Info & Harga</button>
                        <button class="settings-link nav-item nav-item-btn" data-screen="pantry"><i data-lucide="refrigerator"></i> Stok Dapur</button>
                        <button class="settings-link nav-item nav-item-btn" data-screen="notes"><i data-lucide="notebook-pen"></i> Catatan</button>
                        <button class="settings-link nav-item nav-item-btn" data-screen="storage"><i data-lucide="map-pin"></i> Simpanan Barang</button>
                    </div>

                    <!-- Budget -->
                    <div class="settings-section">
                        <label for="setting-budget"><i data-lucide="wallet"></i> Anggaran Belanja (per daftar)</label>
                        <div class="kurs-conv-row">
                            <input type="number" id="setting-budget" placeholder="Misal: 200000" inputmode="numeric">
                            <button id="btn-save-budget" class="btn-primary" style="width:auto; padding:0 18px;">Simpan</button>
                        </div>
                        <small class="settings-hint">Kosongkan lalu simpan untuk menghapus anggaran.</small>
                    </div>

                    <!-- Theme -->
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

                    <!-- Extra Features -->
                    <div class="settings-section">
                        <label><i data-lucide="layout-grid"></i> Fitur Lain</label>
                        <button id="btn-open-parking" class="settings-link"><i data-lucide="circle-parking"></i> Lokasi Parkir</button>
                        <button class="settings-link" data-open="history"><i data-lucide="history"></i> Riwayat Belanja</button>
                        <button class="settings-link" data-open="reminders"><i data-lucide="bell"></i> Pengingat</button>
                    </div>

                    <!-- About -->
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

        <!-- Parking Location Modal -->
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

        <!-- Info / About Modal -->
        <div id="info-modal" class="modal-overlay hidden">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Informasi Aplikasi</h2>
                    <button id="close-info-modal-x" class="close-modal">&times;</button>
                </div>
                <div class="info-body" style="padding:10px 0 20px 0; display:flex; flex-direction:column; align-items:center; text-align:center;">
                    <div class="profile-frame">
                        <img src="/belanja_assets/zusfan.png" alt="Developer Profile" class="profile-img">
                    </div>
                    <p style="margin-bottom:5px;"><strong>Developer:</strong> Zusfan</p>
                    <p><strong>Whatsapp:</strong> <a href="https://wa.me/628998813000" target="_blank" style="color:var(--primary-dark);font-weight:700;text-decoration:none;">08998813000</a></p>
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

                <!-- Discount Tab -->
                <div id="tab-discount" class="calc-tab-content">
                    <div class="calc-body">
                        <div class="form-group">
                            <label>Harga Asli (Rp)</label>
                            <input type="number" id="calc-price" placeholder="Misal: 100000" inputmode="numeric">
                        </div>
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
                            <div class="result-item">
                                <span>Hemat (Potongan):</span>
                                <strong id="res-savings" class="text-success">Rp 0</strong>
                            </div>
                            <div class="result-item large">
                                <span>Harga Jadi (Bayar):</span>
                                <strong id="res-final" class="text-primary">Rp 0</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Standard Tab -->
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

                <!-- Change (Kembalian) Tab -->
                <div id="tab-change" class="calc-tab-content hidden">
                    <div class="calc-body">
                        <div class="form-group">
                            <label>Total Belanja (Rp)</label>
                            <input type="number" id="change-total" placeholder="Misal: 85000" inputmode="numeric">
                            <button type="button" id="btn-use-list-total" class="btn-outline" style="margin-top:8px;">
                                <i data-lucide="clipboard-list"></i><span>Pakai total daftar aktif</span>
                            </button>
                        </div>
                        <div class="form-group">
                            <label>Uang Dibayar (Rp)</label>
                            <input type="number" id="change-paid" placeholder="Misal: 100000" inputmode="numeric">
                        </div>
                        <div id="change-result-box" class="result-box hidden">
                            <div class="result-item large">
                                <span id="change-label">Kembalian:</span>
                                <strong id="change-result" class="text-primary">Rp 0</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Compare Tab -->
                <div id="tab-compare" class="calc-tab-content hidden">
                    <div class="calc-body">
                        <p class="settings-hint" style="margin-bottom:10px;">Bandingkan 2 produk dengan satuan yang sama (mis. gram, ml, buah). Isi harga & jumlahnya.</p>
                        <div class="compare-grid">
                            <div class="compare-col">
                                <strong>Produk A</strong>
                                <input type="number" id="cmp-a-price" placeholder="Harga (Rp)" inputmode="numeric">
                                <input type="number" id="cmp-a-qty" placeholder="Jumlah/isi (mis. 500)" inputmode="decimal">
                            </div>
                            <div class="compare-col">
                                <strong>Produk B</strong>
                                <input type="number" id="cmp-b-price" placeholder="Harga (Rp)" inputmode="numeric">
                                <input type="number" id="cmp-b-qty" placeholder="Jumlah/isi (mis. 1000)" inputmode="decimal">
                            </div>
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
                <div class="install-body" style="text-align:center; padding:10px 0 20px 0;">
                    <div class="install-icon-container">
                        <img src="/belanja_assets/logo.png" alt="App Logo" class="install-app-logo">
                    </div>
                    <h3 style="margin-bottom:10px; color:var(--text-main);">Daftar Belanja Pasar</h3>
                    <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:20px;">
                        Install aplikasi ini di HP Anda agar lebih cepat, mudah dibuka, dan bisa dipakai tanpa internet!
                    </p>
                    <button id="btn-install-now" class="btn-primary" style="background-color:var(--primary); color:white;">
                        <i data-lucide="download" style="width:18px;height:18px;vertical-align:middle;margin-right:5px;"></i>
                        Install Sekarang
                    </button>
                    <button id="btn-later" style="background:none; border:none; color:var(--text-muted); margin-top:15px; font-size:0.85rem; cursor:pointer;">
                        Nanti Saja
                    </button>
                </div>
            </div>
        </div>

    </div><!-- #app -->

    <!-- Recipe Detail Modal (outside #app to avoid z-index issues) -->
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

</div><!-- .belanja-wrapper -->

<!-- Floating Add Button (positioned above DuitKu bottom nav) -->
<div class="belanja-fab-wrapper">
    <button id="fab-add" class="fab" title="Tambah Barang">
        <i data-lucide="plus"></i>
    </button>
</div>

<!-- Print area -->
<div id="print-area"></div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Lucide Icons (needed for belanja JS) -->
<script src="https://unpkg.com/lucide@latest"></script>
<script src="/belanja_assets/js/app.js?v=24"></script>
<script>
    // Initialize Lucide Icons for belanja elements
    lucide.createIcons();

    // Unregister any old belanja service workers that conflict with DuitKu's SW
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            for (let reg of registrations) {
                if (reg.scope && reg.scope.includes('belanja')) {
                    reg.unregister();
                }
            }
        });
    }
</script>
<?= $this->endSection() ?>
