<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Code Standee — <?= esc($store['store_name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        :root {
            --primary: #EA580C;
            --bg-body: #0F172A;
            --panel-bg: #1E293B;
            --border: #334155;
            --text-main: #F8FAFC;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
        }

        /* Top Screen Toolbar */
        .screen-toolbar {
            background: #1E293B;
            border-bottom: 1px solid var(--border);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .toolbar-title {
            font-size: 15px;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-tb {
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            transition: transform 0.1s ease;
        }
        .btn-tb:active { transform: scale(0.96); }
        .btn-tb-back {
            background: rgba(255, 255, 255, 0.08);
            color: #CBD5E1;
            border: 1px solid var(--border);
        }
        .btn-tb-print {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
        }

        /* Main Studio Layout */
        .studio-layout {
            display: grid;
            grid-template-columns: 340px 1fr;
            min-height: calc(100vh - 61px);
        }
        @media (max-width: 860px) {
            .studio-layout { grid-template-columns: 1fr; }
        }

        /* Settings Side Panel */
        .settings-panel {
            background: var(--panel-bg);
            border-right: 1px solid var(--border);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .panel-heading {
            font-size: 14px;
            font-weight: 800;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border);
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-label {
            font-size: 12px;
            font-weight: 700;
            color: #CBD5E1;
        }
        .form-control {
            width: 100%;
            background: #0F172A;
            border: 1px solid var(--border);
            color: #fff;
            padding: 9px 12px;
            border-radius: 10px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
        }
        .form-control:focus { border-color: var(--primary); }

        .theme-select-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
        }
        .theme-card-opt {
            padding: 8px 6px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: #0F172A;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
            cursor: pointer;
            color: #CBD5E1;
        }
        .theme-card-opt.active {
            border-color: var(--primary);
            background: rgba(234, 88, 12, 0.15);
            color: #FB923C;
        }

        /* Preview Canvas Viewport */
        .preview-viewport {
            padding: 40px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0B1120;
            overflow-y: auto;
        }

        /* ── THE PRINTABLE STANDEE POSTER ──────────────────────────────── */
        .standee-poster {
            width: 380px;
            background: #FFFFFF;
            color: #0F172A;
            border-radius: 24px;
            padding: 32px 24px 28px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        /* Theme: Modern Gold Slate (Default) */
        .standee-poster.theme-modern {
            border: 6px solid #EA580C;
            background: #FFFFFF;
        }
        .theme-modern .poster-top-badge {
            background: #EA580C;
            color: #fff;
        }
        .theme-modern .poster-qr-frame {
            border: 3px solid #EA580C;
            padding: 12px;
            border-radius: 20px;
            background: #FFF7ED;
        }

        /* Theme: Vintage Cafe */
        .standee-poster.theme-vintage {
            border: 6px double #78350F;
            background: #FEF3C7;
            font-family: 'Playfair Display', serif;
        }
        .theme-vintage .poster-top-badge {
            background: #78350F;
            color: #FEF3C7;
            font-family: 'Inter', sans-serif;
        }
        .theme-vintage .poster-qr-frame {
            border: 3px dashed #78350F;
            padding: 12px;
            border-radius: 16px;
            background: #FFFFFF;
        }
        .theme-vintage .poster-store-name {
            color: #451A03;
        }

        /* Theme: Elegant Dark / Minimal */
        .standee-poster.theme-dark {
            border: 6px solid #0F172A;
            background: #0F172A;
            color: #FFFFFF;
        }
        .theme-dark .poster-top-badge {
            background: #38BDF8;
            color: #0F172A;
        }
        .theme-dark .poster-qr-frame {
            border: 3px solid #38BDF8;
            padding: 12px;
            border-radius: 20px;
            background: #FFFFFF;
        }
        .theme-dark .poster-store-name {
            color: #FFFFFF;
        }
        .theme-dark .poster-desc-text {
            color: #94A3B8;
        }
        .theme-dark .poster-url-badge {
            background: #1E293B;
            color: #38BDF8;
            border-color: #334155;
        }

        /* Standee Internal Elements */
        .poster-top-badge {
            font-size: 11px;
            font-weight: 900;
            padding: 5px 14px;
            border-radius: 20px;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .poster-store-name {
            font-size: 24px;
            font-weight: 900;
            letter-spacing: -0.5px;
            line-height: 1.2;
            margin-bottom: 4px;
        }
        .poster-table-pill {
            font-size: 13px;
            font-weight: 800;
            color: #EA580C;
            background: rgba(234, 88, 12, 0.1);
            padding: 3px 12px;
            border-radius: 12px;
            margin-bottom: 16px;
            display: inline-block;
        }

        .poster-qr-frame {
            margin-bottom: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        }
        #qrcodeCanvas {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #qrcodeCanvas img, #qrcodeCanvas canvas {
            display: block;
            margin: 0 auto;
        }

        .poster-url-badge {
            font-size: 12px;
            font-weight: 800;
            font-family: 'Courier New', monospace;
            background: #F1F5F9;
            color: #0F172A;
            border: 1px solid #E2E8F0;
            padding: 5px 12px;
            border-radius: 8px;
            margin-bottom: 12px;
            word-break: break-all;
        }
        .poster-desc-text {
            font-size: 12px;
            color: #475569;
            line-height: 1.5;
            padding: 0 6px;
            font-weight: 500;
        }
        .poster-footer-brand {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px dashed rgba(0, 0, 0, 0.15);
            font-size: 10.5px;
            font-weight: 700;
            color: #94A3B8;
            letter-spacing: 0.5px;
        }

        /* ── PRINT STYLESHEET (PDF Export) ────────────────────────────────── */
        @media print {
            .screen-toolbar, .settings-panel { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .studio-layout { display: block !important; min-height: auto !important; }
            .preview-viewport {
                padding: 0 !important;
                background: none !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            .standee-poster {
                box-shadow: none !important;
                margin: 20px auto !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            @page {
                size: A4 portrait;
                margin: 1cm;
            }
        }
    </style>
</head>
<body>

    <!-- Screen Toolbar -->
    <div class="screen-toolbar">
        <div class="toolbar-title">
            <span>🖨️</span> Cetak Standee QR Code Menu
        </div>
        <div class="toolbar-actions">
            <a href="/pos/orders" class="btn-tb btn-tb-back">← Pesanan Masuk</a>
            <a href="/pos" class="btn-tb btn-tb-back">💻 Kasir</a>
            <button class="btn-tb btn-tb-print" onclick="window.print()">
                <span>🖨️</span> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <div class="studio-layout">
        
        <!-- Left Live Controls Panel -->
        <div class="settings-panel">
            <div class="panel-heading">Kustomisasi Standee</div>

            <div class="form-group">
                <label class="form-label">Nama Toko / Outlet</label>
                <input type="text" id="inputStoreName" class="form-control" value="<?= esc($store['store_name']) ?>" oninput="updateLivePoster()">
            </div>

            <div class="form-group">
                <label class="form-label">Nomor Meja (Opsional)</label>
                <input type="text" id="inputTableNo" class="form-control" placeholder="Contoh: 01, Meja 5, VIP..." value="<?= esc($tableNo) ?>" oninput="updateLivePoster()">
                <span style="font-size:10.5px;color:var(--text-muted)">Kosongkan jika QR berlaku umum untuk semua meja.</span>
            </div>

            <div class="form-group">
                <label class="form-label">Teks Keterangan di Bawah QR</label>
                <textarea id="inputQrFooter" class="form-control" rows="3" oninput="updateLivePoster()"><?= esc($store['store_qr_footer']) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Pilihan Bingkai (Frame)</label>
                <div class="theme-select-grid">
                    <div class="theme-card-opt active" onclick="setTheme('modern', this)">
                        🔥 Oranye Modern
                    </div>
                    <div class="theme-card-opt" onclick="setTheme('vintage', this)">
                        ☕ Klasik Cafe
                    </div>
                    <div class="theme-card-opt" onclick="setTheme('dark', this)">
                        🌑 Dark Minimal
                    </div>
                </div>
            </div>

            <div style="background:#0F172A;border:1px solid var(--border);border-radius:12px;padding:12px;margin-top:auto">
                <div style="font-size:11.5px;font-weight:800;color:#38BDF8;margin-bottom:4px">💡 Tips Penggunaan:</div>
                <div style="font-size:11px;color:var(--text-muted);line-height:1.4">
                    Klik tombol <strong>Cetak / Simpan PDF</strong> lalu pilih ukuran kertas A4/A5, kemudian gunting dan masukkan ke standee akrilik meja toko Anda!
                </div>
            </div>
        </div>

        <!-- Right Live Preview Viewport -->
        <div class="preview-viewport">
            
            <div class="standee-poster theme-modern" id="posterCard">
                
                <div class="poster-top-badge" id="posterBadge">
                    <span>📱</span> SCAN & PESAN DI SINI
                </div>

                <div class="poster-store-name" id="posterStoreName">
                    <?= esc($store['store_name']) ?>
                </div>

                <div class="poster-table-pill" id="posterTablePill" style="<?= empty($tableNo) ? 'display:none' : '' ?>">
                    <span>🪑</span> <span id="posterTableText">Nomor Meja <?= esc($tableNo) ?></span>
                </div>

                <div class="poster-qr-frame">
                    <div id="qrcodeCanvas"></div>
                </div>

                <div class="poster-url-badge" id="posterUrlBadge">
                    <?= esc($targetUrl) ?>
                </div>

                <div class="poster-desc-text" id="posterDescText">
                    <?= esc($store['store_qr_footer']) ?>
                </div>

                <div class="poster-footer-brand">
                    Powered by DuitKu POS
                </div>

            </div>

        </div>

    </div>

    <script>
        const BASE_SLUG_URL = "<?= rtrim(base_url(), '/') . '/menu/' . urlencode($store['store_slug']) ?>";
        let qrcode = null;

        function renderQRCode(text) {
            const container = document.getElementById('qrcodeCanvas');
            container.innerHTML = '';
            qrcode = new QRCode(container, {
                text: text,
                width: 200,
                height: 200,
                colorDark: "#0F172A",
                colorLight: "#FFFFFF",
                correctLevel: QRCode.CorrectLevel.H
            });
        }

        function updateLivePoster() {
            const storeName = document.getElementById('inputStoreName').value.trim() || 'Nama Toko';
            const tableNo = document.getElementById('inputTableNo').value.trim();
            const qrFooter = document.getElementById('inputQrFooter').value.trim() || 'Scan QR untuk melihat daftar menu & memesan langsung dari meja Anda.';

            document.getElementById('posterStoreName').textContent = storeName;
            document.getElementById('posterDescText').textContent = qrFooter;

            const tablePill = document.getElementById('posterTablePill');
            const tableText = document.getElementById('posterTableText');

            let finalUrl = BASE_SLUG_URL;
            if (tableNo) {
                tablePill.style.display = 'inline-block';
                tableText.textContent = 'Nomor Meja ' + tableNo;
                finalUrl += '?table=' + encodeURIComponent(tableNo);
            } else {
                tablePill.style.display = 'none';
            }

            document.getElementById('posterUrlBadge').textContent = finalUrl;
            renderQRCode(finalUrl);
        }

        function setTheme(themeName, el) {
            document.querySelectorAll('.theme-card-opt').forEach(t => t.classList.remove('active'));
            el.classList.add('active');

            const card = document.getElementById('posterCard');
            card.className = 'standee-poster theme-' + themeName;
        }

        // Initial render
        window.addEventListener('DOMContentLoaded', () => {
            updateLivePoster();
        });
    </script>
</body>
</html>
