<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.errand-detail-page {
    max-width: 680px;
    margin: 0 auto;
    padding-bottom: 110px;
}
.item-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 18px;
    padding: 14px 16px;
    margin-bottom: 10px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="errand-detail-page">
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="/neighborhood/errands" class="btn btn-sm btn-light rounded-circle"><i class="bi bi-arrow-left"></i></a>
        <h5 class="fw-bold mb-0">Detail Titip Belanja</h5>
    </div>

    <!-- Errand Info Card -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4" style="background: linear-gradient(135deg, #1E40AF, #3B82F6); color: white;">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <span class="badge bg-white text-primary rounded-pill mb-1 fw-bold">Tujuan Toko</span>
                <h4 class="fw-bold mb-0"><?= esc($errand['destination_store']) ?></h4>
            </div>
            <span class="badge bg-success rounded-pill px-3 py-1"><?= strtoupper($errand['status']) ?></span>
        </div>
        <p class="mb-2 opacity-90 small"><?= esc($errand['description'] ?: 'Belanja kebutuhan warga') ?></p>
        <div class="d-flex justify-content-between align-items-center pt-2 border-top border-white border-opacity-25 small">
            <div>Oleh: <strong><?= esc($errand['organizer_name']) ?></strong> (Rumah <?= esc($errand['organizer_house'] ?: '-') ?>)</div>
            <div>Batas: <strong><?= date('d M, H:i', strtotime($errand['cutoff_time'])) ?></strong></div>
        </div>
    </div>

    <!-- Actions -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold text-secondary mb-0" style="font-size: 13px; text-transform: uppercase;">Daftar Barang Titipan (<?= count($errand['items']) ?>)</h6>
        <?php if (!$isOrganizer && $errand['status'] === 'open'): ?>
            <button class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="bi bi-plus-lg me-1"></i> Titip Barang
            </button>
        <?php endif; ?>
    </div>

    <!-- Items List -->
    <?php if (empty($errand['items'])): ?>
        <div class="card border-0 bg-light rounded-4 p-4 text-center text-muted mb-4">
            Belum ada barang titipan di sesi ini.
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-2 mb-4">
            <?php foreach ($errand['items'] as $it): ?>
                <div class="item-card shadow-sm">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge <?= $it['status'] === 'delivered' ? 'bg-success' : 'bg-secondary' ?> rounded-pill mb-1">
                                <?= strtoupper($it['status']) ?>
                            </span>
                            <h6 class="fw-bold mb-1 text-dark"><?= esc($it['item_name']) ?> (<?= esc($it['quantity']) ?> <?= esc($it['unit']) ?>)</h6>
                            <div class="text-muted small">
                                Pemesan: <strong><?= esc($it['requester_name']) ?></strong> (Rumah <?= esc($it['requester_house'] ?: '-') ?>)
                                <?php if ($it['notes']): ?>
                                    • Catatan: <em>"<?= esc($it['notes']) ?>"</em>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="text-end">
                            <div class="fw-bold text-dark">
                                Est: Rp <?= number_format($it['estimated_price'], 0, ',', '.') ?>
                            </div>
                            <div class="text-muted small">Fee: Rp <?= number_format($it['service_fee'], 0, ',', '.') ?></div>

                            <!-- If Organizer and not yet delivered -->
                            <?php if ($isOrganizer && $it['status'] !== 'delivered'): ?>
                                <button class="btn btn-sm btn-outline-success rounded-pill px-3 mt-2" onclick="openDeliverModal(<?= (int)$it['id'] ?>, '<?= esc($it['item_name']) ?>', <?= (float)$it['estimated_price'] ?>)">
                                    <i class="bi bi-check-circle me-1"></i> Serah Terima
                                </button>
                            <?php endif; ?>

                            <!-- If Requester -->
                            <?php if ((int)$it['requester_user_id'] === (int)$userId && $it['status'] !== 'delivered'): ?>
                                <div class="mt-2 text-muted small">
                                    Token: <span class="badge bg-dark font-monospace"><?= esc($it['handover_token']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Titip Barang -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 p-3">
            <h5 class="fw-bold mb-2">Titip Barang Belanjaan</h5>
            <form id="addItemForm">
                <input type="hidden" name="errand_id" value="<?= (int)$errand['id'] ?>">
                <div class="mb-2">
                    <label class="form-label small fw-bold">Nama Barang</label>
                    <input type="text" name="item_name" class="form-control" placeholder="Contoh: Telur Ayam Negeri 1kg" required>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Jumlah</label>
                        <input type="number" name="quantity" class="form-control" value="1" step="0.5" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Satuan</label>
                        <input type="text" name="unit" class="form-control" value="kg / pack / ikat" required>
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Estimasi Harga (Rp)</label>
                        <input type="number" name="estimated_price" class="form-control" placeholder="Contoh: 30000" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Tip / Fee Jasa (Rp)</label>
                        <input type="number" name="service_fee" class="form-control" value="5000" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Catatan / Merek Pengganti</label>
                    <input type="text" name="notes" class="form-control" placeholder="Contoh: Kalau tidak ada merek A, boleh merek B">
                </div>
                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Kirim Titipan</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Serah Terima Belanjaan (Organizer Only) -->
<div class="modal fade" id="deliverModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 p-3">
            <h5 class="fw-bold mb-2">Konfirmasi Serah Terima Belanjaan</h5>
            <p class="text-muted small">Masukkan harga riil sesuai struk belanja dan minta Token 6-digit dari pemesan.</p>
            <form id="deliverForm" enctype="multipart/form-data">
                <input type="hidden" name="item_id" id="deliverItemId">
                <div class="mb-2">
                    <label class="form-label small fw-bold">Harga Riil Barang (Rp)</label>
                    <input type="number" name="actual_price" id="deliverActualPrice" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold">Token Serah Terima (dari Pemesan)</label>
                    <input type="text" name="handover_token" class="form-control text-uppercase" placeholder="Contoh: 9B2C4F" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Foto Struk Belanja (Opsional)</label>
                    <input type="file" name="receipt_photo" class="form-control" accept="image/*">
                </div>
                <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold">Validasi & Catat ke Buku Keuangan</button>
            </form>
        </div>
    </div>
</div>

<script>
function openDeliverModal(itemId, itemName, estPrice) {
    document.getElementById('deliverItemId').value = itemId;
    document.getElementById('deliverActualPrice').value = estPrice;
    new bootstrap.Modal(document.getElementById('deliverModal')).show();
}

document.getElementById('addItemForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('/neighborhood/errands/item', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
});

document.getElementById('deliverForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('/neighborhood/errands/deliver', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
});
</script>
<?= $this->endSection() ?>
