<?= $this->extend('admin/layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$reasonLabels = [
    'spam'          => '📣 Spam',
    'scam'          => '🪤 Penipuan / Scam',
    'inappropriate' => '🚫 Konten Tidak Pantas',
    'harassment'    => '⚠️ Pelecehan / Ancaman',
    'fake'          => '🎭 Iklan / Akun Palsu',
    'illegal'       => '⚖️ Barang/Konten Ilegal',
    'other'         => '➡️ Lainnya',
];
$typeLabels = [
    'user'    => '👤 Pengguna',
    'listing' => '🛍️ Iklan Marketplace',
    'comment' => '💬 Komentar',
    'message' => '✉️ Pesan',
];
$statusBadges = [
    'pending'   => ['danger', '⏳ MENUNGGU'],
    'reviewed'  => ['warning', '🔍 DITINJAU'],
    'resolved'  => ['success', '✅ SELESAI'],
    'dismissed' => ['info', '☁️ DIBATALKAN'],
];
?>

<!-- Top Stats Row -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px;">
    <div class="admin-card" style="padding: 16px; border-left: 4px solid #DC2626;">
        <div style="font-size: 12px; font-weight: 700; color: var(--admin-text-secondary); text-transform: uppercase;">Menunggu Review</div>
        <div style="font-size: 24px; font-weight: 900; color: #DC2626; margin-top: 4px;">
            ⏳ <?= $statMap['pending'] ?? 0 ?>
        </div>
        <div style="font-size: 11px; color: var(--admin-text-secondary); margin-top: 2px;">Butuh tinjauan admin</div>
    </div>
    <div class="admin-card" style="padding: 16px; border-left: 4px solid #D97706;">
        <div style="font-size: 12px; font-weight: 700; color: var(--admin-text-secondary); text-transform: uppercase;">Sedang Ditinjau</div>
        <div style="font-size: 24px; font-weight: 900; color: #D97706; margin-top: 4px;">
            🔍 <?= $statMap['reviewed'] ?? 0 ?>
        </div>
        <div style="font-size: 11px; color: var(--admin-text-secondary); margin-top: 2px;">Dalam proses investigasi</div>
    </div>
    <div class="admin-card" style="padding: 16px; border-left: 4px solid #10B981;">
        <div style="font-size: 12px; font-weight: 700; color: var(--admin-text-secondary); text-transform: uppercase;">Terselesaikan</div>
        <div style="font-size: 24px; font-weight: 900; color: #10B981; margin-top: 4px;">
            ✅ <?= $statMap['resolved'] ?? 0 ?>
        </div>
        <div style="font-size: 11px; color: var(--admin-text-secondary); margin-top: 2px;">Laporan ditindaklanjuti</div>
    </div>
    <div class="admin-card" style="padding: 16px; border-left: 4px solid #64748B;">
        <div style="font-size: 12px; font-weight: 700; color: var(--admin-text-secondary); text-transform: uppercase;">Total Laporan</div>
        <div style="font-size: 24px; font-weight: 900; color: #334155; margin-top: 4px;">
            📊 <?= array_sum($statMap) ?>
        </div>
        <div style="font-size: 11px; color: var(--admin-text-secondary); margin-top: 2px;">Semua kategori</div>
    </div>
</div>

<!-- Filter & Search -->
<div class="admin-card" style="padding: 16px; margin-bottom: 20px;">
    <form method="GET" action="/admin/reports" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 240px;">
            <input type="text" name="q" value="<?= esc($search) ?>" placeholder="Cari pelapor, terlaporkan, atau deskripsi..." class="admin-input">
        </div>
        <div style="width: 200px;">
            <select name="status" class="admin-select" onchange="this.form.submit()">
                <option value="pending" <?= ($status ?? 'pending') === 'pending' ? 'selected' : '' ?>>⏳ Menunggu Review (<?= $statMap['pending'] ?? 0 ?>)</option>
                <option value="all" <?= ($status ?? '') === 'all' ? 'selected' : '' ?>>Semua Status</option>
                <option value="reviewed" <?= ($status ?? '') === 'reviewed' ? 'selected' : '' ?>>🔍 Ditinjau</option>
                <option value="resolved" <?= ($status ?? '') === 'resolved' ? 'selected' : '' ?>>✅ Selesai</option>
                <option value="dismissed" <?= ($status ?? '') === 'dismissed' ? 'selected' : '' ?>>☁️ Dibatalkan</option>
            </select>
        </div>
        <button type="submit" class="admin-btn admin-btn-primary"><span>🔍 Filter</span></button>
        <?php if (!empty($search) || ($status ?? 'pending') !== 'pending'): ?>
            <a href="/admin/reports?status=pending" class="admin-btn admin-btn-outline" style="text-decoration:none;">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Reports Table -->
