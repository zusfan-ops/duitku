<?= $this->extend('admin/layouts/admin') ?>

<?= $this->section('content') ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3 class="admin-card-title">👥 Manajemen Pengguna & Otorisasi Role (<?= count($users) ?>)</h3>
        <form action="/admin/users" method="get" style="display: flex; gap: 8px;">
            <input type="text" name="q" class="admin-input" style="width: 240px; padding: 6px 12px; font-size: 12.5px;" placeholder="Cari nama, email, wa..." value="<?= esc($search ?? '') ?>">
            <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm">Cari</button>
            <?php if (!empty($search)): ?>
                <a href="/admin/users" class="admin-btn admin-btn-outline admin-btn-sm">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Pengguna</th>
                    <th>Kontak / Email / WA</th>
                    <th>Role Saat Ini</th>
                    <th>Ubah Role</th>
                    <th>Terdaftar Pada</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <?php 
                        $isAdmin = in_array(strtolower((string)($u['role'] ?? '')), ['administrator', 'admin']);
                    ?>
                    <tr>
                        <td>#<?= $u['id'] ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #10B981; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">
                                    <?= esc(substr($u['name'] ?? 'U', 0, 1)) ?>
                                </div>
                                <span style="font-weight: 700;"><?= esc($u['name']) ?></span>
                            </div>
                        </td>
                        <td>
                            <div><?= esc($u['email'] ?: '-') ?></div>
                            <?php if (!empty($u['phone'])): ?>
                                <small style="color: var(--text-secondary);">📱 <?= esc($u['phone']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge-admin <?= $isAdmin ? 'success' : 'info' ?>">
                                <?= $isAdmin ? '👑 ADMINISTRATOR' : '👤 USER' ?>
                            </span>
                        </td>
                        <td>
                            <form action="/admin/users/update-role/<?= $u['id'] ?>" method="post" style="display: inline-flex; gap: 6px;">
                                <?= csrf_field() ?>
                                <select name="role" class="admin-select" style="padding: 4px 8px; font-size: 12px; width: 130px;" onchange="this.form.submit()">
                                    <option value="user" <?= !$isAdmin ? 'selected' : '' ?>>User</option>
                                    <option value="administrator" <?= $isAdmin ? 'selected' : '' ?>>Administrator</option>
                                </select>
                            </form>
                        </td>
                        <td style="font-size: 12px; color: var(--text-secondary);">
                            <?= date('d M Y H:i', strtotime($u['created_at'])) ?>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button onclick="promptResetPassword(<?= $u['id'] ?>, '<?= esc(addslashes($u['name'])) ?>')" class="admin-btn admin-btn-outline admin-btn-sm" title="Reset Password">
                                🔑 Reset Pass
                            </button>
                            <form action="/admin/users/delete/<?= $u['id'] ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user <?= esc(addslashes($u['name'])) ?>? Semua data transaksi dan dompet user ini akan terhapus.')" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm" title="Hapus User">
                                    🗑️
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Reset Password -->
<div id="resetModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; align-items: center; justify-content: center;">
    <div class="admin-card" style="width: 360px; margin: auto;">
        <h4 style="margin: 0 0 14px 0;" id="resetUserName">Reset Password</h4>
        <form id="resetForm" action="" method="post">
            <?= csrf_field() ?>
            <div class="admin-form-group">
                <label class="admin-form-label">Password Baru (min 6 karakter)</label>
                <input type="password" name="new_password" class="admin-input" required minlength="6" placeholder="******">
            </div>
            <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px;">
                <button type="button" onclick="document.getElementById('resetModal').style.display='none'" class="admin-btn admin-btn-outline admin-btn-sm">Batal</button>
                <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm">Simpan Password</button>
            </div>
        </form>
    </div>
</div>

<script>
    function promptResetPassword(userId, userName) {
        document.getElementById('resetUserName').innerText = 'Reset Password: ' + userName;
        document.getElementById('resetForm').action = '/admin/users/reset-password/' + userId;
        document.getElementById('resetModal').style.display = 'flex';
    }
</script>

<?= $this->endSection() ?>
