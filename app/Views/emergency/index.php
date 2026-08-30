<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
.emergency-page {
    max-width: 900px;
    margin: 0 auto;
    padding-bottom: 40px;
}

/* Hero SOS Header */
.emergency-hero {
    background: linear-gradient(135deg, #DC2626 0%, #991B1B 100%);
    border-radius: 20px;
    padding: 24px 20px;
    color: #FFFFFF;
    margin-bottom: 24px;
    box-shadow: 0 12px 30px rgba(220, 38, 38, 0.28);
    position: relative;
    overflow: hidden;
}
.emergency-hero::after {
    content: '🚨';
    position: absolute;
    right: -10px;
    bottom: -15px;
    font-size: 110px;
    opacity: 0.15;
    pointer-events: none;
}
.emergency-hero h1 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 900;
    letter-spacing: -0.5px;
}
.emergency-hero p {
    margin: 0 0 16px 0;
    font-size: 13px;
    opacity: 0.9;
    line-height: 1.5;
    max-width: 540px;
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.btn-hero-call {
    background: #FFFFFF;
    color: #DC2626;
    font-weight: 900;
    font-size: 14px;
    padding: 10px 18px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(0,0,0,0.15);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.btn-hero-call:active {
    transform: scale(0.97);
}
.btn-hero-sos {
    background: rgba(255, 255, 255, 0.2);
    color: #FFFFFF;
    border: 1px solid rgba(255, 255, 255, 0.4);
    font-weight: 800;
    font-size: 13px;
    padding: 10px 16px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    backdrop-filter: blur(8px);
    cursor: pointer;
}
.btn-hero-sos:active {
    background: rgba(255, 255, 255, 0.3);
}

/* Category Filter & Search */
.filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
    align-items: center;
}
.search-input-wrap {
    flex: 1;
    min-width: 240px;
    position: relative;
}
.search-input-wrap input {
    width: 100%;
    padding: 12px 16px 12px 42px;
    border-radius: 14px;
    border: 1.5px solid var(--border);
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: 13.5px;
    outline: none;
    box-shadow: var(--shadow-sm);
    transition: border-color 0.2s;
}
.search-input-wrap input:focus {
    border-color: #EF4444;
}
.search-input-wrap .search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 16px;
}

.cat-pill-list {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 4px;
    scrollbar-width: none;
}
.cat-pill-list::-webkit-scrollbar {
    display: none;
}
.cat-pill {
    padding: 8px 16px;
    border-radius: 20px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
}
.cat-pill.active {
    background: #EF4444;
    color: #FFFFFF;
    border-color: #EF4444;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

/* Emergency Cards Grid */
.emergency-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 14px;
    margin-bottom: 30px;
}

.emergency-card {
    background: var(--bg-card);
    border-radius: 16px;
    border: 1.5px solid var(--border);
    padding: 16px;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    position: relative;
}
.emergency-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: rgba(239, 68, 68, 0.4);
}

.ec-header {
    display: flex;
    gap: 12px;
    margin-bottom: 10px;
}
.ec-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
}
.ec-title-wrap {
    flex: 1;
}
.ec-name {
    font-size: 14px;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.3;
    margin-bottom: 4px;
}
.ec-badges {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.ec-badge {
    font-size: 10px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 6px;
    background: var(--bg-surface);
    color: var(--text-muted);
    border: 1px solid var(--border);
}
.ec-badge.free {
    background: #DCFCE7;
    color: #16A34A;
    border-color: #BBF7D0;
}
.ec-desc {
    font-size: 11.5px;
    color: var(--text-secondary);
    line-height: 1.45;
    margin-bottom: 14px;
}
.ec-number-row {
    background: var(--bg-surface);
    border: 1px dashed var(--border);
    border-radius: 10px;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.ec-number {
    font-size: 16px;
    font-weight: 900;
    color: #EF4444;
    letter-spacing: 0.5px;
}
.ec-copy-btn {
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 12px;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 6px;
}
.ec-copy-btn:hover {
    color: var(--text-primary);
    background: var(--border);
}

.ec-actions {
    display: flex;
    gap: 8px;
}
.btn-call-direct {
    flex: 1;
    background: #EF4444;
    color: #FFFFFF;
    text-align: center;
    padding: 9px 12px;
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 800;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25);
    transition: background 0.15s ease;
}
.btn-call-direct:hover {
    background: #DC2626;
}

/* Nearby Stations GPS Box */
.nearby-stations-card {
    background: var(--bg-card);
    border-radius: 18px;
    border: 1px solid var(--border);
    padding: 20px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-sm);
}
.nearby-stations-card h3 {
    margin: 0 0 6px 0;
    font-size: 16px;
    font-weight: 800;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
}
.nearby-stations-card p {
    font-size: 12px;
    color: var(--text-secondary);
    margin: 0 0 16px 0;
}
.station-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 10px;
}
.station-btn {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: var(--text-primary);
    font-size: 12.5px;
    font-weight: 700;
    transition: all 0.15s ease;
}
.station-btn:hover {
    background: var(--bg-card);
    border-color: #EF4444;
    color: #EF4444;
    transform: translateY(-1px);
}
.station-icon {
    font-size: 20px;
}

