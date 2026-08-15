<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.trip-detail-page {
    padding: 12px 16px 110px;
}

/* Trip Header Card */
.trip-header-card {
    background: linear-gradient(135deg, #0E7490 0%, #06B6D4 100%);
    border-radius: 20px;
    padding: 18px 18px 16px;
    color: #fff;
    margin-bottom: 14px;
    box-shadow: 0 8px 24px rgba(6, 182, 212, 0.25);
    position: relative;
    overflow: hidden;
}
.trip-header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}
.trip-header-back {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: rgba(255, 255, 255, 0.85);
    font-size: 12.5px;
    font-weight: 600;
}
.trip-header-actions {
    display: flex;
    gap: 8px;
}
.btn-trip-action {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: #fff;
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}

.trip-header-dest {
    font-size: 22px;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 4px;
}
.trip-header-dates {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.85);
}

/* Tab Bar */
.trip-tabbar {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 4px;
    margin-bottom: 16px;
    box-shadow: var(--shadow-sm);
}
.trip-tab {
    padding: 9px 0;
    text-align: center;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--text-secondary);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}
.trip-tab.active {
    background: #0891B2;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(8, 145, 178, 0.25);
}

/* Tab Contents */
.tab-pane {
    display: none;
}
.tab-pane.active {
    display: block;
}

/* ── Checklist ── */
.checklist-progress-wrap {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 14px 16px;
    margin-bottom: 14px;
    box-shadow: var(--shadow-sm);
}
.checklist-progress-bar-bg {
    height: 8px;
    background: var(--border);
    border-radius: 4px;
    overflow: hidden;
    margin-top: 8px;
}
.checklist-progress-bar-fill {
    height: 100%;
    background: var(--primary);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.checklist-items {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.checklist-item-row {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: var(--shadow-sm);
}
.checklist-item-left {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    flex: 1;
}
.checklist-checkbox {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    border: 2px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.15s ease;
}
.checklist-checkbox.checked {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
}
.checklist-item-name {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-primary);
}
.checklist-item-name.packed {
    text-decoration: line-through;
    color: var(--text-muted);
}
.btn-delete-check-item {
    color: var(--text-muted);
    font-size: 14px;
    padding: 4px;
    cursor: pointer;
}

/* ── Digital Tickets ── */
.ticket-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 16px;
    margin-bottom: 12px;
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
}
.ticket-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.ticket-badge {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 3px 8px;
    border-radius: 6px;
    background: #E0F2FE;
    color: #0284C7;
}
.ticket-route {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.ticket-city {
    font-size: 15px;
    font-weight: 800;
    color: var(--text-primary);
}
.ticket-plane-icon {
    color: var(--text-muted);
    font-size: 18px;
}
.ticket-dashed-line {
    border-top: 1.5px dashed var(--border);
    margin: 12px -16px;
    position: relative;
}
.ticket-dashed-line::before, .ticket-dashed-line::after {
    content: '';
    position: absolute;
    top: -8px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--bg);
}
.ticket-dashed-line::before { left: -8px; }
.ticket-dashed-line::after { right: -8px; }

.ticket-meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    font-size: 12px;
}
.ticket-meta-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--text-muted);
}
.ticket-meta-val {
    font-weight: 700;
    color: var(--text-primary);
}
.ticket-qr-btn {
    width: 100%;
    margin-top: 12px;
    background: var(--bg);
    border: 1px solid var(--border);
    padding: 8px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
}

