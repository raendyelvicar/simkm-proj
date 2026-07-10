<?php
ini_set('session.cookie_path', '/');
session_start();

require __DIR__ . '../../../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'konselor', 'mahasiswa'])) {
    header('Location: ../login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];

/* =========================
   AMBIL DATA USER
   ========================= */
$query = mysqli_query($mysqli, "
    SELECT *
    FROM users
    WHERE id = '$userId'
");

$user = mysqli_fetch_assoc($query);

/* =========================
   UPDATE FOTO PROFIL
   ========================= */
if(isset($_POST['upload'])){

    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){

        $namaFile = $_FILES['foto']['name'];
        $tmpFile  = $_FILES['foto']['tmp_name'];

        // EXTENSION
        $ext = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));

        $allowed = ['jpg','jpeg','png'];

        if(in_array($ext, $allowed)){

            $newName = time() . '_' . preg_replace('/\s+/', '_', $namaFile);

            // FOLDER UPLOAD
            $uploadPath = __DIR__ . '/uploads/profile/';

            // BUAT FOLDER JIKA BELUM ADA
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            move_uploaded_file(
                $tmpFile,
                $uploadPath . $newName
            );

            mysqli_query($mysqli, "
                UPDATE users
                SET profile_image = '$newName'
                WHERE id = '$userId'
            ");

            $_SESSION['profile_image'] = $newName;

            header("Location: edit_profile.php?success=1");
            exit;
        }
    }
}
?>

<?php include __DIR__ . '../../../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '../../../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100"
     style="background:#f8f9fa; min-height:100vh;">

    <div class="card shadow-sm p-4 mx-auto"
         style="max-width:700px;">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h4 class="mb-1">🖼 Edit Foto Profil</h4>

                <small class="text-muted">
                    Upload foto profil akun Anda
                </small>
            </div>

            <a href="../../profile.php"
               class="btn btn-secondary btn-sm">
             
               ⬅ Kembali

            </a>

        </div>

        <!-- ALERT -->
        <?php if(isset($_GET['success'])): ?>

            <div class="alert alert-success">
                ✅ Foto profil berhasil diperbarui
            </div>

        <?php endif; ?>

        <!-- FOTO -->
        <div class="text-center mb-4">

            <?php
            $foto = !empty($user['profile_image'])
                ? 'uploads/profile/' . $user['profile_image']
                : 'uploads/profile/default.png';
            ?>

            <img src="<?= $foto ?>"
                 width="140"
                 height="140"
                 class="rounded-circle border shadow"
                 style="object-fit:cover;">

        </div>

        <!-- FORM -->
        <form method="POST"
              enctype="multipart/form-data">

            <div class="mb-3">

                <label class="form-label">
                    Pilih Foto Profil
                </label>

                <input type="file"
                       name="foto"
                       class="form-control"
                       accept=".jpg,.jpeg,.png"
                       required>

                <small class="text-muted">
                    Format: JPG, JPEG, PNG
                </small>

            </div>

            <button class="btn btn-primary"
                    type="submit"
                    name="upload">

                💾 Simpan Foto

            </button>

        </form>

    </div>

</div>
</div>

<?php include __DIR__ . '../../../dashboard_bootstrap/layout/footer.php'; ?>