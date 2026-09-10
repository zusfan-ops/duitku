<?php
// Calculate tax values
$totalIncome  = $yearly['total_income'] ?? 0;
$totalExpense = $yearly['total_expense'] ?? 0;

// PPh Final UMKM
$yearlyTaxUmkm = $totalIncome * 0.005;
$monthlyTaxUmkm = $yearlyTaxUmkm / 12;
$isFree = $totalIncome <= 500000000;

// PPh 21 (simulasi TK/0)
$biayaJabatan = min(6000000, $totalIncome * 0.05);
$nettoTahunan = max(0, $totalIncome - $biayaJabatan);
$ptkp         = 54000000;
$pkp          = max(0, $nettoTahunan - $ptkp);

$taxPph21 = 0;
$brackets = [];
if ($pkp > 0) {
    $tier1 = min($pkp, 60000000);
    $taxPph21 += $tier1 * 0.05;
    $brackets[] = ['label' => '5% (s.d Rp 60 Jt)', 'amount' => $tier1, 'tax' => $tier1 * 0.05];

    if ($pkp > 60000000) {
        $tier2 = min($pkp - 60000000, 190000000);
        $taxPph21 += $tier2 * 0.15;
        $brackets[] = ['label' => '15% (Rp 60-250 Jt)', 'amount' => $tier2, 'tax' => $tier2 * 0.15];
    }
    if ($pkp > 250000000) {
        $tier3 = min($pkp - 250000000, 250000000);
        $taxPph21 += $tier3 * 0.25;
        $brackets[] = ['label' => '25% (Rp 250-500 Jt)', 'amount' => $tier3, 'tax' => $tier3 * 0.25];
    }
    if ($pkp > 500000000) {
        $tier4 = min($pkp - 500000000, 4500000000);
        $taxPph21 += $tier4 * 0.30;
        $brackets[] = ['label' => '30% (Rp 500 Jt - 5 M)', 'amount' => $tier4, 'tax' => $tier4 * 0.30];
    }
    if ($pkp > 5000000000) {
        $tier5 = $pkp - 5000000000;
        $taxPph21 += $tier5 * 0.35;
        $brackets[] = ['label' => '35% (> Rp 5 Miliar)', 'amount' => $tier5, 'tax' => $tier5 * 0.35];
    }
}

$docId = 'DK-TAX-' . $year . '-' . strtoupper(substr(md5(($userName ?? 'D') . $year), 0, 6));
$printMode = $printMode ?? false;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Pajak Tahunan <?= esc($year) ?> — DuitKu</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --green: #10B981;
    --green-dark: #059669;
    --green-light: #ECFDF5;
    --red: #EF4444;
    --red-light: #FEF2F2;
    --blue: #0284C7;
    --blue-light: #F0F9FF;
    --amber: #F59E0B;
    --amber-light: #FFFBEB;
    --purple: #7C3AED;
    --purple-light: #F5F3FF;
    --gray-50: #F8FAFC;
    --gray-100: #F1F5F9;
    --gray-200: #E2E8F0;
    --gray-500: #64748B;
    --gray-700: #334155;
    --gray-900: #0F172A;
}

body {
    font-family: 'Inter', sans-serif;
    color: var(--gray-900);
    background: #F1F5F9;
    padding: 0;
    font-size: 13.5px;
    line-height: 1.5;
}

.screen-bar {
    position: fixed;
    top: 0; left: 0; right: 0;
    background: #0F172A;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 24px;
    z-index: 1000;
    gap: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.25);
}
.screen-bar-title { font-weight: 700; font-size: 15px; flex: 1; }
.btn-screen {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: all 0.15s ease;
}
.btn-print { background: #10B981; color: #fff; }
.btn-print:hover { background: #059669; }
.btn-back {
    background: rgba(255,255,255,0.12);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.2);
}
.btn-back:hover { background: rgba(255,255,255,0.2); }

.report {
    max-width: 820px;
    margin: 70px auto 40px;
    background: #fff;
    padding: 48px;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.06);
    position: relative;
}

