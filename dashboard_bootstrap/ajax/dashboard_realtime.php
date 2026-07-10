<?php
session_start();

require __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// ================= TOTAL USER =================
$countUser = mysqli_fetch_assoc(
    mysqli_query($mysqli, "SELECT COUNT(*) as total FROM users")
)['total'] ?? 0;

// ================= TOTAL ASSESSMENT =================
$countAssessment = mysqli_fetch_assoc(
    mysqli_query($mysqli, "SELECT COUNT(*) as total FROM assessment_results")
)['total'] ?? 0;

// ================= TOTAL NOTIF =================
$countNotif = mysqli_fetch_assoc(
    mysqli_query($mysqli, "SELECT COUNT(*) as total FROM notifications")
)['total'] ?? 0;

// ================= TOTAL DIARY =================
$countDiary = mysqli_fetch_assoc(
    mysqli_query($mysqli, "SELECT COUNT(*) as total FROM diary_entries")
)['total'] ?? 0;

// ================= TOTAL LOGIN =================
$countLogin = mysqli_fetch_assoc(
    mysqli_query($mysqli, "SELECT COUNT(*) as total FROM log_login")
)['total'] ?? 0;

// ================= DISTRIBUSI STRES =================
$ringan = 0;
$sedang = 0;
$berat  = 0;

$qStres = mysqli_query($mysqli, "
    SELECT total_skor 
    FROM assessment_results
");

while($d = mysqli_fetch_assoc($qStres)){

    if($d['total_skor'] >= 75){
        $berat++;
    }
    elseif($d['total_skor'] >= 50){
        $sedang++;
    }
    else{
        $ringan++;
    }
}

// ================= RETURN JSON =================
echo json_encode([

    'user'        => $countUser,
    'assessment'  => $countAssessment,
    'notif'       => $countNotif,
    'diary'       => $countDiary,
    'login'       => $countLogin,

    'ringan'      => $ringan,
    'sedang'      => $sedang,
    'berat'       => $berat

]);