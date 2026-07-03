/* ═══════════════════════════════════════════════════════════════════════════
   DuitKu — Main App JavaScript
   ═══════════════════════════════════════════════════════════════════════════ */
'use strict';

(function () {

    // ── State ────────────────────────────────────────────────────────────────
    const state = {
        selectedCategoryId: null,
        selectedType: 'expense',
        editingTxId: null,
        pendingDeleteId: null,
        isRecurring: false,
    };

    // ── Cached Elements ──────────────────────────────────────────────────────
    const $ = id => document.getElementById(id);

    // ── Body scroll lock ────────────────────────────────────────────────────
    // overflow:hidden alone doesn't stop touch-drag scrolling on iOS Safari,
    // which lets the background "slide" behind an open modal. Pin the body
    // with position:fixed instead and restore the scroll position on unlock.
    let _scrollLockY = 0;
    window.DuitkuLockScroll = function () {
        _scrollLockY = window.scrollY || window.pageYOffset || 0;
        document.body.style.position = 'fixed';
        document.body.style.top      = `-${_scrollLockY}px`;
        document.body.style.left     = '0';
        document.body.style.right    = '0';
        document.body.style.width    = '100%';
        document.body.style.overflow = 'hidden';
    };
    window.DuitkuUnlockScroll = function () {
        document.body.style.position = '';
        document.body.style.top      = '';
        document.body.style.left     = '';
        document.body.style.right    = '';
        document.body.style.width    = '';
        document.body.style.overflow = '';
        window.scrollTo(0, _scrollLockY);
    };

    // ── CSRF Helper ──────────────────────────────────────────────────────────
    function csrfHeaders() {
        return {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
        };
    }
    function csrfBody(params = {}) {
        const p = new URLSearchParams();
        p.set(window.DUITKU.csrfName, window.DUITKU.csrfToken);
        Object.entries(params).forEach(([k, v]) => { if (v !== null && v !== undefined) p.set(k, v); });
        return p.toString();
    }

    // ── Toast ────────────────────────────────────────────────────────────────
    function showToast(msg, type = 'success') {
        const old = document.querySelector('.toast.dynamic');
        if (old) old.remove();
        const t = document.createElement('div');
        t.className = `toast toast-${type} dynamic`;
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 3200);
    }
    window.showToast = showToast;

    // ── Format Number ────────────────────────────────────────────────────────
    function formatNum(n) {
        return Number(n).toLocaleString('id-ID');
    }
    function parseNum(str) {
        return parseFloat(str.replace(/\./g, '').replace(',', '.')) || 0;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  DARK MODE
    // ════════════════════════════════════════════════════════════════════════
    const darkToggle = $('darkModeToggle');
    const html       = document.documentElement;

    function applyDark(on) {
        html.setAttribute('data-theme', on ? 'dark' : '');
        if (darkToggle) darkToggle.classList.toggle('on', on);
        localStorage.setItem('duitku_dark', on ? '1' : '0');
    }

    // Initial state from localStorage (also set by inline script in <head>)
    const isDark = localStorage.getItem('duitku_dark') === '1';
    if (darkToggle) darkToggle.classList.toggle('on', isDark);

    if (darkToggle) {
        darkToggle.addEventListener('click', () => {
            const nowDark = html.getAttribute('data-theme') === 'dark';
            applyDark(!nowDark);
        });
    }

    // ════════════════════════════════════════════════════════════════════════
    //  USER MENU TOGGLE
    // ════════════════════════════════════════════════════════════════════════
    const userMenuToggle = $('userMenuToggle');
    const userMenu       = $('userMenu');
    if (userMenuToggle && userMenu) {
        userMenuToggle.addEventListener('click', e => {
            e.stopPropagation();
            userMenu.classList.toggle('open');
        });
        document.addEventListener('click', () => userMenu.classList.remove('open'));
    }

    // ════════════════════════════════════════════════════════════════════════
    //  TRANSACTION MODAL
    // ════════════════════════════════════════════════════════════════════════
    const overlay    = $('txModalOverlay');
    const fabBtn     = $('fabBtn');
    const modalClose = $('modalClose');
    const txForm     = $('txForm');

    function populateWalletSelect(selectedId) {
        const sel = $('txWallet');
        if (!sel) return;
        const wallets = window.DUITKU.wallets || [];
        if (!wallets.length) {
            const row = $('walletPickerRow');
            if (row) row.style.display = 'none';
            return;
        }
        const defaultW = wallets.find(w => w.is_default) || wallets[0];
        sel.innerHTML = wallets.map(w => {
            const bal = Number(w.balance || 0).toLocaleString('id-ID');
            const sel = String(w.id) === String(selectedId || defaultW?.id) ? ' selected' : '';
            return `<option value="${w.id}"${sel}>${w.icon} ${w.name} — ${window.DUITKU.symbol} ${bal}</option>`;
        }).join('');
    }

    function openModal(editData = null) {
        if (!overlay) return;
        state.editingTxId        = null;
        state.selectedCategoryId = null;
        state.selectedType       = 'expense';
        state.isRecurring        = false;

        // Reset form
        if (txForm) txForm.reset();
        $('txId').value    = '';
        $('txDate').value  = new Date().toISOString().slice(0, 10);
        $('txType').value  = 'expense';
        $('txAmount').value = '';
        $('txNote').value  = '';
        if ($('txImage')) $('txImage').value = '';
        if ($('txImagePreviewContainer')) {
            $('txImagePreviewContainer').style.display = 'none';
            $('txImagePreview').src = '';
        }
        // Reset recurring toggle
        const recToggle = $('recurringToggle');
        const recInput  = $('txRecurring');
        const recWrap   = $('recurringWrap');
        if (recToggle) recToggle.classList.remove('on');
        if (recInput)  recInput.value = '0';
        if (recWrap)   recWrap.style.display = 'flex';

        $('modalTitle').textContent = 'Transaksi Baru';
        $('btnSave').textContent    = 'Simpan Pengeluaran';

        // Reset type buttons
        $('btnExpense').classList.add('active');
        $('btnIncome').classList.remove('active', 'income-active');
        $('btnExpense').classList.remove('income-active');

        $('amountCurrency').textContent = window.DUITKU.symbol;

        if (editData) {
            state.editingTxId = editData.id;
            state.selectedType = editData.type;
            $('txId').value    = editData.id;
            $('txAmount').value = formatNum(editData.amount);
            $('txNote').value   = editData.note || '';
            $('txDate').value   = editData.date;
            $('txType').value   = editData.type;
            $('modalTitle').textContent = 'Edit Transaksi';
            $('btnSave').textContent    = 'Simpan Perubahan';

            if (editData.type === 'income') {
                $('btnIncome').classList.add('active', 'income-active');
                $('btnExpense').classList.remove('active');
            }
            state.selectedCategoryId = editData.category_id;

            if (editData.image && $('txImagePreviewContainer')) {
                $('txImagePreviewContainer').style.display = 'block';
                $('txImagePreview').src = '/uploads/transactions/' + editData.image;
            }
            // Hide recurring toggle when editing (recurring is set at creation only)
            if (recWrap) recWrap.style.display = 'none';
        }

        populateWalletSelect(editData ? editData.wallet_id : null);
        renderCategoryChips();
        overlay.classList.add('open');
        window.DuitkuLockScroll();
        setTimeout(() => $('txAmount').focus(), 350);
    }

    function closeModal() {
        if (!overlay) return;
        overlay.classList.remove('open');
        window.DuitkuUnlockScroll();
    }

    if (fabBtn)     fabBtn.addEventListener('click', () => openModal());
    if (modalClose) modalClose.addEventListener('click', closeModal);
    if (overlay)    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

    // Type toggle
    const btnExpense = $('btnExpense');
    const btnIncome  = $('btnIncome');
    if (btnExpense && btnIncome) {
        btnExpense.addEventListener('click', () => {
            state.selectedType = 'expense';
            $('txType').value  = 'expense';
            btnExpense.classList.add('active');
            btnExpense.classList.remove('income-active');
            btnIncome.classList.remove('active', 'income-active');
            $('btnSave').textContent = state.editingTxId ? 'Simpan Perubahan' : 'Simpan Pengeluaran';
            renderCategoryChips();
        });
        btnIncome.addEventListener('click', () => {
            state.selectedType = 'income';
            $('txType').value  = 'income';
            btnIncome.classList.add('active', 'income-active');
            btnExpense.classList.remove('active');
            $('btnSave').textContent = state.editingTxId ? 'Simpan Perubahan' : 'Simpan Pemasukan';
            renderCategoryChips();
        });
    }

    // Amount formatting
    const txAmount = $('txAmount');
    if (txAmount) {
        txAmount.addEventListener('input', function () {
            const raw = this.value.replace(/\D/g, '');
            if (raw) {
                this.value = formatNum(parseInt(raw, 10));
            }
        });
    }

    // Recurring toggle
    const recurringToggle = $('recurringToggle');
    const txRecurring     = $('txRecurring');
    if (recurringToggle && txRecurring) {
        recurringToggle.addEventListener('click', () => {
            state.isRecurring = !state.isRecurring;
            recurringToggle.classList.toggle('on', state.isRecurring);
            txRecurring.value = state.isRecurring ? '1' : '0';
        });
    }

    // Category chips renderer
    function renderCategoryChips() {
        const container = $('categoryChips');
        if (!container) return;
        const cats = (window.DUITKU.categories || []).filter(c => c.type === state.selectedType);
        container.innerHTML = cats.map(c => `
            <button type="button"
                class="cat-chip ${String(state.selectedCategoryId) === String(c.id) ? 'selected' : ''}"
                data-id="${c.id}"
                data-color="${c.color}"
                style="--cat-color:${c.color}20;--cat-dark:${c.color}">
                <span style="color:${c.color}">${getCatIconHtml(c.icon)}</span>
                ${escHtml(c.name)}
            </button>
        `).join('');

        container.querySelectorAll('.cat-chip').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                if (String(state.selectedCategoryId) === String(id)) {
                    state.selectedCategoryId = null;
                    $('txCategory').value = '';
                } else {
                    state.selectedCategoryId = id;
                    $('txCategory').value = id;
                }
                renderCategoryChips();
            });
        });
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function getCatIconHtml(icon) {
        const icons = {
            food:      '🍽️', transport: '🚗', utilities: '⚡', shopping: '🛍️',
            fun:       '🎵', health:    '❤️', home:      '🏠', salary:   '💳',
            freelance: '💻', gift:      '🎁', other:     '•',  circle:   '●',
        };
        return icons[icon] || '•';
    }

    // Image Preview
    const txImage = $('txImage');
    const txImagePreviewContainer = $('txImagePreviewContainer');
    const txImagePreview = $('txImagePreview');
    const btnRemoveImage = $('btnRemoveImage');

    if (txImage && txImagePreviewContainer) {
        txImage.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    txImagePreview.src = e.target.result;
                    txImagePreviewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                txImagePreviewContainer.style.display = 'none';
                txImagePreview.src = '';
            }
        });

        if (btnRemoveImage) {
            btnRemoveImage.addEventListener('click', function() {
                txImage.value = '';
                txImagePreviewContainer.style.display = 'none';
                txImagePreview.src = '';
            });
        }
    }

    // Submit form
    if (txForm) {
        txForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const amount = parseNum($('txAmount').value || '0');
            if (amount <= 0) { showToast('Masukkan nominal yang valid.', 'error'); return; }

            const formData = new FormData();
            formData.append(window.DUITKU.csrfName, window.DUITKU.csrfToken);
            formData.append('type', $('txType').value);
            formData.append('amount', amount);
            formData.append('category_id', $('txCategory').value || '');
            formData.append('note', $('txNote').value);
            formData.append('date', $('txDate').value);
            formData.append('is_recurring', txRecurring ? txRecurring.value : '0');
            const txWallet = $('txWallet');
            if (txWallet && txWallet.value) formData.append('wallet_id', txWallet.value);

            if (txImage && txImage.files[0]) {
                formData.append('image', txImage.files[0]);
            }

            const url = state.editingTxId
                ? `/transaction/update/${state.editingTxId}`
                : '/transaction/store';

            try {
                const res  = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData,
                });
                const data = await res.json();
                if (data.success) {
                    closeModal();
                    const msg = state.isRecurring
                        ? 'Transaksi berulang disimpan! 🔁'
                        : (state.editingTxId ? 'Transaksi diperbarui!' : 'Transaksi disimpan!');
                    showToast(msg);
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(data.message || 'Gagal menyimpan.', 'error');
                }
            } catch {
                showToast('Terjadi kesalahan jaringan.', 'error');
            }
        });
    }

    // ════════════════════════════════════════════════════════════════════════
    //  TRANSACTION LIST — Edit & Delete
    // ════════════════════════════════════════════════════════════════════════
    document.querySelectorAll('.tx-item').forEach(item => {
        item.addEventListener('click', function (e) {
            if (e.target.closest('.tx-edit-btn') || e.target.closest('.tx-delete-btn')) return;
            const tx = JSON.parse(this.dataset.tx || '{}');
            if (tx.id) openModal(tx);
        });
    });

    document.querySelectorAll('.tx-edit-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const item = this.closest('.tx-item');
            const tx   = JSON.parse(item.dataset.tx || '{}');
            if (tx.id) openModal(tx);
        });
    });

    // Delete button
    const confirmOverlay = $('confirmOverlay');
    const confirmDelete  = $('confirmDelete');
    const confirmCancel  = $('confirmCancel');

    document.querySelectorAll('.tx-delete-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const item = this.closest('.tx-item');
            state.pendingDeleteId = item.dataset.id;
            if (confirmOverlay) confirmOverlay.classList.add('open');
        });
    });

    if (confirmCancel) {
        confirmCancel.addEventListener('click', () => {
            state.pendingDeleteId = null;
            confirmOverlay.classList.remove('open');
        });
    }

    if (confirmDelete) {
        confirmDelete.addEventListener('click', async () => {
            if (!state.pendingDeleteId) return;
            try {
                const res  = await fetch(`/transaction/delete/${state.pendingDeleteId}`, {
                    method: 'POST', headers: csrfHeaders(), body: csrfBody(),
                });
                const data = await res.json();
                if (data.success) {
                    confirmOverlay.classList.remove('open');
                    showToast('Transaksi dihapus.');
                    setTimeout(() => location.reload(), 600);
                } else {
                    showToast(data.message || 'Gagal menghapus.', 'error');
                }
            } catch {
                showToast('Terjadi kesalahan.', 'error');
            }
        });
    }

    // ════════════════════════════════════════════════════════════════════════
    //  SETTINGS PAGE
    // ════════════════════════════════════════════════════════════════════════

    // Currency
    const currencyItem    = $('currencyItem');
    const currencyOverlay = $('currencyModalOverlay');
    const currencyClose   = $('currencyModalClose');

    if (currencyItem && currencyOverlay) {
        currencyItem.addEventListener('click', () => currencyOverlay.classList.add('open'));
        currencyClose.addEventListener('click', () => currencyOverlay.classList.remove('open'));
        currencyOverlay.addEventListener('click', e => { if (e.target === currencyOverlay) currencyOverlay.classList.remove('open'); });

        document.querySelectorAll('.currency-opt').forEach(btn => {
            btn.addEventListener('click', async function () {
                const currency = this.dataset.currency;
                const res  = await fetch('/settings/currency', { method: 'POST', headers: csrfHeaders(), body: csrfBody({ currency }) });
                const data = await res.json();
                if (data.success) {
                    currencyOverlay.classList.remove('open');
                    $('currencyDisplay').textContent = currency;
                    document.querySelectorAll('.currency-opt').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    showToast('Mata uang diperbarui!');
                }
            });
        });
    }

    // Add Category
    const addCatOverlay = $('addCatModalOverlay');
    const addCatClose   = $('addCatClose');
    const addCatSave    = $('addCatSave');
    let   activeCatType = 'expense';

    document.querySelectorAll('.add-cat-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            activeCatType = btn.dataset.type;
            $('newCatType').value = activeCatType;
            $('newCatName').value = '';
            $('newCatColor').value = '#6B7280';
            document.querySelectorAll('.color-dot').forEach(d => d.classList.remove('selected'));
            if (addCatOverlay) addCatOverlay.classList.add('open');
        });
    });

    if (addCatClose)   addCatClose.addEventListener('click', () => addCatOverlay.classList.remove('open'));
    if (addCatOverlay) addCatOverlay.addEventListener('click', e => { if (e.target === addCatOverlay) addCatOverlay.classList.remove('open'); });

    document.querySelectorAll('.color-dot').forEach(dot => {
        dot.addEventListener('click', function () {
            document.querySelectorAll('.color-dot').forEach(d => d.classList.remove('selected'));
            this.classList.add('selected');
            $('newCatColor').value = this.dataset.color;
        });
    });

    if (addCatSave) {
        addCatSave.addEventListener('click', async () => {
            const name  = $('newCatName').value.trim();
            const type  = $('newCatType').value;
            const color = $('newCatColor').value;
            if (!name) { showToast('Nama kategori wajib diisi.', 'error'); return; }

            const res  = await fetch('/settings/category/store', { method: 'POST', headers: csrfHeaders(), body: csrfBody({ name, type, color }) });
            const data = await res.json();
            if (data.success) {
                addCatOverlay.classList.remove('open');
                showToast('Kategori ditambahkan!');
                setTimeout(() => location.reload(), 600);
            } else {
                showToast(data.message || 'Gagal.', 'error');
            }
        });
    }

    // Delete category
    document.querySelectorAll('.cat-delete-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const id = this.dataset.id;
            if (!confirm('Hapus kategori ini?')) return;
            const res  = await fetch(`/settings/category/delete/${id}`, { method: 'POST', headers: csrfHeaders(), body: csrfBody() });
            const data = await res.json();
            if (data.success) {
                this.closest('.cat-item').remove();
                showToast('Kategori dihapus.');
            } else {
                showToast(data.message || 'Tidak bisa dihapus.', 'error');
            }
        });
    });

    // ════════════════════════════════════════════════════════════════════════
    //  AUTO-DISMISS FLASH TOAST
    // ════════════════════════════════════════════════════════════════════════
    const flashToast = $('flashToast');
    if (flashToast) {
        setTimeout(() => { flashToast.style.opacity = '0'; setTimeout(() => flashToast.remove(), 400); }, 3000);
    }

    // ════════════════════════════════════════════════════════════════════════
    //  PWA Service Worker Registration
    // ════════════════════════════════════════════════════════════════════════
    if ('serviceWorker' in navigator) {
        // Register main SW
        navigator.serviceWorker.register('/sw.js?v=' + Date.now()).catch(() => {});
        
        // Unregister conflicting belanja SW
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            for (let reg of registrations) {
                if (reg.scope && reg.scope.includes('belanja')) {
                    reg.unregister();
                }
            }
        });
    }

    // ════════════════════════════════════════════════════════════════════════
    //  PWA Install Prompt (A2HS) & iOS Support
    // ════════════════════════════════════════════════════════════════════════
    let deferredPrompt;
    const pwaBanner     = $('pwaInstallBanner');
    const btnInstallPwa = $('btnInstallPwa');
    const btnClosePwa   = $('btnClosePwa');

    // 1. Android / Chrome (Native Prompt)
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        if (pwaBanner && !localStorage.getItem('pwa_dismissed')) {
            pwaBanner.classList.add('show');
        }
    });

    if (btnInstallPwa) {
        btnInstallPwa.addEventListener('click', async () => {
            if (pwaBanner) pwaBanner.classList.remove('show');
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            await deferredPrompt.userChoice;
            deferredPrompt = null;
        });
    }

    if (btnClosePwa) {
        btnClosePwa.addEventListener('click', () => {
            if (pwaBanner) pwaBanner.classList.remove('show');
            localStorage.setItem('pwa_dismissed', '1');
        });
    }

    // 2. iOS Safari Custom Prompt
    const isIos = /iphone|ipad|ipod/.test(window.navigator.userAgent.toLowerCase());
    const isSafari = /safari/.test(window.navigator.userAgent.toLowerCase()) && !/chrome/.test(window.navigator.userAgent.toLowerCase());
    const isStandalone = ('standalone' in window.navigator) && (window.navigator.standalone);

    if (isIos && isSafari && !isStandalone && !localStorage.getItem('pwa_dismissed')) {
        if (pwaBanner) {
            // Replace banner content with iOS specific guide
            pwaBanner.innerHTML = `
                <div class="pwa-banner-content" style="flex-direction:column; align-items:center; text-align:center; padding:16px;">
                    <button class="btn-close-pwa" id="btnClosePwaIos" style="position:absolute; top:8px; right:8px;">✕</button>
                    <div style="font-weight:bold; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
                        <img src="/images/icon.svg" width="24" height="24">
                        Install Aplikasi DuitKu
                    </div>
                    <div style="font-size:12px; line-height:1.6; color:var(--text-muted);">
                        Untuk install ke Home Screen iOS:<br>
                        1. Tekan ikon <strong>Share</strong> di bawah layar.<br>
                        2. Pilih <strong>"Add to Home Screen"</strong>
                    </div>
                </div>
            `;
            
            document.getElementById('btnClosePwaIos').addEventListener('click', () => {
                pwaBanner.classList.remove('show');
                localStorage.setItem('pwa_dismissed', '1');
            });

            // Show after 2 seconds to not interrupt immediate load
            setTimeout(() => {
                pwaBanner.classList.add('show');
            }, 2000);
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    //  DYNAMIC ISLAND NAV — sliding indicator
    // ════════════════════════════════════════════════════════════════════════
    (function initNavIndicator() {
        const indicator = document.getElementById('navIndicator');
        const nav       = document.getElementById('bottomNav');
        if (!indicator || !nav) return;

        function snapTo(item, animated) {
            if (!item) return;
            const navRect  = nav.getBoundingClientRect();
            const itemRect = item.getBoundingClientRect();
            // offset by nav left-padding (8px)
            const x = itemRect.left - navRect.left - 8;
            const w = itemRect.width;
            if (!animated) {
                const prev = indicator.style.transition;
                indicator.style.transition = 'none';
                indicator.style.transform  = `translateX(${x}px)`;
                indicator.style.width      = `${w}px`;
                // re-enable transition on next frame
                requestAnimationFrame(() => { indicator.style.transition = prev; });
            } else {
                indicator.style.transform = `translateX(${x}px)`;
                indicator.style.width     = `${w}px`;
            }
        }

        // Position immediately on load (no spring)
        snapTo(nav.querySelector('.bottom-nav-item.active'), false);

        // Animate on tap before page navigates
        nav.querySelectorAll('.bottom-nav-item').forEach(item => {
            item.addEventListener('click', () => snapTo(item, true));
        });
    })();

})();
