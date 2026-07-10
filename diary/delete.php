<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require '../config/db.php';

$id = (int)($_GET['id'] ?? 0);
$uid = $_SESSION['user_id'];

// Hapus diary
$mysqli->query("DELETE FROM diary_entries WHERE id=$id AND user_id=$uid");

header("Location: list.php");
exit;
?>
