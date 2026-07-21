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
$queryParams = $_GET;
unset($queryParams['page']);
ob_start();
?>

<div class="booking-queue-page">
    <div class="page-head">
        <div>
            <h1>📥 Permintaan Booking</h1>
            <p>Konfirmasi booking untuk membuka akses chat, lalu tandai selesai setelah sesi berakhir.</p>
        </div>
        <span class="booking-queue-count"><?= (int) $total ?> Booking</span>
    </div>

    <div class="booking-queue-card" style="padding:16px 20px;margin-bottom:16px;">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Cari Mahasiswa</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Cari nama/NPM...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>
                <a href="/booking-requests" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="booking-queue-card">
        <?php if (!empty($bookings)): ?>
            <div class="booking-queue-table-scroll">
                <table class="booking-queue-table">
                    <thead>
                        <tr>
                            <th><?= sort_link('student_nama', 'Mahasiswa', $sort, $dir, $queryParams) ?></th>
                            <th><?= sort_link('tanggal', 'Tanggal', $sort, $dir, $queryParams) ?></th>
                            <th>Jam</th>
                            <th>Keluhan</th>
                            <th><?= sort_link('status', 'Status', $sort, $dir, $queryParams) ?></th>
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
                                            <button type="button" class="btn-booking-queue btn-booking-queue-primary"
                                                data-bs-toggle="modal" data-bs-target="#completeModal<?= (int) $booking['booking_id'] ?>">Tandai Selesai</button>
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
            <div class="d-flex justify-content-between align-items-center p-3">
                <span class="text-muted small"><?= (int) $total ?> booking ditemukan</span>
                <?= pagination_links($page, $totalPages, $queryParams) ?>
            </div>
        <?php else: ?>
            <div class="booking-queue-empty">
                <div class="booking-queue-empty-icon">📥</div>
                <p>Tidak ada booking yang cocok, atau belum ada booking.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php foreach ($bookings as $booking): ?>
        <?php if ($booking['status'] === 'Confirmed'): ?>
            <div class="modal fade" id="completeModal<?= (int) $booking['booking_id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="post" action="/booking-requests/<?= (int) $booking['booking_id'] ?>/complete" class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Tandai Selesai — <?= htmlspecialchars($booking['student_nama'] ?: '-') ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small">Akses chat dan berbagi diary mahasiswa untuk booking ini akan ditutup. Catatan berikut akan tampil pada Laporan Konseling.</p>
                            <div class="mb-3">
                                <label class="form-label">Catatan Konselor</label>
                                <textarea name="catatan_konselor" class="form-control" rows="3" placeholder="Ringkasan sesi konseling"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Rekomendasi</label>
                                <textarea name="rekomendasi" class="form-control" rows="2" placeholder="Rekomendasi untuk mahasiswa"></textarea>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Tindak Lanjut</label>
                                <textarea name="tindak_lanjut" class="form-control" rows="2" placeholder="Rencana tindak lanjut (opsional)"></textarea>
                            </div>
                            <div class="form-check mt-3">
                                <input type="checkbox" class="form-check-input" id="reassess<?= (int) $booking['booking_id'] ?>" name="recommend_reassessment" value="1">
                                <label class="form-check-label" for="reassess<?= (int) $booking['booking_id'] ?>">
                                    Rekomendasikan Assessment Ulang
                                </label>
                                <div class="form-text">Mahasiswa hanya bisa mengisi self-assessment berikutnya setelah direkomendasikan oleh konselor. Centang untuk membuka satu kesempatan pengisian ulang.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Tandai Selesai</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Permintaan Booking';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
