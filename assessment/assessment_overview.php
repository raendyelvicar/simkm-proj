<?php
ini_set('session.cookie_path', '/');
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'konselor'])) {
    header('Location: ../login.php');
    exit;
}

require __DIR__ . '/../config/db.php';

// ================= DATA =================
$query = $mysqli->query("
    SELECT u.nama, 
           AVG(a.total_skor) as rata
    FROM assessment_results a 
    INNER JOIN users u ON u.id = a.user_id
    GROUP BY a.user_id
    ORDER BY rata DESC
");

$nama = [];
$rata = [];

while ($row = $query->fetch_assoc()) {
    $nama[] = $row['nama'];
    $rata[] = round($row['rata'], 2);
}
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100" style="background:#f8f9fa; min-height:100vh;">

    <div class="card shadow-sm p-4">
        <h4>📊 Grafik Rata-Rata Skor Assessment</h4>

        <canvas id="chartOverview"></canvas>

        <div class="mt-3">
            <a href="../assessment/assessment_chart.php" class="btn btn-primary btn-sm"> Selanjutnya</a>
            <a href="../dashboard_bootstrap/dashboard_bootstrap.php" class="btn btn-secondary btn-sm"> Kembali</a>
        </div>

<!-- =========================
     BUTTON
     ========================= -->
<div class="mt-3">

    <a href="start.php"
       class="btn btn-success w-100 shadow-sm">

        🧠 Memulai Kuis Self-Assessment

    </a>

</div>

    </div>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('chartOverview');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($nama) ?>,
        datasets: [{
            label: 'Rata-Rata Skor',
            data: <?= json_encode($rata) ?>,
            backgroundColor: '#f6c23e'
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});
</script>