<?php
$queryParams = $_GET;
unset($queryParams['page']);
ob_start();
?>

<div class="approval-admin-page">
    <div class="page-head">
        <div>
            <h1>✅ Persetujuan Akun</h1>
            <p>Tinjau pendaftaran mahasiswa baru sebelum akunnya bisa digunakan untuk login.</p>
        </div>
        <span class="approval-admin-count"><?= (int) $total ?> Menunggu</span>
    </div>

    <div class="approval-admin-card" style="padding:16px 20px;margin-bottom:16px;">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Cari Nama / NPM</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Cari...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                <a href="/admin/approvals" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="approval-admin-card">
        <?php if (!empty($pending)): ?>
            <div class="approval-admin-table-scroll">
                <table class="approval-admin-table">
                    <thead>
                        <tr>
                            <th><?= sort_link('name', 'Nama', $sort, $dir, $queryParams) ?></th>
                            <th>NPM</th>
                            <th>Fakultas / Jurusan</th>
                            <th>Kontak</th>
                            <th><?= sort_link('created_at', 'Daftar Pada', $sort, $dir, $queryParams) ?></th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending as $user): ?>
                            <tr>
                                <td>
                                    <div class="approval-admin-name">
                                        <div class="approval-admin-avatar">
                                            <?php $photo = profile_photo_url($user['profile']); ?>
                                            <?php if ($photo): ?>
                                                <img src="<?= htmlspecialchars($photo) ?>"
                                                    alt="<?= htmlspecialchars($user['name'] ?: $user['username']) ?>"
                                                    onerror="this.remove()">
                                            <?php endif; ?>
                                            <?= htmlspecialchars(mb_strtoupper(mb_substr($user['name'] ?: $user['username'], 0, 1))) ?>
                                        </div>
                                        <div>
                                            <div><?= htmlspecialchars($user['name'] ?: '-') ?></div>
                                            <div class="approval-admin-sub"><?= htmlspecialchars($user['username']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($user['student_number'] ?: '-') ?></td>
                                <td>
                                    <?= htmlspecialchars($user['faculty'] ?: '-') ?>
                                    <div class="approval-admin-sub"><?= htmlspecialchars($user['major'] ?: '-') ?></div>
                                </td>
                                <td>
                                    <?= htmlspecialchars($user['email'] ?: '-') ?>
                                    <div class="approval-admin-sub"><?= htmlspecialchars($user['phone_number'] ?: '-') ?></div>
                                </td>
                                <td><?= $user['created_at'] ? htmlspecialchars(date('d M Y', strtotime($user['created_at']))) : '-' ?></td>
                                <td>
                                    <div class="approval-admin-actions">
                                        <form method="post" action="/admin/approvals/<?= urlencode($user['id']) ?>/approve"
                                            onsubmit="return confirm('Setujui akun <?= htmlspecialchars(addslashes($user['name'] ?: $user['username'])) ?>?');"
                                            style="display:inline;">
                                            <button type="submit" class="btn-approval-admin btn-approval-admin-approve">Setujui</button>
                                        </form>
                                        <form method="post" action="/admin/approvals/<?= urlencode($user['id']) ?>/reject"
                                            onsubmit="return confirm('Tolak akun <?= htmlspecialchars(addslashes($user['name'] ?: $user['username'])) ?>?');"
                                            style="display:inline;">
                                            <button type="submit" class="btn-approval-admin btn-approval-admin-reject">Tolak</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3">
                <span class="text-muted small"><?= (int) $total ?> akun menunggu</span>
                <?= pagination_links($page, $totalPages, $queryParams) ?>
            </div>
        <?php else: ?>
            <div class="approval-admin-empty">
                <div class="approval-admin-empty-icon">✅</div>
                <p>Tidak ada pendaftaran yang menunggu persetujuan.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Persetujuan Akun';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../../layouts/index.php';
