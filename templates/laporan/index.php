<?php
ob_start();
?>

<div class="lap-page">
    <div class="page-head">
        <div>
            <h1>📊 Laporan</h1>
            <p>Pilih laporan yang ingin Anda lihat.</p>
        </div>
    </div>

    <?php if (!empty($cards)): ?>
        <div class="lap-hub-grid">
            <?php foreach ($cards as $card): ?>
                <a href="/laporan/<?= htmlspecialchars($card['slug']) ?>" class="lap-hub-card">
                    <div class="icon"><?= $card['icon'] ?></div>
                    <h3><?= htmlspecialchars($card['title']) ?></h3>
                    <p><?= htmlspecialchars($card['desc']) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="lap-card lap-empty">
            <div class="icon">📭</div>
            <p>Belum ada laporan yang tersedia untuk peran Anda.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Laporan';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
