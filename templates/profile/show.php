<?php ob_start(); ?>

<div style="max-width:900px;margin:40px auto;padding:0 20px;color:#333;">
    <h2 style="color:#0ea5a4;margin-bottom:20px;"><?= htmlspecialchars($title ?? 'Profil') ?></h2>
    <p style="opacity:0.7;">This view is a placeholder — wire up real data here.</p>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
