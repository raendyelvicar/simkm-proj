<?php
ob_start();

session_start();

require __DIR__ . '/../config/db.php';
require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ================= ERROR REPORTING =================
error_reporting(E_ALL);
ini_set('display_errors', 0);

// ================= CEK LOGIN =================
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$role   = $_SESSION['role'] ?? 'mahasiswa';

// ================= QUERY DATA =================
if ($role == 'admin' || $role == 'konselor') {

    $sql = "
        SELECT users.nama, diary_entries.*
        FROM diary_entries
        JOIN users ON users.id = diary_entries.user_id
        ORDER BY created_at DESC
    ";

} else {

    $sql = "
        SELECT users.nama, diary_entries.*
        FROM diary_entries
        JOIN users ON users.id = diary_entries.user_id
        WHERE diary_entries.user_id = '$userId'
        ORDER BY created_at DESC
    ";
}

$query = mysqli_query($mysqli, $sql);

// ================= VALIDASI QUERY =================
if (!$query) {
    die("Query Error: " . mysqli_error($mysqli));
}

// ================= HTML PDF =================
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

.title{
    text-align:center;
    margin-bottom:20px;
}

.title h2{
    margin:0;
    color:#2563eb;
}

.title p{
    margin-top:5px;
    color:#555;
}

table{
    width:100%;
    border-collapse:collapse;
}

table th{
    background:#1f2937;
    color:white;
    padding:10px;
    border:1px solid #ddd;
}

table td{
    padding:8px;
    border:1px solid #ddd;
    vertical-align:top;
}

.badge{
    padding:4px 8px;
    border-radius:5px;
    color:white;
    font-size:11px;
}

.senang{
    background:#16a34a;
}

.netral{
    background:#2563eb;
}

.sedih{
    background:#dc2626;
}

.footer{
    margin-top:30px;
    text-align:right;
    font-size:11px;
    color:#666;
}

</style>

</head>

<body>

<div class="title">

    <h2>Laporan Diary Harian</h2>

    <p>
        SIMKM - Sistem Informasi Kesehatan Mental
    </p>

</div>

<table>

<thead>
<tr>

    <th width="5%">No</th>
    <th width="20%">Nama</th>
    <th width="15%">Tanggal</th>
    <th width="15%">Mood</th>
    <th width="45%">Isi Diary</th>

</tr>
</thead>

<tbody>
';

$no = 1;

while($d = mysqli_fetch_assoc($query)){

    // ================= WARNA MOOD =================
    $moodClass = 'netral';

    if(strtolower($d['mood_level']) == 'senang'){
        $moodClass = 'senang';
    }

    if(strtolower($d['mood_level']) == 'sedih'){
        $moodClass = 'sedih';
    }

    $html .= '

    <tr>

        <td>'.$no++.'</td>

        <td>
            '.htmlspecialchars($d['nama']).'
        </td>

        <td>
            '.date('d M Y', strtotime($d['created_at'])).'
        </td>

        <td>
            <span class="badge '.$moodClass.'">
                '.htmlspecialchars($d['mood_level']).'
            </span>
        </td>

        <td>
            '.nl2br(htmlspecialchars($d['content'])).'
        </td>

    </tr>
    ';
}

$html .= '

</tbody>
</table>

<div class="footer">

    Dicetak pada :
    '.date('d F Y H:i').'

</div>

</body>
</html>
';

// ================= DOMPDF =================
$options = new Options();

$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'landscape');

$dompdf->render();

// ================= BERSIHKAN OUTPUT =================
ob_end_clean();

// ================= DOWNLOAD PDF =================
$dompdf->stream(
    "laporan_diary.pdf",
    ["Attachment" => false]
);

exit;
?>