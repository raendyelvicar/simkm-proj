<?php
ini_set('session.cookie_path', '/');
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'konselor', 'mahasiswa'])) {
    header('Location: ../login.php');
    exit;
}

require __DIR__ . '../../config/db.php';

// ================= SIMPAN / UPDATE =================
if($_SERVER['REQUEST_METHOD']==='POST'){
    $key = $_POST['setting_key'];
    $value = $_POST['setting_value'];

    $cek = $mysqli->prepare("SELECT id FROM system_settings WHERE setting_key=?");
    $cek->bind_param("s",$key);
    $cek->execute();
    $cek->store_result();

    if($cek->num_rows > 0){
        $stmt = $mysqli->prepare("UPDATE system_settings SET setting_value=? WHERE setting_key=?");
        $stmt->bind_param("ss",$value,$key);
        $stmt->execute();
    } else {
        $stmt = $mysqli->prepare("INSERT INTO system_settings (setting_key,setting_value) VALUES(?,?)");
        $stmt->bind_param("ss",$key,$value);
        $stmt->execute();
    }
}

// ================= DELETE =================
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $mysqli->query("DELETE FROM system_settings WHERE id=$id");
}

// ================= EDIT =================
$editData = null;
if(isset($_GET['edit'])){
    $id = (int)$_GET['edit'];
    $q = $mysqli->query("SELECT * FROM system_settings WHERE id=$id");
    $editData = $q->fetch_assoc();
}

// ================= AMBIL DATA =================
$settings = $mysqli->query("SELECT * FROM system_settings ORDER BY id DESC");
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

    <!-- SIDEBAR -->
    <?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

    <!-- CONTENT -->
    <div class="content-wrapper p-4 w-100" style="background:#f8f9fa;">

        <!-- BREADCRUMB -->
        <nav>
            <small class="text-muted">Home / Pengaturan Sistem</small>
        </nav>

        <!-- CARD -->
        <div class="card shadow-sm p-4 mt-3">

            <h4 class="mb-3">⚙ Pengaturan Sistem</h4>

            <!-- FORM -->
            <form method="POST" class="mb-4">
                <div class="mb-2">
                    <input type="text" name="setting_key" 
                        class="form-control"
                        value="<?= $editData['setting_key'] ?? '' ?>" 
                        placeholder="Nama Pengaturan" required>
                </div>

                <div class="mb-2">
                    <textarea name="setting_value" class="form-control" placeholder="Nilai"><?= $editData['setting_value'] ?? '' ?></textarea>
                </div>

                <button class="btn btn-primary">
                    <?= $editData ? 'Update' : 'Simpan' ?>
                </button>
            </form>

            <!-- TABEL -->
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Key</th>
                        <th>Value</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($s=$settings->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['setting_key']) ?></td>
                        <td><?= htmlspecialchars($s['setting_value']) ?></td>
                        <td>
                            <a href="?edit=<?= $s['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete=<?= $s['id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Hapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- KEMBALI -->
            <a href="../dashboard_bootstrap/dashboard_bootstrap.php" class="btn btn-secondary mt-3">
                Kembali
            </a>

        </div>

    </div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>