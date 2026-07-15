<?php
ini_set('session.cookie_path', '/');
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'konselor'])) {
    header('Location: ../login.php');
    exit;
}

require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;

$role = $_SESSION['role'];

/* ===========================
   AUTO KESIMPULAN (AI RULE)
=========================== */
function generateKesimpulan($skor){
    if($skor >= 75){
        return "Kondisi mental berat, perlu penanganan intensif dan rujukan profesional.";
    } elseif($skor >= 50){
        return "Kondisi mental sedang, disarankan konseling rutin dan monitoring.";
    } else {
        return "Kondisi mental ringan, cukup pemantauan dan self-care.";
    }
}

if(($role == 'konselor' || $role == 'admin')){
    
    if(isset($_GET['id']) && !empty($_GET['id'])){
        // tampilkan data 1 mahasiswa
        if(isset($_GET['id']) && !empty($_GET['id'])){
    // berdasarkan ID assessment (PENTING!)
    $filterUser = "WHERE a.id = " . (int)$_GET['id'];
} else {
    // semua data
    $filterUser = "";
}
    } else {
        // tampilkan semua mahasiswa
        $filterUser = "";
    }

} else {
    // mahasiswa login sendiri
    $filterUser = "WHERE user_id = " . (int)$_SESSION['user_id'];
}

/* ===========================
   MODE EXPORT PDF
   =========================== */
