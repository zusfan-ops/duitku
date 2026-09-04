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
    background: rgba(5, 150, 105, 0.1);
    color: #059669;
}
.type-toggle-btn.active.rent {
    border-color: #3B82F6;
    background: rgba(59, 130, 246, 0.1);
    color: #3B82F6;
}
.type-toggle-btn.active.service {
    border-color: #2563EB;
    background: rgba(37, 99, 235, 0.1);
    color: #2563EB;
}


/* Category Grid Pills */
.category-select-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 8px;
    margin-bottom: 16px;
}
.cat-pill-btn {
    padding: 9px 8px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text);
    font-size: 12px;
    font-weight: 700;
    text-align: center;
    cursor: pointer;
    transition: all 0.15s ease;
}
.cat-pill-btn.active {
    border-color: var(--primary);
    background: var(--primary);
    color: #fff;
}

/* Photo Upload */
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

/* Existing & Preview Grids */
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
    background: rgba(220, 38, 38, 0.85);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 22px;
    height: 22px;
    font-size: 11px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 2;
    transition: background 0.15s ease;
}
.photo-preview-del:hover {
    background: #dc2626;
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

/* WYSIWYG & Markdown Editor */
.desc-editor-label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
    flex-wrap: wrap;
    gap: 8px;
}
.editor-view-switch {
    display: inline-flex;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 999px;
    padding: 3px;
    gap: 2px;
}
.btn-view-toggle {
    background: transparent;
    border: none;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    padding: 4px 10px;
    border-radius: 999px;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.btn-view-toggle.active {
    background: var(--primary);
    color: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.15);
}

.wysiwyg-wrapper {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.wysiwyg-wrapper:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12);
}

.wysiwyg-toolbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
    padding: 6px 8px;
    background: var(--card);
    border-bottom: 1px solid var(--border);
}
.toolbar-group {
    display: inline-flex;
    align-items: center;
    gap: 3px;
}
.toolbar-sep {
    width: 1px;
    height: 18px;
    background: var(--border);
    margin: 0 4px;
}
.tb-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 28px;
    min-width: 28px;
    padding: 0 7px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text);
    background: transparent;
    border: 1px solid transparent;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.12s ease, border-color 0.12s ease;
}
.tb-btn:hover {
    background: var(--border);
}
.tb-btn.active {
    background: rgba(5, 150, 105, 0.15);
    color: #059669;
    border-color: rgba(5, 150, 105, 0.3);
}
.tb-md-btn {
    background: rgba(5, 150, 105, 0.1);
    color: #059669;
    border: 1px solid rgba(5, 150, 105, 0.25);
    font-size: 11.5px;
    padding: 0 10px;
    border-radius: 8px;
}
.tb-md-btn:hover {
    background: rgba(5, 150, 105, 0.2);
}

.wysiwyg-content {
    min-height: 140px;
    max-height: 360px;
    overflow-y: auto;
    padding: 12px 14px;
    font-size: 13.5px;
    line-height: 1.6;
    color: var(--text);
    outline: none;
    word-break: break-word;
}
.wysiwyg-content[data-placeholder]:empty:before {
    content: attr(data-placeholder);
    color: var(--text-muted);
    opacity: 0.7;
    pointer-events: none;
    display: block;
}
.wysiwyg-content p {
    margin: 0 0 8px;
}
.wysiwyg-content p:last-child {
    margin-bottom: 0;
}
.wysiwyg-content h2 {
    font-size: 16px;
    font-weight: 800;
    margin: 10px 0 6px;
    color: var(--text);
}
.wysiwyg-content h3 {
    font-size: 14.5px;
    font-weight: 800;
    margin: 8px 0 4px;
    color: var(--text);
}
.wysiwyg-content ul, .wysiwyg-content ol {
    margin: 6px 0 8px 22px;
    padding: 0;
}
.wysiwyg-content li {
    margin-bottom: 3px;
}
.wysiwyg-content blockquote {
    border-left: 3px solid #059669;
    padding: 4px 10px;
    margin: 8px 0;
    background: rgba(5, 150, 105, 0.08);
    border-radius: 4px;
    font-style: italic;
    color: var(--text);
}
.wysiwyg-content code {
    background: var(--card);
    border: 1px solid var(--border);
    padding: 2px 5px;
    border-radius: 4px;
    font-size: 12px;
    font-family: monospace;
}
.wysiwyg-content pre {
    background: var(--card);
    border: 1px solid var(--border);
    padding: 8px 10px;
    border-radius: 8px;
    overflow-x: auto;
    margin: 8px 0;
}
.wysiwyg-content hr {
    border: none;
    border-top: 1px dashed var(--border);
    margin: 12px 0;
}

