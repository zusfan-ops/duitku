<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.faq-page {
    max-width: 680px;
    margin: 0 auto;
    padding-bottom: 120px;
}

/* ── Hero FAQ Banner (Emerald Signature) ── */
.faq-hero-card {
    background: linear-gradient(135deg, #064E3B 0%, #059669 60%, #10B981 100%);
    border-radius: var(--radius-xl, 24px);
    padding: 24px 22px;
    color: #ffffff;
    margin-bottom: 18px;
    box-shadow: 0 12px 32px rgba(5, 150, 105, 0.28);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.15);
}
.faq-hero-card::after {
    content: '';
    position: absolute;
    right: -25px;
    bottom: -25px;
    width: 140px;
    height: 140px;
    background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.faq-hero-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.35);
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.4px;
    margin-bottom: 10px;
    color: #ffffff;
}
.faq-hero-title {
    font-size: 22px;
    font-weight: 900;
    margin-bottom: 6px;
    letter-spacing: -0.4px;
    line-height: 1.25;
}
.faq-hero-desc {
    font-size: 13px;
    line-height: 1.5;
    opacity: 0.9;
    margin-bottom: 16px;
    max-width: 90%;
}
.faq-tour-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #ffffff;
    color: #065F46;
    font-size: 12.5px;
    font-weight: 800;
    padding: 8px 18px;
    border-radius: 30px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
    transition: all 0.2s ease;
    cursor: pointer;
    border: none;
}
.faq-tour-btn:hover {
    background: #F0FDF4;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.16);
}

