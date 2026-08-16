<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.ocr-container {
    padding: 6px 0 100px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.ocr-top-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 4px;
}
.ocr-back-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 700;
    color: var(--text-secondary);
    padding: 6px 12px;
    border-radius: 12px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    transition: all var(--transition);
}
.ocr-back-btn:hover {
    color: var(--primary);
    border-color: var(--primary);
}

.ocr-dropzone {
    background: var(--bg-card);
    border: 2px dashed var(--primary);
    border-radius: 20px;
    padding: 28px 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: all 0.2s ease;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
}
.ocr-dropzone.dragover {
    border-color: var(--primary-light);
    background: var(--primary-dim);
}
.ocr-dropzone.has-photo {
    padding: 0;
    border-style: solid;
    border-color: var(--border);
    background: #0F172A;
    min-height: 200px;
    max-height: 320px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ocr-preview-photo {
    width: 100%;
    max-height: 320px;
    object-fit: contain;
    display: block;
}

.ocr-pulse-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--primary-dim);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    transition: transform 0.2s ease;
}
.ocr-dropzone:hover .ocr-pulse-icon {
    transform: scale(1.08);
}

.ocr-laser-beam {
    position: absolute;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, #10B981, #34D399, #10B981, transparent);
    box-shadow: 0 0 16px 4px rgba(16, 185, 129, 0.9);
    z-index: 10;
    top: 0;
    animation: ocrLaserMove 2s ease-in-out infinite alternate;
}

@keyframes ocrLaserMove {
    0%   { top: 2%; opacity: 0.8; }
    50%  { opacity: 1; }
    100% { top: 96%; opacity: 0.8; }
}

.ocr-scanning-layer {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.78);
    backdrop-filter: blur(4px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    z-index: 15;
    color: #ffffff;
}

.ocr-btn-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.ocr-input-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px 14px;
    border-radius: 14px;
    font-size: 13.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all var(--transition);
}
.ocr-input-btn.primary {
    background: linear-gradient(135deg, #059669 0%, #10B981 100%);
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(5, 150, 105, 0.35);
}
.ocr-input-btn.primary:active {
    transform: scale(0.97);
}
.ocr-input-btn.secondary {
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    color: var(--text-primary);
}
.ocr-input-btn.secondary:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.ocr-extracted-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 18px 20px;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.ocr-extracted-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}

.ocr-status-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.ocr-status-chip.success {
    background: var(--income-bg);
    color: var(--income);
}
.ocr-status-chip.waiting {
    background: var(--bg);
    color: var(--text-muted);
}

