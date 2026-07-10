<?php
session_start();
if($_SESSION['role'] !== "admin") exit();

require '../../config/db.php';

$id = $_GET['id'];
$mysqli->query("DELETE FROM articles WHERE id=$id");

header("Location: index.php");
exit();
?>
