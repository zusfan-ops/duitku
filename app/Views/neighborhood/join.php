<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.join-page {
    max-width: 540px;
    margin: 0 auto;
    padding-bottom: 100px;
}
.join-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 24px;
    padding: 26px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="join-page">
    <div class="text-center mb-4">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: #ECFDF5; color: #059669; font-size: 28px;">
            🏘️
        </div>
        <h4 class="fw-bold mb-1">Gabung Komunitas RT</h4>
        <p class="text-muted small">Hubungkan akun Anda dengan lingkungan RT setempat untuk menikmati pinjam alat bersama dan layanan titip belanja tetangga.</p>
    </div>

    <div class="join-card mb-4">
        <form id="joinRtForm">
            <div class="mb-3">
                <label class="form-label fw-bold small">Kode Unik RT</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                    <input type="text" name="unique_code" class="form-control form-control-lg text-uppercase fw-bold" placeholder="Contoh: RT04-RW02-GRIYA-2026" required style="letter-spacing: 1px;">
                </div>
                <div class="form-text small">Dapatkan kode unik ini dari Ketua RT atau tetangga Anda.</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">Status Tempat Tinggal</label>
                <div class="d-flex gap-2">
                    <div class="form-check border p-3 rounded-3 flex-fill">
                        <input class="form-check-input ms-0 me-2" type="radio" name="residence_status" id="resPermanent" value="permanent" checked>
                        <label class="form-check-label fw-bold small" for="resPermanent">
                            🏠 Warga Tetap
                            <div class="text-muted fw-normal" style="font-size: 11px;">KTP beralamat di RT ini</div>
                        </label>
                    </div>
                    <div class="form-check border p-3 rounded-3 flex-fill">
                        <input class="form-check-input ms-0 me-2" type="radio" name="residence_status" id="resTemporary" value="temporary">
                        <label class="form-check-label fw-bold small" for="resTemporary">
                            🏢 Domisili / Kontrak
                            <div class="text-muted fw-normal" style="font-size: 11px;">Penyewa rumah / kost</div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small">Nomor Rumah / Blok</label>
                <input type="text" name="house_number" class="form-control" placeholder="Contoh: Blok B No. 12" required>
            </div>

            <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill fw-bold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Gabung Sekarang
            </button>
        </form>
    </div>

    <div class="text-center">
        <p class="text-muted small mb-2">Anda Ketua RT atau ingin menginisiasi RT baru?</p>
        <a href="/neighborhood/create" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-plus-circle me-1"></i> Daftarkan Lingkungan RT Baru
        </a>
    </div>
</div>

<script>
document.getElementById('joinRtForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('/neighborhood/join', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = '/neighborhood';
        } else {
            alert(data.message || 'Gagal bergabung.');
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan jaringan.');
    });
});
</script>
<?= $this->endSection() ?>
