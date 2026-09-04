<?= $this->extend('admin/layouts/admin') ?>

<?= $this->section('content') ?>

<div style="margin-bottom: 20px;">
    <?php if ($fcmConfigured): ?>
        <div style="background: #ECFDF5; border: 1px solid #10B981; border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 24px;">🚀</span>
                <div>
                    <strong style="color: #065F46; font-size: 14px;">Firebase Cloud Messaging (FCM) Aktif! (Project: duitku-19896)</strong>
                    <div style="font-size: 12px; color: #047857; margin-top: 2px;">
                        Setiap notifikasi yang Anda kirim akan langsung dikirimkan ke server Google FCM dan membunyikan push notification di seluruh HP pengguna—bahkan saat aplikasi DuitKu sedang ditutup total.
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" id="btnTestFcm" onclick="runFcmTest()" class="admin-btn admin-btn-sm" style="background: #10B981; color: white;">
                    🧪 Tes Koneksi FCM
                </button>
                <button type="button" onclick="document.getElementById('fcmConfigBox').style.display = document.getElementById('fcmConfigBox').style.display === 'none' ? 'block' : 'none'" class="admin-btn admin-btn-outline admin-btn-sm" style="background: white;">
                    ⚙️ Perbarui Kunci
                </button>
            </div>
        </div>
    <?php else: ?>
        <div style="background: #FFFBEB; border: 1px solid #F59E0B; border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 24px;">⚠️</span>
                <div>
                    <strong style="color: #92400E; font-size: 14px;">Kunci Service Account Firebase Belum Dipasang</strong>
                    <div style="font-size: 12px; color: #B45309; margin-top: 2px;">
                        Notifikasi saat ini hanya akan muncul jika pengguna membuka aplikasi. Untuk mengaktifkan push notifikasi saat aplikasi ditutup total, unggah file <code>Service Account JSON</code> dari Firebase Console.
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" id="btnTestFcm" onclick="runFcmTest()" class="admin-btn admin-btn-sm" style="background: #10B981; color: white;">
                    🧪 Tes Koneksi FCM
                </button>
                <button type="button" onclick="document.getElementById('fcmConfigBox').style.display = document.getElementById('fcmConfigBox').style.display === 'none' ? 'block' : 'none'" class="admin-btn admin-btn-sm" style="background: #F59E0B; color: white;">
                    🔑 Pasang Kunci Service Account
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Modal / Collapsible Form Upload Service Account -->
    <div id="fcmConfigBox" style="display: none; margin-top: 12px; background: white; border: 1px solid var(--border-light); border-radius: 12px; padding: 18px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <h4 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 700; color: var(--text-primary);">🔧 Konfigurasi Kunci Firebase Service Account (HTTP v1)</h4>
        <p style="margin: 0 0 12px 0; font-size: 12.5px; color: var(--text-secondary); line-height: 1.5;">
            Dapatkan file ini dari: <strong>Firebase Console &rarr; Setelan Project (Project Settings) &rarr; Tab Akun Layanan (Service accounts) &rarr; Klik "Buat kunci privat baru" (Generate new private key)</strong>.
        </p>
        <form action="/admin/notifications/save-fcm" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div>
                    <label class="admin-form-label">Upload File JSON Service Account</label>
                    <input type="file" name="service_account_file" accept=".json" class="admin-input" style="padding: 8px;">
                </div>
                <div>
                    <label class="admin-form-label">Atau Paste Teks Isi File JSON</label>
                    <textarea name="service_account_json" rows="3" class="admin-textarea" placeholder='{"type": "service_account", "project_id": "duitku-19896", ...}'></textarea>
                </div>
            </div>
            <div style="margin-top: 12px; text-align: right;">
                <button type="button" onclick="document.getElementById('fcmConfigBox').style.display='none'" class="admin-btn admin-btn-outline admin-btn-sm" style="margin-right: 8px;">Tutup</button>
                <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm">💾 Simpan Kunci Firebase</button>
            </div>
        </form>
    </div>
</div>

