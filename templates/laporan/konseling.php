<?php
$statusBadge = [
    'Pending'   => 'lap-badge-yellow',
    'Confirmed' => 'lap-badge-gray',
    'Completed' => 'lap-badge-green',
    'Cancelled' => 'lap-badge-red',
    'No Show'   => 'lap-badge-red',
    'Cancellation Requested' => 'lap-badge-orange',
];
$role = $_SESSION['role'] ?? '';

$extraFields = [
    [
        'name'    => 'status',
        'label'   => 'Status Booking',
        'type'    => 'select',
        'value'   => $filters['status'] ?? '',
        'options' => ['Pending' => 'Menunggu', 'Confirmed' => 'Terkonfirmasi', 'Completed' => 'Selesai', 'Cancelled' => 'Dibatalkan', 'No Show' => 'Tidak Hadir', 'Cancellation Requested' => 'Menunggu Persetujuan Pembatalan'],
    ],
];
if ($role === 'admin') {
    $extraFields[] = ['name' => 'counselor', 'label' => 'Nama Konselor', 'value' => $_GET['counselor'] ?? ''];
}
$bookingStatusLabels = ['Pending' => 'Menunggu', 'Confirmed' => 'Terkonfirmasi', 'Completed' => 'Selesai', 'Cancelled' => 'Dibatalkan', 'No Show' => 'Tidak Hadir', 'Cancellation Requested' => 'Menunggu Persetujuan Pembatalan'];

ob_start();
?>

<div class="lap-page">
    <a href="/laporan" class="lap-back-link">← Kembali ke Laporan</a>
    <div class="page-head">
        <div>
            <h1>💬 Laporan Konseling</h1>
            <p>Riwayat booking dan sesi konseling.</p>
        </div>
    </div>

    <?php
    $pdfUrl = '/laporan/konseling/pdf';
    require __DIR__ . '/_filter_bar.php';
    ?>

    <div class="lap-card">
        <?php if ($rows): ?>
            <div class="lap-table-scroll">
                <table class="lap-table">
                    <thead>
                        <tr>
                            <th><?= sort_link('date', 'Tanggal', $sort, $dir, $currentQuery) ?></th>
                            <th><?= sort_link('student_name', 'Mahasiswa', $sort, $dir, $currentQuery) ?></th>
                            <th><?= sort_link('counselor_name', 'Konselor', $sort, $dir, $currentQuery) ?></th>
                            <th>Jam</th>
                            <th><?= sort_link('status', 'Status', $sort, $dir, $currentQuery) ?></th>
                            <th>Catatan Konselor</th>
                            <th>Rekomendasi</th>
                            <th>Tindak Lanjut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= $r['date'] ? htmlspecialchars(date('d M Y', strtotime($r['date']))) : '-' ?></td>
                                <td><?= htmlspecialchars($r['student_name']) ?></td>
                                <td><?= htmlspecialchars($r['counselor_name']) ?></td>
                                <td><?= htmlspecialchars(substr($r['start_time'], 0, 5) . '–' . substr($r['end_time'], 0, 5)) ?></td>
                                <td><span class="lap-badge <?= $statusBadge[$r['status']] ?? 'lap-badge-gray' ?>"><?= htmlspecialchars($bookingStatusLabels[$r['status']] ?? $r['status']) ?></span></td>
                                <td><?= htmlspecialchars($r['counselor_notes'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($r['recommendation'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($r['follow_up'] ?? '-') ?></td>
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
                <p>Tidak ada data konseling pada periode ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Laporan Konseling';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
