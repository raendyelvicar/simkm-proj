<?php
ini_set('session.cookie_path', '/');
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'konselor','mahasiswa'])) {
    header('Location: ../login.php');
    exit;
}

require __DIR__ . '/../config/db.php';

// ✅ FIX: definisikan user id
$uid = $_SESSION['user_id'];

// ================= QUERY =================
if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'konselor'){
    $res = $mysqli->query("
        SELECT ar.*, u.username 
        FROM assessment_results ar
        JOIN users u ON ar.user_id = u.id
        ORDER BY ar.created_at DESC
    ");
}else{
    $res = $mysqli->query("
        SELECT ar.*, u.username 
        FROM assessment_results ar
        JOIN users u ON ar.user_id = u.id
        WHERE ar.user_id = $uid
        ORDER BY ar.created_at DESC
    ");
}

if(!$res){
    die("Query Error: " . $mysqli->error);
}
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100" style="background:#f8f9fa; min-height:100vh;">

<a href="assessment_overview.php" class="btn btn-success mb-3">Grafik Assessment</a>

    <div class="card shadow-sm p-4">

        <h4 class="mb-3">Riwayat Self-Assessment</h4>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Nama</th>
                    <th>Tanggal</th>
                    <th>Skor</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php while($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td><?= $row['total_skor'] ?></td>
                <td>
                    <a href="hasil.php?id=<?= $row['id'] ?>" class="btn btn-info">
                       Lihat
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <a href="../dashboard_bootstrap/dashboard_bootstrap.php" class="btn btn-secondary mt-3">
            ⬅ Kembali
        </a>

    </div>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>