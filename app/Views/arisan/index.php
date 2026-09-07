<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.arisan-page { max-width: 680px; margin: 0 auto; padding-bottom: 120px; }
.arisan-hero {
    background: linear-gradient(135deg, #7C3AED 0%, #8B5CF6 55%, #A78BFA 100%);
    border-radius: var(--radius-xl, 24px);
    padding: 24px 20px;
    color: #fff;
    margin-bottom: 18px;
    box-shadow: 0 12px 32px rgba(124, 58, 237, 0.28);
}
.arisan-group-card {
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: 20px;
    padding: 18px;
    margin-bottom: 14px;
    background: var(--bg-card, #fff);
    cursor: pointer;
    transition: all 0.2s ease;
}
.arisan-group-card:hover { box-shadow: 0 6px 20px rgba(124,58,237,0.12); }
.ag-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
.ag-name { font-size: 15px; font-weight: 900; color: var(--text-primary, #0F172A); }
.ag-sub { font-size: 11px; color: var(--text-secondary, #64748B); margin-top: 2px; }
.ag-amount { font-size: 18px; font-weight: 900; color: #7C3AED; white-space: nowrap; }
.progress-track {
    height: 8px; background: #EDE9FE; border-radius: 20px; margin-top: 12px; overflow: hidden;
}
.progress-fill {
    height: 100%; background: linear-gradient(90deg, #7C3AED, #A78BFA);
    border-radius: 20px; transition: width 0.3s ease;
}
.ag-stats { display: flex; gap: 14px; margin-top: 10px; font-size: 11px; color: var(--text-secondary); }
.ag-stats span strong { color: var(--text-primary); }
.freq-badge {
    display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 800;
    background: #EDE9FE; color: #6D28D9;
}
.btn-add-member { margin-top: 8px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="arisan-page">
    <div class="arisan-hero">
        <div class="rt-location-pill" style="background:rgba(255,255,255,0.2);">
            <span>🤝</span> Arisan &amp; Tabungan Kelompok
        </div>
        <div style="font-size:22px; font-weight:900; margin:6px 0 4px;">Arisan Komunitas</div>
        <div style="font-size:12.5px; opacity:0.9;">Kelola anggota, setoran, dan giliran pencairan dana.</div>
        <div style="margin-top:16px;">
            <button type="button" class="btn-announcement-header" onclick="openCreateModal()" style="background:#fff; color:#7C3AED; border:none; font-weight:900;">
                ➕ Buat Kelompok Arisan
            </button>
        </div>
    </div>

    <?php if (empty($groups)): ?>
        <div class="info-card" style="text-align:center; padding:28px; color:var(--text-secondary); font-size:12.5px;">
            Belum ada kelompok arisan.<br>Buat kelompok pertama Anda untuk mulai menabung bersama.
        </div>
    <?php else: ?>
        <?php foreach ($groups as $g): ?>
            <div class="arisan-group-card" onclick="window.location='/arisan/<?= (int)$g['id'] ?>'">
                <div class="ag-top">
                    <div>
                        <div class="ag-name"><?= esc($g['name']) ?></div>
                        <div class="ag-sub">
                            👥 <?= (int)$g['total_members'] ?> anggota • 🔄 Putaran ke-<?= (int)$g['current_round'] ?>
                        </div>
                    </div>
                    <div class="ag-amount"><?= $symbol ?> <?= number_format((float)$g['amount'], 0, ',', '.') ?></div>
                </div>
                <div class="ag-stats">
                    <span class="freq-badge" style="margin-right:4px;"><?= ucfirst(esc($g['frequency'])) ?></span>
                    <?php if (!empty($g['neighborhood_name'])): ?><span>🏘️ <?= esc($g['neighborhood_name']) ?></span><?php endif; ?>
                    <span>Progres: <strong><?= (int)$g['progress']['percent'] ?>%</strong></span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill" style="width: <?= (int)$g['progress']['percent'] ?>%;"></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal: Buat Kelompok Arisan -->
<div class="modal-overlay" id="createModal" style="display:none;">
    <div class="modal-card">
        <div class="modal-header"><h3>➕ Buat Kelompok Arisan</h3><button type="button" class="modal-close" onclick="closeModal('createModal')">×</button></div>
        <div class="modal-body">
            <label class="form-label">Nama Kelompok</label>
            <input type="text" class="form-control" id="g-name" placeholder="Arisan Ibu-Ibu RW 03">
            <label class="form-label">Nominal Setoran (Rp)</label>
            <input type="text" class="form-control num-input" id="g-amount" placeholder="50.000">
            <label class="form-label">Frekuensi</label>
            <select class="form-control" id="g-frequency">
                <option value="weekly">Mingguan</option>
                <option value="biweekly">2 Mingguan</option>
                <option value="monthly" selected>Bulanan</option>
            </select>
            <label class="form-label">Mulai Dari</label>
            <input type="date" class="form-control" id="g-start" value="<?= date('Y-m-d') ?>">
            <label class="form-label">Nama Anda (Sebagai Anggota 1)</label>
            <input type="text" class="form-control" id="g-owner" value="<?= esc($user['name'] ?? '') ?>">
            <label class="form-label">Deskripsi (opsional)</label>
            <input type="text" class="form-control" id="g-desc" placeholder="Iuran arisan mingguan">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary" onclick="saveGroup()">Simpan</button>
            <button type="button" class="btn-secondary" onclick="closeModal('createModal')">Batal</button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function openCreateModal() {
    document.getElementById('g-start').value = '<?= date('Y-m-d') ?>';
    openModal('createModal');
}

function saveGroup() {
    const amount = (document.getElementById('g-amount').value || '').replace(/\./g, '');
    fetch('/arisan/store', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            name: document.getElementById('g-name').value,
            amount: amount,
            frequency: document.getElementById('g-frequency').value,
            start_date: document.getElementById('g-start').value,
            owner_name: document.getElementById('g-owner').value,
            description: document.getElementById('g-desc').value,
        })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.success) window.location = '/arisan/' + res.group_id;
    });
}
</script>
<?= $this->endSection() ?>