<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['role'] !== 'konselor') {
    header('Location: ../redirect_dashboard.php');
    exit;
}

require '../config/db.php';
if (!isset($mysqli)) {
    die("Koneksi database tidak tersedia.");
}

// TOTAL MAHASISWA
$queryMahasiswa = mysqli_query($mysqli,
    "SELECT COUNT(*) AS total FROM users WHERE role = 'mahasiswa'"
);
$totalMahasiswa = mysqli_fetch_assoc($queryMahasiswa)['total'];

// MONITORING
$queryMonitoring = mysqli_query($mysqli, "
    SELECT u.id, u.nama, a.total_skor, a.assessment_date
    FROM users u
    JOIN assessment_results a ON u.id = a.user_id
    ORDER BY a.assessment_date DESC
    LIMIT 5
");

$username = $_SESSION['username'];
?>

<div class="content">

<h3>Dashboard Konselor</h3>
<p>Selamat datang, <?= htmlspecialchars($username) ?></p>

<!-- CARD -->
<div class="row mt-4">

    <div class="col-md-4">
        <div class="card shadow-sm p-3">
            <h5>Mahasiswa Dipantau</h5>
            <h3><?= $totalMahasiswa ?></h3>
            <a href="monitoring.php" class="btn btn-primary btn-sm">Monitoring</a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm p-3">
            <h5>Self Assessment</h5>
            <p>Lihat hasil assessment mahasiswa</p>
            <a href="assessment.php" class="btn btn-success btn-sm">Lihat</a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm p-3">
            <h5>Chat Konseling</h5>
            <p>Pesan dari mahasiswa</p>
            <a href="/AplikasiSkripsi/konselor/chat.php" class="btn btn-warning btn-sm">Chat</a>
        </div>
    </div>

</div>

<!-- TABLE -->
<div class="card mt-4 p-3 shadow-sm">
<h5>Monitoring Kesehatan Mental</h5>

<table class="table table-bordered mt-3">
<thead>
<tr>
    <th>Nama</th>
    <th>Tanggal</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>

<?php if ($queryMonitoring && mysqli_num_rows($queryMonitoring) > 0): ?>
<?php while($row = mysqli_fetch_assoc($queryMonitoring)): ?>
<tr>
    <td><?= htmlspecialchars($row['nama']) ?></td>
    <td><?= date('d M Y', strtotime($row['assessment_date'])) ?></td>
    <td>
        <?php
        if ($row['total_skor'] >= 75) {
            echo '<span class="badge bg-danger">Berat</span>';
        } elseif ($row['total_skor'] >= 50) {
            echo '<span class="badge bg-warning">Sedang</span>';
        } else {
            echo '<span class="badge bg-success">Ringan</span>';
        }
        ?>
    </td>
    <td>
        <a href="detail_monitoring.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Detail</a>
    </td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
    <td colspan="4" class="text-center">Belum ada data</td>
</tr>
<?php endif; ?>

</tbody>
</table>

</div>

</div>