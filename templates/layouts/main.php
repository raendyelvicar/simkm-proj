<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? config('app.name')) ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
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
            <a href="#fitur">Fitur</a>
            <a href="#cara-kerja">Cara Kerja</a>
            <a href="/counselor">Konselor</a>
            <div class="nav-auth">
                <a href="/login" class="btn-nav btn-nav-ghost">Login</a>
                <a href="/register" class="btn-nav btn-nav-primary">Daftar</a>
            </div>
        </nav>
    </header>

    <main>
        <!-- HERO -->
        <div class="hero-wrap">
            <section class="hero">
                <div>
                    <span class="hero-eyebrow">🎓 Untuk Mahasiswa</span>
                    <h1>Kelola Kesehatan Mentalmu, Bersama SIMKM</h1>

                    <p>
                        Catat perasaanmu lewat <strong>Diary</strong>, kenali kondisi mentalmu
                        lewat <strong>Self-Assessment</strong>, dan bila perlu, konsultasikan
                        langsung dengan <strong>konselor kampus</strong> — semua dalam satu tempat
                        yang aman dan mudah digunakan.
                    </p>

                    <div class="hero-buttons">
                        <a href="/register">Daftar Sekarang</a>
                        <a href="#cara-kerja" class="secondary">Lihat Cara Kerja</a>
                    </div>

                    <div class="hero-note">🔒 Datamu privat — hanya bisa diakses olehmu dan konselor yang berwenang.</div>
                </div>

                <div class="hero-art">
                    <img src="/assets/img/icon-rs.png" alt="Ilustrasi Kesehatan Mental">
                </div>
            </section>
        </div>

        <!-- HOW IT WORKS -->
        <section class="block steps" id="cara-kerja">
            <div class="section-head">
                <div class="section-eyebrow">Alur Penggunaan</div>
                <h2>Cara Kerja SIMKM</h2>
                <p>Empat langkah sederhana dari daftar akun sampai konsultasi dengan konselor.</p>
            </div>

            <div class="step-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3>Daftar Akun</h3>
                    <p>Isi data diri kamu — nama, NPM, fakultas, dan jurusan — untuk membuat akun mahasiswa.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3>Tunggu Persetujuan Admin</h3>
                    <p>Admin meninjau pendaftaranmu. Begitu disetujui, kamu akan mendapat email pemberitahuan.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3>Isi Diary & Assessment</h3>
                    <p>Login lalu mulai catat suasana hatimu di Diary dan kenali kondisimu lewat Self-Assessment.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">4</div>
                    <h3>Konsultasi dengan Konselor</h3>
                    <p>Butuh bantuan lebih lanjut? Pilih konselor kampus dan mulai konsultasi lewat chat.</p>
                </div>
            </div>
        </section>

        <!-- FEATURES -->
        <section class="block features" id="fitur">
            <div class="section-head">
                <div class="section-eyebrow">Fitur Utama</div>
                <h2>Semua yang Kamu Butuhkan</h2>
                <p>Dirancang khusus untuk membantu mahasiswa memahami dan menjaga kesehatan mentalnya.</p>
            </div>

            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">📔</div>
                    <h3>Diary Kesehatan Mental</h3>
                    <p>Catat perasaan, suasana hati, dan aktivitas harian untuk membantu memahami kondisi mental secara berkala.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📝</div>
                    <h3>Self-Assessment</h3>
                    <p>Evaluasi mandiri berbasis kuesioner untuk mengetahui tingkat stres, kecemasan, dan kesejahteraan mental.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">💬</div>
                    <h3>Konsultasi dengan Konselor</h3>
                    <p>Pilih konselor kampus dan konsultasikan kondisimu langsung lewat chat yang privat.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📰</div>
                    <h3>Artikel Edukasi</h3>
                    <p>Baca artikel seputar kesehatan mental untuk menambah wawasan dan strategi menjaga kesejahteraanmu.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Riwayat & Monitoring</h3>
                    <p>Riwayat diary dan hasil assessment tersimpan rapi sebagai bahan refleksi dari waktu ke waktu.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🔐</div>
                    <h3>Keamanan Data</h3>
                    <p>Data pengguna tersimpan secara aman dan hanya dapat diakses oleh pengguna yang bersangkutan.</p>
                </div>
            </div>
        </section>

        <!-- ROLES -->
        <section class="block roles">
            <div class="section-head">
                <div class="section-eyebrow">Untuk Siapa</div>
                <h2>Satu Platform, Tiga Peran</h2>
                <p>SIMKM menghubungkan mahasiswa, konselor, dan admin kampus dalam satu alur kerja.</p>
            </div>

            <div class="role-grid">
                <div class="role-card">
                    <div class="role-icon">🎓</div>
                    <h3>Mahasiswa</h3>
                    <ul>
                        <li>Isi diary & self-assessment</li>
                        <li>Konsultasi dengan konselor</li>
                        <li>Baca artikel edukasi</li>
                    </ul>
                </div>

                <div class="role-card">
                    <div class="role-icon">🧑‍⚕️</div>
                    <h3>Konselor</h3>
                    <ul>
                        <li>Kelola konsultasi masuk</li>
                        <li>Pantau data mahasiswa</li>
                        <li>Tulis artikel edukasi</li>
                    </ul>
                </div>

                <div class="role-card">
                    <div class="role-icon">🛠️</div>
                    <h3>Admin</h3>
                    <ul>
                        <li>Setujui pendaftaran akun</li>
                        <li>Kelola akun konselor</li>
                        <li>Pantau laporan & statistik</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- FINAL CTA -->
        <section class="block final-cta">
            <h2>Siap Memulai Perjalanan Kesehatan Mentalmu?</h2>
            <p>Daftar sekarang dan mulai kenali kondisi mentalmu bersama SIMKM.</p>
            <div class="hero-buttons">
                <a href="/register">Daftar Sekarang</a>
                <a href="/login" class="secondary">Sudah Punya Akun? Masuk</a>
            </div>
        </section>
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
                    <a href="#fitur">Fitur</a>
                    <a href="#cara-kerja">Cara Kerja</a>
                    <a href="/counselor">Konselor</a>
                </div>
                <div>
                    <h4>Akun</h4>
                    <a href="/login">Login</a>
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
