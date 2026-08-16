<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.bills-page {
    padding: 14px 16px 110px;
}

.bills-summary-card {
    background: linear-gradient(135deg, #1D4ED8 0%, #3B82F6 100%);
    border-radius: 20px;
    padding: 18px 20px;
    color: #fff;
    margin-bottom: 16px;
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.25);
    position: relative;
    overflow: hidden;
}
.bills-summary-card::after {
    content: '';
    position: absolute;
    right: -30px;
    bottom: -30px;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
}

.bills-summary-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.8;
    margin-bottom: 4px;
}
.bills-summary-amount {
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.5px;
    line-height: 1.1;
    margin-bottom: 6px;
}
.bills-summary-count {
    font-size: 12px;
    font-weight: 500;
    opacity: 0.85;
}

.bills-actions-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.bills-section-title {
    font-size: 15px;
    font-weight: 800;
    color: var(--text-primary);
}
.btn-add-bill {
    background: var(--primary);
    color: #fff;
    font-size: 12.5px;
    font-weight: 700;
    padding: 8px 14px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 2px 8px rgba(5, 150, 105, 0.25);
    transition: transform 0.15s ease;
}
.btn-add-bill:active {
    transform: scale(0.96);
}

.bills-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.bill-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 14px 16px;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    gap: 10px;
    position: relative;
}

.bill-card-main {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}

.bill-card-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.bill-card-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: #EFF6FF;
    color: #2563EB;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.bill-card-icon svg {
    width: 20px;
    height: 20px;
}

.bill-card-info {
    display: flex;
    flex-direction: column;
}
.bill-card-name {
    font-size: 14px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 2px;
}
.bill-card-due {
    font-size: 11.5px;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 4px;
}
.bill-card-due-badge {
    font-size: 10px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 6px;
    margin-left: 4px;
}
.due-soon {
    background: #FEF3C7;
    color: #D97706;
}
.due-overdue {
    background: #FEE2E2;
    color: #DC2626;
}
.due-normal {
    background: #F1F5F9;
    color: #475569;
}

.bill-card-amount {
    font-size: 15px;
    font-weight: 800;
    color: var(--text-primary);
    text-align: right;
}
.bill-card-notes {
    font-size: 11.5px;
    color: var(--text-muted);
    background: var(--bg);
    padding: 6px 10px;
    border-radius: 8px;
}

