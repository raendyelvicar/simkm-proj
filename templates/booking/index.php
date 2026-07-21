<?php
$statusBadge = [
    'Pending' => 'booking-badge-yellow',
    'Confirmed' => 'booking-badge-green',
    'Completed' => 'booking-badge-blue',
    'Cancelled' => 'booking-badge-gray',
    'No Show' => 'booking-badge-red',
];
$queryParams = $_GET;
unset($queryParams['page']);
ob_start();
?>

<div class="booking-page">
    <div class="page-head">
        <div>
            <h1>📅 Booking Saya</h1>
            <p>Riwayat permintaan booking konsultasi dengan konselor.</p>
        </div>
        <a href="/counselor" class="btn-booking btn-booking-primary">+ Ajukan Booking</a>
    </div>

    <div class="booking-card" style="padding:16px 20px;margin-bottom:16px;">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <?php foreach (['Pending', 'Confirmed', 'Completed', 'Cancelled', 'No Show'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                <a href="/bookings" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="booking-card">
        <?php if (!empty($bookings)): ?>
            <div class="booking-table-scroll">
                <table class="booking-table">
                    <thead>
                        <tr>
                            <th><?= sort_link('konselor_nama', 'Konselor', $sort, $dir, $queryParams) ?></th>
                            <th><?= sort_link('tanggal', 'Tanggal', $sort, $dir, $queryParams) ?></th>
                            <th>Jam</th>
                            <th><?= sort_link('status', 'Status', $sort, $dir, $queryParams) ?></th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td class="booking-konselor"><?= htmlspecialchars($booking['konselor_nama'] ?: '-') ?></td>
                                <td class="booking-date">
                                    <?= htmlspecialchars($booking['tanggal'] ? date('d M Y', strtotime($booking['tanggal'])) : '-') ?>
                                </td>
                                <td class="booking-time">
                                    <?= htmlspecialchars(substr($booking['jam_mulai'], 0, 5)) ?> - <?= htmlspecialchars(substr($booking['jam_selesai'], 0, 5)) ?>
                                </td>
                                <td>
                                    <span class="booking-badge <?= $statusBadge[$booking['status']] ?? 'booking-badge-gray' ?>">
                                        <?= htmlspecialchars($booking['status']) ?>
                                    </span>
                                    <?php if ($booking['status'] === 'Confirmed' && !empty($booking['monitoring_end'])): ?>
                                        <div class="booking-monitoring-note">Monitoring s/d <?= htmlspecialchars(date('d M Y', strtotime($booking['monitoring_end']))) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="booking-actions">
                                        <?php $monitoringActive = $booking['status'] === 'Confirmed' && !empty($booking['monitoring_end']) && $booking['monitoring_end'] >= date('Y-m-d'); ?>
                                        <?php if ($monitoringActive): ?>
                                            <a href="/chat/<?= urlencode($booking['konselor_user_id']) ?>" class="btn-booking btn-booking-primary btn-booking-sm">💬 Chat</a>
                                        <?php endif; ?>
                                        <?php if (in_array($booking['status'], ['Pending', 'Confirmed'], true)): ?>
                                            <form method="post" action="/bookings/<?= urlencode($booking['booking_id']) ?>/cancel"
                                                onsubmit="return confirm('Batalkan booking ini?');">
                                                <button type="submit" class="btn-booking btn-booking-danger btn-booking-sm">Batal</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3">
                <span class="text-muted small"><?= (int) $total ?> booking ditemukan</span>
                <?= pagination_links($page, $totalPages, $queryParams) ?>
            </div>
        <?php else: ?>
            <div class="booking-empty">
                <div class="booking-empty-icon">📅</div>
                <p>Tidak ada booking yang cocok, atau belum ada booking. Pilih konselor untuk mengajukan booking konsultasi.</p>
                <a href="/counselor" class="btn-booking btn-booking-primary">Lihat Daftar Konselor</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Booking Saya';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
