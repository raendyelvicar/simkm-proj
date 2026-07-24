<?php
$queryParams = $_GET;
unset($queryParams['page']);
ob_start();
?>

<div class="bcancel-admin-page">
    <div class="page-head">
        <div>
            <h1>🚫 Persetujuan Pembatalan Booking</h1>
            <p>Tinjau permintaan pembatalan booking dari mahasiswa sebelum booking-nya benar-benar dibatalkan.</p>
        </div>
        <span class="bcancel-admin-count"><?= (int) $total ?> Menunggu</span>
    </div>

    <div class="bcancel-admin-card" style="padding:16px 20px;margin-bottom:16px;">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Cari Nama / NPM</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Cari mahasiswa...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                <a href="/admin/booking-cancellations" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="bcancel-admin-card">
        <?php if (!empty($requests)): ?>
            <div class="bcancel-admin-table-scroll">
                <table class="bcancel-admin-table">
                    <thead>
                        <tr>
                            <th><?= sort_link('student_name', 'Mahasiswa', $sort, $dir, $queryParams) ?></th>
                            <th>Konselor</th>
                            <th><?= sort_link('date', 'Jadwal Booking', $sort, $dir, $queryParams) ?></th>
                            <th>Alasan Pembatalan</th>
                            <th><?= sort_link('requested_at', 'Diajukan Pada', $sort, $dir, $queryParams) ?></th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $req): ?>
                            <tr>
                                <td>
                                    <div><?= htmlspecialchars($req['student_name'] ?: '-') ?></div>
                                    <div class="bcancel-admin-sub"><?= htmlspecialchars($req['student_number'] ?: '-') ?></div>
                                </td>
                                <td><?= htmlspecialchars($req['counselor_name'] ?: '-') ?></td>
                                <td>
                                    <?= htmlspecialchars($req['date'] ? date('d M Y', strtotime($req['date'])) : '-') ?>
                                    <div class="bcancel-admin-sub"><?= htmlspecialchars(substr($req['start_time'], 0, 5)) ?> - <?= htmlspecialchars(substr($req['end_time'], 0, 5)) ?></div>
                                </td>
                                <td class="bcancel-admin-reason"><?= htmlspecialchars($req['reason'] ?: '-') ?></td>
                                <td><?= $req['created_at'] ? htmlspecialchars(date('d M Y H:i', strtotime($req['created_at']))) : '-' ?></td>
                                <td>
                                    <div class="bcancel-admin-actions">
                                        <form method="post" action="/admin/booking-cancellations/<?= urlencode($req['id']) ?>/approve"
                                            onsubmit="return confirm('Setujui pembatalan booking ini?');" style="display:inline;">
                                            <button type="submit" class="btn-bcancel-admin btn-bcancel-admin-approve">Setujui</button>
                                        </form>
                                        <button type="button" class="btn-bcancel-admin btn-bcancel-admin-reject"
                                            data-bs-toggle="modal" data-bs-target="#rejectModal<?= (int) $req['id'] ?>">Tolak</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3">
                <span class="text-muted small"><?= (int) $total ?> permintaan menunggu</span>
                <?= pagination_links($page, $totalPages, $queryParams) ?>
            </div>
        <?php else: ?>
            <div class="bcancel-admin-empty">
                <div class="bcancel-admin-empty-icon">🚫</div>
                <p>Tidak ada permintaan pembatalan booking yang menunggu persetujuan.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php foreach ($requests ?? [] as $req): ?>
        <div class="modal fade" id="rejectModal<?= (int) $req['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="post" action="/admin/booking-cancellations/<?= (int) $req['id'] ?>/reject" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tolak Pembatalan — <?= htmlspecialchars($req['student_name'] ?: '-') ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">Booking ini akan kembali ke status <strong><?= $req['previous_status'] === 'Confirmed' ? 'Terkonfirmasi' : 'Menunggu' ?></strong>.</p>
                        <div class="mb-0">
                            <label class="form-label">Catatan Admin (opsional)</label>
                            <textarea name="admin_notes" class="form-control" rows="3" placeholder="Alasan menolak permintaan pembatalan ini"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak Pembatalan</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Persetujuan Pembatalan Booking';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../../layouts/index.php';
