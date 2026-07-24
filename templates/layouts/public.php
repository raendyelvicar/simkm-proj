<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? config('app.name')) ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <?php if (!empty($extraStyles)): ?>
        <style>
            <?= $extraStyles ?>
        </style>
    <?php endif; ?>
</head>

<body>
    <!-- HEADER -->
    <header>
        <div class="brand">
            <img src="/assets/img/logo.png" alt="<?= htmlspecialchars(config('app.name')) ?>">
            <div class="brand-text">
                <div class="logo"><?= htmlspecialchars(config('app.name')) ?></div>
                <div class="tagline">Konsultasi Kesehatan Mental Mahasiswa</div>
            </div>
        </div>
        <nav>
            <a href="/">Beranda</a>
            <a href="/#fitur">Fitur</a>
            <a href="/#cara-kerja">Cara Kerja</a>
            <a href="/article">Artikel</a>
            <div class="nav-auth">
                <a href="/login" class="btn-nav btn-nav-ghost">Masuk</a>
                <a href="/register" class="btn-nav btn-nav-primary">Daftar</a>
            </div>
        </nav>
    </header>

    <main class="public-page-main">
        <?= $content ?? '' ?>
    </main>

    <!-- FOOTER -->
    <footer>
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="logo"><?= htmlspecialchars(config('app.name')) ?></div>
                <p>Sistem Informasi Manajemen Kesehatan Mental Mahasiswa — membantu mahasiswa mengelola kesehatan mental lewat diary, self-assessment, dan konsultasi dengan konselor kampus.</p>
            </div>

            <div class="footer-links">
                <div>
                    <h4>Navigasi</h4>
                    <a href="/">Beranda</a>
                    <a href="/article">Artikel</a>
                    <a href="/counselor">Konselor</a>
                </div>
                <div>
                    <h4>Akun</h4>
                    <a href="/login">Masuk</a>
                    <a href="/register">Daftar</a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            © <?= date('Y') ?> <?= htmlspecialchars(config('app.name')) ?> — Sistem Informasi Manajemen Kesehatan Mental Mahasiswa
        </div>
    </footer>
</body>

</html>