<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Keuangan <?= esc($monthLabel) ?> — DuitKu</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --green: #16A34A;
    --green-light: #DCFCE7;
    --red: #DC2626;
    --red-light: #FEE2E2;
    --blue: #2563EB;
    --blue-light: #DBEAFE;
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
    background: #fff;
    padding: 0;
    font-size: 14px;
    line-height: 1.5;
}

/* ── Screen UI ────────────────────────────────────────────────────── */
.screen-bar {
    position: fixed;
    top: 0; left: 0; right: 0;
    background: var(--gray-900);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    z-index: 100;
    gap: 12px;
}
.screen-bar-title {
    font-weight: 600;
    font-size: 15px;
    flex: 1;
}
.btn-screen {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    border-radius: 8px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
}
.btn-print {
    background: #4ADE80;
    color: #0F172A;
}
.btn-back {
    background: rgba(255,255,255,0.12);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.2);
}

/* ── Report Body ──────────────────────────────────────────────────── */
.report {
    max-width: 760px;
    margin: 0 auto;
    padding: 80px 28px 60px;
}

.report-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    border-bottom: 2px solid var(--gray-900);
    padding-bottom: 16px;
    margin-bottom: 28px;
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
    font-size: 12px;
    line-height: 1.7;
}
.report-meta strong {
    display: block;
    color: var(--gray-700);
    font-size: 14px;
    font-weight: 600;
}

/* ── Summary Cards ────────────────────────────────────────────────── */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 32px;
}
.summary-card {
    border-radius: 12px;
    padding: 16px 18px;
    position: relative;
    overflow: hidden;
}
.summary-card.income  { background: var(--green-light); }
.summary-card.expense { background: var(--red-light); }
.summary-card.balance { background: var(--blue-light); }
.summary-card-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}
.income  .summary-card-label { color: var(--green); }
.expense .summary-card-label { color: var(--red); }
.balance .summary-card-label { color: var(--blue); }
.summary-card-value {
    font-size: 18px;
    font-weight: 700;
    color: var(--gray-900);
    word-break: break-all;
}

/* ── Section Heading ──────────────────────────────────────────────── */
.section-title {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: var(--gray-500);
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--gray-200);
}

