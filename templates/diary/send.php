<?php
ini_set('session.cookie_path', '/');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'konselor') {
    header('Location: ../login.php');
    exit;
}

require __DIR__ . '/../config/db.php';

// ================= AMBIL DATA =================
$diary_id = isset($_GET['diary_id']) ? intval($_GET['diary_id']) : 0;
$konselor_id = $_SESSION['user_id'];

// ================= PROSES SUBMIT =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $response = trim($_POST['response'] ?? '');

    if (!empty($response) && $diary_id > 0) {

        $stmt = $mysqli->prepare("
            INSERT INTO diary_responses (diary_id, konselor_id, response)
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param("iis", $diary_id, $konselor_id, $response);
        $stmt->execute();

        // Redirect kembali ke detail diary
        header("Location: ../diary/view.php?id=" . $diary_id);
        exit;
    }
}
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100" style="background:#f8f9fa; min-height:100vh;">

    <nav>
        <small class="text-muted">Home / Diary / Tanggapan</small>
    </nav>

    <div class="card shadow-sm mt-3">

        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Berikan Tanggapan</h5>
        </div>

        <div class="card-body">

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Tanggapan Konselor</label>
                    <textarea name="response" class="form-control" rows="5" required></textarea>
                </div>

                <button type="submit" class="btn btn-success">
                    Kirim Response
                </button>

                <a href="../diary/view.php?id=<?= $diary_id ?>" 
                   class="btn btn-secondary">
                   Kembali
                </a>

            </form>

        </div>

    </div>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>