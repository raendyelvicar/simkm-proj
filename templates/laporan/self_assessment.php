<?php
ob_start();
?>

<div class="lap-page">
    <a href="/laporan" class="lap-back-link">← Kembali ke Laporan</a>
    <div class="page-head">
        <div>
            <h1>📝 Laporan Riwayat Self Assessment</h1>
            <p>Skor PWB & BDI-II, tingkat risiko, dan rekomendasi sistem per sesi assessment.</p>
        </div>
    </div>

    <?php
    $pdfUrl = '/laporan/self-assessment/pdf';
    require __DIR__ . '/_filter_bar.php';
    ?>

    <div class="lap-card">
        <?php if ($rows): ?>
            <div class="lap-table-scroll">
                <table class="lap-table">
                    <thead>
                        <tr>
                            <th><?= sort_link('name', 'Nama', $sort, $dir, $currentQuery) ?></th>
                            <th><?= sort_link('date', 'Tanggal', $sort, $dir, $currentQuery) ?></th>
                            <th><?= sort_link('pwb_score', 'Skor PWB', $sort, $dir, $currentQuery) ?></th>
                            <th>Kategori PWB</th>
                            <th><?= sort_link('bdi2_score', 'Skor BDI-II', $sort, $dir, $currentQuery) ?></th>
                            <th>Kategori BDI-II</th>
                            <th><?= sort_link('risk_level', 'Tingkat Risiko', $sort, $dir, $currentQuery) ?></th>
                            <th>Rekomendasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['name']) ?></td>
                                <td><?= $r['date'] ? htmlspecialchars(date('d M Y', strtotime($r['date']))) : '-' ?></td>
                                <td><?= $r['pwb_score'] ?? '-' ?></td>
                                <td><?= $r['pwb_category'] ? '<span class="lap-badge ' . assessment_badge_class($r['pwb_category']) . '">' . htmlspecialchars($r['pwb_category']) . '</span>' : '-' ?></td>
                                <td><?= $r['bdi2_score'] ?? '-' ?></td>
                                <td><?= $r['bdi2_category'] ? '<span class="lap-badge ' . assessment_badge_class($r['bdi2_category']) . '">' . htmlspecialchars($r['bdi2_category']) . '</span>' : '-' ?></td>
                                <td><?= $r['risk_level'] ? '<span class="lap-badge ' . assessment_level_badge_class($r['risk_level']) . '">' . htmlspecialchars($r['risk_label']) . '</span>' : '-' ?></td>
                                <td><?= htmlspecialchars($r['recommendation']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3">
                <span class="text-muted small"><?= (int) $total ?> data ditemukan</span>
                <?= pagination_links($page, $totalPages, $currentQuery) ?>
            </div>
        <?php else: ?>
            <div class="lap-empty">
                <div class="icon">📭</div>
                <p>Tidak ada data assessment pada periode ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Laporan Riwayat Self Assessment';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
