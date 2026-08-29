<?= $this->extend('admin/layouts/admin') ?>

<?= $this->section('content') ?>

<div style="display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start;">
    <!-- Form Kirim Notifikasi -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">📢 Kirim Notifikasi Baru</h3>
        </div>

        <form action="/admin/notifications/store" method="post">
            <?= csrf_field() ?>

            <div class="admin-form-group">
                <label class="admin-form-label">Judul Notifikasi *</label>
                <input type="text" name="title" class="admin-input" placeholder="Contoh: Pembaruan Fitur Baru DuitKu" required value="<?= old('title') ?>">
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Isi Pesan / Pengumuman *</label>
                <textarea name="message" rows="4" class="admin-textarea" placeholder="Tuliskan pesan yang akan diterima di aplikasi DuitKu pengguna..." required><?= old('message') ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="admin-form-group">
                    <label class="admin-form-label">Tipe Pesan</label>
                    <select name="type" class="admin-select">
                        <option value="info">ℹ️ Informasi</option>
                        <option value="announcement">📢 Pengumuman</option>
                        <option value="promo">🎁 Promo & Event</option>
                        <option value="warning">⚠️ Peringatan</option>
                        <option value="system">⚙️ Sistem / Maintenance</option>
                    </select>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Target Penerima</label>
                    <select name="target" class="admin-select" id="notifTarget" onchange="toggleUserSelect()">
                        <option value="all">🌐 Semua Pengguna Apps</option>
                        <option value="user">👤 Pengguna Tertentu</option>
                    </select>
                </div>
            </div>

            <div class="admin-form-group" id="userSelectGroup" style="display: none;">
                <label class="admin-form-label">Pilih Pengguna</label>
                <select name="user_id" class="admin-select">
                    <option value="">-- Pilih Pengguna --</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?> (<?= esc($u['email'] ?: $u['phone']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Action Link / URL (Opsional)</label>
                <input type="url" name="action_url" class="admin-input" placeholder="https://... atau internal action" value="<?= old('action_url') ?>">
                <small style="font-size: 11px; color: var(--text-secondary);">Jika diisi, pengguna di aplikasi dapat mengklik untuk membuka tautan ini.</small>
            </div>

            <div class="admin-form-group" style="display: flex; align-items: center; gap: 8px; margin-top: 14px;">
                <input type="checkbox" name="is_pinned" id="is_pinned" value="1" style="width: 16px; height: 16px;">
                <label for="is_pinned" style="font-size: 13px; font-weight: 600; cursor: pointer;">
                    📌 Pasang sebagai Banner Utama (Pinned di Dashboard App)
                </label>
            </div>

            <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%; margin-top: 12px;">
                🚀 Broadcast Notifikasi ke Apps
            </button>
        </form>
    </div>

    <!-- Riwayat Notifikasi Terkirim -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">📜 Riwayat Notifikasi Terkirim (<?= count($notifications) ?>)</h3>
        </div>

        <?php if (empty($notifications)): ?>
            <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                Belum ada notifikasi yang pernah dikirim.
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <?php foreach ($notifications as $n): ?>
                    <div style="padding: 16px; border-radius: 14px; border: 1px solid var(--border); background: var(--bg); display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px; flex-wrap: wrap;">
                                    <span class="badge-admin <?= $n['type'] === 'promo' ? 'purple' : ($n['type'] === 'warning' ? 'danger' : 'info') ?>">
                                        <?= strtoupper($n['type']) ?>
                                    </span>
                                    <span class="badge-admin <?= $n['target'] === 'all' ? 'success' : 'warning' ?>">
                                        <?= $n['target'] === 'all' ? '🌐 Semua User' : '👤 User Tertentu (ID: '.$n['user_id'].')' ?>
                                    </span>
                                    <?php if ($n['is_pinned']): ?>
                                        <span class="badge-admin warning">📌 PINNED</span>
                                    <?php endif; ?>
                                </div>
                                <h4 style="margin: 4px 0; font-size: 15px; font-weight: 700; color: var(--text-primary);"><?= esc($n['title']) ?></h4>
                                <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--text-secondary); line-height: 1.5; white-space: pre-wrap;"><?= esc($n['message']) ?></p>
                                <?php if (!empty($n['action_url'])): ?>
                                    <div style="margin-top: 6px;">
                                        <a href="<?= esc($n['action_url']) ?>" target="_blank" style="font-size: 12px; color: #0284C7; text-decoration: none; font-weight: 600;">🔗 <?= esc($n['action_url']) ?></a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                <form action="/admin/notifications/toggle-pin/<?= $n['id'] ?>" method="post" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm" title="<?= $n['is_pinned'] ? 'Unpin' : 'Pin ke Dashboard App' ?>">
                                        <?= $n['is_pinned'] ? '📌 Lepas Pin' : '📍 Pin' ?>
                                    </button>
                                </form>
                                <form action="/admin/notifications/delete/<?= $n['id'] ?>" method="post" onsubmit="return confirm('Hapus notifikasi ini?')" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm" title="Hapus">
                                        🗑️ Hapus
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div style="border-top: 1px solid var(--border-light); padding-top: 8px; font-size: 11px; color: var(--text-secondary); display: flex; justify-content: space-between;">
                            <span>Terkirim pada: <?= date('d M Y H:i:s', strtotime($n['created_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function toggleUserSelect() {
        const target = document.getElementById('notifTarget').value;
        const group = document.getElementById('userSelectGroup');
        group.style.display = target === 'user' ? 'block' : 'none';
    }
</script>

<?= $this->endSection() ?>
