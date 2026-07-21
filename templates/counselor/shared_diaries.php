<?php
$queryParams = $_GET;
unset($queryParams['page']);
ob_start();
?>

<div class="counselor-page">
    <div class="page-head">
        <div>
            <h1>Diary Dibagikan</h1>
            <p>Catatan diary yang dibagikan mahasiswa kepada Anda.</p>
        </div>
    </div>

    <div class="counselor-card" style="padding:16px 20px;margin-bottom:16px;">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Cari Mahasiswa</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Cari nama/NPM...">
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Urutkan</label>
                <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?= sort_options(['entry_date' => 'Tanggal Diary', 'student_nama' => 'Nama Mahasiswa'], $sort, $dir) ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>
                <a href="/shared-diaries" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <?php if (!empty($entries)): ?>
        <div class="thread-list">
            <?php foreach ($entries as $entry): ?>
                <a href="/shared-diaries/<?= urlencode($entry['id']) ?>" class="thread-row">
                    <div class="thread-row-body">
                        <div class="thread-row-head">
                            <strong><?= htmlspecialchars($entry['student_nama'] ?: 'Mahasiswa') ?></strong>
                            <span class="thread-row-time"><?= htmlspecialchars($entry['entry_date'] ? date('d M Y', strtotime($entry['entry_date'])) : '') ?></span>
                        </div>
                        <p class="thread-row-snippet"><?= htmlspecialchars(mb_substr($entry['situasi'], 0, 90)) ?></p>
                    </div>
                    <span class="diary-badge <?= diary_intensity_badge_class((int) $entry['intensitas_emosi']) ?>">
                        <?= (int) $entry['intensitas_emosi'] ?> / 5
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="text-muted small"><?= (int) $total ?> diary ditemukan</span>
            <?= pagination_links($page, $totalPages, $queryParams) ?>
        </div>
    <?php else: ?>
        <div class="counselor-empty">
            <div class="counselor-empty-icon">📔</div>
            <p>Tidak ada diary yang cocok, atau belum ada mahasiswa yang membagikan diary kepada Anda.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Diary Dibagikan';
$extraStyles = require __DIR__ . '/_styles.php';
$extraStyles .= require __DIR__ . '/_inbox_styles.php';
$extraStyles .= require __DIR__ . '/../diary/_styles.php';
require __DIR__ . '/../layouts/index.php';
