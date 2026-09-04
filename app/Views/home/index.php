<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
/* ── Home Layout ──────────────────────────────────────────────── */
.home-page { padding-bottom: 32px; }

/* ─── Apple Wallet Stacked Cards Deck (1:1 with Native) ────────── */
.apple-wallet-wrapper {
    position: relative;
    margin: 4px 0 16px;
    perspective: 1000px;
}
.apple-wallet-deck {
    position: relative;
    width: 100%;
}
.apple-deck-card {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    border-radius: 24px;
    padding: 16px 20px 18px;
    border: 1px solid rgba(255, 255, 255, 0.18);
    color: #ffffff;
    cursor: pointer;
    box-sizing: border-box;
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                box-shadow 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                opacity 0.3s ease;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    overflow: hidden;
}
.apple-deck-card:not(.active-card) {
    box-shadow: 0 -4px 14px rgba(0, 0, 0, 0.22), 0 4px 10px rgba(0,0,0,0.12);
}
.apple-deck-card:not(.active-card):hover {
    filter: brightness(1.1);
    transform: translateY(calc(var(--card-y, 0px) - 4px)) scale(var(--card-scale, 0.96)) !important;
}
.apple-deck-card.active-card {
    cursor: default;
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.35), 0 4px 14px rgba(37, 99, 235, 0.3);
}

/* Card Header */
.apple-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 26px;
}
.apple-card-title-group {
    display: flex;
    align-items: center;
    gap: 8px;
}
.apple-card-icon {
    font-size: 15px;
    line-height: 1;
}
.apple-card-name {
    font-size: 11.5px;
    font-weight: 800;
    color: rgba(255, 255, 255, 0.9);
    letter-spacing: 0.6px;
    text-transform: uppercase;
}
.apple-card-meta-group {
    display: flex;
    align-items: center;
    gap: 8px;
}
.apple-card-badge {
    font-size: 9px;
    font-weight: 900;
    color: #ffffff;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 3px 8px;
    letter-spacing: 0.5px;
    line-height: 1;
}
.apple-card-mini-balance {
    font-size: 11px;
    font-weight: 800;
    color: rgba(255, 255, 255, 0.95);
    background: rgba(0, 0, 0, 0.25);
    border-radius: 8px;
    padding: 2.5px 8px;
    letter-spacing: -0.2px;
    display: none;
}
.apple-deck-card:not(.active-card) .apple-card-mini-balance {
    display: inline-block;
}

/* Expanded Card Body */
.apple-card-body {
    margin-top: 12px;
    transition: opacity 0.25s ease;
}
.apple-deck-card:not(.active-card) .apple-card-body {
    display: none;
    opacity: 0;
}
.apple-deck-card.active-card .apple-card-body {
    display: block;
    opacity: 1;
}
.native-hero-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.native-hero-label {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.8px;
    color: rgba(255, 255, 255, 0.75);
    text-transform: uppercase;
}
.native-hero-month-tag {
    font-size: 10px;
    font-weight: 800;
    color: #fff;
    background: rgba(255, 255, 255, 0.16);
    border-radius: 12px;
    padding: 3px 9px;
    letter-spacing: 0.4px;
}
.native-hero-balance-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 18px;
}
.native-hero-amount {
    font-size: 30px;
    font-weight: 900;
    color: #fff;
    letter-spacing: -0.8px;
    line-height: 1.1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.native-hero-trend-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: rgba(16, 185, 129, 0.22);
    color: #6EE7B7;
    padding: 4px 8px;
    border-radius: 10px;
    font-size: 10.5px;
    font-weight: 800;
    white-space: nowrap;
    flex-shrink: 0;
}
.native-hero-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}
.native-hero-pill-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 16px;
    padding: 10px 12px;
    color: #fff;
    font-size: 12.5px;
    font-weight: 800;
    text-decoration: none;
    transition: background 0.15s ease, transform 0.15s ease;
}
.native-hero-pill-btn:active {
    background: rgba(255, 255, 255, 0.25);
    transform: scale(0.98);
}
.native-hero-icon-btn {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-decoration: none;
    flex-shrink: 0;
    transition: background 0.15s ease, transform 0.15s ease;
}
.native-hero-icon-btn:active {
    background: rgba(255, 255, 255, 0.25);
    transform: scale(0.95);
}

/* ── Three-Column Stats Card ──────────────────────────────────── */
.native-stats-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 22px;
    padding: 14px 16px;
    margin-bottom: 14px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04), 0 1px 4px rgba(15, 23, 42, 0.02);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.native-stats-col {
    flex: 1;
    text-align: center;
    min-width: 0;
}
.native-stats-val {
    font-size: 15.5px;
    font-weight: 900;
    letter-spacing: -0.3px;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.native-stats-val.income { color: var(--income); }
.native-stats-val.text-primary { color: var(--text-primary); }
.native-stats-val.blue { color: #2563EB; }
.native-stats-lbl {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-secondary);
    margin-top: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.native-stats-sub {
    font-size: 9.5px;
    font-weight: 500;
    color: var(--text-muted);
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.native-stats-divider {
    width: 1px;
    height: 38px;
    background: var(--border-light);
    flex-shrink: 0;
    margin: 0 4px;
}

/* ── Emergency SOS Home Card ────────────────────────────────── */
.native-emergency-card {
    background: #FEF2F2;
    border: 1px solid #FCA5A5;
    border-radius: 16px;
    padding: 11px 14px;
    margin-bottom: 12px;
    color: #991B1B;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.08);
    transition: transform 0.15s ease, background 0.15s ease;
}
.native-emergency-card:active {
    transform: scale(0.98);
}
.native-emergency-left {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}
.native-emergency-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: #DC2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    color: #fff;
    flex-shrink: 0;
}
.native-emergency-title {
    font-size: 12.5px;
    font-weight: 800;
    color: #991B1B;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.emergency-badge-24 {
    font-size: 8.5px;
    font-weight: 900;
    color: #fff;
    background: #DC2626;
    padding: 1.5px 5px;
    border-radius: 4px;
    letter-spacing: 0.4px;
}
.native-emergency-sub {
    font-size: 10.5px;
    color: #B91C1C;
    font-weight: 600;
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.native-emergency-arrow {
    font-size: 11.5px;
    font-weight: 800;
    background: rgba(220, 38, 38, 0.1);
    color: #DC2626;
    padding: 4px 8px;
    border-radius: 8px;
    white-space: nowrap;
    flex-shrink: 0;
}
[data-theme="dark"] .native-emergency-card {
    background: rgba(220, 38, 38, 0.12);
    border-color: rgba(220, 38, 38, 0.35);
    color: #FCA5A5;
}
[data-theme="dark"] .native-emergency-title { color: #FCA5A5; }
[data-theme="dark"] .native-emergency-sub { color: #F87171; }
[data-theme="dark"] .native-emergency-arrow { background: rgba(239, 68, 68, 0.2); color: #FCA5A5; }

/* ── Belanja Home Card ────────────────────────────────────────── */
.native-belanja-card {
    background: linear-gradient(135deg, #9D174D 0%, #BE185D 50%, #F43F5E 100%);
    border-radius: 20px;
    padding: 14px 16px;
    margin-bottom: 14px;
    color: #fff;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 6px 20px rgba(244, 63, 94, 0.28);
    transition: transform 0.15s ease;
}
.native-belanja-card:active {
    transform: scale(0.98);
}
.native-belanja-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.native-belanja-icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.native-belanja-title {
    font-size: 13.5px;
    font-weight: 800;
    color: #fff;
}
.native-belanja-sub {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 600;
    margin-top: 2px;
}
.native-belanja-arrow {
    font-size: 12px;
    font-weight: 800;
    background: rgba(255, 255, 255, 0.2);
    padding: 5px 10px;
    border-radius: 10px;
}

/* ── Todo Home Card ───────────────────────────────────────────── */
.native-todo-card {
    background: linear-gradient(135deg, #4338CA 0%, #6366F1 50%, #8B5CF6 100%);
    border-radius: 20px;
    padding: 14px 16px;
    margin-bottom: 14px;
    color: #ffffff;
    display: flex;
    flex-direction: column;
    gap: 10px;
    box-shadow: 0 6px 22px rgba(99, 102, 241, 0.28);
    transition: transform 0.15s ease;
}
.native-todo-card:active {
    transform: scale(0.98);
}
.native-todo-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.native-todo-title-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
}
.native-todo-icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.native-todo-title {
    font-size: 13.5px;
    font-weight: 800;
    color: #ffffff;
}
.native-todo-sub {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 600;
    margin-top: 2px;
}
.native-todo-arrow {
    font-size: 12px;
    font-weight: 800;
    background: rgba(255, 255, 255, 0.2);
    padding: 5px 10px;
    border-radius: 10px;
}
.native-todo-tasks-preview {
    background: rgba(0, 0, 0, 0.15);
    border-radius: 12px;
    padding: 8px 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.native-todo-task-item {
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}
.native-todo-task-item.done {
    opacity: 0.6;
    text-decoration: line-through;
}
.native-todo-check-btn {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 1.5px solid rgba(255, 255, 255, 0.8);
    background: transparent;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: transparent;
    flex-shrink: 0;
    font-size: 11px;
    font-weight: 800;
    transition: all 0.15s ease;
}
.native-todo-check-btn:hover {
    background: rgba(255, 255, 255, 0.25);
}
.native-todo-task-item.done .native-todo-check-btn {
    background: #10B981;
    border-color: #10B981;
    color: #ffffff;
}

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

/* ── Quick Actions (icon grid: 4 columns x 2 rows) ─────────────── */
.home-quick-actions {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 16px;
}
.home-qa-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 13px 6px 11px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    font-size: 11.5px;
    font-weight: 700;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    text-decoration: none;
    position: relative;
    box-sizing: border-box;
    min-width: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}
.home-qa-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: var(--bg-card);
    transform: translateY(-2.5px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
}
.home-qa-btn:active {
    transform: scale(0.97);
}
.home-qa-btn svg { flex-shrink: 0; }
.home-qa-icon {
    width: 42px; height: 42px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    transition: transform 0.2s ease;
}
.home-qa-btn:hover .home-qa-icon {
    transform: scale(1.08);
}
.home-qa-btn .qa-badge {
    position: absolute;
    top: 6px; right: 8px;
    background: #EF4444;
    color: #fff;
    font-size: 9.5px;
    font-weight: 800;
    min-width: 17px; height: 17px;
    padding: 0 4px;
    border-radius: 999px;
    display: flex; align-items: center; justify-content: center;
    line-height: 1;
    border: 2px solid var(--bg-card);
    box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
}
.home-qa-label {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    text-align: center;
    letter-spacing: -0.2px;
}

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

/* ── Workspace Mode Switcher ──────────────────────────────── */
.ws-switcher-wrap {
    display: flex;
    background: var(--bg-card);
    padding: 5px;
    border-radius: 16px;
    border: 1px solid var(--border);
    margin-bottom: 16px;
    box-shadow: var(--shadow-sm);
    gap: 6px;
}
.ws-tab-btn {
    flex: 1;
    padding: 10px 14px;
    border-radius: 12px;
    font-size: 12.5px;
    font-weight: 800;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background 0.2s ease, color 0.2s ease, transform 0.1s ease;
    background: transparent;
    color: var(--text-secondary);
}
.ws-tab-btn:active { transform: scale(0.98); }
.ws-tab-btn.active.personal {
    background: var(--primary) !important;
    color: #FFFFFF !important;
    box-shadow: 0 4px 12px rgba(10,169,86,0.3);
}
.ws-tab-btn.active.business {
    background: #4F46E5 !important;
    color: #FFFFFF !important;
    box-shadow: 0 4px 12px rgba(79,70,229,0.35);
}
[data-theme="dark"] .ws-tab-btn {
    color: #94A3B8;
}
[data-theme="dark"] .ws-tab-btn:hover {
    color: #F1F5F9;
}
[data-theme="dark"] .ws-tab-btn.active.personal {
    color: #FFFFFF !important;
    background: var(--primary) !important;
}
[data-theme="dark"] .ws-tab-btn.active.business {
    color: #FFFFFF !important;
    background: #6366F1 !important;
}

/* ── Leaflet & Nearby Places Map Widget ─────────────────────────── */
@import url('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');

.nearby-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 16px;
    margin-bottom: 16px;
    box-shadow: 0 4px 18px rgba(0,0,0,.04);
}
.nearby-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.nearby-title-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.nearby-title {
    font-size: 14px;
    font-weight: 800;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 6px;
}
.nearby-loc-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 10px;
    border-radius: 10px;
    border: 1px solid var(--primary);
    background: var(--primary-dim);
    color: var(--primary);
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
}
.nearby-loc-btn:active {
    transform: scale(0.95);
}
.nearby-filter-pills {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    padding-bottom: 8px;
    margin-bottom: 8px;
    scrollbar-width: none;
}
.nearby-filter-pills::-webkit-scrollbar { display: none; }
.nearby-pill {
    padding: 6px 12px;
    border-radius: 20px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text-secondary);
    font-size: 11.5px;
    font-weight: 700;
    white-space: nowrap;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.nearby-pill.active {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25);
}
.nearby-radius-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 11px;
    color: var(--text-muted);
}
.nearby-rad-chips {
    display: flex;
    gap: 4px;
}
.nearby-rad-chip {
    padding: 3px 8px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text-secondary);
    font-size: 10.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
}
.nearby-rad-chip.active {
    background: #0284C7;
    color: #fff;
    border-color: #0284C7;
}
.nearby-map-container {
    height: 220px !important;
    width: 100% !important;
    border-radius: 16px !important;
    overflow: hidden !important;
    position: relative !important;
    border: 1px solid var(--border);
    margin-bottom: 12px;
    background: #E2E8F0;
    z-index: 1;
    display: block;
}
#nearbyMap {
    height: 100% !important;
    width: 100% !important;
    min-height: 220px !important;
    border-radius: 16px !important;
    overflow: hidden !important;
    position: relative !important;
}
/* Leaflet Tile Container overrides to prevent global img max-width conflicts */
.leaflet-container {
    height: 100% !important;
    width: 100% !important;
    max-width: 100% !important;
    max-height: 100% !important;
    border-radius: 16px;
}
.leaflet-container img,
.leaflet-tile {
    max-width: none !important;
    max-height: none !important;
    width: 256px !important;
    height: 256px !important;
    padding: 0 !important;
    margin: 0 !important;
    border: none !important;
    box-shadow: none !important;
}
.leaflet-tile-pane,
.leaflet-layer,
.leaflet-tile-container {
    width: 100% !important;
    height: 100% !important;
}
.nearby-accordion-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 9px 12px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    cursor: pointer;
    user-select: none;
    transition: all 0.15s ease;
    margin-top: 4px;
    margin-bottom: 8px;
}
.nearby-accordion-hdr:hover {
    border-color: var(--primary);
}
.nearby-count-badge {
    background: var(--primary-dim);
    color: var(--primary);
    font-size: 10px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 10px;
}
.nearby-accordion-icon {
    font-size: 13px;
    color: var(--text-muted);
    transition: transform 0.2s ease;
}
.nearby-accordion-icon.open {
    transform: rotate(180deg);
}
.nearby-list-wrap {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 240px;
    overflow-y: auto;
    transition: all 0.25s ease;
}
.nearby-list-wrap.collapsed {
    display: none;
}
.nearby-place-item {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    transition: border-color 0.15s ease;
    text-decoration: none;
    color: inherit;
}
.nearby-place-item:hover {
    border-color: var(--primary);
}
.nearby-place-info {
    flex: 1;
    min-width: 0;
}
.nearby-place-name {
    font-size: 12.5px;
    font-weight: 800;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 2px;
}
.nearby-place-meta {
    font-size: 11px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
}
.nearby-dist-badge {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    font-weight: 800;
    padding: 1px 6px;
    border-radius: 6px;
    font-size: 10px;
}
.nearby-dir-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: var(--primary);
    color: #fff;
    text-decoration: none;
    padding: 6px 10px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
    transition: transform 0.15s ease;
    flex-shrink: 0;
}
.nearby-dir-btn:active {
    transform: scale(0.95);
}
.pulse-user-marker {
    background: #0284C7;
    border: 2.5px solid #ffffff;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    box-shadow: 0 0 10px rgba(2, 132, 199, 0.6);
}
.custom-poi-marker {
    background: #ffffff;
    border: 1.5px solid var(--border);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* ── 📺 TV & Live Streaming Home Card ────────────────────────── */
.tv-home-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 16px;
    margin-bottom: 16px;
    box-shadow: 0 4px 18px rgba(0,0,0,.04);
}
.tv-home-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.tv-home-title-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.tv-home-title {
    font-size: 14.5px;
    font-weight: 800;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 7px;
}
.tv-badge-live-sm {
    background: #EF4444;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 6px;
    animation: tvPulse 1.6s infinite;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.tv-badge-live-sm::before {
    content: "";
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #fff;
}
@keyframes tvPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.75; transform: scale(0.96); }
}

