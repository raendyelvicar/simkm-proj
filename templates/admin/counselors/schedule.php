<?php ob_start(); ?>

<div class="konselor-admin-page">
    <div class="page-head">
        <div>
            <h1>Jadwal Konsultasi</h1>
            <p><?= htmlspecialchars($counselor['nama'] ?: 'Konselor') ?> &mdash; tambah dan kelola tanggal yang bisa dibooking mahasiswa.</p>
        </div>
        <a href="/admin/counselors" class="btn-konselor-admin btn-konselor-admin-ghost">&larr; Kembali</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="konselor-admin-alert konselor-admin-alert-error">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="konselor-admin-card" style="padding:20px 24px; margin-bottom:20px;">
        <form method="post" action="/admin/counselors/<?= urlencode($counselor['user_id']) ?>/schedule" class="row g-3">
            <div class="col-md-4">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" class="form-control" required
                    min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($old['tanggal'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label for="jam_mulai" class="form-label">Jam Mulai</label>
                <input type="time" id="jam_mulai" name="jam_mulai" class="form-control" required value="<?= htmlspecialchars($old['jamMulai'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label for="jam_selesai" class="form-label">Jam Selesai</label>
                <input type="time" id="jam_selesai" name="jam_selesai" class="form-control" required value="<?= htmlspecialchars($old['jamSelesai'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label for="kuota" class="form-label">Kuota</label>
                <input type="number" id="kuota" name="kuota" class="form-control" min="1" required value="<?= htmlspecialchars((string) ($old['kuota'] ?? 10)) ?>">
            </div>
            <div class="col-12">
                <button type="submit" class="btn-konselor-admin btn-konselor-admin-primary">Tambah Jadwal</button>
            </div>
        </form>
    </div>

    <div class="konselor-admin-card">
        <?php if (!empty($slots)): ?>
            <table class="konselor-admin-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Kuota</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($slots as $slot): ?>
                        <tr>
                            <td><?= htmlspecialchars($slot['tanggal'] ? date('d M Y', strtotime($slot['tanggal'])) : '-') ?></td>
                            <td><?= htmlspecialchars(substr($slot['jam_mulai'], 0, 5)) ?> - <?= htmlspecialchars(substr($slot['jam_selesai'], 0, 5)) ?></td>
                            <td><?= (int) $slot['kuota'] ?></td>
                            <td>
                                <?php if ($slot['status_aktif']): ?>
                                    <span class="status-pill status-pill-active">Aktif</span>
                                <?php else: ?>
                                    <span class="status-pill status-pill-inactive">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" action="/admin/counselors/<?= urlencode($counselor['user_id']) ?>/schedule/<?= urlencode($slot['jadwal_id']) ?>/toggle" style="display:inline;">
                                    <button type="submit" class="btn-konselor-admin btn-konselor-admin-ghost btn-konselor-admin-sm">
                                        <?= $slot['status_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="konselor-admin-empty">
                <div class="konselor-admin-empty-icon">📅</div>
                <p>Belum ada jadwal untuk konselor ini. Tambahkan di atas.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Jadwal Konsultasi';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../../layouts/index.php';
