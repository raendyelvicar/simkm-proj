<?php
require '../config/db.php';

$from = $_GET['from'] ?? null;
$to   = $_GET['to'] ?? null;

$where = "";

if ($from && $to) {
    $where = "WHERE DATE(assessment_date) BETWEEN '$from' AND '$to'";
}

// ================= CHART =================
$dataChart = [];
$qChart = mysqli_query($mysqli, "
    SELECT DATE_FORMAT(assessment_date, '%b') as bulan, COUNT(*) as total
    FROM assessment_results
    $where
    GROUP BY bulan
");

while ($r = mysqli_fetch_assoc($qChart)) {
    $dataChart[] = $r;
}

// ================= STATISTIK =================

// USERS
$q1 = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM users");
$user = mysqli_fetch_assoc($q1)['total'];

// DIARY (FIX NAMA TABEL)
$q2 = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM diary_entries");
$diary = mysqli_fetch_assoc($q2)['total'];

// ASSESSMENT (PAKAI FILTER)
$q3 = mysqli_query($mysqli, "
    SELECT COUNT(*) as total FROM assessment_results $where
");
$assessment = mysqli_fetch_assoc($q3)['total'];

// NOTIF
$q4 = mysqli_query($mysqli, "
    SELECT COUNT(*) as total FROM notifications WHERE is_read = 0
");
$notif = mysqli_fetch_assoc($q4)['total'];

// ================= OUTPUT JSON =================
echo json_encode([
    "chart" => $dataChart,
    "user" => $user,
    "diary" => $diary,
    "assessment" => $assessment,
    "notif" => $notif
]);