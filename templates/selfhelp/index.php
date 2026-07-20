<?php
$level = $combined['level'] ?? null;
$isUrgent = $level !== null && $level >= 6;
$recommended = [];
if ($level !== null) {
    if ($level === 2) {
        $recommended = ['breathing'];
    } elseif ($level >= 3 && $level <= 5) {
        $recommended = ['activities', 'gratitude'];
    } elseif ($level >= 6) {
        $recommended = ['pfa'];
    }
}

$cards = [
    [
        'key' => 'breathing',
        'icon' => '🫁',
        'title' => 'Latihan Pernapasan',
        'desc' => 'Latihan pernapasan sederhana untuk meredakan cemas dan tegang dalam beberapa menit.',
        'href' => '/self-help/breathing',
    ],
    [
        'key' => 'activities',
        'icon' => '🌤️',
        'title' => 'Rencana Aktivitas Positif',
        'desc' => 'Rencanakan dan catat aktivitas kecil yang menyenangkan, lalu pantau perubahan suasana hatimu.',
        'href' => '/self-help/activities',
    ],
    [
        'key' => 'gratitude',
        'icon' => '🙏',
        'title' => 'Gratitude & Self Reflection',
        'desc' => 'Kumpulan rasa syukur dan refleksi diri yang sudah kamu tulis lewat Diary Terstruktur.',
        'href' => '/self-help/gratitude',
    ],
    [
        'key' => 'pfa',
        'icon' => '🆘',
        'title' => 'Bantuan Segera (PFA)',
        'desc' => 'Langkah pertolongan psikologis awal dan kontak darurat jika kamu merasa sangat tertekan.',
        'href' => '/self-help/pfa',
    ],
];

ob_start();
?>

<div class="selfhelp-page">
    <div class="page-head">
        <div>
            <h1>Self Help</h1>
            <p>Kumpulan latihan mandiri untuk membantu menjaga kesehatan mentalmu sehari-hari.</p>
        </div>
        <a href="/assessment" class="btn btn-outline-secondary btn-sm">&larr; Kembali ke Assessment</a>
    </div>

    <?php if ($combined): ?>
        <div class="assess-card assess-card-body mb-3">
            <span class="assess-badge <?= assessment_level_badge_class($combined['level']) ?>">
                Level <?= (int) $combined['level'] ?> &middot; Risiko <?= htmlspecialchars($combined['risk_label']) ?>
            </span>
            <div class="mt-2 small text-muted">
                Rekomendasi sistem: <strong><?= htmlspecialchars($combined['recommendation']) ?></strong> &mdash; <?= htmlspecialchars($combined['purpose']) ?>
            </div>
        </div>
    <?php else: ?>
        <div class="assess-card assess-card-body mb-3">
            <div class="small text-muted">
                Kamu belum memiliki hasil assessment BDI-II dan PWB terbaru. Isi <a href="/assessment">Self-Assessment</a> agar fitur di bawah bisa disesuaikan dengan kondisimu, atau langsung gunakan fitur mana pun kapan saja.
            </div>
        </div>
    <?php endif; ?>

    <?php if ($isUrgent): ?>
        <div class="pfa-emergency-card mb-3">
            <h5 class="mb-1 text-danger">🚨 Tingkat risikomu tergolong tinggi</h5>
            <p class="mb-2">Selain fitur self help di bawah, sangat disarankan untuk segera menghubungi konselor kampus dan membaca langkah bantuan awal.</p>
            <div class="d-flex gap-2 flex-wrap">
                <a href="/self-help/pfa" class="btn btn-danger btn-sm">Buka Bantuan Segera (PFA)</a>
                <a href="/counselor" class="btn btn-outline-danger btn-sm">Hubungi Konselor</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <?php foreach ($cards as $card): ?>
            <?php
                $isRecommended = in_array($card['key'], $recommended, true) && !$isUrgent;
                $isCardUrgent = $card['key'] === 'pfa' && $isUrgent;
                $cardClass = $isCardUrgent ? 'is-urgent' : ($isRecommended ? 'is-recommended' : '');
            ?>
            <div class="col-md-6">
                <a href="<?= $card['href'] ?>" class="selfhelp-feature-card <?= $cardClass ?>">
                    <?php if ($isCardUrgent): ?>
                        <span class="selfhelp-badge-urgent">Segera dibuka</span>
                    <?php elseif ($isRecommended): ?>
                        <span class="selfhelp-badge-recommended">Direkomendasikan untukmu</span>
                    <?php endif; ?>
                    <div class="selfhelp-feature-icon"><?= $card['icon'] ?></div>
                    <h5><?= htmlspecialchars($card['title']) ?></h5>
                    <p><?= htmlspecialchars($card['desc']) ?></p>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Self Help';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
