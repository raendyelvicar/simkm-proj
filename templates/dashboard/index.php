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
$isAdmin = $role === 'admin';
$isCounselor = $role === 'konselor';
$isStudent = $role === 'mahasiswa';

ob_start();
?>

<div class="card p-4 mb-4">
    <h3 class="h4 mb-1">Dashboard <?= htmlspecialchars($roleLabel) ?></h3>
    <p class="text-muted mb-0">Selamat datang, <?= htmlspecialchars($username) ?></p>
</div>

<?php if ($isCounselor): ?>
    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">🧠 Ringkasan Self-Assessment Mahasiswa</h5>
            <a href="/assessment/history" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="row g-3">
            <?php foreach (['bdi2' => ['BDI-II', $assessCountsBdi2 ?? []], 'pwb' => ['PWB', $assessCountsPwb ?? []]] as [$label, $counts]): ?>
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="fw-semibold mb-2"><?= htmlspecialchars($label) ?></div>
                        <?php if (empty($counts)): ?>
                            <p class="text-muted small mb-0">Belum ada data.</p>
                        <?php else: ?>
                            <?php foreach ($counts as $category => $total): ?>
                                <div class="d-flex justify-content-between align-items-center small mb-1">
                                    <span class="assess-badge <?= assessment_badge_class($category) ?>"><?= htmlspecialchars($category) ?></span>
                                    <strong><?= (int) $total ?></strong>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>


    <?php if ($isStudent): ?>
        <div class="card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">🧠 Self-Assessment Saya</h5>
                <a href="/assessment/history" class="btn btn-sm btn-outline-primary">Riwayat</a>
            </div>
            <div class="row g-3">
                <?php foreach (['bdi2' => ['BDI-II', $assessLatestBdi2 ?? null], 'pwb' => ['PWB', $assessLatestPwb ?? null]] as $type => [$label, $latest]): ?>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100 d-flex flex-column">
                            <div class="fw-semibold mb-2"><?= htmlspecialchars($label) ?></div>
                            <?php if ($latest): ?>
                                <span class="assess-badge <?= assessment_badge_class($latest['category']) ?> mb-2"><?= htmlspecialchars($latest['category']) ?></span>
                                <div class="small text-muted mb-2">
                                    Skor: <strong><?= (int) $latest['total_score'] ?> / <?= (int) $latest['max_score'] ?></strong>
                                    &middot; <?= htmlspecialchars(date('d M Y', strtotime($latest['submitted_at']))) ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted small mb-2">Belum pernah mengisi.</p>
                            <?php endif; ?>
                            <a href="/assessment/start" class="btn btn-sm btn-primary mt-auto align-self-start"><?= $latest ? 'Isi Ulang' : 'Isi Sekarang' ?></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($dailyTip)): ?>
        <div class="modal fade" id="dailyTipModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">💡 Tips Hari Ini</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body text-center">
                        <h5 class="mb-3"><?= htmlspecialchars($dailyTip['title']) ?></h5>
                        <?= nl2br(htmlspecialchars($dailyTip['content'])) ?>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                new bootstrap.Modal(document.getElementById('dailyTipModal')).show();
            });
        </script>
    <?php endif; ?>
<?php endif; ?>

<div class="row g-3">

    <?php if ($isAdmin): ?>
        <div class="col-md-4">
            <div class="card p-3 h-100">
                <h5 class="mb-2">🎓 Data Mahasiswa</h5>
                <p class="text-muted mb-3">Lihat daftar dan riwayat mahasiswa.</p>
                <a href="/students" class="btn btn-info btn-sm">Lihat</a>
            </div>
        </div>
    <?php endif; ?>


    <?php if ($isStudent): ?>
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
    <?php endif; ?>


    <?php if ($isStudent): ?>
        <div class="col-md-4">
            <div class="card p-3 h-100">
                <h5 class="mb-2">📰 Artikel</h5>
                <p class="text-muted mb-3">Baca artikel seputar kesehatan mental.</p>
                <a href="/article" class="btn btn-secondary btn-sm">Baca</a>
            </div>
        </div>
    <?php endif; ?>

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
$extraStyles = require __DIR__ . '/../assessment/_styles.php';
require __DIR__ . '/../layouts/index.php';
