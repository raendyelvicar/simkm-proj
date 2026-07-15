<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['user_id'];
$username = $_SESSION['nama'];

// --- INSERT DIARY BARU ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mood = $mysqli->real_escape_string($_POST['mood']);
    $content = $mysqli->real_escape_string($_POST['content']);

    if (!empty($mood) && !empty($content)) {
        $mysqli->query("INSERT INTO diary_entries (user_id, mood, content, entry_date) 
                        VALUES ($uid, '$mood', '$content', NOW())");
        $msg = "Diary berhasil ditambahkan!";
    } else {
        $msg = "Semua field wajib diisi.";
    }
}

// --- LOAD DIARY LIST ---
$list = $mysqli->query("SELECT * FROM diary_entries WHERE user_id=$uid ORDER BY entry_date DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Diary Saya</title>
<style>
body {font-family:Arial;background:#f4f7fa;margin:0;padding:20px;}
.container{max-width:800px;margin:auto}
.card{background:#fff;padding:20px;border-radius:10px;box-shadow:0 5px 20px rgba(0,0,0,0.08);margin-bottom:20px}
input,textarea,select{width:100%;padding:10px;margin-top:6px;border:1px solid #ccc;border-radius:6px}
button{padding:10px 18px;background:#0ea5a4;color:#fff;border:0;border-radius:6px;margin-top:10px;cursor:pointer}
table{width:100%;border-collapse:collapse;margin-top:10px}
th,td{padding:10px;border-bottom:1px solid #ccc;text-align:left}
a{color:#0ea5a4;text-decoration:none}
.msg{padding:10px;background:#e0fff4;border-left:5px solid #0ea5a4;margin-bottom:10px}
</style>
</head>
<body>
<div class="container">

<h2>Diary Harian — <?= htmlspecialchars($username) ?></h2>
<p><a href="dashboard.php">⬅ Kembali ke Dashboard</a></p>

<?php if(isset($msg)): ?>
<div class="msg"><?= $msg ?></div>
<?php endif; ?>

<div class="card">
<h3>Tulis Diary Baru</h3>
<form method="post">
    <label>Mood Hari Ini</label>
    <select name="mood" required>
        <option value="">Pilih...</option>
        <option>Bahagia</option>
        <option>Stres</option>
        <option>Lelah</option>
        <option>Cemas</option>
        <option>Biasa saja</option>
    </select>

    <label>Isi Catatan</label>
    <textarea name="content" rows="4" required></textarea>

    <button type="submit">Simpan</button>
</form>
</div>

<div class="card">
<h3>Riwayat Diary</h3>

<table>
<tr>
    <th>Tanggal</th>
    <th>Mood</th>
    <th>Catatan</th>
</tr>
<?php while($d = $list->fetch_assoc()): ?>
<tr>
    <td><?= $d['entry_date'] ?></td>
    <td><?= htmlspecialchars($d['mood']) ?></td>
    <td><?= nl2br(htmlspecialchars(substr($d['content'],0,120))) ?>...</td>
</tr>
<?php endwhile; ?>
</table>

</div>
</div>
</body>
</html>
