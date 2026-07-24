<?php
$statusBadge = ['Sangat Aktif' => 'lap-badge-green', 'Aktif' => 'lap-badge-yellow', 'Kurang Aktif' => 'lap-badge-red'];
ob_start();
?>

<div class="lap-page">
    <a href="/laporan" class="lap-back-link">← Kembali ke Laporan</a>
    <div class="page-head">
        <div>
            <h1>✅ Laporan Evaluasi Keterlibatan Mahasiswa</h1>
            <p>Tingkat keaktifan mahasiswa dalam menggunakan aplikasi.</p>
        </div>
    </div>

    <?php
    $pdfUrl = '/laporan/engagement/pdf';
    require __DIR__ . '/_filter_bar.php';
    ?>

    <div class="lap-card">
        <?php if ($rows): ?>
            <div class="lap-table-scroll">
                <table class="lap-table">
                    <thead>
                        <tr>
                            <th><?= sort_link('name', 'Nama', $sort, $dir, $currentQuery) ?></th>
                            <th><?= sort_link('assessment_count', 'Assessment', $sort, $dir, $currentQuery) ?></th>
                            <th><?= sort_link('diary_count', 'Diary', $sort, $dir, $currentQuery) ?></th>
                            <th><?= sort_link('selfhelp_count', 'Self Help', $sort, $dir, $currentQuery) ?></th>
                            <th><?= sort_link('booking_count', 'Booking', $sort, $dir, $currentQuery) ?></th>
                            <th>Konseling Selesai</th>
                            <th><?= sort_link('total_actions', 'Status Keaktifan', $sort, $dir, $currentQuery) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['name']) ?></td>
                                <td><?= $r['assessment_count'] ?></td>
                                <td><?= $r['diary_count'] ?></td>
                                <td><?= $r['selfhelp_count'] ?></td>
                                <td><?= $r['booking_count'] ?></td>
                                <td><?= $r['completed_counseling_count'] ?></td>
                                <td><span class="lap-badge <?= $statusBadge[$r['status']] ?? 'lap-badge-gray' ?>"><?= htmlspecialchars($r['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3">
                <span class="text-muted small"><?= (int) $total ?> mahasiswa ditemukan</span>
                <?= pagination_links($page, $totalPages, $currentQuery) ?>
            </div>
        <?php else: ?>
            <div class="lap-empty">
                <div class="icon">📭</div>
                <p>Belum ada aktivitas mahasiswa pada periode ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Laporan Evaluasi Keterlibatan Mahasiswa';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
