<?php ob_start(); ?>

<div class="diary-page">
    <div class="page-head">
        <div>
            <h1>Tulis Diary</h1>
            <p>Ceritakan harimu &mdash; catatan ini bersifat privat secara default.</p>
        </div>
        <a href="/diary" class="btn-diary btn-diary-ghost">&larr; Kembali</a>
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

            <form method="post" action="/diary" class="diary-form">

                <div class="field">
                    <label for="judul">Judul</label>
                    <input type="text" id="judul" name="judul"
                           value="<?= htmlspecialchars($old['judul'] ?? '') ?>"
                           placeholder="Judul diary kamu..." required>
                </div>

                <div class="field">
                    <label for="entry_date">Tanggal</label>
                    <input type="date" id="entry_date" name="entry_date"
                           value="<?= htmlspecialchars($old['entryDate'] ?? date('Y-m-d')) ?>" required>
                </div>

                <div class="field">
                    <label>Mood</label>
                    <div class="mood-picker">
                        <?php foreach ($moods as $mood): $meta = mood_meta($mood); ?>
                            <input type="radio" id="mood-<?= $meta['slug'] ?>" name="mood_level"
                                   value="<?= htmlspecialchars($mood) ?>"
                                   <?= ($old['moodLevel'] ?? '') === $mood ? 'checked' : '' ?> required>
                            <label for="mood-<?= $meta['slug'] ?>"><?= $meta['emoji'] ?> <?= htmlspecialchars($mood) ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="field">
                    <label for="content">Isi Diary</label>
                    <textarea id="content" name="content" placeholder="Tulis apa yang kamu rasakan..."
                              required><?= htmlspecialchars($old['content'] ?? '') ?></textarea>
                </div>

                <div class="field">
                    <label class="diary-check">
                        <input type="checkbox" name="is_private" value="1"
                               <?= ($old['isPrivate'] ?? true) ? 'checked' : '' ?>>
                        <span>Privat &mdash; hanya saya yang bisa lihat</span>
                    </label>
                </div>

                <div class="diary-form-actions">
                    <button type="submit" class="btn-diary btn-diary-primary">Simpan Diary</button>
                    <a href="/diary" class="btn-diary btn-diary-ghost">Batal</a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Tulis Diary';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
