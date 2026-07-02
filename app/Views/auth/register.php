<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Daftar — DuitKu</title>
    <link rel="icon" type="image/png" href="/images/logo.png">
    <link rel="apple-touch-icon" href="/images/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="auth-body">

<div class="auth-page">
    <div class="auth-brand">
        <img src="/images/logo.png" alt="DuitKu" class="auth-logo" style="object-fit:contain">
        <h1 class="auth-title">DuitKu</h1>
        <p class="auth-tagline">Catat. Kelola. Bijak.</p>
    </div>

    <div class="auth-card">
        <h2 class="auth-card-title">Buat Akun</h2>
        <p class="auth-card-sub">Mulai kelola keuangan kamu hari ini</p>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <form method="POST" action="/register" class="auth-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="name">NAMA LENGKAP</label>
                <input type="text" id="name" name="name" class="form-input"
                       placeholder="Nama kamu" value="<?= old('name') ?>" required autocomplete="name">
            </div>
            <div class="form-group">
                <label class="form-label" for="email">EMAIL</label>
                <input type="email" id="email" name="email" class="form-input"
                       placeholder="nama@email.com" value="<?= old('email') ?>" required autocomplete="email">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">PASSWORD</label>
                <div class="password-wrap">
                    <input type="password" id="password" name="password" class="form-input"
                           placeholder="Min. 6 karakter" required minlength="6">
                    <button type="button" class="password-toggle" id="pwToggle">👁</button>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirm">KONFIRMASI PASSWORD</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-input"
                       placeholder="Ulangi password" required>
            </div>
            <button type="submit" class="btn-save">Buat Akun</button>
        </form>

        <p class="auth-switch">Sudah punya akun? <a href="/login">Masuk</a></p>
    </div>
</div>

<script>
document.getElementById('pwToggle').addEventListener('click', function() {
    const pw = document.getElementById('password');
    pw.type = pw.type === 'password' ? 'text' : 'password';
    this.textContent = pw.type === 'password' ? '👁' : '🙈';
});
</script>
</body>
</html>
