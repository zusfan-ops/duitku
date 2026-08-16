<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.shifts-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 16px 16px 100px;
}

.shifts-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}

.shifts-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.btn-back-link {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-primary);
    text-decoration: none;
    font-size: 16px;
    font-weight: 700;
    transition: all 0.15s ease;
}
.btn-back-link:hover {
    background: var(--border-light);
    border-color: var(--primary);
}

.shifts-title {
    font-size: 18px;
    font-weight: 800;
    margin: 0;
    color: var(--text-primary);
    line-height: 1.2;
}

.shifts-subtitle {
    font-size: 12px;
    color: var(--text-muted);
    margin: 2px 0 0;
}

/* Active Shift Card */
.active-shift-card {
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
    border: 2px solid #EA580C;
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 10px 25px rgba(234, 88, 12, 0.15);
}

.active-shift-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
}

.shift-badge-active {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 10px;
    font-weight: 800;
    background: #10B981;
    color: #ffffff;
    padding: 3px 8px;
    border-radius: 6px;
    letter-spacing: 0.5px;
}

.active-cashier-title {
    font-size: 17px;
    font-weight: 800;
    margin: 8px 0 2px;
}

.active-opened-time {
    font-size: 11.5px;
    color: #94A3B8;
}

.expected-cash-label {
    font-size: 10.5px;
    color: #94A3B8;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.expected-cash-val {
    font-size: 22px;
    font-weight: 900;
    color: #FB923C;
}

.shift-metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 10px;
    margin-bottom: 16px;
}

.shift-metric-box {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.08);
    padding: 12px;
    border-radius: 12px;
}
.shift-metric-lbl {
    font-size: 11px;
    color: #94A3B8;
    margin-bottom: 4px;
}
.shift-metric-num {
    font-size: 14px;
    font-weight: 800;
}

/* Empty Card */
.empty-shift-card {
    background: var(--bg-card);
    border: 2px dashed var(--border);
    border-radius: 20px;
    padding: 36px 20px;
    text-align: center;
    margin-bottom: 24px;
}

.empty-shift-icon {
    font-size: 44px;
    margin-bottom: 10px;
}

.empty-shift-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 6px;
}

.empty-shift-desc {
    font-size: 12.5px;
    color: var(--text-muted);
    max-width: 380px;
    margin: 0 auto 16px;
}

/* Action Buttons */
.btn-shift-primary {
    background: #0284C7;
    color: #ffffff;
    border: none;
    padding: 10px 20px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: transform 0.15s ease, opacity 0.15s ease;
}
.btn-shift-primary:active { transform: scale(0.97); }

.btn-shift-danger {
    background: #DC2626;
    color: #ffffff;
    border: none;
    padding: 10px 18px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    justify-content: center;
    transition: transform 0.15s ease, opacity 0.15s ease;
}
.btn-shift-danger:active { transform: scale(0.97); }

/* History List */
.history-title {
    font-size: 15px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 12px;
}

.history-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 14px 16px;
    margin-bottom: 10px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    box-shadow: var(--shadow-sm);
}

.history-card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.history-cashier-name {
    font-size: 14px;
    font-weight: 800;
    color: var(--text-primary);
}

.history-status-badge {
    font-size: 10px;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 6px;
}
.badge-closed { background: var(--border-light); color: var(--text-muted); }
.badge-open { background: rgba(16, 185, 129, 0.15); color: #10B981; }

.history-time {
    font-size: 11px;
    color: var(--text-muted);
}

.history-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
    gap: 8px;
    padding-top: 8px;
    border-top: 1px solid var(--border);
    font-size: 12px;
}

.history-detail-lbl {
    font-size: 10.5px;
    color: var(--text-muted);
    margin-bottom: 2px;
}

/* Modals Overlay */
.shift-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    padding: 16px;
}
.shift-modal-overlay.open {
    display: flex;
}

.shift-modal-box {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 20px;
    width: 100%;
    max-width: 440px;
    padding: 22px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    animation: modalIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalIn {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.shift-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}

.shift-modal-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0;
}

.shift-modal-close {
    background: none;
    border: none;
    font-size: 20px;
    color: var(--text-muted);
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 8px;
}
.shift-modal-close:hover { background: var(--border-light); }

.s-form-group {
    margin-bottom: 14px;
}
.s-form-label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 6px;
}
.s-form-input {
    width: 100%;
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text-primary);
    font-size: 13.5px;
    outline: none;
    transition: border-color 0.15s ease;
    box-sizing: border-box;
}
.s-form-input:focus {
    border-color: var(--primary);
}

