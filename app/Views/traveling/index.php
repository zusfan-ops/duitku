<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.travel-page {
    padding: 14px 16px 110px;
}

.travel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}
.travel-title {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-primary);
}

.btn-new-trip {
    background: #0891B2;
    color: #fff;
    font-size: 12.5px;
    font-weight: 700;
    padding: 8px 14px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 12px rgba(8, 145, 178, 0.25);
    transition: transform 0.15s ease;
}
.btn-new-trip:active {
    transform: scale(0.96);
}

.trip-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.trip-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 16px;
    box-shadow: var(--shadow-sm);
    text-decoration: none;
    color: inherit;
    display: block;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.trip-card:active {
    transform: scale(0.98);
}

.trip-card-top {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.trip-card-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: #ECFEFF;
    color: #0891B2;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.trip-card-icon svg {
    width: 22px;
    height: 22px;
}

.trip-card-title-wrap {
    flex: 1;
    min-width: 0;
}
.trip-card-destination {
    font-size: 15px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.trip-card-dates {
    font-size: 12px;
    color: var(--text-secondary);
}

.trip-card-arrow {
    color: var(--text-muted);
}

.trip-card-divider {
    height: 1px;
    background: var(--border);
    margin-bottom: 12px;
}

.trip-card-stats {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.trip-stat-item {
    display: flex;
    flex-direction: column;
}
.trip-stat-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    margin-bottom: 2px;
}
.trip-stat-val {
    font-size: 13.5px;
    font-weight: 800;
    color: var(--text-primary);
}
.trip-stat-val.cost {
    color: var(--expense);
}

.travel-empty {
    text-align: center;
    padding: 50px 20px;
    background: var(--bg-card);
    border: 1px dashed var(--border);
    border-radius: 20px;
    color: var(--text-muted);
}
.travel-empty-icon {
    font-size: 48px;
    margin-bottom: 10px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="travel-page">

    <div class="travel-header">
        <div class="travel-title">Traveling & Trip</div>
        <button class="btn-new-trip" id="btnOpenTripModal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Buat Trip
        </button>
    </div>

    <?php if (empty($trips)): ?>
        <div class="travel-empty">
            <div class="travel-empty-icon">✈️</div>
            <div style="font-weight:800; color:var(--text-primary); margin-bottom:4px; font-size:16px;">Belum Ada Perjalanan</div>
            <div style="font-size:12.5px; max-width:280px; margin:0 auto 16px;">Buat rencana trip liburan atau dinas Anda, simpan tiket digital, checklist barang, dan pantau pengeluarannya.</div>
            <button class="btn-new-trip" onclick="document.getElementById('btnOpenTripModal').click()">
                + Tambah Trip Pertama
            </button>
        </div>
    <?php else: ?>
        <div class="trip-list">
            <?php foreach ($trips as $t): ?>
                <?php
                    $startFormatted = !empty($t['start_date']) ? date('d M Y', strtotime($t['start_date'])) : '';
                    $endFormatted   = !empty($t['end_date']) ? date('d M Y', strtotime($t['end_date'])) : '';
                    $dateRange      = $endFormatted ? "{$startFormatted} – {$endFormatted}" : $startFormatted;
                    $budget         = (float)($t['budget'] ?? 0);
                    $totalCost      = (float)($t['total_cost'] ?? 0);
                ?>
                <a href="/traveling/<?= esc($t['id']) ?>" class="trip-card">
                    <div class="trip-card-top">
                        <div class="trip-card-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/>
                            </svg>
                        </div>
                        <div class="trip-card-title-wrap">
                            <div class="trip-card-destination"><?= esc($t['destination']) ?></div>
                            <div class="trip-card-dates"><?= esc($dateRange) ?></div>
                        </div>
                        <div class="trip-card-arrow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                    </div>

                    <div class="trip-card-divider"></div>

                    <div class="trip-card-stats">
                        <div class="trip-stat-item">
                            <div class="trip-stat-label">TOTAL PENGELUARAN</div>
                            <div class="trip-stat-val cost"><?= esc($symbol) ?> <?= number_format($totalCost, 0, ',', '.') ?></div>
                        </div>
                        <div class="trip-stat-item" style="text-align:right;">
                            <div class="trip-stat-label">ANGGARAN / BUDGET</div>
                            <div class="trip-stat-val"><?= ($budget > 0) ? (esc($symbol) . ' ' . number_format($budget, 0, ',', '.')) : '—' ?></div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- ════════════════════════════════════════════════════ TRIP MODAL SHEET -->
<div class="modal-overlay" id="tripModalOverlay">
    <div class="modal-sheet" id="tripModal">
        <div class="modal-handle"></div>
        <div class="modal-header">
            <h3 id="tripModalTitle">Trip Baru</h3>
            <button class="modal-close" id="tripModalClose">✕</button>
        </div>

        <form id="tripForm" autocomplete="off" style="display:flex; flex-direction:column; gap:14px;">
            <input type="hidden" name="action" value="save_trip">
            <input type="hidden" id="tripId" name="id">

            <div class="form-group">
                <label class="form-label" for="tripDestination">DESTINASI / TUJUAN</label>
                <input type="text" id="tripDestination" name="destination" placeholder="contoh: Liburan Bali, Trip Jepang" class="form-input" required>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div class="form-group">
                    <label class="form-label" for="tripStartDate">TANGGAL MULAI</label>
                    <input type="date" id="tripStartDate" name="start_date" class="form-input" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="tripEndDate">TANGGAL SELESAI</label>
                    <input type="date" id="tripEndDate" name="end_date" class="form-input">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="tripBudget">ANGGARAN BUDGET (<?= esc($symbol) ?>)</label>
                <input type="number" id="tripBudget" name="budget" placeholder="0" class="form-input" min="0">
            </div>

            <div class="form-group">
                <label class="form-label" for="tripDescription">CATATAN / DESKRIPSI (OPSIONAL)</label>
                <input type="text" id="tripDescription" name="description" placeholder="Hotel, rencana itinerary, dsb." class="form-input">
            </div>

            <button type="submit" class="btn-save" id="btnSaveTrip" style="background:#0891B2; margin-top:8px;">Simpan Trip</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const overlay    = document.getElementById('tripModalOverlay');
    const modalClose = document.getElementById('tripModalClose');
    const btnOpen    = document.getElementById('btnOpenTripModal');
    const tripForm   = document.getElementById('tripForm');

    function openModal() {
        overlay.classList.add('open');
    }
    function closeModal() {
        overlay.classList.remove('open');
        tripForm.reset();
        document.getElementById('tripId').value = '';
    }

    if (btnOpen) btnOpen.addEventListener('click', openModal);
    if (modalClose) modalClose.addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal();
    });

    tripForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(tripForm);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        try {
            const res = await fetch('/traveling/sync', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Gagal menyimpan trip.');
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan.');
        }
    });
});
</script>
<?= $this->endSection() ?>
