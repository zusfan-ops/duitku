// DuitKu Chrome Extension - Popup Logic

const DEFAULT_SERVER_URL = "https://duitku.ordr.my.id";

let state = {
  serverUrl: DEFAULT_SERVER_URL,
  token: null,
  user: null,
  categories: [],
  wallets: [],
  dashboard: null,
  hideBalance: false
};

// ── UTILITIES ──────────────────────────────────────────────────
function formatRupiah(num) {
  if (num === null || num === undefined || isNaN(num)) return "Rp 0";
  return "Rp " + Math.round(num).toLocaleString("id-ID");
}

function parseNumber(str) {
  if (!str) return 0;
  return parseInt(String(str).replace(/[^0-9]/g, ""), 10) || 0;
}

function showToast(msg, duration = 2800) {
  const toast = document.getElementById("toast");
  toast.textContent = msg;
  toast.classList.remove("hidden");
  setTimeout(() => {
    toast.classList.add("hidden");
  }, duration);
}

// ── API CLIENT ────────────────────────────────────────────────
async function api(path, options = {}) {
  const base = state.serverUrl.replace(/\/+$/, "");
  const url = `${base}/api/${path.replace(/^\/+/, "")}`;

  const headers = {
    "Content-Type": "application/json",
    ...(options.headers || {})
  };

  if (state.token) {
    headers["Authorization"] = `Bearer ${state.token}`;
  }

  try {
    const res = await fetch(url, {
      ...options,
      headers
    });

    if (res.status === 401) {
      // Sesi berakhir
      await handleLogout(false);
      showToast("Sesi berakhir, silakan login kembali.");
      throw new Error("Sesi berakhir (401)");
    }

    const data = await res.json();
    if (!res.ok) {
      throw new Error(data.message || `Error ${res.status}`);
    }

    return data;
  } catch (err) {
    console.error("API error:", err);
    throw err;
  }
}

// ── INIT & STORAGE ────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", async () => {
  // Set default date to today
  const today = new Date().toISOString().split("T")[0];
  const dateInput = document.getElementById("tx-date");
  if (dateInput) dateInput.value = today;

  // Load from local storage
  chrome.storage.local.get([
    "serverUrl",
    "token",
    "user",
    "hideBalance",
    "draftAmount",
    "draftNote",
    "activeTab"
  ], async (result) => {
    state.serverUrl = result.serverUrl || DEFAULT_SERVER_URL;
    state.token = result.token || null;
    state.user = result.user || null;
    state.hideBalance = !!result.hideBalance;

    // Populate server url input
    const loginServerInput = document.getElementById("login-server-url");
    const settingsServerInput = document.getElementById("settings-server-url");
    if (loginServerInput) loginServerInput.value = state.serverUrl;
    if (settingsServerInput) settingsServerInput.value = state.serverUrl;

    setupEventListeners();

    if (state.token) {
      showMainView();
      await loadDashboardData();

      // Check for drafts from Context Menu
      if (result.draftAmount || result.draftNote) {
        switchTab("tab-quick-add");
        const amountEl = document.getElementById("tx-amount");
        const noteEl = document.getElementById("tx-note");
        if (result.draftAmount && amountEl) {
          amountEl.value = Number(result.draftAmount).toLocaleString("id-ID");
        }
        if (result.draftNote && noteEl) {
          noteEl.value = result.draftNote;
        }
        // Clear draft
        chrome.storage.local.remove(["draftAmount", "draftNote"]);
      } else if (result.activeTab) {
        switchTab(result.activeTab);
      }
    } else {
      showLoginView();
    }
  });
});

// ── VIEW SWITCHING ────────────────────────────────────────────
function showLoginView() {
  document.getElementById("view-login").classList.remove("hidden");
  document.getElementById("view-main").classList.add("hidden");
  document.getElementById("connection-status").textContent = "Belum Masuk";
}

