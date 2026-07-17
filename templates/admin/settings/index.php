<?php ob_start(); ?>

<div class="card p-4">
    <h3 class="h4 mb-1">⚙️ Pengaturan Sistem</h3>
    <p class="text-muted mb-4">Konfigurasi berlaku untuk sesi assessment yang baru dimulai — sesi yang sedang berjalan tidak terpengaruh.</p>

    <form method="post" action="/admin/settings" class="row g-3" style="max-width:420px;">
        <div class="col-12">
            <label for="assessment_time_limit_minutes" class="form-label">Batas Waktu Pengisian Assessment (menit)</label>
            <input type="number" min="1" max="240" name="assessment_time_limit_minutes" id="assessment_time_limit_minutes"
                class="form-control" value="<?= (int) $timeLimitMinutes ?>" required>
            <div class="form-text">Berlaku untuk satu sesi gabungan BDI-II + PWB (1–240 menit).</div>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Pengaturan Sistem';
require __DIR__ . '/../../layouts/index.php';
