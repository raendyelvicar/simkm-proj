<?php
ini_set('session.cookie_path', '/');
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'konselor'])) {
    header('Location: ../login.php');
    exit;
}

require '../../config/db.php';
$articles = $mysqli->query("SELECT * FROM articles ORDER BY created_at DESC");
?>

<?php include __DIR__ . '/../../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100">

<h4 class="mb-3">📝 Kelola Artikel</h4>

<a href="add.php" class="btn btn-success mb-3">➕ Tambah Artikel</a>

<div class="card shadow-sm p-3">
<table class="table table-bordered">

<tr>
    <th>Judul</th>
    <th>Tanggal</th>
    <th>Aksi</th>
</tr>

<?php while($a = $articles->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($a['title']) ?></td>
    <td><?= $a['created_at'] ?></td>
    <td>
        <a class="btn btn-warning btn-sm" href="edit.php?id=<?= $a['id'] ?>">Edit</a>
        <a class="btn btn-danger btn-sm" href="delete.php?id=<?= $a['id'] ?>">Hapus</a>
    </td>
</tr>
<?php endwhile; ?>

</table>
</div>

<br>
<a href="../../dashboard_bootstrap/dashboard_bootstrap.php" class="btn btn-secondary">⬅ Kembali</a>

</div>
</div>

<?php include __DIR__ . '/../../dashboard_bootstrap/layout/footer.php'; ?>