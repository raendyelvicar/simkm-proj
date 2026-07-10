<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

require '../config/db.php';

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $mysqli->real_escape_string($_POST['nama']);
    $username = $mysqli->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $role = $mysqli->real_escape_string($_POST['role']);

    if (empty($nama) || empty($username) || empty($password)) {
        $errors[] = "Semua field wajib diisi.";
    } else {
        $check = $mysqli->query("SELECT id FROM users WHERE username='$username'");
        if ($check->num_rows > 0) {
            $errors[] = "Username sudah digunakan.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $mysqli->query("INSERT INTO users (nama, username, password, role) 
                            VALUES ('$nama', '$username', '$hash', '$role')");
            $success = "User berhasil ditambahkan!";
        }
    }
}
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100">

<h4 class="mb-3">➕ Tambah User</h4>

<div class="card shadow-sm p-4" style="max-width:600px;">

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <?php foreach($errors as $e) echo "<div>$e</div>"; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="post">

<div class="mb-3">
<label>Nama</label>
<input type="text" name="nama" class="form-control" required>
</div>

<div class="mb-3">
<label>Username</label>
<input type="text" name="username" class="form-control" required>
</div>

<div class="mb-3">
<label>Password</label>
<input type="password" name="password" class="form-control" required>
</div>

<div class="mb-3">
<label>Role</label>
<select name="role" class="form-control" required>
    <option value="mahasiswa">Mahasiswa</option>
    <option value="konselor">Konselor</option>
</select>
</div>

<button type="submit" class="btn btn-primary">Simpan</button>

</form>

<br>
<a href="manage_users.php" class="btn btn-secondary">⬅ Kembali</a>

</div>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>