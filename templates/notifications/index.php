<?php
$queryParams = $_GET;
unset($queryParams['page']);
ob_start();
?>

<div class="notif-page">
    <div class="page-head d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h1>🔔 Notifikasi</h1>
            <p>Riwayat pemberitahuan booking, self-assessment, dan proses verifikasi akunmu.</p>
        </div>
        <?php if (!empty($notifications)): ?>
            <form method="post" action="/notifications/read-all">
                <input type="hidden" name="redirect_to" value="/notifications">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Tandai Semua Dibaca</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="notif-card">
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $n): ?>
                <a href="/notifications/<?= (int) $n->id ?>/open" class="notif-row <?= $n->isRead ? '' : 'notif-unread' ?>">
                    <span class="notif-icon"><?= notification_icon($n->type) ?></span>
                    <span class="notif-body">
                        <div class="notif-title"><?= htmlspecialchars($n->title) ?></div>
                        <div class="notif-message"><?= htmlspecialchars($n->message) ?></div>
                    </span>
                    <span class="notif-time"><?= time_ago($n->createdAt) ?></span>
                </a>
            <?php endforeach; ?>
            <div class="d-flex justify-content-between align-items-center p-3">
                <span class="text-muted small"><?= (int) $total ?> notifikasi</span>
                <?= pagination_links($page, $totalPages, $queryParams) ?>
            </div>
        <?php else: ?>
            <div class="notif-empty">
                <div class="notif-empty-icon">🔔</div>
                <p>Belum ada notifikasi.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Notifikasi';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
