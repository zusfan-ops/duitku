<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.doc-page { max-width: 680px; margin: 0 auto; padding-bottom: 120px; }
.doc-hero {
    background: linear-gradient(135deg, #475569 0%, #64748B 55%, #94A3B8 100%);
    border-radius: var(--radius-xl, 24px);
    padding: 24px 20px;
    color: #fff;
    margin-bottom: 18px;
    box-shadow: 0 12px 32px rgba(71, 85, 105, 0.28);
}
.doc-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 18px; }
.doc-stat { background: var(--bg-card,#fff); border:1.5px solid var(--border,#E2E8F0); border-radius:16px; padding:12px; text-align:center; }
.doc-stat .sv { font-size:18px; font-weight:900; color:var(--text-primary,#0F172A); }
.doc-stat .sl { font-size:10.5px; font-weight:700; color:var(--text-secondary,#64748B); }
.doc-stat .sv.red { color:#EF4444; }
.doc-stat .sv.amber { color:#D97706; }

.doc-row {
    display: flex; align-items: center; gap: 12px; padding: 12px 0;
    border-bottom: 1px solid var(--border,#EEF2F7);
}
.doc-row:last-child { border-bottom: none; }
.doc-thumb {
    width: 44px; height: 44px; border-radius: 12px; background:#F1F5F9; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:18px; overflow:hidden;
}
.doc-thumb img { width:100%; height:100%; object-fit:cover; }
.doc-name { font-size:13.5px; font-weight:800; color:var(--text-primary,#0F172A); }
.doc-cat { font-size:10.5px; color:var(--text-secondary,#64748B); }
.exp-warn { font-size:10.5px; font-weight:800; }
.exp-warn.expired { color:#EF4444; }
.exp-warn.soon { color:#D97706; }
.doc-footer-info { font-size:10.5px; color:var(--text-secondary,#64748B); margin-top:2px; }
.status-badge { padding:3px 9px; border-radius:20px; font-size:10px; font-weight:800; }
.status-badge.active { background:#D1FAE5; color:#047857; }
.status-badge.expired { background:#FEE2E2; color:#B91C1C; }
.status-badge.archived { background:#E2E8F0; color:#64748B; }
.fav { color:#F59E0B; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="doc-page">
    <div class="doc-hero">
        <div class="rt-location-pill" style="background:rgba(255,255,255,0.2);">📁 Dokumen Digital &amp; Catatan Penting</div>
        <div style="font-size:22px; font-weight:900; margin:6px 0 4px;">Penyimpanan Dokumen</div>
        <div style="font-size:12.5px; opacity:0.9;">Simpan foto depan &amp; belakang dokumen penting, lengkap dengan pengingat masa berlaku.</div>
        <div style="margin-top:16px;">
            <button type="button" class="btn-announcement-header" onclick="openAddModal()" style="background:#fff; color:#475569; border:none; font-weight:900;">➕ Tambah Dokumen</button>
        </div>
    </div>

    <div class="doc-stats">
        <div class="doc-stat"><div class="sv"><?= (int)$summary['total_docs'] ?></div><div class="sl">Total Dokumen</div></div>
        <div class="doc-stat"><div class="sv red"><?= (int)$summary['expired'] ?></div><div class="sl">Sudah Kadaluarsa</div></div>
        <div class="doc-stat"><div class="sv amber"><?= (int)$summary['favorites'] ?></div><div class="sl">Favorit</div></div>
    </div>

    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-title"><span>📋</span> Semua Dokumen</div>
        </div>

        <?php if (empty($documents)): ?>
            <div style="text-align:center; padding:20px; color:var(--text-secondary); font-size:12.5px;">
                Belum ada dokumen tersimpan.
            </div>
        <?php else: ?>
            <?php foreach ($documents as $d): ?>
                <div class="doc-row" onclick="window.location='/documents/<?= (int)$d['id'] ?>'" style="cursor:pointer;">
                    <div class="doc-thumb">
                        <?php if (!empty($d['photo_path'])): ?>
                            <img src="<?= esc($d['photo_path']) ?>" alt="">
                        <?php else: ?>
                            📄
                        <?php endif; ?>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                            <span class="doc-name"><?= esc($d['name']) ?></span>
                            <?php if ($d['is_favorite']): ?><span class="fav">★</span><?php endif; ?>
                            <span class="status-badge <?= esc($d['status']) ?>"><?= esc($d['status']) ?></span>
                        </div>
                        <div class="doc-cat"><?= esc($d['category']) ?> • <?= esc($d['document_type']) ?></div>
                        <?php if ($d['expiry_date']): ?>
                            <div class="exp-warn <?= $d['is_expired'] ? 'expired' : ($d['is_expiring_soon'] ? 'soon' : '') ?>">
                                <?= $d['is_expired'] ? '⚠️ Kadaluarsa ' . date('d M Y', strtotime($d['expiry_date'])) : ($d['is_expiring_soon'] ? '⏰ Hampir kadaluarsa: ' . date('d M Y', strtotime($d['expiry_date'])) : 'Berlaku s.d. ' . date('d M Y', strtotime($d['expiry_date']))) ?>
                            </div>
                        <?php else: ?>
                            <div class="doc-footer-info">Tanpa tanggal kadaluarsa</div>
                        <?php endif; ?>
                    </div>
                    <span style="color:var(--text-secondary); font-size:14px;">›</span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Tambah Dokumen -->
<div class="modal-overlay" id="addModal">
    <div class="modal-card">
        <div class="modal-header"><h3>➕ Tambah Dokumen</h3><button type="button" class="modal-close" onclick="closeModal('addModal')">×</button></div>
        <div class="modal-body">
            <label class="form-label">Nama Dokumen</label>
            <input type="text" class="form-control" id="d-name" placeholder="KTP An. Budi">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div>
                    <label class="form-label">Kategori</label>
                    <select class="form-control" id="d-category">
                        <option>Identitas</option><option>Kendaraan</option><option>Properti</option>
                        <option>Kesehatan</option><option>Keuangan</option><option>Pendidikan</option><option>Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Tipe</label>
                    <input type="text" class="form-control" id="d-type" placeholder="KTP/SIM/Paspor">
                </div>
            </div>
            <label class="form-label">Nomor Dokumen (opsional)</label>
            <input type="text" class="form-control" id="d-number" placeholder="Nomor identitas">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div>
                    <label class="form-label">Masa Berlaku Sampai</label>
                    <input type="date" class="form-control" id="d-expiry">
                </div>
                <div>
                    <label class="form-label">Foto Depan</label>
                    <input type="file" class="form-control" id="d-front" accept="image/*">
                </div>
            </div>
            <label class="form-label">Foto Belakang</label>
            <input type="file" class="form-control" id="d-back" accept="image/*">
            <label class="form-label">Catatan (opsional)</label>
            <input type="text" class="form-control" id="d-notes" placeholder="Disimpan di laci meja">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary" onclick="saveDoc()">Simpan</button>
            <button type="button" class="btn-secondary" onclick="closeModal('addModal')">Batal</button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function openModal(id) { const el = document.getElementById(id); if (el) el.classList.add('open'); }
function closeModal(id) { const el = document.getElementById(id); if (el) el.classList.remove('open'); }
function openAddModal() {
    document.getElementById('d-name').value = '';
    document.getElementById('d-type').value = '';
    document.getElementById('d-number').value = '';
    document.getElementById('d-expiry').value = '';
    document.getElementById('d-front').value = '';
    document.getElementById('d-back').value = '';
    document.getElementById('d-notes').value = '';
    openModal('addModal');
}

function fileToBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = e => resolve(e.target.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

async function saveDoc() {
    const fd = new FormData();
    fd.append('name', document.getElementById('d-name').value);
    fd.append('category', document.getElementById('d-category').value);
    fd.append('document_type', document.getElementById('d-type').value);
    fd.append('document_number', document.getElementById('d-number').value);
    fd.append('expiry_date', document.getElementById('d-expiry').value);
    fd.append('notes', document.getElementById('d-notes').value);

    const front = document.getElementById('d-front').files[0];
    const back = document.getElementById('d-back').files[0];
    if (front) fd.append('photo_front', front);
    if (back) fd.append('photo_back', back);

    fetch('/documents/store', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.success) location.reload();
    });
}
</script>
<?= $this->endSection() ?>