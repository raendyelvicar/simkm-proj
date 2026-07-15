<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

require '../config/db.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    $mysqli->query("DELETE FROM users WHERE id=$id");
}

header("Location: manage_users.php");
exit;
?>
