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
// TOTAL DATA
// =========================
$totalUser = mysqli_num_rows(
    mysqli_query($mysqli,"
    SELECT * FROM users
")
);

$totalDiary = mysqli_num_rows(
    mysqli_query($mysqli,"
    SELECT * FROM diary_entries
    WHERE MONTH(entry_date)='$bulan'
    AND YEAR(entry_date)='$tahun'
")
);

$totalAssessment = mysqli_num_rows(
    mysqli_query($mysqli,"
    SELECT * FROM assessment_results
    WHERE MONTH(assessment_date)='$bulan'
    AND YEAR(assessment_date)='$tahun'
")
);

$totalChat = mysqli_num_rows(
    mysqli_query($mysqli,"
    SELECT * FROM chat_messages
")
);

// =========================
// DATA AKTIVITAS USER
// =========================
$q = mysqli_query($mysqli,"
SELECT
    u.id,
    u.nama,
    u.username,
    u.role,

    COUNT(DISTINCT d.id) as total_diary,
    COUNT(DISTINCT a.id) as total_assessment,
    COUNT(DISTINCT c.id) as total_chat

FROM users u

LEFT JOIN diary_entries d
ON u.id = d.user_id
AND MONTH(d.entry_date)='$bulan'
AND YEAR(d.entry_date)='$tahun'

LEFT JOIN assessment_results a
ON u.id = a.user_id
AND MONTH(a.assessment_date)='$bulan'
AND YEAR(a.assessment_date)='$tahun'

LEFT JOIN chat_messages c
ON u.id = c.user_id

GROUP BY u.id

ORDER BY total_chat DESC,
         total_diary DESC
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
            Home / Laporan Aktivitas
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
         CARD STATISTIK
    ========================== -->

    <div class="row mt-3">

        <div class="col-md-3">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6>Total User</h6>

                    <h2 class="text-primary">

                        <?= $totalUser; ?>

                    </h2>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6>Total Diary</h6>

                    <h2 class="text-success">

                        <?= $totalDiary; ?>

                    </h2>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6>Total Assessment</h6>

                    <h2 class="text-warning">

                        <?= $totalAssessment; ?>

                    </h2>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6>Total Konsultasi</h6>

                    <h2 class="text-danger">

                        <?= $totalChat; ?>

                    </h2>

                </div>

            </div>

        </div>

    </div>

    <!-- =========================
         TABEL AKTIVITAS
    ========================== -->

    <div class="card shadow-sm p-4 mt-4">

        <h4 class="mb-3">
            📊 Aktivitas Pengguna Secara Detail
        </h4>

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-dark">

                    <tr>

                        <th>No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Diary</th>
                        <th>Assessment</th>
                        <th>Konsultasi</th>
                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                <?php
                $no = 1;

                while($d = mysqli_fetch_assoc($q)):
                ?>

                <?php

                // =========================
                // TOTAL AKTIVITAS
                // =========================
                $totalAktivitas =
                    $d['total_diary']
                    + $d['total_assessment']
                    + $d['total_chat'];

                // =========================
                // STATUS USER
                // =========================
                $status = "Tidak Aktif";
                $badge  = "secondary";

                if($totalAktivitas >= 1){

                    $status = "Aktif";
                    $badge  = "success";

                }

                if($totalAktivitas >= 10){

                    $status = "Sangat Aktif";
                    $badge  = "primary";

                }
                ?>

                <tr>

                    <td><?= $no++; ?></td>

                    <td>
                        <?= htmlspecialchars($d['nama']); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($d['username']); ?>
                    </td>

                    <td>

                        <span class="badge bg-dark">

                            <?= $d['role']; ?>

                        </span>

                    </td>

                    <td>

                        <span class="badge bg-success">

                            <?= $d['total_diary']; ?>

                        </span>

                    </td>

                    <td>

                        <span class="badge bg-warning">

                            <?= $d['total_assessment']; ?>

                        </span>

                    </td>

                    <td>

                        <span class="badge bg-info">

                            <?= $d['total_chat']; ?>

                        </span>

                    </td>

                    <td>

                        <span class="badge bg-<?= $badge; ?>">

                            <?= $status; ?>

                        </span>

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