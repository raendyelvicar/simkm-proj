<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

require __DIR__ . '/../config/db.php';

$res = $mysqli->query("SELECT id,nama,username,created_at FROM users WHERE role='mahasiswa'");
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100" style="background:#f8f9fa; min-height:100vh;">

    <div class="card shadow-sm p-4">

        <h4>🎓 Data Mahasiswa</h4>

        <p>
            <a href="../dashboard_bootstrap/dashboard_bootstrap.php" class="btn btn-secondary btn-sm">⬅ Kembali</a>
        </p>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Terdaftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php while($r = $res->fetch_assoc()): ?>
            <tr>
                <td><?= $r['id']; ?></td>
                <td><?= htmlspecialchars($r['nama']); ?></td>
                <td><?= htmlspecialchars($r['username']); ?></td>
                <td><?= $r['created_at']; ?></td>
                <td>
                    <a href="detail_student.php?id=<?= $r['id']; ?>" class="btn btn-info btn-sm">Detail</a>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

    </div>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>