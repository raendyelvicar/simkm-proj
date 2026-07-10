<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config/db.php';

$user_id = $_SESSION['user_id'];

// ================= AMBIL DATA USER =================
$stmt = $mysqli->prepare("
    SELECT *
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$userProfile = $result->fetch_assoc();

// ================= VALIDASI =================
if (!$userProfile) {
    die("Data user tidak ditemukan.");
}

// ================= FOTO PROFILE =================
$fotoProfile = $userProfile['profile_image'] ?? 'default.png';

if(empty($fotoProfile)){
    $fotoProfile = 'default.png';
}
?>

<?php include __DIR__ . '/dashboard_bootstrap/layout/header.php'; ?>

<style>

.profile-empty{
    transition:0.3s;
}

.profile-empty:hover{
    transform:scale(1.03);
    background:#e2e8f0 !important;
}

.profile-empty{
    cursor:pointer;
    transition:all .3s ease;
}

.profile-empty:hover{
    border-color:#2563eb;
    color:#2563eb;
}

</style>

<div class="d-flex">

    <!-- SIDEBAR -->
    <?php include __DIR__ . '/dashboard_bootstrap/layout/sidebar.php'; ?>

    <!-- CONTENT -->
    <div class="content-wrapper p-4 w-100"
         style="background:#f8f9fa; min-height:100vh;">

        <!-- TITLE -->
        <h4 class="mb-4">
            👤 Profil Saya
        </h4>

        <!-- CARD PROFILE -->
        <div class="card shadow-sm border-0 p-4"
             style="max-width:700px; border-radius:15px;">

            <!-- FOTO -->
<div class="text-center mb-4">

<?php
$fotoAda = !empty($userProfile['profile_image']) &&
           file_exists(__DIR__ . '/uploads/profile/' . $userProfile['profile_image']);
?>

<?php if($fotoAda): ?>

    <!-- FOTO USER -->
    <img src="/AplikasiSkripsi/uploads/profile/<?= htmlspecialchars($userProfile['profile_image']) ?>"
         width="130"
         height="130"
         class="rounded-circle shadow border"
         style="object-fit:cover;">

<?php else: ?>

    <!-- FOTO KOSONG -->
    <div class="profile-empty" style="
        width:130px;
        height:130px;
        margin:auto;
        border-radius:50%;
        background:#f1f5f9;
        border:3px dashed #cbd5e1;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#94a3b8;
        font-size:14px;
        font-weight:600;
    ">

        Tidak Ada Foto

    </div>

<?php endif; ?>

</div>

            <!-- INFO -->
            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="text-muted">Nama</label>
                    <div class="fw-bold">
                        <?= htmlspecialchars($userProfile['nama'] ?? '-') ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="text-muted">Username</label>
                    <div class="fw-bold">
                        <?= htmlspecialchars($userProfile['username'] ?? '-') ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="text-muted">Role</label>
                    <div>
                        <span class="badge bg-primary">
                            <?= htmlspecialchars($userProfile['role'] ?? '-') ?>
                        </span>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="text-muted">NPM</label>
                    <div class="fw-bold">
                        <?= htmlspecialchars($userProfile['npm'] ?? '-') ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="text-muted">Jenis Kelamin</label>
                    <div class="fw-bold">
                        <?= htmlspecialchars($userProfile['jenis_kelamin'] ?? '-') ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="text-muted">Fakultas</label>
                    <div class="fw-bold">
                        <?= htmlspecialchars($userProfile['fakultas'] ?? '-') ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="text-muted">Jurusan</label>
                    <div class="fw-bold">
                        <?= htmlspecialchars($userProfile['jurusan'] ?? '-') ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="text-muted">Email</label>
                    <div class="fw-bold">
                        <?= htmlspecialchars($userProfile['email'] ?? '-') ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="text-muted">No HP</label>
                    <div class="fw-bold">
                        <?= htmlspecialchars($userProfile['no_hp'] ?? '-') ?>
                    </div>
                </div>

            </div>

            <hr>

            <!-- BUTTON -->
            <div class="d-flex gap-2">

                <a class="btn btn-warning"
                   href="uploads/profile/edit_profile.php">

                    ✏ Ganti Foto

                </a>

                <?php if(($userProfile['role'] ?? '') == 'admin'): ?>

<a class="btn btn-info text-white"
   href="edit_profile.php">

    📷 Ganti Foto

</a>

<?php endif; ?>

                <a class="btn btn-secondary"
                   href="dashboard_bootstrap/dashboard_bootstrap.php">

                    ⬅ Kembali

                </a>

            </div>

        </div>

    </div>
</div>

<?php include __DIR__ . '/dashboard_bootstrap/layout/footer.php'; ?>