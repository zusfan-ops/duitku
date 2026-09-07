<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.arisan-detail-page { max-width: 680px; margin: 0 auto; padding-bottom: 120px; }
.ad-hero {
    background: linear-gradient(135deg, #7C3AED 0%, #8B5CF6 55%, #A78BFA 100%);
    border-radius: var(--radius-xl, 24px);
    padding: 24px 20px;
    color: #fff;
    margin-bottom: 18px;
    box-shadow: 0 12px 32px rgba(124, 58, 237, 0.28);
}
.ad-round-pill {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.35);
    padding: 4px 12px; border-radius: 30px; font-size: 11px; font-weight: 800;
}
.ad-amount { font-size: 28px; font-weight: 900; margin-top: 8px; }
.ad-info { font-size: 12px; opacity: 0.9; margin-top: 6px; line-height: 1.6; }
.progress-track { height: 8px; background: rgba(255,255,255,0.3); border-radius: 20px; margin-top: 14px; overflow: hidden; }
.progress-fill { height: 100%; background: #fff; border-radius: 20px; }

.member-row {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 12px 0; border-bottom: 1px solid var(--border, #EEF2F7);
}
.member-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    background: linear-gradient(135deg, #8B5CF6, #A78BFA); color: #fff;
    display: flex; align-items: center; justify-content: center; font-weight: 900; flex-shrink: 0;
}
.rotation-badge {
    width: 22px; height: 22px; border-radius: 50%; background: #EDE9FE; color: #6D28D9;
    display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 900;
}
.receipt-badge {
    padding: 4px 10px; border-radius: 20px; font-size: 10.5px; font-weight: 800;
}
.receipt-badge.done { background: #D1FAE5; color: #047857; }
.receipt-badge.owner { background: #FEF3C7; color: #B45309; }
.payment-chip {
    padding: 4px 10px; border-radius: 20px; font-size: 10.5px; font-weight: 800;
}
.payment-chip.paid { background: #D1FAE5; color: #047857; }
.payment-chip.unpaid { background: #FEE2E2; color: #B91C1C; }
.btn-advance {
    background: var(--primary, #059669); color: #fff; border: none;
    padding: 9px 16px; border-radius: 12px; font-size: 12px; font-weight: 800; cursor: pointer;
}
.owner-actions { display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="arisan-detail-page">
    <div class="ad-hero">
        <a href="/arisan" style="color:#fff; opacity:0.85; font-size:12px; text-decoration:none;">← Kembali</a>
        <div class="ad-round-pill" style="margin-top:8px;">🔄 Putaran ke-<?= (int)$group['current_round'] ?> dari <?= (int)$group['total_members'] ?></div>
        <div class="ad-amount">Setoran <?= $symbol ?> <?= number_format((float)$group['amount'], 0, ',', '.') ?></div>
        <div class="ad-info">
            👥 <?= (int)$group['total_members'] ?> anggota • <?= ucfirst(esc($group['frequency'])) ?><br>
            <?= esc($group['description'] ?: 'Arisan komunitas') ?>
            <?php if (!empty($group['neighborhood_name'])): ?><br>🏘️ <?= esc($group['neighborhood_name']) ?><?php endif; ?>
        </div>
        <div class="progress-track"><div class="progress-fill" style="width: <?= (int)$group['progress']['percent'] ?>%;"></div></div>
    </div>

    <?php if ($group['is_owner']): ?>
        <div class="owner-actions">
            <button type="button" class="btn-advance" onclick="advanceRound()">🔄 Buka Putaran Berikutnya</button>
            <button type="button" class="btn-primary" onclick="openMemberModal()">➕ Tambah Anggota</button>
        </div>
    <?php endif; ?>

    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-title"><span>👥</span> Anggota &amp; Giliran</div>
        </div>
        <?php if (empty($group['members'])): ?>
            <div style="text-align:center; padding:18px; color:var(--text-secondary); font-size:12px;">Belum ada anggota.</div>
        <?php else: ?>
            <?php foreach ($group['members'] as $m): ?>
                <div class="member-row">
                    <div class="resident-info" style="display:flex; align-items:center; gap:10px;">
                        <span class="rotation-badge"><?= (int)$m['rotation_order'] ?></span>
                        <div class="resident-avatar" style="background:linear-gradient(135deg,#8B5CF6,#A78BFA);">
                            <?= esc(strtoupper(substr($m['member_name'] ?? '?', 0, 1))) ?>
                        </div>
                        <div>
                            <div class="resident-name" style="font-size:13px; font-weight:800;">
                                <?= esc($m['member_name']) ?>
                                <?php if ((int)$m['user_id'] === (int)$group['user_id']): ?><span class="receipt-badge owner" style="margin-left:6px;">Owner</span><?php endif; ?>
                            </div>
                            <?php if ($m['has_received']): ?>
                                <div class="resident-house" style="font-size:10.5px; color:#059669;">✅ Sudah menerima (<?= esc($m['received_at']) ?>)</div>
                            <?php else: ?>
                                <div class="resident-house" style="font-size:10.5px; color:var(--text-secondary);">Belum menerima giliran</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                    $memberPayments = array_values(array_filter($group['payments'] ?? [], fn($p) => (int)$p['member_id'] === (int)$m['id']));
                    $currentPay = null;
                    foreach ($memberPayments as $mp) {
                        if ((int)$mp['round_number'] === (int)$group['current_round']) { $currentPay = $mp; break; }
                    }
                    ?>
                    <?php if ($currentPay): ?>
                        <span class="payment-chip <?= $currentPay['status'] === 'paid' ? 'paid' : 'unpaid' ?>">
                            <?= $currentPay['status'] === 'paid' ? '✓ Setoran Lunas' : 'Belum setor' ?>
                        </span>
                        <?php if ($group['is_owner'] && $currentPay['status'] !== 'paid'): ?>
                            <button type="button" class="btn-pay-sm" onclick="paySetoran(<?= (int)$currentPay['id'] ?>)">Catat</button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php
    $roundPayments = array_values(array_filter($group['payments'] ?? [], fn($p) => (int)$p['round_number'] === (int)$group['current_round']));
    $roundPaid = count(array_filter($roundPayments, fn($p) => $p['status'] === 'paid'));
    $roundTotal = count($roundPayments);
    ?>
    <?php if ($roundPayments): ?>
        <div class="info-card">
            <div class="info-card-header">
                <div class="info-card-title"><span>📊</span> Setoran Putaran ke-<?= (int)$group['current_round'] ?></div>
            </div>
            <div style="font-size:12.5px; color:var(--text-secondary); margin-bottom:10px;">
                <strong style="color:var(--text-primary);"><?= $roundPaid ?>/<?= $roundTotal ?></strong> anggota sudah setor.
            </div>
            <?php foreach ($roundPayments as $rp): ?>
                <div class="resident-row">
                    <div class="resident-name" style="font-size:12.5px;"><?= esc($rp['member_name']) ?></div>
                    <span class="status-badge <?= $rp['status'] === 'paid' ? 'paid' : 'unpaid' ?>">
                        <?= $rp['status'] === 'paid' ? '✓ Setor' : 'Belum' ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Tambah Anggota -->
<div class="modal-overlay" id="memberModal">
    <div class="modal-card">
        <div class="modal-header"><h3>➕ Tambah Anggota</h3><button type="button" class="modal-close" onclick="closeModal('memberModal')">×</button></div>
        <div class="modal-body">
            <label class="form-label">Nama Anggota</label>
            <input type="text" class="form-control" id="m-name" placeholder="Nama anggota">
            <label class="form-label">Nomor HP (opsional)</label>
            <input type="text" class="form-control" id="m-phone" placeholder="08xxxxxxxxxx">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary" onclick="saveMember()">Simpan</button>
            <button type="button" class="btn-secondary" onclick="closeModal('memberModal')">Batal</button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const GROUP_ID = <?= (int)$group['id'] ?>;

function openModal(id) { const el = document.getElementById(id); if (el) el.classList.add('open'); }
function closeModal(id) { const el = document.getElementById(id); if (el) el.classList.remove('open'); }
function openMemberModal() {
    document.getElementById('m-name').value = '';
    openModal('memberModal');
}

function saveMember() {
    fetch('/arisan/' + GROUP_ID + '/member', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            member_name: document.getElementById('m-name').value,
            phone: document.getElementById('m-phone').value,
        })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.success) location.reload();
    });
}

function paySetoran(paymentId) {
    if (!confirm('Catat setoran anggota ini sebagai LUNAS?')) return;
    fetch('/arisan/payment/pay', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ payment_id: paymentId, group_id: GROUP_ID })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.success) location.reload();
    });
}

function advanceRound() {
    if (!confirm('Buka putaran arisan berikutnya? Slot setoran baru akan dibuat untuk semua anggota.')) return;
    fetch('/arisan/' + GROUP_ID + '/advance', { method: 'POST' })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.success) location.reload();
    });
}
</script>
<?= $this->endSection() ?>