.report-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    border-bottom: 2px solid var(--gray-900);
    padding-bottom: 20px;
    margin-bottom: 24px;
}
.report-brand { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; color: var(--gray-900); }
.report-brand span { color: var(--green); }
.report-meta { text-align: right; color: var(--gray-500); font-size: 11.5px; line-height: 1.7; }
.report-meta strong { display: block; color: var(--gray-700); font-size: 14px; font-weight: 800; }

.summary-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 28px;
}
.summary-card {
    border-radius: 12px;
    padding: 16px 18px;
    border: 1px solid transparent;
}
.summary-card.income  { background: var(--green-light); border-color: rgba(16,185,129,0.2); }
.summary-card.expense { background: var(--red-light); border-color: rgba(239,68,68,0.2); }
.summary-card.balance { background: var(--blue-light); border-color: rgba(2,132,199,0.2); }
.summary-card.tax     { background: var(--purple-light); border-color: rgba(124,58,237,0.2); }
.summary-card.free    { background: var(--green-light); border-color: rgba(16,185,129,0.2); }
.summary-card-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}
.income  .summary-card-label { color: var(--green-dark); }
.expense .summary-card-label { color: var(--red); }
.balance .summary-card-label { color: var(--blue); }
.tax     .summary-card-label { color: var(--purple); }
.free    .summary-card-label { color: var(--green-dark); }
.summary-card-value { font-size: 18px; font-weight: 800; color: var(--gray-900); }

.section { margin-bottom: 28px; }
.section-title {
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: var(--gray-500);
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--gray-200);
}
.section-subtitle {
    font-size: 14px;
    font-weight: 800;
    color: var(--gray-900);
    margin-bottom: 6px;
}
.section-desc {
    font-size: 11.5px;
    color: var(--gray-500);
    margin-bottom: 14px;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12.5px;
}
thead th {
    background: var(--gray-50);
    text-align: left;
    padding: 9px 12px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: var(--gray-500);
    border-bottom: 1px solid var(--gray-200);
}
thead th:last-child, thead th:nth-child(2), thead th:nth-child(3) { text-align: right; }
tbody td {
    padding: 9px 12px;
    border-bottom: 1px solid var(--gray-100);
    vertical-align: middle;
}
tfoot td {
    padding: 10px 12px;
    font-weight: 800;
    border-top: 2px solid var(--gray-200);
    background: var(--gray-50);
}
.td-right { text-align: right; }
.td-zero { color: var(--gray-500); }

.info-box {
    font-size: 11.5px;
    color: var(--gray-700);
    background: var(--blue-light);
    padding: 12px 16px;
    border-radius: 10px;
    border-left: 4px solid var(--blue);
    margin-bottom: 16px;
}
.info-box.green { background: var(--green-light); border-left-color: var(--green); }
.info-box.amber { background: var(--amber-light); border-left-color: var(--amber); }
.info-box strong { color: var(--gray-900); }

.bracket-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: var(--gray-50);
    border-radius: 8px;
    margin-bottom: 6px;
    font-size: 12px;
}
.bracket-bar .rate {
    font-weight: 800;
    color: var(--purple);
    min-width: 40px;
}
.bracket-bar .amount { flex: 1; color: var(--gray-500); }
.bracket-bar .tax { font-weight: 700; color: var(--gray-900); }

.report-sign-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-top: 36px;
    padding-top: 24px;
    border-top: 1px dashed var(--gray-200);
}
.stamp-box {
    border: 2px dashed #10B981;
    border-radius: 12px;
    padding: 12px 16px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: #10B981;
    font-weight: 800;
    font-size: 11px;
    text-transform: uppercase;
}
.report-footer {
    margin-top: 30px;
    text-align: center;
    font-size: 11px;
    color: var(--gray-500);
    border-top: 1px solid var(--gray-200);
    padding-top: 16px;
}

