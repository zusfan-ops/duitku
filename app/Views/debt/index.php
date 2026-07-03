<?= $this->extend('layouts/main') ?>
<?= $this->section('styles') ?>
<style>
.debt-summary-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 20px;
}
.debt-summary-card {
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    border-radius: 16px;
    padding: 14px 16px;
    text-align: center;
}
.debt-summary-card.hutang { border-color: rgba(239,68,68,.35); }
.debt-summary-card.piutang { border-color: rgba(34,197,94,.35); }
.debt-summary-label {
    font-size: 12px;
    font-weight: 800;
    color: var(--text-secondary);
    margin-bottom: 2px;
}
.debt-summary-sub {
    font-size: 10px;
    color: var(--text-muted);
    margin-bottom: 8px;
}
.debt-summary-amount { font-size: 17px; font-weight: 800; }
.debt-summary-card.hutang  .debt-summary-amount { color: #EF4444; }
.debt-summary-card.piutang .debt-summary-amount { color: #22C55E; }
.debt-net {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    font-size: 13px;
}
.debt-net-label { color: var(--text-muted); font-weight: 600; }
.debt-net-val   { font-weight: 800; font-size: 15px; }
.debt-tabs {
    display: flex;
    background: var(--bg);
    border-radius: 12px;
    padding: 4px;
    gap: 2px;
    margin-bottom: 16px;
}
.debt-tab {
    flex: 1;
    padding: 9px 6px;
    border-radius: 9px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    background: transparent;
    cursor: pointer;
    transition: all var(--transition);
    text-align: center;
    text-decoration: none;
    display: block;
}
.debt-tab.active { background: var(--bg-card); color: var(--primary); box-shadow: var(--shadow-sm); }
.debt-card {
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    border-radius: 18px;
    margin-bottom: 12px;
    overflow: hidden;
    transition: border-color var(--transition);
}
.debt-card.hutang  { border-left: 4px solid #EF4444; }
.debt-card.piutang { border-left: 4px solid #22C55E; }
.debt-card.settled { opacity: .65; }
.debt-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 14px 16px 10px;
    gap: 10px;
}
.debt-badge {
    font-size: 10px;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: .3px;
    flex-shrink: 0;
}
.debt-badge.hutang  { background: rgba(239,68,68,.15);  color: #EF4444; }
.debt-badge.piutang { background: rgba(34,197,94,.15);  color: #22C55E; }
.debt-badge.settled { background: rgba(100,116,139,.15);color: #64748B; }
.debt-person { font-size: 16px; font-weight: 800; color: var(--text-primary); margin-bottom: 2px; }
.debt-desc   { font-size: 12px; color: var(--text-muted); }
.debt-amounts { padding: 0 16px 10px; }
.debt-amount-row {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    margin-bottom: 6px;
}
.debt-amount-total { font-weight: 800; font-size: 17px; color: var(--text-primary); }
.debt-amount-paid  { color: var(--text-muted); }
.debt-amount-sisa  { font-weight: 700; }
.debt-amount-sisa.hutang  { color: #EF4444; }
.debt-amount-sisa.piutang { color: #22C55E; }
.debt-progress {
    height: 6px;
    background: var(--bg);
    border-radius: 99px;
    overflow: hidden;
    margin-bottom: 8px;
}
.debt-progress-fill { height: 100%; border-radius: 99px; transition: width .5s ease; }
.debt-card.hutang  .debt-progress-fill { background: #EF4444; }
.debt-card.piutang .debt-progress-fill { background: #22C55E; }
.debt-due {
    font-size: 12px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 4px;
}
.debt-due.overdue { color: #EF4444; font-weight: 700; }
.debt-due.soon    { color: #F59E0B; font-weight: 700; }
.debt-actions {
    display: flex;
    border-top: 1px solid var(--border);
}
.debt-action-btn {
    flex: 1;
    padding: 11px 6px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-secondary);
    background: none;
    border: none;
    border-right: 1px solid var(--border);
    cursor: pointer;
    transition: all var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}
.debt-action-btn:last-child { border-right: none; }
.debt-action-btn:hover      { background: var(--bg); }
.debt-action-btn.pay        { color: var(--primary); }
.debt-action-btn.settle     { color: #22C55E; }
.debt-action-btn.del        { color: #EF4444; }
.debt-empty {
    text-align: center;
    padding: 48px 20px;
    color: var(--text-muted);
}
.debt-empty-icon { font-size: 48px; margin-bottom: 12px; }

/* ── Debt type toggle ── */
.debt-type-toggle {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-bottom: 20px;
}
.debt-type-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
    padding: 14px 10px;
    border-radius: 14px;
    border: 2px solid var(--border);
    background: var(--bg);
    cursor: pointer;
    transition: all .18s;
}
.debt-type-btn .dtb-dot  { font-size: 18px; color: var(--text-muted); transition: color .18s; }
.debt-type-btn .dtb-label{ font-size: 13px; font-weight: 700; color: var(--text-muted); transition: color .18s; }
.debt-type-btn .dtb-sub  { font-size: 10px; color: var(--text-muted); transition: color .18s; }

/* Hutang selected */
.debt-type-btn.hutang.active {
    background: rgba(239,68,68,.12);
    border-color: #EF4444;
}
.debt-type-btn.hutang.active .dtb-dot  { color: #EF4444; }
.debt-type-btn.hutang.active .dtb-label{ color: #EF4444; }
.debt-type-btn.hutang.active .dtb-sub  { color: rgba(239,68,68,.7); }

/* Piutang selected */
.debt-type-btn.piutang.active {
    background: rgba(34,197,94,.12);
    border-color: #22C55E;
}
.debt-type-btn.piutang.active .dtb-dot  { color: #22C55E; }
.debt-type-btn.piutang.active .dtb-label{ color: #22C55E; }
.debt-type-btn.piutang.active .dtb-sub  { color: rgba(34,197,94,.7); }

/* ── Group header ── */
.debt-group-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 18px 0 8px;
}
.debt-group-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: var(--primary-dim);
    color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; font-weight: 800;
    flex-shrink: 0;
}
.debt-group-info { flex: 1; min-width: 0; }
.debt-group-name { font-size: 14px; font-weight: 800; color: var(--text-primary); }
.debt-group-meta { font-size: 11px; color: var(--text-muted); margin-top: 1px; }
.debt-group-net  { font-size: 13px; font-weight: 800; white-space: nowrap; }
.debt-group-net.hutang  { color: #EF4444; }
.debt-group-net.piutang { color: #22C55E; }
.debt-group-net.zero    { color: var(--text-muted); }

/* Pay modal */
.pay-modal-info {
    background: var(--bg);
    border-radius: 12px;
    padding: 12px 14px;
    margin-bottom: 16px;
    font-size: 13px;
}
.pay-modal-info strong { display: block; font-size: 15px; margin-bottom: 4px; }
.upcoming-banner {
    background: rgba(245,158,11,.08);
    border: 1.5px solid rgba(245,158,11,.35);
    border-radius: 14px;
    padding: 12px 16px;
    margin-bottom: 16px;
    font-size: 13px;
}
.upcoming-banner-title { font-weight: 700; color: #F59E0B; margin-bottom: 4px; }

/* ── Past-debt toggle ── */
.debt-past-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--bg);
    border: 1.5px solid var(--border);
    border-radius: 14px;
    padding: 12px 14px;
    cursor: pointer;
}
.debt-past-label { font-size: 13px; font-weight: 700; color: var(--text-primary); }
.debt-past-sub   { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.debt-past-switch { position: relative; width: 44px; height: 24px; flex-shrink: 0; }
.debt-past-switch input { opacity: 0; width: 0; height: 0; position: absolute; }
.debt-past-knob {
    position: absolute; inset: 0;
    background: var(--border);
    border-radius: 99px;
    transition: background .2s;
    cursor: pointer;
}
.debt-past-knob::before {
    content: '';
    position: absolute;
    width: 18px; height: 18px;
    left: 3px; top: 3px;
    background: #fff;
    border-radius: 50%;
    transition: transform .2s;
}
.debt-past-switch input:checked + .debt-past-knob { background: #8B5CF6; }
.debt-past-switch input:checked + .debt-past-knob::before { transform: translateX(20px); }
.debt-badge.past { background: rgba(148,163,184,.15); color: #94A3B8; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="stats-page">

    <!-- Upcoming due banner -->
    <?php if (!empty($upcoming)): ?>
    <div class="upcoming-banner">
        <div class="upcoming-banner-title">⏰ Jatuh tempo dalam 7 hari</div>
        <div style="color:var(--text-secondary)">
            <?php foreach ($upcoming as $u): ?>
            <span style="display:inline-flex;align-items:center;gap:4px;margin-right:12px">
                <?= $u['type'] === 'hutang' ? '🔴' : '🟢' ?>
                <?= esc($u['person']) ?> —
                <?= esc($symbol) ?> <?= number_format($u['amount'] - $u['paid'], 0, ',', '.') ?>
                (<?= date('d M', strtotime($u['due_date'])) ?>)
            </span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Summary cards -->
    <div class="debt-summary-row">
        <div class="debt-summary-card hutang">
            <div class="debt-summary-label">🔴 Hutang</div>
            <div class="debt-summary-sub">kamu ke orang lain</div>
            <div class="debt-summary-amount"><?= esc($symbol) ?> <?= number_format($summary['total_hutang'], 0, ',', '.') ?></div>
        </div>
        <div class="debt-summary-card piutang">
            <div class="debt-summary-label">🟢 Piutang</div>
            <div class="debt-summary-sub">orang lain ke kamu</div>
            <div class="debt-summary-amount"><?= esc($symbol) ?> <?= number_format($summary['total_piutang'], 0, ',', '.') ?></div>
        </div>
    </div>

    <?php
        $net    = $summary['total_piutang'] - $summary['total_hutang'];
        $netPos = $net >= 0;
    ?>
    <div class="debt-net">
        <span class="debt-net-label">Posisi bersih</span>
        <span class="debt-net-val" style="color:<?= $netPos ? '#22C55E' : '#EF4444' ?>">
            <?= $netPos ? '+' : '' ?><?= esc($symbol) ?> <?= number_format(abs($net), 0, ',', '.') ?>
            <small style="font-size:11px;font-weight:600;color:var(--text-muted)">
                <?= $netPos ? 'piutang lebih besar' : 'hutang lebih besar' ?>
            </small>
        </span>
    </div>

    <!-- Tabs -->
    <div class="debt-tabs">
        <a href="/hutang?status=active"   class="debt-tab <?= $status === 'active'  ? 'active' : '' ?>">Aktif (<?= $summary['active_count'] ?>)</a>
        <a href="/hutang?status=all"      class="debt-tab <?= $status === 'all'     ? 'active' : '' ?>">Semua</a>
        <a href="/hutang?status=settled"  class="debt-tab <?= $status === 'settled' ? 'active' : '' ?>">Lunas</a>
    </div>

    <!-- Debt list -->
    <?php if (empty($debts)): ?>
    <div class="debt-empty">
        <div class="debt-empty-icon">🤝</div>
        <div style="font-weight:700;margin-bottom:6px">Belum ada catatan</div>
        <div style="font-size:13px">Tekan + untuk mencatat hutang atau piutang.</div>
    </div>
    <?php else: ?>
    <?php
        $today    = date('Y-m-d');
        $idMonths = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];

        // Group by person (case-insensitive)
        $grouped = [];
        foreach ($debts as $d) {
            $key = mb_strtolower(trim($d['person']));
            $grouped[$key]['person'] = $d['person'];
            $grouped[$key]['items'][] = $d;
        }
    ?>
    <?php foreach ($grouped as $grp):
        $items   = $grp['items'];
        $person  = $grp['person'];
        $initial = mb_strtoupper(mb_substr($person, 0, 1));
        // Net position for this person (active only)
        $netHutang  = 0; $netPiutang = 0; $activeCount = 0;
        foreach ($items as $it) {
            if ($it['status'] === 'active') {
                $sisa = (float)$it['amount'] - (float)$it['paid'];
                if ($it['type'] === 'hutang')  $netHutang  += $sisa;
                else                           $netPiutang += $sisa;
                $activeCount++;
            }
        }
        $net    = $netPiutang - $netHutang;
        $netCls = $net > 0 ? 'piutang' : ($net < 0 ? 'hutang' : 'zero');
        $netLbl = $net > 0
            ? ('+ ' . esc($symbol) . ' ' . number_format($net, 0, ',', '.'))
            : ($net < 0
                ? ('- ' . esc($symbol) . ' ' . number_format(abs($net), 0, ',', '.'))
                : 'Lunas semua');
        $totalItems = count($items);
    ?>
    <!-- Group header -->
    <div class="debt-group-header">
        <div class="debt-group-avatar"><?= $initial ?></div>
        <div class="debt-group-info">
            <div class="debt-group-name"><?= esc($person) ?></div>
            <div class="debt-group-meta"><?= $totalItems ?> catatan<?= $activeCount ? " · {$activeCount} aktif" : '' ?></div>
        </div>
        <?php if ($activeCount > 0): ?>
        <div class="debt-group-net <?= $netCls ?>"><?= $netLbl ?></div>
        <?php endif; ?>
    </div>

    <?php foreach ($items as $d):
        $sisa  = (float)$d['amount'] - (float)$d['paid'];
        $pct   = $d['amount'] > 0 ? min(($d['paid'] / $d['amount']) * 100, 100) : 0;
        $isDue = $d['due_date'] && $d['status'] === 'active';
        $dueCls= ''; $dueLabel = '';
        if ($isDue) {
            $diff = (strtotime($d['due_date']) - strtotime($today)) / 86400;
            if ($diff < 0) { $dueCls = 'overdue'; $dueLabel = 'Lewat ' . abs(floor($diff)) . ' hari'; }
            elseif ($diff <= 7) { $dueCls = 'soon'; $dueLabel = floor($diff) === 0 ? 'Hari ini!' : floor($diff) . ' hari lagi'; }
            else { $dt = new DateTime($d['due_date']); $dueLabel = $dt->format('d') . ' ' . ($idMonths[(int)$dt->format('m') - 1]) . ' ' . $dt->format('Y'); }
        }
    ?>
    <div class="debt-card <?= esc($d['type']) ?> <?= $d['status'] === 'settled' ? 'settled' : '' ?>" style="margin-left:8px;border-left-width:3px">
        <div class="debt-card-header">
            <div>
                <?php if ($d['description']): ?>
                <div class="debt-desc" style="font-size:13px;color:var(--text-secondary)"><?= esc($d['description']) ?></div>
                <?php else: ?>
                <div class="debt-desc" style="font-size:13px;color:var(--text-muted);font-style:italic">Tanpa keterangan</div>
                <?php endif; ?>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px">
                <div style="display:flex;gap:4px;flex-wrap:wrap;justify-content:flex-end">
                    <span class="debt-badge <?= $d['status'] === 'settled' ? 'settled' : esc($d['type']) ?>">
                        <?= $d['status'] === 'settled' ? 'Lunas' : ($d['type'] === 'hutang' ? 'Hutang' : 'Piutang') ?>
                    </span>
                    <?php if (!empty($d['is_past'])): ?>
                    <span class="debt-badge past">Lama</span>
                    <?php endif; ?>
                </div>
                <?php if ($isDue && $dueLabel): ?>
                <span class="debt-due <?= $dueCls ?>">📅 <?= esc($dueLabel) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="debt-amounts">
            <div class="debt-amount-row">
                <span class="debt-amount-total"><?= esc($symbol) ?> <?= number_format($d['amount'], 0, ',', '.') ?></span>
                <?php if ((float)$d['paid'] > 0): ?>
                <span class="debt-amount-paid">Dibayar <?= esc($symbol) ?> <?= number_format($d['paid'], 0, ',', '.') ?></span>
                <?php endif; ?>
            </div>
            <?php if ($d['status'] !== 'settled'): ?>
            <div class="debt-progress">
                <div class="debt-progress-fill" style="width:<?= number_format($pct, 1) ?>%"></div>
            </div>
            <div style="font-size:12px;color:var(--text-muted)">
                Sisa: <strong class="debt-amount-sisa <?= esc($d['type']) ?>"><?= esc($symbol) ?> <?= number_format($sisa, 0, ',', '.') ?></strong>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($d['status'] === 'active'): ?>
        <div class="debt-actions">
            <button class="debt-action-btn pay" onclick="openPayModal(<?= $d['id'] ?>, '<?= esc($d['person']) ?>', <?= $sisa ?>, '<?= esc($d['type']) ?>')">
                💳 Bayar
            </button>
            <button class="debt-action-btn settle" onclick="settleDebt(<?= $d['id'] ?>)">
                ✅ Lunas
            </button>
            <button class="debt-action-btn del" onclick="deleteDebt(<?= $d['id'] ?>)">
                🗑
            </button>
        </div>
        <?php else: ?>
        <div class="debt-actions">
            <button class="debt-action-btn del" onclick="deleteDebt(<?= $d['id'] ?>)">
                🗑 Hapus
            </button>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ═══ ADD DEBT MODAL ═══ -->
<div class="modal-overlay" id="addDebtOverlay">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3 id="addDebtTitle">Catat Hutang / Piutang</h3>
            <button class="modal-close" id="addDebtClose">✕</button>
        </div>
        <form id="addDebtForm">
            <!-- Type toggle -->
            <div class="debt-type-toggle">
                <button type="button" class="debt-type-btn hutang active" id="debtTypeHutang" data-val="hutang">
                    <span class="dtb-dot">●</span>
                    <span class="dtb-label">Hutang</span>
                    <span class="dtb-sub">saldo +naik</span>
                </button>
                <button type="button" class="debt-type-btn piutang" id="debtTypePiutang" data-val="piutang">
                    <span class="dtb-dot">●</span>
                    <span class="dtb-label">Piutang</span>
                    <span class="dtb-sub">saldo −turun</span>
                </button>
            </div>
            <input type="hidden" id="debtType" value="hutang">

            <div class="form-group">
                <label class="form-label">NAMA ORANG *</label>
                <input type="text" id="debtPerson" class="form-input" placeholder="Nama teman, keluarga, dll…" required>
            </div>
            <div class="form-group">
                <label class="form-label">NOMINAL *</label>
                <div class="amount-input-wrap" style="margin-bottom:0">
                    <span class="amount-currency"><?= esc($symbol) ?></span>
                    <input type="text" id="debtAmount" class="amount-input" placeholder="0" inputmode="numeric" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">KETERANGAN (OPSIONAL)</label>
                <input type="text" id="debtDesc" class="form-input" placeholder="Untuk apa, beli apa, dll…">
            </div>
            <div class="form-group">
                <label class="form-label">JATUH TEMPO (OPSIONAL)</label>
                <input type="date" id="debtDueDate" class="form-input">
            </div>

            <!-- Past debt toggle -->
            <div class="form-group" style="margin-bottom:20px">
                <label class="debt-past-row" for="debtIsPast">
                    <div>
                        <div class="debt-past-label">Hutang / Piutang Lama</div>
                        <div class="debt-past-sub">Tidak mempengaruhi saldo saat ini</div>
                    </div>
                    <label class="debt-past-switch">
                        <input type="checkbox" id="debtIsPast" value="1">
                        <span class="debt-past-knob"></span>
                    </label>
                </label>
                <div id="debtIsPastHint" style="display:none;font-size:11px;color:#8B5CF6;margin-top:6px;padding:0 2px">
                    ✦ Hanya dicatat, saldo tidak berubah. Saat dilunasi, saldo akan berubah sesuai pembayaran.
                </div>
            </div>

            <button type="submit" class="btn-save" id="debtSaveBtn">Simpan</button>
        </form>
    </div>
</div>

<!-- ═══ PAY PARTIAL MODAL ═══ -->
<div class="mini-modal-overlay" id="payModalOverlay">
    <div class="mini-modal">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
            <h3 style="font-size:16px;font-weight:700">💳 Bayar Sebagian</h3>
            <button class="modal-close" id="payModalClose">✕</button>
        </div>
        <div class="pay-modal-info" id="payModalInfo"></div>
        <div class="form-group">
            <label class="form-label">NOMINAL PEMBAYARAN</label>
            <div class="amount-input-wrap" style="margin-bottom:0">
                <span class="amount-currency"><?= esc($symbol) ?></span>
                <input type="text" id="payAmount" class="amount-input" placeholder="0" inputmode="numeric">
            </div>
        </div>
        <div style="display:flex;gap:8px;margin-top:16px">
            <button type="button" id="payModalCancel" class="btn-cancel-small" style="flex:1">Batal</button>
            <button type="button" id="payConfirmBtn" class="btn-save-small" style="flex:2">Konfirmasi</button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function() {
    'use strict';

    const csrfName  = window.DUITKU.csrfName;
    const csrfToken = window.DUITKU.csrfToken;

    function csrfFd(extra = {}) {
        const fd = new FormData();
        fd.append(csrfName, csrfToken);
        Object.entries(extra).forEach(([k, v]) => fd.append(k, v));
        return fd;
    }
    const headers = { 'X-Requested-With': 'XMLHttpRequest' };

    // ── Add debt modal ──────────────────────────────────────────────
    const addOverlay  = document.getElementById('addDebtOverlay');
    const addClose    = document.getElementById('addDebtClose');
    const addForm     = document.getElementById('addDebtForm');
    const debtTypeInp = document.getElementById('debtType');
    const fabBtn      = document.getElementById('fabBtn');
    const amtInput    = document.getElementById('debtAmount');

    // Intercept FAB before the global app.js handler opens the transaction modal
    fabBtn && fabBtn.addEventListener('click', e => {
        e.stopImmediatePropagation();
        addOverlay.classList.add('open');
        window.DuitkuLockScroll();
    }, true);
    addClose.addEventListener('click', closeAdd);
    addOverlay.addEventListener('click', e => { if (e.target === addOverlay) closeAdd(); });
    function closeAdd() {
        addOverlay.classList.remove('open');
        window.DuitkuUnlockScroll();
        addForm.reset();
        // Reset type buttons to hutang
        document.querySelectorAll('.debt-type-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('debtTypeHutang').classList.add('active');
        debtTypeInp.value = 'hutang';
        // Reset past toggle
        document.getElementById('debtIsPast').checked = false;
        document.getElementById('debtIsPastHint').style.display = 'none';
    }

    // Type toggle
    document.querySelectorAll('.debt-type-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.debt-type-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            debtTypeInp.value = btn.dataset.val;
        });
    });

    // Past toggle hint
    document.getElementById('debtIsPast').addEventListener('change', function() {
        document.getElementById('debtIsPastHint').style.display = this.checked ? 'block' : 'none';
    });

    // Format amount input
    amtInput.addEventListener('input', function() {
        const raw = this.value.replace(/\D/g, '');
        this.value = raw ? Number(raw).toLocaleString('id-ID') : '';
    });

    // Submit
    addForm.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = document.getElementById('debtSaveBtn');
        btn.disabled = true; btn.textContent = 'Menyimpan…';

        const amount = parseFloat(amtInput.value.replace(/\./g, '').replace(',', '.')) || 0;
        const fd = csrfFd({
            type:        debtTypeInp.value,
            person:      document.getElementById('debtPerson').value.trim(),
            amount,
            description: document.getElementById('debtDesc').value.trim(),
            due_date:    document.getElementById('debtDueDate').value,
            is_past:     document.getElementById('debtIsPast').checked ? 1 : 0,
        });

        const res  = await fetch('/hutang/store', { method: 'POST', headers, body: fd });
        const data = await res.json();
        btn.disabled = false; btn.textContent = 'Simpan';

        if (data.success) { closeAdd(); location.reload(); }
        else { alert(data.message || 'Gagal menyimpan.'); }
    });

    // ── Pay partial ─────────────────────────────────────────────────
    let currentPayId = null;

    window.openPayModal = function(id, person, sisa, type) {
        currentPayId = id;
        const sym = '<?= esc($symbol) ?>';
        document.getElementById('payModalInfo').innerHTML =
            `<strong>${person}</strong>
             Sisa ${type === 'hutang' ? 'hutang' : 'piutang'}: ${sym} ${Number(sisa).toLocaleString('id-ID')}`;
        document.getElementById('payAmount').value = '';
        document.getElementById('payModalOverlay').classList.add('open');
        setTimeout(() => document.getElementById('payAmount').focus(), 100);
    };

    const payAmtInp = document.getElementById('payAmount');
    payAmtInp.addEventListener('input', function() {
        const raw = this.value.replace(/\D/g, '');
        this.value = raw ? Number(raw).toLocaleString('id-ID') : '';
    });

    document.getElementById('payModalClose').addEventListener('click', closePayModal);
    document.getElementById('payModalCancel').addEventListener('click', closePayModal);
    document.getElementById('payModalOverlay').addEventListener('click', e => {
        if (e.target === document.getElementById('payModalOverlay')) closePayModal();
    });
    function closePayModal() { document.getElementById('payModalOverlay').classList.remove('open'); }

    document.getElementById('payConfirmBtn').addEventListener('click', async () => {
        const payAmt = parseFloat(payAmtInp.value.replace(/\./g, '').replace(',', '.')) || 0;
        if (payAmt <= 0) { alert('Masukkan nominal pembayaran.'); return; }

        const btn = document.getElementById('payConfirmBtn');
        btn.disabled = true;
        const fd = csrfFd({ pay_amount: payAmt });
        const res  = await fetch('/hutang/pay/' + currentPayId, { method: 'POST', headers, body: fd });
        const data = await res.json();
        btn.disabled = false;

        if (data.success) {
            closePayModal();
            if (data.settled) showToast('Lunas! 🎉');
            else showToast('Pembayaran dicatat.');
            setTimeout(() => location.reload(), 500);
        } else { alert(data.message || 'Gagal.'); }
    });

    // ── Settle / Delete ─────────────────────────────────────────────
    window.settleDebt = async function(id) {
        if (!confirm('Tandai sebagai lunas?\nSaldo akan berubah sesuai sisa yang belum dibayar.')) return;
        const fd  = csrfFd();
        const res = await fetch('/hutang/settle/' + id, { method: 'POST', headers, body: fd });
        const d   = await res.json();
        if (d.success) { showToast('Lunas! 🎉'); setTimeout(() => location.reload(), 500); }
        else alert('Gagal.');
    };

    window.deleteDebt = async function(id) {
        if (!confirm('Hapus catatan ini?')) return;
        const fd  = csrfFd();
        const res = await fetch('/hutang/delete/' + id, { method: 'POST', headers, body: fd });
        const d   = await res.json();
        if (d.success) { showToast('Dihapus.'); setTimeout(() => location.reload(), 400); }
        else alert('Gagal.');
    };
})();
</script>
<?= $this->endSection() ?>
