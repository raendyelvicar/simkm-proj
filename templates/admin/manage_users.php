<?php 
session_start();

// Cek hak akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require __DIR__ . '/../config/db.php';

// ================= CEK KOLOM =================
$hasNama = false;
$check = $mysqli->query("SHOW COLUMNS FROM users LIKE 'nama'");
if ($check && $check->num_rows > 0) {
    $hasNama = true;
}

// ================= AMBIL DATA =================
if ($hasNama) {
    $res = $mysqli->query("SELECT id, nama, username, role, status FROM users ORDER BY id DESC");
} else {
    $res = $mysqli->query("SELECT id, username, role, status FROM users ORDER BY id DESC");
}
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

    <?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

    <div class="content-wrapper p-4 w-100" style="background:#f8f9fa; min-height:100vh;">

        <div class="card shadow-sm p-4">
            <h4>👥 Kelola Pengguna</h4>

            <p>
                <a class="btn btn-primary btn-sm" href="add_user.php">➕ Tambah User</a>
                <a class="btn btn-secondary btn-sm" href="../dashboard_bootstrap/dashboard_bootstrap.php">⬅ Kembali</a>
            </p>

            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <?php if($hasNama): ?>
                        <th>Nama</th>
                        <?php endif; ?>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php while($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>

                    <?php if($hasNama): ?>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                    <?php endif; ?>

                    <td><?= htmlspecialchars($row['username']) ?></td>

                    <td>
                        <span class="badge bg-<?= $row['role']=='admin' ? 'danger' : 'success' ?>">
                            <?= $row['role'] ?>
                        </span>
                    </td>

                    <td align="center">

<?php

if($row['status']=="pending"){

    echo '<span class="badge bg-warning text-dark">Pending</span>';

}elseif($row['status']=="active"){

    echo '<span class="badge bg-success">Active</span>';

}else{

    echo '<span class="badge bg-danger">Rejected</span>';

}

?>

</td>

<td>
    <?php

// tombol Approve hanya muncul jika status Pending
if($row['status']=="pending"){

?>

<a href="approved_user.php?id=<?= $row['id']; ?>"
class="btn btn-success btn-sm"
onclick="return confirm('Setujui akun ini?')">

Approve

</a>

<a href="reject_user.php?id=<?= $row['id']; ?>"
class="btn btn-secondary btn-sm"
onclick="return confirm('Tolak akun ini?')">

Reject

</a>

<?php

}

?>

<?php

if($row['role']=="konselor"){

?>

<a href="edit_konselor.php?id=<?= $row['id']; ?>"
class="btn btn-warning btn-sm">

Edit

</a>

<?php

}else{

?>

<a href="edit_student.php?id=<?= $row['id']; ?>"
class="btn btn-warning btn-sm">

Edit

</a>

<?php

}

?>

<a href="delete_user.php?id=<?= $row['id']; ?>"
onclick="return confirm('Hapus user ini?')"
class="btn btn-danger btn-sm">

Hapus

</a>

</td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

        </div>

    </div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>