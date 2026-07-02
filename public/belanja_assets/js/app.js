/**
 * Simple Shopping List App Logic
 * Designed for Indonesian Moms
 */

console.log('App.JS v11 loaded');
const App = {
    data: [],
    filter: 'all',
    categoryIcons: {
        'Sayur': '🥬',
        'Ikan': '🐟',
        'Buah': '🍎',
        'Bumbu': '🌶️',
        'Kebutuhan Rumah': '🏠',
        'Lainnya': '📦'
    },
    currentScreen: 'list',
    notes: [],
    storage: [],
    // Fitur baru
    lists: [],
    currentListId: null,
    favorites: [],
    history: [],
    pantry: [],
    reminders: [],
    budget: 0,
    theme: 'dark',
    pantryFilter: '',
    kursRates: null,
    recog: null,
    parking: null,
    searchTerm: '',
    parkingBase64: null,


    async init() {
        await this.loadData();
        this.cacheDOM();
        this.bindEvents();
        this.applyTheme();
        this.applyFontScale();
        this.renderListSelector();
        this.renderFavorites();
        this.render();
        this.updateOnlineStatus();
        this.handleInstallPrompt();
        this.checkDueReminders();
        this.autoBackupSnapshot();
        this.checkBackupReminder();
        lucide.createIcons();
        window.addEventListener('online', () => this.updateOnlineStatus());
        window.addEventListener('offline', () => this.updateOnlineStatus());
    },

    handleInstallPrompt() {
        console.log('handleInstallPrompt initialized');
        this.deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('beforeinstallprompt event fired');
            // Prevent Chrome 67 and earlier from automatically showing the prompt
            e.preventDefault();
            // Stash the event so it can be triggered later.
            this.deferredPrompt = e;
            // Update UI notify the user they can install the PWA
            if (this.btnInstall) {
                console.log('Showing install button in header');
                this.btnInstall.classList.remove('hidden');
            }

            // Show modal if not dismissed before
            const dismissed = localStorage.getItem('install_dismissed');
            console.log('Install dismissed status:', dismissed);
            if (!dismissed) {
                console.log('Scheduling install modal popup...');
                setTimeout(() => {
                    console.log('Attempting to show install modal');
                    if (this.installModal) {
                        this.installModal.classList.remove('hidden');
                        console.log('Install modal should now be visible');
                    } else {
                        console.error('this.installModal is null!');
                    }
                }, 3000);
            }
        });

        window.addEventListener('appinstalled', (evt) => {
            console.log('App was installed');
            if (this.btnInstall) {
                this.btnInstall.classList.add('hidden');
            }
            this.installModal.classList.add('hidden');
            this.deferredPrompt = null;
        });
    },

    async installApp() {
        if (!this.deferredPrompt) return;
        // Show the prompt
        this.deferredPrompt.prompt();
        // Wait for the user to respond to the prompt
        const { outcome } = await this.deferredPrompt.userChoice;
        console.log(`User response to the install prompt: ${outcome}`);
        // We've used the prompt, and can't use it again, throw it away
        this.deferredPrompt = null;
        if (this.btnInstall) {
            this.btnInstall.classList.add('hidden');
        }
    },

    cacheDOM() {
        this.listContainer = document.getElementById('shopping-list');
        this.listMain = document.getElementById('list-container');
        this.emptyState = document.getElementById('empty-state');
        this.totalEl = document.getElementById('total-price');
        this.summaryFooter = document.getElementById('summary-footer');
        this.modal = document.getElementById('item-modal');
        this.form = document.getElementById('item-form');
        this.fab = document.getElementById('fab-add');
        this.btnReset = document.getElementById('btn-reset');
        this.btnInfo = document.getElementById('btn-info');
        this.btnCalc = document.getElementById('btn-calc');
        this.btnShare = document.getElementById('btn-share');
        this.infoModal = document.getElementById('info-modal');
        this.calcModal = document.getElementById('calc-modal');
        this.onlineStatus = document.getElementById('online-status');
        this.categoryPills = document.querySelectorAll('.pill');
        this.btnInstall = document.getElementById('btn-install');
        this.installModal = document.getElementById('install-modal');

        // Navigation elements
        this.navItems = document.querySelectorAll('.nav-item');
        this.shoppingFilters = document.getElementById('shopping-filters');
        this.notesScreen = document.getElementById('notes-screen');
        this.priceItemsContainer = document.getElementById('price-items-container');
        this.priceTabs = document.querySelectorAll('.price-tab');
        this.currentPriceGroup = 'sembako';

        // Info screen (Cek Harga + Kurs + Resep) elements
        this.infoScreen = document.getElementById('info-screen');
        this.infoTabs = document.querySelectorAll('.info-tab');
        this.pricesPanel = document.getElementById('prices-panel');
        this.kursPanel = document.getElementById('kurs-panel');
        this.recipesPanel = document.getElementById('recipes-panel');
        this.kursContainer = document.getElementById('kurs-items-container');
        this.currentInfoTab = 'prices';

        // Recipe elements
        this.recipesContainer = document.getElementById('recipes-container');
        this.recipeSearchInput = document.getElementById('recipe-search');
        this.btnSearchRecipe = document.getElementById('btn-search-recipe');
        this.recipeModal = document.getElementById('recipe-modal');
        this.recipeDetailContent = document.getElementById('recipe-detail-content');

        // Calculator elements
        this.calcPrice = document.getElementById('calc-price');
        this.calcPercent = document.getElementById('calc-percent');
        this.calcResultBox = document.getElementById('calc-result-box');
        this.resSavings = document.getElementById('res-savings');
        this.resFinal = document.getElementById('res-final');

        // Standard Calculator elements
        this.stdDisplay = document.getElementById('std-display');
        this.stdExpression = '';

        // Notes and Image elements
        this.notesInput = document.getElementById('item-notes');
        this.imageInput = document.getElementById('item-image');
        this.btnCapture = document.getElementById('btn-capture');
        this.imagePreview = document.getElementById('image-preview');
        this.previewImg = this.imagePreview.querySelector('img');
        this.btnRemoveImage = document.querySelector('.remove-image');
        this.currentBase64 = null;

        // Note Modal elements
        this.noteModal = document.getElementById('note-modal');
        this.noteForm = document.getElementById('note-form');
        this.noteTitleInput = document.getElementById('note-title');
        this.noteContentInput = document.getElementById('note-content');
        this.noteImageInput = document.getElementById('note-image-input');
        this.btnCaptureNote = document.getElementById('btn-capture-note');
        this.noteImagePreview = document.getElementById('note-image-preview');
        this.notePreviewImg = this.noteImagePreview.querySelector('img');
        this.btnRemoveNoteImage = document.querySelector('.remove-note-image');
        this.notesListContainer = document.getElementById('notes-list-container');
        this.noteCurrentBase64 = null;

        // Storage (Item Placement) elements
        this.storageScreen = document.getElementById('storage-screen');
        this.storageListContainer = document.getElementById('storage-list-container');
        this.storageModal = document.getElementById('storage-modal');
        this.storageForm = document.getElementById('storage-form');
        this.storageNameInput = document.getElementById('storage-name');
        this.storageLocationInput = document.getElementById('storage-location');
        this.storageNotesInput = document.getElementById('storage-notes');
        this.storageImageInput = document.getElementById('storage-image-input');
        this.btnCaptureStorage = document.getElementById('btn-capture-storage');
        this.storageImagePreview = document.getElementById('storage-image-preview');
        this.storagePreviewImg = this.storageImagePreview.querySelector('img');
        this.btnRemoveStorageImage = document.querySelector('.remove-storage-image');
        this.btnGetLocation = document.getElementById('btn-get-location');
        this.storageLocationPreview = document.getElementById('storage-location-preview');
        this.storageLocationText = document.getElementById('storage-location-text');
        this.btnRemoveLocation = document.querySelector('.remove-location');
        this.storageSearchInput = document.getElementById('storage-search');
        this.btnSearchStorage = document.getElementById('btn-search-storage');
        this.storageCurrentBase64 = null;
        this.storageCurrentCoords = null;
        this.storageFilter = '';

        // --- Fitur baru ---
        // Header menu / settings
        this.btnMenu = document.getElementById('btn-menu');
        this.settingsModal = document.getElementById('settings-modal');
        // List selector
        this.listSelectorBar = document.getElementById('list-selector-bar');
        this.listSelect = document.getElementById('list-select');
        // Favorites
        this.favoritesBar = document.getElementById('favorites-bar');
        this.favoritesChips = document.getElementById('favorites-chips');
        // Budget / footer
        this.budgetInfo = document.getElementById('budget-info');
        this.budgetValueEl = document.getElementById('budget-value');
        this.budgetBarWrap = document.getElementById('budget-bar-wrap');
        this.budgetBar = document.getElementById('budget-bar');
        // Voice
        this.btnVoice = document.getElementById('btn-voice');
        // Pantry
        this.pantryScreen = document.getElementById('pantry-screen');
        this.pantryListContainer = document.getElementById('pantry-list-container');
        this.pantryModal = document.getElementById('pantry-modal');
        this.pantryForm = document.getElementById('pantry-form');
        this.pantrySearchInput = document.getElementById('pantry-search');
        // History
        this.historyScreen = document.getElementById('history-screen');
        this.historySummary = document.getElementById('history-summary');
        this.historyChart = document.getElementById('history-chart');
        this.historyListContainer = document.getElementById('history-list-container');
        // Reminders
        this.remindersScreen = document.getElementById('reminders-screen');
        this.remindersListContainer = document.getElementById('reminders-list-container');
        this.reminderModal = document.getElementById('reminder-modal');
        this.reminderForm = document.getElementById('reminder-form');
        // Kurs converter
        this.kursConvAmount = document.getElementById('kurs-conv-amount');
        this.kursConvCurrency = document.getElementById('kurs-conv-currency');
        this.kursConvOutput = document.getElementById('kurs-conv-output');

        // Search (shopping list)
        this.shoppingSearch = document.getElementById('shopping-search');
        this.itemSearch = document.getElementById('item-search');
        this.btnClearSearch = document.getElementById('btn-clear-search');

        // Parking
        this.parkingModal = document.getElementById('parking-modal');
    },

    bindEvents() {
        this.fab.addEventListener('click', () => {
            if (this.currentScreen === 'list') {
                this.openModal();
            } else if (this.currentScreen === 'notes') {
                this.openNoteModal();
            } else if (this.currentScreen === 'storage') {
                this.openStorageModal();
            } else if (this.currentScreen === 'pantry') {
                this.openPantryModal();
            } else if (this.currentScreen === 'reminders') {
                this.openReminderModal();
            }
        });
        document.querySelector('.close-modal').addEventListener('click', () => this.closeModal());
        document.querySelector('.close-note-modal').addEventListener('click', () => this.closeNoteModal());

        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveItem();
        });

        this.btnReset.addEventListener('click', () => {
            const listName = this.getCurrentList().name;
            if (confirm(`Hapus semua barang di daftar "${listName}"?`)) {
                this.data = this.data.filter(i => i.listId !== this.currentListId);
                this.saveData();
                this.render();
            }
        });

        this.btnShare.addEventListener('click', () => this.shareToWhatsApp());

        if (this.btnInstall) {
            this.btnInstall.addEventListener('click', () => {
                this.installModal.classList.remove('hidden');
            });
        }

        document.getElementById('btn-install-now').addEventListener('click', () => {
            this.installApp();
        });

        document.getElementById('btn-later').addEventListener('click', () => {
            this.installModal.classList.add('hidden');
            localStorage.setItem('install_dismissed', 'true');
        });

        document.getElementById('close-install-modal-x').addEventListener('click', () => {
            this.installModal.classList.add('hidden');
        });

        if (this.btnInfo) {
            this.btnInfo.addEventListener('click', () => {
                this.infoModal.classList.remove('hidden');
            });
        }

        document.getElementById('btn-close-info').addEventListener('click', () => {
            this.infoModal.classList.add('hidden');
        });

        document.getElementById('close-info-modal-x').addEventListener('click', () => {
            this.infoModal.classList.add('hidden');
        });

        // Calculator Events
        this.btnCalc.addEventListener('click', () => {
            this.calcModal.classList.remove('hidden');
        });

        document.getElementById('btn-close-calc').addEventListener('click', () => {
            this.calcModal.classList.add('hidden');
        });

        document.getElementById('close-calc-modal-x').addEventListener('click', () => {
            this.calcModal.classList.add('hidden');
        });

        this.calcPrice.addEventListener('input', () => this.updateCalculations());
        this.calcPercent.addEventListener('input', () => this.updateCalculations());

        document.querySelectorAll('.percent-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                this.calcPercent.value = btn.dataset.value;
                this.updateCalculations();
            });
        });

        // Tab Switching Logic
        const tabBtns = document.querySelectorAll('.calc-tab-btn');
        const tabContents = document.querySelectorAll('.calc-tab-content');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.add('hidden'));

                btn.classList.add('active');
                document.getElementById(`tab-${btn.dataset.tab}`).classList.remove('hidden');
            });
        });

        // Standard Calculator Button Logic
        document.querySelectorAll('.calc-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const num = btn.dataset.num;
                const op = btn.dataset.op;

                if (num !== undefined) this.appendNum(num);
                if (op !== undefined) this.handleOp(op);

                lucide.createIcons(); // Refresh icons inside modal if needed
            });
        });

        this.categoryPills.forEach(pill => {
            pill.addEventListener('click', (e) => {
                this.categoryPills.forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                this.filter = pill.dataset.category;
                this.render();
            });
        });

        // Image Handling Events
        this.btnCapture.addEventListener('click', () => this.imageInput.click());

        this.imageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    this.currentBase64 = event.target.result;
                    this.previewImg.src = this.currentBase64;
                    this.imagePreview.classList.remove('hidden');
                    this.btnCapture.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });

        this.btnRemoveImage.addEventListener('click', () => {
            this.currentBase64 = null;
            this.imageInput.value = '';
            this.imagePreview.classList.add('hidden');
            this.btnCapture.classList.remove('hidden');
        });

        // Price Tabs Events
        this.priceTabs.forEach(btn => {
            btn.addEventListener('click', () => {
                this.priceTabs.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                this.currentPriceGroup = btn.dataset.group;
                this.fetchPriceList();
            });
        });

        // Navigation Events
        this.navItems.forEach(item => {
            item.addEventListener('click', () => {
                const screen = item.dataset.screen;
                this.switchScreen(screen);
            });
        });


        this.btnRemoveNoteImage.addEventListener('click', () => {
            this.noteCurrentBase64 = null;
            this.noteImageInput.value = '';
            this.noteImagePreview.classList.add('hidden');
            this.btnCaptureNote.classList.remove('hidden');
        });

        this.noteForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveNote();
        });

        this.btnCaptureNote.addEventListener('click', () => this.noteImageInput.click());

        this.noteImageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    this.noteCurrentBase64 = event.target.result;
                    this.notePreviewImg.src = this.noteCurrentBase64;
                    this.noteImagePreview.classList.remove('hidden');
                    this.btnCaptureNote.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });

        // Navigation Events
        this.navItems.forEach(item => {
            item.addEventListener('click', () => {
                const screen = item.dataset.screen;
                this.switchScreen(screen);
            });
        });

        // Storage (Item Placement) Events
        document.querySelector('.close-storage-modal').addEventListener('click', () => this.closeStorageModal());

        this.storageForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveStorage();
        });

        this.btnCaptureStorage.addEventListener('click', () => this.storageImageInput.click());

        this.storageImageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    this.storageCurrentBase64 = event.target.result;
                    this.storagePreviewImg.src = this.storageCurrentBase64;
                    this.storageImagePreview.classList.remove('hidden');
                    this.btnCaptureStorage.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });

        this.btnRemoveStorageImage.addEventListener('click', () => {
            this.storageCurrentBase64 = null;
            this.storageImageInput.value = '';
            this.storageImagePreview.classList.add('hidden');
            this.btnCaptureStorage.classList.remove('hidden');
        });

        this.btnGetLocation.addEventListener('click', () => this.getCurrentLocation());

        this.btnRemoveLocation.addEventListener('click', () => {
            this.storageCurrentCoords = null;
            this.storageLocationPreview.classList.add('hidden');
            this.btnGetLocation.classList.remove('hidden');
        });

        if (this.btnSearchStorage) {
            this.btnSearchStorage.addEventListener('click', () => {
                this.storageFilter = this.storageSearchInput.value.trim().toLowerCase();
                this.renderStorage();
            });
        }
        if (this.storageSearchInput) {
            this.storageSearchInput.addEventListener('input', () => {
                this.storageFilter = this.storageSearchInput.value.trim().toLowerCase();
                this.renderStorage();
            });
        }

        // Info Tab Events (Cek Harga / Resep)
        this.infoTabs.forEach(tab => {
            tab.addEventListener('click', () => this.switchInfoTab(tab.dataset.tab));
        });

        // Recipe Events
        if (this.btnSearchRecipe) {
            this.btnSearchRecipe.addEventListener('click', () => this.fetchRecipes(this.recipeSearchInput.value));
        }
        if (this.recipeSearchInput) {
            this.recipeSearchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.fetchRecipes(this.recipeSearchInput.value);
                }
            });
        }
        if (document.querySelector('.close-recipe-modal')) {
            document.querySelector('.close-recipe-modal').addEventListener('click', () => this.closeRecipeModal());
        }


        // ===== Fitur baru =====
        // Menu & Settings
        this.btnMenu.addEventListener('click', () => this.openSettings());
        document.getElementById('close-settings-modal-x').addEventListener('click', () => this.settingsModal.classList.add('hidden'));
        document.getElementById('btn-save-budget').addEventListener('click', () => this.saveBudget());
        document.getElementById('btn-toggle-theme').addEventListener('click', () => this.toggleTheme());
        // Backup UI dihapus — elemen tidak ada di DOM lagi

        // Font size
        document.getElementById('setting-fontscale').addEventListener('change', (e) => this.saveFontScale(e.target.value));

        // Search in shopping list
        this.itemSearch.addEventListener('input', () => {
            this.searchTerm = this.itemSearch.value.trim().toLowerCase();
            this.btnClearSearch.classList.toggle('hidden', !this.searchTerm);
            this.render();
        });
        this.btnClearSearch.addEventListener('click', () => {
            this.itemSearch.value = '';
            this.searchTerm = '';
            this.btnClearSearch.classList.add('hidden');
            this.render();
        });

        // Change calculator (Kembalian)
        ['change-total', 'change-paid'].forEach(id => {
            document.getElementById(id).addEventListener('input', () => this.updateChange());
        });
        document.getElementById('btn-use-list-total').addEventListener('click', () => this.useListTotalForChange());

        // Unit price comparison (Banding Harga)
        ['cmp-a-price', 'cmp-a-qty', 'cmp-b-price', 'cmp-b-qty'].forEach(id => {
            document.getElementById(id).addEventListener('input', () => this.updateCompare());
        });

        // Parking location
        document.getElementById('btn-open-parking').addEventListener('click', () => {
            this.settingsModal.classList.add('hidden');
            this.openParkingModal();
        });
        document.querySelector('.close-parking-modal').addEventListener('click', () => this.parkingModal.classList.add('hidden'));
        document.getElementById('btn-save-parking').addEventListener('click', () => this.saveParkingNow());
        document.getElementById('btn-capture-parking').addEventListener('click', () => document.getElementById('parking-image-input').click());
        document.getElementById('parking-image-input').addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => {
                this.parkingBase64 = ev.target.result;
                const pv = document.getElementById('parking-image-preview');
                pv.querySelector('img').src = this.parkingBase64;
                pv.classList.remove('hidden');
                document.getElementById('btn-capture-parking').classList.add('hidden');
            };
            reader.readAsDataURL(file);
        });
        document.querySelector('.remove-parking-image').addEventListener('click', () => {
            this.parkingBase64 = null;
            document.getElementById('parking-image-input').value = '';
            document.getElementById('parking-image-preview').classList.add('hidden');
            document.getElementById('btn-capture-parking').classList.remove('hidden');
        });
        document.getElementById('btn-parking-map').addEventListener('click', () => this.openParkingMap());
        document.getElementById('btn-parking-delete').addEventListener('click', () => this.deleteParking());
        document.getElementById('btn-open-about').addEventListener('click', () => {
            this.settingsModal.classList.add('hidden');
            this.infoModal.classList.remove('hidden');
        });
        document.querySelectorAll('.settings-link[data-open]').forEach(btn => {
            btn.addEventListener('click', () => {
                this.settingsModal.classList.add('hidden');
                this.switchScreen(btn.dataset.open);
            });
        });

        // Back buttons
        document.querySelectorAll('.btn-back').forEach(btn => {
            btn.addEventListener('click', () => this.switchScreen(btn.dataset.back || 'list'));
        });

        // List selector
        this.listSelect.addEventListener('change', () => this.changeList(this.listSelect.value));
        document.getElementById('btn-add-list').addEventListener('click', () => this.addList());
        document.getElementById('btn-rename-list').addEventListener('click', () => this.renameList());
        document.getElementById('btn-delete-list').addEventListener('click', () => this.deleteList());

        // Footer extra actions
        document.getElementById('btn-print').addEventListener('click', () => this.printList());
        document.getElementById('btn-finish').addEventListener('click', () => this.finishShopping());

        // Voice input
        if (this.btnVoice) this.btnVoice.addEventListener('click', () => this.startVoice());

        // Pantry
        this.pantryForm.addEventListener('submit', (e) => { e.preventDefault(); this.savePantry(); });
        document.querySelector('.close-pantry-modal').addEventListener('click', () => this.pantryModal.classList.add('hidden'));
        document.getElementById('btn-search-pantry').addEventListener('click', () => {
            this.pantryFilter = this.pantrySearchInput.value.trim().toLowerCase();
            this.renderPantry();
        });
        this.pantrySearchInput.addEventListener('input', () => {
            this.pantryFilter = this.pantrySearchInput.value.trim().toLowerCase();
            this.renderPantry();
        });

        // Reminders
        this.reminderForm.addEventListener('submit', (e) => { e.preventDefault(); this.saveReminder(); });
        document.querySelector('.close-reminder-modal').addEventListener('click', () => this.reminderModal.classList.add('hidden'));

        // Kurs converter
        if (this.kursConvAmount) {
            this.kursConvAmount.addEventListener('input', () => this.updateKursConvert());
            this.kursConvCurrency.addEventListener('change', () => this.updateKursConvert());
        }

        // Close modal on background click
        window.addEventListener('click', (e) => {
            if (e.target === this.modal) this.closeModal();
            if (e.target === this.infoModal) this.infoModal.classList.add('hidden');
            if (e.target === this.calcModal) this.calcModal.classList.add('hidden');
            if (e.target === this.recipeModal) this.closeRecipeModal();
            if (e.target === this.storageModal) this.closeStorageModal();
            if (e.target === this.settingsModal) this.settingsModal.classList.add('hidden');
            if (e.target === this.pantryModal) this.pantryModal.classList.add('hidden');
            if (e.target === this.reminderModal) this.reminderModal.classList.add('hidden');
            if (e.target === this.parkingModal) this.parkingModal.classList.add('hidden');
        });
    },

    loadData() {
        const saved = localStorage.getItem('belanja_data');
        this.data = saved ? JSON.parse(saved) : [];
    },

    saveData() {
        localStorage.setItem('belanja_data', JSON.stringify(this.data));
    },

    getCurrentList() {
        return this.lists.find(l => l.id === this.currentListId) || this.lists[0] || { id: '', name: 'Daftar' };
    },

    getCurrentItems() {
        return this.data.filter(item => item.listId === this.currentListId);
    },

    formatRp(n) {
        return 'Rp ' + (parseInt(n) || 0).toLocaleString('id-ID');
    },

    render() {
        const listItems = this.getCurrentItems();
        let filteredData = this.filter === 'all'
            ? listItems
            : listItems.filter(item => item.category === this.filter);
        if (this.searchTerm) {
            filteredData = filteredData.filter(item => (item.name || '').toLowerCase().includes(this.searchTerm));
        }

        if (listItems.length === 0) {
            this.emptyState.classList.remove('hidden');
            this.listContainer.innerHTML = '';
            this.summaryFooter.classList.add('hidden');
        } else {
            this.emptyState.classList.add('hidden');
            this.summaryFooter.classList.remove('hidden');

            this.listContainer.innerHTML = filteredData.map(item => {
                const isFav = this.favorites.some(f => f.name.toLowerCase() === (item.name || '').toLowerCase());
                return `
                <div class="item-card ${item.bought ? 'bought' : ''}" data-id="${item.id}">
                    <div class="item-main" onclick="App.toggleBought('${item.id}')">
                        <div class="check-box">
                            <i data-lucide="check"></i>
                        </div>
                        ${item.image ? `<img src="${item.image}" class="item-image-display" alt="Foto">` : ''}
                        <div class="item-info">
                            <span class="item-name">${item.name}</span>
                            <span class="item-meta">${item.qty || ''} ${item.qty && item.category ? '•' : ''} ${this.categoryIcons[item.category] || ''} ${item.category || ''}</span>
                            ${item.notes ? `<span class="item-notes-display">${item.notes}</span>` : ''}
                        </div>
                    </div>
                    <div class="item-price-area">
                        ${item.price ? `<span class="item-price">Rp ${parseInt(item.price).toLocaleString('id-ID')}</span>` : ''}
                    </div>
                    <div class="item-actions">
                        <button class="icon-button item-fav-btn ${isFav ? 'active' : ''}" title="Sering dibeli" onclick="App.toggleFavorite('${item.id}')"><i data-lucide="star"></i></button>
                        <button class="icon-button" onclick="App.editItem('${item.id}')"><i data-lucide="edit-3"></i></button>
                        <button class="icon-button" onclick="App.deleteItem('${item.id}')"><i data-lucide="trash-2"></i></button>
                    </div>
                </div>
            `;
            }).join('');

            lucide.createIcons();
        }

        this.updateTotal();
    },

    updateTotal() {
        const total = this.getCurrentItems().reduce((sum, item) => sum + (parseInt(item.price) || 0), 0);
        this.totalEl.textContent = this.formatRp(total);
        this.updateBudgetUI(total);
    },

    updateBudgetUI(total) {
        if (!this.budgetInfo) return;
        if (this.budget && this.budget > 0) {
            const remaining = this.budget - total;
            const pct = Math.min(100, Math.round((total / this.budget) * 100));
            this.budgetInfo.classList.remove('hidden');
            this.budgetBarWrap.classList.remove('hidden');
            this.budgetValueEl.textContent = this.formatRp(remaining);
            this.budgetBar.style.width = pct + '%';
            const over = remaining < 0;
            this.budgetValueEl.classList.toggle('over', over);
            this.budgetBar.classList.toggle('over', over);
            this.budgetBar.style.width = (over ? 100 : pct) + '%';
        } else {
            this.budgetInfo.classList.add('hidden');
            this.budgetBarWrap.classList.add('hidden');
        }
    },

    openModal(item = null) {
        if (item) {
            document.getElementById('modal-title').textContent = 'Edit Barang';
            document.getElementById('item-id').value = item.id;
            document.getElementById('item-name').value = item.name;
            document.getElementById('item-qty').value = item.qty;
            document.getElementById('item-price').value = item.price;
            document.getElementById('item-category').value = item.category;
            this.notesInput.value = item.notes || '';

            if (item.image) {
                this.currentBase64 = item.image;
                this.previewImg.src = item.image;
                this.imagePreview.classList.remove('hidden');
                this.btnCapture.classList.add('hidden');
            } else {
                this.currentBase64 = null;
                this.imagePreview.classList.add('hidden');
                this.btnCapture.classList.remove('hidden');
            }
        } else {
            document.getElementById('modal-title').textContent = 'Tambah Barang';
            this.form.reset();
            document.getElementById('item-id').value = '';
            this.notesInput.value = '';
            this.currentBase64 = null;
            this.imagePreview.classList.add('hidden');
            this.btnCapture.classList.remove('hidden');
        }
        this.modal.classList.remove('hidden');
        document.getElementById('item-name').focus();
    },

    closeModal() {
        this.modal.classList.add('hidden');
    },

    saveItem() {
        const id = document.getElementById('item-id').value;
        const name = document.getElementById('item-name').value;
        const qty = document.getElementById('item-qty').value;
        const price = document.getElementById('item-price').value;
        const category = document.getElementById('item-category').value;
        const notes = this.notesInput.value;
        const image = this.currentBase64;

        if (id) {
            // Update
            const index = this.data.findIndex(i => i.id === id);
            this.data[index] = { ...this.data[index], name, qty, price, category, notes, image };
        } else {
            // Add new
            this.data.push({
                id: Date.now().toString(),
                name,
                qty,
                price,
                category,
                notes,
                image,
                bought: false,
                listId: this.currentListId
            });
        }

        this.saveData();
        this.render();
        this.closeModal();
    },

    toggleBought(id) {
        const index = this.data.findIndex(i => i.id === id);
        this.data[index].bought = !this.data[index].bought;
        this.saveData();
        this.render();
    },

    deleteItem(id) {
        if (confirm('Hapus barang ini?')) {
            this.data = this.data.filter(i => i.id !== id);
            this.saveData();
            this.render();
        }
    },

    editItem(id) {
        const item = this.data.find(i => i.id === id);
        this.openModal(item);
    },

    shareToWhatsApp() {
        const listItems = this.getCurrentItems();
        if (listItems.length === 0) return;

        let text = `*DAFTAR BELANJA - ${this.getCurrentList().name.toUpperCase()}*\n\n`;

        // Group by category for better readability
        const categories = [...new Set(listItems.map(i => i.category))];

        categories.forEach(cat => {
            const items = listItems.filter(i => i.category === cat);
            if (items.length > 0) {
                const icon = this.categoryIcons[cat] || '';
                text += `*_${icon} ${cat}_*\n`;
                items.forEach(item => {
                    const status = item.bought ? "✅ " : "⬜ ";
                    const priceInfo = item.price ? ` - Rp ${parseInt(item.price).toLocaleString('id-ID')}` : "";
                    const noteInfo = item.notes ? `\n     _Catatan: ${item.notes}_` : "";
                    text += `${status}${item.name} (${item.qty || '-'})${priceInfo}${noteInfo}\n`;
                });
                text += "\n";
            }
        });

        const total = listItems.reduce((sum, item) => sum + (parseInt(item.price) || 0), 0);
        if (total > 0) {
            text += `*Total Estimasi: Rp ${total.toLocaleString('id-ID')}*`;
        }

        const url = `https://wa.me/?text=${encodeURIComponent(text)}`;
        window.open(url, '_blank');
    },

    updateOnlineStatus() {
        if (navigator.onLine) {
            this.onlineStatus.textContent = 'Online';
            this.onlineStatus.classList.remove('offline');
            this.onlineStatus.classList.add('online');
        } else {
            this.onlineStatus.textContent = 'Offline (Lokal)';
            this.onlineStatus.classList.remove('online');
            this.onlineStatus.classList.add('offline');
        }
    },

    updateCalculations() {
        const price = parseFloat(this.calcPrice.value) || 0;
        const percent = parseFloat(this.calcPercent.value) || 0;

        if (price > 0 && percent > 0) {
            const savings = (price * percent) / 100;
            const final = price - savings;

            this.resSavings.textContent = `Rp ${Math.round(savings).toLocaleString('id-ID')}`;
            this.resFinal.textContent = `Rp ${Math.round(final).toLocaleString('id-ID')}`;
            this.calcResultBox.classList.remove('hidden');
        } else {
            this.calcResultBox.classList.add('hidden');
        }
    },

    appendNum(num) {
        if (this.stdExpression === '0' && num !== '.') {
            this.stdExpression = num;
        } else {
            this.stdExpression += num;
        }
        this.updateStdDisplay();
    },

    handleOp(op) {
        if (op === 'clear') {
            this.stdExpression = '';
        } else if (op === 'back') {
            this.stdExpression = this.stdExpression.slice(0, -1);
        } else if (op === '=') {
            try {
                // Simplified math eval
                // Warning: eval is generally risky, but for a localized simple calculator it suffices.
                // We sanitize by only allowing numbers and ops.
                const sanitized = this.stdExpression.replace(/[^-+/*0-9.]/g, '');
                this.stdExpression = eval(sanitized).toString();
            } catch (e) {
                this.stdExpression = 'Error';
            }
        } else {
            const lastChar = this.stdExpression.slice(-1);
            const ops = ['+', '-', '*', '/'];
            if (ops.includes(lastChar)) {
                this.stdExpression = this.stdExpression.slice(0, -1) + op;
            } else {
                this.stdExpression += op;
            }
        }
        this.updateStdDisplay();
    },

    updateStdDisplay() {
        this.stdDisplay.textContent = this.stdExpression || '0';
    },

    switchScreen(screen) {
        if (this.currentScreen === screen) return;
        this.currentScreen = screen;


        // Update Nav UI
        this.navItems.forEach(item => {
            if (item.dataset.screen === screen) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });

        // Hide any open modals (like Settings modal)
        document.querySelectorAll('.modal-overlay:not(.hidden)').forEach(modal => {
            modal.classList.add('hidden');
        });

        // Hide all screens first, then reveal the target one
        this.listMain.classList.add('hidden');
        this.shoppingFilters.classList.add('hidden');
        this.listSelectorBar.classList.add('hidden');
        this.favoritesBar.classList.add('hidden');
        this.shoppingSearch.classList.add('hidden');
        this.infoScreen.classList.add('hidden');
        this.notesScreen.classList.add('hidden');
        this.storageScreen.classList.add('hidden');
        this.pantryScreen.classList.add('hidden');
        this.historyScreen.classList.add('hidden');
        this.remindersScreen.classList.add('hidden');
        this.fab.classList.add('hidden');
        this.summaryFooter.classList.add('hidden');

        if (screen === 'list') {
            this.listMain.classList.remove('hidden');
            this.shoppingFilters.classList.remove('hidden');
            this.listSelectorBar.classList.remove('hidden');
            this.shoppingSearch.classList.remove('hidden');
            this.fab.classList.remove('hidden');
            this.renderFavorites();
            if (this.getCurrentItems().length > 0) this.summaryFooter.classList.remove('hidden');
        } else if (screen === 'info') {
            this.infoScreen.classList.remove('hidden');
            this.switchInfoTab(this.currentInfoTab);
        } else if (screen === 'notes') {
            this.notesScreen.classList.remove('hidden');
            this.fab.classList.remove('hidden'); // Enable FAB for notes
            this.renderNotes();
        } else if (screen === 'storage') {
            this.storageScreen.classList.remove('hidden');
            this.fab.classList.remove('hidden'); // Enable FAB for storage
            this.renderStorage();
        } else if (screen === 'pantry') {
            this.pantryScreen.classList.remove('hidden');
            this.fab.classList.remove('hidden'); // Enable FAB for pantry
            this.renderPantry();
        } else if (screen === 'history') {
            this.historyScreen.classList.remove('hidden');
            this.renderHistory();
        } else if (screen === 'reminders') {
            this.remindersScreen.classList.remove('hidden');
            this.fab.classList.remove('hidden'); // Enable FAB for reminders
            this.renderReminders();
        }
        lucide.createIcons();
    },

    switchInfoTab(tab) {
        this.currentInfoTab = tab;
        this.infoTabs.forEach(b => b.classList.toggle('active', b.dataset.tab === tab));

        // Hide all info panels, then reveal the active one
        this.pricesPanel.classList.add('hidden');
        this.kursPanel.classList.add('hidden');
        this.recipesPanel.classList.add('hidden');

        if (tab === 'recipes') {
            this.recipesPanel.classList.remove('hidden');
            this.fetchRecipes();
        } else if (tab === 'kurs') {
            this.kursPanel.classList.remove('hidden');
            this.fetchKurs();
        } else {
            this.pricesPanel.classList.remove('hidden');
            this.fetchPriceList();
        }
        lucide.createIcons();
    },

    async fetchPriceList() {
        // Show loading state
        this.priceItemsContainer.innerHTML = `
            <div class="loading-state">
                <div class="spinner"></div>
                <p>Mengambil data harga...</p>
            </div>
        `;

        try {
            await new Promise(resolve => setTimeout(resolve, 800)); // Simulate delay

            let data = [];

            if (this.currentPriceGroup === 'sembako') {
                data = [
                    { name: 'Beras Medium', price: 15500, change: -200, unit: 'kg' },
                    { name: 'Beras Premium', price: 17800, change: 0, unit: 'kg' },
                    { name: 'Bawang Merah', price: 34200, change: 1500, unit: 'kg' },
                    { name: 'Bawang Putih Honan', price: 38500, change: 200, unit: 'kg' },
                    { name: 'Cabai Merah Keriting', price: 42000, change: -500, unit: 'kg' },
                    { name: 'Cabai Rawit Merah', price: 58000, change: 2000, unit: 'kg' },
                    { name: 'Daging Sapi (Paha)', price: 135000, change: 0, unit: 'kg' },
                    { name: 'Daging Ayam Ras', price: 38000, change: -1000, unit: 'kg' },
                    { name: 'Telur Ayam Ras', price: 29500, change: 300, unit: 'kg' },
                    { name: 'Gula Pasir Konsumsi', price: 18000, change: 0, unit: 'kg' },
                    { name: 'Minyak Goreng Kita', price: 16500, change: 100, unit: 'lt' },
                    { name: 'Minyak Goreng Kemasan', price: 20500, change: 0, unit: 'lt' },
                    { name: 'Tepung Terigu', price: 13200, change: 0, unit: 'kg' },
                    { name: 'Kedelai Impor', price: 12500, change: -300, unit: 'kg' },
                    { name: 'Garam Halus', price: 12000, change: 0, unit: 'kg' }
                ];
            } else if (this.currentPriceGroup === 'logam') {
                data = [
                    { name: 'Emas Antam (1 gr)', price: 1425000, change: 12000, unit: 'gr' },
                    { name: 'Emas UBS (1 gr)', price: 1385000, change: 8000, unit: 'gr' },
                    { name: 'Spot Emas (Lokal)', price: 1345000, change: 5000, unit: 'gr' },
                    { name: 'Perak Antam', price: 18500, change: -200, unit: 'gr' },
                    { name: 'Perak Murni', price: 15200, change: 100, unit: 'gr' }
                ];
            } else if (this.currentPriceGroup === 'bbm') {
                data = [
                    { name: 'Pertalite', price: 10000, change: 0, unit: 'lt' },
                    { name: 'Pertamax', price: 12950, change: 0, unit: 'lt' },
                    { name: 'Pertamax Turbo', price: 14400, change: 0, unit: 'lt' },
                    { name: 'Dexlite', price: 14550, change: 0, unit: 'lt' },
                    { name: 'Pertamina Dex', price: 15100, change: 0, unit: 'lt' },
                    { name: 'Solar Subsidi', price: 6800, change: 0, unit: 'lt' }
                ];
            }

            this.renderPriceList(data);
        } catch (error) {
            this.priceItemsContainer.innerHTML = `<p class="error">Gagal mengambil data. Coba lagi nanti.</p>`;
        }
    },
    renderPriceList(prices) {
        this.priceItemsContainer.innerHTML = prices.map(item => {
            const changeClass = item.change > 0 ? 'up' : (item.change < 0 ? 'down' : '');
            const changeIcon = item.change > 0 ? '↗' : (item.change < 0 ? '↘' : '→');
            const changeText = item.change !== 0 ? `Rp ${Math.abs(item.change).toLocaleString('id-ID')}` : 'Tetap';

            return `
                <div class="price-card">
                    <div class="price-info">
                        <span class="item-name">${item.name}</span>
                        <span class="price-change ${changeClass}">${changeIcon} ${changeText}</span>
                    </div>
                    <div class="price-value">
                        Rp ${item.price.toLocaleString('id-ID')}<span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 400;"> /${item.unit}</span>
                    </div>
                </div>
            `;
        }).join('');
    },

    // Daftar mata uang yang ditampilkan pada tab Kurs
    currencies: [
        { code: 'USD', flag: '🇺🇸', name: 'Dolar Amerika' },
        { code: 'EUR', flag: '🇪🇺', name: 'Euro' },
        { code: 'SAR', flag: '🇸🇦', name: 'Riyal Arab Saudi' },
        { code: 'SGD', flag: '🇸🇬', name: 'Dolar Singapura' },
        { code: 'MYR', flag: '🇲🇾', name: 'Ringgit Malaysia' },
        { code: 'JPY', flag: '🇯🇵', name: 'Yen Jepang' },
        { code: 'GBP', flag: '🇬🇧', name: 'Poundsterling Inggris' },
        { code: 'AUD', flag: '🇦🇺', name: 'Dolar Australia' },
        { code: 'CNY', flag: '🇨🇳', name: 'Yuan Tiongkok' },
        { code: 'AED', flag: '🇦🇪', name: 'Dirham Uni Emirat Arab' },
        { code: 'THB', flag: '🇹🇭', name: 'Baht Thailand' },
        { code: 'KRW', flag: '🇰🇷', name: 'Won Korea Selatan' }
    ],

    async fetchKurs() {
        const cached = JSON.parse(localStorage.getItem('belanja_kurs') || 'null');

        this.kursContainer.innerHTML = `
            <div class="loading-state">
                <div class="spinner"></div>
                <p>Mengambil data kurs...</p>
            </div>
        `;

        try {
            // API gratis tanpa API-key, base IDR.
            // Param anti-cache agar service worker (cache-first) tidak menyajikan data basi.
            const res = await fetch(`https://open.er-api.com/v6/latest/IDR?_=${Date.now()}`);
            if (!res.ok) throw new Error('Network error');
            const json = await res.json();
            if (json.result !== 'success' || !json.rates) throw new Error('Invalid data');

            // rates[X] = jumlah X per 1 IDR -> 1 X = (1 / rates[X]) IDR
            const list = this.currencies
                .map(c => {
                    const rate = json.rates[c.code];
                    return rate ? { ...c, idr: 1 / rate } : null;
                })
                .filter(Boolean);

            const payload = {
                data: list,
                time: json.time_last_update_utc || new Date().toUTCString()
            };
            localStorage.setItem('belanja_kurs', JSON.stringify(payload));
            this.renderKurs(payload, false);
        } catch (error) {
            if (cached && cached.data && cached.data.length) {
                this.renderKurs(cached, true);
            } else {
                this.kursContainer.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-illustration"><i data-lucide="wifi-off"></i></div>
                        <h2>Gagal memuat kurs</h2>
                        <p>Sambungkan internet lalu buka kembali tab ini untuk memperbarui kurs.</p>
                    </div>
                `;
                lucide.createIcons();
            }
        }
    },

    formatKurs(value) {
        // Nilai besar dibulatkan; nilai kecil (mis. JPY, KRW) pakai 2 desimal
        const digits = value >= 100 ? 0 : 2;
        return value.toLocaleString('id-ID', { minimumFractionDigits: digits, maximumFractionDigits: digits });
    },

    renderKurs(payload, isCached) {
        // Simpan rate untuk konverter interaktif
        this.kursRates = {};
        payload.data.forEach(c => { this.kursRates[c.code] = c.idr; });
        this.populateKursConverter();

        let timeStr = '-';
        const parsed = new Date(payload.time);
        if (!isNaN(parsed)) {
            timeStr = parsed.toLocaleString('id-ID', {
                day: 'numeric', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
        }

        const cards = payload.data.map(c => `
            <div class="kurs-card">
                <span class="kurs-flag">${c.flag}</span>
                <div class="kurs-info">
                    <span class="kurs-code">${c.code}</span>
                    <span class="kurs-name">${c.name}</span>
                </div>
                <div class="kurs-value">
                    Rp ${this.formatKurs(c.idr)}
                    <span class="kurs-unit">/1 ${c.code}</span>
                </div>
            </div>
        `).join('');

        this.kursContainer.innerHTML = `
            <p class="kurs-note ${isCached ? 'cached' : ''}">
                ${isCached ? '⚠️ Data tersimpan (mode offline). ' : ''}Update: ${timeStr}
            </p>
            ${cards}
        `;
        lucide.createIcons();
    },

    async loadData() {
        const get = (k, def) => {
            try { const v = localStorage.getItem(k); return v ? JSON.parse(v) : def; }
            catch (e) { return def; }
        };

        // Load from localStorage first (instant, works offline)
        this.data      = get('belanja_data', []);
        this.notes     = get('belanja_notes', []);
        this.storage   = get('belanja_storage', []);
        this.favorites = get('belanja_favorites', []);
        this.history   = get('belanja_history', []);
        this.pantry    = get('belanja_pantry', []);
        this.reminders = get('belanja_reminders', []);
        this.parking   = get('belanja_parking', null);
        this.lists     = get('belanja_lists', []);
        this.currentListId = localStorage.getItem('belanja_current_list');
        this.budget = parseInt(localStorage.getItem('belanja_budget')) || 0;
        this.theme  = localStorage.getItem('belanja_theme') || 'dark';

        // Fetch from server — overrides localStorage with fresher cross-device data
        try {
            const res = await fetch('/belanja/sync', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (res.ok) {
                const srv = await res.json();
                console.log('[Belanja] Loaded from server:', Object.keys(srv));
                const sp = (v, def) => { try { return v ? JSON.parse(v) : def; } catch(e) { return def; } };
                if (srv.belanja_data         !== undefined) this.data      = sp(srv.belanja_data, this.data);
                if (srv.belanja_notes        !== undefined) this.notes     = sp(srv.belanja_notes, this.notes);
                if (srv.belanja_storage      !== undefined) this.storage   = sp(srv.belanja_storage, this.storage);
                if (srv.belanja_favorites    !== undefined) this.favorites = sp(srv.belanja_favorites, this.favorites);
                if (srv.belanja_history      !== undefined) this.history   = sp(srv.belanja_history, this.history);
                if (srv.belanja_pantry       !== undefined) this.pantry    = sp(srv.belanja_pantry, this.pantry);
                if (srv.belanja_reminders    !== undefined) this.reminders = sp(srv.belanja_reminders, this.reminders);
                if (srv.belanja_parking      !== undefined) this.parking   = sp(srv.belanja_parking, this.parking);
                if (srv.belanja_lists        !== undefined) this.lists     = sp(srv.belanja_lists, this.lists);
                if (srv.belanja_current_list)               this.currentListId = srv.belanja_current_list;
                // Mirror server data back to localStorage
                localStorage.setItem('belanja_data',          JSON.stringify(this.data));
                localStorage.setItem('belanja_notes',         JSON.stringify(this.notes));
                localStorage.setItem('belanja_storage',       JSON.stringify(this.storage));
                localStorage.setItem('belanja_favorites',     JSON.stringify(this.favorites));
                localStorage.setItem('belanja_history',       JSON.stringify(this.history));
                localStorage.setItem('belanja_pantry',        JSON.stringify(this.pantry));
                localStorage.setItem('belanja_reminders',     JSON.stringify(this.reminders));
                localStorage.setItem('belanja_lists',         JSON.stringify(this.lists));
                localStorage.setItem('belanja_current_list',  this.currentListId || '');
                localStorage.setItem('belanja_parking',       JSON.stringify(this.parking));
            }
        } catch (e) {
            console.warn('[Belanja] Sync load failed:', e.message);
        }

        // Daftar belanja (multiple lists) defaults
        if (!this.lists.length) {
            this.lists = [
                { id: 'pasar', name: 'Pasar' },
                { id: 'supermarket', name: 'Supermarket' },
                { id: 'warung', name: 'Warung' }
            ];
        }
        if (!this.currentListId || !this.lists.some(l => l.id === this.currentListId)) {
            this.currentListId = this.lists[0].id;
        }
        // Migrasi: item lama tanpa listId dimasukkan ke daftar pertama
        this.data.forEach(item => { if (!item.listId) item.listId = this.lists[0].id; });
    },

    saveData() {
        localStorage.setItem('belanja_data',          JSON.stringify(this.data));
        localStorage.setItem('belanja_notes',         JSON.stringify(this.notes));
        localStorage.setItem('belanja_storage',       JSON.stringify(this.storage));
        localStorage.setItem('belanja_favorites',     JSON.stringify(this.favorites));
        localStorage.setItem('belanja_history',       JSON.stringify(this.history));
        localStorage.setItem('belanja_pantry',        JSON.stringify(this.pantry));
        localStorage.setItem('belanja_reminders',     JSON.stringify(this.reminders));
        localStorage.setItem('belanja_lists',         JSON.stringify(this.lists));
        localStorage.setItem('belanja_current_list',  this.currentListId);
        localStorage.setItem('belanja_parking',       JSON.stringify(this.parking));
        this.syncToServer();
    },

    syncToServer() {
        if (!navigator.onLine) return;
        clearTimeout(this._syncTimer);
        this._syncTimer = setTimeout(() => {
            // Read current CSRF token from cookie (stays fresh across CSRF regeneration)
            const m = document.cookie.match(/csrf_cookie_name=([^;]+)/);
            const csrfToken = m ? decodeURIComponent(m[1]) : (window.DUITKU?.csrfToken || '');
            fetch('/belanja/sync', {
                method: 'POST',
                headers: {
                    'Content-Type':     'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':     csrfToken,
                },
                body: JSON.stringify({
                    belanja_data:         JSON.stringify(this.data),
                    belanja_notes:        JSON.stringify(this.notes),
                    belanja_storage:      JSON.stringify(this.storage),
                    belanja_favorites:    JSON.stringify(this.favorites),
                    belanja_history:      JSON.stringify(this.history),
                    belanja_pantry:       JSON.stringify(this.pantry),
                    belanja_reminders:    JSON.stringify(this.reminders),
                    belanja_lists:        JSON.stringify(this.lists),
                    belanja_current_list: this.currentListId,
                    belanja_parking:      JSON.stringify(this.parking),
                }),
            }).then(r => {
                if (r.ok) console.log('[Belanja] Synced to server ✓');
                else r.text().then(t => console.warn('[Belanja] Sync POST failed:', r.status, t.slice(0, 200)));
            }).catch(e => console.warn('[Belanja] Sync POST error:', e.message));
        }, 1500);
    },

    renderNotes() {
        if (this.notes.length === 0) {
            this.notesListContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-illustration">
                        <i data-lucide="notebook"></i>
                    </div>
                    <h2>Belum ada catatan</h2>
                    <p>Klik tombol + untuk menambah catatan baru.</p>
                </div>
            `;
            lucide.createIcons();
            return;
        }

        this.notesListContainer.innerHTML = this.notes.map(note => `
            <div class="note-card">
                <div class="note-card-header">
                    <span class="note-title">${note.title || 'Tanpa Judul'}</span>
                    <div class="note-actions">
                        <button class="icon-button" onclick="App.editNote('${note.id}')"><i data-lucide="edit-3"></i></button>
                        <button class="icon-button danger" onclick="App.deleteNote('${note.id}')"><i data-lucide="trash-2"></i></button>
                    </div>
                </div>
                ${note.image ? `<img src="${note.image}" class="note-image" alt="Foto">` : ''}
                <div class="note-content">${note.content}</div>
                <div class="note-footer">
                    <span>${new Date(parseInt(note.id)).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</span>
                </div>
            </div>
        `).join('');
        lucide.createIcons();
    },

    openNoteModal(note = null) {
        if (note) {
            document.getElementById('note-modal-title').textContent = 'Edit Catatan';
            document.getElementById('note-id').value = note.id;
            this.noteTitleInput.value = note.title || '';
            this.noteContentInput.value = note.content;

            if (note.image) {
                this.noteCurrentBase64 = note.image;
                this.notePreviewImg.src = note.image;
                this.noteImagePreview.classList.remove('hidden');
                this.btnCaptureNote.classList.add('hidden');
            } else {
                this.noteCurrentBase64 = null;
                this.noteImagePreview.classList.add('hidden');
                this.btnCaptureNote.classList.remove('hidden');
            }
        } else {
            document.getElementById('note-modal-title').textContent = 'Buat Catatan';
            this.noteForm.reset();
            document.getElementById('note-id').value = '';
            this.noteCurrentBase64 = null;
            this.noteImagePreview.classList.add('hidden');
            this.btnCaptureNote.classList.remove('hidden');
        }
        this.noteModal.classList.remove('hidden');
        this.noteContentInput.focus();
    },

    closeNoteModal() {
        this.noteModal.classList.add('hidden');
    },

    saveNote() {
        const id = document.getElementById('note-id').value;
        const title = this.noteTitleInput.value;
        const content = this.noteContentInput.value;
        const image = this.noteCurrentBase64;

        if (id) {
            const index = this.notes.findIndex(n => n.id === id);
            this.notes[index] = { ...this.notes[index], title, content, image };
        } else {
            this.notes.push({
                id: Date.now().toString(),
                title,
                content,
                image
            });
        }

        this.saveData();
        this.renderNotes();
        this.closeNoteModal();
    },

    deleteNote(id) {
        if (confirm('Hapus catatan ini?')) {
            this.notes = this.notes.filter(n => n.id !== id);
            this.saveData();
            this.renderNotes();
        }
    },

    editNote(id) {
        const note = this.notes.find(n => n.id === id);
        this.openNoteModal(note);
    },

    /* ============================================================
       Manajemen Penempatan Barang (Item Placement / Storage)
       ============================================================ */

    renderStorage() {
        const keyword = this.storageFilter || '';
        const filtered = keyword
            ? this.storage.filter(s =>
                (s.name || '').toLowerCase().includes(keyword) ||
                (s.location || '').toLowerCase().includes(keyword) ||
                (s.notes || '').toLowerCase().includes(keyword))
            : this.storage;

        if (this.storage.length === 0) {
            this.storageListContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-illustration">
                        <i data-lucide="map-pin"></i>
                    </div>
                    <h2>Belum ada simpanan</h2>
                    <p>Klik tombol + untuk mencatat di mana Anda menaruh barang.</p>
                </div>
            `;
            lucide.createIcons();
            return;
        }

        if (filtered.length === 0) {
            this.storageListContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-illustration"><i data-lucide="search-x"></i></div>
                    <h2>Tidak ditemukan</h2>
                    <p>Coba kata kunci lain.</p>
                </div>
            `;
            lucide.createIcons();
            return;
        }

        this.storageListContainer.innerHTML = filtered.map(s => {
            const date = new Date(parseInt(s.id)).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            const mapBtn = (s.lat && s.lng)
                ? `<button class="storage-map-link" onclick="App.openStorageMap('${s.id}')"><i data-lucide="navigation"></i> Lihat di Peta</button>`
                : '';
            return `
            <div class="storage-card">
                <div class="storage-card-header">
                    <div class="storage-title-wrap">
                        <span class="storage-name">${s.name}</span>
                        <span class="storage-location"><i data-lucide="map-pin"></i> ${s.location}</span>
                    </div>
                    <div class="storage-actions">
                        <button class="icon-button" onclick="App.editStorage('${s.id}')"><i data-lucide="edit-3"></i></button>
                        <button class="icon-button danger" onclick="App.deleteStorage('${s.id}')"><i data-lucide="trash-2"></i></button>
                    </div>
                </div>
                ${s.image ? `<img src="${s.image}" class="storage-image" alt="Foto barang">` : ''}
                ${s.notes ? `<div class="storage-notes">${s.notes}</div>` : ''}
                <div class="storage-footer">
                    <span><i data-lucide="clock"></i> ${date}</span>
                    ${mapBtn}
                </div>
            </div>`;
        }).join('');
        lucide.createIcons();
    },

    openStorageModal(item = null) {
        if (item) {
            document.getElementById('storage-modal-title').textContent = 'Edit Simpanan';
            document.getElementById('storage-id').value = item.id;
            this.storageNameInput.value = item.name || '';
            this.storageLocationInput.value = item.location || '';
            this.storageNotesInput.value = item.notes || '';

            if (item.image) {
                this.storageCurrentBase64 = item.image;
                this.storagePreviewImg.src = item.image;
                this.storageImagePreview.classList.remove('hidden');
                this.btnCaptureStorage.classList.add('hidden');
            } else {
                this.storageCurrentBase64 = null;
                this.storageImagePreview.classList.add('hidden');
                this.btnCaptureStorage.classList.remove('hidden');
            }

            if (item.lat && item.lng) {
                this.storageCurrentCoords = { lat: item.lat, lng: item.lng, accuracy: item.accuracy };
                this.showLocationPreview();
            } else {
                this.storageCurrentCoords = null;
                this.storageLocationPreview.classList.add('hidden');
                this.btnGetLocation.classList.remove('hidden');
            }
        } else {
            document.getElementById('storage-modal-title').textContent = 'Simpan Lokasi Barang';
            this.storageForm.reset();
            document.getElementById('storage-id').value = '';
            this.storageCurrentBase64 = null;
            this.storageCurrentCoords = null;
            this.storageImagePreview.classList.add('hidden');
            this.btnCaptureStorage.classList.remove('hidden');
            this.storageLocationPreview.classList.add('hidden');
            this.btnGetLocation.classList.remove('hidden');
        }
        this.storageModal.classList.remove('hidden');
        lucide.createIcons();
        this.storageNameInput.focus();
    },

    closeStorageModal() {
        this.storageModal.classList.add('hidden');
    },

    showLocationPreview() {
        const c = this.storageCurrentCoords;
        if (!c) return;
        const acc = c.accuracy ? ` (±${Math.round(c.accuracy)} m)` : '';
        this.storageLocationText.textContent = `${c.lat.toFixed(5)}, ${c.lng.toFixed(5)}${acc}`;
        this.storageLocationPreview.classList.remove('hidden');
        this.btnGetLocation.classList.add('hidden');
        lucide.createIcons();
    },

    getCurrentLocation() {
        if (!navigator.geolocation) {
            alert('Perangkat ini tidak mendukung GPS.');
            return;
        }
        const label = this.btnGetLocation.querySelector('span');
        const original = label.textContent;
        label.textContent = 'Mengambil lokasi...';
        this.btnGetLocation.disabled = true;

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                this.storageCurrentCoords = {
                    lat: pos.coords.latitude,
                    lng: pos.coords.longitude,
                    accuracy: pos.coords.accuracy
                };
                label.textContent = original;
                this.btnGetLocation.disabled = false;
                this.showLocationPreview();
            },
            (err) => {
                label.textContent = original;
                this.btnGetLocation.disabled = false;
                let msg = 'Gagal mengambil lokasi.';
                if (err.code === 1) msg = 'Izin lokasi ditolak. Aktifkan izin lokasi di pengaturan.';
                else if (err.code === 3) msg = 'Lokasi tidak ditemukan, coba lagi di tempat terbuka.';
                alert(msg);
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    },

    openStorageMap(id) {
        const s = this.storage.find(x => x.id === id);
        if (s && s.lat && s.lng) {
            window.open(`https://www.google.com/maps?q=${s.lat},${s.lng}`, '_blank');
        }
    },

    saveStorage() {
        const id = document.getElementById('storage-id').value;
        const name = this.storageNameInput.value.trim();
        const location = this.storageLocationInput.value.trim();
        const notes = this.storageNotesInput.value.trim();
        const image = this.storageCurrentBase64;
        const coords = this.storageCurrentCoords || {};

        const payload = {
            name,
            location,
            notes,
            image,
            lat: coords.lat || null,
            lng: coords.lng || null,
            accuracy: coords.accuracy || null
        };

        if (id) {
            const index = this.storage.findIndex(s => s.id === id);
            this.storage[index] = { ...this.storage[index], ...payload };
        } else {
            this.storage.unshift({ id: Date.now().toString(), ...payload });
        }

        this.saveData();
        this.renderStorage();
        this.closeStorageModal();
    },

    deleteStorage(id) {
        if (confirm('Hapus catatan simpanan ini?')) {
            this.storage = this.storage.filter(s => s.id !== id);
            this.saveData();
            this.renderStorage();
        }
    },

    editStorage(id) {
        const item = this.storage.find(s => s.id === id);
        this.openStorageModal(item);
    },

    // Pagination state
    recipePage: 1,
    recipesPerPage: 6,
    recipeFilteredData: [],

    // Local recipe database with emoji + gradient thumbnails
    allRecipes: [
        {
            key: 'nasi-goreng', emoji: '🍳', gradient: 'linear-gradient(135deg, #f5af19, #f12711)',
            title: 'Nasi Goreng Kampung', times: '15 Menit', difficulty: 'Sangat Mudah', category: 'Nasi',
            desc: 'Nasi goreng khas kampung dengan bumbu sederhana tapi kaya rasa, cocok untuk sarapan atau makan malam.',
            ingredient: ['2 piring nasi putih dingin', '3 siung bawang merah, iris', '2 siung bawang putih, iris', '3 buah cabai rawit', '2 sdm kecap manis', '1 sdm minyak goreng', '1 butir telur', 'Garam secukupnya', 'Daun bawang untuk taburan'],
            step: ['Panaskan minyak, tumis bawang merah dan putih hingga harum.', 'Masukkan cabai rawit, aduk sebentar.', 'Sisihkan tumisan, masak telur orak-arik.', 'Masukkan nasi, aduk rata dengan bumbu.', 'Tambahkan kecap manis dan garam, aduk hingga rata.', 'Sajikan dengan taburan daun bawang.']
        },
        {
            key: 'soto-ayam', emoji: '🍲', gradient: 'linear-gradient(135deg, #f7d794, #f19066)',
            title: 'Soto Ayam Lamongan', times: '45 Menit', difficulty: 'Mudah', category: 'Sup',
            desc: 'Soto ayam khas Lamongan dengan kuah kuning bening yang gurih dan segar.',
            ingredient: ['1 ekor ayam kampung, potong 4', '2 liter air', '3 batang serai, memarkan', '5 lembar daun jeruk', '2 cm kunyit', '3 cm jahe', '5 siung bawang merah', '3 siung bawang putih', 'Garam dan merica secukupnya', 'Soun, tauge, telur rebus untuk pelengkap'],
            step: ['Rebus ayam dengan air hingga empuk, angkat dan suwir dagingnya.', 'Haluskan kunyit, jahe, bawang merah, dan bawang putih.', 'Tumis bumbu halus hingga harum, masukkan serai dan daun jeruk.', 'Tuang tumisan ke kuah rebusan ayam.', 'Masak hingga mendidih, tambahkan garam dan merica.', 'Sajikan dengan soun, tauge, suwiran ayam, dan telur rebus.']
        },
        {
            key: 'rendang', emoji: '🥩', gradient: 'linear-gradient(135deg, #6D214F, #B33771)',
            title: 'Rendang Daging Padang', times: '3 Jam', difficulty: 'Sulit', category: 'Daging',
            desc: 'Rendang daging sapi khas Minangkabau, masakan terenak di dunia versi CNN.',
            ingredient: ['1 kg daging sapi, potong kotak', '1 liter santan kental', '5 batang serai', '10 lembar daun jeruk', 'Asam kandis 3 buah', 'Bumbu halus: 15 cabai merah, 10 bawang merah, 5 bawang putih, 3cm jahe, 3cm lengkuas, 2cm kunyit'],
            step: ['Haluskan semua bumbu.', 'Masak santan dengan bumbu halus dan rempah di wajan besar.', 'Masukkan daging potong, aduk rata.', 'Masak dengan api sedang sambil terus diaduk.', 'Setelah santan mengering, kecilkan api.', 'Aduk terus hingga bumbu mengental, berminyak, dan berwarna cokelat gelap.', 'Masak sekitar 3 jam hingga daging empuk dan bumbu meresap sempurna.']
        },
        {
            key: 'ayam-goreng-kremes', emoji: '🍗', gradient: 'linear-gradient(135deg, #e8b04b, #d4a017)',
            title: 'Ayam Goreng Kremes', times: '60 Menit', difficulty: 'Sedang', category: 'Ayam',
            desc: 'Ayam goreng dengan kremesan renyah yang gurih, favorit keluarga Indonesia.',
            ingredient: ['1 ekor ayam, potong 8-10 bagian', '1 liter air kelapa', '3 batang serai', '5 lembar daun salam', '3 cm lengkuas, memarkan', 'Bumbu halus: 8 bawang merah, 5 bawang putih, 3 kemiri, 2cm kunyit, 1sdt ketumbar', 'Minyak untuk menggoreng', 'Tepung beras 3 sdm untuk kremes'],
            step: ['Rebus ayam dengan air kelapa, bumbu halus, serai, salam, dan lengkuas hingga meresap.', 'Angkat ayam, tiriskan.', 'Goreng ayam hingga kuning keemasan.', 'Untuk kremes: campurkan sisa kuah rebusan dengan tepung beras.', 'Goreng adonan kremes dengan minyak panas sambil diaduk.', 'Sajikan ayam goreng dengan taburan kremes.']
        },
        {
            key: 'gado-gado', emoji: '🥗', gradient: 'linear-gradient(135deg, #78e08f, #38ada9)',
            title: 'Gado-Gado Jakarta', times: '30 Menit', difficulty: 'Mudah', category: 'Sayuran',
            desc: 'Gado-gado dengan bumbu kacang khas Betawi yang gurih dan sedikit manis.',
            ingredient: ['200g kacang tanah goreng', '5 cabai rawit', '3 siung bawang putih goreng', '2 sdm gula merah sisir', '1 sdm air asam jawa', 'Garam secukupnya', 'Tahu goreng, tempe goreng, lontong', 'Sayuran rebus: kangkung, bayam, kol, tauge, kacang panjang', 'Kerupuk dan telur rebus'],
            step: ['Haluskan kacang tanah goreng, cabai, dan bawang putih.', 'Tambahkan gula merah, air asam jawa, garam, dan air secukupnya.', 'Aduk hingga kekentalan pas.', 'Tata sayuran rebus, tahu, tempe, dan lontong di piring.', 'Siram dengan bumbu kacang.', 'Taburi kerupuk dan telur rebus di atasnya.']
        },
        {
            key: 'sop-buntut', emoji: '🍖', gradient: 'linear-gradient(135deg, #c8a2c8, #6c5ce7)',
            title: 'Sop Buntut Goreng', times: '90 Menit', difficulty: 'Sedang', category: 'Sup',
            desc: 'Sop buntut sapi yang gurih dengan kuah bening dan rempah yang harum.',
            ingredient: ['1 kg buntut sapi, potong', '2 liter air', '2 batang daun bawang', '2 buah tomat', '1 buah wortel, potong', '3 buah kentang, potong', 'Bawang goreng', 'Bumbu halus: bawang merah, bawang putih, merica, pala', 'Garam dan gula secukupnya'],
            step: ['Rebus buntut sapi dengan air hingga empuk (bisa pakai presto 45 menit).', 'Tumis bumbu halus hingga harum.', 'Masukkan tumisan bumbu ke kuah buntut.', 'Tambahkan wortel dan kentang, masak hingga empuk.', 'Bumbui dengan garam dan gula.', 'Sajikan dengan taburan bawang goreng, daun bawang, dan tomat.']
        },
        {
            key: 'bakso', emoji: '🍡', gradient: 'linear-gradient(135deg, #e17055, #d63031)',
            title: 'Bakso Sapi Kenyal', times: '60 Menit', difficulty: 'Sedang', category: 'Daging',
            desc: 'Bakso sapi homemade yang kenyal dengan kuah kaldu gurih.',
            ingredient: ['500g daging sapi giling', '100g tepung tapioka', '3 siung bawang putih halus', '1 sdt merica bubuk', '2 sdt garam', 'Es batu 100ml (untuk adonan kenyal)', 'Kuah: tulang sapi, daun bawang, seledri, bawang goreng'],
            step: ['Campurkan daging giling, tepung tapioka, bawang putih, merica, garam.', 'Tambahkan es batu sedikit-sedikit sambil diuleni hingga kalis.', 'Bentuk bulat-bulat menggunakan tangan.', 'Rebus dalam air mendidih hingga bakso mengapung.', 'Untuk kuah: rebus tulang sapi hingga kaldu keluar, saring.', 'Sajikan bakso dengan kuah kaldu, mie, bihun, dan taburan seledri.']
        },
        {
            key: 'pecel-lele', emoji: '🐟', gradient: 'linear-gradient(135deg, #fdcb6e, #e17055)',
            title: 'Pecel Lele Sambal Terasi', times: '30 Menit', difficulty: 'Mudah', category: 'Ikan',
            desc: 'Lele goreng renyah disajikan dengan sambal terasi yang pedas menggoda.',
            ingredient: ['4 ekor lele segar', '1 sdt kunyit bubuk', '1 sdt garam', 'Minyak goreng', 'Sambal: 10 cabai rawit, 5 cabai merah, 3 bawang merah, 2 bawang putih, terasi 1sdt, tomat 1 buah', 'Lalapan: timun, kemangi, kol'],
            step: ['Bersihkan lele, lumuri dengan kunyit dan garam, diamkan 15 menit.', 'Goreng lele dalam minyak panas hingga renyah keemasan.', 'Goreng cabai, bawang, tomat, dan terasi sebentar.', 'Ulek semua bahan sambal hingga kasar.', 'Sajikan lele goreng dengan sambal terasi dan lalapan segar.']
        },
        {
            key: 'mie-goreng', emoji: '🍜', gradient: 'linear-gradient(135deg, #f39c12, #e74c3c)',
            title: 'Mie Goreng Jawa', times: '20 Menit', difficulty: 'Mudah', category: 'Mie',
            desc: 'Mie goreng ala Jawa yang manis gurih dengan topping lengkap.',
            ingredient: ['2 bungkus mie telur, rebus', '3 siung bawang putih, cincang', '2 butir telur', '100g ayam fillet, potong dadu', '2 batang daun bawang, iris', '3 sdm kecap manis', '1 sdm saus tiram', 'Garam dan merica', 'Bawang goreng'],
            step: ['Tumis bawang putih hingga harum.', 'Masukkan ayam, masak hingga berubah warna.', 'Sisihkan ayam, masak telur orak-arik.', 'Masukkan mie rebus, aduk rata.', 'Tambahkan kecap manis, saus tiram, garam, merica.', 'Aduk hingga rata dan bumbu meresap.', 'Sajikan dengan taburan daun bawang dan bawang goreng.']
        },
        {
            key: 'sayur-asem', emoji: '🥒', gradient: 'linear-gradient(135deg, #00b894, #00cec9)',
            title: 'Sayur Asem Segar', times: '35 Menit', difficulty: 'Mudah', category: 'Sayuran',
            desc: 'Sayur asem khas Sunda dengan rasa segar asam manis yang menggugah selera.',
            ingredient: ['100g kacang tanah', '1 buah jagung manis, potong', '100g kacang panjang', '100g labu siam', '50g melinjo muda', 'Asam jawa 2 sdm', 'Gula merah 2 sdm', 'Bumbu: bawang merah, cabai hijau, terasi bakar', 'Garam secukupnya'],
            step: ['Rebus air, masukkan kacang tanah dan jagung terlebih dulu.', 'Setelah setengah matang, masukkan labu siam dan melinjo.', 'Tambahkan kacang panjang.', 'Masukkan bumbu halus, asam jawa, dan gula merah.', 'Masak hingga semua sayuran matang.', 'Koreksi rasa, sajikan hangat.']
        },
        {
            key: 'opor-ayam', emoji: '🍛', gradient: 'linear-gradient(135deg, #ffeaa7, #dfe6e9)',
            title: 'Opor Ayam Lebaran', times: '60 Menit', difficulty: 'Sedang', category: 'Ayam',
            desc: 'Opor ayam dengan santan gurih yang selalu hadir di meja makan saat Lebaran.',
            ingredient: ['1 ekor ayam, potong', '800ml santan', '3 batang serai', '5 lembar daun salam', '3 lembar daun jeruk', 'Bumbu halus: 8 bawang merah, 5 bawang putih, 5 kemiri, 2cm kunyit, 1cm jahe, 1sdt ketumbar', 'Garam dan gula secukupnya'],
            step: ['Tumis bumbu halus bersama serai, salam, dan daun jeruk.', 'Masukkan ayam, aduk hingga berubah warna.', 'Tuang santan, masak dengan api kecil.', 'Aduk sesekali agar santan tidak pecah.', 'Masak hingga ayam empuk dan kuah mengental.', 'Koreksi rasa, sajikan hangat dengan lontong atau ketupat.']
        },
        {
            key: 'tempe-mendoan', emoji: '🫘', gradient: 'linear-gradient(135deg, #a29bfe, #74b9ff)',
            title: 'Tempe Mendoan Crispy', times: '20 Menit', difficulty: 'Sangat Mudah', category: 'Gorengan',
            desc: 'Tempe mendoan khas Purwokerto yang tipis renyah dengan balutan tepung berbumbu.',
            ingredient: ['2 papan tempe, iris tipis', '100g tepung terigu', '50g tepung beras', '1 batang daun bawang, iris halus', '3 siung bawang putih halus', '1 sdt ketumbar bubuk', 'Garam secukupnya', 'Air secukupnya', 'Minyak untuk menggoreng'],
            step: ['Campurkan tepung terigu, tepung beras, bawang putih, ketumbar, garam.', 'Tambahkan daun bawang iris dan air secukupnya (adonan agak encer).', 'Celupkan irisan tempe ke dalam adonan.', 'Goreng dalam minyak panas hingga setengah kering (mendoan = setengah matang).', 'Angkat dan tiriskan.', 'Sajikan hangat dengan sambal kecap.']
        },
        {
            key: 'rawon', emoji: '🥘', gradient: 'linear-gradient(135deg, #2d3436, #636e72)',
            title: 'Rawon Surabaya', times: '90 Menit', difficulty: 'Sedang', category: 'Sup',
            desc: 'Rawon khas Surabaya dengan kuah hitam pekat dari kluwek yang khas.',
            ingredient: ['500g daging sapi, potong', '5 buah kluwek, ambil isinya', '2 batang serai', '5 lembar daun jeruk', '3 cm lengkuas', 'Bumbu halus: 8 bawang merah, 5 bawang putih, 3 kemiri, 2cm kunyit, 1cm jahe', 'Tauge, telur asin, sambal untuk pelengkap', 'Garam secukupnya'],
            step: ['Rebus daging sapi hingga empuk.', 'Haluskan kluwek bersama bumbu halus lainnya.', 'Tumis bumbu halus, serai, daun jeruk, dan lengkuas.', 'Masukkan tumisan bumbu ke kuah rebusan daging.', 'Masak hingga mendidih dan bumbu meresap.', 'Sajikan dengan tauge, telur asin, dan sambal.']
        },
        {
            key: 'gulai-kambing', emoji: '🐑', gradient: 'linear-gradient(135deg, #b8860b, #cd853f)',
            title: 'Gulai Kambing', times: '120 Menit', difficulty: 'Sulit', category: 'Daging',
            desc: 'Gulai kambing empuk dengan kuah santan kuning kental berempah.',
            ingredient: ['1 kg daging kambing, potong', '1 liter santan', '4 batang serai', '5 lembar daun jeruk', '2 lembar daun kunyit', 'Bumbu halus: 15 cabai merah, 10 bawang merah, 6 bawang putih, 3cm kunyit, 3cm jahe, 3cm lengkuas, 5 kemiri', 'Garam dan gula secukupnya'],
            step: ['Rebus daging kambing sebentar, buang air pertama (blanching).', 'Tumis bumbu halus bersama serai, daun jeruk, dan daun kunyit.', 'Masukkan daging kambing, aduk rata.', 'Tuang santan, masak dengan api kecil.', 'Aduk sesekali agar santan tidak pecah.', 'Masak 1.5-2 jam hingga daging empuk dan kuah mengental.']
        },
        {
            key: 'sambal-goreng-kentang', emoji: '🥔', gradient: 'linear-gradient(135deg, #e74c3c, #c0392b)',
            title: 'Sambal Goreng Kentang Ati', times: '40 Menit', difficulty: 'Mudah', category: 'Sayuran',
            desc: 'Kentang goreng dan ati ampela dalam bumbu sambal santan yang gurih pedas.',
            ingredient: ['500g kentang, potong dadu kecil, goreng', '200g ati ampela ayam, rebus dan potong', '200ml santan', '3 lembar daun salam', '2 batang serai', 'Bumbu halus: 8 cabai merah, 5 bawang merah, 3 bawang putih, 1cm lengkuas', 'Garam, gula, merica'],
            step: ['Goreng kentang hingga kuning, tiriskan.', 'Rebus ati ampela, potong-potong.', 'Tumis bumbu halus, salam, serai hingga harum.', 'Masukkan ati ampela, aduk rata.', 'Tuang santan, masak hingga mengental.', 'Masukkan kentang goreng, aduk hingga tercampur rata.', 'Koreksi rasa, sajikan.']
        },
        {
            key: 'pepes-ikan', emoji: '🐠', gradient: 'linear-gradient(135deg, #0abde3, #10ac84)',
            title: 'Pepes Ikan Mas', times: '45 Menit', difficulty: 'Sedang', category: 'Ikan',
            desc: 'Pepes ikan mas dibungkus daun pisang dengan bumbu rempah Sunda yang harum.',
            ingredient: ['2 ekor ikan mas segar', '5 lembar daun kemangi', '2 batang daun bawang', '2 buah tomat, iris', 'Bumbu halus: 8 cabai merah, 5 bawang merah, 3 bawang putih, 2cm kunyit, 1cm jahe', 'Daun pisang untuk membungkus', 'Garam secukupnya'],
            step: ['Bersihkan ikan mas, lumuri dengan bumbu halus.', 'Beri daun kemangi, daun bawang, dan irisan tomat.', 'Bungkus rapi dengan daun pisang, sematkan tusuk gigi.', 'Kukus selama 30 menit.', 'Bakar sebentar di atas arang untuk aroma lebih harum.', 'Sajikan hangat.']
        },
        {
            key: 'cap-cay', emoji: '🥦', gradient: 'linear-gradient(135deg, #27ae60, #2ecc71)',
            title: 'Cap Cay Goreng', times: '20 Menit', difficulty: 'Mudah', category: 'Sayuran',
            desc: 'Cap cay goreng dengan aneka sayuran segar dan bumbu sederhana.',
            ingredient: ['100g wortel, iris', '100g sawi putih, potong', '100g brokoli', '50g jamur kuping', '100g udang kupas', '3 siung bawang putih', '2 sdm saus tiram', '1 sdm maizena + air', 'Garam, gula, merica'],
            step: ['Tumis bawang putih hingga harum.', 'Masukkan udang, masak hingga berubah warna.', 'Masukkan wortel terlebih dulu, tumis sebentar.', 'Tambahkan brokoli, sawi putih, dan jamur.', 'Bumbui dengan saus tiram, garam, gula, merica.', 'Kentalkan dengan larutan maizena.', 'Aduk rata, sajikan hangat.']
        },
        {
            key: 'nasi-uduk', emoji: '🍚', gradient: 'linear-gradient(135deg, #dfe6e9, #b2bec3)',
            title: 'Nasi Uduk Betawi', times: '40 Menit', difficulty: 'Mudah', category: 'Nasi',
            desc: 'Nasi uduk khas Betawi yang pulen gurih dimasak dengan santan dan rempah.',
            ingredient: ['500g beras, cuci bersih', '400ml santan', '2 batang serai', '3 lembar daun salam', '2 lembar daun pandan', '1 sdt garam', 'Pelengkap: orek tempe, telur dadar, bihun goreng, sambal kacang, kerupuk'],
            step: ['Rendam beras selama 30 menit, tiriskan.', 'Campurkan beras dengan santan, serai, salam, pandan, dan garam.', 'Masak di rice cooker seperti biasa.', 'Setelah matang, aduk nasi perlahan.', 'Sajikan dengan orek tempe, telur dadar, bihun, dan sambal kacang.']
        },
        {
            key: 'ayam-bakar-kalasan', emoji: '🔥', gradient: 'linear-gradient(135deg, #d35400, #e67e22)',
            title: 'Ayam Bakar Kalasan', times: '75 Menit', difficulty: 'Sedang', category: 'Ayam',
            desc: 'Ayam bakar empuk dengan bumbu kelapa yang manis gurih khas Kalasan, Yogyakarta.',
            ingredient: ['1 ekor ayam kampung, potong 4', '500ml air kelapa muda', '200ml santan', 'Bumbu halus: 8 bawang merah, 5 bawang putih, 3 kemiri, 2cm kunyit, 1cm jahe, 1sdt ketumbar', '3 lembar daun salam', '2 batang serai', 'Gula merah 3 sdm', 'Garam secukupnya'],
            step: ['Rebus ayam dengan air kelapa, bumbu halus, salam, serai.', 'Masak hingga ayam empuk dan air menyusut.', 'Tambahkan santan dan gula merah, masak hingga mengental.', 'Angkat ayam, bakar di atas arang sambil dioles sisa bumbu.', 'Bakar hingga kecokelatan dan harum.', 'Sajikan dengan sambal dan lalapan.']
        },
        {
            key: 'es-cendol', emoji: '🧊', gradient: 'linear-gradient(135deg, #00b894, #55a3f0)',
            title: 'Es Cendol Dawet', times: '30 Menit', difficulty: 'Mudah', category: 'Minuman',
            desc: 'Es cendol dawet segar dengan santan gurih dan gula merah cair.',
            ingredient: ['100g tepung hunkue hijau', '500ml air', 'Es batu', 'Santan kental 400ml', '1/2 sdt garam untuk santan', 'Gula merah 200g', '200ml air untuk gula merah', 'Daun pandan 2 lembar'],
            step: ['Masak tepung hunkue dengan air dan pandan hingga mengental.', 'Cetak cendol menggunakan saringan ke dalam air es.', 'Buat santan: masak santan dengan sedikit garam.', 'Buat sirup gula merah: rebus gula merah dengan air hingga larut, saring.', 'Sajikan: masukkan cendol ke gelas, tuang santan dan gula merah cair.', 'Tambahkan es batu, siap dinikmati!']
        },
        {
            key: 'pempek', emoji: '🫓', gradient: 'linear-gradient(135deg, #fdcb6e, #e17055)',
            title: 'Pempek Palembang', times: '60 Menit', difficulty: 'Sedang', category: 'Ikan',
            desc: 'Pempek khas Palembang dari campuran ikan tenggiri dan sagu, disajikan dengan kuah cuko.',
            ingredient: ['500g ikan tenggiri giling', '250g tepung sagu/tapioka', '100ml air', '1 sdt garam', '2 siung bawang putih halus', 'Cuko: gula merah, asam jawa, cabai rawit, bawang putih, air, garam', 'Minyak untuk menggoreng'],
            step: ['Campurkan ikan giling, tepung sagu, air, garam, bawang putih.', 'Uleni hingga kalis dan bisa dibentuk.', 'Bentuk sesuai selera (lenjer, kapal selam, adaan).', 'Rebus dalam air mendidih hingga mengapung.', 'Setelah dingin, goreng hingga keemasan.', 'Buat cuko: rebus gula merah, air, asam jawa, cabai, bawang putih.', 'Sajikan pempek dengan kuah cuko dan timun iris.']
        },
        {
            key: 'gudeg', emoji: '🫙', gradient: 'linear-gradient(135deg, #cd853f, #a0522d)',
            title: 'Gudeg Jogja', times: '4 Jam', difficulty: 'Sulit', category: 'Sayuran',
            desc: 'Gudeg khas Yogyakarta dari nangka muda yang dimasak lama hingga manis legit.',
            ingredient: ['1 kg nangka muda, potong', '1 liter santan', '500ml air', 'Telur rebus 6 butir', 'Bumbu halus: 10 bawang merah, 5 bawang putih, 5 kemiri, 2cm lengkuas', 'Daun salam, daun jati (jika ada)', 'Gula merah 100g', 'Garam secukupnya'],
            step: ['Rebus nangka muda hingga setengah empuk.', 'Masak santan dengan bumbu halus, salam, dan gula merah.', 'Masukkan nangka muda dan telur rebus.', 'Masak dengan api sangat kecil selama 3-4 jam.', 'Aduk sesekali agar tidak gosong.', 'Masak hingga kuah menyusut dan nangka berwarna cokelat.', 'Sajikan dengan nasi, ayam, krecek, dan sambal goreng.']
        },
        {
            key: 'sate-ayam', emoji: '🍢', gradient: 'linear-gradient(135deg, #e67e22, #f39c12)',
            title: 'Sate Ayam Madura', times: '30 Menit', difficulty: 'Mudah', category: 'Ayam',
            desc: 'Sate ayam khas Madura dengan bumbu kacang yang manis dan gurih.',
            ingredient: ['500g daging ayam paha, potong dadu', 'Tusuk sate 30 buah', 'Kecap manis 3 sdm', 'Bumbu kacang: 200g kacang goreng, 5 cabai rawit, 3 bawang putih, gula merah, kecap manis, air', 'Bawang merah untuk pelengkap', 'Minyak untuk memanggang'],
            step: ['Tusuk daging ayam ke tusuk sate, 3-4 potong per tusuk.', 'Oles dengan kecap manis.', 'Panggang sate di atas arang sambil dibolak-balik dan dioles kecap.', 'Buat bumbu kacang: haluskan kacang, cabai, bawang putih.', 'Campurkan dengan gula merah, kecap, dan air secukupnya.', 'Sajikan sate dengan bumbu kacang dan irisan bawang merah.']
        },
        {
            key: 'tahu-telur', emoji: '🥚', gradient: 'linear-gradient(135deg, #ffeaa7, #fdcb6e)',
            title: 'Tahu Telur Surabaya', times: '25 Menit', difficulty: 'Mudah', category: 'Gorengan',
            desc: 'Tahu telur khas Surabaya dengan bumbu kacang petis yang khas.',
            ingredient: ['4 buah tahu putih, goreng dan potong dadu', '4 butir telur', 'Minyak goreng', 'Tauge rebus', 'Bumbu petis: kacang goreng, petis udang, bawang putih, cabai rawit, kecap manis, gula, air'],
            step: ['Goreng tahu hingga kuning, potong dadu.', 'Kocok telur, masukkan tahu ke dalamnya.', 'Goreng campuran tahu-telur dalam minyak panas.', 'Buat bumbu petis: haluskan kacang, cabai, bawang putih.', 'Campurkan dengan petis, kecap, gula, dan air.', 'Sajikan tahu telur dengan bumbu petis dan tauge rebus.']
        },
        {
            key: 'ikan-bakar-jimbaran', emoji: '🐟', gradient: 'linear-gradient(135deg, #2980b9, #3498db)',
            title: 'Ikan Bakar Jimbaran', times: '35 Menit', difficulty: 'Mudah', category: 'Ikan',
            desc: 'Ikan bakar ala pantai Jimbaran Bali dengan sambal matah yang segar.',
            ingredient: ['1 ekor ikan kakap/kerapu', '2 sdm air jeruk nipis', '1 sdt garam', '1 sdt kunyit bubuk', 'Sambal matah: 10 bawang merah iris, 5 cabai rawit iris, 2 batang serai iris, 1 lembar daun jeruk iris, minyak goreng panas, garam, gula'],
            step: ['Bersihkan ikan, kerat-kerat badannya.', 'Lumuri dengan jeruk nipis, garam, dan kunyit.', 'Diamkan 15 menit agar bumbu meresap.', 'Bakar ikan di atas arang hingga matang kecokelatan.', 'Buat sambal matah: campurkan semua bahan, siram minyak panas.', 'Sajikan ikan bakar dengan sambal matah di atasnya.']
        },
        {
            key: 'nasi-kuning', emoji: '🌾', gradient: 'linear-gradient(135deg, #f1c40f, #f39c12)',
            title: 'Nasi Kuning Tumpeng', times: '45 Menit', difficulty: 'Mudah', category: 'Nasi',
            desc: 'Nasi kuning khas untuk tumpeng dengan aroma pandan dan rempah.',
            ingredient: ['500g beras', '400ml santan', '100ml air', '3 cm kunyit, parut', '2 batang serai', '3 lembar daun salam', '2 lembar daun pandan', '1 sdt garam', 'Pelengkap: ayam goreng, perkedel, telur dadar iris, sambal goreng tempé'],
            step: ['Rendam beras 30 menit, tiriskan.', 'Campurkan santan, air kunyit, serai, salam, pandan, garam.', 'Masak beras dengan campuran santan di rice cooker.', 'Setelah matang, aduk perlahan.', 'Cetak nasi kuning di piring tumpeng.', 'Hias dengan lauk pelengkap di sekelilingnya.']
        },
        {
            key: 'perkedel-kentang', emoji: '🥔', gradient: 'linear-gradient(135deg, #e8b04b, #daa520)',
            title: 'Perkedel Kentang', times: '35 Menit', difficulty: 'Mudah', category: 'Gorengan',
            desc: 'Perkedel kentang yang lembut di dalam dan renyah di luar, cocok untuk lauk sehari-hari.',
            ingredient: ['500g kentang, rebus dan haluskan', '2 butir telur', '3 siung bawang merah goreng, haluskan', '2 siung bawang putih goreng, haluskan', '2 batang daun bawang, iris halus', '1/2 sdt merica', 'Garam secukupnya', 'Pala bubuk 1/4 sdt', 'Minyak untuk menggoreng'],
            step: ['Haluskan kentang rebus, campurkan dengan 1 telur.', 'Tambahkan bawang goreng halus, daun bawang, merica, garam, pala.', 'Uleni hingga bisa dibentuk.', 'Bentuk bulat pipih.', 'Celup ke kocokan telur.', 'Goreng hingga kuning keemasan, tiriskan.']
        },
        {
            key: 'ayam-geprek', emoji: '🌶️', gradient: 'linear-gradient(135deg, #e74c3c, #c0392b)',
            title: 'Ayam Geprek Sambal Bawang', times: '30 Menit', difficulty: 'Mudah', category: 'Ayam',
            desc: 'Ayam goreng tepung yang digeprek dengan sambal bawang yang super pedas.',
            ingredient: ['4 potong dada ayam fillet', '100g tepung terigu', '50g tepung maizena', '1 sdt bawang putih bubuk', 'Garam dan merica', '1 butir telur', 'Sambal: 15 cabai rawit, 5 bawang putih, 1 sdt garam, minyak goreng'],
            step: ['Campurkan tepung terigu, maizena, bawang putih bubuk, garam, merica.', 'Celupkan ayam ke telur, gulingkan ke campuran tepung.', 'Goreng ayam hingga kuning keemasan.', 'Ulek cabai rawit dan bawang putih dengan garam.', 'Geprek ayam goreng, lalu tuang sambal di atasnya.', 'Sajikan dengan nasi putih hangat.']
        },
        {
            key: 'kolak-pisang', emoji: '🍌', gradient: 'linear-gradient(135deg, #f9ca24, #f0932b)',
            title: 'Kolak Pisang', times: '25 Menit', difficulty: 'Sangat Mudah', category: 'Minuman',
            desc: 'Kolak pisang dengan kuah santan gula merah yang manis dan hangat, hidangan wajib saat bulan puasa.',
            ingredient: ['6 buah pisang kepok matang, potong-potong', '500ml santan', '150g gula merah sisir', '200ml air', '2 lembar daun pandan', '1/4 sdt garam', 'Ubi jalar 200g (opsional)'],
            step: ['Rebus air dengan gula merah dan pandan hingga gula larut.', 'Masukkan pisang (dan ubi jika pakai), masak 5 menit.', 'Tuang santan, tambahkan garam.', 'Masak dengan api kecil hingga mendidih, jangan diaduk terlalu sering.', 'Angkat, sajikan hangat atau dingin.']
        },
        {
            key: 'tumis-kangkung', emoji: '🥬', gradient: 'linear-gradient(135deg, #2ecc71, #27ae60)',
            title: 'Tumis Kangkung Terasi', times: '10 Menit', difficulty: 'Sangat Mudah', category: 'Sayuran',
            desc: 'Tumis kangkung dengan terasi dan cabai yang sederhana tapi selalu nikmat.',
            ingredient: ['2 ikat kangkung, petik daunnya', '3 siung bawang merah, iris', '2 siung bawang putih, iris', '5 cabai rawit', '1 buah tomat, potong', '1 sdt terasi bakar', '1 sdm minyak goreng', 'Garam dan gula secukupnya'],
            step: ['Panaskan minyak, tumis bawang merah dan putih.', 'Masukkan cabai rawit dan terasi, aduk hingga harum.', 'Masukkan tomat, aduk sebentar.', 'Masukkan kangkung, aduk rata dengan api besar.', 'Bumbui garam dan sedikit gula.', 'Masak sebentar saja agar kangkung tetap renyah, sajikan.']
        }
    ],

    fetchRecipes(query = '') {
        this.recipePage = 1;

        if (query) {
            this.recipeFilteredData = this.allRecipes.filter(r =>
                r.title.toLowerCase().includes(query.toLowerCase()) ||
                r.category.toLowerCase().includes(query.toLowerCase()) ||
                r.desc.toLowerCase().includes(query.toLowerCase())
            );
        } else {
            this.recipeFilteredData = [...this.allRecipes];
        }

        this.renderRecipesPage();
    },

    renderRecipesPage() {
        const data = this.recipeFilteredData;
        const totalPages = Math.ceil(data.length / this.recipesPerPage);
        const start = (this.recipePage - 1) * this.recipesPerPage;
        const pageData = data.slice(start, start + this.recipesPerPage);

        if (!data || data.length === 0) {
            this.recipesContainer.innerHTML = `
                <div class="empty-state" style="grid-column: 1/-1;">
                    <div class="empty-illustration"><i data-lucide="search-x"></i></div>
                    <h2>Tidak ada resep ditemukan</h2>
                    <p>Coba kata kunci lain seperti "ayam", "sayur", atau "ikan".</p>
                </div>`;
            lucide.createIcons();
            return;
        }

        let html = pageData.map(recipe => `
            <div class="recipe-card" onclick="App.viewRecipeDetail('${recipe.key}')">
                <div class="recipe-card-thumb" style="background: ${recipe.gradient};">
                    <span class="recipe-emoji">${recipe.emoji}</span>
                </div>
                <div class="recipe-card-info">
                    <h3>${recipe.title}</h3>
                    <div class="recipe-meta">
                        <span>⏱ ${recipe.times || '-'}</span>
                        <span>📊 ${recipe.difficulty || '-'}</span>
                    </div>
                </div>
            </div>
        `).join('');

        // Pagination controls
        if (totalPages > 1) {
            html += `
                <div class="recipe-pagination">
                    <button class="recipe-page-btn" onclick="App.changeRecipePage(-1)" ${this.recipePage <= 1 ? 'disabled' : ''}>
                        <i data-lucide="chevron-left"></i> Prev
                    </button>
                    <span class="recipe-page-info">${this.recipePage} / ${totalPages}</span>
                    <button class="recipe-page-btn" onclick="App.changeRecipePage(1)" ${this.recipePage >= totalPages ? 'disabled' : ''}>
                        Next <i data-lucide="chevron-right"></i>
                    </button>
                </div>
            `;
        }

        // Recipe count info
        html += `<p class="recipe-count-info">${data.length} resep ditemukan</p>`;

        this.recipesContainer.innerHTML = html;
        lucide.createIcons();
    },

    changeRecipePage(dir) {
        const totalPages = Math.ceil(this.recipeFilteredData.length / this.recipesPerPage);
        const newPage = this.recipePage + dir;
        if (newPage >= 1 && newPage <= totalPages) {
            this.recipePage = newPage;
            this.renderRecipesPage();
            // Scroll to top of recipes
            this.infoScreen.scrollTo({ top: 0, behavior: 'smooth' });
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    },

    viewRecipeDetail(key) {
        const recipe = this.allRecipes.find(r => r.key === key);
        if (recipe) {
            this.recipeModal.classList.remove('hidden');
            this.renderRecipeDetail(recipe);
        }
    },

    renderRecipeDetail(recipe) {
        document.getElementById('recipe-modal-title').textContent = recipe.title;
        this.recipeDetailContent.innerHTML = `
            <div class="recipe-detail-thumb" style="background: ${recipe.gradient};">
                <span class="recipe-detail-emoji">${recipe.emoji}</span>
                <div class="recipe-detail-badges">
                    <span class="recipe-badge">⏱ ${recipe.times}</span>
                    <span class="recipe-badge">📊 ${recipe.difficulty}</span>
                    ${recipe.category ? `<span class="recipe-badge">🏷 ${recipe.category}</span>` : ''}
                </div>
            </div>
            <p style="margin: 16px 0; font-style: italic; color: var(--text-muted); padding: 0 10px; line-height: 1.6;">${recipe.desc || ''}</p>

            <div class="recipe-section" style="padding: 0 10px;">
                <h4>🧾 Bahan-bahan</h4>
                <ul style="list-style: none; padding: 0;">
                    ${recipe.ingredient.map(ing => `<li style="margin-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 6px; padding-left: 8px;">• ${ing}</li>`).join('')}
                </ul>
                <button class="btn-primary" style="margin-top:10px;" onclick="App.addRecipeToList('${recipe.key}')">
                    <i data-lucide="shopping-cart" style="width:16px;height:16px;vertical-align:middle;margin-right:6px;"></i>
                    Tambah Bahan ke Daftar Belanja
                </button>
            </div>

            <div class="recipe-section" style="padding: 0 10px;">
                <h4>👩‍🍳 Langkah-langkah</h4>
                <ol style="padding-left: 20px;">
                    ${recipe.step.map(step => `<li style="margin-bottom: 12px; line-height: 1.5;">${step.replace(/^\d+\.\s*/, '')}</li>`).join('')}
                </ol>
            </div>
        `;
        lucide.createIcons();
    },

    closeRecipeModal() {
        this.recipeModal.classList.add('hidden');
    },

    /* ============================================================
       FITUR BARU
       ============================================================ */

    // ---------- Tema ----------
    applyTheme() {
        const light = this.theme === 'light';
        document.body.classList.toggle('light', light);
        const sw = document.getElementById('btn-toggle-theme');
        if (sw) sw.setAttribute('aria-checked', light ? 'true' : 'false');
        const meta = document.querySelector('meta[name="theme-color"]');
        if (meta) meta.setAttribute('content', light ? '#f4f6f8' : '#121212');
    },

    toggleTheme() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
        localStorage.setItem('belanja_theme', this.theme);
        this.applyTheme();
    },

    // ---------- Settings Modal ----------
    openSettings() {
        const budgetEl = document.getElementById('setting-budget');
        if (budgetEl) budgetEl.value = this.budget || '';
        const fontEl = document.getElementById('setting-fontscale');
        if (fontEl) fontEl.value = String(parseInt(localStorage.getItem('belanja_fontscale')) || 16);
        this.applyTheme();
        this.settingsModal.classList.remove('hidden');
        lucide.createIcons();
    },

    // ---------- Anggaran ----------
    saveBudget() {
        const val = parseInt(document.getElementById('setting-budget').value) || 0;
        this.budget = val;
        localStorage.setItem('belanja_budget', val);
        this.settingsModal.classList.add('hidden');
        this.render();
        alert(val > 0 ? `Anggaran diatur: ${this.formatRp(val)}` : 'Anggaran dihapus.');
    },

    // ---------- Backup & Restore ----------
    buildBackupPayload() {
        return {
            app: 'daftar-belanja-pasar',
            version: 1,
            exportedAt: new Date().toISOString(),
            data: this.data,
            notes: this.notes,
            storage: this.storage,
            favorites: this.favorites,
            history: this.history,
            pantry: this.pantry,
            reminders: this.reminders,
            lists: this.lists,
            currentListId: this.currentListId,
            budget: this.budget,
            theme: this.theme,
            parking: this.parking
        };
    },

    // Memuat payload backup ke dalam state aplikasi
    applyBackupPayload(p) {
        if (typeof p.parking !== 'undefined') this.parking = p.parking;
        this.data = p.data || [];
        this.notes = p.notes || [];
        this.storage = p.storage || [];
        this.favorites = p.favorites || [];
        this.history = p.history || [];
        this.pantry = p.pantry || [];
        this.reminders = p.reminders || [];
        if (p.lists && p.lists.length) this.lists = p.lists;
        if (p.currentListId) this.currentListId = p.currentListId;
        if (typeof p.budget !== 'undefined') {
            this.budget = parseInt(p.budget) || 0;
            localStorage.setItem('belanja_budget', this.budget);
        }
        if (p.theme) {
            this.theme = p.theme;
            localStorage.setItem('belanja_theme', this.theme);
        }
        this.saveData();
        this.applyTheme();
        this.renderListSelector();
        this.renderFavorites();
        this.render();
    },

    exportData() {
        const blob = new Blob([JSON.stringify(this.buildBackupPayload(), null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        const stamp = new Date().toISOString().slice(0, 10);
        a.href = url;
        a.download = `backup-belanja-${stamp}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        // Tandai sebagai sudah dicadangkan -> hentikan pengingat
        localStorage.setItem('belanja_last_backup', new Date().toISOString());
        this.hideBackupBanner();
    },

    importData(e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (ev) => {
            try {
                const p = JSON.parse(ev.target.result);
                if (!confirm('Pulihkan data dari file ini? Data saat ini akan ditimpa.')) return;
                this.applyBackupPayload(p);
                this.settingsModal.classList.add('hidden');
                alert('Data berhasil dipulihkan!');
            } catch (err) {
                alert('File tidak valid. Pastikan ini file backup dari aplikasi ini.');
            } finally {
                e.target.value = '';
            }
        };
        reader.readAsText(file);
    },

    // ---------- Auto-backup (snapshot internal + pengingat berkala) ----------
    autobackupDays() {
        const v = localStorage.getItem('belanja_autobackup_days');
        return v === null ? 3 : parseInt(v); // default: ingatkan tiap 3 hari
    },

    // Simpan snapshot internal (jaring pengaman bila salah hapus di dalam app)
    autoBackupSnapshot() {
        // Fitur backup lokal dinonaktifkan — data tersimpan di server
    },

    daysSince(iso) {
        if (!iso) return Infinity;
        const t = new Date(iso).getTime();
        if (isNaN(t)) return Infinity;
        return Math.floor((Date.now() - t) / (24 * 60 * 60 * 1000));
    },

    checkBackupReminder() {
        // Notifikasi backup dinonaktifkan — data tersimpan di server
    },

    hideBackupBanner() {
        const b = document.getElementById('backup-banner');
        if (b) b.classList.add('hidden');
    },

    dismissBackupBanner() {
        // tunda sampai besok
        localStorage.setItem('belanja_backup_snooze', new Date().toISOString().slice(0, 10));
        this.hideBackupBanner();
    },

    saveAutobackupInterval(val) {
        localStorage.setItem('belanja_autobackup_days', String(parseInt(val) || 0));
        localStorage.removeItem('belanja_backup_snooze');
        this.hideBackupBanner();
    },

    restoreAutoBackup() {
        const raw = localStorage.getItem('belanja_autobackup');
        if (!raw) { alert('Belum ada cadangan otomatis.'); return; }
        let p;
        try { p = JSON.parse(raw); } catch (e) { alert('Cadangan otomatis rusak.'); return; }
        const when = parseInt(localStorage.getItem('belanja_autobackup_time')) || 0;
        const whenStr = when ? new Date(when).toLocaleString('id-ID') : '-';
        if (!confirm(`Pulihkan cadangan otomatis (${whenStr})? Data saat ini akan ditimpa.`)) return;
        this.applyBackupPayload(p);
        this.settingsModal.classList.add('hidden');
        alert('Data dipulihkan dari cadangan otomatis.');
    },

    // ---------- Ukuran Huruf ----------
    applyFontScale() {
        const px = parseInt(localStorage.getItem('belanja_fontscale')) || 16;
        document.documentElement.style.fontSize = px + 'px';
    },

    saveFontScale(px) {
        localStorage.setItem('belanja_fontscale', String(parseInt(px) || 16));
        this.applyFontScale();
    },

    // ---------- Hitung Kembalian ----------
    updateChange() {
        const total = parseFloat(document.getElementById('change-total').value) || 0;
        const paid = parseFloat(document.getElementById('change-paid').value) || 0;
        const box = document.getElementById('change-result-box');
        const label = document.getElementById('change-label');
        const res = document.getElementById('change-result');
        if (total <= 0 || paid <= 0) { box.classList.add('hidden'); return; }
        const diff = paid - total;
        box.classList.remove('hidden');
        if (diff >= 0) {
            label.textContent = 'Kembalian:';
            res.textContent = this.formatRp(diff);
            res.className = 'text-primary';
        } else {
            label.textContent = 'Uang kurang:';
            res.textContent = this.formatRp(Math.abs(diff));
            res.className = 'text-danger';
        }
    },

    useListTotalForChange() {
        const total = this.getCurrentItems().reduce((s, i) => s + (parseInt(i.price) || 0), 0);
        document.getElementById('change-total').value = total || '';
        this.updateChange();
    },

    // ---------- Banding Harga per Satuan ----------
    updateCompare() {
        const aP = parseFloat(document.getElementById('cmp-a-price').value) || 0;
        const aQ = parseFloat(document.getElementById('cmp-a-qty').value) || 0;
        const bP = parseFloat(document.getElementById('cmp-b-price').value) || 0;
        const bQ = parseFloat(document.getElementById('cmp-b-qty').value) || 0;
        const box = document.getElementById('compare-result-box');
        if (aP <= 0 || aQ <= 0 || bP <= 0 || bQ <= 0) { box.classList.add('hidden'); return; }

        const unitA = aP / aQ;
        const unitB = bP / bQ;
        const fmt = (v) => 'Rp ' + v.toLocaleString('id-ID', { maximumFractionDigits: 2 });
        document.getElementById('cmp-a-unit').textContent = fmt(unitA) + ' /satuan';
        document.getElementById('cmp-b-unit').textContent = fmt(unitB) + ' /satuan';

        const winner = document.getElementById('cmp-winner');
        if (Math.abs(unitA - unitB) < 1e-9) {
            winner.textContent = 'Sama saja';
        } else {
            const cheaper = unitA < unitB ? 'A' : 'B';
            const hemat = Math.abs(unitA - unitB);
            const pct = Math.round((hemat / Math.max(unitA, unitB)) * 100);
            winner.textContent = `Produk ${cheaper} (hemat ${pct}%)`;
        }
        box.classList.remove('hidden');
    },

    // ---------- Lokasi Parkir ----------
    openParkingModal() {
        // Reset input foto sementara
        this.parkingBase64 = null;
        document.getElementById('parking-note').value = this.parking ? (this.parking.note || '') : '';
        document.getElementById('parking-image-input').value = '';
        document.getElementById('parking-image-preview').classList.add('hidden');
        document.getElementById('btn-capture-parking').classList.remove('hidden');
        this.renderParkingSaved();
        this.parkingModal.classList.remove('hidden');
        lucide.createIcons();
    },

    renderParkingSaved() {
        const box = document.getElementById('parking-saved');
        if (!this.parking || !this.parking.lat) {
            box.classList.add('hidden');
            return;
        }
        const d = new Date(this.parking.time);
        const timeStr = isNaN(d) ? '-' : d.toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
        const acc = this.parking.accuracy ? ` (±${Math.round(this.parking.accuracy)} m)` : '';
        document.getElementById('parking-saved-time').textContent = `Disimpan ${timeStr}${acc}`;
        const noteEl = document.getElementById('parking-saved-note');
        noteEl.textContent = this.parking.note || '';
        noteEl.style.display = this.parking.note ? 'block' : 'none';
        const img = document.getElementById('parking-saved-img');
        if (this.parking.image) { img.src = this.parking.image; img.classList.remove('hidden'); }
        else { img.classList.add('hidden'); }
        box.classList.remove('hidden');
        lucide.createIcons();
    },

    saveParkingNow() {
        if (!navigator.geolocation) { alert('Perangkat ini tidak mendukung GPS.'); return; }
        const btn = document.getElementById('btn-save-parking');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.textContent = 'Mengambil lokasi...';
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                this.parking = {
                    lat: pos.coords.latitude,
                    lng: pos.coords.longitude,
                    accuracy: pos.coords.accuracy,
                    note: document.getElementById('parking-note').value.trim(),
                    image: this.parkingBase64 || null,
                    time: new Date().toISOString()
                };
                this.saveData();
                btn.disabled = false;
                btn.innerHTML = original;
                this.renderParkingSaved();
                lucide.createIcons();
                alert('Lokasi parkir tersimpan!');
            },
            (err) => {
                btn.disabled = false;
                btn.innerHTML = original;
                lucide.createIcons();
                let msg = 'Gagal mengambil lokasi.';
                if (err.code === 1) msg = 'Izin lokasi ditolak. Aktifkan izin lokasi.';
                else if (err.code === 3) msg = 'Lokasi tidak ditemukan, coba lagi di tempat terbuka.';
                alert(msg);
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    },

    openParkingMap() {
        if (this.parking && this.parking.lat) {
            window.open(`https://www.google.com/maps?q=${this.parking.lat},${this.parking.lng}`, '_blank');
        }
    },

    deleteParking() {
        if (!confirm('Hapus lokasi parkir tersimpan?')) return;
        this.parking = null;
        this.parkingBase64 = null;
        this.saveData();
        document.getElementById('parking-note').value = '';
        document.getElementById('parking-image-preview').classList.add('hidden');
        document.getElementById('btn-capture-parking').classList.remove('hidden');
        this.renderParkingSaved();
    },

    // ---------- Daftar Belanja Ganda ----------
    renderListSelector() {
        if (!this.listSelect) return;
        this.listSelect.innerHTML = this.lists.map(l =>
            `<option value="${l.id}" ${l.id === this.currentListId ? 'selected' : ''}>${l.name}</option>`
        ).join('');
    },

    changeList(id) {
        this.currentListId = id;
        localStorage.setItem('belanja_current_list', id);
        this.filter = 'all';
        this.categoryPills.forEach(p => p.classList.toggle('active', p.dataset.category === 'all'));
        this.renderFavorites();
        this.render();
    },

    addList() {
        const name = (prompt('Nama daftar baru:') || '').trim();
        if (!name) return;
        const id = 'list_' + Date.now();
        this.lists.push({ id, name });
        this.currentListId = id;
        this.saveData();
        this.renderListSelector();
        this.render();
    },

    renameList() {
        const cur = this.getCurrentList();
        const name = (prompt('Ubah nama daftar:', cur.name) || '').trim();
        if (!name) return;
        cur.name = name;
        this.saveData();
        this.renderListSelector();
    },

    deleteList() {
        if (this.lists.length <= 1) {
            alert('Minimal harus ada satu daftar.');
            return;
        }
        const cur = this.getCurrentList();
        if (!confirm(`Hapus daftar "${cur.name}" beserta semua barangnya?`)) return;
        this.data = this.data.filter(i => i.listId !== cur.id);
        this.lists = this.lists.filter(l => l.id !== cur.id);
        this.currentListId = this.lists[0].id;
        this.saveData();
        this.renderListSelector();
        this.renderFavorites();
        this.render();
    },

    // ---------- Favorit / Sering Dibeli ----------
    renderFavorites() {
        if (!this.favoritesBar) return;
        if (!this.favorites.length || this.currentScreen !== 'list') {
            this.favoritesBar.classList.add('hidden');
            return;
        }
        this.favoritesBar.classList.remove('hidden');
        this.favoritesChips.innerHTML = this.favorites.map((f, i) =>
            `<button class="fav-chip" onclick="App.addFavoriteToList(${i})">+ ${f.name}</button>`
        ).join('');
    },

    addFavoriteToList(index) {
        const f = this.favorites[index];
        if (!f) return;
        this.data.push({
            id: Date.now().toString(),
            name: f.name,
            qty: '',
            price: '',
            category: f.category || 'Lainnya',
            notes: '',
            image: null,
            bought: false,
            listId: this.currentListId
        });
        this.saveData();
        this.render();
    },

    toggleFavorite(id) {
        const item = this.data.find(i => i.id === id);
        if (!item) return;
        const idx = this.favorites.findIndex(f => f.name.toLowerCase() === item.name.toLowerCase());
        if (idx > -1) {
            this.favorites.splice(idx, 1);
        } else {
            this.favorites.push({ name: item.name, category: item.category });
        }
        this.saveData();
        this.renderFavorites();
        this.render();
    },

    // ---------- Input Suara ----------
    startVoice() {
        const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SR) {
            alert('Maaf, input suara tidak didukung di browser ini. Coba pakai Google Chrome.');
            return;
        }
        if (!this.recog) {
            this.recog = new SR();
            this.recog.lang = 'id-ID';
            this.recog.interimResults = false;
            this.recog.maxAlternatives = 1;
            this.recog.onresult = (e) => {
                const text = e.results[0][0].transcript;
                const input = document.getElementById('item-name');
                input.value = text.charAt(0).toUpperCase() + text.slice(1);
            };
            this.recog.onend = () => this.btnVoice.classList.remove('listening');
            this.recog.onerror = () => this.btnVoice.classList.remove('listening');
        }
        try {
            this.btnVoice.classList.add('listening');
            this.recog.start();
        } catch (err) {
            this.btnVoice.classList.remove('listening');
        }
    },

    // ---------- Selesai Belanja & Riwayat ----------
    finishShopping() {
        const items = this.getCurrentItems();
        if (!items.length) return;
        const total = items.reduce((s, i) => s + (parseInt(i.price) || 0), 0);
        if (!confirm(`Selesaikan belanja "${this.getCurrentList().name}" dan simpan ke riwayat?\nTotal: ${this.formatRp(total)}`)) return;

        this.history.unshift({
            id: Date.now().toString(),
            date: new Date().toISOString(),
            listName: this.getCurrentList().name,
            total: total,
            items: items.map(i => ({ name: i.name, qty: i.qty, price: i.price, category: i.category, bought: i.bought }))
        });
        // Kosongkan daftar aktif
        this.data = this.data.filter(i => i.listId !== this.currentListId);
        this.saveData();
        this.render();
        alert('Belanja disimpan ke Riwayat. Daftar dikosongkan.');
    },

    renderHistory() {
        // Ringkasan bulan ini
        const now = new Date();
        const thisMonth = this.history.filter(h => {
            const d = new Date(h.date);
            return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
        });
        const monthTotal = thisMonth.reduce((s, h) => s + (h.total || 0), 0);
        this.historySummary.innerHTML = `
            <div class="hs-label">Total belanja bulan ini (${now.toLocaleString('id-ID', { month: 'long', year: 'numeric' })})</div>
            <div class="hs-value">${this.formatRp(monthTotal)}</div>
            <div class="hs-label">${thisMonth.length} kali belanja</div>
        `;

        // Grafik 6 transaksi terakhir (terlama -> terbaru)
        const recent = this.history.slice(0, 6).reverse();
        if (recent.length) {
            const max = Math.max(...recent.map(h => h.total || 0), 1);
            this.historyChart.classList.remove('hidden');
            this.historyChart.innerHTML = recent.map(h => {
                const pct = Math.round(((h.total || 0) / max) * 100);
                const d = new Date(h.date);
                return `
                    <div class="chart-col">
                        <div class="chart-bar-track"><div class="chart-bar" style="height:${pct}%"></div></div>
                        <div class="chart-label">${d.getDate()}/${d.getMonth() + 1}</div>
                    </div>`;
            }).join('');
        } else {
            this.historyChart.classList.add('hidden');
        }

        // Daftar riwayat
        if (!this.history.length) {
            this.historyListContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-illustration"><i data-lucide="history"></i></div>
                    <h2>Belum ada riwayat</h2>
                    <p>Selesaikan belanja dengan tombol ✔ untuk menyimpan ke riwayat.</p>
                </div>`;
            lucide.createIcons();
            return;
        }
        this.historyListContainer.innerHTML = this.history.map(h => {
            const d = new Date(h.date);
            const dateStr = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            const names = h.items.map(i => i.name).slice(0, 5).join(', ');
            return `
                <div class="history-card">
                    <div class="history-card-top">
                        <span class="history-card-title">${h.listName || 'Belanja'}</span>
                        <span class="history-card-total">${this.formatRp(h.total)}</span>
                    </div>
                    <div class="history-card-date">${dateStr} • ${h.items.length} barang</div>
                    <div class="history-card-items">${names}${h.items.length > 5 ? ', ...' : ''}</div>
                    <div class="history-card-actions">
                        <button class="settings-link" style="padding:8px 10px;" onclick="App.reuseHistory('${h.id}')"><i data-lucide="rotate-ccw"></i> Belanja Lagi</button>
                        <button class="icon-button danger" onclick="App.deleteHistoryEntry('${h.id}')"><i data-lucide="trash-2"></i></button>
                    </div>
                </div>`;
        }).join('');
        lucide.createIcons();
    },

    deleteHistoryEntry(id) {
        if (!confirm('Hapus riwayat ini?')) return;
        this.history = this.history.filter(h => h.id !== id);
        this.saveData();
        this.renderHistory();
    },

    reuseHistory(id) {
        const h = this.history.find(x => x.id === id);
        if (!h) return;
        if (!confirm(`Salin ${h.items.length} barang ke daftar "${this.getCurrentList().name}"?`)) return;
        h.items.forEach((it, i) => {
            this.data.push({
                id: (Date.now() + i).toString(),
                name: it.name, qty: it.qty, price: it.price,
                category: it.category, notes: '', image: null,
                bought: false, listId: this.currentListId
            });
        });
        this.saveData();
        this.switchScreen('list');
    },

    // ---------- Stok Dapur (Pantry) ----------
    renderPantry() {
        const kw = this.pantryFilter || '';
        const filtered = kw
            ? this.pantry.filter(p => (p.name || '').toLowerCase().includes(kw))
            : this.pantry;

        if (!this.pantry.length) {
            this.pantryListContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-illustration"><i data-lucide="refrigerator"></i></div>
                    <h2>Belum ada stok</h2>
                    <p>Klik + untuk mencatat persediaan dapur Anda.</p>
                </div>`;
            lucide.createIcons();
            return;
        }
        // Urutkan: habis & menipis di atas
        const order = { habis: 0, menipis: 1, cukup: 2 };
        const sorted = [...filtered].sort((a, b) => (order[a.status] ?? 3) - (order[b.status] ?? 3));
        const statusLabel = { cukup: 'Cukup', menipis: 'Menipis', habis: 'Habis' };

        this.pantryListContainer.innerHTML = sorted.map(p => `
            <div class="pantry-card ${p.status}">
                <span class="pantry-emoji">${this.categoryIcons[p.category] || '📦'}</span>
                <div class="pantry-info">
                    <span class="pantry-name">${p.name}</span>
                    <span class="pantry-meta">${p.qty || ''} ${p.qty ? '•' : ''} ${p.category || ''}</span>
                    <span class="pantry-status-badge ${p.status}">${statusLabel[p.status] || p.status}</span>
                </div>
                <div class="pantry-actions">
                    ${p.status !== 'cukup' ? `<button class="icon-button info" title="Tambah ke belanja" onclick="App.pantryToShopping('${p.id}')"><i data-lucide="shopping-cart"></i></button>` : ''}
                    <button class="icon-button" onclick="App.editPantry('${p.id}')"><i data-lucide="edit-3"></i></button>
                    <button class="icon-button danger" onclick="App.deletePantry('${p.id}')"><i data-lucide="trash-2"></i></button>
                </div>
            </div>
        `).join('');
        lucide.createIcons();
    },

    openPantryModal(item = null) {
        if (item) {
            document.getElementById('pantry-modal-title').textContent = 'Edit Stok';
            document.getElementById('pantry-id').value = item.id;
            document.getElementById('pantry-name').value = item.name || '';
            document.getElementById('pantry-qty').value = item.qty || '';
            document.getElementById('pantry-category').value = item.category || 'Lainnya';
            document.getElementById('pantry-status').value = item.status || 'cukup';
        } else {
            document.getElementById('pantry-modal-title').textContent = 'Tambah Stok';
            this.pantryForm.reset();
            document.getElementById('pantry-id').value = '';
        }
        this.pantryModal.classList.remove('hidden');
        document.getElementById('pantry-name').focus();
    },

    savePantry() {
        const id = document.getElementById('pantry-id').value;
        const payload = {
            name: document.getElementById('pantry-name').value.trim(),
            qty: document.getElementById('pantry-qty').value.trim(),
            category: document.getElementById('pantry-category').value,
            status: document.getElementById('pantry-status').value
        };
        if (id) {
            const idx = this.pantry.findIndex(p => p.id === id);
            this.pantry[idx] = { ...this.pantry[idx], ...payload };
        } else {
            this.pantry.unshift({ id: Date.now().toString(), ...payload });
        }
        this.saveData();
        this.renderPantry();
        this.pantryModal.classList.add('hidden');
    },

    editPantry(id) {
        this.openPantryModal(this.pantry.find(p => p.id === id));
    },

    deletePantry(id) {
        if (!confirm('Hapus stok ini?')) return;
        this.pantry = this.pantry.filter(p => p.id !== id);
        this.saveData();
        this.renderPantry();
    },

    pantryToShopping(id) {
        const p = this.pantry.find(x => x.id === id);
        if (!p) return;
        // Hindari duplikat di daftar aktif
        const exists = this.getCurrentItems().some(i => i.name.toLowerCase() === p.name.toLowerCase());
        if (exists) {
            alert(`"${p.name}" sudah ada di daftar belanja "${this.getCurrentList().name}".`);
            return;
        }
        this.data.push({
            id: Date.now().toString(),
            name: p.name, qty: '', price: '',
            category: p.category || 'Lainnya', notes: '', image: null,
            bought: false, listId: this.currentListId
        });
        this.saveData();
        alert(`"${p.name}" ditambahkan ke daftar belanja "${this.getCurrentList().name}".`);
    },

    // ---------- Pengingat ----------
    renderReminders() {
        if (!this.reminders.length) {
            this.remindersListContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-illustration"><i data-lucide="bell"></i></div>
                    <h2>Belum ada pengingat</h2>
                    <p>Klik + untuk menambah pengingat tagihan atau jadwal.</p>
                </div>`;
            lucide.createIcons();
            return;
        }
        const todayStr = new Date().toISOString().slice(0, 10);
        const sorted = [...this.reminders].sort((a, b) => (a.date || '').localeCompare(b.date || ''));
        this.remindersListContainer.innerHTML = sorted.map(r => {
            const isDue = !r.done && r.date && r.date <= todayStr;
            const d = r.date ? new Date(r.date + 'T00:00:00') : null;
            const dateStr = d ? d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) : '-';
            return `
                <div class="reminder-card ${r.done ? 'done' : ''} ${isDue ? 'due' : ''}">
                    <div class="reminder-check" onclick="App.toggleReminder('${r.id}')"><i data-lucide="check"></i></div>
                    <div class="reminder-info">
                        <span class="reminder-title">${r.title}</span>
                        <span class="reminder-meta ${isDue ? 'due' : ''}">${isDue ? '⏰ ' : ''}${dateStr}</span>
                        ${r.note ? `<span class="reminder-meta">${r.note}</span>` : ''}
                    </div>
                    <div class="reminder-actions">
                        <button class="icon-button" onclick="App.editReminder('${r.id}')"><i data-lucide="edit-3"></i></button>
                        <button class="icon-button danger" onclick="App.deleteReminder('${r.id}')"><i data-lucide="trash-2"></i></button>
                    </div>
                </div>`;
        }).join('');
        lucide.createIcons();
    },

    openReminderModal(item = null) {
        if (item) {
            document.getElementById('reminder-modal-title').textContent = 'Edit Pengingat';
            document.getElementById('reminder-id').value = item.id;
            document.getElementById('reminder-title').value = item.title || '';
            document.getElementById('reminder-date').value = item.date || '';
            document.getElementById('reminder-note').value = item.note || '';
        } else {
            document.getElementById('reminder-modal-title').textContent = 'Tambah Pengingat';
            this.reminderForm.reset();
            document.getElementById('reminder-id').value = '';
            document.getElementById('reminder-date').value = new Date().toISOString().slice(0, 10);
        }
        this.reminderModal.classList.remove('hidden');
    },

    saveReminder() {
        const id = document.getElementById('reminder-id').value;
        const payload = {
            title: document.getElementById('reminder-title').value.trim(),
            date: document.getElementById('reminder-date').value,
            note: document.getElementById('reminder-note').value.trim()
        };
        if (id) {
            const idx = this.reminders.findIndex(r => r.id === id);
            this.reminders[idx] = { ...this.reminders[idx], ...payload };
        } else {
            this.reminders.push({ id: Date.now().toString(), done: false, ...payload });
            this.requestNotifyPermission();
        }
        this.saveData();
        this.renderReminders();
        this.reminderModal.classList.add('hidden');
    },

    editReminder(id) {
        this.openReminderModal(this.reminders.find(r => r.id === id));
    },

    deleteReminder(id) {
        if (!confirm('Hapus pengingat ini?')) return;
        this.reminders = this.reminders.filter(r => r.id !== id);
        this.saveData();
        this.renderReminders();
    },

    toggleReminder(id) {
        const r = this.reminders.find(x => x.id === id);
        if (!r) return;
        r.done = !r.done;
        this.saveData();
        this.renderReminders();
    },

    requestNotifyPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    },

    checkDueReminders() {
        const todayStr = new Date().toISOString().slice(0, 10);
        const due = this.reminders.filter(r => !r.done && r.date && r.date <= todayStr);
        if (!due.length) return;
        const titles = due.map(r => '• ' + r.title).join('\n');
        // Notifikasi sistem bila diizinkan
        if ('Notification' in window && Notification.permission === 'granted') {
            try { new Notification('Pengingat Hari Ini', { body: due.map(r => r.title).join(', '), icon: 'logo.png' }); } catch (e) {}
        }
        // Selalu tampilkan info ringan dalam app
        setTimeout(() => {
            alert('⏰ Pengingat jatuh tempo:\n\n' + titles);
        }, 1200);
    },

    // ---------- Konverter Kurs ----------
    populateKursConverter() {
        if (!this.kursConvCurrency || !this.kursRates) return;
        const prev = this.kursConvCurrency.value;
        this.kursConvCurrency.innerHTML = this.currencies
            .filter(c => this.kursRates[c.code])
            .map(c => `<option value="${c.code}">${c.flag} ${c.code}</option>`)
            .join('');
        if (prev && this.kursRates[prev]) this.kursConvCurrency.value = prev;
        this.updateKursConvert();
    },

    updateKursConvert() {
        if (!this.kursRates) return;
        const amount = parseFloat(this.kursConvAmount.value) || 0;
        const code = this.kursConvCurrency.value;
        const rate = this.kursRates[code];
        if (!rate) { this.kursConvOutput.textContent = 'Rp 0'; return; }
        const result = amount * rate;
        this.kursConvOutput.textContent = 'Rp ' + result.toLocaleString('id-ID', { maximumFractionDigits: 0 });
    },

    // ---------- Resep -> Belanja ----------
    addRecipeToList(key) {
        const recipe = this.allRecipes.find(r => r.key === key);
        if (!recipe) return;
        if (!confirm(`Tambahkan ${recipe.ingredient.length} bahan "${recipe.title}" ke daftar "${this.getCurrentList().name}"?`)) return;
        recipe.ingredient.forEach((ing, i) => {
            this.data.push({
                id: (Date.now() + i).toString(),
                name: ing, qty: '', price: '',
                category: 'Lainnya', notes: `Resep: ${recipe.title}`, image: null,
                bought: false, listId: this.currentListId
            });
        });
        this.saveData();
        this.closeRecipeModal();
        this.switchScreen('list');
        alert('Bahan ditambahkan ke daftar belanja!');
    },

    // ---------- Cetak / PDF ----------
    printList() {
        const items = this.getCurrentItems();
        if (!items.length) { alert('Daftar kosong.'); return; }
        const total = items.reduce((s, i) => s + (parseInt(i.price) || 0), 0);
        const dateStr = new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        const rows = items.map(i => `
            <tr>
                <td style="padding:6px 4px;border-bottom:1px solid #ddd;">${i.bought ? '☑' : '☐'} ${i.name}</td>
                <td style="padding:6px 4px;border-bottom:1px solid #ddd;">${i.qty || '-'}</td>
                <td style="padding:6px 4px;border-bottom:1px solid #ddd;text-align:right;">${i.price ? this.formatRp(i.price) : '-'}</td>
            </tr>`).join('');
        document.getElementById('print-area').innerHTML = `
            <h2 style="margin:0 0 4px;">Daftar Belanja - ${this.getCurrentList().name}</h2>
            <p style="margin:0 0 14px;color:#555;">${dateStr}</p>
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead><tr>
                    <th style="text-align:left;border-bottom:2px solid #333;padding:6px 4px;">Barang</th>
                    <th style="text-align:left;border-bottom:2px solid #333;padding:6px 4px;">Jumlah</th>
                    <th style="text-align:right;border-bottom:2px solid #333;padding:6px 4px;">Harga</th>
                </tr></thead>
                <tbody>${rows}</tbody>
            </table>
            <h3 style="text-align:right;margin-top:14px;">Total: ${this.formatRp(total)}</h3>
        `;
        window.print();
    }
};

// Start the app
document.addEventListener('DOMContentLoaded', () => {
    App.init();
    window.showInstallApp = () => App.installModal.classList.remove('hidden');
});
