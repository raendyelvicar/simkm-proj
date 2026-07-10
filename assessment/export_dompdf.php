<?php
// assessment/export_dompdf.php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/* =========================
   AMBIL ID
   ========================= */
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$uid = (int) $_SESSION['user_id'];

if ($id <= 0) {
    die("ID assessment tidak valid.");
}

/* =========================
   AMBIL DATA ASSESSMENT
   ========================= */
$stmt = $mysqli->prepare("
    SELECT 
        ar.id,
        ar.total_skor,
        ar.assessment_date,
        u.nama,
        u.username
    FROM assessment_results ar
    JOIN users u ON u.id = ar.user_id
    WHERE ar.id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows <= 0) {
    die("Data assessment tidak ditemukan.");
}

$row = $result->fetch_assoc();

/* =========================
   KATEGORI OTOMATIS
   ========================= */
$kategori = "Ringan";

if ($row['total_skor'] >= 75) {

    $kategori = "Berat";

} elseif ($row['total_skor'] >= 50) {

    $kategori = "Sedang";

}

/* =========================
   TEMPLATE HTML PDF
   ========================= */
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">

<style>

body{
    font-family: DejaVu Sans, sans-serif;
    font-size:12px;
    color:#111;
}

h1{
    text-align:center;
    color:#0ea5a4;
    margin-bottom:30px;
}

.table{
    width:100%;
    border-collapse:collapse;
}

.table td{
    padding:10px;
    border:1px solid #ddd;
}

.label{
    width:30%;
    font-weight:bold;
    background:#f5f5f5;
}

.note{
    margin-top:30px;
    padding:15px;
    background:#f9f9f9;
    border-left:4px solid #0ea5a4;
    font-size:11px;
}

.footer{
    margin-top:40px;
    text-align:right;
    font-size:11px;
    color:#777;
}

</style>

</head>

<body>

<h1>Hasil Self Assessment SIMKM</h1>

<table class="table">

<tr>
<td class="label">Nama</td>
<td>' . htmlspecialchars($row['nama']) . '</td>
</tr>

<tr>
<td class="label">Username</td>
<td>' . htmlspecialchars($row['username']) . '</td>
</tr>

<tr>
<td class="label">Tanggal Assessment</td>
<td>' . date('d F Y', strtotime($row['assessment_date'])) . '</td>
</tr>

<tr>
<td class="label">Total Skor</td>
<td>' . $row['total_skor'] . '</td>
</tr>

<tr>
<td class="label">Kategori Mental</td>
<td>' . $kategori . '</td>
</tr>

</table>

<div class="note">

<b>Catatan:</b><br>

Dokumen ini bersifat pribadi dan rahasia.
Jika hasil assessment menunjukkan tingkat kondisi sedang atau berat,
disarankan segera berkonsultasi dengan konselor atau tenaga profesional.

</div>

<div class="footer">

Dicetak otomatis oleh SIMKM

</div>

</body>
</html>
';

/* =========================
   GENERATE PDF
   ========================= */
$options = new Options();

$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

/* =========================
   DOWNLOAD PDF
   ========================= */
$filename = "assessment_" . $row['id'] . ".pdf";

$dompdf->stream($filename, [
    "Attachment" => true
]);

exit;
?>