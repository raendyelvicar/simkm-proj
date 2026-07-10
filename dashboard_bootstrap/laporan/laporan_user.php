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
// FILTER
// =========================
$role  = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';

// =========================
// QUERY USER
// =========================
$query = "
SELECT
    u.*,

    COUNT(DISTINCT d.id) as total_diary,
    COUNT(DISTINCT a.id) as total_assessment,
    COUNT(DISTINCT c.id) as total_chat

FROM users u

LEFT JOIN diary_entries d
ON u.id = d.user_id

LEFT JOIN assessment_results a
ON u.id = a.user_id

LEFT JOIN chat_messages c
ON u.id = c.user_id

WHERE 1=1
";

// =========================
// FILTER ROLE
// =========================
if($role != ''){

    $query .= "
    AND u.role='$role'
    ";
}

// =========================
// FILTER SEARCH
// =========================
if($search != ''){

    $query .= "
    AND (
        u.nama LIKE '%$search%'
        OR u.username LIKE '%$search%'
    )
    ";
}

$query .= "
GROUP BY u.id
ORDER BY u.id DESC
";

$q = mysqli_query($mysqli, $query);

// =========================
// STATISTIK USER
// =========================
$totalUser = mysqli_num_rows(
    mysqli_query($mysqli,"
        SELECT id FROM users
    ")
);

$totalAdmin = mysqli_num_rows(
    mysqli_query($mysqli,"
        SELECT id FROM users
        WHERE role='admin'
    ")
);

$totalKonselor = mysqli_num_rows(
    mysqli_query($mysqli,"
        SELECT id FROM users
        WHERE role='konselor'
    ")
);

$totalMahasiswa = mysqli_num_rows(
    mysqli_query($mysqli,"
        SELECT id FROM users
        WHERE role='mahasiswa'
    ")
);
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
            Home / Laporan / User
        </small>
    </nav>

    <!-- =========================
         FILTER
    ========================== -->

    <div class="card shadow-sm p-4 mt-3">

        <h4 class="mb-4">

            👥 Laporan Data User

        </h4>

        <form method="GET"
              class="row g-2">

            <div class="col-md-4">

                <label>Filter Role</label>

                <select name="role"
                        class="form-control">

                    <option value="">Semua Role</option>

                    <option value="admin"
                        <?= $role=='admin'
                            ? 'selected'
                            : ''; ?>>

                        Admin

                    </option>

                    <option value="konselor"
                        <?= $role=='konselor'
                            ? 'selected'
                            : ''; ?>>

                        Konselor

                    </option>

                    <option value="mahasiswa"
                        <?= $role=='mahasiswa'
                            ? 'selected'
                            : ''; ?>>

                        Mahasiswa

                    </option>

                </select>

            </div>

            <div class="col-md-4">

                <label>Cari User</label>

                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Cari nama atau username..."
                       value="<?= htmlspecialchars($search); ?>">

            </div>

            <div class="col-md-4 d-flex align-items-end">

                <button class="btn btn-primary w-100">

                    🔍 Filter Data

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

                    <h6>Admin</h6>

                    <h2 class="text-danger">

                        <?= $totalAdmin; ?>

                    </h2>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6>Konselor</h6>

                    <h2 class="text-warning">

                        <?= $totalKonselor; ?>

                    </h2>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6>Mahasiswa</h6>

                    <h2 class="text-success">

                        <?= $totalMahasiswa; ?>

                    </h2>

                </div>

            </div>

        </div>

    </div>

    <!-- =========================
         TABEL USER
    ========================== -->

    <div class="card shadow-sm p-4 mt-4">

        <h5 class="mb-3">

            📋 Detail Data User

        </h5>

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-dark">

                    <tr>

                        <th>No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Total Diary</th>
                        <th>Total Assessment</th>
                        <th>Total Konsultasi</th>
                        <th>Status Aktivitas</th>

                    </tr>

                </thead>

                <tbody>

                <?php
                $no = 1;

                while($d = mysqli_fetch_assoc($q)):
                ?>

                <?php

                // =========================
                // ROLE COLOR
                // =========================
                $roleBadge = 'secondary';

                if($d['role'] == 'admin'){
                    $roleBadge = 'danger';
                }

                if($d['role'] == 'konselor'){
                    $roleBadge = 'warning';
                }

                if($d['role'] == 'mahasiswa'){
                    $roleBadge = 'success';
                }

                // =========================
                // TOTAL AKTIVITAS
                // =========================
                $totalAktivitas =
                    $d['total_diary']
                    + $d['total_assessment']
                    + $d['total_chat'];

                $status = 'Tidak Aktif';
                $statusBadge = 'secondary';

                if($totalAktivitas >= 1){

                    $status = 'Aktif';
                    $statusBadge = 'success';

                }

                if($totalAktivitas >= 10){

                    $status = 'Sangat Aktif';
                    $statusBadge = 'primary';

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

                        <span class="badge bg-<?= $roleBadge; ?>">

                            <?= ucfirst($d['role']); ?>

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

                        <span class="badge bg-<?= $statusBadge; ?>">

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