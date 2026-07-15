<?php
ini_set('session.cookie_path', '/');
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'konselor', 'mahasiswa'])) {
    header('Location: ../login.php');
    exit;
}

require __DIR__ . '/../config/db.php';
$uid = $_SESSION['user_id'];

$questions = [
    "Saya merasa mudah lelah akhir-akhir ini.",
    "Saya sulit berkonsentrasi ketika belajar.",
    "Saya merasa cemas berlebihan tanpa alasan jelas.",
    "Saya sering merasa sedih atau kehilangan motivasi.",
    "Saya sulit tidur atau tidur tidak nyenyak.",
    "Saya merasa tidak percaya diri.",
    "Saya merasa tertekan dengan beban kuliah.",
    "Saya sulit mengontrol emosi.",
    "Saya merasa kesepian walaupun ada teman.",
    "Saya merasa hidup saya tidak seimbang."
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scores = [];
    for ($i = 0; $i < count($questions); $i++) {
        $scores[$i] = (int) ($_POST["q$i"] ?? 0);
    }

    $total_score = array_sum($scores);

    $stmt = $mysqli->prepare("
        INSERT INTO assessment_results 
        (user_id, total_skor, assessment_date, created_at)
        VALUES (?, ?, NOW(), NOW())
    ");
    $stmt->bind_param("ii", $uid, $total_score);
    $stmt->execute();

    header("Location: hasil.php?id=" . $stmt->insert_id);
    exit;
}
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100" style="background:#f8f9fa; min-height:100vh;">

    <nav>
        <small class="text-muted">Home / Assessment</small>
    </nav>

    <div class="card shadow-sm p-4 mt-3">

        <h4>Self-Assessment Kesehatan Mental</h4>
        <p class="text-muted">Jawab setiap pertanyaan dengan jujur (skor 1–5)</p>

        <form method="post">

        <?php foreach($questions as $index => $q): ?>
            <div class="mb-3">
                <label class="form-label">
                    <strong><?= ($index+1) . ". " . htmlspecialchars($q); ?></strong>
                </label>

                <select name="q<?= $index ?>" class="form-control" required>
                    <option value="">-- Pilih --</option>
                    <option value="1">1 - Tidak Pernah</option>
                    <option value="2">2 - Jarang</option>
                    <option value="3">3 - Kadang-kadang</option>
                    <option value="4">4 - Sering</option>
                    <option value="5">5 - Sangat Sering</option>
                </select>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary">Lihat Hasil</button>

        </form>

        <div class="mt-3">
            <a href="../dashboard_bootstrap/dashboard_bootstrap.php" class="btn btn-secondary">
                Kembali
            </a>
        </div>

    </div>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>