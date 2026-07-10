<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ================= VALIDASI INPUT =================
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($username === '' || $password === '') {
        $error = "Username dan password wajib diisi.";
    } else {

        // ================= QUERY =================
        $stmt = $mysqli->prepare(" 
        SELECT
        id,
        username,
        password,
        role,
        status 
        FROM users WHERE username=?
        LIMIT 1");

        if (!$stmt) {
            die("Prepare failed: " . $mysqli->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $stmt->store_result();
        $stmt->bind_result(
        $id,
        $db_username,
        $db_password,
        $role,
        $status
        );

        if ($stmt->num_rows > 0 && $stmt->fetch()) {

            // ================= DATA USER =================
            $user = [
                'id' => $id,
                'username' => $db_username,
                'password' => $db_password,
                'role' => $role,
                'status' => $status
            ];

            // ================= VALIDASI PASSWORD =================
            $validPassword = false;

            if (!empty($user['password']) && password_verify($password, $user['password'])) {
                $validPassword = true;
            }

            if ($password === $user['password']) {
                $validPassword = true;
            }

            if ($validPassword) {

    // ================= CEK STATUS =================
    if ($user['status'] == 'pending') {

        $error = "Akun Anda masih menunggu persetujuan Administrator.";

    } elseif ($user['status'] == 'rejected') {

        $error = "Pendaftaran Anda ditolak Administrator.";

    } else {

        // ================= SET SESSION =================
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

                // ================= ✅ LOG LOGIN (PROMPT NO. 3) =================
                $ip = $_SERVER['REMOTE_ADDR'];
                $user_id = $user['id'];

                $stmtLog = $mysqli->prepare("INSERT INTO log_login (user_id, ip_address) VALUES (?, ?)");
                $stmtLog->bind_param("is", $user_id, $ip);
                $stmtLog->execute();
                $stmtLog->close();

                // ================= EMAIL =================
                if ($user['role'] === 'mahasiswa' || $user['role'] === 'konselor') {

                    if (file_exists('config/send_email.php')) {
                        require_once 'config/send_email.php';

                        if (function_exists('kirimEmail')) {
                            try {
                                kirimEmail(
                                    "addoafrilioputera@gmail.com",
                                    "Login " . ucfirst($user['role']),
                                    "User login:\nUsername: {$user['username']}\nRole: {$user['role']}\nWaktu: " . date("Y-m-d H:i:s")
                                );
                            } catch (Exception $e) {
                                // silent
                            }
                        }
                    }
                }

                // ================= REDIRECT =================
                if ($user['role'] === 'admin') {
                    header('Location: dashboard_bootstrap/dashboard_bootstrap.php');
                } elseif ($user['role'] === 'mahasiswa') {
                    header('Location: dashboard_bootstrap/dashboard_bootstrap.php');
                } elseif ($user['role'] === 'konselor') {
                    header('Location: dashboard_bootstrap/dashboard_bootstrap.php');
                } else {
                    header('Location: redirect_dashboard.php');
                }

                exit;

    }

} else {

    $error = "Password salah.";
}

        } else {
            $error = "Username tidak ditemukan.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login - SIMKM</title>
  <style>
    :root{--bg:#f3f4f6;--card:#fff;--brand:#5b21b6}
    *{box-sizing:border-box}
    body{margin:0;font-family:Inter,Segoe UI,Arial;background:var(--bg);display:flex;align-items:center;justify-content:center;height:100vh}
    .card{background:var(--card);width:380px;padding:26px;border-radius:12px;box-shadow:0 8px 30px rgba(20,20,40,.08)}
    h1{margin:0 0 6px;font-size:20px;color:#111}
    p.lead{margin:0 0 18px;color:#555;font-size:13px}
    label{display:block;margin-top:10px;font-size:13px;color:#333}
    input[type=text],input[type=password]{width:100%;padding:10px;margin-top:6px;border:1px solid #d1d5db;border-radius:8px}
    button{margin-top:18px;width:100%;padding:10px;border:0;border-radius:8px;background:var(--brand);color:#fff;font-weight:600;cursor:pointer}
    .error{background:#fee2e2;color:#991b1b;padding:8px;border-radius:8px;margin-bottom:12px}
    .footer-note{text-align:center;font-size:12px;color:#777;margin-top:14px}
    .hint{font-size:12px;color:#666;margin-top:10px}
    .back-home {
    display: block;
    margin-top: 12px;
    text-align: center;
    font-size: 14px;
}

.back-home a {
    color: #0ea5a4;
    text-decoration: none;
    font-weight: 600;
}

.back-home a:hover {
    text-decoration: underline;
}
  </style>
</head>
<body>
  <form method="post" class="card" autocomplete="off">
    <h1>Sistem Informasi Manajemen Kesehatan Mental</h1>
    <p class="lead">Masuk untuk mengakses fitur</p>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <label for="username">Username</label>
    <input id="username" name="username" type="text" required>

    <label for="password">Password</label>
    <input id="password" name="password" type="password" required>

    <button type="submit" id="btnLogin">Login</button>

    <div class="hint">
      Belum punya akun? <a href="register.php">Daftar di sini</a>
    </div>
    <div class="back-home">
    <a href="index.php">← Kembali ke Beranda</a>
    </div>

    <div class="footer-note">&copy; <?= date('Y') ?> SIMKM</div>
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