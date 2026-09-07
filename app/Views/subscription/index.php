<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.sub-page { max-width: 680px; margin: 0 auto; padding-bottom: 120px; }
.sub-hero {
    background: linear-gradient(135deg, #2563EB 0%, #3B82F6 55%, #60A5FA 100%);
    border-radius: var(--radius-xl, 24px);
    padding: 24px 20px;
    color: #fff;
    margin-bottom: 18px;
    box-shadow: 0 12px 32px rgba(37, 99, 235, 0.28);
}
.sub-total { font-size: 30px; font-weight: 900; margin-top: 6px; letter-spacing: -0.5px; }
.sub-total-sub { font-size: 12px; opacity: 0.9; margin-top: 2px; }
.hero-chip { display:inline-flex; gap:6px; align-items:center; background:rgba(255,255,255,0.2); padding:4px 12px; border-radius:30px; font-size:11px; font-weight:800; }

.sub-summary-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 18px; }
.sub-stat { background: var(--bg-card,#fff); border:1.5px solid var(--border,#E2E8F0); border-radius: 16px; padding: 12px; text-align:center; }
.sub-stat .sv { font-size: 18px; font-weight: 900; color: var(--text-primary,#0F172A); }
.sub-stat .sl { font-size: 10.5px; font-weight: 700; color: var(--text-secondary,#64748B); }
.sub-stat .sv.muted { color: var(--text-secondary); }
.sub-stat .sv.red { color: #EF4444; }

.sub-row {
    display: flex; align-items: center; gap: 12px; padding: 12px 0;
    border-bottom: 1px solid var(--border,#EEF2F7);
}
.sub-row:last-child { border-bottom: none; }
.sub-icon {
    width: 42px; height: 42px; border-radius: 14px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 18px;
}
.sub-name { font-size: 13.5px; font-weight: 800; color: var(--text-primary,#0F172A); }
.sub-cat { font-size: 10.5px; color: var(--text-secondary,#64748B); }
.sub-next { font-size: 10.5px; color: var(--text-secondary,#64748B); margin-top: 2px; }
.sub-next.due { color: #EF4444; font-weight: 800; }
.sub-amount { font-size: 14px; font-weight: 900; color: var(--text-primary,#0F172A); white-space: nowrap; }
.sub-cycle { font-size: 10px; color: var(--text-secondary,#64748B); text-align:right; }
.status-badge { padding: 3px 9px; border-radius: 20px; font-size: 10px; font-weight: 800; }
.status-badge.active { background:#D1FAE5; color:#047857; }
.status-badge.paused { background:#FEF3C7; color:#B45309; }
.status-badge.cancelled { background:#FEE2E2; color:#B91C1C; }
.status-badge.expired { background:#E2E8F0; color:#64748B; }
.sub-actions { display: flex; gap: 6px; align-items: center; }
.icon-btn {
    background:none; border:1px solid var(--border,#E2E8F0); border-radius:10px;
    width:32px; height:32px; cursor:pointer; font-size:13px; display:flex; align-items:center; justify-content:center;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="sub-page">
    <div class="sub-hero">
        <div class="rt-location-pill" style="background:rgba(255,255,255,0.2);">🔁 Subscription Tracker</div>
        <div class="sub-total"><?= $symbol ?> <?= number_format((float)$summary['total_monthly'], 0, ',', '.') ?><span style="font-size:14px; font-weight:700;"> /bulan</span></div>
        <div class="sub-total-sub">≈ <?= $symbol ?> <?= number_format((float)$summary['total_yearly'], 0, ',', '.') ?> per tahun</div>
        <div style="margin-top:14px;">
            <button type="button" class="btn-announcement-header" onclick="openAddModal()" style="background:#fff; color:#2563EB; border:none; font-weight:900;">➕ Tambah Langganan</button>
        </div>
    </div>

    <div class="sub-summary-row">
        <div class="sub-stat"><div class="sv"><?= (int)$summary['active_count'] ?></div><div class="sl">Aktif</div></div>
        <div class="sub-stat"><div class="sv muted"><?= (int)$summary['paused_count'] ?></div><div class="sl">Jeda</div></div>
        <div class="sub-stat"><div class="sv red"><?= (int)$summary['waste_count'] ?></div><div class="sl">Mubazir</div></div>
    </div>

    <?php if (!empty($upcoming)): ?>
        <div class="info-card">
            <div class="info-card-header">
                <div class="info-card-title"><span>⏰</span> Jatuh Tempo 7 Hari ke Depan</div>
            </div>
            <?php foreach ($upcoming as $u): ?>
                <div class="resident-row">
                    <div>
                        <div class="resident-name" style="font-size:12.5px;"><?= esc($u['name']) ?></div>
                        <div class="sub-next due">Tagihan <?= date('d M Y', strtotime($u['next_billing_date'])) ?></div>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div class="sub-amount"><?= $symbol ?> <?= number_format((float)$u['amount'], 0, ',', '.') ?></div>
                        <button type="button" class="btn-pay-sm" onclick="paySub(<?= (int)$u['id'] ?>)">Bayar</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-title"><span>📋</span> Semua Langganan</div>
        </div>

        <?php if (empty($subscriptions)): ?>
            <div style="text-align:center; padding:20px; color:var(--text-secondary); font-size:12.5px;">
                Belum ada langganan tercatat.
            </div>
        <?php else: ?>
            <?php foreach ($subscriptions as $s): ?>
                <div class="sub-row">
                    <div class="sub-icon" style="background:<?= esc($s['color'] ?? '#EFF6FF') ?>; color:<?= esc($s['color'] ?? '#2563EB') ?>;">
                        <?= esc($s['icon'] ?? '🔁') ?>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                            <span class="sub-name"><?= esc($s['name']) ?></span>
                            <span class="status-badge <?= esc($s['status']) ?>"><?= esc($s['status']) ?></span>
                        </div>
                        <div class="sub-cat"><?= esc($s['category']) ?><?= $s['category_name'] ? ' • ' . esc($s['category_name']) : '' ?></div>
                        <div class="sub-next <?= $s['status'] === 'active' && strtotime($s['next_billing_date']) <= strtotime('+7 days') ? 'due' : '' ?>">
                            Tagihan berikutnya: <?= $s['next_billing_date'] ? date('d M Y', strtotime($s['next_billing_date'])) : '—' ?>
                            <?php if ($s['is_waste']): ?> • 🚮 <strong>Mubazir</strong><?php endif; ?>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div class="sub-amount"><?= $symbol ?> <?= number_format((float)$s['amount'], 0, ',', '.') ?></div>
                        <div class="sub-cycle"><?= esc($s['billing_cycle']) ?></div>
                    </div>
                    <div class="sub-actions">
                        <button type="button" class="icon-btn" title="Catat Bayar" onclick="paySub(<?= (int)$s['id'] ?>)">💸</button>
                        <button type="button" class="icon-btn" title="Tandai Mubazir" onclick="toggleWaste(<?= (int)$s['id'] ?>, <?= $s['is_waste'] ? 1 : 0 ?>)">🚮</button>
                        <button type="button" class="icon-btn" title="Hapus" onclick="delSub(<?= (int)$s['id'] ?>)" style="color:#EF4444;">🗑️</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Tambah Langganan -->
<div class="modal-overlay" id="addModal">
    <div class="modal-card">
        <div class="modal-header"><h3>➕ Tambah Langganan</h3><button type="button" class="modal-close" onclick="closeModal('addModal')">×</button></div>
        <div class="modal-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div>
                    <label class="form-label">Nama</label>
                    <input type="text" class="form-control" id="s-name" placeholder="Netflix">
                </div>
                <div>
                    <label class="form-label">Ikon</label>
                    <input type="text" class="form-control" id="s-icon" placeholder="🎬">
                </div>
            </div>
            <label class="form-label">Kategori</label>
            <select class="form-control" id="s-category">
                <option>Hiburan</option><option>Streaming</option><option>Musik</option>
                <option>Produktivitas</option><option>Asuransi</option><option>Pendidikan</option>
                <option>Cloud Storage</option><option>Lainnya</option>
            </select>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div>
                    <label class="form-label">Nominal (Rp)</label>
                    <input type="text" class="form-control" id="s-amount" placeholder="79.000">
                </div>
                <div>
                    <label class="form-label">Siklus</label>
                    <select class="form-control" id="s-cycle">
                        <option value="weekly">Mingguan</option>
                        <option value="monthly" selected>Bulanan</option>
                        <option value="quarterly">3 Bulanan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>
            </div>
            <label class="form-label">Tanggal Tagihan Berikutnya</label>
            <input type="date" class="form-control" id="s-next">
            <label class="form-label">Catatan (opsional)</label>
            <input type="text" class="form-control" id="s-notes" placeholder="Akun bersama keluarga">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary" onclick="saveSub()">Simpan</button>
            <button type="button" class="btn-secondary" onclick="closeModal('addModal')">Batal</button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function openModal(id) { const el = document.getElementById(id); if (el) el.classList.add('open'); }
function closeModal(id) { const el = document.getElementById(id); if (el) el.classList.remove('open'); }

function openAddModal() {
    document.getElementById('s-name').value = '';
    document.getElementById('s-icon').value = '🔁';
    document.getElementById('s-amount').value = '';
    document.getElementById('s-cycle').value = 'monthly';
    document.getElementById('s-next').value = '<?= date('Y-m-d', strtotime('+1 month')) ?>';
    document.getElementById('s-notes').value = '';
    openModal('addModal');
}

function saveSub() {
    const amount = (document.getElementById('s-amount').value || '').replace(/\./g, '');
    fetch('/subscriptions/store', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            name: document.getElementById('s-name').value,
            icon: document.getElementById('s-icon').value,
            category: document.getElementById('s-category').value,
            amount: amount,
            billing_cycle: document.getElementById('s-cycle').value,
            next_billing_date: document.getElementById('s-next').value,
            notes: document.getElementById('s-notes').value,
        })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.success) location.reload();
    });
}

function paySub(id) {
    fetch('/subscriptions/pay/' + id, { method: 'POST' })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.success) location.reload();
    });
}

function toggleWaste(id, current) {
    fetch('/subscriptions/update/' + id, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ is_waste: current ? 0 : 1 })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.success) location.reload();
    });
}

function delSub(id) {
    if (!confirm('Hapus langganan ini?')) return;
    fetch('/subscriptions/delete/' + id, { method: 'POST' })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.success) location.reload();
    });
}
</script>
<?= $this->endSection() ?>