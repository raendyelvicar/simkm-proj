<?php ob_start(); ?>

<div class="article-page">
    <div class="page-head">
        <a href="/article" class="btn-article btn-article-ghost">&larr; Kembali</a>
    </div>

    <div class="article-detail">
        <?php if (!empty($article['category'])): ?>
            <span class="category-pill"><?= htmlspecialchars($article['category']) ?></span>
        <?php endif; ?>
        <h1><?= htmlspecialchars($article['title']) ?></h1>
        <div class="article-meta">
            <span><?= htmlspecialchars($article['published_at'] ? date('d M Y', strtotime($article['published_at'])) : '-') ?></span>
        </div>

        <?php if (!empty($article['tags_list'])): ?>
            <div class="tag-list">
                <?php foreach ($article['tags_list'] as $tag): ?>
                    <span class="tag-pill">#<?= htmlspecialchars($tag) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($article['image'])): ?>
            <div class="article-detail-image">
                <img src="<?= htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
            </div>
        <?php endif; ?>

        <div class="article-detail-body">
            <?= nl2br(htmlspecialchars($article['content'])) ?>
        </div>

        <?php if (!empty($_SESSION['user_id']) && (int) $_SESSION['user_id'] === (int) $article['user_id']): ?>
            <div class="article-form-actions">
                <a href="/article/<?= urlencode($article['id']) ?>/edit" class="btn-article btn-article-ghost">Edit</a>
                <form method="post" action="/article/<?= urlencode($article['id']) ?>/delete"
                    onsubmit="return confirm('Hapus artikel ini?');">
                    <button type="submit" class="btn-article btn-article-danger">Hapus</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Artikel - ' . ($article['title'] ?? 'Detail Artikel');
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/' . (!empty($_SESSION['user_id']) ? 'index.php' : 'public.php');
