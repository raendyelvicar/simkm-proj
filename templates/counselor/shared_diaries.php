<?php ob_start(); ?>

<div class="counselor-page">
    <div class="page-head">
        <div>
            <h1>Diary Dibagikan</h1>
            <p>Catatan diary yang dibagikan mahasiswa kepada Anda.</p>
        </div>
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
    <?php else: ?>
        <div class="counselor-empty">
            <div class="counselor-empty-icon">📔</div>
            <p>Belum ada mahasiswa yang membagikan diary kepada Anda.</p>
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
