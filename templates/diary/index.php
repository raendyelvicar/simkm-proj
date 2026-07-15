<?php ob_start(); ?>

<div class="diary-page">
    <div class="page-head">
        <div>
            <h1>Diary Saya</h1>
            <p>Catatan harian dan perjalanan mood kamu.</p>
        </div>
        <?php if (!empty($entries)): ?>
            <a href="/diary/create" class="btn-diary btn-diary-primary">+ Tulis Diary</a>
        <?php endif; ?>
    </div>

    <div class="diary-card">
        <?php if (!empty($entries)): ?>
            <table class="diary-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Mood</th>
                        <th>Ringkasan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                        <?php $mood = mood_meta($entry['mood_level'] ?? ''); ?>
                        <tr>
                            <td class="diary-date">
                                <?= htmlspecialchars($entry['entry_date'] ? date('d M Y', strtotime($entry['entry_date'])) : '-') ?>
                            </td>
                            <td>
                                <span class="mood-pill mood-<?= $mood['slug'] ?>">
                                    <?= $mood['emoji'] ?> <?= htmlspecialchars($entry['mood_level'] ?? '-') ?>
                                </span>
                            </td>
                            <td class="diary-snippet">
                                <?= nl2br(htmlspecialchars(substr($entry['content'] ?? '', 0, 80))) ?>&hellip;
                            </td>
                            <td>
                                <div class="diary-actions">
                                    <a href="/diary/<?= urlencode($entry['id']) ?>" class="btn-diary btn-diary-ghost btn-diary-sm">Lihat</a>
                                    <a href="/diary/<?= urlencode($entry['id']) ?>/edit" class="btn-diary btn-diary-ghost btn-diary-sm">Edit</a>
                                    <form method="post" action="/diary/<?= urlencode($entry['id']) ?>/delete"
                                        onsubmit="return confirm('Hapus diary ini?');" style="display:inline;">
                                        <button type="submit" class="btn-diary btn-diary-danger btn-diary-sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="diary-empty">
                <div class="diary-empty-icon">📔</div>
                <p>Belum ada diary. Yuk mulai menulis hari ini.</p>
                <a href="/diary/create" class="btn-diary btn-diary-primary">+ Tulis Diary</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Diary';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
