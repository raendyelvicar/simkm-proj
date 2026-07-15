<?php
// assessment/export.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require '../config/db.php';

// cek library FPDF
$fpdf_path = __DIR__ . '/../lib/fpdf.php';
if (!file_exists($fpdf_path)) {
    die("Library FPDF tidak ditemukan. Silakan letakkan file fpdf.php di folder lib/. ");
}
require $fpdf_path;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$uid = (int)$_SESSION['user_id'];

if ($id <= 0) {
    die("ID assessment tidak valid.");
}

// Ambil hasil assessment, pastikan milik user
$stmt = $mysqli->prepare("SELECT ar.id, ar.score, ar.category, ar.created_at, u.nama, u.username 
                          FROM assessment_results ar 
                          JOIN users u ON u.id = ar.user_id
                          WHERE ar.id = ? AND ar.user_id = ?");
$stmt->bind_param("ii", $id, $uid);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    die("Hasil assessment tidak ditemukan atau Anda tidak berhak mengunduhnya.");
}
$row = $res->fetch_assoc();

// Buat PDF
$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Self-Assessment - SIMKM',0,1,'C');
$pdf->Ln(6);

$pdf->SetFont('Arial','',12);
$pdf->Cell(40,8,'Nama:',0,0);
$pdf->Cell(0,8,htmlspecialchars($row['nama']),0,1);

$pdf->Cell(40,8,'Username:',0,0);
$pdf->Cell(0,8,htmlspecialchars($row['username']),0,1);

$pdf->Cell(40,8,'Tanggal:',0,0);
$pdf->Cell(0,8,$row['created_at'],0,1);

$pdf->Cell(40,8,'Skor:',0,0);
$pdf->Cell(0,8,$row['score'],0,1);

$pdf->Cell(40,8,'Kategori:',0,0);
$pdf->Cell(0,8,$row['category'],0,1);

$pdf->Ln(8);
$pdf->SetFont('Arial','I',10);
$pdf->MultiCell(0,6,"Catatan: Hasil ini hanya untuk keperluan pribadi. Jika kategori menunjukkan kebutuhan bantuan, harap menghubungi konselor kampus atau layanan kesehatan mental.");

$filename = "assessment_{$row['id']}_{$row['username']}.pdf";

// Output ke browser dan download
$pdf->Output('D', $filename);
exit;
