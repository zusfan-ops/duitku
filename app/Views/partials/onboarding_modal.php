<!-- ONBOARDING SYSTEM MODAL TOUR (ALL-APP FEATURES) -->
<style>
.onboard-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(8px);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    animation: onboardFadeIn 0.3s ease;
}
.onboard-overlay.show { display: flex; }
@keyframes onboardFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.onboard-card {
    background: var(--bg-card, #ffffff);
    border-radius: 28px;
    max-width: 480px;
    width: 100%;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
    display: flex;
    flex-direction: column;
    position: relative;
    border: 1px solid rgba(255, 255, 255, 0.15);
    animation: onboardSlideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes onboardSlideUp {
    from { transform: translateY(30px) scale(0.96); }
    to { transform: translateY(0) scale(1); }
}
.onboard-header-graphic {
    height: 160px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    color: #ffffff;
    text-align: center;
    padding: 20px;
    transition: background 0.4s ease;
}
.onboard-icon-bubble {
    width: 72px;
    height: 72px;
    border-radius: 22px;
    background: rgba(255, 255, 255, 0.22);
    backdrop-filter: blur(10px);
    border: 1.5px solid rgba(255, 255, 255, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    animation: bubbleFloat 3s ease-in-out infinite;
}
@keyframes bubbleFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}
.onboard-body {
    padding: 22px 22px 18px;
    text-align: center;
}
.onboard-step-badge {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    display: inline-block;
    padding: 3px 12px;
    border-radius: 20px;
    margin-bottom: 8px;
}
.onboard-title {
    font-size: 19px;
    font-weight: 900;
    color: var(--text-primary, #0F172A);
    margin-bottom: 6px;
    line-height: 1.3;
}
.onboard-desc {
    font-size: 13px;
    color: var(--text-secondary, #475569);
    line-height: 1.5;
    margin-bottom: 14px;
}
.onboard-features-mini {
    background: var(--bg, #F8FAFC);
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: 16px;
    padding: 12px 14px;
    text-align: left;
    display: flex;
    flex-direction: column;
    gap: 8px;
    font-size: 12px;
    color: var(--text-primary, #1E293B);
    margin-bottom: 16px;
}
.onboard-features-mini div {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    line-height: 1.4;
}
.onboard-dots {
    display: flex;
    justify-content: center;
    gap: 6px;
    margin-bottom: 16px;
}
.onboard-dot {
    width: 8px;
    height: 8px;
    border-radius: 4px;
    background: var(--border, #CBD5E1);
    transition: all 0.3s ease;
}
.onboard-dot.active {
    width: 24px;
    background: var(--primary, #059669);
}
.onboard-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}
.onboard-btn-skip {
    background: transparent;
    border: none;
    color: var(--text-muted, #94A3B8);
    font-size: 13px;
    font-weight: 700;
    padding: 8px 12px;
    cursor: pointer;
}
.onboard-btn-next {
    background: var(--primary, #059669);
    color: #ffffff;
    border: none;
    border-radius: 30px;
    font-size: 13.5px;
    font-weight: 800;
    padding: 9px 22px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);
    transition: all 0.2s ease;
}
.onboard-btn-next:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(5, 150, 105, 0.4);
}
</style>

<div class="onboard-overlay" id="onboardingOverlay">
    <div class="onboard-card" id="onboardCard">
        
        <!-- Header Graphic Gradient -->
        <div class="onboard-header-graphic" id="onboardGraphic" style="background: linear-gradient(135deg, #064E3B, #059669);">
            <div class="onboard-icon-bubble" id="onboardIcon">✨</div>
        </div>

        <!-- Content Body -->
        <div class="onboard-body">
            <span class="onboard-step-badge" id="onboardBadge" style="background:#ECFDF5;color:#059669;">Langkah 1 dari 5</span>
            <h4 class="onboard-title" id="onboardTitle">Selamat Datang di DuitKu</h4>
            <p class="onboard-desc" id="onboardDesc">Platform terpadu keuangan pribadi, bisnis kasir POS, dan komunitas RT.</p>
            
            <div class="onboard-features-mini" id="onboardMiniList">
                <div><span>💵</span> <span><strong>Pencatatan Keuangan:</strong> Pemasukan, pengeluaran &amp; scan nota OCR otomatis.</span></div>
                <div><span>💳</span> <span><strong>Multi-Dompet:</strong> Rekening bank, e-wallet &amp; target tabungan impian.</span></div>
            </div>

            <!-- Dots Indicator -->
            <div class="onboard-dots" id="onboardDots">
                <span class="onboard-dot active"></span>
                <span class="onboard-dot"></span>
                <span class="onboard-dot"></span>
                <span class="onboard-dot"></span>
                <span class="onboard-dot"></span>
            </div>

            <!-- Footer Action Buttons -->
            <div class="onboard-footer">
                <button type="button" class="onboard-btn-skip" onclick="closeOnboardingTour(true)">Lewati</button>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="onboard-btn-skip" id="onboardBtnPrev" onclick="prevOnboardSlide()" style="display:none; color: var(--text-primary); font-weight: 700;">Kembali</button>
                    <button type="button" class="onboard-btn-next" id="onboardBtnNext" onclick="nextOnboardSlide()">Lanjut →</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const slides = [
        {
            gradient: 'linear-gradient(135deg, #064E3B, #059669)',
            icon: '💵',
            badge: 'Langkah 1 dari 5 • Keuangan',
            badgeBg: '#ECFDF5',
            badgeColor: '#059669',
            title: 'Keuangan & Scan Nota OCR',
            desc: 'Kendalikan arus kas harian, dompet bank, dan hemat waktu dengan pemindai AI.',
            list: [
                '<div><span>📸</span> <span><strong>Scan OCR Struk:</strong> Foto nota belanja dan total harga terisi otomatis.</span></div>',
                '<div><span>💳</span> <span><strong>Multi-Dompet:</strong> Pisahkan saldo rekening BCA, Mandiri, Cash, dan E-Wallet.</span></div>',
                '<div><span>📊</span> <span><strong>Budgeting & Laporan:</strong> Monitor batas anggaran dan cetak laporan PDF/Excel.</span></div>'
            ]
        },
        {
            gradient: 'linear-gradient(135deg, #3730A3, #6366F1)',
            icon: '🛒',
            badge: 'Langkah 2 dari 5 • Bisnis',
            badgeBg: '#EEF2FF',
            badgeColor: '#4F46E5',
            title: 'Mode Bisnis & Kasir POS',
            desc: 'Ubah aplikasi menjadi mesin kasir digital untuk toko, warung, atau usaha UMKM Anda.',
            list: [
                '<div><span>📦</span> <span><strong>Katalog Produk:</strong> Kelola harga modal, harga jual, dan stok barang.</span></div>',
                '<div><span>🧾</span> <span><strong>Kasir Cepat:</strong> Transaksi kilat tunai/QRIS dan cetak struk nota belanja.</span></div>',
                '<div><span>📈</span> <span><strong>Laporan Omzet:</strong> Pantau laba bersih terpisah dari kas pribadi Anda.</span></div>'
            ]
        },
        {
            gradient: 'linear-gradient(135deg, #065F46, #10B981)',
            icon: '🏘️',
            badge: 'Langkah 3 dari 5 • Komunitas',
            badgeBg: '#ECFDF5',
            badgeColor: '#059669',
            title: 'Sistem RT, Pinjam Alat & Titip Belanja',
            desc: 'Terhubung dengan tetangga sekitar untuk saling bantu dan manfaatkan fasilitas RT.',
            list: [
                '<div><span>🔑</span> <span><strong>Grup RT Warga:</strong> Masuk dengan Kode Unik RT atau pindai QR Ketua RT.</span></div>',
                '<div><span>🔨</span> <span><strong>Pinjam Alat:</strong> Pinjam bor, mesin rumput, atau tenda dengan validasi token QR aman.</span></div>',
                '<div><span>🛍️</span> <span><strong>Titip Belanja:</strong> Buka sesi belanja pasar dan bantu tetangga sekitar.</span></div>'
            ]
        },
        {
            gradient: 'linear-gradient(135deg, #6B21A8, #A855F7)',
            icon: '🏷️',
            badge: 'Langkah 4 dari 5 • Jual Beli',
            badgeBg: '#FAF5FF',
            badgeColor: '#9333EA',
            title: 'Marketplace & Domain Toko Gratis',
            desc: 'Jual barang bekas tak terpakai dan dapatkan website etalase toko pribadi gratis.',
            list: [
                '<div><span>🌐</span> <span><strong>Domain Toko:</strong> Dapatkan URL etalase toko publik gratis untuk jualan online.</span></div>',
                '<div><span>💬</span> <span><strong>Chat Langsung:</strong> Komunikasi aman antar penjual dan pembeli via aplikasi / WA.</span></div>'
            ]
        },
        {
            gradient: 'linear-gradient(135deg, #9D174D, #F43F5E)',
            icon: '⏰',
            badge: 'Langkah 5 dari 5 • Lifestyle',
            badgeBg: '#FFF1F2',
            badgeColor: '#E11D48',
            title: 'Tagihan, Tabungan, Traveling & TV',
            desc: 'Fitur pendukung lengkap untuk memenuhi seluruh kebutuhan gaya hidup dan finansial Anda.',
            list: [
                '<div><span>⏰</span> <span><strong>Pengingat Tagihan:</strong> Reminder jatuh tempo listrik, cicilan & pajak kendaraan.</span></div>',
                '<div><span>🎯</span> <span><strong>Target Menabung:</strong> Capai impian kurban, liburan, atau dana darurat.</span></div>',
                '<div><span>✈️</span> <span><strong>Traveling & Valas:</strong> Trip budget organizer & konversi kurs mata uang asing.</span></div>',
                '<div><span>🎬</span> <span><strong>TV & Game:</strong> Streaming TV digital nasional dan game hub santai.</span></div>'
            ]
        }
    ];

    let currentSlide = 0;
    const overlay   = document.getElementById('onboardingOverlay');
    const graphic   = document.getElementById('onboardGraphic');
    const icon      = document.getElementById('onboardIcon');
    const badge     = document.getElementById('onboardBadge');
    const title     = document.getElementById('onboardTitle');
    const desc      = document.getElementById('onboardDesc');
    const miniList  = document.getElementById('onboardMiniList');
    const dots      = document.querySelectorAll('#onboardDots .onboard-dot');
    const btnPrev   = document.getElementById('onboardBtnPrev');
    const btnNext   = document.getElementById('onboardBtnNext');

    function renderSlide(index) {
        const s = slides[index];
        graphic.style.background = s.gradient;
        icon.textContent = s.icon;
        badge.textContent = s.badge;
        badge.style.background = s.badgeBg;
        badge.style.color = s.badgeColor;
        title.textContent = s.title;
        desc.textContent = s.desc;
        miniList.innerHTML = s.list.join('');
        btnNext.style.background = s.badgeColor;

        dots.forEach((d, i) => {
            if (i === index) {
                d.classList.add('active');
                d.style.background = s.badgeColor;
            } else {
                d.classList.remove('active');
                d.style.background = 'var(--border, #CBD5E1)';
            }
        });

        btnPrev.style.display = (index > 0) ? 'inline-block' : 'none';
        btnNext.textContent = (index === slides.length - 1) ? 'Mulai Sekarang 🚀' : 'Lanjut →';
    }

    window.nextOnboardSlide = function() {
        if (currentSlide < slides.length - 1) {
            currentSlide++;
            renderSlide(currentSlide);
        } else {
            closeOnboardingTour(true);
        }
    };

    window.prevOnboardSlide = function() {
        if (currentSlide > 0) {
            currentSlide--;
            renderSlide(currentSlide);
        }
    };

    window.openOnboardingTour = function() {
        currentSlide = 0;
        renderSlide(0);
        overlay.classList.add('show');
    };

    window.closeOnboardingTour = function(markAsSeen) {
        overlay.classList.remove('show');
        if (markAsSeen) {
            try {
                localStorage.setItem('duitku_onboard_seen_v1', 'true');
            } catch(e) {}
        }
    };

    // Auto-trigger on first visit
    try {
        const seen = localStorage.getItem('duitku_onboard_seen_v1');
        if (!seen) {
            setTimeout(function() {
                window.openOnboardingTour();
            }, 800);
        }
    } catch(e) {}
})();
</script>
