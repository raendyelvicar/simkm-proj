<?php 
/**
 * Sidebar Global
 * Menentukan role dan highlight menu aktif
 */
$config_path = dirname(__DIR__) . '/config/config.php';
if (file_exists($config_path)) {
    include_once $config_path;
}

// Pastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? '';
$current_page = basename($_SERVER['PHP_SELF']);

// Fungsi helper untuk menu aktif
function isActive($page) {
    global $current_page;
    return $current_page === $page ? 'active-menu' : '';
}
?>

<div id="sidebar" class="sidebar bg-dark text-white p-3">

<h5 class="text-center mt-3">MENU</h5>

<?php if($role == 'admin'): ?>

    <a href="../dashboard_bootstrap/dashboard_bootstrap.php" class="menu-link <?= isActive('dashboard_bootstrap.php') ?>">
        <span class="icon">🏠</span>
        <span class="text">Dashboard</span>
    </a>

    <!-- Data Master Collapse -->
    <a class="menu-link" data-bs-toggle="collapse" href="#masterMenu" role="button" aria-expanded="false">
        <span class="icon">📁</span>
        <span class="text">Data Master</span>
    </a>
    <div class="collapse ps-3" id="masterMenu">
        <a href="../admin/manage_users.php" class="menu-link <?= isActive('manage_users.php') ?>">👤 Users</a>
        <a href="../admin/view_students.php" class="menu-link <?= isActive('view_students.php') ?>">🎓 Mahasiswa</a>
        <a href="../admin/manage_konselor.php" class="menu-link <?= isActive('manage_konselor.php') ?>">🧑‍🏫 Konselor</a>
    </div>

    <!-- Aktivitas Collapse -->
    <a class="menu-link" data-bs-toggle="collapse" href="#aktivitasMenu" role="button" aria-expanded="false">
        <span class="icon">📊</span>
        <span class="text">Aktivitas</span>
    </a>
    <div class="collapse ps-3" id="aktivitasMenu">
        <a href="../assessment/assessment_overview.php" class="menu-link <?= isActive('assessment_overview.php') ?>">📝 Assessment</a>
        <a href="../diary/view.php" class="menu-link <?= isActive('view.php') ?>">📖 Diary</a>
    </div>

    <!-- Konsultasi Collapse -->
    <a class="menu-link" data-bs-toggle="collapse" href="#konsultasiMenu" role="button" aria-expanded="false">
        <span class="icon">📁</span>
        <span class="text">Konsultasi</span>
    </a>
    <div class="collapse ps-3" id="konsultasiMenu">
        <a href="../admin/chat.php" class="menu-link <?= isActive('chat.php') ?>">💬 Konsultasi</a>
    </div>

    <!-- Artikel Collapse -->
    <a class="menu-link" data-bs-toggle="collapse" href="#artikelMenu" role="button" aria-expanded="false">
        <span class="icon">📑</span>
        <span class="text">Artikel</span>
    </a>
    <div class="collapse ps-3" id="artikelMenu">
        <a href="../admin/articles/index.php" class="menu-link <?= isActive('index.php') ?>">📰 Artikel</a>
    </div>

    <!-- Laporan Collapse -->
    <a class="menu-link" data-bs-toggle="collapse" href="#laporanMenu" role="button" aria-expanded="false">
        <span class="icon">📑</span>
        <span class="text">Laporan</span>
    </a>
    <div class="collapse ps-3" id="laporanMenu">
        <a href="laporan/laporan_user.php" class="menu-link">➤ Laporan User</a>
        <a href="laporan/laporan_login.php" class="menu-link">➤ Laporan Login</a>
        <a href="laporan/laporan_diary.php" class="menu-link">➤ Laporan Diary</a>
        <a href="laporan/laporan_assessment.php" class="menu-link">➤ Laporan Assessment</a>
        <a href="laporan/laporan_konsultasi.php" class="menu-link">➤ Laporan Konsultasi</a>
        <a href="laporan/laporan_stres.php" class="menu-link">➤ Laporan Tingkat Stres</a>
        <a href="laporan/laporan_aktivitas.php" class="menu-link">➤ Laporan Aktivitas</a>
        <a href="laporan/laporan_statistik_mood.php" class="menu-link">➤ Laporan Statistik Mood</a>
        <a href="laporan/laporan_notifikasi.php" class="menu-link">➤ Laporan Notifikasi</a>
        <a href="laporan/laporan_export.php" class="menu-link">➤ Export PDF</a>
    </div>

<?php elseif($role == 'konselor'): ?>

    <!-- DASHBOARD -->
    <a href="../dashboard_bootstrap/dashboard_bootstrap.php"
       class="menu-link <?= isActive('dashboard_bootstrap.php') ?>">

        <span class="icon">🏠</span>
        <span class="text">Dashboard</span>

    </a>

    <!-- ASSESSMENT -->
    <a href="../assessment/history.php"
       class="menu-link <?= isActive('history.php') ?>">

        <span class="icon">📝</span>
        <span class="text">Assessment</span>

    </a>

    <!-- DIARY -->
    <a href="../diary/view.php"
       class="menu-link <?= isActive('view.php') ?>">

        <span class="icon">📖</span>
        <span class="text">Diary</span>

    </a>

    <!-- ARTIKEL -->
    <a href="../admin/articles/index.php"
       class="menu-link <?= isActive('index.php') ?>">

        <span class="icon">📰</span>
        <span class="text">Artikel</span>

    </a>

    <!-- CHAT -->
    <a href="/AplikasiSkripsi/konselor/chat.php"
       class="menu-link <?= isActive('chat.php') ?>">

        <span class="icon">💬</span>
        <span class="text">Chat Konsultasi</span>

    </a>

<?php else: ?>

    <!-- DASHBOARD -->
    <a href="../dashboard_bootstrap/dashboard_bootstrap.php"
       class="menu-link <?= isActive('dashboard_bootstrap.php') ?>">

        <span class="icon">🏠</span>
        <span class="text">Dashboard</span>

    </a>

    <!-- DIARY -->
    <a href="../diary/add.php"
       class="menu-link <?= isActive('add.php') ?>">

        <span class="icon">📖</span>
        <span class="text">Diary</span>

    </a>

    <!-- ASSESSMENT -->
    <a href="../assessment/start.php"
       class="menu-link <?= isActive('start.php') ?>">

        <span class="icon">📝</span>
        <span class="text">Assessment</span>

    </a>

    <!-- ARTIKEL -->
    <a href="../mahasiswa/articles.php"
       class="menu-link <?= isActive('articles.php') ?>">

        <span class="icon">📰</span>
        <span class="text">Artikel</span>

    </a>

    <!-- CHAT -->
    <a href="../chat/index.php"
       class="menu-link <?= isActive('index.php') ?>">

        <span class="icon">💬</span>
        <span class="text">Chat Konselor</span>

    </a>

<?php endif; ?>

</div>