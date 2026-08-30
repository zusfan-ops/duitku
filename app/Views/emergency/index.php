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
</style>

<div class="emergency-page">

    <!-- Hero Emergency Call & SOS Share -->
    <div class="emergency-hero">
        <h1>🚨 Layanan & Kontak Darurat 24 Jam</h1>
        <p>Akses cepat panggilan darurat nasional, mobil derek resmi jalan tol, pemadam kebakaran, polisi, dan ambulans medis gawat darurat.</p>
        <div class="hero-actions">
            <a href="tel:112" class="btn-hero-call">
                <span>📞</span>
                <span>Panggilan Darurat 112</span>
            </a>
            <button type="button" class="btn-hero-sos" onclick="shareSosLocation()">
                <span>📍</span>
                <span>Kirim SOS & Koordinat Lokasi</span>
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

</div>

<!-- Copy Notification Toast -->
<div id="copyToast">Nomor berhasil disalin!</div>

<script>
let selectedCategory = 'Semua';

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
    navigator.clipboard.writeText(num).then(() => {
        showToast('Nomor ' + name + ' (' + num + ') disalin!');
    }).catch(() => {
        const el = document.createElement('textarea');
        el.value = num;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        showToast('Nomor (' + num + ') disalin!');
    });
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

function shareSosLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const mapUrl = `https://maps.google.com/?q=${lat},${lng}`;
                const text = encodeURIComponent(`🚨 *SOS! PERMINTAAN BANTUAN DARURAT*\nSaya membutuhkan bantuan darurat segera di lokasi ini:\n${mapUrl}\n(Dikirim via Layanan Darurat DuitKu)`);
                window.open(`https://wa.me/?text=${text}`, '_blank');
            },
            () => {
                const text = encodeURIComponent(`🚨 *SOS! PERMINTAAN BANTUAN DARURAT*\nSaya membutuhkan bantuan darurat segera!\n(Dikirim via Layanan Darurat DuitKu)`);
                window.open(`https://wa.me/?text=${text}`, '_blank');
            },
            { timeout: 8000 }
        );
    } else {
        const text = encodeURIComponent(`🚨 *SOS! PERMINTAAN BANTUAN DARURAT*\nSaya membutuhkan bantuan darurat segera!\n(Dikirim via Layanan Darurat DuitKu)`);
        window.open(`https://wa.me/?text=${text}`, '_blank');
    }
}
</script>

<?= $this->endSection() ?>