function showMainView() {
  document.getElementById("view-login").classList.add("hidden");
  document.getElementById("view-main").classList.remove("hidden");
  document.getElementById("connection-status").textContent = "Terhubung";

  if (state.user) {
    const nameEl = document.getElementById("user-display-name");
    const emailEl = document.getElementById("user-display-email");
    const avatarEl = document.getElementById("user-avatar-initial");
    if (nameEl) nameEl.textContent = state.user.name || "Pengguna";
    if (emailEl) emailEl.textContent = state.user.email || "";
    if (avatarEl) avatarEl.textContent = (state.user.name || "U")[0].toUpperCase();
  }
}

function switchTab(tabId) {
  document.querySelectorAll(".tab-btn").forEach(btn => {
    btn.classList.toggle("active", btn.dataset.tab === tabId);
  });
  document.querySelectorAll(".tab-pane").forEach(pane => {
    pane.classList.toggle("active", pane.id === tabId);
  });
  chrome.storage.local.set({ activeTab: tabId });
}

// ── EVENT LISTENERS ───────────────────────────────────────────
function setupEventListeners() {
  // Tabs
  document.querySelectorAll(".tab-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      switchTab(btn.dataset.tab);
    });
  });

  // Toggle eye (hide/show balance)
  const btnEye = document.getElementById("btn-toggle-eye");
  if (btnEye) {
    btnEye.addEventListener("click", () => {
      state.hideBalance = !state.hideBalance;
      chrome.storage.local.set({ hideBalance: state.hideBalance });
      renderBalance();
    });
  }

  // Refresh button
  const btnRefresh = document.getElementById("btn-refresh");
  if (btnRefresh) {
    btnRefresh.addEventListener("click", async () => {
      btnRefresh.style.transform = "rotate(360deg)";
      btnRefresh.style.transition = "transform 0.5s ease";
      setTimeout(() => { btnRefresh.style.transform = ""; }, 500);

      if (state.token) {
        await loadDashboardData();
        showToast("Data berhasil diperbarui");
      }
    });
  }

  // Open web dashboard
  const btnOpenWeb = document.getElementById("btn-open-web");
  if (btnOpenWeb) {
    btnOpenWeb.addEventListener("click", () => {
      openUrlInNewTab("/");
    });
  }

  // See all transactions
  const btnAll = document.getElementById("btn-open-activity");
  if (btnAll) {
    btnAll.addEventListener("click", () => {
      openUrlInNewTab("/activity");
    });
  }

  // Register link
  const linkReg = document.getElementById("link-register");
  if (linkReg) {
    linkReg.addEventListener("click", (e) => {
      e.preventDefault();
      openUrlInNewTab("/register");
    });
  }

  // Shortcuts grid
  document.querySelectorAll(".shortcut-item").forEach(item => {
    item.addEventListener("click", (e) => {
      e.preventDefault();
      const path = item.dataset.path || "/";
      openUrlInNewTab(path);
    });
  });

  // Amount auto format on quick add
  const amountInput = document.getElementById("tx-amount");
  if (amountInput) {
    amountInput.addEventListener("input", (e) => {
      const val = parseNumber(e.target.value);
      e.target.value = val ? val.toLocaleString("id-ID") : "";
    });
  }

  // Type switcher change (expense vs income)
  document.querySelectorAll('input[name="tx-type"]').forEach(radio => {
    radio.addEventListener("change", () => {
      populateCategorySelect();
    });
  });

  // Login Form
  const formLogin = document.getElementById("form-login");
  if (formLogin) {
    formLogin.addEventListener("submit", handleLoginSubmit);
  }

  // Quick Add Form
  const formQuickAdd = document.getElementById("form-quick-add");
  if (formQuickAdd) {
    formQuickAdd.addEventListener("submit", handleQuickAddSubmit);
  }

  // Save Settings
  const btnSaveSettings = document.getElementById("btn-save-settings");
  if (btnSaveSettings) {
    btnSaveSettings.addEventListener("click", handleSaveSettings);
  }

  // Logout
  const btnLogout = document.getElementById("btn-logout");
  if (btnLogout) {
    btnLogout.addEventListener("click", () => handleLogout(true));
  }
}

