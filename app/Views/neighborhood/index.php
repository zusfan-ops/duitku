<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.neighborhood-page {
    padding-bottom: 120px;
    max-width: 680px;
    margin: 0 auto;
}

/* ── Hero RT Header ── */
.rt-hero-card {
    background: linear-gradient(135deg, #064E3B 0%, #059669 60%, #10B981 100%);
    border-radius: var(--radius-xl, 24px);
    padding: 24px 22px;
    color: #ffffff;
    margin-bottom: 18px;
    box-shadow: 0 12px 32px rgba(5, 150, 105, 0.28);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.15);
}
.rt-hero-card::after {
    content: '';
    position: absolute;
    right: -25px;
    bottom: -25px;
    width: 140px;
    height: 140px;
    background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.rt-location-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.35);
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 800;
    margin-bottom: 8px;
    color: #ffffff;
}
.rt-hero-title {
    font-size: 22px;
    font-weight: 900;
    margin-bottom: 4px;
    letter-spacing: -0.4px;
}
.rt-hero-sub {
    font-size: 12.5px;
    opacity: 0.9;
    margin-bottom: 16px;
}
.rt-code-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.22);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 12.5px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #ffffff;
}
.rt-code-chip:hover {
    background: rgba(255, 255, 255, 0.35);
}

