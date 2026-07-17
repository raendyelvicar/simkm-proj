<?php ob_start(); ?>

<div class="tips-page">
    <div class="page-head">
        <div>
            <h1>Edit Tips</h1>
            <p>Perbarui isi atau status tips ini.</p>
        </div>
        <a href="/tips" class="btn-tips btn-tips-ghost">&larr; Kembali</a>
    </div>

    <div class="tips-detail">
        <?php if (!empty($errors)): ?>
            <div class="tips-alert tips-alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/tips/<?= urlencode($tip['id']) ?>" class="tips-form">
            <div class="field">
                <label for="title">Judul Tips</label>
                <input type="text" id="title" name="title" required value="<?= htmlspecialchars($tip['title'] ?? '') ?>">
            </div>

            <div class="field">
                <label for="content">Isi Tips</label>
                <textarea id="content" name="content" required><?= htmlspecialchars($tip['content'] ?? '') ?></textarea>
            </div>

            <div class="field">
                <label class="tips-check">
                    <input type="checkbox" name="is_active" value="1"
                        <?= !empty($tip['is_active']) ? 'checked' : '' ?>>
                    <span>Aktifkan tips ini agar tampil ke mahasiswa</span>
                </label>
            </div>

            <div class="tips-form-actions">
                <button type="submit" class="btn-tips btn-tips-primary">Simpan</button>
                <a href="/tips" class="btn-tips btn-tips-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Edit Tips';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
