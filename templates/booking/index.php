<?php
$statusBadge = [
    'Pending' => 'booking-badge-yellow',
    'Confirmed' => 'booking-badge-green',
    'Completed' => 'booking-badge-blue',
    'Cancelled' => 'booking-badge-gray',
    'No Show' => 'booking-badge-red',
    'Cancellation Requested' => 'booking-badge-orange',
];
$statusLabels = [
    'Pending' => 'Menunggu',
    'Confirmed' => 'Terkonfirmasi',
    'Completed' => 'Selesai',
    'Cancelled' => 'Dibatalkan',
    'No Show' => 'Tidak Hadir',
    'Cancellation Requested' => 'Menunggu Persetujuan Pembatalan',
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
                    <?php foreach (['Pending', 'Confirmed', 'Completed', 'Cancelled', 'No Show', 'Cancellation Requested'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= $statusLabels[$s] ?></option>
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
                            <th><?= sort_link('counselor_name', 'Konselor', $sort, $dir, $queryParams) ?></th>
                            <th><?= sort_link('date', 'Tanggal', $sort, $dir, $queryParams) ?></th>
                            <th>Jam</th>
                            <th><?= sort_link('status', 'Status', $sort, $dir, $queryParams) ?></th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td class="booking-counselor"><?= htmlspecialchars($booking['counselor_name'] ?: '-') ?></td>
                                <td class="booking-date">
                                    <?= htmlspecialchars($booking['date'] ? date('d M Y', strtotime($booking['date'])) : '-') ?>
                                </td>
                                <td class="booking-time">
                                    <?= htmlspecialchars(substr($booking['start_time'], 0, 5)) ?> - <?= htmlspecialchars(substr($booking['end_time'], 0, 5)) ?>
                                </td>
                                <td>
                                    <span class="booking-badge <?= $statusBadge[$booking['status']] ?? 'booking-badge-gray' ?>">
                                        <?= htmlspecialchars($statusLabels[$booking['status']] ?? $booking['status']) ?>
                                    </span>
                                    <?php $wasConfirmed = in_array($booking['status'], ['Confirmed', 'Cancellation Requested'], true); ?>
                                    <?php if ($wasConfirmed && !empty($booking['monitoring_end'])): ?>
                                        <div class="booking-monitoring-note">Monitoring s/d <?= htmlspecialchars(date('d M Y', strtotime($booking['monitoring_end']))) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="booking-actions">
                                        <?php $monitoringActive = $wasConfirmed && !empty($booking['monitoring_end']) && $booking['monitoring_end'] >= date('Y-m-d'); ?>
                                        <?php if ($monitoringActive): ?>
                                            <a href="/chat/<?= urlencode($booking['konselor_user_id']) ?>" class="btn-booking btn-booking-primary btn-booking-sm">💬 Chat</a>
                                        <?php endif; ?>
                                        <?php if (in_array($booking['status'], ['Pending', 'Confirmed'], true)): ?>
                                            <button type="button" class="btn-booking btn-booking-danger btn-booking-sm"
                                                data-bs-toggle="modal" data-bs-target="#cancelModal<?= (int) $booking['booking_id'] ?>">Batal</button>
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

    <?php foreach ($bookings ?? [] as $booking): ?>
        <?php if (in_array($booking['status'], ['Pending', 'Confirmed'], true)): ?>
            <div class="modal fade" id="cancelModal<?= (int) $booking['booking_id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="post" action="/bookings/<?= (int) $booking['booking_id'] ?>/cancel" class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Batalkan Booking</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small">Permintaan pembatalan akan dikirim ke Admin untuk disetujui — booking ini belum benar-benar dibatalkan sampai disetujui.</p>
                            <div class="mb-0">
                                <label class="form-label">Alasan Pembatalan (opsional)</label>
                                <textarea name="reason" class="form-control" rows="3" placeholder="Ceritakan alasanmu membatalkan booking ini"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-danger">Kirim Permintaan Pembatalan</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Booking Saya';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
