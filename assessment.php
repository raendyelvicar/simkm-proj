<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['user_id'];
$username = $_SESSION['nama'];

// --- JIKA SUBMIT FORM ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $q1 = intval($_POST['q1']);
    $q2 = intval($_POST['q2']);
    $q3 = intval($_POST['q3']);
    $q4 = intval($_POST['q4']);
    $q5 = intval($_POST['q5']);

    $score = $q1 + $q2 + $q3 + $q4 + $q5;

    // kategori sederhana
    if ($score <= 7) $cat = "Sehat / Baik";
    else if ($score <= 12) $cat = "Perlu Perhatian";
    else $cat = "Butuh Bantuan Profesional";

    $mysqli->query("INSERT INTO assessment_results (user_id, score, category, created_at)
                    VALUES ($uid, $score, '$cat', NOW())");

    $msg = "Assessment berhasil disimpan!";
}

// Load hasil terakhir
$res = $mysqli->query("SELECT * FROM assessment_results 
                       WHERE user_id=$uid ORDER BY created_at DESC LIMIT 1");
$last = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Self Assessment</title>
<style>
body{font-family:Arial;background:#f4f7fa;margin:0;padding:20px}
.container{max-width:800px;margin:auto}
.card{background:#fff;padding:20px;border-radius:10px;box-shadow:0 5px 20px rgba(0,0,0,0.08);margin-bottom:20px}
select{width:100%;padding:10px;border-radius:6px;margin-top:6px}
button{padding:10px 18px;background:#0ea5a4;color:#fff;border:0;border-radius:6px;margin-top:10px;cursor:pointer}
.msg{padding:10px;background:#e0fff4;border-left:5px solid #0ea5a4;margin-bottom:10px}
</style>
</head>
<body>

<div class="container">
<h2>Self-Assessment — <?= htmlspecialchars($username) ?></h2>
<p><a href="dashboard.php">⬅ Kembali ke Dashboard</a></p>

<?php if(isset($msg)): ?>
<div class="msg"><?= $msg ?></div>
<?php endif; ?>

<div class="card">
<h3>Form Self-Assessment</h3>
<form method="post">

<label>1. Apakah Anda merasa stres hari ini?</label>
<select name="q1" required>
    <option value="1">Tidak</option>
    <option value="2">Sedikit</option>
    <option value="3">Cukup</option>
    <option value="4">Parah</option>
</select>

<label>2. Seberapa baik tidur Anda?</label>
<select name="q2" required>
    <option value="1">Sangat baik</option>
    <option value="2">Baik</option>
    <option value="3">Kurang</option>
    <option value="4">Buruk</option>
</select>

<label>3. Seberapa lelah Anda hari ini?</label>
<select name="q3" required>
    <option value="1">Tidak</option>
    <option value="2">Sedikit</option>
    <option value="3">Cukup</option>
    <option value="4">Sangat lelah</option>
</select>

<label>4. Apakah Anda merasa cemas akhir-akhir ini?</label>
<select name="q4" required>
    <option value="1">Tidak</option>
    <option value="2">Sedikit</option>
    <option value="3">Cukup</option>
    <option value="4">Parah</option>
</select>

<label>5. Mood Anda hari ini?</label>
<select name="q5" required>
    <option value="1">Bahagia</option>
    <option value="2">Netral</option>
    <option value="3">Sedih</option>
    <option value="4">Sangat buruk</option>
</select>

<button type="submit">Simpan Hasil</button>
</form>
</div>

<?php if($last): ?>
<div class="card">
<h3>Hasil Assessment Terakhir</h3>
<p><strong>Skor:</strong> <?= $last['score'] ?></p>
<p><strong>Kategori:</strong> <?= $last['category'] ?></p>
<p><strong>Tanggal:</strong> <?= $last['created_at'] ?></p>
</div>
<?php endif; ?>

</div>
</body>
</html>
