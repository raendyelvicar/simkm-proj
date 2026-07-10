<?php
session_start();
require '../config/db.php';

/* =========================
   PROTEKSI AKSES
   ========================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* =========================
   AMBIL DATA MAHASISWA
   ========================= */
if (!isset($_GET['id'])) {
    header("Location: manage_users.php");
    exit;
}

$id = (int) $_GET['id'];

$q = $mysqli->query("SELECT * FROM users WHERE id=$id AND role='mahasiswa'");
if (!$q || $q->num_rows == 0) {
    echo "<script>alert('Data mahasiswa tidak ditemukan');window.location='manage_users.php';</script>";
    exit;
}

$data = $q->fetch_assoc();

/* =========================
   PROSES UPDATE
   ========================= */
if (isset($_POST['update'])) {
    $nama     = htmlspecialchars($_POST['nama']);
    $username = htmlspecialchars($_POST['username']);
    $email    = htmlspecialchars($_POST['email']);

    $update = $mysqli->query("
        UPDATE users SET
            nama='$nama',
            username='$username',
            email='$email'
        WHERE id=$id
    ");

    if ($update) {
        echo "<script>alert('Data mahasiswa berhasil diperbarui');window.location='manage_users.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data');</script>";
    }
}

/* =========================
   PROSES GANTI PASSWORD
   ========================= */
if (isset($_POST['ganti_password'])) {

    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];

    // ambil password lama dari DB
    $cek = $mysqli->query("SELECT password FROM users WHERE id=$id");
    $user = $cek->fetch_assoc();

    // verifikasi password lama
    if (!password_verify($password_lama, $user['password'])) {
        echo "<script>alert('Password lama salah');</script>";
    } else {

        // hash password baru
        $hash = password_hash($password_baru, PASSWORD_DEFAULT);

        $updatePass = $mysqli->query("
            UPDATE users SET password='$hash' WHERE id=$id
        ");

        if ($updatePass) {
            echo "<script>alert('Password berhasil diubah');</script>";
        } else {
            echo "<script>alert('Gagal mengubah password');</script>";
        }
    }
}

?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100" style="background:#f8f9fa; min-height:100vh;">

    <div class="card shadow-sm p-4" style="max-width:600px; margin:auto; border-radius:12px;">

        <h4 class="mb-3">✏ Edit Data Mahasiswa</h4>

        <form method="post">

            <label class="form-label mt-2">Nama Lengkap</label>
            <input type="text" name="nama" class="form-control"
                value="<?= htmlspecialchars($data['nama']); ?>" required>

            <label class="form-label mt-3">Username</label>
            <input type="text" name="username" class="form-control"
                value="<?= htmlspecialchars($data['username']); ?>" required>

            <label class="form-label mt-3">Email</label>
            <input type="email" name="email" class="form-control"
                value="<?= htmlspecialchars($data['email']); ?>" required>

            <div class="mt-4 d-flex justify-content-between">
                <a href="manage_users.php" class="btn btn-secondary">← Kembali</a>
                <button type="submit" name="update" class="btn btn-success">
                    Simpan Perubahan
                </button>
            </div>

        </form>

        <hr class="my-4">

<h5 class="mb-3">🔒 Ganti Password</h5>

<form method="post">

    <label class="form-label">Password Lama</label>
    <input type="password" name="password_lama" class="form-control" required>

    <label class="form-label mt-3">Password Baru</label>
    <input type="password" name="password_baru" class="form-control" required>

    <div class="mt-4">
        <button type="submit" name="ganti_password" class="btn btn-success w-100">
            Simpan Perubahan
        </button>
    </div>

</form>

    </div>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>