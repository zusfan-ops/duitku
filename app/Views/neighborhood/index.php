<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.neighborhood-page {
    padding-bottom: 110px;
    max-width: 680px;
    margin: 0 auto;
}
.rt-hero-card {
    background: linear-gradient(135deg, #065F46 0%, #059669 50%, #10B981 100%);
    border-radius: 24px;
    padding: 22px 24px;
    color: #ffffff;
    margin-bottom: 18px;
    box-shadow: 0 12px 32px rgba(5, 150, 105, 0.25);
    position: relative;
    overflow: hidden;
}
.rt-hero-card::after {
    content: '';
    position: absolute;
    right: -30px;
    bottom: -30px;
    width: 160px;
    height: 160px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 50%;
    pointer-events: none;
}
.rt-code-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.rt-code-badge:hover {
    background: rgba(255, 255, 255, 0.3);
}
.quick-action-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
.quick-action-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 20px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    text-decoration: none;
    color: var(--text-primary, #111827);
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}
.quick-action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    border-color: #10B981;
}
.quick-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}
.stat-pill {
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 20px;
    font-weight: 600;
}
.resident-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 16px;
    margin-bottom: 8px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="neighborhood-page">
    
    <!-- Hero Header RT -->
    <div class="rt-hero-card">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <span class="badge bg-white text-dark mb-2 px-2 py-1 rounded-pill" style="font-size: 11px; font-weight: 700;">
                    <?= esc($neighborhood['subdistrict']) ?>, <?= esc($neighborhood['city']) ?>
                </span>
                <h3 class="fw-bold mb-1" style="font-size: 22px;"><?= esc($neighborhood['name']) ?></h3>
                <p class="mb-0 opacity-75 small">RT <?= esc($neighborhood['rt']) ?> / RW <?= esc($neighborhood['rw']) ?> • Ketua RT: <?= esc($neighborhood['admin_name'] ?: 'Pengurus RT') ?></p>
            </div>
            <?php if ($isRtAdmin): ?>
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold" style="font-size: 12px;">👑 Ketua RT</span>
            <?php else: ?>
                <span class="badge bg-light text-success px-3 py-2 rounded-pill fw-bold" style="font-size: 12px;">
                    <?= ($user['residence_status'] === 'permanent') ? '🏠 Warga Tetap' : '🏢 Warga Domisili' ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 border-top border-white border-opacity-25">
            <div class="rt-code-badge" onclick="copyRtCode('<?= esc($neighborhood['unique_code']) ?>')" title="Klik untuk salin kode RT">
                <span>🔑 <?= esc($neighborhood['unique_code']) ?></span>
                <i class="bi bi-copy ms-1 opacity-75"></i>
            </div>
            <div class="small opacity-75">
                <i class="bi bi-people-fill me-1"></i> <?= (int)$neighborhood['total_verified_residents'] ?> Warga Terverifikasi
            </div>
        </div>
    </div>

    <!-- Modul Utama Community & Sharing -->
    <h6 class="fw-bold mb-3 text-secondary" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Layanan Komunitas & Sharing</h6>
    <div class="quick-action-grid">
        <a href="/neighborhood/tools" class="quick-action-card">
            <div class="d-flex justify-content-between align-items-center">
                <div class="quick-icon" style="background: #ECFDF5; color: #059669;">
                    🔨
                </div>
                <span class="stat-pill bg-success bg-opacity-10 text-success"><?= (int)$neighborhood['total_community_tools'] ?> Alat</span>
            </div>
            <div>
                <div class="fw-bold" style="font-size: 15px;">Pinjam Alat RT</div>
                <div class="text-muted small">Alat pertukangan, tenda, & kebersihan</div>
            </div>
        </a>

        <a href="/neighborhood/errands" class="quick-action-card">
            <div class="d-flex justify-content-between align-items-center">
                <div class="quick-icon" style="background: #EFF6FF; color: #2563EB;">
                    🛍️
                </div>
                <span class="stat-pill bg-primary bg-opacity-10 text-primary"><?= (int)$neighborhood['active_errands_count'] ?> Buka</span>
            </div>
            <div>
                <div class="fw-bold" style="font-size: 15px;">Titip Belanja</div>
                <div class="text-muted small">Jastip pasar & supermarket antar-warga</div>
            </div>
        </a>
    </div>

    <!-- Antrean Persetujuan Warga Baru (Khusus Ketua RT) -->
    <?php if ($isRtAdmin && !empty($pendingResidents)): ?>
        <div class="card border-warning border-opacity-50 shadow-sm rounded-4 mb-4" style="background: #FFFBEB;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-person-exclamation text-warning me-1"></i> Antrean Verifikasi Warga Baru (<?= count($pendingResidents) ?>)
                    </h6>
                </div>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($pendingResidents as $pr): ?>
                        <div class="resident-item bg-white">
                            <div>
                                <div class="fw-bold text-dark"><?= esc($pr['name']) ?></div>
                                <div class="text-muted small">
                                    Rumah: <strong><?= esc($pr['house_number'] ?: 'Belum diisi') ?></strong> • 
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary"><?= $pr['residence_status'] === 'permanent' ? 'KTP' : 'Domisili/Kost' ?></span>
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-success rounded-pill px-3" onclick="verifyResident(<?= (int)$pr['id'] ?>, 'approve')">
                                    <i class="bi bi-check-lg me-1"></i> Setujui
                                </button>
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-2" onclick="verifyResident(<?= (int)$pr['id'] ?>, 'reject')">
                                    Tolak
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Daftar Warga Terdaftar -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold text-secondary mb-0" style="font-size: 13px; text-transform: uppercase;">Daftar Warga RT (<?= count($residents) ?>)</h6>
        <button class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="shareRtInvite()">
            <i class="bi bi-share me-1"></i> Ajak Tetangga
        </button>
    </div>

    <div class="d-flex flex-column gap-2 mb-4">
        <?php foreach ($residents as $r): ?>
            <div class="resident-item">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" 
                         style="width: 40px; height: 40px; background: #059669; font-size: 14px;">
                        <?= strtoupper(substr($r['name'], 0, 2)) ?>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">
                            <?= esc($r['name']) ?>
                            <?php if ($r['role'] === 'rt_admin'): ?>
                                <span class="badge bg-warning text-dark ms-1" style="font-size: 10px;">Ketua RT</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted small">
                            Rumah No. <?= esc($r['house_number'] ?: '-') ?> • 
                            <span class="text-secondary"><?= $r['residence_status'] === 'permanent' ? 'Warga Tetap' : 'Warga Domisili' ?></span>
                        </div>
                    </div>
                </div>
                <?php if ($r['phone']): ?>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $r['phone']) ?>" target="_blank" class="btn btn-sm btn-light rounded-circle text-success" title="Hubungi WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<script>
function copyRtCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        alert('Kode unik RT (' + code + ') berhasil disalin! Bagikan kode ini kepada tetangga untuk bergabung.');
    });
}

function shareRtInvite() {
    const text = "Halo Tetangga! Yuk bergabung ke komunitas RT kita di aplikasi DuitKu untuk pinjam alat warga & titip belanja. Masukkan Kode Unik RT: <?= esc($neighborhood['unique_code']) ?>";
    if (navigator.share) {
        navigator.share({ title: 'Gabung RT <?= esc($neighborhood['name']) ?>', text: text });
    } else {
        copyRtCode('<?= esc($neighborhood['unique_code']) ?>');
    }
}

function verifyResident(userId, action) {
    if (!confirm('Apakah Anda yakin ingin ' + (action === 'approve' ? 'menyetujui' : 'menolak') + ' warga ini?')) return;

    fetch('/neighborhood/resident/verify', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'target_user_id=' + userId + '&action=' + action
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Terjadi kesalahan.');
        }
    });
}
</script>
<?= $this->endSection() ?>
