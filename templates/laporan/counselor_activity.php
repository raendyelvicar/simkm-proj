<?php
$showSearch = false;
$totalSesi = array_sum(array_column($rows, 'total_sesi'));
$totalMahasiswa = array_sum(array_column($rows, 'total_mahasiswa'));
ob_start();
?>

<div class="lap-page">
    <a href="/laporan" class="lap-back-link">← Kembali ke Laporan</a>
    <div class="page-head">
        <div>
            <h1>🧑‍⚕️ Laporan Aktivitas Konselor</h1>
            <p>Jumlah sesi konseling, fakultas, dan kategori risiko terbanyak yang ditangani per konselor.</p>
        </div>
    </div>

    <?php
    $pdfUrl = '/laporan/counselor-activity/pdf';
    require __DIR__ . '/_filter_bar.php';
    ?>

    <div class="lap-stat-row">
        <div class="lap-stat-tile"><div class="value"><?= count($rows) ?></div><div class="label">Konselor Aktif</div></div>
        <div class="lap-stat-tile"><div class="value"><?= $totalSesi ?></div><div class="label">Total Sesi Selesai</div></div>
        <div class="lap-stat-tile"><div class="value"><?= $totalMahasiswa ?></div><div class="label">Total Mahasiswa Ditangani</div></div>
    </div>

    <div class="lap-card">
        <?php if ($entries): ?>
            <div class="lap-table-scroll">
                <table class="lap-table">
                    <thead>
                        <tr>
                            <th><?= sort_link('nama', 'Konselor', $sort, $dir, $currentQuery) ?></th>
                            <th>Spesialisasi</th>
                            <th><?= sort_link('total_sesi', 'Total Sesi', $sort, $dir, $currentQuery) ?></th>
                            <th><?= sort_link('total_mahasiswa', 'Total Mahasiswa', $sort, $dir, $currentQuery) ?></th>
                            <th>Fakultas Terbanyak</th>
                            <th>Kategori Risiko Terbanyak</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['nama']) ?></td>
                                <td><?= htmlspecialchars($r['spesialisasi'] ?: '-') ?></td>
                                <td><?= $r['total_sesi'] ?></td>
                                <td><?= $r['total_mahasiswa'] ?></td>
                                <td><?= $r['top_fakultas'] ? htmlspecialchars($r['top_fakultas']) . ' (' . $r['top_fakultas_count'] . ')' : '-' ?></td>
                                <td><?= $r['top_risk'] ? htmlspecialchars($r['top_risk']) . ' (' . $r['top_risk_count'] . ')' : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3">
                <span class="text-muted small"><?= (int) $total ?> konselor ditemukan</span>
                <?= pagination_links($page, $totalPages, $currentQuery) ?>
            </div>
        <?php else: ?>
            <div class="lap-empty">
                <div class="icon">📭</div>
                <p>Belum ada sesi konseling yang selesai pada periode ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Laporan Aktivitas Konselor';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
