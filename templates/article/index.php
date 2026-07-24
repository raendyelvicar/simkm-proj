<?php

$role = $role ?? ($_SESSION['role'] ?? '');
$isStaff = in_array($role, ['admin', 'counselor'], true);
$queryParams = $_GET;
unset($queryParams['page']);
ob_start(); ?>

<div class="article-page">
    <div class="page-head">
        <div>
            <h1>Artikel</h1>
            <p>Bacaan seputar kesehatan mental untuk mahasiswa.</p>
        </div>
        <?php if ($isStaff): ?>
            <a href="/article/create" class="btn-article btn-article-primary">+ Tulis Artikel</a>
        <?php endif; ?>
    </div>


    <?php if (!empty($role)): ?>
        <div class="article-card" style="padding:16px 20px;margin-bottom:20px;">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Cari Artikel</label>
                    <input type="text" name="q" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Cari judul/isi...">
                </div>
                <div class="col-auto">
                    <label class="form-label small text-muted mb-1">Kategori</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categoryOptions as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?= ($filters['category'] ?? '') === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small text-muted mb-1">Urutkan</label>
                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?= sort_options(['published_at' => 'Tanggal Publikasi', 'title' => 'Judul'], $sort, $dir) ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                    <a href="/article" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    <?php endif; ?>



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

                        <?php if ($isStaff && (!empty($_SESSION['user_id']) && (int) $_SESSION['user_id'] === (int) $article['user_id'])): ?>
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
        <div class="d-flex justify-content-between align-items-center mt-3">
            <?= pagination_links($page, $totalPages, $queryParams) ?>
        </div>
    <?php else: ?>
        <div class="article-empty">
            <div class="article-empty-icon">📰</div>
            <p>Tidak ada artikel yang cocok, atau belum ada artikel yang dipublikasikan.</p>
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
require __DIR__ . '/../layouts/' . (!empty($_SESSION['user_id']) ? 'index.php' : 'public.php');
