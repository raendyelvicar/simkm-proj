<?php
session_start();

// proteksi login mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
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

<div class="card bg-success">
<div class="card-body">
<h3>Dashboard Mahasiswa</h3>
<p>Selamat datang, <?= $_SESSION['username']; ?></p>
<p>Silakan isi diary dan assessment Anda</p>

<a href="../logout.php" class="btn btn-danger mt-2">Logout</a>

</div>
</div>

</div>
</section>
</div>

<?php require '../layouts/footer.php'; ?>