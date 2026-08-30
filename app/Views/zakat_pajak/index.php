<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.zp-container {
    max-width: 680px;
    margin: 0 auto;
    padding: 16px 16px 120px;
}

.zp-nav-tabs {
    display: flex;
    gap: 6px;
    background: var(--card);
    padding: 6px;
    border-radius: 16px;
    border: 1px solid var(--border);
    margin-bottom: 20px;
    overflow-x: auto;
}

.zp-tab-btn {
    flex: 1;
    min-width: 100px;
    padding: 10px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
    text-align: center;
    border: none;
    background: transparent;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
}

.zp-tab-btn.active {
    background: var(--primary);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
}

.zp-card {
    background: var(--card);
    border-radius: 20px;
    border: 1px solid var(--border);
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}

.zp-header-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 800;
    margin-bottom: 12px;
}

.zp-badge-emerald { background: rgba(16, 185, 129, 0.12); color: #059669; }
.zp-badge-blue { background: rgba(59, 130, 246, 0.12); color: #2563EB; }
.zp-badge-amber { background: rgba(245, 158, 11, 0.12); color: #D97706; }
.zp-badge-purple { background: rgba(139, 92, 246, 0.12); color: #7C3AED; }

.zp-form-group {
    margin-bottom: 16px;
}

.zp-label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-secondary);
    margin-bottom: 6px;
}

.zp-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.zp-prefix {
    position: absolute;
    left: 14px;
    font-size: 13px;
    font-weight: 700;
    color: var(--text-muted);
}

.zp-input {
    width: 100%;
    padding: 11px 14px 11px 40px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text-primary);
    font-size: 14px;
    font-weight: 600;
    outline: none;
    box-sizing: border-box;
    transition: border-color 0.15s ease;
}

.zp-input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}

.zp-result-box {
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
    color: #ffffff;
    border-radius: 18px;
    padding: 20px;
    margin-top: 20px;
}

.zp-res-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    font-size: 12.5px;
    color: #CBD5E1;
}

