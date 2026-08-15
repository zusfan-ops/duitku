<!DOCTYPE html>
<html lang="id" data-theme="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="DuitKu — Aplikasi pencatat keuangan pribadi yang simpel dan cerdas.">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="DuitKu">
    <meta name="mobile-web-app-capable" content="yes">
    <title><?= esc($pageTitle ?? 'DuitKu') ?> — DuitKu</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/images/logo.png">
    <link rel="apple-touch-icon" href="/images/apple-touch-icon.png">
    <link rel="apple-touch-startup-image" href="/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/app.css?v=<?= time() ?>">
    <script>
        // Apply dark mode before render to avoid flash
        (function() {
            if (localStorage.getItem('duitku_dark') === '1') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <?= $this->renderSection('styles') ?>
</head>
<body>

<!-- PWA Install Prompt (Hidden by default) -->
<div class="pwa-install-banner" id="pwaInstallBanner">
    <div class="pwa-banner-content">
        <img src="/images/logo.png" alt="App Icon" class="pwa-icon">
        <div class="pwa-text">
            <strong>Install DuitKu</strong>
            <span>Akses lebih cepat & offline.</span>
        </div>
        <button class="btn-install" id="btnInstallPwa">Install</button>
        <button class="btn-close-pwa" id="btnClosePwa">✕</button>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ APP SHELL -->
<div id="app">

    <!-- TOP BAR -->
    <header class="topbar">
        <div class="topbar-brand">
            <img src="/images/logo.png" alt="DuitKu" class="topbar-logo" style="object-fit:contain">
        </div>
        <div class="topbar-actions">
            <?php
                $userId     = session()->get('user_id');
                $avatarJson = session()->get('user_avatar');
                $avatar     = $avatarJson ? json_decode($avatarJson, true) : ['initials' => 'U', 'color' => '#2D5A27'];
                $avatarImg  = null;
                $_layoutWallets = [];
                if ($userId) {
                    $settingModel = new \App\Models\SettingModel();
                    $avatarImgFile = $settingModel->get($userId, 'avatar_image');
                    if ($avatarImgFile && file_exists(FCPATH . 'uploads/avatars/' . $avatarImgFile)) {
                        $avatarImg = '/uploads/avatars/' . $avatarImgFile;
                    }
                    // Load wallet list for transaction modal (available on every page)
                    if (!isset($wallets)) {
                        $wm = new \App\Models\WalletModel();
                        $wd = $wm->getWithBalances($userId);
                        $_layoutWallets = $wd['wallets'];
                    } else {
                        $_layoutWallets = $wallets;
                    }
                }
            ?>
            <div class="user-avatar" id="userMenuToggle" title="<?= esc(session()->get('user_name')) ?>">
                <?php if ($avatarImg): ?>
                    <img src="<?= esc($avatarImg) ?>?v=<?= time() ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
                <?php else: ?>
                    <span style="background:<?= esc($avatar['color'] ?? '#2D5A27') ?>"><?= esc($avatar['initials'] ?? 'U') ?></span>
                <?php endif; ?>
            </div>
            <!-- Dropdown menu -->
            <div class="user-menu" id="userMenu">
                <div class="user-menu-info">
                    <strong><?= esc(session()->get('user_name')) ?></strong>
                    <small><?= esc(session()->get('user_email')) ?></small>
                </div>
                <hr>
                <a href="/settings" class="user-menu-item">⚙️ Pengaturan</a>
                <a href="/logout" class="user-menu-item logout">🚪 Keluar</a>
            </div>
        </div>
    </header>

    <!-- PAGE CONTENT -->
    <main class="page-content" id="pageContent">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="toast toast-success" id="flashToast">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>
        <?= $this->renderSection('content') ?>
    </main>

    <!-- BOTTOM NAVIGATION (Native-aligned 4 tabs + center FAB) -->
    <?php
        $currentPath = current_url(true)->getPath();
        $isHome      = ($currentPath === '/' || $currentPath === '');
        $isActivity  = str_starts_with($currentPath, '/activity');
        $isFeatures  = (
            str_starts_with($currentPath, '/features') ||
            str_starts_with($currentPath, '/fitur') ||
            str_starts_with($currentPath, '/stats') ||
            str_starts_with($currentPath, '/bills') ||
            str_starts_with($currentPath, '/hutang') ||
            str_starts_with($currentPath, '/wallets') ||
            str_starts_with($currentPath, '/belanja') ||
            str_starts_with($currentPath, '/traveling') ||
            str_starts_with($currentPath, '/barang')
        );
        $isSettings  = str_starts_with($currentPath, '/settings');
    ?>
    <nav class="bottom-nav" id="bottomNav">
        <a href="/" class="bottom-nav-item <?= $isHome ? 'active' : '' ?>" id="nav-home">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <span>Beranda</span>
        </a>
        <a href="/activity" class="bottom-nav-item <?= $isActivity ? 'active' : '' ?>" id="nav-activity">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            <span>Aktivitas</span>
        </a>
        
        <!-- Center Docked FAB Slot -->
        <div class="bottom-nav-fab-slot">
            <button class="fab" id="fabBtn" title="Tambah Transaksi" aria-label="Tambah Transaksi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
            </button>
        </div>

        <a href="/features" class="bottom-nav-item <?= $isFeatures ? 'active' : '' ?>" id="nav-features">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
            </svg>
            <span>Fitur</span>
        </a>
        <a href="/settings" class="bottom-nav-item <?= $isSettings ? 'active' : '' ?>" id="nav-settings">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span>Akun</span>
        </a>
    </nav>

</div><!-- #app -->

<!-- ═══════════════════════════════════════════════════ TRANSACTION MODAL -->
<div class="modal-overlay" id="txModalOverlay">
    <div class="modal-sheet" id="txModal">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3 id="modalTitle">Transaksi Baru</h3>
            <button class="modal-close" id="modalClose">✕</button>
        </div>

        <form id="txForm" autocomplete="off">
            <input type="hidden" id="txId" name="tx_id">

            <!-- Type Toggle -->
            <div class="type-toggle">
                <button type="button" class="type-btn active" data-type="expense" id="btnExpense">Pengeluaran</button>
                <button type="button" class="type-btn" data-type="income" id="btnIncome">Pemasukan</button>
            </div>
            <input type="hidden" id="txType" name="type" value="expense">

            <!-- Amount -->
            <div class="amount-input-wrap">
                <span class="amount-currency" id="amountCurrency">Rp</span>
                <input type="text" id="txAmount" name="amount" placeholder="0" class="amount-input" inputmode="numeric" autocomplete="off">
            </div>

            <!-- Category -->
            <div class="form-group">
                <label class="form-label">KATEGORI</label>
                <div class="category-chips" id="categoryChips"></div>
                <input type="hidden" id="txCategory" name="category_id">
            </div>

            <!-- Wallet Picker -->
            <div class="form-group" id="walletPickerRow">
                <label class="form-label">REKENING</label>
                <select id="txWallet" class="form-input">
                    <option value="">— Pilih rekening —</option>
                </select>
            </div>

            <!-- Bill Picker (expense only) -->
            <div class="form-group" id="billPickerRow" style="display:none">
                <label class="form-label">BAYAR TAGIHAN (OPSIONAL)</label>
                <select id="billPicker" class="form-input" style="color:var(--text-secondary)">
                    <option value="">— Pilih tagihan —</option>
                </select>
            </div>

            <!-- Date -->
            <div class="form-group">
                <label class="form-label" for="txDate">TANGGAL</label>
                <input type="date" id="txDate" name="date" class="form-input" value="<?= date('Y-m-d') ?>">
            </div>

            <!-- Note -->
            <div class="form-group">
                <label class="form-label" for="txNote">CATATAN (OPSIONAL)</label>
                <input type="text" id="txNote" name="note" placeholder="Tambahkan catatan..." class="form-input">
            </div>

            <!-- Image/Photo -->
            <div class="form-group">
                <label class="form-label" for="txImage">FOTO / BUKTI (OPSIONAL)</label>
                <input type="file" id="txImage" name="image" class="form-input" accept="image/*" capture="environment" style="padding:8px">
                <div id="txImagePreviewContainer" style="display:none; margin-top:10px; position:relative; width:fit-content;">
                    <img id="txImagePreview" src="" alt="Preview" style="max-width:100%; max-height:120px; border-radius:8px; border:1px solid #E2E8F0;">
                    <button type="button" id="btnRemoveImage" style="position:absolute; top:-8px; right:-8px; background:#DC2626; color:white; border-radius:50%; width:24px; height:24px; font-size:12px; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 4px rgba(0,0,0,.2)">✕</button>
                </div>
            </div>

            <!-- Recurring Toggle -->
            <div class="recurring-toggle-wrap" id="recurringWrap">
                <div class="recurring-toggle-label">
                    <span>🔁 Ulangi Setiap Bulan</span>
                    <small>Otomatis dicatat tiap bulan berikutnya</small>
                </div>
                <div class="toggle-switch" id="recurringToggle"></div>
                <input type="hidden" id="txRecurring" name="is_recurring" value="0">
            </div>

            <button type="submit" class="btn-save" id="btnSave">Simpan Pengeluaran</button>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ SCRIPTS -->
<script>
    window.DUITKU = {
        categories: <?= json_encode($categories ?? []) ?>,
        wallets:    <?= json_encode($_layoutWallets ?? []) ?>,
        symbol: '<?= esc($symbol ?? 'Rp') ?>',
        csrfToken: '<?= csrf_hash() ?>',
        csrfName: '<?= csrf_token() ?>',
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="/js/app.js?v=<?= time() ?>"></script>
<?= $this->renderSection('scripts') ?>

</body>
</html>
