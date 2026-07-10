<?php
ini_set('session.cookie_path', '/');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['role'] !== 'konselor') {
    header('Location: ../redirect_dashboard.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

/* ===========================
   LOAD DOMPDF (Composer)
   =========================== */
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;

/* =============================
   AMBIL DATA ASSESSMENT + USER
   ============================= */
$query = $mysqli->query("
    SELECT u.username, a.total_skor, a.result_summary, a.created_at
    FROM assessment_results a
    JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC
");

if (!$query || $query->num_rows == 0) {
    die("Data assessment tidak ditemukan.");
}

/* =============================
   BUAT HTML UNTUK PDF
   ============================= */
$html = "
<style>
body { font-family: DejaVu Sans, Arial; font-size: 12px; }
h2 { text-align:center; }
table { width:100%; border-collapse:collapse; margin-top:15px; }
th, td { border:1px solid #333; padding:6px; }
th { background:#f0f0f0; }
</style>

<h2>LAPORAN HASIL SELF-ASSESSMENT MAHASISWA</h2>
<p><strong>Dicetak oleh:</strong> ".htmlspecialchars($_SESSION['username'])."</p>
<p><strong>Tanggal Cetak:</strong> ".date('d-m-Y H:i')."</p>

<table>
<tr>
    <th>No</th>
    <th>Nama Mahasiswa</th>
    <th>Skor</th>
    <th>Status</th>
    <th>Tanggal Assessment</th>
</tr>
";

$no = 1;
while ($row = $query->fetch_assoc()) {

    if ($row['total_skor'] >= 75) {
        $status = "Berat";
    } elseif ($row['total_skor'] >= 50) {
        $status = "Sedang";
    } else {
        $status = "Ringan";
    }

    $html .= "
    <tr>
        <td>{$no}</td>
        <td>".htmlspecialchars($row['username'])."</td>
        <td>{$row['total_skor']}</td>
        <td>{$status}</td>
        <td>".date('d-m-Y', strtotime($row['created_at']))."</td>
    </tr>
    ";

    $no++;
}

$html .= "</table>";

/* =============================
   GENERATE PDF
   ============================= */
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream(
    "laporan_assessment_mahasiswa.pdf",
    ["Attachment" => true]
);

exit;
