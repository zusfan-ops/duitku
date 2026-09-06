<?= $this->extend('admin/layouts/admin') ?>

<?= $this->section('content') ?>
<div class="admin-content-inner">
    
    <!-- Top Stats Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 20px;">
        <div class="admin-card" style="padding: 16px; border-left: 4px solid #D97706;">
            <div style="font-size: 12px; font-weight: 700; color: var(--admin-text-secondary); text-transform: uppercase;">Menunggu Verifikasi SK</div>
            <div style="font-size: 24px; font-weight: 900; color: #D97706; margin-top: 4px;">
                ⏳ <?= $pendingCount ?> Pengajuan
            </div>
            <div style="font-size: 11px; color: var(--admin-text-secondary); margin-top: 2px;">Butuh persetujuan Superadmin</div>
        </div>

        <div class="admin-card" style="padding: 16px; border-left: 4px solid #10B981;">
            <div style="font-size: 12px; font-weight: 700; color: var(--admin-text-secondary); text-transform: uppercase;">RT Aktif &amp; Terverifikasi</div>
            <div style="font-size: 24px; font-weight: 900; color: #10B981; margin-top: 4px;">
                ✅ <?= $verifiedCount ?> Wilayah
            </div>
            <div style="font-size: 11px; color: var(--admin-text-secondary); margin-top: 2px;">Kode RT aktif digunakan warga</div>
        </div>

        <div class="admin-card" style="padding: 16px; border-left: 4px solid #3B82F6;">
            <div style="font-size: 12px; font-weight: 700; color: var(--admin-text-secondary); text-transform: uppercase;">Total RT Terdaftar</div>
            <div style="font-size: 24px; font-weight: 900; color: #3B82F6; margin-top: 4px;">
                🏘️ <?= count($neighborhoods) ?> Total
            </div>
            <div style="font-size: 11px; color: var(--admin-text-secondary); margin-top: 2px;">Keseluruhan data RT</div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="admin-card" style="padding: 16px; margin-bottom: 20px;">
        <form method="GET" action="/admin/neighborhoods" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 240px;">
                <input type="text" name="q" value="<?= esc($search) ?>" placeholder="Cari nama RT, kode unik, wilayah, atau nama pemohon..." class="admin-input">
            </div>
            <div style="width: 180px;">
                <select name="status" class="admin-select" onchange="this.form.submit()">
                    <option value="all" <?= $currentStatus === 'all' ? 'selected' : '' ?>>Semua Status</option>
                    <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>⏳ Menunggu Approval (<?= $pendingCount ?>)</option>
                    <option value="verified" <?= $currentStatus === 'verified' ? 'selected' : '' ?>>✅ Terverifikasi (<?= $verifiedCount ?>)</option>
                    <option value="rejected" <?= $currentStatus === 'rejected' ? 'selected' : '' ?>>❌ Ditolak</option>
                </select>
            </div>
            <button type="submit" class="admin-btn admin-btn-primary">
                <span>🔍 Filter</span>
            </button>
            <?php if (!empty($search) || $currentStatus !== 'all'): ?>
                <a href="/admin/neighborhoods" class="admin-btn admin-btn-secondary" style="text-decoration: none;">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table of Neighborhoods -->
    <div class="admin-card" style="padding: 0; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--admin-border); display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 16px; font-weight: 800; margin: 0; color: var(--admin-text);">Daftar Pengajuan &amp; Lingkungan RT</h2>
            <span style="font-size: 12px; color: var(--admin-text-secondary); font-weight: 700;">Menampilkan <?= count($neighborhoods) ?> data</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--admin-bg); text-align: left; font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--admin-text-secondary);">
                        <th style="padding: 12px 16px;">Wilayah &amp; Nama RT</th>
                        <th style="padding: 12px 16px;">Kode Unik</th>
                        <th style="padding: 12px 16px;">Pemohon (Calon Ketua RT)</th>
                        <th style="padding: 12px 16px;">Dokumen SK / Surat Tugas</th>
                        <th style="padding: 12px 16px;">Status</th>
                        <th style="padding: 12px 16px; text-align: right;">Aksi Approval</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($neighborhoods)): ?>
                        <tr>
                            <td colspan="6" style="padding: 32px; text-align: center; color: var(--admin-text-secondary);">
                                Tidak ada data RT ditemukan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($neighborhoods as $rt): ?>
                            <tr>
                                <td style="padding: 14px 16px;">
                                    <div style="font-weight: 800; font-size: 14px; color: var(--admin-text);"><?= esc($rt['name']) ?></div>
                                    <div style="font-size: 11.5px; color: var(--admin-text-secondary); margin-top: 2px;">
                                        RT <?= esc($rt['rt']) ?> / RW <?= esc($rt['rw']) ?> • Kel. <?= esc($rt['subdistrict']) ?>, Kec. <?= esc($rt['district']) ?>
                                    </div>
                                    <div style="font-size: 11px; color: var(--admin-text-secondary); opacity: 0.8;">
                                        <?= esc($rt['city']) ?>, <?= esc($rt['province']) ?>
                                    </div>
                                </td>
                                <td style="padding: 14px 16px;">
                                    <span style="font-family: monospace; font-weight: 800; font-size: 12px; background: var(--admin-bg); padding: 4px 8px; border-radius: 6px; border: 1px solid var(--admin-border);">
                                        🔑 <?= esc($rt['unique_code']) ?>
                                    </span>
                                </td>
                                <td style="padding: 14px 16px;">
                                    <div style="font-weight: 700; font-size: 13px; color: var(--admin-text);"><?= esc($rt['applicant_name'] ?: 'Pengguna') ?></div>
                                    <div style="font-size: 11.5px; color: var(--admin-text-secondary);"><?= esc($rt['applicant_email']) ?></div>
                                    <?php if (!empty($rt['applicant_phone'])): ?>
                                        <div style="font-size: 11px; color: var(--admin-text-secondary);">📞 <?= esc($rt['applicant_phone']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px;">
                                    <?php if (!empty($rt['sk_number'])): ?>
                                        <div style="font-size: 11px; font-weight: 700; color: var(--admin-text); margin-bottom: 4px;">
                                            No. SK: <?= esc($rt['sk_number']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($rt['sk_document_path'])): ?>
                                        <a href="<?= esc($rt['sk_document_path']) ?>" target="_blank" class="badge-admin info" style="text-decoration: none; padding: 5px 10px;">
                                            📄 Lihat Berkas SK ↗
                                        </a>
                                    <?php else: ?>
                                        <span class="badge-admin warning" style="opacity: 0.8;">Tidak ada file</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px;">
                                    <?php if ($rt['status'] === 'verified'): ?>
                                        <span class="badge-admin success">✅ Terverifikasi</span>
                                    <?php elseif ($rt['status'] === 'rejected'): ?>
                                        <span class="badge-admin danger" title="<?= esc($rt['rejection_reason']) ?>">❌ Ditolak</span>
                                    <?php else: ?>
                                        <span class="badge-admin warning">⏳ Pending Approval</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <?php if ($rt['status'] === 'pending'): ?>
                                        <div style="display: inline-flex; gap: 6px;">
                                            <button type="button" class="admin-btn admin-btn-danger" style="padding: 6px 12px; font-size: 11.5px;" onclick="openRejectModal(<?= (int)$rt['id'] ?>, '<?= esc($rt['name']) ?>')">
                                                Tolak
                                            </button>
                                            <form method="POST" action="/admin/neighborhoods/approve/<?= (int)$rt['id'] ?>" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui pengesahan RT ini? Pemohon akan resmi menjadi Ketua RT.')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="admin-btn admin-btn-primary" style="padding: 6px 14px; font-size: 11.5px;">
                                                    Setujui ✓
                                                </button>
                                            </form>
                                        </div>
                                    <?php elseif ($rt['status'] === 'verified'): ?>
                                        <span style="font-size: 11.5px; color: var(--admin-text-secondary); font-weight: 700;">Aktif</span>
                                    <?php else: ?>
                                        <span style="font-size: 11px; color: #DC2626;"><?= esc($rt['rejection_reason'] ?: 'Ditolak') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Tolak Pengajuan RT -->
<div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center;">
    <div class="admin-card" style="max-width: 440px; width: 100%; padding: 24px; margin: 16px;">
        <h3 style="font-size: 16px; font-weight: 900; margin-bottom: 6px; color: var(--admin-text);">Tolak Pengajuan RT</h3>
        <p style="font-size: 12.5px; color: var(--admin-text-secondary); margin-bottom: 14px;" id="rejectRtNameText"></p>
        
        <form id="rejectForm" method="POST" action="">
            <?= csrf_field() ?>
            <div class="admin-form-group">
                <label class="admin-form-label">Alasan Penolakan</label>
                <textarea name="rejection_reason" class="admin-textarea" rows="3" placeholder="Misal: Nomor SK tidak terdaftar di kelurahan atau dokumen buram" required></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px;">
                <button type="button" class="admin-btn admin-btn-secondary" onclick="closeRejectModal()">Batal</button>
                <button type="submit" class="admin-btn admin-btn-danger">Konfirmasi Tolak</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(rtId, rtName) {
    document.getElementById('rejectForm').action = '/admin/neighborhoods/reject/' + rtId;
    document.getElementById('rejectRtNameText').textContent = 'Anda akan menolak pengajuan untuk ' + rtName + '. Berikan alasan agar pemohon dapat memperbaiki dokumen.';
    document.getElementById('rejectModal').style.display = 'flex';
}
function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
</script>
<?= $this->endSection() ?>
