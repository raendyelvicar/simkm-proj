<?php
session_start();
require '../config/db.php';

// ================= VALIDASI SESSION =================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'konselor') {
    header('Location: ../login.php');
    exit;
}

// ================= VALIDASI ID =================
if (!isset($_GET['id'])) {
    die("ID mahasiswa tidak ditemukan.");
}

$id_mahasiswa = intval($_GET['id']);

// ================= QUERY DETAIL =================
$queryDetail = mysqli_query($mysqli, "
    SELECT 
        u.nama,
        a.total_skor,
        a.assessment_date
    FROM users u
    JOIN assessment_results a ON u.id = a.user_id
    WHERE u.id = $id_mahasiswa
    ORDER BY a.assessment_date DESC
    LIMIT 1
");

if (mysqli_num_rows($queryDetail) == 0) {
    die("Data assessment belum tersedia.");
}

$data = mysqli_fetch_assoc($queryDetail);

// ================= HITUNG LEVEL RISIKO =================
if ($data['total_skor'] >= 75) {
    $risiko = "Berat";
    $class = "berat";
} elseif ($data['total_skor'] >= 50) {
    $risiko = "Sedang";
    $class = "sedang";
} else {
    $risiko = "Ringan";
    $class = "ringan";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Monitoring Mahasiswa</title>
<style>
body{font-family:Segoe UI;background:#f3f4f6;margin:0}
.container{max-width:700px;margin:40px auto;background:#fff;padding:24px;border-radius:12px}
h2{margin-bottom:16px}
.label{font-size:13px;color:#555;margin-top:12px}
.value{font-size:15px;font-weight:600;margin-top:4px}
.badge{display:inline-block;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:700}
.ringan{background:#dcfce7;color:#166534}
.sedang{background:#fef9c3;color:#854d0e}
.berat{background:#fee2e2;color:#991b1b}
.back{margin-top:24px;display:inline-block;text-decoration:none;color:#0f766e;font-weight:600}
</style>
</head>

<body>
<div class="container">

<h2>Detail Monitoring Mahasiswa</h2>

<div class="label">Nama Mahasiswa</div>
<div class="value"><?= htmlspecialchars($data['nama']) ?></div>

<div class="label">Tanggal Assessment Terakhir</div>
<div class="value"><?= date('d F Y', strtotime($data['assessment_date'])) ?></div>

<div class="label">Total Skor</div>
<div class="value"><?= $data['total_skor'] ?></div>

<div class="label">Tingkat Risiko</div>
<div class="value">
    <span class="badge <?= $class ?>"><?= $risiko ?></span>
</div>

<a href="dashboard_konselor.php" class="back">⬅ Kembali ke Dashboard Konselor</a>

</div>
</body>
</html>
