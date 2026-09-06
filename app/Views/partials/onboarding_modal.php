<!-- ONBOARDING SYSTEM MODAL TOUR -->
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
    height: 170px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    color: #ffffff;
    text-align: center;
    padding: 20px;
}
.onboard-icon-bubble {
    width: 76px;
    height: 76px;
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.22);
    backdrop-filter: blur(10px);
    border: 1.5px solid rgba(255, 255, 255, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 38px;
    margin-bottom: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    animation: bubbleFloat 3s ease-in-out infinite;
}
@keyframes bubbleFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-6px); }
}
.onboard-body {
    padding: 24px 22px 18px;
    text-align: center;
}
.onboard-step-badge {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    margin-bottom: 8px;
}
.onboard-title {
    font-size: 20px;
    font-weight: 900;
    color: var(--text-primary, #111827);
    margin-bottom: 8px;
    line-height: 1.3;
}
.onboard-desc {
    font-size: 13.5px;
    color: var(--text-secondary, #6B7280);
    line-height: 1.55;
    margin-bottom: 16px;
}
.onboard-features-mini {
    background: var(--bg, #F9FAFB);
    border: 1px solid var(--border-color, #E5E7EB);
    border-radius: 16px;
    padding: 12px 14px;
    text-align: left;
    display: flex;
    flex-direction: column;
    gap: 8px;
    font-size: 12.5px;
    color: var(--text-primary, #374151);
    margin-bottom: 18px;
}
.onboard-features-mini div {
    display: flex;
    align-items: center;
    gap: 8px;
}
.onboard-dots {
    display: flex;
    justify-content: center;
    gap: 6px;
    margin-bottom: 18px;
}
.onboard-dot {
    width: 8px;
    height: 8px;
    border-radius: 4px;
    background: var(--border-color, #D1D5DB);
    transition: all 0.3s ease;
}
.onboard-dot.active {
    width: 24px;
    background: #059669;
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
    color: var(--text-muted, #9CA3AF);
    font-size: 13px;
    font-weight: 700;
    padding: 8px 12px;
    cursor: pointer;
}
.onboard-btn-next {
    background: #059669;
    color: #ffffff;
    border: none;
    border-radius: 30px;
    font-size: 14px;
    font-weight: 800;
    padding: 10px 24px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);
    transition: all 0.2s ease;
}
.onboard-btn-next:hover {
    background: #047857;
    transform: translateY(-1px);
}
</style>

<div class="onboard-overlay" id="onboardingOverlay">
    <div class="onboard-card" id="onboardCard">
        
        <!-- Header Graphic Gradient -->
        <div class="onboard-header-graphic" id="onboardGraphic" style="background: linear-gradient(135deg, #059669, #10B981);">
            <div class="onboard-icon-bubble" id="onboardIcon">
                ✨
            </div>
        </div>

        <!-- Content Body -->
        <div class="onboard-body">
            <span class="onboard-step-badge" id="onboardBadge" style="background:#ECFDF5;color:#059669;">Langkah 1 dari 5</span>
            <h4 class="onboard-title" id="onboardTitle">Selamat Datang di DuitKu</h4>
            <p class="onboard-desc" id="onboardDesc">Aplikasi serbaguna untuk keuangan pribadi, bisnis UMKM, dan platform komunitas RT rukun warga.</p>
            
            <div class="onboard-features-mini" id="onboardMiniList">
                <div><span>💵</span> <strong>Catatan Keuangan:</strong> Pemasukan, pengeluaran &amp; scan nota OCR otomatis.</div>
                <div><span>🏛️</span> <strong>Multi-Dompet:</strong> Rekening bank, e-wallet &amp; dompet bersama.</div>
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
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3 fw-bold" id="onboardBtnPrev" onclick="prevOnboardSlide()" style="display:none;">Kembali</button>
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
            gradient: 'linear-gradient(135deg, #059669, #10B981)',
            icon: '✨',
            badge: 'Langkah 1 dari 5',
            badgeBg: '#ECFDF5',
            badgeColor: '#059669',
            title: 'Selamat Datang di DuitKu',
            desc: 'Platform terintegrasi pencatatan keuangan pribadi, usaha kasir POS, dan komunitas lingkungan rukun warga (RT).',
            list: [
                '<div><span>💵</span> <strong>Catatan Keuangan:</strong> Pemasukan, pengeluaran, tagihan & scan struk OCR otomatis.</div>',
                '<div><span>💳</span> <strong>Multi-Dompet:</strong> Kelola rekening bank, e-wallet & tabungan impian.</div>'
            ]
        },
        {
            gradient: 'linear-gradient(135deg, #065F46, #059669)',
            icon: '🏘️',
            badge: 'Langkah 2 dari 5',
            badgeBg: '#ECFDF5',
            badgeColor: '#059669',
            title: 'Sistem Komunitas RT',
            desc: 'Hubungkan akun Anda dengan lingkungan RT setempat untuk verifikasi warga asli, transparansi kas, dan silaturahmi.',
            list: [
                '<div><span>🔑</span> <strong>Kode Unik RT:</strong> Cukup masukkan kode unik atau scan QR dari Ketua RT.</div>',
                '<div><span>🏠</span> <strong>Status Warga:</strong> Terbagi jelas antara Warga Tetap (KTP) dan Warga Domisili/Kontrak.</div>',
                '<div><span>🤝</span> <strong>Sistem Vouching:</strong> Warga dapat diaktifkan otomatis lewat jaminan 2 tetangga.</div>'
            ]
        },
        {
            gradient: 'linear-gradient(135deg, #B45309, #F59E0B)',
            icon: '🔨',
            badge: 'Langkah 3 dari 5',
            badgeBg: '#FFFBEB',
            badgeColor: '#B45309',
            title: 'Pinjam Alat Warga (Sharing)',
            desc: 'Pinjam mesin rumput, bor listrik, tangga lipat, atau tenda RT secara praktis tanpa ribet.',
            list: [
                '<div><span>🔐</span> <strong>Validasi Token QR:</strong> Serah terima aman dengan kode token rahasia peminjam.</div>',
                '<div><span>💰</span> <strong>Otomatisasi Deposit:</strong> Uang jaminan sewa otomatis dicatat di buku kas dan di-refund saat alat kembali utuh.</div>'
            ]
        },
        {
            gradient: 'linear-gradient(135deg, #1E40AF, #3B82F6)',
            icon: '🛍️',
            badge: 'Langkah 4 dari 5',
            badgeBg: '#EFF6FF',
            badgeColor: '#1E40AF',
            title: 'Titip Belanja Antar-Tetangga',
            desc: 'Mau ke pasar atau supermarket? Buka sesi belanja dan bantu tetangga yang membutuhkan kebutuhan harian.',
            list: [
                '<div><span>🛒</span> <strong>Jastip Mudah:</strong> Warga satu RT bisa menitip pesanan sebelum batas waktu (cutoff).</div>',
                '<div><span>🧾</span> <strong>Struk & Keuangan:</strong> Biaya belanja riil + tip jasa otomatis dicatat ke buku keuangan kedua pihak.</div>'
            ]
        },
        {
            gradient: 'linear-gradient(135deg, #4338CA, #7C3AED)',
            icon: '📦',
            badge: 'Langkah 5 dari 5',
            badgeBg: '#EEF2FF',
            badgeColor: '#4338CA',
            title: 'Pasar Jual Beli & Domain Toko',
            desc: 'Jual barang bekas, tawarkan jasa, dan dapatkan domain tautan toko pribadi Anda secara gratis.',
            list: [
                '<div><span>🌐</span> <strong>Domain Toko:</strong> Dapatkan link <code>domain/nama_anda</code> untuk etalase publik.</div>',
                '<div><span>💬</span> <strong>Chat & WhatsApp:</strong> Hubungkan langsung dengan pembeli secara aman dan instan.</div>'
            ]
        }
    ];

    let currentSlide = 0;

    function renderSlide(index) {
        const s = slides[index];
        const graphic = document.getElementById('onboardGraphic');
        const icon = document.getElementById('onboardIcon');
        const badge = document.getElementById('onboardBadge');
        const title = document.getElementById('onboardTitle');
        const desc = document.getElementById('onboardDesc');
        const list = document.getElementById('onboardMiniList');
        const dots = document.querySelectorAll('.onboard-dot');
        const btnPrev = document.getElementById('onboardBtnPrev');
        const btnNext = document.getElementById('onboardBtnNext');

        graphic.style.background = s.gradient;
        icon.innerText = s.icon;
        badge.innerText = s.badge;
        badge.style.background = s.badgeBg;
        badge.style.color = s.badgeColor;
        title.innerText = s.title;
        desc.innerText = s.desc;
        list.innerHTML = s.list.join('');

        dots.forEach((dot, i) => {
            if (i === index) dot.classList.add('active');
            else dot.classList.remove('active');
        });

        btnPrev.style.display = index > 0 ? 'block' : 'none';
        btnNext.innerText = index === slides.length - 1 ? 'Mulai Sekarang 🚀' : 'Lanjut →';
    }

    window.openOnboardingTour = function() {
        currentSlide = 0;
        renderSlide(currentSlide);
        document.getElementById('onboardingOverlay').classList.add('show');
    };

    window.closeOnboardingTour = function(markAsSeen = true) {
        document.getElementById('onboardingOverlay').classList.remove('show');
        if (markAsSeen) {
            localStorage.setItem('duitku_onboard_v1', 'seen');
        }
    };

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

    // Auto-trigger on first user visit
    document.addEventListener('DOMContentLoaded', () => {
        const hasSeen = localStorage.getItem('duitku_onboard_v1');
        if (!hasSeen) {
            setTimeout(() => {
                window.openOnboardingTour();
            }, 800);
        }
    });
})();
</script>
