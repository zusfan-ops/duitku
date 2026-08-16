<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.kendaraan-detail-page {
    padding: 14px 16px 110px;
}

.vehicle-detail-hero {
    background: linear-gradient(135deg, #0F766E 0%, #0D9488 50%, #14B8A6 100%);
    border-radius: 20px;
    padding: 20px;
    color: #fff;
    margin-bottom: 16px;
    box-shadow: 0 8px 24px rgba(13, 148, 136, 0.25);
    position: relative;
}

.btn-add-log-main {
    background: var(--primary);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    padding: 12px 18px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: none;
    cursor: pointer;
    box-shadow: 0 3px 12px rgba(5, 150, 105, 0.25);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    width: 100%;
}
.btn-add-log-main:hover {
    box-shadow: 0 5px 16px rgba(5, 150, 105, 0.35);
}
.btn-add-log-main:active {
    transform: scale(0.98);
}

.log-item-card {
    background: var(--bg-card);
    border-radius: 16px;
    border: 1px solid var(--border);
    padding: 14px;
    box-shadow: var(--shadow-sm);
}

.form-input {
    width: 100%;
    padding: 11px 14px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    font-size: 13.5px;
    color: var(--text-primary);
    outline: none;
    transition: border-color 0.2s ease;
}
.form-input:focus {
    border-color: var(--primary);
}
.form-label {
    font-size: 11px;
    font-weight: 800;
    color: var(--text-muted);
    letter-spacing: 0.5px;
    margin-bottom: 5px;
    display: block;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="kendaraan-detail-page">

    <!-- Header / Hero -->
    <div class="vehicle-detail-hero">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
            <a href="/kendaraan" style="display:inline-flex;align-items:center;gap:6px;color:#fff;font-size:12px;text-decoration:none;opacity:0.85;font-weight:600">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
                Daftar Kendaraan
            </a>
            <button id="btnDeleteVehicle" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);color:#fff;border-radius:8px;padding:4px 10px;cursor:pointer;font-size:11.5px;font-weight:700">
                🗑️ Hapus
            </button>
        </div>
        <div style="font-size:20px;font-weight:800;margin-bottom:2px">
            <?= esc($vehicle['name']) ?>
        </div>
        <div style="font-size:12.5px;opacity:0.9">
            <?= esc($vehicle['brand'] ?: ucfirst($vehicle['type'])) ?><?= !empty($vehicle['model_year']) ? ' (' . esc($vehicle['model_year']) . ')' : '' ?>
            <?= !empty($vehicle['license_plate']) ? '· <strong>' . esc($vehicle['license_plate']) . '</strong>' : '' ?>
        </div>
    </div>

    <!-- Stats Card -->
    <div style="background:var(--bg-card);border-radius:18px;border:1px solid var(--border);padding:16px;margin-bottom:16px;box-shadow:var(--shadow-sm)">
        <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:10px;text-align:center">
            <div>
                <div style="font-size:10.5px;font-weight:800;color:var(--text-muted);letter-spacing:0.5px">ODOMETER</div>
                <div style="font-size:16px;font-weight:800;color:var(--text-primary);margin-top:2px">
                    <?= number_format((int)$vehicle['odometer'], 0, ',', '.') ?> KM
                </div>
            </div>
            <div>
                <div style="font-size:10.5px;font-weight:800;color:var(--text-muted);letter-spacing:0.5px">TOTAL BIAYA</div>
                <div style="font-size:16px;font-weight:800;color:#DC2626;margin-top:2px">
                    <?= esc($symbol) ?> <?= number_format((float)$vehicle['total_expense'], 0, ',', '.') ?>
                </div>
            </div>
            <div>
                <div style="font-size:10.5px;font-weight:800;color:var(--text-muted);letter-spacing:0.5px">TOTAL LOG</div>
                <div style="font-size:16px;font-weight:800;color:var(--text-primary);margin-top:2px">
                    <?= count($logs) ?> kali
                </div>
            </div>
        </div>

        <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border);display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:12px">
            <div>
                <span style="color:var(--text-muted)">Pajak PKB:</span>
                <strong><?= !empty($vehicle['tax_annual_date']) ? date('d M Y', strtotime($vehicle['tax_annual_date'])) : '-' ?></strong>
            </div>
            <div>
                <span style="color:var(--text-muted)">Pajak 5 Tahun:</span>
                <strong><?= !empty($vehicle['tax_5year_date']) ? date('d M Y', strtotime($vehicle['tax_5year_date'])) : '-' ?></strong>
            </div>
        </div>
    </div>

    <!-- Action Button -->
    <button class="btn-add-log-main" id="btnAddLogDetail" style="margin-bottom:16px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Catat Perawatan / Servis Baru
    </button>

    <!-- Logs list -->
    <div style="font-size:13px;font-weight:800;color:var(--text-muted);letter-spacing:0.5px;margin:16px 0 10px">RIWAYAT PERAWATAN & SERVIS</div>
    <?php if (empty($logs)): ?>
    <div style="text-align:center;padding:36px 20px;background:var(--bg-card);border-radius:18px;border:1px dashed var(--border)">
        <div style="font-size:36px;margin-bottom:8px">🔧</div>
        <div style="font-weight:700;color:var(--text-primary)">Belum ada riwayat perawatan</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Catat ganti oli, tune up, atau ganti sparepart pertama Anda.</div>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:10px">
        <?php foreach ($logs as $l):
            $typeLabel = match($l['type']) {
                'ganti_oli'      => '🛢️ Ganti Oli',
                'service_rutin'  => '🔧 Servis Berkala',
                'pajak_tahunan'  => '📑 Pajak PKB',
                'pajak_5tahunan' => '📑 Pajak 5 Tahun',
                'ganti_ban'      => '🚗 Ganti Ban / Part',
                'bbm'            => '⛽ BBM',
                default          => '🛠️ Perawatan',
            };
        ?>
        <div class="log-item-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px">
                <div>
                    <span style="font-size:10.5px;font-weight:800;background:var(--bg);padding:3px 7px;border-radius:6px;color:var(--text-muted);border:1px solid var(--border)">
                        <?= $typeLabel ?>
                    </span>
                    <div style="font-weight:800;font-size:14px;color:var(--text-primary);margin-top:5px"><?= esc($l['title']) ?></div>
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span style="font-weight:800;font-size:14px;color:#DC2626">
                        - <?= esc($symbol) ?> <?= number_format((float)$l['cost'], 0, ',', '.') ?>
                    </span>
                    <button class="btn-del-log" data-id="<?= $l['id'] ?>" style="border:none;background:transparent;color:var(--text-muted);cursor:pointer;font-size:14px">✕</button>
                </div>
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:12px;font-size:11.5px;color:var(--text-muted);margin-top:6px">
                <span>📅 <?= date('d M Y', strtotime($l['date'])) ?></span>
                <?php if (!empty($l['km'])): ?>
                <span>📍 <?= number_format((int)$l['km'], 0, ',', '.') ?> KM</span>
                <?php endif; ?>
                <?php if (!empty($l['next_km'])): ?>
                <span>🎯 Target: <?= number_format((int)$l['next_km'], 0, ',', '.') ?> KM</span>
                <?php endif; ?>
                <?php if (!empty($l['workshop'])): ?>
                <span>🏢 <?= esc($l['workshop']) ?></span>
                <?php endif; ?>
            </div>
            <?php if (!empty($l['notes'])): ?>
            <div style="font-size:11.5px;color:var(--text-muted);background:var(--bg);padding:6px 10px;border-radius:8px;margin-top:8px">
                💬 <?= esc($l['notes']) ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<!-- ════════════════════════════ ADD LOG MODAL SHEET -->