.s-form-helper {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 4px;
}

.s-expected-box {
    background: rgba(234, 88, 12, 0.1);
    border: 1px dashed #EA580C;
    border-radius: 12px;
    padding: 12px;
    margin-bottom: 14px;
    text-align: center;
}
.s-expected-lbl {
    font-size: 11.5px;
    color: var(--text-muted);
}
.s-expected-val {
    font-size: 20px;
    font-weight: 900;
    color: #EA580C;
    margin-top: 2px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="shifts-container">

    <!-- Top Header -->
    <div class="shifts-header">
        <div class="shifts-header-left">
            <a href="/pos/orders" class="btn-back-link" title="Kembali ke Pesanan">←</a>
            <div>
                <h1 class="shifts-title">💼 Shift Kasir & Laci Kas</h1>
                <p class="shifts-subtitle">Rekonsiliasi modal awal, penjualan kas, dan fisik laci.</p>
            </div>
        </div>

        <div>
            <?php if ($activeShift): ?>
                <button type="button" class="btn-shift-danger" style="width:auto;padding:8px 16px;" onclick="openCloseShiftModal()">
                    🔒 Tutup Shift Kasir
                </button>
            <?php else: ?>
                <button type="button" class="btn-shift-primary" onclick="openOpenShiftModal()">
                    🔓 Buka Shift Baru
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Active Shift Status Card -->
    <?php if ($activeShift): ?>
        <div class="active-shift-card">
            <div class="active-shift-top">
                <div>
                    <span class="shift-badge-active">● SHIFT AKTIF</span>
                    <div class="active-cashier-title">Kasir: <?= esc($activeShift['cashier_name']) ?></div>
                    <div class="active-opened-time">Dibuka: <?= date('d M Y, H:i', strtotime($activeShift['opened_at'])) ?></div>
                </div>
                <div style="text-align: right;">
                    <div class="expected-cash-label">ESTIMASI LACI KAS</div>
                    <div class="expected-cash-val">
                        <?= $symbol ?> <?= number_format($currentExpectedCash ?? $activeShift['expected_cash'], 0, ',', '.') ?>
                    </div>
                </div>
            </div>

            <div class="shift-metrics-grid">
                <div class="shift-metric-box">
                    <div class="shift-metric-lbl">Modal Awal:</div>
                    <div class="shift-metric-num"><?= $symbol ?> <?= number_format($activeShift['starting_cash'], 0, ',', '.') ?></div>
                </div>
                <div class="shift-metric-box">
                    <div class="shift-metric-lbl">Penjualan Tunai:</div>
                    <div class="shift-metric-num" style="color: #34D399;">+<?= $symbol ?> <?= number_format($currentCashSales ?? 0, 0, ',', '.') ?></div>
                </div>
                <div class="shift-metric-box">
                    <div class="shift-metric-lbl">Total Transaksi:</div>
                    <div class="shift-metric-num"><?= (int)($currentTrxCount ?? 0) ?> Struk</div>
                </div>
            </div>

            <button type="button" class="btn-shift-danger" onclick="openCloseShiftModal()">
                🔒 Tutup Shift & Rekonsiliasi Uang Laci
            </button>
        </div>
    <?php else: ?>
        <div class="empty-shift-card">
            <div class="empty-shift-icon">🔒</div>
            <h3 class="empty-shift-title">Belum Ada Shift Kasir yang Dibuka</h3>
            <p class="empty-shift-desc">
                Buka shift kasir terlebih dahulu sebelum melayani transaksi untuk mencatat modal awal dan memantau laci kas.
            </p>
            <button type="button" class="btn-shift-primary" onclick="openOpenShiftModal()">
                + Buka Shift Kasir Sekarang
            </button>
        </div>
    <?php endif; ?>

    <!-- Shift History Section -->
    <div class="history-title">📜 Riwayat Shift Sebelumnya</div>

    <?php if (empty($shiftHistory)): ?>
        <div class="history-card" style="text-align: center; color: var(--text-muted); font-size: 13px; padding: 24px;">
            Belum ada riwayat shift kasir sebelumnya.
        </div>
    <?php else: ?>
        <div>
            <?php foreach ($shiftHistory as $sh): ?>
                <?php
                    $isClosed = $sh['status'] === 'closed';
                    $diff = (float)$sh['difference'];
                    $diffColor = $diff >= 0 ? '#10B981' : '#EF4444';
                    $diffLabel = $diff == 0 ? '✓ Sesuai (Pas)' : ($diff > 0 ? '+' . $symbol . ' ' . number_format($diff, 0, ',', '.') . ' (Lebih)' : '-' . $symbol . ' ' . number_format(abs($diff), 0, ',', '.') . ' (Kurang)');
                ?>
                <div class="history-card">
                    <div class="history-card-top">
                        <div>
                            <span class="history-cashier-name"><?= esc($sh['cashier_name']) ?></span>
                            <div class="history-time">
                                <?= date('d/m/Y H:i', strtotime($sh['opened_at'])) ?>
                                <?= $isClosed && $sh['closed_at'] ? ' — ' . date('d/m/Y H:i', strtotime($sh['closed_at'])) : ' (Sedang Berjalan)' ?>
                            </div>
                        </div>
                        <span class="history-status-badge <?= $isClosed ? 'badge-closed' : 'badge-open' ?>">
                            <?= $isClosed ? 'DITUTUP' : 'AKTIF' ?>
                        </span>
                    </div>

                    <div class="history-details-grid">
                        <div>
                            <div class="history-detail-lbl">Modal Awal:</div>
                            <strong><?= $symbol ?> <?= number_format($sh['starting_cash'], 0, ',', '.') ?></strong>
                        </div>
                        <div>
                            <div class="history-detail-lbl">Penjualan:</div>
                            <strong><?= $symbol ?> <?= number_format($sh['total_sales'], 0, ',', '.') ?></strong>
                        </div>
                        <?php if ($isClosed): ?>
                            <div>
                                <div class="history-detail-lbl">Fisik Kas:</div>
                                <strong><?= $symbol ?> <?= number_format($sh['actual_cash'] ?? 0, 0, ',', '.') ?></strong>
                            </div>
                            <div>
                                <div class="history-detail-lbl">Selisih:</div>
                                <strong style="color: <?= $diffColor ?>;"><?= $diffLabel ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- ══════════════════ MODAL BUKA SHIFT ══════════════════ -->
<div class="shift-modal-overlay" id="openShiftOverlay">
    <div class="shift-modal-box">
        <div class="shift-modal-header">
            <h3 class="shift-modal-title">🔓 Buka Shift Kasir Baru</h3>
            <button type="button" class="shift-modal-close" onclick="closeOpenShiftModal()">✕</button>
        </div>
        <form id="formOpenShift" onsubmit="submitOpenShift(event)">
            <div class="s-form-group">
                <label class="s-form-label">Nama Kasir Bertugas *</label>
                <input type="text" name="cashier_name" class="s-form-input" value="<?= esc($currentUserName ?? 'Kasir') ?>" required>
            </div>
            <div class="s-form-group">
                <label class="s-form-label">Modal Awal Kas (Laci Uang) *</label>
                <input type="number" name="starting_cash" class="s-form-input" placeholder="100000" min="0" value="100000" required>
                <div class="s-form-helper">Uang pecahan kecil / kembalian awal di laci kasir.</div>
            </div>
            <div class="s-form-group">
                <label class="s-form-label">Catatan (Opsional)</label>
                <input type="text" name="notes" class="s-form-input" placeholder="Shift Pagi / Shift Sore...">
            </div>
            <button type="submit" class="btn-shift-primary" style="width: 100%; justify-content: center; padding: 12px; margin-top: 8px;">
                Mulai Shift Kasir 🚀
            </button>
        </form>
    </div>
</div>

<!-- ══════════════════ MODAL TUTUP SHIFT ══════════════════ -->
<div class="shift-modal-overlay" id="closeShiftOverlay">
    <div class="shift-modal-box">
        <div class="shift-modal-header">
            <h3 class="shift-modal-title">🔒 Tutup Shift & Hitung Kas</h3>
            <button type="button" class="shift-modal-close" onclick="closeCloseShiftModal()">✕</button>
        </div>
        <form id="formCloseShift" onsubmit="submitCloseShift(event)">
            <input type="hidden" name="shift_id" value="<?= $activeShift['id'] ?? 0 ?>">
            
            <div class="s-expected-box">
                <div class="s-expected-lbl">Estimasi Kas Seharusnya di Laci:</div>
                <div class="s-expected-val">
                    <?= $symbol ?> <?= number_format($currentExpectedCash ?? ($activeShift['expected_cash'] ?? 0), 0, ',', '.') ?>
                </div>
            </div>

            <div class="s-form-group">
                <label class="s-form-label">Hitungan Uang Fisik Nyata di Laci (Rp) *</label>
                <input type="number" name="actual_cash" id="inp_actual_cash" class="s-form-input" placeholder="0" min="0" required oninput="calculateDiff()">
                <div class="s-form-helper">Hitung seluruh uang kertas dan koin nyata di laci kasir.</div>
            </div>

            <div id="diffBox" style="display: none; padding: 10px; border-radius: 10px; font-size: 13px; font-weight: 800; margin-bottom: 14px; text-align: center;"></div>

            <div class="s-form-group">
                <label class="s-form-label">Catatan Penutupan / Serah Terima</label>
                <input type="text" name="notes" class="s-form-input" placeholder="Semua aman / ada selisih karena...">
            </div>

            <button type="submit" class="btn-shift-danger" style="padding: 12px; margin-top: 8px;">
                Tutup Shift & Simpan Laporan
            </button>
        </form>
    </div>
</div>

<script>
const expectedCashVal = <?= (float)($currentExpectedCash ?? ($activeShift['expected_cash'] ?? 0)) ?>;
const openOverlay = document.getElementById('openShiftOverlay');
const closeOverlay = document.getElementById('closeShiftOverlay');

function openOpenShiftModal() {
    openOverlay.classList.add('open');
    window.DuitkuLockScroll && window.DuitkuLockScroll();
}

function closeOpenShiftModal() {
    openOverlay.classList.remove('open');
    window.DuitkuUnlockScroll && window.DuitkuUnlockScroll();
}

function openCloseShiftModal() {
    closeOverlay.classList.add('open');
    window.DuitkuLockScroll && window.DuitkuLockScroll();
}

function closeCloseShiftModal() {
    closeOverlay.classList.remove('open');
    window.DuitkuUnlockScroll && window.DuitkuUnlockScroll();
}

openOverlay.addEventListener('click', e => { if (e.target === openOverlay) closeOpenShiftModal(); });
closeOverlay.addEventListener('click', e => { if (e.target === closeOverlay) closeCloseShiftModal(); });

function calculateDiff() {
    const actual = parseFloat(document.getElementById('inp_actual_cash').value) || 0;
    const diff = actual - expectedCashVal;
    const box = document.getElementById('diffBox');
    box.style.display = 'block';

    if (diff === 0) {
        box.style.background = 'rgba(16, 185, 129, 0.15)';
        box.style.color = '#10B981';
        box.textContent = '✅ Kas Sesuai Sempurna (Pas)';
    } else if (diff > 0) {
        box.style.background = 'rgba(16, 185, 129, 0.15)';
        box.style.color = '#10B981';
        box.textContent = 'ℹ️ Kas Lebih: +' + Number(diff).toLocaleString('id-ID');
    } else {
        box.style.background = 'rgba(239, 68, 68, 0.15)';
        box.style.color = '#EF4444';
        box.textContent = '⚠️ Kas Kurang: -' + Number(Math.abs(diff)).toLocaleString('id-ID');
    }
}

async function submitOpenShift(e) {
    e.preventDefault();
    const btn = e.target.querySelector('[type=submit]');
    btn.disabled = true;
    const fd = new FormData(document.getElementById('formOpenShift'));

    try {
        const res = await fetch('/pos/shifts/open', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (data.success) {
            window.showToast && window.showToast('Shift berhasil dibuka!');
            location.reload();
        } else {
            alert(data.message || 'Gagal membuka shift');
        }
    } catch (err) {
        alert('Terjadi kesalahan jaringan.');
    } finally {
        btn.disabled = false;
    }
}

async function submitCloseShift(e) {
    e.preventDefault();
    const btn = e.target.querySelector('[type=submit]');
    btn.disabled = true;
    const fd = new FormData(document.getElementById('formCloseShift'));

    try {
        const res = await fetch('/pos/shifts/close', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (data.success) {
            window.showToast && window.showToast('Shift berhasil ditutup!');
            location.reload();
        } else {
            alert(data.message || 'Gagal menutup shift');
        }
    } catch (err) {
        alert('Terjadi kesalahan jaringan.');
    } finally {
        btn.disabled = false;
    }
}
</script>
<?= $this->endSection() ?>
