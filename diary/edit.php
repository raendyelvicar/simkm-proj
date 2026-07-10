<?php
ini_set('session.cookie_path', '/');
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'konselor', 'mahasiswa'])) {
    header('Location: ../login.php');
    exit;
}

require __DIR__ . '/../config/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Akses tidak valid.");
}

$id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];

// ================= AMBIL DATA =================
$stmt = $mysqli->prepare("
    SELECT * FROM diary_entries 
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows == 0){
    die("Data tidak ditemukan.");
}

$row = $res->fetch_assoc();
$errors = [];

// ================= UPDATE =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $judul      = trim($_POST['judul']);
    $entry_date = trim($_POST['entry_date']);
    $mood       = trim($_POST['mood']);
// ================= VALIDASI MOOD =================
$allowedMood = [
    'Senang',
    'Netral',
    'Sedih',
    'Sangat Buruk'
];

if(!in_array($mood, $allowedMood)){
    $errors[] = "Mood tidak valid.";
}

    $content    = trim($_POST['content']);

    if (empty($judul) || empty($entry_date) || empty($content) || empty($mood)) {

    $errors[] = "Tanggal, mood, dan isi catatan wajib diisi.";

} else {

        $update = $mysqli->prepare("
            UPDATE diary_entries 
            SET judul=?, entry_date=?, mood_level=?, content=?
            WHERE id=? AND user_id=?
        ");

        $update->bind_param(
    "ssssii",
    $judul,
    $entry_date,
    $mood,
    $content,
    $id,
    $user_id
);
        if($update->execute()){

    $_SESSION['success'] = "Diary berhasil diperbarui";

    header('Location: list.php');
    exit;

} else {

    $errors[] = "Gagal memperbarui diary.";

}
    }
}
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100" style="background:#f8f9fa; min-height:100vh;">

    <div class="card shadow-sm p-4">

        <h4 class="mb-3">✏ Edit Diary</h4>

        <?php if(!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach($errors as $e): ?>
                    <div><?= $e ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">

            <div class="mb-3">

        <label class="form-label">Judul Diary</label>

        <input type="text"
           name="judul"
           class="form-control"
           value="<?= htmlspecialchars($row['judul']); ?>"
           required>

           </div>
            
            <div class="mb-3">

                <label class="form-label">Tanggal</label>
                <input type="date" name="entry_date" class="form-control"
                       value="<?= $row['entry_date']; ?>" required>
            </div>

            <div class="mb-3">

    <label class="form-label">Mood</label>

    <select name="mood" class="form-control" required>

        <option value="">-- Pilih Mood --</option>

        <option value="Senang"
            <?= ($row['mood_level'] == 'Senang') ? 'selected' : ''; ?>>
            😊 Senang
        </option>

        <option value="Netral"
            <?= ($row['mood_level'] == 'Netral') ? 'selected' : ''; ?>>
            😐 Netral
        </option>

        <option value="Sedih"
            <?= ($row['mood_level'] == 'Sedih') ? 'selected' : ''; ?>>
            😢 Sedih
        </option>

        <option value="Sangat Buruk"
            <?= ($row['mood_level'] == 'Sangat Buruk') ? 'selected' : ''; ?>>
            😫 Sangat Buruk
        </option>

    </select>

</div>

            <div class="mb-3">
                <label class="form-label">Isi Catatan</label>
                <textarea name="content" class="form-control" rows="6" required>
<?= htmlspecialchars($row['content']); ?>
                </textarea>
            </div>

            <button type="submit" class="btn btn-primary">💾 Update</button>
            <a href="list.php" class="btn btn-secondary">⬅ Kembali</a>

        </form>

    </div>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>