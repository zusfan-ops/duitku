<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.iuran-page { max-width: 680px; margin: 0 auto; padding-bottom: 120px; }
.iuran-hero {
    background: linear-gradient(135deg, #0F766E 0%, #0D9488 60%, #14B8A6 100%);
    border-radius: var(--radius-xl, 24px);
    padding: 24px 20px;
    color: #fff;
    margin-bottom: 18px;
    box-shadow: 0 12px 32px rgba(13, 148, 136, 0.28);
    position: relative;
    overflow: hidden;
}
.iuran-hero::after {
    content: '💰';
    position: absolute;
    right: -5px;
    bottom: -20px;
    font-size: 100px;
    opacity: 0.12;
    pointer-events: none;
}
.iuran-hero .label { font-size: 11px; font-weight: 800; opacity: 0.9; margin-bottom: 6px; }
.iuran-hero .period-pill {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.35);
    padding: 4px 12px; border-radius: 30px; font-size: 11px; font-weight: 800;
}
.iuran-hero .amount { font-size: 30px; font-weight: 900; letter-spacing: -0.5px; margin: 6px 0 2px; }
.iuran-hero .desc { font-size: 12.5px; opacity: 0.9; }

.summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 18px; }
.summary-card {
    background: var(--bg-card, #fff); border: 1.5px solid var(--border, #E2E8F0);
    border-radius: 18px; padding: 14px; text-align: center;
}
.summary-card .sc-val { font-size: 17px; font-weight: 900; color: var(--text-primary, #0F172A); }
.summary-card .sc-label { font-size: 10.5px; font-weight: 700; color: var(--text-secondary, #64748B); margin-top: 3px; }
.summary-card.green .sc-val { color: #059669; }
.summary-card.red .sc-val { color: #EF4444; }
.summary-card.amber .sc-val { color: #D97706; }

.resident-row {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 12px 0; border-bottom: 1px solid var(--border, #EEF2F7);
}
.resident-row:last-child { border-bottom: none; }
.resident-info { display: flex; align-items: center; gap: 10px; min-width: 0; }
.resident-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    background: linear-gradient(135deg, #0D9488, #14B8A6); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-weight: 900; font-size: 15px; flex-shrink: 0;
}
.resident-name { font-size: 13px; font-weight: 800; color: var(--text-primary, #0F172A); }
.resident-house { font-size: 10.5px; color: var(--text-secondary, #64748B); }
.status-badge {
    padding: 4px 10px; border-radius: 20px; font-size: 10.5px; font-weight: 800; white-space: nowrap;
}
.status-badge.paid { background: #D1FAE5; color: #047857; }
.status-badge.partial { background: #FEF3C7; color: #B45309; }
.status-badge.unpaid { background: #FEE2E2; color: #B91C1C; }
.status-badge.overdue { background: #FEE2E2; color: #B91C1C; }
.btn-pay-sm {
    background: var(--primary, #059669); color: #fff; border: none;
    padding: 7px 14px; border-radius: 12px; font-size: 11.5px; font-weight: 800; cursor: pointer;
}
.num-input { padding: 10px 12px; font-size: 15px; font-weight: 800; width: 100%; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="iuran-page">
    <div class="iuran-hero">
        <div class="rt-location-pill" style="background:rgba(255,255,255,0.2);">
            <span>🏛️</span> Iuran Warga — <?= esc($neighborhood['name']) ?>
        </div>
        <div class="period-pill">Periode: <strong><?= esc($periodMonth) ?></strong></div>
        <div class="amount"><?= $symbol ?> <span id="config-amount"><?= $config ? number_format((float)$config['amount'], 0, ',', '.') : '—' ?></span></div>
        <div class="desc" id="config-desc"><?= $config ? esc($config['description']) : 'Iuran belum diatur pengurus RT' ?></div>
        <?php if ($canManage): ?>
            <div style="margin-top:14px;">
                <button type="button" class="btn-announcement-header" onclick="openConfigModal()" style="background:rgba(255,255,255,0.22); color:#fff; border:1px solid rgba(255,255,255,0.4);">
                    ⚙️ Atur Nominal Iuran
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($summary): ?>
        <div class="summary-grid">
            <div class="summary-card green">
                <div class="sc-val"><?= $symbol ?> <?= number_format((float)$summary['total_paid'], 0, ',', '.') ?></div>
                <div class="sc-label">Terkumpul <?= $summary['paid_count'] ?>/<?= $summary['total_warga'] ?> warga</div>
            </div>
            <div class="summary-card red">
                <div class="sc-val"><?= number_format((float)$summary['total_arrears'], 0, ',', '.') ?></div>
                <div class="sc-label">Total Tunggakan</div>
            </div>
            <div class="summary-card amber">
                <div class="sc-val"><?= $summary['unpaid_count'] ?></div>
                <div class="sc-label">Belum Bayar</div>
            </div>
        </div>
    <?php endif; ?>

    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-title"><span>📋</span> Pembayaran Iuran Periode Ini</div>
        </div>

        <?php if (!$config): ?>
            <div style="text-align:center; padding:20px; color:var(--text-secondary); font-size:12.5px;">
                Belum ada konfigurasi iuran.<br><?= $canManage ? 'Klik "Atur Nominal Iuran" untuk memulai.' : 'Menunggu pengurus RT mengatur nominal iuran.' ?>
            </div>
        <?php elseif (empty($payments)): ?>
            <div style="text-align:center; padding:20px; color:var(--text-secondary); font-size:12.5px;">
                Belum ada catatan pembayaran.
            </div>
        <?php else: ?>
            <?php foreach ($payments as $p): ?>
                <div class="resident-row">
                    <div class="resident-info">
                        <div class="resident-avatar"><?= esc(strtoupper(substr($p['resident_name'] ?? '?', 0, 1))) ?></div>
                        <div>
                            <div class="resident-name"><?= esc($p['resident_name'] ?? 'Warga') ?>
                                <?php if (!empty($p['house_number'])): ?><span style="color:var(--text-secondary); font-size:10px;"> • No. <?= esc($p['house_number']) ?></span><?php endif; ?>
                            </div>
                            <div class="resident-house">
                                Terbayar: <?= $symbol ?> <?= number_format((float)$p['amount_paid'], 0, ',', '.') ?> / <?= number_format((float)$p['amount_expected'], 0, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span class="status-badge <?= esc($p['status']) ?>">
                            <?= $p['status'] === 'paid' ? 'Lunas' : ($p['status'] === 'partial' ? 'Sebagian' : 'Belum') ?>
                        </span>
                        <?php if ($canManage && $p['status'] !== 'paid'): ?>
                            <button type="button" class="btn-pay-sm" onclick="openPayModal(<?= (int)$p['id'] ?>, '<?= esc(addslashes($p['resident_name'] ?? 'Warga')) ?>', <?= (float)$p['amount_expected'] ?>, <?= (float)$p['amount_paid'] ?>)">
                                Catat
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Set Konfigurasi Iuran -->
<div class="modal-overlay" id="configModal">
    <div class="modal-card">
        <div class="modal-header"><h3>⚙️ Atur Iuran Warga</h3><button type="button" class="modal-close" onclick="closeModal('configModal')">×</button></div>
        <div class="modal-body">
            <label class="form-label">Jenis Periode</label>
            <select class="form-control" id="cfg-period">
                <option value="monthly">Bulanan</option>
                <option value="weekly">Mingguan</option>
                <option value="yearly">Tahunan</option>
            </select>
            <label class="form-label">Nominal Iuran (Rp)</label>
            <input type="text" class="form-control num-input" id="cfg-amount" placeholder="50.000">
            <label class="form-label">Deskripsi</label>
            <input type="text" class="form-control" id="cfg-desc" placeholder="Iuran Warga Bulanan">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary" onclick="saveConfig()">Simpan</button>
            <button type="button" class="btn-secondary" onclick="closeModal('configModal')">Batal</button>
        </div>
    </div>
</div>

<!-- Modal: Catat Pembayaran Iuran -->
<div class="modal-overlay" id="payModal">
    <div class="modal-card">
        <div class="modal-header"><h3>💸 Catat Pembayaran</h3><button type="button" class="modal-close" onclick="closeModal('payModal')">×</button></div>
        <div class="modal-body">
            <input type="hidden" id="pay-id">
            <div id="pay-name" style="font-weight:900; font-size:16px; margin-bottom:12px;"></div>
            <label class="form-label">Nominal Bayar (Rp)</label>
            <input type="text" class="form-control num-input" id="pay-amount" placeholder="50.000">
            <label class="form-label">Metode</label>
            <select class="form-control" id="pay-via">
                <option value="cash">Tunai</option>
                <option value="transfer">Transfer</option>
                <option value="cashless">E-Wallet</option>
            </select>
            <label class="form-label">Catatan (opsional)</label>
            <input type="text" class="form-control" id="pay-notes" placeholder="Sudah dibayar tunai">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary" onclick="savePay()">Simpan</button>
            <button type="button" class="btn-secondary" onclick="closeModal('payModal')">Batal</button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function openModal(id) { const el = document.getElementById(id); if (el) el.classList.add('open'); }
function closeModal(id) { const el = document.getElementById(id); if (el) el.classList.remove('open'); }

function openConfigModal() {
    document.getElementById('cfg-period').value = '<?= $config ? esc($config['period_type']) : 'monthly' ?>';
    document.getElementById('cfg-amount').value = '<?= $config ? number_format((float)$config['amount'], 0, ',', '.') : '' ?>';
    document.getElementById('cfg-desc').value = '<?= $config ? esc(addslashes($config['description'])) : '' ?>';
    openModal('configModal');
}

function saveConfig() {
    const amount = (document.getElementById('cfg-amount').value || '').replace(/\./g, '');
    fetch('/neighborhood/iuran/config', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            period_type: document.getElementById('cfg-period').value,
            amount: amount,
            description: document.getElementById('cfg-desc').value,
        })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.success) location.reload();
    });
}

function openPayModal(id, name, expected, paid) {
    document.getElementById('pay-id').value = id;
    document.getElementById('pay-name').textContent = name + ' — Tunggakan Rp ' + (expected - paid).toLocaleString('id-ID');
    document.getElementById('pay-amount').value = '';
    openModal('payModal');
}

function savePay() {
    const id = document.getElementById('pay-id').value;
    const amount = (document.getElementById('pay-amount').value || '').replace(/\./g, '');
    fetch('/neighborhood/iuran/pay', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            payment_id: id,
            amount: amount,
            paid_via: document.getElementById('pay-via').value,
            notes: document.getElementById('pay-notes').value,
        })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message || (res.success ? 'Pembayaran dicatat.' : 'Gagal.'));
        if (res.success) location.reload();
    });
}
</script>
<?= $this->endSection() ?>