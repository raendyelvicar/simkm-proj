<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../redirect_dashboard.php');
    exit;
}

require '../config/db.php';

$username = htmlspecialchars($_SESSION['username']);
$role = $_SESSION['role'];
$uid = (int) $_SESSION['user_id'];

/* ================= TIPS ================= */
$tipsList = [
    "Luangkan waktu istirahat minimal 15 menit setiap 2–3 jam belajar.",
    "Buat jadwal harian agar tugas lebih terorganisir.",
    "Jangan ragu berbicara dengan teman atau konselor.",
    "Lakukan aktivitas fisik ringan.",
    "Pastikan tidur cukup 7–8 jam."
];
$randomTip = $tipsList[array_rand($tipsList)];

/* ================= ASSESSMENT ================= */
$lastAssessment = null;
$qAssess = $mysqli->query("
    SELECT total_skor, assessment_date 
    FROM assessment_results 
    WHERE user_id = $uid 
    ORDER BY assessment_date DESC 
    LIMIT 1
");
if ($qAssess && $qAssess->num_rows > 0) {
    $lastAssessment = $qAssess->fetch_assoc();
}

/* ================= DIARY ================= */
$recentDiary = $mysqli->query("
    SELECT entry_date, content 
    FROM diary_entries 
    WHERE user_id = $uid 
    ORDER BY entry_date DESC 
    LIMIT 3
");
?>

<div class="content">

<h3>Halo, <?= $username ?> 👋</h3>
<p>Selamat datang di Dashboard Mahasiswa</p>

<!-- CARD -->
<div class="row mt-4">

    <div class="col-md-4">
        <div class="card shadow-sm p-3">
            <h5>📘 Diary</h5>
            <p>Tulis aktivitas harian</p>
            <a href="../diary/add.php" class="btn btn-primary btn-sm">Tulis</a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm p-3">
            <h5>🧠 Assessment</h5>
            <p>Cek kondisi mental</p>
            <a href="../assessment/start.php" class="btn btn-success btn-sm">Mulai</a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm p-3">
            <h5>💬 Konsultasi</h5>
            <p>Chat dengan konselor</p>
            <a href="/AplikasiSkripsi/redirect_chat.php" class="btn btn-warning btn-sm">
                Chat
            </a>
        </div>
    </div>

</div>

<!-- INFO -->
<div class="row mt-4">

    <div class="col-md-6">
        <div class="card shadow-sm p-3">
            <h5>Status Assessment</h5>
            <?php if($lastAssessment): ?>
                <?php
                if ($lastAssessment['total_skor']>=75) $s="Berat";
                elseif ($lastAssessment['total_skor']>=50) $s="Sedang";
                else $s="Ringan";
                ?>
                <span class="badge bg-danger"><?= $s ?></span>
            <?php else: ?>
                <p>Belum ada assessment</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm p-3">
            <h5>Diary Terbaru</h5>
            <ul>
                <?php if ($recentDiary && $recentDiary->num_rows>0): ?>
                    <?php while($d=$recentDiary->fetch_assoc()): ?>
                        <li><?= substr(strip_tags($d['content']),0,60) ?>...</li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>Belum ada diary</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

</div>

<!-- TIPS -->
<div class="card mt-4 p-3 shadow-sm">
    <h5>Tips Hari Ini</h5>
    <p><?= $randomTip ?></p>
</div>

</div>