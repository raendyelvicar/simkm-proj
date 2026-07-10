<?php

session_start();

require 'config/db.php';

// pastikan hanya admin
if($_SESSION['role']!='admin'){
    die("Akses ditolak");
}

$idUser=$_GET['id'];

$idAdmin=$_SESSION['user_id'];

$stmt=$mysqli->prepare("
UPDATE users
SET
status='active',
approved_by=?,
approved_at=NOW()
WHERE id=?
");

$stmt->bind_param("ii",$idAdmin,$idUser);

$stmt->execute();

header("Location: manage_users.php");

exit;