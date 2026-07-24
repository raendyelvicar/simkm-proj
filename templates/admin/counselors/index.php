<?php
$queryParams = $_GET;
unset($queryParams['page']);
ob_start();
?>

<div class="counselor-admin-page">
    <div class="page-head">
        <div>
            <h1>Kelola Konselor</h1>
            <p>Tambah, ubah, atau nonaktifkan akun konselor.</p>
        </div>
        <a href="/admin/counselors/create" class="btn-counselor-admin btn-counselor-admin-primary">+ Tambah Konselor</a>
    </div>

    <div class="counselor-admin-card" style="padding:16px 20px;margin-bottom:16px;">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Cari Nama / No. Registrasi</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Cari konselor...">
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Profesi</label>
                <?php $professionLabels = ['Psychologist' => 'Psikolog', 'Counselor' => 'Konselor', 'Psychiatrist' => 'Psikiater']; ?>
                <select name="profession" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <?php foreach (['Psychologist', 'Counselor', 'Psychiatrist'] as $p): ?>
                        <option value="<?= $p ?>" <?= ($filters['profession'] ?? '') === $p ? 'selected' : '' ?>><?= $professionLabels[$p] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="is_active" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="1" <?= ($filters['is_active'] ?? '') === '1' ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= ($filters['is_active'] ?? '') === '0' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                <a href="/admin/counselors" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="counselor-admin-card">
        <?php if (!empty($counselors)): ?>
            <table class="counselor-admin-table">
                <thead>
                    <tr>
                        <th><?= sort_link('name', 'Nama', $sort, $dir, $queryParams) ?></th>
                        <th>Username / Email</th>
                        <th><?= sort_link('profession', 'Profesi', $sort, $dir, $queryParams) ?></th>
                        <th>Spesialisasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($counselors as $counselor): ?>
                        <tr>
                            <td>
                                <div class="counselor-admin-name">
                                    <div class="counselor-admin-avatar">
                                        <?php $photo = profile_photo_url($counselor['profile_photo'] ?: $counselor['profile_image']); ?>
                                        <?php if ($photo): ?>
                                            <img src="<?= htmlspecialchars($photo) ?>"
                                                alt="<?= htmlspecialchars($counselor['name']) ?>"
                                                onerror="this.remove()">
                                        <?php endif; ?>
                                        <?= htmlspecialchars(mb_strtoupper(mb_substr($counselor['name'] !== '' ? $counselor['name'] : '?', 0, 1))) ?>
                                    </div>
                                    <span><?= htmlspecialchars($counselor['name'] !== '' ? $counselor['name'] : '-') ?></span>
                                </div>
                            </td>
                            <td>
                                <?= htmlspecialchars($counselor['username']) ?><br>
                                <span style="color:var(--muted); font-size:0.8rem;"><?= htmlspecialchars($counselor['email']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($professionLabels[$counselor['profession']] ?? ($counselor['profession'] ?: '-')) ?></td>
                            <td><?= htmlspecialchars($counselor['specialization'] ?: '-') ?></td>
                            <td>
                                <?php if (!$counselor['has_profile']): ?>
                                    <span class="status-pill status-pill-incomplete">Profil belum lengkap</span>
                                <?php elseif ($counselor['is_active']): ?>
                                    <span class="status-pill status-pill-active">Aktif</span>
                                <?php else: ?>
                                    <span class="status-pill status-pill-inactive">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="counselor-admin-actions">
                                    <a href="/admin/counselors/<?= urlencode($counselor['user_id']) ?>/edit"
                                        class="btn-counselor-admin btn-counselor-admin-ghost btn-counselor-admin-sm">Edit</a>

                                    <?php if ($counselor['has_profile']): ?>
                                        <a href="/admin/counselors/<?= urlencode($counselor['user_id']) ?>/schedule"
                                            class="btn-counselor-admin btn-counselor-admin-ghost btn-counselor-admin-sm">Jadwal</a>
                                    <?php endif; ?>

                                    <?php if ($counselor['has_profile']): ?>
                                        <form method="post" action="/admin/counselors/<?= urlencode($counselor['user_id']) ?>/status"
                                            onsubmit="return confirm('<?= $counselor['is_active'] ? 'Nonaktifkan konselor ini? Ia tidak akan tampil di daftar konselor untuk mahasiswa.' : 'Aktifkan kembali konselor ini?' ?>');"
                                            style="display:inline;">
                                            <button type="submit" class="btn-counselor-admin btn-counselor-admin-danger btn-counselor-admin-sm">
                                                <?= $counselor['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="d-flex justify-content-between align-items-center p-3">
                <span class="text-muted small"><?= (int) $total ?> konselor ditemukan</span>
                <?= pagination_links($page, $totalPages, $queryParams) ?>
            </div>
        <?php else: ?>
            <div class="counselor-admin-empty">
                <div class="counselor-admin-empty-icon">🧑‍⚕️</div>
                <p>Tidak ada konselor yang cocok, atau belum ada akun konselor.</p>
                <a href="/admin/counselors/create" class="btn-counselor-admin btn-counselor-admin-primary">+ Tambah Konselor</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Kelola Konselor';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../../layouts/index.php';
