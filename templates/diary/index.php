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
                        <th>Intensitas</th>
                        <th>Emosi</th>
                        <th>Ringkasan</th>
                        <th>Privasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                        <tr>
                            <td class="diary-date">
                                <?= htmlspecialchars($entry['entry_date'] ? date('d M Y', strtotime($entry['entry_date'])) : '-') ?>
                            </td>
                            <td>
                                <span class="diary-badge <?= diary_intensity_badge_class((int) ($entry['intensitas_emosi'] ?? 0)) ?>">
                                    <?= (int) ($entry['intensitas_emosi'] ?? 0) ?> / 5
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars(implode(', ', $entry['emosi_list'] ?? []) ?: '-') ?>
                            </td>
                            <td class="diary-snippet">
                                <?= htmlspecialchars(mb_substr($entry['situasi'] ?? '', 0, 80)) ?><?= mb_strlen($entry['situasi'] ?? '') > 80 ? '…' : '' ?>
                            </td>
                            <td>
                                <?= !empty($entry['is_private']) ? 'Private' : 'Dibagikan' ?>
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
