<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.myhome-page {
    padding: 14px 16px 120px;
    max-width: 800px;
    margin: 0 auto;
}

/* Header */
.myhome-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.myhome-title-wrap {
    display: flex;
    flex-direction: column;
}
.myhome-greeting {
    font-size: 11.5px;
    font-weight: 600;
    color: var(--text-muted);
}
.myhome-title {
    font-size: 20px;
    font-weight: 900;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 6px;
}
.myhome-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}
.btn-household-badge {
    padding: 6px 10px;
    border-radius: 12px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    font-size: 12px;
    font-weight: 700;
    color: var(--text-secondary);
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
}
.btn-add-asset {
    background: #6366F1;
    color: #fff;
    width: 36px;
    height: 36px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
    transition: transform 0.15s ease;
}
.btn-add-asset:active { transform: scale(0.94); }

/* Navigation Sub-Tabs */
.myhome-tabs {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    padding-bottom: 8px;
    margin-bottom: 14px;
    scrollbar-width: none;
}
.myhome-tabs::-webkit-scrollbar { display: none; }
.myhome-tab {
    padding: 8px 14px;
    border-radius: 14px;
    border: 1px solid var(--border);
    background: var(--bg-card);
    color: var(--text-secondary);
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.myhome-tab.active {
    background: #6366F1;
    color: #ffffff;
    border-color: #6366F1;
    box-shadow: 0 3px 10px rgba(99, 102, 241, 0.3);
}

/* Hero Home Health Card (Material Design 3 Surface Container) */
.myhome-health-card {
    position: relative;
    border-radius: 24px;
    padding: 24px 20px;
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text-primary);
    margin-bottom: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}
.myhome-health-icon {
    width: 48px;
    height: 48px;
    border-radius: 16px;
    background: rgba(99, 102, 241, 0.1);
    color: #6366F1;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    font-size: 22px;
}
.myhome-health-score {
    font-size: 52px;
    font-weight: 900;
    line-height: 1;
    letter-spacing: -1.5px;
    color: var(--text-primary);
    margin-bottom: 4px;
}
.myhome-health-title {
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--text-secondary);
    margin-bottom: 4px;
}
.myhome-health-sub {
    font-size: 12.5px;
    color: var(--text-muted);
    max-width: 340px;
}
.myhome-health-badge {
    margin-top: 14px;
    padding: 7px 16px;
    background: rgba(99, 102, 241, 0.08);
    border: 1px solid rgba(99, 102, 241, 0.2);
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    color: #4F46E5;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.myhome-health-badge.urgent {
    background: rgba(239, 68, 68, 0.08);
    border-color: rgba(239, 68, 68, 0.2);
    color: #DC2626;
}
.myhome-health-badge.safe {
    background: rgba(16, 185, 129, 0.08);
    border-color: rgba(16, 185, 129, 0.2);
    color: #059669;
}

/* Attention Section */
.myhome-section-title {
    font-size: 14px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.myhome-attention-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 12px 14px;
    margin-bottom: 16px;
    box-shadow: var(--shadow-sm);
}
.myhome-attention-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
}
.myhome-attention-item:last-child { border-bottom: none; }
.myhome-attention-info {
    display: flex;
    align-items: center;
    gap: 10px;
}
.myhome-attention-badge {
    padding: 3px 8px;
    border-radius: 8px;
    font-size: 10px;
    font-weight: 800;
}
.badge-maint { background: #FEF3C7; color: #D97706; }
.badge-warr  { background: #EFF6FF; color: #2563EB; }

/* 4-Tile Grid */
.myhome-summary-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 18px;
}
.myhome-sum-tile {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 14px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    box-shadow: var(--shadow-sm);
    cursor: pointer;
    transition: transform 0.15s ease, border-color 0.15s ease;
}
.myhome-sum-tile:active { transform: scale(0.97); }
.myhome-sum-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin-bottom: 8px;
}
.myhome-sum-count {
    font-size: 20px;
    font-weight: 900;
    color: var(--text-primary);
    line-height: 1;
    margin-bottom: 2px;
}
.myhome-sum-lbl {
    font-size: 11.5px;
    font-weight: 600;
    color: var(--text-muted);
}

/* Search Bar */
.myhome-search-wrap {
    position: relative;
    margin-bottom: 14px;
}
.myhome-search-input {
    width: 100%;
    padding: 11px 14px 11px 38px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    font-size: 13.5px;
    color: var(--text-primary);
    box-shadow: var(--shadow-sm);
    outline: none;
    transition: border-color 0.2s ease;
}
.myhome-search-input:focus {
    border-color: #6366F1;
}
.myhome-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
}

