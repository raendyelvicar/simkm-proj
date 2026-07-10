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
// FILTER BULAN & TAHUN
// =========================
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// =========================
// QUERY REKAP MOOD
// =========================
$queryMood = mysqli_query($mysqli, "
SELECT 
    mood_level,
    COUNT(*) as total
FROM diary_entries
WHERE MONTH(entry_date) = '$bulan'
AND YEAR(entry_date) = '$tahun'
GROUP BY mood_level
");

// =========================
// ARRAY CHART
// =========================
$labels = [];
$data = [];

while($m = mysqli_fetch_assoc($queryMood)){

    $labels[] = $m['mood_level'];
    $data[] = $m['total'];
}

// =========================
// TOTAL DIARY
// =========================
$totalDiary = mysqli_num_rows(mysqli_query($mysqli,"
SELECT * FROM diary_entries
WHERE MONTH(entry_date)='$bulan'
AND YEAR(entry_date)='$tahun'
"));

// =========================
// TOTAL MAHASISWA AKTIF
// =========================
$totalMahasiswa = mysqli_num_rows(mysqli_query($mysqli,"
SELECT DISTINCT user_id
FROM diary_entries
WHERE MONTH(entry_date)='$bulan'
AND YEAR(entry_date)='$tahun'
"));

// =========================
// MOOD DOMINAN
// =========================
$moodDominan = '-';

$qDominan = mysqli_query($mysqli,"
SELECT mood_level, COUNT(*) as total
FROM diary_entries
WHERE MONTH(entry_date)='$bulan'
AND YEAR(entry_date)='$tahun'
GROUP BY mood_level
ORDER BY total DESC
LIMIT 1
");

if(mysqli_num_rows($qDominan) > 0){

    $d = mysqli_fetch_assoc($qDominan);

    $moodDominan = $d['mood_level'];
}

// =========================
// DATA DETAIL
// =========================
$detail = mysqli_query($mysqli,"
SELECT 
    d.*,
    u.nama
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

    <!-- BREADCRUMB -->
    <nav>
        <small class="text-muted">
            Home / Laporan / Statistik Mood
        </small>
    </nav>

    <!-- ========================= -->
    <!-- FILTER -->
    <!-- ========================= -->
    <div class="card shadow-sm p-4 mt-3">

        <form method="GET" class="row g-2">

            <div class="col-md-4">

                <label class="form-label">
                    Bulan
                </label>

                <select name="bulan" class="form-control">

                    <?php
                    for($i=1; $i<=12; $i++):
                    ?>

                    <option value="<?= $i ?>"
                        <?= ($bulan == $i ? 'selected' : '') ?>>

                        <?= date('F', mktime(0,0,0,$i,1)) ?>

                    </option>

                    <?php endfor; ?>

                </select>

            </div>

            <div class="col-md-4">

                <label class="form-label">
                    Tahun
                </label>

                <input type="number"
                       name="tahun"
                       class="form-control"
                       value="<?= $tahun ?>">

            </div>

            <div class="col-md-4 d-flex align-items-end">

                <button class="btn btn-primary w-100">
                    📊 Filter Statistik
                </button>

            </div>

        </form>

    </div>

    <!-- ========================= -->
    <!-- STATISTIK -->
    <!-- ========================= -->
    <div class="row mt-4">

        <div class="col-md-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <h6>Total Diary</h6>

                    <h2 class="text-primary">
                        <?= $totalDiary ?>
                    </h2>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <h6>Mahasiswa Aktif</h6>

                    <h2 class="text-success">
                        <?= $totalMahasiswa ?>
                    </h2>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <h6>Mood Dominan</h6>

                    <h2 class="text-danger">
                        <?= htmlspecialchars($moodDominan) ?>
                    </h2>

                </div>

            </div>

        </div>

    </div>

    <!-- ========================= -->
    <!-- GRAFIK -->
    <!-- ========================= -->
    <div class="card shadow-sm p-4 mt-4">

        <h4 class="mb-4">
            📈 Grafik Tren Mood Mahasiswa
        </h4>

        <canvas id="moodChart" height="100"></canvas>

    </div>

    <!-- ========================= -->
    <!-- TABEL DETAIL -->
    <!-- ========================= -->
    <div class="card shadow-sm p-4 mt-4">

        <h4 class="mb-3">
            📋 Detail Mood Mahasiswa
        </h4>

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-dark">

                    <tr>

                        <th>No</th>
                        <th>Nama</th>
                        <th>Judul Diary</th>
                        <th>Mood</th>
                        <th>Tanggal</th>

                    </tr>

                </thead>

                <tbody>

                <?php if(mysqli_num_rows($detail) > 0): ?>

                    <?php $no = 1; ?>

                    <?php while($d = mysqli_fetch_assoc($detail)): ?>

                    <tr>

                        <td><?= $no++ ?></td>

                        <td>
                            <?= htmlspecialchars($d['nama']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($d['judul']) ?>
                        </td>

                        <td>

<?php
$badge = 'secondary';

switch(strtolower($d['mood_level'])){

    case 'bahagia':
    case 'senang':
        $badge = 'success';
        break;

    case 'stres':
        $badge = 'danger';
        break;

    case 'cemas':
        $badge = 'warning';
        break;

    case 'lelah':
        $badge = 'dark';
        break;

    case 'biasa saja':
    case 'netral':
        $badge = 'primary';
        break;
}
?>

<span class="badge bg-<?= $badge ?>">

<?= htmlspecialchars($d['mood_level']) ?>

</span>

                        </td>

                        <td>
                            <?= date(
                                'd M Y',
                                strtotime($d['entry_date'])
                            ) ?>
                        </td>

                    </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="5"
                            class="text-center text-muted">

                            Tidak ada data mood

                        </td>

                    </tr>

                <?php endif; ?>

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

<!-- ========================= -->
<!-- CHART JS -->
<!-- ========================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const ctx = document.getElementById('moodChart');

new Chart(ctx, {

    type: 'bar',

    data: {

        labels: <?= json_encode($labels) ?>,

        datasets: [{

            label: 'Jumlah Mood',

            data: <?= json_encode($data) ?>,

            borderWidth: 1

        }]

    },

    options: {

        responsive: true,

        scales: {

            y: {

                beginAtZero: true

            }

        }

    }

});

</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>