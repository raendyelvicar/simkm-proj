<?php ob_start(); ?>

<div class="counselor-page">
    <a href="/counselor" class="counselor-back">&larr; Kembali ke daftar konselor</a>

    <div class="counselor-detail">
        <div class="counselor-detail-head">
            <div class="counselor-avatar counselor-avatar-lg">
                <?php $photo = profile_photo_url($counselor['profile_photo'] ?: $counselor['profile_image']); ?>
                <?php if ($photo): ?>
                    <img src="<?= htmlspecialchars($photo) ?>"
                        alt="<?= htmlspecialchars($counselor['name']) ?>"
                        onerror="this.remove()">
                <?php endif; ?>
                <span class="counselor-avatar-initial"><?= htmlspecialchars(mb_strtoupper(mb_substr($counselor['name'] !== '' ? $counselor['name'] : '?', 0, 1))) ?></span>
            </div>
            <div>
                <h1><?= htmlspecialchars($counselor['name'] !== '' ? $counselor['name'] : 'Konselor') ?></h1>
                <?php if (!empty($counselor['profession'])): ?>
                    <?php $professionLabels = ['Psychologist' => 'Psikolog', 'Counselor' => 'Konselor', 'Psychiatrist' => 'Psikiater']; ?>
                    <span class="category-pill"><?= htmlspecialchars($professionLabels[$counselor['profession']] ?? $counselor['profession']) ?></span>
                <?php endif; ?>
                <?php if (!empty($counselor['specialization'])): ?>
                    <span class="category-pill"><?= htmlspecialchars($counselor['specialization']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($counselor['biography'])): ?>
            <p class="counselor-detail-bio"><?= nl2br(htmlspecialchars($counselor['biography'])) ?></p>
        <?php endif; ?>

        <?php if (!empty($counselor['education'])): ?>
            <div class="counselor-meta">🎓 <?= htmlspecialchars($counselor['education']) ?></div>
        <?php endif; ?>
        <?php if (!empty($counselor['experience_years'])): ?>
            <div class="counselor-meta">🧭 <?= (int) $counselor['experience_years'] ?> tahun pengalaman</div>
        <?php endif; ?>
        <?php if (!empty($counselor['languages'])): ?>
            <div class="counselor-meta">🗣️ <?= htmlspecialchars($counselor['languages']) ?></div>
        <?php endif; ?>
        <?php if (!empty($counselor['consultation_method'])): ?>
            <div class="counselor-meta">💻 <?= htmlspecialchars($counselor['consultation_method']) ?> &middot; <?= (int) $counselor['session_duration'] ?> menit</div>
        <?php endif; ?>
        <!-- <?php if (!empty($counselor['consultation_fee'])): ?>
            <div class="counselor-meta">💳 Rp<?= number_format((float) $counselor['consultation_fee'], 0, ',', '.') ?></div>
        <?php endif; ?> -->

        <div>
            <?php if (!empty($hasActiveMonitoring)): ?>
                <a href="/chat/<?= urlencode($counselor['user_id']) ?>" class="btn-counselor btn-counselor-primary">💬 Mulai Konsultasi</a>
            <?php elseif (($_SESSION['role'] ?? '') === 'student'): ?>
                <a href="/bookings/create/<?= urlencode($counselor['user_id']) ?>" class="btn-counselor btn-counselor-primary">📅 Ajukan Booking Konsultasi</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Detail Konselor';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
