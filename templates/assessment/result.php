<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require '../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$uid = (int)$_SESSION['user_id'];

$stmt = $mysqli->prepare("SELECT * FROM assessment_results WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $uid);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    die("Hasil assessment tidak ditemukan.");
}

$row = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hasil Assessment</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container">
    <h2>Hasil Self-Assessment</h2>
    
    <p><strong>Tanggal:</strong> <?= $row['created_at'] ?></p>
    <p><strong>Skor:</strong> <?= $row['score'] ?></p>
    <p><strong>Kategori:</strong> <?= htmlspecialchars($row['category']) ?></p>

    <br>

    <a href="assessment/export_dompdf.php?id=<?= $row['id'] ?>" class="call">📄 Export PDF</a>
    <a href=" /AplikasiSkripsi/redirect_dashboard.php" class="btn">⬅Kembali ke Dashboard</a>
</div>
</body>
</html>
