<?php ob_start(); ?>

<div class="assess-page">
    <div class="page-head">
        <div>
            <h1>Assessment Selesai</h1>
            <p>
                <?php if ($session['status'] === 'timed_out'): ?>
                    Waktu pengisian telah habis. Jawaban yang sempat kamu isi telah otomatis dikirim.
                <?php else: ?>
                    Terima kasih, jawaban kamu telah berhasil dikirim.
                <?php endif; ?>
            </p>
        </div>
        <a href="/assessment" class="btn btn-outline-secondary btn-sm">&larr; Kembali</a>
    </div>

    <?php if ($session['status'] === 'timed_out'): ?>
        <div class="alert alert-warning">⏱️ Sesi berakhir karena waktu habis (<span class="assess-badge assess-badge-gray">Waktu Habis</span>). Sebagian pertanyaan mungkin belum sempat dijawab.</div>
    <?php endif; ?>

    <div class="row g-3">
        <?php foreach (['bdi2', 'pwb'] as $type):
            $submissionId = $type === 'bdi2' ? ($session['bdi2_submission_id'] ?? null) : ($session['pwb_submission_id'] ?? null);
        ?>
            <div class="col-md-6">
                <div class="assess-card assess-card-body h-100 d-flex flex-column">
                    <h5 class="mb-1"><?= htmlspecialchars($meta[$type]['short_title']) ?></h5>
                    <p class="text-muted small mb-3"><?= htmlspecialchars($meta[$type]['title']) ?></p>
                    <?php if ($submissionId): ?>
                        <a href="/assessment/result/<?= (int) $submissionId ?>" class="btn btn-primary btn-sm mt-auto align-self-start">Lihat Hasil</a>
                    <?php else: ?>
                        <p class="text-muted small mb-0 mt-auto">Tidak ada jawaban yang tersimpan.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Assessment Selesai';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
