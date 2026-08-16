<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="/pos" class="text-decoration-none">Kasir POS</a></li>
                    <li class="breadcrumb-item active">Stok Bahan Baku (BOM)</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold mb-0">📦 Manajemen Bahan Baku & Resep Menu</h1>
            <p class="text-muted small mb-0">Pantau stok bahan mentah, resep produk (Bill of Materials), dan otomatisasi pengurangan saat order terjual.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalRecipe">
                <i class="bi bi-journal-plus me-1"></i> Atur Resep Menu
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalIngredient">
                <i class="bi bi-plus-lg me-1"></i> Tambah Bahan Baku
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle me-3">
                        <i class="bi bi-boxes fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Bahan Baku</div>
                        <div class="fs-4 fw-bold"><?= count($ingredients) ?> Item</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 <?= $lowStockCount > 0 ? 'border-danger border-2' : '' ?>">
                <div class="d-flex align-items-center">
                    <div class="<?= $lowStockCount > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?> p-3 rounded-circle me-3">
                        <i class="bi bi-exclamation-triangle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Stok Menipis (Kritis)</div>
                        <div class="fs-4 fw-bold <?= $lowStockCount > 0 ? 'text-danger' : 'text-success' ?>"><?= $lowStockCount ?> Item</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-warning-subtle text-warning p-3 rounded-circle me-3">
                        <i class="bi bi-egg-fried fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Menu Produk</div>
                        <div class="fs-4 fw-bold"><?= count($products) ?> Menu</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-info-subtle text-info p-3 rounded-circle me-3">
                        <i class="bi bi-receipt fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Auto Deduct BOM</div>
                        <div class="fs-5 fw-bold text-success">Aktif Otomatis</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Daftar Bahan Mentah</h5>
            <input type="text" id="searchTable" class="form-control form-control-sm w-auto" placeholder="Cari bahan baku...">
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="ingredientTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nama Bahan Baku</th>
                        <th>Satuan</th>
                        <th>Sisa Stok Fisik</th>
                        <th>Batas Min. Stok</th>
                        <th>Biaya Pokok (HPP/Satuan)</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ingredients)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                Belum ada data bahan baku. Klik <strong>Tambah Bahan Baku</strong> untuk mulai mencatat (cth: Biji Kopi, Susu, Sirup, Cup).
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ingredients as $ing): ?>
                            <tr>
                                <td class="ps-4 fw-bold">
                                    <?= esc($ing['name']) ?>
                                </td>
                                <td><span class="badge bg-secondary-subtle text-dark"><?= esc($ing['unit']) ?></span></td>
                                <td>
                                    <span class="fs-6 fw-bold <?= $ing['is_low_stock'] ? 'text-danger' : 'text-dark' ?>">
                                        <?= number_format((float)$ing['stock'], 2, ',', '.') ?>
                                    </span>
                                    <small class="text-muted"><?= esc($ing['unit']) ?></small>
                                </td>
                                <td>
                                    <?= number_format((float)$ing['min_stock'], 2, ',', '.') ?> <?= esc($ing['unit']) ?>
                                </td>
                                <td>
                                    Rp <?= number_format((float)$ing['cost_per_unit'], 0, ',', '.') ?> / <?= esc($ing['unit']) ?>
                                </td>
                                <td>
                                    <?php if ($ing['is_low_stock']): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                            <i class="bi bi-exclamation-circle me-1"></i> Menipis
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-check-circle me-1"></i> Aman
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-success me-1" onclick="openRestock(<?= htmlspecialchars(json_encode($ing)) ?>)" title="Restock Bahan">
                                        <i class="bi bi-plus-circle me-1"></i> Restock
                                    </button>
                                    <button class="btn btn-sm btn-light me-1" onclick="openEdit(<?= htmlspecialchars(json_encode($ing)) ?>)" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light text-danger" onclick="deleteIng(<?= $ing['id'] ?>, '<?= esc($ing['name']) ?>')" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add/Edit Ingredient -->