.markdown-source-area {
    width: 100%;
    min-height: 140px;
    max-height: 360px;
    padding: 12px 14px;
    background: var(--bg);
    border: none;
    outline: none;
    font-family: Consolas, Monaco, "Courier New", monospace;
    font-size: 13px;
    line-height: 1.55;
    color: var(--text);
    resize: vertical;
    box-sizing: border-box;
}

.editor-hint {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    font-size: 11.5px;
    color: var(--text-muted);
    margin-top: 6px;
    line-height: 1.45;
}

/* Paste Markdown Modal */
.md-paste-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.md-paste-overlay.show {
    display: flex;
}
.md-paste-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 18px;
    max-width: 520px;
    width: 100%;
    padding: 18px 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    animation: modalPop 0.2s ease-out;
}
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.md-paste-title {
    font-size: 15px;
    font-weight: 800;
    color: var(--text);
    margin: 0 0 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.md-paste-sub {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0 0 12px;
    line-height: 1.4;
}
.md-paste-textarea {
    width: 100%;
    height: 160px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 10px;
    font-family: monospace;
    font-size: 12.5px;
    color: var(--text);
    outline: none;
    resize: vertical;
    margin-bottom: 12px;
    box-sizing: border-box;
}
.md-paste-textarea:focus {
    border-color: var(--primary);
}
.md-paste-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
}
.btn-md-cancel {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text);
    font-size: 12.5px;
    font-weight: 700;
    padding: 8px 14px;
    border-radius: 10px;
    cursor: pointer;
}
.btn-md-apply {
    background: #059669;
    color: #fff;
    border: none;
    font-size: 12.5px;
    font-weight: 800;
    padding: 8px 16px;
    border-radius: 10px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
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
        <a href="/marketplace/item/<?= esc($listing['id']) ?>" style="display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:700;color:var(--text-muted);text-decoration:none;margin-bottom:8px;">
            ← Kembali ke Detail Iklan
        </a>
        <h2><span>✏️</span> Edit Iklan</h2>
        <p>Perbarui informasi barang, harga, foto, atau deskripsi iklan Anda.</p>
    </div>

    <form id="editListingForm" onsubmit="submitEditListing(event)" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- 1. TIPE TRANSAKSI -->
        <div class="form-card">
            <div class="form-section-title">1. Tipe Iklan / Penawaran</div>
            <div class="type-toggle-group">
                <div class="type-toggle-btn <?= $listing['type'] === 'sale' ? 'active sale' : '' ?>" id="btnTypeSale" onclick="selectType('sale')">
                    🏷️ Dijual
                </div>
                <div class="type-toggle-btn <?= $listing['type'] === 'rent' ? 'active rent' : '' ?>" id="btnTypeRent" onclick="selectType('rent')">
                    🔑 Disewakan
                </div>
                <div class="type-toggle-btn <?= $listing['type'] === 'service' ? 'active service' : '' ?>" id="btnTypeService" onclick="selectType('service')">
                    🛠️ Layanan Jasa
                </div>
            </div>
            <input type="hidden" name="type" id="listingType" value="<?= esc($listing['type']) ?>">

            <div class="form-group" id="rentPeriodGroup" style="<?= $listing['type'] === 'rent' ? 'display:block;' : 'display:none;' ?>">
                <label class="form-label">Periode Sewa <span class="req">*</span></label>
                <select name="rent_period" class="form-control">
                    <option value="hari" <?= ($listing['rent_period'] ?? '') === 'hari' ? 'selected' : '' ?>>Per Hari</option>
                    <option value="minggu" <?= ($listing['rent_period'] ?? '') === 'minggu' ? 'selected' : '' ?>>Per Minggu</option>
                    <option value="bulan" <?= ($listing['rent_period'] ?? '') === 'bulan' ? 'selected' : '' ?>>Per Bulan</option>
                    <option value="tahun" <?= ($listing['rent_period'] ?? '') === 'tahun' ? 'selected' : '' ?>>Per Tahun</option>
                </select>
            </div>
        </div>

        <!-- 2. FOTO PRODUK (MULTI-PHOTO) -->
        <div class="form-card">
            <div class="form-section-title" id="photoSectionTitle">2. Foto Produk / Dokumentasi Layanan</div>

            <!-- Existing Photos -->
            <?php if (!empty($images)): ?>
                <label class="form-label">Foto yang Sudah Ada:</label>
                <div class="photo-preview-grid" id="existingImagesGrid">
                    <?php foreach ($images as $img): ?>
                        <div class="photo-preview-item" id="img-box-<?= esc($img['id']) ?>">
                            <img src="<?= esc($img['image_url']) ?>" class="photo-preview-img" alt="Foto">
                            <button type="button" class="photo-preview-del" title="Hapus foto ini" onclick="deleteExistingPhoto(<?= esc($img['id']) ?>)">✕</button>
                            <?php if (!empty($img['is_primary'])): ?>
                                <div class="photo-preview-primary">UTAMA</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <label class="form-label">Tambah Foto Baru (Bisa Pilih Lebih Dari 1):</label>
            <div class="photo-upload-zone" onclick="document.getElementById('fileInput').click()">
                <div class="photo-upload-icon">📸</div>
                <div class="photo-upload-text">Klik di sini untuk menambah foto baru</div>
                <div class="photo-upload-hint">Format JPG, PNG, WEBP. Maks 5MB per foto.</div>
            </div>
            <input type="file" id="fileInput" name="images[]" multiple accept="image/*" style="display:none;" onchange="handleFiles(this.files)">

            <!-- New Photos Preview Grid -->
            <div class="photo-preview-grid" id="previewGrid"></div>
        </div>

        <!-- 3. INFORMASI PRODUK / JASA -->
        <div class="form-card">
            <div class="form-section-title" id="infoSectionTitle">3. <?= $listing['type'] === 'service' ? 'Informasi Layanan Jasa' : 'Informasi & Spesifikasi Barang' ?></div>

            <div class="form-group">
                <label class="form-label" id="titleLabel">Judul Iklan <span class="req">*</span></label>
                <input type="text" name="title" id="titleInput" class="form-control" placeholder="Contoh: Honda Vario 160cc 2023 Mulus Pajak Hidup" value="<?= esc($listing['title']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Kategori <span class="req">*</span></label>
                <input type="hidden" name="category" id="selectedCategory" value="<?= esc($listing['category']) ?>">
                <div class="category-select-grid" id="categoryGrid">
                    <?php 
                        $catsToShow = $listing['type'] === 'service' ? ($serviceCategories ?? $categories) : ($productCategories ?? $categories);
                        foreach ($catsToShow as $cat): 
                    ?>
                        <div class="cat-pill-btn <?= $listing['category'] === $cat ? 'active' : '' ?>" onclick="selectCategory('<?= esc($cat) ?>', this)">
                            <?= esc($cat) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group" id="conditionGroup" style="<?= $listing['type'] === 'service' ? 'display:none;' : 'display:block;' ?>">
                <label class="form-label">Kondisi Barang <span class="req">*</span></label>
                <select name="condition" id="conditionSelect" class="form-control">
                    <option value="used_good" <?= $listing['condition'] === 'used_good' ? 'selected' : '' ?>>Bekas - Sangat Baik / Terawat</option>
                    <option value="like_new" <?= $listing['condition'] === 'like_new' ? 'selected' : '' ?>>Bekas - Seperti Baru (Mulus 99%)</option>
                    <option value="used_fair" <?= $listing['condition'] === 'used_fair' ? 'selected' : '' ?>>Bekas - Normal Pemakaian / Wajar</option>
                    <option value="new" <?= $listing['condition'] === 'new' ? 'selected' : '' ?>>Baru (Segel / Belum Pernah Dipakai)</option>
                </select>
            </div>

            <!-- KHUSUS DETAIL LAYANAN JASA (DYNAMIC) -->
            <div id="serviceFieldsGroup" style="<?= $listing['type'] === 'service' ? 'display:block;' : 'display:none;' ?>background:rgba(37,99,235,0.04);border:1px dashed rgba(37,99,235,0.3);border-radius:16px;padding:16px;margin-bottom:16px;">
                <div style="font-size:12px;font-weight:800;color:#2563EB;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                    🛠️ Detail & Ketentuan Layanan Jasa
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Sistem / Tipe Layanan <span class="req">*</span></label>
                        <select name="service_type" id="serviceTypeSelect" class="form-control">
                            <option value="panggilan" <?= ($listing['service_type'] ?? '') === 'panggilan' ? 'selected' : '' ?>>🛵 Panggilan ke Tempat Konsumen</option>
                            <option value="di_tempat" <?= ($listing['service_type'] ?? '') === 'di_tempat' ? 'selected' : '' ?>>🏢 Datang ke Bengkel / Tempat Penyedia</option>
                            <option value="keduanya" <?= ($listing['service_type'] ?? '') === 'keduanya' ? 'selected' : '' ?>>🔄 Bisa Panggilan & Datang ke Tempat</option>
                            <option value="online" <?= ($listing['service_type'] ?? '') === 'online' ? 'selected' : '' ?>>💻 Layanan Online / Jarak Jauh</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Skema / Satuan Tarif <span class="req">*</span></label>
                        <select name="rate_unit" id="rateUnitSelect" class="form-control">
                            <option value="per_sesi" <?= ($listing['rate_unit'] ?? '') === 'per_sesi' ? 'selected' : '' ?>>Per Sesi (Pijat / Terapi)</option>
                            <option value="per_panggilan" <?= ($listing['rate_unit'] ?? '') === 'per_panggilan' ? 'selected' : '' ?>>Per Panggilan / Kunjungan</option>
                            <option value="per_jam" <?= ($listing['rate_unit'] ?? '') === 'per_jam' ? 'selected' : '' ?>>Per Jam</option>
                            <option value="per_unit" <?= ($listing['rate_unit'] ?? '') === 'per_unit' ? 'selected' : '' ?>>Per Unit / Titik (AC / Ban / Alat)</option>
                            <option value="per_pekerjaan" <?= ($listing['rate_unit'] ?? '') === 'per_pekerjaan' ? 'selected' : '' ?>>Per Pekerjaan / Borongan</option>
                            <option value="mulai_dari" <?= ($listing['rate_unit'] ?? '') === 'mulai_dari' ? 'selected' : '' ?>>Tarif Mulai Dari (Bisa Nego)</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Jangkauan Area Panggilan / Layanan <span class="req">*</span></label>
                        <input type="text" name="service_area" id="serviceAreaInput" class="form-control" placeholder="Contoh: Semarang Kota & Sekitarnya (radius 15 km)" value="<?= esc($listing['service_area'] ?? '') ?>">
                        <div style="font-size:11px;color:var(--text-muted);margin-top:3px;">Wilayah yang dapat Anda layani.</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jam Operasional / Ketersediaan <span class="req">*</span></label>
                        <input type="text" name="service_hours" id="serviceHoursInput" class="form-control" placeholder="Contoh: 24 Jam Siap Dipanggil / 08:00 - 21:00" value="<?= esc($listing['service_hours'] ?? '') ?>">
                        <div style="font-size:11px;color:var(--text-muted);margin-top:3px;">Kapan Anda siap menerima panggilan/pesanan.</div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Pengalaman, Keahlian & Garansi</label>
                    <input type="text" name="experience_years" class="form-control" placeholder="Contoh: Pengalaman 5+ tahun, bawa alat lengkap, bergaransi 30 hari" value="<?= esc($listing['experience_years'] ?? '') ?>">
                    <div style="font-size:11px;color:var(--text-muted);margin-top:3px;">Opsional tapi meningkatkan kepercayaan calon pelanggan.</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" id="priceLabel"><?= $listing['type'] === 'service' ? 'Tarif / Biaya Jasa' : 'Harga' ?> (<?= esc($symbol) ?>) <span class="req">*</span></label>
                <input type="number" name="price" id="priceInput" class="form-control" value="<?= (int)$listing['price'] ?>" min="1000" required>
            </div>

            <div class="form-group">
                <label class="form-label" id="locationLabel"><?= $listing['type'] === 'service' ? 'Kota / Lokasi Asal Penyedia Jasa' : 'Lokasi / Area COD' ?> <span class="req">*</span></label>
                <input type="text" name="location" id="locationInput" class="form-control" value="<?= esc($listing['location']) ?>" required>
            </div>

            <!-- WYSIWYG & Markdown Description Editor -->
            <div class="form-group">
                <div class="desc-editor-label-row">
                    <label class="form-label" style="margin-bottom:0;">Deskripsi Lengkap</label>
                    <div class="editor-view-switch">
                        <button type="button" class="btn-view-toggle active" id="btnViewWysiwyg" onclick="switchEditorMode('wysiwyg')">
                            👁️ Visual (WYSIWYG)
                        </button>
                        <button type="button" class="btn-view-toggle" id="btnViewMarkdown" onclick="switchEditorMode('markdown')">
                            📝 Markdown
                        </button>
                    </div>
                </div>

                <div class="wysiwyg-wrapper" id="wysiwygWrapper">
                    <div class="wysiwyg-toolbar" id="wysiwygToolbar">
                        <div class="toolbar-group">
                            <button type="button" class="tb-btn" onclick="execCmd('bold')" title="Tebal (Ctrl+B)"><b>B</b></button>
                            <button type="button" class="tb-btn" onclick="execCmd('italic')" title="Miring (Ctrl+I)"><i>I</i></button>
                            <button type="button" class="tb-btn" onclick="execCmd('strikeThrough')" title="Coret"><s>S</s></button>
                        </div>
                        <div class="toolbar-sep"></div>
                        <div class="toolbar-group">
                            <button type="button" class="tb-btn" onclick="execCmd('formatBlock', 'h2')" title="Heading 2">H2</button>
                            <button type="button" class="tb-btn" onclick="execCmd('formatBlock', 'h3')" title="Heading 3">H3</button>
                            <button type="button" class="tb-btn" onclick="execCmd('formatBlock', 'p')" title="Paragraf Normal">¶</button>
                        </div>
                        <div class="toolbar-sep"></div>
                        <div class="toolbar-group">
                            <button type="button" class="tb-btn" onclick="execCmd('insertUnorderedList')" title="Daftar Poin (Bullet)">• List</button>
                            <button type="button" class="tb-btn" onclick="execCmd('insertOrderedList')" title="Daftar Angka">1. List</button>
                            <button type="button" class="tb-btn" onclick="execCmd('formatBlock', 'blockquote')" title="Kutipan">“ Quote</button>
                        </div>
                        <div class="toolbar-sep"></div>
                        <div class="toolbar-group">
                            <button type="button" class="tb-btn" onclick="promptInsertLink()" title="Sisipkan Tautan">🔗</button>
                            <button type="button" class="tb-btn" onclick="execCmd('insertHorizontalRule')" title="Garis Pembatas">―</button>
                            <button type="button" class="tb-btn" onclick="execCmd('removeFormat')" title="Hapus Format">🧹</button>
                        </div>
                        <div class="toolbar-sep"></div>
                        <div class="toolbar-group" style="margin-left:auto;">
                            <button type="button" class="tb-btn tb-md-btn" onclick="openPasteMarkdownModal()" title="Tempel dan konversi teks Markdown">
                                📋 Paste Markdown
                            </button>
                        </div>
                    </div>

                    <!-- WYSIWYG Visual Canvas -->
                    <div id="wysiwygEditor" class="wysiwyg-content" contenteditable="true" spellcheck="false" data-placeholder="Tulis deskripsi atau paste teks Markdown..."><?= $listing['description'] ?? '' ?></div>

                    <!-- Markdown Source Textarea -->
                    <textarea id="markdownSourceEditor" class="markdown-source-area" style="display:none;" placeholder="Tulis atau paste teks berformat Markdown di sini..."></textarea>

                    <!-- Real form control that gets submitted -->
                    <textarea name="description" id="hiddenDescription" style="display:none;"></textarea>
                </div>

                <div class="editor-hint">
                    💡 <strong>Tips Markdown:</strong> Anda bisa langsung <code>Ctrl + V</code> (Paste) teks Markdown ke editor ini. Sistem otomatis mengonversinya ke format visual!
                </div>
            </div>
        </div>

        <!-- 4. KONTAK & PEMBAYARAN PIHAK KETIGA -->
        <div class="form-card">
            <div class="form-section-title">4. Kontak & Pembayaran Online Aman</div>

            <div class="form-group">
                <label class="form-label">Nomor WhatsApp untuk Dihubungi Pembeli <span class="req">*</span></label>
                <input type="tel" name="whatsapp" class="form-control" value="<?= esc($listing['whatsapp']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Link Shopee / Tokopedia (Opsional - Sangat Dianjurkan)</label>
                <input type="url" name="third_party_url" class="form-control" value="<?= esc($listing['third_party_url'] ?? '') ?>" placeholder="https://shopee.co.id/... atau https://tokopedia.com/...">
            </div>

            <button type="submit" class="btn-submit-listing" id="btnSubmit">
                💾 Simpan Perubahan Iklan
            </button>
        </div>

    </form>

    <!-- Paste Markdown Modal -->
    <div id="pasteMarkdownModal" class="md-paste-overlay" onclick="if(event.target===this) closePasteMarkdownModal()">
        <div class="md-paste-card">
            <div class="md-paste-title">📋 Tempel Format Markdown</div>
            <div class="md-paste-sub">Tempel teks Markdown Anda di bawah ini. Teks akan langsung dikonversi ke format visual.</div>
            <textarea id="modalMarkdownInput" class="md-paste-textarea" placeholder="Contoh:&#10;## Spesifikasi&#10;- Mulus 99%&#10;- Garansi **aktif**"></textarea>
            <div class="md-paste-actions">
                <button type="button" class="btn-md-cancel" onclick="closePasteMarkdownModal()">Batal</button>
                <button type="button" class="btn-md-apply" onclick="applyPastedMarkdown()">
                    ✨ Konversi ke Editor
                </button>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
const listingId = <?= (int)$listing['id'] ?>;
let selectedFiles = [];
let currentEditorMode = 'wysiwyg';

// WYSIWYG Editor Commands
function execCmd(cmd, val = null) {
    const editor = document.getElementById('wysiwygEditor');
    if (!editor) return;
    editor.focus();
    document.execCommand(cmd, false, val);
    syncDescription();
}

function promptInsertLink() {
    const url = prompt('Masukkan URL tautan (contoh: https://shopee.co.id/...):');
    if (url) {
        execCmd('createLink', url);
    }
}

// Markdown Detection & Converter
function isMarkdown(text) {
    if (!text || typeof text !== 'string') return false;
    return /(?:^#{1,6}\s+|^\s*[-*+]\s+|^\s*\d+\.\s+|^\s*>\s+|\*\*[^*]+\*\*|__[^_]+__|\*[^*]+\*|~~[^~]+~~|\[.+?\]\(.+?\)|```)/m.test(text);
}

function escapeHtml(str) {
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function convertMarkdownToHtml(md) {
    if (!md) return '';
    if (window.marked && typeof window.marked.parse === 'function') {
        try {
            return window.marked.parse(md, { breaks: true, gfm: true });
        } catch(e) {
            console.warn('marked fallback:', e);
        }
    }

    let html = md
        .replace(/```([a-z0-9_-]*)\n([\s\S]*?)```/g, (m, lang, code) => `<pre><code>${escapeHtml(code.trim())}</code></pre>`)
        .replace(/`([^`]+)`/g, (m, code) => `<code>${escapeHtml(code)}</code>`)
        .replace(/^### (.*$)/gim, '<h3>$1</h3>')
        .replace(/^## (.*$)/gim, '<h2>$1</h2>')
        .replace(/^# (.*$)/gim, '<h1>$1</h1>')
        .replace(/^\> (.*$)/gim, '<blockquote>$1</blockquote>')
        .replace(/\*\*\*(.*?)\*\*\*/g, '<strong><em>$1</em></strong>')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/__(.*?)__/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/_(.*?)_/g, '<em>$1</em>')
        .replace(/~~(.*?)~~/g, '<del>$1</del>')
        .replace(/^(?:---|\*\*\*|___)\s*$/gim, '<hr>')
        .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');

    html = html.replace(/^\s*[-*+]\s+(.*)$/gim, '<li>$1</li>');
    html = html.replace(/(<li>.*<\/li>(\n|$))+/g, '<ul>$&</ul>');
    html = html.replace(/^\s*(\d+)\.\s+(.*)$/gim, '<li>$2</li>');

    const paras = html.split(/\n{2,}/);
    html = paras.map(p => {
        p = p.trim();
        if (!p) return '';
        if (/^<(h[1-6]|ul|ol|pre|blockquote|hr)/i.test(p)) return p;
        return `<p>${p.replace(/\n/g, '<br>')}</p>`;
    }).join('');

    return html;
}

function convertHtmlToMarkdown(html) {
    if (!html) return '';
    const temp = document.createElement('div');
    temp.innerHTML = html;

    function nodeToMd(node) {
        if (node.nodeType === Node.TEXT_NODE) return node.textContent;
        if (node.nodeType !== Node.ELEMENT_NODE) return '';

        const tag = node.tagName.toLowerCase();
        let children = Array.from(node.childNodes).map(nodeToMd).join('');

        switch (tag) {
            case 'h1': return '# ' + children.trim() + '\n\n';
            case 'h2': return '## ' + children.trim() + '\n\n';
            case 'h3': return '### ' + children.trim() + '\n\n';
            case 'strong':
            case 'b': return '**' + children + '**';
            case 'em':
            case 'i': return '*' + children + '*';
            case 'del':
            case 's': return '~~' + children + '~~';
            case 'blockquote': return '> ' + children.trim().replace(/\n/g, '\n> ') + '\n\n';
            case 'code':
                if (node.parentElement && node.parentElement.tagName.toLowerCase() === 'pre') return children;
                return '`' + children + '`';
            case 'pre': return '```\n' + children.trim() + '\n```\n\n';
            case 'ul': return children.trim() + '\n\n';
            case 'ol': {
                let idx = 1;
                let out = '';
                node.querySelectorAll(':scope > li').forEach(li => {
                    out += (idx++) + '. ' + Array.from(li.childNodes).map(nodeToMd).join('').trim() + '\n';
                });
                return out + '\n';
            }
            case 'li': return '- ' + children.trim() + '\n';
            case 'hr': return '---\n\n';
            case 'br': return '\n';
            case 'p': return children.trim() + '\n\n';
            case 'a': return `[${children}](${node.getAttribute('href') || ''})`;
            default: return children;
        }
    }

    return Array.from(temp.childNodes).map(nodeToMd).join('').replace(/\n{3,}/g, '\n\n').trim();
}

function syncDescription() {
    const editor = document.getElementById('wysiwygEditor');
    const mdSource = document.getElementById('markdownSourceEditor');
    const hidden = document.getElementById('hiddenDescription');
    if (!editor || !hidden) return;

    if (currentEditorMode === 'wysiwyg') {
        hidden.value = editor.innerHTML.trim();
    } else if (mdSource) {
        hidden.value = convertMarkdownToHtml(mdSource.value.trim());
    }
}

function switchEditorMode(mode) {
    if (mode === currentEditorMode) return;
    const wysiwygDiv = document.getElementById('wysiwygEditor');
    const mdSource   = document.getElementById('markdownSourceEditor');
    const toolbar    = document.getElementById('wysiwygToolbar');
    const btnW       = document.getElementById('btnViewWysiwyg');
    const btnM       = document.getElementById('btnViewMarkdown');

    if (mode === 'markdown') {
        mdSource.value = convertHtmlToMarkdown(wysiwygDiv.innerHTML);
        wysiwygDiv.style.display = 'none';
        toolbar.style.opacity = '0.35';
        toolbar.style.pointerEvents = 'none';
        mdSource.style.display = 'block';
        btnW.classList.remove('active');
        btnM.classList.add('active');
        currentEditorMode = 'markdown';
        mdSource.focus();
    } else {
        wysiwygDiv.innerHTML = convertMarkdownToHtml(mdSource.value);
        mdSource.style.display = 'none';
        toolbar.style.opacity = '1';
        toolbar.style.pointerEvents = 'auto';
        wysiwygDiv.style.display = 'block';
        btnM.classList.remove('active');
        btnW.classList.add('active');
        currentEditorMode = 'wysiwyg';
        wysiwygDiv.focus();
    }
    syncDescription();
}

function openPasteMarkdownModal() {
    document.getElementById('pasteMarkdownModal').classList.add('show');
    const input = document.getElementById('modalMarkdownInput');
    input.value = '';
    setTimeout(() => input.focus(), 120);
}

function closePasteMarkdownModal() {
    document.getElementById('pasteMarkdownModal').classList.remove('show');
}

function applyPastedMarkdown() {
    const input = document.getElementById('modalMarkdownInput');
    const md = input.value.trim();
    if (!md) {
        closePasteMarkdownModal();
        return;
    }

    const html = convertMarkdownToHtml(md);
    if (currentEditorMode === 'markdown') {
        const source = document.getElementById('markdownSourceEditor');
        source.value = (source.value ? source.value + '\n\n' : '') + md;
    } else {
        const editor = document.getElementById('wysiwygEditor');
        editor.focus();
        document.execCommand('insertHTML', false, html);
    }
    syncDescription();
    closePasteMarkdownModal();
}

// Delete existing image via AJAX
function deleteExistingPhoto(imageId) {
    if (!confirm('Hapus foto ini dari iklan?')) return;

    const fd = new URLSearchParams();
    if (window.DUITKU && window.DUITKU.csrfName && window.DUITKU.csrfToken) {
        fd.append(window.DUITKU.csrfName, window.DUITKU.csrfToken);
    }

    fetch('/marketplace/image/delete/' + imageId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': (window.DUITKU && window.DUITKU.csrfToken) ? window.DUITKU.csrfToken : ''
        },
        body: fd.toString()
    })
    .then(async r => {
        const text = await r.text();
        return JSON.parse(text);
    })
    .then(res => {
        if (res.success) {
            document.getElementById('img-box-' + imageId)?.remove();
        } else {
            alert(res.message || 'Gagal menghapus gambar.');
        }
    })
    .catch(err => {
        alert('Gagal menghapus foto: ' + (err.message || 'Kesalahan jaringan.'));
    });
}

const productCategories = <?= json_encode($productCategories ?? []) ?>;
const serviceCategories = <?= json_encode($serviceCategories ?? []) ?>;

function renderCategoryPills(cats) {
    const grid = document.getElementById('categoryGrid');
    const hidden = document.getElementById('selectedCategory');
    if (!grid || !hidden || !cats) return;
    const current = hidden.value;
    grid.innerHTML = '';
    let found = false;
    cats.forEach(cat => {
        const div = document.createElement('div');
        div.className = 'cat-pill-btn' + (cat === current ? ' active' : '');
        div.textContent = cat;
        div.onclick = function() { selectCategory(cat, div); };
        grid.appendChild(div);
        if (cat === current) found = true;
    });
    if (!found && cats.length > 0) {
        hidden.value = cats[0];
        grid.firstElementChild?.classList.add('active');
    }
}

function selectType(type) {
    document.getElementById('listingType').value = type;
    const btnSale = document.getElementById('btnTypeSale');
    const btnRent = document.getElementById('btnTypeRent');
    const btnService = document.getElementById('btnTypeService');
    const rentGroup = document.getElementById('rentPeriodGroup');
    const conditionGroup = document.getElementById('conditionGroup');
    const serviceGroup = document.getElementById('serviceFieldsGroup');
    const priceLabel = document.getElementById('priceLabel');
    const locationLabel = document.getElementById('locationLabel');
    const infoTitle = document.getElementById('infoSectionTitle');

    btnSale.classList.remove('active', 'sale');
    btnRent.classList.remove('active', 'rent');
    btnService.classList.remove('active', 'service');

    if (type === 'service') {
        btnService.classList.add('active', 'service');
        rentGroup.style.display = 'none';
        conditionGroup.style.display = 'none';
        serviceGroup.style.display = 'block';
        infoTitle.textContent = '3. Informasi Layanan Jasa';
        priceLabel.innerHTML = 'Tarif / Biaya Jasa (<?= esc($symbol) ?>) <span class="req">*</span>';
        locationLabel.innerHTML = 'Kota / Lokasi Asal Penyedia Jasa <span class="req">*</span>';
        renderCategoryPills(serviceCategories);
    } else if (type === 'rent') {
        btnRent.classList.add('active', 'rent');
        rentGroup.style.display = 'block';
        conditionGroup.style.display = 'block';
        serviceGroup.style.display = 'none';
        infoTitle.textContent = '3. Informasi & Spesifikasi Sewa';
        priceLabel.innerHTML = 'Harga Sewa (<?= esc($symbol) ?>) <span class="req">*</span>';
        locationLabel.innerHTML = 'Lokasi / Area COD <span class="req">*</span>';
        renderCategoryPills(productCategories);
    } else {
        btnSale.classList.add('active', 'sale');
        rentGroup.style.display = 'none';
        conditionGroup.style.display = 'block';
        serviceGroup.style.display = 'none';
        infoTitle.textContent = '3. Informasi & Spesifikasi Barang';
        priceLabel.innerHTML = 'Harga (<?= esc($symbol) ?>) <span class="req">*</span>';
        locationLabel.innerHTML = 'Lokasi / Area COD <span class="req">*</span>';
        renderCategoryPills(productCategories);
    }
}

function selectCategory(cat, elem) {
    document.getElementById('selectedCategory').value = cat;
    document.querySelectorAll('.cat-pill-btn').forEach(btn => btn.classList.remove('active'));
    elem.classList.add('active');
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

function submitEditListing(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.innerText = 'Sedang Menyimpan Perubahan...';

    syncDescription();

    const form = document.getElementById('editListingForm');
    const formData = new FormData(form);

    const descVal = document.getElementById('hiddenDescription').value;
    formData.set('description', descVal);

    // Remove default input files and append accumulated selectedFiles
    formData.delete('images[]');
    selectedFiles.forEach(file => {
        formData.append('images[]', file);
    });

    if (window.DUITKU && window.DUITKU.csrfName && window.DUITKU.csrfToken) {
        formData.set(window.DUITKU.csrfName, window.DUITKU.csrfToken);
    }

    const headers = { 'X-Requested-With': 'XMLHttpRequest' };
    if (window.DUITKU && window.DUITKU.csrfToken) {
        headers['X-CSRF-TOKEN'] = window.DUITKU.csrfToken;
    }

    fetch('/marketplace/update/' + listingId, {
        method: 'POST',
        headers: headers,
        body: formData
    })
    .then(async r => {
        const text = await r.text();
        let res;
        try {
            res = JSON.parse(text);
        } catch (jsonErr) {
            throw new Error('Respon server (' + r.status + '): ' + text.substring(0, 100));
        }
        return res;
    })
    .then(res => {
        if (res.success) {
            alert(res.message || 'Iklan berhasil diperbarui!');
            window.location.href = res.redirect || ('/marketplace/item/' + listingId);
        } else {
            alert(res.message || 'Gagal memperbarui iklan.');
            btn.disabled = false;
            btn.innerText = '💾 Simpan Perubahan Iklan';
        }
    })
    .catch(err => {
        console.error('Submit edit error:', err);
        alert('Gagal memperbarui iklan: ' + (err.message || 'Terjadi gangguan jaringan atau server.'));
        btn.disabled = false;
        btn.innerText = '💾 Simpan Perubahan Iklan';
    });
}

// Attach paste & input listeners
document.addEventListener('DOMContentLoaded', function() {
    const editor = document.getElementById('wysiwygEditor');
    const mdSource = document.getElementById('markdownSourceEditor');

    if (editor) {
        editor.addEventListener('input', syncDescription);
        syncDescription();

        editor.addEventListener('paste', function(e) {
            const clipboardData = e.clipboardData || window.clipboardData;
            if (!clipboardData) return;
            const plainText = clipboardData.getData('text/plain');
            if (!plainText) return;

            if (isMarkdown(plainText)) {
                e.preventDefault();
                const html = convertMarkdownToHtml(plainText);
                document.execCommand('insertHTML', false, html);
                syncDescription();
            }
        });
    }

    if (mdSource) {
        mdSource.addEventListener('input', syncDescription);
    }
});
</script>
<?= $this->endSection() ?>
