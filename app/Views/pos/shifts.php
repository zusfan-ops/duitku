<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div style="max-width: 950px; margin: 0 auto; padding-bottom: 80px;">

    <!-- Top Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <a href="/pos/orders" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">← Pesanan</a>
                <h1 style="font-size: 20px; font-weight: 800; margin: 0;">💼 Shift Kasir & Rekonsiliasi Laci Uang</h1>
            </div>
            <p style="font-size: 13px; color: var(--text-muted); margin: 4px 0 0 0;">
                Kelola modal awal kasir, pantau uang masuk laci kas, dan rekonsiliasi uang fisik saat tutup kasir.
            </p>
        </div>

        <div>
            <?php if ($activeShift): ?>
                <button class="btn btn-danger" onclick="openCloseShiftModal()" style="border-radius: 12px; font-weight: 800; padding: 10px 18px;">
                    🔒 Tutup Shift & Hitung Kas
                </button>
            <?php else: ?>
                <button class="btn btn-primary" onclick="openOpenShiftModal()" style="border-radius: 12px; font-weight: 800; padding: 10px 18px;">
                    🔓 Buka Shift Kasir Baru
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Active Shift Status Card -->
    <?php if ($activeShift): ?>
        <div style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); border: 2px solid #EA580C; border-radius: 18px; padding: 20px; margin-bottom: 24px; color: #fff; box-shadow: 0 10px 25px rgba(234,88,12,0.15);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                <div>
                    <span style="font-size: 11px; font-weight: 800; background: #10B981; color: #fff; padding: 3px 8px; border-radius: 6px; letter-spacing: 0.5px;">
                        ● SHIFT SEDANG AKTIF
                    </span>
                    <h2 style="font-size: 18px; font-weight: 800; margin: 8px 0 2px;">Kasir: <?= esc($activeShift['cashier_name']) ?></h2>
                    <div style="font-size: 12px; color: #94A3B8;">Dibuka pada: <?= date('d M Y, H:i', strtotime($activeShift['opened_at'])) ?></div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 11px; color: #94A3B8; font-weight: 700;">ESTIMASI UANG LACI KAS</div>
                    <div style="font-size: 24px; font-weight: 900; color: #FB923C;">
                        <?= $symbol ?> <?= number_format($currentExpectedCash ?? $activeShift['expected_cash'], 0, ',', '.') ?>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px;">
                <div style="background: rgba(255,255,255,0.05); padding: 12px; border-radius: 12px;">
                    <div style="font-size: 11px; color: #94A3B8;">Modal Awal Kas:</div>
                    <div style="font-size: 14px; font-weight: 800;"><?= $symbol ?> <?= number_format($activeShift['starting_cash'], 0, ',', '.') ?></div>
                </div>
                <div style="background: rgba(255,255,255,0.05); padding: 12px; border-radius: 12px;">
                    <div style="font-size: 11px; color: #94A3B8;">Penjualan Tunai Shift Ini:</div>
                    <div style="font-size: 14px; font-weight: 800; color: #34D399;">+<?= $symbol ?> <?= number_format($currentCashSales ?? 0, 0, ',', '.') ?></div>
                </div>
                <div style="background: rgba(255,255,255,0.05); padding: 12px; border-radius: 12px;">
                    <div style="font-size: 11px; color: #94A3B8;">Total Transaksi:</div>
                    <div style="font-size: 14px; font-weight: 800;"><?= $currentTrxCount ?? 0 ?> Transaksi</div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div style="background: var(--card-bg); border: 2px dashed var(--border-color); border-radius: 18px; padding: 32px 20px; text-align: center; margin-bottom: 24px;">
            <div style="font-size: 40px; margin-bottom: 8px;">🔒</div>
            <h3 style="font-size: 16px; font-weight: 800;">Belum Ada Shift Kasir yang Dibuka</h3>
            <p style="font-size: 12.5px; color: var(--text-muted); max-width: 400px; margin: 4px auto 14px;">
                Buka shift kasir terlebih dahulu sebelum melayani transaksi untuk mencatat modal awal dan memantau laci kas.
            </p>
            <button class="btn btn-primary btn-sm" onclick="openOpenShiftModal()" style="border-radius: 10px; font-weight: 700;">
                + Buka Shift Sekarang
            </button>
        </div>
    <?php endif; ?>

    <!-- Shift History Section -->
    <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 12px;">📜 Riwayat Shift & Rekonsiliasi Kas</h3>

    <?php if (empty($shiftHistory)): ?>
        <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 14px; padding: 24px; text-align: center; color: var(--text-muted); font-size: 13px;">
            Belum ada riwayat shift kasir sebelumnya.
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($shiftHistory as $sh): ?>
                <?php
                    $isClosed = $sh['status'] === 'closed';
                    $diff = (float)$sh['difference'];
                    $diffColor = $diff >= 0 ? '#10B981' : '#EF4444';
                    $diffLabel = $diff == 0 ? 'Kas Cocok (Pas)' : ($diff > 0 ? '+' . $symbol . ' ' . number_format($diff, 0, ',', '.') . ' (Lebih)' : '-' . $symbol . ' ' . number_format(abs($diff), 0, ',', '.') . ' (Kurang)');
                ?>
                <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 14px; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 14px; font-weight: 800;"><?= esc($sh['cashier_name']) ?></span>
                            <?php if ($isClosed): ?>
                                <span class="badge bg-secondary" style="font-size: 10px;">Ditutup</span>
                            <?php else: ?>
                                <span class="badge bg-success" style="font-size: 10px;">Aktif</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">
                            <?= date('d/m/Y H:i', strtotime($sh['opened_at'])) ?>
                            <?= $isClosed && $sh['closed_at'] ? ' — ' . date('d/m/Y H:i', strtotime($sh['closed_at'])) : '' ?>
                        </div>
                    </div>

                    <div style="display: flex; gap: 20px; font-size: 12.5px; text-align: right;">
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted);">Modal Awal:</div>
                            <strong><?= $symbol ?> <?= number_format($sh['starting_cash'], 0, ',', '.') ?></strong>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted);">Penjualan:</div>
                            <strong><?= $symbol ?> <?= number_format($sh['total_sales'], 0, ',', '.') ?></strong>
                        </div>
                        <?php if ($isClosed): ?>
                            <div>
                                <div style="font-size: 11px; color: var(--text-muted);">Uang Fisik Kas:</div>
                                <strong><?= $symbol ?> <?= number_format($sh['actual_cash'] ?? 0, 0, ',', '.') ?></strong>
                            </div>
                            <div>
                                <div style="font-size: 11px; color: var(--text-muted);">Selisih:</div>
                                <strong style="color: <?= $diffColor ?>;"><?= $diffLabel ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Modal Buka Shift -->