<div class="modal fade" id="modalIngredient" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="ingModalTitle">Tambah Bahan Baku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formIngredient">
                <input type="hidden" name="id" id="ingId" value="0">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Bahan Baku <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="ingName" placeholder="Contoh: Biji Kopi Espresso / Susu UHT / Cup 16oz" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Satuan Ukur <span class="text-danger">*</span></label>
                            <select class="form-select" name="unit" id="ingUnit">
                                <option value="gram">Gram (g)</option>
                                <option value="ml">Mililiter (ml)</option>
                                <option value="pcs">Pcs / Lembar</option>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="liter">Liter (L)</option>
                                <option value="sachet">Sachet / Porsi</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Stok Awal Fisik</label>
                            <input type="number" step="any" class="form-control" name="stock" id="ingStock" value="0" required>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Batas Minimum Alert</label>
                            <input type="number" step="any" class="form-control" name="min_stock" id="ingMinStock" value="10" required>
                            <div class="form-text small">Peringatan saat stok $\le$ batas ini.</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Biaya per Satuan (HPP)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="any" class="form-control" name="cost_per_unit" id="ingCost" value="0">
                            </div>
                            <div class="form-text small">Cth: Rp 150 / gram kopi.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveIng">Simpan Bahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Restock -->
<div class="modal fade" id="modalRestock" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Restock Bahan Baku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formRestock">
                <input type="hidden" name="id" id="restockId">
                <div class="modal-body">
                    <div class="alert alert-primary bg-primary-subtle border-0 rounded-3 mb-3">
                        <div class="fw-bold" id="restockName">Biji Kopi</div>
                        <div class="small text-muted">Stok saat ini: <strong id="restockCurrentStock">0</strong> <span id="restockUnit">gram</span></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Jumlah Tambahan Stok Masuk <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="any" class="form-control" name="add_stock" id="restockAdd" placeholder="0" required>
                            <span class="input-group-text" id="restockUnitLabel">gram</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Biaya per Satuan Baru (Opsional)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" step="any" class="form-control" name="cost_per_unit" id="restockCost" placeholder="Kosongkan jika tidak berubah">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Konfirmasi Masuk Stok</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Recipe Composer -->
<div class="modal fade" id="modalRecipe" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">🍳 Komposisi Resep Menu (Bill of Materials)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Pilih Produk Menu:</label>
                    <select class="form-select form-select-lg" id="recipeProductSelect" onchange="loadRecipeForProduct(this.value)">
                        <option value="">-- Pilih Menu POS --</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>">
                                <?= esc($p['name']) ?> (Harga Jual: Rp <?= number_format((float)$p['selling_price'], 0, ',', '.') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="recipeBuilderArea" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold small text-muted">KOMPOSISI BAHAN BAKU PER 1 PORSI</span>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecipeRow()">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Bahan
                        </button>
                    </div>

                    <div id="recipeRowsContainer" class="d-flex flex-column gap-2 mb-3">
                        <!-- Dynamic Rows -->
                    </div>

                    <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Estimasi Total HPP Bahan Baku:</span>
                        <span class="fs-5 fw-bold text-primary" id="recipeTotalCost">Rp 0</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btnSaveRecipe" onclick="saveCurrentRecipe()" style="display: none;">Simpan Komposisi Resep</button>
            </div>
        </div>
    </div>
</div>

<script>
const allIngredients = <?= json_encode($ingredients) ?>;

function openEdit(ing) {
    document.getElementById('ingId').value = ing.id;
    document.getElementById('ingName').value = ing.name;
    document.getElementById('ingUnit').value = ing.unit;
    document.getElementById('ingStock').value = ing.stock;
    document.getElementById('ingMinStock').value = ing.min_stock;
    document.getElementById('ingCost').value = ing.cost_per_unit;
    document.getElementById('ingModalTitle').textContent = 'Edit Bahan Baku';
    new bootstrap.Modal(document.getElementById('modalIngredient')).show();
}

function openRestock(ing) {
    document.getElementById('restockId').value = ing.id;
    document.getElementById('restockName').textContent = ing.name;
    document.getElementById('restockCurrentStock').textContent = parseFloat(ing.stock).toLocaleString('id-ID');
    document.getElementById('restockUnit').textContent = ing.unit;
    document.getElementById('restockUnitLabel').textContent = ing.unit;
    document.getElementById('restockAdd').value = '';
    document.getElementById('restockCost').value = ing.cost_per_unit;
    new bootstrap.Modal(document.getElementById('modalRestock')).show();
}

document.getElementById('formIngredient').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveIng');
    btn.disabled = true;
    const formData = new FormData(this);
    try {
        const res = await fetch('/pos/ingredients/save', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json());
        if (res.success) {
            location.reload();
        } else {
            alert(res.message || 'Gagal menyimpan.');
        }
    } catch(err) {
        alert('Terjadi kesalahan jaringan.');
    } finally {
        btn.disabled = false;
    }
});

document.getElementById('formRestock').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    try {
        const res = await fetch('/pos/ingredients/restock', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json());
        if (res.success) {
            location.reload();
        } else {
            alert(res.message || 'Gagal restock.');
        }
    } catch(err) {
        alert('Terjadi kesalahan jaringan.');
    }
});

