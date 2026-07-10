<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'config/db.php';

$user_id = $_SESSION['user_id'];


// ---------------------------
// 1. DATA GRAFIK ASSESSMENT
// ---------------------------
$a_assessment = $mysqli->query("
    SELECT assessment_date, result_summary, created_at  
    FROM assessment_results 
    WHERE user_id = $user_id 
    ORDER BY created_at ASC
");

$a_tanggal = [];
$a_skor = [];

while ($row = $a_assessment->fetch_assoc()) {
    $a_tanggal[] = $row['assessment_date']; 
    $a_skor[] = (int)$row['result_summary']; 
}

// ---------------------------
// 2. DATA GRAFIK MOOD DIARY
// ---------------------------
$q_diary = $mysqli->query("
    SELECT entry_date, content 
    FROM diary_entries 
    WHERE user_id = $user_id 
    ORDER BY entry_date ASC
");

$d_tanggal = [];
$d_mood = [];

while ($row = $q_diary->fetch_assoc()) {
    $d_tanggal[] = $row['entry_date'];
    $d_mood[] = isset($row['mood']) ? (int)$row['mood'] : 0;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Grafik Perkembangan</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body {
    font-family: Arial;
    background:#f3f4f6;
}
.container{
    width:90%; 
    margin:40px auto;
    background:#fff; 
    padding:20px;
    border-radius:12px;
    box-shadow:0px 4px 20px rgba(0,0,0,0.1);
}
h2{ margin-top:20px; }
canvas{
    padding:20px;
    background:#fff;
    border-radius:10px;
}
.btn{
    padding:10px 14px;
    background:#0ea5a4;
    color:white;
    text-decoration:none;
    border-radius:8px;
}
</style>
</head>

<body>

<div class="container">
    <h1>📊 Grafik Perkembangan Anda</h1>
    <p>Berikut adalah grafik perkembangan mental berdasarkan Assessment dan Diary.</p>

    <hr><br>

    <!-- Grafik Assessment -->
    <h2>1. Grafik Perkembangan Assessment</h2>
    <canvas id="ChartAssessment" height="120"></canvas>

    <br><hr><br>

    <!-- Grafik Mood Diary -->
    <h2>2. Grafik Mood Harian (Diary)</h2>
    <canvas id="ChartMood" height="120"></canvas>

    <br>
    <a href="dashboard.php" class="btn">← Kembali ke Dashboard</a>
</div>


<script>
// -------------------
// Grafik Assessment
// -------------------
const ctxA = document.getElementById('ChartAssessment').getContext('2d');
new Chart(ctxA, {
    type: 'line',
    data: {
        labels: <?= json_encode($a_tanggal) ?>,
        datasets: [{
            label: 'Skor Assessment',
            data: <?= json_encode($a_skor) ?>,
            borderWidth: 2,
            borderColor: 'blue',
            tension: 0.25,
            fill: false
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true, max: 100 } }
    }
});


// -------------------
// Grafik Mood Diary
// -------------------
const ctxD = document.getElementById('ChartMood').getContext('2d');
new Chart(ctxD, {
    type: 'line',
    data: {
        labels: <?= json_encode($d_tanggal) ?>,
        datasets: [{
            label: 'Level Mood (1 = buruk, 10 = sangat baik)',
            data: <?= json_encode($d_mood) ?>,
            borderWidth: 2,
            borderColor: 'orange',
            tension: 0.3,
            fill: false
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true, max: 10 } }
    }
});
</script>

</body>
</html>