/* ── Search Container ── */
.faq-search-box {
    background: var(--bg-card, #ffffff);
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: var(--radius-lg, 18px);
    padding: 10px 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
    box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.04));
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.faq-search-box:focus-within {
    border-color: var(--primary, #059669);
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12);
}
.faq-search-input {
    border: none;
    outline: none;
    background: transparent;
    width: 100%;
    font-size: 13.5px;
    font-weight: 500;
    color: var(--text-primary, #0F172A);
    font-family: inherit;
}
.faq-search-input::placeholder {
    color: var(--text-muted, #94A3B8);
}
.faq-search-clear {
    background: none;
    border: none;
    color: var(--text-muted, #94A3B8);
    font-size: 16px;
    cursor: pointer;
    display: none;
    padding: 2px;
}

/* ── Category Filter Pills ── */
.faq-filter-pills {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding: 2px 2px 12px;
    margin-bottom: 12px;
    scrollbar-width: none;
}
.faq-filter-pills::-webkit-scrollbar { display: none; }
.faq-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 15px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    cursor: pointer;
    border: 1.5px solid var(--border, #E2E8F0);
    background: var(--bg-card, #ffffff);
    color: var(--text-secondary, #475569);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-sm);
}
.faq-pill:hover {
    border-color: var(--primary-light, #10B981);
    color: var(--primary, #059669);
}
.faq-pill.active {
    background: var(--primary, #059669);
    color: #ffffff;
    border-color: var(--primary, #059669);
    box-shadow: 0 4px 14px rgba(5, 150, 105, 0.28);
}

/* ── Accordion Card ── */
.faq-accordion-item {
    background: var(--bg-card, #ffffff);
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: var(--radius-lg, 18px);
    margin-bottom: 10px;
    overflow: hidden;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    box-shadow: var(--shadow-sm);
}
.faq-accordion-item:hover {
    border-color: #CBD5E1;
}
.faq-accordion-item.open {
    border-color: var(--primary, #059669);
    box-shadow: 0 4px 18px rgba(5, 150, 105, 0.08);
}
.faq-header-btn {
    width: 100%;
    padding: 16px 18px;
    background: transparent;
    border: none;
    text-align: left;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    cursor: pointer;
    font-family: inherit;
}
.faq-header-btn:focus { outline: none; }
.faq-q-text {
    font-size: 14px;
    font-weight: 800;
    color: var(--text-primary, #0F172A);
    line-height: 1.38;
}
.faq-chevron {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--bg, #F8FAFC);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    color: var(--text-secondary, #64748B);
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), background 0.2s ease, color 0.2s ease;
    flex-shrink: 0;
}
.faq-accordion-item.open .faq-chevron {
    transform: rotate(180deg);
    background: var(--primary-dim, rgba(5, 150, 105, 0.12));
    color: var(--primary, #059669);
}

/* ── Content & Tag styling ── */
.faq-body-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.35s cubic-bezier(0.16, 1, 0.3, 1), padding 0.3s ease;
    padding: 0 18px;
    font-size: 13.5px;
    line-height: 1.62;
    color: var(--text-secondary, #334155);
    border-top: 1px solid transparent;
}
.faq-accordion-item.open .faq-body-content {
    padding: 0 18px 18px 18px;
    border-top-color: var(--border-light, #F1F5F9);
}
.faq-tag {
    font-size: 10px;
    font-weight: 800;
    padding: 2.5px 8px;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    display: inline-block;
    margin-bottom: 6px;
}
.faq-tag.finance { background: rgba(5, 150, 105, 0.12); color: #059669; }
.faq-tag.pos     { background: rgba(79, 70, 229, 0.12); color: #4F46E5; }
.faq-tag.rt      { background: rgba(13, 148, 136, 0.12); color: #0D9488; }
.faq-tag.tools   { background: rgba(217, 119, 6, 0.12);  color: #D97706; }
.faq-tag.errands { background: rgba(2, 132, 199, 0.12);  color: #0284C7; }
.faq-tag.market  { background: rgba(147, 51, 234, 0.12); color: #9333EA; }
.faq-tag.debt    { background: rgba(225, 29, 72, 0.12);  color: #E11D48; }
.faq-tag.travel  { background: rgba(8, 145, 178, 0.12);  color: #0891B2; }
.faq-tag.media   { background: rgba(234, 88, 12, 0.12);  color: #EA580C; }
.faq-tag.secure  { background: rgba(71, 85, 105, 0.12);  color: #475569; }

.faq-empty-state {
    text-align: center;
    padding: 40px 20px;
    background: var(--bg-card, #ffffff);
    border: 1.5px dashed var(--border, #E2E8F0);
    border-radius: 20px;
    display: none;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="faq-page">
    
    <!-- ── Hero FAQ Banner ── -->
    <div class="faq-hero-card">
        <div class="faq-hero-pill">
            <span>✨</span> Pusat Bantuan &amp; Panduan Lengkap
        </div>
        <h1 class="faq-hero-title">Pertanyaan Sering Diajukan (FAQ)</h1>
        <p class="faq-hero-desc">
            Panduan lengkap semua fitur DuitKu: Pencatatan Keuangan, Scan OCR Struk, Kasir POS, Komunitas RT, Pinjam Alat, Titip Belanja, hingga Marketplace.
        </p>
        <button type="button" class="faq-tour-btn" onclick="if(window.openOnboardingTour) window.openOnboardingTour();">
            <span>🚀</span> Mulai Tour Interaktif Aplikasi
        </button>
    </div>

    <!-- ── Search Box ── -->
    <div class="faq-search-box">
        <span style="font-size: 16px;">🔍</span>
        <input type="text" id="faqSearchInput" class="faq-search-input" placeholder="Cari bantuan (misal: scan nota, kasir POS, kode RT, pinjam alat, budget)...">
        <button type="button" class="faq-search-clear" id="faqSearchClear" onclick="clearFaqSearch()">✕</button>
    </div>

    <!-- ── Category Filter Pills ── -->
    <div class="faq-filter-pills" id="faqFilterPills">
        <button type="button" class="faq-pill active" data-category="all">🌟 Semua Topik</button>
        <button type="button" class="faq-pill" data-category="finance">💵 Keuangan &amp; Dompet</button>
        <button type="button" class="faq-pill" data-category="pos">🛒 Kasir POS Bisnis</button>
        <button type="button" class="faq-pill" data-category="rt">🏘️ Komunitas RT</button>
        <button type="button" class="faq-pill" data-category="tools">🔨 Pinjam Alat</button>
        <button type="button" class="faq-pill" data-category="errands">🛍️ Titip Belanja</button>
        <button type="button" class="faq-pill" data-category="market">🏷️ Jual Beli &amp; Toko</button>
        <button type="button" class="faq-pill" data-category="debt">⏰ Tagihan &amp; Hutang</button>
        <button type="button" class="faq-pill" data-category="travel">✈️ Traveling &amp; Valas</button>
        <button type="button" class="faq-pill" data-category="media">🎬 TV &amp; Hiburan</button>
        <button type="button" class="faq-pill" data-category="secure">🛡️ Cadangan &amp; Akun</button>
    </div>

    <!-- ── Accordion List (Auto-Collapse Mode) ── -->
    <div id="faqAccordionContainer">

        <!-- 1. KEUANGAN & DOMPET -->
        <div class="faq-accordion-item" data-cat="finance">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag finance">Keuangan</span>
                    <div class="faq-q-text">Bagaimana cara mencatat transaksi pemasukan atau pengeluaran?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>Ketuk tombol bulat biru bertanda <strong>+</strong> di bagian bawah layar. Pilih jenis transaksi (Pengeluaran, Pemasukan, atau Transfer Antar-Dompet), masukkan nominal, pilih kategori, pilih sumber dompet, lalu simpan. Saldo dompet Anda akan otomatis diperbarui secara instan.</p>
            </div>
        </div>

        <div class="faq-accordion-item" data-cat="finance">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag finance">Scan OCR AI</span>
                    <div class="faq-q-text">Bagaimana cara kerja fitur Scan Struk / Nota Otomatis (OCR)?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>Pada kartu Hero Dashboard atau menu transaksi, ketuk tombol <strong>Scan Struk (OCR)</strong>. Ambil foto nota belanja kasir atau upload dari galeri. Sistem AI DuitKu akan mendeteksi nominal total harga, tanggal pembelian, dan nama toko secara otomatis sehingga Anda tidak perlu mengetik manual.</p>
            </div>
        </div>

        <div class="faq-accordion-item" data-cat="finance">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag finance">Multi-Dompet</span>
                    <div class="faq-q-text">Bagaimana cara mengelola banyak dompet &amp; rekening bank?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>Buka menu <strong>Dompet (Wallets)</strong> untuk menambahkan akun rekening (misal: BCA, Mandiri, Cash Tunai, GoPay, OVO). Anda juga dapat melakukan transfer antar-dompet serta membagikan dompet bersama anggota keluarga.</p>
            </div>
        </div>

        <div class="faq-accordion-item" data-cat="finance">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag finance">Budgeting</span>
                    <div class="faq-q-text">Bagaimana cara membatasi pengeluaran dengan Budget Bulanan?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>Di menu <strong>Pengaturan &gt; Budget Bulan Ini</strong>, tentukan batas nominal maksimal pengeluaran bulanan Anda. Dashboard akan menampilkan progress bar persentase pemakaian budget dan memberikan peringatan jika anggaran sudah menipis.</p>
            </div>
        </div>

        <!-- 2. KASIR POS BISNIS -->
        <div class="faq-accordion-item" data-cat="pos">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag pos">Kasir POS</span>
                    <div class="faq-q-text">Bagaimana cara mengaktifkan Mode Bisnis / Kasir Toko (POS)?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>Di bagian atas Dashboard, Anda dapat beralih dari mode <strong>Personal</strong> ke mode <strong>Business (POS)</strong>. Pada mode ini, Anda dapat menginput katalog produk dagangan, memproses transaksi kasir cepat (tunai/QRIS), mencetak nota belanja, dan memantau laba bersih harian.</p>
            </div>
        </div>

        <div class="faq-accordion-item" data-cat="pos">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag pos">Kasir POS</span>
                    <div class="faq-q-text">Apakah laporan omzet kasir terpisah dari keuangan pribadi?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>Ya. Sistem memisahkan mutasi kasir POS dengan pembukuan kas pribadi. Anda dapat menentukan dompet mana yang ditunjuk sebagai penampung pendapatan kasir bisnis Anda.</p>
            </div>
        </div>

        <!-- 3. KOMUNITAS RT -->
        <div class="faq-accordion-item" data-cat="rt">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag rt">Sistem RT</span>
                    <div class="faq-q-text">Bagaimana cara bergabung ke lingkungan RT saya?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>1. Minta <strong>Kode Unik RT</strong> ke Ketua RT Anda (contoh: <code>RT04-RW02-GRIYA-2026</code>).<br>2. Buka menu <strong>Komunitas RT &gt; Gabung RT</strong>.<br>3. Masukkan kode unik dan nomor rumah Anda. Jika RT mengaktifkan auto-approval, akun langsung aktif. Jika tidak, pengajuan akan masuk ke antrean verifikasi Ketua RT.</p>
            </div>
        </div>

        <div class="faq-accordion-item" data-cat="rt">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag rt">Sistem RT</span>
                    <div class="faq-q-text">Apa bedanya status Warga Tetap dan Warga Domisili/Kontrak?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p><strong>Warga Tetap:</strong> Warga dengan KTP asli beralamat di RT setempat atau pemilik rumah tetap.<br><strong>Warga Domisili/Kontrak:</strong> Warga penyewa, pengontrak, atau indekos. Keduanya memiliki hak akses penuh, namun Ketua RT dapat menentukan limit batas nilai pinjam alat untuk warga non-permanen demi keamanan aset warga.</p>
            </div>
        </div>

        <div class="faq-accordion-item" data-cat="rt">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag rt">Sistem RT</span>
                    <div class="faq-q-text">Apa itu Sistem Penjamin (Vouching) Tetangga?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>Jika Ketua RT sedang berhalangan memverifikasi pengajuan warga baru secara manual, akun warga baru dapat aktif otomatis jika sudah dijamin (<em>vouched</em>) oleh minimal 2 warga yang sudah terverifikasi di RT tersebut.</p>
            </div>
        </div>

        <div class="faq-accordion-item" data-cat="rt">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag rt">Sistem RT</span>
                    <div class="faq-q-text">Bagaimana cara mendaftarkan RT baru jika saya adalah Pengurus RT?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>Pilih menu <strong>Daftarkan RT Baru</strong> (`/neighborhood/create`), masukkan data wilayah (Provinsi, Kota, Kecamatan, Kelurahan, RW, dan RT). Anda akan langsung ditetapkan sebagai Administrator RT tersebut dan mendapatkan Kode Unik RT untuk dibagikan kepada warga.</p>
            </div>
        </div>

        <!-- 4. PINJAM ALAT WARGA -->
        <div class="faq-accordion-item" data-cat="tools">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag tools">Pinjam Alat</span>
                    <div class="faq-q-text">Bagaimana alur meminjam alat pertukangan / kebersihan warga?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <ol style="padding-left: 20px; margin: 0;">
                    <li>Pilih alat dari katalog RT (misal: Bor Listrik, Mesin Rumput, Tenda, Tangga).</li>
                    <li>Tentukan durasi pinjam dan ajukan peminjaman.</li>
                    <li>Dapatkan <strong>Token Serah Terima (6 Digit)</strong>.</li>
                    <li>Tunjukkan token kepada pemilik/petugas RT saat mengambil barang.</li>
                    <li>Setelah selesai, serahkan kembali alat dan gunakan <strong>Token Pengembalian</strong> untuk menutup sesi pinjam.</li>
                </ol>
            </div>
        </div>

        <div class="faq-accordion-item" data-cat="tools">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag tools">Pinjam Alat</span>
                    <div class="faq-q-text">Bagaimana uang deposit jaminan dan biaya sewa dicatat ke keuangan?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>Sistem langsung mencatat otomatis biaya sewa dan uang deposit pada buku kas pengeluaran peminjam saat barang divalidasi serah terima. Ketika barang dikembalikan dalam kondisi baik, uang deposit langsung otomatis di-refund sebagai transaksi pemasukan ke dompet peminjam.</p>
            </div>
        </div>

        <!-- 5. TITIP BELANJA -->
        <div class="faq-accordion-item" data-cat="errands">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag errands">Titip Belanja</span>
                    <div class="faq-q-text">Bagaimana cara membuka sesi titip belanja untuk tetangga?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>Buka menu <strong>Titip Belanja &gt; Buka Sesi Belanja</strong>. Tentukan toko tujuan (misal: Pasar Tradisional, Supermarket, Apotek) dan batas waktu (cutoff). Tetangga satu RT dapat menitipkan daftar belanjaan mereka sebelum batas waktu tersebut berakhir.</p>
            </div>
        </div>

        <div class="faq-accordion-item" data-cat="errands">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag errands">Titip Belanja</span>
                    <div class="faq-q-text">Bagaimana perhitungan harga belanjaan riil dan tip jasa?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>Setelah belanja selesai, pembelanja menginput harga nota belanja riil. Saat barang diserahkan kepada penitip, sistem akan otomatis mendebit buku kas penitip sebesar <code>Harga Barang + Biaya Jasa</code> dan mencatat pemasukan pada pembelanja.</p>
            </div>
        </div>

        <!-- 6. MARKETPLACE & TOKO -->
        <div class="faq-accordion-item" data-cat="market">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag market">Marketplace</span>
                    <div class="faq-q-text">Bagaimana cara menjual barang bekas atau menyewakan properti?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>Buka menu <strong>Pasar / Marketplace</strong> lalu ketuk <strong>Jual Barang</strong>. Masukkan foto, deskripsi, harga, dan lokasi. Iklan Anda akan tayang di katalog pasar dan dapat dilihat oleh pengguna di sekitar Anda.</p>
            </div>
        </div>

        <div class="faq-accordion-item" data-cat="market">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag market">Domain Toko</span>
                    <div class="faq-q-text">Apakah saya mendapatkan halaman website toko publik gratis?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>Ya! Setiap akun DuitKu otomatis memiliki domain etalase toko publik (misal: <code>duitku.my.id/toko/namatoko</code>). Anda dapat membagikan link ini ke media sosial atau WhatsApp agar calon pembeli bisa melihat katalog produk Anda.</p>
            </div>
        </div>

        <!-- 7. TAGIHAN & HUTANG -->
        <div class="faq-accordion-item" data-cat="debt">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag debt">Tagihan &amp; Hutang</span>
                    <div class="faq-q-text">Bagaimana cara mengaktifkan pengingat jatuh tempo tagihan &amp; hutang?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>Masuk ke menu <strong>Tagihan (Bills)</strong> atau <strong>Hutang-Piutang</strong>. Masukkan tanggal jatuh tempo. Dashboard akan menampilkan banner pengingat darurat H-3, H-1, dan hari H, serta mengirimkan notifikasi agar Anda tidak telat bayar.</p>
            </div>
        </div>

        <!-- 8. TRAVELING & VALAS -->
        <div class="faq-accordion-item" data-cat="travel">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag travel">Traveling</span>
                    <div class="faq-q-text">Bagaimana mencatat pengeluaran liburan &amp; kalkulator kurs valas?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>Buka menu <strong>Travelling</strong> untuk mengelompokkan anggaran perjalanan per trip. Anda juga dapat menggunakan tombol <strong>Kurs Terkini</strong> di dashboard untuk menghitung konversi valuta asing (USD, SGD, MYR, JPY, EUR ke IDR) secara langsung.</p>
            </div>
        </div>

        <!-- 9. TV & HIBURAN -->
        <div class="faq-accordion-item" data-cat="media">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag media">Streaming &amp; Game</span>
                    <div class="faq-q-text">Bagaimana cara menonton TV streaming dan bermain mini-game?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>DuitKu dilengkapi fitur multimedia terpadu: Anda dapat menonton siaran TV digital nasional langsung di kartu dashboard, streaming video server lokal, membaca berita terkini, serta memainkan aneka game ringan di menu <strong>Game Hub</strong>.</p>
            </div>
        </div>

        <!-- 10. KEAMANAN & CADANGAN -->
        <div class="faq-accordion-item" data-cat="secure">
            <button type="button" class="faq-header-btn">
                <div>
                    <span class="faq-tag secure">Cadangan &amp; Keamanan</span>
                    <div class="faq-q-text">Apakah data saya aman jika koneksi offline atau berganti HP?</div>
                </div>
                <div class="faq-chevron">▼</div>
            </button>
            <div class="faq-body-content">
                <p>Aplikasi DuitKu menggunakan arsitektur <em>Offline-First</em>: transaksi tetap tersimpan di perangkat saat offline dan otomatis sinkron saat terhubung internet. Anda juga dapat mengekspor cadangan penuh dalam format JSON di menu <strong>Pengaturan &gt; Cadangan &amp; Pemulihan</strong>.</p>
            </div>
        </div>

    </div>

    <!-- Empty State for Search -->
    <div id="faqEmptyState" class="faq-empty-state">
        <div style="font-size: 36px; margin-bottom: 8px;">🔍</div>
        <h5 style="font-weight: 800; margin-bottom: 4px; color: var(--text-primary);">Tidak ada jawaban ditemukan</h5>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">Coba gunakan kata kunci lain atau pilih topik di atas.</p>
        <button type="button" class="faq-pill active" onclick="clearFaqSearch()">Reset Pencarian</button>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('faqSearchInput');
    const searchClear = document.getElementById('faqSearchClear');
    const filterPills = document.querySelectorAll('#faqFilterPills .faq-pill');
    const items = document.querySelectorAll('#faqAccordionContainer .faq-accordion-item');
    const emptyState = document.getElementById('faqEmptyState');
    let activeCategory = 'all';

    // Auto-Collapse Accordion Logic
    items.forEach(function(item) {
        const btn = item.querySelector('.faq-header-btn');
        const content = item.querySelector('.faq-body-content');

        btn.addEventListener('click', function() {
            const isOpen = item.classList.contains('open');

            // Close all other accordion items
            items.forEach(function(otherItem) {
                if (otherItem !== item && otherItem.classList.contains('open')) {
                    otherItem.classList.remove('open');
                    otherItem.querySelector('.faq-body-content').style.maxHeight = '0px';
                }
            });

            // Toggle current item
            if (isOpen) {
                item.classList.remove('open');
                content.style.maxHeight = '0px';
            } else {
                item.classList.add('open');
                content.style.maxHeight = content.scrollHeight + 30 + 'px';
            }
        });
    });

    // Category Filter Pills
    filterPills.forEach(function(pill) {
        pill.addEventListener('click', function() {
            filterPills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            activeCategory = this.getAttribute('data-category');
            filterFaqItems();
        });
    });

    // Live Search
    searchInput.addEventListener('input', function() {
        searchClear.style.display = this.value.trim() ? 'block' : 'none';
        filterFaqItems();
    });

    window.clearFaqSearch = function() {
        searchInput.value = '';
        searchClear.style.display = 'none';
        filterPills.forEach(p => {
            if (p.getAttribute('data-category') === 'all') p.classList.add('active');
            else p.classList.remove('active');
        });
        activeCategory = 'all';
        filterFaqItems();
    };

    function filterFaqItems() {
        const query = (searchInput.value || '').toLowerCase().trim();
        let visibleCount = 0;

        items.forEach(function(item) {
            const cat = item.getAttribute('data-cat');
            const text = item.innerText.toLowerCase();
            const matchesCat = (activeCategory === 'all' || cat === activeCategory);
            const matchesQuery = (!query || text.includes(query));

            if (matchesCat && matchesQuery) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
                item.classList.remove('open');
                item.querySelector('.faq-body-content').style.maxHeight = '0px';
            }
        });

        emptyState.style.display = (visibleCount === 0) ? 'block' : 'none';
    }
});
</script>
<?= $this->endSection() ?>
