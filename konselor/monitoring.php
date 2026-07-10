<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'konselor') {
    header('Location: dashboard_konselor.php');
    exit;
}

// ================= DATA MONITORING MAHASISWA =================
$queryMonitoring = mysqli_query($mysqli, "
    SELECT 
        u.id,
        u.nama, 
        a.total_skor AS skor_total,
        a.assessment_date AS tanggal
    FROM users u
    JOIN assessment_results a ON u.id = a.user_id
    ORDER BY a.assessment_date DESC
");

if (!$queryMonitoring) {
    die('Query error: ' . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Monitoring Mahasiswa</title>
<style>
body{font-family:Segoe UI;background:#f3f4f6;margin:0}
.container{padding:24px}
h2{margin-bottom:14px}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:10px;overflow:hidden}
th,td{padding:12px;border-bottom:1px solid #e5e7eb;font-size:13px}
th{background:#ecfeff;text-align:left}
.badge{padding:4px 8px;border-radius:6px;font-size:11px;font-weight:600}
.ringan{background:#dcfce7;color:#166534}
.sedang{background:#fef9c3;color:#854d0e}
.berat{background:#fee2e2;color:#991b1b}
a{color:#0f766e;text-decoration:none;font-weight:600}
.btn{
    display:inline-block;
    margin-top:16px;
    padding:8px 14px;
    background:#0f766e;
    color:#fff;
    border-radius:8px;
    font-size:13px;
}
</style>
</head>

<body>
<div class="container">
<div class="section">
<h2>Monitoring Kesehatan Mental Mahasiswa</h2>

<table>
<tr>
    <th>Nama</th>
    <th>Assessment Terakhir</th>
    <th>Tingkat Risiko</th>
    <th>Aksi</th>
</tr>

<?php if (mysqli_num_rows($queryMonitoring) > 0): ?>
<?php while($row = mysqli_fetch_assoc($queryMonitoring)): ?>
<tr>
    <td><?= htmlspecialchars($row['nama']) ?></td>
    <td><?= date('d F Y', strtotime($row['tanggal'])) ?></td>
    <td>
        <?php
        if ($row['skor_total'] >= 75) {
            echo '<span class="badge berat">Berat</span>';
        } elseif ($row['skor_total'] >= 50) {
            echo '<span class="badge sedang">Sedang</span>';
        } else {
            echo '<span class="badge ringan">Ringan</span>';
        }
        ?>
    </td>
    <td>
        <a href="detail_monitoring.php?id=<?= $row['id'] ?>">Detail</a>
    </td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
    <td colspan="5" style="text-align:center;color:#777;">
        Belum ada data assessment mahasiswa
    </td>
</tr>
<?php endif; ?>
</table>

<a href="dashboard_konselor.php" class="btn">⬅ Kembali ke Dashboard Konselor</a>

</div>
</div>
</body>
</html>
