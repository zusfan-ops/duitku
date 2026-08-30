<!DOCTYPE html>
<html lang="id" data-theme="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Admin Panel') ?> — DuitKu Admin</title>
    <link rel="icon" type="image/png" href="/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/app.css?v=<?= time() ?>">
    <!-- HLS.js for stream previews -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <style>
        :root {
            --admin-sidebar-w: 78px;
            --admin-primary: #10B981;
            --admin-primary-dark: #059669;
            --admin-primary-light: #D1FAE5;
            --admin-primary-bg: #ECFDF5;
            --admin-bg: #F8FAFC;
            --admin-card: #FFFFFF;
            --admin-sidebar: #0F172A;
            --admin-sidebar-text: #94A3B8;
            --admin-sidebar-active: #1E293B;
            --admin-border: #E2E8F0;
            --admin-text: #0F172A;
            --admin-text-secondary: #64748B;
        }
        [data-theme="dark"] {
            --admin-bg: #0B1120;
            --admin-card: #1E293B;
            --admin-sidebar: #020617;
            --admin-border: #334155;
            --admin-text: #F8FAFC;
            --admin-text-secondary: #94A3B8;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--admin-bg);
            color: var(--admin-text);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* ── Sidebar Compact Rail (Vitafarma exact style) ─────────────── */
        .admin-sidebar {
            width: 78px;
            background: linear-gradient(180deg, #123b6d 0%, #0b2447 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            border-right: 1px solid rgba(255,255,255,0.08);
            transition: width 0.22s ease;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 30;
            box-shadow: 2px 0 10px rgba(15,23,42,0.15);
        }
        .admin-sidebar.collapsed {
            width: 60px;
        }
        .admin-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 16px 0;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            text-decoration: none;
        }
        .admin-brand img {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
        }
        .admin-brand-text {
            display: block;
        }
        .admin-brand-text .name {
            font-size: 10px;
            font-weight: 800;
            color: #e2e8f0;
            letter-spacing: 0.2px;
            text-align: center;
        }
        .admin-nav {
            flex: 1;
            overflow-y: auto;
            padding: 14px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            scrollbar-width: none;
        }
        .admin-nav::-webkit-scrollbar { display: none; }
        
        .rail-btn {
            position: relative;
            width: 52px;
            height: 52px;
            padding: 0;
            border-radius: 14px;
            border: none;
            background: transparent;
            color: #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.15s;
            cursor: pointer;
            text-decoration: none;
        }
        .rail-btn .rail-label { display: none; }
        .rail-btn .rail-icon {
            font-size: 24px;
            line-height: 1;
            width: 42px;
            height: 42px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            transition: transform 0.15s ease, background 0.15s ease;
        }
        .rail-btn:hover .rail-icon {
            background: rgba(255,255,255,0.08);
            transform: scale(1.05);
        }
        .rail-btn.active .rail-icon {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-dark));
            box-shadow: 0 6px 14px rgba(16,185,129,0.45);
            color: #fff;
        }
        .admin-sidebar-footer {
            padding: 12px 6px;
            border-top: 1px solid rgba(255,255,255,0.08);
            font-size: 9px;
            color: #64748b;
            text-align: center;
            line-height: 1.5;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }
        .admin-user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10B981, #059669);
            color: #fff;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            cursor: pointer;
        }

        /* ── Rail Tooltip ──────────────────────────────────────────── */
        .rail-tooltip {
            position: fixed;
            background: #0f172a;
            color: #fff;
            font-size: 11.5px;
            font-weight: 700;
            padding: 6px 10px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            pointer-events: none;
            z-index: 1000;
            opacity: 0;
            transform: translateY(-50%) translateX(-6px);
            transition: opacity 0.15s ease, transform 0.15s ease;
            white-space: nowrap;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .rail-tooltip.show {
            opacity: 1;
            transform: translateY(-50%) translateX(0);
        }

        /* ── Rail Flyout (Vitafarma Exact Style) ────────────────────── */
        .rail-flyout-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.35);
            z-index: 90;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.18s ease;
        }
        .rail-flyout-backdrop.open {
            opacity: 1;
            visibility: visible;
        }
        .rail-flyout {
            position: fixed;
            top: 0;
            height: max-content;
            max-height: 100vh;
            width: max-content;
            min-width: 260px;
            max-width: calc(100vw - 90px);
            background: var(--admin-card);
            z-index: 91;
            display: flex;
            flex-direction: column;
            box-shadow: 0 24px 48px -12px rgba(2,20,46,0.35), 6px 0 26px rgba(15,23,42,0.18);
            transform: translateX(-10px);
            opacity: 0;
            visibility: hidden;
            transition: transform 0.18s ease, opacity 0.18s ease;
            border-bottom-right-radius: 20px;
            border: 1px solid var(--admin-border);
        }
        .rail-flyout.open {
            transform: translateX(0);
            opacity: 1;
            visibility: visible;
        }
        .rail-flyout-panel {
            display: none;
            flex-direction: column;
            width: max-content;
            min-width: 260px;
            max-width: 100%;
            max-height: 100%;
            overflow-y: auto;
            padding: 20px 20px 24px;
            box-sizing: border-box;
        }
        .rail-flyout-panel.open {
            display: flex;
        }
        .rail-flyout-head h2 {
            font-size: 17px;
            font-weight: 800;
            color: var(--admin-text);
            letter-spacing: -0.2px;
            margin: 0;
        }
        .rail-flyout-rule {
            height: 3px;
            width: 100%;
            border-radius: 3px;
            background: linear-gradient(90deg, var(--admin-primary), #059669);
            margin: 8px 0 16px;
            opacity: 0.8;
        }
        .rail-flyout-grid {
            display: grid;
            gap: 10px;
        }
        .rail-flyout-grid.cols-1 { grid-template-columns: repeat(1, 120px); }
        .rail-flyout-grid.cols-2 { grid-template-columns: repeat(2, 120px); }
        .rail-flyout-grid.cols-3 { grid-template-columns: repeat(3, 120px); }
        
        .rail-flyout .nav-item {
            width: 120px;
            height: 100px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 8px;
            padding: 10px 8px;
            border: 1px solid var(--admin-border);
            border-radius: 14px;
            background: var(--admin-card);
            color: var(--admin-text-secondary);
            white-space: normal;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.1s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        }
        .rail-flyout .nav-item:hover {
            border-color: var(--admin-primary);
            color: var(--admin-primary-dark);
            box-shadow: 0 8px 18px -6px rgba(16,185,129,0.25);
            transform: translateY(-2px);
        }
        .rail-flyout .nav-item.active {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-dark));
            border-color: var(--admin-primary-dark);
            color: #fff;
            box-shadow: 0 6px 16px rgba(16,185,129,0.35);
        }
        .rail-flyout .nav-item .nav-icon {
            font-size: 22px;
            width: 40px;
            height: 40px;
            line-height: 1;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: var(--admin-bg);
            transition: background 0.15s ease;
        }
        .rail-flyout .nav-item.active .nav-icon {
            background: rgba(255,255,255,0.2);
        }
        .rail-flyout .nav-item .nav-label {
            font-size: 11.5px;
            font-weight: 700;
        }

        /* ── Main Content Area ─────────────────────────────────────── */
        .admin-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow-x: hidden;
        }
        .admin-header {
            height: 64px;
            background: var(--admin-card);
            border-bottom: 1px solid var(--admin-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .admin-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .sidebar-toggle-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 1px solid var(--admin-border);
            background: var(--admin-card);
            color: var(--admin-text);
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .sidebar-toggle-btn:hover {
            background: var(--admin-bg);
            border-color: var(--admin-primary);
        }
        .admin-header-title h1 {
            font-size: 18px;
            font-weight: 800;
            margin: 0;
            color: var(--admin-text);
            letter-spacing: -0.3px;
        }
        .admin-header-title p {
            margin: 2px 0 0 0;
            font-size: 11px;
            color: var(--admin-text-secondary);
        }
        .admin-header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ── VitaFarma Closable Tab Strip ───────────────────────────── */
        .tab-strip {
            display: flex;
            align-items: center;
            gap: 4px;
            background: var(--admin-card);
            border-bottom: 1px solid var(--admin-border);
            padding: 6px 20px 0 20px;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .tab-strip::-webkit-scrollbar { display: none; }
        .tab-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 10px 10px 0 0;
            font-size: 12px;
            font-weight: 700;
            color: var(--admin-text-secondary);
            text-decoration: none;
            background: var(--admin-bg);
            border: 1px solid var(--admin-border);
            border-bottom: none;
            transition: all 0.15s ease;
            position: relative;
            cursor: pointer;
            white-space: nowrap;
        }
        .tab-pill:hover {
            color: var(--admin-text);
            background: var(--admin-card);
        }
        .tab-pill.active {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-dark));
            color: #fff;
            border-color: var(--admin-primary-dark);
            box-shadow: 0 -2px 10px rgba(16, 185, 129, 0.15);
        }
        .tab-pill .tab-icon { font-size: 13px; }
        .tab-pill .tab-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            font-size: 9px;
            margin-left: 4px;
            opacity: 0.7;
            transition: all 0.15s ease;
        }
        .tab-pill .tab-close:hover {
            opacity: 1;
            background: rgba(0,0,0,0.2);
        }

        .admin-body {
            padding: 24px;
            flex: 1;
        }

        /* ── Cards ─────────────────────────────────────────────────── */
        .admin-card {
            background: var(--admin-card);
            border: 1px solid var(--admin-border);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            margin-bottom: 20px;
        }
        .admin-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--admin-border);
        }
        .admin-card-title {
            font-size: 15px;
            font-weight: 800;
            margin: 0;
            color: var(--admin-text);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Tables ────────────────────────────────────────────────── */
        .admin-table-wrap {
            width: 100%;
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--admin-border);
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }
        .admin-table th {
            padding: 11px 14px;
            background: var(--admin-bg);
            color: var(--admin-text-secondary);
            font-weight: 800;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--admin-border);
            white-space: nowrap;
        }
        .admin-table td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--admin-border);
            vertical-align: middle;
        }
        .admin-table tr:last-child td {
            border-bottom: none;
        }
        .admin-table tr:hover td {
            background: rgba(16, 185, 129, 0.02);
        }

        /* ── Badges ────────────────────────────────────────────────── */
        .badge-admin {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
        }
        .badge-admin.info { background: #E0F2FE; color: #0284C7; }
        .badge-admin.success { background: #DCFCE7; color: #16A34A; }
        .badge-admin.warning { background: #FEF3C7; color: #D97706; }
        .badge-admin.danger { background: #FEE2E2; color: #DC2626; }
        .badge-admin.purple { background: #F3E8FF; color: #9333EA; }

        /* ── Forms ─────────────────────────────────────────────────── */
        .admin-form-group { margin-bottom: 14px; }
        .admin-form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--admin-text);
        }
        .admin-input, .admin-select, .admin-textarea {
            width: 100%;
            padding: 9px 12px;
            border-radius: 10px;
            border: 1px solid var(--admin-border);
            background: var(--admin-bg);
            color: var(--admin-text);
            font-family: inherit;
            font-size: 13px;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .admin-input:focus, .admin-select:focus, .admin-textarea:focus {
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
            background: var(--admin-card);
        }

        /* ── Buttons ───────────────────────────────────────────────── */
        .admin-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 12.5px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .admin-btn:active { transform: scale(0.97); }
        .admin-btn-primary {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-dark));
            color: #fff;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25);
        }
        .admin-btn-primary:hover {
            filter: brightness(1.05);
        }
        .admin-btn-outline {
            background: var(--admin-card);
            border: 1px solid var(--admin-border);
            color: var(--admin-text);
        }
        .admin-btn-outline:hover {
            background: var(--admin-bg);
            border-color: var(--admin-primary);
        }
        .admin-btn-danger {
            background: #EF4444;
            color: #fff;
        }
        .admin-btn-danger:hover {
            background: #DC2626;
        }
        .admin-btn-sm {
            padding: 5px 10px;
            font-size: 11.5px;
            border-radius: 8px;
        }

        /* ── Stat Cards ────────────────────────────────────────────── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        .stat-card-admin {
            background: var(--admin-card);
            border: 1px solid var(--admin-border);
            border-radius: 16px;
            padding: 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .stat-card-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.06);
        }
        .stat-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .stat-content h4 {
            margin: 0;
            font-size: 11px;
            font-weight: 700;
            color: var(--admin-text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-content .val {
            font-size: 20px;
            font-weight: 800;
            color: var(--admin-text);
            margin: 3px 0 0 0;
        }

        /* ── Responsive & Mobile ───────────────────────────────────── */
        @media (max-width: 900px) {
            .admin-sidebar {
                position: fixed;
                left: -80px;
                top: 0;
                bottom: 0;
            }
            .admin-sidebar.open {
                left: 0;
            }
        }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <!-- Compact Sidebar Rail (Vitafarma Exact Design) -->
    <aside class="admin-sidebar" id="adminSidebar">
        <a href="/admin" class="admin-brand" title="DuitKu Admin Hub">
            <img src="/images/logo.png" alt="DuitKu Logo">
            <div class="admin-brand-text">
                <div class="name">DuitKu</div>
            </div>
        </a>

        <nav class="admin-nav">
            <button type="button" class="rail-btn <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>" data-group="utama" data-tip="Utama & Dashboard" onclick="openRailFlyout('utama')">
                <span class="rail-icon">🏠</span>
                <span class="rail-label">Utama</span>
            </button>
            <button type="button" class="rail-btn <?= ($activeMenu ?? '') === 'notifications' ? 'active' : '' ?>" data-group="notifikasi" data-tip="Notifikasi & Broadcast" onclick="openRailFlyout('notifikasi')">
                <span class="rail-icon">📢</span>
                <span class="rail-label">Notifikasi</span>
            </button>
            <button type="button" class="rail-btn <?= ($activeMenu ?? '') === 'tv' ? 'active' : '' ?>" data-group="media" data-tip="TV & Streaming M3U" onclick="openRailFlyout('media')">
                <span class="rail-icon">📺</span>
                <span class="rail-label">TV & M3U</span>
            </button>
            <button type="button" class="rail-btn <?= ($activeMenu ?? '') === 'users' ? 'active' : '' ?>" data-group="pengguna" data-tip="Kelola Pengguna" onclick="openRailFlyout('pengguna')">
                <span class="rail-icon">👥</span>
                <span class="rail-label">Pengguna</span>
            </button>
            <button type="button" class="rail-btn" data-group="pintasan" data-tip="Aplikasi & Pintasan" onclick="openRailFlyout('pintasan')">
                <span class="rail-icon">⚡</span>
                <span class="rail-label">Pintasan</span>
            </button>
        </nav>

        <div class="admin-sidebar-footer">
            <div class="admin-user-avatar" title="<?= esc(session()->get('user_name') ?? 'Administrator') ?>">
                <?= esc(substr(session()->get('user_name') ?? 'A', 0, 1)) ?>
            </div>
            <a href="/logout" title="Keluar / Logout" style="color: #EF4444; font-size: 15px; text-decoration: none; margin-top: 4px;">🚪</a>
        </div>
    </aside>

    <!-- Rail Tooltip -->
    <div id="rail-tooltip" class="rail-tooltip"></div>

    <!-- Rail Flyout Backdrop -->
    <div id="rail-flyout-backdrop" class="rail-flyout-backdrop" onclick="closeRailFlyout()"></div>

    <!-- Rail Flyout Panels (Closed by default, opened on demand) -->
    <div id="rail-flyout" class="rail-flyout">
        <!-- Panel: Utama -->
        <div class="rail-flyout-panel" data-group="utama">
            <div class="rail-flyout-head">
                <h2>Utama &amp; Ringkasan</h2>
                <div class="rail-flyout-rule"></div>
            </div>
            <div class="rail-flyout-grid cols-2">
                <a href="/admin" class="nav-item <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>" onclick="handleMenuClick(event, 'dashboard', '/admin'); closeRailFlyout();">
                    <span class="nav-icon">📊</span>
                    <span class="nav-label">Dashboard Ringkasan</span>
                </a>
                <a href="/" target="_blank" class="nav-item" onclick="closeRailFlyout();">
                    <span class="nav-icon">📱</span>
                    <span class="nav-label">Buka Web App ↗</span>
                </a>
            </div>
        </div>

        <!-- Panel: Notifikasi -->
        <div class="rail-flyout-panel" data-group="notifikasi">
            <div class="rail-flyout-head">
                <h2>Notifikasi &amp; Siaran</h2>
                <div class="rail-flyout-rule"></div>
            </div>
            <div class="rail-flyout-grid cols-2">
                <a href="/admin/notifications" class="nav-item <?= ($activeMenu ?? '') === 'notifications' ? 'active' : '' ?>" onclick="handleMenuClick(event, 'notifications', '/admin/notifications'); closeRailFlyout();">
                    <span class="nav-icon">📢</span>
                    <span class="nav-label">Kirim Notifikasi</span>
                </a>
                <a href="/notifications" target="_blank" class="nav-item" onclick="closeRailFlyout();">
                    <span class="nav-icon">🔔</span>
                    <span class="nav-label">Pusat Notif App ↗</span>
                </a>
            </div>
        </div>

        <!-- Panel: Media & TV -->
        <div class="rail-flyout-panel" data-group="media">
            <div class="rail-flyout-head">
                <h2>TV Streaming &amp; M3U</h2>
                <div class="rail-flyout-rule"></div>
            </div>
            <div class="rail-flyout-grid cols-2">
                <a href="/admin/tv" class="nav-item <?= ($activeMenu ?? '') === 'tv' ? 'active' : '' ?>" onclick="handleMenuClick(event, 'tv', '/admin/tv'); closeRailFlyout();">
                    <span class="nav-icon">📺</span>
                    <span class="nav-label">Kelola Channel TV</span>
                </a>
                <a href="/tv" target="_blank" class="nav-item" onclick="closeRailFlyout();">
                    <span class="nav-icon">▶️</span>
                    <span class="nav-label">Live TV Player ↗</span>
                </a>
            </div>
        </div>

        <!-- Panel: Pengguna -->
        <div class="rail-flyout-panel" data-group="pengguna">
            <div class="rail-flyout-head">
                <h2>Kelola Pengguna</h2>
                <div class="rail-flyout-rule"></div>
            </div>
            <div class="rail-flyout-grid cols-1">
                <a href="/admin/users" class="nav-item <?= ($activeMenu ?? '') === 'users' ? 'active' : '' ?>" onclick="handleMenuClick(event, 'users', '/admin/users'); closeRailFlyout();">
                    <span class="nav-icon">👥</span>
                    <span class="nav-label">Daftar Pengguna</span>
                </a>
            </div>
        </div>

        <!-- Panel: Pintasan -->
        <div class="rail-flyout-panel" data-group="pintasan">
            <div class="rail-flyout-head">
                <h2>Pintasan Layanan</h2>
                <div class="rail-flyout-rule"></div>
            </div>
            <div class="rail-flyout-grid cols-2">
                <a href="/" class="nav-item" onclick="closeRailFlyout();">
                    <span class="nav-icon">🏠</span>
                    <span class="nav-label">Aplikasi Utama</span>
                </a>
                <a href="/settings" target="_blank" class="nav-item" onclick="closeRailFlyout();">
                    <span class="nav-icon">⚙️</span>
                    <span class="nav-label">Pengaturan App ↗</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="admin-main">
        <header class="admin-header">
            <div class="admin-header-left">
                <button type="button" class="sidebar-toggle-btn" onclick="toggleAdminSidebar()" title="Toggle Sidebar">☰</button>
                <div class="admin-header-title">
                    <h1><?= esc($pageTitle ?? 'Administrator') ?></h1>
                    <p>DuitKu Multi-Platform Management Hub</p>
                </div>
            </div>

            <div class="admin-header-actions">
                <a href="/admin/notifications" class="admin-btn admin-btn-primary admin-btn-sm" onclick="handleMenuClick(event, 'notifications', '/admin/notifications')">
                    <span>+</span> Kirim Notifikasi
                </a>
                <a href="/admin/tv" class="admin-btn admin-btn-outline admin-btn-sm" onclick="handleMenuClick(event, 'tv', '/admin/tv')">
                    <span>+</span> Tambah TV Channel
                </a>
            </div>
        </header>

        <!-- VitaFarma-Style Closable Tab Strip -->
        <div class="tab-strip" id="tabStrip"></div>

        <div class="admin-body">
            <?php if (session()->getFlashdata('success')): ?>
                <div style="background: #DCFCE7; border: 1px solid #86EFAC; color: #166534; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; font-size: 13px;">
                    ✅ <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div style="background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; font-size: 13px;">
                    ⚠️ <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </div>
    </main>
</div>

<script>
    // ── VitaFarma Tab Strip Manager ──────────────────────────────────────────
    const ALL_TABS = {
        'dashboard':     { icon: '📊', title: 'Dashboard', url: '/admin' },
        'notifications': { icon: '📢', title: 'Kirim Notifikasi', url: '/admin/notifications' },
        'tv':            { icon: '📺', title: 'TV & M3U Channels', url: '/admin/tv' },
        'users':         { icon: '👥', title: 'Kelola Pengguna', url: '/admin/users' }
    };

    const currentTabId = '<?= esc($activeMenu ?? 'dashboard') ?>';

    function getOpenTabs() {
        try {
            const saved = JSON.parse(sessionStorage.getItem('duitku_admin_tabs') || '[]');
            if (Array.isArray(saved) && saved.length > 0) {
                if (!saved.includes('dashboard')) saved.unshift('dashboard');
                if (!saved.includes(currentTabId)) saved.push(currentTabId);
                return saved;
            }
        } catch (e) {}
        return ['dashboard', currentTabId];
    }

    function saveOpenTabs(tabs) {
        sessionStorage.setItem('duitku_admin_tabs', JSON.stringify(tabs));
    }

    function renderTabStrip() {
        const strip = document.getElementById('tabStrip');
        if (!strip) return;
        const openTabs = getOpenTabs();
        
        strip.innerHTML = openTabs.map(tabId => {
            const tab = ALL_TABS[tabId] || { icon: '📄', title: tabId, url: '/admin/' + tabId };
            const isActive = tabId === currentTabId;
            const isPinned = tabId === 'dashboard';

            return `
                <a href="${tab.url}" class="tab-pill ${isActive ? 'active' : ''}" onclick="handleTabPillClick(event, '${tabId}', '${tab.url}')">
                    <span class="tab-icon">${tab.icon}</span>
                    <span class="tab-label">${tab.title}</span>
                    ${!isPinned ? `<span class="tab-close" onclick="closeAdminTab(event, '${tabId}')" title="Tutup Tab">✕</span>` : ''}
                </a>
            `;
        }).join('');
    }

    function handleMenuClick(event, tabId, url) {
        const openTabs = getOpenTabs();
        if (!openTabs.includes(tabId)) {
            openTabs.push(tabId);
            saveOpenTabs(openTabs);
        }
    }

    function handleTabPillClick(event, tabId, url) {
        // Normal navigation to tab URL
    }

    function closeAdminTab(event, tabId) {
        event.preventDefault();
        event.stopPropagation();
        let openTabs = getOpenTabs();
        openTabs = openTabs.filter(id => id !== tabId);
        saveOpenTabs(openTabs);

        if (currentTabId === tabId) {
            const lastTab = openTabs[openTabs.length - 1] || 'dashboard';
            const targetUrl = ALL_TABS[lastTab]?.url || '/admin';
            window.location.href = targetUrl;
        } else {
            renderTabStrip();
        }
    }

    /* ── Vitafarma Exact Rail Flyout & Tooltip Logic ───────────────────────── */
    function toggleAdminSidebar() {
        const sidebar = document.getElementById('adminSidebar');
        if (sidebar) {
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('open');
        }
        closeRailFlyout();
    }

    function openRailFlyout(group) {
        const panel = document.querySelector('.rail-flyout-panel[data-group="' + group + '"]');
        if (!panel) return;
        
        const flyout = document.getElementById('rail-flyout');
        const backdrop = document.getElementById('rail-flyout-backdrop');
        const sidebar = document.getElementById('adminSidebar');
        
        const isCurrentlyOpen = panel.classList.contains('open') && flyout && flyout.classList.contains('open');
        closeRailFlyout();

        if (!isCurrentlyOpen && flyout) {
            panel.classList.add('open');
            if (sidebar) {
                const rect = sidebar.getBoundingClientRect();
                flyout.style.left = (rect.right > 0 ? rect.right : 78) + 'px';
            } else {
                flyout.style.left = '78px';
            }
            flyout.classList.add('open');
            if (backdrop) backdrop.classList.add('open');
            document.querySelectorAll('.rail-btn').forEach(b => {
                b.classList.toggle('active', b.dataset.group === group);
            });
        }
    }

    function closeRailFlyout() {
        document.querySelectorAll('.rail-flyout-panel').forEach(p => p.classList.remove('open'));
        const flyout = document.getElementById('rail-flyout');
        if (flyout) flyout.classList.remove('open');
        const backdrop = document.getElementById('rail-flyout-backdrop');
        if (backdrop) backdrop.classList.remove('open');
        
        const groupMapping = {
            'dashboard': 'utama',
            'notifications': 'notifikasi',
            'tv': 'media',
            'users': 'pengguna'
        };
        const activeGroup = groupMapping[currentTabId] || 'utama';
        document.querySelectorAll('.rail-btn').forEach(b => {
            b.classList.toggle('active', b.dataset.group === activeGroup);
        });
        hideRailTooltip();
    }

    function showRailTooltip(btn) {
        const tip = document.getElementById('rail-tooltip');
        if (!tip) return;
        tip.textContent = btn.dataset.tip || '';
        const r = btn.getBoundingClientRect();
        tip.style.left = (r.right + 10) + 'px';
        tip.style.top = (r.top + r.height / 2) + 'px';
        tip.classList.add('show');
    }

    function hideRailTooltip() {
        const tip = document.getElementById('rail-tooltip');
        if (tip) tip.classList.remove('show');
    }

    document.addEventListener('mouseover', (e) => {
        const btn = e.target.closest('.rail-btn');
        if (btn) showRailTooltip(btn);
        else hideRailTooltip();
    });

    document.addEventListener('mouseout', (e) => {
        if (e.target.closest('.rail-btn')) hideRailTooltip();
    });

    document.addEventListener('DOMContentLoaded', () => {
        renderTabStrip();
        if (localStorage.getItem('duitku_dark') === '1') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    });
</script>

<?= $this->renderSection('scripts') ?>
</body>
</html>

