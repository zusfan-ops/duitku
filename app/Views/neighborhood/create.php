<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.create-rt-page {
    max-width: 580px;
    margin: 0 auto;
    padding-bottom: 120px;
}

/* ── Top Nav Back Bar ── */
.create-topbar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}
.btn-back-circle {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: var(--bg-card, #ffffff);
    border: 1.5px solid var(--border, #E2E8F0);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-primary, #0F172A);
    text-decoration: none;
    font-size: 16px;
    box-shadow: var(--shadow-sm);
    transition: all 0.2s ease;
}
.btn-back-circle:hover {
    border-color: var(--primary, #059669);
    color: var(--primary, #059669);
}
.create-topbar-title {
    font-size: 18px;
    font-weight: 900;
    color: var(--text-primary, #0F172A);
}

/* ── Form Card ── */
.create-card {
    background: var(--bg-card, #ffffff);
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: var(--radius-xl, 24px);
    padding: 24px;
    box-shadow: var(--shadow-md, 0 4px 14px rgba(15,23,42,0.06));
}
.create-section-title {
    font-size: 13px;
    font-weight: 900;
    color: var(--primary, #059669);
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.create-form-group {
    margin-bottom: 16px;
}
.create-form-label {
    display: block;
    font-size: 12.5px;
    font-weight: 800;
    color: var(--text-primary, #0F172A);
    margin-bottom: 6px;
}
.create-input-text {
    width: 100%;
    padding: 12px 14px;
    font-size: 13.5px;
    font-weight: 600;
    font-family: inherit;
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: var(--radius-md, 14px);
    background: var(--bg, #F8FAFC);
    color: var(--text-primary, #0F172A);
    outline: none;
    transition: all 0.2s ease;
}
.create-input-text:focus {
    background: var(--bg-card, #ffffff);
    border-color: var(--primary, #059669);
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12);
}
.create-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

/* ── Toggle Switch Card ── */
.create-toggle-card {
    background: var(--bg, #F8FAFC);
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: var(--radius-md, 14px);
    padding: 14px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
}
.create-toggle-checkbox {
    width: 20px;
    height: 20px;
    accent-color: var(--primary, #059669);
    cursor: pointer;
}

/* ── Submit CTA ── */
.btn-create-submit {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 30px;
    background: linear-gradient(135deg, #065F46 0%, #059669 60%, #10B981 100%);
    color: #ffffff;
    font-size: 14.5px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(5, 150, 105, 0.35);
    transition: all 0.2s ease;
    font-family: inherit;
}
.btn-create-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(5, 150, 105, 0.45);
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="create-rt-page">
    
    <div class="create-topbar">
        <a href="/neighborhood/join" class="btn-back-circle">←</a>
        <h1 class="create-topbar-title">Daftarkan Lingkungan RT Baru</h1>
    </div>

    <div class="create-card">
        <form id="createRtForm">
            
            <div class="create-section-title">
                <span>📍</span> Informasi Wilayah &amp; Nama Lingkungan
            </div>

            <div class="create-form-group">
                <label class="create-form-label" for="rtNameInput">Nama Komunitas / Lingkungan RT</label>
                <input type="text" id="rtNameInput" name="name" class="create-input-text" placeholder="Contoh: RT 04 Griya Asri Permai" required>
            </div>

            <div class="create-grid-2">
                <div class="create-form-group">
                    <label class="create-form-label">Nomor RT</label>
                    <input type="text" name="rt" class="create-input-text" placeholder="Contoh: 04" required>
                </div>
                <div class="create-form-group">
                    <label class="create-form-label">Nomor RW</label>
                    <input type="text" name="rw" class="create-input-text" placeholder="Contoh: 02" required>
                </div>
            </div>

            <div class="create-grid-2">
                <div class="create-form-group">
                    <label class="create-form-label">Kelurahan / Desa</label>
                    <input type="text" name="subdistrict" class="create-input-text" placeholder="Contoh: Sukamaju" required>
                </div>
                <div class="create-form-group">
                    <label class="create-form-label">Kecamatan</label>
                    <input type="text" name="district" class="create-input-text" placeholder="Contoh: Cilodong" required>
                </div>
            </div>

            <div class="create-grid-2">
                <div class="create-form-group">
                    <label class="create-form-label">Kota / Kabupaten</label>
                    <input type="text" name="city" class="create-input-text" placeholder="Contoh: Kota Depok" required>
                </div>
                <div class="create-form-group">
                    <label class="create-form-label">Provinsi</label>
                    <input type="text" name="province" class="create-input-text" placeholder="Contoh: Jawa Barat" required>
                </div>
            </div>

            <div class="create-section-title" style="margin-top: 14px;">
                <span>⚙️</span> Pengaturan Kode &amp; Batasan Pinjam
            </div>

            <div class="create-form-group">
                <label class="create-form-label">Kode Unik RT Kustom (Opsional)</label>
                <input type="text" name="unique_code" class="create-input-text uppercase-code" placeholder="Kosongkan untuk otomatis (misal: RT04-RW02-GRIYA-2026)">
            </div>

            <div class="create-form-group">
                <label class="create-form-label">Batas Nilai Pinjam Warga Domisili (Rp)</label>
                <input type="number" name="max_borrow_limit_domisili" class="create-input-text" value="250000" step="50000">
            </div>

            <label class="create-toggle-card" for="autoApprovalCheck">
                <input type="checkbox" name="auto_approval" value="1" id="autoApprovalCheck" class="create-toggle-checkbox">
                <div>
                    <strong style="font-size: 13px; color: var(--text-primary); display: block;">Auto-Approval Warga Baru</strong>
                    <span style="font-size: 11.5px; color: var(--text-secondary); line-height: 1.35;">
                        Warga langsung aktif tanpa harus menunggu persetujuan manual Ketua RT.
                    </span>
                </div>
            </label>

            <button type="submit" class="btn-create-submit" id="btnSubmitCreate">
                <span>Daftarkan RT &amp; Jadi Admin</span>
                <span>👑</span>
            </button>
        </form>
    </div>

</div>

<script>
document.getElementById('createRtForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitCreate');
    btn.disabled = true;
    btn.innerHTML = '<span>Mendaftarkan...</span>';

    const formData = new FormData(this);

    fetch('/neighborhood/store', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Lingkungan RT berhasil didaftarkan!');
            window.location.href = data.redirect || '/neighborhood';
        } else {
            alert(data.message || 'Gagal mendaftarkan RT.');
            btn.disabled = false;
            btn.innerHTML = '<span>Daftarkan RT &amp; Jadi Admin</span> <span>👑</span>';
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan jaringan.');
        btn.disabled = false;
        btn.innerHTML = '<span>Daftarkan RT &amp; Jadi Admin</span> <span>👑</span>';
    });
});
</script>
<?= $this->endSection() ?>
