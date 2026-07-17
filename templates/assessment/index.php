<?php ob_start(); ?>

<div class="assess-page">
    <div class="page-head">
        <div>
            <h1>Self-Assessment</h1>
            <p>Evaluasi kondisi kesehatan mental dan kesejahteraan psikologis kamu.</p>
        </div>
        <a href="/assessment/history" class="btn btn-outline-secondary btn-sm">🕒 Riwayat</a>
    </div>

    <?php if (!empty($isStaff)): ?>

        <div class="row g-3 mb-3">
            <div class="col-md-3 col-6">
                <div class="assess-stat-tile">
                    <div class="value"><?= (int) ($activeMahasiswaCount ?? 0) ?></div>
                    <div class="label">Mahasiswa Aktif</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="assess-stat-tile">
                    <div class="value"><?= (int) ($participation['participants'] ?? 0) ?></div>
                    <div class="label">Sudah Mengisi Assessment</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="assess-stat-tile">
                    <div class="value"><?= (int) ($participation['total_submissions'] ?? 0) ?></div>
                    <div class="label">Total Submission</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="assess-stat-tile">
                    <div class="value"><?= number_format((float) ($participation['timeout_rate'] ?? 0), 1) ?>%</div>
                    <div class="label">Tingkat Waktu Habis</div>
                </div>
            </div>
        </div>

        <?php if (!empty($suicidalIdeationFlags)): ?>
            <div class="assess-card assess-card-body mb-3 border border-danger">
                <h5 class="mb-3">🚨 Indikasi Risiko Bunuh Diri (BDI-II Butir 9)</h5>
                <p class="text-muted small mb-3">Mahasiswa yang menjawab butir "Pikiran-pikiran atau keinginan bunuh diri" dengan skor &gt; 0. Segera tindak lanjuti.</p>
                <div class="table-responsive">
                    <table class="table assess-table align-middle">
                        <thead><tr><th>Mahasiswa</th><th>Skor Butir</th><th>Total Skor BDI-II</th><th>Tanggal</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach ($suicidalIdeationFlags as $flag): ?>
                                <tr>
                                    <td><?= htmlspecialchars($flag['nama'] ?? '-') ?></td>
                                    <td><span class="assess-badge assess-badge-red"><?= (int) $flag['item_score'] ?> / 3</span></td>
                                    <td><?= (int) $flag['total_score'] ?> / <?= (int) $flag['max_score'] ?></td>
                                    <td><?= htmlspecialchars(date('d M Y', strtotime($flag['submitted_at']))) ?></td>
                                    <td><a href="/assessment/result/<?= (int) $flag['id'] ?>" class="btn btn-sm btn-danger">Lihat</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="assess-card assess-card-body">
                    <h5 class="mb-3">🎓 Distribusi Mahasiswa per Fakultas</h5>
                    <?php if (empty($fakultasCounts)): ?>
                        <p class="text-muted mb-0">Belum ada data mahasiswa.</p>
                    <?php else: ?>
                        <canvas id="chart-fakultas" height="220"></canvas>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="assess-card assess-card-body">
                    <h5 class="mb-3">BDI-II — Kategori</h5>
                    <?php if (empty($countsBdi2)): ?>
                        <p class="text-muted mb-0">Belum ada data.</p>
                    <?php else: ?>
                        <canvas id="chart-bdi2-category" height="220"></canvas>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="assess-card assess-card-body">
                    <h5 class="mb-3">PWB — Kategori</h5>
                    <?php if (empty($countsPwb)): ?>
                        <p class="text-muted mb-0">Belum ada data.</p>
                    <?php else: ?>
                        <canvas id="chart-pwb-category" height="220"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-5">
                <div class="assess-card assess-card-body">
                    <h5 class="mb-3">Rata-rata Skor per Dimensi PWB</h5>
                    <?php if (empty($pwbDimensionAverages)): ?>
                        <p class="text-muted mb-0">Belum ada data.</p>
                    <?php else: ?>
                        <canvas id="chart-pwb-dimensions" height="260"></canvas>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-7">
                <div class="assess-card assess-card-body">
                    <h5 class="mb-3">Rata-rata Skor per Butir BDI-II</h5>
                    <p class="text-muted small mb-3">Butir 9 (pikiran bunuh diri) ditandai merah.</p>
                    <?php if (empty($bdi2ItemAverages)): ?>
                        <p class="text-muted mb-0">Belum ada data.</p>
                    <?php else: ?>
                        <canvas id="chart-bdi2-items" height="260"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="assess-card assess-card-body">
            <h5 class="mb-3">⚠️ Perlu Perhatian (Kategori Berat / Rendah Terbaru)</h5>
            <?php if (empty($recentFlagged)): ?>
                <p class="text-muted mb-0">Tidak ada hasil yang perlu perhatian khusus saat ini.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table assess-table align-middle">
                        <thead>
                            <tr><th>Mahasiswa</th><th>Jenis</th><th>Skor</th><th>Kategori</th><th>Tanggal</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentFlagged as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['nama'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($meta[$s['type']]['short_title'] ?? $s['type']) ?></td>
                                    <td><?= (int) $s['total_score'] ?> / <?= (int) $s['max_score'] ?></td>
                                    <td><span class="assess-badge <?= assessment_badge_class($s['category']) ?>"><?= htmlspecialchars($s['category']) ?></span></td>
                                    <td><?= htmlspecialchars(date('d M Y', strtotime($s['submitted_at']))) ?></td>
                                    <td><a href="/assessment/result/<?= (int) $s['id'] ?>" class="btn btn-sm btn-outline-primary">Lihat</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            (function () {
                var fakultasCounts = <?= json_encode($fakultasCounts ?? []) ?>;
                var countsBdi2 = <?= json_encode($countsBdi2 ?? []) ?>;
                var countsPwb = <?= json_encode($countsPwb ?? []) ?>;
                var pwbDimensionLabels = <?= json_encode(\App\Services\AssessmentScoringService::PWB_DIMENSIONS) ?>;
                var pwbDimensionAverages = <?= json_encode($pwbDimensionAverages ?? []) ?>;
                var bdi2ItemAverages = <?= json_encode($bdi2ItemAverages ?? []) ?>;

                var categoryColors = { 'Minimal': '#15803d', 'Tinggi': '#15803d', 'Ringan': '#a16207', 'Sedang': '#c2410c', 'Berat': '#b91c1c', 'Rendah': '#b91c1c' };

                if (Object.keys(fakultasCounts).length) {
                    new Chart(document.getElementById('chart-fakultas'), {
                        type: 'bar',
                        data: {
                            labels: Object.keys(fakultasCounts),
                            datasets: [{ label: 'Mahasiswa', data: Object.values(fakultasCounts), backgroundColor: '#2563eb' }]
                        },
                        options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
                    });
                }

                function categoryDoughnut(canvasId, counts) {
                    var el = document.getElementById(canvasId);
                    if (!el || !Object.keys(counts).length) { return; }
                    new Chart(el, {
                        type: 'doughnut',
                        data: {
                            labels: Object.keys(counts),
                            datasets: [{ data: Object.values(counts), backgroundColor: Object.keys(counts).map(function (c) { return categoryColors[c] || '#94a3b8'; }) }]
                        },
                        options: { plugins: { legend: { position: 'bottom' } } }
                    });
                }
                categoryDoughnut('chart-bdi2-category', countsBdi2);
                categoryDoughnut('chart-pwb-category', countsPwb);

                if (Object.keys(pwbDimensionAverages).length) {
                    new Chart(document.getElementById('chart-pwb-dimensions'), {
                        type: 'radar',
                        data: {
                            labels: Object.keys(pwbDimensionAverages).map(function (k) { return pwbDimensionLabels[k] || k; }),
                            datasets: [{ label: 'Rata-rata Skor (maks 18)', data: Object.values(pwbDimensionAverages), backgroundColor: 'rgba(37,99,235,0.2)', borderColor: '#2563eb' }]
                        },
                        options: { scales: { r: { beginAtZero: true, suggestedMax: 18 } } }
                    });
                }

                if (bdi2ItemAverages.length) {
                    new Chart(document.getElementById('chart-bdi2-items'), {
                        type: 'bar',
                        data: {
                            labels: bdi2ItemAverages.map(function (i) { return 'No. ' + i.order_no; }),
                            datasets: [{
                                label: 'Rata-rata Skor (maks 3)',
                                data: bdi2ItemAverages.map(function (i) { return i.avg_score; }),
                                backgroundColor: bdi2ItemAverages.map(function (i) { return i.order_no === 9 ? '#b91c1c' : '#2563eb'; })
                            }]
                        },
                        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, suggestedMax: 3 } } }
                    });
                }
            })();
        </script>

    <?php else: ?>

        <div class="row g-3">
            <?php foreach (['bdi2', 'pwb'] as $type):
                $latest = $type === 'bdi2' ? ($latestBdi2 ?? null) : ($latestPwb ?? null);
            ?>
                <div class="col-md-6">
                    <div class="assess-card assess-card-body h-100 d-flex flex-column">
                        <h5 class="mb-1"><?= htmlspecialchars($meta[$type]['short_title']) ?></h5>
                        <p class="text-muted small mb-3"><?= htmlspecialchars($meta[$type]['title']) ?></p>

                        <?php if ($latest): ?>
                            <div class="mb-3">
                                <span class="assess-badge <?= assessment_badge_class($latest['category']) ?>"><?= htmlspecialchars($latest['category']) ?></span>
                                <?php if (!empty($latest['is_timed_out'])): ?>
                                    <span class="assess-badge assess-badge-gray">Waktu Habis</span>
                                <?php endif; ?>
                                <div class="small text-muted mt-1">
                                    Skor terakhir: <strong><?= (int) $latest['total_score'] ?> / <?= (int) $latest['max_score'] ?></strong>
                                    &middot; <?= htmlspecialchars(date('d M Y', strtotime($latest['submitted_at']))) ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small mb-3">Kamu belum pernah mengisi assessment ini.</p>
                        <?php endif; ?>

                        <div class="mt-auto d-flex gap-2">
                            <a href="/assessment/start" class="btn btn-primary btn-sm"><?= $latest ? 'Isi Ulang' : 'Mulai' ?></a>
                            <?php if ($latest): ?>
                                <a href="/assessment/result/<?= (int) $latest['id'] ?>" class="btn btn-outline-secondary btn-sm">Lihat Hasil</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Self-Assessment';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
