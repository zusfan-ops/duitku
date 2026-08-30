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
            --admin-sidebar-w: 250px;
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
        
        /* ── Sidebar (VitaFarma style) ──────────────────────────────── */
        .admin-sidebar {
            width: var(--admin-sidebar-w);
            background: linear-gradient(180deg, #0F172A 0%, #020617 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            border-right: 1px solid rgba(255,255,255,0.06);
            transition: all 0.22s ease;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 100;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
        }
        .admin-sidebar.collapsed {
            width: 72px;
        }
        .admin-brand {
            padding: 20px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
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
        .admin-brand-text h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 6px;
            letter-spacing: -0.2px;
            white-space: nowrap;
        }
        .admin-brand-text span {
            font-size: 10px;
            background: rgba(16, 185, 129, 0.2);
            color: #34D399;
            padding: 2px 6px;
            border-radius: 6px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .admin-nav {
            padding: 16px 12px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
            overflow-y: auto;
            scrollbar-width: none;
        }
        .admin-nav::-webkit-scrollbar { display: none; }
        
        .admin-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            color: var(--admin-sidebar-text);
            text-decoration: none;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.15s ease;
            white-space: nowrap;
            position: relative;
        }
        .admin-nav-item:hover {
            color: #fff;
            background: rgba(255,255,255,0.06);
            transform: translateX(2px);
        }
        .admin-nav-item.active {
            color: #fff;
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-dark));
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
        }
        .admin-nav-icon {
            font-size: 18px;
            width: 24px;
            text-align: center;
            flex-shrink: 0;
        }
        .admin-sidebar.collapsed .admin-nav-text,
        .admin-sidebar.collapsed .admin-brand-text {
            display: none;
        }
        .admin-sidebar.collapsed .admin-nav-item {
            justify-content: center;
            padding: 12px 0;
        }
        
        .admin-sidebar-footer {
            padding: 14px 16px;
            border-top: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .admin-user-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .admin-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10B981, #059669);
            color: #fff;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
        }
        .admin-user-info {
            line-height: 1.2;
            min-width: 0;
            overflow: hidden;
        }
        .admin-user-name {
            font-size: 12.5px;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .admin-user-role {
            font-size: 10px;
            color: #34D399;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.4px;
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
            margin: 1px 0 0 0;
            font-size: 11px;
            color: var(--admin-text-secondary);
        }
        .admin-header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ── Accurate/VitaFarma-Style Closable Tab Strip ─────────────── */
        .tab-strip {
            display: flex;
            gap: 4px;
            background: var(--admin-card);
            border-bottom: 1px solid var(--admin-border);
            padding: 8px 20px 0;
            overflow-x: auto;
            scrollbar-width: thin;
            position: sticky;
            top: 64px;
            z-index: 45;
        }
        .tab-strip::-webkit-scrollbar { height: 4px; }
        .tab-strip::-webkit-scrollbar-thumb { background: var(--admin-border); border-radius: 4px; }
        
        .tab-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border: 1px solid var(--admin-border);
            border-bottom: none;
            border-radius: 10px 10px 0 0;
            background: var(--admin-bg);
            color: var(--admin-text-secondary);
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            cursor: pointer;
            flex-shrink: 0;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .tab-pill:hover {
            background: var(--admin-primary-light);
            color: var(--admin-primary-dark);
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

        /* ── Responsive & Modal ────────────────────────────────────── */
        @media (max-width: 900px) {
            .admin-sidebar {
                position: fixed;
                left: -260px;
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
    <!-- Sidebar (VitaFarma Multi-Menu Rail) -->
    <aside class="admin-sidebar" id="adminSidebar">
        <a href="/admin" class="admin-brand">
            <img src="/images/logo.png" alt="DuitKu Logo">
            <div class="admin-brand-text">
                <h2>DuitKu <span>ADMIN</span></h2>
            </div>
        </a>

        <nav class="admin-nav">
            <a href="/admin" class="admin-nav-item <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>" data-tab-id="dashboard" data-tab-icon="📊" data-tab-title="Dashboard" onclick="handleMenuClick(event, 'dashboard', '/admin')">
                <span class="admin-nav-icon">📊</span>
                <span class="admin-nav-text">Dashboard Ringkasan</span>
            </a>
            <a href="/admin/notifications" class="admin-nav-item <?= ($activeMenu ?? '') === 'notifications' ? 'active' : '' ?>" data-tab-id="notifications" data-tab-icon="📢" data-tab-title="Kirim Notifikasi" onclick="handleMenuClick(event, 'notifications', '/admin/notifications')">
                <span class="admin-nav-icon">📢</span>
                <span class="admin-nav-text">Kirim Notifikasi</span>
            </a>
            <a href="/admin/tv" class="admin-nav-item <?= ($activeMenu ?? '') === 'tv' ? 'active' : '' ?>" data-tab-id="tv" data-tab-icon="📺" data-tab-title="TV & M3U Channels" onclick="handleMenuClick(event, 'tv', '/admin/tv')">
                <span class="admin-nav-icon">📺</span>
                <span class="admin-nav-text">TV & M3U Channels</span>
            </a>
            <a href="/admin/users" class="admin-nav-item <?= ($activeMenu ?? '') === 'users' ? 'active' : '' ?>" data-tab-id="users" data-tab-icon="👥" data-tab-title="Kelola Pengguna" onclick="handleMenuClick(event, 'users', '/admin/users')">
                <span class="admin-nav-icon">👥</span>
                <span class="admin-nav-text">Kelola Pengguna</span>
            </a>
            <a href="/tv" target="_blank" class="admin-nav-item">
                <span class="admin-nav-icon">▶️</span>
                <span class="admin-nav-text">Live TV Web Player ↗</span>
            </a>
            <a href="/" class="admin-nav-item">
                <span class="admin-nav-icon">🏠</span>
                <span class="admin-nav-text">Kembali ke App ↗</span>
            </a>
        </nav>

        <div class="admin-sidebar-footer">
            <div class="admin-user-pill">
                <div class="admin-user-avatar">
                    <?= esc(substr(session()->get('user_name') ?? 'A', 0, 1)) ?>
                </div>
                <div class="admin-user-info">
                    <div class="admin-user-name"><?= esc(session()->get('user_name') ?? 'Administrator') ?></div>
                    <div class="admin-user-role"><?= esc(session()->get('user_role') ?? 'ADMIN') ?></div>
                </div>
            </div>
            <a href="/logout" title="Logout" style="color: #EF4444; font-size: 16px; text-decoration: none;">🚪</a>
        </div>
    </aside>

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
        // Let normal browser navigation take user to page smoothly
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

    function toggleAdminSidebar() {
        const sidebar = document.getElementById('adminSidebar');
        if (sidebar) {
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('open');
        }
    }

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

