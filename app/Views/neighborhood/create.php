<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.create-rt-page {
    max-width: 580px;
    margin: 0 auto;
    padding-bottom: 100px;
}
.create-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 24px;
    padding: 26px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="create-rt-page">
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="/neighborhood" class="btn btn-sm btn-light rounded-circle"><i class="bi bi-arrow-left"></i></a>
        <h5 class="fw-bold mb-0">Daftarkan Lingkungan RT Baru</h5>
    </div>

    <div class="create-card">
        <form id="createRtForm">
            <div class="mb-3">
                <label class="form-label fw-bold small">Nama Komunitas / Lingkungan RT</label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: RT 04 Griya Asri Permai" required>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label fw-bold small">Nomor RT</label>
                    <input type="text" name="rt" class="form-control" placeholder="Contoh: 04" required>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small">Nomor RW</label>
                    <input type="text" name="rw" class="form-control" placeholder="Contoh: 02" required>
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label fw-bold small">Kelurahan / Desa</label>
                    <input type="text" name="subdistrict" class="form-control" placeholder="Contoh: Sukamaju" required>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small">Kecamatan</label>
                    <input type="text" name="district" class="form-control" placeholder="Contoh: Cilodong" required>
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label fw-bold small">Kota / Kabupaten</label>
                    <input type="text" name="city" class="form-control" placeholder="Contoh: Kota Depok" required>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small">Provinsi</label>
                    <input type="text" name="province" class="form-control" placeholder="Contoh: Jawa Barat" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">Kode Unik RT Kustom (Opsional)</label>
                <input type="text" name="unique_code" class="form-control text-uppercase" placeholder="Kosongkan untuk generate otomatis">
                <div class="form-text small">Kode ini akan dipakai warga untuk bergabung.</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">Batas Nilai Pinjam Warga Domisili (Rp)</label>
                <input type="number" name="max_borrow_limit_domisili" class="form-control" value="250000" step="50000">
                <div class="form-text small">Batas maksimal harga barang/deposit yang boleh dipinjam warga non-permanen tanpa persetujuan khusus.</div>
            </div>

            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" name="auto_approval" value="1" id="autoApprovalCheck">
                <label class="form-check-label small" for="autoApprovalCheck">
                    <strong>Auto-Approval Warga</strong>: Warga langsung terverifikasi begitu memasukkan kode unik RT tanpa menunggu antrean persetujuan.
                </label>
            </div>

            <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill fw-bold">
                <i class="bi bi-check-circle me-1"></i> Daftarkan Komunitas RT
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('createRtForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('/neighborhood/store', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = data.redirect || '/neighborhood';
        } else {
            alert(data.message || 'Gagal mendaftarkan RT.');
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan jaringan.');
    });
});
</script>
<?= $this->endSection() ?>
