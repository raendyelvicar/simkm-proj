<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../../config/db.php';

$userId = $_SESSION['user_id'] ?? 0;

// ================= DATA USER =================
$qUser = mysqli_query($mysqli, "
    SELECT nama, profile_image, role
    FROM users
    WHERE id = '$userId'
");

$userData = mysqli_fetch_assoc($qUser);

$namaUser = $userData['nama'] ?? 'User';
$roleUser = $userData['role'] ?? '';
// ================= FOTO PROFIL =================
if ($roleUser == 'admin') {

    $fotoUser = !empty($userData['profile_image'])
        ? $userData['profile_image']
        : 'default.png';
} else {

    // akun selain admin kosongkan foto
    $fotoUser = '';
}

// ================= FOTO KHUSUS ADMIN =================
$showPhoto = ($roleUser == 'admin');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Dashboard SIMKM</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background: #eef2f7;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }

        /* SIDEBAR */
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 70px;
            left: 0;
            background: linear-gradient(180deg, #111827, #1f2937);
            color: white;
            padding: 15px;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
            transition: 0.3s;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.15);
            height: calc(100vh - 70px);
        }

        /* MENU */
        .menu-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            margin-bottom: 8px;
            color: #d1d5db;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.25s ease;
            font-size: 15px;
            font-weight: 500;
        }

        .menu-link:hover {
            background: #2563eb;
            color: white;
            transform: translateX(4px);
        }

        .active-menu {
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            color: white;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        }

        /* CONTENT */
        .content-wrapper {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s;
        }

        /* CARD */
        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            transition: 0.3s;
            overflow: hidden;
        }

        .card h6 {
            font-weight: 600;
        }

        .card .btn {
            border-radius: 6px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
        }

        .stat-card {
            border-radius: 18px;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            right: -20px;
            top: -20px;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .stat-card h3 {
            font-size: 34px;
            font-weight: bold;
        }

        .stat-card p {
            margin-bottom: 0;
            opacity: 0.9;
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
            transition: 0.25s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        /* BOX */
        .small-box {
            padding: 20px;
            border-radius: 12px;
            color: white;
            text-align: center;
        }

        .bg-info {
            background: #3b82f6;
        }

        .bg-warning {
            background: #f59e0b;
        }

        .bg-danger {
            background: #ef4444;
        }

        .bg-success {
            background: #22c55e;
        }

        /* ===== FIX TRANSISI CONTENT ===== */
        .content-wrapper {
            transition: margin-left 0.3s;
        }

        /* ===== MINI SIDEBAR ===== */
        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar.collapsed .text {
            display: none;
        }

        .sidebar.collapsed h5 {
            display: none;
        }

        .sidebar.collapsed .menu-link {
            text-align: center;
        }

        .sidebar.collapsed .icon {
            display: block;
            font-size: 20px;
        }

        .sidebar.collapsed+.content-wrapper {
            margin-left: 70px;
        }

        /* ===== ANIMASI HALUS ===== */
        .sidebar {
            transition: all 0.3s;
        }

        /* ===== HOVER EXPAND ===== */
        .sidebar.collapsed:hover {
            width: 250px;
        }

        .sidebar.collapsed:hover .text {
            display: inline;
        }

        /* SIDEBAR NORMAL */
        .sidebar {
            width: 250px;
            min-height: 100vh;
            transition: 0.3s;
        }

        /* MODE COLLAPSE (ICON ONLY) */
        .sidebar.collapsed {
            width: 70px;
        }

        /* SEMBUNYIKAN TEXT */
        .sidebar.collapsed .text {
            display: none;
        }

        /* ICON TETAP MUNCUL */
        .sidebar .icon {
            width: 30px;
            min-width: 30px;
            text-align: center;
            font-size: 18px;
        }

        /* LINK STYLE */
        .menu-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 5px;
            transition: 0.2s;
        }

        .menu-link:hover {
            background: #0d6efd;
        }

        .sidebar.collapsed .menu-link {
            justify-content: center;
            padding: 12px 0;
        }

        .sidebar.collapsed .icon {
            margin: 0;
            font-size: 20px;
        }

        /* ACTIVE */
        .active-menu {
            background: #0d6efd;
        }

        /* FIX COLLAPSE AGAR TERLIHAT */
        .collapse {
            transition: all 0.3s ease;
        }

        .collapse.show {
            display: block;
        }

        /* PENTING: BIAR DROPDOWN MUNCUL */
        .sidebar .collapse {
            position: relative;
            z-index: 9999;
        }

        /* ================= DEFAULT AVATAR ================= */
        .default-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #d1d5db;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            border: 2px solid #fff;
        }

        .table {
            border-radius: 12px;
            overflow: hidden;
        }

        .table thead {
            background: #f3f4f6;
        }

        .table tbody tr:hover {
            background: #f9fafb;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-thumb {
            background: #9ca3af;
            border-radius: 10px;
        }

        .welcome-box {
            background: white;
            padding: 20px;
            border-radius: 18px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        /* ================= FOOTER MAHASISWA ================= */
        body {
            min-height: 100vh;
        }

        /* KHUSUS MAHASISWA */
        .footer-mahasiswa {

            position: fixed;

            bottom: 0;

            left: 250px;

            width: calc(100% - 250px);

            background: transparent;

            padding: 15px;

            font-weight: 600;

            z-index: 10;

            backdrop-filter: blur(5px);

        }

        /* SAAT SIDEBAR COLLAPSE */
        .sidebar.collapsed~.content-wrapper .footer-mahasiswa {

            left: 70px;

            width: calc(100% - 70px);

        }
    </style>

</head>

<body>

    <!-- ================= TOPBAR ================= -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-4 py-3 border-bottom sticky-top">

        <div class="container-fluid">

            <!-- LEFT -->
            <div class="d-flex align-items-center">

                <!-- TOGGLE -->
                <button id="toggleSidebar"
                    class="btn btn-primary me-3">
                    ☰
                </button>

                <!-- TITLE -->
                <span class="navbar-brand fw-bold mb-0">
                    SIMKM
                </span>

            </div>

            <!-- RIGHT -->
            <div class="d-flex align-items-center">

                <!-- ROLE -->
                <span class="badge 
            <?php
            if ($roleUser == 'admin') {
                echo 'bg-danger';
            } elseif ($roleUser == 'konselor') {
                echo 'bg-success';
            } else {
                echo 'bg-primary';
            }
            ?>
            me-3">
                    <?= ucfirst($roleUser) ?>
                </span>

                <!-- PROFILE -->
                <div class="dropdown">

                    <a href="#"
                        class="d-flex align-items-center text-decoration-none"
                        data-bs-toggle="dropdown">

                        <?php if ($showPhoto): ?>

                            <!-- FOTO ADMIN -->
                            <?php if ($roleUser == 'admin'): ?>

                                <img src="/AplikasiSkripsi/uploads/profile/<?= $fotoUser ?>"
                                    width="45"
                                    height="45"
                                    class="rounded-circle border shadow-sm"
                                    style="object-fit:cover;">

                            <?php else: ?>

                                <div class="default-avatar">
                                    👤
                                </div>

                            <?php endif; ?>

                        <?php else: ?>

                            <!-- ICON USER -->
                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                style="width:45px; height:45px; font-size:18px;">

                                👤

                            </div>

                        <?php endif; ?>

                        <!-- NAMA -->
                        <span class="ms-2 text-dark fw-bold text-nowrap">
                            <?= htmlspecialchars($namaUser) ?>
                        </span>

                    </a>

                    <!-- DROPDOWN -->
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">

                        <li>
                            <a class="dropdown-item"
                                href="/AplikasiSkripsi/profile.php">
                                👤 Profil Saya
                            </a>
                        </li>

                        <?php if ($roleUser == 'admin'): ?>
                            <li>
                                <a class="dropdown-item"
                                    href="/AplikasiSkripsi/admin/system_settings.php">
                                    ⚙ Pengaturan
                                </a>
                            </li>
                        <?php endif; ?>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item text-danger"
                                href="/AplikasiSkripsi/logout.php">
                                🚪 Logout
                            </a>
                        </li>

                    </ul>

                </div>

            </div>

        </div>

    </nav>