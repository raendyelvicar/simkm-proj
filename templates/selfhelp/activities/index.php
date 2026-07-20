<?php
$statusLabels = ['planned' => 'Direncanakan', 'done' => 'Selesai', 'skipped' => 'Dilewati'];
$statusBadge = ['planned' => 'activity-badge-yellow', 'done' => 'activity-badge-green', 'skipped' => 'activity-badge-gray'];
ob_start();
?>

<div class="activity-page">
    <div class="page-head">
        <div>
            <h1>🌤️ Rencana Aktivitas Positif</h1>
            <p>Rencanakan aktivitas kecil yang menyenangkan, lalu bandingkan suasana hatimu sebelum dan sesudah melakukannya.</p>
        </div>
        <div class="page-head-actions">
            <a href="/self-help/activities/create" class="btn-activity btn-activity-primary">+ Tambah Aktivitas</a>
            <a href="/self-help" class="btn-activity btn-activity-ghost">&larr; Self Help</a>
        </div>
    </div>

    <div class="activity-card">
        <?php if (!empty($items)): ?>
            <div class="activity-table-scroll">
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Aktivitas</th>
                            <th>Mood</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="activity-date">
                                    <?= htmlspecialchars(date('d M Y', strtotime($item['planned_date']))) ?>
                                </td>
                                <td>
                                    <div class="activity-title"><?= htmlspecialchars($item['title']) ?></div>
                                    <?php if (!empty($item['description'])): ?>
                                        <div class="activity-desc"><?= htmlspecialchars($item['description']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="activity-mood">
                                    <?= $item['mood_before'] ? (int) $item['mood_before'] . '/5' : '-' ?>
                                    &rarr;
                                    <?= $item['mood_after'] ? (int) $item['mood_after'] . '/5' : '-' ?>
                                </td>
                                <td>
                                    <span class="activity-badge <?= $statusBadge[$item['status']] ?? 'activity-badge-gray' ?>">
                                        <?= htmlspecialchars($statusLabels[$item['status']] ?? $item['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="activity-actions">
                                        <?php if ($item['status'] === 'planned'): ?>
                                            <form method="post" action="/self-help/activities/<?= (int) $item['id'] ?>/complete" class="activity-complete-form">
                                                <select name="mood_after" aria-label="Mood setelah selesai">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <option value="<?= $i ?>" <?= $i === 3 ? 'selected' : '' ?>>Mood <?= $i ?>/5</option>
                                                    <?php endfor; ?>
                                                </select>
                                                <button type="submit" class="btn-activity btn-activity-success btn-activity-sm">✓ Selesai</button>
                                            </form>
                                            <div class="activity-actions-row">
                                                <form method="post" action="/self-help/activities/<?= (int) $item['id'] ?>/skip">
                                                    <button type="submit" class="btn-activity btn-activity-ghost btn-activity-sm">Lewati</button>
                                                </form>
                                                <form method="post" action="/self-help/activities/<?= (int) $item['id'] ?>/delete"
                                                      onsubmit="return confirm('Hapus aktivitas ini?');">
                                                    <button type="submit" class="btn-activity btn-activity-danger btn-activity-sm">Hapus</button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <form method="post" action="/self-help/activities/<?= (int) $item['id'] ?>/delete"
                                                  onsubmit="return confirm('Hapus aktivitas ini?');">
                                                <button type="submit" class="btn-activity btn-activity-danger btn-activity-sm">Hapus</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="activity-empty">
                <div class="activity-empty-icon">🌤️</div>
                <p>Belum ada aktivitas yang direncanakan. Mulai dari satu hal kecil yang ingin kamu lakukan hari ini.</p>
                <a href="/self-help/activities/create" class="btn-activity btn-activity-primary">+ Tambah Aktivitas</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Rencana Aktivitas Positif';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../../layouts/index.php';
