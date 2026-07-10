<?php
session_start();

// ================= CEK LOGIN =================
if (!isset($_SESSION['user_id'])) {

    header('Location: ../login.php');
    exit;

}

// ================= ROLE =================
$role = $_SESSION['role'] ?? '';

// HANYA IZINKAN:
$allowedRoles = ['admin', 'konselor', 'mahasiswa'];

if (!in_array($role, $allowedRoles)) {

    header('Location: ../login.php');
    exit;

}

require '../config/db.php';

// ================= AMBIL DATA USER =================
$user_id = (int) $_SESSION['user_id'];

$username = $_SESSION['username'] ?? 'User';

// ================= QUERY USER =================
$queryUser = $mysqli->query("
    SELECT username
    FROM users
    WHERE id = '$user_id'
    LIMIT 1
");

// ================= CEK QUERY =================
if ($queryUser && mysqli_num_rows($queryUser) > 0) {

    $user = mysqli_fetch_assoc($queryUser);

    if (is_array($user) && isset($user['username'])) {

        $username = $user['username'];

    }

}
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<style>

.assessment-card{
    border:none;
    border-radius:18px;
    box-shadow:0 4px 15px rgba(0,0,0,0.06);
}

.assessment-card ul{
    padding-left:20px;
}

.assessment-card li{
    margin-bottom:10px;
}

.assessment-title{
    font-weight:700;
    color:#1e293b;
}

</style>

<div class="d-flex">

    <!-- SIDEBAR -->
    <?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

    <!-- CONTENT -->
    <div class="content-wrapper p-4 w-100">

        <h4 class="mb-4 assessment-title">
            🧠 Self-Assessment Kesehatan Mental
        </h4>

        <div class="card assessment-card p-4" style="max-width:700px;">

            <!-- USER -->
            <p>
                Halo,
                <strong>
                    <?= htmlspecialchars($username); ?>
                </strong>.
            </p>

            <!-- DESKRIPSI -->
            <p>
                Self-Assessment ini bertujuan untuk membantu Anda memahami kondisi kesehatan mental Anda saat ini.
                Jawaban Anda bersifat <b>rahasia</b> dan hanya Anda yang dapat melihat hasilnya.
            </p>

            <!-- INFO -->
            <ul>
                <li>Durasi ± 3 menit</li>
                <li>10–20 pertanyaan pilihan</li>
                <li>Hasil langsung ditampilkan setelah selesai</li>
            </ul>

            <br>

            <!-- BUTTON -->
            <a href="assessment.php"
               class="btn btn-success">

                Mulai Assessment

            </a>

            <br><br>

            <a href="../dashboard_bootstrap/dashboard_bootstrap.php"
               class="btn btn-secondary">

               ⬅ Kembali

            </a>

        </div>

    </div>

</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>