/* ── Expenses / Biaya ── */
.cost-budget-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 16px;
    margin-bottom: 14px;
    box-shadow: var(--shadow-sm);
}
.cost-budget-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}
.cost-tx-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.cost-tx-item {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: var(--shadow-sm);
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $startFormatted = !empty($trip['start_date']) ? date('d M Y', strtotime($trip['start_date'])) : '';
    $endFormatted   = !empty($trip['end_date']) ? date('d M Y', strtotime($trip['end_date'])) : '';
    $dateRange      = $endFormatted ? "{$startFormatted} – {$endFormatted}" : $startFormatted;
    $budget         = (float)($trip['budget'] ?? 0);
    $totalCost      = (float)($trip['total_cost'] ?? 0);

    $packedCount = 0;
    foreach ($items as $it) {
        if (!empty($it['is_packed'])) $packedCount++;
    }
    $totalItems = count($items);
    $progressPct = $totalItems > 0 ? round(($packedCount / $totalItems) * 100) : 0;
?>
<div class="trip-detail-page">

    <!-- Trip Header Card -->
    <div class="trip-header-card">
        <div class="trip-header-top">
            <a href="/traveling" class="trip-header-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                Kembali
            </a>
            <div class="trip-header-actions">
                <button type="button" class="btn-trip-action" onclick="deleteTrip('<?= esc($trip['id']) ?>', '<?= esc(addslashes($trip['destination'])) ?>')">Hapus Trip</button>
            </div>
        </div>
        <div class="trip-header-dest"><?= esc($trip['destination']) ?></div>
        <div class="trip-header-dates">🗓️ <?= esc($dateRange) ?></div>
        <?php if (!empty($trip['description'])): ?>
            <div style="font-size:12px; opacity:0.9; margin-top:6px;"><?= esc($trip['description']) ?></div>
        <?php endif; ?>
    </div>

    <!-- 3 Tabs Navigation -->
    <div class="trip-tabbar">
        <div class="trip-tab active" data-target="tabChecklist">
            🧳 Checklist
        </div>
        <div class="trip-tab" data-target="tabTickets">
            🎫 Tiket
        </div>
        <div class="trip-tab" data-target="tabCost">
            💰 Biaya
        </div>
    </div>

    <!-- ════════════════════════════════════════════ TAB 1: CHECKLIST -->
    <div class="tab-pane active" id="tabChecklist">
        <div class="checklist-progress-wrap">
            <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:700; color:var(--text-secondary);">
                <span>PROGRESS PACKING</span>
                <span><?= $packedCount ?> dari <?= $totalItems ?> barang (<?= $progressPct ?>%)</span>
            </div>
            <div class="checklist-progress-bar-bg">
                <div class="checklist-progress-bar-fill" style="width: <?= $progressPct ?>%;"></div>
            </div>
        </div>

        <form id="addCheckItemForm" style="display:flex; gap:8px; margin-bottom:14px;">
            <input type="text" id="checkItemName" placeholder="Tambah barang bawaan (cth: Paspor, Obat)" class="form-input" style="flex:1;" required>
            <button type="submit" class="btn-save" style="width:auto; padding:0 16px; font-size:13px; background:#0891B2;">+ Tambah</button>
        </form>

        <div class="checklist-items">
            <?php if (empty($items)): ?>
                <div style="text-align:center; padding:30px 10px; color:var(--text-muted); font-size:13px;">
                    Belum ada barang di checklist. Tambahkan barang di atas.
                </div>
            <?php else: ?>
                <?php foreach ($items as $it): ?>
                    <?php $isPacked = !empty($it['is_packed']); ?>
                    <div class="checklist-item-row" id="item-<?= esc($it['id']) ?>">
                        <div class="checklist-item-left" onclick="toggleItem('<?= esc($it['id']) ?>')">
                            <div class="checklist-checkbox <?= $isPacked ? 'checked' : '' ?>">
                                <?php if ($isPacked): ?>✓<?php endif; ?>
                            </div>
                            <span class="checklist-item-name <?= $isPacked ? 'packed' : '' ?>">
                                <?= esc($it['name']) ?>
                            </span>
                        </div>
                        <span class="btn-delete-check-item" onclick="deleteItem('<?= esc($it['id']) ?>')">✕</span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ════════════════════════════════════════════ TAB 2: TIKET -->
    <div class="tab-pane" id="tabTickets">
        <button type="button" class="btn-save" id="btnOpenTicketModal" style="background:#0891B2; margin-bottom:14px;">
            + Tambah Tiket Digital
        </button>

        <?php if (empty($tickets)): ?>
            <div style="text-align:center; padding:40px 10px; color:var(--text-muted); font-size:13px;">
                Belum ada tiket digital untuk trip ini. Simpan tiket pesawat/kereta agar mudah diakses.
            </div>
        <?php else: ?>
            <?php foreach ($tickets as $tk): ?>
                <div class="ticket-card">
                    <div class="ticket-card-header">
                        <span class="ticket-badge"><?= strtoupper(esc($tk['type'] ?? 'TIKET')) ?></span>
                        <span style="font-size:12px; color:var(--text-muted); cursor:pointer;" onclick="deleteTicket('<?= esc($tk['id']) ?>')">🗑️ Hapus</span>
                    </div>

                    <div class="ticket-route">
                        <div class="ticket-city"><?= esc($tk['departure'] ?? 'Asal') ?></div>
                        <div class="ticket-plane-icon">➔</div>
                        <div class="ticket-city"><?= esc($tk['arrival'] ?? 'Tujuan') ?></div>
                    </div>

                    <div class="ticket-dashed-line"></div>

                    <div class="ticket-meta-grid">
                        <div>
                            <div class="ticket-meta-label">PENUMPANG</div>
                            <div class="ticket-meta-val"><?= esc($tk['passenger_name'] ?: '—') ?></div>
                        </div>
                        <div>
                            <div class="ticket-meta-label">WAKTU BERANGKAT</div>
                            <div class="ticket-meta-val"><?= esc($tk['departure_time'] ?: '—') ?></div>
                        </div>
                        <div>
                            <div class="ticket-meta-label">KODE BOOKING</div>
                            <div class="ticket-meta-val" style="color:#0891B2; letter-spacing:0.5px;"><?= esc($tk['code'] ?: '—') ?></div>
                        </div>
                        <div>
                            <div class="ticket-meta-label">KURSI / SEAT</div>
                            <div class="ticket-meta-val"><?= esc($tk['seat'] ?: '—') ?></div>
                        </div>
                    </div>

                    <?php if (!empty($tk['qr_data']) || !empty($tk['code'])): ?>
                        <button type="button" class="ticket-qr-btn" onclick="showTicketQR('<?= esc(addslashes($tk['qr_data'] ?: $tk['code'])) ?>', '<?= esc(addslashes($tk['code'])) ?>')">
                            📱 Tampilkan QR / Barcode Tiket
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ════════════════════════════════════════════ TAB 3: BIAYA -->
    <div class="tab-pane" id="tabCost">
        <div class="cost-budget-card">
            <div class="cost-budget-row">
                <span style="font-size:12px; font-weight:700; color:var(--text-muted);">TOTAL PENGELUARAN TRIP</span>
                <span style="font-size:16px; font-weight:800; color:var(--expense);">
                    <?= esc($symbol) ?> <?= number_format($totalCost, 0, ',', '.') ?>
                </span>
            </div>
            <?php if ($budget > 0): ?>
                <?php $budgetPct = min(round(($totalCost / $budget) * 100), 100); ?>
                <div class="cost-budget-row" style="margin-top:8px;">
                    <span style="font-size:12px; color:var(--text-secondary);">Anggaran: <?= esc($symbol) ?> <?= number_format($budget, 0, ',', '.') ?></span>
                    <span style="font-size:12px; font-weight:700; color:<?= $totalCost > $budget ? 'var(--expense)' : 'var(--primary)' ?>;"><?= $budgetPct ?>% terpakai</span>
                </div>
                <div class="checklist-progress-bar-bg" style="margin-top:6px;">
                    <div class="checklist-progress-bar-fill" style="width:<?= $budgetPct ?>%; background:<?= $totalCost > $budget ? 'var(--expense)' : 'var(--primary)' ?>;"></div>
                </div>
            <?php endif; ?>
        </div>

        <button type="button" class="btn-save" onclick="addTripExpense()" style="background:#0891B2; margin-bottom:14px;">
            + Catat Pengeluaran Trip
        </button>

        <div class="cost-tx-list">
            <?php if (empty($transactions)): ?>
                <div style="text-align:center; padding:30px 10px; color:var(--text-muted); font-size:13px;">
                    Belum ada catatan pengeluaran khusus trip ini.
                </div>
            <?php else: ?>
                <?php foreach ($transactions as $tx): ?>
                    <?php
                        $cleanNote = trim(str_replace('[Trip:' . $trip['id'] . ']', '', $tx['note']));
                    ?>
                    <div class="cost-tx-item">
                        <div>
                            <div style="font-size:13.5px; font-weight:700; color:var(--text-primary);">
                                <?= esc($cleanNote ?: ($tx['category_name'] ?? 'Pengeluaran Trip')) ?>
                            </div>
                            <div style="font-size:11.5px; color:var(--text-muted); margin-top:2px;">
                                <?= date('d M Y', strtotime($tx['date'])) ?> <?= !empty($tx['wallet_name']) ? ' • ' . esc($tx['wallet_name']) : '' ?>
                            </div>
                        </div>
                        <div style="font-size:14px; font-weight:800; color:var(--expense);">
                            -<?= esc($symbol) ?> <?= number_format((float)$tx['amount'], 0, ',', '.') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ════════════════════════════════════════════════════ TICKET MODAL -->
<div class="modal-overlay" id="ticketModalOverlay">
    <div class="modal-sheet" id="ticketModal">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3>Tiket Digital Baru</h3>
            <button class="modal-close" id="ticketModalClose">✕</button>
        </div>

        <form id="ticketForm" autocomplete="off" style="display:flex; flex-direction:column; gap:12px;">
            <input type="hidden" name="action" value="save_ticket">
            <input type="hidden" name="trip_id" value="<?= esc($trip['id']) ?>">

            <div class="form-group">
                <label class="form-label" for="ticketType">JENIS TRANSPORTASI</label>
                <select id="ticketType" name="type" class="form-input">
                    <option value="flight">✈️ Pesawat</option>
                    <option value="train">🚆 Kereta Api</option>
                    <option value="bus">🚌 Bus</option>
                    <option value="ship">🚢 Kapal Laut</option>
                    <option value="other">🎫 Lainnya</option>
                </select>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div class="form-group">
                    <label class="form-label" for="ticketDep">KOTA ASAL</label>
                    <input type="text" id="ticketDep" name="departure" placeholder="contoh: Jakarta (CGK)" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="ticketArr">KOTA TUJUAN</label>
                    <input type="text" id="ticketArr" name="arrival" placeholder="contoh: Bali (DPS)" class="form-input" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div class="form-group">
                    <label class="form-label" for="ticketCode">KODE BOOKING</label>
                    <input type="text" id="ticketCode" name="code" placeholder="contoh: ABC123" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label" for="ticketSeat">KURSI / SEAT</label>
                    <input type="text" id="ticketSeat" name="seat" placeholder="contoh: 12A" class="form-input">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="ticketPassenger">NAMA PENUMPANG</label>
                <input type="text" id="ticketPassenger" name="passenger_name" placeholder="Nama lengkap" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label" for="ticketTime">WAKTU KEBERANGKATAN</label>
                <input type="text" id="ticketTime" name="departure_time" placeholder="contoh: 12 Agu 2026, 08:30 WIB" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label" for="ticketQr">QR / BARCODE DATA</label>
                <input type="text" id="ticketQr" name="qr_data" placeholder="String data barcode tiket jika ada" class="form-input">
            </div>

            <button type="submit" class="btn-save" style="background:#0891B2; margin-top:6px;">Simpan Tiket</button>
        </form>
    </div>
</div>

<!-- ════════════════════════════════════════════════════ QR PREVIEW MODAL -->
<div class="modal-overlay" id="qrModalOverlay">
    <div class="modal-sheet" id="qrModal" style="text-align:center;">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3>QR Code Tiket</h3>
            <button class="modal-close" id="qrModalClose">✕</button>
        </div>
        <div style="padding:20px 10px;">
            <div id="qrCodeContainer" style="display:flex; justify-content:center; margin-bottom:14px;">
                <img id="qrImage" src="" alt="QR Code" style="max-width:200px; border-radius:12px; border:1px solid var(--border);">
            </div>
            <div id="qrBookingCode" style="font-size:16px; font-weight:800; color:#0891B2; letter-spacing:1px;"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tripId = '<?= esc($trip['id']) ?>';

    // Tabs
    const tabs = document.querySelectorAll('.trip-tab');
    const panes = document.querySelectorAll('.tab-pane');
    tabs.forEach(t => {
        t.addEventListener('click', () => {
            tabs.forEach(tab => tab.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));
            t.classList.add('active');
            const target = document.getElementById(t.getAttribute('data-target'));
            if (target) target.classList.add('active');
        });
    });

    // Checklist add
    const addCheckForm = document.getElementById('addCheckItemForm');
    if (addCheckForm) {
        addCheckForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = document.getElementById('checkItemName');
            const name = input.value.trim();
            if (!name) return;

            const formData = new FormData();
            formData.append('action', 'save_item');
            formData.append('trip_id', tripId);
            formData.append('name', name);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            const res = await fetch('/traveling/sync', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            }
        });
    }

    // Toggle checklist item
    window.toggleItem = async function(id) {
        const formData = new FormData();
        formData.append('action', 'toggle_item');
        formData.append('id', id);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const res = await fetch('/traveling/sync', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        }
    };

    // Delete checklist item
    window.deleteItem = async function(id) {
        const formData = new FormData();
        formData.append('action', 'delete_item');
        formData.append('id', id);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const res = await fetch('/traveling/sync', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        }
    };

    // Delete trip
    window.deleteTrip = async function(id, dest) {
        if (!confirm(`Hapus trip "${dest}" beserta seluruh checklist, tiket, dan catatannya?`)) return;
        const formData = new FormData();
        formData.append('action', 'delete_trip');
        formData.append('id', id);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const res = await fetch('/traveling/sync', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            window.location.href = '/traveling';
        }
    };

    // Ticket modal
    const ticketOverlay = document.getElementById('ticketModalOverlay');
    const ticketClose   = document.getElementById('ticketModalClose');
    const btnOpenTicket = document.getElementById('btnOpenTicketModal');
    const ticketForm    = document.getElementById('ticketForm');

    if (btnOpenTicket) btnOpenTicket.addEventListener('click', () => ticketOverlay.classList.add('open'));
    if (ticketClose) ticketClose.addEventListener('click', () => ticketOverlay.classList.remove('open'));
    ticketOverlay.addEventListener('click', (e) => {
        if (e.target === ticketOverlay) ticketOverlay.classList.remove('open');
    });

    if (ticketForm) {
        ticketForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(ticketForm);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            const res = await fetch('/traveling/sync', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Gagal menyimpan tiket.');
            }
        });
    }

    window.deleteTicket = async function(id) {
        if (!confirm('Hapus tiket ini?')) return;
        const formData = new FormData();
        formData.append('action', 'delete_ticket');
        formData.append('id', id);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const res = await fetch('/traveling/sync', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        }
    };

    // QR Preview
    const qrOverlay = document.getElementById('qrModalOverlay');
    const qrClose   = document.getElementById('qrModalClose');
    if (qrClose) qrClose.addEventListener('click', () => qrOverlay.classList.remove('open'));
    qrOverlay.addEventListener('click', (e) => {
        if (e.target === qrOverlay) qrOverlay.classList.remove('open');
    });

    window.showTicketQR = function(data, code) {
        const qrImg = document.getElementById('qrImage');
        const qrCode = document.getElementById('qrBookingCode');
        qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(data);
        qrCode.textContent = code ? 'Kode: ' + code : '';
        qrOverlay.classList.add('open');
    };

    // Add Trip Expense
    window.addTripExpense = function() {
        const txOverlay = document.getElementById('txModalOverlay');
        if (txOverlay) {
            document.getElementById('txType').value = 'expense';
            const btnExp = document.getElementById('btnExpense');
            const btnInc = document.getElementById('btnIncome');
            if (btnExp) btnExp.classList.add('active');
            if (btnInc) btnInc.classList.remove('active');

            const noteInput = document.getElementById('txNote');
            if (noteInput) {
                noteInput.value = '[Trip:' + tripId + '] ';
            }
            txOverlay.classList.add('open');
        }
    };
});
</script>
<?= $this->endSection() ?>