<div style="display: grid; grid-template-columns: 420px 1fr; gap: 24px; align-items: start;">
    <!-- Form Kirim Notifikasi & Update APK -->
    <div class="admin-card">
        <!-- Tab Switcher -->
        <div style="display: flex; border-bottom: 2px solid var(--border-light); margin: -4px 0 16px 0; gap: 8px;">
            <button type="button" id="tabBtnUpdate" onclick="switchFormTab('update')" class="admin-btn admin-btn-sm" style="flex: 1; border-radius: 8px 8px 0 0; background: #059669; color: white; font-weight: 700; border: none; padding: 10px;">
                🚀 Rilis Update APK
            </button>
            <button type="button" id="tabBtnGeneral" onclick="switchFormTab('general')" class="admin-btn admin-btn-sm" style="flex: 1; border-radius: 8px 8px 0 0; background: #F3F4F6; color: #4B5563; font-weight: 600; border: none; padding: 10px;">
                📢 Notifikasi Biasa
            </button>
        </div>

        <!-- FORM 1: RILIS UPDATE APK (Langsung Download In-App) -->
        <div id="formTabUpdate">
            <div style="background: #ECFDF5; border: 1px solid #10B981; border-radius: 10px; padding: 10px 14px; margin-bottom: 14px; font-size: 12px; color: #065F46; line-height: 1.4;">
                <strong>📲 Fitur Pembaruan Otomatis:</strong> Saat pengguna mengklik push notifikasi ini di HP, aplikasi DuitKu akan <strong>langsung mengunduh file APK di dalam aplikasi</strong> dan membuka installer tanpa browser.
            </div>

            <form action="/admin/notifications/store-update" method="post">
                <?= csrf_field() ?>

                <div class="admin-form-group">
                    <label class="admin-form-label">Link Download File APK (.apk) *</label>
                    <input type="url" name="apk_url" id="apkUrlInput" class="admin-input" placeholder="https://github.com/zusfan-ops/duitku/releases/download/.../app.apk" required value="<?= old('apk_url') ?>" oninput="suggestUpdateFields()">
                    <small style="font-size: 11px; color: var(--text-secondary);">Direct URL ke file installer APK (misal dari GitHub Releases atau server hosting).</small>
                </div>

                <div style="display: grid; grid-template-columns: 130px 1fr; gap: 10px;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Versi Baru</label>
                        <input type="text" name="version" id="versionInput" class="admin-input" placeholder="v1.2.33" value="<?= old('version') ?>" oninput="suggestUpdateTitle()">
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Judul Notifikasi *</label>
                        <input type="text" name="title" id="updateTitleInput" class="admin-input" placeholder="Pembaruan Aplikasi DuitKu" required value="<?= old('title', 'Pembaruan Aplikasi DuitKu Tersedia! 🎉') ?>">
                    </div>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Keterangan / Catatan Rilis (Changelog) *</label>
                    <textarea name="message" rows="5" class="admin-textarea" placeholder="Tuliskan apa saja fitur baru atau perbaikan di versi ini:&#10;• Peningkatan kecepatan & stabilitas&#10;• Penambahan fitur baru&#10;• Perbaikan bug sistem" required><?= old('message') ?></textarea>
                </div>

                <div class="admin-form-group" style="display: flex; align-items: center; gap: 8px; margin-top: 8px;">
                    <input type="checkbox" name="is_pinned" id="is_pinned_update" value="1" checked style="width: 16px; height: 16px;">
                    <label for="is_pinned_update" style="font-size: 13px; font-weight: 600; cursor: pointer; color: var(--text-primary);">
                        📌 Pasang sebagai Banner Utama di Beranda Aplikasi
                    </label>
                </div>

                <button type="submit" class="admin-btn" style="width: 100%; margin-top: 14px; background: #059669; color: white; font-size: 13.5px; padding: 12px; font-weight: 700;">
                    🚀 Broadcast Pembaruan ke Seluruh HP
                </button>
            </form>
        </div>

        <!-- FORM 2: NOTIFIKASI UMUM -->
        <div id="formTabGeneral" style="display: none;">
            <form action="/admin/notifications/store" method="post">
                <?= csrf_field() ?>

                <div class="admin-form-group">
                    <label class="admin-form-label">Judul Notifikasi *</label>
                    <input type="text" name="title" class="admin-input" placeholder="Contoh: Pengumuman Layanan DuitKu" required value="<?= old('title') ?>">
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
                    <input type="checkbox" name="is_pinned" id="is_pinned_gen" value="1" style="width: 16px; height: 16px;">
                    <label for="is_pinned_gen" style="font-size: 13px; font-weight: 600; cursor: pointer;">
                        📌 Pasang sebagai Banner Utama di Beranda Aplikasi
                    </label>
                </div>

                <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%; margin-top: 12px;">
                    📢 Broadcast Notifikasi ke Apps
                </button>
            </form>
        </div>
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
                    <?php $isUpdate = ($n['type'] === 'update' || str_ends_with(strtolower($n['action_url'] ?? ''), '.apk')); ?>
                    <div style="padding: 16px; border-radius: 14px; border: <?= $isUpdate ? '1.5px solid #10B981' : '1px solid var(--border)' ?>; background: <?= $isUpdate ? '#F0FDF4' : 'var(--bg)' ?>; display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                                    <?php if ($isUpdate): ?>
                                        <span class="badge-admin" style="background: #059669; color: white; font-weight: 700;">
                                            🚀 UPDATE APK IN-APP
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-admin <?= $n['type'] === 'promo' ? 'purple' : ($n['type'] === 'warning' ? 'danger' : 'info') ?>">
                                            <?= strtoupper($n['type']) ?>
                                        </span>
                                    <?php endif; ?>

                                    <span class="badge-admin <?= $n['target'] === 'all' ? 'success' : 'warning' ?>">
                                        <?= $n['target'] === 'all' ? '🌐 Semua User' : '👤 User Tertentu (ID: '.$n['user_id'].')' ?>
                                    </span>
                                    <?php if ($n['is_pinned']): ?>
                                        <span class="badge-admin warning">📌 PINNED BERANDA</span>
                                    <?php endif; ?>
                                </div>
                                <h4 style="margin: 4px 0; font-size: 15px; font-weight: 700; color: var(--text-primary);"><?= esc($n['title']) ?></h4>
                                <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--text-secondary); line-height: 1.5; white-space: pre-wrap;"><?= esc($n['message']) ?></p>
                                
                                <?php if (!empty($n['action_url'])): ?>
                                    <div style="margin-top: 8px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <?php if ($isUpdate): ?>
                                            <a href="<?= esc($n['action_url']) ?>" target="_blank" class="admin-btn admin-btn-sm" style="background: #059669; color: white; text-decoration: none; font-size: 11px; padding: 4px 10px; display: inline-flex; align-items: center; gap: 4px;">
                                                📦 File APK: <?= esc(basename(parse_url($n['action_url'], PHP_URL_PATH) ?? 'app.apk')) ?>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= esc($n['action_url']) ?>" target="_blank" style="font-size: 11.5px; color: #0284C7; text-decoration: none; font-weight: 500; word-break: break-all;">
                                            🔗 <?= esc($n['action_url']) ?>
                                        </a>
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
    function switchFormTab(tab) {
        const tabUpdate = document.getElementById('formTabUpdate');
        const tabGen = document.getElementById('formTabGeneral');
        const btnUpdate = document.getElementById('tabBtnUpdate');
        const btnGen = document.getElementById('tabBtnGeneral');

        if (tab === 'update') {
            tabUpdate.style.display = 'block';
            tabGen.style.display = 'none';
            btnUpdate.style.background = '#059669';
            btnUpdate.style.color = '#FFFFFF';
            btnGen.style.background = '#F3F4F6';
            btnGen.style.color = '#4B5563';
        } else {
            tabUpdate.style.display = 'none';
            tabGen.style.display = 'block';
            btnGen.style.background = 'var(--primary, #0284C7)';
            btnGen.style.color = '#FFFFFF';
            btnUpdate.style.background = '#F3F4F6';
            btnUpdate.style.color = '#4B5563';
        }
    }

    function suggestUpdateFields() {
        const url = document.getElementById('apkUrlInput').value.trim();
        const verInput = document.getElementById('versionInput');
        if (url && !verInput.value) {
            // Coba ambil pattern v1.x.x dari url
            const match = url.match(/(v?[0-9]+\.[0-9]+(\.[0-9]+)?)/i);
            if (match && match[1]) {
                verInput.value = match[1].startsWith('v') ? match[1] : 'v' + match[1];
                suggestUpdateTitle();
            }
        }
    }

    function suggestUpdateTitle() {
        const ver = document.getElementById('versionInput').value.trim();
        const titleInput = document.getElementById('updateTitleInput');
        if (ver) {
            titleInput.value = 'Pembaruan DuitKu ' + ver + ' Tersedia! 🎉';
        } else {
            titleInput.value = 'Pembaruan Aplikasi DuitKu Tersedia! 🎉';
        }
    }

    function toggleUserSelect() {
        const target = document.getElementById('notifTarget').value;
        const group = document.getElementById('userSelectGroup');
        group.style.display = target === 'user' ? 'block' : 'none';
    }

    async function runFcmTest() {
        const btn = document.getElementById('btnTestFcm');
        const oldText = btn.innerHTML;
        btn.innerHTML = '⏳ Mengetes...';
        btn.disabled = true;

        try {
            const res = await fetch('/admin/notifications/test-fcm');
            const data = await res.json();
            if (data.success) {
                alert('✅ BERHASIL!\n\n' + data.message + '\n\nPesan tes telah dikirim ke topic "duitku_broadcasts" dan membunyikan notifikasi di HP/BlueStacks.');
            } else {
                alert('❌ GAGAL:\n\n' + data.message + '\n\nDetail Diagnostik:\n' + JSON.stringify(data.steps, null, 2));
            }
        } catch(e) {
            alert('❌ Terjadi kesalahan saat menghubungi server: ' + e.message);
        } finally {
            btn.innerHTML = oldText;
            btn.disabled = false;
        }
    }
</script>

<?= $this->endSection() ?>
