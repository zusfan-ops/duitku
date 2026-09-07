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

/* ── Upload Box & Preview ── */
.upload-dropzone {
    border: 2px dashed var(--border, #CBD5E1);
    border-radius: var(--radius-md, 16px);
    background: var(--bg, #F8FAFC);
    padding: 20px 16px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}
.upload-dropzone:hover {
    border-color: var(--primary, #059669);
    background: #ECFDF5;
}
.upload-dropzone-icon {
    font-size: 32px;
    margin-bottom: 6px;
    display: block;
}
.upload-dropzone-text {
    font-size: 12.5px;
    font-weight: 700;
    color: var(--text-primary, #0F172A);
    margin-bottom: 4px;
}
.upload-dropzone-hint {
    font-size: 11px;
    color: var(--text-secondary, #64748B);
}
.upload-btn-row {
    display: flex;
    gap: 8px;
    justify-content: center;
    margin-top: 12px;
}
.upload-btn-chip {
    padding: 6px 12px;
    background: #ffffff;
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
    color: var(--text-primary, #0F172A);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.upload-btn-chip:hover {
    border-color: var(--primary, #059669);
    color: var(--primary, #059669);
}

.upload-preview-card {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #ECFDF5;
    border: 1.5px solid #A7F3D0;
    border-radius: var(--radius-md, 16px);
    padding: 12px 14px;
}
.upload-preview-thumb {
    width: 54px;
    height: 54px;
    border-radius: 10px;
    object-fit: cover;
    border: 1px solid #6EE7B7;
}
.upload-preview-info {
    flex: 1;
    min-width: 0;
}
.upload-preview-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11.5px;
    font-weight: 800;
    color: #065F46;
}
.upload-preview-name {
    font-size: 11px;
    color: #047857;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 2px;
}
.upload-preview-remove {
    background: none;
    border: none;
    color: #EF4444;
    cursor: pointer;
    font-size: 18px;
    padding: 6px;
    border-radius: 8px;
}
.upload-preview-remove:hover {
    background: #FEE2E2;
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

    <?php if (!empty($existingRt) && ($existingRt['status'] ?? '') === 'pending'): ?>
    <div style="background: #FEF3C7; border: 1.5px solid #FDE68A; border-radius: 16px; padding: 16px; margin-bottom: 20px;">
        <div style="display: flex; gap: 10px; align-items: flex-start;">
            <span style="font-size: 22px;">⏳</span>
            <div>
                <strong style="color: #92400E; font-size: 13.5px; display: block; margin-bottom: 3px;">Pengajuan Anda Sedang Ditinjau</strong>
                <p style="color: #B45309; font-size: 12px; margin: 0; line-height: 1.4;">
                    Anda telah mengajukan RT <strong><?= esc($existingRt['name']) ?></strong>. Superadmin sedang memverifikasi SK penunjukan Anda.
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="create-card">
        <form id="createRtForm" enctype="multipart/form-data">
            <?= csrf_field() ?>
            
            <div class="create-section-title">
                <span>📍</span> Informasi Wilayah &amp; Nama Lingkungan
            </div>

            <div class="create-form-group">
                <label class="create-form-label" for="rtNameInput">Nama Komunitas / Lingkungan RT</label>
                <input type="text" id="rtNameInput" name="name" class="create-input-text" placeholder="Contoh: RT 04 Jasmine Park" required>
            </div>

            <div class="create-grid-2">
                <div class="create-form-group">
                    <label class="create-form-label">Nomor RT</label>
                    <input type="text" name="rt" class="create-input-text" placeholder="Contoh: 04" required>
                </div>
                <div class="create-form-group">
                    <label class="create-form-label">Nomor RW</label>
                    <input type="text" name="rw" class="create-input-text" placeholder="Contoh: 05" required>
                </div>
            </div>

            <div class="create-grid-2">
                <div class="create-form-group">
                    <label class="create-form-label">Kelurahan / Desa</label>
                    <input type="text" name="subdistrict" class="create-input-text" placeholder="Contoh: Mranggen" required>
                </div>
                <div class="create-form-group">
                    <label class="create-form-label">Kecamatan</label>
                    <input type="text" name="district" class="create-input-text" placeholder="Contoh: Batursari" required>
                </div>
            </div>

            <div class="create-grid-2">
                <div class="create-form-group">
                    <label class="create-form-label">Kota / Kabupaten</label>
                    <input type="text" name="city" class="create-input-text" placeholder="Contoh: Demak" required>
                </div>
                <div class="create-form-group">
                    <label class="create-form-label">Provinsi</label>
                    <input type="text" name="province" class="create-input-text" placeholder="Contoh: Jawa Tengah" required>
                </div>
            </div>

            <div class="create-section-title" style="margin-top: 14px;">
                <span>📄</span> Legalitas &amp; Dokumen SK Penunjukan RT
            </div>

            <div class="create-form-group">
                <label class="create-form-label">Nomor Surat Keputusan (SK) RT</label>
                <input type="text" name="sk_number" class="create-input-text" placeholder="Contoh: SK/04/RW05/2026">
            </div>

            <div class="create-form-group">
                <label class="create-form-label">Foto / Scan Dokumen Surat Penunjukan (SK)</label>
                <input type="file" id="skFileInput" name="sk_document" accept="image/*,application/pdf" style="display: none;">
                
                <div id="uploadDropzone" class="upload-dropzone">
                    <span class="upload-dropzone-icon">📷</span>
                    <div class="upload-dropzone-text">Ambil Foto atau Pilih Berkas SK</div>
                    <div class="upload-dropzone-hint">Format: JPG, PNG, atau PDF (Maks. 5 MB)</div>
                    <div class="upload-btn-row">
                        <button type="button" class="upload-btn-chip" id="btnTriggerCamera">
                            <span>📸</span> Kamera
                        </button>
                        <button type="button" class="upload-btn-chip" id="btnTriggerGallery">
                            <span>🖼️</span> Galeri / File
                        </button>
                    </div>
                </div>

                <div id="uploadPreviewCard" class="upload-preview-card" style="display: none;">
                    <img id="uploadPreviewImg" class="upload-preview-thumb" src="" alt="Preview SK">
                    <div class="upload-preview-info">
                        <div class="upload-preview-badge">
                            <span>✅</span> Dokumen Terlampir
                        </div>
                        <div id="uploadPreviewName" class="upload-preview-name">file.jpg</div>
                    </div>
                    <button type="button" id="btnRemoveFile" class="upload-preview-remove" title="Hapus Berkas">
                        🗑️
                    </button>
                </div>
            </div>

            <div class="create-form-group">
                <label class="create-form-label">Catatan Alamat / Lingkup Kawasan (Opsional)</label>
                <textarea name="address_note" class="create-input-text" rows="2" placeholder="Contoh: Perumahan Jasmine Park Blok A - F"></textarea>
            </div>

            <div class="create-section-title" style="margin-top: 14px;">
                <span>⚙️</span> Pengaturan Kode &amp; Batasan Pinjam
            </div>

            <div class="create-form-group">
                <label class="create-form-label">Kode Unik RT Kustom (Opsional)</label>
                <input type="text" name="unique_code" class="create-input-text uppercase-code" placeholder="Kosongkan untuk otomatis (misal: RT04-RW05-JASMINE)">
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
                <span>Daftarkan RT &amp; Ajukan Verifikasi</span>
                <span>👑</span>
            </button>
        </form>
    </div>

</div>

<script>
const skFileInput = document.getElementById('skFileInput');
const uploadDropzone = document.getElementById('uploadDropzone');
const uploadPreviewCard = document.getElementById('uploadPreviewCard');
const uploadPreviewImg = document.getElementById('uploadPreviewImg');
const uploadPreviewName = document.getElementById('uploadPreviewName');
const btnRemoveFile = document.getElementById('btnRemoveFile');
const btnTriggerCamera = document.getElementById('btnTriggerCamera');
const btnTriggerGallery = document.getElementById('btnTriggerGallery');

btnTriggerCamera.addEventListener('click', (e) => {
    e.stopPropagation();
    skFileInput.setAttribute('capture', 'environment');
    skFileInput.click();
});

btnTriggerGallery.addEventListener('click', (e) => {
    e.stopPropagation();
    skFileInput.removeAttribute('capture');
    skFileInput.click();
});

uploadDropzone.addEventListener('click', () => {
    skFileInput.removeAttribute('capture');
    skFileInput.click();
});

skFileInput.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        alert('Ukuran berkas maksimal 5 MB.');
        this.value = '';
        return;
    }

    uploadPreviewName.textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
    
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            uploadPreviewImg.src = e.target.result;
            uploadPreviewImg.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        uploadPreviewImg.src = 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"><text y="20" font-size="20">📄</text></svg>';
    }

    uploadDropzone.style.display = 'none';
    uploadPreviewCard.style.display = 'flex';
});

btnRemoveFile.addEventListener('click', () => {
    skFileInput.value = '';
    uploadPreviewImg.src = '';
    uploadDropzone.style.display = 'block';
    uploadPreviewCard.style.display = 'none';
});

// Drag and drop support
uploadDropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadDropzone.style.borderColor = '#059669';
    uploadDropzone.style.background = '#ECFDF5';
});

uploadDropzone.addEventListener('dragleave', () => {
    uploadDropzone.style.borderColor = '';
    uploadDropzone.style.background = '';
});

uploadDropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadDropzone.style.borderColor = '';
    uploadDropzone.style.background = '';
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
        skFileInput.files = e.dataTransfer.files;
        skFileInput.dispatchEvent(new Event('change'));
    }
});

// Form Submission
document.getElementById('createRtForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitCreate');
    btn.disabled = true;
    btn.innerHTML = '<span>Mengirimkan Pengajuan...</span>';

    const formData = new FormData(this);

    fetch('/neighborhood/store', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async res => {
        const isJson = (res.headers.get('content-type') || '').includes('application/json');
        const data = isJson ? await res.json() : { success: false, message: 'Respon server tidak valid (' + res.status + ')' };
        if (data.success) {
            alert(data.message || 'Pengajuan RT berhasil dikirim! Menunggu verifikasi SK.');
            window.location.href = data.redirect || '/neighborhood/join';
        } else {
            alert(data.message || 'Gagal mendaftarkan RT.');
            btn.disabled = false;
            btn.innerHTML = '<span>Daftarkan RT &amp; Ajukan Verifikasi</span> <span>👑</span>';
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan: ' + (err.message || 'Gagal terhubung ke server.'));
        btn.disabled = false;
        btn.innerHTML = '<span>Daftarkan RT &amp; Ajukan Verifikasi</span> <span>👑</span>';
    });
});
</script>
<?= $this->endSection() ?>
