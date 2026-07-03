<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
/* ── Wallet Page ───────────────────────────────────────── */
.wallet-page { padding-bottom: 32px; }

.wallet-page .page-hero {
    background: linear-gradient(140deg, #043D22 0%, #076836 42%, #0AA956 100%);
    border-radius: 22px;
    padding: 22px 20px 20px;
    margin-bottom: 20px;
    box-shadow: 0 8px 28px rgba(7,104,54,.28);
    text-align: center;
}
.wp-hero-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .6px;
    text-transform: uppercase;
    color: rgba(255,255,255,.5);
    margin-bottom: 6px;
}
.wp-hero-amount {
    font-size: 34px;
    font-weight: 800;
    color: #fff;
    letter-spacing: -1px;
}
.wp-hero-sub {
    font-size: 12px;
    color: rgba(255,255,255,.4);
    margin-top: 4px;
}

/* Transfer hero button */
.wp-transfer-btn {
    margin-top: 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    background: rgba(255,255,255,.15);
    border: 1.5px solid rgba(255,255,255,.25);
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    color: #fff;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,.15);
    transition: background .15s ease, transform .12s ease, box-shadow .12s ease;
}
.wp-transfer-btn:hover  { background: rgba(255,255,255,.22); }
.wp-transfer-btn:active { transform: translateY(1px) scale(.98); box-shadow: 0 1px 3px rgba(0,0,0,.12); }

/* Wallet list */
.wallet-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}
.wallet-item {
    display: flex;
    align-items: center;
    gap: 14px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 14px 16px;
    transition: border-color var(--transition);
}
.wallet-item:hover { border-color: var(--primary); }
.wi-icon {
    width: 48px; height: 48px;
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.wi-body { flex: 1; min-width: 0; }
.wi-name {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 2px;
}
.wi-meta {
    font-size: 11px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
}
.wi-default-badge {
    background: var(--primary-dim);
    color: var(--primary);
    font-size: 9px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.wi-balance {
    font-size: 16px;
    font-weight: 800;
    color: var(--text-primary);
    text-align: right;
    flex-shrink: 0;
}
.wi-balance.negative { color: var(--expense); }
.wi-actions {
    display: flex;
    gap: 4px;
    margin-left: 8px;
    flex-shrink: 0;
}
.wi-action-btn {
    width: 30px; height: 30px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    color: var(--text-secondary);
    background: transparent;
    transition: background var(--transition);
}
.wi-action-btn:hover { background: var(--border); }
.wi-action-btn.danger { color: var(--expense); }
.wi-action-btn.danger:hover { background: var(--expense-bg); }
.wi-action-btn.primary { color: var(--primary); }
.wi-action-btn.primary:hover { background: var(--primary-dim); }

/* Add wallet button */
.btn-add-wallet {
    width: 100%;
    padding: 14px;
    background: var(--bg-card);
    border: 1.5px dashed var(--border);
    border-radius: 18px;
    font-size: 14px;
    font-weight: 700;
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    transition: all var(--transition);
    margin-bottom: 20px;
}
.btn-add-wallet:hover { border-color: var(--primary); background: var(--primary-dim); }

/* Modal styles */
.w-form-group { margin-bottom: 14px; }
.w-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--text-secondary);
    margin-bottom: 6px;
}
.w-input {
    width: 100%;
    padding: 11px 14px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    font-size: 15px;
    font-family: var(--font);
    background: var(--bg);
    color: var(--text-primary);
    transition: border-color var(--transition);
    box-sizing: border-box;
}
.w-input:focus { outline: none; border-color: var(--primary); }

/* Icon + color pickers */
.icon-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 6px;
}
.icon-btn {
    height: 40px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-size: 18px;
    background: var(--bg);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all var(--transition);
}
.icon-btn.selected, .icon-btn:hover { border-color: var(--primary); background: var(--primary-dim); }
.color-swatch-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.color-swatch {
    width: 32px; height: 32px;
    border-radius: 50%;
    cursor: pointer;
    border: 2.5px solid transparent;
    transition: transform .12s ease;
    flex-shrink: 0;
}
.color-swatch:hover { transform: scale(1.12); }
.color-swatch.selected { border-color: var(--text-primary); transform: scale(1.12); }

