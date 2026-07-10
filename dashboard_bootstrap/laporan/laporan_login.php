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
// FILTER TANGGAL
// =========================
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

// =========================
// QUERY LOGIN
// =========================
$query = "
SELECT
    log_login.*,
    users.nama,
    users.username,
    users.role

FROM log_login

JOIN users
ON users.id = log_login.user_id

WHERE 1=1
";

if (!empty($start_date) && !empty($end_date)) {

    $query .= "
    AND DATE(waktu_login)
    BETWEEN '$start_date'
    AND '$end_date'
    ";
}

$query .= "
ORDER BY waktu_login DESC
";

$result = mysqli_query($mysqli, $query);

// =========================
// TOTAL LOGIN
// =========================
$totalLogin = mysqli_num_rows($result);

// =========================
// LOGIN HARI INI
// =========================
$todayLogin = mysqli_num_rows(
    mysqli_query($mysqli,"
        SELECT id
        FROM log_login
        WHERE DATE(waktu_login)=CURDATE()
    ")
);

// =========================
// TOTAL USER LOGIN
// =========================
$totalUserLogin = mysqli_num_rows(
    mysqli_query($mysqli,"
        SELECT DISTINCT user_id
        FROM log_login
    ")
);

// =========================
// USER PALING AKTIF
// =========================
$aktifQuery = mysqli_query($mysqli,"
SELECT
    users.nama,
    COUNT(log_login.id) as total_login

FROM log_login

JOIN users
ON users.id = log_login.user_id

GROUP BY log_login.user_id

ORDER BY total_login DESC

LIMIT 1
");

$userAktif = mysqli_fetch_assoc($aktifQuery);
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
            Home / Laporan / Login
        </small>
    </nav>

    <!-- =========================
         FILTER
    ========================== -->

    <div class="card shadow-sm p-4 mt-3">

        <h4 class="mb-4">
            🔐 Laporan Login User
        </h4>

        <form method="GET"
              class="row g-2">

            <div class="col-md-4">

                <label>Tanggal Awal</label>

                <input type="date"
                       name="start_date"
                       class="form-control"
                       value="<?= $start_date ?>">

            </div>

            <div class="col-md-4">

                <label>Tanggal Akhir</label>

                <input type="date"
                       name="end_date"
                       class="form-control"
                       value="<?= $end_date ?>">

            </div>

            <div class="col-md-4 d-flex align-items-end">

                <button class="btn btn-primary w-100">

                    🔍 Filter

                </button>

            </div>

        </form>

    </div>

    <!-- =========================
         CARD STATISTIK
    ========================== -->

    <div class="row mt-3">

        <div class="col-md-4">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6>Total Login</h6>

                    <h2 class="text-primary">

                        <?= $totalLogin ?>

                    </h2>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6>Login Hari Ini</h6>

                    <h2 class="text-success">

                        <?= $todayLogin ?>

                    </h2>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6>User Paling Aktif</h6>

                    <h5 class="text-danger">

                        <?= $userAktif['nama'] ?? '-' ?>

                    </h5>

                    <small>

                        <?= $userAktif['total_login'] ?? 0 ?>
                        Login

                    </small>

                </div>

            </div>

        </div>

    </div>

    <!-- =========================
         TABEL LOGIN
    ========================== -->

    <div class="card shadow-sm p-4 mt-4">

        <h5 class="mb-3">

            📋 Detail Aktivitas Login

        </h5>

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-dark">

                    <tr>

                        <th>No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Waktu Login</th>
                        <th>IP Address</th>
                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                <?php
                $no = 1;

                while($row = mysqli_fetch_assoc($result)):
                ?>

                <?php

                // =========================
                // STATUS LOGIN
                // =========================
                $loginTime = strtotime($row['waktu_login']);
                $todayTime = strtotime(date('Y-m-d'));

                $status = "Tidak Aktif";
                $badge  = "secondary";

                if(date('Y-m-d', $loginTime)
                    == date('Y-m-d')){

                    $status = "Online Hari Ini";
                    $badge  = "success";

                }
                ?>

                <tr>

                    <td><?= $no++ ?></td>

                    <td>

                        <?= htmlspecialchars($row['nama']) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($row['username']) ?>

                    </td>

                    <td>

                        <span class="badge bg-dark">

                            <?= $row['role'] ?>

                        </span>

                    </td>

                    <td>

                        <?= date(
                            'd M Y H:i:s',
                            strtotime($row['waktu_login'])
                        ) ?>

                    </td>

                    <td>

                        <?= $row['ip_address'] ?>

                    </td>

                    <td>

                        <span class="badge bg-<?= $badge ?>">

                            <?= $status ?>

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