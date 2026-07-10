<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Cek login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];

// ================= REDIRECT SESUAI ROLE =================
switch ($role) {
    case 'admin':
        header("Location: dashboard_bootstrap/dashboard_bootstrap");
        exit;

    case 'mahasiswa':
        header("Location: dashboard_bootstrap/dashboard_bootstrap.php");
        exit;

    case 'konselor':
        header("Location: dashboard_bootstrap/dashboard_bootstrap.php");
        exit;

    default:
        // Role tidak dikenal → logout paksa
        session_destroy();
        header("Location: login.php");
        exit;
}