<?php
session_start();

// =========================
// CEK LOGIN ADMIN
// =========================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require __DIR__ . '/../config/db.php';

// =========================
// VALIDASI ID
// =========================
if (!isset($_GET['id'])) {
    header('Location: manage_users.php');
    exit;
}

$id = (int) $_GET['id'];

// =========================
// CEK KOLOM NAMA
// =========================
$hasNama = false;
$check = $mysqli->query("SHOW COLUMNS FROM users LIKE 'nama'");

if ($check && $check->num_rows > 0) {
    $hasNama = true;
}

// =========================
// AMBIL DATA USER
// =========================
$stmt = $mysqli->prepare("
    SELECT * FROM users 
    WHERE id=? AND role='konselor' 
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    echo "<script>
            alert('Data konselor tidak ditemukan');
            window.location='manage_users.php';
          </script>";
    exit;
}

$user = $result->fetch_assoc();

// =========================
// UPDATE DATA
// =========================
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nama     = $hasNama ? trim($_POST['nama']) : '';
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role     = 'konselor';

    // =========================
    // VALIDASI
    // =========================
    if (empty($username)) {

        $message = '
        <div class="alert alert-danger">
            Username wajib diisi
        </div>';

    } else {

        // =========================
        // CEK USERNAME DUPLIKAT
        // =========================
        $cek = $mysqli->prepare("
            SELECT id 
            FROM users 
            WHERE username=? AND id != ?
        ");

        $cek->bind_param("si", $username, $id);
        $cek->execute();

        $cekResult = $cek->get_result();

        if ($cekResult->num_rows > 0) {

            $message = '
            <div class="alert alert-danger">
                Username sudah digunakan
            </div>';

        } else {

            // =========================
            // UPDATE TANPA PASSWORD
            // =========================
            if (empty($password)) {

                if ($hasNama) {

                    $update = $mysqli->prepare("
                        UPDATE users 
                        SET nama=?, username=?, role=? 
                        WHERE id=?
                    ");

                    $update->bind_param(
                        "sssi",
                        $nama,
                        $username,
                        $role,
                        $id
                    );

                } else {

                    $update = $mysqli->prepare("
                        UPDATE users 
                        SET username=?, role=? 
                        WHERE id=?
                    ");

                    $update->bind_param(
                        "ssi",
                        $username,
                        $role,
                        $id
                    );
                }

            } else {

                // =========================
                // HASH PASSWORD
                // =========================
                $hashPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // =========================
                // UPDATE DENGAN PASSWORD
                // =========================
                if ($hasNama) {

                    $update = $mysqli->prepare("
                        UPDATE users 
                        SET nama=?, username=?, password=?, role=? 
                        WHERE id=?
                    ");

                    $update->bind_param(
                        "ssssi",
                        $nama,
                        $username,
                        $hashPassword,
                        $role,
                        $id
                    );

                } else {

                    $update = $mysqli->prepare("
                        UPDATE users 
                        SET username=?, password=?, role=? 
                        WHERE id=?
                    ");

                    $update->bind_param(
                        "sssi",
                        $username,
                        $hashPassword,
                        $role,
                        $id
                    );
                }
            }

            // =========================
            // EKSEKUSI UPDATE
            // =========================
            if ($update->execute()) {

                echo "
                <script>
                    alert('Data konselor berhasil diperbarui');
                    window.location='manage_users.php';
                </script>";
                exit;

            } else {

                $message = '
                <div class="alert alert-danger">
                    Gagal update data
                </div>';
            }
        }
    }
}
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100"
     style="background:#f8f9fa; min-height:100vh;">

    <div class="card shadow-sm p-4"
         style="max-width:700px; margin:auto;">

        <h4 class="mb-4">🧑‍🏫 Edit Konselor</h4>

        <?= $message; ?>

        <form method="POST">

            <?php if($hasNama): ?>

            <div class="mb-3">
                <label class="form-label">
                    Nama Lengkap
                </label>

                <input type="text"
                       name="nama"
                       class="form-control"
                       value="<?= htmlspecialchars($user['nama']); ?>"
                       required>
            </div>

            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">
                    Username
                </label>

                <input type="text"
                       name="username"
                       class="form-control"
                       value="<?= htmlspecialchars($user['username']); ?>"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Password Baru
                </label>

                <input type="password"
                       name="password"
                       class="form-control">

                <small class="text-muted">
                    Kosongkan jika tidak ingin mengganti password
                </small>
            </div>

            <div class="mb-4">
                <label class="form-label">
                    Role
                </label>

                <input type="text"
                       class="form-control"
                       value="konselor"
                       readonly>
            </div>

            <button type="submit"
                    class="btn btn-primary">
                💾 Simpan Perubahan
            </button>

            <a href="manage_users.php"
           class="btn btn-primary">
           ⬅ Kembali
        </a>

        </form>

    </div>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>