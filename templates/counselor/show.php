<?php ob_start(); ?>

<div class="counselor-page">
    <a href="/counselor" class="counselor-back">&larr; Kembali ke daftar konselor</a>

    <div class="counselor-detail">
        <div class="counselor-detail-head">
            <div class="counselor-avatar counselor-avatar-lg">
                <?php $photo = $counselor['foto_profil'] ?: $counselor['profile_image']; ?>
                <?php if (!empty($photo)): ?>
                    <img src="<?= htmlspecialchars($photo) ?>"
                        alt="<?= htmlspecialchars($counselor['nama']) ?>"
                        onerror="this.remove()">
                <?php endif; ?>
                <span class="counselor-avatar-initial"><?= htmlspecialchars(mb_strtoupper(mb_substr($counselor['nama'] !== '' ? $counselor['nama'] : '?', 0, 1))) ?></span>
            </div>
            <div>
                <h1><?= htmlspecialchars($counselor['nama'] !== '' ? $counselor['nama'] : 'Konselor') ?></h1>
                <?php if (!empty($counselor['profesi'])): ?>
                    <span class="category-pill"><?= htmlspecialchars($counselor['profesi']) ?></span>
                <?php endif; ?>
                <?php if (!empty($counselor['spesialisasi'])): ?>
                    <span class="category-pill"><?= htmlspecialchars($counselor['spesialisasi']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($counselor['biografi'])): ?>
            <p class="counselor-detail-bio"><?= nl2br(htmlspecialchars($counselor['biografi'])) ?></p>
        <?php endif; ?>

        <?php if (!empty($counselor['pendidikan'])): ?>
            <div class="counselor-meta">🎓 <?= htmlspecialchars($counselor['pendidikan']) ?></div>
        <?php endif; ?>
        <?php if (!empty($counselor['pengalaman_tahun'])): ?>
            <div class="counselor-meta">🧭 <?= (int) $counselor['pengalaman_tahun'] ?> tahun pengalaman</div>
        <?php endif; ?>
        <?php if (!empty($counselor['bahasa'])): ?>
            <div class="counselor-meta">🗣️ <?= htmlspecialchars($counselor['bahasa']) ?></div>
        <?php endif; ?>
        <?php if (!empty($counselor['metode_konsultasi'])): ?>
            <div class="counselor-meta">💻 <?= htmlspecialchars($counselor['metode_konsultasi']) ?> &middot; <?= (int) $counselor['durasi_sesi'] ?> menit</div>
        <?php endif; ?>
        <!-- <?php if (!empty($counselor['biaya_konsultasi'])): ?>
            <div class="counselor-meta">💳 Rp<?= number_format((float) $counselor['biaya_konsultasi'], 0, ',', '.') ?></div>
        <?php endif; ?> -->

        <div>
            <a href="/chat/<?= urlencode($counselor['user_id']) ?>" class="btn-counselor btn-counselor-primary">💬 Mulai Konsultasi</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Detail Konselor';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
