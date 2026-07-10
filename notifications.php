<?php
session_start();
require 'config/db.php';

// Ambil daftar notifikasi
$res = $mysqli->query("
    SELECT * FROM notifications 
    WHERE user_id = ".$_SESSION['user_id']." 
    ORDER BY created_at DESC
");
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Notifikasi</title>
<style>
body { font-family: Arial; background:#f4f6f9; margin:0; padding:0; }
.container { max-width:800px; margin:30px auto; background:#fff; padding:20px; border-radius:10px; }
.noti { padding:12px; border-bottom:1px solid #ddd; }
.unread { background:#eaf7ff; font-weight:bold; }
.time { font-size:12px; color:#555; }
.btn { padding:6px 10px; background:#0ea5a4; color:#fff; text-decoration:none; border-radius:6px; }
</style>
</head>

<body>
<div class="container">
<h2>🔔 Notifikasi</h2>

<p><a class="btn" href="mark_all_read.php">✅ Tandai Semua Sudah Dibaca</a></p>

<?php while($row = $res->fetch_assoc()): ?>
<div class="noti <?= $row['is_read'] ? '' : 'unread' ?>">
    <?= htmlspecialchars($row['message']) ?><br>
    <span class="time"><?= $row['created_at'] ?></span>
</div>
<?php endwhile; ?>

<p><a class="btn" href=" /AplikasiSkripsi/redirect_dashboard.php">⬅ Kembali</a></p>

</div>
</body>
</html>
