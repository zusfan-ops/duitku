<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.barang-page {
    padding: 12px 16px 110px;
}

.barang-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.barang-title {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-primary);
}

.btn-add-barang {
    background: #4F46E5;
    color: #fff;
    font-size: 12.5px;
    font-weight: 700;
    padding: 8px 14px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    transition: transform 0.15s ease;
}
.btn-add-barang:active {
    transform: scale(0.96);
}

.barang-search-bar {
    position: relative;
    margin-bottom: 14px;
}
.barang-search-input {
    width: 100%;
    padding: 12px 14px 12px 38px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    font-size: 13.5px;
    color: var(--text-primary);
    box-shadow: var(--shadow-sm);
    outline: none;
    transition: border-color 0.2s ease;
}
.barang-search-input:focus {
    border-color: #4F46E5;
}
.barang-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
}

.barang-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.barang-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 12px 14px;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: transform 0.15s ease;
}
.barang-card:active {
    transform: scale(0.98);
}

.barang-thumb {
    width: 54px;
    height: 54px;
    border-radius: 14px;
    background: #EEF2FF;
    color: #4F46E5;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid var(--border);
}
.barang-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.barang-thumb svg {
    width: 24px;
    height: 24px;
}

.barang-info {
    flex: 1;
    min-width: 0;
}
.barang-name {
    font-size: 14px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.barang-location-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #F1F5F9;
    color: var(--text-secondary);
    font-size: 11.5px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 6px;
}

.barang-arrow {
    color: var(--text-muted);
}

.barang-empty {
    text-align: center;
    padding: 40px 20px;
    background: var(--bg-card);
    border: 1px dashed var(--border);
    border-radius: 18px;
    color: var(--text-muted);
}
.barang-empty-icon {
    font-size: 44px;
    margin-bottom: 8px;
}

