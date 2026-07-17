<?php ob_start(); ?>

<div class="counselor-page">
    <a href="/counselor/<?= urlencode($counselor['user_id']) ?>" class="counselor-back">&larr; Kembali ke profil konselor</a>

    <div class="counselor-detail" style="max-width:640px;">
        <div class="counselor-detail-head">
            <div class="counselor-avatar">
                <?php $photo = $counselor['foto_profil'] ?: $counselor['profile_image']; ?>
                <?php if (!empty($photo)): ?>
                    <img src="<?= htmlspecialchars($photo) ?>"
                        alt="<?= htmlspecialchars($counselor['nama']) ?>"
                        onerror="this.remove()">
                <?php endif; ?>
                <span class="counselor-avatar-initial"><?= htmlspecialchars(mb_strtoupper(mb_substr($counselor['nama'] !== '' ? $counselor['nama'] : '?', 0, 1))) ?></span>
            </div>
            <div>
                <h1><?= htmlspecialchars($counselor['nama'] !== '' ? $counselor['nama'] : 'Konselor') ?></h1>
                <p class="text-muted mb-0">Ajukan booking konsultasi. Chat akan terbuka setelah konselor mengonfirmasi.</p>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($slots)): ?>
            <div class="alert alert-warning mb-0">Konselor ini belum membuka jadwal konsultasi. Coba lagi nanti.</div>
        <?php else: ?>
            <form method="post" action="/bookings">
                <input type="hidden" name="counselor_id" value="<?= (int) $counselor['user_id'] ?>">

                <div class="mb-3">
                    <label for="jadwal_id" class="form-label">Tanggal &amp; Jam</label>
                    <select id="jadwal_id" name="jadwal_id" class="form-select" required>
                        <option value="">Pilih tanggal tersedia</option>
                        <?php foreach ($slots as $slot): ?>
                            <option value="<?= (int) $slot['jadwal_id'] ?>" <?= (int) ($old['jadwal_id'] ?? 0) === (int) $slot['jadwal_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($slot['tanggal'] ? date('d M Y', strtotime($slot['tanggal'])) : '-') ?>, <?= htmlspecialchars(substr($slot['jam_mulai'], 0, 5)) ?>-<?= htmlspecialchars(substr($slot['jam_selesai'], 0, 5)) ?> (sisa <?= (int) $slot['sisa_kuota'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="keluhan" class="form-label">Keluhan (opsional)</label>
                    <textarea id="keluhan" name="keluhan" class="form-control" rows="3"><?= htmlspecialchars($old['keluhan'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-counselor btn-counselor-primary">Ajukan Booking</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Ajukan Booking';
$extraStyles = require __DIR__ . '/../counselor/_styles.php';
require __DIR__ . '/../layouts/index.php';