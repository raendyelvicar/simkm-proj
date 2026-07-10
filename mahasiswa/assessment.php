<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

require '../config/db.php';
$user_id = $_SESSION['user_id'];

$questions = [
    "Saya merasa mudah lelah akhir-akhir ini.",
    "Saya sulit berkonsentrasi saat belajar.",
    "Saya merasa cemas tanpa alasan jelas.",
    "Saya sering merasa sedih atau putus asa.",
    "Saya sulit tidur atau tidur tidak nyenyak."
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total = 0;
    foreach ($questions as $i => $q) {
        $total += (int)$_POST["q$i"];
    }

    $stmt = $mysqli->prepare(
        "INSERT INTO assessment_results (user_id, total_skor, assessment_date, created_at)
         VALUES (?, ?, CURDATE(), NOW())"
    );
    $stmt->bind_param("ii", $user_id, $total);
    $stmt->execute();

    header("Location: ../dashboard_mahasiswa.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Self-Assessment Mahasiswa</title>
<style>
body{font-family:Arial;background:#f3f4f6}
.container{max-width:700px;margin:40px auto;background:#fff;padding:20px;border-radius:10px}
.card{margin-bottom:15px}
select{width:100%;padding:8px}
button{background:#0ea5a4;color:#fff;padding:10px;border:none;border-radius:6px}
</style>
</head>
<body>

<div class="container">
<h2>🧠 Self-Assessment Kesehatan Mental</h2>
<p>Jawab setiap pertanyaan dengan jujur.</p>

<form method="post">
<?php foreach ($questions as $i => $q): ?>
<div class="card">
<strong><?= ($i+1).". ".$q ?></strong><br>
<select name="q<?= $i ?>" required>
    <option value="">Pilih</option>
    <option value="1">1 - Tidak Pernah</option>
    <option value="2">2 - Jarang</option>
    <option value="3">3 - Kadang</option>
    <option value="4">4 - Sering</option>
    <option value="5">5 - Sangat Sering</option>
</select>
</div>
<?php endforeach; ?>

<button type="submit">Simpan Assessment</button>
</form>

<br>
<a href="../dashboard_mahasiswa.php">⬅ Kembali</a>
</div>

</body>
</html>
