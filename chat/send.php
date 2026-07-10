<?php
session_start();
require __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$user_id = (int)$_SESSION['user_id'];

// VALIDASI INPUT
if (!isset($_POST['message']) || trim($_POST['message']) === '') {
    header("Location: index.php");
    exit;
}

$message = trim($_POST['message']);
$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;

if ($receiver_id <= 0) {
    die("Receiver tidak valid");
}

// ================= SIMPAN (LEBIH AMAN) =================
$stmt = $mysqli->prepare("
    INSERT INTO chat_messages (user_id, receiver_id, message, created_at)
    VALUES (?, ?, ?, NOW())
");

$stmt->bind_param("iis", $user_id, $receiver_id, $message);
$stmt->execute();

// ================= REDIRECT KEMBALI =================
header("Location: index.php");
exit;