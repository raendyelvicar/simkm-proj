<?php
// admin/index.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}
require '../config/db.php';
$res_u = $mysqli->query("SELECT COUNT(*) as cnt FROM users WHERE role='mahasiswa'");
$users = $res_u->fetch_assoc();
$res_d = $mysqli->query("SELECT COUNT(*) as cnt FROM diary_entries");
$diary = $res_d->fetch_assoc();
$res_a = $mysqli->query("SELECT COUNT(*) as cnt FROM assessment_results");
$ass = $res_a->fetch_assoc();
?>
<!doctype html><html><head><meta charset='utf-8'><title>Admin Dashboard</title>
<link rel='stylesheet' href='../assets/css/style.css'>
</head><body>
<div class="container">
<h2>Admin Dashboard</h2>
<p>Halo, <?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?> | <a href="../logout.php">Logout</a></p>
<ul>
<li>Total Mahasiswa Terdaftar: <?php echo $users['cnt']; ?></li>
<li>Total Diary Entries: <?php echo $diary['cnt']; ?></li>
<li>Total Assessment Results: <?php echo $ass['cnt']; ?></li>
</ul>
<p><a href="view_students.php">Lihat Data Mahasiswa</a></p>
</div></body></html>

<p>
  <a href="../dashboard_bootstrap/dashboard_bootstrap.php" style="display:inline-block;margin-top:12px;color:#0ea5a4;font-weight:600;text-decoration:none;">
    ← Kembali ke Halaman Sebelumnya
  </a>
</p>
