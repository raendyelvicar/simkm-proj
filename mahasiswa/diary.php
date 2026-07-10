<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

require '../config/db.php';
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mood = (int)$_POST['mood'];
    $content = $mysqli->real_escape_string($_POST['content']);

    $mysqli->query("
        INSERT INTO diary_entries (user_id, entry_date, mood_level, content)
        VALUES ($user_id, CURDATE(), $mood, '$content')
    ");

    header("Location: diary.php");
    exit;
}

$diary = $mysqli->query("
    SELECT entry_date, mood_level, content
    FROM diary_entries
    WHERE user_id=$user_id
    ORDER BY entry_date DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Diary Mahasiswa</title>
<style>
body{font-family:Arial;background:#f3f4f6}
.container{max-width:700px;margin:40px auto;background:#fff;padding:20px;border-radius:10px}
textarea{width:100%;height:120px}
select,button{padding:8px}
.entry{border-bottom:1px solid #ddd;padding:10px 0}
</style>
</head>
<body>

<div class="container">
<h2>📘 Diary Harian</h2>

<form method="post">
<label>Mood Hari Ini</label><br>
<select name="mood" required>
<option value="">Pilih</option>
<option value="1">😞 Buruk</option>
<option value="3">😐 Biasa</option>
<option value="5">😊 Baik</option>
<option value="8">😄 Sangat Baik</option>
</select><br><br>

<label>Catatan</label><br>
<textarea name="content" required></textarea><br><br>

<button type="submit">Simpan Diary</button>
</form>

<hr>

<h3>Riwayat Diary</h3>
<?php while($d = $diary->fetch_assoc()): ?>
<div class="entry">
<strong><?= $d['entry_date'] ?></strong> |
Mood: <?= $d['mood_level'] ?><br>
<?= nl2br(htmlspecialchars($d['content'])) ?>
</div>
<?php endwhile; ?>

<br>
<a href="../dashboard_mahasiswa.php">⬅ Kembali</a>
</div>

</body>
</html>
