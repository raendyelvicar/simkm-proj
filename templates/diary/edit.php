<?php ob_start(); ?>

<div class="diary-page">
    <div class="page-head">
        <div>
            <h1>Edit Diary</h1>
            <p>Perbarui catatan diary kamu.</p>
        </div>
        <a href="/diary/<?= urlencode($entry['id']) ?>" class="btn-diary btn-diary-ghost">&larr; Kembali</a>
    </div>

    <div class="diary-card">
        <div class="diary-card-body">

            <?php if (!empty($errors)): ?>
                <div class="diary-alert diary-alert-error">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/diary/<?= urlencode($entry['id']) ?>" class="diary-form">

                <div class="field">
                    <label for="judul">Judul</label>
                    <input type="text" id="judul" name="judul"
                           value="<?= htmlspecialchars($entry['judul'] ?? '') ?>" required>
                </div>

                <div class="field">
                    <label for="entry_date">Tanggal</label>
                    <input type="date" id="entry_date" name="entry_date"
                           value="<?= htmlspecialchars($entry['entryDate'] ?? $entry['entry_date'] ?? '') ?>" required>
                </div>

                <div class="field">
                    <label>Mood</label>
                    <div class="mood-picker">
                        <?php $currentMood = $entry['moodLevel'] ?? $entry['mood_level'] ?? ''; ?>
                        <?php foreach ($moods as $mood): $meta = mood_meta($mood); ?>
                            <input type="radio" id="mood-<?= $meta['slug'] ?>" name="mood_level"
                                   value="<?= htmlspecialchars($mood) ?>"
                                   <?= $currentMood === $mood ? 'checked' : '' ?> required>
                            <label for="mood-<?= $meta['slug'] ?>"><?= $meta['emoji'] ?> <?= htmlspecialchars($mood) ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="field">
                    <label for="content">Isi Diary</label>
                    <textarea id="content" name="content"
                              required><?= htmlspecialchars($entry['content'] ?? '') ?></textarea>
                </div>

                <div class="field">
                    <label class="diary-check">
                        <input type="checkbox" name="is_private" value="1"
                               <?= !empty($entry['isPrivate'] ?? $entry['is_private'] ?? true) ? 'checked' : '' ?>>
                        <span>Privat &mdash; hanya saya yang bisa lihat</span>
                    </label>
                </div>

                <div class="diary-form-actions">
                    <button type="submit" class="btn-diary btn-diary-primary">Update Diary</button>
                    <a href="/diary/<?= urlencode($entry['id']) ?>" class="btn-diary btn-diary-ghost">Batal</a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Edit Diary';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
