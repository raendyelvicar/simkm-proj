<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require '../config/db.php';

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'mahasiswa';

/* =========================
   ARRAY DATA CHART
   ========================= */
$labels = [];
$datasets = [];

/* =========================
   JIKA ADMIN / KONSELOR
   ========================= */
if ($role == 'admin' || $role == 'konselor') {

    $query = $mysqli->query("
        SELECT 
            assessment_results.user_id,
            users.nama,
            assessment_results.tanggal_tes,
            assessment_results.total_skor
        FROM assessment_results
        JOIN users 
            ON users.id = assessment_results.user_id
        ORDER BY assessment_results.tanggal_tes ASC
    ");

    if (!$query) {

    die("Query Error: " . $mysqli->error);

}

    $groupData = [];

    while ($row = $query->fetch_assoc()) {

        $nama = $row['nama'];

        if (!in_array($row['tanggal_tes'], $labels)) {
            $labels[] = $row['tanggal_tes'];
        }

        $groupData[$nama][] = $row['total_skor'];
    }

    // WARNA OTOMATIS
    $colors = [
        '#0ea5a4',
        '#2563eb',
        '#dc2626',
        '#16a34a',
        '#f59e0b',
        '#7c3aed',
        '#db2777'
    ];

    $i = 0;

    foreach ($groupData as $nama => $nilai) {

        $datasets[] = [
            'label' => $nama,
            'data' => $nilai,
            'borderWidth' => 3,
            'borderColor' => $colors[$i % count($colors)],
            'backgroundColor' => 'transparent',
            'fill' => false,
            'tension' => 0.3,
            'pointRadius' => 5,
            'pointHoverRadius' => 7
        ];

        $i++;
    }

} else {

    /* =========================
       MAHASISWA
       ========================= */

    $query = $mysqli->query("
        SELECT 
            tanggal_tes,
            total_skor 
        FROM assessment_results 
        WHERE user_id = '$user_id'
        ORDER BY tanggal_tes ASC
    ");

    $skor = [];

    while ($row = $query->fetch_assoc()) {

        $labels[] = $row['tanggal_tes'];
        $skor[]   = $row['total_skor'];
    }

    $datasets[] = [
        'label' => 'Skor Assessment',
        'data' => $skor,
        'borderWidth' => 3,
        'borderColor' => '#0ea5a4',
        'backgroundColor' => 'rgba(14,165,164,0.1)',
        'fill' => true,
        'tension' => 0.3,
        'pointRadius' => 5,
        'pointHoverRadius' => 7
    ];
}
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100"
     style="background:#f8f9fa; min-height:100vh;">

    <!-- =========================
         CARD GRAFIK
         ========================= -->
    <div class="card shadow-sm p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h4 class="mb-1">
            📈 Grafik Perkembangan Assessment
        </h4>

        <small class="text-muted">
            Riwayat Perkembangan Skor Self-Assessment
        </small>

    </div>

    <!-- BUTTON AREA -->
    <div class="d-flex gap-2">

        <a href="assessment_mood.php"
           class="btn btn-primary btn-sm shadow-sm">

            ➜ Selanjutnya

        </a>

        <a href="history.php"
           class="btn btn-secondary btn-sm shadow-sm">

            ⬅ Kembali

        </a>

    </div>

</div>

<!-- =========================
     CHART
     ========================= -->
<div style="height:420px; position:relative;">

    <canvas id="chartAssessment"></canvas>

</div>

<!-- =========================
     INFORMASI
     ========================= -->
<div class="alert alert-info mt-4">

    <b>Informasi:</b>
    <br>

    Grafik ini menampilkan perkembangan Hasil Self-Assessment
    Kesehatan Mental Mahasiswa berdasarkan Riwayat pengisian Kuisioner.

</div>

    </div>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>

<!-- =========================
     CHART JS
     ========================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const ctx = document
    .getElementById('chartAssessment')
    .getContext('2d');

const chart = new Chart(ctx, {

    type: 'line',

    data: {

        labels: <?= json_encode($labels) ?>,

        datasets: <?= json_encode($datasets) ?>

    },

    options: {

        responsive: true,

        maintainAspectRatio: false,

        plugins: {

            legend: {
                display: true
            }

        },

        scales: {

            y: {

                beginAtZero: true,

                max: 100

            }

        }

    }

});

</script>