/* ── Category Table ───────────────────────────────────────────────── */
.section { margin-bottom: 32px; }

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
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
tbody tr:last-child td { border-bottom: none; }
.td-right { text-align: right; }
.td-amount { font-weight: 600; font-variant-numeric: tabular-nums; }
.td-income  { color: var(--green); }
.td-expense { color: var(--red); }
.cat-dot {
    display: inline-block;
    width: 10px; height: 10px;
    border-radius: 50%;
    margin-right: 7px;
    flex-shrink: 0;
}
.cat-name-wrap { display: flex; align-items: center; }
.type-pill {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.type-pill.income  { background: var(--green-light); color: var(--green); }
.type-pill.expense { background: var(--red-light);   color: var(--red); }

tfoot td {
    padding: 10px 12px;
    font-weight: 700;
    font-size: 13px;
    border-top: 2px solid var(--gray-200);
    background: var(--gray-50);
}

/* ── Footer ───────────────────────────────────────────────────────── */
.report-footer {
    text-align: center;
    color: var(--gray-500);
    font-size: 11px;
    margin-top: 40px;
    padding-top: 16px;
    border-top: 1px solid var(--gray-200);
}

/* ── Print ────────────────────────────────────────────────────────── */
@media print {
    .screen-bar { display: none !important; }
    .report { padding: 24px 20px; }
    body { font-size: 12px; }
    .summary-card-value { font-size: 15px; }
    thead th { background: #f3f4f6 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .summary-card { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .type-pill { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .cat-dot   { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    @page { margin: 1.5cm; size: A4 portrait; }
}
</style>
</head>
<body>

<!-- Screen-only toolbar -->
<div class="screen-bar">
    <span class="screen-bar-title">Laporan Keuangan — <?= esc($monthLabel) ?></span>
    <a href="/stats" class="btn-screen btn-back">← Kembali</a>
    <button class="btn-screen btn-print" onclick="window.print()">🖨 Cetak / Simpan PDF</button>
</div>

<div class="report">

    <!-- Header -->
    <div class="report-header">
        <div>
            <div class="report-brand">Duit<span>Ku</span></div>
            <div style="color:var(--gray-500);font-size:12px;margin-top:4px">Laporan Keuangan Pribadi</div>
        </div>
        <div class="report-meta">
            <strong><?= esc($monthLabel) ?></strong>
            <?= esc($userName) ?><br>
            Dicetak: <?= date('d/m/Y H:i') ?>
        </div>
    </div>

    <!-- Summary -->
    <div class="summary-grid">
        <div class="summary-card income">
            <div class="summary-card-label">Pemasukan</div>
            <div class="summary-card-value"><?= esc($symbol) ?> <?= number_format($monthly['income'], 0, ',', '.') ?></div>
        </div>
        <div class="summary-card expense">
            <div class="summary-card-label">Pengeluaran</div>
            <div class="summary-card-value"><?= esc($symbol) ?> <?= number_format($monthly['expense'], 0, ',', '.') ?></div>
        </div>
        <div class="summary-card balance">
            <div class="summary-card-label">Saldo Bersih</div>
            <div class="summary-card-value"><?= esc($symbol) ?> <?= number_format($monthly['balance'], 0, ',', '.') ?></div>
        </div>
    </div>

    <!-- Category Breakdown -->
    <?php
        $expenseCats = array_filter($catStats, fn($c) => $c['type'] === 'expense');
        $incomeCats  = array_filter($catStats, fn($c) => $c['type'] === 'income');
    ?>
    <?php if (!empty($expenseCats)): ?>
    <div class="section">
        <div class="section-title">Pengeluaran per Kategori</div>
        <table>
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th style="text-align:right">Jumlah</th>
                    <th style="text-align:right">% dari Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expenseCats as $cat): ?>
                <tr>
                    <td>
                        <div class="cat-name-wrap">
                            <span class="cat-dot" style="background:<?= esc($cat['color'] ?? '#94A3B8') ?>"></span>
                            <?= esc($cat['category'] ?? 'Tanpa Kategori') ?>
                        </div>
                    </td>
                    <td class="td-right td-amount td-expense"><?= esc($symbol) ?> <?= number_format($cat['total'], 0, ',', '.') ?></td>
                    <td class="td-right" style="color:var(--gray-500)">
                        <?= $monthly['expense'] > 0 ? number_format($cat['total'] / $monthly['expense'] * 100, 1) : '0' ?>%
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td>Total Pengeluaran</td>
                    <td class="td-right td-expense"><?= esc($symbol) ?> <?= number_format($monthly['expense'], 0, ',', '.') ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($incomeCats)): ?>
    <div class="section">
        <div class="section-title">Pemasukan per Kategori</div>
        <table>
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th style="text-align:right">Jumlah</th>
                    <th style="text-align:right">% dari Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incomeCats as $cat): ?>
                <tr>
                    <td>
                        <div class="cat-name-wrap">
                            <span class="cat-dot" style="background:<?= esc($cat['color'] ?? '#94A3B8') ?>"></span>
                            <?= esc($cat['category'] ?? 'Tanpa Kategori') ?>
                        </div>
                    </td>
                    <td class="td-right td-amount td-income"><?= esc($symbol) ?> <?= number_format($cat['total'], 0, ',', '.') ?></td>
                    <td class="td-right" style="color:var(--gray-500)">
                        <?= $monthly['income'] > 0 ? number_format($cat['total'] / $monthly['income'] * 100, 1) : '0' ?>%
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td>Total Pemasukan</td>
                    <td class="td-right td-income"><?= esc($symbol) ?> <?= number_format($monthly['income'], 0, ',', '.') ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>

    <!-- Transaction List -->
    <?php if (!empty($rows)): ?>
    <div class="section">
        <div class="section-title">Daftar Transaksi (<?= count($rows) ?> transaksi)</div>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Kategori</th>
                    <th>Catatan</th>
                    <th style="text-align:right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td style="white-space:nowrap;color:var(--gray-500)"><?= esc(date('d/m/Y', strtotime($row['date']))) ?></td>
                    <td>
                        <span class="type-pill <?= $row['type'] === 'income' ? 'income' : 'expense' ?>">
                            <?= $row['type'] === 'income' ? 'Masuk' : 'Keluar' ?>
                        </span>
                    </td>
                    <td>
                        <div class="cat-name-wrap">
                            <?php if (!empty($catStats)): ?>
                            <?php
                                $matchedCat = null;
                                foreach ($catStats as $cs) {
                                    if ($cs['category'] === $row['category_name']) { $matchedCat = $cs; break; }
                                }
                            ?>
                            <?php if ($matchedCat): ?>
                            <span class="cat-dot" style="background:<?= esc($matchedCat['color'] ?? '#94A3B8') ?>"></span>
                            <?php endif; ?>
                            <?php endif; ?>
                            <?= esc($row['category_name'] ?? 'Tanpa Kategori') ?>
                        </div>
                    </td>
                    <td style="color:var(--gray-500)"><?= esc($row['note'] ?? '—') ?></td>
                    <td class="td-right td-amount <?= $row['type'] === 'income' ? 'td-income' : 'td-expense' ?>">
                        <?= $row['type'] === 'income' ? '+' : '-' ?><?= esc($symbol) ?> <?= number_format($row['amount'], 0, ',', '.') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:40px;color:var(--gray-500)">
        Tidak ada transaksi pada bulan ini.
    </div>
    <?php endif; ?>

    <div class="report-footer">
        DuitKu — Aplikasi Keuangan Pribadi &nbsp;·&nbsp; <?= date('Y') ?>
    </div>

</div>

</body>
</html>
