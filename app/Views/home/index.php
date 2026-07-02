<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
/* ── Home Layout ──────────────────────────────────────────────── */
.home-page { padding-bottom: 32px; }

/* ── Hero Balance ─────────────────────────────────────────────── */
.hb-hero {
    background: linear-gradient(140deg, #043D22 0%, #076836 42%, #0AA956 100%);
    border-radius: 24px;
    padding: 24px 20px 22px;
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 28px rgba(7,104,54,.30), 0 2px 8px rgba(7,104,54,.15);
}
.hb-hero::before {
    content: '';
    position: absolute;
    top: -50px; right: -50px;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: rgba(255,255,255,.06);
    pointer-events: none;
}
.hb-hero::after {
    content: '';
    position: absolute;
    bottom: -40px; left: -10px;
    width: 140px; height: 140px;
    border-radius: 50%;
    background: rgba(255,255,255,.04);
    pointer-events: none;
}
.hb-greeting {
    font-size: 13px;
    font-weight: 600;
    color: rgba(255,255,255,.6);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.hb-balance-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .6px;
    text-transform: uppercase;
    color: rgba(255,255,255,.5);
    margin-bottom: 4px;
}
.hb-balance-amount {
    font-size: 34px;
    font-weight: 800;
    color: #fff;
    letter-spacing: -1px;
    line-height: 1;
    margin-bottom: 4px;
}
.hb-balance-sub {
    font-size: 11px;
    color: rgba(255,255,255,.4);
    margin-bottom: 16px;
}
.hb-divider {
    height: 1px;
    background: rgba(255,255,255,.12);
    margin-bottom: 14px;
}
.hb-month-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .4px;
    text-transform: uppercase;
    color: rgba(255,255,255,.35);
    margin-bottom: 10px;
}
.hb-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.hb-stat {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(0,0,0,.18);
    border-radius: 14px;
    padding: 10px 12px;
}
.hb-stat-icon {
    width: 32px; height: 32px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.hb-stat-icon.income  { background: rgba(74,222,128,.18); }
.hb-stat-icon.expense { background: rgba(248,113,113,.18); }
.hb-stat-icon svg { width: 16px; height: 16px; }
.hb-stat-icon.income  svg { stroke: #4ADE80; }
.hb-stat-icon.expense svg { stroke: #F87171; }
.hb-stat-lbl { font-size: 10px; font-weight: 600; color: rgba(255,255,255,.45); margin-bottom: 2px; }
.hb-stat-val { font-size: 14px; font-weight: 800; color: #fff; }

/* ── Wallet Strip ─────────────────────────────────────────────── */
.wallet-strip-wrap { margin-bottom: 16px; }
.wallet-strip-hdr {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 8px;
}
.wallet-strip-lbl {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; color: var(--text-muted);
}
.wallet-strip-link {
    font-size: 11px; font-weight: 700; color: var(--primary); text-decoration: none;
}
.wallet-strip {
    display: flex; gap: 10px; overflow-x: auto; padding-bottom: 4px;
    scrollbar-width: none; -ms-overflow-style: none;
}
.wallet-strip::-webkit-scrollbar { display: none; }
.w-card {
    flex-shrink: 0; width: 140px; border-radius: 18px;
    padding: 12px 14px 10px; text-decoration: none;
    transition: transform .14s ease; position: relative; overflow: hidden;
    box-shadow: 0 4px 14px rgba(0,0,0,.12);
}
.w-card::before {
    content:''; position:absolute; top:-25px; right:-25px;
    width:80px; height:80px; border-radius:50%;
    background:rgba(255,255,255,.08); pointer-events:none;
}
.w-card:active { transform: scale(.96); }
.w-card-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; }
.w-card-icon { font-size:20px; line-height:1; }
.w-card-type {
    font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.4px;
    color:rgba(255,255,255,.65); background:rgba(0,0,0,.2);
    border-radius:5px; padding:2px 6px;
}
.w-card-name {
    font-size:11px; font-weight:700; color:rgba(255,255,255,.8);
    margin-bottom:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.w-card-balance {
    font-size:13px; font-weight:800; color:#fff;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.w-card-add {
    background:var(--bg-card) !important;
    border:1.5px dashed var(--border);
    box-shadow:none;
    display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px;
}
.w-card-add::before { display:none; }
.w-card-add-icon {
    font-size:22px; color:var(--primary); font-weight:300; line-height:1;
    width:36px; height:36px; border-radius:12px;
    background:var(--primary-dim); display:flex; align-items:center; justify-content:center;
}
.w-card-add-label { font-size:11px; font-weight:700; color:var(--text-secondary); }

/* ── Daily Balance Sparkline ───────────────────────────────────── */
.daily-chart-card {
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:18px; padding:12px 14px 8px; margin-bottom:16px;
}
.daily-chart-hdr {
    display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;
}
.daily-chart-title { font-size:12px; font-weight:700; color:var(--text-primary); }
.daily-chart-month { font-size:11px; font-weight:600; color:var(--text-muted); }

/* ── Reminders ─────────────────────────────────────────────────── */
.reminder-card {
    background:var(--bg-card); border:1.5px solid #F59E0B;
    border-radius:16px; padding:12px 14px; margin-bottom:16px;
    animation: slideIn .25s ease;
}
@keyframes slideIn { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:none} }
.reminder-hdr {
    display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;
}
.reminder-title { font-size:13px; font-weight:700; color:#D97706; }
.reminder-close {
    width:22px; height:22px; border-radius:50%; background:var(--border);
    font-size:11px; color:var(--text-muted); display:flex; align-items:center; justify-content:center;
    cursor:pointer;
}
.reminder-row {
    display:flex; align-items:center; gap:10px;
    padding:7px 0; border-top:1px solid var(--border);
}
.reminder-icon { font-size:16px; flex-shrink:0; }
.reminder-body { flex:1; min-width:0; }
.reminder-name { font-size:13px; font-weight:700; color:var(--text-primary); }
.reminder-sub { font-size:11px; color:var(--text-muted); margin-top:1px; }
.reminder-badge {
    flex-shrink:0; font-size:9px; font-weight:800; letter-spacing:.4px;
    padding:3px 8px; border-radius:20px; text-transform:uppercase;
}
.reminder-badge.soon    { background:#FEF3C7; color:#D97706; }
.reminder-badge.urgent  { background:#FEE2E2; color:#DC2626; }
.reminder-badge.overdue { background:#EF4444; color:#fff; }
[data-theme="dark"] .reminder-badge.soon   { background:#3B2A0A; color:#FCD34D; }
[data-theme="dark"] .reminder-badge.urgent { background:#3B0A0A; color:#FCA5A5; }

/* ── Quick Actions (icon grid) ────────────────────────────────── */
.home-quick-actions {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 8px;
    margin-bottom: 16px;
}
.home-qa-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 12px 4px 10px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all var(--transition);
    text-decoration: none;
    position: relative;
    box-sizing: border-box;
    min-width: 0;
}
.home-qa-btn:hover, .home-qa-btn:active {
    border-color: var(--primary);
    color: var(--primary);
    background: var(--primary-dim);
}
.home-qa-btn svg { flex-shrink: 0; color: var(--primary); }
.home-qa-btn:hover svg, .home-qa-btn:active svg { color: var(--primary); }
.home-qa-icon {
    width: 36px; height: 36px;
    border-radius: 12px;
    background: var(--primary-dim);
    display: flex; align-items: center; justify-content: center;
}
.home-qa-btn .qa-badge {
    position: absolute;
    top: 6px; right: 6px;
    background: #EF4444;
    color: #fff;
    font-size: 9px;
    font-weight: 800;
    width: 16px; height: 16px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    line-height: 1;
}
.home-qa-label { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }

/* ── Calculator Modal Content ───────────────────────────────────── */
.hc-tabs {
    display: flex;
    background: var(--bg);
    border-radius: 12px;
    padding: 4px;
    gap: 2px;
    margin-bottom: 20px;
    overflow-x: auto;
    scrollbar-width: none;
}
.hc-tabs::-webkit-scrollbar { display: none; }
.hc-tab {
    flex: 1;
    padding: 9px 8px;
    border-radius: 9px;
    font-size: 12px;
    font-weight: 700;
    font-family: var(--font);
    color: var(--text-muted);
    background: transparent;
    white-space: nowrap;
    cursor: pointer;
    transition: all var(--transition);
}
.hc-tab.active {
    background: var(--bg-card);
    color: var(--primary);
    box-shadow: var(--shadow-sm);
}
.hc-tab-content { display: block; }
.hc-tab-content.hidden { display: none; }

/* form */
.hc-form-group { margin-bottom: 16px; }
.hc-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-secondary);
    margin-bottom: 6px;
}
.hc-input {
    width: 100%;
    padding: 12px 14px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    font-size: 15px;
    font-family: var(--font);
    background: var(--bg);
    color: var(--text-primary);
    transition: border-color var(--transition);
}
.hc-input:focus { outline: none; border-color: var(--primary); }
.hc-percent-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 6px;
}
.hc-pct-btn {
    padding: 9px 4px;
    background: var(--bg);
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-primary);
    cursor: pointer;
    transition: all var(--transition);
}
.hc-pct-btn:active, .hc-pct-btn.active {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}

/* result box */
.hc-result {
    background: var(--primary-dim);
    border: 1.5px dashed var(--primary);
    border-radius: var(--radius-md);
    padding: 16px;
    margin-top: 16px;
}
.hc-result.hidden { display: none; }
.hc-result-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
    margin-bottom: 6px;
    color: var(--text-secondary);
}
.hc-result-row:last-child { margin-bottom: 0; }
.hc-result-row.large {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid var(--border);
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
}
.hc-green  { color: var(--income); }
.hc-red    { color: var(--expense); }
.hc-accent { color: var(--primary); }

/* standard calculator */
.hc-std-display {
    background: #111;
    color: #fff;
    border-radius: var(--radius-md);
    padding: 20px 16px;
    font-size: 32px;
    font-weight: 700;
    text-align: right;
    min-height: 76px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    word-break: break-all;
    margin-bottom: 12px;
    letter-spacing: -0.5px;
}
[data-theme="dark"] .hc-std-display { background: #000; }
.hc-calc-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
}
.hc-btn {
    height: 58px;
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    font-size: 20px;
    font-weight: 700;
    font-family: var(--font);
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.1s ease;
    box-shadow: var(--shadow-sm);
}
.hc-btn:active { transform: scale(0.94); background: var(--bg); }
.hc-btn-danger { color: var(--expense); background: var(--expense-bg); border-color: transparent; }
.hc-btn-eq {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
    grid-row: span 2;
    height: calc(116px + 8px);
}
.hc-btn-eq:active { background: var(--primary-light); }
.hc-btn-wide { grid-column: span 2; }

/* compare */
.hc-hint {
    font-size: 12px;
    color: var(--text-muted);
    margin-bottom: 14px;
    line-height: 1.5;
}
.hc-compare-grid {
    display: flex;
    gap: 10px;
    margin-bottom: 4px;
}
.hc-compare-col {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
    background: var(--bg);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    padding: 12px;
}
.hc-compare-col strong {
    font-size: 13px;
    font-weight: 700;
    text-align: center;
    color: var(--text-primary);
}
.hc-compare-col .hc-input { background: var(--bg-card); }

/* ── Simpanan Barang ─────────────────────────────────────────────── */
.hs-badge {
    min-width: 18px; height: 18px;
    background: var(--primary);
    color: #fff;
    border-radius: 9px;
    font-size: 10px;
    font-weight: 700;
    padding: 0 5px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-left: 2px;
}
.hs-sheet { max-height: 85dvh; }
.hs-search-row {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 16px;
}
.hs-add-btn {
    width: 44px; height: 44px;
    flex-shrink: 0;
    background: var(--primary);
    color: #fff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background var(--transition);
}
.hs-add-btn:hover { background: var(--primary-light); }

/* Storage card list */
#hsListContainer { display: flex; flex-direction: column; gap: 10px; }
.hs-empty {
    text-align: center;
    padding: 40px 16px;
    color: var(--text-muted);
}
.hs-empty svg { margin: 0 auto 12px; opacity: .4; }
.hs-empty p { font-size: 14px; margin-top: 4px; }
.hs-empty strong { color: var(--text-secondary); font-size: 15px; }
.hs-card {
    background: var(--bg);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    overflow: hidden;
}
.hs-card-body { padding: 12px 14px; }
.hs-card-name {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}
.hs-card-loc {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
    color: var(--primary);
    font-weight: 600;
    margin-bottom: 4px;
}
.hs-card-notes {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 4px;
    line-height: 1.4;
}
.hs-card-thumb {
    width: 100%;
    max-height: 140px;
    object-fit: cover;
    display: block;
    border-bottom: 1.5px solid var(--border);
}
.hs-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 14px;
    border-top: 1px solid var(--border);
    gap: 8px;
}
.hs-card-date { font-size: 11px; color: var(--text-muted); }
.hs-card-actions { display: flex; gap: 6px; }
.hs-icon-btn {
    width: 30px; height: 30px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    transition: background var(--transition);
    color: var(--text-secondary);
    background: transparent;
}
.hs-icon-btn:hover { background: var(--border); }
.hs-icon-btn.danger { color: var(--expense); }
.hs-icon-btn.danger:hover { background: var(--expense-bg); }
.hs-map-btn {
    font-size: 11px;
    font-weight: 600;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 4px;
    background: none;
    padding: 2px 0;
}

/* Form modal */
.hs-form-modal { max-height: 90dvh; overflow-y: auto; max-width: 420px; }
.hs-outline-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 14px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    background: var(--bg);
    color: var(--text-primary);
    font-size: 13px;
    font-weight: 600;
    font-family: var(--font);
    cursor: pointer;
    transition: all var(--transition);
    width: 100%;
    justify-content: center;
}
.hs-outline-btn:hover { border-color: var(--primary); color: var(--primary); }
.hs-save-btn {
    padding: 12px 20px;
    background: var(--primary);
    color: #fff;
    border-radius: var(--radius-md);
    font-size: 14px;
    font-weight: 700;
    font-family: var(--font);
    cursor: pointer;
    border: none;
    transition: background var(--transition);
}
.hs-save-btn:hover { background: var(--primary-light); }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="home-page">

    <!-- ── HERO BALANCE ──────────────────────────────────────── -->
    <div class="hb-hero">
        <div class="hb-greeting">
            Halo, <?= esc(explode(' ', session()->get('user_name'))[0]) ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.5)" stroke-width="1.8" width="14" height="14"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="hb-balance-label">Total Saldo</div>
        <div class="hb-balance-amount" id="totalBalance"><?= esc($symbol) ?> <?= number_format($balance, 0, ',', '.') ?></div>
        <div class="hb-balance-sub">Akumulasi semua transaksi</div>
        <div class="hb-divider"></div>
        <div class="hb-month-label">Bulan ini · <?= esc($month) ?></div>
        <div class="hb-stats">
            <div class="hb-stat">
                <div class="hb-stat-icon income">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                </div>
                <div>
                    <div class="hb-stat-lbl">Pemasukan</div>
                    <div class="hb-stat-val"><?= esc($symbol) ?> <?= number_format($monthly['income'], 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="hb-stat">
                <div class="hb-stat-icon expense">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                </div>
                <div>
                    <div class="hb-stat-lbl">Pengeluaran</div>
                    <div class="hb-stat-val"><?= esc($symbol) ?> <?= number_format($monthly['expense'], 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── WALLET STRIP ─────────────────────────────────────── -->
    <?php if (!empty($wallets)): ?>
    <div class="wallet-strip-wrap">
        <div class="wallet-strip-hdr">
            <span class="wallet-strip-lbl">Rekening</span>
            <a href="/wallets" class="wallet-strip-link">Kelola →</a>
        </div>
        <div class="wallet-strip" id="walletStrip">
            <?php foreach ($wallets as $w):
                $hex = ltrim($w['color'],'#');
                if (strlen($hex)===3) $hex=$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                $dark = sprintf('#%02x%02x%02x',
                    (int)(hexdec(substr($hex,0,2))*.6),
                    (int)(hexdec(substr($hex,2,2))*.6),
                    (int)(hexdec(substr($hex,4,2))*.6));
            ?>
            <a href="/wallets" class="w-card" style="background:linear-gradient(135deg,<?= $dark ?> 0%,<?= esc($w['color']) ?> 100%)">
                <div class="w-card-top">
                    <span class="w-card-icon"><?= esc($w['icon']) ?></span>
                    <span class="w-card-type"><?= esc(\App\Models\WalletModel::typeLabel($w['type'])) ?></span>
                </div>
                <div class="w-card-name"><?= esc($w['name']) ?></div>
                <div class="w-card-balance"><?= esc($symbol) ?> <?= number_format($w['balance'], 0, ',', '.') ?></div>
            </a>
            <?php endforeach; ?>
            <a href="/wallets" class="w-card w-card-add">
                <div class="w-card-add-icon">+</div>
                <div class="w-card-add-label">Tambah</div>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── DAILY BALANCE SPARKLINE ──────────────────────────── -->
    <?php if (!empty($dailyBalance) && count($dailyBalance) > 1): ?>
    <div class="daily-chart-card">
        <div class="daily-chart-hdr">
            <span class="daily-chart-title">Tren Saldo Bulan Ini</span>
            <span class="daily-chart-month"><?= date('F Y') ?></span>
        </div>
        <div style="height:88px;position:relative">
            <canvas id="dailyBalanceChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── REMINDERS (bills + debts) ───────────────────────── -->
    <?php
    $hasReminder = !empty($upcomingBills) || !empty($upcomingDebts);
    if ($hasReminder):
    ?>
    <div class="reminder-card" id="reminderCard">
        <div class="reminder-hdr">
            <span class="reminder-title">⏰ Pengingat Jatuh Tempo</span>
            <button onclick="document.getElementById('reminderCard').style.display='none'" class="reminder-close">✕</button>
        </div>
        <?php foreach ($upcomingBills as $b):
            $dl = $b['daysLeft'];
            $cls = $dl <= 0 ? 'overdue' : ($dl <= 1 ? 'urgent' : 'soon');
            $dlLabel = $dl <= 0 ? 'LEWAT' : ($dl === 0 ? 'HARI INI' : ($dl === 1 ? 'BESOK' : $dl.' hari'));
        ?>
        <div class="reminder-row">
            <span class="reminder-icon">📋</span>
            <div class="reminder-body">
                <div class="reminder-name"><?= esc($b['name']) ?></div>
                <div class="reminder-sub">Tagihan · tgl <?= (int)$b['dueDay'] ?></div>
            </div>
            <span class="reminder-badge <?= $cls ?>"><?= $dlLabel ?></span>
        </div>
        <?php endforeach; ?>
        <?php foreach ($upcomingDebts as $d):
            $dl = $d['daysLeft'];
            $cls = $dl <= 0 ? 'overdue' : ($dl <= 1 ? 'urgent' : 'soon');
            $dlLabel = $dl <= 0 ? 'LEWAT' : ($dl === 0 ? 'HARI INI' : ($dl === 1 ? 'BESOK' : $dl.' hari'));
        ?>
        <div class="reminder-row">
            <span class="reminder-icon"><?= $d['type'] === 'hutang' ? '💸' : '💰' ?></span>
            <div class="reminder-body">
                <div class="reminder-name"><?= esc($d['person']) ?></div>
                <div class="reminder-sub"><?= $d['type'] === 'hutang' ? 'Bayar hutang' : 'Tagih piutang' ?> · <?= esc($symbol) ?> <?= number_format($d['amount'] - $d['paid'], 0, ',', '.') ?></div>
            </div>
            <span class="reminder-badge <?= $cls ?>"><?= $dlLabel ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- BUDGET PROGRESS BAR -->
    <?php if ($budget > 0):
        $pct   = $budgetPct;
        $cls   = $pct >= 100 ? 'over' : ($pct >= 80 ? 'warning' : 'safe');
        $sisa  = max($budget - $monthly['expense'], 0);
    ?>
    <div class="budget-card">
        <div class="budget-header">
            <span class="budget-title">🎯 Budget Bulan Ini</span>
            <span class="budget-amounts">
                <strong><?= esc($symbol) ?> <?= number_format($monthly['expense'], 0, ',', '.') ?></strong>
                / <?= esc($symbol) ?> <?= number_format($budget, 0, ',', '.') ?>
            </span>
        </div>
        <div class="budget-bar-wrap">
            <div class="budget-bar <?= $cls ?>" style="width:<?= min($pct, 100) ?>%"></div>
        </div>
        <div class="budget-footer">
            <span class="budget-remaining">
                <?php if ($pct >= 100): ?>
                    ⚠️ Over budget <?= esc($symbol) ?> <?= number_format($monthly['expense'] - $budget, 0, ',', '.') ?>
                <?php else: ?>
                    Sisa <?= esc($symbol) ?> <?= number_format($sisa, 0, ',', '.') ?>
                <?php endif; ?>
            </span>
            <span class="budget-pct <?= $cls ?>"><?= number_format($pct, 0) ?>%</span>
        </div>
    </div>
    <?php endif; ?>

    <!-- QUICK ACTIONS -->
    <div class="home-quick-actions">
        <button class="home-qa-btn" id="btnOpenCalc">
            <div class="home-qa-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="18" height="18"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="10" y2="10"/><line x1="12" y1="10" x2="14" y2="10"/><line x1="16" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="10" y2="14"/><line x1="12" y1="14" x2="14" y2="14"/><line x1="16" y1="14" x2="16" y2="18"/><line x1="14" y1="16" x2="18" y2="16"/></svg>
            </div>
            <span class="home-qa-label">Kalkulator</span>
        </button>
        <button class="home-qa-btn" id="btnOpenStorage">
            <div class="home-qa-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="18" height="18"><circle cx="12" cy="8" r="7"/><path d="M8.56 2.75c4.37 6.03 6.02 9.42 8.03 17.72m2.54-15.38c-3.72 4.35-8.94 5.66-16.88 5.85m19.5 1.9c-3.5-.93-6.63-.82-8.94 0-2.58.92-5.01 2.86-7.44 6.32"/></svg>
            </div>
            <span class="home-qa-label">Simpanan</span>
            <span class="qa-badge" id="hsBadge" style="display:none"></span>
        </button>
        <button class="home-qa-btn" id="btnOpenBills">
            <div class="home-qa-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="18" height="18"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M4 9h16M8 3v4M16 3v4"/><line x1="8" y1="13" x2="10" y2="13"/><line x1="12" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="10" y2="17"/></svg>
            </div>
            <span class="home-qa-label">Tagihan</span>
            <span class="qa-badge" id="billDueBadge" style="display:none"></span>
        </button>
        <button class="home-qa-btn" id="btnOpenNote">
            <div class="home-qa-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="18" height="18"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </div>
            <span class="home-qa-label">Catatan</span>
        </button>
        <a href="/hutang" class="home-qa-btn">
            <div class="home-qa-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="18" height="18"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <span class="home-qa-label">Hutang</span>
            <?php if (isset($debtSummary) && $debtSummary['active_count'] > 0): ?>
            <span class="qa-badge"><?= $debtSummary['active_count'] ?></span>
            <?php endif; ?>
        </a>
    </div>

    <!-- DUE BILLS BANNER (JS-rendered) -->
    <div id="dueBillsBanner" style="display:none"></div>

    <!-- SAVINGS GOAL CARD -->
    <?php if ($savingsTarget > 0): ?>
    <div class="savings-card" id="savingsCard">
        <div class="savings-header">
            <span class="savings-icon">🎯</span>
            <div class="savings-info">
                <div class="savings-title"><?= esc($savingsName ?: 'Target Menabung') ?></div>
                <div class="savings-amounts">
                    <strong><?= esc($symbol) ?> <?= number_format($savingsSaved, 0, ',', '.') ?></strong>
                    / <?= esc($symbol) ?> <?= number_format($savingsTarget, 0, ',', '.') ?>
                </div>
            </div>
            <button class="savings-edit-btn" id="btnEditSavings" title="Edit target">✏️</button>
        </div>
        <div class="savings-bar-wrap">
            <div class="savings-bar" style="width:<?= number_format(min($savingsPct, 100), 1) ?>%"></div>
        </div>
        <div class="savings-footer">
            <span><?= $savingsPct >= 100 ? '🎉 Target tercapai!' : 'Sisa ' . esc($symbol) . ' ' . number_format(max($savingsTarget - $savingsSaved, 0), 0, ',', '.') ?></span>
            <span class="savings-pct"><?= number_format($savingsPct, 0) ?>%</span>
        </div>
    </div>
    <?php endif; ?>

    <!-- MONTHLY NOTE PREVIEW -->
    <?php if ($monthNote): ?>
    <div class="note-preview-card" id="notePreviewCard" onclick="document.getElementById('btnOpenNote').click()">
        <div class="note-preview-label">📝 Catatan <?= date('F Y') ?></div>
        <div class="note-preview-text"><?= nl2br(esc(mb_substr($monthNote, 0, 120))) ?><?= mb_strlen($monthNote) > 120 ? '…' : '' ?></div>
    </div>
    <?php endif; ?>

    <!-- DEBT SUMMARY CARD -->
    <?php if ($debtSummary['active_count'] > 0): ?>
    <a href="/hutang" style="text-decoration:none;display:block;margin-bottom:12px">
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:18px;padding:14px 16px;display:flex;align-items:center;gap:14px;transition:border-color var(--transition)" onmouseenter="this.style.borderColor='var(--primary)'" onmouseleave="this.style.borderColor='var(--border)'">
            <div style="width:40px;height:40px;background:var(--primary-dim);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" width="20" height="20"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:12px;font-weight:700;color:var(--text-secondary);margin-bottom:6px">Hutang &amp; Piutang</div>
                <div style="display:flex;gap:16px">
                    <div>
                        <div style="font-size:10px;color:var(--text-muted);margin-bottom:2px">Hutang</div>
                        <div style="font-size:14px;font-weight:800;color:#EF4444"><?= esc($symbol) ?> <?= number_format($debtSummary['total_hutang'], 0, ',', '.') ?></div>
                    </div>
                    <div style="width:1px;background:var(--border)"></div>
                    <div>
                        <div style="font-size:10px;color:var(--text-muted);margin-bottom:2px">Piutang</div>
                        <div style="font-size:14px;font-weight:800;color:#22C55E"><?= esc($symbol) ?> <?= number_format($debtSummary['total_piutang'], 0, ',', '.') ?></div>
                    </div>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0">
                <span style="background:#EF444415;color:#EF4444;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px"><?= $debtSummary['active_count'] ?> aktif</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2.2" stroke-linecap="round" width="14" height="14"><path d="M9 18l6-6-6-6"/></svg>
            </div>
        </div>
    </a>
    <?php endif; ?>

    <!-- RECENT ACTIVITY -->
    <div class="section-header">
        <h2 class="section-title">Aktivitas Terbaru</h2>
        <a href="/activity" style="font-size:12px;font-weight:700;color:var(--primary);text-decoration:none"><?= count($recent) ?> transaksi →</a>
    </div>

    <?php if (empty($recent)): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                <line x1="9" y1="7" x2="15" y2="7"/><line x1="9" y1="11" x2="15" y2="11"/>
                <line x1="9" y1="15" x2="11" y2="15"/>
            </svg>
        </div>
        <p class="empty-title">Belum ada transaksi</p>
        <p class="empty-sub">Tekan tombol + untuk mencatat transaksi pertama.</p>
    </div>
    <?php else: ?>
    <div class="tx-list" id="recentList">
        <?php foreach ($recent as $tx): ?>
        <div class="tx-item" data-id="<?= $tx['id'] ?>" data-tx='<?= json_encode($tx) ?>'>
            <div class="tx-icon" style="background:<?= esc($tx['category_color'] ?? '#6B7280') ?>20;color:<?= esc($tx['category_color'] ?? '#6B7280') ?>">
                <?= categoryIcon($tx['category_icon'] ?? 'other') ?>
            </div>
            <div class="tx-body">
                <div class="tx-name"><?= esc($tx['category_name'] ?? 'Tanpa Kategori') ?></div>
                <div class="tx-note">
                    <?= esc($tx['note'] ?? '') ?>
                    <?php if (!empty($tx['image'])): ?>
                        <span title="Ada Foto" style="margin-left:4px; opacity:0.6">📷</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="tx-right">
                <div class="tx-amount <?= $tx['type'] === 'income' ? 'income' : 'expense' ?>">
                    <?= $tx['type'] === 'income' ? '+' : '-' ?> <?= esc($symbol) ?> <?= number_format($tx['amount'], 0, ',', '.') ?>
                </div>
                <div class="tx-date"><?= date('d M', strtotime($tx['date'])) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (count($recent) >= 15): ?>
    <a href="/activity" class="see-all-link">Lihat semua aktivitas →</a>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- ═══════════════════════ CALCULATOR MODAL ═══════════════════════ -->
<div class="modal-overlay" id="calcModalOverlay">
    <div class="modal-sheet" id="calcModal">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3>Kalkulator</h3>
            <button class="modal-close" id="calcModalClose">✕</button>
        </div>

        <!-- Tabs -->
        <div class="hc-tabs">
            <button class="hc-tab active" data-tab="discount">Diskon %</button>
            <button class="hc-tab" data-tab="standard">Hitung</button>
            <button class="hc-tab" data-tab="change">Kembalian</button>
            <button class="hc-tab" data-tab="compare">Banding</button>
        </div>

        <!-- Tab: Diskon % -->
        <div class="hc-tab-content" id="htab-discount">
            <div class="hc-form-group">
                <label class="hc-label">Harga Asli (<?= esc($symbol) ?>)</label>
                <input type="number" id="hc-price" class="hc-input" placeholder="Contoh: 150000" inputmode="numeric">
            </div>
            <div class="hc-form-group">
                <label class="hc-label">Potongan (%)</label>
                <div class="hc-percent-grid">
                    <button class="hc-pct-btn" data-v="5">5%</button>
                    <button class="hc-pct-btn" data-v="10">10%</button>
                    <button class="hc-pct-btn" data-v="15">15%</button>
                    <button class="hc-pct-btn" data-v="20">20%</button>
                    <button class="hc-pct-btn" data-v="25">25%</button>
                    <button class="hc-pct-btn" data-v="50">50%</button>
                </div>
                <input type="number" id="hc-percent" class="hc-input" placeholder="Atau isi manual %" inputmode="numeric" style="margin-top:10px">
            </div>
            <div id="hc-disc-result" class="hc-result hidden">
                <div class="hc-result-row">
                    <span>Hemat:</span>
                    <strong id="hc-savings" class="hc-green">Rp 0</strong>
                </div>
                <div class="hc-result-row large">
                    <span>Harga Bayar:</span>
                    <strong id="hc-final" class="hc-accent">Rp 0</strong>
                </div>
            </div>
        </div>

        <!-- Tab: Standard -->
        <div class="hc-tab-content hidden" id="htab-standard">
            <div class="hc-std-display" id="hc-std-display">0</div>
            <div class="hc-calc-grid">
                <button class="hc-btn hc-btn-danger" data-op="clear">C</button>
                <button class="hc-btn" data-op="/">÷</button>
                <button class="hc-btn" data-op="*">×</button>
                <button class="hc-btn hc-btn-danger" data-op="back">⌫</button>
                <button class="hc-btn" data-num="7">7</button>
                <button class="hc-btn" data-num="8">8</button>
                <button class="hc-btn" data-num="9">9</button>
                <button class="hc-btn" data-op="-">−</button>
                <button class="hc-btn" data-num="4">4</button>
                <button class="hc-btn" data-num="5">5</button>
                <button class="hc-btn" data-num="6">6</button>
                <button class="hc-btn" data-op="+">+</button>
                <button class="hc-btn" data-num="1">1</button>
                <button class="hc-btn" data-num="2">2</button>
                <button class="hc-btn" data-num="3">3</button>
                <button class="hc-btn hc-btn-eq" data-op="=">=</button>
                <button class="hc-btn hc-btn-wide" data-num="0">0</button>
                <button class="hc-btn" data-num=".">.</button>
            </div>
        </div>

        <!-- Tab: Kembalian -->
        <div class="hc-tab-content hidden" id="htab-change">
            <div class="hc-form-group">
                <label class="hc-label">Total Belanja (<?= esc($symbol) ?>)</label>
                <input type="number" id="hc-total" class="hc-input" placeholder="Contoh: 85000" inputmode="numeric">
            </div>
            <div class="hc-form-group">
                <label class="hc-label">Uang Dibayar (<?= esc($symbol) ?>)</label>
                <input type="number" id="hc-paid" class="hc-input" placeholder="Contoh: 100000" inputmode="numeric">
            </div>
            <div id="hc-change-result" class="hc-result hidden">
                <div class="hc-result-row large">
                    <span id="hc-change-label">Kembalian:</span>
                    <strong id="hc-change-val" class="hc-accent">Rp 0</strong>
                </div>
            </div>
        </div>

        <!-- Tab: Banding Harga -->
        <div class="hc-tab-content hidden" id="htab-compare">
            <p class="hc-hint">Bandingkan harga 2 produk. Isi harga &amp; jumlah/volume satuan yang sama (gram, ml, buah, dll).</p>
            <div class="hc-compare-grid">
                <div class="hc-compare-col">
                    <strong>Produk A</strong>
                    <input type="number" id="hc-a-price" class="hc-input" placeholder="Harga (<?= esc($symbol) ?>)" inputmode="numeric">
                    <input type="number" id="hc-a-qty" class="hc-input" placeholder="Jumlah / isi" inputmode="decimal">
                </div>
                <div class="hc-compare-col">
                    <strong>Produk B</strong>
                    <input type="number" id="hc-b-price" class="hc-input" placeholder="Harga (<?= esc($symbol) ?>)" inputmode="numeric">
                    <input type="number" id="hc-b-qty" class="hc-input" placeholder="Jumlah / isi" inputmode="decimal">
                </div>
            </div>
            <div id="hc-compare-result" class="hc-result hidden">
                <div class="hc-result-row"><span>Harga/satuan A:</span><strong id="hc-a-unit">-</strong></div>
                <div class="hc-result-row"><span>Harga/satuan B:</span><strong id="hc-b-unit">-</strong></div>
                <div class="hc-result-row large"><span>Lebih hemat:</span><strong id="hc-winner" class="hc-green">-</strong></div>
            </div>
        </div>

    </div><!-- .modal-sheet -->
</div><!-- .modal-overlay -->

<!-- ══════════════════════ BILLS MODAL ══════════════════════ -->
<div class="modal-overlay" id="billsModalOverlay">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3>📋 Tagihan Rutin</h3>
            <button class="modal-close" id="billsModalClose">✕</button>
        </div>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:14px">Catat tagihan bulanan agar tidak lupa jatuh tempo.</p>
        <div id="billsList"></div>
        <button class="btn-save" id="btnAddBill" style="margin-top:12px">+ Tambah Tagihan</button>
    </div>
</div>

<!-- BILL FORM MODAL -->
<div class="mini-modal-overlay" id="billFormOverlay">
    <div class="mini-modal">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
            <h3 id="billFormTitle" style="font-size:16px;font-weight:700">Tambah Tagihan</h3>
            <button class="modal-close" id="billFormClose">✕</button>
        </div>
        <form id="billForm">
            <input type="hidden" id="billId">
            <div class="hc-form-group">
                <label class="hc-label">Nama Tagihan *</label>
                <input type="text" id="billName" class="hc-input" placeholder="Listrik, Air, Internet…" required>
            </div>
            <div class="hc-form-group">
                <label class="hc-label">Nominal (<?= esc($symbol) ?>)</label>
                <input type="number" id="billAmount" class="hc-input" placeholder="0" inputmode="numeric">
            </div>
            <div class="hc-form-group">
                <label class="hc-label">Jatuh Tempo (tanggal ke-) *</label>
                <input type="number" id="billDueDay" class="hc-input" placeholder="1–31" min="1" max="31" required inputmode="numeric">
            </div>
            <div class="hc-form-group">
                <label class="hc-label">Catatan</label>
                <input type="text" id="billNotes" class="hc-input" placeholder="Opsional">
            </div>
            <div style="display:flex;gap:8px;margin-top:4px">
                <button type="button" id="billCancelBtn" class="hs-outline-btn" style="flex:1">Batal</button>
                <button type="submit" class="hs-save-btn" style="flex:2">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════ SAVINGS GOAL MODAL ══════════════════ -->
<div class="mini-modal-overlay" id="savingsModalOverlay">
    <div class="mini-modal">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
            <h3 style="font-size:16px;font-weight:700">🎯 Target Menabung</h3>
            <button class="modal-close" id="savingsModalClose">✕</button>
        </div>
        <div class="hc-form-group">
            <label class="hc-label">Nama Target</label>
            <input type="text" id="savingsNameInput" class="hc-input" placeholder="Beli motor, Liburan, Dana darurat…" value="<?= esc($savingsName) ?>">
        </div>
        <div class="hc-form-group">
            <label class="hc-label">Target Nominal (<?= esc($symbol) ?>)</label>
            <input type="text" id="savingsTargetInput" class="hc-input" placeholder="0" value="<?= $savingsTarget > 0 ? number_format($savingsTarget, 0, ',', '.') : '' ?>" inputmode="numeric">
        </div>
        <div class="hc-form-group">
            <label class="hc-label">Sudah Tersimpan (<?= esc($symbol) ?>)</label>
            <input type="text" id="savingsSavedInput" class="hc-input" placeholder="0" value="<?= $savingsSaved > 0 ? number_format($savingsSaved, 0, ',', '.') : '' ?>" inputmode="numeric">
        </div>
        <div style="display:flex;gap:8px;margin-top:4px">
            <button type="button" id="savingsCancelBtn" class="hs-outline-btn" style="flex:1">Batal</button>
            <button type="button" id="savingsSaveBtn" class="hs-save-btn" style="flex:2">Simpan</button>
        </div>
    </div>
</div>

<!-- ═════════════════ MONTHLY NOTE MODAL ═════════════════ -->
<div class="modal-overlay" id="noteModalOverlay">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3>📝 Catatan — <?= date('F Y') ?></h3>
            <button class="modal-close" id="noteModalClose">✕</button>
        </div>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:12px">Rencana anggaran, target keuangan, atau catatan bebas bulan ini.</p>
        <textarea id="noteTextarea" class="hc-input" rows="8" placeholder="Tulis catatan keuangan bulan ini…" style="resize:vertical;line-height:1.6"><?= esc($monthNote) ?></textarea>
        <div style="display:flex;gap:8px;margin-top:12px">
            <button type="button" id="noteCancelBtn" class="hs-outline-btn" style="flex:1">Batal</button>
            <button type="button" id="noteSaveBtn" class="hs-save-btn" style="flex:2">Simpan</button>
        </div>
    </div>
</div>

<!-- ════════════════════ STORAGE LIST MODAL ════════════════════ -->
<div class="modal-overlay" id="storageListOverlay">
    <div class="modal-sheet hs-sheet">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3>📦 Simpanan Barang</h3>
            <button class="modal-close" id="storageListClose">✕</button>
        </div>

        <!-- Search + Add -->
        <div class="hs-search-row">
            <input type="search" id="hsSearch" class="hc-input" placeholder="Cari nama barang atau tempat…" style="flex:1">
            <button class="hs-add-btn" id="hsBtnAdd" title="Tambah barang">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
            </button>
        </div>

        <!-- Item list -->
        <div id="hsListContainer"></div>
    </div>
</div>

<!-- ════════════════════ STORAGE FORM MODAL ════════════════════ -->
<div class="mini-modal-overlay" id="storageFormOverlay">
    <div class="mini-modal hs-form-modal">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <h3 id="hsFormTitle" style="font-size:16px;font-weight:700">Tambah Simpanan</h3>
            <button class="modal-close" id="storageFormClose">✕</button>
        </div>
        <form id="hsForm">
            <input type="hidden" id="hsId">
            <div class="hc-form-group">
                <label class="hc-label">Nama Barang *</label>
                <input type="text" id="hsName" class="hc-input" placeholder="Contoh: Kunci motor, Ijazah…" required>
            </div>
            <div class="hc-form-group">
                <label class="hc-label">Tempat Penyimpanan *</label>
                <input type="text" id="hsLocation" class="hc-input" placeholder="Contoh: Laci meja kamar, Lemari atas…" required>
            </div>
            <div class="hc-form-group">
                <label class="hc-label">Catatan (opsional)</label>
                <textarea id="hsNotes" class="hc-input" rows="2" placeholder="Detail tambahan, warna kotak, dsb…" style="resize:none"></textarea>
            </div>
            <div class="hc-form-group">
                <label class="hc-label">Foto (opsional)</label>
                <input type="file" id="hsImageInput" accept="image/*" capture="environment" style="display:none">
                <button type="button" id="hsBtnPhoto" class="hs-outline-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    Ambil / Pilih Foto
                </button>
                <div id="hsImagePreview" style="display:none;margin-top:8px;position:relative;width:fit-content">
                    <img id="hsPreviewImg" src="" alt="" style="max-width:100%;max-height:120px;border-radius:10px;border:1.5px solid var(--border)">
                    <button type="button" id="hsBtnRemovePhoto" style="position:absolute;top:-8px;right:-8px;background:var(--expense);color:#fff;border-radius:50%;width:22px;height:22px;font-size:12px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.25)">✕</button>
                </div>
            </div>
            <div class="hc-form-group">
                <label class="hc-label">Lokasi GPS (opsional)</label>
                <button type="button" id="hsBtnGps" class="hs-outline-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Ambil Lokasi Sekarang
                </button>
                <div id="hsGpsPreview" style="display:none;margin-top:8px;padding:8px 12px;background:var(--primary-dim);border:1px solid var(--primary);border-radius:10px;align-items:center;gap:8px;font-size:12px;color:var(--primary)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="flex-shrink:0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span id="hsGpsText">-</span>
                    <button type="button" id="hsBtnRemoveGps" style="margin-left:auto;color:var(--text-muted);font-size:14px;line-height:1">✕</button>
                </div>
            </div>
            <div style="display:flex;gap:8px;margin-top:4px">
                <button type="button" id="hsBtnCancelForm" class="hs-outline-btn" style="flex:1">Batal</button>
                <button type="submit" class="hs-save-btn" style="flex:2">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
/* ── Daily Balance Sparkline ──────────────────────────────────── */
(function() {
    const dailyData = <?= json_encode($dailyBalance ?? []) ?>;
    const ctx = document.getElementById('dailyBalanceChart');
    if (!dailyData.length || !ctx) return;
    const isDark  = document.documentElement.getAttribute('data-theme') === 'dark';
    const primary = '#0AA956';
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dailyData.map(d => d.d),
            datasets: [{
                data: dailyData.map(d => d.b),
                borderColor: primary,
                backgroundColor: isDark ? 'rgba(10,169,86,.08)' : 'rgba(10,169,86,.12)',
                borderWidth: 2, tension: 0.35, fill: true,
                pointRadius: 0, pointHoverRadius: 5,
                pointHoverBackgroundColor: primary,
                pointHoverBorderColor: '#fff', pointHoverBorderWidth: 2,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#162032' : '#fff',
                    borderColor:     isDark ? '#1E3050' : '#DDE3EC',
                    borderWidth: 1,
                    titleColor: isDark ? '#F1F5F9' : '#0F172A',
                    bodyColor:  isDark ? '#CBD5E1' : '#475569',
                    padding: 8,
                    callbacks: {
                        title: (ctx) => 'Tgl ' + ctx[0].label,
                        label: (ctx) => ' <?= esc($symbol) ?> ' + Math.round(ctx.parsed.y).toLocaleString('id-ID'),
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false }, border: { display: false },
                    ticks: { color: isDark ? '#64748B' : '#94A3B8', font: { size: 10, family: 'Inter' }, maxTicksLimit: 10 }
                },
                y: { display: false }
            }
        }
    });
})();

