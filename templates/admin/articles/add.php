<?php 
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'konselor'])) {
    header('Location: ../login.php');
    exit;
}

require '../../config/db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $title   = $mysqli->real_escape_string($_POST['title']);
    $content = $mysqli->real_escape_string($_POST['content']);

    $mysqli->query("
        INSERT INTO articles (title, content) 
        VALUES ('$title','$content')
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
            <h4 class="mb-0">➕ Tambah Artikel</h4>

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
                    placeholder="Masukkan judul artikel"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Isi Artikel</label>

                <textarea
                    name="content"
                    rows="10"
                    class="form-control"
                    placeholder="Tulis isi artikel..."
                    required
                ></textarea>
            </div>

            <button class="btn btn-primary" type="submit">
                💾 Simpan Artikel
            </button>

        </form>

    </div>

</div>
</div>

<?php include __DIR__ . '/../../dashboard_bootstrap/layout/footer.php'; ?>