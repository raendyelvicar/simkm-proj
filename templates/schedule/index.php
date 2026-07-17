<?php ob_start(); ?>

<div class="schedule-page">
    <div class="page-head">
        <div>
            <h1>📅 Jadwal Konsultasi</h1>
            <p>Tanggal yang bisa dibooking mahasiswa. Jadwal baru ditambahkan oleh admin.</p>
        </div>
        <span class="schedule-count"><?= count($slots) ?> Jadwal</span>
    </div>

    <div class="schedule-card">
        <?php if (!empty($slots)): ?>
            <div class="schedule-table-scroll">
                <table class="schedule-table">
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
                                    <form method="post" action="/schedule/<?= urlencode($slot['jadwal_id']) ?>/toggle" style="display:inline;">
                                        <button type="submit" class="btn-schedule">
                                            <?= $slot['status_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="schedule-empty">
                <div class="schedule-empty-icon">📅</div>
                <p>Belum ada jadwal konsultasi. Hubungi admin untuk menambahkan jadwal agar mahasiswa bisa mengajukan booking.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Jadwal Konsultasi';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
