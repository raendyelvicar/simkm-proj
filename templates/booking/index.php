<?php
$statusBadge = [
    'Pending' => 'text-bg-warning',
    'Confirmed' => 'text-bg-success',
    'Completed' => 'text-bg-primary',
    'Cancelled' => 'text-bg-secondary',
    'No Show' => 'text-bg-danger',
];
ob_start();
?>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="h4 mb-1">📅 Booking Saya</h3>
            <p class="text-muted mb-0">Riwayat permintaan booking konsultasi dengan konselor.</p>
        </div>
        <a href="/counselor" class="btn btn-primary">+ Ajukan Booking</a>
    </div>

    <?php if (!empty($bookings)): ?>
        <div class="table-responsive">
            <table class="table align-middle">
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
                            <td><?= htmlspecialchars($booking['konselor_nama'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($booking['tanggal'] ? date('d M Y', strtotime($booking['tanggal'])) : '-') ?></td>
                            <td><?= htmlspecialchars(substr($booking['jam_mulai'], 0, 5)) ?> - <?= htmlspecialchars(substr($booking['jam_selesai'], 0, 5)) ?></td>
                            <td>
                                <span class="badge <?= $statusBadge[$booking['status']] ?? 'text-bg-secondary' ?>"><?= htmlspecialchars($booking['status']) ?></span>
                                <?php if ($booking['status'] === 'Confirmed' && !empty($booking['monitoring_end'])): ?>
                                    <div class="text-muted small">Monitoring s/d <?= htmlspecialchars(date('d M Y', strtotime($booking['monitoring_end']))) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <?php $monitoringActive = $booking['status'] === 'Confirmed' && !empty($booking['monitoring_end']) && $booking['monitoring_end'] >= date('Y-m-d'); ?>
                                    <?php if ($monitoringActive): ?>
                                        <a href="/chat/<?= urlencode($booking['konselor_user_id']) ?>" class="btn btn-sm btn-primary">💬 Chat</a>
                                    <?php endif; ?>
                                    <?php if (in_array($booking['status'], ['Pending', 'Confirmed'], true)): ?>
                                        <form method="post" action="/bookings/<?= urlencode($booking['booking_id']) ?>/cancel"
                                            onsubmit="return confirm('Batalkan booking ini?');" style="display:inline;">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Batal</button>
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
        <div class="text-center py-5">
            <div class="mb-2" style="font-size:2rem;">📅</div>
            <p class="text-muted">Belum ada booking. Pilih konselor untuk mengajukan booking konsultasi.</p>
            <a href="/counselor" class="btn btn-primary">Lihat Daftar Konselor</a>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Booking Saya';
require __DIR__ . '/../layouts/index.php';