<?php
$statusBadge = ['Membaik' => 'lap-badge-green', 'Tetap' => 'lap-badge-yellow', 'Memburuk' => 'lap-badge-red'];
$focusRow = count($rows) === 1 ? $rows[0] : null;
ob_start();
?>

<div class="lap-page">
    <a href="/laporan" class="lap-back-link">← Kembali ke Laporan</a>
    <div class="page-head">
        <div>
            <h1>📈 Laporan Analisis Mood & Perkembangan Kondisi</h1>
            <p>Perbandingan assessment awal vs. terakhir, mood dominan, dan status perkembangan.</p>
        </div>
    </div>

    <?php
    $pdfUrl = '/laporan/mood-analysis/pdf';
    require __DIR__ . '/_filter_bar.php';
    ?>

    <div class="lap-card">
        <?php if ($entries): ?>
            <div class="lap-table-scroll">
                <table class="lap-table">
                    <thead>
                        <tr>
                            <th><?= sort_link('nama', 'Nama', $sort, $dir, $currentQuery) ?></th>
                            <th>Assessment Awal</th>
                            <th>Assessment Terakhir</th>
                            <th>Mood Dominan</th>
                            <th><?= sort_link('status', 'Status', $sort, $dir, $currentQuery) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['nama']) ?></td>
                                <td><?= $r['first_tanggal'] ? htmlspecialchars(date('d M Y', strtotime($r['first_tanggal']))) . ' (PWB ' . $r['first_pwb'] . ', BDI-II ' . $r['first_bdi2'] . ')' : '-' ?></td>
                                <td><?= $r['last_tanggal'] ? htmlspecialchars(date('d M Y', strtotime($r['last_tanggal']))) . ' (PWB ' . $r['last_pwb'] . ', BDI-II ' . $r['last_bdi2'] . ')' : '-' ?></td>
                                <td><?= htmlspecialchars($r['mood_dominan']) ?></td>
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
                <p>Belum ada riwayat assessment pada periode ini.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($focusRow && count($focusRow['sessions']) > 1): ?>
        <div class="lap-card">
            <h5 class="mb-3">Grafik Tren — <?= htmlspecialchars($focusRow['nama']) ?></h5>
            <canvas id="lap-mood-trend" class="lap-chart"></canvas>
        </div>
    <?php endif; ?>
</div>

<?php if ($focusRow && count($focusRow['sessions']) > 1): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const labels = <?= json_encode(array_map(fn ($s) => $s['tanggal'] ? date('d M Y', strtotime($s['tanggal'])) : '-', $focusRow['sessions'])) ?>;
    const pwb = <?= json_encode(array_map(fn ($s) => $s['pwb']['total_score'] ?? null, $focusRow['sessions'])) ?>;
    const bdi2 = <?= json_encode(array_map(fn ($s) => $s['bdi2']['total_score'] ?? null, $focusRow['sessions'])) ?>;

    new Chart(document.getElementById('lap-mood-trend'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                { label: 'Skor PWB', data: pwb, borderColor: '#2563eb', tension: 0.3 },
                { label: 'Skor BDI-II', data: bdi2, borderColor: '#dc2626', tension: 0.3 },
            ],
        },
        options: { scales: { y: { beginAtZero: true } } },
    });
})();
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Laporan Analisis Mood & Perkembangan Kondisi';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
