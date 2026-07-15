<?php
session_start();

// jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ambil role
$role = $_SESSION['role'] ?? 'mahasiswa';

// arahkan berdasarkan role
if ($role === 'konselor') {
    header("Location: /AplikasiSkripsi/konselor/chat.php");
} else {
    header("Location: /AplikasiSkripsi/chat/index.php");
}

exit;