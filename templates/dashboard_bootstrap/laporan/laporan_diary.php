<?php
session_start();

// =========================
// KONEKSI
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
// FILTER BULAN
// =========================
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// =========================
// CEK KOLOM MOOD
// =========================
$hasMood = false;

$checkMood = $mysqli->query("SHOW COLUMNS FROM diary_entries LIKE 'mood_level'");

if ($checkMood && $checkMood->num_rows > 0) {
    $hasMood = true;
}

// =========================
// TOTAL DIARY
// =========================
$totalDiary = mysqli_fetch_assoc(mysqli_query($mysqli, "
SELECT COUNT(*) as total
FROM diary_entries
WHERE MONTH(entry_date)='$bulan'
AND YEAR(entry_date)='$tahun'
"))['total'];

// =========================
// TOTAL USER AKTIF
// =========================
$totalUser = mysqli_fetch_assoc(mysqli_query($mysqli, "
SELECT COUNT(DISTINCT user_id) as total
FROM diary_entries
WHERE MONTH(entry_date)='$bulan'
AND YEAR(entry_date)='$tahun'
"))['total'];

// =========================
// REKAP MOOD
// =========================
$moodData = [];

if ($hasMood) {

    $moodQuery = mysqli_query($mysqli, "
    SELECT mood_level, COUNT(*) as jumlah
    FROM diary_entries
    WHERE MONTH(entry_date)='$bulan'
    AND YEAR(entry_date)='$tahun'
    GROUP BY mood_level
    ");

    while($m = mysqli_fetch_assoc($moodQuery)) {
        $moodData[] = $m;
    }
}

// =========================
// DATA DIARY
// =========================
$q = mysqli_query($mysqli, "
SELECT d.*, u.nama
FROM diary_entries d
JOIN users u ON d.user_id = u.id
WHERE MONTH(d.entry_date)='$bulan'
AND YEAR(d.entry_date)='$tahun'
ORDER BY d.entry_date DESC
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
                Home / Laporan Diary
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
                                <?= $bulan == sprintf('%02d',$i) ? 'selected' : ''; ?>>

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

                        <h6>Total Diary</h6>

                        <h2 class="fw-bold text-primary">

                            <?= $totalDiary; ?>

                        </h2>

                    </div>

                </div>

            </div>

            <div class="col-md-6">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <h6>User Aktif Menulis</h6>

                        <h2 class="fw-bold text-success">

                            <?= $totalUser; ?>

                        </h2>

                    </div>

                </div>

            </div>

        </div>

        <!-- =========================
             REKAP MOOD
        ========================== -->

        <?php if($hasMood): ?>

        <div class="card shadow-sm p-4 mt-4">

            <h5 class="mb-3">
                😊 Rekap Mood Bulanan
            </h5>

            <div class="row">

                <?php foreach($moodData as $m): ?>

                <?php
                    $persen = $totalDiary > 0
                        ? round(($m['jumlah'] / $totalDiary) * 100)
                        : 0;
                ?>

                <div class="col-md-6 mb-3">

                    <div class="border rounded p-3 bg-light">

                        <div class="d-flex justify-content-between">

                            <strong>

                                <?= htmlspecialchars($m['mood_level']); ?>

                            </strong>

                            <span>

                                <?= $m['jumlah']; ?> Entry

                            </span>

                        </div>

                        <div class="progress mt-2" style="height:20px;">

                            <div class="progress-bar"
                                 role="progressbar"
                                 style="width: <?= $persen; ?>%;">

                                <?= $persen; ?>%

                            </div>

                        </div>

                    </div>

                </div>

                <?php endforeach; ?>

            </div>

        </div>

        <?php endif; ?>

        <!-- =========================
             TABEL DIARY
        ========================== -->

        <div class="card shadow-sm p-4 mt-4">

            <h4 class="mb-3">
                📔 Data Diary
            </h4>

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-dark">

                        <tr>

                            <th width="70">No</th>
                            <th>User</th>
                            <th>Judul</th>

                            <?php if($hasMood): ?>
                                <th>Mood</th>
                            <?php endif; ?>

                            <th>Tanggal</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php
                    $no = 1;

                    while($d = mysqli_fetch_assoc($q)):
                    ?>

                    <tr>

                        <td><?= $no++; ?></td>

                        <td>
                            <?= htmlspecialchars($d['nama']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($d['judul']); ?>
                        </td>

                        <?php if($hasMood): ?>

                        <td>

                            <?php
                            $badge = 'secondary';

                            if(strtolower($d['mood_level']) == 'senang') {
                                $badge = 'success';
                            }

                            if(strtolower($d['mood_level']) == 'sedih') {
                                $badge = 'danger';
                            }

                            if(strtolower($d['mood_level']) == 'cemas') {
                                $badge = 'warning';
                            }

                            if(strtolower($d['mood_level']) == 'tenang') {
                                $badge = 'primary';
                            }
                            ?>

                            <span class="badge bg-<?= $badge; ?>">

                                <?= htmlspecialchars($d['mood_level']); ?>

                            </span>

                        </td>

                        <?php endif; ?>

                        <td>

                            <?= date('d M Y', strtotime($d['entry_date'])); ?>

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