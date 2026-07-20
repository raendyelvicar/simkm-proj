<?php ob_start(); ?>

<div class="selfhelp-page">
    <div class="page-head">
        <div>
            <h1>🙏 Gratitude & Self Reflection</h1>
            <p>Kumpulan rasa syukur dan refleksi diri yang sudah kamu tulis lewat Diary Terstruktur.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/diary/create" class="btn btn-primary btn-sm">+ Tulis Diary Baru</a>
            <a href="/self-help" class="btn btn-outline-secondary btn-sm">&larr; Kembali</a>
        </div>
    </div>

    <?php if (empty($entries)): ?>
        <div class="assess-card assess-card-body text-center">
            <p class="mb-2">Kamu belum menulis gratitude atau self reflection apa pun.</p>
            <p class="text-muted small mb-3">Setiap kali menulis Diary Terstruktur, kamu bisa mengisi bagian "🖊 Self Reflection" dan "🙏 Gratitude Journal" &mdash; isiannya akan muncul di sini.</p>
            <a href="/diary/create" class="btn btn-primary btn-sm">Tulis Diary Sekarang</a>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($entries as $entry): ?>
                <div class="col-md-6">
                    <div class="assess-card assess-card-body h-100" style="position:relative;">
                        <div class="text-muted small mb-2">
                            <?= htmlspecialchars(date('d F Y', strtotime($entry['entry_date']))) ?>
                        </div>

                        <?php if (!empty($entry['gratitude_list'])): ?>
                            <div class="mb-2">
                                <div class="fw-semibold small mb-1">🙏 Gratitude</div>
                                <ul class="mb-0 small">
                                    <?php foreach ($entry['gratitude_list'] as $g): ?>
                                        <li><?= htmlspecialchars($g) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($entry['self_reflection'])): ?>
                            <div>
                                <div class="fw-semibold small mb-1">🖊 Self Reflection</div>
                                <p class="small mb-0"><?= nl2br(htmlspecialchars($entry['self_reflection'])) ?></p>
                            </div>
                        <?php endif; ?>

                        <a href="/diary/<?= (int) $entry['id'] ?>" class="stretched-link"></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Gratitude & Self Reflection';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
