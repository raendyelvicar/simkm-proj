<?php ob_start(); ?>

<div class="activity-page">
    <div class="page-head">
        <div>
            <h1>Tambah Aktivitas</h1>
            <p>Rencanakan satu aktivitas kecil yang menyenangkan untuk dilakukan.</p>
        </div>
        <a href="/self-help/activities" class="btn-activity btn-activity-ghost">&larr; Kembali</a>
    </div>

    <div class="activity-detail">
        <?php if (!empty($errors)): ?>
            <div class="activity-alert activity-alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/self-help/activities" class="activity-form">
            <div class="field">
                <label for="title">Nama Aktivitas</label>
                <p class="field-hint">Contoh: Jalan pagi 15 menit, menelepon teman, menonton film favorit.</p>
                <input type="text" id="title" name="title" required
                    value="<?= htmlspecialchars($old['title'] ?? '') ?>">
            </div>

            <div class="field">
                <label for="description">Catatan (opsional)</label>
                <textarea id="description" name="description"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
            </div>

            <div class="field">
                <label for="planned_date">Tanggal Rencana</label>
                <input type="date" id="planned_date" name="planned_date"
                    value="<?= htmlspecialchars($old['planned_date'] ?? date('Y-m-d')) ?>" required>
            </div>

            <div class="field">
                <label for="mood_before">Mood Sekarang (opsional)</label>
                <select id="mood_before" name="mood_before">
                    <option value="">Pilih mood...</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= (int) ($old['mood_before'] ?? 0) === $i ? 'selected' : '' ?>><?= $i ?> / 5</option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="activity-form-actions">
                <button type="submit" class="btn-activity btn-activity-primary">Simpan Rencana</button>
                <a href="/self-help/activities" class="btn-activity btn-activity-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Tambah Aktivitas';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../../layouts/index.php';
