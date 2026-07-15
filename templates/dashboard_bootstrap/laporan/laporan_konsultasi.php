<?php
session_start();

// =========================
// KONEKSI DATABASE
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
// FILTER BULAN & TAHUN
// =========================
$bulan = isset($_GET['bulan'])
    ? $_GET['bulan']
    : date('m');

$tahun = isset($_GET['tahun'])
    ? $_GET['tahun']
    : date('Y');

// =========================
// TOTAL PESAN
// =========================
$totalPesan = mysqli_fetch_assoc(mysqli_query($mysqli,"
SELECT COUNT(*) as total
FROM chat_messages
WHERE MONTH(created_at)='$bulan'
AND YEAR(created_at)='$tahun'
"))['total'];

// =========================
// TOTAL USER AKTIF
// =========================
$totalUser = mysqli_fetch_assoc(mysqli_query($mysqli,"
SELECT COUNT(DISTINCT user_id) as total
FROM chat_messages
WHERE MONTH(created_at)='$bulan'
AND YEAR(created_at)='$tahun'
"))['total'];

// =========================
// DATA KONSULTASI
// =========================
$q = mysqli_query($mysqli,"
SELECT
    c.*,
    u.nama,
    u.username

FROM chat_messages c

JOIN users u
ON c.user_id = u.id

WHERE MONTH(c.created_at)='$bulan'
AND YEAR(c.created_at)='$tahun'

ORDER BY c.created_at DESC
");
?>

<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100"
     style="background:#f8f9fa; min-height:100vh;">

    <!-- =========================
         BREADCRUMB
    ========================== -->

    <nav>
        <small class="text-muted">
            Home / Laporan Konsultasi
        </small>
    </nav>

    <!-- =========================
         FILTER
    ========================== -->

    <div class="card shadow-sm p-3 mt-3">

        <form method="GET" class="row g-2">

            <div class="col-md-3">

                <label>Bulan</label>

                <select name="bulan" class="form-control">

                    <?php for($i=1; $i<=12; $i++): ?>

                    <option value="<?= sprintf('%02d',$i); ?>"
                        <?= $bulan == sprintf('%02d',$i)
                            ? 'selected'
                            : ''; ?>>

                        <?= date('F', mktime(0,0,0,$i,1)); ?>

                    </option>

                    <?php endfor; ?>

                </select>

            </div>

            <div class="col-md-3">

                <label>Tahun</label>

                <select name="tahun" class="form-control">

                    <?php for($y=date('Y'); $y>=2024; $y--): ?>

                    <option value="<?= $y; ?>"
                        <?= $tahun == $y ? 'selected' : ''; ?>>

                        <?= $y; ?>

                    </option>

                    <?php endfor; ?>

                </select>

            </div>

            <div class="col-md-3 d-flex align-items-end">

                <button class="btn btn-primary">

                    🔍 Filter

                </button>

            </div>

        </form>

    </div>

    <!-- =========================
         STATISTIK
    ========================== -->

    <div class="row mt-3">

        <div class="col-md-6">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6>Total Pesan Konsultasi</h6>

                    <h2 class="fw-bold text-primary">

                        <?= $totalPesan; ?>

                    </h2>

                </div>

            </div>

        </div>

        <div class="col-md-6">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6>Mahasiswa Aktif Konsultasi</h6>

                    <h2 class="fw-bold text-success">

                        <?= $totalUser; ?>

                    </h2>

                </div>

            </div>

        </div>

    </div>

    <!-- =========================
         TABEL LAPORAN
    ========================== -->

    <div class="card shadow-sm p-4 mt-4">

        <h4 class="mb-3">
            💬 Rekapitulasi Konsultasi Mahasiswa
        </h4>

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-dark">

                    <tr>

                        <th width="60">No</th>
                        <th>Nama Mahasiswa</th>
                        <th>Username</th>
                        <th>Isi Konsultasi</th>
                        <th>Status</th>
                        <th>Tanggal</th>

                    </tr>

                </thead>

                <tbody>

                <?php
                $no = 1;

                while($d = mysqli_fetch_assoc($q)):
                ?>

                <?php

                // =========================
                // STATUS AKTIVITAS
                // =========================
                $status = "Aktif";
                $badge  = "success";

                if(strlen($d['message']) < 10){

                    $status = "Singkat";
                    $badge  = "warning";

                }

                if(strlen($d['message']) > 100){

                    $status = "Detail";
                    $badge  = "primary";

                }
                ?>

                <tr>

                    <td>
                        <?= $no++; ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($d['nama']); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($d['username']); ?>
                    </td>

                    <td>

                        <?= nl2br(
                            htmlspecialchars(
                                substr($d['message'],0,80)
                            )
                        ); ?>

                        ...

                    </td>

                    <td>

                        <span class="badge bg-<?= $badge; ?>">

                            <?= $status; ?>

                        </span>

                    </td>

                    <td>

                        <?php
                        if(isset($d['created_at'])){

                            echo date(
                                'd M Y H:i',
                                strtotime($d['created_at'])
                            );

                        } else {

                            echo '-';

                        }
                        ?>

                    </td>

                </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

    <!-- =========================
         BUTTON
    ========================== -->

    <div class="mt-3">

        <a href="../../dashboard_bootstrap/dashboard_bootstrap.php"
           class="btn btn-secondary">

            ← Kembali

        </a>

    </div>

</div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>