<div class="modal-overlay" id="addLogOverlay">
    <div class="modal-sheet" id="addLogModal">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3>🔧 Catat Perawatan Kendaraan</h3>
            <button class="modal-close" id="addLogClose">✕</button>
        </div>

        <form id="logForm" autocomplete="off" style="display:flex; flex-direction:column; gap:14px;">
            <div class="form-group">
                <label class="form-label">JENIS KEGIATAN</label>
                <select id="logType" class="form-input">
                    <option value="ganti_oli">🛢️ Ganti Oli Mesin / Gardan</option>
                    <option value="service_rutin">🔧 Servis Berkala / Tune Up</option>
                    <option value="pajak_tahunan">📑 Bayar Pajak Tahunan (PKB)</option>
                    <option value="pajak_5tahunan">📑 Bayar Pajak 5 Tahun (Ganti Plat)</option>
                    <option value="ganti_ban">🚗 Ganti Ban / Aki / Sparepart</option>
                    <option value="bbm">⛽ Pengisian BBM</option>
                    <option value="perbaikan">🛠️ Perbaikan / Reparasi</option>
                    <option value="lainnya">📦 Lain-lain</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">DESKRIPSI / RINCIAN *</label>
                <input type="text" id="logTitle" class="form-input" placeholder="cth: Ganti Oli Shell Advance AX7 10W-40" required>
            </div>

            <div class="form-group">
                <label class="form-label">BIAYA (<?= esc($symbol) ?>)</label>
                <input type="text" id="logCost" class="form-input" placeholder="0" inputmode="numeric">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div class="form-group">
                    <label class="form-label">KM SAAT INI</label>
                    <input type="text" id="logKm" class="form-input" value="<?= number_format((int)$vehicle['odometer'], 0, ',', '.') ?>" inputmode="numeric">
                </div>
                <div class="form-group">
                    <label class="form-label">TARGET KM BERIKUTNYA</label>
                    <input type="text" id="logNextKm" class="form-input" placeholder="0" inputmode="numeric">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div class="form-group">
                    <label class="form-label">TANGGAL</label>
                    <input type="date" id="logDate" class="form-input" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">BENGKEL / LOKASI</label>
                    <input type="text" id="logWorkshop" class="form-input" placeholder="Ahass / Auto2000">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">CATATAN (OPSIONAL)</label>
                <input type="text" id="logNotes" class="form-input" placeholder="Catatan part, nomor seri...">
            </div>

            <button type="submit" class="btn-save" id="addLogSave" style="margin-top:8px;background:var(--primary);color:#fff;padding:12px;border-radius:12px;font-weight:800;border:none;cursor:pointer">
                Simpan Log Perawatan
            </button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Delete vehicle
    document.getElementById('btnDeleteVehicle')?.addEventListener('click', async () => {
        if (!confirm('Hapus data kendaraan ini beserta seluruh riwayat perawatannya?')) return;
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        const res = await fetch('/kendaraan/delete/<?= $vehicle['id'] ?>', {
            method: 'POST',
            headers: {'X-Requested-With':'XMLHttpRequest'},
            body: formData
        });
        const json = await res.json();
        if (json.success) location.href = '/kendaraan';
        else alert('Gagal menghapus.');
    });

    // Delete Log
    document.querySelectorAll('.btn-del-log').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Hapus log perawatan ini?')) return;
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            const res = await fetch('/kendaraan/log/delete/' + btn.dataset.id, {
                method: 'POST',
                headers: {'X-Requested-With':'XMLHttpRequest'},
                body: formData
            });
            const json = await res.json();
            if (json.success) location.reload();
        });
    });

    // Modal
    const logOverlay = document.getElementById('addLogOverlay');
    document.getElementById('btnAddLogDetail')?.addEventListener('click', () => {
        logOverlay.classList.add('open');
    });
    document.getElementById('addLogClose')?.addEventListener('click', () => {
        logOverlay.classList.remove('open');
    });
    logOverlay?.addEventListener('click', (e) => {
        if (e.target === logOverlay) logOverlay.classList.remove('open');
    });

    // Format numeric inputs
    ['logCost','logKm','logNextKm'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', function(){
            let raw = this.value.replace(/\D/g,'');
            this.value = raw ? parseInt(raw,10).toLocaleString('id-ID') : '';
        });
    });

    document.getElementById('logForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const title = document.getElementById('logTitle').value.trim();
        const cost  = document.getElementById('logCost').value.replace(/\./g,'').replace(',','.');
        if (!title) { alert('Deskripsi kegiatan wajib diisi.'); return; }

        const btn = document.getElementById('addLogSave');
        btn.disabled = true; btn.textContent = 'Menyimpan...';

        try {
            const res = await fetch('/kendaraan/log/store', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
                body: new URLSearchParams({
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                    vehicle_id: '<?= $vehicle['id'] ?>',
                    type:       document.getElementById('logType').value,
                    title:      title,
                    cost:       cost || '0',
                    km:         document.getElementById('logKm').value.replace(/\./g,''),
                    next_km:    document.getElementById('logNextKm').value.replace(/\./g,''),
                    date:       document.getElementById('logDate').value,
                    workshop:   document.getElementById('logWorkshop').value,
                    notes:      document.getElementById('logNotes').value,
                })
            });
            const json = await res.json();
            if (json.success) location.reload();
            else alert(json.message || 'Gagal.');
        } catch(e) { alert('Terjadi kesalahan.'); }
        btn.disabled = false; btn.textContent = 'Simpan Log Perawatan';
    });
});
</script>
<?= $this->endSection() ?>