/* Modal Form Styles */
.photo-uploader-box {
    border: 1.5px dashed var(--border);
    border-radius: 14px;
    padding: 12px;
    text-align: center;
    background: var(--bg);
    cursor: pointer;
    position: relative;
    overflow: hidden;
}
.photo-preview-img {
    max-height: 120px;
    border-radius: 10px;
    margin: 0 auto;
    display: block;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="barang-page">

    <div class="barang-header">
        <div class="barang-title">Manajemen Barang</div>
        <button class="btn-add-barang" id="btnOpenBarangModal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Barang
        </button>
    </div>

    <!-- Search Bar -->
    <div class="barang-search-bar">
        <svg class="barang-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" id="barangSearchInput" class="barang-search-input" placeholder="Cari nama atau lokasi barang..." value="<?= esc($search ?? '') ?>">
    </div>

    <!-- Barang Items List -->
    <?php if (empty($items)): ?>
        <div class="barang-empty">
            <div class="barang-empty-icon">📦</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:4px;">Tidak Ada Barang Ditemukan</div>
            <div style="font-size:12px;">Catat barang berharga atau barang yang jarang dipakai lengkap dengan lokasi dan foto agar tidak lupa.</div>
        </div>
    <?php else: ?>
        <div class="barang-list" id="barangListContainer">
            <?php foreach ($items as $item): ?>
                <?php
                    $itemImg = $item['item_photo'] ?? $item['itemPhoto'] ?? null;
                    $locImg  = $item['location_photo'] ?? $item['locationPhoto'] ?? null;
                ?>
                <div class="barang-card" onclick="editBarang(<?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') ?>)">
                    <div class="barang-thumb">
                        <?php if ($itemImg): ?>
                            <img src="<?= esc($itemImg) ?>" alt="<?= esc($item['name']) ?>">
                        <?php else: ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                                <line x1="12" y1="22.08" x2="12" y2="12"/>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="barang-info">
                        <div class="barang-name"><?= esc($item['name']) ?></div>
                        <div class="barang-location-badge">
                            📍 <?= esc($item['location']) ?>
                        </div>
                    </div>
                    <div class="barang-arrow">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- ════════════════════════════════════════════════════ BARANG MODAL -->
<div class="modal-overlay" id="barangModalOverlay">
    <div class="modal-sheet" id="barangModal">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3 id="barangModalTitle">Simpan Barang Baru</h3>
            <button class="modal-close" id="barangModalClose">✕</button>
        </div>

        <form id="barangForm" autocomplete="off" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:12px;">
            <input type="hidden" id="barangId" name="id">

            <div class="form-group">
                <label class="form-label" for="barangName">NAMA BARANG</label>
                <input type="text" id="barangName" name="name" placeholder="contoh: Paspor, Kamera Sony, Surat Berharga" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="barangLocation">LOKASI PENYIMPANAN</label>
                <input type="text" id="barangLocation" name="location" placeholder="contoh: Lemari Kamar Utama, Rak 3" class="form-input" required>
            </div>

            <!-- Foto Barang -->
            <div class="form-group">
                <label class="form-label">FOTO BARANG (OPSIONAL)</label>
                <input type="file" id="itemPhotoFile" name="item_photo" accept="image/*" style="display:none;">
                <div class="photo-uploader-box" id="itemPhotoBox" onclick="document.getElementById('itemPhotoFile').click()">
                    <img id="itemPhotoPreview" src="" alt="Preview" class="photo-preview-img" style="display:none;">
                    <div id="itemPhotoPlaceholder" style="font-size:12.5px; color:var(--text-secondary);">
                        📷 Klik untuk ambil/upload foto barang
                    </div>
                </div>
            </div>

            <!-- Foto Lokasi -->
            <div class="form-group">
                <label class="form-label">FOTO LOKASI / RAK (OPSIONAL)</label>
                <input type="file" id="locPhotoFile" name="location_photo" accept="image/*" style="display:none;">
                <div class="photo-uploader-box" id="locPhotoBox" onclick="document.getElementById('locPhotoFile').click()">
                    <img id="locPhotoPreview" src="" alt="Preview" class="photo-preview-img" style="display:none;">
                    <div id="locPhotoPlaceholder" style="font-size:12.5px; color:var(--text-secondary);">
                        📍 Klik untuk ambil/upload foto tempat penyimpanan
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:8px;">
                <button type="button" id="btnDeleteBarang" class="btn-cancel" style="display:none; color:var(--expense); border:1px solid var(--border); padding:10px 14px; border-radius:12px; font-weight:700;">Hapus</button>
                <button type="submit" class="btn-save" style="background:#4F46E5; flex:1;">Simpan Data Barang</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const overlay    = document.getElementById('barangModalOverlay');
    const modalClose = document.getElementById('barangModalClose');
    const btnOpen    = document.getElementById('btnOpenBarangModal');
    const barangForm = document.getElementById('barangForm');
    const searchInput = document.getElementById('barangSearchInput');

    const itemFile   = document.getElementById('itemPhotoFile');
    const itemPrev   = document.getElementById('itemPhotoPreview');
    const itemHold   = document.getElementById('itemPhotoPlaceholder');

    const locFile    = document.getElementById('locPhotoFile');
    const locPrev    = document.getElementById('locPhotoPreview');
    const locHold    = document.getElementById('locPhotoPlaceholder');

    const btnDel     = document.getElementById('btnDeleteBarang');

    // Live search filter
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const q = encodeURIComponent(searchInput.value.trim());
                window.location.href = `/barang${q ? '?q=' + q : ''}`;
            }, 500);
        });
    }

    // Photo preview handlers
    itemFile.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const url = URL.createObjectURL(file);
            itemPrev.src = url;
            itemPrev.style.display = 'block';
            itemHold.style.display = 'none';
        }
    });

    locFile.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const url = URL.createObjectURL(file);
            locPrev.src = url;
            locPrev.style.display = 'block';
            locHold.style.display = 'none';
        }
    });

    function openModal(isEdit = false) {
        document.getElementById('barangModalTitle').textContent = isEdit ? 'Edit Data Barang' : 'Simpan Barang Baru';
        btnDel.style.display = isEdit ? 'block' : 'none';
        overlay.classList.add('open');
    }
    function closeModal() {
        overlay.classList.remove('open');
        barangForm.reset();
        document.getElementById('barangId').value = '';
        itemPrev.src = '';
        itemPrev.style.display = 'none';
        itemHold.style.display = 'block';
        locPrev.src = '';
        locPrev.style.display = 'none';
        locHold.style.display = 'block';
    }

    if (btnOpen) btnOpen.addEventListener('click', () => openModal(false));
    if (modalClose) modalClose.addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal();
    });

    window.editBarang = function(b) {
        document.getElementById('barangId').value = b.id || '';
        document.getElementById('barangName').value = b.name || '';
        document.getElementById('barangLocation').value = b.location || '';

        const itemPhoto = b.item_photo || b.itemPhoto;
        if (itemPhoto) {
            itemPrev.src = itemPhoto;
            itemPrev.style.display = 'block';
            itemHold.style.display = 'none';
        } else {
            itemPrev.style.display = 'none';
            itemHold.style.display = 'block';
        }

        const locPhoto = b.location_photo || b.locationPhoto;
        if (locPhoto) {
            locPrev.src = locPhoto;
            locPrev.style.display = 'block';
            locHold.style.display = 'none';
        } else {
            locPrev.style.display = 'none';
            locHold.style.display = 'block';
        }

        openModal(true);
    };

    btnDel.addEventListener('click', async () => {
        const id = document.getElementById('barangId').value;
        const name = document.getElementById('barangName').value;
        if (!confirm(`Hapus barang "${name}" dari inventaris?`)) return;

        try {
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            const res = await fetch(`/barang/delete/${id}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Gagal menghapus.');
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan.');
        }
    });

    barangForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(barangForm);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        try {
            const res = await fetch('/barang/store', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Gagal menyimpan barang.');
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan.');
        }
    });
});
</script>
<?= $this->endSection() ?>
