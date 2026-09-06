<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.errands-page {
    max-width: 680px;
    margin: 0 auto;
    padding-bottom: 110px;
}
.errand-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 20px;
    padding: 16px;
    margin-bottom: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    transition: all 0.2s ease;
    text-decoration: none;
    color: inherit;
    display: block;
}
.errand-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    border-color: #3B82F6;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="errands-page">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="/neighborhood" class="btn btn-sm btn-light rounded-circle"><i class="bi bi-arrow-left"></i></a>
            <h5 class="fw-bold mb-0">Titip Belanja Tetangga</h5>
        </div>
        <button class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#createErrandModal">
            <i class="bi bi-plus-lg me-1"></i> Buka Titipan
        </button>
    </div>

    <!-- Sesi Belanja Aktif -->
    <h6 class="fw-bold text-secondary mb-3" style="font-size: 12px; text-transform: uppercase;">Sesi Belanja Buka (<?= count($errands) ?>)</h6>
    <?php if (empty($errands)): ?>
        <div class="card border-0 bg-light rounded-4 p-4 text-center text-muted mb-4">
            <div style="font-size: 32px;" class="mb-2">🛍️</div>
            <div class="fw-bold">Belum ada warga yang membuka sesi titip belanja.</div>
            <div class="small">Sedang mau ke pasar atau supermarket? Yuk buka sesi belanja untuk bantu tetangga!</div>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-2 mb-4">
            <?php foreach ($errands as $e): ?>
                <a href="/neighborhood/errands/<?= (int)$e['id'] ?>" class="errand-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill mb-1">
                                🛒 Ke: <?= esc($e['destination_store']) ?>
                            </span>
                            <h6 class="fw-bold mb-0 text-dark"><?= esc($e['description'] ?: 'Belanja kebutuhan dapur & harian') ?></h6>
                        </div>
                        <span class="badge bg-success rounded-pill px-2 py-1" style="font-size: 10px;">BUKA</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center text-muted small pt-2 border-top">
                        <div>
                            <i class="bi bi-person me-1"></i> Oleh: <strong><?= esc($e['organizer_name']) ?></strong> (Rumah <?= esc($e['organizer_house'] ?: '-') ?>)
                        </div>
                        <div>
                            <i class="bi bi-clock me-1"></i> Batas: <strong><?= date('H:i', strtotime($e['cutoff_time'])) ?></strong>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Titipan Saya -->
    <?php if (!empty($myRequests)): ?>
        <h6 class="fw-bold text-secondary mb-2" style="font-size: 12px; text-transform: uppercase;">Barang Titipan Saya (<?= count($myRequests) ?>)</h6>
        <div class="d-flex flex-column gap-2 mb-4">
            <?php foreach ($myRequests as $req): ?>
                <div class="card border-0 shadow-sm rounded-4 p-3" style="background: <?= $req['status'] === 'delivered' ? '#ECFDF5' : '#FFFBEB' ?>;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge <?= $req['status'] === 'delivered' ? 'bg-success' : 'bg-warning text-dark' ?> rounded-pill mb-1">
                                <?= strtoupper($req['status']) ?>
                            </span>
                            <h6 class="fw-bold mb-1 text-dark"><?= esc($req['item_name']) ?> (<?= esc($req['quantity']) ?> <?= esc($req['unit']) ?>)</h6>
                            <div class="text-muted small">Toko: <strong><?= esc($req['destination_store']) ?></strong> • Pembelanja: <?= esc($req['organizer_name']) ?></div>
                        </div>
                        <div class="text-end">
                            <?php if ($req['status'] !== 'delivered'): ?>
                                <div class="text-muted small">Token Serah Terima:</div>
                                <div class="badge bg-dark font-monospace text-white px-2 py-1" style="font-size: 13px; letter-spacing: 1px;">
                                    <?= esc($req['handover_token']) ?>
                                </div>
                            <?php else: ?>
                                <div class="fw-bold text-success">Rp <?= number_format($req['actual_price'] + $req['service_fee'], 0, ',', '.') ?></div>
                                <div class="small text-muted">Selesai</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Buka Sesi Belanja Baru -->
<div class="modal fade" id="createErrandModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 p-3">
            <h5 class="fw-bold mb-2">Buka Sesi Titip Belanja</h5>
            <form id="createErrandForm">
                <div class="mb-2">
                    <label class="form-label small fw-bold">Tujuan Belanja (Pasar / Toko / Supermarket)</label>
                    <input type="text" name="destination_store" class="form-control" placeholder="Contoh: Pasar Pagi Subuh RW 02" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold">Catatan / Rute Perjalanan</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Misal: Berangkat jam 06.00 pakai motor, bisa bawa belanjaan ringan/sedang"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Batas Akhir Warga Menitip (Cutoff Time)</label>
                    <input type="datetime-local" name="cutoff_time" class="form-control" required value="<?= date('Y-m-d\TH:i', strtotime('+2 hours')) ?>">
                </div>
                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Buka Sesi Belanja</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('createErrandForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('/neighborhood/errands/store', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
});
</script>
<?= $this->endSection() ?>
