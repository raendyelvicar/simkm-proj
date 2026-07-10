<?php
ini_set('session.cookie_path', '/');
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'konselor', 'mahasiswa'])) {
    header('Location: ../login.php');
    exit;
}

require __DIR__ . '/../config/db.php';

// ================= CEK ID =================
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: list.php");
    exit;
}

$id = intval($_GET['id']);

// ================= QUERY =================
$query = "SELECT d.*, u.username as nama_mahasiswa 
          FROM diary_entries d 
          JOIN users u ON d.user_id = u.id 
          WHERE d.id = ?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows == 0){
    echo "Data diary tidak ditemukan.";
    exit;
}

$row = $res->fetch_assoc();
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100" style="background:#f8f9fa; min-height:100vh;">

    <nav>
        <small class="text-muted">Home / Diary / Detail</small>
    </nav>

    <div class="card shadow-sm mt-3">

        <div class="card-header bg-primary text-white d-flex justify-content-between">
            <h5 class="mb-0">Detail Diary</h5>
            <span class="badge bg-light text-dark"><?= ucfirst($_SESSION['role']); ?></span>
        </div>

        <div class="card-body">

            <table class="table">
                <tr>
                    <th width="200">Nama Mahasiswa</th>
                    <td><?= htmlspecialchars($row['nama_mahasiswa']); ?></td>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <td><?= date('d M Y', strtotime($row['entry_date'])); ?></td>
                </tr>
                <tr>
                    <th>Mood</th>
                    <td>
                        <span class="badge bg-info">
                            <?= htmlspecialchars($row['mood_level']); ?>
                        </span>
                    </td>
                </tr>
            </table>

            <hr>

            <h6>Isi Diary</h6>
            <div class="p-3 border rounded bg-light">
                <?= nl2br(htmlspecialchars($row['content'])); ?>
            </div>

            <div class="mt-3">
                <a href="list.php" class="btn btn-secondary btn-sm">⬅ Kembali</a>

                <?php if($_SESSION['role'] == 'konselor'): ?>
                    <a href="send.php?diary_id=<?= $id ?>" 
                     class="btn btn-success btn-sm">
                     Tanggapan
                    </a>
                <?php endif; ?>
            </div>

        </div>

    </div>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>