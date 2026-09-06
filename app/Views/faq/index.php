<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.faq-page {
    max-width: 720px;
    margin: 0 auto;
    padding-bottom: 110px;
}
.faq-hero-card {
    background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 50%, #3B82F6 100%);
    border-radius: 24px;
    padding: 24px 22px;
    color: #ffffff;
    margin-bottom: 20px;
    box-shadow: 0 12px 32px rgba(37, 99, 235, 0.25);
    position: relative;
    overflow: hidden;
}
.faq-search-box {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 16px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
}
.faq-search-input {
    border: none;
    outline: none;
    background: transparent;
    width: 100%;
    font-size: 14px;
    color: var(--text-primary, #111827);
}
.faq-filter-pills {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 8px;
    margin-bottom: 16px;
    scrollbar-width: none;
}
.faq-filter-pills::-webkit-scrollbar { display: none; }
.faq-pill {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    cursor: pointer;
    border: 1px solid var(--border-color, #e5e7eb);
    background: var(--bg-card, #ffffff);
    color: var(--text-secondary, #6B7280);
    transition: all 0.2s ease;
}
.faq-pill.active {
    background: #2563EB;
    color: #ffffff;
    border-color: #2563EB;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}
.faq-accordion-item {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 18px;
    margin-bottom: 10px;
    overflow: hidden;
    transition: all 0.2s ease;
}
.faq-accordion-item:hover {
    border-color: #93C5FD;
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
    font-size: 14.5px;
    font-weight: 800;
    color: var(--text-primary, #111827);
    cursor: pointer;
    transition: background 0.15s ease;
}
.faq-header-btn:focus { outline: none; }
.faq-chevron {
    font-size: 14px;
    color: #6B7280;
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    flex-shrink: 0;
}
.faq-accordion-item.open .faq-chevron {
    transform: rotate(180deg);
    color: #2563EB;
}
.faq-body-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.35s cubic-bezier(0.16, 1, 0.3, 1), padding 0.3s ease;
    padding: 0 18px;
    font-size: 13.5px;
    line-height: 1.6;
    color: var(--text-secondary, #4B5563);
    border-top: 1px solid transparent;
}
.faq-accordion-item.open .faq-body-content {
    padding: 0 18px 16px 18px;
    border-top-color: var(--border-color, #f3f4f6);
}
.faq-tag {
    font-size: 10px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 6px;
    text-transform: uppercase;
    margin-right: 6px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="faq-page">
    
    <!-- Hero FAQ Header -->
    <div class="faq-hero-card">
        <div class="d-flex align-items-center gap-2 mb-2">
            <a href="/settings" class="btn btn-sm btn-light bg-opacity-25 text-white rounded-circle"><i class="bi bi-arrow-left"></i></a>
            <span class="badge bg-white bg-opacity-25 text-white px-2 py-1 rounded-pill" style="font-size: 11px; font-weight: 700;">
                Pusat Bantuan &amp; Panduan
            </span>
        </div>
        <h3 class="fw-bold mb-1" style="font-size: 22px;">Pertanyaan Sering Diajukan (FAQ)</h3>
        <p class="mb-3 opacity-80 small">Temukan jawaban lengkap seputar Komunitas RT, Pinjam Alat, Titip Belanja, dan Pencatatan Keuangan.</p>
        <button class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-primary shadow-sm" onclick="if(window.openOnboardingTour) window.openOnboardingTour();">
            <i class="bi bi-play-circle-fill me-1"></i> Mulai Tour / Panduan Aplikasi
        </button>
    </div>

    <!-- Search Box -->
    <div class="faq-search-box">
        <i class="bi bi-search text-muted"></i>
        <input type="text" id="faqSearchInput" class="faq-search-input" placeholder="Ketik kata kunci (misal: kode unik RT, sewa alat, deposit, struk)...">
        <button type="button" class="btn-close btn-sm" id="faqSearchClear" style="display:none;" onclick="clearFaqSearch()"></button>
    </div>

    <!-- Category Filter Pills -->
    <div class="faq-filter-pills" id="faqFilterPills">
        <button type="button" class="faq-pill active" data-category="all">Semua Topik</button>
        <button type="button" class="faq-pill" data-category="rt">🏘️ Sistem RT</button>
        <button type="button" class="faq-pill" data-category="tools">🔨 Pinjam Alat</button>
        <button type="button" class="faq-pill" data-category="errands">🛍️ Titip Belanja</button>
        <button type="button" class="faq-pill" data-category="finance">💰 Keuangan &amp; Dompet</button>
        <button type="button" class="faq-pill" data-category="marketplace">📦 Jual Beli &amp; Sewa</button>
    </div>

    <!-- FAQ Accordion Container (Auto Collapse Enabled) -->
    <div id="faqAccordionContainer">

        <!-- ── 1. SISTEM RT & KEANGGOTAAN ── -->
        <div class="faq-accordion-item" data-cat="rt">
            <button type="button" class="faq-header-btn">
                <span><span class="faq-tag bg-success bg-opacity-10 text-success">Sistem RT</span> Bagaimana cara saya bergabung ke lingkungan RT saya?</span>
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-body-content">
                <p class="mb-2">Ada 2 cara mudah untuk bergabung ke grup lingkungan RT:</p>
                <ol class="mb-2 ps-3">
                    <li><strong>Masukkan Kode Unik RT</strong>: Minta kode unik kepada Ketua RT Anda (contoh: <code>RT04-RW02-GRIYA-2026</code>), lalu masukkan di menu <strong>Komunitas RT &gt; Gabung RT</strong>.</li>
                    <li><strong>Pindai QR Code RT</strong>: Pindai QR Code yang ditunjukkan Ketua RT saat pertemuan warga.</li>
                </ol>
                <p class="mb-0">Jika RT mengaktifkan <em>Auto-Approval</em>, akun Anda akan langsung aktif. Jika tidak, pengajuan Anda akan diverifikasi oleh Ketua RT.</p>
            </div>
        </div>

        <div class="faq-accordion-item" data-cat="rt">
            <button type="button" class="faq-header-btn">
                <span><span class="faq-tag bg-success bg-opacity-10 text-success">Sistem RT</span> Apa bedanya status Warga Tetap dan Warga Domisili/Kontrak?</span>
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-body-content">
                <ul class="mb-2 ps-3">
                    <li><strong>Warga Tetap</strong>: Warga dengan KTP asli beralamat di RT tersebut atau pemilik rumah tetap.</li>
                    <li><strong>Warga Domisili/Kontrak</strong>: Warga penyewa rumah, kontrakan, atau indekos di lingkungan RT.</li>
                </ul>
                <p class="mb-0">Keduanya memiliki akses penuh meminjam alat dan menitip belanja. Namun, Ketua RT dapat mengatur batasan nilai maksimal barang yang boleh dipinjam oleh warga domisili untuk menjaga aset bersama.</p>
            </div>
        </div>

        <div class="faq-accordion-item" data-cat="rt">
            <button type="button" class="faq-header-btn">
                <span><span class="faq-tag bg-success bg-opacity-10 text-success">Sistem RT</span> Apa itu Sistem Penjamin (Vouching) Tetangga?</span>
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-body-content">
                <p class="mb-0">Jika Ketua RT sedang berhalangan atau slow-response, warga baru yang mendaftar dapat diverifikasi otomatis apabila telah dijamin (*vouched*) oleh minimal <strong>2 orang tetangga</strong> di RT yang sama yang akunnya sudah terverifikasi sebelumnya.</p>
            </div>
        </div>

        <div class="faq-accordion-item" data-cat="rt">
            <button type="button" class="faq-header-btn">
                <span><span class="faq-tag bg-success bg-opacity-10 text-success">Sistem RT</span> Bagaimana cara mendaftar sebagai Ketua RT / Admin Lingkungan?</span>
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-body-content">
                <p class="mb-0">Pengurus RT dapat membuka menu <strong>Komunitas RT &gt; Daftarkan Lingkungan RT Baru</strong>. Masukkan nomor RT, RW, Kelurahan, Kecamatan, dan Kota. Akun Anda otomatis menjadi <code>Ketua RT (rt_admin)</code> yang memegang wewenang approval warga, pengelolaan inventaris alat, dan kas RT.</p>
            </div>
        </div>

        <!-- ── 2. PINJAM ALAT BERSAMA ── -->
        <div class="faq-accordion-item" data-cat="tools">
            <button type="button" class="faq-header-btn">
                <span><span class="faq-tag bg-warning bg-opacity-10 text-warning">Pinjam Alat</span> Bagaimana alur peminjaman alat pertukangan / kebersihan RT?</span>
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-body-content">
                <ol class="mb-0 ps-3">
                    <li>Pilih alat yang ingin dipinjam dari katalog (misal: Bor Listrik, Mesin Rumput, Tangga).</li>
                    <li>Tentukan durasi pinjam (1-7 hari) dan klik <strong>Pinjam</strong>.</li>
                    <li>Sistem memberikan <strong>Token Serah Terima (6 Digit)</strong>.</li>
                    <li>Saat mengambil barang ke pos RT / pemilik alat, tunjukkan token tersebut untuk divalidasi.</li>
                    <li>Saat alat dikembalikan, tunjukkan <strong>Token Pengembalian</strong> untuk menutup masa pinjam.</li>
                </ol>
            </div>
        </div>

        <div class="faq-accordion-item" data-cat="tools">
            <button type="button" class="faq-header-btn">
                <span><span class="faq-tag bg-warning bg-opacity-10 text-warning">Pinjam Alat</span> Bagaimana uang jaminan (deposit) & biaya sewa dicatat di keuangan?</span>
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-body-content">
                <p class="mb-0">Ketika token serah terima divalidasi, sistem <strong>otomatis mencatat pengeluaran sewa &amp; deposit</strong> pada buku kas peminjam. Begitu alat dikembalikan dalam kondisi baik dan divalidasi dengan token pengembalian, <strong>uang deposit langsung otomatis dikembalikan (*refund*)</strong> sebagai pemasukan saldo kas peminjam tanpa perlu input manual.</p>
            </div>
        </div>

        <!-- ── 3. TITIP BELANJA (ERRANDS) ── -->
        <div class="faq-accordion-item" data-cat="errands">
            <button type="button" class="faq-header-btn">
                <span><span class="faq-tag bg-primary bg-opacity-10 text-primary">Titip Belanja</span> Bagaimana cara membuka sesi titip belanja untuk tetangga?</span>
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-body-content">
                <p class="mb-0">Jika Anda hendak pergi ke pasar, minimarket, atau supermarket, buka menu <strong>Titip Belanja &gt; Buka Titipan</strong>. Masukkan nama toko tujuan dan batas waktu menitip (*cutoff time*). Tetangga satu RT akan menerima info dan bisa menitipkan belanjaan.</p>
            </div>
        </div>

        <div class="faq-accordion-item" data-cat="errands">
            <button type="button" class="faq-header-btn">
                <span><span class="faq-tag bg-primary bg-opacity-10 text-primary">Titip Belanja</span> Bagaimana perhitungan harga riil belanjaan & tip jasa?</span>
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-body-content">
                <p class="mb-0">Saat belanjaan diantar ke rumah pemesan, pembelanja memasukkan harga riil sesuai nota/struk belanja asli beserta foto struk, lalu memasukkan <strong>Token Serah Terima</strong> dari pemesan. Sistem langsung mencatat pengeluaran (*harga barang + tip jasa*) di pemesan dan mencatat pemasukan *reimbursement* di pembelanja.</p>
            </div>
        </div>

        <!-- ── 4. INTEGRASI KEUANGAN & TRANSAKSI ── -->
        <div class="faq-accordion-item" data-cat="finance">
            <button type="button" class="faq-header-btn">
                <span><span class="faq-tag bg-info bg-opacity-10 text-info">Keuangan</span> Apakah transaksi RT merusak data catatan keuangan pribadi saya?</span>
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-body-content">
                <p class="mb-0"><strong>Tidak sama sekali.</strong> Semua modul RT, sewa alat, dan titip belanja terintegrasi secara modular ke tabel transaksi utama (`transactions`) dengan format standar yang sama, sehingga laporan mutasi bulanan, grafik analitik, dan saldo dompet Anda tetap sinkron dan rapi.</p>
            </div>
        </div>

        <!-- ── 5. JUAL BELI & SEWA ── -->
        <div class="faq-accordion-item" data-cat="marketplace">
            <button type="button" class="faq-header-btn">
                <span><span class="faq-tag bg-purple bg-opacity-10 text-purple" style="color:#7C3AED;">Jual Beli</span> Bagaimana cara menjual barang bekas atau menyewakan properti?</span>
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-body-content">
                <p class="mb-0">Buka menu <strong>Jual Beli &amp; Sewa &gt; Pasang Iklan</strong>. Masukkan judul, kategori, harga, foto produk, dan lokasi COD. Iklan akan tayang secara publik dan calon pembeli dapat langsung menghubungi Anda melalui chat aplikasi atau tombol WhatsApp.</p>
            </div>
        </div>

    </div>

    <!-- Empty search notice -->
    <div id="faqEmptyState" class="card border-0 bg-light rounded-4 p-4 text-center text-muted" style="display:none;">
        <div style="font-size: 32px;" class="mb-2">🔍</div>
        <div class="fw-bold">Pertanyaan tidak ditemukan</div>
        <div class="small">Coba kata kunci lain atau hubungi pengurus RT Anda.</div>
    </div>

</div>

<script>
(function() {
    const items = document.querySelectorAll('.faq-accordion-item');
    const searchInput = document.getElementById('faqSearchInput');
    const searchClear = document.getElementById('faqSearchClear');
    const filterPills = document.querySelectorAll('.faq-pill');
    const emptyState = document.getElementById('faqEmptyState');

    // Auto-Collapse Accordion Logic
    items.forEach(item => {
        const btn = item.querySelector('.faq-header-btn');
        const body = item.querySelector('.faq-body-content');

        btn.addEventListener('click', () => {
            const isOpen = item.classList.contains('open');

            // Auto-collapse all other open items
            items.forEach(otherItem => {
                if (otherItem !== item && otherItem.classList.contains('open')) {
                    otherItem.classList.remove('open');
                    otherItem.querySelector('.faq-body-content').style.maxHeight = null;
                }
            });

            if (isOpen) {
                item.classList.remove('open');
                body.style.maxHeight = null;
            } else {
                item.classList.add('open');
                body.style.maxHeight = (body.scrollHeight + 32) + 'px';
            }
        });
    });

    // Category Filter Pills
    filterPills.forEach(pill => {
        pill.addEventListener('click', () => {
            filterPills.forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            applyFilter();
        });
    });

    // Search Filter
    searchInput.addEventListener('input', () => {
        if (searchInput.value.trim().length > 0) {
            searchClear.style.display = 'block';
        } else {
            searchClear.style.display = 'none';
        }
        applyFilter();
    });

    window.clearFaqSearch = function() {
        searchInput.value = '';
        searchClear.style.display = 'none';
        applyFilter();
    };

    function applyFilter() {
        const query = searchInput.value.toLowerCase().trim();
        const activeCategory = document.querySelector('.faq-pill.active').getAttribute('data-category');
        let visibleCount = 0;

        items.forEach(item => {
            const cat = item.getAttribute('data-cat');
            const text = item.textContent.toLowerCase();
            const matchesCat = (activeCategory === 'all' || cat === activeCategory);
            const matchesQuery = (query === '' || text.includes(query));

            if (matchesCat && matchesQuery) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
                if (item.classList.contains('open')) {
                    item.classList.remove('open');
                    item.querySelector('.faq-body-content').style.maxHeight = null;
                }
            }
        });

        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }
})();
</script>
<?= $this->endSection() ?>