/* Toast Message */
#copyToast {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: #0F172A;
    color: #FFFFFF;
    padding: 10px 20px;
    border-radius: 30px;
    font-size: 12.5px;
    font-weight: 700;
    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    transition: transform 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28);
    z-index: 9999;
    pointer-events: none;
}
#copyToast.show {
    transform: translateX(-50%) translateY(0);
}
/* SOS Modal */
.sos-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    z-index: 10000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.25s ease;
}
.sos-modal-overlay.active {
    opacity: 1;
    visibility: visible;
}
.sos-modal-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 24px;
    width: 100%;
    max-width: 420px;
    padding: 22px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    transform: scale(0.92);
    transition: transform 0.25s cubic-bezier(0.18, 0.89, 0.32, 1.28);
}
.sos-modal-overlay.active .sos-modal-card {
    transform: scale(1);
}
.sos-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.sos-modal-title {
    font-size: 16px;
    font-weight: 900;
    color: #DC2626;
    display: flex;
    align-items: center;
    gap: 8px;
}
.sos-btn-close {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    color: var(--text-muted);
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.sos-action-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.sos-item-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1.5px solid var(--border);
    background: var(--bg-surface);
    text-decoration: none;
    color: var(--text-primary);
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
}
.sos-item-btn:hover, .sos-item-btn:active {
    border-color: #DC2626;
    background: rgba(220, 38, 38, 0.08);
    color: #DC2626;
}
.sos-item-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
</style>

