<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $mysqli->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $q = $mysqli->query("SELECT * FROM users WHERE username='$username' LIMIT 1");

    if ($q && $q->num_rows === 1) {
        $user = $q->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            // CEK ROLE (hanya konselor)
            if ($user['role'] !== 'konselor') {
                $error = "Akun ini bukan konselor. Gunakan login mahasiswa.";
            } else {

                // SET SESSION
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // ================= EMAIL NOTIFIKASI =================
                if (file_exists('config/send_email.php')) {
                    require_once 'config/send_email.php';

                    if (function_exists('kirimEmail')) {
                        $adminEmail = "addoafrilioputera@gmail.com";

                        $subject = "Login Konselor";
                        $message = "Konselor login:\n\n"
                                 . "Username: " . $user['username'] . "\n"
                                 . "Waktu: " . date("Y-m-d H:i:s");

                        try {
                            kirimEmail($adminEmail, $subject, $message);
                        } catch (Exception $e) {
                            // aman
                        }
                    }
                }

                header('Location: dashboard_bootstrap/dashboard_bootstrap.php');
                exit;
            }

        } else {
            $error = "Password salah.";
        }

    } else {
        $error = "Username tidak ditemukan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login Konselor - SIMKM</title>

<style>
:root{
  --bg:#eef2ff;
  --card:#fff;
  --brand:#1d4ed8;
}
*{box-sizing:border-box}

body{
  margin:0;
  font-family:Segoe UI,Arial;
  background:linear-gradient(135deg,#1e3a8a,#2563eb);
  display:flex;
  align-items:center;
  justify-content:center;
  height:100vh;
}

.card{
  background:#fff;
  width:380px;
  padding:26px;
  border-radius:12px;
  box-shadow:0 8px 30px rgba(0,0,0,.2);
  animation:fadeIn .8s ease;
}

@keyframes fadeIn{
  from{opacity:0;transform:translateY(20px)}
  to{opacity:1;transform:translateY(0)}
}

h1{
  margin:0 0 6px;
  font-size:20px;
}

.lead{
  font-size:13px;
  color:#555;
}

label{
  display:block;
  margin-top:10px;
  font-size:13px;
}

input{
  width:100%;
  padding:10px;
  margin-top:6px;
  border:1px solid #ccc;
  border-radius:8px;
}

button{
  margin-top:18px;
  width:100%;
  padding:10px;
  border:0;
  border-radius:8px;
  background:var(--brand);
  color:#fff;
  font-weight:600;
  cursor:pointer;
}

.error{
  background:#fee2e2;
  color:#991b1b;
  padding:8px;
  border-radius:8px;
  margin-bottom:12px;
}

.hint{
  font-size:12px;
  margin-top:10px;
  text-align:center;
}

.hint a{
  color:#2563eb;
  text-decoration:none;
  font-weight:600;
}

.hint a:hover{
  text-decoration:underline;
}
</style>
</head>

<body>

<form method="post" class="card">
  <h1>Login Konselor</h1>
  <p class="lead">Akses khusus untuk konselor</p>

  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <label>Username</label>
  <input type="text" name="username" required>

  <label>Password</label>
  <input type="password" name="password" required>

  <button type="submit" id="btnLogin">Login Konselor</button>

  <div class="hint">
    Login mahasiswa? <a href="login.php">Klik di sini</a>
  </div>

  <div class="hint">
    <a href="index.php">← Kembali ke Beranda</a>
  </div>

</form>

<script>
document.querySelector("form").addEventListener("submit", function() {
    var btn = document.getElementById("btnLogin");

    if (btn) {
        btn.innerHTML = "⏳ Memproses...";
        btn.disabled = true;
    }
});
</script>

</body>
</html>