<?php ob_start(); $mood = mood_meta($entry['mood_level'] ?? ''); ?>

<div class="diary-page">
    <div class="page-head">
        <div>
            <h1><?= htmlspecialchars($entry['judul']) ?></h1>
            <p>
                <?= htmlspecialchars($entry['entry_date'] ? date('d M Y', strtotime($entry['entry_date'])) : '-') ?>
                &middot; <?= !empty($entry['is_private']) ? 'Privat' : 'Publik' ?>
            </p>
        </div>
        <a href="/diary" class="btn-diary btn-diary-ghost">&larr; Kembali</a>
    </div>

    <div class="diary-card">
        <div class="diary-card-body">

            <span class="mood-pill mood-<?= $mood['slug'] ?>">
                <?= $mood['emoji'] ?> <?= htmlspecialchars($entry['mood_level'] ?? '-') ?>
            </span>

            <div class="diary-content-text">
                <?= nl2br(htmlspecialchars($entry['content'] ?? '')) ?>
            </div>

            <div class="diary-form-actions">
                <a href="/diary/<?= urlencode($entry['id']) ?>/edit" class="btn-diary btn-diary-ghost">Edit</a>
                <form method="post" action="/diary/<?= urlencode($entry['id']) ?>/delete"
                      onsubmit="return confirm('Hapus diary ini?');">
                    <button type="submit" class="btn-diary btn-diary-danger">Hapus</button>
                </form>
            </div>

        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Detail Diary';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
