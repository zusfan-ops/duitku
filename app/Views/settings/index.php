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
    <div class="settings-section">
        <div class="settings-section-label">TRANSAKSI BERULANG</div>
        <div class="settings-list">
            <a href="/recurring" class="settings-item" style="text-decoration:none;cursor:pointer">
                <div class="settings-item-left">
                    <div class="settings-item-icon" style="background:#DBEAFE;color:#2563EB">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M21 12a9 9 0 0 1-9 9m9-9a9 9 0 0 0-9-9m9 9H3m9 9a9 9 0 0 1-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                    </div>
                    <div>
                        <div class="settings-item-label">Kelola Transaksi Berulang</div>
                        <div style="font-size:11px;color:var(--text-muted)"><?= count($recurring) ?> transaksi aktif</div>
                    </div>
                </div>
                <div class="settings-item-right">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="9 18 15 12 9 6"/></svg>
                </div>
            </a>
        </div>
    </div>

    <!-- Savings Goal Section -->
    <div class="settings-section">
        <div class="settings-section-label">TARGET MENABUNG</div>
        <div class="settings-list">
            <a href="/savings" class="settings-item" style="text-decoration:none;cursor:pointer">
                <div class="settings-item-left">
                    <div class="settings-item-icon" style="background:#DCFCE7;color:#16A34A">🎯</div>
                    <div>
                        <div class="settings-item-label">Kelola Target Menabung</div>
                        <div style="font-size:11px;color:var(--text-muted)">Multi-goal · Setor tabungan berkala</div>
                    </div>
                </div>
                <div class="settings-item-right">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="9 18 15 12 9 6"/></svg>
                </div>
            </a>
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
    <!-- Developer -->
    <div class="settings-section">
        <div class="settings-section-label">TENTANG DEVELOPER</div>
        <div class="dev-card" style="background:var(--bg-card);border:1px solid var(--border);border-radius:18px;padding:18px;box-shadow:var(--shadow-sm)">
            <div class="dev-card-top" style="display:flex;gap:14px;align-items:flex-start">
                <img src="https://zusfan.hallosemarang.com/DSC00218.jpg"
                     class="dev-photo" alt="Zusfan Mashuri"
                     style="width:68px;height:68px;border-radius:50%;object-fit:cover;border:2px solid var(--primary);flex-shrink:0"
                     onerror="this.style.display='none'">
                <div class="dev-info" style="flex:1">
                    <div class="dev-name" style="font-size:16px;font-weight:900;color:var(--text-primary)">Zusfan Mashuri</div>
                    <div style="font-size:11.5px;font-weight:700;color:var(--primary);margin:2px 0 6px">
                        Marketing Strategist · IT Builder · Public Service Innovator
                    </div>
                    <div class="dev-roles" style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:6px">
                        <span class="dev-role-pill" style="font-size:10px;font-weight:700;background:var(--primary-dim);color:var(--primary);padding:2px 8px;border-radius:10px">Founder & IT Director</span>
                        <span class="dev-role-pill" style="font-size:10px;font-weight:700;background:var(--bg);color:var(--text-secondary);padding:2px 8px;border-radius:10px;border:1px solid var(--border)">Hallo Semarang</span>
                    </div>
                </div>
            </div>

            <p style="font-size:12px;color:var(--text-secondary);line-height:1.45;margin:12px 0 14px">
                Pengembang sistem digital dengan pengalaman di marketing strategi, infrastruktur IT, smart city, dan pemberdayaan UMKM & komunitas melalui teknologi.
            </p>

            <!-- Achievements -->
            <div style="background:var(--bg);border-radius:12px;padding:12px;margin-bottom:14px;border:1px solid var(--border)">
                <div style="font-size:11.5px;font-weight:800;color:var(--text-primary);margin-bottom:8px">🎯 Pencapaian Highlight</div>
                <div style="font-size:11px;color:var(--text-secondary);display:flex;flex-direction:column;gap:6px">
                    <div>🚀 <strong>Hallo Semarang:</strong> 100,000+ pembaca bulanan & traffic naik 200%</div>
                    <div>🌐 <strong>Smart City:</strong> WiFi gratis di 50+ lokasi publik Semarang</div>
                    <div>📡 <strong>Media:</strong> TV streaming (GETTV) & Videotron Centralized</div>
                    <div>🤝 <strong>UMKM:</strong> Pemberdayaan digital UMKM & komunitas</div>
                </div>
            </div>

            <!-- Links & Contacts -->
            <div class="dev-links" style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px">
                <a href="https://wa.me/628998813000" class="dev-link" target="_blank" rel="noopener noreferrer" style="background:#25D366;color:#fff;text-decoration:none;padding:8px 10px;border-radius:10px;font-size:11.5px;font-weight:800;display:flex;align-items:center;justify-content:center;gap:6px">
                    💬 WhatsApp
                </a>
                <a href="https://zusfan.hallosemarang.com" class="dev-link" target="_blank" rel="noopener noreferrer" style="background:#1E293B;color:#fff;text-decoration:none;padding:8px 10px;border-radius:10px;font-size:11.5px;font-weight:800;display:flex;align-items:center;justify-content:center;gap:6px">
                    🌐 Digital Card
                </a>
                <a href="https://hallosemarang.com" class="dev-link" target="_blank" rel="noopener noreferrer" style="background:var(--bg);color:var(--text-primary);border:1px solid var(--border);text-decoration:none;padding:8px 10px;border-radius:10px;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;gap:4px">
                    📰 Hallo Semarang
                </a>
                <a href="https://zusfan.hallosemarang.com/resume.html" class="dev-link" target="_blank" rel="noopener noreferrer" style="background:var(--bg);color:var(--text-primary);border:1px solid var(--border);text-decoration:none;padding:8px 10px;border-radius:10px;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;gap:4px">
                    📄 Resume & CV
                </a>
            </div>

            <div style="text-align:center;font-size:10.5px;color:var(--text-muted);margin-top:14px">
                DuitKu · Made with ❤️ in Indonesia
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
