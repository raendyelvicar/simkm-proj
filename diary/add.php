<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require __DIR__ . '/../config/db.php';

$uid = $_SESSION['user_id'];
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul  = $mysqli->real_escape_string($_POST['judul']);
    $mood   = $mysqli->real_escape_string($_POST['mood']);
    $content = $mysqli->real_escape_string($_POST['content']);

    if ($judul == "" || $content == "") {

    $msg = "Judul dan isi diary wajib diisi.";

} else {
        $mysqli->query("
        INSERT INTO diary_entries
        (user_id, entry_date, judul, mood_level, content)

        VALUES
        ($uid, NOW(), '$judul', '$mood', '$content')
    ");

        header("Location: list.php");
        exit;
    }
}
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100" style="background:#f8f9fa; min-height:100vh;">

    <nav>
        <small class="text-muted">Home / Diary / Tambah</small>
    </nav>

    <div class="card shadow-sm p-4 mt-3">

        <h4>✍️ Tulis Diary Baru</h4>

        <?php if($msg): ?>
            <div class="alert alert-danger"><?= $msg ?></div>
        <?php endif; ?>

        <form method="post">

            <div class="mb-3">

                <label class="form-label">Judul Diary</label>

                <input type="text"
                    name="judul"
                    class="form-control"
                    placeholder="Masukkan judul diary..."
                    required>

            </div>

            <div class="mb-3">
                <label class="form-label">Mood</label>
                <select name="mood" class="form-control">
                    <option value="Bahagia">Bahagia</option>
                    <option value="Stres">Stres</option>
                    <option value="Lelah">Lelah</option>
                    <option value="Cemas">Cemas</option>
                    <option value="Biasa saja">Biasa saja</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Isi Diary</label>
                <textarea name="content" class="form-control" rows="6" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">💾 Simpan</button>

            <a href="list.php" class="btn btn-primary">⬅ Kembali</a>

        </form>

    </div>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>