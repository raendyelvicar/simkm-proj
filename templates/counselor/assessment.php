<?php
session_start();
require '../config/db.php';

// ================= VALIDASI SESSION =================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'konselor') {
    header('Location: dashboard_konselor.php');
    exit;
}

// ================= AMBIL DATA ASSESSMENT =================
$queryAssessment = mysqli_query($mysqli, "
    SELECT 
        u.nama,
        a.total_skor,
        a.assessment_date
    FROM assessment_results a
    JOIN users u ON a.user_id = u.id
    ORDER BY a.assessment_date DESC
");

if (!$queryAssessment) {
    die("Query gagal: " . mysqli_error($mysqli));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Hasil Self-Assessment</title>
<style>
body{font-family:Segoe UI;background:#f3f4f6;margin:0}
.container{padding:24px}
.card{background:#fff;padding:16px;border-radius:12px;margin-bottom:14px}
h3{margin:0 0 6px}
p{font-size:13px;color:#555}
.status{font-weight:700}
.ringan{color:#166534}
.sedang{color:#854d0e}
.berat{color:#991b1b}
.back{display:inline-block;margin-top:20px;text-decoration:none;color:#0f766e;font-weight:600}
</style>
</head>

<body>
<div class="container">

<h2>Hasil Self-Assessment Mahasiswa</h2>

<!-- TOMBOL EXPORT PDF -->
<a href="export_assessment_konselor.php" 
   style="display:inline-block;
   background:#0ea5a4;
   color:white;
   padding:8px 14px;
   border-radius:6px;
   text-decoration:none;
   margin-bottom:15px;">
   📄 Export PDF
</a>

<?php if (mysqli_num_rows($queryAssessment) == 0): ?>
    <p>Belum ada data assessment.</p>
<?php endif; ?>

<?php while ($row = mysqli_fetch_assoc($queryAssessment)): ?>

<?php
// ================= HITUNG STATUS =================
if ($row['total_skor'] >= 75) {
    $status = "Berat";
    $class = "berat";
    $catatan = "Perlu penanganan intensif.";
} elseif ($row['total_skor'] >= 50) {
    $status = "Sedang";
    $class = "sedang";
    $catatan = "Disarankan sesi konseling lanjutan.";
} else {
    $status = "Ringan";
    $class = "ringan";
    $catatan = "Perlu pemantauan ringan dan follow-up.";
}
?>

<div class="card">
    <h3><?= htmlspecialchars($row['nama']) ?></h3>
    <p>Status:
        <span class="status <?= $class ?>"><?= $status ?></span>
    </p>
    <p>Tanggal Assessment: <?= date('d F Y', strtotime($row['assessment_date'])) ?></p>
    <p>Catatan Konselor: <?= $catatan ?></p>
</div>

<?php endwhile; ?>

<a href="dashboard_konselor.php" class="back">⬅ Kembali ke Dashboard Konselor</a>

</div>
</body>
</html>