async function deleteIng(id, name) {
    if (!confirm(`Hapus bahan baku "${name}"? Resep yang menggunakan bahan ini akan otomatis terhapus.`)) return;
    try {
        const res = await fetch(`/pos/ingredients/delete/${id}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json());
        if (res.success) {
            location.reload();
        }
    } catch(err) {
        alert('Gagal menghapus.');
    }
}

// Recipe Builder Logic
async function loadRecipeForProduct(productId) {
    const builder = document.getElementById('recipeBuilderArea');
    const saveBtn = document.getElementById('btnSaveRecipe');
    if (!productId) {
        builder.style.display = 'none';
        saveBtn.style.display = 'none';
        return;
    }
    builder.style.display = 'block';
    saveBtn.style.display = 'inline-block';

    const container = document.getElementById('recipeRowsContainer');
    container.innerHTML = '<div class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Memuat resep...</div>';

    try {
        const res = await fetch(`/pos/recipes/${productId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json());
        container.innerHTML = '';
        if (res.recipes && res.recipes.length > 0) {
            res.recipes.forEach(r => addRecipeRow(r.ingredient_id, r.amount_needed));
        } else {
            addRecipeRow(); // Add 1 empty row
        }
        recalcRecipeCost();
    } catch(err) {
        container.innerHTML = '<div class="text-danger small">Gagal memuat resep.</div>';
    }
}

function addRecipeRow(ingredientId = '', amount = '') {
    const container = document.getElementById('recipeRowsContainer');
    const row = document.createElement('div');
    row.className = 'row g-2 align-items-center recipe-row';

    let options = '<option value="">-- Pilih Bahan --</option>';
    allIngredients.forEach(ing => {
        const sel = ing.id == ingredientId ? 'selected' : '';
        options += `<option value="${ing.id}" data-unit="${ing.unit}" data-cost="${ing.cost_per_unit}" ${sel}>${ing.name} (${ing.unit})</option>`;
    });

    row.innerHTML = `
        <div class="col-6">
            <select class="form-select form-select-sm ing-select" onchange="updateRecipeRowUnit(this)">
                ${options}
            </select>
        </div>
        <div class="col-4">
            <div class="input-group input-group-sm">
                <input type="number" step="any" class="form-control ing-amount" placeholder="Jml" value="${amount}" oninput="recalcRecipeCost()" required>
                <span class="input-group-text ing-unit-label">satuan</span>
            </div>
        </div>
        <div class="col-2 text-end">
            <button type="button" class="btn btn-sm btn-light text-danger" onclick="this.closest('.recipe-row').remove(); recalcRecipeCost();">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
    updateRecipeRowUnit(row.querySelector('.ing-select'));
}

function updateRecipeRowUnit(select) {
    const opt = select.selectedOptions[0];
    const unit = opt ? opt.getAttribute('data-unit') : 'satuan';
    const row = select.closest('.recipe-row');
    row.querySelector('.ing-unit-label').textContent = unit || 'satuan';
    recalcRecipeCost();
}

function recalcRecipeCost() {
    let total = 0;
    document.querySelectorAll('.recipe-row').forEach(row => {
        const select = row.querySelector('.ing-select');
        const amountInput = row.querySelector('.ing-amount');
        const opt = select.selectedOptions[0];
        if (opt && opt.value) {
            const cost = parseFloat(opt.getAttribute('data-cost')) || 0;
            const amt = parseFloat(amountInput.value) || 0;
            total += (cost * amt);
        }
    });
    document.getElementById('recipeTotalCost').textContent = 'Rp ' + Math.round(total).toLocaleString('id-ID');
}

async function saveCurrentRecipe() {
    const productId = document.getElementById('recipeProductSelect').value;
    if (!productId) return;

    const recipes = [];
    document.querySelectorAll('.recipe-row').forEach(row => {
        const ingId = row.querySelector('.ing-select').value;
        const amt = row.querySelector('.ing-amount').value;
        if (ingId && amt) {
            recipes.push({ ingredient_id: ingId, amount_needed: amt });
        }
    });

    const formData = new FormData();
    formData.append('recipes', JSON.stringify(recipes));

    try {
        const res = await fetch(`/pos/recipes/${productId}/save`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json());
        if (res.success) {
            alert('Komposisi resep menu berhasil disimpan!');
            bootstrap.Modal.getInstance(document.getElementById('modalRecipe')).hide();
        }
    } catch(err) {
        alert('Gagal menyimpan resep.');
    }
}

// Search filter
document.getElementById('searchTable').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#ingredientTable tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
<?= $this->endSection() ?>
