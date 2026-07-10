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
// TOTAL ASSESSMENT
// =========================
$totalAssessment = mysqli_fetch_assoc(mysqli_query($mysqli, "
SELECT COUNT(*) as total
FROM assessment_results
WHERE MONTH(assessment_date)='$bulan'
AND YEAR(assessment_date)='$tahun'
"))['total'];

// =========================
// TOTAL MAHASISWA
// =========================
$totalMahasiswa = mysqli_fetch_assoc(mysqli_query($mysqli, "
SELECT COUNT(DISTINCT user_id) as total
FROM assessment_results
WHERE MONTH(assessment_date)='$bulan'
AND YEAR(assessment_date)='$tahun'
"))['total'];

// =========================
// DATA ASSESSMENT
// =========================
$q = mysqli_query($mysqli, "
SELECT
    a.*,
    u.nama,
    u.username

FROM assessment_results a

JOIN users u
ON a.user_id = u.id

WHERE MONTH(a.assessment_date)='$bulan'
AND YEAR(a.assessment_date)='$tahun'

ORDER BY a.assessment_date DESC
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
            Home / Laporan Assessment
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

                    <h6>Total Assessment</h6>

                    <h2 class="fw-bold text-primary">

                        <?= $totalAssessment; ?>

                    </h2>

                </div>

            </div>

        </div>

        <div class="col-md-6">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6>Mahasiswa Mengikuti</h6>

                    <h2 class="fw-bold text-success">

                        <?= $totalMahasiswa; ?>

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
            🧠 Laporan Hasil Self-Assessment Mahasiswa
        </h4>

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-dark">

                    <tr>

                        <th width="60">No</th>
                        <th>Nama Mahasiswa</th>
                        <th>Username</th>
                        <th>Skor</th>
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
                // KATEGORI SKOR
                // =========================
                $status = "Normal";
                $badge  = "success";

                if($d['total_skor'] >= 10
                    && $d['total_skor'] <= 30){

                    $status = "Perlu Perhatian";
                    $badge  = "warning";

                }

                if($d['total_skor'] > 30){

                    $status = "Butuh Konseling";
                    $badge  = "danger";

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

                        <span class="badge bg-primary">

                            <?= $d['total_skor']; ?>

                        </span>

                    </td>

                    <td>

                        <span class="badge bg-<?= $badge; ?>">

                            <?= $status; ?>

                        </span>

                    </td>

                    <td>

                        <?= date(
                            'd M Y',
                            strtotime($d['assessment_date'])
                        ); ?>

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