.zp-res-total {
    border-top: 1px solid rgba(255, 255, 255, 0.15);
    margin-top: 10px;
    padding-top: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.zp-res-val {
    font-size: 22px;
    font-weight: 900;
    color: #34D399;
}

.zp-btn-action {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 13px;
    border-radius: 14px;
    background: var(--primary);
    color: #ffffff;
    font-size: 14px;
    font-weight: 800;
    border: none;
    cursor: pointer;
    margin-top: 16px;
    transition: transform 0.15s ease;
}
.zp-btn-action:hover {
    transform: translateY(-1px);
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="zp-container">
    
    <!-- Title & Description -->
    <div style="margin-bottom: 16px;">
        <h2 style="margin: 0 0 4px; font-size: 20px; font-weight: 800; color: var(--text-primary);">🧮 Kalkulator Zakat & Pajak</h2>
        <p style="margin: 0; font-size: 12.5px; color: var(--text-secondary);">Hitung kewajiban Zakat Maal, Profesi, Fitrah, serta Pajak PPh/PPN dengan standar resmi.</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="zp-nav-tabs">
        <button class="zp-tab-btn active" onclick="switchZpTab('maal')">🕌 Zakat Maal</button>
        <button class="zp-tab-btn" onclick="switchZpTab('profesi')">💼 Profesi</button>
        <button class="zp-tab-btn" onclick="switchZpTab('fitrah')">🌾 Fitrah</button>
        <button class="zp-tab-btn" onclick="switchZpTab('pajak')">📊 Pajak (PPh/PPN)</button>
    </div>

    <!-- ── 1. TAB ZAKAT MAAL ── -->
    <div id="tab-maal" class="zp-tab-content">
        <div class="zp-card">
            <span class="zp-header-badge zp-badge-emerald">🕌 ZAKAT HARTA / SIMPANAN (2.5%)</span>
            <p style="font-size: 12px; color: var(--text-secondary); margin-top: 0; margin-bottom: 16px;">
                Wajib jika total harta melebihi nisab <strong>85 gram emas</strong> dan telah tersimpan selama 1 tahun (haul).
            </p>

            <div class="zp-form-group">
                <label class="zp-label">Harga Emas Saat Ini / Gram (Rp)</label>
                <div class="zp-input-wrap">
                    <span class="zp-prefix">Rp</span>
                    <input type="number" id="maal_gold_price" class="zp-input" value="1450000" oninput="calcMaal()">
                </div>
            </div>

            <div class="zp-form-group">
                <label class="zp-label">Total Saldo Kas & Tabungan Bank (Rp)</label>
                <div class="zp-input-wrap">
                    <span class="zp-prefix">Rp</span>
                    <input type="number" id="maal_cash" class="zp-input" value="<?= esc($totalBalance) ?>" oninput="calcMaal()">
                </div>
                <small style="font-size: 11px; color: var(--primary); margin-top: 4px; display: block;">*Otomatis terisi dari total saldo seluruh dompet Anda.</small>
            </div>

            <div class="zp-form-group">
                <label class="zp-label">Harta Lainnya (Emas, Surat Berharga, Investasi) (Rp)</label>
                <div class="zp-input-wrap">
                    <span class="zp-prefix">Rp</span>
                    <input type="number" id="maal_other" class="zp-input" value="0" oninput="calcMaal()">
                </div>
            </div>

            <div class="zp-form-group">
                <label class="zp-label">Hutang Jatuh Tempo / Mendesak (Rp)</label>
                <div class="zp-input-wrap">
                    <span class="zp-prefix">Rp</span>
                    <input type="number" id="maal_debt" class="zp-input" value="0" oninput="calcMaal()">
                </div>
            </div>

            <div class="zp-result-box">
                <div class="zp-res-row">
                    <span>Nisab 85 Gram Emas:</span>
                    <strong id="maal_nisab_val">Rp 123.250.000</strong>
                </div>
                <div class="zp-res-row">
                    <span>Total Harta Bersih:</span>
                    <strong id="maal_net_val">Rp 0</strong>
                </div>
                <div class="zp-res-row">
                    <span>Status Kewajiban:</span>
                    <span id="maal_status_badge" style="color: #FBBF24; font-weight: 800;">Belum Wajib Zakat</span>
                </div>
                <div class="zp-res-total">
                    <div>
                        <div style="font-size: 11px; color: #94A3B8;">ZAKAT MAAL WAJIB (2.5%)</div>
                        <div class="zp-res-val" id="maal_result_val">Rp 0</div>
                    </div>
                </div>
            </div>

            <button class="zp-btn-action" onclick="recordToDuitku('maal')">
                <span>📝</span> Catat Pengeluaran Zakat Maal
            </button>
        </div>
    </div>

    <!-- ── 2. TAB ZAKAT PROFESI ── -->
    <div id="tab-profesi" class="zp-tab-content" style="display: none;">
        <div class="zp-card">
            <span class="zp-header-badge zp-badge-blue">💼 ZAKAT PENGHASILAN / GAJI (2.5%)</span>
            <p style="font-size: 12px; color: var(--text-secondary); margin-top: 0; margin-bottom: 16px;">
                Dikeluarkan setiap menerima gaji bulanan dengan nisab setara <strong>653 kg beras / gabah</strong> per tahun (~54.4 kg/bln).
            </p>

            <div class="zp-form-group">
                <label class="zp-label">Harga Beras / Kg (Rp)</label>
                <div class="zp-input-wrap">
                    <span class="zp-prefix">Rp</span>
                    <input type="number" id="prof_rice_price" class="zp-input" value="15000" oninput="calcProfesi()">
                </div>
            </div>

            <div class="zp-form-group">
                <label class="zp-label">Gaji / Penghasilan Bulanan Pokok (Rp)</label>
                <div class="zp-input-wrap">
                    <span class="zp-prefix">Rp</span>
                    <input type="number" id="prof_salary" class="zp-input" value="<?= $monthlyIncome > 0 ? (int)$monthlyIncome : 6000000 ?>" oninput="calcProfesi()">
                </div>
            </div>

            <div class="zp-form-group">
                <label class="zp-label">Bonus / Penghasilan Tambahan Lainnya (Rp)</label>
                <div class="zp-input-wrap">
                    <span class="zp-prefix">Rp</span>
                    <input type="number" id="prof_bonus" class="zp-input" value="0" oninput="calcProfesi()">
                </div>
            </div>

            <div class="zp-form-group">
                <label class="zp-label">Kebutuhan Pokok / Cicilan Bulanan (Opsional) (Rp)</label>
                <div class="zp-input-wrap">
                    <span class="zp-prefix">Rp</span>
                    <input type="number" id="prof_living" class="zp-input" value="0" oninput="calcProfesi()">
                </div>
            </div>

            <div class="zp-result-box">
                <div class="zp-res-row">
                    <span>Nisab Beras Bulanan (54.4 kg):</span>
                    <strong id="prof_nisab_val">Rp 816.000</strong>
                </div>
                <div class="zp-res-row">
                    <span>Penghasilan Bersih Bulanan:</span>
                    <strong id="prof_net_val">Rp 0</strong>
                </div>
                <div class="zp-res-total">
                    <div>
                        <div style="font-size: 11px; color: #94A3B8;">ZAKAT PROFESI PER BULAN (2.5%)</div>
                        <div class="zp-res-val" id="prof_result_val">Rp 0</div>
                    </div>
                </div>
            </div>

            <button class="zp-btn-action" onclick="recordToDuitku('profesi')">
                <span>📝</span> Catat Pengeluaran Zakat Profesi
            </button>
        </div>
    </div>

    <!-- ── 3. TAB ZAKAT FITRAH ── -->
    <div id="tab-fitrah" class="zp-tab-content" style="display: none;">
        <div class="zp-card">
            <span class="zp-header-badge zp-badge-amber">🌾 ZAKAT FITRAH RAMADHAN</span>
            <p style="font-size: 12px; color: var(--text-secondary); margin-top: 0; margin-bottom: 16px;">
                Besaran zakat fitrah adalah <strong>2.5 kg / 3.5 liter beras</strong> per jiwa atau dikonversikan ke uang tunai sesuai ketetapan BAZNAS setempat.
            </p>

            <div class="zp-form-group">
                <label class="zp-label">Jumlah Anggota Keluarga (Jiwa)</label>
                <div class="zp-input-wrap">
                    <span class="zp-prefix">👥</span>
                    <input type="number" id="fitrah_count" class="zp-input" value="4" min="1" oninput="calcFitrah()">
                </div>
            </div>

            <div class="zp-form-group">
                <label class="zp-label">Tarif Zakat Fitrah per Jiwa (Rp)</label>
                <div class="zp-input-wrap">
                    <span class="zp-prefix">Rp</span>
                    <input type="number" id="fitrah_rate" class="zp-input" value="45000" oninput="calcFitrah()">
                </div>
            </div>

            <div class="zp-result-box">
                <div class="zp-res-row">
                    <span>Jumlah Jiwa Ditanggung:</span>
                    <strong id="fitrah_persons_val">4 Orang</strong>
                </div>
                <div class="zp-res-row">
                    <span>Estimasi Beras:</span>
                    <strong id="fitrah_rice_val">10.0 Kg</strong>
                </div>
                <div class="zp-res-total">
                    <div>
                        <div style="font-size: 11px; color: #94A3B8;">TOTAL UANG ZAKAT FITRAH</div>
                        <div class="zp-res-val" id="fitrah_result_val">Rp 180.000</div>
                    </div>
                </div>
            </div>

            <button class="zp-btn-action" onclick="recordToDuitku('fitrah')">
                <span>📝</span> Catat Pengeluaran Zakat Fitrah
            </button>
        </div>
    </div>

    <!-- ── 4. TAB KALKULATOR PAJAK ── -->
    <div id="tab-pajak" class="zp-tab-content" style="display: none;">
        <div class="zp-card">
            <span class="zp-header-badge zp-badge-purple">📊 KALKULATOR PAJAK (PPh Final UMKM / PPh 21)</span>
            
            <div style="display: flex; gap: 8px; margin-bottom: 16px;">
                <button id="tax_type_umkm" class="zp-tab-btn active" style="border: 1px solid var(--border);" onclick="setTaxType('umkm')">🏪 PPh Final UMKM (0.5%)</button>
                <button id="tax_type_pph21" class="zp-tab-btn" style="border: 1px solid var(--border);" onclick="setTaxType('pph21')">👔 PPh 21 Karyawan / Pribadi</button>
            </div>

            <!-- UMKM Form -->
            <div id="form_umkm">
                <div class="zp-form-group">
                    <label class="zp-label">Omset / Peredaran Bruto Bulanan (Rp)</label>
                    <div class="zp-input-wrap">
                        <span class="zp-prefix">Rp</span>
                        <input type="number" id="umkm_omset" class="zp-input" value="15000000" oninput="calcTax()">
                    </div>
                </div>
                <div style="font-size: 11.5px; color: var(--text-secondary); background: var(--bg); padding: 10px; border-radius: 10px; margin-bottom: 14px;">
                    ℹ️ Sesuai <strong>UU HPP No. 7/2021</strong>, Wajib Pajak Orang Pribadi UMKM mendapat fasilitas <strong>bebas pajak omset s.d Rp 500 Juta/tahun</strong>.
                </div>
            </div>

            <!-- PPh 21 Form -->
            <div id="form_pph21" style="display: none;">
                <div class="zp-form-group">
                    <label class="zp-label">Gaji Bruto Bulanan (Rp)</label>
                    <div class="zp-input-wrap">
                        <span class="zp-prefix">Rp</span>
                        <input type="number" id="pph21_salary" class="zp-input" value="10000000" oninput="calcTax()">
                    </div>
                </div>

                <div class="zp-form-group">
                    <label class="zp-label">Status PTKP (Penghasilan Tidak Kena Pajak)</label>
                    <select id="pph21_ptkp" class="zp-input" style="padding-left: 14px;" onchange="calcTax()">
                        <option value="54000000">TK/0 (Lajang Tanpa Tanggungan) - Rp 54 Jt</option>
                        <option value="58500000">TK/1 (Lajang 1 Tanggungan) - Rp 58.5 Jt</option>
                        <option value="63000000">TK/2 (Lajang 2 Tanggungan) - Rp 63 Jt</option>
                        <option value="67500000">TK/3 (Lajang 3 Tanggungan) - Rp 67.5 Jt</option>
                        <option value="58500000">K/0 (Menikah Tanpa Tanggungan) - Rp 58.5 Jt</option>
                        <option value="63000000">K/1 (Menikah 1 Tanggungan) - Rp 63 Jt</option>
                        <option value="67500000">K/2 (Menikah 2 Tanggungan) - Rp 67.5 Jt</option>
                        <option value="72000000">K/3 (Menikah 3 Tanggungan) - Rp 72 Jt</option>
                    </select>
                </div>
            </div>

            <div class="zp-result-box">
                <div class="zp-res-row">
                    <span id="tax_label_summary">Tarif Pajak Berlaku:</span>
                    <strong id="tax_rate_val">0.5% (Final UMKM)</strong>
                </div>
                <div class="zp-res-row">
                    <span>Estimasi Pajak Per Tahun:</span>
                    <strong id="tax_year_val">Rp 900.000</strong>
                </div>
                <div class="zp-res-total">
                    <div>
                        <div style="font-size: 11px; color: #94A3B8;">PAJAK WAJIB SETOR (BULANAN)</div>
                        <div class="zp-res-val" id="tax_result_val">Rp 75.000</div>
                    </div>
                </div>
            </div>

            <button class="zp-btn-action" onclick="recordToDuitku('pajak')">
                <span>📝</span> Catat Pembayaran Pajak
            </button>
        </div>
    </div>

</div>

<script>
let currentTaxMode = 'umkm';
let lastCalculatedAmounts = {
    maal: 0,
    profesi: 0,
    fitrah: 0,
    pajak: 0
};

function formatRupiah(num) {
    return 'Rp ' + Math.round(num).toLocaleString('id-ID');
}

function switchZpTab(tabId) {
    document.querySelectorAll('.zp-tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.zp-nav-tabs .zp-tab-btn').forEach(el => el.classList.remove('active'));
    
    document.getElementById('tab-' + tabId).style.display = 'block';
    event.target.classList.add('active');
}

function calcMaal() {
    const goldPrice = parseFloat(document.getElementById('maal_gold_price').value) || 0;
    const cash = parseFloat(document.getElementById('maal_cash').value) || 0;
    const other = parseFloat(document.getElementById('maal_other').value) || 0;
    const debt = parseFloat(document.getElementById('maal_debt').value) || 0;

    const nisab = 85 * goldPrice;
    const netAssets = Math.max(0, (cash + other) - debt);
    
    document.getElementById('maal_nisab_val').innerText = formatRupiah(nisab);
    document.getElementById('maal_net_val').innerText = formatRupiah(netAssets);

    if (netAssets >= nisab && nisab > 0) {
        const zakat = netAssets * 0.025;
        document.getElementById('maal_status_badge').innerText = '✅ Wajib Zakat (Mencapai Nisab)';
        document.getElementById('maal_status_badge').style.color = '#34D399';
        document.getElementById('maal_result_val').innerText = formatRupiah(zakat);
        lastCalculatedAmounts.maal = zakat;
    } else {
        document.getElementById('maal_status_badge').innerText = '⏳ Belum Wajib (Di bawah Nisab)';
        document.getElementById('maal_status_badge').style.color = '#FBBF24';
        document.getElementById('maal_result_val').innerText = 'Rp 0';
        lastCalculatedAmounts.maal = 0;
    }
}

function calcProfesi() {
    const ricePrice = parseFloat(document.getElementById('prof_rice_price').value) || 0;
    const salary = parseFloat(document.getElementById('prof_salary').value) || 0;
    const bonus = parseFloat(document.getElementById('prof_bonus').value) || 0;
    const living = parseFloat(document.getElementById('prof_living').value) || 0;

    const monthlyNisab = (653 / 12) * ricePrice;
    const netIncome = Math.max(0, (salary + bonus) - living);

    document.getElementById('prof_nisab_val').innerText = formatRupiah(monthlyNisab);
    document.getElementById('prof_net_val').innerText = formatRupiah(netIncome);

    if (netIncome >= monthlyNisab && monthlyNisab > 0) {
        const zakat = netIncome * 0.025;
        document.getElementById('prof_result_val').innerText = formatRupiah(zakat);
        lastCalculatedAmounts.profesi = zakat;
    } else {
        document.getElementById('prof_result_val').innerText = 'Rp 0';
        lastCalculatedAmounts.profesi = 0;
    }
}

function calcFitrah() {
    const count = parseInt(document.getElementById('fitrah_count').value) || 1;
    const rate = parseFloat(document.getElementById('fitrah_rate').value) || 45000;

    const totalMoney = count * rate;
    const totalRice = count * 2.5;

    document.getElementById('fitrah_persons_val').innerText = count + ' Orang';
    document.getElementById('fitrah_rice_val').innerText = totalRice.toFixed(1) + ' Kg';
    document.getElementById('fitrah_result_val').innerText = formatRupiah(totalMoney);
    lastCalculatedAmounts.fitrah = totalMoney;
}

function setTaxType(type) {
    currentTaxMode = type;
    if (type === 'umkm') {
        document.getElementById('tax_type_umkm').classList.add('active');
        document.getElementById('tax_type_pph21').classList.remove('active');
        document.getElementById('form_umkm').style.display = 'block';
        document.getElementById('form_pph21').style.display = 'none';
    } else {
        document.getElementById('tax_type_pph21').classList.add('active');
        document.getElementById('tax_type_umkm').classList.remove('active');
        document.getElementById('form_pph21').style.display = 'block';
        document.getElementById('form_umkm').style.display = 'none';
    }
    calcTax();
}

function calcTax() {
    if (currentTaxMode === 'umkm') {
        const monthlyOmset = parseFloat(document.getElementById('umkm_omset').value) || 0;
        const taxMonth = monthlyOmset * 0.005;
        const taxYear = taxMonth * 12;

        document.getElementById('tax_label_summary').innerText = 'Tarif Pajak Berlaku:';
        document.getElementById('tax_rate_val').innerText = '0.5% (Final PP 23)';
        document.getElementById('tax_year_val').innerText = formatRupiah(taxYear);
        document.getElementById('tax_result_val').innerText = formatRupiah(taxMonth);
        lastCalculatedAmounts.pajak = taxMonth;
    } else {
        const salaryMonth = parseFloat(document.getElementById('pph21_salary').value) || 0;
        const ptkp = parseFloat(document.getElementById('pph21_ptkp').value) || 54000000;

        const bruttoYear = salaryMonth * 12;
        const biayaJabatan = Math.min(6000000, bruttoYear * 0.05);
        const nettoYear = Math.max(0, bruttoYear - biayaJabatan);
        const pkp = Math.max(0, nettoYear - ptkp);

        // Tarif Progresif UU HPP
        let taxYear = 0;
        if (pkp > 0) {
            const tier1 = Math.min(pkp, 60000000);
            taxYear += tier1 * 0.05;

            if (pkp > 60000000) {
                const tier2 = Math.min(pkp - 60000000, 190000000);
                taxYear += tier2 * 0.15;
            }
            if (pkp > 250000000) {
                const tier3 = Math.min(pkp - 250000000, 250000000);
                taxYear += tier3 * 0.25;
            }
            if (pkp > 500000000) {
                const tier4 = Math.min(pkp - 500000000, 4500000000);
                taxYear += tier4 * 0.30;
            }
            if (pkp > 5000000000) {
                taxYear += (pkp - 5000000000) * 0.35;
            }
        }

        const taxMonth = taxYear / 12;
        document.getElementById('tax_label_summary').innerText = 'PKP (Kena Pajak / Thn):';
        document.getElementById('tax_rate_val').innerText = formatRupiah(pkp);
        document.getElementById('tax_year_val').innerText = formatRupiah(taxYear);
        document.getElementById('tax_result_val').innerText = formatRupiah(taxMonth);
        lastCalculatedAmounts.pajak = taxMonth;
    }
}

function recordToDuitku(type) {
    const amount = lastCalculatedAmounts[type] || 0;
    if (amount <= 0) {
        alert('Nominal kalkulasi adalah Rp 0. Masukkan angka yang sesuai terlebih dahulu.');
        return;
    }

    let note = 'Zakat Maal';
    if (type === 'profesi') note = 'Zakat Profesi / Penghasilan';
    if (type === 'fitrah') note = 'Zakat Fitrah';
    if (type === 'pajak') note = currentTaxMode === 'umkm' ? 'Pajak PPh Final UMKM 0.5%' : 'Pajak PPh 21 Pribadi';

    // Open quick transaction modal if present or redirect to activity with prefill
    if (typeof openTransactionModal === 'function') {
        openTransactionModal('expense', amount, note);
    } else {
        window.location.href = '/?add_expense=1&amount=' + Math.round(amount) + '&note=' + encodeURIComponent(note);
    }
}

// Initial Run
document.addEventListener('DOMContentLoaded', () => {
    calcMaal();
    calcProfesi();
    calcFitrah();
    calcTax();
});
</script>
<?= $this->endSection() ?>