/* Room Accordion & Asset Cards */
.myhome-room-group {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    margin-bottom: 12px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.myhome-room-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    cursor: pointer;
    user-select: none;
}
.myhome-room-hdr:active {
    background: rgba(0,0,0,0.02);
}
.myhome-room-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.myhome-room-icon {
    font-size: 20px;
}
.myhome-room-name {
    font-size: 14.5px;
    font-weight: 800;
    color: var(--text-primary);
}
.myhome-room-count {
    font-size: 11.5px;
    font-weight: 600;
    color: var(--text-muted);
}
.myhome-room-items {
    padding: 0 14px 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.myhome-asset-card {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    cursor: pointer;
    transition: transform 0.15s ease;
}
.myhome-asset-card:active { transform: scale(0.98); }
.myhome-asset-left {
    display: flex;
    align-items: center;
    gap: 10px;
    overflow: hidden;
}
.myhome-asset-thumb {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #EEF2FF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid var(--border);
}
.myhome-asset-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.myhome-asset-name {
    font-size: 13.5px;
    font-weight: 800;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.myhome-asset-meta {
    font-size: 11px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
}
.myhome-asset-badges {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
}
.badge-mini {
    padding: 2px 6px;
    border-radius: 6px;
    font-size: 9.5px;
    font-weight: 800;
}

/* Modals */
.myhome-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.55);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
}
.myhome-modal.active {
    opacity: 1;
    pointer-events: auto;
}
.myhome-modal-body {
    background: var(--bg-card);
    width: 100%;
    max-width: 540px;
    max-height: 90vh;
    border-radius: 24px 24px 0 0;
    padding: 20px 20px 32px;
    overflow-y: auto;
    transform: translateY(30px);
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.myhome-modal.active .myhome-modal-body {
    transform: translateY(0);
}
.myhome-modal-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}
.myhome-modal-title {
    font-size: 17px;
    font-weight: 900;
    color: var(--text-primary);
}
.myhome-modal-close {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    color: var(--text-muted);
}

.form-group {
    margin-bottom: 12px;
}
.form-label {
    display: block;
    font-size: 11.5px;
    font-weight: 700;
    color: var(--text-secondary);
    margin-bottom: 5px;
}
.form-input, .form-select {
    width: 100%;
    padding: 10px 12px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text-primary);
    font-size: 13px;
    outline: none;
}
.form-input:focus, .form-select:focus {
    border-color: #6366F1;
}
.btn-save {
    width: 100%;
    padding: 12px;
    border-radius: 14px;
    background: #6366F1;
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 800;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
    margin-top: 10px;
}
.btn-save:active { transform: scale(0.98); }