/* ── Notification reminder ────────────────────────────────────── */
(function() {
    if (!('Notification' in window)) return;
    const upcomingBills = <?= json_encode(array_values($upcomingBills ?? [])) ?>;
    const upcomingDebts = <?= json_encode(array_values($upcomingDebts ?? [])) ?>;
    const urgent = [
        ...upcomingBills.filter(b => b.daysLeft <= 1),
        ...upcomingDebts.filter(d => d.daysLeft <= 1),
    ];
    if (!urgent.length) return;
    const today    = new Date().toISOString().slice(0, 10);
    const lastSent = localStorage.getItem('duitku_notif_date');
    if (lastSent === today) return;

    function sendNotif() {
        localStorage.setItem('duitku_notif_date', today);
        const parts = [];
        upcomingBills.filter(b => b.daysLeft <= 1).forEach(b => parts.push('📋 ' + b.name + ' (tgl ' + b.dueDay + ')'));
        upcomingDebts.filter(d => d.daysLeft <= 1).forEach(d => {
            parts.push((d.type === 'hutang' ? '💸 Bayar hutang: ' : '💰 Tagih piutang: ') + d.person);
        });
        try {
            new Notification('DuitKu — Pengingat', { body: parts.join('\n'), icon: '/images/logo.png' });
        } catch(e) {}
    }

    if (Notification.permission === 'granted') {
        sendNotif();
    } else if (Notification.permission === 'default') {
        setTimeout(() => Notification.requestPermission().then(p => { if (p === 'granted') sendNotif(); }), 2000);
    }
})();

