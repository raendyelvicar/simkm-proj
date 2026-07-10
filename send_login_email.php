<?php
require 'config/db.php';

$user_id = $_SESSION['user_id'];

$q = $mysqli->query("SELECT email, username FROM users WHERE id = $user_id");
$user = $q->fetch_assoc();

$email = $user['email'];
$username = $user['username'];

$subject = "Login Notifikasi SIMKM";
$message = "
Halo $username,

Akun Anda baru saja login ke sistem SIMKM.

Jika ini bukan Anda, segera hubungi admin.

Tanggal: ".date('d-m-Y H:i:s')."
IP: ".$_SERVER['REMOTE_ADDR']."
";

$headers = "From: simkm@kampus.ac.id";

mail($email, $subject, $message, $headers);
?>