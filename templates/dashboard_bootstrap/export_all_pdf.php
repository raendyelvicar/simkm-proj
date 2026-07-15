<?php
session_start();

require __DIR__ . '../../vendor/autoload.php';
require __DIR__ . '../../config/db.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/* =========================
   DOMPDF CONFIG
   ========================= */
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

/* =========================
   HTML HEADER
   ========================= */
$html = '
<style>

body{
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size:12px;
    color:#333;
}

.header{
    text-align:center;
    margin-bottom:20px;
}

.title{
    font-size:22px;
    font-weight:bold;
    color:#0f766e;
}

.subtitle{
    font-size:12px;
    color:#666;
}

.section{
    margin-top:25px;
}

.section h3{
    background:#0ea5a4;
    color:white;
    padding:8px;
    border-radius:4px;
    font-size:14px;
}

.table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

.table th,
.table td{
    border:1px solid #ccc;
    padding:8px;
}

.table th{
    background:#f3f4f6;
}

.watermark{
    position:fixed;
    top:35%;
    left:15%;
    opacity:0.05;
    font-size:90px;
    transform:rotate(-30deg);
    color:#000;
}

ul{
    padding-left:18px;
}

</style>

<div class="watermark">
SIMKM
</div>

<div class="header">

    <div class="title">
        LAPORAN SIMKM
    </div>

    <div class="subtitle">
        Sistem Informasi Monitoring Kesehatan Mental
    </div>

    <div class="subtitle">
        Tanggal Cetak : '.date('d-m-Y H:i').'
    </div>

</div>
';

/* =========================
   LAPORAN 1
   ========================= */
$q1 = $mysqli->query("
    SELECT COUNT(*) as total 
    FROM users 
    WHERE role='mahasiswa'
");

$d1 = $q1->fetch_assoc();

$html .= '
<div class="section">
<h3>1. Total Mahasiswa</h3>

<table class="table">
<tr>
    <th>Total Mahasiswa</th>
</tr>
<tr>
    <td>'.$d1['total'].' Mahasiswa</td>
</tr>
</table>
</div>
';

/* =========================
   LAPORAN 2
   ========================= */
$q2 = $mysqli->query("
SELECT 
SUM(CASE WHEN total_skor < 50 THEN 1 ELSE 0 END) as ringan,
SUM(CASE WHEN total_skor BETWEEN 50 AND 74 THEN 1 ELSE 0 END) as sedang,
SUM(CASE WHEN total_skor >= 75 THEN 1 ELSE 0 END) as berat
FROM assessment_results
");

$d2 = $q2->fetch_assoc();

$html .= '
<div class="section">
<h3>2. Statistik Mental</h3>

<table class="table">
<tr>
    <th>Ringan</th>
    <th>Sedang</th>
    <th>Berat</th>
</tr>
<tr>
    <td>'.$d2['ringan'].'</td>
    <td>'.$d2['sedang'].'</td>
    <td>'.$d2['berat'].'</td>
</tr>
</table>
</div>
';

/* =========================
   LAPORAN 3
   ========================= */
$q3 = $mysqli->query("
    SELECT COUNT(*) as total 
    FROM diary_entries
");

$d3 = $q3->fetch_assoc();

$html .= '
<div class="section">
<h3>3. Total Diary</h3>

<table class="table">
<tr>
    <th>Total Diary</th>
</tr>
<tr>
    <td>'.$d3['total'].' Entry</td>
</tr>
</table>
</div>
';

/* =========================
   LAPORAN 4
   ========================= */
$q4 = $mysqli->query("
SELECT u.nama, a.total_skor
FROM assessment_results a
JOIN users u ON u.id = a.user_id
ORDER BY a.total_skor DESC
LIMIT 5
");

$html .= '
<div class="section">
<h3>4. Top Risiko Tinggi</h3>

<table class="table">
<tr>
    <th>Nama</th>
    <th>Skor</th>
</tr>
';

while($r = $q4->fetch_assoc()){

    $html .= '
    <tr>
        <td>'.htmlspecialchars($r['nama']).'</td>
        <td>'.$r['total_skor'].'</td>
    </tr>
    ';
}

$html .= '</table></div>';

/* =========================
   LAPORAN 5
   ========================= */
$q5 = $mysqli->query("
    SELECT COUNT(*) as total 
    FROM chat_messages
");

$d5 = $q5->fetch_assoc();

$html .= '
<div class="section">
<h3>5. Total Chat</h3>

<table class="table">
<tr>
    <th>Total Pesan</th>
</tr>
<tr>
    <td>'.$d5['total'].' Pesan</td>
</tr>
</table>
</div>
';

/* =========================
   LAPORAN 6
   ========================= */
$q6 = $mysqli->query("
SELECT DATE(created_at) tgl, COUNT(*) total
FROM assessment_results
GROUP BY tgl
ORDER BY tgl DESC
");

$html .= '
<div class="section">
<h3>6. Aktivitas Harian Assessment</h3>

<table class="table">
<tr>
    <th>Tanggal</th>
    <th>Total</th>
</tr>
';

while($r = $q6->fetch_assoc()){

    $html .= '
    <tr>
        <td>'.$r['tgl'].'</td>
        <td>'.$r['total'].'</td>
    </tr>
    ';
}

$html .= '</table></div>';

/* =========================
   LAPORAN 7
   ========================= */
$q7 = $mysqli->query("
SELECT COUNT(*) total
FROM users
WHERE role='konselor'
");

$d7 = $q7->fetch_assoc();

$html .= '
<div class="section">
<h3>7. Total Konselor</h3>

<table class="table">
<tr>
    <th>Total Konselor</th>
</tr>
<tr>
    <td>'.$d7['total'].'</td>
</tr>
</table>
</div>
';

/* =========================
   LAPORAN 8
   ========================= */
$q8 = $mysqli->query("
SELECT nama
FROM users
WHERE role='mahasiswa'
LIMIT 5
");

$html .= '
<div class="section">
<h3>8. Sample Mahasiswa</h3>

<table class="table">
<tr>
    <th>Nama Mahasiswa</th>
</tr>
';

while($r = $q8->fetch_assoc()){

    $html .= '
    <tr>
        <td>'.htmlspecialchars($r['nama']).'</td>
    </tr>
    ';
}

$html .= '</table></div>';

/* =========================
   RENDER PDF
   ========================= */
$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

$dompdf->stream(
    "laporan_simkm.pdf",
    ["Attachment" => false]
);

exit;