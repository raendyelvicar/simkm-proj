<?php ob_start(); ?>

<div class="counselor-page">
    <div class="page-head">
        <div>
            <h1><?= htmlspecialchars($entry['student_nama'] ?: 'Mahasiswa') ?></h1>
            <p>
                <?= htmlspecialchars($entry['student_npm'] ?: '-') ?>
                &middot;
                <?= htmlspecialchars($entry['entry_date'] ? date('d M Y', strtotime($entry['entry_date'])) : '-') ?>
            </p>
        </div>
        <a href="/shared-diaries" class="btn-diary btn-diary-ghost">&larr; Kembali</a>
    </div>

    <div class="diary-card">
        <div class="diary-card-body">

            <span class="diary-badge <?= diary_intensity_badge_class((int) $entry['intensitas_emosi']) ?>">
                Intensitas Emosi: <?= (int) $entry['intensitas_emosi'] ?> / 5
            </span>

            <div class="diary-section">
                <h5>1. Situasi</h5>
                <div class="diary-content-text"><?= nl2br(htmlspecialchars($entry['situasi'] ?? '')) ?></div>
            </div>

            <div class="diary-section">
                <h5>2. Pikiran Pertama (Automatic Thought)</h5>
                <div class="diary-content-text"><?= nl2br(htmlspecialchars($entry['pikiran_awal'] ?? '')) ?></div>
            </div>

            <div class="diary-section">
                <h5>3. Emosi yang Dirasakan</h5>
                <div class="diary-checkbox-group diary-checkbox-group-readonly">
                    <?php foreach (($entry['emosi_list'] ?? []) as $emosi): ?>
                        <span class="diary-checkbox-pill diary-checkbox-pill-active"><?= htmlspecialchars($emosi) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($entry['emosi_lainnya'])): ?>
                    <p class="field-hint">Lainnya: <?= htmlspecialchars($entry['emosi_lainnya']) ?></p>
                <?php endif; ?>
            </div>

            <div class="diary-section">
                <h5>4. Reaksi Fisik</h5>
                <div class="diary-checkbox-group diary-checkbox-group-readonly">
                    <?php foreach (($entry['reaksi_fisik_list'] ?? []) as $reaksi): ?>
                        <span class="diary-checkbox-pill diary-checkbox-pill-active"><?= htmlspecialchars($reaksi) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($entry['reaksi_fisik_lainnya'])): ?>
                    <p class="field-hint">Lainnya: <?= htmlspecialchars($entry['reaksi_fisik_lainnya']) ?></p>
                <?php endif; ?>
            </div>

            <div class="diary-section">
                <h5>5. Perilaku</h5>
                <div class="diary-content-text"><?= nl2br(htmlspecialchars($entry['perilaku'] ?? '')) ?></div>
            </div>

            <?php if (!empty($entry['self_reflection'])): ?>
                <div class="diary-section">
                    <h5>🖊 Self Reflection</h5>
                    <div class="diary-content-text"><?= nl2br(htmlspecialchars($entry['self_reflection'])) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($entry['gratitude_list'])): ?>
                <div class="diary-section">
                    <h5>🙏 Gratitude Journal</h5>
                    <ul class="diary-gratitude-view">
                        <?php foreach ($entry['gratitude_list'] as $item): ?>
                            <li><?= htmlspecialchars($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($entry['rencana_besok'])): ?>
                <div class="diary-section">
                    <h5>🎯 Rencana Besok</h5>
                    <div class="diary-content-text"><?= nl2br(htmlspecialchars($entry['rencana_besok'])) ?></div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Diary Dibagikan';
$extraStyles = require __DIR__ . '/_styles.php';
$extraStyles .= require __DIR__ . '/../diary/_styles.php';
require __DIR__ . '/../layouts/index.php';
