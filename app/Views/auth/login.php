<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<meta name="theme-color" content="#07221A">
<meta name="description" content="DuitKu — Aplikasi pencatat keuangan pribadi yang simpel, aman, dan gratis.">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="DuitKu">
<title>DuitKu — Kendalikan Uangmu</title>
<link rel="manifest" href="/manifest.json">
<link rel="icon" type="image/png" href="/images/logo.png">
<link rel="apple-touch-icon" href="/images/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: {
          sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
          mono: ['"JetBrains Mono"', 'monospace'],
        },
        colors: {
          ink:     { DEFAULT: '#07221A', 800: '#0B2C22', 700: '#103A2C' },
          emerald: { 50:'#E6F6EE', 100:'#C9EBD9', 400:'#34D399', 500:'#13A36C', 600:'#0E9F6E', 700:'#0B7A54' },
          paper:   '#FAFCFB',
        },
      },
    },
  };
</script>

<style>
  :root { --ease: cubic-bezier(.22,.61,.36,1); }
  html  { scroll-behavior: smooth; }
  body  { background: #FAFCFB; color: #07221A; -webkit-font-smoothing: antialiased; }

  .grid-bg {
    background-image:
      linear-gradient(to right,  rgba(7,34,26,.04) 1px, transparent 1px),
      linear-gradient(to bottom, rgba(7,34,26,.04) 1px, transparent 1px);
    background-size: 38px 38px;
    mask-image: radial-gradient(ellipse 90% 60% at 50% 0%, #000 40%, transparent 100%);
  }

  .fcard { transition: transform .35s var(--ease), box-shadow .35s var(--ease), border-color .35s var(--ease); }
  .fcard:hover { transform: translateY(-4px); box-shadow: 0 18px 40px -18px rgba(7,34,26,.22); border-color: #C9EBD9; }

  .phone-shadow { box-shadow: 0 40px 80px -30px rgba(7,34,26,.45), 0 8px 24px -12px rgba(7,34,26,.3); }
  .float { animation: float 6s ease-in-out infinite; }
  @keyframes float { 0%,100%{ transform:translateY(0) } 50%{ transform:translateY(-10px) } }

  .bar { transform-origin: bottom; animation: grow 1s var(--ease) both; }
  @keyframes grow { from { transform: scaleY(0); opacity:.3 } to { transform: scaleY(1); opacity:1 } }

  [data-reveal] { opacity:0; transform:translateY(18px); transition: opacity .7s var(--ease), transform .7s var(--ease); }
  [data-reveal].in { opacity:1; transform:none; }

  .tabnum { font-variant-numeric: tabular-nums; }

  a:focus-visible, button:focus-visible, input:focus-visible {
    outline: 2px solid #0E9F6E; outline-offset: 2px; border-radius: 8px;
  }

  /* FAQ slide */
  .faq-body { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .28s var(--ease); }
  .faq-body.open { grid-template-rows: 1fr; }
  .faq-inner { overflow: hidden; }

  [x-cloak] { display: none !important; }

  /* PWA banner */
  .pwa-install-banner {
    position: fixed; bottom: 16px; left: 50%; transform: translateX(-50%);
    z-index: 999; width: calc(100% - 32px); max-width: 400px;
    background: #fff; border-radius: 16px;
    box-shadow: 0 8px 32px rgba(7,34,26,.18);
    padding: 12px 14px;
    display: none; align-items: center; gap: 10px;
  }
  .pwa-install-banner.show { display: flex; }
  .pwa-icon { width: 40px; height: 40px; border-radius: 10px; object-fit: contain; }
  .pwa-text { flex: 1; min-width: 0; }
  .pwa-text strong { display: block; font-size: 13px; font-weight: 700; }
  .pwa-text span { font-size: 12px; color: #64748B; }
  .btn-install { background: #0E9F6E; color: #fff; border: none; border-radius: 10px; padding: 7px 16px; font-weight: 700; font-size: 13px; cursor: pointer; }
  .btn-close-pwa { background: none; border: none; font-size: 18px; color: #94A3B8; cursor: pointer; padding: 4px; }
</style>
</head>

<body class="font-sans antialiased" x-data="{ login: <?= session()->getFlashdata('error') ? 'true' : 'false' ?>, showPw: false, faq: null }">

<!-- ── PWA Install Banner ──────────────────────────────── -->
<div class="pwa-install-banner" id="pwaInstallBanner">
  <img src="/images/logo.png" alt="DuitKu" class="pwa-icon">
  <div class="pwa-text">
    <strong>Install DuitKu</strong>
    <span>Akses lebih cepat &amp; offline.</span>
  </div>
  <button class="btn-install" id="btnInstallPwa">Install</button>
  <button class="btn-close-pwa" id="btnClosePwa">✕</button>
</div>

<!-- ══════════════════════════════════════════════════════
     HEADER
══════════════════════════════════════════════════════ -->
<header class="sticky top-0 z-40 border-b border-ink/5 bg-paper/80 backdrop-blur-md">
  <div class="mx-auto flex max-w-5xl items-center justify-between px-5 py-3.5">
    <!-- Logo wordmark -->
    <a href="#" class="flex items-center gap-2.5">
      <img src="/images/logo.png" alt="DuitKu" class="h-8 w-8 rounded-xl object-contain">
      <span class="text-lg font-extrabold tracking-tight text-ink">DuitKu</span>
    </a>

    <!-- Nav links (desktop) -->
    <nav class="hidden items-center gap-7 text-sm font-semibold text-ink/60 md:flex">
      <a href="#fitur"  class="transition hover:text-ink">Fitur</a>
      <a href="#aman"   class="transition hover:text-ink">Keamanan</a>
      <a href="#mulai"  class="transition hover:text-ink">Cara Mulai</a>
      <a href="#faq"    class="transition hover:text-ink">FAQ</a>
    </nav>

    <!-- CTA buttons -->
    <div class="flex items-center gap-2">
      <button @click="login=true" class="rounded-xl px-3.5 py-2 text-sm font-semibold text-ink/70 transition hover:bg-ink/5">Masuk</button>
      <a href="/register" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">Daftar Gratis</a>
    </div>
  </div>
</header>

<!-- ══════════════════════════════════════════════════════
     HERO
══════════════════════════════════════════════════════ -->
<section class="relative overflow-hidden">
  <div class="grid-bg pointer-events-none absolute inset-0"></div>
  <div class="relative mx-auto grid max-w-5xl items-center gap-12 px-5 pb-20 pt-14 md:grid-cols-2 md:pt-24">

    <!-- Copy -->
    <div data-reveal>
      <!-- Pill badge -->
      <span class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/90 px-3.5 py-1.5 text-xs font-semibold text-emerald-700 backdrop-blur">
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" style="animation: pulse-d 2s infinite"></span>
        Gratis selamanya · Tanpa iklan
      </span>
      <style>@keyframes pulse-d{0%,100%{opacity:1}50%{opacity:.35}}</style>

      <h1 class="mt-5 text-[2.6rem] font-extrabold leading-[1.06] tracking-tight text-ink sm:text-5xl">
        Kendalikan<br>uangmu.
      </h1>
      <p class="mt-4 max-w-sm text-base leading-relaxed text-ink/60">
        Catat keuangan rumah tangga dalam hitungan detik. Lihat ke mana uangmu pergi tiap bulan — aman, simpel, dan sepenuhnya gratis.
      </p>

      <!-- CTA buttons -->
      <div class="mt-7 flex flex-wrap items-center gap-3">
        <a href="/register" class="group inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700">
          Mulai catat gratis
          <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 transition group-hover:translate-x-0.5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
        <button @click="login=true" class="inline-flex items-center gap-2 rounded-2xl border border-ink/10 bg-white px-6 py-3.5 text-sm font-bold text-ink transition hover:border-ink/25">
          Sudah punya akun
        </button>
      </div>

      <!-- Trust strip -->
      <div class="mt-8 flex flex-wrap gap-x-5 gap-y-3 text-xs font-semibold text-ink/50">
        <span class="inline-flex items-center gap-1.5">
          <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-emerald-600" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3Z"/><path d="m9 12 2 2 4-4"/></svg>
          Data terenkripsi
        </span>
        <span class="inline-flex items-center gap-1.5">
          <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-emerald-600" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>
          Tanpa pelacak
        </span>
        <span class="inline-flex items-center gap-1.5">
          <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-emerald-600" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="3" width="12" height="18" rx="2.5"/><path d="M11 18h2"/></svg>
          Install di HP (PWA)
        </span>
      </div>
    </div>

    <!-- Phone mockup -->
    <div class="relative flex justify-center md:justify-end" data-reveal>
      <div class="float relative w-[280px]">
        <div class="phone-shadow rounded-[2.6rem] border-[10px] border-ink bg-ink">
          <div class="overflow-hidden rounded-[1.9rem] bg-paper">

            <!-- App topbar -->
            <div class="bg-ink px-4 pb-5 pt-4 text-white">
              <div class="flex items-center justify-between text-[11px] text-white/45">
                <span>Juni 2026</span>
                <span class="inline-flex items-center gap-1">
                  <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Tersinkron
                </span>
              </div>
              <p class="mt-3 text-[10px] uppercase tracking-wider text-white/40">Saldo bulan ini</p>
              <p class="mt-1 font-mono text-2xl font-bold tabnum text-white"
                 x-data="{n:0}" x-init="let t=2840000,s=t/40,id=setInterval(()=>{n=Math.min(t,n+s);if(n>=t)clearInterval(id)},18)">
                Rp <span x-text="Math.round(n).toLocaleString('id-ID')"></span>
              </p>
              <div class="mt-3 flex gap-2 text-[10px]">
                <span class="rounded-lg bg-emerald-500/15 px-2 py-1 text-emerald-300">↑ Masuk Rp 5,2 jt</span>
                <span class="rounded-lg bg-white/10 px-2 py-1 text-white/65">↓ Keluar Rp 2,36 jt</span>
              </div>
            </div>

            <!-- Mini chart -->
            <div class="px-4 pt-4">
              <p class="text-[10px] font-semibold text-ink/40">Pengeluaran 6 bulan</p>
              <div class="mt-2.5 flex h-16 items-end justify-between gap-1.5">
                <template x-for="(h,i) in [42,58,38,66,50,72]" :key="i">
                  <div class="bar w-full rounded-md"
                       :class="i===5 ? 'bg-emerald-500' : 'bg-emerald-100'"
                       :style="`height:${h}%;animation-delay:${i*90}ms`"></div>
                </template>
              </div>
              <div class="mt-1 flex justify-between text-[9px] text-ink/30">
                <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span><span>Mei</span>
                <span class="font-bold text-emerald-600">Jun</span>
              </div>
            </div>

            <!-- Transaction list -->
            <div class="mt-3 space-y-0.5 px-3 pb-5">
              <template x-for="t in [
                {i:'🍳',c:'#FEF3E2',n:'Belanja Dapur',s:'Hari ini · 14:20',v:'-78.500'},
                {i:'⚡',c:'#E6F6EE',n:'Listrik PLN',s:'Kemarin · 09:05',v:'-235.000'},
                {i:'🎒',c:'#EAF1FF',n:'Sekolah Anak',s:'2 Jun · 07:30',v:'-150.000'}
              ]">
                <div class="flex items-center gap-3 rounded-xl px-2 py-2">
                  <span class="grid h-8 w-8 place-items-center rounded-xl text-sm flex-shrink-0" :style="`background:${t.c}`" x-text="t.i"></span>
                  <div class="min-w-0 flex-1">
                    <p class="truncate text-[12px] font-semibold text-ink" x-text="t.n"></p>
                    <p class="text-[10px] text-ink/35" x-text="t.s"></p>
                  </div>
                  <span class="font-mono text-[12px] font-bold tabnum text-ink" x-text="'Rp '+t.v"></span>
                </div>
              </template>
            </div>
          </div>
        </div>

        <!-- Floating chip -->
        <div class="absolute -left-6 top-24 hidden rounded-2xl border border-emerald-100 bg-white px-3 py-2.5 shadow-xl sm:block">
          <p class="text-[10px] font-medium text-ink/40">Target nabung</p>
          <p class="font-mono text-sm font-bold text-emerald-700">68% tercapai</p>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ══════════════════════════════════════════════════════
     FEATURES
══════════════════════════════════════════════════════ -->
<section id="fitur" class="mx-auto max-w-5xl px-5 py-20">
  <div class="max-w-xl" data-reveal>
    <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Fitur</p>
    <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">Semua yang kamu butuh untuk rapikan keuangan.</h2>
  </div>

  <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <?php
    $features = [
      ['icon'=>'<path d="M3 3v18h18"/><rect x="7" y="11" width="3" height="6" rx="1"/><rect x="13" y="7" width="3" height="10" rx="1"/>',
       'title'=>'Catat pemasukan & pengeluaran',
       'desc' =>'Rekam tiap transaksi dalam hitungan detik, lengkap dengan kategori dan catatan.'],
      ['icon'=>'<path d="M3 17l5-5 4 4 8-8"/><path d="M16 8h4v4"/>',
       'title'=>'Analisis keuangan visual',
       'desc' =>'Grafik bulanan interaktif yang menunjukkan ke mana uangmu pergi setiap bulan.'],
      ['icon'=>'<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1" fill="currentColor"/>',
       'title'=>'Budget & target menabung',
       'desc' =>'Tetapkan batas belanja bulanan dan target tabungan agar finansialmu lebih terarah.'],
      ['icon'=>'<path d="M16 7a4 4 0 1 0-8 0"/><path d="M5 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2"/><path d="M19 8v4M21 10h-4"/>',
       'title'=>'Kelola hutang & piutang',
       'desc' =>'Catat siapa berhutang ke kamu dan kamu ke siapa, lengkap dengan tanggal jatuh tempo.'],
      ['icon'=>'<rect x="4" y="5" width="16" height="16" rx="2"/><path d="M4 9h16M8 3v4M16 3v4M9 14h6"/>',
       'title'=>'Tagihan rutin & pengingat',
       'desc' =>'Simpan daftar tagihan bulanan agar tak ada yang terlewat, langsung tercatat sebagai pengeluaran.'],
      ['icon'=>'<rect x="6" y="3" width="12" height="18" rx="2.5"/><path d="M11 18h2"/>',
       'title'=>'Install seperti aplikasi asli',
       'desc' =>'Berbasis PWA — pasang di layar utama HP tanpa perlu ke Play Store.'],
    ];
    foreach ($features as $f): ?>
    <div class="fcard rounded-3xl border border-ink/8 bg-white p-6" data-reveal>
      <span class="grid h-11 w-11 place-items-center rounded-2xl bg-emerald-50 text-emerald-600">
        <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= $f['icon'] ?></svg>
      </span>
      <h3 class="mt-5 text-base font-bold text-ink"><?= $f['title'] ?></h3>
      <p class="mt-2 text-sm leading-relaxed text-ink/55"><?= $f['desc'] ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════
     SECURITY (dark section)
══════════════════════════════════════════════════════ -->
<section id="aman" class="bg-ink py-20 text-white">
  <div class="mx-auto max-w-5xl px-5">
    <div class="grid gap-12 md:grid-cols-2 md:items-center">
      <div data-reveal>
        <p class="text-xs font-bold uppercase tracking-wider text-emerald-400">Privasi &amp; Keamanan</p>
        <h2 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">Data kamu adalah milik kamu sepenuhnya.</h2>
        <p class="mt-4 max-w-md text-sm leading-relaxed text-white/50">DuitKu dibangun tanpa pelacak, tanpa iklan, dan tanpa akses ke rekening manapun. Kamu yang pegang kendali penuh.</p>
        <a href="/register" class="mt-7 inline-flex items-center gap-2 rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-bold text-ink transition hover:bg-emerald-400">
          Coba sekarang — gratis
        </a>
      </div>

      <ul class="space-y-3" data-reveal>
        <?php
        $points = [
          'Password dienkripsi dengan bcrypt — bahkan admin tidak bisa membacanya.',
          'Data hanya bisa diakses oleh kamu sendiri lewat login yang valid.',
          'Tidak ada integrasi bank — kamu input sendiri, data tetap di tanganmu.',
          'Tanpa tracking, analytics pihak ketiga, atau iklan apa pun.',
          'Disimpan di server pribadi, bukan layanan cloud publik yang bisa dijual.',
        ];
        foreach ($points as $p): ?>
        <li class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-4">
          <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>
          <span class="text-sm leading-relaxed text-white/75"><?= $p ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════
     3 STEPS
══════════════════════════════════════════════════════ -->
<section id="mulai" class="mx-auto max-w-5xl px-5 py-20">
  <div class="text-center" data-reveal>
    <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Cara Mulai</p>
    <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">Siap dalam 3 langkah.</h2>
  </div>

  <div class="mt-10 grid gap-5 md:grid-cols-3">
    <?php
    $steps = [
      ['01','Daftar akun gratis','Cukup email dan password. Selesai dalam 30 detik.'],
      ['02','Sesuaikan kategori','Tambah kategori sesuai kebutuhan rumah tanggamu.'],
      ['03','Mulai catat transaksi','Tekan tombol + dan catat pengeluaran atau pemasukanmu.'],
    ];
    foreach ($steps as $s): ?>
    <div class="rounded-3xl border border-ink/8 bg-white p-7" data-reveal>
      <span class="font-mono text-5xl font-bold text-emerald-100"><?= $s[0] ?></span>
      <h3 class="mt-3 text-base font-bold text-ink"><?= $s[1] ?></h3>
      <p class="mt-2 text-sm leading-relaxed text-ink/55"><?= $s[2] ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════
     FAQ
══════════════════════════════════════════════════════ -->
<section id="faq" class="mx-auto max-w-3xl px-5 py-16">
  <div class="text-center" data-reveal>
    <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">FAQ</p>
    <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">Pertanyaan yang sering ditanya.</h2>
  </div>

  <div class="mt-10 space-y-3">
    <?php
    $faqs = [
      ['Apakah DuitKu benar-benar gratis?','Ya, 100% gratis. Tidak ada versi premium, fitur berbayar, atau iklan. Semua fitur tersedia untuk semua pengguna tanpa biaya apa pun.'],
      ['Apakah data keuangan saya aman?','Password dienkripsi dengan bcrypt sehingga tidak bisa dibaca siapa pun. Data transaksi hanya bisa diakses dengan akunmu sendiri.'],
      ['Apakah perlu koneksi internet terus?','DuitKu butuh internet untuk menyimpan dan sinkronisasi data. Karena berbasis PWA, tampilan dasar tetap bisa dimuat meski sinyal lemah.'],
      ['Bisakah dipakai untuk keuangan rumah tangga?','Tentu. DuitKu dirancang untuk pencatatan pribadi dan rumah tangga — buat kategori seperti Belanja Dapur, Listrik, atau Sekolah Anak sesuai kebutuhan.'],
      ['Apakah terhubung ke rekening bank saya?','Tidak. DuitKu adalah pencatatan manual — kamu yang input sendiri. Justru lebih aman karena tidak ada akses ke rekening, kartu, atau data perbankan.'],
      ['Bagaimana cara install di HP?','Buka DuitKu di browser HP, lalu muncul banner Install di bagian bawah. Tekan Install, dan DuitKu terpasang di layar utama seperti aplikasi biasa.'],
    ];
    foreach ($faqs as $i => $faq): ?>
    <div class="overflow-hidden rounded-2xl border border-ink/8 bg-white" data-reveal>
      <button onclick="toggleFaq(<?= $i ?>)" id="faqbtn<?= $i ?>"
              class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left">
        <span class="text-sm font-semibold text-ink"><?= $faq[0] ?></span>
        <svg id="faqarrow<?= $i ?>" viewBox="0 0 24 24" fill="none" class="h-5 w-5 shrink-0 text-emerald-600 transition-transform duration-300"
             stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
          <path d="M12 5v14M5 12h14"/>
        </svg>
      </button>
      <div id="faqbody<?= $i ?>" class="faq-body">
        <div class="faq-inner">
          <p class="px-5 pb-5 text-sm leading-relaxed text-ink/55 border-t border-ink/6 pt-3"><?= $faq[1] ?></p>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════
     FINAL CTA
══════════════════════════════════════════════════════ -->
<section id="daftar" class="mx-auto max-w-5xl px-5 pb-24">
  <div class="relative overflow-hidden rounded-[2.5rem] bg-ink px-8 py-16 text-center text-white" data-reveal>
    <div class="grid-bg pointer-events-none absolute inset-0 opacity-50"></div>
    <div class="relative">
      <h2 class="mx-auto max-w-lg text-3xl font-extrabold tracking-tight sm:text-4xl">Mulai kendalikan uangmu hari ini.</h2>
      <p class="mt-3 text-sm text-white/50">Gratis, aman, dan siap dalam 30 detik.</p>
      <a href="/register" class="mt-7 inline-flex items-center gap-2 rounded-2xl bg-emerald-500 px-7 py-4 text-base font-bold text-ink transition hover:bg-emerald-400">
        Daftar gratis sekarang
        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
      <p class="mt-4 text-sm text-white/40">Sudah punya akun?
        <button @click="login=true" class="font-semibold text-emerald-400 underline-offset-2 hover:underline">Masuk di sini</button>
      </p>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════
     FOOTER
══════════════════════════════════════════════════════ -->
<footer class="border-t border-ink/8 py-10">
  <div class="mx-auto flex max-w-5xl flex-col items-center gap-2 px-5 text-center text-sm text-ink/40">
    <div class="flex items-center gap-2">
      <img src="/images/logo.png" alt="DuitKu" class="h-7 w-7 rounded-lg object-contain">
      <span class="font-bold text-ink">DuitKu</span>
    </div>
    <p>© <?= date('Y') ?> · Dibuat untuk keluarga Indonesia. Data kamu adalah milik kamu sepenuhnya.</p>
  </div>
</footer>

<!-- ══════════════════════════════════════════════════════
     LOGIN MODAL
══════════════════════════════════════════════════════ -->
<div x-show="login" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition duration-200 ease-out"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150 ease-in"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

  <!-- Backdrop -->
  <div class="absolute inset-0 bg-ink/50 backdrop-blur-sm" @click="login=false"></div>

  <!-- Card -->
  <div class="relative w-full max-w-sm rounded-3xl bg-white p-7 shadow-2xl"
       x-transition:enter="transition duration-250 ease-out"
       x-transition:enter-start="opacity-0 scale-95 translate-y-4"
       x-transition:enter-end="opacity-100 scale-100 translate-y-0"
       x-transition:leave="transition duration-150 ease-in"
       x-transition:leave-start="opacity-100 scale-100"
       x-transition:leave-end="opacity-0 scale-95">

    <!-- Close -->
    <button @click="login=false" class="absolute right-4 top-4 text-ink/35 transition hover:text-ink">
      <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
    </button>

    <!-- Logo -->
    <img src="/images/logo.png" alt="DuitKu" class="h-11 w-11 rounded-2xl object-contain bg-ink/5 p-1.5">

    <h3 class="mt-4 text-xl font-extrabold text-ink">Masuk ke akun</h3>
    <p class="mt-1 text-sm text-ink/50">Lanjutkan pencatatan finansialmu.</p>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="mt-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
      <?= esc(session()->getFlashdata('error')) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/login" class="mt-6 space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="text-[11px] font-bold uppercase tracking-wide text-ink/45">Email</label>
        <input type="email" name="email" value="<?= old('email') ?>"
               placeholder="kamu@email.com" required autocomplete="email"
               class="mt-1.5 w-full rounded-xl border border-ink/12 bg-paper px-4 py-3 text-sm text-ink placeholder-ink/30 outline-none transition focus:border-emerald-500 focus:bg-white">
      </div>
      <div>
        <label class="text-[11px] font-bold uppercase tracking-wide text-ink/45">Password</label>
        <div class="relative mt-1.5">
          <input :type="showPw ? 'text' : 'password'" name="password"
                 placeholder="••••••••" required autocomplete="current-password"
                 class="w-full rounded-xl border border-ink/12 bg-paper px-4 py-3 pr-11 text-sm text-ink placeholder-ink/30 outline-none transition focus:border-emerald-500 focus:bg-white">
          <button type="button" @click="showPw=!showPw"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-ink/35 transition hover:text-ink">
            <svg x-show="!showPw" viewBox="0 0 24 24" fill="none" class="h-5 w-5" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg x-show="showPw"  viewBox="0 0 24 24" fill="none" class="h-5 w-5" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 3l18 18M10.6 10.6a3 3 0 0 0 4.2 4.2M9.4 5.2A9.5 9.5 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-2.4 3.4M6.2 6.2A17 17 0 0 0 2 12s3.5 7 10 7a9.6 9.6 0 0 0 3-.5"/></svg>
          </button>
        </div>
      </div>
      <button type="submit"
              class="w-full rounded-xl bg-emerald-600 py-3.5 text-sm font-bold text-white transition hover:bg-emerald-700">
        Masuk
      </button>
    </form>

    <p class="mt-5 text-center text-sm text-ink/50">
      Pengguna baru?
      <a href="/register" class="font-semibold text-emerald-700 hover:underline">Daftar gratis</a>
    </p>
  </div>
</div>

<!-- ── Scripts ──────────────────────────────────────── -->
<script src="/js/app.js?v=<?= time() ?>"></script>
<script>
// Scroll reveal
const io = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
}, { threshold: 0.1 });
document.querySelectorAll('[data-reveal]').forEach(el => io.observe(el));

// FAQ accordion (vanilla, no Alpine dependency)
let openFaq = null;
function toggleFaq(i) {
  const body  = document.getElementById('faqbody' + i);
  const arrow = document.getElementById('faqarrow' + i);
  if (openFaq !== null && openFaq !== i) {
    document.getElementById('faqbody' + openFaq).classList.remove('open');
    document.getElementById('faqarrow' + openFaq).style.transform = '';
  }
  const isOpen = body.classList.contains('open');
  body.classList.toggle('open', !isOpen);
  arrow.style.transform = isOpen ? '' : 'rotate(45deg)';
  openFaq = isOpen ? null : i;
}
</script>
</body>
</html>
