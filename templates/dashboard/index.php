<?php
$role = $role ?? ($_SESSION['role'] ?? '');
$username = $username ?? ($_SESSION['username'] ?? '');

$roleLabels = [
    'admin'     => 'Admin',
    'konselor'  => 'Konselor',
    'mahasiswa' => 'Mahasiswa',
];
$roleLabel = $roleLabels[$role] ?? ucfirst($role);
$isStaff = in_array($role, ['admin', 'konselor'], true);

ob_start();
?>

<div class="card p-4 mb-4">
    <h3 class="h4 mb-1">Dashboard <?= htmlspecialchars($roleLabel) ?></h3>
    <p class="text-muted mb-0">Selamat datang, <?= htmlspecialchars($username) ?></p>
</div>

<div class="row g-3">

    <?php if ($isStaff): ?>
        <div class="col-md-4">
            <div class="card p-3 h-100">
                <h5 class="mb-2">🎓 Data Mahasiswa</h5>
                <p class="text-muted mb-3">Lihat daftar dan riwayat mahasiswa.</p>
                <a href="/students" class="btn btn-info btn-sm">Lihat</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-md-4">
        <div class="card p-3 h-100">
            <h5 class="mb-2">📖 Diary</h5>
            <p class="text-muted mb-3">Catat aktivitas dan perasaan harian.</p>
            <a href="/diary" class="btn btn-primary btn-sm">Buka Diary</a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3 h-100">
            <h5 class="mb-2">📝 Self-Assessment</h5>
            <p class="text-muted mb-3">Evaluasi kondisi kesehatan mental.</p>
            <a href="/assessment" class="btn btn-success btn-sm">Mulai</a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3 h-100">
            <h5 class="mb-2">📰 Artikel</h5>
            <p class="text-muted mb-3">Baca artikel seputar kesehatan mental.</p>
            <a href="/article" class="btn btn-secondary btn-sm">Baca</a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3 h-100">
            <h5 class="mb-2">💬 Konselor</h5>
            <p class="text-muted mb-3">Temukan konselor untuk konsultasi.</p>
            <a href="/counselor" class="btn btn-warning btn-sm">Lihat Konselor</a>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Dashboard';
require __DIR__ . '/../layouts/index.php';
