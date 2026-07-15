<?php ob_start(); ?>

<p><?= htmlspecialchars($message) ?></p>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
