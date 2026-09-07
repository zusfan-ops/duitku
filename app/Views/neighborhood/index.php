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
    background: linear-gradient(135deg, #064E3B 0%, #065F46 50%, #047857 100%);
    border-radius: var(--radius-xl, 24px);
    padding: 24px 20px;
    color: #ffffff;
    margin-bottom: 18px;
    box-shadow: 0 12px 32px rgba(6, 78, 59, 0.28);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.15);
}
.rt-hero-card::after {
    content: '🏛️';
    position: absolute;
    right: -10px;
    bottom: -15px;
    font-size: 96px;
    opacity: 0.12;
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
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #ffffff;
}
.rt-code-chip:hover {
    background: rgba(255, 255, 255, 0.35);
}

/* ── Informative Card Base ── */
.info-card {
    background: var(--bg-card, #ffffff);
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: var(--radius-xl, 20px);
    padding: 20px;
    margin-bottom: 18px;
    box-shadow: var(--shadow-sm, 0 2px 8px rgba(15,23,42,0.04));
    transition: all 0.2s ease;
}
.info-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.info-card-title {
    font-size: 15px;
    font-weight: 900;
    color: var(--text-primary, #0F172A);
    display: flex;
    align-items: center;
    gap: 8px;
}
.info-card-action {
    font-size: 12px;
    font-weight: 800;
    color: var(--primary, #059669);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
}
.info-card-action:hover {
    text-decoration: underline;
}

/* ── Kas RT Metric Highlight ── */
.kas-stat-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 14px;
}
.kas-stat-box {
    background: var(--bg, #F8FAFC);
    border: 1px solid var(--border, #E2E8F0);
    border-radius: 14px;
    padding: 12px 10px;
    text-align: center;
}
.kas-stat-label {
    font-size: 10.5px;
    font-weight: 700;
    color: var(--text-secondary, #64748B);
    margin-bottom: 4px;
}
.kas-stat-val {
    font-size: 14px;
    font-weight: 900;
    color: var(--text-primary, #0F172A);
}
.kas-stat-val.primary { color: #059669; }
.kas-stat-val.danger { color: #EF4444; }

/* ── Compact List Rows ── */
.compact-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.compact-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    background: var(--bg, #F8FAFC);
    border: 1px solid var(--border, #E2E8F0);
    border-radius: 14px;
    gap: 12px;
}
.compact-item-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
}
.compact-item-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.compact-item-icon.in { background: #ECFDF5; color: #059669; }
.compact-item-icon.out { background: #FEF2F2; color: #EF4444; }
.compact-item-icon.event { background: #EFF6FF; color: #2563EB; }
.compact-title {
    font-size: 13px;
    font-weight: 800;
    color: var(--text-primary, #0F172A);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.compact-sub {
    font-size: 11px;
    color: var(--text-secondary, #64748B);
}
.compact-amount {
    font-size: 13px;
    font-weight: 900;
    flex-shrink: 0;
}
.compact-amount.in { color: #059669; }
.compact-amount.out { color: #EF4444; }

/* ── Announcement Cards ── */
.announcement-card {
    background: var(--bg, #F8FAFC);
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: 16px;
    padding: 14px;
    margin-bottom: 10px;
    position: relative;
}
.announcement-card.pinned {
    border-color: #FDE68A;
    background: #FFFDF5;
}
.announcement-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 8px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    margin-bottom: 6px;
}
.announcement-badge.Penting { background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; }
.announcement-badge.Info { background: #EFF6FF; color: #1E40AF; border: 1px solid #BFDBFE; }
.announcement-badge.Darurat { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }
.announcement-badge.Kegiatan { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }

/* ── Discussion Feed Cards ── */
.discussion-card {
    background: var(--bg, #F8FAFC);
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 12px;
}
.discussion-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}
.discussion-author-box {
    display: flex;
    align-items: center;
    gap: 10px;
}
.discussion-avatar {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    background: #059669;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 14px;
}
.discussion-category-pill {
    padding: 2px 8px;
    border-radius: 8px;
    font-size: 10px;
    font-weight: 800;
    background: #E2E8F0;
    color: #334155;
}

/* ── Buttons ── */
.btn-primary-sm {
    background: linear-gradient(135deg, #059669 0%, #10B981 100%);
    color: #ffffff !important;
    border: none;
    border-radius: 20px;
    padding: 7px 15px;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    transition: all 0.2s ease;
    text-decoration: none;
}
.btn-primary-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    filter: brightness(1.05);
}

.btn-announcement-header {
    background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}
.btn-announcement-header:hover {
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-secondary-sm {
    background: var(--bg, #F8FAFC);
    color: var(--text-primary, #0F172A);
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: 20px;
    padding: 7px 15px;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}
.btn-secondary-sm:hover {
    background: var(--border, #E2E8F0);
}

/* ── Main Action & Submit Buttons ── */
.btn-create-submit {
    width: 100%;
    padding: 13px 20px;
    border: none;
    border-radius: 16px;
    background: linear-gradient(135deg, #059669 0%, #10B981 100%);
    color: #ffffff !important;
    font-size: 14px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    transition: all 0.2s ease;
    font-family: inherit;
    text-decoration: none;
    outline: none;
}
.btn-create-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
    filter: brightness(1.05);
}
.btn-create-submit:active {
    transform: translateY(0);
}
.btn-create-submit:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.btn-announcement-submit {
    background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%);
    box-shadow: 0 4px 14px rgba(59, 130, 246, 0.35);
}
.btn-announcement-submit:hover {
    box-shadow: 0 6px 18px rgba(59, 130, 246, 0.45);
}

.btn-comment-reply {
    background: none;
    border: none;
    color: #059669;
    font-size: 11px;
    font-weight: 800;
    cursor: pointer;
    padding: 3px 8px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: background 0.15s;
}
.btn-comment-reply:hover {
    background: rgba(5, 150, 105, 0.1);
}

.resident-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(5, 150, 105, 0.12);
    color: #059669;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 14px;
    flex-shrink: 0;
}
.role-badge {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}
.role-badge.rt-admin { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }
.role-badge.treasurer { background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; }
.role-badge.resident { background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0; }

/* ── Modal Bottom Sheet Backdrop ── */
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
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="neighborhood-page">
    
    <!-- ── 1. HERO CARD RT ── -->
    <div class="rt-hero-card">
        <div class="rt-location-pill">
            <span>📍</span> RT <?= esc($neighborhood['rt']) ?> / RW <?= esc($neighborhood['rw']) ?> • Kel. <?= esc($neighborhood['subdistrict']) ?>
        </div>
        <div class="rt-hero-title"><?= esc($neighborhood['name']) ?></div>
        <div class="rt-hero-sub">
            Kec. <?= esc($neighborhood['district']) ?>, <?= esc($neighborhood['city']) ?>, <?= esc($neighborhood['province']) ?>
        </div>
        
        <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
            <div class="rt-code-chip" onclick="copyRtCode('<?= esc($neighborhood['unique_code']) ?>')">
                <span>🔑 Kode Unik: <strong><?= esc($neighborhood['unique_code']) ?></strong></span>
                <span style="font-size: 14px;">📋</span>
            </div>
            
            <?php if ($isRtAdmin): ?>
                <span class="role-badge rt-admin" style="color: #ffffff; background: rgba(255,255,255,0.25); border-color: rgba(255,255,255,0.4);">
                    👑 Ketua RT
                </span>
            <?php elseif ($isTreasurer): ?>
                <span class="role-badge treasurer" style="color: #ffffff; background: rgba(255,255,255,0.25); border-color: rgba(255,255,255,0.4);">
                    💼 Bendahara RT
                </span>
            <?php else: ?>
                <span class="role-badge resident" style="color: #ffffff; background: rgba(255,255,255,0.2); border-color: rgba(255,255,255,0.3);">
                    👤 Warga Terdaftar
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── 2. CARD PENGUMUMAN RESMI RT ── -->
    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-title">
                <span>📢</span> Papan Pengumuman RT
            </div>
            <?php if ($canManageKas): ?>
                <button type="button" class="btn-primary-sm btn-announcement-header" onclick="openAnnouncementModal()">
                    <span>📢</span> Buat Pengumuman
                </button>
            <?php endif; ?>
        </div>

        <?php if (empty($announcements)): ?>
            <div style="text-align: center; padding: 18px; color: var(--text-secondary); font-size: 12px;">
                Belum ada pengumuman resmi dari pengurus RT.
            </div>
        <?php else: ?>
            <div>
                <?php foreach ($announcements as $ann): ?>
                    <div class="announcement-card <?= !empty($ann['is_pinned']) ? 'pinned' : '' ?>">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <span class="announcement-badge <?= esc($ann['badge'] ?? 'Info') ?>">
                                    <?= !empty($ann['is_pinned']) ? '📌 ' : '' ?><?= esc($ann['badge'] ?? 'Info') ?>
                                </span>
                                <h4 style="font-size: 14px; font-weight: 900; margin: 0 0 4px; color: var(--text-primary);">
                                    <?= esc($ann['title']) ?>
                                </h4>
                                <div style="font-size: 10.5px; color: var(--text-secondary); margin-bottom: 8px;">
                                    Oleh: <?= esc($ann['author_name'] ?? 'Pengurus RT') ?> • <?= date('d M Y, H:i', strtotime($ann['created_at'])) ?> WIB
                                </div>
                            </div>
                            <?php if ($canManageKas): ?>
                                <button type="button" onclick="deleteAnnouncement(<?= $ann['id'] ?>)" style="background:none; border:none; color:#EF4444; cursor:pointer; font-size:14px;" title="Hapus Pengumuman">
                                    🗑️
                                </button>
                            <?php endif; ?>
                        </div>
                        <p style="font-size: 12.5px; color: var(--text-secondary); margin: 0; line-height: 1.5; white-space: pre-line;">
                            <?= esc($ann['content']) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── 3. CARD KAS RT (LAPORAN KEUANGAN) ── -->
    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-title">
                <span>🏛️</span> Buku Kas RT &amp; Transparansi
            </div>
            <?php if ($canManageKas): ?>
                <button type="button" class="btn-primary-sm" onclick="openKasModal()">
                    <span>➕</span> Catat Kas
                </button>
            <?php endif; ?>
        </div>

        <div class="kas-stat-grid">
            <div class="kas-stat-box">
                <div class="kas-stat-label">Saldo Kas RT</div>
                <div class="kas-stat-val primary"><?= $symbol ?> <?= number_format($kasSummary['balance'], 0, ',', '.') ?></div>
            </div>
            <div class="kas-stat-box">
                <div class="kas-stat-label">Pemasukan</div>
                <div class="kas-stat-val"><?= $symbol ?> <?= number_format($kasSummary['total_in'], 0, ',', '.') ?></div>
            </div>
            <div class="kas-stat-box">
                <div class="kas-stat-label">Pengeluaran</div>
                <div class="kas-stat-val danger"><?= $symbol ?> <?= number_format($kasSummary['total_out'], 0, ',', '.') ?></div>
            </div>
        </div>

        <div style="font-size: 11px; color: #065F46; background: #ECFDF5; padding: 8px 12px; border-radius: 10px; margin-bottom: 12px; border: 1px solid #A7F3D0;">
            💡 <strong>Transparansi Warga:</strong> Seluruh pencatatan kas (iuran, sewa alat, donasi &amp; pengeluaran) dapat dipantau oleh seluruh warga.
        </div>

        <?php if (empty($kasLedger)): ?>
            <div style="text-align: center; padding: 18px; color: var(--text-secondary); font-size: 12px;">
                Belum ada transaksi kas dicatat.
            </div>
        <?php else: ?>
            <div class="compact-list">
                <?php foreach (array_slice($kasLedger, 0, 4) as $k): ?>
                    <div class="compact-item">
                        <div class="compact-item-left">
                            <div class="compact-item-icon <?= $k['type'] === 'in' ? 'in' : 'out' ?>">
                                <?= $k['type'] === 'in' ? '↗️' : '↘️' ?>
                            </div>
                            <div>
                                <div class="compact-title"><?= esc($k['category']) ?> <?= $k['description'] ? '— ' . esc($k['description']) : '' ?></div>
                                <div class="compact-sub"><?= date('d M Y', strtotime($k['date'])) ?> • Oleh: <?= esc($k['recorded_by_name'] ?? 'Pengurus') ?></div>
                            </div>
                        </div>
                        <div class="compact-amount <?= $k['type'] === 'in' ? 'in' : 'out' ?>">
                            <?= $k['type'] === 'in' ? '+' : '-' ?> <?= $symbol ?> <?= number_format($k['amount'], 0, ',', '.') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── 4. CARD ALAT BERSAMA (TOOL SHARING) ── -->
    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-title">
                <span>🪚</span> Pinjam Alat Bersama RT
            </div>
            <a href="/neighborhood/tools" class="info-card-action">
                Buka Katalog →
            </a>
        </div>

        <div style="display: flex; gap: 10px; margin-bottom: 12px;">
            <div class="kas-stat-box" style="flex: 1;">
                <div class="kas-stat-label">Alat Siap Pinjam</div>
                <div class="kas-stat-val primary"><?= $toolsAvailable ?> Alat</div>
            </div>
            <div class="kas-stat-box" style="flex: 1;">
                <div class="kas-stat-label">Sedang Dipinjam</div>
                <div class="kas-stat-val"><?= $toolsRented ?> Alat</div>
            </div>
            <div class="kas-stat-box" style="flex: 1;">
                <div class="kas-stat-label">Tarif Kas RT</div>
                <div class="kas-stat-val primary"><?= $symbol ?> <?= number_format($neighborhood['tool_rental_fee'] ?? 2000, 0, ',', '.') ?></div>
            </div>
        </div>

        <a href="/neighborhood/tools" style="display: block; text-decoration: none;">
            <button type="button" class="btn-secondary-sm" style="width: 100%; justify-content: center; padding: 10px;">
                <span>🛠️</span> Jelajahi &amp; Ajukan Pinjam Alat Warga
            </button>
        </a>
    </div>

    <!-- ── 5. CARD AGENDA KEGIATAN WARGA ── -->
    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-title">
                <span>📅</span> Agenda &amp; Jadwal Kegiatan RT
            </div>
            <?php if ($canManageKas): ?>
                <button type="button" class="btn-primary-sm" onclick="openActivityModal()">
                    <span>➕</span> Tambah Agenda
                </button>
            <?php endif; ?>
        </div>

        <?php if (empty($activities)): ?>
            <div style="text-align: center; padding: 20px; color: var(--text-secondary); font-size: 12px;">
                Belum ada agenda kegiatan mendatang.
            </div>
        <?php else: ?>
            <div class="compact-list">
                <?php foreach ($activities as $act): ?>
                    <div class="compact-item">
                        <div class="compact-item-left">
                            <div class="compact-item-icon event">
                                📌
                            </div>
                            <div>
                                <div class="compact-title"><?= esc($act['title']) ?></div>
                                <div class="compact-sub">
                                    🗓️ <?= date('d M Y', strtotime($act['event_date'])) ?> • ⏰ <?= esc($act['event_time'] ?? '08:00') ?> WIB • 📍 <?= esc($act['location']) ?>
                                </div>
                            </div>
                        </div>
                        <?php if ($canManageKas): ?>
                            <button type="button" onclick="deleteActivity(<?= $act['id'] ?>)" style="background:none; border:none; color:#EF4444; cursor:pointer; font-size:14px;" title="Hapus Agenda">
                                🗑️
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── 6. CARD FORUM DISKUSI WARGA ── -->
    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-title">
                <span>💬</span> Forum Diskusi Warga
            </div>
            <button type="button" class="btn-primary-sm" onclick="openDiscussionModal()">
                <span>✏️</span> Tulis Topik
            </button>
        </div>

        <p style="font-size: 12px; color: var(--text-secondary); margin: 0 0 12px 0;">
            Ruang diskusi terbuka untuk menyampaikan usul, info keamanan, kehilangan, atau saran lingkungan.
        </p>

        <?php if (empty($discussions)): ?>
            <div style="text-align: center; padding: 20px; color: var(--text-secondary); font-size: 12px;">
                Belum ada postingan di forum diskusi warga. Yuk mulai diskusi pertama!
            </div>
        <?php else: ?>
            <div>
                <?php foreach ($discussions as $disc): ?>
                    <div class="discussion-card">
                        <div class="discussion-header">
                            <div class="discussion-author-box">
                                <div class="discussion-avatar">
                                    <?= strtoupper(substr($disc['author_name'] ?? 'W', 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-size: 13px; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 6px;">
                                        <span><?= esc($disc['author_name'] ?? 'Warga') ?></span>
                                        <?php if (in_array(strtolower($disc['author_role'] ?? ''), ['rt_admin', 'admin'])): ?>
                                            <span class="role-badge rt-admin" style="font-size: 9px;">Ketua RT</span>
                                        <?php elseif (in_array(strtolower($disc['author_role'] ?? ''), ['rt_treasurer', 'bendahara'])): ?>
                                            <span class="role-badge treasurer" style="font-size: 9px;">Bendahara</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 10.5px; color: var(--text-secondary);">
                                        Rumah: <?= esc($disc['house_number'] ?? '-') ?> • <?= date('d M Y, H:i', strtotime($disc['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                            <span class="discussion-category-pill"><?= esc($disc['category'] ?? 'Umum') ?></span>
                        </div>

                        <?php if (!empty($disc['title'])): ?>
                            <h4 style="font-size: 14px; font-weight: 900; margin: 0 0 6px; color: var(--text-primary);">
                                <?= esc($disc['title']) ?>
                            </h4>
                        <?php endif; ?>

                        <p style="font-size: 12.5px; color: var(--text-secondary); margin: 0 0 12px; line-height: 1.5; white-space: pre-line;">
                            <?= esc($disc['content']) ?>
                        </p>

                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 10px;">
                            <button type="button" class="btn-secondary-sm" style="padding: 4px 10px; font-size: 11px;" onclick="openDiscussionDetail(<?= $disc['id'] ?>)">
                                💬 <?= (int)($disc['comments_count'] ?? 0) ?> Komentar
                            </button>
                            <?php if ($isRtAdmin || $disc['user_id'] == session()->get('user_id')): ?>
                                <button type="button" onclick="deleteDiscussion(<?= $disc['id'] ?>)" style="background:none; border:none; color:#EF4444; cursor:pointer; font-size:12px;" title="Hapus Postingan">
                                    🗑️ Hapus
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── 7. CARD TITIP BELANJA ANTAR-WARGA ── -->
    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-title">
                <span>🛍️</span> Titip Belanja Antar-Warga
            </div>
            <a href="/neighborhood/errands" class="info-card-action">
                Buka Sesi Belanja →
            </a>
        </div>
        <p style="font-size: 12px; color: var(--text-secondary); margin: 0 0 12px 0;">
            Tetangga mau ke pasar atau supermarket? Titip belanjaan tanpa keluar rumah dan hemat ongkos kirim.
        </p>
        <a href="/neighborhood/errands" style="display: block; text-decoration: none;">
            <button type="button" class="btn-secondary-sm" style="width: 100%; justify-content: center; padding: 10px;">
                <span>🛒</span> Lihat Sesi Belanja Terbuka / Buka Titipan
            </button>
        </a>
    </div>

    <!-- ── 8. CARD DAFTAR WARGA & PENGURUS ── -->
    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-title">
                <span>👥</span> Struktur Pengurus &amp; Warga (<?= count($residents) ?>)
            </div>
        </div>

        <?php if (!empty($pendingResidents) && $isRtAdmin): ?>
            <div style="background: #FEF3C7; border: 1.5px solid #FDE68A; border-radius: 14px; padding: 14px; margin-bottom: 14px;">
                <strong style="font-size: 12.5px; color: #92400E; display: block; margin-bottom: 8px;">
                    ⚠️ Antrean Permintaan Warga Baru (<?= count($pendingResidents) ?>)
                </strong>
                <div class="compact-list">
                    <?php foreach ($pendingResidents as $pr): ?>
                        <div class="compact-item" style="background: #ffffff;">
                            <div>
                                <div class="compact-title"><?= esc($pr['name']) ?> (Rumah: <?= esc($pr['house_number'] ?? '-') ?>)</div>
                                <div class="compact-sub"><?= esc($pr['phone'] ?? $pr['email']) ?></div>
                            </div>
                            <div style="display: flex; gap: 6px;">
                                <button type="button" class="btn-primary-sm" style="padding: 4px 10px; font-size: 11px;" onclick="verifyResident(<?= $pr['id'] ?>, 'approve')">
                                    Setujui
                                </button>
                                <button type="button" class="btn-secondary-sm" style="padding: 4px 10px; font-size: 11px; color:#EF4444;" onclick="verifyResident(<?= $pr['id'] ?>, 'reject')">
                                    Tolak
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="compact-list">
            <?php foreach ($residents as $res): ?>
                <?php 
                    $roleStr = strtolower(trim((string)($res['role'] ?? '')));
                    $isMemberRtAdmin = in_array($roleStr, ['rt_admin', 'admin', 'administrator'], true);
                    $isMemberTreasurer = in_array($roleStr, ['rt_treasurer', 'bendahara'], true);
                ?>
                <div class="compact-item">
                    <div class="compact-item-left">
                        <div class="resident-avatar">
                            <?= strtoupper(substr($res['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="compact-title" style="display: flex; align-items: center; gap: 6px;">
                                <span><?= esc($res['name']) ?></span>
                                <?php if ($isMemberRtAdmin): ?>
                                    <span class="role-badge rt-admin">Ketua RT</span>
                                <?php elseif ($isMemberTreasurer): ?>
                                    <span class="role-badge treasurer">Bendahara</span>
                                <?php else: ?>
                                    <span class="role-badge resident"><?= $res['residence_status'] === 'permanent' ? 'Tetap' : 'Domisili' ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="compact-sub">
                                Rumah No. <?= esc($res['house_number'] ?? '-') ?> • <?= esc($res['phone'] ?? $res['email']) ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($isRtAdmin && !$isMemberRtAdmin): ?>
                        <div>
                            <?php if ($isMemberTreasurer): ?>
                                <button type="button" class="btn-secondary-sm" style="padding: 4px 8px; font-size: 10.5px; color:#64748B;" onclick="changeMemberRole(<?= $res['id'] ?>, 'user', '<?= esc($res['name']) ?>')">
                                    Hapus Bendahara
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn-secondary-sm" style="padding: 4px 8px; font-size: 10.5px; color:#059669;" onclick="changeMemberRole(<?= $res['id'] ?>, 'rt_treasurer', '<?= esc($res['name']) ?>')">
                                    👑 Jadikan Bendahara
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- ── MODAL BUAT PENGUMUMAN RT ── -->
<div id="modalAnnouncement" class="modal-overlay" onclick="if(event.target===this)closeAnnouncementModal()">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <h3 style="font-size: 17px; font-weight: 900; margin: 0 0 6px; text-align: center;">📢 Terbitkan Pengumuman Resmi RT</h3>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; margin: 0 0 16px;">
            Pengumuman akan disiarkan langsung di papan pengumuman RT seluruh warga
        </p>

        <form id="formAnnouncement">
            <?= csrf_field() ?>
            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Judul Pengumuman</label>
                <input type="text" name="title" class="create-input-text" placeholder="Contoh: Pemadaman Listrik Sementara dari PLN" required style="width: 100%; padding: 10px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--bg);">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px;">
                <div>
                    <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Kategori Badge</label>
                    <select name="badge" class="create-input-text" style="width: 100%; padding: 10px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--bg);">
                        <option value="Info">ℹ️ Info Biasa</option>
                        <option value="Penting">⚠️ Penting</option>
                        <option value="Darurat">🚨 Darurat</option>
                        <option value="Kegiatan">📅 Kegiatan</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Sematkan (Pin)</label>
                    <select name="is_pinned" class="create-input-text" style="width: 100%; padding: 10px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--bg);">
                        <option value="1">📌 Sematkan di Paling Atas</option>
                        <option value="0">Tidak Disematkan</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Isi Lengkap Pengumuman</label>
                <textarea name="content" rows="4" class="create-input-text" placeholder="Tuliskan isi pengumuman secara rinci untuk warga..." required style="width: 100%; padding: 10px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--bg);"></textarea>
            </div>

            <button type="submit" id="btnSubmitAnnouncement" class="btn-create-submit btn-announcement-submit">
                <span>📢 Siarkan Pengumuman Sekarang</span>
            </button>
        </form>
    </div>
</div>

<!-- ── MODAL BUAT TOPIK DISKUSI WARGA ── -->
<div id="modalDiscussion" class="modal-overlay" onclick="if(event.target===this)closeDiscussionModal()">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <h3 style="font-size: 17px; font-weight: 900; margin: 0 0 6px; text-align: center;">💬 Buat Postingan Diskusi Warga</h3>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; margin: 0 0 16px;">
            Sampaikan usul, informasi keamanan, atau saran untuk kemajuan RT
        </p>

        <form id="formDiscussion">
            <?= csrf_field() ?>
            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Kategori</label>
                <select name="category" class="create-input-text" style="width: 100%; padding: 10px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--bg);">
                    <option value="Umum">💬 Diskusi Umum</option>
                    <option value="Usul & Saran">💡 Usul &amp; Saran</option>
                    <option value="Keamanan">👮 Keamanan &amp; Ronda</option>
                    <option value="Kebersihan">🧹 Kebersihan Lingkungan</option>
                    <option value="Info Warga">📢 Info Warga</option>
                    <option value="Kehilangan">❓ Kehilangan / Ditemukan</option>
                </select>
            </div>

            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Judul Topik (Opsional)</label>
                <input type="text" name="title" class="create-input-text" placeholder="Contoh: Usul penambahan lampu penerangan jalan Blok C" style="width: 100%; padding: 10px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--bg);">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Isi Diskusi</label>
                <textarea name="content" rows="4" class="create-input-text" placeholder="Tulis pendapat atau hal yang ingin didiskusikan..." required style="width: 100%; padding: 10px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--bg);"></textarea>
            </div>

            <button type="submit" id="btnSubmitDiscussion" class="btn-create-submit btn-discussion-submit">
                <span>💬 Kirim Postingan ke Forum Warga</span>
            </button>
        </form>
    </div>
</div>

<!-- ── MODAL DETAIL DISKUSI & KOMENTAR ── -->
<div id="modalDiscussionDetail" class="modal-overlay" onclick="if(event.target===this)closeDiscussionDetailModal()">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <div id="discDetailContent">
            <div style="text-align: center; padding: 30px;">Memuat diskusi...</div>
        </div>
    </div>
</div>

<!-- ── MODAL CATAT KAS RT ── -->
<div id="modalKas" class="modal-overlay" onclick="if(event.target===this)closeKasModal()">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <h3 style="font-size: 17px; font-weight: 900; margin: 0 0 6px; text-align: center;">🏛️ Catat Transaksi Kas RT</h3>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; margin: 0 0 16px;">
            Pemasukan atau pengeluaran dana kas bersama RT
        </p>

        <form id="formKas" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px;">
                <label style="display: flex; align-items: center; gap: 8px; padding: 10px; background: #ECFDF5; border: 1.5px solid #A7F3D0; border-radius: 12px; cursor: pointer;">
                    <input type="radio" name="type" value="in" checked onchange="updateKasCategory(this.value)">
                    <strong style="font-size: 12.5px; color: #065F46;">↗️ Pemasukan</strong>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; padding: 10px; background: #FEF2F2; border: 1.5px solid #FECACA; border-radius: 12px; cursor: pointer;">
                    <input type="radio" name="type" value="out" onchange="updateKasCategory(this.value)">
                    <strong style="font-size: 12.5px; color: #991B1B;">↘️ Pengeluaran</strong>
                </label>
            </div>

            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Kategori</label>
                <select id="kasCategorySelect" name="category" class="create-input-text" style="width: 100%; padding: 10px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--bg);">
                    <option value="Iuran Warga">Iuran Warga Bulanan</option>
                    <option value="Sewa Alat">Kas Sewa Alat RT</option>
                    <option value="Donasi / Sukarela">Donasi / Sumbangan Sukarela</option>
                    <option value="Lainnya">Pemasukan Lainnya</option>
                </select>
            </div>

            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Nominal (Rp)</label>
                <input type="number" name="amount" class="create-input-text" placeholder="Contoh: 50000" required style="width: 100%; padding: 10px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--bg);">
            </div>

            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Tanggal</label>
                <input type="date" name="date" value="<?= date('Y-m-d') ?>" class="create-input-text" required style="width: 100%; padding: 10px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--bg);">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Keterangan / Keperluan</label>
                <textarea name="description" rows="2" class="create-input-text" placeholder="Contoh: Pembelian 5 buah sapu lidi untuk kerja bakti" style="width: 100%; padding: 10px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--bg);"></textarea>
            </div>

            <button type="submit" id="btnSubmitKas" class="btn-create-submit btn-kas-submit">
                <span>💾 Simpan Transaksi Kas RT</span>
            </button>
        </form>
    </div>
</div>

<!-- ── MODAL TAMBAH AGENDA RT ── -->
<div id="modalActivity" class="modal-overlay" onclick="if(event.target===this)closeActivityModal()">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <h3 style="font-size: 17px; font-weight: 900; margin: 0 0 6px; text-align: center;">📅 Tambah Agenda Kegiatan RT</h3>
        <p style="font-size: 12px; color: var(--text-secondary); text-align: center; margin: 0 0 16px;">
            Jadwalkan rapat, kerja bakti, atau agenda bersama warga
        </p>

        <form id="formActivity">
            <?= csrf_field() ?>
            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Nama Kegiatan</label>
                <input type="text" name="title" class="create-input-text" placeholder="Contoh: Kerja Bakti Bersih Saluran Air" required style="width: 100%; padding: 10px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--bg);">
            </div>

            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Kategori</label>
                <select name="category" class="create-input-text" style="width: 100%; padding: 10px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--bg);">
                    <option value="Kerja Bakti">🧹 Kerja Bakti</option>
                    <option value="Rapat Warga">🗣️ Rapat Warga</option>
                    <option value="Posyandu">👶 Posyandu / Balita</option>
                    <option value="Ronda Malam">🔦 Ronda Malam / Siskamling</option>
                    <option value="Perayaan / PHBN">🇮🇩 Perayaan 17 Agustus / PHBN</option>
                    <option value="Lainnya">📌 Kegiatan Lainnya</option>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px;">
                <div>
                    <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Tanggal</label>
                    <input type="date" name="event_date" value="<?= date('Y-m-d') ?>" required class="create-input-text" style="width: 100%; padding: 10px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--bg);">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Jam (WIB)</label>
                    <input type="time" name="event_time" value="08:00" class="create-input-text" style="width: 100%; padding: 10px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--bg);">
                </div>
            </div>

            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Lokasi Berkumpul</label>
                <input type="text" name="location" class="create-input-text" placeholder="Contoh: Balai Warga / Lapangan Blok A" required style="width: 100%; padding: 10px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--bg);">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 800; display: block; margin-bottom: 4px;">Keterangan Tambahan</label>
                <textarea name="description" rows="2" class="create-input-text" placeholder="Contoh: Harap membawa cangkul & sapu lidi masing-masing" style="width: 100%; padding: 10px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: var(--bg);"></textarea>
            </div>

            <button type="submit" id="btnSubmitActivity" class="btn-create-submit btn-activity-submit">
                <span>📅 Simpan Agenda RT</span>
            </button>
        </form>
    </div>
</div>

<script>
function copyRtCode(code) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(code).then(() => {
            alert('Kode Unik RT disalin: ' + code);
        });
    } else {
        alert('Kode Unik RT: ' + code);
    }
}

function openAnnouncementModal() {
    document.getElementById('modalAnnouncement').classList.add('open');
}
function closeAnnouncementModal() {
    document.getElementById('modalAnnouncement').classList.remove('open');
}

function openDiscussionModal() {
    document.getElementById('modalDiscussion').classList.add('open');
}
function closeDiscussionModal() {
    document.getElementById('modalDiscussion').classList.remove('open');
}

function openKasModal() {
    document.getElementById('modalKas').classList.add('open');
}
function closeKasModal() {
    document.getElementById('modalKas').classList.remove('open');
}

function openActivityModal() {
    document.getElementById('modalActivity').classList.add('open');
}
function closeActivityModal() {
    document.getElementById('modalActivity').classList.remove('open');
}

function closeDiscussionDetailModal() {
    document.getElementById('modalDiscussionDetail').classList.remove('open');
}

function updateKasCategory(type) {
    const sel = document.getElementById('kasCategorySelect');
    if (type === 'in') {
        sel.innerHTML = `
            <option value="Iuran Warga">Iuran Warga Bulanan</option>
            <option value="Sewa Alat">Kas Sewa Alat RT</option>
            <option value="Donasi / Sukarela">Donasi / Sumbangan Sukarela</option>
            <option value="Lainnya">Pemasukan Lainnya</option>
        `;
    } else {
        sel.innerHTML = `
            <option value="Kerja Bakti">Kebersihan & Kerja Bakti</option>
            <option value="Perbaikan Fasilitas">Perbaikan Lampu / Jalan / Fasilitas</option>
            <option value="Konsumsi / Rapat">Konsumsi Rapat Warga</option>
            <option value="Peralatan RT">Pembelian Inventaris Alat RT</option>
            <option value="Sosial / Santunan">Santunan Warga / Sosial</option>
            <option value="Lainnya">Pengeluaran Lainnya</option>
        `;
    }
}

// Submit Announcement Form
document.getElementById('formAnnouncement')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitAnnouncement');
    btn.disabled = true;
    btn.innerHTML = '<span>Menyiarkan...</span>';

    const formData = new FormData(this);

    fetch('/neighborhood/announcement/store', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Pengumuman berhasil disiarkan!');
            location.reload();
        } else {
            alert(data.message || 'Gagal menerbitkan pengumuman.');
            btn.disabled = false;
            btn.innerHTML = '<span>Siarkan Pengumuman Sekarang</span>';
        }
    })
    .catch(() => {
        alert('Terjadi kesalahan jaringan.');
        btn.disabled = false;
        btn.innerHTML = '<span>Siarkan Pengumuman Sekarang</span>';
    });
});

function deleteAnnouncement(id) {
    if (!confirm('Hapus pengumuman ini?')) return;
    const fd = new FormData();
    fetch('/neighborhood/announcement/delete/' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Gagal menghapus pengumuman.');
    });
}

// Submit Discussion Form
document.getElementById('formDiscussion')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitDiscussion');
    btn.disabled = true;
    btn.innerHTML = '<span>Mengirim...</span>';

    const formData = new FormData(this);

    fetch('/neighborhood/discussion/store', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Postingan berhasil dikirim!');
            location.reload();
        } else {
            alert(data.message || 'Gagal mengirim postingan.');
            btn.disabled = false;
            btn.innerHTML = '<span>Kirim Postingan ke Forum Warga</span>';
        }
    })
    .catch(() => {
        alert('Terjadi kesalahan jaringan.');
        btn.disabled = false;
        btn.innerHTML = '<span>Kirim Postingan ke Forum Warga</span>';
    });
});

function deleteDiscussion(id) {
    if (!confirm('Hapus postingan diskusi ini?')) return;
    const fd = new FormData();
    fetch('/neighborhood/discussion/delete/' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Gagal menghapus diskusi.');
    });
}

// Global Reply State
let currentDiscussionId = null;
let currentReplyParentId = null;

// Open Discussion Detail with Comments & Reply support
function openDiscussionDetail(id) {
    currentDiscussionId = id;
    currentReplyParentId = null;
    const modal = document.getElementById('modalDiscussionDetail');
    const container = document.getElementById('discDetailContent');
    modal.classList.add('open');
    container.innerHTML = '<div style="text-align:center; padding:30px; font-weight:700; color:var(--text-secondary);">Memuat komentar...</div>';

    fetch('/neighborhood/discussion/' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success || !data.discussion) {
            container.innerHTML = '<div style="color:red; text-align:center; padding:20px;">Gagal memuat diskusi.</div>';
            return;
        }

        const d = data.discussion;
        const comments = d.comments || [];

        let commentsHtml = comments.length === 0 
            ? '<div style="text-align:center; padding:20px; color:var(--text-secondary); font-size:12px;">Belum ada komentar. Jadilah yang pertama berkomentar!</div>'
            : comments.map(c => {
                const isReply = c.parent_id && c.reply_to_author_name;
                const authorEscaped = (c.author_name || 'Warga').replace(/'/g, "\\'");
                return `
                <div style="background:var(--bg); border:1px solid var(--border); border-radius:12px; padding:10px 12px; margin-bottom:8px; ${isReply ? 'margin-left:18px; border-left:3px solid #059669;' : ''}">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                            <strong style="font-size:12px; color:var(--text-primary);">${c.author_name} (Rumah ${c.house_number || '-'})</strong>
                            ${isReply ? `<span style="font-size:10.5px; color:#059669; background:#ECFDF5; padding:1px 6px; border-radius:6px; font-weight:700;">↩️ Membalas @${c.reply_to_author_name}</span>` : ''}
                        </div>
                        <span style="font-size:10px; color:var(--text-secondary);">${c.created_at}</span>
                    </div>
                    <p style="font-size:12.5px; color:var(--text-primary); margin:0 0 6px; line-height:1.4;">${c.comment}</p>
                    <div style="display:flex; justify-content:flex-end;">
                        <button type="button" class="btn-comment-reply" onclick="startReply(${c.id}, '${authorEscaped}')">
                            ↩️ Balas
                        </button>
                    </div>
                </div>
            `}).join('');

        container.innerHTML = `
            <div style="margin-bottom:14px; padding-bottom:12px; border-bottom:1px solid var(--border);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span class="discussion-category-pill">${d.category}</span>
                    <span style="font-size:11px; color:var(--text-secondary);">${d.created_at}</span>
                </div>
                ${d.title ? `<h3 style="font-size:15px; font-weight:900; margin:0 0 6px; color:var(--text-primary);">${d.title}</h3>` : ''}
                <p style="font-size:13px; color:var(--text-primary); margin:0 0 8px; line-height:1.5;">${d.content}</p>
                <div style="font-size:11px; color:var(--text-secondary);">Oleh: <strong>${d.author_name}</strong> (Rumah ${d.house_number || '-'})</div>
            </div>

            <h4 style="font-size:13px; font-weight:800; margin:0 0 10px; color:var(--text-primary);">Komentar Warga (${comments.length})</h4>
            <div style="max-height:260px; overflow-y:auto; margin-bottom:14px; padding-right:4px;">
                ${commentsHtml}
            </div>

            <div id="replyIndicatorBox" style="display:none; align-items:center; justify-content:space-between; background:#ECFDF5; border-left:3px solid #059669; padding:6px 12px; border-radius:8px; margin-bottom:8px; font-size:11.5px; color:#065F46;">
                <span>↩️ Membalas <strong id="replyAuthorText">@...</strong></span>
                <button type="button" onclick="cancelReply()" style="background:none; border:none; color:#EF4444; font-weight:800; font-size:11.5px; cursor:pointer;">✕ Batal</button>
            </div>

            <form onsubmit="submitComment(event, ${d.id})" style="display:flex; gap:8px;">
                <input type="text" id="inputCommentText" placeholder="Tulis komentar atau balasan..." required style="flex:1; padding:11px 14px; border-radius:14px; border:1.5px solid var(--border); background:var(--bg); font-size:12.5px; color:var(--text-primary); outline:none;">
                <button type="submit" class="btn-primary-sm" style="padding:10px 18px; border-radius:14px; font-size:13px;">
                    <span>Kirim</span>
                </button>
            </form>
        `;
    })
    .catch(() => {
        container.innerHTML = '<div style="color:red; text-align:center; padding:20px;">Terjadi kesalahan jaringan.</div>';
    });
}

function startReply(commentId, authorName) {
    currentReplyParentId = commentId;
    const box = document.getElementById('replyIndicatorBox');
    const txt = document.getElementById('replyAuthorText');
    const input = document.getElementById('inputCommentText');
    if (box && txt && input) {
        box.style.display = 'flex';
        txt.textContent = '@' + authorName;
        input.placeholder = 'Tulis balasan untuk @' + authorName + '...';
        input.focus();
    }
}

function cancelReply() {
    currentReplyParentId = null;
    const box = document.getElementById('replyIndicatorBox');
    const input = document.getElementById('inputCommentText');
    if (box) box.style.display = 'none';
    if (input) {
        input.placeholder = 'Tulis komentar atau balasan...';
    }
}

function submitComment(e, discussionId) {
    e.preventDefault();
    const input = document.getElementById('inputCommentText');
    const comment = input.value.trim();
    if (!comment) return;

    const fd = new FormData();
    fd.append('comment', comment);
    if (currentReplyParentId) {
        fd.append('parent_id', currentReplyParentId);
    }

    fetch('/neighborhood/discussion/' + discussionId + '/comment', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            currentReplyParentId = null;
            openDiscussionDetail(discussionId); // Refresh discussion thread
        } else {
            alert(data.message || 'Gagal mengirim komentar.');
        }
    })
    .catch(() => {
        alert('Terjadi kesalahan jaringan saat mengirim komentar.');
    });
}

// Submit Kas RT Form
document.getElementById('formKas')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitKas');
    btn.disabled = true;
    btn.innerHTML = '<span>Menyimpan...</span>';

    const formData = new FormData(this);

    fetch('/neighborhood/kas/store', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Kas RT berhasil dicatat!');
            location.reload();
        } else {
            alert(data.message || 'Gagal mencatat kas.');
            btn.disabled = false;
            btn.innerHTML = '<span>Simpan Transaksi Kas RT</span>';
        }
    })
    .catch(() => {
        alert('Terjadi kesalahan jaringan.');
        btn.disabled = false;
        btn.innerHTML = '<span>Simpan Transaksi Kas RT</span>';
    });
});

// Submit Activity Form
document.getElementById('formActivity')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitActivity');
    btn.disabled = true;
    btn.innerHTML = '<span>Menyimpan...</span>';

    const formData = new FormData(this);

    fetch('/neighborhood/activity/store', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Agenda berhasil ditambahkan!');
            location.reload();
        } else {
            alert(data.message || 'Gagal menambahkan agenda.');
            btn.disabled = false;
            btn.innerHTML = '<span>Simpan Agenda RT</span>';
        }
    })
    .catch(() => {
        alert('Terjadi kesalahan jaringan.');
        btn.disabled = false;
        btn.innerHTML = '<span>Simpan Agenda RT</span>';
    });
});

function deleteActivity(id) {
    if (!confirm('Hapus agenda kegiatan ini?')) return;

    const fd = new FormData();
    fetch('/neighborhood/activity/delete/' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Gagal menghapus agenda.');
        }
    });
}

function changeMemberRole(userId, newRole, name) {
    const isTreasurer = newRole === 'rt_treasurer';
    const msg = isTreasurer 
        ? `Jadikan ${name} sebagai Bendahara RT? Bendahara memiliki wewenang mencatat kas RT dan membuat agenda kegiatan.`
        : `Kembalikan jabatan ${name} menjadi warga biasa?`;

    if (!confirm(msg)) return;

    const fd = new FormData();
    fd.append('target_user_id', userId);
    fd.append('role', newRole);

    fetch('/neighborhood/member/role', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Gagal mengubah jabatan.');
        }
    });
}

function verifyResident(userId, action) {
    const isApprove = action === 'approve';
    if (!confirm(isApprove ? 'Setujui warga ini bergabung ke RT Anda?' : 'Tolak permohonan warga ini?')) return;

    const fd = new FormData();
    fd.append('target_user_id', userId);
    fd.append('action', action);

    fetch('/neighborhood/resident/verify', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Berhasil diproses.');
            location.reload();
        } else {
            alert(data.message || 'Gagal memproses.');
        }
    });
}
</script>
<?= $this->endSection() ?>
