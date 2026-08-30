<?= $this->extend('admin/layouts/admin') ?>

<?= $this->section('content') ?>

<div class="admin-card">
    <div class="admin-card-header" style="flex-wrap: wrap; gap: 12px;">
        <h3 class="admin-card-title">👥 Manajemen Pengguna & Otorisasi Role (<?= count($users) ?>)</h3>
        <form action="/admin/users" method="get" style="display: flex; gap: 8px;">
            <input type="text" name="q" class="admin-input" style="width: 220px; padding: 6px 12px; font-size: 12.5px;" placeholder="🔍 Cari nama, email, wa..." value="<?= esc($search ?? '') ?>">
            <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm">Cari</button>
            <?php if (!empty($search)): ?>
                <a href="/admin/users" class="admin-btn admin-btn-outline admin-btn-sm">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Nama Pengguna</th>
                    <th>Kontak / Email / WA</th>
                    <th>Role Saat Ini</th>
                    <th>Ubah Role</th>
                    <th>Terdaftar Pada</th>
                    <th style="text-align: right; width: 140px;">Aksi</th>
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
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #10B981, #059669); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">
                                    <?= esc(substr($u['name'] ?? 'U', 0, 1)) ?>
                                </div>
                                <strong style="font-size: 13px; color: var(--admin-text);"><?= esc($u['name']) ?></strong>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 12.5px;"><?= esc($u['email'] ?: '-') ?></div>
                            <?php if (!empty($u['phone'])): ?>
                                <small style="color: var(--admin-text-secondary);">📱 <?= esc($u['phone']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge-admin <?= $isAdmin ? 'success' : 'info' ?>">
                                <?= $isAdmin ? '👑 ADMINISTRATOR' : '👤 USER' ?>
                            </span>
                        </td>
                        <td>
                            <form action="/admin/users/update-role/<?= $u['id'] ?>" method="post" style="display: inline-flex; gap: 6px; margin: 0;">
                                <?= csrf_field() ?>
                                <select name="role" class="admin-select" style="padding: 4px 8px; font-size: 12px; width: 130px;" onchange="this.form.submit()">
                                    <option value="user" <?= !$isAdmin ? 'selected' : '' ?>>User</option>
                                    <option value="administrator" <?= $isAdmin ? 'selected' : '' ?>>Administrator</option>
                                </select>
                            </form>
                        </td>
                        <td style="font-size: 12px; color: var(--admin-text-secondary);">
                            <?= date('d M Y H:i', strtotime($u['created_at'])) ?>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div style="display: inline-flex; align-items: center; gap: 4px; justify-content: flex-end;">
                                <button type="button" onclick="promptResetPassword(<?= $u['id'] ?>, '<?= esc(addslashes($u['name'])) ?>')" class="admin-btn admin-btn-outline admin-btn-sm" style="padding: 4px 8px;" title="Reset Password">
                                    🔑 Reset
                                </button>
                                <form action="/admin/users/delete/<?= $u['id'] ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user <?= esc(addslashes($u['name'])) ?>? Semua data transaksi dan dompet user ini akan terhapus.')" style="display: inline; margin: 0;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm" style="padding: 4px 8px;" title="Hapus User">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Reset Password -->
<div id="resetModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 16px;">
    <div class="admin-card" style="width: 100%; max-width: 380px; margin: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
        <div class="admin-card-header">
            <h4 style="margin: 0;" id="resetUserName">Reset Password</h4>
            <button type="button" onclick="document.getElementById('resetModal').style.display='none'" class="admin-btn admin-btn-outline admin-btn-sm" style="padding: 2px 6px;">✕</button>
        </div>
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

