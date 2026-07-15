<?php
session_start();

// proteksi login konselor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'konselor') {
    header("Location: ../login.php");
    exit;
}

// layout
require '../layouts/header.php';
require '../layouts/sidebar.php';
?>

<div class="content-wrapper">
<section class="content p-3">

<div class="container-fluid">

<div class="card bg-warning">
<div class="card-body">
<h3>Dashboard Konselor</h3>
<p>Selamat datang, <?= $_SESSION['username']; ?></p>
<p>Kelola data mahasiswa dan konsultasi di sini</p>

<a href="../logout.php" class="btn btn-danger mt-2">Logout</a>

</div>
</div>

</div>
</section>
</div>

<?php require '../layouts/footer.php'; ?>