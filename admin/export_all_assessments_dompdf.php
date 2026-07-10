<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

/* ===========================
   LOAD DOMPDF (Composer)
   =========================== */
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/* ===============================
   AMBIL DATA ASSESSMENT
   =============================== */
$q = $mysqli->query("
    SELECT 
        u.nama,
        u.username,
        a.total_skor,
        a.result_summary,
        a.created_at
    FROM assessment_results a
    JOIN users u ON u.id = a.user_id
    ORDER BY a.created_at DESC
");

if (!$q || $q->num_rows === 0) {
    die("Data assessment belum tersedia.");
}

/* ===============================
   TEMPLATE HTML
   =============================== */
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
    color: #111;
}
h1 {
    text-align: center;
    color: #0ea5a4;
    margin-bottom: 4px;
}
.sub {
    text-align: center;
    font-size: 10px;
    color: #555;
    margin-bottom: 15px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    border: 1px solid #cbd5e1;
    padding: 6px;
}
th {
    background: #0ea5a4;
    color: #fff;
    text-align: center;
}
td {
    vertical-align: top;
}
.center { text-align: center; }
.footer {
    margin-top: 18px;
    font-size: 9px;
    color: #555;
}
</style>
</head>
<body>

<h1>LAPORAN HASIL ASSESSMENT MAHASISWA</h1>
<div class="sub">
Sistem Informasi Manajemen Kesehatan Mental (SIMKM)<br>
Tanggal Cetak: '.date('d-m-Y H:i').'
</div>

<table>
<thead>
<tr>
  <th>No</th>
  <th>Nama Mahasiswa</th>
  <th>Username</th>
  <th>Skor</th>
  <th>Kategori</th>
  <th>Tanggal</th>
</tr>
</thead>
<tbody>
';

$no = 1;
while ($row = $q->fetch_assoc()) {
    $html .= '
    <tr>
      <td class="center">'.$no++.'</td>
      <td>'.htmlspecialchars($row['nama']).'</td>
      <td>'.htmlspecialchars($row['username']).'</td>
      <td class="center">'.$row['total_skor'].'</td>
      <td class="center">'.htmlspecialchars($row['result_summary']).'</td>
      <td class="center">'.date('d-m-Y', strtotime($row['created_at'])).'</td>
    </tr>
    ';
}

$html .= '
</tbody>
</table>

<div class="footer">
Dokumen ini bersifat rahasia dan hanya digunakan untuk kepentingan internal SIMKM.
</div>

</body>
</html>
';

/* ===============================
   GENERATE PDF
   =============================== */
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$filename = "laporan_semua_assessment.pdf";
$dompdf->stream($filename, ["Attachment" => true]);
exit;
