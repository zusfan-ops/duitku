<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-container">
    <div class="page-header">
        <a href="/settings" class="back-btn" style="display:inline-flex;align-items:center;gap:6px;color:var(--text-muted);font-size:13px;text-decoration:none;margin-bottom:6px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="15 18 9 12 15 6"/></svg>
            Pengaturan
        </a>
        <h1>🎯 Target Menabung</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Kelola semua tujuan tabunganmu dalam satu tempat.</p>
    </div>

    <button class="btn-primary" id="btnAddGoal" style="width:100%;margin-bottom:18px">＋ Tambah Target Baru</button>

    <!-- Goals list -->
    <div id="goalsList">
        <?php if (empty($goals)): ?>
        <div class="empty-state" style="text-align:center;padding:48px 20px">
            <div style="font-size:48px;margin-bottom:12px">🎯</div>
            <div style="font-weight:700;color:var(--text-primary);margin-bottom:6px">Belum ada target tabungan</div>
            <div style="font-size:13px;color:var(--text-muted)">Mulai tambahkan tujuan menabungmu, seperti liburan, kendaraan, atau dana darurat.</div>
        </div>
        <?php else: ?>
        <?php foreach ($goals as $g):
            $pct = $g['target_amount'] > 0
                ? min(($g['saved_amount'] / $g['target_amount']) * 100, 100) : 0;
            $reached = $pct >= 100;
            $daysLeft = null;
            if ($g['deadline']) {
                $daysLeft = (int)ceil((strtotime($g['deadline']) - time()) / 86400);
            }
        ?>
        <div class="savings-card goal-card" data-id="<?= $g['id'] ?>" style="margin-bottom:14px;padding:18px;border-radius:18px;background:var(--card);border:1px solid var(--border)">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;background:<?= esc($g['color']) ?>20;flex-shrink:0">
                        <?= esc($g['icon']) ?>
                    </div>
                    <div>
                        <div style="font-weight:800;font-size:15px;color:var(--text-primary)"><?= esc($g['name']) ?></div>
                        <?php if ($g['deadline']): ?>
                        <div style="font-size:11px;color:var(--text-muted)">
                            Tenggat <?= date('d M Y', strtotime($g['deadline'])) ?>
                            <?php if ($daysLeft !== null && $daysLeft >= 0): ?>
                            · <strong style="color:<?= $daysLeft <= 7 ? '#DC2626' : 'var(--text-muted)' ?>"><?= $daysLeft ?> hari lagi</strong>
                            <?php elseif ($daysLeft < 0): ?>
                            · <span style="color:#DC2626">Sudah lewat</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="display:flex;gap:6px">
                    <button class="goal-topup-btn btn-primary" data-id="<?= $g['id'] ?>" data-name="<?= esc($g['name']) ?>"
                            style="padding:6px 12px;font-size:12px;border-radius:8px" <?= $reached ? 'disabled' : '' ?>>
                        + Setor
                    </button>
                    <button class="goal-del-btn" data-id="<?= $g['id'] ?>"
                            style="padding:6px 10px;font-size:12px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--text-muted);cursor:pointer">
                        ✕
                    </button>
                </div>
            </div>

            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:8px">
                <span style="color:<?= esc($g['color']) ?>;font-weight:800"><?= esc($symbol) ?> <?= number_format($g['saved_amount'], 0, ',', '.') ?></span>
                <span style="color:var(--text-muted)">dari <?= esc($symbol) ?> <?= number_format($g['target_amount'], 0, ',', '.') ?></span>
            </div>
            <div style="height:8px;background:var(--border);border-radius:99px;overflow:hidden;margin-bottom:6px">
                <div class="goal-bar" data-id="<?= $g['id'] ?>" style="height:100%;width:<?= number_format($pct, 1) ?>%;background:<?= esc($g['color']) ?>;border-radius:99px;transition:width .6s ease"></div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:11px">
                <span style="color:var(--text-muted)"><?= $reached ? '🎉 Target tercapai!' : 'Sisa ' . esc($symbol) . ' ' . number_format(max($g['target_amount'] - $g['saved_amount'], 0), 0, ',', '.') ?></span>
                <span style="font-weight:800;color:<?= esc($g['color']) ?>"><?= number_format($pct, 0) ?>%</span>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add Goal Modal -->