<div class="admin-card" style="padding: 0; overflow: hidden;">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--admin-border); display: flex; justify-content: space-between; align-items: center;">
        <h2 style="font-size: 16px; font-weight: 800; margin: 0; color: var(--admin-text);">🛡️ Laporan Pengguna &amp; Konten</h2>
        <span style="font-size: 12px; color: var(--admin-text-secondary); font-weight: 700;">Menampilkan <?= count($reports) ?> laporan</span>
    </div>

    <div style="overflow-x: auto;">
        <table class="admin-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--admin-bg); text-align: left; font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--admin-text-secondary);">
                    <th style="padding: 12px 14px;"># ID</th>
                    <th style="padding: 12px 14px;">Pelapor</th>
                    <th style="padding: 12px 14px;">Terlaporkan</th>
                    <th style="padding: 12px 14px;">Tipe &amp; Alasan</th>
                    <th style="padding: 12px 14px;">Deskripsi</th>
                    <th style="padding: 12px 14px;">Status</th>
                    <th style="padding: 12px 14px; width: 180px; text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reports)): ?>
                    <tr>
                        <td colspan="7" style="padding: 40px; text-align: center; color: var(--admin-text-secondary);">
                            <div style="font-size: 32px; margin-bottom: 8px;">🎉</div>
                            Tidak ada laporan ditemukan. Komunitas terlihat aman!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reports as $r):
                        $badge = $statusBadges[$r['status']] ?? ['info', $r['status']];
                        $label = $reasonLabels[$r['reason']] ?? $r['reason'];
                        $type  = $typeLabels[$r['target_type']] ?? $r['target_type'];
                    ?>
                        <tr>
                            <td style="padding: 12px 14px; font-weight: 800; color: var(--admin-text-secondary);">#<?= $r['id'] ?></td>
                            <td style="padding: 12px 14px;">
                                <div style="font-weight: 700; font-size: 13px; color: var(--admin-text);"><?= esc($r['reporter_name'] ?: 'Pengguna #' . $r['reporter_id']) ?></div>
                                <div style="font-size: 11px; color: var(--admin-text-secondary);"><?= esc($r['reporter_email'] ?: '-') ?></div>
                            </td>
                            <td style="padding: 12px 14px;">
                                <?php if (!empty($r['reported_user_id'])): ?>
                                    <div style="font-weight: 700; font-size: 13px; color: var(--admin-text);"><?= esc($r['reported_name'] ?: 'User #' . $r['reported_user_id']) ?></div>
                                    <div style="font-size: 11px; color: var(--admin-text-secondary);"><?= esc($r['reported_email'] ?: '-') ?></div>
                                <?php else: ?>
                                    <span style="font-size: 12px; color: var(--admin-text-secondary);">(konten)</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 14px;">
                                <div style="font-size: 11.5px; font-weight: 800; color: var(--admin-text);"><?= $type ?> → <span style="color:var(--admin-primary-dark);">#<?= $r['target_id'] ?></span></div>
                                <div style="font-size: 11.5px; margin-top: 3px;"><?= $label ?></div>
                            </td>
                            <td style="padding: 12px 14px; max-width: 260px;">
                                <div style="font-size: 12px; color: var(--admin-text-secondary); overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; line-height: 1.4;">
                                    <?= esc($r['description'] ?: '—') ?>
                                </div>
                            </td>
                            <td style="padding: 12px 14px;">
                                <span class="badge-admin <?= $badge[0] ?>"><?= $badge[1] ?></span>
                                <?php if (!empty($r['admin_note'])): ?>
                                    <div style="font-size: 10px; color: var(--admin-text-secondary); margin-top: 4px; font-style: italic;">📝 <?= esc($r['admin_note']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 14px;">
                                <div style="display:flex; flex-direction:column; gap:4px; align-items:flex-end;">
                                    <form action="/admin/reports/update/<?= $r['id'] ?>" method="post" style="display:inline-flex; gap:5px; margin:0;">
                                        <?= csrf_field() ?>
                                        <select name="status" class="admin-select" style="width:110px; padding:4px 6px; font-size:11px;" onchange="this.form.submit()">
                                            <?php foreach (['pending' => '⏳ Menunggu', 'reviewed' => '🔍 Ditinjau', 'resolved' => '✅ Selesai', 'dismissed' => '☁️ Batal'] as $v => $lbl): ?>
                                                <option value="<?= $v ?>" <?= $r['status'] === $v ? 'selected' : '' ?>><?= $lbl ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                    <?php if (!empty($r['reported_user_id'])): ?>
                                        <button type="button" onclick="promptAdminNote(<?= $r['id'] ?>)" class="admin-btn admin-btn-outline admin-btn-sm">📝 Catatan</button>
                                        <form action="/admin/reports/ban-user/<?= $r['id'] ?>" method="post" style="margin:0;" onsubmit="return confirm('Yakin ingin BANNED PERMANEN akun #<?= (int)$r['reported_user_id'] ?>? Semua data user ini akan terhapus!')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm" title="Ban permanen">🚫 Ban Akun</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function promptAdminNote(reportId) {
    const note = prompt('Catatan admin untuk laporan #' + reportId + ':');
    if (note === null) return; // cancelled
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/reports/update/' + reportId;
    const token = document.createElement('input');
    token.type = 'hidden';
    token.name = '<?= csrf_token() ?>';
    token.value = '<?= csrf_hash() ?>';
    const status = document.createElement('input');
    status.type = 'hidden';
    status.name = 'status';
    status.value = 'resolved';
    const adminNote = document.createElement('input');
    adminNote.type = 'hidden';
    adminNote.name = 'admin_note';
    adminNote.value = note;
    form.appendChild(token);
    form.appendChild(status);
    form.appendChild(adminNote);
    document.body.appendChild(form);
    form.submit();
}
</script>

<?= $this->endSection() ?>