if (isset($_GET['export']) && $_GET['export'] == 'pdf') {

    // DEBUG (taruh DI LUAR query kalau perlu)
echo "<pre>QUERY DEBUG: SELECT a.total_skor, a.kesimpulan, a.created_at, u.username FROM assessment_results a JOIN users u ON a.user_id = u.id $filterUser ORDER BY a.created_at DESC</pre>";

$q = $mysqli->query("
    SELECT a.total_skor, a.kesimpulan,a.result_summary, a.saran_tindakan, a.created_at, u.username
    FROM assessment_results a
    JOIN users u ON a.user_id = u.id
    $filterUser
    ORDER BY a.created_at DESC
");

    if (!$q || $q->num_rows === 0) {
        die("Data tidak ditemukan");
    }

    $html = "
    <style>
    body { font-family: DejaVu Sans, Arial; font-size: 12px; }
    h2 { text-align: center; }
    table { width: 100%; border-collapse: collapse; margin-top: 14px; }
    th, td { border: 1px solid #333; padding: 8px; }
    th { background: #f0f0f0; }
    </style>

    <h2>LAPORAN HASIL SELF-ASSESSMENT</h2>
    <p><strong>Nama:</strong> ".htmlspecialchars($_SESSION['username'])."</p>
    <p><strong>Tanggal Cetak:</strong> ".date('d-m-Y H:i')."</p>

    <table>
    <tr>
      <th>No</th>
      <th>Nama</th>
      <th>Skor</th>
      <th>Ringkasan</th>
      <th>Tanggal</th>
    </tr>
    ";

    $no = 1;
    while ($row = $q->fetch_assoc()) {
        $html .= "
    <tr>
        <td>".$no."</td>
        <td>".htmlspecialchars($row['username'])."</td>
        <td>".$row['total_skor']."</td>
        <td>".htmlspecialchars($row['kesimpulan'])."</td>
        <td>".date('d-m-Y H:i', strtotime($row['created_at']))."</td>
    </tr>
    ";
        $no++;
    }

    $html .= "</table>";

    $pdf = new Dompdf();
    $pdf->loadHtml($html);
    $pdf->setPaper('A4', 'portrait');
    $pdf->render();
    $pdf->stream(
        "hasil_assessment_" . $_SESSION['username'] . ".pdf",
        ["Attachment" => true]
    );
    exit;
}

/* ===========================
   MODE TAMPILAN WEB
   =========================== */
$q = $mysqli->query("
    SELECT a.total_skor, a.kesimpulan, a.result_summary, a.saran_tindakan, a.created_at
    FROM assessment_results a
    $filterUser
    ORDER BY a.created_at DESC
");

?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100" style="background:#f8f9fa; min-height:100vh;">

    <h4 class="mb-3">Hasil Self-Assessment</h4>

    <div class="card shadow-sm p-3">

        <!-- =========================== 
     TAMPILAN CARD (UPGRADE)
=========================== -->

<h5 class="mb-3">Isi Self-Assessment Mahasiswa</h5>

<?php

/* ===========================
   SIMPAN CATATAN KONSELOR
=========================== */
if(isset($_POST['save_catatan'])){
    $id_assessment = (int)$_POST['id_assessment'];
    $catatan = $mysqli->real_escape_string($_POST['catatan']);

    $mysqli->query("
        UPDATE assessment_results 
        SET saran_tindakan = '$catatan'
        WHERE id = $id_assessment
    ");

    echo "<div class='alert alert-success'>Catatan berhasil disimpan</div>";
}

// ambil ulang data khusus untuk card (BIAR TABEL TIDAK RUSAK)
$qCard = $mysqli->query("
    SELECT a.id, a.total_skor, a.kesimpulan, a.result_summary, a.saran_tindakan, a.created_at, u.username
    FROM assessment_results a
    JOIN users u ON a.user_id = u.id
    $filterUser
    ORDER BY a.created_at DESC
");

/* ===========================
   AUTO UPDATE KESIMPULAN
=========================== */
$auto = $mysqli->query("
    SELECT id, total_skor, kesimpulan 
    FROM assessment_results
    WHERE kesimpulan IS NULL OR kesimpulan = ''
");

if($auto && $auto->num_rows > 0){
    while($a = $auto->fetch_assoc()){
        $isi = generateKesimpulan($a['total_skor']);
        $mysqli->query("
            UPDATE assessment_results 
            SET kesimpulan = '". $mysqli->real_escape_string($isi) ."'
            WHERE id = ".$a['id']."
        ");
    }
}

if($qCard && $qCard->num_rows > 0):
    while($d = $qCard->fetch_assoc()):
        
        // LOGIKA STATUS
        if($d['total_skor'] >= 75){
            $status = "Berat";
            $color = "danger";
        } elseif($d['total_skor'] >= 50){
            $status = "Sedang";
            $color = "warning";
        } else {
            $status = "Ringan";
            $color = "success";
        }
?>

<div class="card mb-3 shadow-sm">
    <div class="card-body">

        <h5 class="mb-1"><?= htmlspecialchars($d['username']) ?></h5>

        <p class="mb-1">
            Status: <span class="text-<?= $color ?>"><b><?= $status ?></b></span>
        </p>

        <p class="mb-1">
            Ringkasan: <?= htmlspecialchars($d['result_summary'] ?? '-') ?>
        </p>

        <small class="text-muted d-block mb-1">
            Tanggal Assessment: <?= date('d F Y', strtotime($d['created_at'])) ?>
        </small>

        <p class="mb-2">
    Catatan Konselor:
</p>

<form method="POST" class="mb-2">
    <input type="hidden" name="id_assessment" value="<?= $d['id'] ?? '' ?>">

    <textarea name="catatan" class="form-control mb-2" rows="2"
        placeholder="Tulis catatan konselor..."><?= htmlspecialchars($d['saran_tindakan'] ?? '') ?></textarea>

    <button type="submit" name="save_catatan" class="btn btn-sm btn-primary">
        💾 Simpan Catatan
    </button>
</form>

<small class="text-muted">
    <?= htmlspecialchars(
        $d['saran_tindakan'] 
        ?? $d['kesimpulan'] 
        ?? 'Belum ada catatan'
    ) ?>
</small> 
        </p>

        <div class="mt-3">

    <a href="export_dompdf.php?id=<?= $d['id'] ?>"
       class="btn btn-danger btn-sm">

       ⬇ Export PDF

    </a>

</div>

</div>
</div>

<?php
    endwhile;
else:
?>

<div class="alert alert-warning">
    Tidak ada data assessment
</div>

<?php endif; ?>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Skor</th>
                    <th>Ringkasan</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>

<?php 
$no = 1;
if($q && $q->num_rows > 0):
    while ($row = $q->fetch_assoc()):
?>

<tr>
    <td><?= $no++ ?></td>
    <td><?= $row['total_skor'] ?></td>
    <td><?= htmlspecialchars($row['result_summary'] ?? '-') ?></td>
    <td><?= date('d-m-Y H:i', strtotime($row['created_at'])) ?></td>
</tr>

<?php 
    endwhile;
else:
?>

<tr>
    <td colspan="4" class="text-center text-muted">
        Data tidak ditemukan
    </td>
</tr>

<?php endif; ?>

</tbody>
        </table>


<div class="mt-3">

    <a href="../assessment/history.php"
       class="btn btn-secondary">

       ⬅ Kembali

    </a>

</div>

</div>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>