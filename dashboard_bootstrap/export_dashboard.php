<?php
session_start();

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/db.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/* =========================
   DOMPDF CONFIG
   ========================= */
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

/* =========================
   DATA DASHBOARD
   ========================= */
$totalUser = mysqli_fetch_assoc(
    mysqli_query($mysqli, "
        SELECT COUNT(*) as total 
        FROM users
    ")
)['total'];

$totalDiary = mysqli_fetch_assoc(
    mysqli_query($mysqli, "
        SELECT COUNT(*) as total 
        FROM diary_entries
    ")
)['total'];

$totalAssess = mysqli_fetch_assoc(
    mysqli_query($mysqli, "
        SELECT COUNT(*) as total 
        FROM assessment_results
    ")
)['total'];

/* =========================
   DISTRIBUSI MENTAL
   ========================= */
$ringan = 0;
$sedang = 0;
$berat  = 0;

$qPie = mysqli_query($mysqli, "
    SELECT total_skor 
    FROM assessment_results
");

while ($d = mysqli_fetch_assoc($qPie)) {

    if ($d['total_skor'] >= 75) {

        $berat++;

    } elseif ($d['total_skor'] >= 50) {

        $sedang++;

    } else {

        $ringan++;
    }
}

/* =========================
   HTML PDF
   ========================= */
$html = '
<style>

body{
    font-family: DejaVu Sans, Arial;
    font-size:12px;
    color:#333;
}

.header{
    text-align:center;
    margin-bottom:25px;
}

.title{
    font-size:22px;
    font-weight:bold;
    color:#0f766e;
}

.subtitle{
    color:#666;
    font-size:12px;
}

.table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

.table th,
.table td{
    border:1px solid #ccc;
    padding:10px;
    text-align:center;
}

.table th{
    background:#f3f4f6;
}

.section-title{
    margin-top:30px;
    font-size:16px;
    font-weight:bold;
    color:#0f766e;
}

</style>

<div class="header">

    <div class="title">
        Laporan Dashboard SIMKM
    </div>

    <div class="subtitle">
        Tanggal Cetak : '.date('d-m-Y H:i').'
    </div>

</div>

<table class="table">

<tr>
    <th>Total User</th>
    <th>Total Diary</th>
    <th>Total Assessment</th>
</tr>

<tr>
    <td>'.$totalUser.'</td>
    <td>'.$totalDiary.'</td>
    <td>'.$totalAssess.'</td>
</tr>

</table>

<div class="section-title">
Distribusi Kondisi Mental
</div>

<table class="table">

<tr>
    <th>Ringan</th>
    <th>Sedang</th>
    <th>Berat</th>
</tr>

<tr>
    <td>'.$ringan.'</td>
    <td>'.$sedang.'</td>
    <td>'.$berat.'</td>
</tr>

</table>
';

/* =========================
   RENDER PDF
   ========================= */
$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

$dompdf->stream(
    "dashboard_simkm.pdf",
    ["Attachment" => false]
);

exit;