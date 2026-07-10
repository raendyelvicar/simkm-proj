<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Login sementara (default)
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = $_POST['username'];
    $_SESSION['role'] = 'mahasiswa';

    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Sistem Informasi Kesehatan Mental Mahasiswa</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
}

body {
    background: linear-gradient(135deg, #0ea5a4, #22c1c3);
    color: #fff;
}

/* ===== HEADER ===== */
header {
    padding: 20px 60px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    font-size: 22px;
    font-weight: bold;
}

nav a {
    color: #fff;
    text-decoration: none;
    margin-left: 20px;
    font-weight: 500;
}

nav a:hover {
    text-decoration: underline;
}

/* ===== HERO ===== */
.hero {
    padding: 80px 60px;
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 40px;
    align-items: center;
}

.hero h1 {
    font-size: 36px;
    margin-bottom: 18px;
}

.hero p {
    font-size: 16px;
    line-height: 1.7;
    margin-bottom: 30px;
    opacity: 0.95;
}

.hero-buttons a {
    display: inline-block;
    padding: 14px 22px;
    background: #fff;
    color: #0ea5a4;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    margin-right: 12px;
}

.hero-buttons a.secondary {
    background: transparent;
    border: 2px solid #fff;
    color: #fff;
}

/* ===== FEATURE SECTION ===== */
.features {
    background: #f6f8fa;
    color: #333;
    padding: 70px 60px;
}

.features h2 {
    text-align: center;
    font-size: 28px;
    margin-bottom: 40px;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 30px;
}

.feature-card {
    background: #fff;
    padding: 26px;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
}

.feature-card h3 {
    margin-bottom: 10px;
    color: #0ea5a4;
}

/* ===== FOOTER ===== */
footer {
    background: #0f172a;
    padding: 25px;
    text-align: center;
    font-size: 14px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
    header {
        padding: 20px;
        flex-direction: column;
        gap: 10px;
    }

    .hero {
        padding: 50px 20px;
        grid-template-columns: 1fr;
        text-align: center;
    }

    .hero-buttons a {
        margin-bottom: 10px;
    }

    .features {
        padding: 50px 20px;
    }
}
</style>
</head>

<body>

<!-- HEADER -->
<header>
    <div class="logo">MentalCare Student</div>
    <nav>
        <a href="#">Beranda</a>
        <a href="#fitur">Fitur</a>
        <a href="login.php">Login</a>
        <a href="register.php">Daftar</a>
    </nav>
</header>

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
            <a href="login.php">Mulai Sekarang</a>
            <a href="#fitur" class="secondary">Pelajari Fitur</a>
        </div>
    </div>

    <div>
        <img src="Icon RS.png"
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

<!-- FOOTER -->
<footer>
    © <?= date('Y') ?> Sistem Informasi Manajemen Kesehatan Mental Mahasiswa
</footer>

</body>
</html>
