<?php ob_start(); ?>

<div class="article-page">
    <div class="page-head">
        <div>
            <h1>Artikel</h1>
            <p>Bacaan seputar kesehatan mental untuk mahasiswa.</p>
        </div>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="/article/create" class="btn-article btn-article-primary">+ Tulis Artikel</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($articles)): ?>
        <div class="article-grid">
            <?php foreach ($articles as $article): ?>
                <div class="article-card">
                    <?php if (!empty($article['image'])): ?>
                        <a href="/article/<?= urlencode($article['id']) ?>" class="article-card-thumb">
                            <img src="<?= htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" loading="lazy">
                        </a>
                    <?php endif; ?>
                    <div class="article-card-body">
                        <h2><a href="/article/<?= urlencode($article['id']) ?>"><?= htmlspecialchars($article['title']) ?></a></h2>
                        <div class="article-meta">
                            <?php if (!empty($article['category'])): ?>
                                <span class="category-pill"><?= htmlspecialchars($article['category']) ?></span>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($article['published_at'] ? date('d M Y', strtotime($article['published_at'])) : '-') ?></span>
                        </div>
                        <p class="article-snippet"><?= htmlspecialchars(substr(strip_tags($article['content']), 0, 120)) ?>&hellip;</p>

                        <?php if (!empty($article['tags_list'])): ?>
                            <div class="tag-list">
                                <?php foreach ($article['tags_list'] as $tag): ?>
                                    <span class="tag-pill">#<?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($_SESSION['user_id']) && (int) $_SESSION['user_id'] === (int) $article['user_id']): ?>
                            <div class="article-actions">
                                <a href="/article/<?= urlencode($article['id']) ?>/edit" class="btn-article btn-article-ghost btn-article-sm">Edit</a>
                                <form method="post" action="/article/<?= urlencode($article['id']) ?>/delete"
                                    onsubmit="return confirm('Hapus artikel ini?');" style="display:inline;">
                                    <button type="submit" class="btn-article btn-article-danger btn-article-sm">Hapus</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="article-empty">
            <div class="article-empty-icon">📰</div>
            <p>Belum ada artikel yang dipublikasikan.</p>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="/article/create" class="btn-article btn-article-primary">+ Tulis Artikel</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Artikel';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
