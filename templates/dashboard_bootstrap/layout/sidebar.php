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
function isActive($page)
{
    global $current_page;
    return $current_page === $page ? 'active-menu' : '';
}
?>

<div id="sidebar" class="sidebar">
    <nav class="sidebar-menu">

        <?php if ($role == 'admin'): ?>

            <a href="../dashboard_bootstrap/dashboard_bootstrap.php"
                class="menu-link <?= isActive('dashboard_bootstrap.php') ?>"
                title="Dashboard">
                <span class="icon">🏠</span>
                <span class="text">Dashboard</span>
            </a>

            <!-- Data Master Collapse -->
            <a class="menu-link has-submenu" data-bs-toggle="collapse" href="#masterMenu"
                role="button" aria-expanded="false" title="Data Master">
                <span class="icon">📁</span>
                <span class="text">Data Master</span>
                <span class="chevron">›</span>
            </a>
            <div class="collapse submenu" id="masterMenu">
                <a href="../admin/manage_users.php" class="menu-link submenu-link <?= isActive('manage_users.php') ?>">👤 <span class="text">Users</span></a>
                <a href="../admin/view_students.php" class="menu-link submenu-link <?= isActive('view_students.php') ?>">🎓 <span class="text">Mahasiswa</span></a>
                <a href="../admin/manage_konselor.php" class="menu-link submenu-link <?= isActive('manage_konselor.php') ?>">🧑‍🏫 <span class="text">Konselor</span></a>
            </div>

            <!-- Aktivitas Collapse -->
            <a class="menu-link has-submenu" data-bs-toggle="collapse" href="#aktivitasMenu"
                role="button" aria-expanded="false" title="Aktivitas">
                <span class="icon">📊</span>
                <span class="text">Aktivitas</span>
                <span class="chevron">›</span>
            </a>
            <div class="collapse submenu" id="aktivitasMenu">
                <a href="../assessment/assessment_overview.php" class="menu-link submenu-link <?= isActive('assessment_overview.php') ?>">📝 <span class="text">Assessment</span></a>
                <a href="../diary/view.php" class="menu-link submenu-link <?= isActive('view.php') ?>">📖 <span class="text">Diary</span></a>
            </div>

            <!-- Konsultasi Collapse -->
            <a class="menu-link has-submenu" data-bs-toggle="collapse" href="#konsultasiMenu"
                role="button" aria-expanded="false" title="Konsultasi">
                <span class="icon">💬</span>
                <span class="text">Konsultasi</span>
                <span class="chevron">›</span>
            </a>
            <div class="collapse submenu" id="konsultasiMenu">
                <a href="../admin/chat.php" class="menu-link submenu-link <?= isActive('chat.php') ?>">💬 <span class="text">Konsultasi</span></a>
            </div>

            <!-- Artikel Collapse -->
            <a class="menu-link has-submenu" data-bs-toggle="collapse" href="#artikelMenu"
                role="button" aria-expanded="false" title="Artikel">
                <span class="icon">📑</span>
                <span class="text">Artikel</span>
                <span class="chevron">›</span>
            </a>
            <div class="collapse submenu" id="artikelMenu">
                <a href="../admin/articles/index.php" class="menu-link submenu-link <?= isActive('index.php') ?>">📰 <span class="text">Artikel</span></a>
            </div>

            <!-- Laporan Collapse -->
            <a class="menu-link has-submenu" data-bs-toggle="collapse" href="#laporanMenu"
                role="button" aria-expanded="false" title="Laporan">
                <span class="icon">📑</span>
                <span class="text">Laporan</span>
                <span class="chevron">›</span>
            </a>
            <div class="collapse submenu" id="laporanMenu">
                <a href="laporan/laporan_user.php" class="menu-link submenu-link">➤ <span class="text">Laporan User</span></a>
                <a href="laporan/laporan_login.php" class="menu-link submenu-link">➤ <span class="text">Laporan Login</span></a>
                <a href="laporan/laporan_diary.php" class="menu-link submenu-link">➤ <span class="text">Laporan Diary</span></a>
                <a href="laporan/laporan_assessment.php" class="menu-link submenu-link">➤ <span class="text">Laporan Assessment</span></a>
                <a href="laporan/laporan_konsultasi.php" class="menu-link submenu-link">➤ <span class="text">Laporan Konsultasi</span></a>
                <a href="laporan/laporan_stres.php" class="menu-link submenu-link">➤ <span class="text">Laporan Tingkat Stres</span></a>
                <a href="laporan/laporan_aktivitas.php" class="menu-link submenu-link">➤ <span class="text">Laporan Aktivitas</span></a>
                <a href="laporan/laporan_statistik_mood.php" class="menu-link submenu-link">➤ <span class="text">Laporan Statistik Mood</span></a>
                <a href="laporan/laporan_notifikasi.php" class="menu-link submenu-link">➤ <span class="text">Laporan Notifikasi</span></a>
                <a href="laporan/laporan_export.php" class="menu-link submenu-link">➤ <span class="text">Export PDF</span></a>
            </div>

        <?php elseif ($role == 'konselor'): ?>

            <a href="../dashboard_bootstrap/dashboard_bootstrap.php"
                class="menu-link <?= isActive('dashboard_bootstrap.php') ?>" title="Dashboard">
                <span class="icon">🏠</span>
                <span class="text">Dashboard</span>
            </a>

            <a href="../assessment/history.php"
                class="menu-link <?= isActive('history.php') ?>" title="Assessment">
                <span class="icon">📝</span>
                <span class="text">Assessment</span>
            </a>

            <a href="../diary/view.php"
                class="menu-link <?= isActive('view.php') ?>" title="Diary">
                <span class="icon">📖</span>
                <span class="text">Diary</span>
            </a>

            <a href="../admin/articles/index.php"
                class="menu-link <?= isActive('index.php') ?>" title="Artikel">
                <span class="icon">📰</span>
                <span class="text">Artikel</span>
            </a>

            <a href="/AplikasiSkripsi/konselor/chat.php"
                class="menu-link <?= isActive('chat.php') ?>" title="Chat Konsultasi">
                <span class="icon">💬</span>
                <span class="text">Chat Konsultasi</span>
            </a>

        <?php else: ?>

            <a href="../dashboard_bootstrap/dashboard_bootstrap.php"
                class="menu-link <?= isActive('dashboard_bootstrap.php') ?>" title="Dashboard">
                <span class="icon">🏠</span>
                <span class="text">Dashboard</span>
            </a>

            <a href="../diary/add.php"
                class="menu-link <?= isActive('add.php') ?>" title="Diary">
                <span class="icon">📖</span>
                <span class="text">Diary</span>
            </a>

            <a href="../assessment/start.php"
                class="menu-link <?= isActive('start.php') ?>" title="Assessment">
                <span class="icon">📝</span>
                <span class="text">Assessment</span>
            </a>

            <a href="../mahasiswa/articles.php"
                class="menu-link <?= isActive('articles.php') ?>" title="Artikel">
                <span class="icon">📰</span>
                <span class="text">Artikel</span>
            </a>

            <a href="../chat/index.php"
                class="menu-link <?= isActive('index.php') ?>" title="Chat Konselor">
                <span class="icon">💬</span>
                <span class="text">Chat Konselor</span>
            </a>

        <?php endif; ?>

    </nav>

</div>