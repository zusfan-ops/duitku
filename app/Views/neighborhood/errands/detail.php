<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.errand-detail-page {
    max-width: 680px;
    margin: 0 auto;
    padding-bottom: 120px;
}

/* ── Top Bar ── */
.detail-topbar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}
.btn-back-circle {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: var(--bg-card, #ffffff);
    border: 1.5px solid var(--border, #E2E8F0);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-primary, #0F172A);
    text-decoration: none;
    font-size: 16px;
    box-shadow: var(--shadow-sm);
    transition: all 0.2s ease;
}
.btn-back-circle:hover {
    border-color: var(--primary, #059669);
    color: var(--primary, #059669);
}

/* ── Errand Info Banner ── */
.errand-hero-banner {
    background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%);
    border-radius: var(--radius-xl, 24px);
    padding: 22px 20px;
    color: #ffffff;
    margin-bottom: 20px;
    box-shadow: 0 10px 25px rgba(59, 130, 246, 0.28);
}
.errand-hero-badge {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.35);
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 800;
    display: inline-block;
    margin-bottom: 6px;
}
.errand-hero-title {
    font-size: 20px;
    font-weight: 900;
    margin-bottom: 4px;
}
.errand-hero-desc {
    font-size: 12px;
    opacity: 0.9;
    margin-bottom: 14px;
}
.errand-hero-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 10px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    font-size: 11.5px;
}

/* ── Item Card ── */
.item-card {
    background: var(--bg-card, #ffffff);
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: var(--radius-xl, 18px);
    padding: 16px;
    margin-bottom: 10px;
    box-shadow: var(--shadow-sm, 0 2px 8px rgba(15,23,42,0.04));
}
.item-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}
.status-pill {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}
.status-pill.delivered { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
.status-pill.pending { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }

/* ── Modal Bottom Sheet ── */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: none;
    align-items: flex-end;
    justify-content: center;
}
.modal-overlay.open {
    display: flex;
}
.modal-sheet {
    background: var(--bg-card, #ffffff);
    border-radius: 24px 24px 0 0;
    width: 100%;
    max-width: 580px;
    max-height: 85vh;
    overflow-y: auto;
    padding: 24px 20px 40px;
    box-shadow: 0 -10px 30px rgba(0,0,0,0.15);
    animation: slideUp 0.25s ease-out;
}
@keyframes slideUp {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}
.modal-handle {
    width: 40px;
    height: 4px;
    background: var(--border, #E2E8F0);
    border-radius: 2px;
    margin: 0 auto 16px;
}
.create-input-text {
    width: 100%;
    padding: 10px 12px;
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: 12px;
    background: var(--bg, #F8FAFC);
    color: var(--text-primary, #0F172A);
    outline: none;
    box-sizing: border-box;
}
.btn-create-submit {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 20px;
    background: #3B82F6;
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 800;
    cursor: pointer;
}
.btn-primary-sm {
    padding: 8px 14px;
    background: #3B82F6;
    color: #ffffff;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 800;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="errand-detail-page">
    
    <!-- Top Bar -->
    <div class="detail-topbar">
        <a href="/neighborhood/errands" class="btn-back-circle">←</a>
        <h1 style="font-size: 18px; font-weight: 900; margin: 0; color: var(--text-primary);">Detail Titip Belanja</h1>
    </div>

    <!-- Hero Banner -->
    <div class="errand-hero-banner">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span class="errand-hero-badge">🛒 Tujuan Belanja</span>
                <div class="errand-hero-title"><?= esc($errand['destination_store']) ?></div>
            </div>
            <span style="background: rgba(255,255,255,0.25); border: 1px solid rgba(255,255,255,0.4); padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: 800; text-transform: uppercase;">
                <?= esc($errand['status']) ?>
            </span>
        </div>
        <p class="errand-hero-desc"><?= esc($errand['description'] ?: 'Belanja kebutuhan dapur & harian') ?></p>
        <div class="errand-hero-meta">
            <div>👤 Oleh: <strong><?= esc($errand['organizer_name']) ?></strong> (Rumah <?= esc($errand['organizer_house'] ?: '-') ?>)</div>
            <div>⏰ Batas: <strong><?= date('d M, H:i', strtotime($errand['cutoff_time'])) ?></strong></div>
        </div>
    </div>

    <!-- Items Section Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <strong style="font-size: 13px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">
            Daftar Barang Titipan (<?= count($errand['items']) ?>)
        </strong>
        <?php if (!$isOrganizer && $errand['status'] === 'open'): ?>
            <button type="button" class="btn-primary-sm" onclick="openAddItemModal()">
                <span>➕</span> Titip Barang
            </button>
        <?php endif; ?>
    </div>

    <!-- Items List -->
    <?php if (empty($errand['items'])): ?>
        <div style="background: var(--bg-card); border: 1.5px solid var(--border); border-radius: 18px; padding: 28px; text-align: center; color: var(--text-secondary); margin-bottom: 20px;">
            Belum ada barang titipan di sesi ini.
        </div>
    <?php else: ?>
        <div style="margin-bottom: 24px;">
            <?php foreach ($errand['items'] as $it): ?>
                <div class="item-card">
                    <div class="item-card-header">
                        <div>
                            <span class="status-pill <?= $it['status'] === 'delivered' ? 'delivered' : 'pending' ?>">
                                <?= strtoupper($it['status']) ?>
                            </span>
                            <div style="font-size: 14px; font-weight: 800; color: var(--text-primary); margin-top: 4px;">
                                <?= esc($it['item_name']) ?> (<?= esc($it['quantity']) ?> <?= esc($it['unit']) ?>)
                            </div>
                            <div style="font-size: 11px; color: var(--text-secondary); margin-top: 2px;">
                                Pemesan: <strong><?= esc($it['requester_name']) ?></strong> (Rumah <?= esc($it['requester_house'] ?: '-') ?>)
                                <?php if ($it['notes']): ?>
                                    • <em>"<?= esc($it['notes']) ?>"</em>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div style="text-align: right;">
                            <div style="font-size: 13px; font-weight: 900; color: var(--text-primary);">
                                Est: Rp <?= number_format($it['estimated_price'], 0, ',', '.') ?>
                            </div>
                            <div style="font-size: 10.5px; color: var(--text-secondary);">Fee: Rp <?= number_format($it['service_fee'], 0, ',', '.') ?></div>

                            <?php if ($isOrganizer && $it['status'] !== 'delivered'): ?>
                                <button type="button" class="btn-primary-sm" style="background:#059669; padding: 4px 10px; font-size: 11px; margin-top: 6px;" onclick="openDeliverModal(<?= (int)$it['id'] ?>, '<?= esc($it['item_name']) ?>', <?= (float)$it['estimated_price'] ?>)">
                                    ✓ Serah Terima
                                </button>
                            <?php endif; ?>

                            <?php if ((int)$it['requester_user_id'] === (int)$userId && $it['status'] !== 'delivered'): ?>
                                <div style="margin-top: 6px; font-size: 10px; color: var(--text-secondary);">
                                    Token: <span style="font-family: monospace; font-size: 12px; font-weight: 800; background: var(--bg); border: 1px solid var(--border); padding: 2px 6px; border-radius: 6px;"><?= esc($it['handover_token']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- ── MODAL TITIP BARANG ── -->
<div id="modalAddItem" class="modal-overlay" onclick="if(event.target===this)closeAddItemModal()">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <h3 style="font-size: 17px; font-weight: 900; margin: 0 0 6px; text-align: center;">🛍️ Titip Barang Belanjaan</h3>
        
        <form id="addItemForm">
            <?= csrf_field() ?>
            <input type="hidden" name="errand_id" value="<?= (int)$errand['id'] ?>">
            
            <div style="margin-bottom: 10px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Nama Barang</label>
                <input type="text" name="item_name" class="create-input-text" placeholder="Contoh: Telur Ayam 1kg / Beras 5kg" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px;">
                <div>
                    <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Jumlah</label>
                    <input type="number" name="quantity" class="create-input-text" value="1" step="0.5" required>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Satuan</label>
                    <input type="text" name="unit" class="create-input-text" value="kg / pack / ikat" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px;">
                <div>
                    <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Estimasi Harga (Rp)</label>
                    <input type="number" name="estimated_price" class="create-input-text" placeholder="30000" required>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Tip / Jasa (Rp)</label>
                    <input type="number" name="service_fee" class="create-input-text" value="3000" required>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Catatan Tambahan</label>
                <input type="text" name="notes" class="create-input-text" placeholder="Contoh: Kalau tidak ada merek A, ganti merek B">
            </div>

            <button type="submit" id="btnSubmitItem" class="btn-create-submit">
                <span>Kirim Titipan Barang</span>
            </button>
        </form>
    </div>
</div>

<!-- ── MODAL SERAH TERIMA (ORGANIZER) ── -->
<div id="modalDeliver" class="modal-overlay" onclick="if(event.target===this)closeDeliverModal()">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <h3 style="font-size: 17px; font-weight: 900; margin: 0 0 6px; text-align: center;">✅ Serah Terima Titipan Belanja</h3>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; margin: 0 0 16px;">
            Masukkan nominal harga riil sesuai nota/struk belanja dan minta Token 6-digit dari tetangga pemesan.
        </p>

        <form id="deliverForm" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="item_id" id="deliverItemId">
            
            <div style="margin-bottom: 10px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Harga Riil Belanja (Rp)</label>
                <input type="number" name="actual_price" id="deliverActualPrice" class="create-input-text" required>
            </div>

            <div style="margin-bottom: 10px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Token Serah Terima (dari Pemesan)</label>
                <input type="text" name="handover_token" class="create-input-text uppercase-code" placeholder="Contoh: 9B2C4F" required>
            </div>

            <button type="submit" id="btnSubmitDeliver" class="btn-create-submit" style="background:#059669;">
                <span>Validasi &amp; Catat Transaksi Selesai</span>
            </button>
        </form>
    </div>
</div>

<script>
function openAddItemModal() {
    document.getElementById('modalAddItem').classList.add('open');
}
function closeAddItemModal() {
    document.getElementById('modalAddItem').classList.remove('open');
}

function openDeliverModal(itemId, itemName, estPrice) {
    document.getElementById('deliverItemId').value = itemId;
    document.getElementById('deliverActualPrice').value = estPrice;
    document.getElementById('modalDeliver').classList.add('open');
}
function closeDeliverModal() {
    document.getElementById('modalDeliver').classList.remove('open');
}

document.getElementById('addItemForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitItem');
    btn.disabled = true;
    btn.innerHTML = '<span>Mengirimkan...</span>';

    const formData = new FormData(this);
    fetch('/neighborhood/errands/item', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
        else {
            btn.disabled = false;
            btn.innerHTML = '<span>Kirim Titipan Barang</span>';
        }
    });
});

document.getElementById('deliverForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitDeliver');
    btn.disabled = true;
    btn.innerHTML = '<span>Memvalidasi...</span>';

    const formData = new FormData(this);
    fetch('/neighborhood/errands/deliver', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
        else {
            btn.disabled = false;
            btn.innerHTML = '<span>Validasi & Catat Transaksi Selesai</span>';
        }
    });
});
</script>
<?= $this->endSection() ?>
