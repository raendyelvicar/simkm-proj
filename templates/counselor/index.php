<?php ob_start(); ?>

<div class="counselor-page">
    <div class="page-head">
        <div>
            <h1>Konselor</h1>
            <p>Pilih konselor untuk memulai konsultasi kesehatan mental.</p>
        </div>
    </div>

    <?php if (!empty($counselors)): ?>
        <div class="counselor-grid">
            <?php foreach ($counselors as $counselor): ?>
                <div class="counselor-card">
                    <div class="counselor-avatar">
                        <?php if (!empty($counselor['profile'])): ?>
                            <img src="<?= htmlspecialchars($counselor['profile']) ?>"
                                alt="<?= htmlspecialchars($counselor['nama']) ?>"
                                onerror="this.remove()">
                        <?php endif; ?>
                        <span class="counselor-avatar-initial"><?= htmlspecialchars(mb_strtoupper(mb_substr($counselor['nama'] !== '' ? $counselor['nama'] : '?', 0, 1))) ?></span>
                    </div>

                    <div class="counselor-card-body">
                        <h2><?= htmlspecialchars($counselor['nama'] !== '' ? $counselor['nama'] : 'Konselor') ?></h2>

                        <?php if (!empty($counselor['spesialisasi'])): ?>
                            <span class="category-pill"><?= htmlspecialchars($counselor['spesialisasi']) ?></span>
                        <?php endif; ?>

                        <?php if (!empty($counselor['biografi_singkat'])): ?>
                            <p class="counselor-bio"><?= htmlspecialchars(substr($counselor['biografi_singkat'], 0, 110)) ?>&hellip;</p>
                        <?php endif; ?>

                        <?php if (!empty($counselor['jadwal_praktik'])): ?>
                            <div class="counselor-meta">🗓️ <?= htmlspecialchars($counselor['jadwal_praktik']) ?></div>
                        <?php endif; ?>

                        <div class="counselor-actions">
                            <a href="/counselor/<?= urlencode($counselor['id']) ?>" class="btn-counselor btn-counselor-ghost">Lihat Profil</a>
                            <a href="/chat/<?= urlencode($counselor['id']) ?>" class="btn-counselor btn-counselor-primary">💬 Konsultasi</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="counselor-empty">
            <div class="counselor-empty-icon">🧑‍⚕️</div>
            <p>Belum ada konselor yang tersedia saat ini.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Konselor';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
