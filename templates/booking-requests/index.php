<?php
$statusLabel = [
    'Pending' => 'Pending',
    'Confirmed' => 'On Progress',
    'Completed' => 'Completed',
];
$statusPill = [
    'Pending' => 'status-pill-pending',
    'Confirmed' => 'status-pill-progress',
    'Completed' => 'status-pill-completed',
];
ob_start();
?>

<div class="booking-queue-page">
    <div class="page-head">
        <div>
            <h1>📥 Permintaan Booking</h1>
            <p>Konfirmasi booking untuk membuka akses chat, lalu tandai selesai setelah sesi berakhir.</p>
        </div>
        <span class="booking-queue-count"><?= count($bookings) ?> Booking</span>
    </div>

    <div class="booking-queue-card">
        <?php if (!empty($bookings)): ?>
            <div class="booking-queue-table-scroll">
                <table class="booking-queue-table">
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Keluhan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>
                                    <div><?= htmlspecialchars($booking['student_nama'] ?: '-') ?></div>
                                    <div class="booking-queue-sub"><?= htmlspecialchars($booking['student_npm'] ?: '-') ?></div>
                                </td>
                                <td><?= htmlspecialchars($booking['tanggal'] ? date('d M Y', strtotime($booking['tanggal'])) : '-') ?></td>
                                <td><?= htmlspecialchars(substr($booking['jam_mulai'], 0, 5)) ?> - <?= htmlspecialchars(substr($booking['jam_selesai'], 0, 5)) ?></td>
                                <td style="max-width:280px; white-space:normal;"><?= htmlspecialchars($booking['keluhan'] ?: '-') ?></td>
                                <td>
                                    <span class="status-pill <?= $statusPill[$booking['status']] ?? 'status-pill-completed' ?>"><?= htmlspecialchars($statusLabel[$booking['status']] ?? $booking['status']) ?></span>
                                    <?php if ($booking['status'] === 'Confirmed' && !empty($booking['monitoring_end'])): ?>
                                        <div class="booking-queue-sub">s/d <?= htmlspecialchars(date('d M Y', strtotime($booking['monitoring_end']))) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($booking['status'] === 'Pending'): ?>
                                        <div class="booking-queue-actions">
                                            <form method="post" action="/booking-requests/<?= urlencode($booking['booking_id']) ?>/confirm" class="booking-queue-inline-form">
                                                <input type="number" name="durasi_hari" value="30" min="1" max="365"
                                                    class="booking-queue-input-sm" title="Durasi monitoring (hari)">
                                                <button type="submit" class="btn-booking-queue btn-booking-queue-success">Konfirmasi</button>
                                            </form>
                                            <form method="post" action="/booking-requests/<?= urlencode($booking['booking_id']) ?>/reject"
                                                onsubmit="return confirm('Tolak booking ini?');" style="display:inline;">
                                                <button type="submit" class="btn-booking-queue btn-booking-queue-danger">Tolak</button>
                                            </form>
                                        </div>
                                    <?php elseif ($booking['status'] === 'Confirmed'): ?>
                                        <div class="booking-queue-actions">
                                            <form method="post" action="/booking-requests/<?= urlencode($booking['booking_id']) ?>/extend" class="booking-queue-inline-form">
                                                <input type="number" name="tambah_hari" value="7" min="1" max="365"
                                                    class="booking-queue-input-sm" title="Tambah hari">
                                                <button type="submit" class="btn-booking-queue btn-booking-queue-ghost">Perpanjang</button>
                                            </form>
                                            <form method="post" action="/booking-requests/<?= urlencode($booking['booking_id']) ?>/complete"
                                                onsubmit="return confirm('Tandai booking ini selesai? Akses chat dan berbagi diary mahasiswa untuk booking ini akan ditutup.');" style="display:inline;">
                                                <button type="submit" class="btn-booking-queue btn-booking-queue-primary">Tandai Selesai</button>
                                            </form>
                                            <form method="post" action="/booking-requests/<?= urlencode($booking['booking_id']) ?>/no-show"
                                                onsubmit="return confirm('Tandai mahasiswa tidak hadir?');" style="display:inline;">
                                                <button type="submit" class="btn-booking-queue btn-booking-queue-danger">Tidak Hadir</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="booking-queue-sub">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="booking-queue-empty">
                <div class="booking-queue-empty-icon">📥</div>
                <p>Belum ada booking.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Permintaan Booking';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
