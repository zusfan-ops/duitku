<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.kendaraan-page {
    padding: 14px 16px 110px;
}

.kendaraan-hero {
    background: linear-gradient(135deg, #0F766E 0%, #0D9488 50%, #14B8A6 100%);
    border-radius: 20px;
    padding: 20px;
    color: #fff;
    margin-bottom: 16px;
    box-shadow: 0 8px 24px rgba(13, 148, 136, 0.25);
    position: relative;
    overflow: hidden;
}
.kendaraan-hero::after {
    content: '';
    position: absolute;
    right: -20px;
    bottom: -20px;
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
}
.kendaraan-hero-title {
    font-size: 20px;
    font-weight: 800;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.kendaraan-hero-sub {
    font-size: 12.5px;
    opacity: 0.88;
    line-height: 1.4;
}

.btn-add-vehicle {
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
.btn-add-vehicle:hover {
    box-shadow: 0 5px 16px rgba(5, 150, 105, 0.35);
}
.btn-add-vehicle:active {
    transform: scale(0.98);
}

.vehicle-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 16px;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: border-color 0.2s ease;
}
.vehicle-card:hover {
    border-color: var(--primary);
}

.empty-vehicle-card {
    text-align: center;
    padding: 44px 20px;
    background: var(--bg-card);
    border-radius: 20px;
    border: 1px dashed var(--border);
    margin-top: 6px;
}
.empty-vehicle-icon {
    font-size: 48px;
    margin-bottom: 12px;
}

/* Modal form controls */
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
<div class="kendaraan-page">

    <!-- Hero Card -->
    <div class="kendaraan-hero">
        <div style="margin-bottom: 10px;">
            <a href="/features" style="display:inline-flex;align-items:center;gap:6px;color:#fff;font-size:12px;text-decoration:none;opacity:0.85;font-weight:600">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
                Layanan & Fitur
            </a>
        </div>
        <div class="kendaraan-hero-title">
            <span>🚗</span> Kendaraan & Servis
        </div>
        <div class="kendaraan-hero-sub">
            Catat data armada, riwayat servis, ganti oli, dan pengingat jatuh tempo pajak tahunan.
        </div>
    </div>

    <!-- Tax Alerts -->
    <?php if (!empty($taxAlerts)): ?>
    <div style="background:#FEF2F2;border:1px solid #FCA5A5;border-radius:16px;padding:14px;margin-bottom:16px">
        <div style="font-weight:800;color:#DC2626;font-size:12.5px;margin-bottom:8px;display:flex;align-items:center;gap:6px">
            <span>⚠️</span> PERINGATAN JATUH TEMPO PAJAK
        </div>
        <div style="display:flex;flex-direction:column;gap:6px">
            <?php foreach ($taxAlerts as $alert): ?>
            <div style="font-size:12px;color:#991B1B;display:flex;justify-content:space-between;align-items:center">
                <span><strong><?= esc($alert['vehicle_name']) ?></strong> (<?= esc($alert['license_plate'] ?: '-') ?>) — <?= esc($alert['type']) ?></span>
                <span style="font-weight:800;background:#FEE2E2;padding:2px 8px;border-radius:6px">
                    <?= $alert['days_left'] <= 0 ? 'Hari Ini / Lewat' : $alert['days_left'] . ' hari lagi' ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Action Bar -->
    <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
        <button class="btn-add-vehicle" id="btnAddVehicle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Kendaraan
        </button>
        <?php if (!empty($vehicles)): ?>
        <button class="btn-outline" id="btnQuickLog" style="flex:1;min-width:140px;padding:10px 14px;border-radius:14px;font-weight:700;font-size:12.5px">
            🔧 Catat Servis / Oli
        </button>
        <?php endif; ?>
    </div>

    <!-- Vehicles List -->
    <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:24px">
        <?php if (empty($vehicles)): ?>
        <div class="empty-vehicle-card">
            <div class="empty-vehicle-icon">🛵</div>
            <div style="font-weight:800;color:var(--text-primary);font-size:16px;margin-bottom:6px">Belum Ada Data Kendaraan</div>
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:16px;max-width:320px;margin-left:auto;margin-right:auto">
                Tambahkan motor atau mobil Anda untuk memantau perawatan, riwayat oli, dan pengingat pajaknya.
            </div>
            <button class="btn-add-vehicle" id="btnAddVehicleEmpty" style="max-width:240px;margin:0 auto">
                ＋ Tambah Kendaraan Pertama
            </button>
        </div>
        <?php else: ?>
        <?php foreach ($vehicles as $v):
            $typeIcon = match($v['type']) { 'mobil' => '🚗', 'truk' => '🚚', 'lainnya' => '🚜', default => '🏍️' };
        ?>
        <div class="vehicle-card">
            <div style="display:flex;gap:12px;align-items:flex-start">
                <div style="width:48px;height:48px;border-radius:14px;background:var(--bg);display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0;border:1px solid var(--border)">
                    <?= $typeIcon ?>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <h3 style="font-size:15px;font-weight:800;color:var(--text-primary);margin:0"><?= esc($v['name']) ?></h3>
                        <?php if (!empty($v['license_plate'])): ?>
                        <span style="font-size:11px;font-weight:800;background:var(--bg);padding:2px 8px;border-radius:6px;border:1px solid var(--border);letter-spacing:0.5px">
                            <?= esc($v['license_plate']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:3px">
                        <?= esc($v['brand'] ?: ucfirst($v['type'])) ?><?= !empty($v['model_year']) ? ' · ' . esc($v['model_year']) : '' ?>
                        · <strong><?= number_format((int)$v['odometer'], 0, ',', '.') ?> KM</strong>
                    </div>
                </div>
            </div>

            <!-- Highlights Grid -->
            <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:8px;background:var(--bg);border-radius:12px;padding:10px;font-size:11.5px">
                <div>
                    <div style="color:var(--text-muted)">🛢️ Oli Terakhir</div>
                    <div style="font-weight:700;color:var(--text-primary);margin-top:2px">
                        <?= !empty($v['last_oil_date']) ? date('d M Y', strtotime($v['last_oil_date'])) : 'Belum tercatat' ?>
                    </div>
                </div>
                <div>
                    <div style="color:var(--text-muted)">📑 Pajak PKB</div>
                    <div style="font-weight:700;color:var(--text-primary);margin-top:2px">
                        <?= !empty($v['tax_annual_date']) ? date('d M Y', strtotime($v['tax_annual_date'])) : 'Belum diatur' ?>
                    </div>
                </div>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;padding-top:8px;border-top:1px solid var(--border)">
                <div style="font-size:12px;color:var(--text-muted)">
                    Total Biaya: <strong style="color:var(--text-primary)"><?= esc($symbol) ?> <?= number_format((float)$v['total_expense'], 0, ',', '.') ?></strong>
                </div>
                <div style="display:flex;gap:6px">
                    <button class="btn-outline btn-add-log-v" data-id="<?= $v['id'] ?>" data-name="<?= esc($v['name']) ?>" data-km="<?= $v['odometer'] ?>" style="padding:6px 12px;font-size:12px;border-radius:8px;font-weight:700">
                        + Catat Log
                    </button>
                    <a href="/kendaraan/<?= $v['id'] ?>" class="btn-primary" style="padding:6px 14px;font-size:12px;border-radius:8px;text-decoration:none;font-weight:700">
                        Detail →
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Recent Logs -->
    <?php if (!empty($logs)): ?>
    <div style="font-size:13px;font-weight:800;color:var(--text-muted);letter-spacing:0.5px;margin:20px 0 10px">RIWAYAT PERAWATAN TERBARU</div>
    <div style="background:var(--bg-card);border-radius:18px;border:1px solid var(--border);overflow:hidden">
        <?php foreach ($logs as $log):
            $typeLabel = match($log['type']) {
                'ganti_oli'      => '🛢️ Ganti Oli',
                'service_rutin'  => '🔧 Servis Berkala',
                'pajak_tahunan'  => '📑 Pajak PKB',
                'pajak_5tahunan' => '📑 Pajak 5 Tahun',
                'ganti_ban'      => '🚗 Ganti Ban/Part',
                'bbm'            => '⛽ BBM',
                default          => '🛠️ Perawatan',
            };
        ?>
        <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
            <div>
                <div style="font-weight:700;font-size:13px;color:var(--text-primary)"><?= esc($log['title']) ?></div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px">
                    <?= esc($log['vehicle_name']) ?> · <?= $typeLabel ?> · <?= date('d M Y', strtotime($log['date'])) ?>
                    <?php if (!empty($log['km'])): ?> · <strong><?= number_format((int)$log['km'], 0, ',', '.') ?> KM</strong><?php endif; ?>
                </div>
            </div>
            <div style="font-weight:800;font-size:13px;color:#DC2626;text-align:right">
                - <?= esc($symbol) ?> <?= number_format((float)$log['cost'], 0, ',', '.') ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<!-- ════════════════════════════ ADD VEHICLE MODAL SHEET -->
<div class="modal-overlay" id="addVehicleOverlay">
    <div class="modal-sheet" id="addVehicleModal">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3 id="vehicleModalTitle">🚗 Tambah Kendaraan</h3>
            <button class="modal-close" id="addVehicleClose">✕</button>
        </div>

        <form id="vehicleForm" autocomplete="off" style="display:flex; flex-direction:column; gap:14px;">
            <input type="hidden" id="vehicleId" value="">

            <div class="form-group">
                <label class="form-label">TIPE KENDARAAN</label>
                <select id="vehicleType" class="form-input">
                    <option value="motor">🏍️ Sepeda Motor</option>
                    <option value="mobil">🚗 Mobil</option>
                    <option value="truk">🚚 Truk / Niaga</option>
                    <option value="lainnya">🚜 Lainnya</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">NAMA KENDARAAN *</label>
                <input type="text" id="vehicleName" class="form-input" placeholder="cth: Vario 160 Harian, Avanza Veloz" required>
            </div>

            <div class="form-group">
                <label class="form-label">PLAT NOMOR</label>
                <input type="text" id="vehiclePlate" class="form-input" placeholder="cth: H 1234 AB" style="text-transform:uppercase">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div class="form-group">
                    <label class="form-label">MERK</label>
                    <input type="text" id="vehicleBrand" class="form-input" placeholder="Honda / Toyota">
                </div>
                <div class="form-group">
                    <label class="form-label">TAHUN</label>
                    <input type="text" id="vehicleYear" class="form-input" placeholder="2022">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">ODOMETER SAAT INI (KM)</label>
                <input type="text" id="vehicleOdometer" class="form-input" placeholder="0" inputmode="numeric">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div class="form-group">
                    <label class="form-label">PAJAK TAHUNAN (PKB)</label>
                    <input type="date" id="vehicleTaxAnnual" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">PAJAK 5 TAHUN (PLAT)</label>
                    <input type="date" id="vehicleTax5Year" class="form-input">
                </div>
            </div>

            <button type="submit" class="btn-save" id="addVehicleSave" style="margin-top:8px;background:var(--primary);color:#fff;padding:12px;border-radius:12px;font-weight:800;border:none;cursor:pointer">
                Simpan Kendaraan
            </button>
        </form>
    </div>
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
            <input type="hidden" id="logVehicleId" value="">

            <div class="form-group">
                <label class="form-label">PILIH KENDARAAN</label>
                <select id="logSelectVehicle" class="form-input">
                    <?php if (!empty($vehicles)): ?>
                        <?php foreach ($vehicles as $v): ?>
                        <option value="<?= $v['id'] ?>" data-km="<?= $v['odometer'] ?>"><?= esc($v['name']) ?> (<?= esc($v['license_plate'] ?: '-') ?>)</option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

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
                    <input type="text" id="logKm" class="form-input" placeholder="0" inputmode="numeric">
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
                <input type="text" id="logNotes" class="form-input" placeholder="Catatan part, garansi...">
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
    // Format numeric inputs
    ['vehicleOdometer','logCost','logKm','logNextKm'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', function(){
            let raw = this.value.replace(/\D/g,'');
            this.value = raw ? parseInt(raw,10).toLocaleString('id-ID') : '';
        });
    });

    // Add Vehicle Modal open/close
    const vehicleOverlay = document.getElementById('addVehicleOverlay');
    const openVehicleModal = () => {
        document.getElementById('vehicleId').value = '';
        document.getElementById('vehicleName').value = '';
        document.getElementById('vehiclePlate').value = '';
        document.getElementById('vehicleBrand').value = '';
        document.getElementById('vehicleYear').value = '';
        document.getElementById('vehicleOdometer').value = '';
        document.getElementById('vehicleTaxAnnual').value = '';
        document.getElementById('vehicleTax5Year').value = '';
        vehicleOverlay.classList.add('open');
    };

    document.getElementById('btnAddVehicle')?.addEventListener('click', openVehicleModal);
    document.getElementById('btnAddVehicleEmpty')?.addEventListener('click', openVehicleModal);
    document.getElementById('addVehicleClose')?.addEventListener('click', () => {
        vehicleOverlay.classList.remove('open');
    });
    vehicleOverlay?.addEventListener('click', (e) => {
        if (e.target === vehicleOverlay) vehicleOverlay.classList.remove('open');
    });

    // Submit Vehicle Form
    document.getElementById('vehicleForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('vehicleName').value.trim();
        if (!name) { alert('Nama kendaraan wajib diisi.'); return; }
        const btn = document.getElementById('addVehicleSave');
        btn.disabled = true; btn.textContent = 'Menyimpan...';

        try {
            const res = await fetch('/kendaraan/store', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
                body: new URLSearchParams({
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                    id:              document.getElementById('vehicleId').value,
                    type:            document.getElementById('vehicleType').value,
                    name:            name,
                    license_plate:   document.getElementById('vehiclePlate').value,
                    brand:           document.getElementById('vehicleBrand').value,
                    model_year:      document.getElementById('vehicleYear').value,
                    odometer:        document.getElementById('vehicleOdometer').value.replace(/\./g,''),
                    tax_annual_date: document.getElementById('vehicleTaxAnnual').value,
                    tax_5year_date:  document.getElementById('vehicleTax5Year').value,
                })
            });
            const json = await res.json();
            if (json.success) location.reload();
            else alert(json.message || 'Gagal menyimpan.');
        } catch(e) { alert('Terjadi kesalahan.'); }
        btn.disabled = false; btn.textContent = 'Simpan Kendaraan';
    });

    // Add Log Modal
    const logOverlay = document.getElementById('addLogOverlay');
    const openLogModal = (vehicleId, km) => {
        const sel = document.getElementById('logSelectVehicle');
        if (sel && vehicleId) sel.value = vehicleId;
        if (sel) document.getElementById('logVehicleId').value = sel.value;
        if (km) document.getElementById('logKm').value = parseInt(km, 10).toLocaleString('id-ID');
        document.getElementById('logCost').value = '';
        document.getElementById('logTitle').value = '';
        document.getElementById('logNextKm').value = '';
        document.getElementById('logNotes').value = '';
        logOverlay.classList.add('open');
    };

    document.getElementById('btnQuickLog')?.addEventListener('click', () => openLogModal());
    document.querySelectorAll('.btn-add-log-v').forEach(btn => {
        btn.addEventListener('click', () => openLogModal(btn.dataset.id, btn.dataset.km));
    });

    document.getElementById('logSelectVehicle')?.addEventListener('change', function(){
        document.getElementById('logVehicleId').value = this.value;
        const opt = this.options[this.selectedIndex];
        const km = opt.dataset.km;
        if (km) document.getElementById('logKm').value = parseInt(km, 10).toLocaleString('id-ID');
    });

    document.getElementById('addLogClose')?.addEventListener('click', () => {
        logOverlay.classList.remove('open');
    });
    logOverlay?.addEventListener('click', (e) => {
        if (e.target === logOverlay) logOverlay.classList.remove('open');
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
                    vehicle_id: document.getElementById('logSelectVehicle').value,
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
            else alert(json.message || 'Gagal menyimpan log.');
        } catch(e) { alert('Terjadi kesalahan.'); }
        btn.disabled = false; btn.textContent = 'Simpan Log Perawatan';
    });
});
</script>
<?= $this->endSection() ?>