.raw-text-box {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 12px;
    font-family: monospace;
    font-size: 11.5px;
    color: var(--text-secondary);
    max-height: 120px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-break: break-word;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="ocr-container">

    <!-- Top Navigation Header -->
    <div class="ocr-top-header">
        <a href="/features" class="ocr-back-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <span>Kembali</span>
        </a>
        <div style="font-size:15px;font-weight:800;color:var(--text-primary)">
            📷 Smart Scan Struk (OCR)
        </div>
        <div style="width:70px"></div>
    </div>

    <!-- Hidden File Inputs -->
    <input type="file" id="ocrCameraInput" accept="image/*" capture="environment" style="display:none">
    <input type="file" id="ocrGalleryInput" accept="image/*" style="display:none">

    <!-- Receipt Scan / Photo Preview Area -->
    <div class="ocr-dropzone" id="ocrDropzone">
        <!-- Laser scanner beam (visible when scanning) -->
        <div class="ocr-laser-beam" id="ocrLaserBeam" style="display:none"></div>

        <!-- Scanning Loader Overlay -->
        <div class="ocr-scanning-layer" id="ocrScanningLayer" style="display:none">
            <div class="ocr-spinner"></div>
            <div style="font-weight:800;font-size:14px" id="ocrStatusText">Membaca struk belanja...</div>
            <div style="font-size:11px;color:rgba(255,255,255,0.7)" id="ocrSubStatusText">Mendeteksi nominal dan merchant</div>
        </div>

        <!-- Default Empty State -->
        <div id="ocrEmptyState">
            <div class="ocr-pulse-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="28" height="28">
                    <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/>
                    <line x1="8" y1="8" x2="16" y2="8"/>
                    <line x1="8" y1="12" x2="16" y2="12"/>
                    <line x1="8" y1="16" x2="12" y2="16"/>
                </svg>
            </div>
            <div style="font-size:15px;font-weight:800;color:var(--text-primary);margin-bottom:4px">
                Foto Struk atau Nota Belanja
            </div>
            <div style="font-size:12px;color:var(--text-muted);max-width:280px;margin:0 auto">
                Mendukung struk Indomaret, Alfamart, SPBU, resto, cafe, atau nota belanja toko lainnya.
            </div>
        </div>

        <!-- Image Preview (when photo selected) -->
        <div id="ocrPreviewContainer" style="display:none;position:relative;width:100%">
            <img id="ocrPreviewImage" class="ocr-preview-photo" alt="Preview Struk">
            <button id="ocrBtnRemovePhoto" style="position:absolute;top:10px;right:10px;background:#DC2626;color:#fff;border-radius:50%;width:30px;height:30px;font-size:14px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 10px rgba(0,0,0,0.3);z-index:20;cursor:pointer">✕</button>
        </div>
    </div>

    <!-- Capture Buttons -->
    <div class="ocr-btn-grid">
        <button type="button" class="ocr-input-btn primary" id="ocrBtnCamera">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="18" height="18">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                <circle cx="12" cy="13" r="4"/>
            </svg>
            <span>Buka Kamera</span>
        </button>
        <button type="button" class="ocr-input-btn secondary" id="ocrBtnGallery">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="18" height="18">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
            </svg>
            <span>Pilih Galeri</span>
        </button>
    </div>

    <!-- Paste Text Shortcut -->
    <button type="button" id="ocrBtnPasteText" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;background:var(--bg-card);border:1.5px solid var(--border);border-radius:14px;font-size:12.5px;font-weight:700;color:var(--text-secondary);cursor:pointer;transition:all var(--transition)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
        </svg>
        <span>Tempel Teks Struk / SMS Perbankan</span>
    </button>

    <!-- Extracted Result Form Card -->
    <div class="ocr-extracted-card">
        <div class="ocr-extracted-header">
            <div style="display:flex;align-items:center;gap:8px">
                <div style="font-size:16px">🧾</div>
                <div style="font-size:14px;font-weight:800;color:var(--text-primary)">Hasil Deteksi Transaksi</div>
            </div>
            <span class="ocr-status-chip waiting" id="ocrResultBadge">Belum Ada Data</span>
        </div>

        <form id="ocrForm">
            <input type="hidden" id="ocrExistingImage" name="existing_image">

            <!-- Nominal Amount -->
            <div class="form-group">
                <label class="form-label">TOTAL NOMINAL BELANJA</label>
                <div class="amount-input-wrap" style="margin-bottom:0">
                    <span class="amount-currency"><?= esc($symbol ?? 'Rp') ?></span>
                    <input type="text" id="ocrAmount" name="amount" placeholder="0" class="amount-input" inputmode="numeric" required>
                </div>
            </div>

            <!-- Merchant / Store Name -->
            <div class="form-group">
                <label class="form-label">TOKO / MERCHANT / KETERANGAN</label>
                <input type="text" id="ocrMerchant" name="note" placeholder="Contoh: Indomaret, Alfamart, SPBU..." class="form-input" required>
            </div>

            <!-- Date -->
            <div class="form-group">
                <label class="form-label">TANGGAL TRANSAKSI</label>
                <input type="date" id="ocrDate" name="date" class="form-input" value="<?= date('Y-m-d') ?>" required>
            </div>

            <!-- Wallet -->
            <div class="form-group">
                <label class="form-label">DIBAYAR DARI REKENING</label>
                <select id="ocrWallet" name="wallet_id" class="form-input">
                    <?php foreach (($wallets ?? []) as $w): ?>
                        <option value="<?= $w['id'] ?>" <?= !empty($w['is_default']) ? 'selected' : '' ?>>
                            <?= $w['icon'] ?? '💳' ?> <?= esc($w['name']) ?> — <?= esc($symbol ?? 'Rp') ?> <?= number_format($w['balance'] ?? 0, 0, ',', '.') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Category Suggestion -->
            <div class="form-group">
                <label class="form-label">KATEGORI PENGELUARAN</label>
                <select id="ocrCategory" name="category_id" class="form-input">
                    <option value="">— Pilih Kategori —</option>
                    <?php foreach (($categories ?? []) as $c): ?>
                        <?php if ($c['type'] === 'expense'): ?>
                            <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Toggle Raw OCR Text -->
            <div style="margin-top:4px">
                <button type="button" id="btnToggleRawOcr" style="font-size:12px;font-weight:700;color:var(--primary);background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:4px">
                    <span>Lihat Teks Mentah OCR</span>
                    <span id="rawOcrArrow">▼</span>
                </button>
                <div id="rawOcrContainer" style="display:none;margin-top:8px">
                    <div class="raw-text-box" id="rawOcrText">Belum ada teks yang dipindai.</div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div style="display:flex;flex-direction:column;gap:10px;margin-top:16px">
                <button type="submit" class="btn-save" id="btnSaveOcr" style="margin-top:0">
                    💾 Simpan Pengeluaran Ini
                </button>
            </div>
        </form>
    </div>

</div>

<!-- Paste Text Modal Dialog -->
<div class="mini-modal-overlay" id="pasteModalOverlay">
    <div class="mini-modal">
        <h3>📋 Tempel Teks Struk / Nota</h3>
        <p style="font-size:12px;color:var(--text-muted);margin-bottom:12px">
            Tempel salinan teks dari SMS banking, chat nota, atau invoice belanja:
        </p>
        <textarea id="pasteTextarea" rows="5" class="form-input" placeholder="Contoh: TOTAL Rp 85.000 di Alfamart..."></textarea>
        <div class="mini-modal-footer">
            <button type="button" class="btn-cancel-small" id="btnCancelPaste">Batal</button>
            <button type="button" class="btn-save-small" id="btnSubmitPaste">Ekstrak Data</button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Tesseract.js for Browser-Side Receipt Recognition -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dropzone        = document.getElementById('ocrDropzone');
    const cameraInput     = document.getElementById('ocrCameraInput');
    const galleryInput    = document.getElementById('ocrGalleryInput');
    const btnCamera       = document.getElementById('ocrBtnCamera');
    const btnGallery      = document.getElementById('ocrBtnGallery');
    const previewContainer= document.getElementById('ocrPreviewContainer');
    const emptyState      = document.getElementById('ocrEmptyState');
    const previewImage    = document.getElementById('ocrPreviewImage');
    const btnRemovePhoto  = document.getElementById('ocrBtnRemovePhoto');
    const laserBeam       = document.getElementById('ocrLaserBeam');
    const scanningLayer   = document.getElementById('ocrScanningLayer');
    const statusText      = document.getElementById('ocrStatusText');
    const subStatusText   = document.getElementById('ocrSubStatusText');
    const resultBadge     = document.getElementById('ocrResultBadge');
    
    // Form fields
    const ocrForm         = document.getElementById('ocrForm');
    const amountInput     = document.getElementById('ocrAmount');
    const merchantInput   = document.getElementById('ocrMerchant');
    const dateInput       = document.getElementById('ocrDate');
    const categorySelect  = document.getElementById('ocrCategory');
    const existingImgInput= document.getElementById('ocrExistingImage');
    const rawOcrText      = document.getElementById('rawOcrText');
    const btnToggleRaw    = document.getElementById('btnToggleRawOcr');
    const rawContainer    = document.getElementById('rawOcrContainer');
    const rawArrow        = document.getElementById('rawOcrArrow');

    // Paste Text Modal
    const btnPasteText    = document.getElementById('ocrBtnPasteText');
    const pasteOverlay    = document.getElementById('pasteModalOverlay');
    const btnCancelPaste  = document.getElementById('btnCancelPaste');
    const btnSubmitPaste  = document.getElementById('btnSubmitPaste');
    const pasteTextarea   = document.getElementById('pasteTextarea');

    let currentFile = null;

    btnCamera?.addEventListener('click', () => cameraInput.click());
    btnGallery?.addEventListener('click', () => galleryInput.click());

    cameraInput?.addEventListener('change', (e) => handleFileSelect(e.target.files[0]));
    galleryInput?.addEventListener('change', (e) => handleFileSelect(e.target.files[0]));

    btnRemovePhoto?.addEventListener('click', () => {
        currentFile = null;
        cameraInput.value = '';
        galleryInput.value = '';
        previewContainer.style.display = 'none';
        emptyState.style.display = 'block';
        dropzone.classList.remove('has-photo');
        existingImgInput.value = '';
    });

    // Toggle Raw OCR Text
    btnToggleRaw?.addEventListener('click', () => {
        const isHidden = rawContainer.style.display === 'none';
        rawContainer.style.display = isHidden ? 'block' : 'none';
        rawArrow.textContent = isHidden ? '▲' : '▼';
    });

    // Paste Modal Handlers
    btnPasteText?.addEventListener('click', () => {
        pasteTextarea.value = '';
        pasteOverlay.classList.add('open');
        setTimeout(() => pasteTextarea.focus(), 150);
    });
    btnCancelPaste?.addEventListener('click', () => pasteOverlay.classList.remove('open'));
    btnSubmitPaste?.addEventListener('click', async () => {
        const text = pasteTextarea.value.trim();
        if (!text) {
            window.showToast('Silakan tempel teks struk terlebih dahulu.', 'error');
            return;
        }
        pasteOverlay.classList.remove('open');
        processExtractedText(text);
    });

    // Drag & Drop
    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('dragover');
    });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        if (e.dataTransfer.files?.length > 0) {
            handleFileSelect(e.dataTransfer.files[0]);
        }
    });

    // Format Amount Input
    amountInput?.addEventListener('input', function() {
        const raw = this.value.replace(/\D/g, '');
        if (raw) {
            this.value = Number(raw).toLocaleString('id-ID');
        }
    });

    async function handleFileSelect(file) {
        if (!file || !file.type.startsWith('image/')) {
            window.showToast('Silakan pilih file gambar (JPG, PNG, WebP).', 'error');
            return;
        }

        currentFile = file;

        // Show image preview
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.src = e.target.result;
            previewContainer.style.display = 'block';
            emptyState.style.display = 'none';
            dropzone.classList.add('has-photo');
        };
        reader.readAsDataURL(file);

        // Start OCR Process
        startScanningUi();

        try {
            // Step 1: Upload image to server for storage & server-side receipt analysis
            const formData = new FormData();
            formData.append(window.DUITKU.csrfName, window.DUITKU.csrfToken);
            formData.append('receipt_image', file);

            const serverPromise = fetch('/transaction/ocr-scan', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            }).then(r => r.json()).catch(() => null);

            // Step 2: Client-side OCR via Tesseract.js for high accuracy text extraction
            let ocrText = '';
            if (window.Tesseract) {
                statusText.textContent = 'Memindai teks struk...';
                subStatusText.textContent = 'Membaca rincian dari foto';
                const res = await Tesseract.recognize(file, 'ind+eng', {
                    logger: m => {
                        if (m.status === 'recognizing text') {
                            subStatusText.textContent = `Mengenali teks: ${Math.round(m.progress * 100)}%`;
                        }
                    }
                });
                ocrText = res.data?.text || '';
            }

            const serverRes = await serverPromise;
            if (serverRes?.image) {
                existingImgInput.value = serverRes.image;
            }

            // Step 3: Parse extracted text
            processExtractedText(ocrText, serverRes?.data);
            window.showToast('Struk berhasil dipindai!', 'success');

        } catch (err) {
            console.error('OCR Error:', err);
            window.showToast('Pemindaian selesai dengan pembacaan standar.', 'info');
        } finally {
            stopScanningUi();
        }
    }

    function startScanningUi() {
        laserBeam.style.display = 'block';
        scanningLayer.style.display = 'flex';
        statusText.textContent = 'Memindai struk belanja...';
        subStatusText.textContent = 'Menyiapkan pemrosesan gambar';
    }

    function stopScanningUi() {
        laserBeam.style.display = 'none';
        scanningLayer.style.display = 'none';
    }

    function processExtractedText(text, serverData = null) {
        rawOcrText.textContent = text || 'Teks tidak terdeteksi secara otomatis.';

        let merchant = serverData?.merchant || '';
        let amount = serverData?.amount || 0;
        let date = serverData?.date || '';
        let suggestedCategory = serverData?.suggested_category || '';

        // Local regex parser if server did not find or for client refinement
        const lines = (text || '').split('\n').map(l => l.trim()).filter(Boolean);

        // 1. Merchant Detection
        const knownStores = [
            'Indomaret', 'Alfamart', 'Alfamidi', 'Superindo', 'Hypermart',
            'Transmart', 'Carrefour', 'Hero', 'Lotte Mart', 'Starbucks',
            'Janji Jiwa', 'Kopi Kenangan', 'KFC', 'McDonald\'s', 'McD',
            'Burger King', 'A&W', 'Pizza Hut', 'Dominos', 'HokBen',
            'SPBU', 'Pertamina', 'Shell', 'BP AKR', 'Guardian', 'Watsons',
            'Gramedia', 'Ace Hardware', 'Informa', 'Uniqlo', 'H&M', 'Zara'
        ];

        if (!merchant) {
            for (const store of knownStores) {
                if (text.toLowerCase().includes(store.toLowerCase())) {
                    merchant = store;
                    break;
                }
            }
        }

        if (!merchant && lines.length > 0) {
            for (const line of lines.slice(0, 4)) {
                const cleaned = line.replace(/[^a-zA-Z0-9\s]/g, '').trim();
                if (cleaned.length >= 3 && !/^\d+$/.test(cleaned) && !/^(jl|jalan|struk|nota|selamat|no|telp)/i.test(cleaned)) {
                    merchant = cleaned;
                    break;
                }
            }
        }

        // 2. Amount Detection
        if (!amount || amount <= 0) {
            const totalKeywords = ['TOTAL', 'GRAND TOTAL', 'JUMLAH', 'TOTAL BAYAR', 'TOTAL BELANJA', 'BAYAR', 'HARGA TOTAL', 'NETTO', 'SUBTOTAL'];
            for (const line of lines) {
                const upper = line.toUpperCase();
                for (const kw of totalKeywords) {
                    if (upper.includes(kw)) {
                        const matches = line.match(/(?:RP\.?|IDR)?\s*([0-9\.\,]+)/i);
                        if (matches && matches[1]) {
                            const val = parseFloat(matches[1].replace(/\./g, '').replace(',', '.'));
                            if (val > amount) amount = val;
                        }
                    }
                }
            }

            // Fallback to highest Rp amount found
            if (!amount || amount <= 0) {
                const allNumbers = text.match(/(?:Rp\.?|IDR)?\s*([0-9]{1,3}(?:\.[0-9]{3})+(?:,[0-9]+)?)/gi) || [];
                for (const m of allNumbers) {
                    const clean = parseFloat(m.replace(/[^0-9]/g, ''));
                    if (clean > amount && clean < 100000000) amount = clean;
                }
            }
        }

        // 3. Date Detection
        if (!date) {
            const dateMatch = text.match(/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})/);
            if (dateMatch) {
                let [_, d, m, y] = dateMatch;
                if (y.length === 2) y = '20' + y;
                d = d.padStart(2, '0');
                m = m.padStart(2, '0');
                date = `${y}-${m}-${d}`;
            } else {
                date = new Date().toISOString().substring(0, 10);
            }
        }

        // Populate fields
        if (amount > 0) {
            amountInput.value = Number(amount).toLocaleString('id-ID');
        }
        if (merchant) {
            merchantInput.value = merchant;
        } else if (!merchantInput.value) {
            merchantInput.value = 'Belanja Nota / Struk';
        }
        if (date) {
            dateInput.value = date;
        }

        // Auto-select matching category
        if (merchant) {
            const mLower = merchant.toLowerCase();
            const catOptions = categorySelect.options;
            for (let i = 0; i < catOptions.length; i++) {
                const optText = catOptions[i].text.toLowerCase();
                if (
                    (mLower.includes('makan') || mLower.includes('kfc') || mLower.includes('mcd') || mLower.includes('resto') || mLower.includes('kopi') || mLower.includes('starbucks')) && (optText.includes('makan') || optText.includes('kuliner') || optText.includes('food')) ||
                    (mLower.includes('indomaret') || mLower.includes('alfamart') || mLower.includes('superindo') || mLower.includes('belanja')) && (optText.includes('belanja') || optText.includes('shopping')) ||
                    (mLower.includes('spbu') || mLower.includes('pertamina') || mLower.includes('shell')) && (optText.includes('transport') || optText.includes('bensin'))
                ) {
                    categorySelect.selectedIndex = i;
                    break;
                }
            }
        }

        resultBadge.className = 'ocr-status-chip success';
        resultBadge.textContent = '✓ Data Terdeteksi';
    }

    // Submit transaction
    ocrForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const rawAmount = amountInput.value.replace(/\D/g, '');
        const amount = parseFloat(rawAmount);

        if (!amount || amount <= 0) {
            window.showToast('Nominal belanja wajib diisi.', 'error');
            amountInput.focus();
            return;
        }

        const formData = new FormData();
        formData.append(window.DUITKU.csrfName, window.DUITKU.csrfToken);
        formData.append('type', 'expense');
        formData.append('amount', amount);
        formData.append('note', merchantInput.value.trim());
        formData.append('date', dateInput.value);
        formData.append('wallet_id', document.getElementById('ocrWallet').value);
        formData.append('category_id', categorySelect.value || '');

        if (existingImgInput.value) {
            formData.append('existing_image', existingImgInput.value);
        } else if (currentFile) {
            formData.append('image', currentFile);
        }

        const btn = document.getElementById('btnSaveOcr');
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';

        try {
            const res = await fetch('/transaction/store', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            });
            const data = await res.json();

            if (data.success) {
                window.showToast('Transaksi belanja berhasil dicatat! 🎉', 'success');
                setTimeout(() => {
                    window.location.href = '/activity';
                }, 800);
            } else {
                window.showToast(data.message || 'Gagal menyimpan transaksi.', 'error');
                btn.disabled = false;
                btn.textContent = '💾 Simpan Pengeluaran Ini';
            }
        } catch (err) {
            window.showToast('Terjadi kesalahan jaringan.', 'error');
            btn.disabled = false;
            btn.textContent = '💾 Simpan Pengeluaran Ini';
        }
    });
});
</script>
<?= $this->endSection() ?>
