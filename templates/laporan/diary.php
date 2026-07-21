<?php
// --- Aggregate stats + chart data (emosi_list/reaksi_fisik_list are already decoded
// arrays here, hydrated via DiaryEntry — see LaporanController::diaryData()). The
// literal 'Lainnya' checkbox option is folded into its free-text companion field
// instead of being tallied as its own category. ---
function lap_tally_with_lainnya(array $rows, string $listKey, string $lainnyaKey): array
{
    $counts = [];
    foreach ($rows as $r) {
        foreach ($r[$listKey] as $item) {
            if ($item === 'Lainnya') {
                continue;
            }
            $counts[$item] = ($counts[$item] ?? 0) + 1;
        }
        if (!empty($r[$lainnyaKey])) {
            $counts[$r[$lainnyaKey]] = ($counts[$r[$lainnyaKey]] ?? 0) + 1;
        }
    }
    arsort($counts);

    // Cap the chart at 8 bars — free-text "lainnya" values could otherwise proliferate.
    if (count($counts) > 8) {
        $top = array_slice($counts, 0, 7, true);
        $top['Lainnya'] = array_sum(array_slice($counts, 7, null, true));
        $counts = $top;
    }

    return $counts;
}

$totalEntries = count($rows);
$emotionCounts = lap_tally_with_lainnya($rows, 'emosi_list', 'emosi_lainnya');
$reaksiCounts = lap_tally_with_lainnya($rows, 'reaksi_fisik_list', 'reaksi_fisik_lainnya');

$dateCounts = [];
$intensitySum = 0;
foreach ($rows as $r) {
    $intensitySum += (int) $r['intensitas_emosi'];
    if ($r['entry_date']) {
        $dateCounts[$r['entry_date']] = ($dateCounts[$r['entry_date']] ?? 0) + 1;
    }
}
ksort($dateCounts);

$avgIntensity = $totalEntries > 0 ? round($intensitySum / $totalEntries, 1) : 0;
$topEmotion = $emotionCounts ? array_key_first($emotionCounts) : '-';
$topReaksi = $reaksiCounts ? array_key_first($reaksiCounts) : '-';

$intensityBadge = [1 => 'lap-badge-green', 2 => 'lap-badge-green', 3 => 'lap-badge-yellow', 4 => 'lap-badge-orange', 5 => 'lap-badge-red'];

ob_start();
?>

