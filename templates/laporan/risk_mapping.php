<?php
$showSearch = false;
ob_start();
?>

<div class="lap-page">
    <a href="/laporan" class="lap-back-link">← Kembali ke Laporan</a>
    <div class="page-head">
        <div>
            <h1>📊 Laporan Pemetaan Risiko Kesehatan Mental</h1>
            <p>Distribusi tingkat risiko mahasiswa berdasarkan hasil assessment terakhir.</p>
        </div>
    </div>

    <?php
    $pdfUrl = '/laporan/risk-mapping/pdf';
    require __DIR__ . '/_filter_bar.php';
    ?>

    <div class="lap-card">
        <?php if ($total > 0): ?>
            <div class="row">
                <div class="col-lg-6">
                    <div class="lap-table-scroll">
                        <table class="lap-table">
                            <thead><tr><th>Tingkat Risiko</th><th>Jumlah</th><th>Persentase</th></tr></thead>
                            <tbody>
                                <?php foreach ($distribution as $d): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($d['label']) ?></td>
                                        <td><?= $d['count'] ?></td>
                                        <td><?= $d['percentage'] ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr><td><strong>Total</strong></td><td><strong><?= $total ?></strong></td><td><strong>100%</strong></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-3">
                    <canvas id="lap-risk-pie" class="lap-chart"></canvas>
                </div>
                <div class="col-lg-3">
                    <canvas id="lap-risk-bar" class="lap-chart"></canvas>
                </div>
            </div>
        <?php else: ?>
            <div class="lap-empty">
                <div class="icon">📭</div>
                <p>Belum ada data assessment untuk dipetakan pada periode ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($total > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const labels = <?= json_encode(array_column($distribution, 'label')) ?>;
    const counts = <?= json_encode(array_column($distribution, 'count')) ?>;
    const colors = ['#15803d', '#65a30d', '#a16207', '#c2410c', '#dc2626', '#b91c1c'];

    new Chart(document.getElementById('lap-risk-pie'), {
        type: 'pie',
        data: { labels: labels, datasets: [{ data: counts, backgroundColor: colors }] },
        options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } } }
    });

    new Chart(document.getElementById('lap-risk-bar'), {
        type: 'bar',
        data: { labels: labels, datasets: [{ label: 'Jumlah Mahasiswa', data: counts, backgroundColor: colors }] },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });
})();
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Laporan Pemetaan Risiko Kesehatan Mental';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
