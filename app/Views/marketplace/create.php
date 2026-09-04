<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.market-create-page {
    padding: 14px 16px 110px;
    max-width: 680px;
    margin: 0 auto;
}

.create-header {
    margin-bottom: 18px;
}
.create-header h2 {
    font-size: 20px;
    font-weight: 800;
    margin: 0 0 4px;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 8px;
}
.create-header p {
    font-size: 13px;
    color: var(--text-muted);
    margin: 0;
}

.form-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    margin-bottom: 18px;
}

.form-section-title {
    font-size: 11.5px;
    font-weight: 800;
    color: var(--primary);
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin: 0 0 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.form-group {
    margin-bottom: 16px;
}
.form-label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 6px;
}
.form-label span.req {
    color: #EF4444;
}

.form-control {
    width: 100%;
    padding: 11px 14px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    font-size: 13.5px;
    color: var(--text);
    outline: none;
    box-sizing: border-box;
    transition: border-color 0.15s ease;
}
.form-control:focus {
    border-color: var(--primary);
}

/* Type Toggle */
.type-toggle-group {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
}
.type-toggle-btn {
    flex: 1;
    padding: 12px 14px;
    border-radius: 14px;
    border: 2px solid var(--border);
    background: var(--bg);
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 800;
    text-align: center;
    cursor: pointer;
    transition: all 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.type-toggle-btn.active.sale {
    border-color: #059669;
    background: rgba(5, 150, 105, 0.08);
    color: #059669;
}
.type-toggle-btn.active.rent {
    border-color: #6D28D9;
    background: rgba(109, 40, 217, 0.08);
    color: #6D28D9;
}

/* Photo Upload Zone */
.photo-upload-zone {
    border: 2px dashed var(--border);
    border-radius: 16px;
    padding: 24px 16px;
    text-align: center;
    cursor: pointer;
    background: var(--bg);
    transition: border-color 0.15s, background 0.15s;
    margin-bottom: 12px;
}
.photo-upload-zone:hover {
    border-color: var(--primary);
    background: rgba(5, 150, 105, 0.03);
}
.photo-upload-icon {
    font-size: 32px;
    margin-bottom: 6px;
}
.photo-upload-text {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
}
.photo-upload-hint {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 4px;
}

/* Preview Grid */
.photo-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 10px;
    margin-bottom: 16px;
}
.photo-preview-item {
    position: relative;
    width: 100%;
    padding-top: 100%;
    border-radius: 12px;
    overflow: hidden;
    background: #374151;
    border: 2px solid var(--border);
}
.photo-preview-img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.photo-preview-del {
    position: absolute;
    top: 4px;
    right: 4px;
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
.photo-preview-primary {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(5, 150, 105, 0.9);
    color: #fff;
    font-size: 9px;
    font-weight: 800;
    text-align: center;
    padding: 2px 0;
}

/* Safety Tip Box */
.safety-tip-box {
    background: #FEF3C7;
    border: 1px solid #FDE68A;
    border-radius: 14px;
    padding: 12px 14px;
    margin-bottom: 18px;
    font-size: 12px;
    color: #92400E;
    line-height: 1.45;
}
[data-theme="dark"] .safety-tip-box {
    background: rgba(245, 158, 11, 0.12);
    border-color: rgba(245, 158, 11, 0.25);
    color: #FBBF24;
}

.btn-submit-listing {
    width: 100%;
    padding: 14px;
    background: #059669;
    color: #fff;
    border: none;
    border-radius: 14px;
    font-size: 14.5px;
    font-weight: 800;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 16px rgba(5, 150, 105, 0.35);
    transition: transform 0.15s ease, background 0.15s ease;
}
.btn-submit-listing:hover {
    background: #047857;
    transform: translateY(-1px);
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="market-create-page">

    <div class="create-header">
        <a href="/marketplace" style="display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:700;color:var(--text-muted);text-decoration:none;margin-bottom:8px;">
            ← Kembali ke Pasar
        </a>
        <h2><span>🏷️</span> Pasang Iklan Jual / Sewa</h2>
        <p>Iklankan motor, mobil, rumah, gadget, atau barang bekas Anda langsung ke komunitas pengguna DuitKu.</p>
    </div>

    <form id="createListingForm" onsubmit="submitListing(event)" enctype="multipart/form-data">

        <!-- 1. TIPE TRANSAKSI -->
        <div class="form-card">
            <div class="form-section-title">1. Tipe Transaksi</div>
            <div class="type-toggle-group">
                <div class="type-toggle-btn active sale" id="btnTypeSale" onclick="selectType('sale')">
                    🏷️ Dijual (Barang Bekas / Baru)
                </div>
                <div class="type-toggle-btn rent" id="btnTypeRent" onclick="selectType('rent')">
                    🔑 Disewakan (Rental / Kontrak)
                </div>
            </div>
            <input type="hidden" name="type" id="listingType" value="sale">

            <div class="form-group" id="rentPeriodGroup" style="display:none;">
                <label class="form-label">Periode Sewa <span class="req">*</span></label>
                <select name="rent_period" id="rentPeriod" class="form-control">
                    <option value="hari">Per Hari</option>
                    <option value="bulan" selected>Per Bulan</option>
                    <option value="tahun">Per Tahun</option>
                </select>
            </div>
        </div>

        <!-- 2. FOTO PRODUK (MULTIPLE IMAGES) -->
        <div class="form-card">
            <div class="form-section-title">2. Foto Produk (Bisa Lebih dari 1 Foto)</div>
            
            <div class="photo-upload-zone" onclick="document.getElementById('fileInput').click()">
                <div class="photo-upload-icon">📸</div>
                <div class="photo-upload-text">+ Tambah Foto (Pilih dari Galeri / Kamera)</div>
                <div class="photo-upload-hint">Format JPG, PNG, atau WebP. Foto pertama otomatis jadi sampul utama.</div>
            </div>
            <input type="file" id="fileInput" name="images[]" multiple accept="image/*" style="display:none" onchange="handleFiles(this.files)">

            <!-- Preview Grid -->
            <div class="photo-preview-grid" id="previewGrid"></div>
        </div>

        <!-- 3. DETAIL PRODUK -->
        <div class="form-card">
            <div class="form-section-title">3. Informasi Produk</div>

            <div class="form-group">
                <label class="form-label">Judul Iklan <span class="req">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="Contoh: Honda Vario 150 2021 Mulus Siap Pakai" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label class="form-label">Kategori <span class="req">*</span></label>
                    <select name="category" class="form-control" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= esc($cat) ?>"><?= esc($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Kondisi <span class="req">*</span></label>
                    <select name="condition" class="form-control" required>
                        <option value="used_good" selected>Bekas (Mulus / Siap Pakai)</option>
                        <option value="like_new">Bekas (Seperti Baru)</option>
                        <option value="used_fair">Bekas (Layak Pakai / Minus)</option>
                        <option value="new">Baru (Belum Pernah Dipakai)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Harga (<?= esc($symbol) ?>) <span class="req">*</span></label>
                <input type="number" name="price" class="form-control" placeholder="Contoh: 14500000" min="1000" required>
            </div>

            <div class="form-group">
                <label class="form-label">Lokasi / Area COD <span class="req">*</span></label>
                <input type="text" name="location" class="form-control" placeholder="Contoh: Tebet, Jakarta Selatan / Sekitarnya" required>
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi Lengkap</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Jelaskan spesifikasi, tahun pembelian, kelengkapan surat-surat/nota, kelebihan, dan minus barang secara jujur."></textarea>
            </div>
        </div>

        <!-- 4. KONTAK & KEAMANAN TRANSAKSI -->
        <div class="form-card">
            <div class="form-section-title">4. Kontak & Pembayaran Online Aman</div>

            <div class="form-group">
                <label class="form-label">Nomor WhatsApp untuk Dihubungi Pembeli <span class="req">*</span></label>
                <input type="tel" name="whatsapp" class="form-control" placeholder="08xxxxxxxxxx" value="<?= esc($user['phone'] ?? '') ?>" required>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                    Calon pembeli dapat mengklik tombol WhatsApp langsung dari halaman iklan Anda.
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Link Shopee / Tokopedia (Opsional - Sangat Dianjurkan)</label>
                <input type="url" name="third_party_url" class="form-control" placeholder="https://shopee.co.id/... atau https://tokopedia.com/...">
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                    Sertakan link produk Anda di Shopee/Tokopedia jika pembeli ingin transaksi jarak jauh dengan pembayaran rekening bersama yang aman.
                </div>
            </div>

            <div class="safety-tip-box">
                🛡️ <strong>Ketentuan Transaksi Komunitas:</strong><br>
                Pastikan Anda bersedia bertemu langsung (COD) di lokasi umum yang aman atau bertransaksi menggunakan platform pihak ketiga bergaransi. Jangan meminta uang muka (DP) kepada calon pembeli sebelum bertemu.
            </div>

            <button type="submit" class="btn-submit-listing" id="btnSubmit">
                🚀 Tayangkan Iklan Sekarang
            </button>
        </div>

    </form>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let selectedFiles = [];

function selectType(type) {
    document.getElementById('listingType').value = type;
    const btnSale = document.getElementById('btnTypeSale');
    const btnRent = document.getElementById('btnTypeRent');
    const rentGroup = document.getElementById('rentPeriodGroup');

    if (type === 'sale') {
        btnSale.classList.add('active');
        btnRent.classList.remove('active');
        rentGroup.style.display = 'none';
    } else {
        btnRent.classList.add('active');
        btnSale.classList.remove('active');
        rentGroup.style.display = 'block';
    }
}

function handleFiles(files) {
    for (let i = 0; i < files.length; i++) {
        selectedFiles.push(files[i]);
    }
    renderPreviews();
}

function renderPreviews() {
    const grid = document.getElementById('previewGrid');
    grid.innerHTML = '';

    selectedFiles.forEach((file, idx) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'photo-preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" class="photo-preview-img" alt="Preview">
                <button type="button" class="photo-preview-del" onclick="removePhoto(${idx})">✕</button>
                ${idx === 0 ? '<div class="photo-preview-primary">UTAMA</div>' : ''}
            `;
            grid.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function removePhoto(idx) {
    selectedFiles.splice(idx, 1);
    renderPreviews();
}

function submitListing(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.innerText = 'Sedang Mengunggah & Memproses...';

    const form = document.getElementById('createListingForm');
    const formData = new FormData(form);

    // Remove default input files and append accumulated selectedFiles
    formData.delete('images[]');
    selectedFiles.forEach(file => {
        formData.append('images[]', file);
    });

    fetch('/marketplace/store', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            window.location.href = res.redirect || '/marketplace';
        } else {
            alert(res.message || 'Gagal memasang iklan.');
            btn.disabled = false;
            btn.innerText = '🚀 Tayangkan Iklan Sekarang';
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan jaringan.');
        btn.disabled = false;
        btn.innerText = '🚀 Tayangkan Iklan Sekarang';
    });
}
</script>
<?= $this->endSection() ?>
