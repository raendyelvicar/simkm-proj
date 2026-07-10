<?php 
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'konselor'])) {
    header('Location: ../login.php');
    exit;
}

require '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = (int) $_GET['id'];

$article = $mysqli->query("
    SELECT * FROM articles 
    WHERE id=$id
")->fetch_assoc();

if (!$article) {
    echo "<script>alert('Artikel tidak ditemukan');window.location='index.php';</script>";
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $title   = $mysqli->real_escape_string($_POST['title']);
    $content = $mysqli->real_escape_string($_POST['content']);

    $mysqli->query("
        UPDATE articles 
        SET 
            title='$title',
            content='$content'
        WHERE id=$id
    ");

    header("Location: index.php");
    exit();
}
?>

<?php include __DIR__ . '/../../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100" style="background:#f8f9fa; min-height:100vh;">

    <div class="card shadow-sm p-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">✏ Edit Artikel</h4>

            <a href="index.php" class="btn btn-secondary btn-sm">
                ⬅ Kembali
            </a>
        </div>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Judul Artikel</label>

                <input
                    type="text"
                    name="title"
                    class="form-control"
                    value="<?= htmlspecialchars($article['title']) ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Isi Artikel</label>

                <textarea
                    name="content"
                    rows="10"
                    class="form-control"
                    required
                ><?= htmlspecialchars($article['content']) ?></textarea>
            </div>

            <button class="btn btn-primary" type="submit">
                💾 Update Artikel
            </button>

        </form>

    </div>

</div>
</div>

<?php include __DIR__ . '/../../dashboard_bootstrap/layout/footer.php'; ?>