/* Detail Sheet Section Tabs */
.detail-tabs {
    display: flex;
    border-bottom: 1px solid var(--border);
    margin-bottom: 14px;
}
.detail-tab {
    flex: 1;
    padding: 8px 0;
    text-align: center;
    font-size: 12px;
    font-weight: 800;
    color: var(--text-muted);
    border-bottom: 2px solid transparent;
    cursor: pointer;
}
.detail-tab.active {
    color: #6366F1;
    border-bottom-color: #6366F1;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="myhome-page">

    <!-- Header -->
    <div class="myhome-header">
        <div class="myhome-title-wrap">
            <span class="myhome-greeting">Selamat pagi,</span>
            <span class="myhome-title">🏡 My Home</span>
        </div>
        <div class="myhome-header-actions">
            <button type="button" class="btn-household-badge" onclick="switchViewTab('household')">
                <span>👥</span> <span><?= esc($userName) ?></span>
            </button>
            <button type="button" class="btn-add-asset" onclick="openAddAssetModal()" title="Tambah Aset Baru">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
        </div>
    </div>

    <!-- Navigation Sub-Tabs -->
    <div class="myhome-tabs">
        <button type="button" class="myhome-tab <?= $activeTab === 'home' ? 'active' : '' ?>" onclick="switchViewTab('home')">
            <span>🏠</span> <span>Home</span>
        </button>
        <button type="button" class="myhome-tab <?= $activeTab === 'rooms' ? 'active' : '' ?>" onclick="switchViewTab('rooms')">
            <span>🏢</span> <span>Ruangan (<?= $summary['rooms_count'] ?? 0 ?>)</span>
        </button>
        <button type="button" class="myhome-tab <?= $activeTab === 'maintenance' ? 'active' : '' ?>" onclick="switchViewTab('maintenance')">
            <span>🛠️</span> <span>Perawatan (<?= $summary['maintenance_count'] ?? 0 ?>)</span>
        </button>
        <button type="button" class="myhome-tab <?= $activeTab === 'warranty' ? 'active' : '' ?>" onclick="switchViewTab('warranty')">
            <span>🛡️</span> <span>Garansi (<?= $summary['warranties_active'] ?? 0 ?>)</span>
        </button>
        <button type="button" class="myhome-tab <?= $activeTab === 'household' ? 'active' : '' ?>" onclick="switchViewTab('household')">
            <span>👥</span> <span>Keluarga</span>
        </button>
    </div>

    <!-- ── TAB 1: HOME AT A GLANCE ── -->
    <div id="viewHome" style="<?= $activeTab === 'home' ? '' : 'display:none' ?>">
        <!-- Hero Health Card (Material Design) -->
        <?php $hasAssets = ($summary['assets_count'] ?? 0) > 0; ?>
        <div class="myhome-health-card">
            <div class="myhome-health-icon">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div class="myhome-health-score"><?= $hasAssets ? ($summary['health_score'] ?? 100) : '-' ?></div>
            <div class="myhome-health-title">Home Health</div>
            <div class="myhome-health-sub"><?= esc($summary['health_status'] ?? 'Mulai catat aset rumah Anda untuk memantau kondisi.') ?></div>
            
            <?php if (!$hasAssets): ?>
            <div class="myhome-health-badge" style="cursor:pointer" onclick="openAddAssetModal()">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Mulai Catat Aset Pertama</span>
            </div>
            <?php elseif (!empty($summary['attention'])): ?>
            <div class="myhome-health-badge urgent">
                <span>⚠️</span> <span><?= count($summary['attention']) ?> jadwal butuh perhatian Anda</span>
            </div>
            <?php else: ?>
            <div class="myhome-health-badge safe">
                <span>✓</span> <span>Semua perawatan & garansi aman</span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Attention Section -->
        <div class="myhome-section-title">
            <span>Perlu Perhatian (Attention)</span>
            <span style="font-size:11px;font-weight:600;color:var(--text-muted)">
                <?= count($summary['attention'] ?? []) ?> jadwal
            </span>
        </div>
        <div class="myhome-attention-card">
            <?php if (empty($summary['attention'])): ?>
            <div style="text-align:center;padding:12px;font-size:12px;color:var(--text-muted)">
                🎉 Hebat! Tidak ada jadwal perawatan yang tertunda atau garansi kritis.
            </div>
            <?php else: ?>
                <?php foreach ($summary['attention'] as $att): ?>
                <div class="myhome-attention-item">
                    <div class="myhome-attention-info">
                        <span style="font-size:18px"><?= $att['type'] === 'maintenance' ? '🛠️' : '🛡️' ?></span>
                        <div>
                            <div style="font-size:13px;font-weight:800;color:var(--text-primary)"><?= esc($att['title']) ?></div>
                            <div style="font-size:11px;color:var(--text-muted)"><?= esc($att['subtitle']) ?></div>
                        </div>
                    </div>
                    <span class="myhome-attention-badge <?= $att['type'] === 'maintenance' ? 'badge-maint' : 'badge-warr' ?>">
                        <?= $att['is_overdue'] ? 'Lewat Tempo' : 'Mendekati' ?>
                    </span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 4-Tile Grid -->
        <div class="myhome-section-title">Ringkasan Rumah (Home Summary)</div>
        <div class="myhome-summary-grid">
            <div class="myhome-sum-tile" onclick="switchViewTab('rooms')">
                <div class="myhome-sum-icon" style="background:#EFF6FF;color:#2563EB">🏢</div>
                <div class="myhome-sum-count"><?= $summary['rooms_count'] ?? 0 ?></div>
                <div class="myhome-sum-lbl">Ruangan</div>
            </div>
            <div class="myhome-sum-tile" onclick="switchViewTab('rooms')">
                <div class="myhome-sum-icon" style="background:#FEF3C7;color:#D97706">📦</div>
                <div class="myhome-sum-count"><?= $summary['assets_count'] ?? 0 ?></div>
                <div class="myhome-sum-lbl">Aset Barang</div>
            </div>
            <div class="myhome-sum-tile" onclick="switchViewTab('maintenance')">
                <div class="myhome-sum-icon" style="background:#ECFDF5;color:#059669">🛠️</div>
                <div class="myhome-sum-count"><?= $summary['maintenance_count'] ?? 0 ?></div>
                <div class="myhome-sum-lbl">Perawatan</div>
            </div>
            <div class="myhome-sum-tile" onclick="switchViewTab('warranty')">
                <div class="myhome-sum-icon" style="background:#F5F3FF;color:#7C3AED">🛡️</div>
                <div class="myhome-sum-count"><?= $summary['warranties_active'] ?? $summary['warranties_count'] ?? 0 ?></div>
                <div class="myhome-sum-lbl">Garansi Aktif</div>
            </div>
        </div>
    </div>

    <!-- ── TAB 2: ROOMS & ASSETS (EVERY ROOM ORGANIZED) ── -->
    <div id="viewRooms" style="<?= $activeTab === 'rooms' ? '' : 'display:none' ?>">
        <!-- Search Assets -->
        <div class="myhome-search-wrap">
            <span class="myhome-search-icon">🔍</span>
            <input type="text" id="searchInput" class="myhome-search-input"
                   placeholder="Cari aset (contoh: Kulkas, TV, Mesin Cuci)..."
                   value="<?= esc($search) ?>" onkeyup="filterAssets(this.value)">
        </div>

        <!-- Rooms List -->
        <div id="roomsContainer">
            <?php 
            $groupedByRoom = [];
            foreach ($items as $it) {
                $r = trim($it['room'] ?? $it['location'] ?? 'Lainnya');
                $groupedByRoom[$r][] = $it;
            }
            ?>

            <?php if (empty($groupedByRoom)): ?>
            <div style="text-align:center;padding:40px 20px;background:var(--bg-card);border:1px solid var(--border);border-radius:20px">
                <div style="font-size:36px;margin-bottom:8px">📦</div>
                <div style="font-size:14.5px;font-weight:800;color:var(--text-primary)">Belum ada aset terdaftar</div>
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:14px">Mulai catat barang rumah tangga Anda agar tersusun rapi per ruangan.</div>
                <button type="button" class="btn-save" style="width:auto;padding:8px 20px;display:inline-block" onclick="openAddAssetModal()">
                    + Tambah Aset Pertama
                </button>
            </div>
            <?php else: ?>
                <?php foreach ($groupedByRoom as $roomName => $roomItems): ?>
                <div class="myhome-room-group" data-room="<?= esc($roomName) ?>">
                    <div class="myhome-room-hdr" onclick="toggleRoomGroup(this)">
                        <div class="myhome-room-left">
                            <span class="myhome-room-icon"><?= \App\Controllers\BarangController::getRoomIcon($roomName) ?></span>
                            <div>
                                <div class="myhome-room-name"><?= esc($roomName) ?></div>
                                <div class="myhome-room-count"><?= count($roomItems) ?> aset terdaftar</div>
                            </div>
                        </div>
                        <span style="font-size:14px;color:var(--text-muted)">▾</span>
                    </div>

                    <div class="myhome-room-items">
                        <?php foreach ($roomItems as $asset): ?>
                        <div class="myhome-asset-card" onclick="openAssetDetail(<?= htmlspecialchars(json_encode($asset), ENT_QUOTES, 'UTF-8') ?>)">
                            <div class="myhome-asset-left">
                                <div class="myhome-asset-thumb">
                                    <?php if (!empty($asset['item_photo']) || !empty($asset['itemPhoto'])): ?>
                                    <img src="<?= esc($asset['item_photo'] ?? $asset['itemPhoto']) ?>" alt="Foto">
                                    <?php else: ?>
                                    <span>📦</span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="myhome-asset-name"><?= esc($asset['name']) ?></div>
                                    <div class="myhome-asset-meta">
                                        <span><?= esc($asset['category'] ?? 'Perlengkapan') ?></span>
                                        <?php if (!empty($asset['brand'])): ?>
                                        <span>• <?= esc($asset['brand']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="myhome-asset-badges">
                                <?php if (!empty($asset['maintenance'])): ?>
                                <span class="badge-mini badge-maint">🛠️ <?= count($asset['maintenance']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($asset['warranties'])): ?>
                                <span class="badge-mini badge-warr">🛡️ <?= count($asset['warranties']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── TAB 3: MAINTENANCE (NEVER MISS MAINTENANCE) ── -->
    <div id="viewMaintenance" style="<?= $activeTab === 'maintenance' ? '' : 'display:none' ?>">
        <div class="myhome-section-title">
            <span>Daftar Jadwal Perawatan</span>
            <button type="button" class="btn-household-badge" onclick="openAddAssetModal()">+ Jadwal Baru</button>
        </div>

        <?php 
        $allMaintenance = [];
        foreach ($items as $it) {
            if (!empty($it['maintenance'])) {
                foreach ($it['maintenance'] as $m) {
                    $m['asset_name'] = $it['name'];
                    $m['asset_room'] = $it['room'] ?? $it['location'] ?? '';
                    $m['asset_id']   = $it['id'];
                    $allMaintenance[] = $m;
                }
            }
        }
        ?>

        <?php if (empty($allMaintenance)): ?>
        <div style="text-align:center;padding:36px 20px;background:var(--bg-card);border:1px solid var(--border);border-radius:20px">
            <div style="font-size:32px;margin-bottom:6px">🛠️</div>
            <div style="font-size:14px;font-weight:800;color:var(--text-primary)">Belum ada tugas perawatan</div>
            <div style="font-size:11.5px;color:var(--text-muted);margin-bottom:12px">Buat jadwal perawatan berkala seperti ganti filter AC, bersihkan kulkas, kuras toren, dll.</div>
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:10px">
            <?php foreach ($allMaintenance as $m): ?>
            <div class="myhome-asset-card" style="padding:12px 14px">
                <div style="display:flex;align-items:center;gap:10px">
                    <span style="font-size:20px">🛠️</span>
                    <div>
                        <div style="font-size:13.5px;font-weight:800;color:var(--text-primary)"><?= esc($m['title'] ?? 'Perawatan') ?></div>
                        <div style="font-size:11px;color:var(--text-muted)">
                            <?= esc($m['asset_name']) ?> (<?= esc($m['asset_room']) ?>) • <?= esc($m['frequency'] ?? 'Setiap 6 Bulan') ?>
                        </div>
                    </div>
                </div>
                <div style="text-align:right">
                    <span class="myhome-attention-badge <?= !empty($m['is_done']) ? 'badge-warr' : 'badge-maint' ?>">
                        <?= !empty($m['is_done']) ? 'Selesai' : 'Jatuh tempo ' . esc($m['due_date'] ?? '-') ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── TAB 4: WARRANTIES (TRACK EVERY WARRANTY) ── -->
    <div id="viewWarranty" style="<?= $activeTab === 'warranty' ? '' : 'display:none' ?>">
        <div class="myhome-section-title">
            <span>Daftar Garansi Aset</span>
            <button type="button" class="btn-household-badge" onclick="openAddAssetModal()">+ Garansi Baru</button>
        </div>

        <?php 
        $allWarranties = [];
        $todayStr = date('Y-m-d');
        foreach ($items as $it) {
            if (!empty($it['warranties'])) {
                foreach ($it['warranties'] as $w) {
                    $w['asset_name'] = $it['name'];
                    $w['asset_room'] = $it['room'] ?? $it['location'] ?? '';
                    $w['asset_id']   = $it['id'];
                    $allWarranties[] = $w;
                }
            }
        }
        ?>

        <?php if (empty($allWarranties)): ?>
        <div style="text-align:center;padding:36px 20px;background:var(--bg-card);border:1px solid var(--border);border-radius:20px">
            <div style="font-size:32px;margin-bottom:6px">🛡️</div>
            <div style="font-size:14px;font-weight:800;color:var(--text-primary)">Belum ada garansi dicatat</div>
            <div style="font-size:11.5px;color:var(--text-muted);margin-bottom:12px">Simpan informasi garansi resmi alat elektronik dan perabotan rumah agar tidak hilang saat klaim.</div>
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:10px">
            <?php foreach ($allWarranties as $w): 
                $isExp = !empty($w['expiry_date']) && $w['expiry_date'] < $todayStr;
            ?>
            <div class="myhome-asset-card" style="padding:12px 14px">
                <div style="display:flex;align-items:center;gap:10px">
                    <span style="font-size:20px">🛡️</span>
                    <div>
                        <div style="font-size:13.5px;font-weight:800;color:var(--text-primary)"><?= esc($w['provider'] ?? 'Garansi Resmi') ?></div>
                        <div style="font-size:11px;color:var(--text-muted)">
                            <?= esc($w['asset_name']) ?> • Berakhir <?= esc($w['expiry_date'] ?? '-') ?>
                        </div>
                    </div>
                </div>
                <div style="text-align:right">
                    <span class="myhome-attention-badge <?= $isExp ? 'badge-maint' : 'badge-warr' ?>">
                        <?= $isExp ? 'Kadaluarsa' : 'Aktif' ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── TAB 5: HOUSEHOLD (SHARE WITH YOUR HOUSEHOLD) ── -->
    <div id="viewHousehold" style="<?= $activeTab === 'household' ? '' : 'display:none' ?>">
        <div class="myhome-section-title">Keluarga & Penghuni Rumah</div>
        <div class="myhome-attention-card" style="padding:18px">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px">
                Masuk sebagai akun:
            </div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
                <div style="width:48px;height:48px;border-radius:14px;background:#6366F1;color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:900">
                    <?= mb_strtoupper(mb_substr($userName, 0, 1)) ?>
                </div>
                <div>
                    <div style="font-size:15px;font-weight:800;color:var(--text-primary)"><?= esc($userName) ?></div>
                    <div style="font-size:11.5px;color:#059669;font-weight:700">Pemilik Rumah (Owner)</div>
                </div>
            </div>

            <div style="border-top:1px solid var(--border);padding-top:14px">
                <div style="font-size:12px;font-weight:800;color:var(--text-primary);margin-bottom:8px">Anggota Rumah Tangga Terhubung:</div>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span>👤</span>
                        <span style="font-size:13px;font-weight:700"><?= esc($userName) ?> (Anda)</span>
                    </div>
                    <span class="myhome-attention-badge badge-warr">Owner</span>
                </div>
                <div style="margin-top:10px;font-size:11px;color:var(--text-muted)">
                    Semua anggota keluarga di aplikasi ini dapat melihat daftar aset, ruangan, jadwal perawatan, serta masa berlaku garansi rumah.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── MODAL: TAMBAH / EDIT ASET ── -->
<div class="myhome-modal" id="assetModal">
    <div class="myhome-modal-body">
        <div class="myhome-modal-hdr">
            <span class="myhome-modal-title" id="assetModalTitle">Tambah Aset Baru</span>
            <button type="button" class="myhome-modal-close" onclick="closeAssetModal()">✕</button>
        </div>

        <form id="assetForm" onsubmit="saveAsset(event)" enctype="multipart/form-data">
            <input type="hidden" name="id" id="formAssetId">
            <input type="hidden" name="maintenance_json" id="formMaintJson">
            <input type="hidden" name="warranties_json" id="formWarrJson">

            <div class="form-group">
                <label class="form-label">Nama Aset / Barang *</label>
                <input type="text" name="name" id="formAssetName" class="form-input" placeholder="Contoh: Kulkas Inverter 2 Pintu" required>
            </div>

            <div class="form-group">
                <label class="form-label">Ruangan (Room) *</label>
                <select name="room" id="formAssetRoom" class="form-select" required>
                    <option value="Dapur">🍽️ Dapur (Kitchen)</option>
                    <option value="Ruang Tamu">🛋️ Ruang Tamu (Living Room)</option>
                    <option value="Kamar Tidur">🛏️ Kamar Tidur (Bedroom)</option>
                    <option value="Garasi">🚗 Garasi (Garage)</option>
                    <option value="Exterior">🏡 Luar / Taman (Exterior)</option>
                    <option value="Ruang Kerja">💼 Ruang Kerja (Office)</option>
                    <option value="Kamar Mandi">🚿 Kamar Mandi (Bathroom)</option>
                    <option value="Gudang">📦 Gudang (Storage)</option>
                    <option value="Lainnya">🏠 Lainnya</option>
                </select>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="category" id="formAssetCat" class="form-input" placeholder="Elektronik, Perabot...">
                </div>
                <div class="form-group">
                    <label class="form-label">Merek (Brand)</label>
                    <input type="text" name="brand" id="formAssetBrand" class="form-input" placeholder="Samsung, LG...">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                <div class="form-group">
                    <label class="form-label">Tanggal Pembelian</label>
                    <input type="date" name="purchase_date" id="formAssetDate" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Beli (Rp)</label>
                    <input type="number" name="purchase_price" id="formAssetPrice" class="form-input" placeholder="0">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Foto Aset</label>
                <input type="file" name="item_photo" id="formAssetPhoto" class="form-input" accept="image/*">
            </div>

            <button type="submit" class="btn-save" id="btnSaveAsset">Simpan Aset</button>
        </form>
    </div>
</div>

<!-- ── MODAL: DETAIL ASET (NEVER MISS MAINTENANCE & WARRANTIES) ── -->
<div class="myhome-modal" id="detailModal">
    <div class="myhome-modal-body">
        <div class="myhome-modal-hdr">
            <span class="myhome-modal-title" id="detailAssetName">Detail Aset</span>
            <button type="button" class="myhome-modal-close" onclick="closeDetailModal()">✕</button>
        </div>

        <div class="detail-tabs">
            <div class="detail-tab active" id="tabSpec" onclick="switchDetailTab('spec')">Spesifikasi</div>
            <div class="detail-tab" id="tabMaint" onclick="switchDetailTab('maint')">Perawatan</div>
            <div class="detail-tab" id="tabWarr" onclick="switchDetailTab('warr')">Garansi</div>
        </div>

        <!-- Tab 1: Spec -->
        <div id="detailSecSpec">
            <div style="width:100%;height:160px;border-radius:14px;background:#0F172A;display:flex;align-items:center;justify-content:center;overflow:hidden;margin-bottom:12px" id="detailPhotoBox">
                <span style="font-size:40px">📦</span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px">
                <div style="background:var(--bg);padding:10px;border-radius:12px;border:1px solid var(--border)">
                    <div style="font-size:10.5px;color:var(--text-muted)">Ruangan</div>
                    <div style="font-size:13px;font-weight:800;color:var(--text-primary)" id="detailRoomText">-</div>
                </div>
                <div style="background:var(--bg);padding:10px;border-radius:12px;border:1px solid var(--border)">
                    <div style="font-size:10.5px;color:var(--text-muted)">Kategori</div>
                    <div style="font-size:13px;font-weight:800;color:var(--text-primary)" id="detailCatText">-</div>
                </div>
                <div style="background:var(--bg);padding:10px;border-radius:12px;border:1px solid var(--border)">
                    <div style="font-size:10.5px;color:var(--text-muted)">Merek</div>
                    <div style="font-size:13px;font-weight:800;color:var(--text-primary)" id="detailBrandText">-</div>
                </div>
                <div style="background:var(--bg);padding:10px;border-radius:12px;border:1px solid var(--border)">
                    <div style="font-size:10.5px;color:var(--text-muted)">Tanggal Pembelian</div>
                    <div style="font-size:13px;font-weight:800;color:var(--text-primary)" id="detailDateText">-</div>
                </div>
            </div>
            <div style="display:flex;gap:8px">
                <button type="button" class="btn-save" style="background:#EF4444;flex:1" onclick="deleteCurrentAsset()">Hapus Aset</button>
                <button type="button" class="btn-save" style="flex:2" onclick="editCurrentAsset()">Edit Aset</button>
            </div>
        </div>

        <!-- Tab 2: Maintenance -->
        <div id="detailSecMaint" style="display:none">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                <span style="font-size:13px;font-weight:800">Tugas Perawatan Berkala</span>
                <button type="button" class="btn-household-badge" onclick="addMaintenancePrompt()">+ Tambah</button>
            </div>
            <div id="detailMaintList" style="display:flex;flex-direction:column;gap:8px"></div>
        </div>

        <!-- Tab 3: Warranty -->
        <div id="detailSecWarr" style="display:none">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                <span style="font-size:13px;font-weight:800">Garansi & Perlindungan</span>
                <button type="button" class="btn-household-badge" onclick="addWarrantyPrompt()">+ Tambah</button>
            </div>
            <div id="detailWarrList" style="display:flex;flex-direction:column;gap:8px"></div>
        </div>
    </div>
</div>

<script>
let currentSelectedAsset = null;

function switchViewTab(tab) {
    document.querySelectorAll('.myhome-tab').forEach(b => b.classList.remove('active'));
    ['home', 'rooms', 'maintenance', 'warranty', 'household'].forEach(t => {
        const el = document.getElementById('view' + t.charAt(0).toUpperCase() + t.slice(1));
        if (el) el.style.display = (t === tab) ? 'block' : 'none';
    });

    const activeBtn = Array.from(document.querySelectorAll('.myhome-tab')).find(b => b.textContent.toLowerCase().includes(tab));
    if (activeBtn) activeBtn.classList.add('active');
}

function filterAssets(val) {
    const q = val.toLowerCase().trim();
    document.querySelectorAll('.myhome-room-group').forEach(group => {
        let hasMatch = false;
        group.querySelectorAll('.myhome-asset-card').forEach(card => {
            const text = card.textContent.toLowerCase();
            if (text.includes(q)) {
                card.style.display = 'flex';
                hasMatch = true;
            } else {
                card.style.display = 'none';
            }
        });
        group.style.display = hasMatch ? 'block' : 'none';
    });
}

function toggleRoomGroup(el) {
    const items = el.nextElementSibling;
    if (items) {
        items.style.display = items.style.display === 'none' ? 'flex' : 'none';
    }
}

function openAddAssetModal() {
    document.getElementById('assetForm').reset();
    document.getElementById('formAssetId').value = '';
    document.getElementById('formMaintJson').value = '[]';
    document.getElementById('formWarrJson').value = '[]';
    document.getElementById('assetModalTitle').textContent = 'Tambah Aset Baru';
    document.getElementById('assetModal').classList.add('active');
}

function closeAssetModal() {
    document.getElementById('assetModal').classList.remove('active');
}

function openAssetDetail(asset) {
    currentSelectedAsset = asset;
    document.getElementById('detailAssetName').textContent = asset.name || 'Detail Aset';
    document.getElementById('detailRoomText').textContent = asset.room || asset.location || '-';
    document.getElementById('detailCatText').textContent = asset.category || '-';
    document.getElementById('detailBrandText').textContent = asset.brand || '-';
    document.getElementById('detailDateText').textContent = asset.purchase_date || '-';

    const photoBox = document.getElementById('detailPhotoBox');
    const photoUrl = asset.item_photo || asset.itemPhoto;
    if (photoUrl) {
        photoBox.innerHTML = `<img src="${photoUrl}" style="width:100%;height:100%;object-fit:cover">`;
    } else {
        photoBox.innerHTML = `<span style="font-size:40px">📦</span>`;
    }

    renderDetailMaintenance(asset.maintenance || []);
    renderDetailWarranties(asset.warranties || []);
    switchDetailTab('spec');
    document.getElementById('detailModal').classList.add('active');
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.remove('active');
}

function switchDetailTab(tab) {
    document.getElementById('tabSpec').classList.toggle('active', tab === 'spec');
    document.getElementById('tabMaint').classList.toggle('active', tab === 'maint');
    document.getElementById('tabWarr').classList.toggle('active', tab === 'warr');

    document.getElementById('detailSecSpec').style.display = tab === 'spec' ? 'block' : 'none';
    document.getElementById('detailSecMaint').style.display = tab === 'maint' ? 'block' : 'none';
    document.getElementById('detailSecWarr').style.display = tab === 'warr' ? 'block' : 'none';
}

function renderDetailMaintenance(list) {
    const box = document.getElementById('detailMaintList');
    if (!list.length) {
        box.innerHTML = `<div style="font-size:12px;color:var(--text-muted);text-align:center;padding:12px">Belum ada jadwal perawatan untuk aset ini.</div>`;
        return;
    }
    box.innerHTML = list.map((m, idx) => `
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:10px 12px;display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-size:13px;font-weight:800;color:var(--text-primary)">${m.title || 'Perawatan'}</div>
                <div style="font-size:11px;color:var(--text-muted)">${m.frequency || 'Setiap 6 Bulan'} • Tempo: ${m.due_date || '-'}</div>
            </div>
            <button type="button" style="background:${m.is_done ? '#059669' : '#F59E0B'};color:#fff;border:none;border-radius:8px;font-size:10.5px;font-weight:700;padding:4px 8px" onclick="toggleMaintDone(${idx})">
                ${m.is_done ? '✓ Selesai' : 'Belum'}
            </button>
        </div>
    `).join('');
}

function renderDetailWarranties(list) {
    const box = document.getElementById('detailWarrList');
    if (!list.length) {
        box.innerHTML = `<div style="font-size:12px;color:var(--text-muted);text-align:center;padding:12px">Belum ada garansi dicatat untuk aset ini.</div>`;
        return;
    }
    box.innerHTML = list.map(w => `
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:10px 12px;display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-size:13px;font-weight:800;color:var(--text-primary)">${w.provider || 'Resmi'}</div>
                <div style="font-size:11px;color:var(--text-muted)">Berlaku sampai: ${w.expiry_date || '-'}</div>
            </div>
            <span style="font-size:11px;font-weight:800;color:#2563EB">${w.status || 'Aktif'}</span>
        </div>
    `).join('');
}

async function addMaintenancePrompt() {
    if (!currentSelectedAsset) return;
    const title = prompt('Nama Perawatan (contoh: Bersihkan Filter / Ganti Oli):');
    if (!title) return;
    const freq = prompt('Frekuensi (contoh: Setiap 6 Bulan / Setiap 1 Tahun):', 'Setiap 6 Bulan') || 'Setiap 6 Bulan';
    const dueDate = prompt('Tanggal Jatuh Tempo (YYYY-MM-DD):', new Date().toISOString().slice(0, 10)) || '';

    currentSelectedAsset.maintenance = currentSelectedAsset.maintenance || [];
    currentSelectedAsset.maintenance.push({
        id: 'm_' + Date.now(),
        title: title,
        frequency: freq,
        due_date: dueDate,
        is_done: false
    });

    await saveAssetDataDirect(currentSelectedAsset);
    renderDetailMaintenance(currentSelectedAsset.maintenance);
}

async function addWarrantyPrompt() {
    if (!currentSelectedAsset) return;
    const provider = prompt('Penyedia Garansi / Toko (contoh: Samsung Official / Mitra10):');
    if (!provider) return;
    const expiry = prompt('Tanggal Berakhir Garansi (YYYY-MM-DD):', new Date(Date.now() + 365*86400000).toISOString().slice(0, 10)) || '';

    currentSelectedAsset.warranties = currentSelectedAsset.warranties || [];
    currentSelectedAsset.warranties.push({
        id: 'w_' + Date.now(),
        provider: provider,
        expiry_date: expiry,
        status: 'Aktif'
    });

    await saveAssetDataDirect(currentSelectedAsset);
    renderDetailWarranties(currentSelectedAsset.warranties);
}

async function toggleMaintDone(idx) {
    if (!currentSelectedAsset || !currentSelectedAsset.maintenance) return;
    currentSelectedAsset.maintenance[idx].is_done = !currentSelectedAsset.maintenance[idx].is_done;
    await saveAssetDataDirect(currentSelectedAsset);
    renderDetailMaintenance(currentSelectedAsset.maintenance);
}

async function saveAssetDataDirect(asset) {
    const formData = new FormData();
    formData.append('id', asset.id);
    formData.append('name', asset.name);
    formData.append('room', asset.room || asset.location || 'Lainnya');
    formData.append('category', asset.category || 'Perlengkapan');
    formData.append('brand', asset.brand || '');
    formData.append('purchase_date', asset.purchase_date || '');
    formData.append('purchase_price', asset.purchase_price || 0);
    formData.append('maintenance_json', JSON.stringify(asset.maintenance || []));
    formData.append('warranties_json', JSON.stringify(asset.warranties || []));

    await fetch('/barang/store', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    });
}

function editCurrentAsset() {
    if (!currentSelectedAsset) return;
    closeDetailModal();

    document.getElementById('formAssetId').value = currentSelectedAsset.id;
    document.getElementById('formAssetName').value = currentSelectedAsset.name || '';
    document.getElementById('formAssetRoom').value = currentSelectedAsset.room || currentSelectedAsset.location || 'Dapur';
    document.getElementById('formAssetCat').value = currentSelectedAsset.category || '';
    document.getElementById('formAssetBrand').value = currentSelectedAsset.brand || '';
    document.getElementById('formAssetDate').value = currentSelectedAsset.purchase_date || '';
    document.getElementById('formAssetPrice').value = currentSelectedAsset.purchase_price || '';
    document.getElementById('formMaintJson').value = JSON.stringify(currentSelectedAsset.maintenance || []);
    document.getElementById('formWarrJson').value = JSON.stringify(currentSelectedAsset.warranties || []);

    document.getElementById('assetModalTitle').textContent = 'Edit Aset';
    document.getElementById('assetModal').classList.add('active');
}

async function deleteCurrentAsset() {
    if (!currentSelectedAsset) return;
    if (!confirm(`Hapus "${currentSelectedAsset.name}" secara permanen?`)) return;

    await fetch('/barang/delete/' + currentSelectedAsset.id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    window.location.reload();
}

async function saveAsset(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveAsset');
    btn.textContent = 'Menyimpan...';
    btn.disabled = true;

    const form = document.getElementById('assetForm');
    const formData = new FormData(form);

    try {
        const res = await fetch('/barang/store', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Gagal menyimpan aset.');
            btn.textContent = 'Simpan Aset';
            btn.disabled = false;
        }
    } catch (err) {
        alert('Terjadi kesalahan jaringan.');
        btn.textContent = 'Simpan Aset';
        btn.disabled = false;
    }
}
</script>
<?= $this->endSection() ?>
