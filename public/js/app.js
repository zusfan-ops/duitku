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
    // with position:fixed and modal-open class, restore scroll position on unlock.
    let _scrollLockY = 0;
    window.DuitkuLockScroll = function () {
        _scrollLockY = window.scrollY || window.pageYOffset || 0;
        document.documentElement.classList.add('modal-open');
        document.body.classList.add('modal-open');
        document.body.style.position = 'fixed';
        document.body.style.top      = `-${_scrollLockY}px`;
        document.body.style.left     = '0';
        document.body.style.right    = '0';
        document.body.style.width    = '100%';
        document.body.style.height   = '100%';
        document.body.style.overflow = 'hidden';
    };
    window.DuitkuUnlockScroll = function () {
        document.documentElement.classList.remove('modal-open');
        document.body.classList.remove('modal-open');
        document.body.style.position = '';
        document.body.style.top      = '';
        document.body.style.left     = '';
        document.body.style.right    = '';
        document.body.style.width    = '';
        document.body.style.height   = '';
        document.body.style.overflow = '';
        window.scrollTo(0, _scrollLockY);
    };

    // Prevent mobile rubber-band dragging of background when any modal is open
    document.addEventListener('touchmove', function (e) {
        if (document.body.classList.contains('modal-open') || document.querySelector('.modal-overlay.open')) {
            if (e.target.closest('.modal-sheet, .todo-modal-sheet, .hs-sheet, .modal-body, .sheet-body, .mini-modal')) {
                return; // allow smooth scrolling inside the sheet
            }
            e.preventDefault(); // stop background drag & size jitter
        }
    }, { passive: false });

    // ESC key closes modal
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (typeof window.closeModal === 'function') window.closeModal();
            document.querySelectorAll('.modal-overlay.open').forEach(el => {
                el.classList.remove('open');
            });
            window.DuitkuUnlockScroll();
        }
    });

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
        const rawWallets = window.DUITKU && window.DUITKU.wallets;
        const wallets = Array.isArray(rawWallets) ? rawWallets : (rawWallets && typeof rawWallets === 'object' ? Object.values(rawWallets) : []);
        if (!wallets.length) {
            const row = $('walletPickerRow');
            if (row) row.style.display = 'none';
            return;
        }
        const defaultW = wallets.find(w => w && w.is_default) || wallets[0];
        sel.innerHTML = wallets.map(w => {
            if (!w) return '';
            const bal = Number(w.balance || 0).toLocaleString('id-ID');
            const isSel = String(w.id) === String(selectedId || (defaultW ? defaultW.id : '')) ? ' selected' : '';
            return `<option value="${w.id}"${isSel}>${w.icon || '💳'} ${escHtml(w.name || 'Dompet')} — ${window.DUITKU?.symbol || 'Rp'} ${bal}</option>`;
        }).join('');
    }

    function openModal(editData = null) {
        const modalOverlay = $('txModalOverlay') || document.getElementById('txModalOverlay');
        if (!modalOverlay) return;
        state.editingTxId        = null;
        state.selectedCategoryId = null;
        state.selectedType       = 'expense';
        state.isRecurring        = false;

        // Reset form
        const formEl = $('txForm') || document.getElementById('txForm');
        if (formEl) formEl.reset();
        if ($('txId')) $('txId').value = '';
        if ($('txDate')) $('txDate').value = new Date().toISOString().slice(0, 10);
        if ($('txType')) $('txType').value = 'expense';
        if ($('txAmount')) $('txAmount').value = '';
        if ($('txNote')) $('txNote').value = '';
        if ($('txCategory')) $('txCategory').value = '';
        if ($('txExistingImage')) $('txExistingImage').value = '';
        if ($('txImage')) $('txImage').value = '';
        if ($('txImagePreviewContainer')) {
            $('txImagePreviewContainer').style.display = 'none';
            if ($('txImagePreview')) $('txImagePreview').src = '';
        }
        // Reset recurring toggle
        const recToggle = $('recurringToggle');
        const recInput  = $('txRecurring');
        const recWrap   = $('recurringWrap');
        if (recToggle) recToggle.classList.remove('on');
        if (recInput)  recInput.value = '0';
        if (recWrap)   recWrap.style.display = 'flex';

        if ($('modalTitle')) $('modalTitle').textContent = 'Transaksi Baru';
        if ($('btnSave')) $('btnSave').textContent = 'Simpan Pengeluaran';

        // Reset type buttons
        if ($('btnExpense')) {
            $('btnExpense').classList.add('active');
            $('btnExpense').classList.remove('income-active');
        }
        if ($('btnIncome')) $('btnIncome').classList.remove('active', 'income-active');

        if ($('amountCurrency')) $('amountCurrency').textContent = (window.DUITKU && window.DUITKU.symbol) ? window.DUITKU.symbol : 'Rp';

        if (editData) {
            state.editingTxId = editData.id;
            state.selectedType = editData.type || 'expense';
            if ($('txId')) $('txId').value = editData.id || '';
            if ($('txAmount')) $('txAmount').value = editData.amount ? formatNum(editData.amount) : '';
            if ($('txNote')) $('txNote').value = editData.note || '';
            if ($('txDate') && editData.date) $('txDate').value = editData.date;
            if ($('txType')) $('txType').value = editData.type || 'expense';
            if ($('modalTitle')) $('modalTitle').textContent = 'Edit Transaksi';
            if ($('btnSave')) $('btnSave').textContent = 'Simpan Perubahan';

            if (editData.type === 'income') {
                if ($('btnIncome')) $('btnIncome').classList.add('active', 'income-active');
                if ($('btnExpense')) $('btnExpense').classList.remove('active', 'income-active');
            }
            state.selectedCategoryId = editData.category_id;
            if ($('txCategory')) $('txCategory').value = editData.category_id || '';

            if (editData.image && $('txImagePreviewContainer')) {
                $('txImagePreviewContainer').style.display = 'block';
                if ($('txImagePreview')) $('txImagePreview').src = '/uploads/transactions/' + editData.image;
            }
            if (recWrap) recWrap.style.display = 'none';
        }

        try { populateWalletSelect(editData ? editData.wallet_id : null); } catch (e) { console.error(e); }
        try { renderCategoryChips(); } catch (e) { console.error(e); }
        
        modalOverlay.classList.add('open');
        if (typeof window.DuitkuLockScroll === 'function') window.DuitkuLockScroll();
        setTimeout(() => { if ($('txAmount')) $('txAmount').focus(); }, 350);
    }

    function closeModal() {
        const modalOverlay = $('txModalOverlay') || document.getElementById('txModalOverlay');
        if (!modalOverlay) return;
        modalOverlay.classList.remove('open');
        if (typeof window.DuitkuUnlockScroll === 'function') window.DuitkuUnlockScroll();
    }

    window.openModal = openModal;
    window.closeModal = closeModal;
    window.openTransactionModal = function(type = 'expense', amount = null, note = null) {
        openModal({
            id: '',
            type: type,
            amount: amount,
            note: note,
            date: new Date().toISOString().slice(0, 10),
            wallet_id: null,
            category_id: null
        });
    };

    // Auto-open modal if URL query params are present (e.g. from Zakat / Pajak / Quick Action)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('add_expense') || urlParams.has('add_income')) {
        const type = urlParams.has('add_income') ? 'income' : 'expense';
        const amount = urlParams.get('amount') || '';
        const note = urlParams.get('note') || '';
        setTimeout(() => {
            window.openTransactionModal(type, amount, note);
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 150);
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
        const rawCats = window.DUITKU && window.DUITKU.categories;
        const allCats = Array.isArray(rawCats) ? rawCats : (rawCats && typeof rawCats === 'object' ? Object.values(rawCats) : []);
        const cats = allCats.filter(c => c && typeof c === 'object' && c.type === state.selectedType);
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
            } else if ($('txExistingImage') && $('txExistingImage').value) {
                formData.append('existing_image', $('txExistingImage').value);
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
    //  PWA Install Prompt (A2HS) & Android GitHub / iOS Support
    // ════════════════════════════════════════════════════════════════════════
    const GITHUB_RELEASE_URL = 'https://github.com/zusfan-ops/duitku/releases';
    let deferredPrompt;
    const pwaBanner     = $('pwaInstallBanner');
    const btnInstallPwa = $('btnInstallPwa');
    const btnClosePwa   = $('btnClosePwa');

    const ua = (navigator.userAgent || navigator.vendor || window.opera || '').toLowerCase();
    const isAndroid = /android/i.test(ua);
    const isIos = /iphone|ipad|ipod/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

    // Customize banner UI per OS
    if (pwaBanner) {
        if (isAndroid && btnInstallPwa) {
            btnInstallPwa.textContent = 'Unduh APK';
            const titleEl = pwaBanner.querySelector('strong');
            const subEl = pwaBanner.querySelector('span');
            if (titleEl) titleEl.textContent = 'Aplikasi Android (APK)';
            if (subEl) subEl.textContent = 'Unduh versi rilis resmi di GitHub.';
        } else if (isIos && btnInstallPwa) {
            btnInstallPwa.textContent = 'Petunjuk';
            const titleEl = pwaBanner.querySelector('strong');
            const subEl = pwaBanner.querySelector('span');
            if (titleEl) titleEl.textContent = 'Pasang di iPhone / iPad';
            if (subEl) subEl.textContent = 'Akses cepat dari Home Screen iOS.';
        }
    }

    // 1. Android / Chrome (Native Prompt Event)
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        window.deferredPrompt = e;
        if (pwaBanner && !localStorage.getItem('pwa_dismissed')) {
            pwaBanner.classList.add('show');
        }
    });

    if (btnInstallPwa) {
        btnInstallPwa.addEventListener('click', async () => {
            if (isAndroid) {
                // Direct Android users to GitHub Release APK
                window.location.href = GITHUB_RELEASE_URL;
                return;
            }

            if (isIos) {
                // Show iOS Guide popup / banner details
                alert('Untuk memasang di iPhone/iPad:\n1. Buka di Safari.\n2. Ketuk ikon Share di bilah bawah.\n3. Pilih "Tambah ke Layar Utama" (Add to Home Screen).');
                return;
            }

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

    // ════════════════════════════════════════════════════════════════════════
    //  REAL-TIME CHAT UNREAD BADGE POLLING
    // ════════════════════════════════════════════════════════════════════════
    (function initChatUnreadPolling() {
        const badge = document.getElementById('navChatBadge');
        if (!badge) return;

        function checkUnread() {
            fetch('/marketplace/chat/unread-count', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.status === 'success') {
                    if (data.unread_count > 0) {
                        badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(() => {});
        }

        // Check after 3s, then poll every 7s
        setTimeout(checkUnread, 3000);
        setInterval(checkUnread, 7000);
    })();

})();
