<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Keuangan Resmi <?= esc($monthLabel) ?> — DuitKu</title>
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

/* ── Screen UI Navigation Bar ──────────────────────────────────────── */
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
.screen-bar-title {
    font-weight: 700;
    font-size: 15px;
    flex: 1;
}
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
.btn-print {
    background: #10B981;
    color: #fff;
}
.btn-print:hover { background: #059669; }
.btn-back {
    background: rgba(255,255,255,0.12);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.2);
}
.btn-back:hover { background: rgba(255,255,255,0.2); }

/* ── Report Paper Canvas ───────────────────────────────────────────── */
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
.report-brand {
    font-size: 22px;
    font-weight: 800;
    letter-spacing: -0.5px;
    color: var(--gray-900);
}
.report-brand span {
    color: var(--green);
}
.report-meta {
    text-align: right;
    color: var(--gray-500);
    font-size: 11.5px;
    line-height: 1.7;
}
.report-meta strong {
    display: block;
    color: var(--gray-700);
    font-size: 14px;
    font-weight: 800;
}

/* ── Summary Cards ────────────────────────────────────────────────── */
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
.summary-card-value {
    font-size: 18px;
    font-weight: 800;
    color: var(--gray-900);
}

/* ── Section Tables ───────────────────────────────────────────────── */
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
thead th:last-child { text-align: right; }
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
.td-income { color: var(--green-dark); font-weight: 700; }
.td-expense { color: var(--red); font-weight: 700; }

.type-pill {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 10.5px;
    font-weight: 700;
}
.type-pill.income  { background: var(--green-light); color: var(--green-dark); }
.type-pill.expense { background: var(--red-light); color: var(--red); }

/* ── Signatures & Stamp ────────────────────────────────────────────── */
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

/* ── Print Media Query ─────────────────────────────────────────────── */
@media print {
    body {
        background: #fff !important;
        font-size: 11pt;
    }
    .no-print {
        display: none !important;
    }
    .report {
        margin: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }
    @page {
        margin: 1.5cm;
        size: A4;
    }
}
</style>
</head>
<body>

<div class="screen-bar no-print">
    <a href="/stats" class="btn-screen btn-back">← Kembali</a>
    <span class="screen-bar-title">Laporan Keuangan Resmi — <?= esc($monthLabel) ?></span>
    <div style="display:flex; gap:8px;">
        <a href="/export/csv?month=<?= esc($month) ?>" class="btn-screen" style="background:#0284C7; color:#fff;">
            📥 Download Excel / CSV
        </a>
        <button onclick="window.print()" class="btn-screen btn-print">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>
</div>

<div class="report">

    <!-- Official Report Header -->
    <div class="report-header">
        <div style="display:flex; align-items:center; gap:14px;">
            <div style="width:48px; height:48px; background:linear-gradient(135deg, #10B981, #059669); border-radius:12px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:24px; font-weight:900; box-shadow:0 4px 10px rgba(16,185,129,0.3);">
                D
            </div>
            <div>
                <div class="report-brand">Duit<span>Ku</span> Financial Hub</div>
                <div style="font-size:11px; color:var(--gray-500); letter-spacing:0.3px;">Laporan Ringkasan Arus Kas &amp; Pembukuan Transaksi Resmi</div>
            </div>
        </div>
        <div class="report-meta">
            <strong><?= esc($userName ?? 'Pengguna') ?></strong>
            <div>Periode: <?= esc($monthLabel) ?></div>
            <div>No. Dokumen: DK-FIN-<?= esc(str_replace('-', '', $month)) ?>-<?= strtoupper(substr(md5(($userName ?? 'D').$month), 0, 6)) ?></div>
            <div style="font-size:10px; color:#10B981; font-weight:700;">✓ TERVERIFIKASI SISTEM</div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card income">
            <div class="summary-card-label">Total Pemasukan</div>
            <div class="summary-card-value">+ <?= esc($symbol) ?> <?= number_format($monthly['income'] ?? 0, 0, ',', '.') ?></div>
        </div>
        <div class="summary-card expense">
            <div class="summary-card-label">Total Pengeluaran</div>
            <div class="summary-card-value">- <?= esc($symbol) ?> <?= number_format($monthly['expense'] ?? 0, 0, ',', '.') ?></div>
        </div>
        <div class="summary-card balance">
            <div class="summary-card-label">Surplus / Defisit Bersih</div>
            <?php $net = ($monthly['income'] ?? 0) - ($monthly['expense'] ?? 0); ?>
            <div class="summary-card-value" style="color: <?= $net >= 0 ? '#0284C7' : '#EF4444' ?>;">
                <?= $net >= 0 ? '+' : '-' ?> <?= esc($symbol) ?> <?= number_format(abs($net), 0, ',', '.') ?>
            </div>
        </div>
    </div>

    <!-- Category Breakdown (if available) -->
    <?php if (!empty($catStats)): ?>
    <div class="section">
        <div class="section-title">Distribusi Pengeluaran Berdasarkan Kategori</div>
        <table>
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th class="td-right">Jumlah Transaksi</th>
                    <th class="td-right">Total Nominal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($catStats as $cs): ?>
                <tr>
                    <td>
                        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:<?= esc($cs['color'] ?? '#10B981') ?>; margin-right:6px;"></span>
                        <?= esc($cs['category']) ?>
                    </td>
                    <td class="td-right"><?= esc($cs['count']) ?> tx</td>
                    <td class="td-right td-expense"><?= esc($symbol) ?> <?= number_format($cs['total'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Transaction List Table -->
    <div class="section">
        <div class="section-title">Rincian Buku Kas Transaksi (<?= count($rows) ?> Transaksi)</div>
        <?php if (!empty($rows)): ?>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Kategori</th>
                    <th>Catatan / Keterangan</th>
                    <th class="td-right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td style="white-space:nowrap; color:var(--gray-500);"><?= esc(date('d/m/Y', strtotime($row['date']))) ?></td>
                    <td>
                        <span class="type-pill <?= $row['type'] === 'income' ? 'income' : 'expense' ?>">
                            <?= $row['type'] === 'income' ? 'Masuk' : 'Keluar' ?>
                        </span>
                    </td>
                    <td><?= esc($row['category_name'] ?? 'Umum') ?></td>
                    <td style="color:var(--gray-500); max-width:240px;"><?= esc($row['note'] ?: '—') ?></td>
                    <td class="td-right <?= $row['type'] === 'income' ? 'td-income' : 'td-expense' ?>">
                        <?= $row['type'] === 'income' ? '+' : '-' ?> <?= esc($symbol) ?> <?= number_format($row['amount'], 0, ',', '.') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="text-align:center; padding:30px; color:var(--gray-500);">
            Tidak ada data transaksi yang tercatat pada periode ini.
        </div>
        <?php endif; ?>
    </div>

    <!-- Official Stamp & Verification -->
    <div class="report-sign-grid">
        <div>
            <div class="stamp-box">
                <span style="font-size:18px;">🛡️</span>
                <div>
                    <div>DuitKu Verified</div>
                    <small style="font-size:9px; color:#059669; font-weight:600;">Digital Ledger Audit Passed</small>
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
        Dokumen ini diterbitkan secara resmi melalui platform DuitKu Financial Application. Berlaku sebagai arsip pembukuan keuangan sah.
    </div>

</div>

</body>
</html>