@media print {
    body { background: #fff !important; font-size: 11pt; }
    .no-print { display: none !important; }
    .report {
        margin: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }
    @page { margin: 1.5cm; size: A4; }
}
</style>
</head>
<body>

<?php if (!$printMode): ?>
<div class="screen-bar no-print">
    <a href="/zakat-pajak" class="btn-screen btn-back">&larr; Kembali</a>
    <span class="screen-bar-title">Laporan Pajak Tahunan <?= esc($year) ?> &mdash; DuitKu</span>
    <div style="display:flex; gap:8px;">
        <a href="/tax-report/csv?year=<?= esc($year) ?>" class="btn-screen" style="background:#0284C7; color:#fff;">
            &#128229; Download CSV
        </a>
        <a href="/tax-report/print?year=<?= esc($year) ?>" class="btn-screen" style="background:#7C3AED; color:#fff;">
            &#128196; Mode Print
        </a>
        <button onclick="window.print()" class="btn-screen btn-print">
            &#128424;&#65039; Cetak / Simpan PDF
        </button>
    </div>
</div>
<?php endif; ?>

<div class="report">

    <!-- Official Report Header -->
    <div class="report-header">
        <div style="display:flex; align-items:center; gap:14px;">
            <div style="width:48px; height:48px; background:linear-gradient(135deg, #7C3AED, #5B21B6); border-radius:12px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:24px; font-weight:900; box-shadow:0 4px 10px rgba(124,58,237,0.3);">
                P
            </div>
            <div>
                <div class="report-brand">Duit<span>Ku</span> Tax Report</div>
                <div style="font-size:11px; color:var(--gray-500); letter-spacing:0.3px;">Laporan Pajak Tahunan Otomatis &mdash; Pembukuan Berbasis Transaksi</div>
            </div>
        </div>
        <div class="report-meta">
            <strong><?= esc($userName ?? 'Pengguna') ?></strong>
            <div>Tahun Pajak: <?= esc($year) ?></div>
            <div>No. Dokumen: <?= esc($docId) ?></div>
            <div style="font-size:10px; color:#7C3AED; font-weight:700;">&#10003; AUTO-GENERATED FROM LEDGER</div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card income">
            <div class="summary-card-label">Total Pemasukan <?= esc($year) ?></div>
            <div class="summary-card-value">+ <?= esc($symbol) ?> <?= number_format($totalIncome, 0, ',', '.') ?></div>
        </div>
        <div class="summary-card expense">
            <div class="summary-card-label">Total Pengeluaran <?= esc($year) ?></div>
            <div class="summary-card-value">- <?= esc($symbol) ?> <?= number_format($totalExpense, 0, ',', '.') ?></div>
        </div>
        <div class="summary-card <?= $isFree ? 'free' : 'tax' ?>">
            <div class="summary-card-label"><?= $isFree ? 'PPh Final UMKM (BEBAS)' : 'PPh Final UMKM (0.5%)' ?></div>
            <div class="summary-card-value"><?= $isFree ? 'Rp 0' : esc($symbol) . ' ' . number_format($yearlyTaxUmkm, 0, ',', '.') ?></div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- BAGIAN A: PPh Final UMKM -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="section">
        <div class="section-title">Bagian A: PPh Final UMKM (PP 55/2022 &mdash; Tarif 0.5%)</div>

        <?php if ($isFree): ?>
        <div class="info-box green">
            &#9989; <strong>Bebas Pajak.</strong> Total omset tahunan <?= esc($symbol) ?> <?= number_format($totalIncome, 0, ',', '.') ?> berada di bawah Rp 500.000.000/tahun untuk Wajib Pajak Orang Pribadi UMKM sesuai <strong>UU HPP No. 7/2021</strong>.
        </div>
        <?php else: ?>
        <div class="info-box amber">
            &#9888;&#65039; <strong>Kena PPh Final.</strong> Total omset tahunan melebihi Rp 500.000.000. PPh Final 0.5% dikenakan atas seluruh peredaran bruto.
        </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th style="text-align:right;">Omset / Peredaran Bruto</th>
                    <th style="text-align:right;">PPh Final 0.5%</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($yearly['months'] as $m): ?>
                <?php $mTax = $m['income'] * 0.005; ?>
                <tr>
                    <td style="font-weight:600;"><?= esc($m['label']) ?></td>
                    <td class="td-right <?= $m['income'] == 0 ? 'td-zero' : '' ?>"><?= $m['income'] > 0 ? esc($symbol) . ' ' . number_format($m['income'], 0, ',', '.') : '&mdash;' ?></td>
                    <td class="td-right <?= ($isFree || $m['income'] == 0) ? 'td-zero' : '' ?>"><?= (!$isFree && $m['income'] > 0) ? esc($symbol) . ' ' . number_format($mTax, 0, ',', '.') : '&mdash;' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td>TOTAL TAHUNAN</td>
                    <td class="td-right"><?= esc($symbol) ?> <?= number_format($totalIncome, 0, ',', '.') ?></td>
                    <td class="td-right"><?= $isFree ? '&mdash;' : esc($symbol) . ' ' . number_format($yearlyTaxUmkm, 0, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- BAGIAN B: Simulasi PPh 21 Pribadi -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="section">
        <div class="section-title">Bagian B: Simulasi PPh 21 Pribadi (UU HPP &mdash; Tarif Progresif)</div>
        <div class="section-desc">Perhitungan berdasarkan total penghasilan kotor tahunan dengan status PTKP <strong>TK/0</strong> (Rp 54.000.000).</div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
            <div style="background:var(--gray-50); border-radius:12px; padding:14px;">
                <div style="font-size:11px; font-weight:700; color:var(--gray-500); text-transform:uppercase; margin-bottom:4px;">Penghasilan Kotor Tahunan</div>
                <div style="font-size:16px; font-weight:800; color:var(--gray-900);"><?= esc($symbol) ?> <?= number_format($totalIncome, 0, ',', '.') ?></div>
            </div>
            <div style="background:var(--gray-50); border-radius:12px; padding:14px;">
                <div style="font-size:11px; font-weight:700; color:var(--gray-500); text-transform:uppercase; margin-bottom:4px;">Biaya Jabatan (5%, max Rp 6 Jt)</div>
                <div style="font-size:16px; font-weight:800; color:var(--gray-900);"><?= esc($symbol) ?> <?= number_format($biayaJabatan, 0, ',', '.') ?></div>
            </div>
            <div style="background:var(--blue-light); border-radius:12px; padding:14px;">
                <div style="font-size:11px; font-weight:700; color:var(--blue); text-transform:uppercase; margin-bottom:4px;">Penghasilan Netto Tahunan</div>
                <div style="font-size:16px; font-weight:800; color:var(--gray-900);"><?= esc($symbol) ?> <?= number_format($nettoTahunan, 0, ',', '.') ?></div>
            </div>
            <div style="background:var(--purple-light); border-radius:12px; padding:14px;">
                <div style="font-size:11px; font-weight:700; color:var(--purple); text-transform:uppercase; margin-bottom:4px;">PKP (Penghasilan Kena Pajak)</div>
                <div style="font-size:16px; font-weight:800; color:var(--gray-900);"><?= esc($symbol) ?> <?= number_format($pkp, 0, ',', '.') ?></div>
            </div>
        </div>

        <?php if (!empty($brackets)): ?>
        <div style="margin-bottom:16px;">
            <div style="font-size:12px; font-weight:700; color:var(--gray-700); margin-bottom:8px;">Rincian Tarif Progresif:</div>
            <?php foreach ($brackets as $br): ?>
            <div class="bracket-bar">
                <span class="rate"><?= esc($br['label']) ?></span>
                <span class="amount">PKP: <?= esc($symbol) ?> <?= number_format($br['amount'], 0, ',', '.') ?></span>
                <span class="tax">= <?= esc($symbol) ?> <?= number_format($br['tax'], 0, ',', '.') ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color: #fff; border-radius: 14px; padding: 18px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 11px; color: #94A3B8; font-weight: 700; text-transform: uppercase;">Estimasi PPh 21 Per Bulan</div>
                <div style="font-size: 24px; font-weight: 900; color: #A78BFA; margin-top: 4px;"><?= esc($symbol) ?> <?= number_format($taxPph21 / 12, 0, ',', '.') ?></div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 11px; color: #94A3B8; font-weight: 700; text-transform: uppercase;">PPh 21 Tahunan</div>
                <div style="font-size: 16px; font-weight: 800; color: #CBD5E1; margin-top: 4px;"><?= esc($symbol) ?> <?= number_format($taxPph21, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- BAGIAN C: Ringkasan Arus Kas Per Bulan -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="section">
        <div class="section-title">Bagian C: Ringkasan Arus Kas Per Bulan</div>
        <table>
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th style="text-align:right;">Pemasukan</th>
                    <th style="text-align:right;">Pengeluaran</th>
                    <th style="text-align:right;">Saldo Bersih</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($yearly['months'] as $m): ?>
                <tr>
                    <td style="font-weight:600;"><?= esc($m['label']) ?></td>
                    <td class="td-right <?= $m['income'] == 0 ? 'td-zero' : '' ?>" style="color: <?= $m['income'] > 0 ? 'var(--green-dark)' : 'inherit' ?>; font-weight: <?= $m['income'] > 0 ? '700' : '400' ?>;">
                        <?= $m['income'] > 0 ? '+ ' . esc($symbol) . ' ' . number_format($m['income'], 0, ',', '.') : '&mdash;' ?>
                    </td>
                    <td class="td-right <?= $m['expense'] == 0 ? 'td-zero' : '' ?>" style="color: <?= $m['expense'] > 0 ? 'var(--red)' : 'inherit' ?>; font-weight: <?= $m['expense'] > 0 ? '700' : '400' ?>;">
                        <?= $m['expense'] > 0 ? '- ' . esc($symbol) . ' ' . number_format($m['expense'], 0, ',', '.') : '&mdash;' ?>
                    </td>
                    <td class="td-right" style="font-weight:700; color: <?= $m['balance'] >= 0 ? 'var(--blue)' : 'var(--red)' ?>;">
                        <?= $m['balance'] >= 0 ? '+' : '-' ?> <?= esc($symbol) ?> <?= number_format(abs($m['balance']), 0, ',', '.') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td>TOTAL TAHUNAN</td>
                    <td class="td-right" style="color: var(--green-dark);">+ <?= esc($symbol) ?> <?= number_format($totalIncome, 0, ',', '.') ?></td>
                    <td class="td-right" style="color: var(--red);">- <?= esc($symbol) ?> <?= number_format($totalExpense, 0, ',', '.') ?></td>
                    <td class="td-right" style="color: <?= ($totalIncome - $totalExpense) >= 0 ? 'var(--blue)' : 'var(--red)' ?>;">
                        <?= ($totalIncome - $totalExpense) >= 0 ? '+' : '-' ?> <?= esc($symbol) ?> <?= number_format(abs($totalIncome - $totalExpense), 0, ',', '.') ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Signatures & Stamp -->
    <div class="report-sign-grid">
        <div>
            <div class="stamp-box">
                <span style="font-size:18px;">&#128737;&#65039;</span>
                <div>
                    <div>DuitKu Tax Verified</div>
                    <small style="font-size:9px; color:#059669; font-weight:600;">Auto-Generated Ledger Report</small>
                </div>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:11px; color:var(--gray-500);">Dicetak secara otomatis oleh sistem pada:</div>
            <div style="font-weight:700; font-size:12px; margin-top:3px;"><?= date('d F Y, H:i') ?> WIB</div>
            <div style="margin-top:20px; font-size:11px; font-weight:700; color:var(--gray-700);"><?= esc($userName ?? 'Pengguna') ?></div>
        </div>
    </div>

    <div class="report-footer">
        Dokumen ini diterbitkan secara otomatis oleh DuitKu Financial Application berdasarkan data transaksi yang tercatat.
        Berlaku sebagai arsip pembukuan pajak pribadi / UMKM. Perhitungan bersifat simulasi &mdash; konsultasikan dengan konsultan pajak untuk pelaporan resmi.
    </div>

</div>

</body>
</html>
