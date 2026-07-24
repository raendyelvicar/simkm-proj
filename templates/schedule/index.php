<?php
$queryParams = $_GET;
unset($queryParams['page']);
ob_start();
?>

<div class="schedule-page">
    <div class="page-head">
        <div>
            <h1>📅 Jadwal Konsultasi</h1>
            <p>Tanggal yang bisa dibooking mahasiswa. Jadwal baru ditambahkan oleh admin.</p>
        </div>
        <span class="schedule-count"><?= (int) $total ?> Jadwal</span>
    </div>

    <div class="schedule-card" style="padding:16px 20px;margin-bottom:16px;">
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
                <a href="/schedule" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="schedule-card">
        <?php if (!empty($slots)): ?>
            <div class="schedule-table-scroll">
                <table class="schedule-table">
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
                                    <form method="post" action="/schedule/<?= urlencode($slot['schedule_id']) ?>/toggle" style="display:inline;">
                                        <button type="submit" class="btn-schedule">
                                            <?= $slot['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3">
                <span class="text-muted small"><?= (int) $total ?> jadwal ditemukan</span>
                <?= pagination_links($page, $totalPages, $queryParams) ?>
            </div>
        <?php else: ?>
            <div class="schedule-empty">
                <div class="schedule-empty-icon">📅</div>
                <p>Tidak ada jadwal yang cocok, atau belum ada jadwal konsultasi. Hubungi admin untuk menambahkan jadwal agar mahasiswa bisa mengajukan booking.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Jadwal Konsultasi';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
