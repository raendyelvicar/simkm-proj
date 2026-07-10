<?php
session_start();
require 'config/db.php';
require 'fpdf/fpdf.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];

// Ambil riwayat chat
$q = $mysqli->query("SELECT * FROM chat_logs WHERE user_id=$user_id ORDER BY id ASC");

// Buat PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Judul
$pdf->Cell(0, 10, 'Riwayat Chat Konseling', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);

while ($row = $q->fetch_assoc()) {

    $sender = strtoupper($row['sender']);
    $message = $row['message'];

    // Label pengirim
    if ($row["sender"] == "user") {
        $pdf->SetTextColor(0, 102, 0);  // hijau gelap
    } else {
        $pdf->SetTextColor(0, 0, 150);  // biru gelap
    }

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, "$sender:", 0, 1);

    // Isi pesan
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->MultiCell(0, 7, $message);
    $pdf->Ln(3);
}

$pdf->Output("D", "chat_konseling.pdf");
exit;
?>