/* Transfer form */
.transfer-arrow {
    text-align: center;
    font-size: 22px;
    color: var(--primary);
    margin: 4px 0 8px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="wallet-page">

    <!-- Hero total balance -->
    <div class="page-hero">
        <div class="wp-hero-label">Total Saldo Semua Rekening</div>
        <div class="wp-hero-amount"><?= esc($symbol) ?> <?= number_format($total, 0, ',', '.') ?></div>
        <div class="wp-hero-sub"><?= count($wallets) ?> rekening terdaftar</div>
        <button class="wp-transfer-btn" id="btnOpenTransfer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="16" height="16" stroke-linecap="round">
                <path d="M7 16V4m0 0L3 8m4-4 4 4M17 8v12m0 0 4-4m-4 4-4-4"/>
            </svg>
            Transfer Antar Rekening
        </button>
    </div>

    <!-- Wallet list -->
    <div class="wallet-list" id="walletList">
        <?php foreach ($wallets as $w):
            $neg  = $w['balance'] < 0;
            $bg   = $w['color'] . '22';
        ?>
        <div class="wallet-item" data-id="<?= $w['id'] ?>">
            <div class="wi-icon" style="background:<?= esc($bg) ?>">
                <?= esc($w['icon']) ?>
            </div>
            <div class="wi-body">
                <div class="wi-name"><?= esc($w['name']) ?></div>
                <div class="wi-meta">
                    <span><?= esc(\App\Models\WalletModel::typeLabel($w['type'])) ?></span>
                    <?php if ($w['is_default']): ?>
                    <span class="wi-default-badge">Utama</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="wi-balance<?= $neg ? ' negative' : '' ?>">
                <?= esc($symbol) ?> <?= number_format($w['balance'], 0, ',', '.') ?>
            </div>
            <div class="wi-actions">
                <?php if (!$w['is_default']): ?>
                <button class="wi-action-btn primary" data-act="default" data-id="<?= $w['id'] ?>" title="Jadikan utama">⭐</button>
                <?php endif; ?>
                <button class="wi-action-btn" data-act="edit" data-wallet='<?= json_encode($w) ?>' title="Edit">✏️</button>
                <?php if (!$w['is_default']): ?>
                <button class="wi-action-btn danger" data-act="delete" data-id="<?= $w['id'] ?>" title="Hapus">🗑</button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Add wallet -->
    <button class="btn-add-wallet" id="btnAddWallet">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Rekening Baru
    </button>

    <!-- Info card -->
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:14px 16px;font-size:13px;color:var(--text-secondary);line-height:1.6">
        <strong style="color:var(--primary);display:block;margin-bottom:6px">💡 Tips Multi-Rekening</strong>
        Setiap transaksi baru akan ditanya rekening mana yang digunakan. Transfer antar rekening tidak mempengaruhi total saldo keseluruhan.
    </div>

</div>

<!-- ══════════════════════ WALLET FORM MODAL ══════════════════════ -->
<div class="mini-modal-overlay" id="walletFormOverlay">
    <div class="mini-modal" style="max-width:440px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
            <h3 id="wfTitle" style="font-size:16px;font-weight:700">Tambah Rekening</h3>
            <button class="modal-close" id="wfClose">✕</button>
        </div>
        <form id="walletForm">
            <input type="hidden" id="wfId">

            <div class="w-form-group">
                <label class="w-label">Nama Rekening *</label>
                <input type="text" id="wfName" class="w-input" placeholder="Mis: BCA Utama, GoPay, Celengan…" required>
            </div>

            <div class="w-form-group">
                <label class="w-label">Jenis</label>
                <select id="wfType" class="w-input">
                    <option value="cash">💵 Dompet / Tunai</option>
                    <option value="bank">🏦 Rekening Bank</option>
                    <option value="e-wallet">📱 E-Wallet</option>
                    <option value="savings_home">🏠 Tabungan di Rumah / Celengan</option>
                </select>
            </div>

            <div class="w-form-group">
                <label class="w-label">Ikon</label>
                <div class="icon-grid" id="iconGrid">
                    <?php
                    $icons = ['💵','🏦','📱','🏠','💰','💳','🎯','🐷','💎','🪙','📊','🏧','💼','🛒','🎁','⭐'];
                    foreach ($icons as $ic): ?>
                    <button type="button" class="icon-btn" data-icon="<?= esc($ic) ?>"><?= $ic ?></button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="wfIcon" value="💵">
            </div>

            <div class="w-form-group">
                <label class="w-label">Warna</label>
                <div class="color-swatch-grid" id="colorSwatches">
                    <?php
                    $colors = ['#0AA956','#2563EB','#7C3AED','#DB2777','#D97706','#DC2626','#0891B2','#65A30D','#6B7280','#F97316'];
                    foreach ($colors as $clr): ?>
                    <div class="color-swatch" data-color="<?= $clr ?>" style="background:<?= $clr ?>"></div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="wfColor" value="#0AA956">
            </div>

            <div class="w-form-group">
                <label class="w-label">Saldo Awal (<?= esc($symbol) ?>) — Opsional</label>
                <input type="text" id="wfInitial" class="w-input" placeholder="Saldo yang sudah ada sebelum pakai aplikasi" inputmode="numeric" value="0">
            </div>

            <div style="display:flex;gap:8px;margin-top:4px">
                <button type="button" id="wfCancel" class="hs-outline-btn" style="flex:1">Batal</button>
                <button type="submit" class="hs-save-btn" style="flex:2">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════ TRANSFER MODAL ══════════════════════ -->
<div class="mini-modal-overlay" id="transferOverlay">
    <div class="mini-modal" style="max-width:400px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
            <h3 style="font-size:16px;font-weight:700">🔄 Transfer Antar Rekening</h3>
            <button class="modal-close" id="trClose">✕</button>
        </div>
        <form id="transferForm">
            <div class="w-form-group">
                <label class="w-label">Dari Rekening</label>
                <select id="trFrom" class="w-input">
                    <?php foreach ($wallets as $w): ?>
                    <option value="<?= $w['id'] ?>"><?= esc($w['icon']) ?> <?= esc($w['name']) ?> — <?= esc($symbol) ?> <?= number_format($w['balance'], 0, ',', '.') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="transfer-arrow">↕️</div>
            <div class="w-form-group">
                <label class="w-label">Ke Rekening</label>
                <select id="trTo" class="w-input">
                    <?php foreach ($wallets as $w): ?>
                    <option value="<?= $w['id'] ?>"><?= esc($w['icon']) ?> <?= esc($w['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="w-form-group">
                <label class="w-label">Nominal (<?= esc($symbol) ?>) *</label>
                <input type="text" id="trAmount" class="w-input" placeholder="0" inputmode="numeric" required>
            </div>
            <div class="w-form-group">
                <label class="w-label">Catatan (opsional)</label>
                <input type="text" id="trNote" class="w-input" placeholder="Mis: beli bensin, transfer ke OVO…">
            </div>
            <div class="w-form-group">
                <label class="w-label">Tanggal</label>
                <input type="date" id="trDate" class="w-input" value="<?= date('Y-m-d') ?>">
            </div>
            <div style="display:flex;gap:8px;margin-top:4px">
                <button type="button" id="trCancel" class="hs-outline-btn" style="flex:1">Batal</button>
                <button type="submit" class="hs-save-btn" style="flex:2">Transfer</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function() {
    'use strict';

    const COLORS  = <?= json_encode(array_values(['#0AA956','#2563EB','#7C3AED','#DB2777','#D97706','#DC2626','#0891B2','#65A30D','#6B7280','#F97316'])) ?>;
    const symbol  = '<?= esc($symbol) ?>';

    function csrf() {
        return { name: window.DUITKU.csrfName, token: window.DUITKU.csrfToken };
    }
    async function api(url, data) {
        const fd = new FormData();
        fd.append(csrf().name, csrf().token);
        Object.entries(data).forEach(([k,v]) => { if (v !== null && v !== undefined) fd.append(k, v); });
        const res = await fetch(url, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd });
        return res.json();
    }

    // ── Wallet Form ──────────────────────────────────────────────
    const wfOverlay = document.getElementById('walletFormOverlay');
    const wfForm    = document.getElementById('walletForm');
    const wfTitle   = document.getElementById('wfTitle');
    const wfClose   = document.getElementById('wfClose');
    const wfCancel  = document.getElementById('wfCancel');
    const wfId      = document.getElementById('wfId');
    const wfName    = document.getElementById('wfName');
    const wfType    = document.getElementById('wfType');
    const wfIcon    = document.getElementById('wfIcon');
    const wfColor   = document.getElementById('wfColor');
    const wfInitial = document.getElementById('wfInitial');

    function openWalletForm(wallet = null) {
        wfId.value      = wallet ? wallet.id : '';
        wfName.value    = wallet ? wallet.name : '';
        wfType.value    = wallet ? wallet.type : 'cash';
        wfIcon.value    = wallet ? wallet.icon : '💵';
        wfColor.value   = wallet ? wallet.color : '#0AA956';
        wfInitial.value = wallet && wallet.initial_balance > 0 ? Number(wallet.initial_balance).toLocaleString('id-ID') : '0';
        wfTitle.textContent = wallet ? 'Edit Rekening' : 'Tambah Rekening';

        // Reflect selections
        document.querySelectorAll('.icon-btn').forEach(b => b.classList.toggle('selected', b.dataset.icon === wfIcon.value));
        document.querySelectorAll('.color-swatch').forEach(b => b.classList.toggle('selected', b.dataset.color === wfColor.value));

        wfOverlay.classList.add('open');
        window.DuitkuLockScroll();
        setTimeout(() => wfName.focus(), 80);
    }
    function closeWalletForm() {
        wfOverlay.classList.remove('open');
        window.DuitkuUnlockScroll();
    }

    document.getElementById('btnAddWallet').addEventListener('click', () => openWalletForm());
    wfClose.addEventListener('click', closeWalletForm);
    wfCancel.addEventListener('click', closeWalletForm);
    wfOverlay.addEventListener('click', e => { if (e.target === wfOverlay) closeWalletForm(); });

    // Icon picker
    document.querySelectorAll('.icon-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.icon-btn').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            wfIcon.value = btn.dataset.icon;
        });
    });

    // Color picker
    document.querySelectorAll('.color-swatch').forEach(sw => {
        sw.addEventListener('click', () => {
            document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
            sw.classList.add('selected');
            wfColor.value = sw.dataset.color;
        });
    });

    // Initial balance formatting
    wfInitial.addEventListener('input', function() {
        const raw = this.value.replace(/\D/g,'');
        this.value = raw ? Number(raw).toLocaleString('id-ID') : '';
    });

    // Form submit
    wfForm.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = wfForm.querySelector('[type=submit]');
        btn.disabled = true;
        btn.textContent = 'Menyimpan…';

        const raw = wfInitial.value.replace(/\./g,'').replace(',','.');
        const data = await api('/wallets/store', {
            id:              wfId.value || '',
            name:            wfName.value.trim(),
            type:            wfType.value,
            icon:            wfIcon.value,
            color:           wfColor.value,
            initial_balance: parseFloat(raw) || 0,
        });

        btn.disabled = false;
        btn.textContent = 'Simpan';

        if (data.success) {
            closeWalletForm();
            window.showToast && window.showToast('Rekening disimpan!');
            setTimeout(() => location.reload(), 600);
        } else {
            alert(data.message || 'Gagal menyimpan.');
        }
    });

    // Wallet list actions (edit/delete/default)
    document.querySelectorAll('[data-act]').forEach(btn => {
        btn.addEventListener('click', async function() {
            const act = this.dataset.act;
            const id  = this.dataset.id;

            if (act === 'edit') {
                const w = JSON.parse(this.dataset.wallet || '{}');
                openWalletForm(w);
            } else if (act === 'delete') {
                if (!confirm('Hapus rekening ini? Semua transaksi terkait akan tetap ada.')) return;
                const data = await api('/wallets/delete/' + id, {});
                if (data.success) {
                    window.showToast && window.showToast('Rekening dihapus.');
                    setTimeout(() => location.reload(), 600);
                } else {
                    alert(data.message || 'Gagal menghapus.');
                }
            } else if (act === 'default') {
                const data = await api('/wallets/default/' + id, {});
                if (data.success) {
                    window.showToast && window.showToast('Rekening utama diubah!');
                    setTimeout(() => location.reload(), 600);
                }
            }
        });
    });

    // ── Transfer Form ────────────────────────────────────────────
    const trOverlay = document.getElementById('transferOverlay');
    const trForm    = document.getElementById('transferForm');
    const trClose   = document.getElementById('trClose');
    const trCancel  = document.getElementById('trCancel');
    const trAmount  = document.getElementById('trAmount');

    document.getElementById('btnOpenTransfer').addEventListener('click', () => {
        trOverlay.classList.add('open');
        window.DuitkuLockScroll();
    });
    trClose.addEventListener('click',  () => { trOverlay.classList.remove('open'); window.DuitkuUnlockScroll(); });
    trCancel.addEventListener('click', () => { trOverlay.classList.remove('open'); window.DuitkuUnlockScroll(); });
    trOverlay.addEventListener('click', e => { if (e.target === trOverlay) { trOverlay.classList.remove('open'); window.DuitkuUnlockScroll(); } });

    trAmount.addEventListener('input', function() {
        const raw = this.value.replace(/\D/g,'');
        this.value = raw ? Number(raw).toLocaleString('id-ID') : '';
    });

    trForm.addEventListener('submit', async e => {
        e.preventDefault();
        const fromId = document.getElementById('trFrom').value;
        const toId   = document.getElementById('trTo').value;
        if (fromId === toId) { alert('Rekening sumber dan tujuan tidak boleh sama.'); return; }

        const raw = trAmount.value.replace(/\./g,'').replace(',','.');
        const btn = trForm.querySelector('[type=submit]');
        btn.disabled = true;
        btn.textContent = 'Memproses…';

        const data = await api('/wallets/transfer', {
            from_wallet_id: fromId,
            to_wallet_id:   toId,
            amount:         parseFloat(raw) || 0,
            note:           document.getElementById('trNote').value.trim(),
            date:           document.getElementById('trDate').value,
        });

        btn.disabled = false;
        btn.textContent = 'Transfer';

        if (data.success) {
            trOverlay.classList.remove('open');
            window.DuitkuUnlockScroll();
            window.showToast && window.showToast('Transfer berhasil!');
            setTimeout(() => location.reload(), 600);
        } else {
            alert(data.message || 'Gagal transfer.');
        }
    });

})();
</script>
<?= $this->endSection() ?>
