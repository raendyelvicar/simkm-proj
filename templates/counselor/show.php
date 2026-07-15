<?php ob_start(); ?>

<div class="counselor-page">
    <a href="/counselor" class="counselor-back">&larr; Kembali ke daftar konselor</a>

    <div class="counselor-detail">
        <div class="counselor-detail-head">
            <div class="counselor-avatar counselor-avatar-lg">
                <?php if (!empty($counselor['profile'])): ?>
                    <img src="<?= htmlspecialchars($counselor['profile']) ?>"
                        alt="<?= htmlspecialchars($counselor['nama']) ?>"
                        onerror="this.remove()">
                <?php endif; ?>
                <span class="counselor-avatar-initial"><?= htmlspecialchars(mb_strtoupper(mb_substr($counselor['nama'] !== '' ? $counselor['nama'] : '?', 0, 1))) ?></span>
            </div>
            <div>
                <h1><?= htmlspecialchars($counselor['nama'] !== '' ? $counselor['nama'] : 'Konselor') ?></h1>
                <?php if (!empty($counselor['spesialisasi'])): ?>
                    <span class="category-pill"><?= htmlspecialchars($counselor['spesialisasi']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($counselor['biografi_singkat'])): ?>
            <p class="counselor-detail-bio"><?= nl2br(htmlspecialchars($counselor['biografi_singkat'])) ?></p>
        <?php endif; ?>

        <?php if (!empty($counselor['jadwal_praktik'])): ?>
            <div class="counselor-meta">🗓️ Jadwal Praktik: <?= htmlspecialchars($counselor['jadwal_praktik']) ?></div>
        <?php endif; ?>

        <div>
            <a href="/chat/<?= urlencode($counselor['id']) ?>" class="btn-counselor btn-counselor-primary">💬 Mulai Konsultasi</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Detail Konselor';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
