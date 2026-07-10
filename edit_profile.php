<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config/db.php';

$user_id = $_SESSION['user_id'];

/* ================= AMBIL DATA USER ================= */
$stmt = $mysqli->prepare("
    SELECT *
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("Data user tidak ditemukan.");
}

/* ================= ROLE USER ================= */
$userRole = $user['role'] ?? '';

/* ================= UPDATE PROFILE ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama           = trim($_POST['nama']);
    $username       = trim($_POST['username']);
    $npm            = trim($_POST['npm']);
    $jenis_kelamin  = trim($_POST['jenis_kelamin']);
    $fakultas       = trim($_POST['fakultas']);
    $jurusan        = trim($_POST['jurusan']);
    $email          = trim($_POST['email']);
    $no_hp          = trim($_POST['no_hp']);
    $password       = trim($_POST['password']);

    // ================= FOTO PROFILE =================
$profile_image = $user['profile_image'] ?? '';

// HANYA ADMIN YANG BOLEH UPLOAD FOTO
if(
    $userRole == 'admin' &&
    isset($_FILES['profile_image']) &&
    $_FILES['profile_image']['error'] == 0
){

        $folder = "uploads/profile/";

        if(!is_dir($folder)){
            mkdir($folder, 0777, true);
        }

        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);

        $namaFile = time() . "_" . rand(100,999) . "." . $ext;

        move_uploaded_file(
            $_FILES['profile_image']['tmp_name'],
            $folder . $namaFile
        );

        $profile_image = $namaFile;
    }

    // ================= UPDATE TANPA PASSWORD =================
    if(empty($password)){

        $update = $mysqli->prepare("
            UPDATE users SET

                nama = ?,
                username = ?,
                npm = ?,
                jenis_kelamin = ?,
                fakultas = ?,
                jurusan = ?,
                email = ?,
                no_hp = ?,
                profile_image = ?

            WHERE id = ?
        ");

        $update->bind_param(
            "sssssssssi",
            $nama,
            $username,
            $npm,
            $jenis_kelamin,
            $fakultas,
            $jurusan,
            $email,
            $no_hp,
            $profile_image,
            $user_id
        );

    } else {

        // ================= HASH PASSWORD =================
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $update = $mysqli->prepare("
            UPDATE users SET

                nama = ?,
                username = ?,
                npm = ?,
                jenis_kelamin = ?,
                fakultas = ?,
                jurusan = ?,
                email = ?,
                no_hp = ?,
                password = ?,
                profile_image = ?

            WHERE id = ?
        ");

        $update->bind_param(
            "ssssssssssi",
            $nama,
            $username,
            $npm,
            $jenis_kelamin,
            $fakultas,
            $jurusan,
            $email,
            $no_hp,
            $hash,
            $profile_image,
            $user_id
        );
    }

    if($update->execute()){

        $_SESSION['username'] = $username;

        header("Location: profile.php?success=1");
        exit();

    } else {

        $error = "Gagal memperbarui profil.";

    }
}
?>

<?php include __DIR__ . '/dashboard_bootstrap/layout/header.php'; ?>

<style>

.profile-card{
    border:none;
    border-radius:20px;
}

.empty-profile-space{
    height:20px;
}

.profile-photo{
    width:130px;
    height:130px;
    object-fit:cover;
    border-radius:50%;
    border:5px solid #fff;
    box-shadow:0 5px 20px rgba(0,0,0,0.15);
}

.form-control,
.form-select{
    border-radius:12px;
    min-height:45px;
}

.btn{
    border-radius:12px;
}

</style>

<div class="d-flex">

<?php include __DIR__ . '/dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100">

<div class="container-fluid">

<h3 class="mb-4">
    👤 Edit Profil Saya
</h3>

<?php if(isset($_GET['success'])): ?>

<div class="alert alert-success">
    Profil berhasil diperbarui.
</div>

<?php endif; ?>

<?php if(isset($error)): ?>

<div class="alert alert-danger">
    <?= $error ?>
</div>

<?php endif; ?>

<div class="card shadow-sm profile-card">

<div class="card-body p-4">

<form method="POST" enctype="multipart/form-data">

<div class="text-center mb-4">

<?php if($userRole == 'admin'): ?>

    <?php
    $foto = !empty($user['profile_image'])
        ? 'uploads/profile/' . $user['profile_image']
        : 'uploads/profile/default.png';
    ?>

    <img src="<?= $foto ?>"
         class="profile-photo mb-3">

    <br>

    <label class="btn btn-info text-white btn-sm">

        📷 Ganti Foto

        <input type="file"
               name="profile_image"
               hidden>

    </label>

<?php else: ?>

    <!-- KOSONG UNTUK MAHASISWA & KONSELOR -->
    <div class="empty-profile-space"></div>

<?php endif; ?>

</div>

<div class="row">

<!-- ================= KIRI ================= -->
<div class="col-md-6">

<div class="mb-3">
<label>Nama Lengkap</label>
<input type="text"
       name="nama"
       class="form-control"
       value="<?= htmlspecialchars($user['nama'] ?? '') ?>"
       required>
</div>

<div class="mb-3">
<label>Username</label>
<input type="text"
       name="username"
       class="form-control"
       value="<?= htmlspecialchars($user['username'] ?? '') ?>"
       required>
</div>

<div class="mb-3">
<label>NPM</label>
<input type="text"
       name="npm"
       class="form-control"
       value="<?= htmlspecialchars($user['npm'] ?? '') ?>">
</div>

<div class="mb-3">
<label>Jenis Kelamin</label>

<select name="jenis_kelamin"
        class="form-select">

<option value="">-- Pilih --</option>

<option value="Laki-laki"
<?= ($user['jenis_kelamin'] ?? '') == 'Laki-laki' ? 'selected' : '' ?>>
Laki-laki
</option>

<option value="Perempuan"
<?= ($user['jenis_kelamin'] ?? '') == 'Perempuan' ? 'selected' : '' ?>>
Perempuan
</option>

</select>

</div>

</div>

<!-- ================= KANAN ================= -->
<div class="col-md-6">

<div class="mb-3">
<label>Fakultas</label>
<input type="text"
       name="fakultas"
       class="form-control"
       value="<?= htmlspecialchars($user['fakultas'] ?? '') ?>">
</div>

<div class="mb-3">
<label>Jurusan</label>
<input type="text"
       name="jurusan"
       class="form-control"
       value="<?= htmlspecialchars($user['jurusan'] ?? '') ?>">
</div>

<div class="mb-3">
<label>Email</label>
<input type="email"
       name="email"
       class="form-control"
       value="<?= htmlspecialchars($user['email'] ?? '') ?>">
</div>

<div class="mb-3">
<label>No HP</label>
<input type="text"
       name="no_hp"
       class="form-control"
       value="<?= htmlspecialchars($user['no_hp'] ?? '') ?>">
</div>

</div>

</div>

<hr>

<div class="row">

<div class="col-md-6">

<div class="mb-3">
<label>Password Baru</label>

<input type="password"
       name="password"
       class="form-control"
       placeholder="Kosongkan jika tidak diganti">

<small class="text-muted">
Password lama tidak akan berubah jika kosong
</small>

</div>

</div>

</div>

<div class="d-flex gap-2">

<button type="submit"
        class="btn btn-primary">

    💾 Simpan Perubahan

</button>

<a href="profile.php"
   class="btn btn-secondary">

    ⬅ Kembali

</a>

</div>

</form>

</div>
</div>

</div>
</div>
</div>

<script>

const profileInput = document.querySelector('input[name="profile_image"]');

if(profileInput){

    profileInput.addEventListener('change', function(e){

        const reader = new FileReader();

        reader.onload = function(){

            const photo = document.querySelector('.profile-photo');

            if(photo){
                photo.src = reader.result;
            }

        }

        reader.readAsDataURL(e.target.files[0]);

    });

}

</script>

<?php include __DIR__ . '/dashboard_bootstrap/layout/footer.php'; ?>