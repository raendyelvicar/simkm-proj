<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'config/db.php';

if (file_exists('config/send_email.php')) {
    require 'config/send_email.php';
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama     = $mysqli->real_escape_string($_POST['nama']);
    $username = $mysqli->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $npm             = $mysqli->real_escape_string($_POST['npm']);
    $email           = $mysqli->real_escape_string($_POST['email']);
    $jenis_kelamin   = $mysqli->real_escape_string($_POST['jenis_kelamin']);
    $fakultas        = $mysqli->real_escape_string($_POST['fakultas']);
    $jurusan         = $mysqli->real_escape_string($_POST['jurusan']);
    $no_hp           = $mysqli->real_escape_string($_POST['no_hp']);
    $role = 'mahasiswa';

if (
    empty($nama) ||
    empty($npm) ||
    empty($username) ||
    empty($email) ||
    empty($password) ||
    empty($jenis_kelamin) ||
    empty($fakultas) ||
    empty($jurusan) ||
    empty($no_hp)
    ) {
        $errors[] = "Semua field wajib diisi.";
    } else {

        $resUsername = $mysqli->query(
"SELECT id FROM users WHERE username='$username'"
);

$resEmail = $mysqli->query(
"SELECT id FROM users WHERE email='$email'"
);

$resNpm = $mysqli->query(
"SELECT id FROM users WHERE npm='$npm'"
);

if($resUsername && $resUsername->num_rows > 0){
    $errors[]="Username sudah terdaftar.";
}

if($resEmail && $resEmail->num_rows > 0){
    $errors[]="Email sudah digunakan.";
}

if($resNpm && $resNpm->num_rows > 0){
    $errors[]="NPM sudah terdaftar.";
}

if(empty($errors)){

    $hash=password_hash($password,PASSWORD_DEFAULT);

    // INSERT
            if (!$mysqli->query("
                INSERT INTO users(nama,npm,username,email,password,jenis_kelamin,fakultas,jurusan,no_hp,role,status)
                VALUES('$nama','$npm','$username','$email','$hash','$jenis_kelamin','$fakultas','$jurusan','$no_hp','$role','$status')
            ")) {
                die("Error DB: " . $mysqli->error);
            }

            // EMAIL
            $adminEmail = "addoafrilioputera@gmail.com";

            $subject = "Pendaftaran Mahasiswa Baru";
            $message = "Ada pengguna baru mendaftar:\n\n"
                     . "Nama: $nama\n"
                     . "NPM: $npm\n"
                     . "Email: $email\n"
                     . "Jenis Kelamin: $jenis_kelamin\n"
                     . "Fakultas: $fakultas\n"
                     . "Jurusan: $jurusan\n"
                     . "No HP: $no_hp\n"
                     . "Username: $username\n"
                     . "Role: Mahasiswa\n";

            if (function_exists('kirimEmail')) {
                try {
                    kirimEmail($adminEmail, $subject, $message);
                } catch (Exception $e) {
                    echo "Email Error: " . $e->getMessage();
                }
            }

            $_SESSION['success_register'] = 'Registrasi berhasil.
                                             Akun Anda sedang menunggu persetujuan Administrator.

                                             Silakan menunggu hingga akun diaktifkan.';
            header('Location: login.php');
            exit;
        }
    }
}
?>

<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Register - SIMKM</title>

<!-- Bootstrap 5 CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg, #0ea5a4, #065f46);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Segoe UI', sans-serif;
}

.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

