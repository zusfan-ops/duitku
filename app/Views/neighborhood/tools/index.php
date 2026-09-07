<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.tools-page {
    max-width: 720px;
    margin: 0 auto;
    padding-bottom: 120px;
}

/* ── Top Bar ── */
.tools-topbar {
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

/* ── Hero Kas RT Tool Sharing Card ── */
.kas-rt-card {
    background: linear-gradient(135deg, #064E3B 0%, #065F46 50%, #047857 100%);
    color: #ffffff;
    border-radius: var(--radius-xl, 24px);
    padding: 22px 20px;
    box-shadow: 0 10px 25px -5px rgba(6, 78, 59, 0.35);
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
}
.kas-rt-card::after {
    content: '🪚';
    position: absolute;
    right: -10px;
    bottom: -15px;
    font-size: 88px;
    opacity: 0.12;
    pointer-events: none;
}

/* ── Filter Chips ── */
.category-filter-row {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 8px;
    margin-bottom: 16px;
    scrollbar-width: none;
}
.category-filter-row::-webkit-scrollbar { display: none; }
.filter-chip {
    padding: 7px 14px;
    background: var(--bg-card, #ffffff);
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-primary, #0F172A);
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.2s ease;
}
.filter-chip.active {
    background: #059669;
    color: #ffffff;
    border-color: #059669;
}

/* ── Tool Cards Grid ── */
.tool-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}
@media (max-width: 576px) {
    .tool-grid {
        grid-template-columns: 1fr;
    }
}
.tool-card {
    background: var(--bg-card, #ffffff);
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: var(--radius-xl, 20px);
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 12px;
    box-shadow: var(--shadow-sm);
    transition: all 0.2s ease;
}
.tool-card:hover {
    transform: translateY(-2px);
    border-color: var(--primary, #059669);
    box-shadow: 0 6px 18px rgba(5, 150, 105, 0.12);
}
.tool-img {
    width: 100%;
    height: 130px;
    border-radius: 12px;
    object-fit: cover;
    background: var(--bg, #F8FAFC);
}
.tool-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 10.5px;
    font-weight: 800;
}
.tool-badge.available { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
.tool-badge.rented { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }

/* ── Buttons ── */
.btn-primary-sm {
    padding: 8px 14px;
    background: #059669;
    color: #ffffff;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 800;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}
.btn-primary-sm:hover {
    background: #047857;
    transform: translateY(-1px);
}
.btn-secondary-sm {
    padding: 8px 14px;
    background: var(--bg, #F8FAFC);
    color: var(--text-primary, #0F172A);
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: 20px;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* ── Modal Overlay & Sheet ── */
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
    background: #059669;
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 800;
    cursor: pointer;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="tools-page">
    
    <!-- Top Bar -->
    <div class="tools-topbar">
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="/neighborhood" class="btn-back-circle">←</a>
            <div>
                <h1 style="font-size: 18px; font-weight: 900; margin: 0; color: var(--text-primary);">Pinjam Alat Bersama RT</h1>
                <div style="font-size: 11px; color: var(--text-secondary);"><?= esc($neighborhood['name'] ?? 'Komunitas RT') ?></div>
            </div>
        </div>
        <button type="button" class="btn-primary-sm" onclick="openAddToolModal()">
            <span>➕</span> Tambah Alat
        </button>
    </div>

    <!-- Hero Card Kas RT Pinjam Alat -->
    <div class="kas-rt-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
            <div>
                <span style="display: inline-block; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.35); padding: 3px 10px; border-radius: 20px; font-size: 10.5px; font-weight: 800; margin-bottom: 6px;">
                    🏛️ Kas RT &amp; Inventaris Bersama
                </span>
                <div style="font-size: 22px; font-weight: 900;">
                    Rp <?= number_format($kasSummary['total_kas_collected'] ?? 0, 0, ',', '.') ?>
                </div>
                <div style="font-size: 11px; opacity: 0.85;">
                    Total Kas Terkumpul dari <?= count($kasSummary['records'] ?? []) ?>x peminjaman alat
                </div>
            </div>

            <div style="text-align: right;">
                <span style="display: inline-block; background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; margin-bottom: 8px;">
                    Tarif Kas: Rp <?= number_format($toolRentalFee, 0, ',', '.') ?>
                </span>
                <?php if ($isRtAdmin): ?>
                    <div>
                        <button type="button" class="btn-secondary-sm" style="padding: 4px 10px; font-size: 11px; background:#ffffff; color:#065F46;" onclick="openFeeSettingModal()">
                            ⚙️ Atur Tarif Kas
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: flex; gap: 8px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.15);">
            <button type="button" class="btn-secondary-sm" style="background: rgba(255,255,255,0.2); color:#ffffff; border-color: rgba(255,255,255,0.35);" onclick="openKasHistoryModal()">
                📜 Buku Kas Peminjaman
            </button>
            <?php if ($isRtAdmin): ?>
                <button type="button" class="btn-secondary-sm" style="background: #ffffff; color:#065F46;" onclick="openHandoverModal()">
                    🔑 Validasi Token Kas
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Category Filters -->
    <div class="category-filter-row">
        <a href="/neighborhood/tools" class="filter-chip <?= $selectedCategory === 'Semua' ? 'active' : '' ?>">Semua</a>
        <?php foreach ($categories as $catKey => $catLabel): ?>
            <a href="/neighborhood/tools?category=<?= urlencode($catKey) ?>" class="filter-chip <?= $selectedCategory === $catKey ? 'active' : '' ?>">
                <?= esc($catLabel) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Tool Cards Grid -->
    <?php if (empty($tools)): ?>
        <div style="background: var(--bg-card); border: 1.5px solid var(--border); border-radius: 18px; padding: 32px; text-align: center; color: var(--text-secondary); margin-bottom: 20px;">
            <div style="font-size: 38px; margin-bottom: 8px;">🧰</div>
            <strong style="color: var(--text-primary); display: block; font-size: 14px; margin-bottom: 4px;">Belum Ada Alat Terdaftar</strong>
            <p style="font-size: 12px; margin: 0;">Punya alat pertukangan atau pemotong rumput? Daftarkan untuk dipinjam bersama warga!</p>
        </div>
    <?php else: ?>
        <div class="tool-grid">
            <?php foreach ($tools as $t): ?>
                <div class="tool-card">
                    <div>
                        <?php if (!empty($t['photo'])): ?>
                            <img src="<?= esc($t['photo']) ?>" alt="<?= esc($t['name']) ?>" class="tool-img">
                        <?php else: ?>
                            <div class="tool-img" style="display: flex; align-items: center; justify-content: center; font-size: 40px; color: var(--text-secondary);">
                                🔧
                            </div>
                        <?php endif; ?>

                        <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: flex-start;">
                            <span class="tool-badge <?= $t['status'] === 'available' ? 'available' : 'rented' ?>">
                                <?= $t['status'] === 'available' ? '✓ Tersedia' : '⏳ Sedang Dipinjam' ?>
                            </span>
                            <span style="font-size: 11px; color: var(--text-secondary); font-weight: 700;">
                                Max <?= esc($t['max_rent_days']) ?> Hari
                            </span>
                        </div>

                        <div style="font-size: 14.5px; font-weight: 900; color: var(--text-primary); margin-top: 6px;">
                            <?= esc($t['name']) ?>
                        </div>
                        <div style="font-size: 11.5px; color: var(--text-secondary); margin-top: 2px;">
                            Kondisi: <?= esc($t['condition_note'] ?: 'Baik & Siap Pakai') ?>
                        </div>
                    </div>

                    <div>
                        <div style="background: var(--bg); border: 1px solid var(--border); border-radius: 12px; padding: 8px 10px; margin-bottom: 10px; font-size: 11.5px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                                <span style="color: #065F46; font-weight: 700;">Kas RT (Tunai):</span>
                                <strong style="color: #059669;">Rp <?= number_format($toolRentalFee, 0, ',', '.') ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; color: var(--text-secondary);">
                                <span>Sewa Pemilik:</span>
                                <strong><?= $t['rental_fee'] > 0 ? 'Rp ' . number_format($t['rental_fee'], 0, ',', '.') : 'Gratis' ?></strong>
                            </div>
                        </div>

                        <?php if ($t['status'] === 'available'): ?>
                            <button type="button" class="btn-primary-sm" style="width: 100%; justify-content: center; padding: 10px;" onclick="openRentModal(<?= (int)$t['id'] ?>, '<?= esc($t['name']) ?>', <?= (float)$t['rental_fee'] ?>, <?= (float)$t['deposit_amount'] ?>, <?= (int)$t['max_rent_days'] ?>)">
                                Ajukan Pinjam Alat →
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn-secondary-sm" style="width: 100%; justify-content: center; opacity: 0.6; cursor: not-allowed;" disabled>
                                Sedang Dipinjam
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- ── MODAL TAMBAH ALAT ── -->
<div id="modalAddTool" class="modal-overlay" onclick="if(event.target===this)closeAddToolModal()">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <h3 style="font-size: 17px; font-weight: 900; margin: 0 0 6px; text-align: center;">🧰 Tambah Alat ke Inventaris RT</h3>
        
        <form id="formAddTool" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div style="margin-bottom: 10px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Nama Alat</label>
                <input type="text" name="name" class="create-input-text" placeholder="Contoh: Bor Listrik Impact Drill Bosch" required>
            </div>

            <div style="margin-bottom: 10px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Kategori</label>
                <select name="category" class="create-input-text">
                    <?php foreach ($categories as $k => $lbl): ?>
                        <option value="<?= esc($k) ?>"><?= esc($lbl) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px;">
                <div>
                    <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Maks Hari Pinjam</label>
                    <input type="number" name="max_rent_days" class="create-input-text" value="3" required>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Kondisi Alat</label>
                    <input type="text" name="condition_note" class="create-input-text" value="Baik & Siap Pakai" required>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Foto Alat (Opsional)</label>
                <input type="file" name="photo" class="create-input-text" accept="image/*">
            </div>

            <button type="submit" id="btnSubmitTool" class="btn-create-submit">
                <span>Simpan Alat ke Inventaris</span>
            </button>
        </form>
    </div>
</div>

<!-- ── MODAL PINJAM ALAT ── -->
<div id="modalRent" class="modal-overlay" onclick="if(event.target===this)closeRentModal()">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <h3 id="rentModalTitle" style="font-size: 17px; font-weight: 900; margin: 0 0 6px; text-align: center;">Pinjam Alat</h3>
        
        <div style="background: #ECFDF5; border: 1.5px solid #A7F3D0; border-radius: 14px; padding: 12px; margin-bottom: 14px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                <span style="font-size: 12px; color: #065F46; font-weight: 700;">🏛️ Kas RT (Bayar Tunai):</span>
                <strong style="font-size: 13px; color: #059669;">Rp <?= number_format($toolRentalFee, 0, ',', '.') ?></strong>
            </div>
            <div style="font-size: 11px; color: #047857; line-height: 1.3;">
                Biaya Kas RT sepenuhnya masuk ke pembukuan Kas RT untuk perawatan fasilitas bersama.
            </div>
        </div>

        <form id="formRent">
            <?= csrf_field() ?>
            <input type="hidden" name="tool_id" id="rentToolId">
            
            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Durasi Peminjaman (Hari)</label>
                <input type="number" name="rental_days" id="rentDaysInput" class="create-input-text" value="1" min="1" max="7" required>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Catatan Keperluan (Opsional)</label>
                <input type="text" name="borrower_note" class="create-input-text" placeholder="Misal: Untuk pasang kanopi rumah">
            </div>

            <button type="submit" id="btnSubmitRent" class="btn-create-submit">
                <span>Ajukan Peminjaman Sekarang →</span>
            </button>
        </form>
    </div>
</div>

<!-- ── MODAL ATUR TARIF KAS RT (ADMIN) ── -->
<div id="modalFeeSetting" class="modal-overlay" onclick="if(event.target===this)closeFeeSettingModal()">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <h3 style="font-size: 17px; font-weight: 900; margin: 0 0 6px; text-align: center;">⚙️ Atur Tarif Kas RT</h3>
        
        <form id="formFeeSetting">
            <?= csrf_field() ?>
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Tarif Kas RT per Pinjam (Rp)</label>
                <input type="number" name="tool_rental_fee" class="create-input-text" value="<?= (int)$toolRentalFee ?>" step="500" required>
            </div>
            <button type="submit" class="btn-create-submit">
                <span>Simpan Tarif Kas RT</span>
            </button>
        </form>
    </div>
</div>

<!-- ── MODAL SERAH TERIMA & KAS CASH ── -->
<div id="modalHandover" class="modal-overlay" onclick="if(event.target===this)closeHandoverModal()">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <h3 style="font-size: 17px; font-weight: 900; margin: 0 0 6px; text-align: center;">🔑 Validasi Token Kas &amp; Serah Terima</h3>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; margin: 0 0 14px;">
            Pastikan menerima uang kas RT tunai Rp <?= number_format($toolRentalFee, 0, ',', '.') ?> dari peminjam saat serah terima.
        </p>

        <form id="formHandover">
            <?= csrf_field() ?>
            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">ID Peminjaman</label>
                <input type="number" name="rental_id" class="create-input-text" placeholder="Contoh: 1" required>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Token Serah Terima (6 Digit)</label>
                <input type="text" name="handover_token" class="create-input-text uppercase-code" placeholder="Contoh: A4B9C2" required>
            </div>
            <button type="submit" class="btn-create-submit">
                <span>Validasi &amp; Catat Kas Masuk</span>
            </button>
        </form>
    </div>
</div>

<!-- ── MODAL RIWAYAT KAS PEMINJAMAN ALAT ── -->
<div id="modalKasHistory" class="modal-overlay" onclick="if(event.target===this)closeKasHistoryModal()">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <h3 style="font-size: 17px; font-weight: 900; margin: 0 0 6px; text-align: center;">📜 Buku Kas Peminjaman Alat</h3>
        <div style="margin-bottom: 14px; text-align: center; font-size: 13px; color: #059669; font-weight: 800;">
            Total Kas Terkumpul: Rp <?= number_format($kasSummary['total_kas_collected'] ?? 0, 0, ',', '.') ?>
        </div>

        <?php if (empty($kasSummary['records'])): ?>
            <div style="text-align: center; padding: 20px; color: var(--text-secondary); font-size: 12px;">
                Belum ada transaksi peminjaman yang diserahterimakan.
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <?php foreach ($kasSummary['records'] as $rec): ?>
                    <div style="padding: 10px 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 13px; font-weight: 800; color: var(--text-primary);"><?= esc($rec['tool_name'] ?? 'Alat RT') ?></div>
                            <div style="font-size: 11px; color: var(--text-secondary);">Peminjam: <?= esc($rec['borrower_name'] ?? 'Warga') ?> • <?= esc($rec['start_date'] ?? '') ?></div>
                        </div>
                        <div style="font-size: 13px; font-weight: 900; color: #059669;">
                            + Rp <?= number_format($rec['rt_fee_amount'] ?? $toolRentalFee, 0, ',', '.') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function openAddToolModal() { document.getElementById('modalAddTool').classList.add('open'); }
function closeAddToolModal() { document.getElementById('modalAddTool').classList.remove('open'); }

function openRentModal(id, name, fee, deposit, maxDays) {
    document.getElementById('rentToolId').value = id;
    document.getElementById('rentModalTitle').textContent = 'Pinjam ' + name;
    document.getElementById('rentDaysInput').max = maxDays;
    document.getElementById('modalRent').classList.add('open');
}
function closeRentModal() { document.getElementById('modalRent').classList.remove('open'); }

function openFeeSettingModal() { document.getElementById('modalFeeSetting').classList.add('open'); }
function closeFeeSettingModal() { document.getElementById('modalFeeSetting').classList.remove('open'); }

function openHandoverModal() { document.getElementById('modalHandover').classList.add('open'); }
function closeHandoverModal() { document.getElementById('modalHandover').classList.remove('open'); }

function openKasHistoryModal() { document.getElementById('modalKasHistory').classList.add('open'); }
function closeKasHistoryModal() { document.getElementById('modalKasHistory').classList.remove('open'); }

document.getElementById('formAddTool')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitTool');
    btn.disabled = true;
    btn.innerHTML = '<span>Menyimpan...</span>';

    const formData = new FormData(this);
    fetch('/neighborhood/tools/store', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        alert(d.message);
        if (d.success) location.reload();
        else {
            btn.disabled = false;
            btn.innerHTML = '<span>Simpan Alat ke Inventaris</span>';
        }
    });
});

document.getElementById('formRent')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitRent');
    btn.disabled = true;
    btn.innerHTML = '<span>Mengajukan...</span>';

    const formData = new FormData(this);
    fetch('/neighborhood/tools/rent', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        alert(d.message);
        if (d.success) location.reload();
        else {
            btn.disabled = false;
            btn.innerHTML = '<span>Ajukan Peminjaman Sekarang →</span>';
        }
    });
});

document.getElementById('formFeeSetting')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('/neighborhood/tools/fee-setting', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        alert(d.message);
        if (d.success) location.reload();
    });
});

document.getElementById('formHandover')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('/neighborhood/tools/handover', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        alert(d.message);
        if (d.success) location.reload();
    });
});
</script>
<?= $this->endSection() ?>
