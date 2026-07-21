<?php ob_start(); ?>

<div class="assess-page">
    <div class="page-head">
        <div>
            <h1>Mulai Self-Assessment</h1>
            <p>Kamu akan mengisi dua instrumen secara berurutan dalam satu sesi waktu terbatas.</p>
        </div>
        <a href="/assessment" class="btn btn-outline-secondary btn-sm">&larr; Kembali</a>
    </div>

    <div class="assess-card assess-card-body mb-3">
        <?php if (!empty($grant)): ?>
            <div class="alert alert-success mb-4">
                ✅ Direkomendasikan oleh <strong><?= htmlspecialchars($grant['konselor_nama']) ?></strong> pada <?= htmlspecialchars(date('d M Y', strtotime($grant['granted_at']))) ?>.
            </div>
        <?php endif; ?>
        <div class="alert alert-warning mb-4">
            ⏱️ Sesi ini memiliki batas waktu <strong><?= (int) $timeLimitMinutes ?> menit</strong> untuk mengisi kedua instrumen. Jika waktu habis, jawaban yang sudah kamu isi akan otomatis dikirim.
        </div>

        <?php foreach ($meta as $type => $m): ?>
            <div class="mb-4">
                <h5 class="mb-1"><?= (int) ($type === 'bdi2' ? 1 : 2) ?>. <?= htmlspecialchars($m['short_title']) ?></h5>
                <p class="text-muted small mb-1"><?= htmlspecialchars($m['title']) ?></p>
                <p class="mb-0"><?= htmlspecialchars($m['description']) ?></p>
            </div>
        <?php endforeach; ?>

        <div class="alert alert-info mb-4">
            Tidak ada jawaban benar atau salah. Hasil assessment ini bukan merupakan diagnosis medis, melainkan gambaran awal berdasarkan jawaban yang kamu berikan. Kamu dapat berpindah antar pertanyaan bebas selama sesi berlangsung.
        </div>

        <form method="post" action="/assessment/session">
            <button type="submit" class="btn btn-primary">Mulai Sekarang</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Mulai Self-Assessment';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
