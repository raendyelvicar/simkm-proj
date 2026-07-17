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
                        <?php $photo = $counselor['foto_profil'] ?: $counselor['profile_image']; ?>
                        <?php if (!empty($photo)): ?>
                            <img src="<?= htmlspecialchars($photo) ?>"
                                alt="<?= htmlspecialchars($counselor['nama']) ?>"
                                onerror="this.remove()">
                        <?php endif; ?>
                        <span class="counselor-avatar-initial"><?= htmlspecialchars(mb_strtoupper(mb_substr($counselor['nama'] !== '' ? $counselor['nama'] : '?', 0, 1))) ?></span>
                    </div>

                    <div class="counselor-card-body">
                        <h2><?= htmlspecialchars($counselor['nama'] !== '' ? $counselor['nama'] : 'Konselor') ?></h2>

                        <?php if (!empty($counselor['profesi'])): ?>
                            <span class="category-pill"><?= htmlspecialchars($counselor['profesi']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($counselor['spesialisasi'])): ?>
                            <span class="category-pill"><?= htmlspecialchars($counselor['spesialisasi']) ?></span>
                        <?php endif; ?>

                        <?php if (!empty($counselor['biografi'])): ?>
                            <p class="counselor-bio"><?= htmlspecialchars(substr($counselor['biografi'], 0, 110)) ?>&hellip;</p>
                        <?php endif; ?>

                        <?php if (!empty($counselor['metode_konsultasi'])): ?>
                            <div class="counselor-meta">💻 <?= htmlspecialchars($counselor['metode_konsultasi']) ?> &middot; <?= (int) $counselor['durasi_sesi'] ?> menit</div>
                        <?php endif; ?>
                        <?php if (!empty($counselor['biaya_konsultasi'])): ?>
                            <div class="counselor-meta">💳 Rp<?= number_format((float) $counselor['biaya_konsultasi'], 0, ',', '.') ?></div>
                        <?php endif; ?>

                        <div class="counselor-actions">
                            <a href="/counselor/<?= urlencode($counselor['konselor_id']) ?>" class="btn-counselor btn-counselor-ghost">Lihat Profil</a>
                            <a href="/chat/<?= urlencode($counselor['konselor_id']) ?>" class="btn-counselor btn-counselor-primary">💬 Konsultasi</a>
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
