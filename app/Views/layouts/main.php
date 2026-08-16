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
    <style>
    .topbar-btn-notif {
        position: relative;
        background: var(--bg);
        border: 1px solid var(--border);
        color: var(--text-primary);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: transform 0.15s ease, background 0.15s ease, border-color 0.15s ease;
    }
    .topbar-btn-notif:hover {
        background: var(--border-light);
        border-color: var(--primary);
    }
    .topbar-btn-notif:active { transform: scale(0.92); }
    .notif-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background: #DC2626;
        color: #fff;
        font-size: 9.5px;
        font-weight: 900;
        min-width: 17px;
        height: 17px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        border: 2px solid var(--bg-card);
        line-height: 1;
    }
    .notif-item-card {
        padding: 12px 14px;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .notif-item-card.urgent { border-color: #FCA5A5; background: #FEF2F2; }
    .notif-item-card.warning { border-color: #FDE68A; background: #FFFBEB; }
    [data-theme="dark"] .notif-item-card.urgent { background: rgba(220, 38, 38, 0.12); border-color: #DC2626; }
    [data-theme="dark"] .notif-item-card.warning { background: rgba(217, 119, 6, 0.12); border-color: #D97706; }
    </style>
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
                $avatar     = $avatarJson ? json_decode($avatarJson, true) : null;
                $avatar     = (is_array($avatar) && !empty($avatar['initials'])) ? $avatar : ['initials' => 'U', 'color' => '#2D5A27'];
                $avatarImg  = null;
                $_layoutWallets = [];
                $_layoutNotifs = [];
                if ($userId) {
                    try {
                        $settingModel = new \App\Models\SettingModel();
                        $avatarImgFile = $settingModel->get($userId, 'avatar_image');
                        if ($avatarImgFile && file_exists(FCPATH . 'uploads/avatars/' . $avatarImgFile)) {
                            $avatarImg = '/uploads/avatars/' . $avatarImgFile;
                        }
                    } catch (\Throwable $e) {}

                    // Load wallet list for transaction modal (available on every page)
                    if (!isset($wallets)) {
                        try {
                            $wm = new \App\Models\WalletModel();
                            $wd = $wm->getWithBalances($userId);
                            $_layoutWallets = $wd['wallets'] ?? [];
                        } catch (\Throwable $e) {
                            $_layoutWallets = [];
                        }
                    } else {
                        $_layoutWallets = $wallets;
                    }

                    // Compute notifications if not already provided
                    if (!isset($notifications)) {
                        try {
                            $bm = new \App\Models\SettingModel();
                            $dm = new \App\Models\DebtModel();
                            $vm = new \App\Models\VehicleModel();
                            
                            $billsRaw = $bm->get($userId, 'bills', '[]');
                            $billsAll = json_decode($billsRaw, true) ?: [];
                            $todayDay = (int)date('j');
                            $todayDate = date('Y-m-d');
                            $_layoutNotifs = [];
                            foreach ($billsAll as $b) {
                                $dueDay = (int)($b['dueDay'] ?? 0);
                                $daysLeft = $dueDay - $todayDay;
                                if ($daysLeft >= -3 && $daysLeft <= 3) {
                                    $_layoutNotifs[] = [
                                        'id' => 'b_' . ($b['id'] ?? uniqid()),
                                        'type' => 'bill',
                                        'title' => $b['name'] ?? 'Tagihan',
                                        'subtitle' => ($daysLeft <= 0 ? ($daysLeft == 0 ? 'Jatuh tempo Hari Ini!' : 'Lewat ' . abs($daysLeft) . ' hari') : 'Jatuh tempo ' . $daysLeft . ' hari lagi'),
                                        'amount' => (float)($b['amount'] ?? 0),
                                        'days_left' => $daysLeft,
                                        'icon' => '📋',
                                        'action_url' => '/bills',
                                    ];
                                }
                            }
                            $debts = $dm->getUpcoming($userId, 7);
                            foreach ($debts as $d) {
                                $daysLeft = (int)floor((strtotime($d['due_date']) - strtotime($todayDate)) / 86400);
                                $_layoutNotifs[] = [
                                    'id' => 'd_' . $d['id'],
                                    'type' => 'debt',
                                    'title' => ($d['type'] === 'hutang' ? 'Bayar Hutang: ' : 'Tagih Piutang: ') . ($d['person'] ?? ''),
                                    'subtitle' => ($daysLeft <= 0 ? ($daysLeft == 0 ? 'Jatuh tempo Hari Ini!' : 'Lewat ' . abs($daysLeft) . ' hari') : 'Jatuh tempo ' . $daysLeft . ' hari lagi'),
                                    'amount' => (float)($d['amount'] ?? 0),
                                    'days_left' => $daysLeft,
                                    'icon' => $d['type'] === 'hutang' ? '💸' : '💰',
                                    'action_url' => '/hutang',
                                ];
                            }
                            $taxes = $vm->getUpcomingTaxes($userId, 30);
                            foreach ($taxes as $t) {
                                $_layoutNotifs[] = [
                                    'id' => 't_' . $t['vehicle_id'] . '_' . md5($t['type']),
                                    'type' => 'tax',
                                    'title' => $t['type'] . ' · ' . ($t['vehicle_name'] ?? 'Kendaraan'),
                                    'subtitle' => ($t['days_left'] <= 0 ? ($t['days_left'] == 0 ? 'Jatuh tempo Hari Ini!' : 'Lewat ' . abs($t['days_left']) . ' hari') : 'Jatuh tempo ' . $t['days_left'] . ' hari lagi'),
                                    'amount' => 0,
                                    'days_left' => $t['days_left'],
                                    'icon' => '🚗',
                                    'action_url' => '/kendaraan/' . $t['vehicle_id'],
                                ];
                            }
                            usort($_layoutNotifs, fn($a, $b) => $a['days_left'] <=> $b['days_left']);
                        } catch (\Throwable $e) {
                            $_layoutNotifs = [];
                        }
                    } else {
                        $_layoutNotifs = $notifications;
                    }
                } else {
                    $_layoutNotifs = [];
                }
            ?>
            <button class="topbar-btn-notif" id="btnOpenSearch" title="Cari Data (Ctrl+K)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="17" height="17">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
            <button class="topbar-btn-notif" id="btnOpenNotif" title="Notifikasi Pengingat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="17" height="17">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <?php if (!empty($_layoutNotifs)): ?>
                    <span class="notif-badge"><?= count($_layoutNotifs) ?></span>
                <?php endif; ?>
            </button>
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

<!-- ════════════════════════════ NOTIFICATION SHEET MODAL -->
<div class="modal-overlay" id="notifModalOverlay">
    <div class="modal-sheet" id="notifModal" style="max-height:85vh">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:8px">
                <h3 style="margin:0;font-size:16px;font-weight:800">🔔 Notifikasi & Pengingat</h3>
                <?php if (!empty($_layoutNotifs)): ?>
                <span style="font-size:11px;font-weight:800;background:#FEE2E2;color:#DC2626;padding:2px 8px;border-radius:10px">
                    <?= count($_layoutNotifs) ?>
                </span>
                <?php endif; ?>
            </div>
            <button class="modal-close" id="notifModalClose">✕</button>
        </div>

        <div style="padding:14px 16px 30px;overflow-y:auto;display:flex;flex-direction:column;gap:12px">
            <!-- Web Notification permission prompt -->
            <div id="webNotifPrompt" style="display:none;background:var(--primary-dim);border:1px solid var(--primary);border-radius:14px;padding:12px;display:flex;align-items:center;justify-content:space-between;gap:10px">
                <div style="font-size:12px;color:var(--text-primary)">
                    <strong>🔔 Aktifkan Notifikasi Pop-up Browser</strong><br>
                    <span style="font-size:11px;color:var(--text-muted)">Dapatkan pop-up saat tagihan mendekati jatuh tempo.</span>
                </div>
                <button id="btnEnableWebNotif" style="background:var(--primary);color:#fff;border:none;padding:6px 12px;border-radius:8px;font-size:11.5px;font-weight:700;cursor:pointer;white-space:nowrap">
                    Aktifkan
                </button>
            </div>

            <?php if (empty($_layoutNotifs)): ?>
            <div style="text-align:center;padding:40px 20px;color:var(--text-muted)">
                <div style="font-size:44px;margin-bottom:10px">🎉</div>
                <div style="font-weight:800;font-size:15px;color:var(--text-primary);margin-bottom:4px">Semua Aman Terkendali!</div>
                <div style="font-size:12px">Tidak ada tagihan, hutang, atau pajak yang jatuh tempo dalam waktu dekat.</div>
            </div>
            <?php else: ?>
                <?php foreach ($_layoutNotifs as $item):
                    $daysLeft = (int)$item['days_left'];
                    $cardClass = $daysLeft <= 0 ? 'urgent' : ($daysLeft <= 2 ? 'warning' : '');
                    $badgeColor = $daysLeft <= 0 ? '#DC2626' : ($daysLeft <= 2 ? '#D97706' : 'var(--text-muted)');
                ?>
                <div class="notif-item-card <?= $cardClass ?>">
                    <div style="display:flex;align-items:flex-start;gap:10px;flex:1;min-width:0">
                        <div style="font-size:24px;flex-shrink:0"><?= $item['icon'] ?></div>
                        <div style="min-width:0">
                            <div style="font-size:13.5px;font-weight:800;color:var(--text-primary);margin-bottom:2px"><?= esc($item['title']) ?></div>
                            <div style="font-size:11.5px;font-weight:700;color:<?= $badgeColor ?>">
                                <?= esc($item['subtitle']) ?>
                            </div>
                            <?php if ($item['amount'] > 0): ?>
                            <div style="font-size:12px;font-weight:800;color:var(--text-primary);margin-top:2px">
                                <?= esc($symbol ?? 'Rp') ?> <?= number_format($item['amount'], 0, ',', '.') ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="<?= esc($item['action_url']) ?>" style="background:var(--primary);color:#fff;text-decoration:none;padding:6px 12px;border-radius:8px;font-size:11.5px;font-weight:700;white-space:nowrap">
                        <?= $item['type'] === 'bill' || $item['type'] === 'debt' ? 'Bayar' : 'Buka' ?>
                    </a>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════ UNIVERSAL SEARCH MODAL -->
<div class="modal-sheet-overlay" id="searchModalOverlay">
    <div class="modal-sheet" style="max-height:85vh;display:flex;flex-direction:column">
        <div class="modal-sheet-handle"></div>
        <div style="padding:16px 20px 10px;border-bottom:1px solid var(--border)">
            <div style="position:relative">
                <input type="text" id="globalSearchInput" placeholder="Cari transaksi, produk POS, barang, kendaraan, kasbon..." 
                       style="width:100%;padding:12px 14px 12px 38px;border-radius:14px;border:1.5px solid var(--border);font-size:14px;background:var(--bg);color:var(--text-primary);outline:none">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"
                     style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted)">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <button id="searchModalClose" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);font-size:16px;color:var(--text-muted);padding:4px">✕</button>
            </div>
        </div>
        <div id="globalSearchResults" style="flex:1;overflow-y:auto;padding:14px 20px;display:flex;flex-direction:column;gap:14px">
            <div style="text-align:center;padding:30px 10px;color:var(--text-muted)">
                <div style="font-size:32px;margin-bottom:8px">🔍</div>
                <div style="font-size:13px">Ketik kata kunci untuk mencari seluruh data.</div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ SCRIPTS -->
<script>
    window.DUITKU = {
        categories:    <?= json_encode($categories ?? []) ?>,
        wallets:       <?= json_encode($_layoutWallets ?? []) ?>,
        notifications: <?= json_encode($_layoutNotifs ?? []) ?>,
        symbol:        '<?= esc($symbol ?? 'Rp') ?>',
        csrfToken:     '<?= csrf_hash() ?>',
        csrfName:      '<?= csrf_token() ?>',
    };

    document.addEventListener('DOMContentLoaded', () => {
        // Notif Sheet
        const notifOverlay = document.getElementById('notifModalOverlay');
        document.getElementById('btnOpenNotif')?.addEventListener('click', () => {
            notifOverlay?.classList.add('open');
        });
        document.getElementById('notifModalClose')?.addEventListener('click', () => {
            notifOverlay?.classList.remove('open');
        });
        notifOverlay?.addEventListener('click', (e) => {
            if (e.target === notifOverlay) notifOverlay.classList.remove('open');
        });

        // Universal Search Modal
        const searchOverlay = document.getElementById('searchModalOverlay');
        const searchInput   = document.getElementById('globalSearchInput');
        const searchResults = document.getElementById('globalSearchResults');

        function openGlobalSearch() {
            searchOverlay?.classList.add('open');
            setTimeout(() => searchInput?.focus(), 150);
        }

        document.getElementById('btnOpenSearch')?.addEventListener('click', openGlobalSearch);
        document.getElementById('searchModalClose')?.addEventListener('click', () => searchOverlay?.classList.remove('open'));
        searchOverlay?.addEventListener('click', (e) => {
            if (e.target === searchOverlay) searchOverlay.classList.remove('open');
        });

        // Shortcut Ctrl+K / Cmd+K
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                openGlobalSearch();
            }
        });

        let searchDebounce = null;
        searchInput?.addEventListener('input', () => {
            clearTimeout(searchDebounce);
            const q = searchInput.value.trim();
            if (q.length < 2) {
                searchResults.innerHTML = `
                    <div style="text-align:center;padding:30px 10px;color:var(--text-muted)">
                        <div style="font-size:32px;margin-bottom:8px">🔍</div>
                        <div style="font-size:13px">Ketik minimal 2 karakter untuk mencari.</div>
                    </div>`;
                return;
            }

            searchResults.innerHTML = '<div style="text-align:center;padding:24px;color:var(--text-muted)">Mencari data...</div>';

            searchDebounce = setTimeout(async () => {
                try {
                    const res = await fetch('/search?q=' + encodeURIComponent(q), { credentials: 'same-origin' });
                    const data = await res.json();
                    renderSearchResults(data);
                } catch (e) {
                    searchResults.innerHTML = '<div style="text-align:center;padding:24px;color:var(--expense)">Gagal memuat pencarian.</div>';
                }
            }, 300);
        });

        function renderSearchResults(data) {
            const { results, total } = data;
            if (total === 0) {
                searchResults.innerHTML = `
                    <div style="text-align:center;padding:30px 10px;color:var(--text-muted)">
                        <div style="font-size:32px;margin-bottom:8px">❌</div>
                        <div style="font-size:13px;font-weight:700">Tidak ada data yang cocok.</div>
                    </div>`;
                return;
            }

            let html = '';
            const sym = window.DUITKU.symbol;

            // 1. Transactions
            if (results.transactions?.length > 0) {
                html += `<div>
                    <div style="font-size:11px;font-weight:800;color:var(--text-muted);letter-spacing:0.5px;margin-bottom:8px">TRANSAKSI (${results.transactions.length})</div>
                    <div style="display:flex;flex-direction:column;gap:6px">`;
                results.transactions.forEach(t => {
                    const isIncome = t.type === 'income';
                    const color = isIncome ? '#16A34A' : '#DC2626';
                    html += `
                        <div style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:10px 12px;display:flex;align-items:center;justify-content:space-between">
                            <div>
                                <div style="font-size:13px;font-weight:700;color:var(--text-primary)">${t.description || t.category_name || 'Transaksi'}</div>
                                <div style="font-size:11px;color:var(--text-muted)">${t.date} · ${t.wallet_name || 'Dompet'}</div>
                            </div>
                            <div style="font-size:13px;font-weight:800;color:${color}">${isIncome ? '+' : '-'}${sym} ${Number(t.amount).toLocaleString('id-ID')}</div>
                        </div>`;
                });
                html += `</div></div>`;
            }

            // 2. POS Products
            if (results.pos_products?.length > 0) {
                html += `<div>
                    <div style="font-size:11px;font-weight:800;color:var(--text-muted);letter-spacing:0.5px;margin-bottom:8px">PRODUK POS (${results.pos_products.length})</div>
                    <div style="display:flex;flex-direction:column;gap:6px">`;
                results.pos_products.forEach(p => {
                    html += `
                        <a href="/pos/products" style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:10px 12px;display:flex;align-items:center;justify-content:space-between;text-decoration:none">
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="font-size:20px">📦</div>
                                <div>
                                    <div style="font-size:13px;font-weight:700;color:var(--text-primary)">${p.name}</div>
                                    <div style="font-size:11px;color:var(--text-muted)">${p.category || 'Menu'} · Stok: <strong>${p.stock}</strong> ${p.unit || 'pcs'}</div>
                                </div>
                            </div>
                            <div style="font-size:13px;font-weight:800;color:#EA580C">${sym} ${Number(p.selling_price).toLocaleString('id-ID')}</div>
                        </a>`;
                });
                html += `</div></div>`;
            }

            // 3. Debts
            if (results.debts?.length > 0) {
                html += `<div>
                    <div style="font-size:11px;font-weight:800;color:var(--text-muted);letter-spacing:0.5px;margin-bottom:8px">HUTANG & KASBON (${results.debts.length})</div>
                    <div style="display:flex;flex-direction:column;gap:6px">`;
                results.debts.forEach(d => {
                    html += `
                        <a href="/hutang" style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:10px 12px;display:flex;align-items:center;justify-content:space-between;text-decoration:none">
                            <div>
                                <div style="font-size:13px;font-weight:700;color:var(--text-primary)">${d.type === 'hutang' ? '🔴 Hutang ke: ' : '🟢 Kasbon: '}${d.person}</div>
                                <div style="font-size:11px;color:var(--text-muted)">Tempo: ${d.due_date || '-'} · ${d.is_settled == 1 ? 'Lunas' : 'Belum Lunas'}</div>
                            </div>
                            <div style="font-size:13px;font-weight:800;color:var(--text-primary)">${sym} ${Number(d.amount).toLocaleString('id-ID')}</div>
                        </a>`;
                });
                html += `</div></div>`;
            }

            // 4. Vehicles
            if (results.vehicles?.length > 0) {
                html += `<div>
                    <div style="font-size:11px;font-weight:800;color:var(--text-muted);letter-spacing:0.5px;margin-bottom:8px">KENDARAAN (${results.vehicles.length})</div>
                    <div style="display:flex;flex-direction:column;gap:6px">`;
                results.vehicles.forEach(v => {
                    html += `
                        <a href="/kendaraan/${v.id}" style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:10px 12px;display:flex;align-items:center;justify-content:space-between;text-decoration:none">
                            <div>
                                <div style="font-size:13px;font-weight:700;color:var(--text-primary)">🚗 ${v.name}</div>
                                <div style="font-size:11px;color:var(--text-muted)">${v.plate_number} · ${v.odometer_km || 0} KM</div>
                            </div>
                            <span style="font-size:11.5px;color:var(--primary);font-weight:700">Lihat →</span>
                        </a>`;
                });
                html += `</div></div>`;
            }

            // 5. Barang
            if (results.barang?.length > 0) {
                html += `<div>
                    <div style="font-size:11px;font-weight:800;color:var(--text-muted);letter-spacing:0.5px;margin-bottom:8px">ASET BARANG (${results.barang.length})</div>
                    <div style="display:flex;flex-direction:column;gap:6px">`;
                results.barang.forEach(b => {
                    html += `
                        <a href="/barang" style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:10px 12px;display:flex;align-items:center;justify-content:space-between;text-decoration:none">
                            <div>
                                <div style="font-size:13px;font-weight:700;color:var(--text-primary)">📍 ${b.nama || b.name}</div>
                                <div style="font-size:11px;color:var(--text-muted)">Lokasi: ${b.lokasi || b.location || 'Rumah'}</div>
                            </div>
                            <span style="font-size:11.5px;color:var(--primary);font-weight:700">Buka →</span>
                        </a>`;
                });
                html += `</div></div>`;
            }

            searchResults.innerHTML = html;
        }

        // Browser Web Notifications
        const promptEl = document.getElementById('webNotifPrompt');
        if ('Notification' in window) {
            if (Notification.permission === 'default' && promptEl) {
                promptEl.style.display = 'flex';
            }
            document.getElementById('btnEnableWebNotif')?.addEventListener('click', async () => {
                const perm = await Notification.requestPermission();
                if (perm === 'granted') {
                    if (promptEl) promptEl.style.display = 'none';
                    new Notification('DuitKu Notifikasi Aktif! 🔔', {
                        body: 'Pengingat tagihan dan jatuh tempo akan otomatis muncul.',
                        icon: '/images/logo.png'
                    });
                }
            });

            // Automatic push reminder if granted and urgent
            if (Notification.permission === 'granted' && window.DUITKU.notifications) {
                const urgent = window.DUITKU.notifications.filter(n => Number(n.days_left) <= 1);
                if (urgent.length > 0 && !sessionStorage.getItem('duitku_notif_fired')) {
                    sessionStorage.setItem('duitku_notif_fired', '1');
                    urgent.slice(0, 2).forEach(n => {
                        new Notification(`⏰ ${n.title}`, {
                            body: `${n.subtitle}` + (n.amount > 0 ? ` (${window.DUITKU.symbol} ${Number(n.amount).toLocaleString('id-ID')})` : ''),
                            icon: '/images/logo.png'
                        });
                    });
                }
            }
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="/js/app.js?v=<?= time() ?>"></script>
<?= $this->renderSection('scripts') ?>

</body>
</html>