<div class="lap-page">
    <a href="/laporan" class="lap-back-link">← Kembali ke Laporan</a>
    <div class="page-head">
        <div>
            <h1>📖 Laporan Diary</h1>
            <p>Riwayat diary terstruktur mahasiswa.</p>
        </div>
    </div>

    <?php
    $pdfUrl = '/laporan/diary/pdf';
    require __DIR__ . '/_filter_bar.php';
    ?>

    <?php if ($rows): ?>
        <div class="lap-stat-row">
            <div class="lap-stat-tile"><div class="value"><?= $totalEntries ?></div><div class="label">Total Entri Diary</div></div>
            <div class="lap-stat-tile"><div class="value"><?= $avgIntensity ?>/5</div><div class="label">Rata-rata Intensitas Emosi</div></div>
            <div class="lap-stat-tile"><div class="value" style="font-size:1.1rem;"><?= htmlspecialchars($topEmotion) ?></div><div class="label">Emosi Paling Sering</div></div>
            <div class="lap-stat-tile"><div class="value" style="font-size:1.1rem;"><?= htmlspecialchars($topReaksi) ?></div><div class="label">Reaksi Fisik Paling Sering</div></div>
        </div>

        <div class="lap-card">
            <h5 class="mb-3">Tren Jumlah Entri Diary</h5>
            <canvas id="lap-diary-trend" class="lap-chart"></canvas>
        </div>

        <div class="row g-3 mb-1">
            <div class="col-lg-6">
                <div class="lap-card h-100">
                    <h5 class="mb-3">Distribusi Emosi</h5>
                    <canvas id="lap-diary-emotions" class="lap-chart"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="lap-card h-100">
                    <h5 class="mb-3">Distribusi Reaksi Fisik</h5>
                    <canvas id="lap-diary-reactions" class="lap-chart"></canvas>
                </div>
            </div>
        </div>

        <div class="lap-card">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <h5 class="mb-0">Detail Entri</h5>
                <span class="text-muted small"><?= $total ?> entri &middot; Halaman <?= $page ?> / <?= $totalPages ?></span>
            </div>

            <div class="lap-table-scroll">
                <table class="lap-table">
                    <thead>
                        <tr>
                            <th><?= sort_link('entry_date', 'Tanggal', $sort, $dir, $currentQuery) ?></th>
                            <th><?= sort_link('student_nama', 'Mahasiswa', $sort, $dir, $currentQuery) ?></th>
                            <th>Emosi</th>
                            <th>Intensitas</th>
                            <th>Ringkasan Situasi</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $i => $e): ?>
                            <?php
                            $emosiText = implode(', ', $e['emosi_list']) . ($e['emosi_lainnya'] ? ', ' . $e['emosi_lainnya'] : '');
                            $situasiPreview = mb_strlen($e['situasi']) > 70 ? mb_substr($e['situasi'], 0, 70) . '…' : $e['situasi'];
                            $modalId = 'diaryDetail' . $page . '_' . $i;
                            ?>
                            <tr>
                                <td><?= $e['entry_date'] ? htmlspecialchars(date('d M Y', strtotime($e['entry_date']))) : '-' ?></td>
                                <td><?= htmlspecialchars($e['student_nama']) ?></td>
                                <td><?= htmlspecialchars($emosiText ?: '-') ?></td>
                                <td><span class="lap-badge <?= $intensityBadge[(int) $e['intensitas_emosi']] ?? 'lap-badge-gray' ?>"><?= (int) $e['intensitas_emosi'] ?>/5</span></td>
                                <td><?= htmlspecialchars($situasiPreview) ?></td>
                                <td>
                                    <button type="button" class="lap-btn lap-btn-ghost" style="padding:4px 10px;font-size:0.78rem;" data-bs-toggle="modal" data-bs-target="#<?= $modalId ?>">Lihat Detail</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <?php
                $paginationQuery = $_GET;
                unset($paginationQuery['page']);
                echo pagination_links($page, $totalPages, $paginationQuery);
                ?>
            </div>
        </div>

        <?php foreach ($entries as $i => $e): ?>
            <?php $modalId = 'diaryDetail' . $page . '_' . $i; ?>
            <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?= htmlspecialchars($e['student_nama']) ?> — <?= $e['entry_date'] ? htmlspecialchars(date('d M Y', strtotime($e['entry_date']))) : '-' ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="lap-diary-entry" style="border:0;padding:0;margin:0;">
                                <dl>
                                    <dt>Situasi</dt><dd><?= nl2br(htmlspecialchars($e['situasi'])) ?></dd>
                                    <dt>Pikiran</dt><dd><?= nl2br(htmlspecialchars($e['pikiran_awal'])) ?></dd>
                                    <dt>Emosi</dt><dd><?= htmlspecialchars(implode(', ', $e['emosi_list']) . ($e['emosi_lainnya'] ? ', ' . $e['emosi_lainnya'] : '')) ?> (Intensitas: <?= (int) $e['intensitas_emosi'] ?>/5)</dd>
                                    <dt>Reaksi Fisik</dt><dd><?= htmlspecialchars(implode(', ', $e['reaksi_fisik_list']) . ($e['reaksi_fisik_lainnya'] ? ', ' . $e['reaksi_fisik_lainnya'] : '')) ?></dd>
                                    <dt>Perilaku</dt><dd><?= nl2br(htmlspecialchars($e['perilaku'])) ?></dd>
                                    <dt>Self Reflection</dt><dd><?= $e['self_reflection'] ? nl2br(htmlspecialchars($e['self_reflection'])) : '-' ?></dd>
                                    <dt>Gratitude Journal</dt><dd><?= $e['gratitude_list'] ? htmlspecialchars(implode('; ', $e['gratitude_list'])) : '-' ?></dd>
                                    <dt>Rencana Besok</dt><dd><?= $e['rencana_besok'] ? nl2br(htmlspecialchars($e['rencana_besok'])) : '-' ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="lap-card lap-empty">
            <div class="icon">📭</div>
            <p>Tidak ada diary pada periode ini.</p>
        </div>
    <?php endif; ?>
</div>

<?php if ($rows): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const dateLabels = <?= json_encode(array_map(fn ($d) => date('d M', strtotime($d)), array_keys($dateCounts))) ?>;
    const dateCounts = <?= json_encode(array_values($dateCounts)) ?>;

    new Chart(document.getElementById('lap-diary-trend'), {
        type: 'line',
        data: {
            labels: dateLabels,
            datasets: [{ label: 'Entri Diary', data: dateCounts, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.1)', fill: true, tension: 0.3 }],
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } },
    });

    new Chart(document.getElementById('lap-diary-emotions'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($emotionCounts)) ?>,
            datasets: [{ label: 'Jumlah', data: <?= json_encode(array_values($emotionCounts)) ?>, backgroundColor: '#2563eb', borderRadius: 4 }],
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } },
    });

    new Chart(document.getElementById('lap-diary-reactions'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($reaksiCounts)) ?>,
            datasets: [{ label: 'Jumlah', data: <?= json_encode(array_values($reaksiCounts)) ?>, backgroundColor: '#c2410c', borderRadius: 4 }],
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } },
    });
})();
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Laporan Diary';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
