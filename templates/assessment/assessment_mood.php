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
   ARRAY CHART
   ========================= */
$labels = [];
$datasets = [];

/* =========================
   ADMIN / KONSELOR
   ========================= */
if ($role == 'admin' || $role == 'konselor') {

    $query = $mysqli->query("
        SELECT
            users.nama,
            diary_entries.created_at,
            diary_entries.mood_level
        FROM diary_entries
        JOIN users
            ON users.id = diary_entries.user_id
        ORDER BY diary_entries.created_at ASC
    ");

    $groupData = [];

    while ($row = $query->fetch_assoc()) {

        $tanggal = date(
            'd M',
            strtotime($row['created_at'])
        );

        if (!in_array($tanggal, $labels)) {
            $labels[] = $tanggal;
        }

        // KONVERSI MOOD KE ANGKA
        $nilaiMood = 2;

        if (strtolower($row['mood_level']) == 'senang') {
            $nilaiMood = 3;
        }

        if (strtolower($row['mood_level']) == 'sedih') {
            $nilaiMood = 1;
        }

        $groupData[$row['nama']][] = $nilaiMood;
    }

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

            'borderColor' =>
                $colors[$i % count($colors)],

            'backgroundColor' => 'transparent',

            'borderWidth' => 3,

            'tension' => 0.3,

            'fill' => false,

            'pointRadius' => 5
        ];

        $i++;
    }

} else {

    /* =========================
       MAHASISWA
       ========================= */

    $query = $mysqli->query("
        SELECT
            created_at,
            mood_level
        FROM diary_entries
        WHERE user_id = '$user_id'
        ORDER BY created_at ASC
    ");

    $moodData = [];

    while ($row = $query->fetch_assoc()) {

        $labels[] = date(
            'd M',
            strtotime($row['created_at'])
        );

        $nilaiMood = 2;

        if (strtolower($row['mood_level']) == 'senang') {
            $nilaiMood = 3;
        }

        if (strtolower($row['mood_level']) == 'sedih') {
            $nilaiMood = 1;
        }

        $moodData[] = $nilaiMood;
    }

    $datasets[] = [

        'label' => 'Mood Harian',

        'data' => $moodData,

        'borderColor' => '#2563eb',

        'backgroundColor' =>
            'rgba(37,99,235,0.1)',

        'borderWidth' => 3,

        'tension' => 0.3,

        'fill' => true,

        'pointRadius' => 5
    ];
}
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100"
     style="background:#f8f9fa; min-height:100vh;">

    <div class="card shadow-sm p-4">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h4 class="mb-1">
                    😊 Grafik Mood Harian
                </h4>

                <small class="text-muted">
                    Riwayat perkembangan mood harian mahasiswa
                </small>

            </div>

            <div class="d-flex gap-2">

                <a href="assessment_chart.php"
                   class="btn btn-secondary btn-sm">

                    ⬅ Sebelumnya

                </a>

            </div>

        </div>

        <!-- CHART -->
        <div style="height:420px;">

            <canvas id="moodChart"></canvas>

        </div>

        <!-- INFO -->
        <div class="alert alert-info mt-4">

            <b>Informasi:</b>
            <br>

            Grafik ini menampilkan perubahan mood harian
            mahasiswa berdasarkan diary yang telah diisi.

        </div>

    </div>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const ctx = document
    .getElementById('moodChart')
    .getContext('2d');

new Chart(ctx, {

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

                max: 3,

                ticks: {

                    callback: function(value){

                        if(value == 1){
                            return 'Sedih';
                        }

                        if(value == 2){
                            return 'Netral';
                        }

                        if(value == 3){
                            return 'Senang';
                        }

                    }

                }

            }

        }

    }

});

</script>