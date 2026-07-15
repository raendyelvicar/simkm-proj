<?php ob_start(); ?>

<div class="counselor-page">
    <div class="page-head">
        <div>
            <h1>Konsultasi Masuk</h1>
            <p>Mahasiswa yang telah mengirim pesan konsultasi kepada Anda.</p>
        </div>
    </div>

    <?php if (!empty($threads)): ?>
        <div class="thread-list">
            <?php foreach ($threads as $thread): ?>
                <a href="/consultations/<?= urlencode($thread['student_id']) ?>" class="thread-row">
                    <div class="counselor-avatar counselor-avatar-sm">
                        <?php if (!empty($thread['profile'])): ?>
                            <img src="<?= htmlspecialchars($thread['profile']) ?>"
                                alt="<?= htmlspecialchars($thread['nama']) ?>"
                                onerror="this.remove()">
                        <?php endif; ?>
                        <span class="counselor-avatar-initial"><?= htmlspecialchars(mb_strtoupper(mb_substr($thread['nama'] !== '' ? $thread['nama'] : '?', 0, 1))) ?></span>
                    </div>

                    <div class="thread-row-body">
                        <div class="thread-row-head">
                            <strong><?= htmlspecialchars($thread['nama'] !== '' ? $thread['nama'] : 'Mahasiswa') ?></strong>
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
    <?php else: ?>
        <div class="counselor-empty">
            <div class="counselor-empty-icon">💬</div>
            <p>Belum ada mahasiswa yang mengirim pesan konsultasi.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Konsultasi Masuk';
$extraStyles = require __DIR__ . '/_styles.php';
$extraStyles .= require __DIR__ . '/_inbox_styles.php';
require __DIR__ . '/../layouts/index.php';
