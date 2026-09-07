<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.doc-detail-page { max-width: 680px; margin: 0 auto; padding-bottom: 120px; }
.doc-photo-wrap {
    border: 1.5px solid var(--border,#E2E8F0); border-radius: 20px;
    overflow: hidden; margin-bottom: 16px; background: #fff;
}
.doc-photo { width: 100%; max-height: 420px; object-fit: contain; display: block; background: #F8FAFC; }
.doc-photo-empty { padding: 40px; text-align: center; color: var(--text-secondary,#64748B); font-size: 13px; }
.doc-label { font-size: 10.5px; font-weight: 800; color: var(--text-secondary,#64748B); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
.doc-value { font-size: 14px; font-weight: 800; color: var(--text-primary,#0F172A); margin-bottom: 14px; }
.info-banner {
    padding: 12px 16px; border-radius: 14px; margin-bottom: 16px;
    font-size: 12.5px; font-weight: 800;
}
.info-banner.expired { background: #FEE2E2; color: #B91C1C; }
.info-banner.soon { background: #FEF3C7; color: #B45309; }
.doc-actions { display: flex; gap: 10px; flex-wrap: wrap; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="doc-detail-page">
    <a href="/documents" style="color:var(--primary); font-size:12px; font-weight:700; text-decoration:none;">← Kembali ke Dokumen</a>

    <?php
    $isExpired = $document['expiry_date'] && $document['expiry_date'] < date('Y-m-d');
    $isSoon = !$isExpired && $document['expiry_date'] && strtotime($document['expiry_date']) <= strtotime('+30 days');
    ?>
    <?php if ($isExpired): ?>
        <div class="info-banner expired" style="margin-top:14px;">⚠️ Dokumen ini sudah melewati masa berlaku.</div>
    <?php elseif ($isSoon): ?>
        <div class="info-banner soon" style="margin-top:14px;">⏰ Dokumen ini akan segera kadaluarsa.</div>
    <?php endif; ?>

    <h2 style="font-size:20px; font-weight:900; margin:16px 0 4px; color:var(--text-primary);">
        <?= esc($document['name']) ?> <?php if ($document['is_favorite']): ?><span style="color:#F59E0B;">★</span><?php endif; ?>
    </h2>
    <div style="font-size:12px; color:var(--text-secondary); margin-bottom:18px;"><?= esc($document['category']) ?> • <?= esc($document['document_type']) ?></div>

    <div class="doc-photo-wrap">
        <?php if (!empty($document['photo_path'])): ?>
            <img src="<?= esc($document['photo_path']) ?>" class="doc-photo" alt="depan">
        <?php else: ?>
            <div class="doc-photo-empty">Tidak ada foto depan</div>
        <?php endif; ?>
    </div>
    <?php if (!empty($document['photo_back_path'])): ?>
        <div class="doc-photo-wrap">
            <img src="<?= esc($document['photo_back_path']) ?>" class="doc-photo" alt="belakang">
        </div>
    <?php endif; ?>

    <div class="info-card">
        <div class="doc-label">Nomor Dokumen</div>
        <div class="doc-value"><?= esc($document['document_number'] ?: '—') ?></div>
        <div class="doc-label">Atas Nama</div>
        <div class="doc-value"><?= esc($document['owner_name'] ?: '—') ?></div>
        <div class="doc-label">Diterbitkan Oleh</div>
        <div class="doc-value"><?= esc($document['issuing_authority'] ?: '—') ?></div>
        <div class="doc-label">Tanggal Terbit</div>
        <div class="doc-value"><?= $document['issued_date'] ? date('d M Y', strtotime($document['issued_date'])) : '—' ?></div>
        <div class="doc-label">Masa Berlaku</div>
        <div class="doc-value"><?= $document['expiry_date'] ? date('d M Y', strtotime($document['expiry_date'])) : 'Selamanya' ?></div>
        <div class="doc-label">Catatan</div>
        <div class="doc-value" style="margin-bottom:0;"><?= esc($document['notes'] ?: '—') ?></div>
    </div>

    <div class="doc-actions">
        <button type="button" class="btn-primary" onclick="toggleFav()">
            <?= $document['is_favorite'] ? '★ Hapus Favorit' : '☆ Tandai Favorit' ?>
        </button>
        <button type="button" class="btn-secondary" onclick="delDoc()">🗑️ Hapus</button>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const DOC_ID = <?= (int)$document['id'] ?>;

function toggleFav() {
    fetch('/documents/favorite/' + DOC_ID, { method: 'POST' })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.success) location.reload();
    });
}

function delDoc() {
    if (!confirm('Hapus dokumen ini?')) return;
    fetch('/documents/delete/' + DOC_ID, { method: 'POST' })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.success) window.location = '/documents';
    });
}
</script>
<?= $this->endSection() ?>