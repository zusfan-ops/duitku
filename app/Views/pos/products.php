<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.pos-prod-page {
    padding: 12px 14px 110px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.pos-prod-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.pos-btn-add-prod {
    background: #EA580C;
    color: #fff;
    border: none;
    padding: 8px 14px;
    border-radius: 12px;
    font-size: 12.5px;
    font-weight: 800;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
}
.pos-prod-item {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}
.pos-prod-info { flex: 1; min-width: 0; }
.pos-prod-name { font-size: 13.5px; font-weight: 800; color: var(--text-primary); margin-bottom: 2px; }
.pos-prod-meta { font-size: 11.5px; color: var(--text-muted); display: flex; gap: 8px; flex-wrap: wrap; }
.pos-prod-price { font-size: 13.5px; font-weight: 800; color: #EA580C; }
.pos-prod-cost { font-size: 11px; color: var(--text-secondary); }
.pos-prod-stock-pill {
    padding: 3px 8px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 800;
    background: var(--primary-dim);
    color: var(--primary);
}
.pos-prod-stock-pill.low {
    background: #FEE2E2;
    color: #DC2626;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="pos-prod-page">

    <!-- Top Nav & Header -->
    <div class="pos-prod-header">
        <div style="display:flex;align-items:center;gap:8px">
            <a href="/pos" style="background:var(--bg-card);border:1px solid var(--border);color:var(--text-primary);padding:8px 12px;border-radius:10px;text-decoration:none;font-size:12px;font-weight:700">
                ← Kasir
            </a>
            <h2 style="font-size:18px;font-weight:800;margin:0">Katalog Produk</h2>
        </div>
        <button type="button" class="pos-btn-add-prod" id="btnOpenAddModal">
            + Tambah Menu
        </button>
    </div>

    <!-- Low Stock Alert Banner -->
    <?php if (!empty($lowStock)): ?>
    <div style="background:#FEF2F2;border:1px solid #FCA5A5;border-radius:14px;padding:12px;display:flex;align-items:center;gap:10px">
        <div style="font-size:22px">⚠️</div>
        <div style="flex:1;font-size:12px;color:#991B1B">
            <strong><?= count($lowStock) ?> Produk Stok Menipis!</strong><br>
            <span>Segera lakukan restock / kulakan untuk barang bertanda merah.</span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Product List -->
    <div style="display:flex;flex-direction:column;gap:8px" id="productList">
        <?php if (empty($products)): ?>
            <div style="text-align:center;padding:40px 10px;color:var(--text-muted)">
                <div style="font-size:40px;margin-bottom:8px">📦</div>
                <div style="font-weight:800;font-size:15px;color:var(--text-primary)">Belum ada data produk</div>
                <div style="font-size:12px">Tambahkan daftar menu atau stok barang toko Anda.</div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $p):
                $isLow = (int)$p['stock'] <= (int)$p['min_stock_alert'];
                $margin = (float)$p['selling_price'] - (float)$p['cost_price'];
                $marginPct = $p['cost_price'] > 0 ? round(($margin / (float)$p['cost_price']) * 100, 1) : 100;
            ?>
            <div class="pos-prod-item">
                <div class="pos-prod-info">
                    <div class="pos-prod-name"><?= esc($p['name']) ?></div>
                    <div class="pos-prod-meta">
                        <span>Kategori: <strong><?= esc($p['category']) ?></strong></span>
                        <span>•</span>
                        <span class="pos-prod-stock-pill <?= $isLow ? 'low' : '' ?>">
                            Stok: <?= $p['stock'] ?> <?= esc($p['unit']) ?> <?= $isLow ? '(Menipis!)' : '' ?>
                        </span>
                    </div>
                    <div style="margin-top:4px;display:flex;align-items:center;gap:10px">
                        <span class="pos-prod-price"><?= esc($symbol) ?> <?= number_format($p['selling_price'], 0, ',', '.') ?></span>
                        <span class="pos-prod-cost">HPP: <?= esc($symbol) ?> <?= number_format($p['cost_price'], 0, ',', '.') ?></span>
                        <span style="font-size:10.5px;font-weight:700;color:#059669">(Laba: +<?= $marginPct ?>%)</span>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px">
                    <button type="button" class="btn-restock" data-id="<?= $p['id'] ?>" data-name="<?= esc($p['name']) ?>" data-stock="<?= $p['stock'] ?>" style="background:var(--bg);border:1px solid var(--border);padding:6px 10px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer">
                        Restock
                    </button>
                    <button type="button" class="btn-edit-prod" data-json='<?= json_encode($p) ?>' style="background:var(--bg);border:1px solid var(--border);padding:6px 8px;border-radius:8px;font-size:12px;cursor:pointer">
                        ✏️
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<!-- ════════════════════════════ ADD/EDIT PRODUCT MODAL -->
<div class="modal-overlay" id="productModalOverlay">
    <div class="modal-sheet" id="productModal" style="max-height:90vh">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3 style="margin:0;font-size:16px;font-weight:800" id="prodModalTitle">Tambah Menu / Produk</h3>
            <button class="modal-close" id="productModalClose">✕</button>
        </div>
        <form id="productForm" style="padding:14px 16px 30px;overflow-y:auto;display:flex;flex-direction:column;gap:12px">
            <input type="hidden" id="prodId" name="id" value="0">

            <div class="form-group">
                <label class="form-label">NAMA PRODUK / MENU *</label>
                <input type="text" id="prodName" name="name" class="form-input" placeholder="Contoh: Kopi Susu Aren / Beras 5kg" required>
            </div>

            <div class="form-group">
                <label class="form-label">KATEGORI</label>
                <input type="text" id="prodCategory" name="category" class="form-input" placeholder="Contoh: Kopi / Minuman / Sembako" list="categorySuggestions">
                <datalist id="categorySuggestions">
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= esc($c) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div class="form-group">
                    <label class="form-label">HARGA JUAL *</label>
                    <input type="text" id="prodSellingPrice" name="selling_price" class="form-input" placeholder="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">HARGA MODAL (HPP)</label>
                    <input type="text" id="prodCostPrice" name="cost_price" class="form-input" placeholder="0">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div class="form-group">
                    <label class="form-label">JUMLAH STOK</label>
                    <input type="number" id="prodStock" name="stock" class="form-input" value="0" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">SATUAN</label>
                    <input type="text" id="prodUnit" name="unit" class="form-input" value="pcs" placeholder="pcs/cup/kg">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">BATAS PENGINGAT STOK MENIPIS</label>
                <input type="number" id="prodMinStock" name="min_stock_alert" class="form-input" value="5" min="1">
            </div>

            <div class="form-group">
                <label class="form-label">IKON PRODUK</label>
                <select id="prodIcon" name="icon" class="form-input">
                    <option value="coffee">☕ Kopi / Espresso</option>
                    <option value="tea">🍵 Teh / Matcha</option>
                    <option value="drink">🧃 Jus / Minuman Dingin</option>
                    <option value="food">🥐 Roti / Makanan Berat</option>
                    <option value="snack">🍟 Snack / Camilan</option>
                    <option value="groceries">🛒 Sembako / Toko</option>
                    <option value="rice">🌾 Beras / Gandum</option>
                    <option value="cigarette">🚬 Rokok / Tembakau</option>
                    <option value="box">📦 Umum / Barang Lain</option>
                </select>
            </div>

            <button type="submit" class="btn-save" style="background:#EA580C;margin-top:8px">
                Simpan Produk
            </button>
            <button type="button" id="btnDeleteProd" style="background:#DC2626;color:#fff;border:none;border-radius:12px;padding:10px;font-size:12px;font-weight:700;cursor:pointer;display:none">
                Hapus Produk Ini 🗑️
            </button>
        </form>
    </div>
</div>

<!-- ════════════════════════════ QUICK RESTOCK MODAL -->
<div class="modal-overlay" id="restockModalOverlay">
    <div class="modal-sheet" id="restockModal" style="max-height:60vh">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3 style="margin:0;font-size:16px;font-weight:800">📦 Update Stok / Kulakan</h3>
            <button class="modal-close" id="restockModalClose">✕</button>
        </div>
        <form id="restockForm" style="padding:14px 16px 30px;display:flex;flex-direction:column;gap:12px">
            <input type="hidden" id="restockProdId" name="product_id">
            <div style="font-size:14px;font-weight:800;color:var(--text-primary)" id="restockProdName"></div>
            
            <div class="form-group">
                <label class="form-label">TOTAL STOK TERBARU</label>
                <input type="number" id="restockStockVal" name="stock" class="form-input" style="font-size:18px;font-weight:800" min="0" required>
            </div>

            <button type="submit" class="btn-save" style="background:#059669">
                Perbarui Stok
            </button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const prodOverlay = document.getElementById('productModalOverlay');
    const restockOverlay = document.getElementById('restockModalOverlay');
    const prodForm = document.getElementById('productForm');
    const restockForm = document.getElementById('restockForm');
    const btnDelete = document.getElementById('btnDeleteProd');

    document.getElementById('btnOpenAddModal')?.addEventListener('click', () => {
        document.getElementById('prodModalTitle').textContent = 'Tambah Menu / Produk Baru';
        prodForm.reset();
        document.getElementById('prodId').value = '0';
        btnDelete.style.display = 'none';
        prodOverlay.classList.add('open');
    });

    document.getElementById('productModalClose')?.addEventListener('click', () => prodOverlay.classList.remove('open'));
    document.getElementById('restockModalClose')?.addEventListener('click', () => restockOverlay.classList.remove('open'));

    // Edit Product
    document.querySelectorAll('.btn-edit-prod').forEach(b => {
        b.addEventListener('click', () => {
            const p = JSON.parse(b.dataset.json);
            document.getElementById('prodModalTitle').textContent = 'Edit Menu / Produk';
            document.getElementById('prodId').value = p.id;
            document.getElementById('prodName').value = p.name;
            document.getElementById('prodCategory').value = p.category;
            document.getElementById('prodSellingPrice').value = Number(p.selling_price).toLocaleString('id-ID');
            document.getElementById('prodCostPrice').value = Number(p.cost_price).toLocaleString('id-ID');
            document.getElementById('prodStock').value = p.stock;
            document.getElementById('prodUnit').value = p.unit;
            document.getElementById('prodMinStock').value = p.min_stock_alert;
            document.getElementById('prodIcon').value = p.icon || 'box';
            btnDelete.style.display = 'block';
            prodOverlay.classList.add('open');
        });
    });

    // Restock
    document.querySelectorAll('.btn-restock').forEach(b => {
        b.addEventListener('click', () => {
            document.getElementById('restockProdId').value = b.dataset.id;
            document.getElementById('restockProdName').textContent = b.dataset.name;
            document.getElementById('restockStockVal').value = b.dataset.stock;
            restockOverlay.classList.add('open');
        });
    });

    // Save Product
    prodForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(prodForm);
        const res = await fetch('/pos/products/store', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': window.DUITKU.csrfToken },
            body: fd
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Gagal menyimpan produk.');
        }
    });

    // Restock Form
    restockForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(restockForm);
        const res = await fetch('/pos/products/adjust-stock', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': window.DUITKU.csrfToken },
            body: fd
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Gagal mengubah stok.');
        }
    });

    // Delete Product
    btnDelete?.addEventListener('click', async () => {
        if (!confirm('Yakin ingin menghapus produk ini?')) return;
        const id = document.getElementById('prodId').value;
        const res = await fetch('/pos/products/delete/' + id, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': window.DUITKU.csrfToken },
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Gagal menghapus produk.');
        }
    });
});
</script>
<?= $this->endSection() ?>
