<?php
session_start();

// =========================
// CEK LOGIN ADMIN
// =========================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require __DIR__ . '/../config/db.php';

// =========================
// CEK KOLOM NAMA
// =========================
$hasNama = false;

$check = $mysqli->query("SHOW COLUMNS FROM users LIKE 'nama'");

if ($check && $check->num_rows > 0) {
    $hasNama = true;
}

// =========================
// HAPUS KONSELOR
// =========================
if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    $hapus = $mysqli->prepare("DELETE FROM users WHERE id=? AND role='konselor'");
    $hapus->bind_param("i", $id);

    if ($hapus->execute()) {

        echo "<script>
                alert('Data konselor berhasil dihapus');
                window.location='manage_konselor.php';
              </script>";
        exit;

    } else {

        echo "<script>
                alert('Gagal menghapus data');
              </script>";
    }
}

// =========================
// AMBIL DATA KONSELOR
// =========================
if ($hasNama) {

    $result = $mysqli->query("
        SELECT id, nama, username, role
        FROM users
        WHERE role='konselor'
        ORDER BY id DESC
    ");

} else {

    $result = $mysqli->query("
        SELECT id, username, role
        FROM users
        WHERE role='konselor'
        ORDER BY id DESC
    ");
}
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

    <?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

    <div class="content-wrapper p-4 w-100"
         style="background:#f8f9fa; min-height:100vh;">

        <div class="card shadow-sm p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h4>🧑‍🏫 Manage Konselor</h4>

                <div>

                    <a href="add_user.php"
                       class="btn btn-primary btn-sm">
                        ➕ Tambah Konselor
                    </a>

                    <a href="../dashboard_bootstrap/dashboard_bootstrap.php"
                       class="btn btn-secondary btn-sm">
                        ⬅ Kembali
                    </a>

                </div>

            </div>

            <!-- =========================
                 TABEL DATA
            ========================== -->

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-dark">

                        <tr>

                            <th width="80">ID</th>

                            <?php if($hasNama): ?>
                                <th>Nama</th>
                            <?php endif; ?>

                            <th>Username</th>
                            <th width="150">Role</th>
                            <th width="220">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if($result->num_rows > 0): ?>

                        <?php while($row = $result->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?= $row['id']; ?>
                            </td>

                            <?php if($hasNama): ?>

                            <td>
                                <?= htmlspecialchars($row['nama']); ?>
                            </td>

                            <?php endif; ?>

                            <td>
                                <?= htmlspecialchars($row['username']); ?>
                            </td>

                            <td>

                                <span class="badge bg-success">
                                    <?= $row['role']; ?>
                                </span>

                            </td>

                            <td>

                                <!-- EDIT -->
                                <a href="edit_konselor.php?id=<?= $row['id']; ?>"
                                   class="btn btn-warning btn-sm">

                                    ✏ Edit

                                </a>

                                <!-- HAPUS -->
                                <a href="?hapus=<?= $row['id']; ?>"
                                   onclick="return confirm('Yakin ingin menghapus konselor ini?')"
                                   class="btn btn-danger btn-sm">

                                    🗑 Hapus

                                </a>

                            </td>

                        </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="5" class="text-center">

                                Belum ada data konselor

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>