// ── DATA LOADING & RENDERING ──────────────────────────────────
async function loadDashboardData() {
  try {
    const data = await api("dashboard");
    state.dashboard = data;
    state.wallets = data.wallets || [];
    state.categories = data.categories || [];

    // Also fetch full categories if not fully returned
    if (!state.categories || state.categories.length === 0) {
      try {
        const catRes = await api("categories");
        state.categories = catRes.categories || [];
      } catch (_) {}
    }

    renderDashboard();
    populateCategorySelect();
    populateWalletSelect();
  } catch (err) {
    console.error("Gagal memuat data dashboard:", err);
    showToast("Gagal memuat data dari server");
  }
}

function renderBalance() {
  const el = document.getElementById("dash-total-balance");
  if (!el || !state.dashboard) return;

  if (state.hideBalance) {
    el.textContent = "Rp ••••••••";
  } else {
    el.textContent = formatRupiah(state.dashboard.balance || 0);
  }
}

function renderDashboard() {
  if (!state.dashboard) return;
  const d = state.dashboard;

  renderBalance();

  // Income & Expense
  const incEl = document.getElementById("dash-income");
  const expEl = document.getElementById("dash-expense");
  if (incEl) incEl.textContent = formatRupiah(d.monthly?.income || 0);
  if (expEl) expEl.textContent = formatRupiah(d.monthly?.expense || 0);

  // Budget
  const budgetWrap = document.getElementById("dash-budget-wrap");
  if (budgetWrap) {
    if (d.budget && d.budget > 0) {
      budgetWrap.classList.remove("hidden");
      const pct = Math.min(100, Math.round(((d.monthly?.expense || 0) / d.budget) * 100));
      document.getElementById("dash-budget-pct").textContent = `${pct}%`;
      const bar = document.getElementById("dash-budget-bar");
      if (bar) {
        bar.style.width = `${pct}%`;
        bar.style.backgroundColor = pct > 90 ? "var(--expense)" : "var(--primary)";
      }
    } else {
      budgetWrap.classList.add("hidden");
    }
  }

  // Recent transactions
  renderRecentTransactions(d.recent || []);
}

function renderRecentTransactions(transactions) {
  const container = document.getElementById("recent-transactions-list");
  if (!container) return;

  if (!transactions || transactions.length === 0) {
    container.innerHTML = `<div class="empty-state">Belum ada transaksi bulan ini.</div>`;
    return;
  }

  const items = transactions.slice(0, 5);
  container.innerHTML = items.map(tx => {
    const isExp = tx.type === "expense";
    const amountStr = (isExp ? "- " : "+ ") + formatRupiah(tx.amount);
    const amountClass = isExp ? "expense" : "income";
    const badgeBg = isExp ? "var(--expense-bg)" : "var(--income-bg)";
    const badgeColor = isExp ? "var(--expense)" : "var(--income)";
    const iconChar = tx.category_icon || (isExp ? "🛍️" : "💰");

    return `
      <div class="tx-item">
        <div class="tx-badge" style="background: ${badgeBg}; color: ${badgeColor};">
          ${iconChar}
        </div>
        <div class="tx-details">
          <div class="tx-title">${escapeHtml(tx.note || tx.category_name || "Transaksi")}</div>
          <div class="tx-meta">${escapeHtml(tx.category_name || "")} • ${tx.date || ""}</div>
        </div>
        <div class="tx-amount ${amountClass}">${amountStr}</div>
      </div>
    `;
  }).join("");
}

function populateCategorySelect() {
  const select = document.getElementById("tx-category");
  if (!select) return;

  const currentType = document.querySelector('input[name="tx-type"]:checked')?.value || "expense";
  const filtered = (state.categories || []).filter(c => !c.type || c.type === currentType);

  select.innerHTML = `<option value="">-- Pilih Kategori --</option>` +
    filtered.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join("");
}

