<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.tools-page {
    max-width: 680px;
    margin: 0 auto;
    padding-bottom: 110px;
}
.tool-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}
.tool-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 20px;
    padding: 14px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    transition: all 0.2s ease;
}
.tool-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}
.tool-img {
    width: 100%;
    height: 120px;
    border-radius: 14px;
    object-fit: cover;
    background: #F3F4F6;
}
.token-box {
    background: #F8FAFC;
    border: 1px dashed #CBD5E1;
    border-radius: 12px;
    padding: 8px 12px;
    font-family: monospace;
    font-size: 14px;
    font-weight: bold;
    letter-spacing: 1px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="tools-page">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="/neighborhood" class="btn btn-sm btn-light rounded-circle"><i class="bi bi-arrow-left"></i></a>
            <h5 class="fw-bold mb-0">Pinjam Alat Warga</h5>
        </div>
        <button class="btn btn-sm btn-success rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#addToolModal">
            <i class="bi bi-plus-lg me-1"></i> Tambah Alat
        </button>
    </div>

    <!-- Active / Pending Rentals Section -->
    <?php if (!empty($myRentals)): ?>
        <div class="mb-4">
            <h6 class="fw-bold text-secondary mb-2" style="font-size: 12px; text-transform: uppercase;">Peminjaman Saya</h6>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($myRentals as $rental): ?>
                    <div class="card border-0 shadow-sm rounded-4 p-3" style="background: <?= $rental['status'] === 'borrowed' ? '#ECFDF5' : '#FFFBEB' ?>;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge <?= $rental['status'] === 'borrowed' ? 'bg-success' : 'bg-warning text-dark' ?> rounded-pill mb-1">
                                    <?= strtoupper($rental['status']) ?>
                                </span>
                                <h6 class="fw-bold mb-1 text-dark"><?= esc($rental['tool_name']) ?></h6>
                                <div class="text-muted small">Jatuh Tempo: <strong><?= date('d M Y', strtotime($rental['due_date'])) ?></strong></div>
                            </div>
                            <div class="text-end">
                                <?php if ($rental['status'] === 'requested'): ?>
                                    <div class="text-muted small">Token Serah Terima:</div>
                                    <div class="token-box text-primary"><?= esc($rental['handover_token']) ?></div>
                                <?php elseif ($rental['status'] === 'borrowed'): ?>
                                    <div class="text-muted small">Token Pengembalian:</div>
                                    <div class="token-box text-success"><?= esc($rental['return_token']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tool Catalog Grid -->
    <h6 class="fw-bold text-secondary mb-3" style="font-size: 12px; text-transform: uppercase;">Katalog Alat Tersedia (<?= count($tools) ?>)</h6>
    <div class="tool-grid">
        <?php foreach ($tools as $tool): ?>
            <div class="tool-card">
                <div>
                    <?php if ($tool['photo']): ?>
                        <img src="<?= esc($tool['photo']) ?>" class="tool-img mb-2" alt="<?= esc($tool['name']) ?>">
                    <?php else: ?>
                        <div class="tool-img d-flex align-items-center justify-content-center text-secondary mb-2" style="font-size: 36px;">
                            🔨
                        </div>
                    <?php endif; ?>
                    <span class="badge bg-light text-secondary rounded-pill mb-1" style="font-size: 10px;"><?= esc($tool['category']) ?></span>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;"><?= esc($tool['name']) ?></h6>
                    <div class="text-muted" style="font-size: 12px;">
                        <?= (float)$tool['rental_fee'] > 0 ? 'Sewa: Rp ' . number_format($tool['rental_fee'], 0, ',', '.') : 'Sewa: <strong>Gratis</strong>' ?>
                        <?php if ((float)$tool['deposit_amount'] > 0): ?>
                            • Dep: Rp <?= number_format($tool['deposit_amount'], 0, ',', '.') ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                    <span class="badge <?= $tool['status'] === 'available' ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary' ?> rounded-pill">
                        <?= $tool['status'] === 'available' ? 'Tersedia' : 'Sedang Dipinjam' ?>
                    </span>
                    <?php if ($tool['status'] === 'available'): ?>
                        <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="openRentModal(<?= (int)$tool['id'] ?>, '<?= esc($tool['name']) ?>', <?= (int)$tool['max_rent_days'] ?>)">
                            Pinjam
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Admin / Petugas Tool Validation Actions -->
    <?php if ($isRtAdmin): ?>
        <div class="card border-0 shadow-sm rounded-4 p-3 mt-4" style="background: #F1F5F9;">
            <h6 class="fw-bold mb-2"><i class="bi bi-qr-code-scan me-1"></i> Validasi Token / Serah Terima Alat</h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary rounded-pill flex-fill" data-bs-toggle="modal" data-bs-target="#handoverModal">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Validasi Serah Terima
                </button>
                <button class="btn btn-sm btn-outline-success rounded-pill flex-fill" data-bs-toggle="modal" data-bs-target="#returnModal">
                    <i class="bi bi-box-arrow-in-down me-1"></i> Validasi Pengembalian
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Pinjam Alat -->
<div class="modal fade" id="rentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 p-3">
            <h5 class="fw-bold mb-2">Pinjam <span id="rentToolName"></span></h5>
            <form id="rentToolForm">
                <input type="hidden" name="tool_id" id="rentToolId">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Durasi Peminjaman (Hari)</label>
                    <input type="number" name="rental_days" id="rentDays" class="form-control" value="1" min="1" max="7" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Catatan Penggunaan</label>
                    <textarea name="borrower_note" class="form-control" rows="2" placeholder="Misal: Untuk bersih-bersih taman depan rumah"></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Konfirmasi Pengajuan</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Alat -->
<div class="modal fade" id="addToolModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 p-3">
            <h5 class="fw-bold mb-2">Tambah Alat Bersama</h5>
            <form id="addToolForm" enctype="multipart/form-data">
                <div class="mb-2">
                    <label class="form-label small fw-bold">Nama Alat</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Bor Listrik Bosch" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold">Kategori</label>
                    <select name="category" class="form-select">
                        <?php foreach (CommunityToolModel::getCategories() as $catKey => $catLabel): ?>
                            <option value="<?= esc($catKey) ?>"><?= esc($catLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Biaya Sewa (Rp)</label>
                        <input type="number" name="rental_fee" class="form-control" value="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Uang Jaminan/Deposit (Rp)</label>
                        <input type="number" name="deposit_amount" class="form-control" value="0">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold">Foto Alat</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>
                <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold mt-2">Simpan Alat</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Handover Token Validasi -->
<div class="modal fade" id="handoverModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 p-3">
            <h5 class="fw-bold mb-2">Validasi Serah Terima Alat</h5>
            <p class="text-muted small">Masukkan ID Rental dan Token 6-digit dari HP peminjam.</p>
            <form id="handoverForm">
                <div class="mb-2">
                    <label class="form-label small fw-bold">ID Rental / Peminjaman</label>
                    <input type="number" name="rental_id" class="form-control" placeholder="Contoh: 1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Token Serah Terima</label>
                    <input type="text" name="handover_token" class="form-control text-uppercase" placeholder="Contoh: 7A9B3F" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Validasi & Catat Ledger</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Return Token Validasi -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 p-3">
            <h5 class="fw-bold mb-2">Validasi Pengembalian Alat</h5>
            <p class="text-muted small">Masukkan Token Pengembalian dari peminjam saat barang dikembalikan.</p>
            <form id="returnForm">
                <div class="mb-2">
                    <label class="form-label small fw-bold">ID Rental / Peminjaman</label>
                    <input type="number" name="rental_id" class="form-control" placeholder="Contoh: 1" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold">Token Pengembalian</label>
                    <input type="text" name="return_token" class="form-control text-uppercase" placeholder="Contoh: 4B2A1C" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Catatan Kondisi Barang</label>
                    <input type="text" name="condition_note" class="form-control" value="Baik & Lengkap">
                </div>
                <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold">Validasi & Refund Deposit</button>
            </form>
        </div>
    </div>
</div>

<script>
function openRentModal(toolId, toolName, maxDays) {
    document.getElementById('rentToolId').value = toolId;
    document.getElementById('rentToolName').innerText = toolName;
    document.getElementById('rentDays').max = maxDays;
    new bootstrap.Modal(document.getElementById('rentModal')).show();
}

document.getElementById('rentToolForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('/neighborhood/tools/rent', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
});

document.getElementById('addToolForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('/neighborhood/tools/store', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
});

document.getElementById('handoverForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('/neighborhood/tools/handover', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
});

document.getElementById('returnForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('/neighborhood/tools/return', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
});
</script>
<?= $this->endSection() ?>