<div class="emergency-page">

    <!-- Hero Emergency Call & SOS Share -->
    <div class="emergency-hero">
        <h1>🚨 Layanan Darurat</h1>
        <p>Akses cepat panggilan darurat nasional, mobil derek jalan tol, pemadam kebakaran, polisi, dan medis gawat darurat.</p>
        <div class="hero-actions">
            <a href="tel:112" class="btn-hero-call">
                <span>📞</span>
                <span>Panggil 112 (Bebas Pulsa)</span>
            </a>
            <button type="button" class="btn-hero-sos" onclick="openSosModal()">
                <span>📍</span>
                <span id="btnSosText">Kirim SOS Lokasi</span>
            </button>
        </div>
    </div>

    <!-- Search & Category Filter -->
    <div class="filter-bar">
        <div class="search-input-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" id="emergencySearch" placeholder="Cari layanan (Derek tol, 14080, Damkar, Polisi, BBM)..." oninput="filterCards()">
        </div>
        <div class="cat-pill-list" id="catPills">
            <?php foreach ($categories as $idx => $cat): ?>
                <button type="button" class="cat-pill <?= $idx === 0 ? 'active' : '' ?>" onclick="selectCategory('<?= esc($cat) ?>', this)">
                    <?= esc($cat) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Emergency Directory Grid -->
    <div class="emergency-grid" id="emergencyGrid">
        <?php foreach ($directory as $item): ?>
            <div class="emergency-card" data-category="<?= esc($item['category']) ?>" data-name="<?= esc(strtolower($item['name'] . ' ' . $item['number'] . ' ' . $item['description'])) ?>">
                <div>
                    <div class="ec-header">
                        <div class="ec-icon-box" style="background:<?= $item['color'] ?>18;border-color:<?= $item['color'] ?>35;">
                            <?= $item['icon'] ?>
                        </div>
                        <div class="ec-title-wrap">
                            <div class="ec-name"><?= esc($item['name']) ?></div>
                            <div class="ec-badges">
                                <span class="ec-badge"><?= esc($item['category']) ?></span>
                                <?php if ($item['is_toll_free']): ?>
                                    <span class="ec-badge free">Bebas Pulsa</span>
                                <?php else: ?>
                                    <span class="ec-badge">24 Jam</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="ec-desc"><?= esc($item['description']) ?></div>
                </div>

                <div>
                    <div class="ec-number-row">
                        <span class="ec-number" style="color:<?= $item['color'] ?>;"><?= esc($item['number']) ?></span>
                        <button type="button" class="ec-copy-btn" onclick="copyNumber('<?= esc($item['number']) ?>', '<?= esc($item['name']) ?>')" title="Salin nomor">
                            📋 Salin
                        </button>
                    </div>
                    <div class="ec-actions">
                        <a href="tel:<?= esc($item['number']) ?>" class="btn-call-direct" style="background:<?= $item['color'] ?>;">
                            <span>📞</span>
                            <span>Panggil Sekarang</span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Nearby Emergency Facilities (GPS Assisted) -->
    <div class="nearby-stations-card">
        <h3>📍 Cari Pos & Fasilitas Darurat Terdekat (GPS)</h3>
        <p>Buka peta navigasi instan menuju fasilitas gawat darurat terdekat dari posisi Anda saat ini:</p>
        <div class="station-grid">
            <a href="https://www.google.com/maps/search/Rumah+Sakit+UGD+24+Jam+terdekat" target="_blank" class="station-btn">
                <span class="station-icon">🏥</span>
                <span>RS / UGD 24 Jam</span>
            </a>
            <a href="https://www.google.com/maps/search/Kantor+Polisi+Polsek+terdekat" target="_blank" class="station-btn">
                <span class="station-icon">🚓</span>
                <span>Kantor Polisi / Polsek</span>
            </a>
            <a href="https://www.google.com/maps/search/Pos+Pemadam+Kebakaran+terdekat" target="_blank" class="station-btn">
                <span class="station-icon">🚒</span>
                <span>Pos Damkar</span>
            </a>
            <a href="https://www.google.com/maps/search/Gerbang+Tol+Posko+Derek+terdekat" target="_blank" class="station-btn">
                <span class="station-icon">🚗</span>
                <span>Posko Derek & Gerbang Tol</span>
            </a>
            <a href="https://www.google.com/maps/search/SPBU+Pom+Bensin+terdekat" target="_blank" class="station-btn">
                <span class="station-icon">⛽</span>
                <span>SPBU / Pom Bensin</span>
            </a>
            <a href="https://www.google.com/maps/search/Tambal+Ban+Bengkel+24+Jam+terdekat" target="_blank" class="station-btn">
                <span class="station-icon">🔧</span>
                <span>Bengkel / Tambal Ban</span>
            </a>
        </div>
    </div>

<!-- SOS Quick Action Modal -->
<div class="sos-modal-overlay" id="sosModalOverlay" onclick="if(event.target===this) closeSosModal()">
    <div class="sos-modal-card">
        <div class="sos-modal-header">
            <div class="sos-modal-title">
                <span>🚨</span>
                <span>Tindakan Darurat SOS</span>
            </div>
            <button type="button" class="sos-btn-close" onclick="closeSosModal()">✕</button>
        </div>
        <p style="font-size:12px;color:var(--text-secondary);margin:0 0 16px 0;line-height:1.4">
            Pilih tindakan bantuan darurat berikut. Titik koordinat GPS akan disertakan otomatis:
        </p>
        <div class="sos-action-list">
            <button type="button" class="sos-item-btn" id="btnSendWa" onclick="sendWhatsAppSos()">
                <div class="sos-item-icon" style="background:#DCFCE7;color:#16A34A">💬</div>
                <div style="flex:1;text-align:left">
                    <div style="font-size:13.5px;font-weight:800;color:var(--text-primary)">Kirim SOS via WhatsApp</div>
                    <div style="font-size:11px;color:var(--text-muted);font-weight:500" id="btnWaSub">Kirim koordinat Maps ke kontak/grup</div>
                </div>
            </button>
            <a href="tel:112" class="sos-item-btn" style="border-color:#FCA5A5">
                <div class="sos-item-icon" style="background:#FEE2E2;color:#DC2626">📞</div>
                <div style="flex:1;text-align:left">
                    <div style="font-size:13.5px;font-weight:800;color:#DC2626">Panggil 112 (Bebas Pulsa)</div>
                    <div style="font-size:11px;color:var(--text-muted);font-weight:500">Panggilan darurat terpadu 24 jam</div>
                </div>
            </a>
            <a href="tel:14080" class="sos-item-btn">
                <div class="sos-item-icon" style="background:#E0E7FF;color:#4338CA">🚗</div>
                <div style="flex:1;text-align:left">
                    <div style="font-size:13.5px;font-weight:800;color:var(--text-primary)">Derek Tol Jasa Marga (14080)</div>
                    <div style="font-size:11px;color:var(--text-muted);font-weight:500">Bantuan derek resmi jalan tol 24 jam</div>
                </div>
            </a>
            <button type="button" class="sos-item-btn" onclick="copySosText()">
                <div class="sos-item-icon" style="background:var(--bg-card);color:var(--text-primary)">📋</div>
                <div style="flex:1;text-align:left">
                    <div style="font-size:13.5px;font-weight:800;color:var(--text-primary)">Salin Format Pesan SOS</div>
                    <div style="font-size:11px;color:var(--text-muted);font-weight:500">Salin teks darurat & link lokasi</div>
                </div>
            </button>
        </div>
    </div>
