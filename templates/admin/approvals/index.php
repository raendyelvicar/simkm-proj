<?php ob_start(); ?>

<div class="approval-admin-page">
    <div class="page-head">
        <div>
            <h1>✅ Persetujuan Akun</h1>
            <p>Tinjau pendaftaran mahasiswa baru sebelum akunnya bisa digunakan untuk login.</p>
        </div>
        <span class="approval-admin-count"><?= count($pending) ?> Menunggu</span>
    </div>

    <div class="approval-admin-card">
        <?php if (!empty($pending)): ?>
            <div class="approval-admin-table-scroll">
                <table class="approval-admin-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>NPM</th>
                            <th>Fakultas / Jurusan</th>
                            <th>Kontak</th>
                            <th>Daftar Pada</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending as $user): ?>
                            <tr>
                                <td>
                                    <div class="approval-admin-name">
                                        <div class="approval-admin-avatar">
                                            <?php if (!empty($user['profile'])): ?>
                                                <img src="<?= htmlspecialchars($user['profile']) ?>"
                                                    alt="<?= htmlspecialchars($user['nama'] ?: $user['username']) ?>"
                                                    onerror="this.remove()">
                                            <?php endif; ?>
                                            <?= htmlspecialchars(mb_strtoupper(mb_substr($user['nama'] ?: $user['username'], 0, 1))) ?>
                                        </div>
                                        <div>
                                            <div><?= htmlspecialchars($user['nama'] ?: '-') ?></div>
                                            <div class="approval-admin-sub"><?= htmlspecialchars($user['username']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($user['npm'] ?: '-') ?></td>
                                <td>
                                    <?= htmlspecialchars($user['fakultas'] ?: '-') ?>
                                    <div class="approval-admin-sub"><?= htmlspecialchars($user['jurusan'] ?: '-') ?></div>
                                </td>
                                <td>
                                    <?= htmlspecialchars($user['email'] ?: '-') ?>
                                    <div class="approval-admin-sub"><?= htmlspecialchars($user['no_hp'] ?: '-') ?></div>
                                </td>
                                <td><?= $user['created_at'] ? htmlspecialchars(date('d M Y', strtotime($user['created_at']))) : '-' ?></td>
                                <td>
                                    <div class="approval-admin-actions">
                                        <form method="post" action="/admin/approvals/<?= urlencode($user['id']) ?>/approve"
                                            onsubmit="return confirm('Setujui akun <?= htmlspecialchars(addslashes($user['nama'] ?: $user['username'])) ?>?');"
                                            style="display:inline;">
                                            <button type="submit" class="btn-approval-admin btn-approval-admin-approve">Setujui</button>
                                        </form>
                                        <form method="post" action="/admin/approvals/<?= urlencode($user['id']) ?>/reject"
                                            onsubmit="return confirm('Tolak akun <?= htmlspecialchars(addslashes($user['nama'] ?: $user['username'])) ?>?');"
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
