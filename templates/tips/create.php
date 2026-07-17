<?php ob_start(); ?>

<div class="tips-page">
    <div class="page-head">
        <div>
            <h1>Tambah Tips</h1>
            <p>Tips baru akan tampil sebagai popup untuk mahasiswa setelah login.</p>
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

        <form method="post" action="/tips" class="tips-form">
            <div class="field">
                <label for="title">Judul Tips</label>
                <input type="text" id="title" name="title" required value="<?= htmlspecialchars($old['title'] ?? '') ?>">
            </div>

            <div class="field">
                <label for="content">Isi Tips</label>
                <textarea id="content" name="content" required><?= htmlspecialchars($old['content'] ?? '') ?></textarea>
            </div>

            <div class="field">
                <label class="tips-check">
                    <input type="checkbox" name="is_active" value="1"
                        <?= !isset($old['is_active']) || $old['is_active'] ? 'checked' : '' ?>>
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
$pageTitle = $title ?? 'Tambah Tips';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
