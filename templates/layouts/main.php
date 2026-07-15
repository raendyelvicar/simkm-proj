<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? config('app.name')) ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>

<body>
    <!-- HEADER -->
    <header>
        <h1><?= htmlspecialchars(config('app.name')) ?></h1>
        <nav>
            <a href="/">Beranda</a>
            <a href="#fitur">Fitur</a>
            <a href="/login">Login</a>
            <a href="/register">Daftar</a>
        </nav>
    </header>

    <main>
        <!-- HERO -->
        <section class="hero">
            <div>
                <h1>
                    Sistem Informasi Manajemen
                    Kesehatan Mental Mahasiswa
                </h1>

                <p>
                    Aplikasi berbasis web yang dirancang untuk membantu mahasiswa
                    dalam mengelola kesehatan mental melalui fitur <strong>Diary</strong>
                    dan <strong>Self-Assessment</strong> secara mandiri, aman, dan terstruktur.
                </p>

                <div class="hero-buttons">
                    <a href="/login">Mulai Sekarang</a>
                    <a href="#fitur" class="secondary">Pelajari Fitur</a>
                </div>
            </div>

            <div>
                <img src="/assets/img/icon-rs.png"
                    alt="Mental Health Illustration"
                    style="width:100%; max-width:380px; display:block; margin:auto;">
            </div>
        </section>

        <!-- FEATURES -->
        <section class="features" id="fitur">
            <h2>Fitur Utama Aplikasi</h2>

            <div class="feature-grid">
                <div class="feature-card">
                    <h3>📔 Diary Kesehatan Mental</h3>
                    <p>
                        Mahasiswa dapat mencatat perasaan, suasana hati, dan aktivitas
                        harian untuk membantu memahami kondisi mental secara berkala.
                    </p>
                </div>

                <div class="feature-card">
                    <h3>📝 Self-Assessment</h3>
                    <p>
                        Fitur evaluasi mandiri berbasis kuesioner untuk mengetahui tingkat
                        stres, kecemasan, dan kesejahteraan mental.
                    </p>
                </div>

                <div class="feature-card">
                    <h3>📊 Monitoring & Riwayat</h3>
                    <p>
                        Menyediakan riwayat diary dan hasil assessment sebagai bahan refleksi
                        dan evaluasi kondisi mental mahasiswa.
                    </p>
                </div>

                <div class="feature-card">
                    <h3>🔐 Keamanan Data</h3>
                    <p>
                        Data pengguna tersimpan secara aman dan hanya dapat diakses oleh
                        pengguna yang bersangkutan.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <footer>
        © <?= date('Y') ?> Sistem Informasi Manajemen Kesehatan Mental Mahasiswa
    </footer>

    <script src="/assets/js/app.js"></script>
</body>

</html>