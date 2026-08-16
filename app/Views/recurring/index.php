<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-container">
    <div class="page-header">
        <a href="/settings" class="back-btn" style="display:inline-flex;align-items:center;gap:6px;color:var(--text-muted);font-size:13px;text-decoration:none;margin-bottom:6px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="15 18 9 12 15 6"/></svg>
            Pengaturan
        </a>
        <h1>🔁 Transaksi Berulang</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Transaksi yang dijalankan otomatis secara berkala.</p>
    </div>

    <!-- Action bar -->
    <div style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap">
        <button class="btn-primary" id="btnAddRecurring" style="flex:1;min-width:140px">＋ Tambah Baru</button>
        <button class="btn-outline" id="btnProcess" style="flex:1;min-width:140px">⚡ Proses Sekarang</button>
    </div>

    <!-- Recurring list -->
    <div id="recurringListWrap">
        <?php if (empty($recurring)): ?>
        <div class="empty-state" style="text-align:center;padding:48px 20px">
            <div style="font-size:48px;margin-bottom:12px">🔁</div>
            <div style="font-weight:700;color:var(--text-primary);margin-bottom:6px">Belum ada transaksi berulang</div>
            <div style="font-size:13px;color:var(--text-muted)">Tambah transaksi yang berulang seperti gaji, langganan, atau cicilan.</div>
        </div>
        <?php else: ?>
        <div class="settings-list">
            <?php foreach ($recurring as $r):
                $freq = $r['frequency'] ?? 'monthly';
                $freqLabel = match($freq) { 'weekly' => 'Mingguan', 'yearly' => 'Tahunan', default => 'Bulanan' };
                $typeColor = $r['type'] === 'income' ? '#16A34A' : '#DC2626';
                $typeSign  = $r['type'] === 'income' ? '+' : '-';
            ?>
            <div class="settings-item recurring-item" data-id="<?= $r['id'] ?>">
                <div class="settings-item-left">
                    <div class="settings-item-icon"
                         style="background:<?= esc($r['category_color'] ?? '#6B7280') ?>20;color:<?= esc($r['category_color'] ?? '#6B7280') ?>">
                        <?= categoryIcon($r['category_icon'] ?? 'other') ?>
                    </div>
                    <div>
                        <div class="settings-item-label"><?= esc($r['category_name'] ?? 'Tanpa Kategori') ?></div>
                        <div style="font-size:11px;color:var(--text-muted)">
                            <span style="color:<?= $typeColor ?>;font-weight:700"><?= $typeSign ?> <?= esc($symbol) ?> <?= number_format($r['amount'], 0, ',', '.') ?></span>
                            &nbsp;·&nbsp; <?= $freqLabel ?>
                            &nbsp;·&nbsp; Berikutnya <strong><?= date('d M Y', strtotime($r['next_date'])) ?></strong>
                        </div>
                        <?php if ($r['note']): ?>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px"><?= esc($r['note']) ?></div>
                        <?php endif; ?>
                        <?php if ($r['wallet_name']): ?>
                        <div style="font-size:11px;color:var(--text-muted)">💳 <?= esc($r['wallet_name']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px">
                    <button class="btn-outline recurring-exec-btn" data-id="<?= $r['id'] ?>" data-name="<?= esc($r['category_name'] ?? 'Transaksi') ?>" style="padding:4px 8px;font-size:11px;border-radius:8px" title="Catat transaksi sekarang">
                        ⚡ Catat Sekarang
                    </button>
                    <button class="cat-delete-btn recurring-del-btn" data-id="<?= $r['id'] ?>" title="Hapus">✕</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Recurring Modal -->
<div class="mini-modal-overlay" id="addRecurringOverlay">
    <div class="mini-modal" style="max-width:420px">
        <h3>🔁 Transaksi Berulang Baru</h3>

        <!-- Type toggle -->
        <div style="display:flex;background:var(--bg);border-radius:10px;padding:4px;margin-bottom:14px">
            <button class="rec-type-btn active" data-type="expense" style="flex:1;padding:9px;border:none;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;transition:all .2s;background:#DC2626;color:#fff">Pengeluaran</button>
            <button class="rec-type-btn" data-type="income" style="flex:1;padding:9px;border:none;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;transition:all .2s;background:transparent;color:var(--text-muted)">Pemasukan</button>
        </div>
        <input type="hidden" id="recType" value="expense">

        <div class="form-group">
            <label class="form-label">KATEGORI</label>
            <select id="recCategory" class="form-input">
                <option value="">— Tanpa Kategori —</option>
                <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" data-type="<?= $c['type'] ?>"><?= esc($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">DOMPET / REKENING</label>
            <select id="recWallet" class="form-input">
                <option value="">— Default —</option>
                <?php foreach ($wallets as $w): ?>
                <option value="<?= $w['id'] ?>"><?= esc($w['icon'] . ' ' . $w['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">JUMLAH</label>
            <div class="amount-input-wrap" style="margin-bottom:0">
                <span class="amount-currency"><?= esc($symbol) ?></span>
                <input type="text" id="recAmount" class="amount-input" placeholder="0" inputmode="numeric">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">FREKUENSI</label>
            <select id="recFrequency" class="form-input">
                <option value="monthly">Bulanan (setiap bulan)</option>
                <option value="weekly">Mingguan (setiap minggu)</option>
                <option value="yearly">Tahunan (setiap tahun)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">MULAI TANGGAL</label>
            <input type="date" id="recStartDate" class="form-input" value="<?= date('Y-m-d') ?>">
        </div>

        <div class="form-group">
            <label class="form-label">CATATAN (OPSIONAL)</label>
            <input type="text" id="recNote" class="form-input" placeholder="cth. Gaji bulanan">
        </div>

        <div class="mini-modal-footer">
            <button class="btn-cancel-small" id="addRecurringClose">Batal</button>
            <button class="btn-save-small" id="addRecurringSave">Simpan</button>
        </div>
    </div>
</div>

<script>
(function(){
    // Type toggle
    document.querySelectorAll('.rec-type-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.rec-type-btn').forEach(b => {
                b.style.background = 'transparent';
                b.style.color = 'var(--text-muted)';
            });
            btn.style.background = btn.dataset.type === 'expense' ? '#DC2626' : '#16A34A';
            btn.style.color = '#fff';
            document.getElementById('recType').value = btn.dataset.type;
        });
    });

    // Format amount input
    document.getElementById('recAmount').addEventListener('input', function(){
        let raw = this.value.replace(/\D/g,'');
        this.value = raw ? parseInt(raw,10).toLocaleString('id-ID') : '';
    });

    // Open modal
    document.getElementById('btnAddRecurring').addEventListener('click', () => {
        document.getElementById('addRecurringOverlay').classList.add('active');
    });
    document.getElementById('addRecurringClose').addEventListener('click', () => {
        document.getElementById('addRecurringOverlay').classList.remove('active');
    });

    // Save recurring
    document.getElementById('addRecurringSave').addEventListener('click', async () => {
        const amount = document.getElementById('recAmount').value.replace(/\./g,'').replace(',','.');
        if (!amount || parseFloat(amount) <= 0) {
            alert('Masukkan nominal transaksi.'); return;
        }
        const btn = document.getElementById('addRecurringSave');
        btn.disabled = true; btn.textContent = 'Menyimpan...';

        try {
            const res = await fetch('/recurring/store', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
                body: new URLSearchParams({
                    type:       document.getElementById('recType').value,
                    category_id:document.getElementById('recCategory').value,
                    wallet_id:  document.getElementById('recWallet').value,
                    amount:     amount,
                    frequency:  document.getElementById('recFrequency').value,
                    start_date: document.getElementById('recStartDate').value,
                    note:       document.getElementById('recNote').value,
                })
            });
            const json = await res.json();
            if (json.success) {
                location.reload();
            } else {
                alert(json.message || 'Gagal menyimpan.');
            }
        } catch(e) {
            alert('Terjadi kesalahan.');
        }
        btn.disabled = false; btn.textContent = 'Simpan';
    });

    // Delete recurring
    document.querySelectorAll('.recurring-del-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Hentikan transaksi berulang ini?')) return;
            const id = btn.dataset.id;
            const res = await fetch('/recurring/drop/' + id, {method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}});
            const json = await res.json();
            if (json.success) {
                btn.closest('.recurring-item').remove();
                // Show empty if none left
                if (!document.querySelector('.recurring-item')) location.reload();
            }
        });
    });

    // Execute single recurring
    document.querySelectorAll('.recurring-exec-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const name = btn.dataset.name;
            if (!confirm(`Catat transaksi "${name}" sekarang dan majukan jadwal berikutnya?`)) return;
            const id = btn.dataset.id;
            btn.disabled = true; btn.textContent = 'Memproses...';
            try {
                const res = await fetch('/recurring/execute/' + id, {method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}});
                const json = await res.json();
                if (json.success) {
                    alert(json.message || 'Transaksi berhasil dicatat!');
                    location.reload();
                } else {
                    alert(json.message || 'Gagal mengeksekusi transaksi.');
                }
            } catch(e) {
                alert('Terjadi kesalahan.');
            }
            btn.disabled = false; btn.textContent = '⚡ Catat Sekarang';
        });
    });

    // Process recurring
    document.getElementById('btnProcess').addEventListener('click', async () => {
        const btn = document.getElementById('btnProcess');
        btn.disabled = true; btn.textContent = 'Memproses...';
        try {
            const res = await fetch('/recurring/process', {method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}});
            const json = await res.json();
            alert(json.message || 'Selesai.');
            if (json.processed > 0) location.reload();
        } catch(e) {
            alert('Terjadi kesalahan.');
        }
        btn.disabled = false; btn.textContent = '⚡ Proses Sekarang';
    });
})();
</script>

<?= $this->endSection() ?>
