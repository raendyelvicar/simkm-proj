<?php
session_start();

// proteksi login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ✅ PINDAHKAN KE SINI
require 'layouts/header.php';
require 'layouts/sidebar.php';
?>

<div class="content-wrapper">
<section class="content p-3">

<div class="container-fluid">

<div class="card bg-info">
<div class="card-body">
<h3>Dashboard Admin</h3>
<p>Selamat datang, <?= $_SESSION['username']; ?></p>
<p>Kelola sistem di sini</p>

<a href="../logout.php" class="btn btn-danger mt-2">Logout</a>

</div>
</div>

</div>
</section>
</div>

<?php require '../layouts/footer.php'; ?>