/* ================= BONUS ANIMASI ================= */
.card {
    animation: fadeIn 0.9s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
/* ================= END BONUS ================= */

.btn-primary {
    background-color: #0ea5a4;
    border: none;
}

.btn-primary:hover {
    background-color: #0c8f8e;
}

.title {
    font-weight: bold;
    color: #065f46;
}
</style>
</head>

<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            <div class="card p-4">
                <h3 class="text-center title mb-3">Register</h3>
                <p class="text-center text-muted small">Buat akun untuk memulai</p>

                <!-- ERROR -->
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach($errors as $e): ?>
                            <div><?= $e ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- FORM -->
                <form method="post">

                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">NPM</label>
                        <input type="text" name="npm" class="form-control" placeholder="Masukkan npm" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="text" name="email" class="form-control" placeholder="Masukkan email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    </div>

                    <div class="mb-3">
                          <label class="form-label">Jenis Kelamin</label>
                          <select name="jenis_kelamin" class="form-select" required>
                          <option value="">Pilih</option>
                          <option>Laki-laki</option>
                          <option>Perempuan</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">No HP</label>
                        <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fakultas</label>
                        <select name="fakultas" class="form-select" required>
                            <option value="">Pilih Fakultas</option>
                            <option>Fakultas Teknologi Informasi</option>
                            <option>Fakultas Keguruan dan Ilmu Pendidikan</option>
                            <option>Fakultas Hukum</option>
                            <option>Fakultas Studi Islam</option>
                            <option>Fakultas Farmasi</option>
                            <option>Fakultas Ilmu Sosial Ilmu Politik</option>
                            <option>Fakultas Ekonomi</option>
                            <option>Fakultas Pertanian</option>
                            <option>Fakultas Kesehatan Masyarakat</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jurusan</label>
                        <select name="jurusan" class="form-select" required>
                            <option value="">Pilih Jurusan</option>
                            <option>Teknik Informatika</option>
                            <option>Sistem Informasi</option>
                            <option>Pendidikan Bahasa Inggris</option>
                            <option>Bimbingan dan Konseling</option>
                            <option>Pendidikan Kimia</option>
                            <option>Pendidikan Olah Raga</option>
                            <option>Ilmu Hukum</option>
                            <option>Hukum Ekonomi Syari’ah</option>
                            <option>Ekonomi Syari’ah</option>
                            <option>Pendidikan Guru Madrasah Ibtidaiyah</option>
                            <option>Farmasi</option>
                            <option>Ilmu Komunikasi</option>
                            <option>Ilmu Administrasi Publik</option>
                            <option>Manajemen</option>
                            <option>Peternakan</option>
                            <option>Agribisnis</option>
                            <option>Kesehatan Masyarakat</option>
                        </select>
                    </div>

                    <button type="submit" id="btnSubmit" class="btn btn-primary w-100">
                    <span id="btnText">Daftar</span>
                    <span id="btnLoading" class="spinner-border spinner-border-sm ms-2 d-none"></span>
                    </button>
                </form>

                <hr>

                <div class="text-center">
                    <small>
                        Sudah punya akun? 
                        <a href="login.php">Login</a>
                    </small>
                </div>

            </div>

        </div>
    </div>
</div>

<!-- TOAST SUCCESS -->
<div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
  <div id="toastSuccess" class="toast text-bg-success border-0">
    <div class="d-flex">
      <div class="toast-body">
        <?= $_SESSION['success_register'] ?? '' ?>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<!-- TOAST ERROR -->
<?php if(!empty($errors)): ?>
<div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
  <div id="toastError" class="toast text-bg-danger border-0">
    <div class="d-flex">
      <div class="toast-body">
        <?php foreach($errors as $e): ?>
          <div><?= $e ?></div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
window.onload = function() {

    <?php if(isset($_SESSION['success_register'])): ?>
        var toastEl = document.getElementById('toastSuccess');
        if (toastEl) {
            var toast = new bootstrap.Toast(toastEl);
            toast.show();
        }

        // AUTO REDIRECT
        setTimeout(function(){
            window.location.href = "login.php";
        }, 3500);
    <?php endif; ?>

    <?php if(!empty($errors)): ?>
        var toastError = document.getElementById('toastError');
        if (toastError) {
            var toast = new bootstrap.Toast(toastError);
            toast.show();
        }
    <?php endif; ?>

// ================= LOADING BUTTON =================
var form = document.querySelector("form");
var btn = document.getElementById("btnSubmit");
var text = document.getElementById("btnText");
var loading = document.getElementById("btnLoading");

if (form) {
    form.addEventListener("submit", function() {

        // disable tombol
        btn.disabled = true;

        // ubah text
        text.innerHTML = "Memproses...";

        // tampilkan spinner
        loading.classList.remove("d-none");
    });
}

};
</script>

<?php unset($_SESSION['success_register']); ?>

</body>
</html>