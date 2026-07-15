<?php ob_start(); ?>

<div class="article-page">
    <div class="page-head">
        <div>
            <h1>Edit Artikel</h1>
            <p>Perbarui artikel kamu.</p>
        </div>
        <a href="/article/<?= urlencode($article['id']) ?>" class="btn-article btn-article-ghost">&larr; Kembali</a>
    </div>

    <div class="article-detail">
        <?php if (!empty($errors)): ?>
            <div class="article-alert article-alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/article/<?= urlencode($article['id']) ?>" class="article-form" enctype="multipart/form-data">

            <div class="field">
                <label for="title">Judul</label>
                <input type="text" id="title" name="title"
                       value="<?= htmlspecialchars($article['title'] ?? '') ?>" required>
            </div>

            <div class="field">
                <label for="category">Kategori</label>
                <input type="text" id="category" name="category"
                       value="<?= htmlspecialchars($article['category'] ?? '') ?>"
                       placeholder="Mis. Edukasi, Tips, Assessment (opsional)">
            </div>

            <div class="field">
                <label for="tags">Tag</label>
                <input type="text" id="tags" name="tags"
                       value="<?= htmlspecialchars($article['tags'] ?? '') ?>"
                       placeholder="Pisahkan dengan koma, mis. stres, kuliah, self-care (opsional)">
            </div>

            <div class="field">
                <label for="image">Foto Sampul</label>
                <?php if (!empty($article['image'])): ?>
                    <div class="current-image-preview">
                        <img src="<?= htmlspecialchars($article['image']) ?>" alt="Foto sampul saat ini">
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                <p class="field-hint">JPG, PNG, atau WEBP, maksimal 2MB. Biarkan kosong untuk mempertahankan foto saat ini.</p>
            </div>

            <div class="field">
                <label for="content">Isi Artikel</label>
                <textarea id="content" name="content"
                          required><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
            </div>

            <div class="article-form-actions">
                <button type="submit" class="btn-article btn-article-primary">Update Artikel</button>
                <a href="/article/<?= urlencode($article['id']) ?>" class="btn-article btn-article-ghost">Batal</a>
            </div>

        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Edit Artikel';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
