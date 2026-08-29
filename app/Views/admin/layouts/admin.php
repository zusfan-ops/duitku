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
            --admin-sidebar-w: 260px;
            --admin-primary: #10B981;
            --admin-primary-dark: #059669;
            --admin-bg: #F8FAFC;
            --admin-card: #FFFFFF;
            --admin-sidebar: #0F172A;
            --admin-sidebar-text: #94A3B8;
            --admin-sidebar-active: #1E293B;
        }
        [data-theme="dark"] {
            --admin-bg: #0B1120;
            --admin-card: #1E293B;
            --admin-sidebar: #020617;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--admin-bg);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar */
        .admin-sidebar {
            width: var(--admin-sidebar-w);
            background: var(--admin-sidebar);
            color: #fff;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            border-right: 1px solid rgba(255,255,255,0.06);
            transition: all 0.2s ease;
            z-index: 100;
        }
        .admin-brand {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .admin-brand img {
            width: 36px;
            height: 36px;
            border-radius: 10px;
        }
        .admin-brand-text h2 {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .admin-brand-text span {
            font-size: 11px;
            background: rgba(16, 185, 129, 0.2);
            color: #34D399;
            padding: 2px 6px;
            border-radius: 6px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .admin-nav {
            padding: 16px 12px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .admin-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            color: var(--admin-sidebar-text);
            text-decoration: none;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            transition: all 0.15s ease;
        }
        .admin-nav-item:hover {
            color: #fff;
            background: rgba(255,255,255,0.05);
        }
        .admin-nav-item.active {
            color: #fff;
            background: var(--admin-primary-dark);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }
        .admin-nav-icon {
            font-size: 18px;
            width: 22px;
            text-align: center;
        }
        .admin-sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .admin-user-pill {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .admin-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--admin-primary);
            color: #fff;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
        }
        .admin-user-info {
            line-height: 1.2;
        }
        .admin-user-name {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
        }
        .admin-user-role {
            font-size: 11px;
            color: #34D399;
            text-transform: uppercase;
            font-weight: 700;
        }
        /* Main Content */
        .admin-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow-x: hidden;
        }
        .admin-header {
            height: 70px;
            background: var(--admin-card);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .admin-header-title h1 {
            font-size: 20px;
            font-weight: 800;
            margin: 0;
            color: var(--text-primary);
        }
        .admin-header-title p {
            margin: 2px 0 0 0;
            font-size: 12px;
            color: var(--text-secondary);
        }
        .admin-header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .admin-body {
            padding: 28px;
            flex: 1;
        }
        /* Cards */
        .admin-card {
            background: var(--admin-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            margin-bottom: 24px;
        }
        .admin-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border-light);
        }
        .admin-card-title {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        /* Tables */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13.5px;
        }
        .admin-table th {
            padding: 12px 14px;
            background: var(--border-light);
            color: var(--text-secondary);
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border);
        }
        .admin-table td {
            padding: 14px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
        }
        .admin-table tr:hover td {
            background: rgba(0,0,0,0.015);
        }
        /* Badges */
        .badge-admin {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
        }
        .badge-admin.info { background: #E0F2FE; color: #0284C7; }
        .badge-admin.success { background: #DCFCE7; color: #16A34A; }
        .badge-admin.warning { background: #FEF3C7; color: #D97706; }
        .badge-admin.danger { background: #FEE2E2; color: #DC2626; }
        .badge-admin.purple { background: #F3E8FF; color: #9333EA; }
        /* Forms */
        .admin-form-group {
            margin-bottom: 16px;
        }
        .admin-form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--text-primary);
        }
        .admin-input, .admin-select, .admin-textarea {
            width: 100%;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--bg);
            color: var(--text-primary);
            font-family: inherit;
            font-size: 13.5px;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .admin-input:focus, .admin-select:focus, .admin-textarea:focus {
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }
        .admin-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .admin-btn-primary {
            background: var(--admin-primary);
            color: #fff;
        }
        .admin-btn-primary:hover {
            background: var(--admin-primary-dark);
            transform: translateY(-1px);
        }
        .admin-btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-primary);
        }
        .admin-btn-outline:hover {
            background: var(--border-light);
        }
        .admin-btn-danger {
            background: #EF4444;
            color: #fff;
        }
        .admin-btn-danger:hover {
            background: #DC2626;
        }
        .admin-btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 8px;
        }
        /* Stat Cards */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }
        .stat-card-admin {
            background: var(--admin-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            transition: transform 0.15s ease;
        }
        .stat-card-admin:hover {
            transform: translateY(-2px);
        }
        .stat-icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .stat-content h4 {
            margin: 0;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-content .val {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-primary);
            margin: 4px 0 0 0;
        }
        /* Mobile Toggle */
        .admin-sidebar-toggle {
            display: none;
            background: none;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 6px 10px;
            cursor: pointer;
            color: var(--text-primary);
        }
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
            .admin-sidebar-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-brand">
            <img src="/images/logo.png" alt="DuitKu Logo">
            <div class="admin-brand-text">
                <h2>DuitKu <span>ADMIN</span></h2>
            </div>
        </div>

        <nav class="admin-nav">
            <a href="/admin" class="admin-nav-item <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
                <span class="admin-nav-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="/admin/notifications" class="admin-nav-item <?= ($activeMenu ?? '') === 'notifications' ? 'active' : '' ?>">
                <span class="admin-nav-icon">📢</span>
                <span>Kirim Notifikasi</span>
            </a>
            <a href="/admin/tv" class="admin-nav-item <?= ($activeMenu ?? '') === 'tv' ? 'active' : '' ?>">
                <span class="admin-nav-icon">📺</span>
                <span>TV & M3U Channels</span>
            </a>
            <a href="/admin/users" class="admin-nav-item <?= ($activeMenu ?? '') === 'users' ? 'active' : '' ?>">
                <span class="admin-nav-icon">👥</span>
                <span>Kelola Pengguna</span>
            </a>
            <a href="/tv" target="_blank" class="admin-nav-item">
                <span class="admin-nav-icon">▶️</span>
                <span>Live TV Web Player ↗</span>
            </a>
            <a href="/" class="admin-nav-item">
                <span class="admin-nav-icon">🏠</span>
                <span>Kembali ke App ↗</span>
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
            <div style="display: flex; align-items: center; gap: 14px;">
                <button class="admin-sidebar-toggle" onclick="document.getElementById('adminSidebar').classList.toggle('open')">☰</button>
                <div class="admin-header-title">
                    <h1><?= esc($pageTitle ?? 'Administrator') ?></h1>
                    <p>DuitKu Multi-Platform Management Hub</p>
                </div>
            </div>

            <div class="admin-header-actions">
                <a href="/admin/notifications" class="admin-btn admin-btn-primary admin-btn-sm">
                    <span>+</span> Kirim Notifikasi
                </a>
                <a href="/admin/tv" class="admin-btn admin-btn-outline admin-btn-sm">
                    <span>+</span> Tambah Channel TV
                </a>
            </div>
        </header>

        <div class="admin-body">
            <?php if (session()->getFlashdata('success')): ?>
                <div style="background: #DCFCE7; border: 1px solid #86EFAC; color: #166534; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; font-size: 13.5px;">
                    ✅ <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div style="background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; font-size: 13.5px;">
                    ⚠️ <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </div>
    </main>
</div>

<script>
    // Theme setup from storage
    if (localStorage.getItem('duitku_dark') === '1') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
