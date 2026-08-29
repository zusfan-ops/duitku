<?= $this->extend('admin/layouts/admin') ?>

<?= $this->section('content') ?>

<!-- Stat Grid -->
<div class="stat-grid">
    <div class="stat-card-admin">
        <div class="stat-icon-wrap" style="background: #E0F2FE; color: #0284C7;">
            👥
        </div>
        <div class="stat-content">
            <h4>Total Pengguna</h4>
            <div class="val"><?= number_format($totalUsers) ?></div>
            <small style="color: var(--text-secondary); font-size: 11px;">Admin: <?= number_format($totalAdmins) ?></small>
        </div>
    </div>

    <div class="stat-card-admin">
        <div class="stat-icon-wrap" style="background: #DCFCE7; color: #16A34A;">
            💵
        </div>
        <div class="stat-content">
            <h4>Total Transaksi</h4>
            <div class="val"><?= number_format($totalTx) ?></div>
            <small style="color: #16A34A; font-size: 11px;">Pemasukan: Rp <?= number_format($totalIncome, 0, ',', '.') ?></small>
        </div>
    </div>

    <div class="stat-card-admin">
        <div class="stat-icon-wrap" style="background: #FEF3C7; color: #D97706;">
            🏪
        </div>
        <div class="stat-content">
            <h4>POS & Bisnis UMKM</h4>
            <div class="val"><?= number_format($totalPosOrders) ?> Order</div>
            <small style="color: #D97706; font-size: 11px;">Omset: Rp <?= number_format($totalPosRevenue, 0, ',', '.') ?></small>
        </div>
    </div>

    <div class="stat-card-admin">
        <div class="stat-icon-wrap" style="background: #F3E8FF; color: #9333EA;">
            📺
        </div>
        <div class="stat-content">
            <h4>TV Streaming M3U</h4>
            <div class="val"><?= number_format($totalTvChannels) ?> Saluran</div>
            <small style="color: #9333EA; font-size: 11px;">Aktif: <?= number_format($activeTvChannels) ?> Channel</small>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px;">
    <!-- Recent Notifications -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">📢 Broadcast Notifikasi Terakhir</h3>
            <a href="/admin/notifications" class="admin-btn admin-btn-outline admin-btn-sm">Kelola Semua →</a>
        </div>
        <?php if (empty($recentNotifs)): ?>
            <div style="text-align: center; padding: 30px; color: var(--text-secondary);">
                Belum ada notifikasi broadcast yang dikirim.
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($recentNotifs as $n): ?>
                    <div style="padding: 12px 14px; border-radius: 12px; border: 1px solid var(--border-light); background: var(--bg); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                                <span class="badge-admin <?= $n['type'] === 'promo' ? 'purple' : ($n['type'] === 'warning' ? 'danger' : 'info') ?>">
                                    <?= strtoupper($n['type']) ?>
                                </span>
                                <?php if ($n['is_pinned']): ?>
                                    <span class="badge-admin warning">📌 PINNED</span>
                                <?php endif; ?>
                                <strong style="font-size: 13.5px;"><?= esc($n['title']) ?></strong>
                            </div>
                            <p style="margin: 0; font-size: 12px; color: var(--text-secondary);"><?= esc(mb_strimwidth($n['message'], 0, 80, '...')) ?></p>
                        </div>
                        <small style="color: var(--text-secondary); font-size: 11px; white-space: nowrap;"><?= date('d M Y H:i', strtotime($n['created_at'])) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Users -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">👥 Pengguna Terdaftar Terbaru</h3>
            <a href="/admin/users" class="admin-btn admin-btn-outline admin-btn-sm">Kelola Semua →</a>
        </div>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Role</th>
                        <th>Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $u): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 700;"><?= esc($u['name']) ?></div>
                                <div style="font-size: 11px; color: var(--text-secondary);"><?= esc($u['email'] ?: $u['phone'] ?: '-') ?></div>
                            </td>
                            <td>
                                <span class="badge-admin <?= ($u['role'] ?? '') === 'administrator' || ($u['role'] ?? '') === 'admin' ? 'success' : 'info' ?>">
                                    <?= strtoupper($u['role'] ?? 'USER') ?>
                                </span>
                            </td>
                            <td style="font-size: 12px; color: var(--text-secondary);">
                                <?= date('d M Y', strtotime($u['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