<div class="mini-modal-overlay" id="addGoalOverlay">
    <div class="mini-modal" style="max-width:420px">
        <h3 id="goalModalTitle">🎯 Target Baru</h3>
        <input type="hidden" id="goalEditId" value="">

        <!-- Icon picker -->
        <div class="form-group">
            <label class="form-label">IKON</label>
            <div style="display:flex;gap:8px;flex-wrap:wrap" id="iconPicker">
                <?php foreach (['🎯','🏠','🚗','✈️','💍','📱','💊','🎓','🏋️','💰','🏖️','🎮'] as $ico): ?>
                <button class="goal-icon-btn" data-icon="<?= $ico ?>"
                        style="width:36px;height:36px;border:2px solid var(--border);border-radius:10px;background:var(--bg);font-size:18px;cursor:pointer;transition:all .15s">
                    <?= $ico ?>
                </button>
                <?php endforeach; ?>
            </div>
            <input type="hidden" id="goalIcon" value="🎯">
        </div>

        <!-- Color picker -->
        <div class="form-group">
            <label class="form-label">WARNA</label>
            <div style="display:flex;gap:8px;flex-wrap:wrap" id="goalColorPicker">
                <?php foreach (['#0AA956','#2563EB','#8B5CF6','#DC2626','#F59E0B','#0D9488','#EC4899','#F97316'] as $c): ?>
                <button class="goal-color-btn" data-color="<?= $c ?>"
                        style="width:26px;height:26px;border-radius:50%;background:<?= $c ?>;border:2px solid transparent;cursor:pointer;transition:all .15s">
                </button>
                <?php endforeach; ?>
            </div>
            <input type="hidden" id="goalColor" value="#0AA956">
        </div>

        <div class="form-group">
            <label class="form-label">NAMA TARGET</label>
            <input type="text" id="goalName" class="form-input" placeholder="cth. Dana Liburan, Beli Laptop...">
        </div>

        <div class="form-group">
            <label class="form-label">TARGET NOMINAL</label>
            <div class="amount-input-wrap" style="margin-bottom:0">
                <span class="amount-currency"><?= esc($symbol) ?></span>
                <input type="text" id="goalTarget" class="amount-input" placeholder="0" inputmode="numeric">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">SUDAH TERKUMPUL (OPSIONAL)</label>
            <div class="amount-input-wrap" style="margin-bottom:0">
                <span class="amount-currency"><?= esc($symbol) ?></span>
                <input type="text" id="goalSaved" class="amount-input" placeholder="0" inputmode="numeric">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">TENGGAT WAKTU (OPSIONAL)</label>
            <input type="date" id="goalDeadline" class="form-input">
        </div>

        <div class="mini-modal-footer">
            <button class="btn-cancel-small" id="addGoalClose">Batal</button>
            <button class="btn-save-small" id="addGoalSave">Simpan</button>
        </div>
    </div>
</div>

<!-- Top-Up Modal -->
<div class="mini-modal-overlay" id="topupOverlay">
    <div class="mini-modal">
        <h3>💸 Setor Tabungan</h3>
        <p id="topupGoalName" style="font-size:13px;color:var(--text-muted);margin-bottom:16px"></p>
        <input type="hidden" id="topupGoalId" value="">
        <div class="form-group">
            <label class="form-label">NOMINAL SETORAN</label>
            <div class="amount-input-wrap" style="margin-bottom:0">
                <span class="amount-currency"><?= esc($symbol) ?></span>
                <input type="text" id="topupAmount" class="amount-input" placeholder="0" inputmode="numeric">
            </div>
        </div>
        <div class="mini-modal-footer">
            <button class="btn-cancel-small" id="topupClose">Batal</button>
            <button class="btn-save-small" id="topupSave">Setor</button>
        </div>
    </div>
</div>

