<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config/db.php';

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];

    // Ambil password lama
    $query = $mysqli->query("SELECT password FROM users WHERE id='$user_id'");
    $data = $query->fetch_assoc();

    // Cek password lama
    if (password_verify($old, $data['password'])) {

        $hashed = password_hash($new, PASSWORD_DEFAULT);

        $mysqli->query("UPDATE users SET password='$hashed' WHERE id='$user_id'");
        $message = "✅ Password berhasil diubah!";
    } else {
        $message = "❌ Password lama salah.";
    }
}
?>

<?php include __DIR__ . '../dashboard_bootstrap//layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '../dashboard_bootstrap//layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100" style="background:#f8f9fa; min-height:100vh;">

    <div class="card shadow-sm p-4" style="max-width:450px; margin:auto;">

        <h4 class="mb-3">🔒 Ganti Password</h4>

        <?php if ($message): ?>
            <div class="alert alert-info text-center">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label class="form-label">Password Lama</label>
            <input type="password" name="old_password" class="form-control" required>

            <label class="form-label mt-3">Password Baru</label>
            <input type="password" name="new_password" class="form-control" required>

            <button type="submit" class="btn btn-success w-100 mt-4">
                Simpan Perubahan
            </button>
        </form>

        <a href="../dashboard_bootstrap/dashboard_bootstrap.php"
           class="btn btn-secondary w-100 mt-3">
           ⬅ Kembali
        </a>

    </div>

</div>
</div>

<?php include __DIR__ . '../dashboard_bootstrap//layout/footer.php'; ?>