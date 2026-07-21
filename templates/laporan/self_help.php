<?php
$statusLabel = ['done' => 'Selesai', 'skipped' => 'Dilewati', 'planned' => 'Direncanakan'];
$statusBadge = ['done' => 'lap-badge-green', 'skipped' => 'lap-badge-red', 'planned' => 'lap-badge-yellow'];
ob_start();
?>

<div class="lap-page">
    <a href="/laporan" class="lap-back-link">← Kembali ke Laporan</a>
    <div class="page-head">
        <div>
            <h1>🌱 Laporan Aktivitas Self Help</h1>
            <p>Aktivitas positif yang direncanakan dan diselesaikan mahasiswa.</p>
        </div>
    </div>

    <?php
    $pdfUrl = '/laporan/self-help/pdf';
    require __DIR__ . '/_filter_bar.php';
    ?>

    <div class="lap-card">
        <?php if ($rows): ?>
            <div class="lap-table-scroll">
                <table class="lap-table">
                    <thead>
                        <tr>
                            <th><?= sort_link('student_nama', 'Nama', $sort, $dir, $currentQuery) ?></th>
                            <th><?= sort_link('title', 'Aktivitas', $sort, $dir, $currentQuery) ?></th>
                            <th><?= sort_link('planned_date', 'Tanggal', $sort, $dir, $currentQuery) ?></th>
                            <th><?= sort_link('status', 'Status', $sort, $dir, $currentQuery) ?></th>
                            <th>Mood Sebelum</th>
                            <th>Mood Sesudah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['student_nama']) ?></td>
                                <td><?= htmlspecialchars($r['title']) ?></td>
                                <td><?= $r['planned_date'] ? htmlspecialchars(date('d M Y', strtotime($r['planned_date']))) : '-' ?></td>
                                <td><span class="lap-badge <?= $statusBadge[$r['status']] ?? 'lap-badge-gray' ?>"><?= htmlspecialchars($statusLabel[$r['status']] ?? $r['status']) ?></span></td>
                                <td><?= $r['mood_before'] ?? '-' ?></td>
                                <td><?= $r['mood_after'] ?? '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3">
                <span class="text-muted small"><?= (int) $total ?> aktivitas ditemukan</span>
                <?= pagination_links($page, $totalPages, $currentQuery) ?>
            </div>
        <?php else: ?>
            <div class="lap-empty">
                <div class="icon">📭</div>
                <p>Tidak ada aktivitas self help pada periode ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Laporan Aktivitas Self Help';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
