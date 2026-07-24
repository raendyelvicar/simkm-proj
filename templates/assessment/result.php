<?php ob_start(); ?>

<div class="assess-page">
    <div class="page-head">
        <div>
            <h1>Hasil <?= htmlspecialchars($meta['short_title']) ?></h1>
            <p><?= htmlspecialchars($meta['title']) ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="/assessment/history/<?= (int) $submission['id'] ?>/pdf" class="btn btn-outline-secondary btn-sm">⬇️ Unduh PDF</a>
            <a href="/assessment" class="btn btn-outline-secondary btn-sm">&larr; Kembali</a>
        </div>
    </div>

    <div class="assess-card mb-3">
        <div class="assess-score-hero">
            <div class="text-muted small">Total Skor</div>
            <div class="score"><?= (int) $submission['total_score'] ?> / <?= (int) $submission['max_score'] ?></div>
            <?php if ($submission['category_percentage'] !== null): ?>
                <div class="text-muted small mb-2"><?= $submission['category_percentage'] ?>%</div>
            <?php endif; ?>
            <span class="assess-badge <?= assessment_badge_class($submission['category']) ?>" style="font-size:1rem;">
                <?= htmlspecialchars($submission['category']) ?>
            </span>
            <?php if (!empty($submission['is_timed_out'])): ?>
                <span class="assess-badge assess-badge-gray" style="font-size:1rem;">Waktu Habis</span>
            <?php endif; ?>
            <div class="text-muted small mt-2">
                <?= htmlspecialchars(date('d F Y, H:i', strtotime($submission['submitted_at']))) ?>
                <?php if (!empty($isStaff) && !empty($submission['name'])): ?>
                    &middot; <?= htmlspecialchars($submission['name']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="assess-card assess-card-body mb-3">
        <h5 class="mb-2">Kesimpulan</h5>
        <p class="mb-0"><?= htmlspecialchars($feedback) ?></p>
    </div>

    <?php if (!empty($combined)): ?>
        <div class="assess-card assess-card-body mb-3">
            <span class="assess-badge <?= assessment_level_badge_class($combined['level']) ?>">
                Level <?= (int) $combined['level'] ?> &middot; Risiko <?= htmlspecialchars($combined['risk_label']) ?>
            </span>
            <div class="mt-2">
                <strong><?= htmlspecialchars($combined['recommendation']) ?></strong>
                <div class="text-muted small"><?= htmlspecialchars($combined['purpose']) ?></div>
            </div>
            <div class="mt-3 d-flex gap-2 flex-wrap">
                <?php if ($combined['level'] >= 6): ?>
                    <a href="/counselor" class="btn btn-danger btn-sm">Hubungi Konselor</a>
                    <a href="/self-help/pfa" class="btn btn-outline-danger btn-sm">Bantuan Segera (PFA)</a>
                <?php elseif ($combined['level'] >= 2): ?>
                    <a href="/self-help" class="btn btn-warning btn-sm">Buka Self Help</a>
                    <?php if ($combined['level'] >= 3): ?>
                        <a href="/diary" class="btn btn-outline-warning btn-sm">Isi Diary</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/article" class="btn btn-secondary btn-sm">Baca Artikel</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($tips)): ?>
        <div class="assess-card assess-card-body mb-3">
            <h5 class="mb-2">💡 Tips Menjaga Kesehatan Mental</h5>
            <ul class="mb-0">
                <?php foreach ($tips as $tip): ?>
                    <li><?= htmlspecialchars($tip) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>


    <?php if (!empty($submission['dimension_scores'])): ?>
        <div class="assess-card assess-card-body mb-3">
            <h5 class="mb-3">Detail Hasil per Dimensi</h5>
            <div class="assess-dimension-grid">
                <?php foreach ($submission['dimension_scores'] as $dim): ?>
                    <div class="assess-dimension-card">
                        <h6><?= htmlspecialchars($dim['label']) ?></h6>
                        <div class="mb-2">
                            <span class="assess-badge <?= assessment_badge_class($dim['category']) ?>"><?= htmlspecialchars($dim['category']) ?></span>
                            <span class="text-muted small ms-1"><?= (int) $dim['score'] ?> / <?= (int) $dim['max_score'] ?></span>
                        </div>
                        <p class="small text-muted mb-0"><?= htmlspecialchars($dim['feedback']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="assess-card assess-card-body mb-3">
        <h5 class="mb-3">Rincian Jawaban</h5>
        <div class="table-responsive">
            <table class="table assess-table table-sm">
                <thead>
                    <tr>
                        <th style="width:60%">Pertanyaan</th>
                        <th>Jawaban</th>
                        <th class="text-center">Skor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($answers as $answer): ?>
                        <tr>
                            <td><?= (int) $answer['order_no'] ?>. <?= htmlspecialchars($answer['question_text']) ?></td>
                            <td><?= htmlspecialchars($answer['label']) ?></td>
                            <td class="text-center"><?= (int) $answer['score_value'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="/assessment/start" class="btn btn-primary btn-sm">Isi Ulang</a>
        <a href="/assessment/history" class="btn btn-outline-secondary btn-sm">Lihat Riwayat</a>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Hasil Assessment';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