function populateWalletSelect() {
  const select = document.getElementById("tx-wallet");
  if (!select) return;

  const wallets = state.wallets || [];
  if (wallets.length === 0) {
    select.innerHTML = `<option value="">Dompet Utama</option>`;
    return;
  }

  select.innerHTML = wallets.map(w => {
    const selected = w.is_default ? "selected" : "";
    return `<option value="${w.id}" ${selected}>${escapeHtml(w.name)} (${formatRupiah(w.balance || 0)})</option>`;
  }).join("");
}

// ── HANDLERS ──────────────────────────────────────────────────
async function handleLoginSubmit(e) {
  e.preventDefault();
  const errorEl = document.getElementById("login-error-msg");
  const submitBtn = document.getElementById("btn-submit-login");

  errorEl.classList.add("hidden");
  errorEl.textContent = "";

  const email = document.getElementById("login-email").value.trim();
  const password = document.getElementById("login-password").value;
  const customServer = document.getElementById("login-server-url")?.value.trim();

  if (customServer) {
    state.serverUrl = customServer;
    chrome.storage.local.set({ serverUrl: customServer });
  }

  submitBtn.disabled = true;
  submitBtn.textContent = "Memproses...";

  try {
    const res = await api("login", {
      method: "POST",
      body: JSON.stringify({
        email,
        password,
        device: "chrome_extension"
      })
    });

    state.token = res.token;
    state.user = res.user;

    await chrome.storage.local.set({
      token: res.token,
      user: res.user,
      serverUrl: state.serverUrl
    });

    showToast("Berhasil masuk!");
    showMainView();
    await loadDashboardData();
  } catch (err) {
    errorEl.textContent = err.message || "Gagal masuk. Periksa email & password Anda.";
    errorEl.classList.remove("hidden");
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = "Masuk";
  }
}

async function handleQuickAddSubmit(e) {
  e.preventDefault();
  const btn = document.getElementById("btn-save-tx");
  const amount = parseNumber(document.getElementById("tx-amount").value);
  const type = document.querySelector('input[name="tx-type"]:checked')?.value || "expense";
  const categoryId = document.getElementById("tx-category").value;
  const walletId = document.getElementById("tx-wallet").value;
  const note = document.getElementById("tx-note").value.trim();
  const date = document.getElementById("tx-date").value;

  if (amount <= 0) {
    showToast("Nominal transaksi harus lebih dari 0");
    return;
  }

  btn.disabled = true;
  btn.textContent = "Menyimpan...";

  try {
    await api("transaction/store", {
      method: "POST",
      body: JSON.stringify({
        type,
        amount,
        category_id: categoryId || null,
        wallet_id: walletId || null,
        note,
        date
      })
    });

    showToast("Transaksi berhasil dicatat!");
    // Reset fields
    document.getElementById("tx-amount").value = "";
    document.getElementById("tx-note").value = "";

    // Refresh and return to dashboard
    await loadDashboardData();
    switchTab("tab-dashboard");
  } catch (err) {
    showToast(err.message || "Gagal menyimpan transaksi");
  } finally {
    btn.disabled = false;
    btn.textContent = "Simpan Transaksi";
  }
}

function handleSaveSettings() {
  const input = document.getElementById("settings-server-url");
  if (!input) return;

  const url = input.value.trim();
  if (!url) {
    showToast("URL server tidak boleh kosong");
    return;
  }

  state.serverUrl = url;
  chrome.storage.local.set({ serverUrl: url }, () => {
    showToast("Pengaturan server disimpan");
  });
}

async function handleLogout(callApi = true) {
  if (callApi && state.token) {
    try {
      await api("logout", { method: "POST" });
    } catch (_) {}
  }

  state.token = null;
  state.user = null;
  state.dashboard = null;

  await chrome.storage.local.remove(["token", "user", "activeTab"]);
  showLoginView();
  showToast("Anda telah keluar");
}

function openUrlInNewTab(path) {
  const base = state.serverUrl.replace(/\/+$/, "");
  const cleanPath = path.startsWith("/") ? path : `/${path}`;
  const fullUrl = `${base}${cleanPath}`;
  chrome.tabs.create({ url: fullUrl });
}

function escapeHtml(str) {
  if (!str) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
