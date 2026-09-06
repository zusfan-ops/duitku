<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.join-page {
    max-width: 580px;
    margin: 0 auto;
    padding-bottom: 120px;
}

/* ── Hero RT Join Banner ── */
.join-hero-card {
    background: linear-gradient(135deg, #064E3B 0%, #059669 60%, #10B981 100%);
    border-radius: var(--radius-xl, 24px);
    padding: 24px 22px;
    color: #ffffff;
    margin-bottom: 18px;
    box-shadow: 0 12px 32px rgba(5, 150, 105, 0.28);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.15);
    text-align: center;
}
.join-hero-card::after {
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
.join-icon-bubble {
    width: 64px;
    height: 64px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(8px);
    border: 1.5px solid rgba(255, 255, 255, 0.35);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    margin-bottom: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
.join-hero-title {
    font-size: 22px;
    font-weight: 900;
    margin-bottom: 6px;
    letter-spacing: -0.4px;
}
.join-hero-desc {
    font-size: 13px;
    line-height: 1.5;
    opacity: 0.9;
    max-width: 90%;
    margin: 0 auto;
}

/* ── Join Form Card ── */
.join-form-card {
    background: var(--bg-card, #ffffff);
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: var(--radius-xl, 24px);
    padding: 24px;
    box-shadow: var(--shadow-md, 0 4px 14px rgba(15,23,42,0.06));
    margin-bottom: 18px;
}
.join-form-group {
    margin-bottom: 18px;
}
.join-form-label {
    display: block;
    font-size: 12.5px;
    font-weight: 800;
    color: var(--text-primary, #0F172A);
    margin-bottom: 8px;
    letter-spacing: -0.2px;
}
.join-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}
.join-input-icon {
    position: absolute;
    left: 14px;
    font-size: 17px;
    color: var(--text-muted, #94A3B8);
    pointer-events: none;
}
.join-input-text {
    width: 100%;
    padding: 12px 14px 12px 42px;
    font-size: 14px;
    font-weight: 600;
    font-family: inherit;
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: var(--radius-md, 14px);
    background: var(--bg, #F8FAFC);
    color: var(--text-primary, #0F172A);
    outline: none;
    transition: all 0.2s ease;
}
.join-input-text:focus {
    background: var(--bg-card, #ffffff);
    border-color: var(--primary, #059669);
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12);
}
.join-input-text.uppercase-code {
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 800;
    font-family: monospace;
}
.join-helper-text {
    font-size: 11.5px;
    color: var(--text-muted, #64748B);
    margin-top: 6px;
    line-height: 1.4;
}

/* ── Custom Styled Radio Cards ── */
.residence-options-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}
.residence-option-label {
    position: relative;
    display: flex;
    flex-direction: column;
    padding: 14px 12px;
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: var(--radius-md, 14px);
    background: var(--bg, #F8FAFC);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.residence-option-label:hover {
    border-color: var(--primary-light, #10B981);
}
.residence-option-input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.residence-option-input:checked + .residence-option-label {
    border-color: var(--primary, #059669);
    background: rgba(5, 150, 105, 0.05);
    box-shadow: 0 0 0 1px var(--primary, #059669);
}
.res-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
}
.res-card-title {
    font-size: 13px;
    font-weight: 800;
    color: var(--text-primary, #0F172A);
}
.res-card-check {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 1.5px solid var(--border, #CBD5E1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: transparent;
    transition: all 0.2s ease;
}
.residence-option-input:checked + .residence-option-label .res-card-check {
    border-color: var(--primary, #059669);
    background: var(--primary, #059669);
    color: #ffffff;
}
.res-card-desc {
    font-size: 11px;
    color: var(--text-secondary, #64748B);
    line-height: 1.35;
}

/* ── Submit Button ── */
.btn-join-submit {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 30px;
    background: linear-gradient(135deg, #059669 0%, #10B981 100%);
    color: #ffffff;
    font-size: 14.5px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(5, 150, 105, 0.35);
    transition: all 0.2s ease;
    font-family: inherit;
}
.btn-join-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(5, 150, 105, 0.45);
}
.btn-join-submit:active {
    transform: translateY(1px);
}

/* ── Secondary Create RT Card ── */
.create-rt-card-prompt {
    background: var(--bg-card, #ffffff);
    border: 1.5px dashed var(--border, #CBD5E1);
    border-radius: var(--radius-xl, 24px);
    padding: 20px;
    text-align: center;
}
.btn-create-rt-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 20px;
    border-radius: 30px;
    background: var(--bg, #F8FAFC);
    border: 1.5px solid var(--border, #CBD5E1);
    color: var(--text-primary, #0F172A);
    font-size: 13px;
    font-weight: 800;
    text-decoration: none;
    transition: all 0.2s ease;
    margin-top: 10px;
}
.btn-create-rt-link:hover {
    border-color: var(--primary, #059669);
    color: var(--primary, #059669);
    background: rgba(5, 150, 105, 0.05);
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="join-page">
    
    <!-- ── Hero RT Join Banner ── -->
    <div class="join-hero-card">
        <div class="join-icon-bubble">🏘️</div>
        <h1 class="join-hero-title">Gabung Komunitas RT</h1>
        <p class="join-hero-desc">
            Hubungkan akun Anda dengan lingkungan RT setempat untuk menikmati pinjam alat bersama, titip belanja antar-tetangga, dan transparansi kas RT.
        </p>
    </div>

    <!-- ── Join Form Card ── -->
    <div class="join-form-card">
        <form id="joinRtForm">
            
            <!-- Kode Unik RT -->
            <div class="join-form-group">
                <label class="join-form-label" for="uniqueCodeInput">🔑 Kode Unik RT</label>
                <div class="join-input-wrapper">
                    <span class="join-input-icon">🏷️</span>
                    <input type="text" id="uniqueCodeInput" name="unique_code" class="join-input-text uppercase-code" placeholder="Contoh: RT04-RW02-GRIYA-2026" required autocomplete="off">
                </div>
                <div class="join-helper-text">Minta kode unik RT ini kepada Ketua RT atau tetangga yang sudah bergabung.</div>
            </div>

            <!-- Status Tempat Tinggal (Custom Radio Cards) -->
            <div class="join-form-group">
                <label class="join-form-label">🏠 Status Tempat Tinggal</label>
                <div class="residence-options-grid">
                    <div>
                        <input type="radio" name="residence_status" id="resPermanent" value="permanent" class="residence-option-input" checked>
                        <label for="resPermanent" class="residence-option-label">
                            <div class="res-card-top">
                                <span class="res-card-title">🏠 Warga Tetap</span>
                                <div class="res-card-check">✓</div>
                            </div>
                            <span class="res-card-desc">KTP asli beralamat di RT ini atau pemilik rumah tetap.</span>
                        </label>
                    </div>

                    <div>
                        <input type="radio" name="residence_status" id="resTemporary" value="temporary" class="residence-option-input">
                        <label for="resTemporary" class="residence-option-label">
                            <div class="res-card-top">
                                <span class="res-card-title">🏢 Domisili / Kontrak</span>
                                <div class="res-card-check">✓</div>
                            </div>
                            <span class="res-card-desc">Penyewa rumah, kontrakan, atau indekos setempat.</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Nomor Rumah / Blok -->
            <div class="join-form-group">
                <label class="join-form-label" for="houseNumberInput">📍 Nomor Rumah / Blok</label>
                <div class="join-input-wrapper">
                    <span class="join-input-icon">🚪</span>
                    <input type="text" id="houseNumberInput" name="house_number" class="join-input-text" placeholder="Contoh: Blok B No. 12 / Jl. Mawar No. 4" required>
                </div>
            </div>

            <button type="submit" class="btn-join-submit" id="btnSubmitJoin">
                <span>Bergabung Sekarang</span>
                <span>→</span>
            </button>
        </form>
    </div>

    <!-- ── Secondary Create RT Card ── -->
    <div class="create-rt-card-prompt">
        <div style="font-size: 26px; margin-bottom: 6px;">👑</div>
        <h3 style="font-size: 15px; font-weight: 800; margin-bottom: 4px; color: var(--text-primary);">Anda Pengurus atau Ketua RT?</h3>
        <p style="font-size: 12.5px; color: var(--text-secondary); margin-bottom: 8px;">
            Inisiasi lingkungan RT Anda agar seluruh warga dapat saling terhubung dan berbagi alat.
        </p>
        <a href="/neighborhood/create" class="btn-create-rt-link">
            <span>+</span>
            <span>Daftarkan Lingkungan RT Baru</span>
        </a>
    </div>

</div>

<script>
document.getElementById('joinRtForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitJoin');
    btn.disabled = true;
    btn.innerHTML = '<span>Memproses...</span>';

    const formData = new FormData(this);

    fetch('/neighborhood/join', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Berhasil bergabung!');
            window.location.href = '/neighborhood';
        } else {
            alert(data.message || 'Gagal bergabung. Periksa kembali kode unik RT Anda.');
            btn.disabled = false;
            btn.innerHTML = '<span>Bergabung Sekarang</span> <span>→</span>';
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan jaringan.');
        btn.disabled = false;
        btn.innerHTML = '<span>Bergabung Sekarang</span> <span>→</span>';
    });
});
</script>
<?= $this->endSection() ?>
