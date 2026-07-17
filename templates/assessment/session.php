<?php ob_start(); ?>

<div class="assess-page">
    <div class="page-head">
        <div>
            <h1>Isi Self-Assessment</h1>
            <p>Jawab satu per satu, kamu bisa berpindah pertanyaan bebas selama waktu tersedia.</p>
        </div>
        <div class="assess-timer" id="assess-timer">--:--</div>
    </div>

    <div class="assess-card assess-card-body mb-3">
        <div class="assess-rail" id="assess-rail"></div>
    </div>

    <div class="assess-card assess-card-body mb-3">
        <div class="assess-question-nav-label text-muted small mb-2" id="assess-question-label"></div>
        <div class="assess-question-title" id="assess-question-text"></div>
        <div id="assess-choices"></div>
    </div>

    <div class="assess-sticky-submit d-flex justify-content-between align-items-center">
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" id="assess-prev">&larr; Sebelumnya</button>
            <button type="button" class="btn btn-outline-secondary" id="assess-next">Selanjutnya &rarr;</button>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small" id="assess-progress"></span>
            <button type="button" class="btn btn-primary" id="assess-finish">Selesai</button>
        </div>
    </div>
</div>

<script>
    window.ASSESS_INIT = <?= json_encode([
        'sessionId'        => $sessionId,
        'remainingSeconds' => $remainingSeconds,
        'questions'        => $questions,
        'answers'          => $answers,
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>
<script src="/assets/js/assessment-session.js"></script>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Isi Self-Assessment';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