.bill-card-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    border-top: 1px solid var(--border);
    padding-top: 10px;
    margin-top: 2px;
}
.btn-pay-bill {
    background: var(--primary-dim);
    color: var(--primary);
    font-size: 12px;
    font-weight: 700;
    padding: 6px 12px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.btn-edit-bill, .btn-delete-bill {
    font-size: 12px;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 8px;
    color: var(--text-secondary);
}
.btn-delete-bill {
    color: var(--expense);
}

.bills-empty {
    text-align: center;
    padding: 40px 20px;
    background: var(--bg-card);
    border: 1px dashed var(--border);
    border-radius: 18px;
    color: var(--text-muted);
}
.bills-empty-icon {
    font-size: 40px;
    margin-bottom: 8px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $totalAmount = 0;
    $todayDay    = (int)date('j');
    foreach ($bills as $b) {
        $totalAmount += (float)($b['amount'] ?? 0);
    }
?>
<div class="bills-page">

    <!-- Summary Hero -->
    <div class="bills-summary-card">
        <div class="bills-summary-label">TOTAL TAGIHAN RUTIN BULANAN</div>
        <div class="bills-summary-amount"><?= esc($symbol) ?> <?= number_format($totalAmount, 0, ',', '.') ?></div>
        <div class="bills-summary-count"><?= count($bills) ?> tagihan terdaftar tiap bulan</div>
    </div>

    <!-- Header Actions -->
    <div class="bills-actions-bar">
        <div class="bills-section-title">Daftar Tagihan</div>
        <button class="btn-add-bill" id="btnOpenBillModal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Tagihan
        </button>
    </div>

    <!-- Bills List -->
    <?php if (empty($bills)): ?>
        <div class="bills-empty">
            <div class="bills-empty-icon">🧾</div>
            <div style="font-weight:700; color:var(--text-primary); margin-bottom:4px;">Belum Ada Tagihan</div>
            <div style="font-size:12px;">Tambahkan tagihan bulanan seperti Listrik, Air, Wifi, atau Langganan untuk pengingat otomatis.</div>
        </div>
    <?php else: ?>
        <div class="bills-list">
            <?php foreach ($bills as $b): ?>
                <?php
                    $dueDay   = (int)($b['dueDay'] ?? $b['due_day'] ?? 1);
                    $daysLeft = $dueDay - $todayDay;
                    $badgeClass = 'due-normal';
                    $badgeText  = "Tgl {$dueDay}";
                    if ($daysLeft === 0) {
                        $badgeClass = 'due-soon';
                        $badgeText  = 'Hari Ini!';
                    } elseif ($daysLeft > 0 && $daysLeft <= 3) {
                        $badgeClass = 'due-soon';
                        $badgeText  = "{$daysLeft} hari lagi";
                    } elseif ($daysLeft < 0 && $daysLeft >= -3) {
                        $badgeClass = 'due-overdue';
                        $badgeText  = abs($daysLeft) . " hari lalu";
                    }
                ?>
                <div class="bill-card" data-id="<?= esc($b['id']) ?>">
                    <div class="bill-card-main">
                        <div class="bill-card-left">
                            <div class="bill-card-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/>
                                    <line x1="8" y1="8" x2="16" y2="8"/><line x1="8" y1="12" x2="16" y2="12"/>
                                </svg>
                            </div>
                            <div class="bill-card-info">
                                <div class="bill-card-name"><?= esc($b['name']) ?></div>
                                <div class="bill-card-due">
                                    <span>Jatuh tempo:</span>
                                    <span class="bill-card-due-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="bill-card-amount">
                            <?= esc($symbol) ?> <?= number_format((float)($b['amount'] ?? 0), 0, ',', '.') ?>
                        </div>
                    </div>

                    <?php if (!empty($b['notes'])): ?>
                        <div class="bill-card-notes">
                            💬 <?= esc($b['notes']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="bill-card-actions">
                        <button type="button" class="btn-pay-bill" onclick="payBill(<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>)">
                            💳 Bayar Tagihan
                        </button>
                        <button type="button" class="btn-edit-bill" onclick="editBill(<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>)">
                            ✏️ Edit
                        </button>
                        <button type="button" class="btn-delete-bill" onclick="deleteBill('<?= esc($b['id']) ?>', '<?= esc(addslashes($b['name'])) ?>')">
                            🗑️ Hapus
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- ════════════════════════════════════════════════════ BILL MODAL SHEET -->
<div class="modal-overlay" id="billModalOverlay">
    <div class="modal-sheet" id="billModal">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3 id="billModalTitle">Tagihan Baru</h3>
            <button class="modal-close" id="billModalClose">✕</button>
        </div>

        <form id="billForm" autocomplete="off" style="display:flex; flex-direction:column; gap:14px;">
            <input type="hidden" id="billId" name="id">

            <div class="form-group">
                <label class="form-label" for="billName">NAMA TAGIHAN</label>
                <input type="text" id="billName" name="name" placeholder="contoh: Tagihan Listrik PLN" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="billAmount">NOMINAL TAGIHAN (<?= esc($symbol) ?>)</label>
                <input type="number" id="billAmount" name="amount" placeholder="0" class="form-input" min="0" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="billDueDay">TANGGAL JATUH TEMPO (TIAP BULAN)</label>
                <select id="billDueDay" name="due_day" class="form-input">
                    <?php for ($d = 1; $d <= 31; $d++): ?>
                        <option value="<?= $d ?>">Tanggal <?= $d ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="billNotes">CATATAN (OPSIONAL)</label>
                <input type="text" id="billNotes" name="notes" placeholder="Nomor pelanggan, ID meter, dsb." class="form-input">
            </div>

            <button type="submit" class="btn-save" id="btnSaveBill" style="margin-top:8px;">Simpan Tagihan</button>
        </form>
    </div>
</div>

<!-- ════════════════════════════ PAY BILL MODAL SHEET -->
<div class="modal-overlay" id="payBillModalOverlay">
    <div class="modal-sheet" id="payBillModal">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3>💳 Bayar Tagihan</h3>
            <button class="modal-close" id="payBillModalClose">✕</button>
        </div>

        <form id="payBillForm" autocomplete="off" style="display:flex; flex-direction:column; gap:14px;">
            <input type="hidden" name="type" value="expense">

            <div class="form-group">
                <label class="form-label">NAMA TAGIHAN / CATATAN</label>
                <input type="text" id="payBillNote" name="note" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">NOMINAL PEMBAYARAN (<?= esc($symbol) ?>)</label>
                <input type="number" id="payBillAmount" name="amount" class="form-input" min="0" required>
            </div>

            <div class="form-group">
                <label class="form-label">SUMBER DANA / REKENING</label>
                <select id="payBillWallet" name="wallet_id" class="form-input">
                    <?php if (!empty($wallets)): ?>
                        <?php foreach ($wallets as $w): ?>
                            <option value="<?= $w['id'] ?>" <?= !empty($w['is_default']) ? 'selected' : '' ?>>
                                <?= esc($w['name']) ?> (<?= esc($symbol) ?> <?= number_format((float)($w['balance'] ?? $w['initial_balance'] ?? 0), 0, ',', '.') ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">— Kas Utama —</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">KATEGORI PENGELUARAN</label>
                <select id="payBillCategory" name="category_id" class="form-input">
                    <option value="">— Pilih Kategori —</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $c): ?>
                            <?php if ($c['type'] === 'expense'): ?>
                                <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">TANGGAL BAYAR</label>
                <input type="date" id="payBillDate" name="date" class="form-input" value="<?= date('Y-m-d') ?>">
            </div>

            <button type="submit" class="btn-save" id="btnSubmitPayBill" style="margin-top:8px; background:var(--primary);">
                Konfirmasi & Catat Pengeluaran
            </button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const overlay    = document.getElementById('billModalOverlay');
    const modalClose = document.getElementById('billModalClose');
    const btnOpen    = document.getElementById('btnOpenBillModal');
    const billForm   = document.getElementById('billForm');

    function openModal(isEdit = false) {
        document.getElementById('billModalTitle').textContent = isEdit ? 'Edit Tagihan' : 'Tagihan Baru';
        overlay.classList.add('open');
    }
    function closeModal() {
        overlay.classList.remove('open');
        billForm.reset();
        document.getElementById('billId').value = '';
    }

    if (btnOpen) btnOpen.addEventListener('click', () => openModal(false));
    if (modalClose) modalClose.addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal();
    });

    window.editBill = function(b) {
        document.getElementById('billId').value = b.id || '';
        document.getElementById('billName').value = b.name || '';
        document.getElementById('billAmount').value = b.amount || '';
        document.getElementById('billDueDay').value = b.dueDay || b.due_day || 1;
        document.getElementById('billNotes').value = b.notes || '';
        openModal(true);
    };

    window.payBill = function(b) {
        document.getElementById('payBillNote').value   = 'Bayar ' + (b.name || 'Tagihan');
        document.getElementById('payBillAmount').value = b.amount || '';
        document.getElementById('payBillModalOverlay').classList.add('open');
    };

    document.getElementById('payBillModalClose')?.addEventListener('click', () => {
        document.getElementById('payBillModalOverlay').classList.remove('open');
    });

    document.getElementById('payBillForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = document.getElementById('payBillForm');
        const btn  = document.getElementById('btnSubmitPayBill');
        btn.disabled = true; btn.textContent = 'Memproses...';

        const formData = new FormData(form);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        try {
            const res = await fetch('/transaction/store', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                alert('Pembayaran tagihan berhasil dicatat sebagai pengeluaran!');
                window.location.reload();
            } else {
                alert(data.message || 'Gagal mencatat transaksi.');
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan.');
        }
        btn.disabled = false; btn.textContent = 'Konfirmasi & Catat Pengeluaran';
    });

    window.deleteBill = async function(id, name) {
        if (!confirm(`Hapus tagihan "${name}" secara permanen?`)) return;
        try {
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            const res = await fetch(`/bills/delete/${id}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Gagal menghapus tagihan.');
            }
        } catch (e) {
            alert('Terjadi kesalahan.');
        }
    };

    billForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(billForm);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        try {
            const res = await fetch('/bills/store', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Gagal menyimpan tagihan.');
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan.');
        }
    });
});
</script>
<?= $this->endSection() ?>
