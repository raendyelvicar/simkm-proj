<?php
session_start();

$role = $_SESSION['role'] ?? '';

if (!isset($_SESSION['user_id']) || !in_array($role, ['admin', 'konselor'])) {
    header('Location: ../login.php');
    exit;
}

require __DIR__ . '/../config/db.php';

// ================= ERROR REPORT =================
error_reporting(E_ALL);
ini_set('display_errors', 0);

// ================= AMBIL ID =================
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die("ID tidak valid");
}

// ================= DATA MAHASISWA =================
$stmt = $mysqli->prepare("
    SELECT *
    FROM users
    WHERE id = ? AND role = 'mahasiswa'
");

$stmt->bind_param("i", $id);

$stmt->execute();

$resultUser = $stmt->get_result();

if (!$resultUser || $resultUser->num_rows == 0) {
    die("Data mahasiswa tidak ditemukan");
}

$student = $resultUser->fetch_assoc();

// ================= DIARY =================
$diary = $mysqli->query("
    SELECT *
    FROM diary_entries
    WHERE user_id = '$id'
    ORDER BY created_at DESC
");

// ================= ASSESSMENT =================
$ass = $mysqli->query("
    SELECT *
    FROM assessment_results
    WHERE user_id = '$id'
    ORDER BY assessment_date DESC
");
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

    <!-- SIDEBAR -->
    <?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

    <!-- CONTENT -->
    <div class="content-wrapper p-4 w-100">

        <!-- BREADCRUMB -->
        <div class="mb-3">
            <small class="text-muted">
                Home / Mahasiswa / Detail
            </small>
        </div>

        <!-- ================= DETAIL USER ================= -->
        <div class="card shadow-sm border-0 p-4 mb-4">

            <h3 class="mb-4">
                🎓 Detail Mahasiswa
            </h3>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">
                        Nama
                    </label>

                    <div class="form-control bg-light">
                        <?= htmlspecialchars($student['nama'] ?? '-') ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">
                        Username
                    </label>

                    <div class="form-control bg-light">
                        <?= htmlspecialchars($student['username'] ?? '-') ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">
                        Email
                    </label>

                    <div class="form-control bg-light">
                        <?= htmlspecialchars($student['email'] ?? '-') ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">
                        Role
                    </label>

                    <div class="form-control bg-light">
                        <?= htmlspecialchars($student['role'] ?? '-') ?>
                    </div>
                </div>

            </div>

        </div>

        <!-- ================= DIARY ================= -->
        <div class="card shadow-sm border-0 p-4 mb-4">

            <h4 class="mb-3">
                📖 Diary Mahasiswa
            </h4>

            <?php if ($diary && mysqli_num_rows($diary) > 0): ?>

                <?php while ($d = mysqli_fetch_assoc($diary)): ?>

                    <div class="border rounded p-3 mb-3 bg-light">

                        <div class="d-flex justify-content-between mb-2">

                            <strong>
    <?php
    // ================= FIX TANGGAL DIARY =================
    $tanggalDiary = '-';

    if (!empty($d['created_at']) && $d['created_at'] != '0000-00-00') {

        $tanggalDiary = date(
            'd F Y',
            strtotime($d['created_at'])
        );

    } elseif (!empty($d['entry_date'])) {

        $tanggalDiary = date(
            'd F Y',
            strtotime($d['entry_date'])
        );

    }

    echo $tanggalDiary;
    ?>
</strong>

                            <span class="badge bg-primary">
                                <?php
$moodDiary = $d['mood_level'] ?? $d['mood'] ?? 'Netral';
?>

<?= htmlspecialchars($moodDiary) ?>
                            </span>

                        </div>

                        <p class="mb-0">
                            <?= nl2br(htmlspecialchars($d['content'])) ?>
                        </p>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="alert alert-secondary">
                    Belum ada diary mahasiswa.
                </div>

            <?php endif; ?>

        </div>

        <!-- ================= ASSESSMENT ================= -->
        <div class="card shadow-sm border-0 p-4 mb-4">

            <h4 class="mb-3">
                📊 Riwayat Assessment
            </h4>

            <?php if ($ass && mysqli_num_rows($ass) > 0): ?>

                <?php while ($a = mysqli_fetch_assoc($ass)): ?>

                    <?php
                    $badge = 'success';
                    $status = 'Ringan';

                    if ($a['total_skor'] >= 75) {
                        $badge = 'danger';
                        $status = 'Berat';
                    } elseif ($a['total_skor'] >= 50) {
                        $badge = 'warning';
                        $status = 'Sedang';
                    }
                    ?>

                    <div class="border rounded p-3 mb-3 bg-light">

                        <div class="d-flex justify-content-between">

                            <strong>
                                <?= date('d F Y', strtotime($a['assessment_date'])) ?>
                            </strong>

                            <span class="badge bg-<?= $badge ?>">
                                <?= $status ?>
                            </span>

                        </div>

                        <div class="mt-2">
                            Skor Assessment:
                            <strong>
                                <?= $a['total_skor'] ?>
                            </strong>
                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="alert alert-secondary">
                    Belum ada data assessment.
                </div>

            <?php endif; ?>

        </div>

        <!-- BUTTON -->
        <div class="mt-3">

            <a href="../admin/view_students.php"
               class="btn btn-secondary">

                ⬅ Kembali

            </a>

        </div>

    </div>

</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>