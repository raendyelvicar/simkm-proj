<?php
$queryParams = $_GET;
unset($queryParams['page']);
ob_start();
?>

<div class="counselor-admin-page">
    <div class="page-head">
        <div>
            <h1>Jadwal Konsultasi</h1>
            <p><?= htmlspecialchars($counselor['name'] ?: 'Konselor') ?> &mdash; tambah dan kelola tanggal yang bisa dibooking mahasiswa.</p>
        </div>
        <a href="/admin/counselors" class="btn-counselor-admin btn-counselor-admin-ghost">&larr; Kembali</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="counselor-admin-alert counselor-admin-alert-error">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="counselor-admin-card" style="padding:20px 24px; margin-bottom:20px;">
        <form method="post" action="/admin/counselors/<?= urlencode($counselor['user_id']) ?>/schedule" class="row g-3">
            <div class="col-md-4">
                <label for="date" class="form-label">Tanggal</label>
                <input type="date" id="date" name="date" class="form-control" required
                    min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($old['date'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label for="start_time" class="form-label">Jam Mulai</label>
                <input type="time" id="start_time" name="start_time" class="form-control" required value="<?= htmlspecialchars($old['jamMulai'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label for="end_time" class="form-label">Jam Selesai</label>
                <input type="time" id="end_time" name="end_time" class="form-control" required value="<?= htmlspecialchars($old['jamSelesai'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label for="quota" class="form-label">Kuota</label>
                <input type="number" id="quota" name="quota" class="form-control" min="1" required value="<?= htmlspecialchars((string) ($old['quota'] ?? 10)) ?>">
            </div>
            <div class="col-12">
                <button type="submit" class="btn-counselor-admin btn-counselor-admin-primary">Tambah Jadwal</button>
            </div>
        </form>
    </div>

    <div class="counselor-admin-card" style="padding:16px 20px;margin-bottom:16px;">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="is_active" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="1" <?= ($filters['is_active'] ?? '') === '1' ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= ($filters['is_active'] ?? '') === '0' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                <a href="/admin/counselors/<?= urlencode($counselor['user_id']) ?>/schedule" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="counselor-admin-card">
        <?php if (!empty($slots)): ?>
            <table class="counselor-admin-table">
                <thead>
                    <tr>
                        <th><?= sort_link('date', 'Tanggal', $sort, $dir, $queryParams) ?></th>
                        <th><?= sort_link('start_time', 'Jam', $sort, $dir, $queryParams) ?></th>
                        <th>Kuota</th>
                        <th><?= sort_link('is_active', 'Status', $sort, $dir, $queryParams) ?></th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($slots as $slot): ?>
                        <tr>
                            <td><?= htmlspecialchars($slot['date'] ? date('d M Y', strtotime($slot['date'])) : '-') ?></td>
                            <td><?= htmlspecialchars(substr($slot['start_time'], 0, 5)) ?> - <?= htmlspecialchars(substr($slot['end_time'], 0, 5)) ?></td>
                            <td><?= (int) $slot['quota'] ?></td>
                            <td>
                                <?php if ($slot['is_active']): ?>
                                    <span class="status-pill status-pill-active">Aktif</span>
                                <?php else: ?>
                                    <span class="status-pill status-pill-inactive">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" action="/admin/counselors/<?= urlencode($counselor['user_id']) ?>/schedule/<?= urlencode($slot['schedule_id']) ?>/toggle" style="display:inline;">
                                    <button type="submit" class="btn-counselor-admin btn-counselor-admin-ghost btn-counselor-admin-sm">
                                        <?= $slot['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="d-flex justify-content-between align-items-center p-3">
                <span class="text-muted small"><?= (int) $total ?> jadwal ditemukan</span>
                <?= pagination_links($page, $totalPages, $queryParams) ?>
            </div>
        <?php else: ?>
            <div class="counselor-admin-empty">
                <div class="counselor-admin-empty-icon">📅</div>
                <p>Tidak ada jadwal yang cocok, atau belum ada jadwal untuk konselor ini. Tambahkan di atas.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Jadwal Konsultasi';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../../layouts/index.php';
