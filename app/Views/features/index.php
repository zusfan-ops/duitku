<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.features-page {
    padding: 12px 16px 110px;
}

.features-section-title {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.8px;
    color: var(--text-muted);
    text-transform: uppercase;
    margin: 18px 4px 10px;
}
.features-section-title:first-child {
    margin-top: 4px;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.feature-card {
    position: relative;
    border-radius: 18px;
    padding: 14px 14px 12px;
    min-height: 88px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    text-decoration: none;
    overflow: hidden;
    color: #ffffff;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    -webkit-user-select: none;
    user-select: none;
}
.feature-card:active {
    transform: scale(0.97);
}

/* Watermark icon on background */
.feature-card-watermark {
    position: absolute;
    right: -10px;
    bottom: -10px;
    width: 68px;
    height: 68px;
    opacity: 0.16;
    transform: rotate(-10deg);
    pointer-events: none;
}

/* Ambient glow */
.feature-card::before {
    content: '';
    position: absolute;
    top: -20px;
    right: -20px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    pointer-events: none;
}

.feature-card-icon {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.22);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
}
.feature-card-icon svg {
    width: 17px;
    height: 17px;
    stroke: #ffffff;
}

.feature-card-info {
    position: relative;
    z-index: 1;
}

.feature-card-title {
    font-size: 13px;
    font-weight: 800;
    letter-spacing: -0.2px;
    line-height: 1.25;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.feature-card-desc {
    font-size: 10.5px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.85);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Theme Gradients matching Flutter */
.feat-stats {
    background: linear-gradient(135deg, #047857 0%, #10B981 100%);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.25);
}
.feat-vehicle {
    background: linear-gradient(135deg, #0284C7 0%, #38BDF8 100%);
    box-shadow: 0 6px 18px rgba(56, 189, 248, 0.25);
}
.feat-pos {
    background: linear-gradient(135deg, #EA580C 0%, #FB923C 100%);
    box-shadow: 0 6px 18px rgba(249, 115, 22, 0.25);
}
.feat-pos-stock {
    background: linear-gradient(135deg, #059669 0%, #34D399 100%);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.25);
}
.feat-pos-report {
    background: linear-gradient(135deg, #4F46E5 0%, #818CF8 100%);
    box-shadow: 0 6px 18px rgba(99, 102, 241, 0.25);
}
.feat-debt {
    background: linear-gradient(135deg, #B45309 0%, #F59E0B 100%);
    box-shadow: 0 6px 18px rgba(245, 158, 11, 0.25);
}
.feat-bills {
    background: linear-gradient(135deg, #1D4ED8 0%, #3B82F6 100%);
    box-shadow: 0 6px 18px rgba(59, 130, 246, 0.25);
}
.feat-wallets {
    background: linear-gradient(135deg, #6D28D9 0%, #8B5CF6 100%);
    box-shadow: 0 6px 18px rgba(139, 92, 246, 0.25);
}
.feat-belanja {
    background: linear-gradient(135deg, #BE185D 0%, #F43F5E 100%);
    box-shadow: 0 6px 18px rgba(244, 63, 94, 0.25);
}
.feat-travel {
    background: linear-gradient(135deg, #0E7490 0%, #06B6D4 100%);
    box-shadow: 0 6px 18px rgba(6, 182, 212, 0.25);
}
.feat-barang {
    background: linear-gradient(135deg, #4338CA 0%, #6366F1 100%);
    box-shadow: 0 6px 18px rgba(99, 102, 241, 0.25);
}
.feat-activity {
    background: linear-gradient(135deg, #334155 0%, #64748B 100%);
    box-shadow: 0 6px 18px rgba(100, 116, 139, 0.25);
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="features-page">

    <!-- Header Section -->
    <div class="features-section-title">MANAJEMEN KEUANGAN</div>
    <div class="features-grid">
        
        <!-- 1. Statistik & Analisis -->
        <a href="/stats" class="feature-card feat-stats">
            <svg class="feature-card-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>
            </svg>
            <div class="feature-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>
                </svg>
            </div>
            <div class="feature-card-info">
                <div class="feature-card-title">Statistik & Analisis</div>
                <div class="feature-card-desc">Grafik tren & kategori</div>
            </div>
        </a>

        <!-- 2. Daftar Tagihan -->
        <a href="/bills" class="feature-card feat-bills">
            <svg class="feature-card-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/>
                <line x1="8" y1="8" x2="16" y2="8"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="8" y1="16" x2="12" y2="16"/>
            </svg>
            <div class="feature-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/>
                    <line x1="8" y1="8" x2="16" y2="8"/><line x1="8" y1="12" x2="16" y2="12"/>
                </svg>
            </div>
            <div class="feature-card-info">
                <div class="feature-card-title">Daftar Tagihan</div>
                <div class="feature-card-desc">Pengingat tagihan rutin</div>
            </div>
        </a>

        <!-- 3. Hutang & Piutang -->
        <a href="/hutang" class="feature-card feat-debt">
            <svg class="feature-card-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M19 5H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2Z"/>
                <path d="M16 12h.01M3 10h18"/>
            </svg>
            <div class="feature-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 5H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2Z"/>
                    <path d="M16 12h.01M3 10h18"/>
                </svg>
            </div>
            <div class="feature-card-info">
                <div class="feature-card-title">Hutang & Piutang</div>
                <div class="feature-card-desc">Catatan pinjaman & tempo</div>
            </div>
        </a>

        <!-- 4. Kelola Rekening -->
        <a href="/wallets" class="feature-card feat-wallets">
            <svg class="feature-card-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3"/>
            </svg>
            <div class="feature-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3"/>
                </svg>
            </div>
            <div class="feature-card-info">
                <div class="feature-card-title">Kelola Rekening</div>
                <div class="feature-card-desc">Bank, e-wallet & dompet</div>
            </div>
        </a>

    </div>

    <!-- Lifestyle & Plans Section -->
    <div class="features-section-title">GAYA HIDUP & BELANJA</div>
    <div class="features-grid">

        <!-- 5. Daftar Belanja -->
        <a href="/belanja" class="feature-card feat-belanja">
            <svg class="feature-card-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <div class="feature-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </div>
            <div class="feature-card-info">
                <div class="feature-card-title">Daftar Belanja</div>
                <div class="feature-card-desc">Checklist & budget belanja</div>
            </div>
        </a>

        <!-- 6. Traveling & Trip -->
        <a href="/traveling" class="feature-card feat-travel">
            <svg class="feature-card-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/>
            </svg>
            <div class="feature-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/>
                </svg>
            </div>
            <div class="feature-card-info">
                <div class="feature-card-title">Traveling & Trip</div>
                <div class="feature-card-desc">Liburan & tiket digital</div>
            </div>
        </a>

        <!-- 7. Stok Barang -->
        <a href="/barang" class="feature-card feat-barang">
            <svg class="feature-card-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                <line x1="12" y1="22.08" x2="12" y2="12"/>
            </svg>
            <div class="feature-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
            <div class="feature-card-info">
                <div class="feature-card-title">Stok Barang</div>
                <div class="feature-card-desc">Inventaris & aset fisik</div>
            </div>
        </a>

        <!-- 8. Kendaraan & Servis -->
        <a href="/kendaraan" class="feature-card feat-vehicle">
            <svg class="feature-card-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/>
                <circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/>
            </svg>
            <div class="feature-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/>
                    <circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/>
                </svg>
            </div>
            <div class="feature-card-info">
                <div class="feature-card-title">Kendaraan & Servis</div>
                <div class="feature-card-desc">Servis, oli & pajak STNK</div>
            </div>
        </a>
    </div>

    <!-- ── BISNIS & USAHA (KASIR UMKM) ── -->
    <!-- ── BISNIS & USAHA (KASIR UMKM) ── -->
    <div class="features-section-title">Bisnis & Usaha (Kasir POS)</div>
    <div class="features-grid">
        <!-- 1. Live Pesanan Masuk -->
        <a href="/pos/orders" class="feature-card feat-pos">
            <svg class="feature-card-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            <div class="feature-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
            </div>
            <div class="feature-card-info">
                <div class="feature-card-title">Pesanan Masuk (Live)</div>
                <div class="feature-card-desc">Antrean meja & status saji</div>
            </div>
        </a>

        <!-- 2. Cetak Standee QR Code -->
        <a href="/pos/qr" class="feature-card feat-wallets">
            <svg class="feature-card-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <div class="feature-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </div>
            <div class="feature-card-info">
                <div class="feature-card-title">Cetak QR Standee</div>
                <div class="feature-card-desc">Poster menu meja PDF</div>
            </div>
        </a>

        <!-- 3. Kasir Mini POS -->
        <a href="/pos" class="feature-card feat-pos-stock">
            <svg class="feature-card-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                <line x1="8" y1="21" x2="16" y2="21"></line>
                <line x1="12" y1="17" x2="12" y2="21"></line>
            </svg>
            <div class="feature-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                </svg>
            </div>
            <div class="feature-card-info">
                <div class="feature-card-title">Kasir Mini (POS)</div>
                <div class="feature-card-desc">Kasir cepat & cetak struk</div>
            </div>
        </a>

        <!-- 4. Stok & HPP Produk -->
        <a href="/pos/products" class="feature-card feat-barang">
            <svg class="feature-card-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <div class="feature-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            </div>
            <div class="feature-card-info">
                <div class="feature-card-title">Katalog & Stok</div>
                <div class="feature-card-desc">Kelola menu, HPP & stok</div>
            </div>
        </a>

        <!-- 5. Laporan Laba Rugi -->
        <a href="/pos/reports" class="feature-card feat-pos-report" style="grid-column: span 2">
            <svg class="feature-card-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
            <div class="feature-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
            </div>
            <div class="feature-card-info">
                <div class="feature-card-title">Laporan Laba Rugi & Best Seller</div>
                <div class="feature-card-desc">Omset penjualan, total modal HPP & laba bersih usaha</div>
            </div>
        </a>
    </div>

    <div class="features-grid">
        <!-- 9. Semua Mutasi -->
        <a href="/activity" class="feature-card feat-activity">
            <svg class="feature-card-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
            </svg>
            <div class="feature-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                </svg>
            </div>
            <div class="feature-card-info">
                <div class="feature-card-title">Semua Mutasi</div>
                <div class="feature-card-desc">Riwayat & pencarian data</div>
            </div>
        </a>

    </div>

</div>
<?= $this->endSection() ?>
