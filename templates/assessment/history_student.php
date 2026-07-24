<?php
$queryParams = $_GET;
unset($queryParams['page']);
ob_start();
?>

<div class="assess-page">
    <div class="page-head">
        <div>
            <h1><?= htmlspecialchars($student['name'] ?: '-') ?></h1>
            <p>
                NPM <?= htmlspecialchars($student['student_number'] ?: '-') ?>
                &middot; <?= htmlspecialchars($student['faculty'] ?: '-') ?> / <?= htmlspecialchars($student['major'] ?: '-') ?>
            </p>
        </div>
        <a href="/assessment/history" class="btn btn-outline-secondary btn-sm">&larr; Kembali ke Daftar Mahasiswa</a>
    </div>

    <div class="assess-card assess-card-body mb-3">
        <form method="get" action="/assessment/history/student/<?= (int) $student['id'] ?>" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Jenis</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <?php foreach ($meta as $key => $m): ?>
                        <option value="<?= $key ?>" <?= ($filters['type'] ?? '') === $key ? 'selected' : '' ?>><?= htmlspecialchars($m['short_title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Kategori</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <?php foreach (['Minimal', 'Ringan', 'Sedang', 'Berat', 'Tinggi', 'Rendah'] as $c): ?>
                        <option value="<?= $c ?>" <?= ($filters['category'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                <a href="/assessment/history/student/<?= (int) $student['id'] ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="assess-card assess-card-body">
        <?php if (empty($submissions)): ?>
            <p class="text-muted mb-0">Tidak ada riwayat assessment yang cocok.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table assess-table align-middle">
                    <thead>
                        <tr>
                            <th><?= sort_link('type', 'Jenis', $sort, $dir, $queryParams) ?></th>
                            <th><?= sort_link('submitted_at', 'Tanggal', $sort, $dir, $queryParams) ?></th>
                            <th><?= sort_link('total_score', 'Skor', $sort, $dir, $queryParams) ?></th>
                            <th><?= sort_link('category', 'Kategori', $sort, $dir, $queryParams) ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($meta[$s['type']]['short_title'] ?? $s['type']) ?></td>
                                <td><?= htmlspecialchars(date('d M Y H:i', strtotime($s['submitted_at']))) ?></td>
                                <td><?= (int) $s['total_score'] ?> / <?= (int) $s['max_score'] ?></td>
                                <td>
                                    <span class="assess-badge <?= assessment_badge_class($s['category']) ?>"><?= htmlspecialchars($s['category']) ?></span>
                                    <?php if (!empty($s['is_timed_out'])): ?>
                                        <span class="assess-badge assess-badge-gray">Waktu Habis</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/assessment/result/<?= (int) $s['id'] ?>" class="btn btn-sm btn-outline-primary">Lihat</a>
                                    <a href="/assessment/history/<?= (int) $s['id'] ?>/pdf" class="btn btn-sm btn-outline-secondary">PDF</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="text-muted small"><?= (int) $total ?> submission ditemukan</span>
                <?= pagination_links($page, $totalPages, $queryParams) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Riwayat Assessment Mahasiswa';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