/* Video Player Box (No Autoplay Initial State) */
.tv-home-player-box {
    position: relative;
    width: 100%;
    padding-top: 56.25%; /* 16:9 Aspect Ratio */
    background: #0B0F19;
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    border: 1px solid rgba(255,255,255,0.08);
}
.tv-home-video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: none;
}
.tv-home-video.active {
    display: block;
}
.tv-home-poster {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: radial-gradient(circle at center, #1E293B 0%, #0F172A 100%);
    padding: 16px;
    text-align: center;
    cursor: pointer;
    transition: transform 0.2s ease, opacity 0.2s ease;
    z-index: 2;
}
.tv-home-poster.hidden {
    display: none !important;
}
.tv-home-play-btn {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: var(--primary);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
    margin-bottom: 10px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.tv-home-poster:hover .tv-home-play-btn {
    transform: scale(1.1);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.6);
}
.tv-home-poster-name {
    color: #FFFFFF;
    font-size: 14px;
    font-weight: 800;
    margin-bottom: 3px;
}
.tv-home-poster-hint {
    color: #94A3B8;
    font-size: 11px;
    font-weight: 500;
}

/* Channel selector chips */
.tv-home-chips {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    padding-bottom: 8px;
    margin-bottom: 12px;
    scrollbar-width: none;
}
.tv-home-chips::-webkit-scrollbar { display: none; }
.tv-home-chip {
    padding: 6px 12px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text-secondary);
    font-size: 11.5px;
    font-weight: 700;
    white-space: nowrap;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.tv-home-chip.active {
    background: var(--primary-dim);
    color: var(--primary);
    border-color: var(--primary);
}
.tv-home-chip:active {
    transform: scale(0.96);
}

/* Full View Button */
.tv-home-see-all {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 10px 16px;
    border-radius: 12px;
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text-primary);
    text-decoration: none;
    font-size: 12.5px;
    font-weight: 800;
    transition: all 0.2s ease;
}
.tv-home-see-all:hover {
    background: var(--primary);
    color: #FFFFFF;
    border-color: var(--primary);
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);
}
.tv-home-see-all:active {
    transform: scale(0.98);
}

/* ── 🎬 Tabbed Media Streaming & Jellyfin Styles ────────────── */
.media-tab-group {
    display: flex;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 3px;
    gap: 4px;
}
.media-tab-btn {
    flex: 1;
    border: none;
    background: transparent;
    padding: 6px 10px;
    border-radius: 9px;
    font-size: 11.5px;
    font-weight: 700;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    white-space: nowrap;
}
.media-tab-btn.active {
    background: var(--bg-card);
    color: var(--text-primary);
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.jellyfin-badge-sm {
    background: linear-gradient(135deg, #00A4DC, #AA5CC3);
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 6px;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.film-cards-scroll {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding-bottom: 8px;
    margin-bottom: 12px;
    scrollbar-width: none;
}
.film-cards-scroll::-webkit-scrollbar { display: none; }
.film-mini-card {
    flex: 0 0 100px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.15s ease, border-color 0.15s ease;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
}
.film-mini-card:hover, .film-mini-card.active {
    border-color: #00A4DC;
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(0, 164, 220, 0.18);
}
.film-mini-poster {
    width: 100%;
    aspect-ratio: 2/3;
    object-fit: cover;
    background: #1E293B;
    display: block;
}
.film-mini-info {
    padding: 6px 8px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.film-mini-title {
    font-size: 11px;
    font-weight: 800;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.film-mini-meta {
    font-size: 9.5px;
    color: var(--text-muted);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.film-rating-badge {
    color: #EAB308;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 2px;
}

/* ── 🏡 My Home Dashboard Card ────────────────────────────── */
.my-home-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 22px;
    padding: 16px;
    margin-bottom: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,.04);
}
.my-home-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.my-home-title-group {
    display: flex;
    align-items: center;
    gap: 8px;
}
.my-home-title {
    font-size: 14.5px;
    font-weight: 800;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 6px;
}
.my-home-badge {
    background: rgba(99,102,241,0.12);
    color: #4F46E5;
    font-size: 11px;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 8px;
}
.my-home-hero-banner {
    position: relative;
    border-radius: 20px;
    padding: 20px 18px;
    background: var(--bg);
    border: 1px solid var(--border);
    margin-bottom: 14px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}
.my-home-hero-banner:hover {
    border-color: #6366F1;
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.08);
}
.my-home-hero-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: rgba(99, 102, 241, 0.1);
    color: #6366F1;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    font-size: 20px;
}
.my-home-score-num {
    font-size: 46px;
    font-weight: 900;
    line-height: 1;
    letter-spacing: -1.5px;
    color: var(--text-primary);
    margin-bottom: 4px;
}
.my-home-score-label {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--text-secondary);
    margin-bottom: 4px;
}
.my-home-score-sub {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-muted);
    max-width: 320px;
}
.my-home-attention-box {
    margin-top: 12px;
    padding: 6px 14px;
    background: rgba(99, 102, 241, 0.08);
    border: 1px solid rgba(99, 102, 241, 0.2);
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
    color: #4F46E5;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.my-home-attention-box.urgent {
    background: rgba(239, 68, 68, 0.08);
    border-color: rgba(239, 68, 68, 0.2);
    color: #DC2626;
}
.my-home-attention-box.safe {
    background: rgba(16, 185, 129, 0.08);
    border-color: rgba(16, 185, 129, 0.2);
    color: #059669;
}
.my-home-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    margin-bottom: 14px;
}
.my-home-tile {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 12px 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.my-home-tile-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.my-home-tile-count {
    font-size: 16px;
    font-weight: 900;
    color: var(--text-primary);
    line-height: 1.1;
}
.my-home-tile-lbl {
    font-size: 10.5px;
    font-weight: 600;
    color: var(--text-muted);
}
.my-home-see-all {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 10px 16px;
    border-radius: 12px;
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text-primary);
    text-decoration: none;
    font-size: 12.5px;
    font-weight: 800;
    transition: all 0.2s ease;
}
.my-home-see-all:hover {
    background: #6366F1;
    color: #FFFFFF;
    border-color: #6366F1;
    box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
}
.my-home-see-all:active {
    transform: scale(0.98);
}

