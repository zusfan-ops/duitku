<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.tools-page {
    max-width: 720px;
    margin: 0 auto;
    padding-bottom: 120px;
}
.kas-rt-card {
    background: linear-gradient(135deg, #064E3B 0%, #065F46 50%, #047857 100%);
    color: #ffffff;
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 10px 25px -5px rgba(6, 78, 59, 0.35);
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
}
.kas-rt-card::after {
    content: '🏛️';
    position: absolute;
    right: -10px;
    bottom: -15px;
    font-size: 88px;
    opacity: 0.12;
    pointer-events: none;
}
.tool-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
}
@media (max-width: 576px) {
    .tool-grid {
        grid-template-columns: 1fr;
    }
}
.tool-card {
    background: var(--bg-card, #ffffff);
    border: 1.5px solid var(--border-color, #e5e7eb);
    border-radius: 20px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    transition: all 0.2s ease;
}
.tool-card:hover {
    transform: translateY(-2px);
    border-color: #10B981;
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.1);
}
.tool-img {
    width: 100%;
    height: 140px;
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
.fee-chip {
    background: #ECFDF5;
    border: 1px solid #A7F3D0;
    color: #065F46;
    border-radius: 10px;
    padding: 6px 10px;
    font-size: 12px;
    font-weight: 700;
}
.kas-notice-box {
    background: #F0FDF4;
    border: 1px solid #86EFAC;
    border-radius: 16px;
    padding: 14px 16px;
    margin-bottom: 20px;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="tools-page">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="/neighborhood" class="btn btn-sm btn-light rounded-circle shadow-sm"><i class="bi bi-arrow-left"></i></a>
            <div>
                <h5 class="fw-bold mb-0">Pinjam Alat Bersama RT</h5>
                <small class="text-muted"><?= esc($neighborhood['name'] ?? 'Komunitas RT') ?></small>
            </div>
        </div>
        <button class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addToolModal">
            <i class="bi bi-plus-lg me-1"></i> Tambah Alat
        </button>
    </div>

    <!-- ── KAS RT DARI PEMINJAMAN ALAT HERO CARD ── -->
    <div class="kas-rt-card">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 mb-1 fw-bold" style="font-size: 11px;">
                    🏛️ Kas RT &amp; Inventaris Bersama
                </span>
                <div class="h2 fw-bold mb-0 text-white">
                    Rp <?= number_format($kasSummary['total_kas_collected'] ?? 0, 0, ',', '.') ?>
                </div>
                <small class="text-white text-opacity-80">Total Kas Terkumpul dari <?= count($kasSummary['records'] ?? []) ?>x Peminjaman Alat</small>
            </div>
            <div class="text-end">
                <div class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-1 mb-2">
                    Tarif Kas: Rp <?= number_format($toolRentalFee, 0, ',', '.') ?> / pinjam
                </div>
                <?php if ($isRtAdmin): ?>
                    <div>
                        <button class="btn btn-xs btn-light rounded-pill px-3 py-1 fw-bold text-success shadow-sm" data-bs-toggle="modal" data-bs-target="#feeSettingModal" style="font-size: 11.5px;">
                            <i class="bi bi-sliders me-1"></i> Atur Tarif Kas RT
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex gap-2 pt-2 border-top border-white border-opacity-10">
            <button class="btn btn-sm btn-outline-light rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#kasHistoryModal">
                <i class="bi bi-receipt me-1"></i> Buku Kas Peminjaman Alat
            </button>
            <?php if ($isRtAdmin): ?>
                <button class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#handoverModal">
                    <i class="bi bi-qr-code-scan me-1"></i> Serah Terima (Kas Cash)
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── TRANSPARENCY NOTICE BANNER ── -->
    <div class="kas-notice-box">
        <div class="fs-4 text-success">💡</div>
        <div style="font-size: 12.5px; color: #166534; line-height: 1.45;">
            <strong>Informasi Transparansi Kas RT:</strong> Setiap peminjaman alat dikenakan biaya administrasi <strong>Rp <?= number_format($toolRentalFee, 0, ',', '.') ?></strong> yang dibayarkan tunai (cash) ke <strong>Bendahara / Ketua RT</strong>. Biaya ini <strong>100% masuk ke pembukuan Kas RT</strong> untuk dikelola bersama demi perawatan alat dan fasilitas warga.
        </div>
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
                                <div class="text-success small mt-1">
                                    <i class="bi bi-check-circle-fill me-1"></i> Kas RT: Rp <?= number_format($rental['rt_fee_amount'] ?? $toolRentalFee, 0, ',', '.') ?> (Bayar Tunai ke RT)
                                </div>
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold text-secondary mb-0" style="font-size: 12px; text-transform: uppercase;">Katalog Alat Tersedia (<?= count($tools) ?>)</h6>
    </div>

    <div class="tool-grid">
        <?php foreach ($tools as $tool): ?>
            <div class="tool-card">
                <div>
                    <?php if ($tool['photo']): ?>
                        <img src="<?= esc($tool['photo']) ?>" class="tool-img mb-2" alt="<?= esc($tool['name']) ?>">
                    <?php else: ?>
                        <div class="tool-img d-flex align-items-center justify-content-center text-secondary mb-2" style="font-size: 40px;">
                            🔨
                        </div>
                    <?php endif; ?>
                    <span class="badge bg-light text-secondary rounded-pill mb-1" style="font-size: 10px;"><?= esc($tool['category']) ?></span>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;"><?= esc($tool['name']) ?></h6>
                    
                    <div class="d-flex flex-column gap-1 mt-2">
                        <div class="fee-chip d-flex justify-content-between">
                            <span>🏛️ Kas RT:</span>
                            <strong>Rp <?= number_format($toolRentalFee, 0, ',', '.') ?></strong>
                        </div>
                        <?php if ((float)$tool['rental_fee'] > 0 || (float)$tool['deposit_amount'] > 0): ?>
                            <div class="text-muted" style="font-size: 11.5px;">
                                <?= (float)$tool['rental_fee'] > 0 ? 'Sewa Pemilik: Rp ' . number_format($tool['rental_fee'], 0, ',', '.') : '' ?>
                                <?= (float)$tool['deposit_amount'] > 0 ? ' • Deposit: Rp ' . number_format($tool['deposit_amount'], 0, ',', '.') : '' ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                    <span class="badge <?= $tool['status'] === 'available' ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary' ?> rounded-pill">
                        <?= $tool['status'] === 'available' ? 'Tersedia' : 'Sedang Dipinjam' ?>
                    </span>
                    <?php if ($tool['status'] === 'available'): ?>
                        <button class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" onclick="openRentModal(<?= (int)$tool['id'] ?>, '<?= esc($tool['name']) ?>', <?= (int)$tool['max_rent_days'] ?>, <?= (float)$tool['rental_fee'] ?>, <?= (float)$tool['deposit_amount'] ?>)">
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
            <h6 class="fw-bold mb-2"><i class="bi bi-qr-code-scan me-1"></i> Validasi Token &amp; Penerimaan Kas RT</h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary rounded-pill flex-fill fw-bold" data-bs-toggle="modal" data-bs-target="#handoverModal">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Validasi Serah Terima (Terima Kas)
                </button>
                <button class="btn btn-sm btn-outline-success rounded-pill flex-fill fw-bold" data-bs-toggle="modal" data-bs-target="#returnModal">
                    <i class="bi bi-box-arrow-in-down me-1"></i> Validasi Pengembalian (Refund Dep)
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Pinjam Alat (Dengan Rincian Biaya Kas RT) -->
<div class="modal fade" id="rentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 p-3">
            <h5 class="fw-bold mb-1">Pinjam <span id="rentToolName"></span></h5>
            <p class="text-muted small mb-3">Lengkapi durasi dan konfirmasi rincian biaya.</p>

            <form id="rentToolForm">
                <input type="hidden" name="tool_id" id="rentToolId">

                <!-- Rincian Biaya Kas & Deposit -->
                <div class="p-3 rounded-3 mb-3" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted">🏛️ Biaya Kas RT (Tunai ke RT):</span>
                        <span class="fw-bold text-success">Rp <?= number_format($toolRentalFee, 0, ',', '.') ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1" id="rentOwnerFeeRow">
                        <span class="small text-muted">👤 Sewa Pemilik:</span>
                        <span class="fw-bold text-dark" id="rentOwnerFeeVal">Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center" id="rentDepositRow">
                        <span class="small text-muted">🛡️ Uang Jaminan (Deposit):</span>
                        <span class="fw-bold text-primary" id="rentDepositVal">Rp 0</span>
                    </div>
                    <hr class="my-2">
                    <div style="font-size: 11px; color: #166534;">
                        ✅ Biaya Kas RT Rp <?= number_format($toolRentalFee, 0, ',', '.') ?> dibayarkan cash kepada Bendahara/Ketua RT saat serah terima barang dan 100% masuk pembukuan Kas RT untuk kepentingan warga.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Durasi Peminjaman (Hari)</label>
                    <input type="number" name="rental_days" id="rentDays" class="form-control" value="1" min="1" max="7" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Catatan Penggunaan</label>
                    <textarea name="borrower_note" class="form-control" rows="2" placeholder="Misal: Untuk perbaikan pagar rumah"></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2">Konfirmasi &amp; Dapatkan Token</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Atur Tarif Kas RT (Khusus RT Admin) -->
<div class="modal fade" id="feeSettingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 p-3">
            <h5 class="fw-bold mb-1">⚙️ Atur Tarif Kas RT</h5>
            <p class="text-muted small mb-3">Tentukan nominal kas RT yang dipungut setiap kali warga meminjam alat.</p>
            <form id="feeSettingForm">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Tarif Kas RT per Peminjaman (Rp)</label>
                    <input type="number" name="tool_rental_fee" class="form-control" value="<?= esc($toolRentalFee) ?>" step="500" min="0" required>
                    <div class="form-text small">Default sistem adalah Rp 2.000. Uang ini diterima tunai oleh RT dan dicatat otomatis ke kas.</div>
                </div>
                <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold">Simpan Tarif Baru</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Riwayat Pembukuan Kas RT dari Alat -->
<div class="modal fade" id="kasHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">📜 Buku Kas Peminjaman Alat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="p-2 mb-2 rounded-3 bg-light d-flex justify-content-between">
                <span class="small text-muted">Total Dana Kas Alat:</span>
                <strong class="text-success">Rp <?= number_format($kasSummary['total_kas_collected'] ?? 0, 0, ',', '.') ?></strong>
            </div>
            
            <?php if (empty($kasSummary['records'])): ?>
                <div class="text-center py-4 text-muted small">
                    Belum ada riwayat peminjaman yang diserahterimakan.
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-2" style="max-height: 360px; overflow-y: auto;">
                    <?php foreach ($kasSummary['records'] as $rec): ?>
                        <div class="p-2 border rounded-3 d-flex justify-content-between align-items-center">
                            <div>
                                <strong style="font-size: 13px;"><?= esc($rec['tool_name']) ?></strong>
                                <div class="text-muted" style="font-size: 11px;">
                                    Peminjam: <?= esc($rec['borrower_name']) ?> (No. <?= esc($rec['borrower_house'] ?: '-') ?>)
                                </div>
                                <div class="text-muted" style="font-size: 10px;">
                                    <?= date('d M Y', strtotime($rec['start_date'])) ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success rounded-pill">+ Rp <?= number_format($rec['rt_fee_amount'] ?? $toolRentalFee, 0, ',', '.') ?></span>
                                <div class="text-muted" style="font-size: 10px;">Tunai Cash</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
                        <label class="form-label small fw-bold">Biaya Sewa Pemilik (Rp)</label>
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
            <p class="text-muted small">Terima uang kas RT tunai (Rp <?= number_format($toolRentalFee, 0, ',', '.') ?>) dari peminjam, lalu masukkan ID dan Token serah terima.</p>
            <form id="handoverForm">
                <div class="mb-2">
                    <label class="form-label small fw-bold">ID Rental / Peminjaman</label>
                    <input type="number" name="rental_id" class="form-control" placeholder="Contoh: 1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Token Serah Terima (Dari HP Peminjam)</label>
                    <input type="text" name="handover_token" class="form-control text-uppercase" placeholder="Contoh: 7A9B3F" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Validasi &amp; Catat Kas RT</button>
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
                    <input type="text" name="condition_note" class="form-control" value="Baik &amp; Lengkap">
                </div>
                <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold">Validasi &amp; Refund Deposit</button>
            </form>
        </div>
    </div>
</div>

<script>
function openRentModal(toolId, toolName, maxDays, fee, deposit) {
    document.getElementById('rentToolId').value = toolId;
    document.getElementById('rentToolName').innerText = toolName;
    document.getElementById('rentDays').max = maxDays;
    
    document.getElementById('rentOwnerFeeVal').innerText = 'Rp ' + (fee || 0).toLocaleString('id-ID');
    document.getElementById('rentDepositVal').innerText = 'Rp ' + (deposit || 0).toLocaleString('id-ID');
    
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

document.getElementById('feeSettingForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('/neighborhood/tools/fee-setting', { method: 'POST', body: formData })
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