/* ── Calculator modal + home interactions ────────────────────── */
(function() {
    'use strict';

    // ── Elements ───────────────────────────────────────────────────
    const overlay   = document.getElementById('calcModalOverlay');
    const btnOpen   = document.getElementById('btnOpenCalc');
    const btnClose  = document.getElementById('calcModalClose');

    // Discount tab
    const hcPrice   = document.getElementById('hc-price');
    const hcPercent = document.getElementById('hc-percent');
    const discResult= document.getElementById('hc-disc-result');
    const hcSavings = document.getElementById('hc-savings');
    const hcFinal   = document.getElementById('hc-final');

    // Standard tab
    const stdDisplay= document.getElementById('hc-std-display');
    let   stdExpr   = '';

    // Change tab
    const hcTotal   = document.getElementById('hc-total');
    const hcPaid    = document.getElementById('hc-paid');
    const chgResult = document.getElementById('hc-change-result');
    const chgLabel  = document.getElementById('hc-change-label');
    const chgVal    = document.getElementById('hc-change-val');

    // Compare tab
    const cmpAPrice = document.getElementById('hc-a-price');
    const cmpAQty   = document.getElementById('hc-a-qty');
    const cmpBPrice = document.getElementById('hc-b-price');
    const cmpBQty   = document.getElementById('hc-b-qty');
    const cmpResult = document.getElementById('hc-compare-result');
    const cmpAUnit  = document.getElementById('hc-a-unit');
    const cmpBUnit  = document.getElementById('hc-b-unit');
    const cmpWinner = document.getElementById('hc-winner');

    const symbol = '<?= esc($symbol) ?>';

    function fmtRp(n) {
        return symbol + ' ' + Math.round(n).toLocaleString('id-ID');
    }

    // ── Open / Close ───────────────────────────────────────────────
    function openCalc() {
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeCalc() {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    btnOpen.addEventListener('click', openCalc);
    btnClose.addEventListener('click', closeCalc);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeCalc(); });

    // ── Tab switching ──────────────────────────────────────────────
    document.querySelectorAll('.hc-tab').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.hc-tab').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.hc-tab-content').forEach(c => c.classList.add('hidden'));
            btn.classList.add('active');
            document.getElementById('htab-' + btn.dataset.tab).classList.remove('hidden');
        });
    });

    // ── Discount % ─────────────────────────────────────────────────
    function updateDiscount() {
        const price   = parseFloat(hcPrice.value)   || 0;
        const percent = parseFloat(hcPercent.value) || 0;
        if (price > 0 && percent > 0) {
            const savings = price * percent / 100;
            hcSavings.textContent = fmtRp(savings);
            hcFinal.textContent   = fmtRp(price - savings);
            discResult.classList.remove('hidden');
        } else {
            discResult.classList.add('hidden');
        }
    }
    hcPrice.addEventListener('input', updateDiscount);
    hcPercent.addEventListener('input', updateDiscount);
    document.querySelectorAll('.hc-pct-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.hc-pct-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            hcPercent.value = btn.dataset.v;
            updateDiscount();
        });
    });

    // ── Standard Calculator ────────────────────────────────────────
    function renderStd() {
        stdDisplay.textContent = stdExpr || '0';
    }
    function stdAppend(ch) {
        if (stdExpr === '0' && ch !== '.') stdExpr = ch;
        else stdExpr += ch;
        renderStd();
    }
    function stdOp(op) {
        if (op === 'clear') { stdExpr = ''; }
        else if (op === 'back') { stdExpr = stdExpr.slice(0, -1); }
        else if (op === '=') {
            try {
                const safe = stdExpr.replace(/[^-+/*0-9.]/g, '');
                // eslint-disable-next-line no-eval
                stdExpr = String(eval(safe));
                if (stdExpr === 'Infinity' || stdExpr === 'NaN') stdExpr = 'Error';
            } catch(e) { stdExpr = 'Error'; }
        } else {
            const ops = ['+','-','*','/'];
            if (ops.includes(stdExpr.slice(-1))) stdExpr = stdExpr.slice(0,-1) + op;
            else stdExpr += op;
        }
        renderStd();
    }
    document.querySelectorAll('.hc-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.dataset.num !== undefined) stdAppend(btn.dataset.num);
            if (btn.dataset.op  !== undefined) stdOp(btn.dataset.op);
        });
    });

    // ── Kembalian ──────────────────────────────────────────────────
    function updateChange() {
        const total = parseFloat(hcTotal.value) || 0;
        const paid  = parseFloat(hcPaid.value)  || 0;
        if (total > 0 && paid > 0) {
            const diff = paid - total;
            if (diff >= 0) {
                chgLabel.textContent = 'Kembalian:';
                chgVal.className = 'hc-green';
                chgVal.textContent = fmtRp(diff);
            } else {
                chgLabel.textContent = 'Kurang bayar:';
                chgVal.className = 'hc-red';
                chgVal.textContent = fmtRp(Math.abs(diff));
            }
            chgResult.classList.remove('hidden');
        } else {
            chgResult.classList.add('hidden');
        }
    }
    hcTotal.addEventListener('input', updateChange);
    hcPaid.addEventListener('input', updateChange);

    // ── Banding Harga ──────────────────────────────────────────────
    function updateCompare() {
        const aP = parseFloat(cmpAPrice.value) || 0;
        const aQ = parseFloat(cmpAQty.value)   || 0;
        const bP = parseFloat(cmpBPrice.value) || 0;
        const bQ = parseFloat(cmpBQty.value)   || 0;
        if (aP > 0 && aQ > 0 && bP > 0 && bQ > 0) {
            const aUnit = aP / aQ;
            const bUnit = bP / bQ;
            cmpAUnit.textContent = fmtRp(aUnit) + '/satuan';
            cmpBUnit.textContent = fmtRp(bUnit) + '/satuan';
            if (aUnit < bUnit) {
                cmpWinner.textContent = 'Produk A lebih hemat';
                cmpWinner.className = 'hc-green';
            } else if (bUnit < aUnit) {
                cmpWinner.textContent = 'Produk B lebih hemat';
                cmpWinner.className = 'hc-green';
            } else {
                cmpWinner.textContent = 'Harga sama';
                cmpWinner.className = '';
            }
            cmpResult.classList.remove('hidden');
        } else {
            cmpResult.classList.add('hidden');
        }
    }
    [cmpAPrice, cmpAQty, cmpBPrice, cmpBQty].forEach(el => el.addEventListener('input', updateCompare));

})();

/* ════════════════════════════════════════════════════════════════
   SIMPANAN BARANG
   Reads/writes localStorage key 'belanja_storage' — same key
   the Belanja app uses, so data is shared automatically.
   ════════════════════════════════════════════════════════════════ */
(function() {
    'use strict';

    const LS_KEY = 'belanja_storage';

    // ── state ────────────────────────────────────────────────────
    let items       = [];
    let searchTerm  = '';
    let editId      = null;
    let currentCoords  = null;
    let currentBase64  = null;

    // ── elements ─────────────────────────────────────────────────
    const listOverlay  = document.getElementById('storageListOverlay');
    const formOverlay  = document.getElementById('storageFormOverlay');
    const btnOpenSt    = document.getElementById('btnOpenStorage');
    const listClose    = document.getElementById('storageListClose');
    const badge        = document.getElementById('hsBadge');

    const searchInput  = document.getElementById('hsSearch');
    const listCont     = document.getElementById('hsListContainer');
    const btnAdd       = document.getElementById('hsBtnAdd');

    const form         = document.getElementById('hsForm');
    const formTitle    = document.getElementById('hsFormTitle');
    const formClose    = document.getElementById('storageFormClose');
    const cancelBtn    = document.getElementById('hsBtnCancelForm');
    const idInput      = document.getElementById('hsId');
    const nameInput    = document.getElementById('hsName');
    const locInput     = document.getElementById('hsLocation');
    const notesInput   = document.getElementById('hsNotes');

    const imageInput   = document.getElementById('hsImageInput');
    const btnPhoto     = document.getElementById('hsBtnPhoto');
    const imagePreview = document.getElementById('hsImagePreview');
    const previewImg   = document.getElementById('hsPreviewImg');
    const btnRemPhoto  = document.getElementById('hsBtnRemovePhoto');

    const btnGps       = document.getElementById('hsBtnGps');
    const gpsPreview   = document.getElementById('hsGpsPreview');
    const gpsText      = document.getElementById('hsGpsText');
    const btnRemGps    = document.getElementById('hsBtnRemoveGps');

    // ── load / save ──────────────────────────────────────────────
    function load() {
        try { items = JSON.parse(localStorage.getItem(LS_KEY) || '[]'); }
        catch(e) { items = []; }
    }
    function save() {
        localStorage.setItem(LS_KEY, JSON.stringify(items));
        updateBadge();
    }
    function updateBadge() {
        const n = items.length;
        if (n > 0) {
            badge.textContent = n;
            badge.style.display = 'inline-flex';
        } else {
            badge.style.display = 'none';
        }
    }

    // ── format ───────────────────────────────────────────────────
    function fmtDate(id) {
        return new Date(parseInt(id)).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' });
    }
    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    // ── render list ───────────────────────────────────────────────
    function render() {
        const q = searchTerm.toLowerCase();
        const filtered = q
            ? items.filter(s => s.name.toLowerCase().includes(q) || s.location.toLowerCase().includes(q) || (s.notes||'').toLowerCase().includes(q))
            : items;

        if (items.length === 0) {
            listCont.innerHTML = `<div class="hs-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" width="48" height="48">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                </svg>
                <strong>Belum ada simpanan</strong>
                <p>Tekan + untuk mencatat di mana kamu menaruh barang.</p>
            </div>`;
            return;
        }
        if (filtered.length === 0) {
            listCont.innerHTML = `<div class="hs-empty"><strong>Tidak ditemukan</strong><p>Coba kata kunci lain.</p></div>`;
            return;
        }

        listCont.innerHTML = filtered.map(s => {
            const mapBtn = (s.lat && s.lng)
                ? `<button class="hs-map-btn" onclick="hsOpenMap('${s.lat}','${s.lng}')">
                       <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                       Buka Maps
                   </button>`
                : '';
            return `<div class="hs-card">
                ${s.image ? `<img class="hs-card-thumb" src="${s.image}" alt="">` : ''}
                <div class="hs-card-body">
                    <div class="hs-card-name">${esc(s.name)}</div>
                    <div class="hs-card-loc">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        ${esc(s.location)}
                    </div>
                    ${s.notes ? `<div class="hs-card-notes">${esc(s.notes)}</div>` : ''}
                </div>
                <div class="hs-card-footer">
                    <div style="display:flex;align-items:center;gap:10px">
                        <span class="hs-card-date">${fmtDate(s.id)}</span>
                        ${mapBtn}
                    </div>
                    <div class="hs-card-actions">
                        <button class="hs-icon-btn" onclick="hsEdit('${s.id}')" title="Edit">✏️</button>
                        <button class="hs-icon-btn danger" onclick="hsDelete('${s.id}')" title="Hapus">🗑</button>
                    </div>
                </div>
            </div>`;
        }).join('');
    }

    // ── open / close modals ───────────────────────────────────────
    function openList() {
        load();
        render();
        updateBadge();
        listOverlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeList() {
        listOverlay.classList.remove('open');
        document.body.style.overflow = '';
    }
    function openForm(item = null) {
        editId = item ? item.id : null;
        formTitle.textContent = item ? 'Edit Simpanan' : 'Tambah Simpanan';
        idInput.value       = item ? item.id : '';
        nameInput.value     = item ? item.name : '';
        locInput.value      = item ? item.location : '';
        notesInput.value    = item ? (item.notes || '') : '';

        // Photo
        currentBase64 = item?.image || null;
        if (currentBase64) {
            previewImg.src = currentBase64;
            imagePreview.style.display = 'block';
            btnPhoto.style.display = 'none';
        } else {
            imagePreview.style.display = 'none';
            btnPhoto.style.display = 'flex';
        }
        imageInput.value = '';

        // GPS
        currentCoords = (item?.lat && item?.lng) ? { lat: item.lat, lng: item.lng, accuracy: item.accuracy } : null;
        showGpsPreview();

        formOverlay.classList.add('open');
        setTimeout(() => nameInput.focus(), 80);
    }
    function closeForm() {
        formOverlay.classList.remove('open');
    }

    // ── GPS ───────────────────────────────────────────────────────
    function showGpsPreview() {
        if (currentCoords) {
            const acc = currentCoords.accuracy ? ` (±${Math.round(currentCoords.accuracy)}m)` : '';
            gpsText.textContent = `${currentCoords.lat.toFixed(5)}, ${currentCoords.lng.toFixed(5)}${acc}`;
            gpsPreview.style.display = 'flex';
            btnGps.style.display = 'none';
        } else {
            gpsPreview.style.display = 'none';
            btnGps.style.display = 'flex';
        }
    }

    // ── save / delete ─────────────────────────────────────────────
    function saveItem(e) {
        e.preventDefault();
        const name     = nameInput.value.trim();
        const location = locInput.value.trim();
        const notes    = notesInput.value.trim();
        if (!name || !location) return;

        const payload = {
            name, location, notes,
            image:    currentBase64 || null,
            lat:      currentCoords?.lat    || null,
            lng:      currentCoords?.lng    || null,
            accuracy: currentCoords?.accuracy || null,
        };

        if (editId) {
            const idx = items.findIndex(s => s.id === editId);
            if (idx !== -1) items[idx] = { ...items[idx], ...payload };
        } else {
            items.unshift({ id: Date.now().toString(), ...payload });
        }

        save();
        render();
        closeForm();
    }

    // ── exposed globals (for onclick= in card HTML) ───────────────
    window.hsEdit = function(id) {
        const item = items.find(s => s.id === id);
        if (item) openForm(item);
    };
    window.hsDelete = function(id) {
        if (!confirm('Hapus catatan simpanan ini?')) return;
        items = items.filter(s => s.id !== id);
        save();
        render();
    };
    window.hsOpenMap = function(lat, lng) {
        window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
    };

    // ── events ────────────────────────────────────────────────────
    btnOpenSt.addEventListener('click', openList);
    listClose.addEventListener('click', closeList);
    listOverlay.addEventListener('click', e => { if (e.target === listOverlay) closeList(); });

    btnAdd.addEventListener('click', () => { closeList(); openForm(); });
    formClose.addEventListener('click', closeForm);
    cancelBtn.addEventListener('click', closeForm);
    formOverlay.addEventListener('click', e => { if (e.target === formOverlay) closeForm(); });

    searchInput.addEventListener('input', () => {
        searchTerm = searchInput.value.trim();
        render();
    });

    form.addEventListener('submit', saveItem);

    // Photo
    btnPhoto.addEventListener('click', () => imageInput.click());
    imageInput.addEventListener('change', e => {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = ev => {
            currentBase64 = ev.target.result;
            previewImg.src = currentBase64;
            imagePreview.style.display = 'block';
            btnPhoto.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });
    btnRemPhoto.addEventListener('click', () => {
        currentBase64 = null;
        imageInput.value = '';
        imagePreview.style.display = 'none';
        btnPhoto.style.display = 'flex';
    });

    // GPS
    btnGps.addEventListener('click', () => {
        if (!navigator.geolocation) { alert('Perangkat tidak mendukung GPS.'); return; }
        const span = btnGps.querySelector('svg + *') || btnGps;
        btnGps.disabled = true;
        btnGps.style.opacity = '.6';
        navigator.geolocation.getCurrentPosition(
            pos => {
                currentCoords = { lat: pos.coords.latitude, lng: pos.coords.longitude, accuracy: pos.coords.accuracy };
                btnGps.disabled = false;
                btnGps.style.opacity = '';
                showGpsPreview();
            },
            err => {
                btnGps.disabled = false;
                btnGps.style.opacity = '';
                let msg = 'Gagal mengambil lokasi.';
                if (err.code === 1) msg = 'Izin lokasi ditolak. Aktifkan di pengaturan browser.';
                alert(msg);
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    });
    btnRemGps.addEventListener('click', () => {
        currentCoords = null;
        showGpsPreview();
    });

    // ── init ─────────────────────────────────────────────────────
    load();
    updateBadge();

})();

/* ════════════════════════════════════════════════════════════════
   TAGIHAN RUTIN  —  server-side via /bills (syncs across devices)
   ════════════════════════════════════════════════════════════════ */
(function() {
    'use strict';

    const symbol   = '<?= esc($symbol) ?>';
    const today    = new Date().getDate();

    let bills      = [];
    let editBillId = null;

    // ── elements ────────────────────────────────────────────────────
    const billsOverlay = document.getElementById('billsModalOverlay');
    const billsClose   = document.getElementById('billsModalClose');
    const btnOpenBills = document.getElementById('btnOpenBills');
    const btnAddBill   = document.getElementById('btnAddBill');
    const billsList    = document.getElementById('billsList');
    const dueBanner    = document.getElementById('dueBillsBanner');
    const billsBadge   = document.getElementById('billDueBadge');

    const formOverlay  = document.getElementById('billFormOverlay');
    const formClose    = document.getElementById('billFormClose');
    const cancelBtn    = document.getElementById('billCancelBtn');
    const billForm     = document.getElementById('billForm');
    const billIdInput  = document.getElementById('billId');
    const billName     = document.getElementById('billName');
    const billAmount   = document.getElementById('billAmount');
    const billDueDay   = document.getElementById('billDueDay');
    const billNotes    = document.getElementById('billNotes');
    const formTitle    = document.getElementById('billFormTitle');

    // ── CSRF helper ─────────────────────────────────────────────────
    function csrf() {
        return { name: window.DUITKU.csrfName, token: window.DUITKU.csrfToken };
    }

    // ── Server calls ─────────────────────────────────────────────────
    async function loadBills() {
        try {
            const res  = await fetch('/bills', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            bills = data.bills || [];
        } catch(e) { bills = []; }
    }

    async function serverStore(payload) {
        const fd = new FormData();
        fd.append(csrf().name, csrf().token);
        Object.entries(payload).forEach(([k, v]) => { if (v !== null && v !== undefined) fd.append(k, v); });
        const res  = await fetch('/bills/store', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
        return res.json();
    }

    async function serverDelete(id) {
        const fd = new FormData();
        fd.append(csrf().name, csrf().token);
        const res  = await fetch('/bills/delete/' + id, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
        return res.json();
    }

    // ── Due logic ────────────────────────────────────────────────────
    function isDueSoon(d) { const diff = d - today; return diff >= 0 && diff <= 3; }
    function isOverdue(d) { return d < today; }

    function updateBadge() {
        const n = bills.filter(b => isDueSoon(b.dueDay) || isOverdue(b.dueDay)).length;
        billsBadge.textContent    = n;
        billsBadge.style.display  = n > 0 ? 'inline-flex' : 'none';
    }

    function checkDue() {
        const due = bills.filter(b => isDueSoon(b.dueDay) || isOverdue(b.dueDay));
        if (!due.length) { dueBanner.style.display = 'none'; return; }
        dueBanner.style.display = 'block';
        dueBanner.innerHTML = `<div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:var(--bg-card);border:1.5px solid #F59E0B;border-radius:14px;margin-bottom:16px">
            <span style="font-size:20px">⏰</span>
            <div>
                <div style="font-weight:700;font-size:14px;color:#F59E0B">${due.length} tagihan jatuh tempo</div>
                <div style="font-size:12px;color:var(--text-muted)">${due.map(b => b.name + ' (tgl ' + b.dueDay + ')').join(', ')}</div>
            </div>
            <button onclick="document.getElementById('btnOpenBills').click()" style="margin-left:auto;padding:6px 12px;background:#F59E0B;color:#fff;border-radius:8px;font-size:12px;font-weight:700">Lihat</button>
        </div>`;
    }

    // ── Render ───────────────────────────────────────────────────────
    function fmtAmt(n) { return n ? symbol + ' ' + parseFloat(n).toLocaleString('id-ID') : '—'; }
    function escH(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

    function render() {
        if (!bills.length) {
            billsList.innerHTML = `<div style="text-align:center;padding:32px 0;color:var(--text-muted)">
                <div style="font-size:36px;margin-bottom:8px">📋</div>
                <div style="font-weight:600;margin-bottom:4px">Belum ada tagihan</div>
                <div style="font-size:13px">Tambah tagihan rutin agar tidak lupa jatuh tempo.</div>
            </div>`;
            return;
        }
        const sorted = [...bills].sort((a, b) => a.dueDay - b.dueDay);
        billsList.innerHTML = sorted.map(b => {
            const soon = isDueSoon(b.dueDay), over = isOverdue(b.dueDay);
            const tag  = over
                ? `<span style="background:#EF4444;color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px">LEWAT JATUH TEMPO</span>`
                : soon
                ? `<span style="background:#F59E0B;color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px">JATUH TEMPO SEGERA</span>` : '';
            return `<div class="bill-card">
                <div class="bill-card-main">
                    <div>
                        <div style="font-weight:700;font-size:15px">${escH(b.name)} ${tag}</div>
                        <div style="font-size:13px;color:var(--text-muted);margin-top:2px">
                            📅 Jatuh tempo tgl ${b.dueDay} &nbsp;·&nbsp; ${fmtAmt(b.amount)}
                            ${b.notes ? `<br><span style="font-size:12px">${escH(b.notes)}</span>` : ''}
                        </div>
                    </div>
                    <div style="display:flex;gap:6px">
                        <button class="hs-icon-btn" onclick="billEdit('${escH(b.id)}')" title="Edit">✏️</button>
                        <button class="hs-icon-btn danger" onclick="billDelete('${escH(b.id)}')" title="Hapus">🗑</button>
                    </div>
                </div>
            </div>`;
        }).join('');
    }

    // ── Modal open/close ─────────────────────────────────────────────
    async function openBillsModal() {
        billsList.innerHTML = `<div style="text-align:center;padding:20px;color:var(--text-muted)">Memuat…</div>`;
        billsOverlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        await loadBills();
        render();
        updateBadge();
        checkDue();
    }
    function closeBillsModal() {
        billsOverlay.classList.remove('open');
        document.body.style.overflow = '';
    }
    function openForm(bill) {
        editBillId        = bill ? bill.id : null;
        formTitle.textContent = bill ? 'Edit Tagihan' : 'Tambah Tagihan';
        billIdInput.value = bill ? bill.id : '';
        billName.value    = bill ? bill.name : '';
        billAmount.value  = bill ? (bill.amount || '') : '';
        billDueDay.value  = bill ? bill.dueDay : '';
        billNotes.value   = bill ? (bill.notes || '') : '';
        formOverlay.classList.add('open');
        setTimeout(() => billName.focus(), 80);
    }
    function closeForm() { formOverlay.classList.remove('open'); }

    // ── Submit ───────────────────────────────────────────────────────
    billForm.addEventListener('submit', async e => {
        e.preventDefault();
        const name   = billName.value.trim();
        const dueDay = parseInt(billDueDay.value);
        if (!name || !dueDay) return;

        const submitBtn = billForm.querySelector('[type=submit]');
        submitBtn.disabled = true;

        const data = await serverStore({
            id:      editBillId || '',
            name,
            amount:  billAmount.value ? parseFloat(billAmount.value) : 0,
            due_day: dueDay,
            notes:   billNotes.value.trim(),
        });

        submitBtn.disabled = false;
        if (!data.success) { alert(data.message || 'Gagal menyimpan.'); return; }

        await loadBills();
        render();
        updateBadge();
        checkDue();
        closeForm();
    });

    // ── Global callbacks (onclick= in rendered HTML) ─────────────────
    window.billEdit = id => { const b = bills.find(x => x.id === id); if (b) openForm(b); };
    window.billDelete = async id => {
        if (!confirm('Hapus tagihan ini?')) return;
        await serverDelete(id);
        await loadBills();
        render();
        updateBadge();
        checkDue();
    };

    // ── Events ───────────────────────────────────────────────────────
    btnOpenBills.addEventListener('click', openBillsModal);
    billsClose.addEventListener('click', closeBillsModal);
    billsOverlay.addEventListener('click', e => { if (e.target === billsOverlay) closeBillsModal(); });
    btnAddBill.addEventListener('click', () => openForm(null));
    formClose.addEventListener('click', closeForm);
    cancelBtn.addEventListener('click', closeForm);
    formOverlay.addEventListener('click', e => { if (e.target === formOverlay) closeForm(); });

    // ── Init: load bills on page load for badge + banner ─────────────
    loadBills().then(() => { updateBadge(); checkDue(); });

    // ── Expose for bill picker in transaction form ────────────────────
    window.getBills = () => bills;

})();

/* ════════════════════════════════════════════════════════════════
   TARGET MENABUNG  —  POST /settings/savings
   ════════════════════════════════════════════════════════════════ */
(function() {
    'use strict';

    const overlay     = document.getElementById('savingsModalOverlay');
    const closeBtn    = document.getElementById('savingsModalClose');
    const cancelBtn   = document.getElementById('savingsCancelBtn');
    const saveBtn     = document.getElementById('savingsSaveBtn');
    const nameInput   = document.getElementById('savingsNameInput');
    const targetInput = document.getElementById('savingsTargetInput');
    const savedInput  = document.getElementById('savingsSavedInput');

    // Open modal when clicking savings card or its edit button
    const savingsCard   = document.getElementById('savingsCard');
    const btnEditSavings = document.getElementById('btnEditSavings');
    if (savingsCard)    savingsCard.addEventListener('click', openModal);
    if (btnEditSavings) btnEditSavings.addEventListener('click', e => { e.stopPropagation(); openModal(); });

    function openModal() {
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    function numVal(input) {
        return parseFloat(input.value.replace(/\./g, '').replace(',', '.')) || 0;
    }

    // Allow formatted number input
    [targetInput, savedInput].forEach(inp => {
        inp.addEventListener('blur', () => {
            const n = numVal(inp);
            if (n > 0) inp.value = n.toLocaleString('id-ID');
        });
        inp.addEventListener('focus', () => {
            const n = numVal(inp);
            if (n > 0) inp.value = n;
        });
    });

    saveBtn.addEventListener('click', async () => {
        const name   = nameInput.value.trim();
        const target = numVal(targetInput);
        const saved  = numVal(savedInput);
        if (!name || target <= 0) { alert('Isi nama target dan nominal target terlebih dahulu.'); return; }

        saveBtn.disabled = true;
        saveBtn.textContent = 'Menyimpan…';

        const fd = new FormData();
        fd.append(window.DUITKU.csrfName, window.DUITKU.csrfToken);
        fd.append('savings_name', name);
        fd.append('savings_target', target);
        fd.append('savings_saved', saved);

        try {
            const res  = await fetch('/settings/savings', { method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest'}, body: fd });
            const data = await res.json();
            if (data.success) { closeModal(); location.reload(); }
            else { alert(data.message || 'Gagal menyimpan.'); }
        } catch(e) {
            alert('Terjadi kesalahan koneksi.');
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Simpan';
        }
    });

    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

})();

/* ════════════════════════════════════════════════════════════════
   CATATAN KEUANGAN  —  POST /settings/note
   ════════════════════════════════════════════════════════════════ */
(function() {
    'use strict';

    const overlay   = document.getElementById('noteModalOverlay');
    const closeBtn  = document.getElementById('noteModalClose');
    const cancelBtn = document.getElementById('noteCancelBtn');
    const saveBtn   = document.getElementById('noteSaveBtn');
    const textarea  = document.getElementById('noteTextarea');
    const btnOpen   = document.getElementById('btnOpenNote');

    btnOpen.addEventListener('click', openModal);
    function openModal() {
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        setTimeout(() => textarea.focus(), 80);
    }
    function closeModal() {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    saveBtn.addEventListener('click', async () => {
        saveBtn.disabled = true;
        saveBtn.textContent = 'Menyimpan…';

        const fd = new FormData();
        fd.append(window.DUITKU.csrfName, window.DUITKU.csrfToken);
        fd.append('note', textarea.value);

        try {
            const res  = await fetch('/settings/note', { method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest'}, body: fd });
            const data = await res.json();
            if (data.success) { closeModal(); location.reload(); }
            else { alert(data.message || 'Gagal menyimpan catatan.'); }
        } catch(e) {
            alert('Terjadi kesalahan koneksi.');
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Simpan';
        }
    });

    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

})();

/* ════════════════════════════════════════════════════════════════
   BILL PICKER — pre-fills transaction form when paying a bill
   ════════════════════════════════════════════════════════════════ */
(function() {
    'use strict';

    const billPickerRow = document.getElementById('billPickerRow');
    const billPicker    = document.getElementById('billPicker');
    const txAmount      = document.getElementById('txAmount');
    const txNote        = document.getElementById('txNote');
    const btnExpense    = document.getElementById('btnExpense');
    const btnIncome     = document.getElementById('btnIncome');
    const fabBtn        = document.getElementById('fabBtn');

    if (!billPickerRow || !billPicker) return;

    function showPicker()  { billPickerRow.style.display = ''; }
    function hidePicker()  { billPickerRow.style.display = 'none'; billPicker.value = ''; }

    function populatePicker() {
        const bills = (typeof window.getBills === 'function') ? window.getBills() : [];
        billPicker.innerHTML = '<option value="">— Pilih tagihan (opsional) —</option>' +
            bills.map(b => {
                const label = b.name + (b.dueDay ? ' · tgl ' + b.dueDay : '') +
                              (b.amount ? ' · ' + Number(b.amount).toLocaleString('id-ID') : '');
                return `<option value="${b.id}" data-amount="${b.amount||''}" data-name="${b.name}">${label}</option>`;
            }).join('');
    }

    // Show/hide based on type toggle
    btnExpense && btnExpense.addEventListener('click', showPicker);
    btnIncome  && btnIncome.addEventListener('click', hidePicker);

    // Populate when FAB opens (transaction is expense by default)
    fabBtn && fabBtn.addEventListener('click', () => {
        populatePicker();
        showPicker();
        billPicker.value = '';
    });

    // Pre-fill amount + note when bill selected
    billPicker.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (!opt || !opt.value) return;
        const amount = parseFloat(opt.dataset.amount || '0');
        const name   = opt.dataset.name || '';
        if (amount > 0) txAmount.value = amount.toLocaleString('id-ID');
        if (name) txNote.value = 'Bayar tagihan: ' + name;
    });

})();
</script>
<?= $this->endSection() ?>