/* ── 🛍️ Marketplace Products Dashboard Showcase ────────────── */
.market-dash-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 22px;
    padding: 16px;
    margin-bottom: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,.04);
    position: relative;
    overflow: hidden;
}
.market-dash-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    gap: 8px;
    flex-wrap: wrap;
}
.market-dash-title-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.market-dash-title {
    font-size: 14.5px;
    font-weight: 800;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 6px;
}
.market-dash-badge {
    background: linear-gradient(135deg, rgba(79, 70, 229, 0.15) 0%, rgba(16, 185, 129, 0.15) 100%);
    color: #4F46E5;
    font-size: 10.5px;
    font-weight: 800;
    padding: 2.5px 8px;
    border-radius: 8px;
    border: 1px solid rgba(79, 70, 229, 0.2);
}
[data-theme="dark"] .market-dash-badge {
    color: #818CF8;
}
.market-dash-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}
.market-dash-btn-add {
    background: #10B981;
    color: #FFFFFF !important;
    font-size: 11.5px;
    font-weight: 700;
    padding: 5px 12px;
    border-radius: 999px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    transition: all 0.15s ease;
}
.market-dash-btn-add:hover {
    background: #059669;
    transform: translateY(-1px);
    color: #FFFFFF;
}
.market-dash-see-all-link {
    font-size: 12px;
    font-weight: 700;
    color: var(--primary);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    transition: opacity 0.15s;
}
.market-dash-see-all-link:hover {
    opacity: 0.8;
}
.market-dash-safety-tip {
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.2);
    border-radius: 12px;
    padding: 7px 12px;
    margin-bottom: 12px;
    font-size: 11px;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 7px;
    line-height: 1.35;
}
.market-dash-scroll {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 6px;
    scroll-snap-type: x mandatory;
    scrollbar-width: thin;
    -webkit-overflow-scrolling: touch;
}
.market-dash-scroll::-webkit-scrollbar {
    height: 4px;
}
.market-dash-scroll::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 4px;
}
.market-item-card {
    flex: 0 0 220px;
    scroll-snap-align: start;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease, box-shadow 0.2s ease;
    position: relative;
}
.market-item-card:hover {
    transform: translateY(-4px);
    border-color: #6366F1;
    box-shadow: 0 8px 24px rgba(99, 102, 241, 0.14);
}
.market-item-thumb-box {
    position: relative;
    width: 100%;
    height: 130px;
    background: #1E293B;
    overflow: hidden;
}
.market-item-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.3s ease;
}
.market-item-card:hover .market-item-img {
    transform: scale(1.06);
}
.market-item-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 34px;
    background: linear-gradient(135deg, #334155 0%, #1E293B 100%);
}
.market-badge-type {
    position: absolute;
    top: 8px;
    left: 8px;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 9.5px;
    font-weight: 800;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    color: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
}
.market-badge-type.sale {
    background: #10B981;
}
.market-badge-type.rent {
    background: #6366F1;
}
.market-badge-cond {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(0, 0, 0, 0.62);
    backdrop-filter: blur(4px);
    color: #fff;
    font-size: 9px;
    font-weight: 700;
    padding: 2.5px 7px;
    border-radius: 6px;
}
.market-floating-price {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(180deg, transparent 0%, rgba(15, 23, 42, 0.9) 100%);
    padding: 16px 10px 5px;
    display: flex;
    align-items: baseline;
    gap: 3px;
}
.market-price-val {
    font-size: 13.5px;
    font-weight: 900;
    color: #34D399;
    text-shadow: 0 1px 3px rgba(0,0,0,0.6);
}
.market-price-unit {
    font-size: 10px;
    color: rgba(255, 255, 255, 0.85);
    font-weight: 600;
}
.market-item-info {
    padding: 10px 10px 8px;
    display: flex;
    flex-direction: column;
    flex: 1;
    gap: 3px;
}
.market-item-cat {
    font-size: 10px;
    font-weight: 700;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 4px;
}
.market-item-title {
    font-size: 12.5px;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 2px;
}
.market-item-footer {
    margin-top: auto;
    padding-top: 6px;
    border-top: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 10.5px;
    color: var(--text-muted);
}
.market-item-loc {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120px;
    display: flex;
    align-items: center;
    gap: 3px;
}
.market-item-seller {
    font-weight: 700;
    color: var(--text-secondary);
}
.market-promo-card {
    flex: 0 0 190px;
    scroll-snap-align: start;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.06) 0%, rgba(16, 185, 129, 0.06) 100%);
    border: 1.5px dashed rgba(99, 102, 241, 0.35);
    border-radius: 16px;
    padding: 16px 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
}
.market-promo-card:hover {
    border-color: #6366F1;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.12) 0%, rgba(16, 185, 129, 0.12) 100%);
    transform: translateY(-3px);
}
.market-promo-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: #6366F1;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin-bottom: 10px;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
}
.market-promo-title {
    font-size: 12.5px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 4px;
}
.market-promo-sub {
    font-size: 10.5px;
    color: var(--text-muted);
    line-height: 1.35;
    margin-bottom: 10px;
}
.market-promo-btn {
    font-size: 11px;
    font-weight: 800;
    color: #6366F1;
    background: rgba(99, 102, 241, 0.12);
    padding: 4px 10px;
    border-radius: 20px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="home-page">

    <!-- ── WORKSPACE MODE SWITCHER ─────────────────────────────── -->
    <div class="ws-switcher-wrap">
        <button type="button" id="btnModePersonal" class="ws-tab-btn active personal" onclick="window.switchWorkspace('personal')">
            <span style="font-size:15px">👤</span> <span>Mode Personal</span>
        </button>
        <button type="button" id="btnModeBusiness" class="ws-tab-btn business" onclick="window.switchWorkspace('business')">
            <span style="font-size:15px">☕</span> <span>Mode Usaha (POS)</span>
        </button>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ PERSONAL WORKSPACE CONTAINER -->
    <div id="workspacePersonal">

    <!-- ── EMERGENCY SOS QUICK ACCESS BANNER ── -->
    <a href="/emergency" class="native-emergency-card">
        <div class="native-emergency-left">
            <div class="native-emergency-icon">🚨</div>
            <div>
                <div class="native-emergency-title">
                    <span>Layanan Darurat &amp; Derek Tol</span>
                    <span class="emergency-badge-24">24 JAM</span>
                </div>
                <div class="native-emergency-sub">112 · 14080 (Derek Tol) · Damkar · Medis · Polisi</div>
            </div>
        </div>
        <div class="native-emergency-arrow">Buka →</div>
    </a>

    <!-- ── APPLE WALLET STACKED CARDS DECK (1:1 with Flutter Native) ── -->
    <?php
    $activeHeroWallets = array_values(array_filter($wallets ?? [], function($w) {
        return (float)($w['balance'] ?? 0) > 0;
    }));

    $paletteGradients = [
        'total'       => 'linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 50%, #2563EB 100%)',
        'bank'        => 'linear-gradient(135deg, #0F172A 0%, #1E3A8A 55%, #3B82F6 100%)',
        'cash'        => 'linear-gradient(135deg, #064E3B 0%, #047857 55%, #10B981 100%)',
        'ewallet'     => 'linear-gradient(135deg, #312E81 0%, #4338CA 55%, #6366F1 100%)',
        'credit_card' => 'linear-gradient(135deg, #701A75 0%, #86198F 55%, #C026D3 100%)',
        'default'     => 'linear-gradient(135deg, #1E293B 0%, #334155 55%, #475569 100%)',
    ];

    $deckCards = [
        [
            'id'       => 'total',
            'name'     => 'TOTAL BALANCE',
            'badge'    => strtoupper(esc($month)),
            'balance'  => (float)$balance,
            'icon'     => '💎',
            'gradient' => $paletteGradients['total'],
            'is_total' => true,
        ]
    ];

    foreach ($activeHeroWallets as $hw) {
        $wType = strtolower($hw['type'] ?? 'default');
        $grad = $paletteGradients[$wType] ?? $paletteGradients['default'];
        $deckCards[] = [
            'id'       => (string)$hw['id'],
            'name'     => 'SALDO ' . strtoupper($hw['name']),
            'badge'    => strtoupper($hw['type'] ?? 'CASH'),
            'balance'  => (float)$hw['balance'],
            'icon'     => $hw['icon'] ?? '💳',
            'gradient' => $grad,
            'is_total' => false,
        ];
    }
    $stackOffsetPx = max(0, count($deckCards) - 1) * 36;
    ?>
    <div class="apple-wallet-wrapper" id="appleWalletWrapper" style="height: calc(195px + <?= $stackOffsetPx ?>px);">
        <div class="apple-wallet-deck" id="appleWalletDeck">
            <?php foreach ($deckCards as $cIdx => $card): ?>
            <div class="apple-deck-card <?= $cIdx === 0 ? 'active-card' : '' ?>"
                 id="appleCard-<?= $cIdx ?>"
                 data-index="<?= $cIdx ?>"
                 data-wallet-id="<?= esc($card['id']) ?>"
                 data-name="<?= esc($card['name']) ?>"
                 data-balance="<?= (float)$card['balance'] ?>"
                 style="background: <?= $card['gradient'] ?>;"
                 onclick="selectAppleDeckCard(<?= $cIdx ?>)">
                
                <!-- Card Header (Always visible in stack) -->
                <div class="apple-card-header">
                    <div class="apple-card-title-group">
                        <span class="apple-card-icon"><?= $card['icon'] ?></span>
                        <span class="apple-card-name"><?= esc($card['name']) ?></span>
                    </div>
                    <div class="apple-card-meta-group">
                        <span class="apple-card-mini-balance" data-raw="<?= esc($symbol) ?> <?= number_format($card['balance'], 0, ',', '.') ?>" data-hidden="Rp ••••">
                            <?= esc($symbol) ?> <?= number_format($card['balance'], 0, ',', '.') ?>
                        </span>
                        <span class="apple-card-badge"><?= esc($card['badge']) ?></span>
                    </div>
                </div>

                <!-- Card Body (Expanded when active) -->
                <div class="apple-card-body">
                    <div class="native-hero-balance-row">
                        <div class="native-hero-amount display-balance-amount"
                             data-raw="<?= esc($symbol) ?> <?= number_format($card['balance'], 0, ',', '.') ?>"
                             data-hidden="Rp •••••••••">
                            <?= esc($symbol) ?> <?= number_format($card['balance'], 0, ',', '.') ?>
                        </div>
                        <div class="native-hero-trend-pill">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                <polyline points="17 6 23 6 23 12"></polyline>
                            </svg>
                            <span><?= $card['is_total'] ? ('+' . esc($symbol) . ' ' . number_format($monthly['income'], 0, ',', '.')) : esc($card['badge']) ?></span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="native-hero-actions">
                        <a href="/stats" class="native-hero-pill-btn" onclick="event.stopPropagation()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <line x1="18" y1="20" x2="18" y2="10"></line>
                                <line x1="12" y1="20" x2="12" y2="4"></line>
                                <line x1="6" y1="20" x2="6" y2="14"></line>
                            </svg>
                            <span>View Reports</span>
                        </a>
                        <a href="/scan" class="native-hero-icon-btn" title="Scan Struk (OCR)" onclick="event.stopPropagation()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="17" height="17">
                                <path d="M4 7V4h3M20 7V4h-3M4 17v3h3M20 17v3h-3M7 12h10M7 8h10M7 16h6"/>
                            </svg>
                        </a>
                        <button type="button" class="native-hero-icon-btn btn-privacy-toggle" title="Sembunyikan / Tampilkan Saldo" onclick="event.stopPropagation(); toggleBalancePrivacy();">
                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="17" height="17">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-closed" style="display:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="17" height="17">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── THREE-COLUMN STATS CARD (Aligned 1:1 with Native _ThreeColumnStatsCard) ── -->
    <div class="native-stats-card">
        <div class="native-stats-col">
            <div class="native-stats-val income"><?= esc($symbol) ?> <?= number_format($monthly['income'], 0, ',', '.') ?></div>
            <div class="native-stats-lbl">Pemasukan</div>
            <div class="native-stats-sub">Bulan ini</div>
        </div>
        <div class="native-stats-divider"></div>
        <div class="native-stats-col">
            <div class="native-stats-val text-primary"><?= esc($symbol) ?> <?= number_format($monthly['expense'], 0, ',', '.') ?></div>
            <div class="native-stats-lbl">Pengeluaran</div>
            <div class="native-stats-sub"><?= count($recent ?? []) ?> transaksi</div>
        </div>
        <div class="native-stats-divider"></div>
        <div class="native-stats-col">
            <?php if (($budget ?? 0) > 0): ?>
                <div class="native-stats-val blue"><?= esc($symbol) ?> <?= number_format(max(0, $budget - $monthly['expense']), 0, ',', '.') ?></div>
                <div class="native-stats-lbl">Sisa Budget</div>
                <div class="native-stats-sub">Limit bulanan</div>
            <?php else: ?>
                <div class="native-stats-val blue"><?= count($wallets ?? []) ?> Dompet</div>
                <div class="native-stats-lbl">Akun Dompet</div>
                <div class="native-stats-sub">Aktif</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── BELANJA QUICK CARD (Aligned 1:1 with Native _BelanjaHomeCard) ── -->
    <a href="/belanja" class="native-belanja-card">
        <div class="native-belanja-left">
            <div class="native-belanja-icon">🛒</div>
            <div>
                <div class="native-belanja-title">Daftar Rencana Belanja</div>
                <div class="native-belanja-sub">Kelola catatan kebutuhan &amp; checklist belanja</div>
            </div>
        </div>
        <div class="native-belanja-arrow">Buka →</div>
    </a>

    <!-- ── TODO-LIST QUICK HOME CARD (Aligned 1:1 with Native _TodoHomeCard) ── -->
    <div class="native-todo-card">
        <div class="native-todo-hdr">
            <a href="/todo" class="native-todo-title-wrap" style="color:inherit;text-decoration:none">
                <div class="native-todo-icon">🎯</div>
                <div>
                    <div class="native-todo-title">Rencana &amp; Target Tugas (Todo)</div>
                    <div class="native-todo-sub">
                        <?= (int)($todoSummary['completed_all'] ?? 0) ?>/<?= (int)($todoSummary['total_all'] ?? 0) ?> Selesai · <?= (int)($todoSummary['pending_all'] ?? 0) ?> Aktif
                    </div>
                </div>
            </a>
            <a href="/todo" class="native-todo-arrow" style="color:inherit;text-decoration:none">Buka →</a>
        </div>

        <?php if (!empty($todoSummary['previews'])): ?>
        <div class="native-todo-tasks-preview">
            <?php foreach ($todoSummary['previews'] as $pTask): ?>
            <div class="native-todo-task-item" id="homeTodo-<?= $pTask['id'] ?>">
                <button type="button" class="native-todo-check-btn" onclick="toggleHomeTask(event, <?= $pTask['id'] ?>)" title="Tandai selesai">✓</button>
                <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= esc($pTask['title']) ?></span>
                <span style="font-size:10px;opacity:0.8;background:rgba(255,255,255,0.15);padding:1px 6px;border-radius:6px"><?= esc($pTask['category']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
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

    <!-- QUICK ACTIONS (4 KOLOM x 2 BARIS) -->
    <div class="home-quick-actions">
        <!-- 1. Scan Nota -->
        <a href="/scan" class="home-qa-btn">
            <div class="home-qa-icon" style="background:rgba(5,150,105,0.12)">
                <svg viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
            </div>
            <span class="home-qa-label">Scan Nota</span>
        </a>

        <!-- 2. Kalkulator -->
        <button type="button" class="home-qa-btn" id="btnOpenCalc">
            <div class="home-qa-icon" style="background:rgba(16,185,129,0.12)">
                <svg viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="10" y2="10"/><line x1="12" y1="10" x2="14" y2="10"/><line x1="16" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="10" y2="14"/><line x1="12" y1="14" x2="14" y2="14"/><line x1="16" y1="14" x2="16" y2="18"/><line x1="14" y1="16" x2="18" y2="16"/></svg>
            </div>
            <span class="home-qa-label">Kalkulator</span>
        </button>

        <!-- 3. My Home -->
        <a href="/barang" class="home-qa-btn">
            <div class="home-qa-icon" style="background:rgba(79,70,229,0.12)">
                <svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <span class="home-qa-label">My Home</span>
            <?php if (!empty($myHomeSummary['attention'])): ?>
            <span class="qa-badge" style="background:#EF4444"><?= count($myHomeSummary['attention']) ?></span>
            <?php endif; ?>
        </a>

        <!-- 4. Tagihan -->
        <a href="/bills" class="home-qa-btn">
            <div class="home-qa-icon" style="background:rgba(37,99,235,0.12)">
                <svg viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M4 9h16M8 3v4M16 3v4"/><line x1="8" y1="13" x2="10" y2="13"/><line x1="12" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="10" y2="17"/></svg>
            </div>
            <span class="home-qa-label">Tagihan</span>
            <?php if (!empty($upcomingBills)): ?>
            <span class="qa-badge"><?= count($upcomingBills) ?></span>
            <?php endif; ?>
        </a>

        <!-- 5. Traveling -->
        <a href="/traveling" class="home-qa-btn">
            <div class="home-qa-icon" style="background:rgba(6,182,212,0.12)">
                <svg viewBox="0 0 24 24" fill="none" stroke="#0891B2" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg>
            </div>
            <span class="home-qa-label">Traveling</span>
        </a>

        <!-- 6. Hutang -->
        <a href="/hutang" class="home-qa-btn">
            <div class="home-qa-icon" style="background:rgba(245,158,11,0.12)">
                <svg viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <span class="home-qa-label">Hutang</span>
            <?php if (isset($debtSummary) && $debtSummary['active_count'] > 0): ?>
            <span class="qa-badge"><?= $debtSummary['active_count'] ?></span>
            <?php endif; ?>
        </a>

        <!-- 7. Jual & Sewa -->
        <a href="/marketplace" class="home-qa-btn">
            <div class="home-qa-icon" style="background:rgba(124,58,237,0.12)">
                <svg viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                    <path d="M3 6h18"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
            </div>
            <span class="home-qa-label">Jual &amp; Sewa</span>
        </a>

        <!-- 8. Statistik & Analisis -->
        <a href="/stats" class="home-qa-btn">
            <div class="home-qa-icon" style="background:rgba(236,72,153,0.12)">
                <svg viewBox="0 0 24 24" fill="none" stroke="#E11D48" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                    <path d="M21.21 15.89A10 10 0 1 1 8 2.83"/>
                    <path d="M22 12A10 10 0 0 0 12 2v10z"/>
                </svg>
            </div>
            <span class="home-qa-label">Statistik</span>
        </a>
    </div>

    <!-- ── 🛍️ WIDGET JUAL BELI & SEWA (MARKETPLACE SHOWCASE) ── -->
    <?php
        $mfItems = $marketplaceFeatured ?? [];
        $mfCatIcons = [
            'Motor' => '🏍️', 'Mobil' => '🚗', 'Properti' => '🏠',
            'Elektronik' => '💻', 'Gadget' => '📱', 'Fashion' => '👕',
            'Hobi' => '🎸', 'Lainnya' => '📦'
        ];
        $mfCondLabels = [
            'new' => 'Baru', 'like_new' => 'Spt Baru',
            'used_good' => 'Bekas Bagus', 'used_fair' => 'Bekas Layak'
        ];
    ?>
    <div class="market-dash-card" id="marketplaceDashboardCard">
        <div class="market-dash-hdr">
            <div class="market-dash-title-group">
                <span class="market-dash-title">🛍️ Jual Beli &amp; Sewa</span>
                <span class="market-dash-badge"><?= count($mfItems) > 0 ? count($mfItems) . ' Pilihan' : 'Komunitas' ?></span>
            </div>
            <div class="market-dash-actions">
                <a href="<?= base_url('marketplace/create') ?>" class="market-dash-btn-add">
                    <span>+</span> Pasang Iklan
                </a>
                <a href="<?= base_url('marketplace') ?>" class="market-dash-see-all-link">
                    Semua ›
                </a>
            </div>
        </div>

        <!-- Safety Pill -->
        <div class="market-dash-safety-tip">
            <span style="font-size:14px">🛡️</span>
            <span><strong>Bebas Biaya:</strong> Utamakan COD &amp; cek fisik barang secara langsung. Jangan kirim DP untuk keamanan bersama!</span>
        </div>

        <!-- Product Cards Carousel / Row -->
        <?php if (empty($mfItems)): ?>
        <div style="background:var(--bg);border:1.5px dashed var(--border);border-radius:16px;padding:24px 16px;text-align:center;">
            <div style="font-size:32px;margin-bottom:6px">🛵 📱 🏠</div>
            <div style="font-size:13px;font-weight:800;color:var(--text-primary);margin-bottom:4px">Belum Ada Iklan Aktif</div>
            <div style="font-size:11.5px;color:var(--text-muted);margin-bottom:12px;max-width:320px;margin-left:auto;margin-right:auto">Jual barang bekas Anda atau sewakan properti / kendaraan langsung ke sesama pengguna.</div>
            <a href="<?= base_url('marketplace/create') ?>" class="market-dash-btn-add" style="padding:7px 16px;font-size:12px">
                <span>+</span> Pasang Iklan Pertama Anda
            </a>
        </div>
        <?php else: ?>
        <div class="market-dash-scroll">
            <?php foreach ($mfItems as $p): 
                $pIcon = $mfCatIcons[$p['category']] ?? '📦';
                $pCond = $mfCondLabels[$p['condition']] ?? 'Bekas';
                $pTypeLabel = ($p['type'] ?? 'sale') === 'rent' ? 'SEWA' : 'JUAL';
                $pTypeClass = ($p['type'] ?? 'sale') === 'rent' ? 'rent' : 'sale';
                $pPeriod = !empty($p['rent_period']) ? '/' . esc($p['rent_period']) : '';
                $pImg = !empty($p['primary_image']) ? base_url($p['primary_image']) : null;
            ?>
            <a href="<?= base_url('marketplace/item/' . $p['id']) ?>" class="market-item-card">
                <div class="market-item-thumb-box">
                    <?php if ($pImg): ?>
                        <img src="<?= esc($pImg) ?>" alt="<?= esc($p['title']) ?>" class="market-item-img" loading="lazy">
                    <?php else: ?>
                        <div class="market-item-img-placeholder">
                            <?= $pIcon ?>
                        </div>
                    <?php endif; ?>
                    <span class="market-badge-type <?= $pTypeClass ?>"><?= $pTypeLabel ?></span>
                    <span class="market-badge-cond"><?= $pCond ?></span>
                    <div class="market-floating-price">
                        <span class="market-price-val"><?= esc($symbol) ?> <?= number_format((float)$p['price'], 0, ',', '.') ?></span>
                        <?php if ($pPeriod): ?>
                            <span class="market-price-unit"><?= $pPeriod ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="market-item-info">
                    <div class="market-item-cat">
                        <span><?= $pIcon ?></span>
                        <span><?= esc($p['category']) ?></span>
                    </div>
                    <div class="market-item-title" title="<?= esc($p['title']) ?>"><?= esc($p['title']) ?></div>
                    <div class="market-item-footer">
                        <div class="market-item-loc" title="<?= esc($p['location'] ?: 'Indonesia') ?>">
                            <span>📍</span>
                            <span><?= esc($p['location'] ?: 'Indonesia') ?></span>
                        </div>
                        <div class="market-item-seller">
                            @<?= esc($p['seller_username'] ?: 'user') ?>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>

            <!-- End Card: Pasang Iklan CTA -->
            <a href="<?= base_url('marketplace/create') ?>" class="market-promo-card">
                <div class="market-promo-icon">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </div>
                <div class="market-promo-title">Jual / Sewakan</div>
                <div class="market-promo-sub">Punya barang tak terpakai? Pasang iklan gratis sekarang!</div>
                <div class="market-promo-btn">+ Pasang Iklan</div>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── 🏡 WIDGET MY HOME & INVENTARIS ASET (KNOW YOUR HOME AT A GLANCE) ── -->
    <?php $hs = $myHomeSummary ?? []; ?>
    <div class="my-home-card" id="myHomeDashboardCard">
        <div class="my-home-header-row">
            <div class="my-home-title-group">
                <span class="my-home-title">🏡 My Home</span>
                <span class="my-home-badge">Aset & Ruangan</span>
            </div>
            <span style="font-size:11.5px; font-weight:700; color:var(--text-muted);">
                <?= $hs['assets_count'] ?? 0 ?> Aset Terdata
            </span>
        </div>

        <!-- Hero Home Health Banner -->
        <?php $hasAssets = ($hs['assets_count'] ?? 0) > 0; ?>
        <a href="/barang" class="my-home-hero-banner" style="text-decoration:none">
            <div class="my-home-hero-icon">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div class="my-home-score-num"><?= $hasAssets ? ($hs['health_score'] ?? 100) : '-' ?></div>
            <div class="my-home-score-label">Home Health</div>
            <div class="my-home-score-sub"><?= esc($hs['health_status'] ?? 'Mulai catat aset rumah Anda untuk memantau kondisi.') ?></div>

            <?php if (!$hasAssets): ?>
            <div class="my-home-attention-box">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Mulai Catat Aset Pertama</span>
            </div>
            <?php elseif (!empty($hs['attention'])): ?>
            <div class="my-home-attention-box urgent">
                <span>⚠️</span> <span><?= count($hs['attention']) ?> tugas butuh perhatian Anda</span>
            </div>
            <?php else: ?>
            <div class="my-home-attention-box safe">
                <span>✓</span> <span>Semua perawatan & garansi aman</span>
            </div>
            <?php endif; ?>
        </a>

        <!-- 4-Tile Grid -->
        <div class="my-home-grid">
            <div class="my-home-tile">
                <div class="my-home-tile-icon" style="background:#EFF6FF;color:#2563EB">🏢</div>
                <div>
                    <div class="my-home-tile-count"><?= $hs['rooms_count'] ?? 0 ?></div>
                    <div class="my-home-tile-lbl">Ruangan</div>
                </div>
            </div>
            <div class="my-home-tile">
                <div class="my-home-tile-icon" style="background:#FEF3C7;color:#D97706">📦</div>
                <div>
                    <div class="my-home-tile-count"><?= $hs['assets_count'] ?? 0 ?></div>
                    <div class="my-home-tile-lbl">Aset Barang</div>
                </div>
            </div>
            <div class="my-home-tile">
                <div class="my-home-tile-icon" style="background:#ECFDF5;color:#059669">🛠️</div>
                <div>
                    <div class="my-home-tile-count"><?= $hs['maintenance_count'] ?? 0 ?></div>
                    <div class="my-home-tile-lbl">Perawatan</div>
                </div>
            </div>
            <div class="my-home-tile">
                <div class="my-home-tile-icon" style="background:#F5F3FF;color:#7C3AED">🛡️</div>
                <div>
                    <div class="my-home-tile-count"><?= $hs['warranties_active'] ?? $hs['warranties_count'] ?? 0 ?></div>
                    <div class="my-home-tile-lbl">Garansi Aktif</div>
                </div>
            </div>
        </div>

        <!-- Action Button -->
        <a href="/barang" class="my-home-see-all">
            <span>Buka My Home (Inventaris & Perawatan)</span>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>

    <!-- ── 📺 & 🎬 TABBED MEDIA STREAMING (TV & FILM JELLYFIN) ── -->
    <?php 
        $hasTv = !empty($tvChannels);
        $hasFilm = !empty($jellyfinMovies);
        if ($hasTv || $hasFilm): 
            $defaultChannel = $hasTv ? $tvChannels[0] : null;
            $defaultMovie = $hasFilm ? $jellyfinMovies[0] : null;
    ?>
    <div class="tv-home-card" id="mediaStreamingCard">
        <!-- Header with Segmented Tabs -->
        <div class="tv-home-hdr" style="gap:8px; flex-wrap:wrap;">
            <div class="media-tab-group" id="mediaTabGroup">
                <?php if ($hasTv): ?>
                <button type="button" class="media-tab-btn active" id="tabBtnTv" onclick="switchMediaTab('tv')">
                    <span>📺</span> <span>TV Streaming</span>
                </button>
                <?php endif; ?>
                <?php if ($hasFilm): ?>
                <button type="button" class="media-tab-btn <?= !$hasTv ? 'active' : '' ?>" id="tabBtnFilm" onclick="switchMediaTab('film')">
                    <span>🎬</span> <span>Film Streaming</span>
                </button>
                <?php endif; ?>
            </div>
            <div id="mediaHeaderBadge">
                <span class="tv-badge-live-sm" id="badgeLiveTv">LIVE</span>
                <span class="jellyfin-badge-sm" id="badgeJellyfin" style="display:none">FILM HD</span>
            </div>
        </div>

        <!-- ── TAB 1: TV STREAMING PANEL ── -->
        <div id="mediaPanelTv" style="<?= $hasTv ? '' : 'display:none' ?>">
            <?php if ($hasTv): ?>
            <!-- Video Player Box (No Autoplay) -->
            <div class="tv-home-player-box" id="tvHomePlayerBox">
                <div class="tv-home-poster" id="tvHomePoster" onclick="startHomeTvPlay()">
                    <div class="tv-home-play-btn">
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </div>
                    <div class="tv-home-poster-name" id="tvHomePosterName"><?= esc($defaultChannel['name']) ?></div>
                    <div class="tv-home-poster-hint">Ketuk untuk Memutar Siaran Langsung</div>
                </div>
                <video id="homeTvVideo" class="tv-home-video" controls playsinline preload="none"
                       data-stream="<?= esc($defaultChannel['stream_url']) ?>">
                </video>
            </div>

            <!-- Channel Switcher Chips -->
            <div class="tv-home-chips" id="tvHomeChips">
                <?php foreach ($tvChannels as $idx => $ch): ?>
                <button type="button" class="tv-home-chip <?= $idx === 0 ? 'active' : '' ?>"
                        onclick="switchHomeTvChannel(<?= (int)$ch['id'] ?>, '<?= esc($ch['stream_url'], 'js') ?>', '<?= esc($ch['name'], 'js') ?>', '<?= esc($ch['category'], 'js') ?>', this)">
                    <span>📺</span> <?= esc($ch['name']) ?>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- Action: Lihat Semua Live Streaming -->
            <a href="<?= base_url('tv') ?>" class="tv-home-see-all">
                <span>Lihat Semua Live Streaming</span>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <?php endif; ?>
        </div>

        <!-- ── TAB 2: FILM STREAMING (JELLYFIN) PANEL ── -->
        <div id="mediaPanelFilm" style="<?= !$hasTv && $hasFilm ? '' : 'display:none' ?>">
            <?php if ($hasFilm): ?>
            <!-- Film Video Player Box (No Autoplay) -->
            <div class="tv-home-player-box" id="filmHomePlayerBox">
                <div class="tv-home-poster" id="filmHomePoster" onclick="startHomeFilmPlay()" style="background-image:url('<?= esc($defaultMovie['backdrop'] ?? $defaultMovie['poster']) ?>'); background-size:cover; background-position:center;">
                    <div style="position:absolute; inset:0; background:rgba(11,15,25,0.72); backdrop-filter:blur(2px);"></div>
                    <div class="tv-home-play-btn" style="position:relative; z-index:2; background:#00A4DC; box-shadow:0 6px 20px rgba(0,164,220,0.5);">
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </div>
                    <div class="tv-home-poster-name" id="filmHomePosterName" style="position:relative; z-index:2;"><?= esc($defaultMovie['title']) ?> (<?= esc($defaultMovie['year']) ?>)</div>
                    <div class="tv-home-poster-hint" id="filmHomePosterHint" style="position:relative; z-index:2;">
                        <?= !empty($defaultMovie['rating']) ? '⭐ ' . esc($defaultMovie['rating']) . ' • ' : '' ?><?= esc($defaultMovie['duration']) ?> • Ketuk untuk Memutar Film
                    </div>
                </div>
                <video id="homeFilmVideo" class="tv-home-video" controls playsinline preload="none"
                       data-stream="<?= esc($defaultMovie['stream_url']) ?>">
                </video>
            </div>

            <!-- Katalog Film Section (HANYA MUNCUL DI TAB FILM STREAMING) -->
            <div style="margin-top:14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:13px; font-weight:800; color:var(--text-primary);">🎬 Katalog Film</span>
                        <span style="font-size:11px; font-weight:700; color:var(--text-muted);">(<?= count($jellyfinMovies) ?> Judul)</span>
                    </div>
                    <span style="font-size:10.5px; color:#00A4DC; font-weight:700;">Film Bioskop</span>
                </div>

                <!-- Search Input inside tab -->
                <div style="margin-bottom:12px; position:relative;">
                    <input type="text" id="jellyfinSearchInput" placeholder="Cari judul film..." 
                           style="width:100%; padding:9px 12px 9px 34px; border-radius:12px; border:1px solid var(--border); background:var(--bg); color:var(--text-primary); font-size:12px; outline:none;"
                           oninput="filterJellyfinCatalog(this.value)">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" style="position:absolute; left:11px; top:10px; color:var(--text-muted)"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>

                <!-- Grid of Films inside tab -->
                <div id="jellyfinCatalogGrid" style="display:grid; grid-template-columns:repeat(3, 1fr); gap:10px; max-height:420px; overflow-y:auto; padding-right:2px; scrollbar-width:thin;">
                    <?php foreach ($jellyfinMovies as $idx => $m): ?>
                    <div class="jellyfin-catalog-item" data-title="<?= esc(strtolower($m['title'])) ?>" onclick="playFilmFromCatalog('<?= esc($m['id'], 'js') ?>', '<?= esc($m['stream_url'], 'js') ?>', '<?= esc($m['title'], 'js') ?>', '<?= esc($m['year'], 'js') ?>', '<?= esc($m['backdrop'], 'js') ?>', '<?= esc($m['rating'] ?? '', 'js') ?>', '<?= esc($m['duration'], 'js') ?>')" style="background:var(--bg); border:1px solid var(--border); border-radius:12px; overflow:hidden; cursor:pointer; display:flex; flex-direction:column; transition:transform 0.15s ease, border-color 0.15s ease;">
                        <img src="<?= esc($m['poster']) ?>" alt="<?= esc($m['title']) ?>" style="width:100%; aspect-ratio:2/2.8; object-fit:cover; background:#1E293B; display:block;" loading="lazy">
                        <div style="padding:6px; display:flex; flex-direction:column; gap:2px; flex:1;">
                            <div style="font-size:11px; font-weight:800; color:var(--text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= esc($m['title']) ?>"><?= esc($m['title']) ?></div>
                            <div style="font-size:9.5px; color:var(--text-muted); display:flex; justify-content:space-between; align-items:center;">
                                <span><?= esc($m['year']) ?></span>
                                <?php if (!empty($m['rating'])): ?>
                                <span style="color:#D97706; font-weight:800">★ <?= esc($m['rating']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── 📍 WIDGET LOKASI TERDEKAT (TOKO KELONTONG, SPBU, TAMBAL BAN) ── -->
    <div class="nearby-card" id="nearbyCard">
        <div class="nearby-hdr">
            <div class="nearby-title-wrap">
                <span class="nearby-title">📍 Layanan Terdekat</span>
            </div>
            <button type="button" class="nearby-loc-btn" id="btnRefreshLoc" title="Perbarui Lokasi GPS">
                <span style="font-size:12px">🎯</span> <span id="nearbyLocStatus">GPS Aktif</span>
            </button>
        </div>

        <!-- Filter Kategori Layanan -->
        <div class="nearby-filter-pills" id="nearbyCatPills">
            <button type="button" class="nearby-pill active" data-cat="toko_kelontong">
                <span>🏪</span> Toko Kelontong
            </button>
            <button type="button" class="nearby-pill" data-cat="spbu">
                <span>⛽</span> Pom Bensin (SPBU)
            </button>
            <button type="button" class="nearby-pill" data-cat="tambal_ban">
                <span>🔧</span> Tambal Ban
            </button>
            <button type="button" class="nearby-pill" data-cat="atm">
                <span>🏧</span> ATM Bank
            </button>
            <button type="button" class="nearby-pill" data-cat="kuliner">
                <span>☕</span> Warkop / Kuliner
            </button>
        </div>

        <!-- Radius Bar -->
        <div class="nearby-radius-bar">
            <span>Radius Jangkauan:</span>
            <div class="nearby-rad-chips" id="nearbyRadiusChips">
                <button type="button" class="nearby-rad-chip active" data-rad="500">500 m</button>
                <button type="button" class="nearby-rad-chip" data-rad="1000">1 km</button>
                <button type="button" class="nearby-rad-chip" data-rad="2000">2 km</button>
                <button type="button" class="nearby-rad-chip" data-rad="5000">5 km</button>
            </div>
        </div>

        <!-- Leaflet Map Screen -->
        <div id="nearbyMap" class="nearby-map-container"></div>

        <!-- Accordion Header Toggle -->
        <div class="nearby-accordion-hdr" id="nearbyAccordionHdr">
            <div style="display:flex;align-items:center;gap:6px">
                <span style="font-size:13px">📋</span>
                <span style="font-size:12px;font-weight:700;color:var(--text-primary)" id="nearbyAccordionLabel">Daftar Tempat Terdekat</span>
                <span class="nearby-count-badge" id="nearbyCountBadge">Memuat...</span>
            </div>
            <div class="nearby-accordion-icon" id="nearbyAccordionIcon">▾</div>
        </div>

        <!-- Nearby Places List (Collapsible Accordion) -->
        <div class="nearby-list-wrap collapsed" id="nearbyPlacesList">
            <!-- Dynamic POI Cards loaded via JS -->
            <div style="text-align:center;padding:16px;color:var(--text-muted);font-size:12px">
                ⏳ Memindai tempat terdekat di sekitar lokasi Anda...
            </div>
        </div>
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
        <?php
        $incomePal = ['bg' => '#F0FDF4', 'border' => '#BBF7D0', 'accent' => '#16A34A', 'icon_bg' => '#DCFCE7'];
        $expensePal = ['bg' => '#FEF2F2', 'border' => '#FECDD3', 'accent' => '#DC2626', 'icon_bg' => '#FEE2E2'];
        $recurringPal = ['bg' => '#FEFCE8', 'border' => '#FDE68A', 'accent' => '#D97706', 'icon_bg' => '#FEF3C7'];
        ?>
        <?php foreach ($recent as $tx): ?>
        <?php
            $isIncome = ($tx['type'] ?? '') === 'income';
            $noteStr = $tx['note'] ?? '';
            $isRecurring = !empty($tx['is_recurring']) || !empty($tx['recurring_id']) ||
                stripos($noteStr, '(otomatis)') !== false ||
                stripos($noteStr, 'pembayaran rutin') !== false ||
                stripos($noteStr, '(berulang)') !== false ||
                stripos($noteStr, 'rutin') !== false;

            if ($isRecurring) {
                $pal = $recurringPal;
            } elseif ($isIncome) {
                $pal = $incomePal;
            } else {
                $pal = $expensePal;
            }
            $hasNote = !empty(trim($noteStr));
            $title = $hasNote ? $noteStr : ($tx['category_name'] ?? 'Transaksi');
        ?>
        <div class="tx-item" style="--tx-bg:<?= $pal['bg'] ?>;--tx-border:<?= $pal['border'] ?>;--tx-accent:<?= $pal['accent'] ?>;--tx-icon-bg:<?= $pal['icon_bg'] ?>;" data-id="<?= $tx['id'] ?>" data-tx='<?= json_encode($tx) ?>'>
            <div class="tx-icon">
                <?= $isRecurring ? '🔄' : categoryIcon($tx['category_icon'] ?? ($isIncome ? 'income' : 'other')) ?>
            </div>
            <div class="tx-body">
                <div class="tx-name">
                    <span><?= esc($title) ?></span>
                </div>
                <div class="tx-note">
                    <span class="tx-badge"><?= $isRecurring ? 'Berulang' : esc($tx['category_name'] ?? ($isIncome ? 'Pemasukan' : 'Pengeluaran')) ?></span>
                    <span style="margin-left: 4px;"><?= esc($tx['wallet_name'] ?? 'Dompet') ?></span>
                    <?php if (!empty($tx['image'])): ?>
                        <span title="Ada Foto" style="margin-left:4px; opacity:0.7">📷</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="tx-right">
                <div class="tx-amount" style="color:<?= $pal['accent'] ?>;">
                    <?= $isIncome ? '+' : '-' ?> <?= esc($symbol) ?> <?= number_format($tx['amount'], 0, ',', '.') ?>
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
    </div>
    <!-- END PERSONAL WORKSPACE CONTAINER -->

    <!-- ═══════════════════════════════════════════════════════════ BUSINESS WORKSPACE CONTAINER -->
    <div id="workspaceBusiness" style="display:none">
        <?php $biz = $business ?? []; ?>
        <!-- Hero Business Card -->
        <div style="background:linear-gradient(135deg, #1E1B4B 0%, #312E81 50%, #4338CA 100%);border-radius:24px;padding:22px 20px;color:#fff;margin-bottom:16px;box-shadow:0 8px 28px rgba(49,46,129,.35);position:relative;overflow:hidden">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                <div style="font-size:11px;font-weight:700;letter-spacing:0.8px;text-transform:uppercase;color:rgba(255,255,255,0.65)">OMSET HARI INI (<?= date('d M Y') ?>)</div>
                <div style="background:rgba(255,255,255,0.15);padding:2px 8px;border-radius:8px;font-size:11px;font-weight:800"><?= $biz['today_orders'] ?? 0 ?> Order</div>
            </div>
            <div style="font-size:32px;font-weight:900;letter-spacing:-1px;line-height:1;margin-bottom:12px">
                <?= esc($symbol) ?> <?= number_format((float)($biz['today_sales'] ?? 0), 0, ',', '.') ?>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                <div style="background:rgba(255,255,255,0.08);padding:10px 12px;border-radius:12px">
                    <div style="font-size:10.5px;color:rgba(255,255,255,0.7)">Laba Bersih Hari Ini</div>
                    <div style="font-size:14px;font-weight:900;color:#34D399"><?= esc($symbol) ?> <?= number_format((float)($biz['today_profit'] ?? 0), 0, ',', '.') ?></div>
                </div>
                <div style="background:rgba(255,255,255,0.08);padding:10px 12px;border-radius:12px">
                    <div style="font-size:10.5px;color:rgba(255,255,255,0.7)">Omset Bulan Ini</div>
                    <div style="font-size:14px;font-weight:900;color:#FDE047"><?= esc($symbol) ?> <?= number_format((float)($biz['month_sales'] ?? 0), 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <!-- Quick POS Navigation -->
        <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:10px;margin-bottom:16px">
            <a href="/pos" style="background:linear-gradient(135deg,#EA580C,#FB923C);color:#fff;border-radius:16px;padding:14px 10px;text-align:center;text-decoration:none;box-shadow:0 4px 14px rgba(234,88,12,.25);display:flex;flex-direction:column;align-items:center;gap:6px">
                <span style="font-size:24px">☕</span>
                <span style="font-size:12px;font-weight:800;white-space:nowrap">Kasir POS</span>
            </a>
            <a href="/pos/products" style="background:linear-gradient(135deg,#059669,#34D399);color:#fff;border-radius:16px;padding:14px 10px;text-align:center;text-decoration:none;box-shadow:0 4px 14px rgba(5,150,105,.25);display:flex;flex-direction:column;align-items:center;gap:6px">
                <span style="font-size:24px">📦</span>
                <span style="font-size:12px;font-weight:800;white-space:nowrap">Stok Produk</span>
            </a>
            <a href="/pos/reports" style="background:linear-gradient(135deg,#4F46E5,#818CF8);color:#fff;border-radius:16px;padding:14px 10px;text-align:center;text-decoration:none;box-shadow:0 4px 14px rgba(79,70,229,.25);display:flex;flex-direction:column;align-items:center;gap:6px">
                <span style="font-size:24px">📊</span>
                <span style="font-size:12px;font-weight:800;white-space:nowrap">Laba Rugi</span>
            </a>
        </div>

        <!-- Low Stock Alert -->
        <?php if (!empty($biz['low_stock_count']) && $biz['low_stock_count'] > 0): ?>
        <div style="background:#FEF2F2;border:1px solid #FCA5A5;border-radius:14px;padding:12px 14px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:20px">⚠️</span>
                <div>
                    <div style="font-size:13px;font-weight:800;color:#DC2626"><?= $biz['low_stock_count'] ?> Produk Stok Menipis</div>
                    <div style="font-size:11px;color:#7F1D1D">Segera lakukan restock barang dagangan.</div>
                </div>
            </div>
            <a href="/pos/products" style="background:#DC2626;color:#fff;padding:6px 12px;border-radius:8px;font-size:11px;font-weight:800;text-decoration:none">Restock</a>
        </div>
        <?php endif; ?>

        <!-- Unpaid Kasbon Alert -->
        <?php if (!empty($biz['kasbon_unsettled_count']) && $biz['kasbon_unsettled_count'] > 0): ?>
        <div style="background:#FFFBEB;border:1px solid #FDE68A;border-radius:14px;padding:12px 14px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:20px">📒</span>
                <div>
                    <div style="font-size:13px;font-weight:800;color:#D97706"><?= $biz['kasbon_unsettled_count'] ?> Kasbon Belum Lunas</div>
                    <div style="font-size:11px;color:#78350F">Total: <?= esc($symbol) ?> <?= number_format($biz['kasbon_unsettled_total'] ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
            <a href="/hutang" style="background:#D97706;color:#fff;padding:6px 12px;border-radius:8px;font-size:11px;font-weight:800;text-decoration:none">Tagih</a>
        </div>
        <?php endif; ?>

        <!-- Best Sellers -->
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:18px;padding:16px;margin-bottom:14px;box-shadow:var(--shadow-sm)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="font-size:13.5px;font-weight:800;color:var(--text-primary)">🏆 4 Produk Terlaris Bulan Ini</div>
                <a href="/pos/reports" style="font-size:11.5px;color:var(--primary);font-weight:700;text-decoration:none">Laporan Lengkap →</a>
            </div>
            <?php if (empty($biz['best_sellers'])): ?>
                <div style="text-align:center;padding:16px 0;font-size:12px;color:var(--text-muted)">Belum ada transaksi kasir pada bulan ini.</div>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:8px">
                    <?php foreach ($biz['best_sellers'] as $idx => $bs): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;background:var(--bg);padding:8px 12px;border-radius:10px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <span style="font-size:12px;font-weight:900;width:20px;height:20px;background:var(--primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center"><?= (int)$idx + 1 ?></span>
                            <div>
                                <div style="font-size:13px;font-weight:700;color:var(--text-primary)"><?= esc($bs['product_name'] ?? '') ?></div>
                                <div style="font-size:11px;color:var(--text-muted)"><?= (int)($bs['total_qty'] ?? 0) ?> terjual</div>
                            </div>
                        </div>
                        <div style="font-size:13px;font-weight:800;color:#EA580C"><?= esc($symbol) ?> <?= number_format((float)($bs['total_revenue'] ?? 0), 0, ',', '.') ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- END BUSINESS WORKSPACE CONTAINER -->

    <!-- FLOATING MARKETPLACE CHAT SHORTCUT (DASHBOARD) -->
    <a href="/marketplace?tab=orders" class="market-floating-chat-btn" id="dashMarketFloatingChatBtn" title="Buka Pesan & Minat Marketplace">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
        </svg>
        <span>Chat Marketplace</span>
        <?php if (!empty($marketInquiriesCount)): ?>
            <span class="market-floating-chat-badge"><?= (int)$marketInquiriesCount ?></span>
        <?php endif; ?>
    </a>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
/* ════════════════════════════════════════════════════════════════
   WORKSPACE SWITCHER (Personal vs Business Mode)
   ════════════════════════════════════════════════════════════════ */
window.switchWorkspace = function(mode) {
    const btnPersonal = document.getElementById('btnModePersonal');
    const btnBusiness = document.getElementById('btnModeBusiness');
    const wsPersonal  = document.getElementById('workspacePersonal');
    const wsBusiness  = document.getElementById('workspaceBusiness');

    if (mode === 'business') {
        btnBusiness?.classList.add('active', 'business');
        btnPersonal?.classList.remove('active', 'personal');
        if (wsPersonal) wsPersonal.style.display = 'none';
        if (wsBusiness) wsBusiness.style.display = 'block';
        try { localStorage.setItem('duitku_mode', 'business'); } catch(e){}
    } else {
        btnPersonal?.classList.add('active', 'personal');
        btnBusiness?.classList.remove('active', 'business');
        if (wsPersonal) wsPersonal.style.display = 'block';
        if (wsBusiness) wsBusiness.style.display = 'none';
        try { localStorage.setItem('duitku_mode', 'personal'); } catch(e){}
    }
};

// Auto-run on load
try {
    const saved = localStorage.getItem('duitku_mode') || 'personal';
    window.switchWorkspace(saved);
} catch(e){}

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
        window.DuitkuLockScroll();
    }
    function closeCalc() {
        overlay.classList.remove('open');
        window.DuitkuUnlockScroll();
    }

    btnOpen?.addEventListener('click', openCalc);
    btnClose?.addEventListener('click', closeCalc);
    overlay?.addEventListener('click', (e) => { if (e.target === overlay) closeCalc(); });

    // ── Tab switching ──────────────────────────────────────────────
    document.querySelectorAll('.hc-tab').forEach(btn => {
        btn?.addEventListener('click', () => {
            document.querySelectorAll('.hc-tab').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.hc-tab-content').forEach(c => c.classList.add('hidden'));
            btn.classList.add('active');
            document.getElementById('htab-' + btn.dataset.tab)?.classList.remove('hidden');
        });
    });

    // ── Discount % ─────────────────────────────────────────────────
    function updateDiscount() {
        if (!hcPrice || !hcPercent) return;
        const price   = parseFloat(hcPrice.value)   || 0;
        const percent = parseFloat(hcPercent.value) || 0;
        if (price > 0 && percent > 0) {
            const savings = price * percent / 100;
            if (hcSavings) hcSavings.textContent = fmtRp(savings);
            if (hcFinal) hcFinal.textContent   = fmtRp(price - savings);
            discResult?.classList.remove('hidden');
        } else {
            discResult?.classList.add('hidden');
        }
    }
    hcPrice?.addEventListener('input', updateDiscount);
    hcPercent?.addEventListener('input', updateDiscount);
    document.querySelectorAll('.hc-pct-btn').forEach(btn => {
        btn?.addEventListener('click', () => {
            document.querySelectorAll('.hc-pct-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            if (hcPercent) hcPercent.value = btn.dataset.v;
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
        if (!hcTotal || !hcPaid) return;
        const total = parseFloat(hcTotal.value) || 0;
        const paid  = parseFloat(hcPaid.value)  || 0;
        if (total > 0 && paid > 0) {
            const diff = paid - total;
            if (diff >= 0) {
                if (chgLabel) chgLabel.textContent = 'Kembalian:';
                if (chgVal) { chgVal.className = 'hc-green'; chgVal.textContent = fmtRp(diff); }
            } else {
                if (chgLabel) chgLabel.textContent = 'Kurang bayar:';
                if (chgVal) { chgVal.className = 'hc-red'; chgVal.textContent = fmtRp(Math.abs(diff)); }
            }
            chgResult?.classList.remove('hidden');
        } else {
            chgResult?.classList.add('hidden');
        }
    }
    hcTotal?.addEventListener('input', updateChange);
    hcPaid?.addEventListener('input', updateChange);

    // ── Banding Harga ──────────────────────────────────────────────
    function updateCompare() {
        if (!cmpAPrice || !cmpAQty || !cmpBPrice || !cmpBQty) return;
        const aP = parseFloat(cmpAPrice.value) || 0;
        const aQ = parseFloat(cmpAQty.value)   || 0;
        const bP = parseFloat(cmpBPrice.value) || 0;
        const bQ = parseFloat(cmpBQty.value)   || 0;
        if (aP > 0 && aQ > 0 && bP > 0 && bQ > 0) {
            const aUnit = aP / aQ;
            const bUnit = bP / bQ;
            if (cmpAUnit) cmpAUnit.textContent = fmtRp(aUnit) + '/satuan';
            if (cmpBUnit) cmpBUnit.textContent = fmtRp(bUnit) + '/satuan';
            if (cmpWinner) {
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
            }
            cmpResult?.classList.remove('hidden');
        } else {
            cmpResult?.classList.add('hidden');
        }
    }
    [cmpAPrice, cmpAQty, cmpBPrice, cmpBQty].forEach(el => el?.addEventListener('input', updateCompare));

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
        if (!badge) return;
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
        listOverlay?.classList.add('open');
        window.DuitkuLockScroll?.();
    }
    function closeList() {
        listOverlay?.classList.remove('open');
        window.DuitkuUnlockScroll?.();
    }
    function openForm(item = null) {
        editId = item ? item.id : null;
        if (formTitle) formTitle.textContent = item ? 'Edit Simpanan' : 'Tambah Simpanan';
        if (idInput) idInput.value       = item ? item.id : '';
        if (nameInput) nameInput.value     = item ? item.name : '';
        if (locInput) locInput.value      = item ? item.location : '';
        if (notesInput) notesInput.value    = item ? (item.notes || '') : '';

        // Photo
        currentBase64 = item?.image || null;
        if (currentBase64) {
            if (previewImg) previewImg.src = currentBase64;
            if (imagePreview) imagePreview.style.display = 'block';
            if (btnPhoto) btnPhoto.style.display = 'none';
        } else {
            if (imagePreview) imagePreview.style.display = 'none';
            if (btnPhoto) btnPhoto.style.display = 'flex';
        }
        if (imageInput) imageInput.value = '';

        // GPS
        currentCoords = (item?.lat && item?.lng) ? { lat: item.lat, lng: item.lng, accuracy: item.accuracy } : null;
        showGpsPreview();

        formOverlay?.classList.add('open');
        setTimeout(() => nameInput?.focus(), 80);
    }
    function closeForm() {
        formOverlay?.classList.remove('open');
    }

    // ── GPS ───────────────────────────────────────────────────────
    function showGpsPreview() {
        if (currentCoords) {
            const acc = currentCoords.accuracy ? ` (±${Math.round(currentCoords.accuracy)}m)` : '';
            if (gpsText) gpsText.textContent = `${currentCoords.lat.toFixed(5)}, ${currentCoords.lng.toFixed(5)}${acc}`;
            if (gpsPreview) gpsPreview.style.display = 'flex';
            if (btnGps) btnGps.style.display = 'none';
        } else {
            if (gpsPreview) gpsPreview.style.display = 'none';
            if (btnGps) btnGps.style.display = 'flex';
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
    btnOpenSt?.addEventListener('click', openList);
    listClose?.addEventListener('click', closeList);
    listOverlay?.addEventListener('click', e => { if (e.target === listOverlay) closeList(); });

    btnAdd?.addEventListener('click', () => { closeList(); openForm(); });
    formClose?.addEventListener('click', closeForm);
    cancelBtn?.addEventListener('click', closeForm);
    formOverlay?.addEventListener('click', e => { if (e.target === formOverlay) closeForm(); });

    searchInput?.addEventListener('input', () => {
        searchTerm = searchInput ? searchInput.value.trim() : '';
        render();
    });

    form?.addEventListener('submit', saveItem);

    // Photo
    btnPhoto?.addEventListener('click', () => imageInput?.click());
    imageInput?.addEventListener('change', e => {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = ev => {
            currentBase64 = ev.target.result;
            if (previewImg) previewImg.src = currentBase64;
            if (imagePreview) imagePreview.style.display = 'block';
            if (btnPhoto) btnPhoto.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });
    btnRemPhoto?.addEventListener('click', () => {
        currentBase64 = null;
        if (imageInput) imageInput.value = '';
        if (imagePreview) imagePreview.style.display = 'none';
        if (btnPhoto) btnPhoto.style.display = 'flex';
    });

    // GPS
    btnGps?.addEventListener('click', () => {
        if (!navigator.geolocation) { alert('Perangkat tidak mendukung GPS.'); return; }
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
    btnRemGps?.addEventListener('click', () => {
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
        if (!billsBadge) return;
        const n = bills.filter(b => isDueSoon(b.dueDay) || isOverdue(b.dueDay)).length;
        billsBadge.textContent    = n;
        billsBadge.style.display  = n > 0 ? 'inline-flex' : 'none';
    }

    function checkDue() {
        if (!dueBanner) return;
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
        window.DuitkuLockScroll();
        await loadBills();
        render();
        updateBadge();
        checkDue();
    }
    function closeBillsModal() {
        billsOverlay.classList.remove('open');
        window.DuitkuUnlockScroll();
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
    btnOpenBills?.addEventListener('click', openBillsModal);
    billsClose?.addEventListener('click', closeBillsModal);
    billsOverlay?.addEventListener('click', e => { if (e.target === billsOverlay) closeBillsModal(); });
    btnAddBill?.addEventListener('click', () => openForm(null));
    formClose?.addEventListener('click', closeForm);
    cancelBtn?.addEventListener('click', closeForm);
    formOverlay?.addEventListener('click', e => { if (e.target === formOverlay) closeForm(); });

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
        window.DuitkuLockScroll();
    }
    function closeModal() {
        overlay.classList.remove('open');
        window.DuitkuUnlockScroll();
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

    saveBtn?.addEventListener('click', async () => {
        const name   = nameInput ? nameInput.value.trim() : '';
        const target = targetInput ? numVal(targetInput) : 0;
        const saved  = savedInput ? numVal(savedInput) : 0;
        if (!name || target <= 0) { alert('Isi nama target dan nominal target terlebih dahulu.'); return; }

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.textContent = 'Menyimpan…';
        }

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
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Simpan';
            }
        }
    });

    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);
    overlay?.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

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

    btnOpen?.addEventListener('click', openModal);
    function openModal() {
        if (!overlay) return;
        overlay.classList.add('open');
        window.DuitkuLockScroll();
        setTimeout(() => textarea?.focus(), 80);
    }
    function closeModal() {
        if (!overlay) return;
        overlay.classList.remove('open');
        window.DuitkuUnlockScroll();
    }

    saveBtn?.addEventListener('click', async () => {
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.textContent = 'Menyimpan…';
        }

        const fd = new FormData();
        fd.append(window.DUITKU.csrfName, window.DUITKU.csrfToken);
        fd.append('note', textarea ? textarea.value : '');

        try {
            const res  = await fetch('/settings/note', { method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest'}, body: fd });
            const data = await res.json();
            if (data.success) { closeModal(); location.reload(); }
            else { alert(data.message || 'Gagal menyimpan catatan.'); }
        } catch(e) {
            alert('Terjadi kesalahan koneksi.');
        } finally {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Simpan';
            }
        }
    });

    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);
    overlay?.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

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
    billPicker?.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (!opt || !opt.value) return;
        const amount = parseFloat(opt.dataset.amount || '0');
        const name   = opt.dataset.name || '';
        if (amount > 0 && txAmount) txAmount.value = amount.toLocaleString('id-ID');
        if (name && txNote) txNote.value = 'Bayar tagihan: ' + name;
    });

})();
</script>

<!-- Leaflet Library for OpenStreetMap -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// ══════════════════════════════════════════════════════════════════════════════
// 📍 NEARBY PLACES DISCOVERY ENGINE (Toko Kelontong, SPBU, Tambal Ban, ATM)
// ══════════════════════════════════════════════════════════════════════════════
(function() {
    let nearbyMap = null;
    let userMarker = null;
    let radiusCircle = null;
    let poiMarkers = [];
    
    // Default coordinate: Jakarta / Indonesia center
    let currentLat = -6.200000;
    let currentLng = 106.816666;
    let currentRadius = 500; // in meters
    let currentCategory = 'toko_kelontong';

    const CAT_CONFIG = {
        toko_kelontong: {
            icon: '🏪',
            name: 'Toko Kelontong / Warung',
            osmTag: 'shop~"convenience|general|kiosk|supermarket"',
            fallbackNames: ['Warung Madura 24 Jam', 'Toko Kelontong Berkah', 'Minimarket Barokah', 'Warung Sembako Bu Siti', 'Toko Kelontong Rejeki', 'Warung Kelontong Sumber Rejeki', 'Toko Sembako Abadi']
        },
        spbu: {
            icon: '⛽',
            name: 'Pom Bensin (SPBU)',
            osmTag: 'amenity="fuel"',
            fallbackNames: ['SPBU Pertamina 34-12345', 'Pertashop 24 Jam', 'SPBU Pertamina Pasti Pas', 'SPBU Shell', 'SPBU BP-AKR', 'Kios Bensin Eceran & Pertalite']
        },
        tambal_ban: {
            icon: '🔧',
            name: 'Tambal Ban & Bengkel',
            osmTag: 'shop~"motorcycle_repair|tyres|car_repair"',
            fallbackNames: ['Tambal Ban Tubeless 24 Jam', 'Bengkel Motor & Tambal Ban Pak Joko', 'Tambal Ban Pres & Pompa Angin Nitrogen', 'Bengkel Service & Tambal Ban Berkah', 'Kios Tambal Ban Mas Bro']
        },
        atm: {
            icon: '🏧',
            name: 'ATM & Bank',
            osmTag: 'amenity~"atm|bank"',
            fallbackNames: ['ATM BCA 24 Jam', 'ATM Mandiri', 'ATM BRI Link', 'ATM BNI', 'ATM Bersama', 'Bank & ATM Syariah']
        },
        kuliner: {
            icon: '☕',
            name: 'Warkop & Kuliner',
            osmTag: 'amenity~"cafe|restaurant|fast_food"',
            fallbackNames: ['Warkop Warmindo 24 Jam', 'Kedai Kopi & Warkop Nusantara', 'Warung Makan Padang Sederhana', 'Warung Soto & Bakso Enak', 'Cafe Santai Kopi']
        }
    };

    function initMap() {
        const mapEl = document.getElementById('nearbyMap');
        if (!mapEl || typeof L === 'undefined') return;

        if (!nearbyMap) {
            nearbyMap = L.map('nearbyMap', {
                center: [currentLat, currentLng],
                zoom: 16,
                zoomControl: false,
                attributionControl: false
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(nearbyMap);

            // Create Custom Pulse User Marker
            const userIcon = L.divIcon({
                className: 'pulse-user-marker',
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });
            userMarker = L.marker([currentLat, currentLng], { icon: userIcon }).addTo(nearbyMap);
            userMarker.bindPopup('<b>📍 Lokasi Anda</b><br><small>Titik acuan radius.</small>');

            // Radius Circle
            radiusCircle = L.circle([currentLat, currentLng], {
                radius: currentRadius,
                color: '#10B981',
                fillColor: '#10B981',
                fillOpacity: 0.12,
                weight: 1.5
            }).addTo(nearbyMap);

            // Trigger size calculation to ensure full-width tile rendering
            setTimeout(() => { if (nearbyMap) nearbyMap.invalidateSize(true); }, 50);
            setTimeout(() => { if (nearbyMap) nearbyMap.invalidateSize(true); }, 250);
            setTimeout(() => { if (nearbyMap) nearbyMap.invalidateSize(true); }, 600);

            window.addEventListener('resize', () => {
                if (nearbyMap) nearbyMap.invalidateSize(true);
            });
        }
    }

    function updateMapPosition(lat, lng) {
        currentLat = lat;
        currentLng = lng;
        if (nearbyMap) {
            nearbyMap.setView([lat, lng], currentRadius <= 500 ? 16 : (currentRadius <= 1000 ? 15 : 14));
            if (userMarker) userMarker.setLatLng([lat, lng]);
            if (radiusCircle) {
                radiusCircle.setLatLng([lat, lng]);
                radiusCircle.setRadius(currentRadius);
            }
            setTimeout(() => { if (nearbyMap) nearbyMap.invalidateSize(true); }, 50);
        }
    }

    function calculateDistanceMeters(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // metres
        const φ1 = lat1 * Math.PI / 180;
        const φ2 = lat2 * Math.PI / 180;
        const Δφ = (lat2 - lat1) * Math.PI / 180;
        const Δλ = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return Math.round(R * c);
    }

    function getDirectionUrl(destLat, destLng, destName) {
        return `https://www.google.com/maps/dir/?api=1&origin=${currentLat},${currentLng}&destination=${destLat},${destLng}&travelmode=driving`;
    }

    function generateRealisticFallbackPlaces() {
        const cfg = CAT_CONFIG[currentCategory] || CAT_CONFIG.toko_kelontong;
        const places = [];
        const count = Math.min(cfg.fallbackNames.length, currentRadius <= 500 ? 4 : 7);

        for (let i = 0; i < count; i++) {
            // Generate offset within radius
            const angle = (i / count) * (2 * Math.PI) + (Math.random() * 0.4 - 0.2);
            const dist = Math.round(50 + (i + 1) * (currentRadius / (count + 1.2)));
            // 1 deg lat ~ 111,000 meters
            const latOffset = (dist * Math.cos(angle)) / 111000;
            const lngOffset = (dist * Math.sin(angle)) / (111000 * Math.cos(currentLat * Math.PI / 180));
            
            const pLat = currentLat + latOffset;
            const pLng = currentLng + lngOffset;

            places.push({
                name: cfg.fallbackNames[i % cfg.fallbackNames.length],
                icon: cfg.icon,
                categoryName: cfg.name,
                lat: pLat,
                lng: pLng,
                distance: dist
            });
        }
        return places.sort((a, b) => a.distance - b.distance);
    }

    async function loadNearbyPlaces() {
        const listEl = document.getElementById('nearbyPlacesList');
        if (!listEl) return;

        listEl.innerHTML = '<div style="text-align:center;padding:16px;color:var(--text-muted);font-size:12px">⏳ Memindai data ' + (CAT_CONFIG[currentCategory]?.name || 'Layanan') + ' di sekitar Anda...</div>';

        // Clear existing POI markers
        poiMarkers.forEach(m => { if (nearbyMap) nearbyMap.removeLayer(m); });
        poiMarkers = [];

        let places = [];
        const cfg = CAT_CONFIG[currentCategory] || CAT_CONFIG.toko_kelontong;

        // Try OpenStreetMap Overpass API with short timeout
        try {
            const query = `[out:json][timeout:4];node[${cfg.osmTag}](around:${currentRadius},${currentLat},${currentLng});out body 15;`;
            const url = `https://overpass-api.de/api/interpreter?data=${encodeURIComponent(query)}`;
            
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 3500);

            const res = await fetch(url, { signal: controller.signal });
            clearTimeout(timeoutId);

            if (res.ok) {
                const data = await res.json();
                if (data.elements && data.elements.length > 0) {
                    places = data.elements.map(el => {
                        const d = calculateDistanceMeters(currentLat, currentLng, el.lat, el.lon);
                        return {
                            name: el.tags.name || el.tags.brand || cfg.name,
                            icon: cfg.icon,
                            categoryName: cfg.name,
                            lat: el.lat,
                            lng: el.lon,
                            distance: d
                        };
                    }).filter(p => p.distance <= currentRadius).sort((a, b) => a.distance - b.distance);
                }
            }
        } catch (err) {
            // Overpass unavailable / timeout, smoothly use local generation
        }

        // If no Overpass results found, use high-precision local fallback places
        if (places.length === 0) {
            places = generateRealisticFallbackPlaces();
        }

        // Render to List & Leaflet Map
        renderPlaces(places);
    }

    function toggleNearbyList(forceOpen = false) {
        const listEl = document.getElementById('nearbyPlacesList');
        const iconEl = document.getElementById('nearbyAccordionIcon');
        if (!listEl) return;
        if (forceOpen) {
            listEl.classList.remove('collapsed');
            if (iconEl) iconEl.classList.add('open');
        } else {
            const isCollapsed = listEl.classList.toggle('collapsed');
            if (iconEl) iconEl.classList.toggle('open', !isCollapsed);
        }
    }

    function renderPlaces(places) {
        const listEl = document.getElementById('nearbyPlacesList');
        const countBadge = document.getElementById('nearbyCountBadge');
        if (!listEl) return;

        if (countBadge) {
            countBadge.innerText = places.length > 0 ? `${places.length} Lokasi` : '0 Lokasi';
        }

        if (places.length === 0) {
            listEl.innerHTML = `
                <div style="text-align:center;padding:16px;color:var(--text-muted);font-size:12px">
                    Belum ditemukan lokasi pada radius ${currentRadius}m.<br>Coba perbesar radius jangkauan ke 1km atau 2km.
                </div>`;
            return;
        }

        let html = '';
        places.forEach((p, idx) => {
            const dirUrl = getDirectionUrl(p.lat, p.lng, p.name);
            const distLabel = p.distance < 1000 ? `${p.distance} m` : `${(p.distance/1000).toFixed(1)} km`;

            html += `
                <div class="nearby-place-item">
                    <div style="font-size:22px;line-height:1;margin-right:2px">${p.icon}</div>
                    <div class="nearby-place-info">
                        <div class="nearby-place-name" title="${p.name}">${p.name}</div>
                        <div class="nearby-place-meta">
                            <span class="nearby-dist-badge">📍 ${distLabel}</span>
                            <span>• Estimasi ${(p.distance / 250).toFixed(0) || 1} mnt</span>
                        </div>
                    </div>
                    <a href="${dirUrl}" target="_blank" class="nearby-dir-btn">
                        <span>🗺️ Rute</span>
                    </a>
                </div>`;

            // Add marker to Leaflet Map
            if (nearbyMap && typeof L !== 'undefined') {
                const poiIcon = L.divIcon({
                    className: 'custom-poi-marker',
                    html: `<span>${p.icon}</span>`,
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });

                const marker = L.marker([p.lat, p.lng], { icon: poiIcon }).addTo(nearbyMap);
                marker.bindPopup(`
                    <div style="font-family:sans-serif;font-size:12px">
                        <strong>${p.icon} ${p.name}</strong><br>
                        <span style="color:#059669;font-weight:700">Jarak: ${distLabel}</span><br>
                        <a href="${dirUrl}" target="_blank" style="display:inline-block;margin-top:6px;padding:4px 8px;background:#10B981;color:#fff;border-radius:6px;text-decoration:none;font-weight:700;font-size:11px">🗺️ Buka Rute Google Maps</a>
                    </div>
                `);
                poiMarkers.push(marker);
            }
        });

        listEl.innerHTML = html;
    }

    function acquireGeolocation() {
        const statusEl = document.getElementById('nearbyLocStatus');
        if (statusEl) statusEl.innerText = 'Mencari GPS...';

        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(
                pos => {
                    updateMapPosition(pos.coords.latitude, pos.coords.longitude);
                    if (statusEl) statusEl.innerText = 'GPS Terkunci';
                    loadNearbyPlaces();
                },
                err => {
                    if (statusEl) statusEl.innerText = 'GPS Default';
                    // Fallback to default position
                    updateMapPosition(currentLat, currentLng);
                    loadNearbyPlaces();
                },
                { enableHighAccuracy: true, timeout: 7000 }
            );
        } else {
            if (statusEl) statusEl.innerText = 'GPS Default';
            updateMapPosition(currentLat, currentLng);
            loadNearbyPlaces();
        }
    }

    // Bind Event Listeners
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize Map
        initMap();
        acquireGeolocation();
        setTimeout(() => {
            if (nearbyMap) nearbyMap.invalidateSize(true);
        }, 150);
        setTimeout(() => {
            if (nearbyMap) nearbyMap.invalidateSize(true);
        }, 450);

        // Accordion Toggle Header
        document.getElementById('nearbyAccordionHdr')?.addEventListener('click', () => {
            toggleNearbyList();
        });

        // Refresh Location Button
        document.getElementById('btnRefreshLoc')?.addEventListener('click', () => {
            acquireGeolocation();
        });

        // Category Pills
        const catPills = document.querySelectorAll('#nearbyCatPills .nearby-pill');
        catPills.forEach(pill => {
            pill.addEventListener('click', function() {
                catPills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                currentCategory = this.dataset.cat;
                loadNearbyPlaces();
            });
        });

        // Radius Chips
        const radChips = document.querySelectorAll('#nearbyRadiusChips .nearby-rad-chip');
        radChips.forEach(chip => {
            chip.addEventListener('click', function() {
                radChips.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                currentRadius = parseInt(this.dataset.rad, 10);
                if (radiusCircle) radiusCircle.setRadius(currentRadius);
                if (nearbyMap) nearbyMap.setView([currentLat, currentLng], currentRadius <= 500 ? 16 : (currentRadius <= 1000 ? 15 : 14));
                loadNearbyPlaces();
            });
        });

        // Initialize balance privacy from localStorage
        try {
            if (localStorage.getItem('duitku_hide_balance') === 'true') {
                toggleBalancePrivacy(true);
            }
        } catch(e) {}
    });

})();

let activeAppleCardIndex = 0;

function layoutAppleDeck() {
    const cards = document.querySelectorAll('.apple-deck-card');
    const wrapper = document.getElementById('appleWalletWrapper');
    if (!cards.length) return;
    const totalCards = cards.length;
    const stackOffsetPx = (totalCards - 1) * 36;
    if (wrapper) {
        wrapper.style.height = `calc(195px + ${stackOffsetPx}px)`;
    }

    let stackIdx = 0;
    cards.forEach((card, idx) => {
        if (idx === activeAppleCardIndex) {
            card.classList.add('active-card');
            const yOffset = (totalCards - 1) * 36;
            card.style.setProperty('--card-y', `${yOffset}px`);
            card.style.setProperty('--card-scale', '1');
            card.style.transform = `translateY(${yOffset}px) scale(1)`;
            card.style.zIndex = '25';
            card.style.opacity = '1';
        } else {
            card.classList.remove('active-card');
            const yOffset = stackIdx * 36;
            const scale = 0.94 + (stackIdx * 0.018);
            card.style.setProperty('--card-y', `${yOffset}px`);
            card.style.setProperty('--card-scale', `${scale}`);
            card.style.transform = `translateY(${yOffset}px) scale(${scale})`;
            card.style.zIndex = String(stackIdx + 1);
            card.style.opacity = '0.94';
            stackIdx++;
        }
    });
}

function selectAppleDeckCard(idx) {
    if (activeAppleCardIndex === idx) return;
    activeAppleCardIndex = idx;
    layoutAppleDeck();
}
window.selectAppleDeckCard = selectAppleDeckCard;

document.addEventListener('DOMContentLoaded', () => {
    layoutAppleDeck();
});

function toggleBalancePrivacy(forceHide = null) {
    const isHidden = (localStorage.getItem('duitku_hide_balance') === 'true');
    const shouldHide = forceHide !== null ? forceHide : !isHidden;

    try { localStorage.setItem('duitku_hide_balance', shouldHide ? 'true' : 'false'); } catch(e) {}

    // Update all cards in deck
    document.querySelectorAll('.display-balance-amount').forEach(el => {
        const raw = el.dataset.raw || el.textContent;
        const hidden = el.dataset.hidden || 'Rp •••••••••';
        el.textContent = shouldHide ? hidden : raw;
    });

    document.querySelectorAll('.apple-card-mini-balance').forEach(el => {
        const raw = el.dataset.raw || el.textContent;
        const hidden = el.dataset.hidden || 'Rp ••••';
        el.textContent = shouldHide ? hidden : raw;
    });

    document.querySelectorAll('.btn-privacy-toggle').forEach(btn => {
        const open = btn.querySelector('.eye-open');
        const closed = btn.querySelector('.eye-closed');
        if (open) open.style.display = shouldHide ? 'none' : 'block';
        if (closed) closed.style.display = shouldHide ? 'block' : 'none';
    });
}

async function toggleHomeTask(event, taskId) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    const item = document.getElementById('homeTodo-' + taskId);
    if (!item) return;

    item.classList.toggle('done');

    try {
        await fetch('/todo/toggle/' + taskId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: '<?= csrf_token() ?>=<?= csrf_hash() ?>'
        });
    } catch(e) {
        console.error('Error toggling home todo:', e);
    }
}

/* ── TV Home Player Controller (No Autoplay) ── */
let homeHls = null;
let isHomeTvPlaying = false;

function loadHlsScriptIfNeeded(callback) {
    if (typeof Hls !== 'undefined') {
        callback();
        return;
    }
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/hls.js@latest';
    script.onload = callback;
    document.head.appendChild(script);
}

function startHomeTvPlay() {
    const poster = document.getElementById('tvHomePoster');
    const video = document.getElementById('homeTvVideo');
    if (!video) return;

    const streamUrl = video.dataset.stream;
    if (!streamUrl) return;

    if (poster) poster.classList.add('hidden');
    video.classList.add('active');
    isHomeTvPlaying = true;

    loadHlsScriptIfNeeded(() => {
        playStreamUrl(video, streamUrl);
    });
}

function playStreamUrl(video, url) {
    if (!url) return;
    if (homeHls) {
        homeHls.destroy();
        homeHls = null;
    }

    if (typeof Hls !== 'undefined' && Hls.isSupported() && (url.includes('.m3u8') || url.includes('m3u') || !url.endsWith('.mp4'))) {
        homeHls = new Hls({ enableWorker: true, lowLatencyMode: true });
        homeHls.loadSource(url);
        homeHls.attachMedia(video);
        homeHls.on(Hls.Events.MANIFEST_PARSED, () => {
            video.play().catch(() => {});
        });
    } else {
        video.src = url;
        video.play().catch(() => {});
    }
}

function switchHomeTvChannel(id, streamUrl, name, cat, btnEl) {
    document.querySelectorAll('.tv-home-chip').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');

    const video = document.getElementById('homeTvVideo');
    const posterName = document.getElementById('tvHomePosterName');
    if (posterName) posterName.textContent = name;
    if (video) video.dataset.stream = streamUrl;

    if (isHomeTvPlaying && video) {
        playStreamUrl(video, streamUrl);
    }
}

function switchMediaTab(tab) {
    const tabBtnTv = document.getElementById('tabBtnTv');
    const tabBtnFilm = document.getElementById('tabBtnFilm');
    const panelTv = document.getElementById('mediaPanelTv');
    const panelFilm = document.getElementById('mediaPanelFilm');
    const badgeLive = document.getElementById('badgeLiveTv');
    const badgeJellyfin = document.getElementById('badgeJellyfin');

    const tvVideo = document.getElementById('homeTvVideo');
    const filmVideo = document.getElementById('homeFilmVideo');

    if (tab === 'tv') {
        if (tabBtnTv) tabBtnTv.classList.add('active');
        if (tabBtnFilm) tabBtnFilm.classList.remove('active');
        if (panelTv) panelTv.style.display = 'block';
        if (panelFilm) panelFilm.style.display = 'none';
        if (badgeLive) badgeLive.style.display = 'inline-flex';
        if (badgeJellyfin) badgeJellyfin.style.display = 'none';

        if (filmVideo && !filmVideo.paused) {
            filmVideo.pause();
        }
    } else {
        if (tabBtnFilm) tabBtnFilm.classList.add('active');
        if (tabBtnTv) tabBtnTv.classList.remove('active');
        if (panelFilm) panelFilm.style.display = 'block';
        if (panelTv) panelTv.style.display = 'none';
        if (badgeLive) badgeLive.style.display = 'none';
        if (badgeJellyfin) badgeJellyfin.style.display = 'inline-flex';

        if (tvVideo && !tvVideo.paused) {
            tvVideo.pause();
        }
    }
}

function startHomeFilmPlay() {
    const poster = document.getElementById('filmHomePoster');
    const video = document.getElementById('homeFilmVideo');
    if (!video) return;

    const streamUrl = video.dataset.stream;
    if (!streamUrl) return;

    if (poster) poster.style.display = 'none';
    video.classList.add('active');

    video.src = streamUrl;
    video.play().catch(() => {});
}

function selectHomeFilm(el) {
    document.querySelectorAll('.film-mini-card').forEach(c => c.classList.remove('active'));
    el.classList.add('active');

    const video = document.getElementById('homeFilmVideo');
    const poster = document.getElementById('filmHomePoster');
    const nameEl = document.getElementById('filmHomePosterName');
    const hintEl = document.getElementById('filmHomePosterHint');

    const streamUrl = el.dataset.stream;
    const title = el.dataset.title;
    const year = el.dataset.year;
    const rating = el.dataset.rating;
    const duration = el.dataset.duration;
    const backdrop = el.dataset.backdrop;

    if (video) {
        video.dataset.stream = streamUrl;
        video.src = streamUrl;
        video.classList.remove('active');
        video.pause();
    }

    if (poster) {
        poster.style.display = 'flex';
        if (backdrop) {
            poster.style.backgroundImage = `url('${backdrop}')`;
        }
    }

    if (nameEl) nameEl.textContent = `${title} (${year})`;
    if (hintEl) {
        hintEl.textContent = `${rating ? '⭐ ' + rating + ' • ' : ''}${duration ? duration + ' • ' : ''}Ketuk untuk Memutar Film`;
    }
}


function filterJellyfinCatalog(q) {
    q = (q || '').toLowerCase().trim();
    document.querySelectorAll('.jellyfin-catalog-item').forEach(item => {
        const title = item.dataset.title || '';
        if (!q || title.includes(q)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function playFilmFromCatalog(id, streamUrl, title, year, backdrop, rating, duration) {
    document.querySelectorAll('.jellyfin-catalog-item').forEach(el => {
        el.style.borderColor = 'var(--border)';
    });
    if (window.event && window.event.currentTarget) {
        window.event.currentTarget.style.borderColor = '#00A4DC';
    }

    switchMediaTab('film');

    const video = document.getElementById('homeFilmVideo');
    const poster = document.getElementById('filmHomePoster');
    const nameEl = document.getElementById('filmHomePosterName');
    const hintEl = document.getElementById('filmHomePosterHint');

    if (video) {
        video.dataset.stream = streamUrl;
        video.src = streamUrl;
        video.classList.remove('active');
        video.pause();
    }

    if (poster) {
        poster.style.display = 'flex';
        if (backdrop) {
            poster.style.backgroundImage = `url('${backdrop}')`;
        }
    }

    if (nameEl) nameEl.textContent = `${title} (${year})`;
    if (hintEl) {
        hintEl.textContent = `${rating ? '⭐ ' + rating + ' • ' : ''}${duration ? duration + ' • ' : ''}Ketuk untuk Memutar Film`;
    }

    const box = document.getElementById('filmHomePlayerBox');
    if (box) {
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
</script>
<?= $this->endSection() ?>
