<?php
session_start();

// =========================
// FIX PATH
// =========================
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/config.php';

// =========================
// CEK LOGIN
// =========================
if (!isset($_SESSION['username'])) {
    header("Location: ../../login.php");
    exit;
}

// =========================
// FILTER USER
// =========================
$keyword = $_GET['keyword'] ?? '';

// =========================
// CEK FIELD
// =========================
$hasRead = false;
$hasCreated = false;

$c1 = mysqli_query($mysqli, "
SHOW COLUMNS FROM notifications LIKE 'is_read'
");

if(mysqli_num_rows($c1) > 0){
    $hasRead = true;
}

$c2 = mysqli_query($mysqli, "
SHOW COLUMNS FROM notifications LIKE 'created_at'
");

if(mysqli_num_rows($c2) > 0){
    $hasCreated = true;
}

// =========================
// QUERY DATA
// =========================
$query = "
SELECT 
    n.*, 
    u.nama,
    u.username
FROM notifications n
JOIN users u ON n.user_id = u.id
WHERE 1=1
";

if(!empty($keyword)){
    $query .= "
    AND (
        u.nama LIKE '%$keyword%'
        OR u.username LIKE '%$keyword%'
        OR n.message LIKE '%$keyword%'
    )
    ";
}

$query .= " ORDER BY n.id DESC";

$result = mysqli_query($mysqli, $query);

// =========================
// TOTAL DATA
// =========================
$totalNotif = mysqli_num_rows(mysqli_query($mysqli,
"SELECT * FROM notifications"
));

$totalRead = 0;
$totalUnread = 0;

if($hasRead){

    $totalRead = mysqli_num_rows(mysqli_query($mysqli,
    "SELECT * FROM notifications WHERE is_read=1"
    ));

    $totalUnread = mysqli_num_rows(mysqli_query($mysqli,
    "SELECT * FROM notifications WHERE is_read=0"
    ));
}
?>

<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100"
     style="background:#f8f9fa; min-height:100vh;">

    <!-- BREADCRUMB -->
    <nav>
        <small class="text-muted">
            Home / Laporan / Notifikasi
        </small>
    </nav>

    <!-- ========================= -->
    <!-- CARD UTAMA -->
    <!-- ========================= -->
    <div class="card shadow-sm p-4 mt-3">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>🔔 Laporan Notifikasi</h4>
        </div>

        <!-- ========================= -->
        <!-- FILTER -->
        <!-- ========================= -->
        <form method="GET" class="row g-2 mb-4">

            <div class="col-md-10">
                <input type="text"
                       name="keyword"
                       class="form-control"
                       placeholder="Cari nama user / isi notifikasi..."
                       value="<?= htmlspecialchars($keyword) ?>">
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100">
                    🔍 Filter
                </button>
            </div>

        </form>

        <!-- ========================= -->
        <!-- STATISTIK -->
        <!-- ========================= -->
        <div class="row mb-4">

            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6>Total Notifikasi</h6>

                        <h2 class="text-primary">
                            <?= $totalNotif ?>
                        </h2>
                    </div>
                </div>
            </div>

            <?php if($hasRead): ?>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6>Sudah Dibaca</h6>

                        <h2 class="text-success">
                            <?= $totalRead ?>
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6>Belum Dibaca</h6>

                        <h2 class="text-danger">
                            <?= $totalUnread ?>
                        </h2>
                    </div>
                </div>
            </div>

            <?php endif; ?>

        </div>

        <!-- ========================= -->
        <!-- TABEL -->
        <!-- ========================= -->
        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-dark">
                    <tr>
                        <th width="60">No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Isi Notifikasi</th>

                        <?php if($hasRead): ?>
                            <th width="120">Status</th>
                        <?php endif; ?>

                        <?php if($hasCreated): ?>
                            <th width="180">Tanggal</th>
                        <?php endif; ?>
                    </tr>
                </thead>

                <tbody>

                <?php if(mysqli_num_rows($result) > 0): ?>

                    <?php $no = 1; ?>

                    <?php while($d = mysqli_fetch_assoc($result)): ?>

                    <tr>

                        <td><?= $no++ ?></td>

                        <td>
                            <?= htmlspecialchars($d['nama']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($d['username']) ?>
                        </td>

                        <td>
                            <?= nl2br(htmlspecialchars($d['message'])) ?>
                        </td>

                        <?php if($hasRead): ?>

                        <td>

                            <?php if($d['is_read'] == 1): ?>

                                <span class="badge bg-success">
                                    Dibaca
                                </span>

                            <?php else: ?>

                                <span class="badge bg-danger">
                                    Belum Dibaca
                                </span>

                            <?php endif; ?>

                        </td>

                        <?php endif; ?>

                        <?php if($hasCreated): ?>

                        <td>

                            <?= date(
                                'd M Y H:i',
                                strtotime($d['created_at'])
                            ) ?>

                        </td>

                        <?php endif; ?>

                    </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6"
                            class="text-center text-muted">

                            Tidak ada data notifikasi

                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

    <!-- ========================= -->
    <!-- REKAP USER -->
    <!-- ========================= -->
    <div class="card shadow-sm p-4 mt-4">

        <h5 class="mb-3">
            📊 Rekap Notifikasi User
        </h5>

        <div class="table-responsive">

            <table class="table table-bordered">

                <thead class="table-secondary">
                    <tr>
                        <th>No</th>
                        <th>Nama User</th>
                        <th>Total Notifikasi</th>
                    </tr>
                </thead>

                <tbody>

                <?php

                $rekap = mysqli_query($mysqli, "
                SELECT 
                    u.nama,
                    COUNT(n.id) as total_notif
                FROM users u
                LEFT JOIN notifications n
                    ON u.id = n.user_id
                GROUP BY u.id
                ORDER BY total_notif DESC
                ");

                $no = 1;

                while($r = mysqli_fetch_assoc($rekap)):
                ?>

                <tr>

                    <td><?= $no++ ?></td>

                    <td>
                        <?= htmlspecialchars($r['nama']) ?>
                    </td>

                    <td>
                        <span class="badge bg-primary">
                            <?= $r['total_notif'] ?> Notifikasi
                        </span>
                    </td>

                </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

    <!-- TOMBOL -->
    <a href="../../dashboard_bootstrap/dashboard_bootstrap.php"
       class="btn btn-secondary mt-3">

        ← Kembali

    </a>

</div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>