<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.pos-page {
    padding: 12px 14px 110px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.pos-header-stats {
    background: linear-gradient(135deg, #EA580C 0%, #FB923C 100%);
    border-radius: 16px;
    padding: 14px 16px;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 6px 16px rgba(234, 88, 12, 0.25);
}
.pos-stat-col { display: flex; flex-direction: column; }
.pos-stat-label { font-size: 11px; font-weight: 700; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.5px; }
.pos-stat-val { font-size: 18px; font-weight: 800; line-height: 1.2; margin-top: 2px; }

.pos-search-bar {
    position: relative;
    width: 100%;
}
.pos-search-input {
    width: 100%;
    padding: 10px 12px 10px 36px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: 13px;
    outline: none;
}
.pos-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
    color: var(--text-muted);
}

.pos-category-scroll {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 4px;
    scrollbar-width: none;
}
.pos-category-scroll::-webkit-scrollbar { display: none; }
.pos-cat-pill {
    padding: 6px 14px;
    border-radius: 20px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    cursor: pointer;
    transition: all 0.15s ease;
}
.pos-cat-pill.active {
    background: #EA580C;
    border-color: #EA580C;
    color: #fff;
}

/* Product Grid */
.pos-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}
.pos-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 105px;
    position: relative;
    cursor: pointer;
    transition: transform 0.12s ease, border-color 0.12s ease;
    -webkit-user-select: none;
    user-select: none;
}
.pos-card:active { transform: scale(0.96); }
.pos-card.in-cart { border-color: #EA580C; background: var(--primary-dim); }

.pos-card-icon { font-size: 22px; margin-bottom: 4px; }
.pos-card-title {
    font-size: 13px;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.25;
    margin-bottom: 2px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.pos-card-price {
    font-size: 13px;
    font-weight: 800;
    color: #EA580C;
    margin-top: 4px;
}
.pos-card-stock {
    font-size: 10.5px;
    font-weight: 600;
    color: var(--text-muted);
}
.pos-card-stock.low { color: #DC2626; font-weight: 700; }

.pos-card-qty-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #EA580C;
    color: #fff;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 900;
    border: 2px solid var(--bg-card);
}

/* Sticky Bottom Cart Bar */
.pos-cart-bar {
    position: fixed;
    bottom: 65px;
    left: 50%;
    transform: translateX(-50%);
    width: calc(100% - 24px);
    max-width: 480px;
    background: #18181B;
    color: #fff;
    border-radius: 16px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    z-index: 99;
    cursor: pointer;
    animation: slideUp 0.2s ease-out;
}
.pos-cart-info { display: flex; align-items: center; gap: 10px; }
.pos-cart-badge {
    background: #EA580C;
    color: #fff;
    padding: 4px 8px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 800;
}
.pos-cart-total { font-size: 14px; font-weight: 800; color: #fff; }
.pos-btn-pay {
    background: #10B981;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Cart & Checkout Items */
.pos-order-item-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
}
.pos-stepper {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--bg);
    padding: 4px 8px;
    border-radius: 8px;
    border: 1px solid var(--border);
}
.pos-btn-step {
    background: transparent;
    border: none;
    font-size: 15px;
    font-weight: 800;
    color: var(--text-primary);
    width: 20px;
    height: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Quick Pay Money Presets */
.pos-preset-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
    margin: 8px 0 12px;
}
.pos-btn-preset {
    padding: 8px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text-primary);
    font-size: 11.5px;
    font-weight: 700;
    cursor: pointer;
    text-align: center;
}
.pos-btn-preset:active { background: var(--primary-dim); }

/* Thermal Receipt Styling */
.thermal-receipt {
    background: #fff;
    color: #000;
    font-family: 'Courier New', Courier, monospace;
    padding: 16px;
    border-radius: 12px;
    border: 1px dashed #ccc;
    font-size: 12px;
    line-height: 1.4;
}
.thermal-divider { border-bottom: 1px dashed #000; margin: 8px 0; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="pos-page">

    <!-- Top Shift Summary -->
    <div class="pos-header-stats">
        <div class="pos-stat-col">
            <span class="pos-stat-label">Omset Hari Ini</span>
            <span class="pos-stat-val"><?= esc($symbol) ?> <?= number_format($summary['total_sales'], 0, ',', '.') ?></span>
        </div>
        <div style="text-align:right">
            <span class="pos-stat-label">Laba Bersih</span>
            <span class="pos-stat-val" style="color:#FEF08A"><?= esc($symbol) ?> <?= number_format($summary['total_profit'], 0, ',', '.') ?></span>
            <div style="font-size:10.5px;opacity:0.9;margin-top:1px"><?= $summary['total_orders'] ?> Transaksi</div>
        </div>
    </div>

    <!-- Actions Row -->
    <div style="display:flex;align-items:center;justify-content:space-between;gap:6px;flex-wrap:wrap">
        <div class="pos-search-bar" style="flex:1;min-width:140px">
            <span class="pos-search-icon">🔍</span>
            <input type="text" id="posSearchInput" class="pos-search-input" placeholder="Cari menu...">
        </div>
        <a href="/pos/orders" style="background:linear-gradient(135deg, #EA580C 0%, #FB923C 100%);color:#fff;padding:9px 12px;border-radius:12px;font-size:12px;font-weight:800;text-decoration:none;display:flex;align-items:center;gap:4px;white-space:nowrap;box-shadow:0 3px 8px rgba(234,88,12,0.3)">
            📋 Pesanan
        </a>
        <a href="/pos/qr" style="background:var(--bg-card);border:1px solid var(--border);color:var(--text-primary);padding:9px 12px;border-radius:12px;font-size:12px;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:4px;white-space:nowrap">
            📱 QR Menu
        </a>
        <a href="/pos/products" style="background:var(--bg-card);border:1px solid var(--border);color:var(--text-primary);padding:9px 12px;border-radius:12px;font-size:12px;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:4px;white-space:nowrap">
            📦 Produk
        </a>
        <a href="/pos/reports" style="background:var(--bg-card);border:1px solid var(--border);color:var(--text-primary);padding:9px 12px;border-radius:12px;font-size:12px;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:4px;white-space:nowrap">
            📊 Laba
        </a>
    </div>

    <!-- Category Filter Scroll -->
    <div class="pos-category-scroll" id="posCatScroll">
        <?php foreach ($categories as $idx => $cat): ?>
            <button class="pos-cat-pill <?= $idx === 0 ? 'active' : '' ?>" data-category="<?= esc($cat) ?>">
                <?= esc($cat) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Product Grid (2 columns) -->
    <div class="pos-grid" id="posProductGrid">
        <?php if (empty($products)): ?>
            <div style="grid-column: span 2; text-align:center; padding: 40px 10px; color:var(--text-muted)">
                <div style="font-size:40px;margin-bottom:8px">☕</div>
                <div style="font-weight:800;font-size:15px;color:var(--text-primary);margin-bottom:4px">Belum Ada Produk</div>
                <div style="font-size:12px;margin-bottom:14px">Tambahkan menu kopi atau barang dagangan toko Anda.</div>
                <a href="/pos/products" style="background:#EA580C;color:#fff;padding:8px 16px;border-radius:10px;font-size:12px;font-weight:700;text-decoration:none">
                    + Tambah Produk Baru
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($products as $p): 
                $iconMap = [
                    'coffee' => '☕', 'tea' => '🍵', 'drink' => '🧃',
                    'food' => '🥐', 'snack' => '🍟', 'box' => '📦',
                    'groceries' => '🛒', 'rice' => '🌾', 'cigarette' => '🚬'
                ];
                $icon = $iconMap[$p['icon'] ?? 'box'] ?? '📦';
            ?>
            <div class="pos-card" data-id="<?= $p['id'] ?>" data-name="<?= esc($p['name']) ?>" data-price="<?= (float)$p['selling_price'] ?>" data-cost="<?= (float)$p['cost_price'] ?>" data-stock="<?= (int)$p['stock'] ?>" data-category="<?= esc($p['category']) ?>">
                <div class="pos-card-icon"><?= $icon ?></div>
                <div class="pos-card-title"><?= esc($p['name']) ?></div>
                <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-top:auto">
                    <div>
                        <div class="pos-card-price"><?= esc($symbol) ?> <?= number_format($p['selling_price'], 0, ',', '.') ?></div>
                        <div class="pos-card-stock <?= $p['stock'] <= $p['min_stock_alert'] ? 'low' : '' ?>">
                            Stok: <?= $p['stock'] ?> <?= esc($p['unit']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Floating Bottom Sticky Cart Bar -->
<div class="pos-cart-bar" id="posCartBar" style="display:none">
    <div class="pos-cart-info" id="btnOpenCartSheet">
        <span class="pos-cart-badge" id="cartItemCount">0</span>
        <span class="pos-cart-total" id="cartTotalDisplay"><?= esc($symbol) ?> 0</span>
    </div>
    <button class="pos-btn-pay" id="btnProceedCheckout">
        Bayar →
    </button>
</div>

<!-- ════════════════════════════ CART REVIEW BOTTOM SHEET -->
<div class="modal-overlay" id="cartModalOverlay">
    <div class="modal-sheet" id="cartModal" style="max-height:85vh">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3 style="margin:0;font-size:16px;font-weight:800">🛒 Keranjang Pesanan</h3>
            <button class="modal-close" id="cartModalClose">✕</button>
        </div>
        <div style="padding:14px 16px 30px;overflow-y:auto;display:flex;flex-direction:column;gap:12px">
            <div id="cartItemList" style="display:flex;flex-direction:column"></div>

            <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:800;border-top:1px solid var(--border);padding-top:12px">
                <span>Total Tagihan:</span>
                <span id="cartSheetTotal" style="color:#EA580C"><?= esc($symbol) ?> 0</span>
            </div>

            <button type="button" class="btn-save" id="btnCartToCheckout" style="background:#10B981;margin-top:10px">
                Lanjut ke Pembayaran 💳
            </button>
        </div>
    </div>
</div>

<!-- ════════════════════════════ CHECKOUT SHEET -->
<div class="modal-overlay" id="checkoutModalOverlay">
    <div class="modal-sheet" id="checkoutModal" style="max-height:90vh">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3 style="margin:0;font-size:16px;font-weight:800">💳 Proses Pembayaran</h3>
            <button class="modal-close" id="checkoutModalClose">✕</button>
        </div>
        <form id="posCheckoutForm" style="padding:14px 16px 30px;overflow-y:auto;display:flex;flex-direction:column;gap:14px">
            <div style="background:var(--bg);border-radius:14px;padding:12px;text-align:center">
                <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase">Total Pembayaran</div>
                <div style="font-size:24px;font-weight:900;color:#EA580C;margin-top:2px" id="checkoutTotalAmount"><?= esc($symbol) ?> 0</div>
            </div>

            <!-- Payment Method Pills -->
            <div class="form-group">
                <label class="form-label">METODE PEMBAYARAN</label>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px">
                    <button type="button" class="pos-btn-preset active" data-method="cash" style="background:#EA580C;color:#fff;border-color:#EA580C">
                        💵 Tunai / Cash
                    </button>
                    <button type="button" class="pos-btn-preset" data-method="qris">
                        📱 QRIS
                    </button>
                    <button type="button" class="pos-btn-preset" data-method="transfer">
                        💳 Transfer Bank
                    </button>
                    <button type="button" class="pos-btn-preset" data-method="kasbon" style="color:#DC2626">
                        📒 Kasbon / Hutang
                    </button>
                </div>
                <input type="hidden" id="payMethod" name="payment_method" value="cash">
            </div>

            <!-- Cash Input & Presets (Only if Cash) -->
            <div id="cashInputSection">
                <label class="form-label">UANG DITERIMA</label>
                <input type="text" id="cashReceivedInput" class="form-input" placeholder="Masukkan jumlah uang..." style="font-size:16px;font-weight:800">
                <div class="pos-preset-grid" id="cashPresetGrid">
                    <button type="button" class="pos-btn-preset" id="presetPas">Uang Pas</button>
                    <button type="button" class="pos-btn-preset" data-val="10000">10.000</button>
                    <button type="button" class="pos-btn-preset" data-val="20000">20.000</button>
                    <button type="button" class="pos-btn-preset" data-val="50000">50.000</button>
                    <button type="button" class="pos-btn-preset" data-val="100000">100.000</button>
                    <button type="button" class="pos-btn-preset" data-val="200000">200.000</button>
                </div>

                <!-- Kembalian Display -->
                <div style="background:var(--primary-dim);border:1px solid var(--primary);border-radius:12px;padding:10px 14px;display:flex;align-items:center;justify-content:space-between">
                    <span style="font-size:12px;font-weight:700;color:var(--text-primary)">Kembalian:</span>
                    <span id="changeDisplay" style="font-size:18px;font-weight:900;color:#059669"><?= esc($symbol) ?> 0</span>
                </div>
            </div>

            <!-- Wallet Selector (For Cash, QRIS, Transfer) -->
            <div class="form-group" id="walletSelectSection">
                <label class="form-label">MASUK KE REKENING</label>
                <select id="posWallet" class="form-input">
                    <?php foreach ($wallets as $w): ?>
                        <option value="<?= $w['id'] ?>"><?= esc($w['name']) ?> (<?= esc($symbol) ?> <?= number_format($w['balance'], 0, ',', '.') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Customer Info (Required if Kasbon / Optional for Receipt) -->
            <div class="form-group">
                <label class="form-label">NAMA PELANGGAN (OPSIONAL / WAJIB KASBON)</label>
                <input type="text" id="customerName" class="form-input" placeholder="Contoh: Mas Budi / Meja 04">
            </div>
            <div class="form-group">
                <label class="form-label">NO. WHATSAPP (UNTUK KIRIM STRUK / TAGIHAN KASBON)</label>
                <input type="tel" id="customerPhone" class="form-input" placeholder="Contoh: 08123456789">
            </div>

            <button type="submit" class="btn-save" id="btnFinishCheckout" style="background:#10B981">
                Selesaikan Transaksi & Cetak Struk 🖨️
            </button>
        </form>
    </div>
</div>

<!-- ════════════════════════════ RECEIPT SHEET -->
<div class="modal-overlay" id="receiptModalOverlay">
    <div class="modal-sheet" id="receiptModal" style="max-height:90vh">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3 style="margin:0;font-size:16px;font-weight:800">🧾 Struk Transaksi</h3>
            <button class="modal-close" id="receiptModalClose">✕</button>
        </div>
        <div style="padding:14px 16px 30px;overflow-y:auto;display:flex;flex-direction:column;gap:14px">
            <!-- Thermal Style Receipt Preview -->
            <div class="thermal-receipt" id="printableReceipt">
                <div style="text-align:center">
                    <strong style="font-size:14px"><?= esc(session()->get('user_name')) ?></strong><br>
                    <small>DuitKu Mini POS & Usaha</small><br>
                    <small id="recOrderDate"></small>
                </div>
                <div class="thermal-divider"></div>
                <div style="display:flex;justify-content:space-between;font-size:11px">
                    <span id="recOrderNum"></span>
                    <span id="recPayMethod"></span>
                </div>
                <div id="recCustomerRow" style="font-size:11px;display:none">
                    Pelanggan: <span id="recCustomerName"></span>
                </div>
                <div class="thermal-divider"></div>
                <div id="recItemList"></div>
                <div class="thermal-divider"></div>
                <div style="display:flex;justify-content:space-between">
                    <strong>TOTAL:</strong>
                    <strong id="recTotal"></strong>
                </div>
                <div id="recCashRow" style="display:flex;justify-content:space-between;font-size:11px">
                    <span>Bayar:</span>
                    <span id="recCash"></span>
                </div>
                <div id="recChangeRow" style="display:flex;justify-content:space-between;font-size:11px">
                    <span>Kembali:</span>
                    <span id="recChange"></span>
                </div>
                <div class="thermal-divider"></div>
                <div style="text-align:center;font-size:10.5px">
                    Terima kasih atas kunjungan Anda!<br>
                    Simpan struk ini sebagai bukti pembayaran.
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display:flex;flex-direction:column;gap:8px">
                <button type="button" class="btn-save" id="btnShareWA" style="background:#25D366;color:#fff">
                    💬 Kirim Struk via WhatsApp
                </button>
                <div style="display:flex;gap:8px">
                    <button type="button" class="btn-save" id="btnPrintReceipt" style="background:#4B5563;flex:1">
                        🖨️ Cetak Struk
                    </button>
                    <button type="button" class="btn-save" id="btnNewOrder" style="background:#EA580C;flex:1">
                        ➕ Pesanan Baru
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    let cart = {}; // { productId: { name, price, cost, qty, stock } }
    let lastOrderData = null;
    const symbol = '<?= esc($symbol) ?>';

    const productCards = document.querySelectorAll('.pos-card');
    const cartBar = document.getElementById('posCartBar');
    const cartItemCount = document.getElementById('cartItemCount');
    const cartTotalDisplay = document.getElementById('cartTotalDisplay');

    // Category Filter
    document.querySelectorAll('.pos-cat-pill').forEach(pill => {
        pill.addEventListener('click', () => {
            document.querySelectorAll('.pos-cat-pill').forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            const cat = pill.dataset.category;
            productCards.forEach(card => {
                if (cat === 'Semua' || card.dataset.category === cat) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Search filter
    document.getElementById('posSearchInput')?.addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase();
        productCards.forEach(card => {
            const name = card.dataset.name.toLowerCase();
            card.style.display = name.includes(q) ? 'flex' : 'none';
        });
    });

    // Tap Product Card to add to cart
    productCards.forEach(card => {
        card.addEventListener('click', () => {
            const id = card.dataset.id;
            const name = card.dataset.name;
            const price = parseFloat(card.dataset.price);
            const cost = parseFloat(card.dataset.cost);
            const stock = parseInt(card.dataset.stock);

            if (cart[id]) {
                cart[id].qty += 1;
            } else {
                cart[id] = { id, name, price, cost, qty: 1, stock };
            }
            updateCartUI();
        });
    });

    function updateCartUI() {
        let totalItems = 0;
        let totalAmount = 0;

        productCards.forEach(card => {
            const id = card.dataset.id;
            const badge = card.querySelector('.pos-card-qty-badge');
            if (cart[id] && cart[id].qty > 0) {
                card.classList.add('in-cart');
                if (!badge) {
                    const b = document.createElement('div');
                    b.className = 'pos-card-qty-badge';
                    b.textContent = cart[id].qty;
                    card.appendChild(b);
                } else {
                    badge.textContent = cart[id].qty;
                }
            } else {
                card.classList.remove('in-cart');
                if (badge) badge.remove();
            }
        });

        Object.values(cart).forEach(item => {
            totalItems += item.qty;
            totalAmount += (item.price * item.qty);
        });

        if (totalItems > 0) {
            cartBar.style.display = 'flex';
            cartItemCount.textContent = totalItems;
            cartTotalDisplay.textContent = symbol + ' ' + totalAmount.toLocaleString('id-ID');
        } else {
            cartBar.style.display = 'none';
        }
    }

    // Open Cart Sheet
    const cartOverlay = document.getElementById('cartModalOverlay');
    const checkoutOverlay = document.getElementById('checkoutModalOverlay');
    const receiptOverlay = document.getElementById('receiptModalOverlay');

    document.getElementById('btnOpenCartSheet')?.addEventListener('click', openCartSheet);
    document.getElementById('btnProceedCheckout')?.addEventListener('click', openCheckoutSheet);
    document.getElementById('cartModalClose')?.addEventListener('click', () => cartOverlay.classList.remove('open'));
    document.getElementById('checkoutModalClose')?.addEventListener('click', () => checkoutOverlay.classList.remove('open'));
    document.getElementById('receiptModalClose')?.addEventListener('click', () => receiptOverlay.classList.remove('open'));

    function openCartSheet() {
        renderCartItems();
        cartOverlay.classList.add('open');
    }

    function renderCartItems() {
        const list = document.getElementById('cartItemList');
        list.innerHTML = '';
        let total = 0;

        Object.values(cart).forEach(item => {
            const subtotal = item.price * item.qty;
            total += subtotal;
            const row = document.createElement('div');
            row.className = 'pos-order-item-row';
            row.innerHTML = `
                <div style="flex:1">
                    <strong style="font-size:13px">${item.name}</strong>
                    <div style="font-size:11.5px;color:var(--text-muted)">${symbol} ${item.price.toLocaleString('id-ID')} x ${item.qty}</div>
                </div>
                <div style="display:flex;align-items:center;gap:10px">
                    <div class="pos-stepper">
                        <button type="button" class="pos-btn-step btn-minus" data-id="${item.id}">-</button>
                        <span style="font-size:12px;font-weight:800;min-width:14px;text-align:center">${item.qty}</span>
                        <button type="button" class="pos-btn-step btn-plus" data-id="${item.id}">+</button>
                    </div>
                    <strong style="font-size:13px;min-width:70px;text-align:right">${symbol} ${subtotal.toLocaleString('id-ID')}</strong>
                </div>
            `;
            list.appendChild(row);
        });

        document.getElementById('cartSheetTotal').textContent = symbol + ' ' + total.toLocaleString('id-ID');

        list.querySelectorAll('.btn-minus').forEach(b => {
            b.addEventListener('click', () => {
                const id = b.dataset.id;
                if (cart[id]) {
                    cart[id].qty -= 1;
                    if (cart[id].qty <= 0) delete cart[id];
                    updateCartUI();
                    renderCartItems();
                    if (Object.keys(cart).length === 0) cartOverlay.classList.remove('open');
                }
            });
        });

        list.querySelectorAll('.btn-plus').forEach(b => {
            b.addEventListener('click', () => {
                const id = b.dataset.id;
                if (cart[id]) {
                    cart[id].qty += 1;
                    updateCartUI();
                    renderCartItems();
                }
            });
        });
    }

    document.getElementById('btnCartToCheckout')?.addEventListener('click', () => {
        cartOverlay.classList.remove('open');
        openCheckoutSheet();
    });

    let currentTotal = 0;
    function openCheckoutSheet() {
        currentTotal = Object.values(cart).reduce((sum, item) => sum + (item.price * item.qty), 0);
        if (currentTotal <= 0) return;

        document.getElementById('checkoutTotalAmount').textContent = symbol + ' ' + currentTotal.toLocaleString('id-ID');
        document.getElementById('cashReceivedInput').value = currentTotal.toLocaleString('id-ID');
        calcChange();
        checkoutOverlay.classList.add('open');
    }

    // Payment Method Selector
    const payMethodInput = document.getElementById('payMethod');
    const cashSection = document.getElementById('cashInputSection');
    const walletSection = document.getElementById('walletSelectSection');

    document.querySelectorAll('[data-method]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('[data-method]').forEach(b => {
                b.classList.remove('active');
                b.style.background = 'var(--bg)';
                b.style.color = 'var(--text-primary)';
                b.style.borderColor = 'var(--border)';
            });
            btn.classList.add('active');
            btn.style.background = '#EA580C';
            btn.style.color = '#fff';
            btn.style.borderColor = '#EA580C';

            const m = btn.dataset.method;
            payMethodInput.value = m;

            if (m === 'cash') {
                cashSection.style.display = 'block';
                walletSection.style.display = 'block';
            } else if (m === 'kasbon') {
                cashSection.style.display = 'none';
                walletSection.style.display = 'none';
            } else {
                cashSection.style.display = 'none';
                walletSection.style.display = 'block';
            }
        });
    });

    // Cash presets & calculation
    const cashInput = document.getElementById('cashReceivedInput');
    const changeDisplay = document.getElementById('changeDisplay');

    function calcChange() {
        const raw = parseFloat(cashInput.value.replace(/\./g, '').replace(/,/g, '.')) || 0;
        const diff = raw - currentTotal;
        changeDisplay.textContent = symbol + ' ' + (diff > 0 ? diff.toLocaleString('id-ID') : '0');
    }

    cashInput.addEventListener('input', () => {
        let val = cashInput.value.replace(/\D/g, '');
        if (val) cashInput.value = parseInt(val, 10).toLocaleString('id-ID');
        calcChange();
    });

    document.getElementById('presetPas')?.addEventListener('click', () => {
        cashInput.value = currentTotal.toLocaleString('id-ID');
        calcChange();
    });

    document.querySelectorAll('[data-val]').forEach(btn => {
        btn.addEventListener('click', () => {
            const v = parseInt(btn.dataset.val);
            cashInput.value = v.toLocaleString('id-ID');
            calcChange();
        });
    });

    // Submit Checkout
    document.getElementById('posCheckoutForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const items = Object.values(cart).map(i => ({
            product_id: parseInt(i.id),
            name: i.name,
            price: i.price,
            cost_price: i.cost,
            qty: i.qty,
        }));

        const rawCash = parseFloat(cashInput.value.replace(/\./g, '').replace(/,/g, '.')) || 0;
        const payload = {
            items: items,
            payment_method: payMethodInput.value,
            wallet_id: parseInt(document.getElementById('posWallet')?.value || '0'),
            cash_received: rawCash,
            customer_name: document.getElementById('customerName')?.value || '',
            customer_phone: document.getElementById('customerPhone')?.value || '',
        };

        const res = await fetch('/pos/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': window.DUITKU.csrfToken,
            },
            body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.success && data.order) {
            checkoutOverlay.classList.remove('open');
            lastOrderData = data.order;
            cart = {};
            updateCartUI();
            showReceipt(data.order);
        } else {
            alert(data.message || 'Gagal memproses transaksi.');
        }
    });

    function showReceipt(order) {
        document.getElementById('recOrderDate').textContent = order.date + ' ' + (order.created_at ? order.created_at.substring(11, 16) : '');
        document.getElementById('recOrderNum').textContent = '#' + order.order_number;
        document.getElementById('recPayMethod').textContent = order.payment_method.toUpperCase();

        if (order.customer_name) {
            document.getElementById('recCustomerRow').style.display = 'block';
            document.getElementById('recCustomerName').textContent = order.customer_name;
        } else {
            document.getElementById('recCustomerRow').style.display = 'none';
        }

        const itemsEl = document.getElementById('recItemList');
        itemsEl.innerHTML = '';
        (order.items || []).forEach(it => {
            const row = document.createElement('div');
            row.style.display = 'flex';
            row.style.justifyContent = 'space-between';
            row.style.marginBottom = '3px';
            row.innerHTML = `
                <span>${it.product_name} x${it.qty}</span>
                <span>${symbol} ${Number(it.subtotal).toLocaleString('id-ID')}</span>
            `;
            itemsEl.appendChild(row);
        });

        document.getElementById('recTotal').textContent = symbol + ' ' + Number(order.total_amount).toLocaleString('id-ID');
        if (order.payment_method === 'cash') {
            document.getElementById('recCashRow').style.display = 'flex';
            document.getElementById('recChangeRow').style.display = 'flex';
            document.getElementById('recCash').textContent = symbol + ' ' + Number(order.cash_received).toLocaleString('id-ID');
            document.getElementById('recChange').textContent = symbol + ' ' + Number(order.change_amount).toLocaleString('id-ID');
        } else {
            document.getElementById('recCashRow').style.display = 'none';
            document.getElementById('recChangeRow').style.display = 'none';
        }

        receiptOverlay.classList.add('open');
    }

    // Share via WhatsApp
    document.getElementById('btnShareWA')?.addEventListener('click', () => {
        if (!lastOrderData) return;
        let text = `*STRUK PEMBAYARAN - <?= esc(session()->get('user_name')) ?>*\n`;
        text += `No: #${lastOrderData.order_number}\n`;
        text += `Tgl: ${lastOrderData.date}\n`;
        if (lastOrderData.customer_name) text += `Pelanggan: ${lastOrderData.customer_name}\n`;
        text += `Metode: ${lastOrderData.payment_method.toUpperCase()}\n`;
        text += `--------------------------------\n`;
        (lastOrderData.items || []).forEach(it => {
            text += `${it.product_name} x${it.qty} = ${symbol} ${Number(it.subtotal).toLocaleString('id-ID')}\n`;
        });
        text += `--------------------------------\n`;
        text += `*TOTAL: ${symbol} ${Number(lastOrderData.total_amount).toLocaleString('id-ID')}*\n`;
        if (lastOrderData.payment_method === 'cash') {
            text += `Bayar: ${symbol} ${Number(lastOrderData.cash_received).toLocaleString('id-ID')}\n`;
            text += `Kembali: ${symbol} ${Number(lastOrderData.change_amount).toLocaleString('id-ID')}\n`;
        }
        text += `\nTerima kasih telah berbelanja! 🙏`;

        const phone = lastOrderData.customer_phone ? lastOrderData.customer_phone.replace(/\D/g, '') : '';
        const waUrl = phone 
            ? `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(text)}`
            : `https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`;
        window.open(waUrl, '_blank');
    });

    // Print receipt
    document.getElementById('btnPrintReceipt')?.addEventListener('click', () => {
        window.print();
    });

    // New order
    document.getElementById('btnNewOrder')?.addEventListener('click', () => {
        receiptOverlay.classList.remove('open');
        window.location.reload();
    });
});
</script>
<?= $this->endSection() ?>