</div>

<!-- Copy Notification Toast -->
<div id="copyToast">Nomor berhasil disalin!</div>

<script>
let selectedCategory = 'Semua';
let lastKnownLocation = null;

// Pre-fetch position in background so SOS is instantaneous
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        (pos) => { lastKnownLocation = { lat: pos.coords.latitude, lng: pos.coords.longitude }; },
        () => {},
        { timeout: 5000, enableHighAccuracy: true }
    );
}

function openSosModal() {
    document.getElementById('sosModalOverlay')?.classList.add('active');
}

function closeSosModal() {
    document.getElementById('sosModalOverlay')?.classList.remove('active');
}

function selectCategory(cat, btn) {
    selectedCategory = cat;
    document.querySelectorAll('.cat-pill').forEach(el => el.classList.remove('active'));
    btn.classList.add('active');
    filterCards();
}

function filterCards() {
    const q = (document.getElementById('emergencySearch')?.value || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.emergency-card');

    cards.forEach(card => {
        const cat = card.getAttribute('data-category') || '';
        const name = card.getAttribute('data-name') || '';

        const matchesCat = (selectedCategory === 'Semua' || cat === selectedCategory);
        const matchesQuery = !q || name.includes(q);

        if (matchesCat && matchesQuery) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

function copyNumber(num, name) {
    const textToCopy = num;
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(textToCopy).then(() => {
            showToast('Nomor ' + name + ' (' + num + ') disalin!');
        }).catch(() => fallbackCopy(textToCopy, name));
    } else {
        fallbackCopy(textToCopy, name);
    }
}

function fallbackCopy(num, name) {
    const el = document.createElement('textarea');
    el.value = num;
    el.setAttribute('readonly', '');
    el.style.position = 'absolute';
    el.style.left = '-9999px';
    document.body.appendChild(el);
    el.select();
    try {
        document.execCommand('copy');
        showToast('Nomor ' + (name ? name + ' ' : '') + '(' + num + ') disalin!');
    } catch (err) {
        showToast('Gagal menyalin nomor');
    }
    document.body.removeChild(el);
}

function showToast(msg) {
    const toast = document.getElementById('copyToast');
    if (!toast) return;
    toast.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
    }, 2200);
}

function buildSosMessage(coords) {
    let locPart = '';
    if (coords && coords.lat && coords.lng) {
        locPart = `\nLokasi saya:\nhttps://maps.google.com/?q=${coords.lat},${coords.lng}\n`;
    }
    return `🚨 *SOS! PERMINTAAN BANTUAN DARURAT*\nSaya membutuhkan bantuan darurat segera!${locPart}\n(Dikirim melalui Layanan Darurat DuitKu)`;
}

function sendWhatsAppSos() {
    const subEl = document.getElementById('btnWaSub');
    if (subEl) subEl.textContent = 'Mendeteksi GPS & membuka WhatsApp...';

    const dispatch = (coords) => {
        const text = buildSosMessage(coords);
        const encoded = encodeURIComponent(text);
        const waUrl = `https://api.whatsapp.com/send?text=${encoded}`;
        closeSosModal();
        if (subEl) subEl.textContent = 'Kirim koordinat Maps ke kontak/grup';
        // Direct navigation works 100% reliably in PWAs and mobile browsers without popup blocker issues
        window.location.href = waUrl;
    };

    if (lastKnownLocation) {
        dispatch(lastKnownLocation);
        return;
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                lastKnownLocation = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                dispatch(lastKnownLocation);
            },
            () => {
                dispatch(null);
            },
            { timeout: 4000, enableHighAccuracy: true }
        );
    } else {
        dispatch(null);
    }
}

function copySosText() {
    const text = buildSosMessage(lastKnownLocation);
    fallbackCopy(text, 'Format SOS');
    closeSosModal();
}
</script>

<?= $this->endSection() ?>
