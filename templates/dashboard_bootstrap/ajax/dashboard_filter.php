<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require __DIR__ . '/../../config/db.php';

$from = $_GET['from'] ?? null;
$to   = $_GET['to'] ?? null;

// ================= TOTAL USER =================
$qUser = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM users");
$user = mysqli_fetch_assoc($qUser)['total'];

// ================= ASSESSMENT =================
if ($from && $to) {
    $qAssessment = mysqli_query($mysqli, "
        SELECT COUNT(*) as total 
        FROM assessment_results 
        WHERE DATE(assessment_date) BETWEEN '$from' AND '$to'
    ");
} else {
    $qAssessment = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM assessment_results");
}
$assessment = mysqli_fetch_assoc($qAssessment)['total'];

// ================= DIARY =================
$qDiary = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM diary_entries");
$diary = mysqli_fetch_assoc($qDiary)['total'];

// ================= NOTIF =================
$qNotif = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM notifications");
$notif = mysqli_fetch_assoc($qNotif)['total'];

// ================= ✅ TAMBAHAN LOGIN =================
$qLogin = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM log_login");
$login = mysqli_fetch_assoc($qLogin)['total'];

// ================= CHART =================
$chart = [];

$qChart = mysqli_query($mysqli, "
    SELECT DATE_FORMAT(assessment_date, '%b') as bulan, COUNT(*) as total
    FROM assessment_results
    GROUP BY bulan
");

while ($row = mysqli_fetch_assoc($qChart)) {
    $chart[] = $row;
}

// ================= OUTPUT =================
echo json_encode([
    'user' => (int)$user,
    'assessment' => (int)$assessment,
    'diary' => (int)$diary,
    'notif' => (int)$notif,
    'login' => (int)$login, // ✅ TAMBAHAN
    'chart' => $chart
]);