<script>
(function(){
    let selectedIcon = '🎯', selectedColor = '#0AA956';

    // Icon picker
    document.querySelectorAll('.goal-icon-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.goal-icon-btn').forEach(b => b.style.borderColor = 'var(--border)');
            btn.style.borderColor = '#0AA956';
            selectedIcon = btn.dataset.icon;
            document.getElementById('goalIcon').value = selectedIcon;
        });
    });
    // Select first icon by default
    document.querySelector('.goal-icon-btn')?.click();

    // Color picker
    document.querySelectorAll('.goal-color-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.goal-color-btn').forEach(b => b.style.border = '2px solid transparent');
            btn.style.border = '2px solid var(--text-primary)';
            selectedColor = btn.dataset.color;
            document.getElementById('goalColor').value = selectedColor;
        });
    });
    // Select first color by default
    document.querySelector('.goal-color-btn')?.click();

    // Format amount inputs
    ['goalTarget','goalSaved','topupAmount'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', function(){
            let raw = this.value.replace(/\D/g,'');
            this.value = raw ? parseInt(raw,10).toLocaleString('id-ID') : '';
        });
    });

    // Open add modal
    document.getElementById('btnAddGoal').addEventListener('click', () => {
        document.getElementById('goalEditId').value = '';
        document.getElementById('goalModalTitle').textContent = '🎯 Target Baru';
        document.getElementById('goalName').value = '';
        document.getElementById('goalTarget').value = '';
        document.getElementById('goalSaved').value = '';
        document.getElementById('goalDeadline').value = '';
        document.getElementById('addGoalOverlay').classList.add('active');
    });
    document.getElementById('addGoalClose').addEventListener('click', () => {
        document.getElementById('addGoalOverlay').classList.remove('active');
    });

    // Save goal
    document.getElementById('addGoalSave').addEventListener('click', async () => {
        const name   = document.getElementById('goalName').value.trim();
        const target = document.getElementById('goalTarget').value.replace(/\./g,'').replace(',','.');
        const saved  = document.getElementById('goalSaved').value.replace(/\./g,'').replace(',','.') || '0';
        if (!name || !target || parseFloat(target) <= 0) {
            alert('Nama dan nominal target wajib diisi.'); return;
        }
        const btn = document.getElementById('addGoalSave');
        btn.disabled = true; btn.textContent = 'Menyimpan...';
        try {
            const res = await fetch('/savings/store', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
                body: new URLSearchParams({
                    id:           document.getElementById('goalEditId').value,
                    name:         name,
                    icon:         document.getElementById('goalIcon').value,
                    color:        document.getElementById('goalColor').value,
                    target_amount: target,
                    saved_amount:  saved,
                    deadline:     document.getElementById('goalDeadline').value,
                })
            });
            const json = await res.json();
            if (json.success) {
                location.reload();
            } else {
                alert(json.message || 'Gagal menyimpan.');
            }
        } catch(e) { alert('Terjadi kesalahan.'); }
        btn.disabled = false; btn.textContent = 'Simpan';
    });

    // Top-up modal
    document.querySelectorAll('.goal-topup-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('topupGoalId').value  = btn.dataset.id;
            document.getElementById('topupGoalName').textContent = 'Target: ' + btn.dataset.name;
            document.getElementById('topupAmount').value = '';
            document.getElementById('topupOverlay').classList.add('active');
        });
    });
    document.getElementById('topupClose').addEventListener('click', () => {
        document.getElementById('topupOverlay').classList.remove('active');
    });

    document.getElementById('topupSave').addEventListener('click', async () => {
        const id     = document.getElementById('topupGoalId').value;
        const amount = document.getElementById('topupAmount').value.replace(/\./g,'').replace(',','.');
        if (!amount || parseFloat(amount) <= 0) { alert('Masukkan nominal setoran.'); return; }

        const btn = document.getElementById('topupSave');
        btn.disabled = true; btn.textContent = 'Menyimpan...';
        try {
            const res = await fetch('/savings/topup/' + id, {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
                body: new URLSearchParams({ amount })
            });
            const json = await res.json();
            if (json.success) {
                location.reload();
            } else {
                alert(json.message || 'Gagal.');
            }
        } catch(e) { alert('Terjadi kesalahan.'); }
        btn.disabled = false; btn.textContent = 'Setor';
    });

    // Delete goal
    document.querySelectorAll('.goal-del-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Hapus target tabungan ini?')) return;
            const id = btn.dataset.id;
            const res = await fetch('/savings/delete/' + id, {method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}});
            const json = await res.json();
            if (json.success) {
                btn.closest('.goal-card').remove();
                if (!document.querySelector('.goal-card')) location.reload();
            }
        });
    });
})();
</script>

<?= $this->endSection() ?>
