<?php
session_start();
require 'config/db.php';
require 'config/send_email.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama     = $mysqli->real_escape_string($_POST['nama']);
    $username = $mysqli->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $role = 'konselor';

    if (empty($nama) || empty($username) || empty($password)) {
        $errors[] = "Semua field wajib diisi.";
    } else {

        $res = $mysqli->query("SELECT id FROM users WHERE username='$username'");
        if ($res && $res->num_rows > 0) {
            $errors[] = "Username sudah terdaftar.";
        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $mysqli->query("
                INSERT INTO users (nama, username, password, role)
                VALUES ('$nama', '$username', '$hash', '$role')
            ");

            // email ke admin
            $adminEmail = "admin@gmail.com";
            $subject = "Pendaftaran Konselor Baru";
            $message = "Konselor baru mendaftar:\n\nNama: $nama\nUsername: $username";
            kirimEmail($adminEmail, $subject, $message);

            $_SESSION['flash'] = 'Registrasi konselor berhasil.';
            header('Location: login.php');
            exit;
        }
    }
}
?>

<!doctype html>
<html>
<head>
<title>Register Konselor</title>
</head>
<body>

<h2>Register Konselor</h2>

<?php 
foreach($errors as $e){
    echo "<p style='color:red'>$e</p>";
}
?>

<form method="post">
<input type="text" name="nama" placeholder="Nama" required><br>
<input type="text" name="username" placeholder="Username" required><br>
<input type="password" name="password" placeholder="Password" required><br>
<button type="submit">Daftar Konselor</button>
</form>

</body>
</html>