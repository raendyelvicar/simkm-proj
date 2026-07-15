<?php
session_start();
require __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: /AplikasiSkripsi/redirect_dashboard.php");
    exit;
}

$query = mysqli_query($mysqli, "SELECT * FROM articles ORDER BY created_at DESC");
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100" style="background:#f8f9fa; min-height:100vh;">

    <h4 class="mb-3">📘 Artikel Kesehatan Mental</h4>

    <?php if (mysqli_num_rows($query) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($query)): ?>

            <div class="card shadow-sm p-3 mb-3 border-0">
                <h5 class="text-success mb-1">
                    <?= htmlspecialchars($row['title']) ?>
                </h5>

                <small class="text-muted">
                    Kategori: <?= htmlspecialchars($row['category']) ?> |
                    <?= date('d M Y', strtotime($row['created_at'])) ?>
                </small>

                <hr>

                <p style="font-size:14px; line-height:1.6;">
                    <?= nl2br(htmlspecialchars($row['content'])) ?>
                </p>
            </div>

        <?php endwhile; ?>
    <?php else: ?>

        <div class="alert alert-info">
            Belum ada artikel yang tersedia.
        </div>

    <?php endif; ?>

    <a href="../dashboard_bootstrap/dashboard_bootstrap.php" class="btn btn-secondary mt-3">
        ⬅ Kembali
    </a>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>