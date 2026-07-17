<?php ob_start(); ?>

<div class="konselor-admin-page">
    <div class="page-head">
        <div>
            <h1>Kelola Konselor</h1>
            <p>Tambah, ubah, atau nonaktifkan akun konselor.</p>
        </div>
        <a href="/admin/counselors/create" class="btn-konselor-admin btn-konselor-admin-primary">+ Tambah Konselor</a>
    </div>

    <div class="konselor-admin-card">
        <?php if (!empty($counselors)): ?>
            <table class="konselor-admin-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username / Email</th>
                        <th>Profesi</th>
                        <th>Spesialisasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($counselors as $counselor): ?>
                        <tr>
                            <td>
                                <div class="konselor-admin-name">
                                    <div class="konselor-admin-avatar">
                                        <?php if (!empty($counselor['foto_profil']) || !empty($counselor['profile_image'])): ?>
                                            <img src="<?= htmlspecialchars($counselor['foto_profil'] ?: $counselor['profile_image']) ?>"
                                                alt="<?= htmlspecialchars($counselor['nama']) ?>"
                                                onerror="this.remove()">
                                        <?php endif; ?>
                                        <?= htmlspecialchars(mb_strtoupper(mb_substr($counselor['nama'] !== '' ? $counselor['nama'] : '?', 0, 1))) ?>
                                    </div>
                                    <span><?= htmlspecialchars($counselor['nama'] !== '' ? $counselor['nama'] : '-') ?></span>
                                </div>
                            </td>
                            <td>
                                <?= htmlspecialchars($counselor['username']) ?><br>
                                <span style="color:var(--muted); font-size:0.8rem;"><?= htmlspecialchars($counselor['email']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($counselor['profesi'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($counselor['spesialisasi'] ?: '-') ?></td>
                            <td>
                                <?php if (!$counselor['has_profile']): ?>
                                    <span class="status-pill status-pill-incomplete">Profil belum lengkap</span>
                                <?php elseif ($counselor['status_aktif']): ?>
                                    <span class="status-pill status-pill-active">Aktif</span>
                                <?php else: ?>
                                    <span class="status-pill status-pill-inactive">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="konselor-admin-actions">
                                    <a href="/admin/counselors/<?= urlencode($counselor['user_id']) ?>/edit"
                                        class="btn-konselor-admin btn-konselor-admin-ghost btn-konselor-admin-sm">Edit</a>

                                    <?php if ($counselor['has_profile']): ?>
                                        <a href="/admin/counselors/<?= urlencode($counselor['user_id']) ?>/schedule"
                                            class="btn-konselor-admin btn-konselor-admin-ghost btn-konselor-admin-sm">Jadwal</a>
                                    <?php endif; ?>

                                    <?php if ($counselor['has_profile']): ?>
                                        <form method="post" action="/admin/counselors/<?= urlencode($counselor['user_id']) ?>/status"
                                            onsubmit="return confirm('<?= $counselor['status_aktif'] ? 'Nonaktifkan konselor ini? Ia tidak akan tampil di daftar konselor untuk mahasiswa.' : 'Aktifkan kembali konselor ini?' ?>');"
                                            style="display:inline;">
                                            <button type="submit" class="btn-konselor-admin btn-konselor-admin-danger btn-konselor-admin-sm">
                                                <?= $counselor['status_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="konselor-admin-empty">
                <div class="konselor-admin-empty-icon">🧑‍⚕️</div>
                <p>Belum ada akun konselor.</p>
                <a href="/admin/counselors/create" class="btn-konselor-admin btn-konselor-admin-primary">+ Tambah Konselor</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Kelola Konselor';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../../layouts/index.php';
