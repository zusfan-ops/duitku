<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="settings-page">
    <div class="page-header">
        <h1>Pengaturan</h1>
    </div>

    <!-- PROFILE SECTION -->
    <div class="settings-section">
        <div class="settings-section-label">PROFIL</div>
        <div class="settings-list">
            <div class="settings-item" id="profileItem" style="cursor:pointer">
                <div class="settings-item-left" style="gap:14px">
                    <?php
                        $avatarJson  = session()->get('user_avatar');
                        $avatarData  = $avatarJson ? json_decode($avatarJson, true) : ['initials' => 'U', 'color' => '#2D5A27'];
                        $avatarImgFile = $settings['avatar_image'] ?? null;
                        $avatarImg   = ($avatarImgFile && file_exists(FCPATH . 'uploads/avatars/' . $avatarImgFile))
                                       ? '/uploads/avatars/' . $avatarImgFile : null;
                    ?>
                    <div class="profile-avatar-big" id="profileAvatarBtn" style="width:48px;height:48px;border-radius:50%;overflow:hidden;cursor:pointer;flex-shrink:0;border:2px solid var(--border);position:relative;">
                        <?php if ($avatarImg): ?>
                            <img src="<?= esc($avatarImg) ?>?v=<?= time() ?>" alt="Avatar" id="profileAvatarImg" style="width:100%;height:100%;object-fit:cover">
                        <?php else: ?>
                            <span id="profileAvatarInitials" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:#fff;background:<?= esc($avatarData['color'] ?? '#2D5A27') ?>"><?= esc($avatarData['initials'] ?? 'U') ?></span>
                        <?php endif; ?>
                        <div class="profile-avatar-overlay">📷</div>
                    </div>
                    <div>
                        <div class="settings-item-label" id="profileNameDisplay"><?= esc($user['name'] ?? session()->get('user_name')) ?></div>
                        <div style="font-size:12px;color:var(--text-muted)" id="profileEmailDisplay"><?= esc($user['email'] ?? session()->get('user_email')) ?></div>
                    </div>
                </div>
                <div class="settings-item-right">
                    <span style="font-size:12px">Edit</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </div>
            </div>
        </div>
        <!-- Hidden file input for avatar upload -->
        <input type="file" id="avatarFileInput" accept="image/*" style="display:none">
    </div>

    <!-- Edit Profile Modal -->
    <div class="mini-modal-overlay" id="profileModalOverlay">
        <div class="mini-modal">
            <h3>Edit Profil</h3>
            <div class="form-group">
                <label class="form-label" for="editName">NAMA</label>
                <input type="text" id="editName" class="form-input" value="<?= esc($user['name'] ?? '') ?>" placeholder="Nama kamu">
            </div>
            <div class="form-group">
                <label class="form-label" for="editEmail">EMAIL</label>
                <input type="email" id="editEmail" class="form-input" value="<?= esc($user['email'] ?? '') ?>" placeholder="email@kamu.com">
            </div>
            <div class="form-group">
                <label class="form-label" for="editPassword">PASSWORD BARU (KOSONGKAN JIKA TIDAK DIUBAH)</label>
                <input type="password" id="editPassword" class="form-input" placeholder="Min. 6 karakter">
            </div>
            <div class="mini-modal-footer">
                <button class="btn-cancel-small" id="profileModalClose">Batal</button>
                <button class="btn-save-small" id="profileSave">Simpan</button>
            </div>
        </div>
    </div>

    <!-- PREFERENCES SECTION -->
    <div class="settings-section">
        <div class="settings-section-label">PREFERENSI</div>
        <div class="settings-list">
            <!-- Currency -->
            <div class="settings-item" id="currencyItem">
                <div class="settings-item-left">
                    <div class="settings-item-icon" style="background:#DCFCE7;color:#16A34A">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 1 0 0 7h5a3.5 3.5 0 1 1 0 7H6"/>
                        </svg>
                    </div>
                    <span class="settings-item-label">Mata Uang</span>
                </div>
                <div class="settings-item-right">
                    <span id="currencyDisplay"><?= esc($currency) ?></span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </div>
            </div>
            <!-- Dark Mode -->
            <div class="settings-item" style="cursor:default">
                <div class="settings-item-left">
                    <div class="settings-item-icon" style="background:#1E293B;color:#94A3B8">
                        🌙
                    </div>
                    <span class="settings-item-label">Mode Gelap</span>
                </div>
                <div class="settings-item-right">
                    <div class="toggle-switch" id="darkModeToggle"></div>
                </div>
            </div>
            <!-- Budget -->
            <div class="settings-item" id="budgetItem">
                <div class="settings-item-left">
                    <div class="settings-item-icon" style="background:#FEF3C7;color:#D97706">🎯</div>
                    <span class="settings-item-label">Budget Bulan Ini</span>
                </div>
                <div class="settings-item-right">
                    <span id="budgetDisplay" style="font-size:12px;color:var(--text-muted)">
                        <?= $budget > 0 ? esc($symbol) . ' ' . number_format($budget, 0, ',', '.') : 'Belum diatur' ?>
                    </span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Currency Modal -->
    <div class="mini-modal-overlay" id="currencyModalOverlay">
        <div class="mini-modal">
            <h3>Pilih Mata Uang</h3>
            <div class="currency-options">
                <?php
                $currencies = [
                    'IDR' => ['symbol' => 'Rp',  'label' => 'Rupiah Indonesia'],
                    'USD' => ['symbol' => '$',   'label' => 'Dollar Amerika'],
                    'SGD' => ['symbol' => 'S$',  'label' => 'Dollar Singapura'],
                    'MYR' => ['symbol' => 'RM',  'label' => 'Ringgit Malaysia'],
                ];
                foreach ($currencies as $code => $info):
                ?>
                <button class="currency-opt <?= $currency === $code ? 'active' : '' ?>"
                        data-currency="<?= $code ?>"
                        data-symbol="<?= $info['symbol'] ?>">
                    <strong><?= $code ?></strong> — <?= $info['label'] ?>
                    <span class="currency-sym"><?= $info['symbol'] ?></span>
                </button>
                <?php endforeach; ?>
            </div>
            <button class="btn-cancel-small" id="currencyModalClose">Batal</button>
        </div>
    </div>

    <!-- Budget Modal -->
    <div class="mini-modal-overlay" id="budgetModalOverlay">
        <div class="mini-modal">
            <h3>🎯 Set Budget Bulan Ini</h3>
            <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px">Atur batas pengeluaran untuk <strong><?= date('F Y') ?></strong></p>
            <div class="form-group">
                <label class="form-label">JUMLAH BUDGET</label>
                <div class="amount-input-wrap" style="margin-bottom:0">
                    <span class="amount-currency" id="budgetCurrencySymbol"><?= esc($symbol) ?></span>
                    <input type="text" id="budgetAmountInput" class="amount-input"
                           style="font-size:22px"
                           placeholder="0"
                           value="<?= $budget > 0 ? number_format($budget, 0, ',', '.') : '' ?>"
                           inputmode="numeric">
                </div>
            </div>
            <div class="mini-modal-footer">
                <button class="btn-cancel-small" id="budgetModalClose">Batal</button>
                <button class="btn-save-small" id="budgetSave">Simpan</button>
            </div>
        </div>
    </div>

    <!-- Categories Section -->
    <div class="settings-section">
        <div class="settings-section-label">KATEGORI PENGELUARAN</div>
        <div class="settings-list" id="expenseCatList">
            <?php foreach (array_filter($categories, fn($c) => $c['type'] === 'expense') as $cat): ?>
            <div class="settings-item cat-item" data-id="<?= $cat['id'] ?>">
                <div class="settings-item-left">
                    <div class="settings-item-icon" style="background:<?= esc($cat['color']) ?>20;color:<?= esc($cat['color']) ?>">
                        <?= categoryIcon($cat['icon']) ?>
                    </div>
                    <span class="settings-item-label"><?= esc($cat['name']) ?></span>
                </div>
                <?php if (!$cat['is_default']): ?>
                <button class="cat-delete-btn" data-id="<?= $cat['id'] ?>" title="Hapus">✕</button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <button class="settings-item add-cat-btn" id="addExpenseCat" data-type="expense">
                <div class="settings-item-left">
                    <div class="settings-item-icon" style="background:var(--bg);color:var(--text-muted)">＋</div>
                    <span class="settings-item-label" style="color:var(--text-muted)">Tambah kategori</span>
                </div>
            </button>
        </div>
    </div>

    <div class="settings-section">
        <div class="settings-section-label">KATEGORI PEMASUKAN</div>
        <div class="settings-list" id="incomeCatList">
            <?php foreach (array_filter($categories, fn($c) => $c['type'] === 'income') as $cat): ?>
            <div class="settings-item cat-item" data-id="<?= $cat['id'] ?>">
                <div class="settings-item-left">
                    <div class="settings-item-icon" style="background:<?= esc($cat['color']) ?>20;color:<?= esc($cat['color']) ?>">
                        <?= categoryIcon($cat['icon']) ?>
                    </div>
                    <span class="settings-item-label"><?= esc($cat['name']) ?></span>
                </div>
                <?php if (!$cat['is_default']): ?>
                <button class="cat-delete-btn" data-id="<?= $cat['id'] ?>" title="Hapus">✕</button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <button class="settings-item add-cat-btn" id="addIncomeCat" data-type="income">
                <div class="settings-item-left">
                    <div class="settings-item-icon" style="background:var(--bg);color:var(--text-muted)">＋</div>
                    <span class="settings-item-label" style="color:var(--text-muted)">Tambah kategori</span>
                </div>
            </button>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="mini-modal-overlay" id="addCatModalOverlay">
        <div class="mini-modal">
            <h3>Tambah Kategori</h3>
            <input type="hidden" id="newCatType" value="expense">
            <div class="form-group">
                <label class="form-label" for="newCatName">NAMA</label>
                <input type="text" id="newCatName" class="form-input" placeholder="cth. Langganan">
            </div>
            <div class="form-group">
                <label class="form-label">WARNA</label>
                <div class="color-picker-row" id="colorPicker">
                    <?php foreach (['#EF4444','#F97316','#EAB308','#22C55E','#14B8A6','#3B82F6','#8B5CF6','#EC4899','#6B7280','#059669'] as $c): ?>
                    <button class="color-dot" data-color="<?= $c ?>" style="background:<?= $c ?>" type="button"></button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="newCatColor" value="#6B7280">
            </div>
            <div class="mini-modal-footer">
                <button class="btn-cancel-small" id="addCatClose">Batal</button>
                <button class="btn-save-small" id="addCatSave">Simpan</button>
            </div>
        </div>
    </div>

    <!-- RECURRING TRANSACTIONS -->
    <?php if (!empty($recurring)): ?>
    <div class="settings-section">
        <div class="settings-section-label">TRANSAKSI BERULANG</div>
        <div class="settings-list" id="recurringList">
            <?php foreach ($recurring as $r): ?>
            <div class="settings-item" data-recurring-id="<?= $r['id'] ?>">
                <div class="settings-item-left">
                    <div class="settings-item-icon"
                         style="background:<?= esc($r['category_color'] ?? '#6B7280') ?>20;color:<?= esc($r['category_color'] ?? '#6B7280') ?>">
                        <?= categoryIcon($r['category_icon'] ?? 'other') ?>
                    </div>
                    <div>
                        <div class="settings-item-label"><?= esc($r['category_name'] ?? 'Tanpa Kategori') ?></div>
                        <div style="font-size:11px;color:var(--text-muted)">
                            <?= $r['type'] === 'income' ? '+' : '-' ?> <?= esc($symbol) ?> <?= number_format($r['amount'], 0, ',', '.') ?>
                            · Berikutnya <?= date('d M Y', strtotime($r['next_date'])) ?>
                        </div>
                    </div>
                </div>
                <button class="cat-delete-btn recurring-delete-btn" data-id="<?= $r['id'] ?>" title="Hapus">✕</button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Savings Goal Section -->
    <div class="settings-section">
        <div class="settings-section-label">TARGET MENABUNG</div>
        <?php
            $savingsName   = $settings['savings_name']   ?? '';
            $savingsTarget = (float)($settings['savings_target'] ?? 0);
            $savingsSaved  = (float)($settings['savings_saved']  ?? 0);
            $savingsPct    = ($savingsTarget > 0) ? min(($savingsSaved / $savingsTarget) * 100, 100) : 0;
        ?>
        <div class="settings-list" id="savingsSettingBox">
            <?php if ($savingsTarget > 0): ?>
            <div class="savings-card" id="savingsSettingCard" style="margin-bottom:0;border-radius:0;border:none;border-bottom:1px solid var(--border)">
                <div class="savings-header">
                    <span class="savings-icon">🎯</span>
                    <div class="savings-info">
                        <div class="savings-title"><?= esc($savingsName ?: 'Target Menabung') ?></div>
                        <div class="savings-amounts">
                            <strong><?= esc($symbol) ?> <?= number_format($savingsSaved, 0, ',', '.') ?></strong>
                            / <?= esc($symbol) ?> <?= number_format($savingsTarget, 0, ',', '.') ?>
                        </div>
                    </div>
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
            <div class="settings-item" id="btnOpenSavingsSetting" style="cursor:pointer">
                <div class="settings-item-left">
                    <div class="settings-item-icon" style="background:var(--primary-dim);color:var(--primary)">🎯</div>
                    <span class="settings-item-label"><?= $savingsTarget > 0 ? 'Edit Target Menabung' : 'Set Target Menabung' ?></span>
                </div>
                <div class="settings-item-right">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Savings Modal (settings page copy) -->
    <div class="mini-modal-overlay" id="savingsSettingOverlay">
        <div class="mini-modal">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
                <h3 style="font-size:16px;font-weight:700">🎯 Target Menabung</h3>
                <button class="modal-close" id="savingsSettingClose">✕</button>
            </div>
            <div class="form-group">
                <label class="form-label">NAMA TARGET</label>
                <input type="text" id="settingSavingsName" class="form-input" placeholder="Beli motor, Liburan…" value="<?= esc($savingsName) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">TARGET NOMINAL (<?= esc($symbol) ?>)</label>
                <input type="text" id="settingSavingsTarget" class="form-input" placeholder="0" inputmode="numeric" value="<?= $savingsTarget > 0 ? number_format($savingsTarget, 0, ',', '.') : '' ?>">
            </div>
            <div class="form-group">
                <label class="form-label">SUDAH TERSIMPAN (<?= esc($symbol) ?>)</label>
                <input type="text" id="settingSavingsSaved" class="form-input" placeholder="0" inputmode="numeric" value="<?= $savingsSaved > 0 ? number_format($savingsSaved, 0, ',', '.') : '' ?>">
            </div>
            <div style="display:flex;gap:8px;margin-top:4px">
                <button type="button" id="savingsSettingCancel" class="btn-cancel-small" style="flex:1">Batal</button>
                <button type="button" id="savingsSettingSave" class="btn-save-small" style="flex:2">Simpan</button>
            </div>
        </div>
    </div>

    <!-- App Info -->
    <div class="settings-section">
        <div class="settings-section-label">TENTANG APLIKASI</div>
        <div class="settings-list">
            <div class="settings-item" style="cursor:default">
                <div class="settings-item-left">
                    <img src="/images/logo.png" alt="DuitKu" width="48" height="26" style="object-fit:contain">
                    <div>
                        <div class="settings-item-label">DuitKu</div>
                        <div style="font-size:12px;color:var(--text-muted)">v2.0.0 · CodeIgniter 4 · PWA</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Developer -->
    <div class="settings-section">
        <div class="settings-section-label">DEVELOPER</div>
        <div class="dev-card">
            <div class="dev-card-top">
                <img src="https://zusfan.hallosemarang.com/DSC00218.jpg"
                     class="dev-photo" alt="Zusfan Mashuri"
                     onerror="this.style.display='none'">
                <div class="dev-info">
                    <div class="dev-name">Zusfan Mashuri</div>
                    <div class="dev-roles">
                        <span class="dev-role-pill">Marketing Strategist</span>
                        <span class="dev-role-pill">IT Builder</span>
                        <span class="dev-role-pill">Public Service Innovator</span>
                    </div>
                    <div class="dev-tagline">Made with ❤️ in Indonesia</div>
                </div>
            </div>
            <div class="dev-links">
                <a href="https://wa.me/628998813000" class="dev-link" target="_blank" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.27h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.87a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    WhatsApp
                </a>
                <a href="https://zusfan.hallosemarang.com" class="dev-link" target="_blank" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="2" y1="12" x2="22" y2="12"/>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    </svg>
                    Portfolio
                </a>
                <a href="https://www.hallosemarang.com" class="dev-link" target="_blank" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Hallo Semarang
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function() {
    const $ = id => document.getElementById(id);

    function csrfHeaders() {
        return { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' };
    }
    function csrfBody(params = {}) {
        const p = new URLSearchParams();
        p.set(window.DUITKU.csrfName, window.DUITKU.csrfToken);
        Object.entries(params).forEach(([k,v]) => { if (v !== null && v !== undefined) p.set(k,v); });
        return p.toString();
    }

    // ── Profile ───────────────────────────────────────────────────────────────
    const profileItem    = $('profileItem');
    const profileOverlay = $('profileModalOverlay');
    const profileClose   = $('profileModalClose');
    const profileSave    = $('profileSave');
    const avatarFileInput = $('avatarFileInput');
    const profileAvatarBtn = $('profileAvatarBtn');

    if (profileItem) {
        profileItem.addEventListener('click', () => profileOverlay.classList.add('open'));
    }
    if (profileClose)   profileClose.addEventListener('click', () => profileOverlay.classList.remove('open'));
    if (profileOverlay) profileOverlay.addEventListener('click', e => { if (e.target === profileOverlay) profileOverlay.classList.remove('open'); });

    if (profileAvatarBtn && avatarFileInput) {
        profileAvatarBtn.addEventListener('click', e => { e.stopPropagation(); avatarFileInput.click(); });
        avatarFileInput.addEventListener('change', async function() {
            if (!this.files[0]) return;
            const fd = new FormData();
            fd.append(window.DUITKU.csrfName, window.DUITKU.csrfToken);
            fd.append('avatar', this.files[0]);
            try {
                const res  = await fetch('/settings/avatar', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
                const data = await res.json();
                if (data.success) {
                    // Update topbar avatar & profile avatar
                    const topbarAvatar = document.querySelector('#userMenuToggle img, #userMenuToggle span');
                    if (topbarAvatar) {
                        const img = document.createElement('img');
                        img.src = data.image + '?v=' + Date.now();
                        img.style.cssText = 'width:100%;height:100%;object-fit:cover;border-radius:50%';
                        topbarAvatar.replaceWith(img);
                    }
                    const profileInner = profileAvatarBtn.querySelector('img, span');
                    if (profileInner) {
                        const img2 = document.createElement('img');
                        img2.src = data.image + '?v=' + Date.now();
                        img2.id = 'profileAvatarImg';
                        img2.style.cssText = 'width:100%;height:100%;object-fit:cover';
                        profileInner.replaceWith(img2);
                    }
                    window.DUITKU && window.showToast && showToast('Foto profil diperbarui!');
                } else {
                    alert(data.message || 'Gagal mengunggah foto.');
                }
            } catch(err) {
                alert('Terjadi kesalahan.');
            }
        });
    }

    if (profileSave) {
        profileSave.addEventListener('click', async () => {
            const name     = $('editName').value.trim();
            const email    = $('editEmail').value.trim();
            const password = $('editPassword').value;
            const res  = await fetch('/settings/profile', { method: 'POST', headers: csrfHeaders(), body: csrfBody({ name, email, password }) });
            const data = await res.json();
            if (data.success) {
                profileOverlay.classList.remove('open');
                $('profileNameDisplay').textContent  = data.name;
                $('profileEmailDisplay').textContent = data.email;
                // Update topbar
                const topbarName  = document.querySelector('.user-menu-info strong');
                const topbarEmail = document.querySelector('.user-menu-info small');
                if (topbarName)  topbarName.textContent  = data.name;
                if (topbarEmail) topbarEmail.textContent = data.email;
                showToast('Profil diperbarui!');
            } else {
                showToast(data.message || 'Gagal.', 'error');
            }
        });
    }

    // ── Budget ────────────────────────────────────────────────────────────────
    const budgetItem    = $('budgetItem');
    const budgetOverlay = $('budgetModalOverlay');
    const budgetClose   = $('budgetModalClose');
    const budgetSave    = $('budgetSave');
    const budgetInput   = $('budgetAmountInput');

    if (budgetItem)    budgetItem.addEventListener('click', () => budgetOverlay.classList.add('open'));
    if (budgetClose)   budgetClose.addEventListener('click', () => budgetOverlay.classList.remove('open'));
    if (budgetOverlay) budgetOverlay.addEventListener('click', e => { if (e.target === budgetOverlay) budgetOverlay.classList.remove('open'); });

    if (budgetInput) {
        budgetInput.addEventListener('input', function() {
            const raw = this.value.replace(/\D/g, '');
            this.value = raw ? Number(raw).toLocaleString('id-ID') : '';
        });
    }

    if (budgetSave) {
        budgetSave.addEventListener('click', async () => {
            const rawVal = ($('budgetAmountInput').value || '0').replace(/\./g, '').replace(',', '.');
            const amount = parseFloat(rawVal) || 0;
            const month  = '<?= $monthKey ?>';
            const res    = await fetch('/settings/budget', { method: 'POST', headers: csrfHeaders(), body: csrfBody({ amount, month }) });
            const data   = await res.json();
            if (data.success) {
                budgetOverlay.classList.remove('open');
                const sym = window.DUITKU.symbol;
                $('budgetDisplay').textContent = amount > 0
                    ? sym + ' ' + Number(amount).toLocaleString('id-ID')
                    : 'Belum diatur';
                showToast('Budget disimpan!');
            } else {
                showToast(data.message || 'Gagal.', 'error');
            }
        });
    }

    // ── Savings Goal (settings page) ────────────────────────────────────────
    const savingsSettingOverlay = $('savingsSettingOverlay');
    const savingsSettingClose   = $('savingsSettingClose');
    const savingsSettingCancel  = $('savingsSettingCancel');
    const savingsSettingSave    = $('savingsSettingSave');
    const btnOpenSavingsSetting = $('btnOpenSavingsSetting');

    if (btnOpenSavingsSetting) {
        btnOpenSavingsSetting.addEventListener('click', () => {
            savingsSettingOverlay.classList.add('open');
        });
    }
    if (savingsSettingClose)  savingsSettingClose.addEventListener('click',  () => savingsSettingOverlay.classList.remove('open'));
    if (savingsSettingCancel) savingsSettingCancel.addEventListener('click', () => savingsSettingOverlay.classList.remove('open'));
    if (savingsSettingOverlay) savingsSettingOverlay.addEventListener('click', e => { if (e.target === savingsSettingOverlay) savingsSettingOverlay.classList.remove('open'); });

    ['settingSavingsTarget','settingSavingsSaved'].forEach(id => {
        const el = $(id);
        if (!el) return;
        el.addEventListener('input', function() {
            const raw = this.value.replace(/\D/g, '');
            this.value = raw ? Number(raw).toLocaleString('id-ID') : '';
        });
    });

    if (savingsSettingSave) {
        savingsSettingSave.addEventListener('click', async () => {
            const name   = ($('settingSavingsName').value || '').trim();
            const target = parseFloat(($('settingSavingsTarget').value || '0').replace(/\./g, '').replace(',', '.')) || 0;
            const saved  = parseFloat(($('settingSavingsSaved').value  || '0').replace(/\./g, '').replace(',', '.')) || 0;
            if (!name || target <= 0) { showToast('Nama dan target wajib diisi.', 'error'); return; }

            savingsSettingSave.disabled = true;
            const fd = new FormData();
            fd.append(window.DUITKU.csrfName, window.DUITKU.csrfToken);
            fd.append('savings_name', name);
            fd.append('savings_target', target);
            fd.append('savings_saved', saved);
            const res  = await fetch('/settings/savings', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
            const data = await res.json();
            savingsSettingSave.disabled = false;
            if (data.success) {
                savingsSettingOverlay.classList.remove('open');
                showToast('Target disimpan!');
                setTimeout(() => location.reload(), 600);
            } else {
                showToast(data.message || 'Gagal.', 'error');
            }
        });
    }

    // ── Recurring delete ──────────────────────────────────────────────────────
    document.querySelectorAll('.recurring-delete-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            if (!confirm('Hapus transaksi berulang ini?')) return;
            const res  = await fetch('/recurring/delete/' + id, { method: 'POST', headers: csrfHeaders(), body: csrfBody() });
            const data = await res.json();
            if (data.success) {
                this.closest('[data-recurring-id]').remove();
                showToast('Transaksi berulang dihapus.');
            } else {
                showToast(data.message || 'Gagal.', 'error');
            }
        });
    });
})();
</script>
<?= $this->endSection() ?>
