<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= esc($store['store_name']) ?> — Daftar Menu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #EA580C;
            --primary-hover: #C2410C;
            --primary-light: #FFF7ED;
            --bg: #0F172A;
            --card-bg: #1E293B;
            --card-hover: #334155;
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
            --border: #334155;
            --accent-green: #10B981;
            --accent-amber: #F59E0B;
            --accent-red: #EF4444;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            padding-bottom: 120px;
            -webkit-tap-highlight-color: transparent;
        }

        /* Top Hero Header */
        .store-hero {
            background: linear-gradient(180deg, #1E293B 0%, #0F172A 100%);
            padding: 24px 18px 16px;
            border-bottom: 1px solid var(--border);
            position: relative;
        }
        .store-header-content {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }
        .store-badge-open {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            background: rgba(16, 185, 129, 0.15);
            color: #34D399;
            border: 1px solid rgba(16, 185, 129, 0.3);
            margin-bottom: 6px;
        }
        .store-badge-open.closed {
            background: rgba(239, 68, 68, 0.15);
            color: #F87171;
            border-color: rgba(239, 68, 68, 0.3);
        }
        .store-badge-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
        }
        .store-title {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: #fff;
            line-height: 1.2;
        }
        .store-tagline {
            font-size: 12.5px;
            color: var(--text-muted);
            margin-top: 4px;
            line-height: 1.4;
        }
        .store-meta-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 10px;
            font-size: 11.5px;
            color: #CBD5E1;
            flex-wrap: wrap;
        }
        .store-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Table pill indicator */
        .table-pill {
            background: linear-gradient(135deg, #EA580C 0%, #F97316 100%);
            color: #fff;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
            cursor: pointer;
        }

        /* Search Bar */
        .search-box-wrap {
            padding: 12px 16px 4px;
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(12px);
        }
        .search-input-inner {
            position: relative;
            display: flex;
            align-items: center;
        }
        .search-input-inner input {
            width: 100%;
            background: #1E293B;
            border: 1px solid var(--border);
            color: #fff;
            padding: 10px 14px 10px 38px;
            border-radius: 14px;
            font-size: 13.5px;
            outline: none;
            transition: border-color 0.15s;
        }
        .search-input-inner input:focus {
            border-color: var(--primary);
        }
        .search-icon-pos {
            position: absolute;
            left: 12px;
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Categories Scroll */
        .cat-scroll-nav {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: 10px 16px;
            scrollbar-width: none;
            position: sticky;
            top: 54px;
            z-index: 39;
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(51, 65, 85, 0.5);
        }
        .cat-scroll-nav::-webkit-scrollbar { display: none; }
        .cat-pill-btn {
            padding: 7px 16px;
            border-radius: 20px;
            background: #1E293B;
            border: 1px solid var(--border);
            color: #CBD5E1;
            font-size: 12.5px;
            font-weight: 700;
            white-space: nowrap;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .cat-pill-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
        }

        /* Catalog Layout */
        .catalog-container {
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .section-name {
            font-size: 15px;
            font-weight: 800;
            color: #F1F5F9;
            letter-spacing: -0.2px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .section-count {
            font-size: 11.5px;
            color: var(--text-muted);
            font-weight: 600;
        }

        /* Product Card */
        .product-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }
        @media (min-width: 640px) {
            .product-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
            .catalog-container { max-width: 760px; margin: 0 auto; }
            .search-box-wrap { max-width: 760px; margin: 0 auto; }
            .cat-scroll-nav { max-width: 760px; margin: 0 auto; }
            .store-hero { max-width: 760px; margin: 0 auto; }
        }

        .menu-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            transition: border-color 0.15s ease, transform 0.1s ease;
            position: relative;
        }
        .menu-card:active {
            transform: scale(0.99);
        }
        .menu-card-main {
            flex: 1;
            min-width: 0;
        }
        .menu-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #334155;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .menu-title {
            font-size: 14.5px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 3px;
            line-height: 1.3;
        }
        .menu-desc {
            font-size: 11.5px;
            color: var(--text-muted);
            margin-bottom: 6px;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .menu-price {
            font-size: 14px;
            font-weight: 800;
            color: #FB923C;
        }

        /* Action Add/Counter */
        .btn-add-item {
            background: linear-gradient(135deg, #EA580C 0%, #F97316 100%);
            color: #fff;
            border: none;
            padding: 7px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 4px 10px rgba(234, 88, 12, 0.25);
            flex-shrink: 0;
        }
        .btn-add-item:active { transform: scale(0.94); }

        .item-stepper {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #0F172A;
            padding: 3px 6px;
            border-radius: 10px;
            border: 1px solid var(--border);
            flex-shrink: 0;
        }
        .btn-step-mini {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            border: none;
            background: #334155;
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .btn-step-mini:active { background: var(--primary); }
        .step-qty-val {
            font-size: 13px;
            font-weight: 800;
            color: #fff;
            min-width: 18px;
            text-align: center;
        }

        /* Floating Bottom Cart Bar */
        .bottom-cart-bar {
            position: fixed;
            bottom: 16px;
            left: 16px;
            right: 16px;
            max-width: 500px;
            margin: 0 auto;
            background: linear-gradient(135deg, #EA580C 0%, #C2410C 100%);
            border-radius: 18px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 30px rgba(234, 88, 12, 0.45);
            z-index: 50;
            cursor: pointer;
            animation: bounceIn 0.3s ease-out;
        }
        .bottom-cart-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .cart-badge-count {
            background: #fff;
            color: #EA580C;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12.5px;
            font-weight: 900;
        }
        .cart-bar-total {
            font-size: 16px;
            font-weight: 900;
            color: #fff;
            letter-spacing: -0.3px;
        }
        .cart-bar-subtext {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.85);
        }
        .btn-open-cart {
            background: #fff;
            color: #0F172A;
            border: none;
            border-radius: 12px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Modal & Drawer Styling */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(6px);
            z-index: 100;
            display: none;
            align-items: flex-end;
            justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal-drawer {
            background: #1E293B;
            border: 1px solid var(--border);
            border-top-left-radius: 24px;
            border-top-right-radius: 24px;
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: slideDrawer 0.25s ease-out;
        }
        .drawer-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .drawer-title { font-size: 16px; font-weight: 800; color: #fff; }
        .drawer-close {
            background: #334155;
            border: none;
            color: #fff;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
        }
        .drawer-body {
            padding: 16px 20px;
            overflow-y: auto;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .drawer-footer {
            padding: 14px 20px;
            border-top: 1px solid var(--border);
            background: #0F172A;
        }

        /* Form Inputs in Drawer */
        .input-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .input-label {
            font-size: 12px;
            font-weight: 700;
            color: #CBD5E1;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .input-control {
            width: 100%;
            background: #0F172A;
            border: 1px solid var(--border);
            color: #fff;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 13.5px;
            font-family: inherit;
            outline: none;
        }
        .input-control:focus { border-color: var(--primary); }

        .cart-item-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(51, 65, 85, 0.6);
            gap: 10px;
        }
        .cart-item-title { font-size: 13.5px; font-weight: 700; color: #fff; }
        .cart-item-note-input {
            width: 100%;
            background: #0F172A;
            border: 1px dashed var(--border);
            color: #94A3B8;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 11.5px;
            margin-top: 4px;
            font-family: inherit;
            outline: none;
        }
        .cart-item-note-input:focus { border-color: var(--primary); color: #fff; }

        .btn-submit-order {
            width: 100%;
            background: linear-gradient(135deg, #EA580C 0%, #C2410C 100%);
            color: #fff;
            border: none;
            padding: 14px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 20px rgba(234, 88, 12, 0.4);
            transition: transform 0.1s ease;
        }
        .btn-submit-order:active { transform: scale(0.98); }
        .btn-submit-order:disabled { opacity: 0.5; cursor: not-allowed; }

        .type-tab-btn {
            background: transparent;
            border: 1px solid transparent;
            color: var(--text-muted);
            padding: 8px 6px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            text-align: center;
        }
        .type-tab-btn.active {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 2px 8px rgba(234, 88, 12, 0.4);
        }

        @keyframes slideDrawer {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }
        @keyframes bounceIn {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>

    <!-- Store Hero Header -->
    <div class="store-hero">
        <div class="store-header-content">
            <div>
                <?php if ($store['store_is_open']): ?>
                    <div class="store-badge-open">
                        <span class="store-badge-dot"></span> BUKA SEKARANG
                    </div>
                <?php else: ?>
                    <div class="store-badge-open closed">
                        <span class="store-badge-dot"></span> SEDANG TUTUP
                    </div>
                <?php endif; ?>
                <h1 class="store-title"><?= esc($store['store_name']) ?></h1>
                <p class="store-tagline"><?= esc($store['store_tagline']) ?></p>
            </div>
            
            <!-- Table selector / indicator -->
            <div class="table-pill" id="tableHeaderPill" onclick="openTableModal()">
                <span>🪑</span>
                <span id="displayTableText"><?= $tableQuery ? ('Meja ' . esc($tableQuery)) : 'Pilih Meja' ?></span>
            </div>
        </div>

        <?php if (!empty($store['store_address']) || !empty($store['store_phone'])): ?>
            <div class="store-meta-row">
                <?php if (!empty($store['store_address'])): ?>
                    <div class="store-meta-item">📍 <?= esc($store['store_address']) ?></div>
                <?php endif; ?>
                <?php if (!empty($store['store_phone'])): ?>
                    <div class="store-meta-item">📞 <?= esc($store['store_phone']) ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Search Box -->
    <div class="search-box-wrap">
        <div class="search-input-inner">
            <span class="search-icon-pos">🔍</span>
            <input type="text" id="publicSearchInput" placeholder="Cari makanan, minuman, cemilan..." oninput="handleSearch(this.value)">
        </div>
    </div>

    <!-- Category Filter Bar -->
    <div class="cat-scroll-nav" id="catScrollNav">
        <?php foreach ($categories as $idx => $cat): ?>
            <button class="cat-pill-btn <?= ($idx === 0) ? 'active' : '' ?>" onclick="filterCategory('<?= esc($cat) ?>', this)">
                <?= esc($cat) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Catalog Menu Container -->
    <div class="catalog-container" id="catalogList">
        <?php if (empty($products)): ?>
            <div style="text-align:center;padding:50px 20px;color:var(--text-muted)">
                <div style="font-size:44px;margin-bottom:12px">🍽️</div>
                <div style="font-size:16px;font-weight:800;color:#fff;margin-bottom:6px">Daftar Menu Belum Tersedia</div>
                <div style="font-size:12.5px">Outlet ini belum menambahkan menu atau sedang memperbarui katalog.</div>
            </div>
        <?php else: ?>
            <?php foreach ($groupedProducts as $categoryName => $catItems): 
                $iconMap = [
                    'coffee' => '☕', 'tea' => '🍵', 'drink' => '🧃',
                    'food' => '🥐', 'snack' => '🍟', 'box' => '📦',
                    'groceries' => '🛒', 'rice' => '🌾', 'cigarette' => '🚬'
                ];
            ?>
                <div class="category-block" data-category="<?= esc($categoryName) ?>">
                    <div class="section-header">
                        <div class="section-name">
                            <span>✨</span> <?= esc($categoryName) ?>
                        </div>
                        <div class="section-count"><?= count($catItems) ?> menu</div>
                    </div>

                    <div class="product-grid">
                        <?php foreach ($catItems as $p): 
                            $icon = $iconMap[$p['icon'] ?? 'box'] ?? '☕';
                        ?>
                            <div class="menu-card" id="card-<?= $p['id'] ?>" data-id="<?= $p['id'] ?>" data-name="<?= esc($p['name']) ?>" data-price="<?= (float)$p['selling_price'] ?>" data-category="<?= esc($p['category']) ?>">
                                <div class="menu-icon-box"><?= $icon ?></div>
                                <div class="menu-card-main">
                                    <div class="menu-title"><?= esc($p['name']) ?></div>
                                    <?php if (!empty($p['description'])): ?>
                                        <div class="menu-desc"><?= esc($p['description']) ?></div>
                                    <?php endif; ?>
                                    <div class="menu-price"><?= esc($symbol) ?> <?= number_format($p['selling_price'], 0, ',', '.') ?></div>
                                </div>
                                <div class="menu-card-action" id="action-<?= $p['id'] ?>">
                                    <button class="btn-add-item" onclick="addToCart(<?= $p['id'] ?>)">
                                        + Tambah
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Floating Bottom Cart Bar (Shown when cart > 0) -->
    <div class="bottom-cart-bar" id="bottomCartBar" style="display:none" onclick="openCartModal()">
        <div class="bottom-cart-left">
            <div class="cart-badge-count" id="cartTotalItems">0</div>
            <div>
                <div class="cart-bar-total" id="cartTotalPrice"><?= esc($symbol) ?> 0</div>
                <div class="cart-bar-subtext">Ketuk untuk review & pesan</div>
            </div>
        </div>
        <button class="btn-open-cart">
            Lanjut Pesan ➔
        </button>
    </div>

    <!-- Cart / Checkout Drawer Modal -->
    <div class="modal-overlay" id="cartModal">
        <div class="modal-drawer">
            <div class="drawer-header">
                <div class="drawer-title">🛒 Keranjang Pesanan Anda</div>
                <button class="drawer-close" onclick="closeCartModal()">✕</button>
            </div>
            <div class="drawer-body">
                
                <!-- Fulfillment Method / Order Type Selector -->
                <div style="background:#0F172A;border:1px solid var(--border);border-radius:14px;padding:4px;display:grid;grid-template-columns:repeat(3, 1fr);gap:4px">
                    <button type="button" id="typeTabDelivery" class="type-tab-btn active" onclick="setOrderType('delivery')">
                        🛵 Delivery
                    </button>
                    <button type="button" id="typeTabTakeaway" class="type-tab-btn" onclick="setOrderType('takeaway')">
                        🛍️ Takeaway
                    </button>
                    <button type="button" id="typeTabDineIn" class="type-tab-btn" onclick="setOrderType('dine_in')">
                        🪑 Di Tempat
                    </button>
                </div>

                <!-- Customer Details -->
                <div style="display:flex;flex-direction:column;gap:10px">
                    <!-- Delivery Fields -->
                    <div id="deliveryFieldsGroup">
                        <div class="input-group">
                            <label class="input-label">📍 Alamat Pengiriman Lengkap *</label>
                            <textarea id="orderDeliveryAddress" class="input-control" rows="2" placeholder="Nama Jalan, No. Rumah, RT/RW, Kelurahan, Kecamatan..."></textarea>
                        </div>
                        <div class="input-group" style="margin-top:8px">
                            <label class="input-label">🏡 Patokan / Catatan Kurir <span style="font-weight:400;color:var(--text-muted)">(opsional)</span></label>
                            <input type="text" id="orderDeliveryNotes" class="input-control" placeholder="Pagar hitam depan pos ronda, titip satpam, dll.">
                        </div>
                    </div>

                    <!-- Takeaway Field -->
                    <div id="takeawayFieldsGroup" style="display:none">
                        <div class="input-group">
                            <label class="input-label">⏰ Estimasi Waktu Pengambilan</label>
                            <input type="text" id="orderPickupTime" class="input-control" placeholder="Contoh: 15-20 Menit lagi, Jam 18:30...">
                        </div>
                    </div>

                    <!-- Dine-in Field -->
                    <div id="dineInFieldsGroup" style="display:none">
                        <div class="input-group">
                            <label class="input-label">🪑 Nomor Meja *</label>
                            <input type="text" id="orderTableNo" class="input-control" placeholder="Contoh: 01, Meja 5, VIP..." value="<?= esc($tableQuery) ?>">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                        <div class="input-group">
                            <label class="input-label">👤 Nama Pemesan *</label>
                            <input type="text" id="orderCustomerName" class="input-control" placeholder="Nama Anda (cth: Budi)">
                        </div>
                        <div class="input-group">
                            <label class="input-label">📱 No. WhatsApp *</label>
                            <input type="tel" id="orderCustomerPhone" class="input-control" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <!-- Payment Method Option -->
                    <div class="input-group">
                        <label class="input-label">💳 Metode Pembayaran</label>
                        <select id="orderPaymentMethod" class="input-control" onchange="togglePaymentInfo()">
                            <option value="cod">💵 Bayar di Tempat (COD / Kasir)</option>
                            <option value="transfer">🏦 Transfer Bank Manual</option>
                            <option value="qris">📱 QRIS / E-Wallet</option>
                        </select>
                    </div>

                    <!-- Bank / QRIS info box if configured -->
                    <div id="bankInfoBox" style="display:none;background:#0F172A;border:1px dashed var(--primary);border-radius:12px;padding:10px;font-size:12px;color:#CBD5E1">
                        <div style="font-weight:800;color:#FB923C;margin-bottom:4px">ℹ️ Informasi Rekening Toko:</div>
                        <div><?= nl2br(esc($store['store_bank_info'] ?: 'Silakan hubungi kasir/toko untuk nomor rekening transfer.')) ?></div>
                    </div>
                </div>

                <!-- Order Item List -->
                <div>
                    <div style="font-size:12px;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:8px">Rincian Menu Dipilih</div>
                    <div id="cartItemList">
                        <!-- Rendered by JS -->
                    </div>
                </div>

                <!-- Total Bill Breakdown -->
                <div style="background:#0F172A;border:1px solid var(--border);border-radius:14px;padding:12px;display:flex;flex-direction:column;gap:6px">
                    <div style="display:flex;justify-content:space-between;font-size:12.5px;color:var(--text-muted)">
                        <span>Subtotal Produk (<span id="drawerTotalQty">0</span> item)</span>
                        <span id="drawerSubtotalAmount"><?= esc($symbol) ?> 0</span>
                    </div>
                    <div id="deliveryFeeRow" style="display:flex;justify-content:space-between;font-size:12.5px;color:var(--text-muted)">
                        <span>Ongkos Kirim (Delivery)</span>
                        <span id="drawerDeliveryFee"><?= esc($symbol) ?> <?= number_format((float)($store['store_delivery_fee'] ?? 0), 0, ',', '.') ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:900;color:#fff;border-top:1px dashed var(--border);padding-top:6px;margin-top:2px">
                        <span>Total Pembayaran</span>
                        <span id="drawerTotalAmount" style="color:#FB923C"><?= esc($symbol) ?> 0</span>
                    </div>
                </div>

                <div style="font-size:11.5px;color:var(--text-muted);line-height:1.4;text-align:center;padding:0 8px">
                    ℹ️ Pesanan Anda akan langsung dikirim ke toko dan diproses. Anda dapat memantau status pesanan secara langsung.
                </div>

            </div>
            <div class="drawer-footer">
                <button class="btn-submit-order" id="btnSubmitOrder" onclick="submitOrder()">
                    <span>🚀</span> Kirim Pesanan Sekarang
                </button>
            </div>
        </div>
    </div>

    <!-- Table Selection Modal -->
    <div class="modal-overlay" id="tableModal">
        <div class="modal-drawer" style="max-height:400px">
            <div class="drawer-header">
                <div class="drawer-title">🪑 Masukkan Nomor Meja Anda</div>
                <button class="drawer-close" onclick="closeTableModal()">✕</button>
            </div>
            <div class="drawer-body">
                <p style="font-size:13px;color:var(--text-muted)">Pastikan nomor meja sesuai tempat Anda duduk agar pesanan diantar dengan tepat.</p>
                <div class="input-group" style="margin-top:8px">
                    <label class="input-label">Nomor Meja</label>
                    <input type="text" id="inputTableModal" class="input-control" placeholder="Contoh: 01, Meja 5, VIP..." value="<?= esc($tableQuery) ?>">
                </div>
            </div>
            <div class="drawer-footer">
                <button class="btn-submit-order" onclick="saveTableFromModal()">
                    Simpan Nomor Meja
                </button>
            </div>
        </div>
    </div>

    <script>
        const STORE_SLUG = "<?= esc($slug) ?>";
        const CURRENCY_SYMBOL = "<?= esc($symbol) ?>";
        const PRODUCTS = <?= json_encode($products) ?>;
        const DELIVERY_FEE = <?= (float)($store['store_delivery_fee'] ?? 0) ?>;
        
        let cart = {}; // { productId: { id, name, price, qty, notes } }
        let currentTable = "<?= esc($tableQuery) ?>";
        let currentOrderType = currentTable ? "dine_in" : "delivery";

        function formatMoney(num) {
            return CURRENCY_SYMBOL + ' ' + Number(num).toLocaleString('id-ID');
        }

        function setOrderType(type) {
            currentOrderType = type;
            document.querySelectorAll('.type-tab-btn').forEach(b => b.classList.remove('active'));

            document.getElementById('deliveryFieldsGroup').style.display = (type === 'delivery') ? 'block' : 'none';
            document.getElementById('takeawayFieldsGroup').style.display = (type === 'takeaway') ? 'block' : 'none';
            document.getElementById('dineInFieldsGroup').style.display = (type === 'dine_in') ? 'block' : 'none';
            document.getElementById('deliveryFeeRow').style.display = (type === 'delivery') ? 'flex' : 'none';

            if (type === 'delivery') {
                document.getElementById('typeTabDelivery').classList.add('active');
            } else if (type === 'takeaway') {
                document.getElementById('typeTabTakeaway').classList.add('active');
            } else {
                document.getElementById('typeTabDineIn').classList.add('active');
            }

            updateUI();
        }

        function togglePaymentInfo() {
            const method = document.getElementById('orderPaymentMethod').value;
            const box = document.getElementById('bankInfoBox');
            if (box) {
                box.style.display = (method === 'transfer' || method === 'qris') ? 'block' : 'none';
            }
        }

        function addToCart(productId) {
            const product = PRODUCTS.find(p => p.id == productId);
            if (!product) return;

            if (!cart[productId]) {
                cart[productId] = {
                    product_id: productId,
                    name: product.name,
                    price: parseFloat(product.selling_price),
                    qty: 1,
                    notes: ''
                };
            } else {
                cart[productId].qty += 1;
            }
            updateUI();
        }

        function changeQty(productId, delta) {
            if (!cart[productId]) return;
            cart[productId].qty += delta;
            if (cart[productId].qty <= 0) {
                delete cart[productId];
            }
            updateUI();
        }

        function updateItemNote(productId, noteText) {
            if (cart[productId]) {
                cart[productId].notes = noteText.trim();
            }
        }

        function updateUI() {
            // Update Card Action Buttons
            PRODUCTS.forEach(p => {
                const actionEl = document.getElementById('action-' + p.id);
                if (!actionEl) return;

                if (cart[p.id] && cart[p.id].qty > 0) {
                    actionEl.innerHTML = `
                        <div class="item-stepper">
                            <button class="btn-step-mini" onclick="changeQty(${p.id}, -1)">−</button>
                            <span class="step-qty-val">${cart[p.id].qty}</span>
                            <button class="btn-step-mini" onclick="changeQty(${p.id}, 1)">+</button>
                        </div>
                    `;
                } else {
                    actionEl.innerHTML = `
                        <button class="btn-add-item" onclick="addToCart(${p.id})">
                            + Tambah
                        </button>
                    `;
                }
            });

            // Calculate Totals
            let totalQty = 0;
            let subtotalPrice = 0;
            Object.values(cart).forEach(item => {
                totalQty += item.qty;
                subtotalPrice += (item.price * item.qty);
            });

            const currentDeliveryFee = (currentOrderType === 'delivery') ? DELIVERY_FEE : 0;
            const finalTotal = subtotalPrice + currentDeliveryFee;

            // Update Bottom Bar
            const bar = document.getElementById('bottomCartBar');
            if (totalQty > 0) {
                bar.style.display = 'flex';
                document.getElementById('cartTotalItems').textContent = totalQty + ' item';
                document.getElementById('cartTotalPrice').textContent = formatMoney(finalTotal);
            } else {
                bar.style.display = 'none';
                closeCartModal();
            }

            // Update Drawer
            document.getElementById('drawerTotalQty').textContent = totalQty;
            document.getElementById('drawerSubtotalAmount').textContent = formatMoney(subtotalPrice);
            document.getElementById('drawerTotalAmount').textContent = formatMoney(finalTotal);
            renderDrawerItems();
        }

        function renderDrawerItems() {
            const listEl = document.getElementById('cartItemList');
            if (!listEl) return;

            const items = Object.values(cart);
            if (items.length === 0) {
                listEl.innerHTML = '<div style="color:var(--text-muted);font-size:13px;text-align:center;padding:20px 0">Belum ada item dipilih</div>';
                return;
            }

            listEl.innerHTML = items.map(it => `
                <div class="cart-item-row">
                    <div style="flex:1">
                        <div class="cart-item-title">${escapeHtml(it.name)}</div>
                        <div style="font-size:12px;color:#FB923C;font-weight:700">${formatMoney(it.price)} x ${it.qty} = ${formatMoney(it.price * it.qty)}</div>
                        <input type="text" class="cart-item-note-input" placeholder="Catatan: misal pedas, less sugar..." value="${escapeHtml(it.notes || '')}" onchange="updateItemNote(${it.product_id}, this.value)">
                    </div>
                    <div class="item-stepper" style="margin-top:4px">
                        <button class="btn-step-mini" onclick="changeQty(${it.product_id}, -1)">−</button>
                        <span class="step-qty-val">${it.qty}</span>
                        <button class="btn-step-mini" onclick="changeQty(${it.product_id}, 1)">+</button>
                    </div>
                </div>
            `).join('');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function openCartModal() {
            document.getElementById('cartModal').classList.add('active');
            renderDrawerItems();
        }

        function closeCartModal() {
            document.getElementById('cartModal').classList.remove('active');
        }

        function openTableModal() {
            document.getElementById('tableModal').classList.add('active');
        }

        function closeTableModal() {
            document.getElementById('tableModal').classList.remove('active');
        }

        function saveTableFromModal() {
            const val = document.getElementById('inputTableModal').value.trim();
            if (val) {
                currentTable = val;
                document.getElementById('orderTableNo').value = val;
                document.getElementById('displayTableText').textContent = 'Meja ' + val;
                setOrderType('dine_in');
            }
            closeTableModal();
        }

        function filterCategory(catName, btn) {
            document.querySelectorAll('.cat-pill-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const blocks = document.querySelectorAll('.category-block');
            blocks.forEach(block => {
                if (catName === 'Semua' || block.getAttribute('data-category') === catName) {
                    block.style.display = 'block';
                } else {
                    block.style.display = 'none';
                }
            });
        }

        function handleSearch(query) {
            const q = query.toLowerCase().trim();
            const cards = document.querySelectorAll('.menu-card');
            cards.forEach(card => {
                const name = card.getAttribute('data-name').toLowerCase();
                if (!q || name.includes(q)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });

            // Check if any block is completely empty
            document.querySelectorAll('.category-block').forEach(block => {
                const visibleCards = block.querySelectorAll('.menu-card[style="display: flex;"], .menu-card:not([style*="display: none"])');
                block.style.display = visibleCards.length > 0 ? 'block' : 'none';
            });
        }

        // Initialize state on load
        if (currentTable) {
            setOrderType('dine_in');
        } else {
            setOrderType('delivery');
        }

        async function submitOrder() {
            const items = Object.values(cart);
            if (items.length === 0) {
                alert('Pilih minimal satu produk / menu untuk memesan.');
                return;
            }

            const customerName = document.getElementById('orderCustomerName').value.trim();
            const customerPhone = document.getElementById('orderCustomerPhone').value.trim();
            const deliveryAddress = document.getElementById('orderDeliveryAddress').value.trim();
            const deliveryNotes = document.getElementById('orderDeliveryNotes').value.trim();
            const pickupTime = document.getElementById('orderPickupTime').value.trim();
            const tableNo = document.getElementById('orderTableNo').value.trim();
            const paymentMethod = document.getElementById('orderPaymentMethod').value;

            if (currentOrderType === 'delivery') {
                if (!deliveryAddress) {
                    alert('Mohon masukkan Alamat Pengiriman Lengkap.');
                    document.getElementById('orderDeliveryAddress').focus();
                    return;
                }
                if (!customerPhone) {
                    alert('Mohon masukkan No. WhatsApp aktif agar kurir dapat mengonfirmasi pengiriman.');
                    document.getElementById('orderCustomerPhone').focus();
                    return;
                }
            } else if (currentOrderType === 'dine_in') {
                if (!tableNo) {
                    alert('Mohon masukkan Nomor Meja tempat Anda duduk.');
                    document.getElementById('orderTableNo').focus();
                    return;
                }
            } else if (currentOrderType === 'takeaway') {
                if (!customerName && !customerPhone) {
                    alert('Mohon masukkan Nama atau No. WhatsApp pemesan.');
                    document.getElementById('orderCustomerName').focus();
                    return;
                }
            }

            const btn = document.getElementById('btnSubmitOrder');
            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Mengirim Pesanan...';

            try {
                const res = await fetch('/menu/' + encodeURIComponent(STORE_SLUG) + '/order', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        order_type: currentOrderType,
                        customer_name: customerName,
                        customer_phone: customerPhone,
                        delivery_address: deliveryAddress,
                        delivery_notes: deliveryNotes,
                        pickup_time: pickupTime,
                        table_no: tableNo,
                        payment_method: paymentMethod,
                        items: items
                    })
                });

                const data = await res.json();
                if (data.success) {
                    // Redirect to status tracking page
                    window.location.href = data.status_url;
                } else {
                    alert('Gagal mengirim pesanan: ' + (data.message || 'Terjadi kesalahan'));
                    btn.disabled = false;
                    btn.innerHTML = '<span>🚀</span> Kirim Pesanan Sekarang';
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kendala jaringan saat mengirim pesanan. Silakan periksa koneksi Anda dan coba lagi.');
                btn.disabled = false;
                btn.innerHTML = '<span>🚀</span> Kirim Pesanan Sekarang';
            }
        }
    </script>
</body>
</html>
