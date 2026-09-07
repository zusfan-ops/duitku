<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.policy-page {
    max-width: 720px;
    margin: 0 auto;
    padding: 24px 16px 120px;
}
.policy-hero {
    background: linear-gradient(135deg, #7C3AED 0%, #6D28D9 50%, #4C1D95 100%);
    border-radius: var(--radius-xl, 24px);
    padding: 28px 24px;
    color: #fff;
    margin-bottom: 24px;
    box-shadow: 0 12px 32px rgba(109,40,217,0.28);
    position: relative;
    overflow: hidden;
}
.policy-hero::after {
    content: '';
    position: absolute;
    right: -25px;
    bottom: -25px;
    width: 140px;
    height: 140px;
    background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
}
.policy-hero h1 {
    font-size: 24px;
    font-weight: 900;
    margin-bottom: 6px;
    letter-spacing: -0.4px;
}
.policy-hero p {
    font-size: 13px;
    opacity: 0.9;
    line-height: 1.5;
}
.policy-hero .pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.35);
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.4px;
    margin-bottom: 10px;
    color: #fff;
}
.policy-section {
    background: var(--bg-card, #fff);
    border: 1px solid var(--border, #E5E7EB);
    border-radius: 18px;
    padding: 22px 20px;
    margin-bottom: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.policy-section h2 {
    font-size: 16px;
    font-weight: 800;
    margin-bottom: 10px;
    color: var(--text-primary, #0F172A);
}
.policy-section h3 {
    font-size: 14px;
    font-weight: 700;
    margin: 14px 0 6px;
    color: var(--text-primary, #0F172A);
}
.policy-section p, .policy-section li {
    font-size: 13px;
    line-height: 1.7;
    color: var(--text-secondary, #475569);
    margin-bottom: 8px;
}
.policy-section ul {
    padding-left: 20px;
    margin-bottom: 10px;
}
.policy-section li {
    margin-bottom: 4px;
}
.policy-section .warn {
    background: #FEF2F2;
    border-left: 4px solid #DC2626;
    padding: 12px 16px;
    border-radius: 0 12px 12px 0;
    margin: 12px 0;
}
.policy-section .warn p {
    margin: 0;
    font-weight: 600;
    color: #991B1B;
}
.policy-section .info {
    background: #EFF6FF;
    border-left: 4px solid #2563EB;
    padding: 12px 16px;
    border-radius: 0 12px 12px 0;
    margin: 12px 0;
}
.policy-section .info p {
    margin: 0;
    font-weight: 600;
    color: #1E40AF;
}
.category-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin: 12px 0;
}
.category-card {
    padding: 12px;
    border-radius: 12px;
    border: 1px solid var(--border, #E5E7EB);
    background: var(--bg, #F8FAFC);
}
.category-card .emoji {
    font-size: 20px;
    margin-bottom: 4px;
}
.category-card .label {
    font-size: 12px;
    font-weight: 800;
    color: var(--text-primary, #0F172A);
}
.category-card .desc {
    font-size: 11px;
    color: var(--text-muted, #94A3B8);
    margin-top: 2px;
}
.policy-footer {
    text-align: center;
    padding: 20px 0;
    font-size: 12px;
    color: var(--text-muted, #94A3B8);
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="policy-page">

    <div class="policy-hero">
        <div class="pill">🛡️ MODERASI</div>
        <h1>Kebijakan Moderasi Konten</h1>
        <p>Terakhir diperbarui: <?= date('d F Y') ?></p>
        <p style="margin-top:8px;">DuitKu berkomitmen menciptakan lingkungan yang aman, saling menghormati, dan bebas dari penyalahgunaan untuk semua pengguna.</p>
    </div>

    <div class="policy-section">
        <h2>1. Standar Komunitas</h2>
        <p>Semua pengguna DuitKu wajib mematuhi standar komunitas berikut saat menggunakan fitur marketplace, chat, status/stories, forum diskusi RT, dan fitur lainnya:</p>

        <div class="category-grid">
            <div class="category-card">
                <div class="emoji">✅</div>
                <div class="label">Diperbolehkan</div>
                <div class="desc">Jual beli barang legal, chat sopan, foto produk asli</div>
            </div>
            <div class="category-card">
                <div class="emoji">❌</div>
                <div class="label">Dilarang</div>
                <div class="desc">Penipuan, konten dewasa, kekerasan, spam</div>
            </div>
        </div>
    </div>

    <div class="policy-section">
        <h2>2. Konten yang DILARANG</h2>
        <p>Pengguna dilarang mempublikasikan, mengirim, atau membagikan konten berikut:</p>
        <ul>
            <li><strong>Penipuan & Scam:</strong> Listing palsu, harga manipulatif, transfer DP palsu, identitas palsu.</li>
            <li><strong>Konten Seksual:</strong> Foto/film dewasa, pornografi, eksploitasi seksual.</li>
            <li><strong>Kekerasan:</strong> Ancaman, pelecehan, intimidasi, atau konten yang mempromosikan kekerasan.</li>
            <li><strong>Hate Speech:</strong> Ujaran kebencian berdasarkan Suku, Agama, Ras, dan Antargolongan (SARA).</li>
            <li><strong>Spam & Phishing:</strong> Pesan massal yang tidak diinginkan, link phishing, jual beli data pribadi.</li>
            <li><strong>Barang Ilegal:</strong> Narkoba, senjata api, barang curian, obat tanpa izin, atau barang yang melanggar hukum Indonesia.</li>
            <li><strong>Hak Cipta:</strong> Menjual konten bajakan, software ilegal, atau materi yang melanggar hak kekayaan intelektual.</li>
            <li><strong>Identitas Palsu:</strong> Mengaku sebagai orang lain, menggunakan foto profil orang lain tanpa izin.</li>
        </ul>

        <div class="warn">
            <p>⚠️ Pelanggaran berat (penipuan, konten ilegal) akan mengakibatkan BANNED PERMANEN tanpa peringatan.</p>
        </div>
    </div>

    <div class="policy-section">
        <h2>3. Sistem Pelaporan (Report)</h2>
        <p>DuitKu menyediakan fitur pelaporan untuk menjaga keamanan komunitas:</p>
        <ul>
            <li><strong>Report Pengguna:</strong> Laporkan pengguna yang melakukan pelanggaran melalui menu obrolan (chat) → ⋮ → Laporkan Pengguna.</li>
            <li><strong>Report Iklan/Listing:</strong> Laporkan iklan marketplace yang mencurigakan atau melanggar kebijakan.</li>
            <li><strong>Report Komentar:</strong> Laporkan komentar yang tidak pantas di iklan atau diskusi.</li>
        </ul>
        <div class="info">
            <p>📩 Semua laporan akan ditinjau oleh admin dalam waktu 1×24 jam. Identitas pelapor dirahasiakan.</p>
        </div>
    </div>

    <div class="policy-section">
        <h2>4. Sistem Pemblokiran (Block)</h2>
        <p>Kamu bisa memblokir pengguna lain untuk mencegah mereka menghubungimu:</p>
        <ul>
            <li>Buka obrolan dengan pengguna → ⋮ → <strong>Blokir Pengguna</strong>.</li>
            <li>Pemblokiran bersifat <strong>dua arah</strong>: kamu dan pengguna yang diblokir tidak bisa saling mengirim pesan.</li>
            <li>Pemblokiran tidak menghapus riwayat obrolan yang sudah ada.</li>
            <li>Kamu bisa membuka blokir kapan saja melalui Pengaturan → Daftar Blokir.</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>5. Proses Moderasi</h2>
        <h3>Setiap laporan akan melalui proses berikut:</h3>
        <ul>
            <li><strong>Step 1 — Laporan Diterima:</strong> Sistem mencatat laporan dan mengirim notifikasi ke admin.</li>
            <li><strong>Step 2 — Review Manual:</strong> Admin meninjau konten yang dilaporkan dan konteks sekitarnya.</li>
            <li><strong>Step 3 — Keputusan:</strong> Admin dapat menghapus konten, memperingatkan, atau memblokir pengguna.</li>
            <li><strong>Step 4 — Notifikasi:</strong> Pelapor menerima notifikasi tentang hasil tinjauan (tanpa mengungkap detail aksi).</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>6. Sanksi & Hukuman</h2>
        <p>Pelanggaran akan dikenakan sanksi berdasarkan tingkat keparahan:</p>
        <ul>
            <li><strong>Peringatan Pertama:</strong> Pesan peringatan via notifikasi untuk pelanggaran ringan (spam, konten tidak relevan).</li>
            <li><strong>Pemblokiran Sementara:</strong> Akun diblokir 3-7 hari untuk pelanggaran berulang.</li>
            <li><strong>Pemblokiran Permanen:</strong> Akun dihapus permanen untuk pelanggaran berat (penipuan, konten ilegal, kekerasan).</li>
            <li><strong>Laporan ke Pihak Berwajib:</strong> Untuk pelanggaran yang melanggar hukum Indonesia (UU ITE, pidana), data akan diserahkan kepada pihak berwajib.</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>7. Marketplace — Aturan Khusus</h2>
        <ul>
            <li>Foto produk harus <strong>asli</strong> (bukan screenshot dari toko lain).</li>
            <li>Harga harus <strong>realistis</strong> dan sesuai dengan kondisi barang.</li>
            <li>Lokasi COD harus di <strong>tempat umum</strong> yang aman.</li>
            <li>Deskripsi harus <strong>jelas dan jujur</strong> tentang kondisi barang.</li>
            <li>Dilarang menjual barang yang termasuk kategori <strong>dilarang</strong> (lihat pasal 2).</li>
            <li>Gunakan fitur <strong>Anti-Penipuan</strong> yang tersedia di setiap listing.</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>8. Chat & Pesan Langsung</h2>
        <ul>
            <li>Dilarang mengirim pesan yang mengandung <strong>pelecehan, ancaman, atau konten tidak pantas</strong>.</li>
            <li>Dilarang melakukan <strong>spam</strong> atau mengirim pesan massal tanpa izin.</li>
            <li>Dilarang membagikan <strong>data pribadi orang lain</strong> tanpa izin (doxxing).</li>
            <li>Gunakan fitur <strong>Blokir & Laporkan</strong> jika menerima pesan yang tidak diinginkan.</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>9. Status / Stories</h2>
        <ul>
            <li>Status/stories hanya terlihat oleh <strong>teman yang diterima</strong> (bukan publik).</li>
            <li>Konten status harus mematuhi standar komunitas (pasal 2).</li>
            <li>Kamu bisa menghapus status kapan saja.</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>10. Banding & Kontak</h2>
        <p>Jika kamu merasa akun atau konten kamu dikunci secara tidak adil, kamu bisa mengajukan banding:</p>
        <ul>
            <li><strong>Email:</strong> moderasi@duitku.ordr.my.id</li>
            <li><strong>Developer:</strong> Zusfan Mashuri</li>
            <li><strong>Website:</strong> <a href="https://duitku.ordr.my.id" style="color:#6D28D9;font-weight:700;">duitku.ordr.my.id</a></li>
        </ul>
        <p>Banding akan ditinjau dalam waktu <strong>3 hari kerja</strong>.</p>
    </div>

    <div class="policy-footer">
        <p>© <?= date('Y') ?> DuitKu — Bersama kita ciptakan komunitas yang aman dan saling menghormati.</p>
    </div>

</div>
<?= $this->endSection() ?>
