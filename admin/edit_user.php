<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

require '../config/db.php';

$id = (int) ($_GET['id'] ?? 0);

// Ambil data user
$res = $mysqli->query("SELECT * FROM users WHERE id=$id LIMIT 1");
if ($res->num_rows == 0) { echo "User tidak ditemukan."; exit; }

$user = $res->fetch_assoc();
$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $mysqli->real_escape_string($_POST['nama']);
    $username = $mysqli->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $role = $mysqli->real_escape_string($_POST['role']);

    if (empty($nama) || empty($username)) {
        $errors[] = "Nama dan Username wajib diisi.";
    } else {
        $check = $mysqli->query("SELECT id FROM users WHERE username='$username' AND id!=$id");
        if ($check->num_rows > 0) {
            $errors[] = "Username sudah digunakan oleh pengguna lain.";
        } else {
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $mysqli->query("UPDATE users SET nama='$nama', username='$username', password='$hash', role='$role' WHERE id=$id");
            } else {
                $mysqli->query("UPDATE users SET nama='$nama', username='$username', role='$role' WHERE id=$id");
            }
            $success = "Perubahan berhasil disimpan.";
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Edit User</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.container{ max-width:600px; margin:40px auto; }
input, select, button { width:100%; padding:10px; margin-top:5px; }
button { background:#0ea5a4; color:#fff; border:0; }
</style>
</head>

<body>
<div class="container">
<h2>Edit User</h2>

<?php if ($errors): foreach($errors as $e) echo "<div class='error'>$e</div>"; endif; ?>
<?php if ($success) echo "<div class='success'>$success</div>"; ?>

<form method="post">

<label>Nama</label>
<input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>

<label>Username</label>
<input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

<label>Password Baru (opsional)</label>
<input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">

<label>Role</label>
<select name="role">
    <option value="mahasiswa" <?= $user['role'] == 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
</select>

<br><br>
<button type="submit">Simpan Perubahan</button>

</form>

<br>
<a href="manage_users.php">Kembali</a>

</div>
</body>
</html>
