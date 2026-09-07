<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.errands-page {
    max-width: 680px;
    margin: 0 auto;
    padding-bottom: 120px;
}

/* ── Top Bar ── */
.errands-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
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

/* ── Hero Banner ── */
.errands-hero {
    background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 60%, #60A5FA 100%);
    border-radius: var(--radius-xl, 24px);
    padding: 22px 20px;
    color: #ffffff;
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(59, 130, 246, 0.28);
}
.errands-hero::after {
    content: '🛒';
    position: absolute;
    right: -10px;
    bottom: -15px;
    font-size: 88px;
    opacity: 0.15;
    pointer-events: none;
}
.errands-hero-title {
    font-size: 20px;
    font-weight: 900;
    margin-bottom: 4px;
}
.errands-hero-desc {
    font-size: 12.5px;
    opacity: 0.92;
    line-height: 1.4;
    margin-bottom: 14px;
}

/* ── Section Title ── */
.section-title {
    font-size: 13px;
    font-weight: 900;
    color: var(--text-secondary, #64748B);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* ── Errand Session Card ── */
.errand-card {
    background: var(--bg-card, #ffffff);
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: var(--radius-xl, 20px);
    padding: 16px;
    margin-bottom: 12px;
    text-decoration: none;
    color: var(--text-primary, #0F172A);
    display: block;
    box-shadow: var(--shadow-sm, 0 2px 8px rgba(15,23,42,0.04));
    transition: all 0.2s ease;
}
.errand-card:hover {
    transform: translateY(-2px);
    border-color: #3B82F6;
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.12);
}
.errand-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}
.errand-store-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #EFF6FF;
    color: #1D4ED8;
    border: 1px solid #BFDBFE;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 800;
    margin-bottom: 6px;
}
.errand-status-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}
.errand-status-badge.open { background: #DCFCE7; color: #166534; border: 1px solid #86EFAC; }
.errand-status-badge.shopping { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }
.errand-status-badge.delivering { background: #E0E7FF; color: #3730A3; border: 1px solid #C7D2FE; }

.errand-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11.5px;
    color: var(--text-secondary, #64748B);
    padding-top: 10px;
    margin-top: 10px;
    border-top: 1px solid var(--border, #F1F5F9);
}

/* ── Buttons ── */
.btn-primary-sm {
    padding: 8px 16px;
    background: #3B82F6;
    color: #ffffff;
    border-radius: 20px;
    font-size: 12.5px;
    font-weight: 800;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
}
.btn-primary-sm:hover {
    background: #2563EB;
    transform: translateY(-1px);
}

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
    padding: 12px 14px;
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: 14px;
    background: var(--bg, #F8FAFC);
    color: var(--text-primary, #0F172A);
    outline: none;
    box-sizing: border-box;
}
.create-input-text:focus {
    border-color: #3B82F6;
    background: #ffffff;
}
.btn-create-submit {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 24px;
    background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%);
    color: #ffffff;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.35);
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="errands-page">
    
    <!-- Top Bar -->
    <div class="errands-topbar">
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="/neighborhood" class="btn-back-circle">←</a>
            <h1 style="font-size: 18px; font-weight: 900; margin: 0; color: var(--text-primary);">Titip Belanja Tetangga</h1>
        </div>
        <button type="button" class="btn-primary-sm" onclick="openCreateModal()">
            <span>➕</span> Buka Titipan
        </button>
    </div>

    <!-- Hero Banner -->
    <div class="errands-hero">
        <div class="errands-hero-title">Belanja Saling Bantu Warga RT</div>
        <div class="errands-hero-desc">
            Sedang mau ke pasar, toko swalayan, atau apotek? Buka sesi belanja dan bantu tetangga yang membutuhkan.
        </div>
        <button type="button" class="btn-primary-sm" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.4);" onclick="openCreateModal()">
            <span>🛒</span> Buat Sesi Titip Belanja Sekarang
        </button>
    </div>

    <!-- Sesi Belanja Terbuka -->
    <div class="section-title">
        <span>Sesi Belanja Buka (<?= count($errands) ?>)</span>
    </div>

    <?php if (empty($errands)): ?>
        <div style="background: var(--bg-card); border: 1.5px solid var(--border); border-radius: 18px; padding: 28px; text-align: center; color: var(--text-secondary); margin-bottom: 20px;">
            <div style="font-size: 38px; margin-bottom: 8px;">🛍️</div>
            <strong style="color: var(--text-primary); display: block; margin-bottom: 4px; font-size: 14px;">Belum Ada Sesi Belanja Terbuka</strong>
            <p style="font-size: 12px; margin: 0; line-height: 1.4;">
                Yuk jadi yang pertama membuka sesi belanja untuk membantu tetangga di RT Anda!
            </p>
        </div>
    <?php else: ?>
        <div style="margin-bottom: 24px;">
            <?php foreach ($errands as $e): ?>
                <a href="/neighborhood/errands/<?= (int)$e['id'] ?>" class="errand-card">
                    <div class="errand-card-header">
                        <div>
                            <span class="errand-store-badge">
                                🛒 <?= esc($e['destination_store']) ?>
                            </span>
                            <div style="font-size: 14px; font-weight: 900; color: var(--text-primary);">
                                <?= esc($e['description'] ?: 'Belanja kebutuhan dapur & harian') ?>
                            </div>
                        </div>
                        <span class="errand-status-badge <?= esc($e['status']) ?>">
                            <?= strtoupper($e['status']) ?>
                        </span>
                    </div>

                    <div class="errand-footer">
                        <div>
                            👤 Oleh: <strong><?= esc($e['organizer_name']) ?></strong> (Rumah <?= esc($e['organizer_house'] ?: '-') ?>)
                        </div>
                        <div>
                            ⏰ Batas: <strong><?= date('d M, H:i', strtotime($e['cutoff_time'])) ?></strong>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Titipan Saya -->
    <?php if (!empty($myRequests)): ?>
        <div class="section-title">
            <span>Barang Titipan Saya (<?= count($myRequests) ?>)</span>
        </div>
        <div>
            <?php foreach ($myRequests as $req): ?>
                <div style="background: var(--bg-card); border: 1.5px solid var(--border); border-radius: 18px; padding: 14px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 13.5px; font-weight: 800; color: var(--text-primary);">
                            <?= esc($req['item_name']) ?> (<?= esc($req['quantity']) ?> <?= esc($req['unit']) ?>)
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 2px;">
                            Toko: <strong><?= esc($req['destination_store']) ?></strong> • Oleh: <?= esc($req['organizer_name']) ?>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <?php if ($req['status'] !== 'delivered'): ?>
                            <div style="font-size: 10px; color: var(--text-secondary);">Token Terima:</div>
                            <span style="font-family: monospace; font-size: 12px; font-weight: 800; background: var(--bg); border: 1px solid var(--border); padding: 3px 8px; border-radius: 8px;">
                                <?= esc($req['handover_token']) ?>
                            </span>
                        <?php else: ?>
                            <span style="font-size: 11.5px; font-weight: 800; color: #059669;">
                                ✅ Selesai
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- ── MODAL BUKA SESI BELANJA ── -->
<div id="modalCreateErrand" class="modal-overlay" onclick="if(event.target===this)closeCreateModal()">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <h3 style="font-size: 17px; font-weight: 900; margin: 0 0 6px; text-align: center;">🛒 Buka Sesi Titip Belanja</h3>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; margin: 0 0 16px;">
            Beri tahu tetangga tujuan belanja Anda agar mereka bisa menitip
        </p>

        <form id="createErrandForm">
            <?= csrf_field() ?>
            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Tujuan Toko / Pasar / Supermarket</label>
                <input type="text" name="destination_store" class="create-input-text" placeholder="Contoh: Pasar Pagi Subuh RW 02 / Superindo" required>
            </div>

            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Catatan / Rute Perjalanan</label>
                <textarea name="description" class="create-input-text" rows="2" placeholder="Misal: Berangkat jam 06.00 pakai motor, bisa bawa belanjaan ringan/sedang"></textarea>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Batas Waktu Menitip (Cutoff Time)</label>
                <input type="datetime-local" name="cutoff_time" class="create-input-text" required value="<?= date('Y-m-d\TH:i', strtotime('+2 hours')) ?>">
            </div>

            <button type="submit" id="btnSubmitErrand" class="btn-create-submit">
                <span>Buka Sesi Belanja Sekarang →</span>
            </button>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modalCreateErrand').classList.add('open');
}
function closeCreateModal() {
    document.getElementById('modalCreateErrand').classList.remove('open');
}

document.getElementById('createErrandForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitErrand');
    btn.disabled = true;
    btn.innerHTML = '<span>Membuka Sesi...</span>';

    const formData = new FormData(this);

    fetch('/neighborhood/errands/store', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Sesi belanja berhasil dibuka!');
            location.reload();
        } else {
            alert(data.message || 'Gagal membuka sesi.');
            btn.disabled = false;
            btn.innerHTML = '<span>Buka Sesi Belanja Sekarang →</span>';
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan jaringan.');
        btn.disabled = false;
        btn.innerHTML = '<span>Buka Sesi Belanja Sekarang →</span>';
    });
});
</script>
<?= $this->endSection() ?>
