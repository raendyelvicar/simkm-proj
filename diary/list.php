<?php
ini_set('session.cookie_path', '/');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require __DIR__ . '/../config/db.php';

// =========================
// CEK FIELD MOOD
// =========================
$hasMood = false;

$checkMood = $mysqli->query("
    SHOW COLUMNS FROM diary_entries LIKE 'mood_level'
");

if($checkMood && $checkMood->num_rows > 0){
    $hasMood = true;
}

$uid = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// ================= LOAD DIARY =================
if($role == 'konselor' || $role == 'admin'){
    // Konselor lihat semua diary mahasiswa
    $res = $mysqli->query("
        SELECT d.*, u.username 
        FROM diary_entries d
        JOIN users u ON d.user_id = u.id
        ORDER BY d.entry_date DESC
    ");
} else {
    // Mahasiswa hanya lihat milik sendiri
    $res = $mysqli->query("
        SELECT *
        FROM diary_entries
        WHERE user_id='".intval($uid)."'
        ORDER BY entry_date DESC
    ");
}
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100" style="background:#f8f9fa; min-height:100vh;">

    <nav>
        <small class="text-muted">Home / Diary</small>
    </nav>

    <div class="card shadow-sm p-4 mt-3">

    <?php if(isset($_SESSION['success'])): ?>

<div class="alert alert-success">

    <?= $_SESSION['success']; ?>

</div>

<?php unset($_SESSION['success']); ?>

<?php endif; ?>

        <h4>📖 Diary Harian — <?= htmlspecialchars($username) ?></h4>

        <div class="mb-3">
            <a href="../dashboard_bootstrap/dashboard_bootstrap.php" class="btn btn-secondary btn-sm">Dashboard</a>
            <a href="add.php" class="btn btn-primary btn-sm">+ Tulis Diary</a>
            <a href="export_diary.php" class="btn btn-primary btn-sm">📩 Export Diary</a>
        </div>

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
    <?php if($role == 'konselor' || $role == 'admin'): ?>
        <th>Nama</th>
    <?php endif; ?>

    <th>Tanggal</th>
    <th>Mood</th>
    <th>Ringkasan</th>
    <th width="180">Aksi</th>
</tr>
            </thead>

            <tbody>
    <?php if($res && $res->num_rows > 0): ?>
        <?php while ($row = $res->fetch_assoc()): ?>
        <tr>

            <?php if($role == 'konselor' || $role == 'admin'): ?>
                <td><?= htmlspecialchars($row['username']) ?></td>
            <?php endif; ?>

            <td><?= date('d M Y', strtotime($row['entry_date'])) ?></td>

            <td>

<?php
// =========================
// AMBIL DATA MOOD
// =========================
$moodValue = 'Tidak Ada';

if(isset($row['mood_level']) && $row['mood_level'] != ''){

    $moodValue = trim($row['mood_level']);

}

// =========================
// WARNA BADGE MOOD
// =========================
$badgeColor = 'secondary';

// lowercase agar aman
$moodLower = strtolower($moodValue);

if($moodLower == 'senang'){

    $badgeColor = 'success';

} elseif($moodLower == 'sedih'){

    $badgeColor = 'danger';

} elseif($moodLower == 'netral'){

    $badgeColor = 'primary';

} elseif($moodLower == 'sangat buruk'){

    $badgeColor = 'dark';

}

?>

<span class="badge bg-<?= $badgeColor; ?>">

    <?= htmlspecialchars($moodValue); ?>

</span>

</td>

            <td>
                <?= nl2br(htmlspecialchars(substr($row['content'],0,80))) ?>...
            </td>

            <td>
                <a href="view.php?id=<?= $row['id']; ?>" 
                   class="btn btn-sm btn-info">👁</a>

                <a href="edit.php?id=<?= $row['id']; ?>" 
                   class="btn btn-sm btn-warning">✏</a>
            </td>

        </tr>
        <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Belum ada diary
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>