<div class="modal fade" id="openShiftModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold">🔓 Buka Shift Kasir Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="openShiftForm" onsubmit="submitOpenShift(event)">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Kasir *</label>
                        <input type="text" name="cashier_name" id="open_cashier_name" class="form-control" value="<?= esc($currentUserName ?? 'Kasir') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Modal Awal Kas (Laci Uang) *</label>
                        <input type="number" name="starting_cash" id="open_starting_cash" class="form-control" placeholder="Contoh: 100000" min="0" value="100000" required>
                        <small class="text-muted">Uang pecahan kecil / kembalian awal di laci kasir.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Catatan (Opsional)</label>
                        <input type="text" name="notes" id="open_notes" class="form-control" placeholder="Shift Pagi / Shift Sore...">
                    </div>
                    <button type="submit" class="btn btn-primary w-100" style="border-radius: 12px; font-weight: 800; padding: 12px;">
                        Mulai Shift Kasir 🚀
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tutup Shift & Rekonsiliasi Kas -->
<div class="modal fade" id="closeShiftModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold">🔒 Tutup Shift & Rekonsiliasi Kas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="closeShiftForm" onsubmit="submitCloseShift(event)">
                    <input type="hidden" name="shift_id" value="<?= $activeShift['id'] ?? 0 ?>">
                    
                    <div style="background: rgba(234,88,12,0.1); border: 1px dashed #EA580C; border-radius: 12px; padding: 12px; margin-bottom: 16px; text-align: center;">
                        <div style="font-size: 12px; color: var(--text-muted);">Estimasi Sistem (Modal Awal + Tunai Masuk):</div>
                        <div style="font-size: 20px; font-weight: 900; color: #EA580C; margin-top: 2px;">
                            <?= $symbol ?> <?= number_format($currentExpectedCash ?? ($activeShift['expected_cash'] ?? 0), 0, ',', '.') ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Hitungan Uang Fisik Nyata di Laci (Rp) *</label>
                        <input type="number" name="actual_cash" id="close_actual_cash" class="form-control" placeholder="0" min="0" required oninput="calculateShiftDiff()">
                        <small class="text-muted">Hitung seluruh uang kertas dan koin di laci kasir.</small>
                    </div>

                    <div id="diffPreviewBox" style="display: none; padding: 10px; border-radius: 10px; font-size: 13px; font-weight: 800; margin-bottom: 14px; text-align: center;"></div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Catatan Penutupan</label>
                        <input type="text" name="notes" id="close_notes" class="form-control" placeholder="Semua aman / ada selisih karena...">
                    </div>

                    <button type="submit" class="btn btn-danger w-100" style="border-radius: 12px; font-weight: 800; padding: 12px;">
                        Tutup Shift & Simpan Laporan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let openModalInst = null;
    let closeModalInst = null;
    const expectedCashVal = <?= (float)($currentExpectedCash ?? ($activeShift['expected_cash'] ?? 0)) ?>;

    document.addEventListener('DOMContentLoaded', () => {
        const elOpen = document.getElementById('openShiftModal');
        const elClose = document.getElementById('closeShiftModal');
        if (elOpen) openModalInst = new bootstrap.Modal(elOpen);
        if (elClose) closeModalInst = new bootstrap.Modal(elClose);
    });

    function openOpenShiftModal() {
        if (openModalInst) openModalInst.show();
    }

    function openCloseShiftModal() {
        if (closeModalInst) closeModalInst.show();
    }

    function calculateShiftDiff() {
        const actual = parseFloat(document.getElementById('close_actual_cash').value) || 0;
        const diff = actual - expectedCashVal;
        const box = document.getElementById('diffPreviewBox');
        box.style.display = 'block';

        if (diff === 0) {
            box.style.background = 'rgba(16, 185, 129, 0.15)';
            box.style.color = '#10B981';
            box.textContent = '✅ Kas Cocok Sempurna (Tidak Ada Selisih)';
        } else if (diff > 0) {
            box.style.background = 'rgba(16, 185, 129, 0.15)';
            box.style.color = '#10B981';
            box.textContent = 'ℹ️ Kas Lebih: +' + Number(diff).toLocaleString('id-ID');
        } else {
            box.style.background = 'rgba(239, 68, 68, 0.15)';
            box.style.color = '#EF4444';
            box.textContent = '⚠️ Kas Kurang: -' + Number(Math.abs(diff)).toLocaleString('id-ID');
        }
    }

    async function submitOpenShift(e) {
        e.preventDefault();
        const form = document.getElementById('openShiftForm');
        const fd = new FormData(form);

        try {
            const res = await fetch('/pos/shifts/open', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal membuka shift');
            }
        } catch (err) {
            alert('Terjadi kesalahan jaringan.');
        }
    }

    async function submitCloseShift(e) {
        e.preventDefault();
        const form = document.getElementById('closeShiftForm');
        const fd = new FormData(form);

        try {
            const res = await fetch('/pos/shifts/close', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal menutup shift');
            }
        } catch (err) {
            alert('Terjadi kesalahan jaringan.');
        }
    }
</script>

<?= $this->endSection() ?>
