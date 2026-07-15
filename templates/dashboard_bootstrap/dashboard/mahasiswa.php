<?php
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

                    <a href="/logout" class="btn btn-danger mt-2">Logout</a>

                </div>
            </div>

        </div>
    </section>
</div>

<?php require '../layouts/footer.php'; ?>