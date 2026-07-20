<?php
$statusBadge = [
    'Pending' => 'booking-badge-yellow',
    'Confirmed' => 'booking-badge-green',
    'Completed' => 'booking-badge-blue',
    'Cancelled' => 'booking-badge-gray',
    'No Show' => 'booking-badge-red',
];
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

    <div class="booking-card">
        <?php if (!empty($bookings)): ?>
            <div class="booking-table-scroll">
                <table class="booking-table">
                    <thead>
                        <tr>
                            <th>Konselor</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Status</th>
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
        <?php else: ?>
            <div class="booking-empty">
                <div class="booking-empty-icon">📅</div>
                <p>Belum ada booking. Pilih konselor untuk mengajukan booking konsultasi.</p>
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
