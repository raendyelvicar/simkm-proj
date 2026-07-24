<?php
$queryParams = $_GET;
unset($queryParams['page']);
ob_start();
?>

<div class="counselor-page">
    <div class="page-head">
        <div>
            <h1>Konsultasi Masuk</h1>
            <p>Mahasiswa yang telah mengirim pesan konsultasi kepada Anda.</p>
        </div>
    </div>

    <div class="counselor-card" style="padding:16px 20px;margin-bottom:16px;">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Cari Mahasiswa</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Cari nama...">
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Urutkan</label>
                <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?= sort_options(['last_message_at' => 'Pesan Terakhir', 'name' => 'Nama Mahasiswa'], $sort, $dir) ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>
                <a href="/consultations" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <?php if (!empty($threads)): ?>
        <div class="thread-list">
            <?php foreach ($threads as $thread): ?>
                <a href="/consultations/<?= urlencode($thread['student_id']) ?>" class="thread-row">
                    <div class="counselor-avatar counselor-avatar-sm">
                        <?php $photo = profile_photo_url($thread['profile']); ?>
                        <?php if ($photo): ?>
                            <img src="<?= htmlspecialchars($photo) ?>"
                                alt="<?= htmlspecialchars($thread['name']) ?>"
                                onerror="this.remove()">
                        <?php endif; ?>
                        <span class="counselor-avatar-initial"><?= htmlspecialchars(mb_strtoupper(mb_substr($thread['name'] !== '' ? $thread['name'] : '?', 0, 1))) ?></span>
                    </div>

                    <div class="thread-row-body">
                        <div class="thread-row-head">
                            <strong><?= htmlspecialchars($thread['name'] !== '' ? $thread['name'] : 'Mahasiswa') ?></strong>
                            <span class="thread-row-time"><?= htmlspecialchars($thread['last_message_at'] ? date('d M Y H:i', strtotime($thread['last_message_at'])) : '') ?></span>
                        </div>
                        <p class="thread-row-snippet"><?= htmlspecialchars(substr($thread['last_message'], 0, 90)) ?></p>
                    </div>

                    <?php if ($thread['unread_count'] > 0): ?>
                        <span class="thread-unread-badge"><?= (int) $thread['unread_count'] ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="text-muted small"><?= (int) $total ?> mahasiswa ditemukan</span>
            <?= pagination_links($page, $totalPages, $queryParams) ?>
        </div>
    <?php else: ?>
        <div class="counselor-empty">
            <div class="counselor-empty-icon">💬</div>
            <p>Tidak ada mahasiswa yang cocok, atau belum ada pesan konsultasi.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Konsultasi Masuk';
$extraStyles = require __DIR__ . '/_styles.php';
$extraStyles .= require __DIR__ . '/_inbox_styles.php';
require __DIR__ . '/../layouts/index.php';