/* ── Quick Action Grid ── */
.quick-action-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
.quick-action-card {
    background: var(--bg-card, #ffffff);
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: var(--radius-lg, 18px);
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    text-decoration: none;
    color: var(--text-primary, #0F172A);
    transition: all 0.2s ease;
    box-shadow: var(--shadow-sm);
}
.quick-action-card:hover {
    transform: translateY(-2px);
    border-color: var(--primary, #059669);
    box-shadow: var(--shadow-md);
}
.quick-action-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.quick-action-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}
.quick-action-title {
    font-size: 14px;
    font-weight: 800;
    color: var(--text-primary, #0F172A);
    margin-bottom: 2px;
}
.quick-action-sub {
    font-size: 11.5px;
    color: var(--text-secondary, #64748B);
    line-height: 1.35;
}

/* ── Section Card Containers ── */
.section-card {
    background: var(--bg-card, #ffffff);
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: var(--radius-xl, 24px);
    padding: 20px;
    margin-bottom: 18px;
    box-shadow: var(--shadow-sm);
}
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.section-title {
    font-size: 15px;
    font-weight: 900;
    color: var(--text-primary, #0F172A);
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ── Resident Tile ── */
.resident-tile {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    background: var(--bg, #F8FAFC);
    border: 1px solid var(--border, #E2E8F0);
    border-radius: var(--radius-md, 14px);
    margin-bottom: 8px;
}
.resident-name {
    font-size: 13.5px;
    font-weight: 800;
    color: var(--text-primary, #0F172A);
}
.resident-sub {
    font-size: 11.5px;
    color: var(--text-secondary, #64748B);
}
.status-tag {
    font-size: 10.5px;
    font-weight: 800;
    padding: 3px 9px;
    border-radius: 20px;
}
.status-tag.permanent { background: rgba(5, 150, 105, 0.12); color: #059669; }
.status-tag.temporary { background: rgba(2, 132, 199, 0.12); color: #0284C7; }
.status-tag.pending   { background: rgba(217, 119, 6, 0.12);  color: #D97706; }

/* ── Approval Queue Card ── */
.approval-queue-card {
    border: 1.5px solid #FDE68A;
    background: #FFFDF5;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="neighborhood-page">
    
    <!-- ── Hero RT Banner ── -->
    <div class="rt-hero-card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <div class="rt-location-pill">
                <span>📍</span> <?= esc($neighborhood['subdistrict']) ?>, <?= esc($neighborhood['city']) ?>
            </div>
            <?php if ($isRtAdmin): ?>
                <span style="background: #FDE047; color: #713F12; font-size: 11px; font-weight: 900; padding: 3px 10px; border-radius: 20px;">👑 KETUA RT</span>
            <?php else: ?>
                <span style="background: rgba(255,255,255,0.25); color: #ffffff; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 20px;">
                    <?= ($user['residence_status'] === 'permanent') ? '🏠 Warga Tetap' : '🏢 Warga Domisili' ?>
                </span>
            <?php endif; ?>
        </div>

        <h1 class="rt-hero-title"><?= esc($neighborhood['name']) ?></h1>
        <p class="rt-hero-sub">
            RT <?= esc($neighborhood['rt']) ?> / RW <?= esc($neighborhood['rw']) ?> • Ketua RT: <?= esc($neighborhood['admin_name'] ?: 'Pengurus RT') ?>
        </p>

        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 12px;">
            <div class="rt-code-chip" onclick="navigator.clipboard.writeText('<?= esc($neighborhood['unique_code']) ?>'); alert('Kode unik RT disalin: <?= esc($neighborhood['unique_code']) ?>');">
                <span>🔑 <?= esc($neighborhood['unique_code']) ?></span>
                <span style="font-size: 11px; opacity: 0.8;">(Salin)</span>
            </div>
            <span style="font-size: 12px; font-weight: 700; opacity: 0.9;">
                👥 <?= count($residents) ?> Warga Terverifikasi
            </span>
        </div>
    </div>

    <!-- ── Quick Action Grid ── -->
    <div class="quick-action-grid">
        <a href="/neighborhood/tools" class="quick-action-card">
            <div class="quick-action-header">
                <div class="quick-action-icon" style="background: #ECFDF5;">🔨</div>
                <span style="font-size: 10.5px; font-weight: 800; color: #059669; background: rgba(5,150,105,0.1); padding: 2px 8px; border-radius: 12px;">Katalog</span>
            </div>
            <div>
                <div class="quick-action-title">Pinjam Alat Warga</div>
                <div class="quick-action-sub">Sewa mesin rumput, bor listrik, tangga, dan tenda RT.</div>
            </div>
        </a>

        <a href="/neighborhood/errands" class="quick-action-card">
            <div class="quick-action-header">
                <div class="quick-action-icon" style="background: #EFF6FF;">🛍️</div>
                <span style="font-size: 10.5px; font-weight: 800; color: #0284C7; background: rgba(2,132,199,0.1); padding: 2px 8px; border-radius: 12px;">Titip Beli</span>
            </div>
            <div>
                <div class="quick-action-title">Titip Belanja</div>
                <div class="quick-action-sub">Bantu belanja tetangga atau titip kebutuhan pasar.</div>
            </div>
        </a>
    </div>

    <!-- ── Approval Queue (Hanya untuk Admin RT) ── -->
    <?php if ($isRtAdmin && !empty($pendingResidents)): ?>
    <div class="section-card approval-queue-card">
        <div class="section-header">
            <div class="section-title" style="color: #B45309;">
                <span>⏳</span> Antrean Approval Warga Baru (<?= count($pendingResidents) ?>)
            </div>
        </div>

        <?php foreach ($pendingResidents as $pr): ?>
            <div class="resident-tile" id="pendingRow-<?= $pr['id'] ?>">
                <div>
                    <div class="resident-name"><?= esc($pr['name']) ?></div>
                    <div class="resident-sub">Rumah: <?= esc($pr['house_number'] ?: '-') ?> • <?= ($pr['residence_status'] === 'permanent') ? 'Warga Tetap' : 'Warga Domisili' ?></div>
                </div>
                <div style="display: flex; gap: 6px;">
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" onclick="verifyResident(<?= $pr['id'] ?>, 'reject')" style="font-size: 11px; font-weight: 700;">Tolak</button>
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1" onclick="verifyResident(<?= $pr['id'] ?>, 'approve')" style="font-size: 11px; font-weight: 800; background: #059669;">Setujui ✓</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── Warga Terverifikasi ── -->
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">
                <span>👥</span> Daftar Warga RT (<?= count($residents) ?>)
            </div>
        </div>

        <?php if (empty($residents)): ?>
            <p class="text-muted small text-center py-3">Belum ada warga lain yang terdaftar di RT ini.</p>
        <?php else: ?>
            <?php foreach ($residents as $r): ?>
                <div class="resident-tile">
                    <div>
                        <div class="resident-name"><?= esc($r['name']) ?></div>
                        <div class="resident-sub">Rumah: <?= esc($r['house_number'] ?: '-') ?></div>
                    </div>
                    <div>
                        <span class="status-tag <?= $r['residence_status'] === 'permanent' ? 'permanent' : 'temporary' ?>">
                            <?= $r['residence_status'] === 'permanent' ? '🏠 Tetap' : '🏢 Domisili' ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<script>
function verifyResident(userId, action) {
    if (!confirm('Apakah Anda yakin ingin ' + (action === 'approve' ? 'menyetujui' : 'menolak') + ' warga ini?')) return;

    const fd = new FormData();
    fd.append('target_user_id', userId);
    fd.append('action', action);

    fetch('/neighborhood/resident/verify', {
        method: 'POST',
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Status berhasil diperbarui.');
            const row = document.getElementById('pendingRow-' + userId);
            if (row) row.remove();
        } else {
            alert(data.message || 'Gagal memproses.');
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan jaringan.');
    });
}
</script>
<?= $this->endSection() ?>
