<?php
$statusPills = [
    'active'   => 'status-pill-active',
    'approved' => 'status-pill-active',
    'pending'  => 'status-pill-incomplete',
    'rejected' => 'status-pill-rejected',
];

ob_start();
?>

<?php
$queryParams = $_GET;
unset($queryParams['page']);
?>
<div class="student-admin-page">
    <div class="page-head">
        <div>
            <h1>🎓 Data Mahasiswa</h1>
            <p>Daftar mahasiswa yang terdaftar di sistem.</p>
        </div>
        <span class="student-admin-count"><?= (int) $total ?> Terdaftar</span>
    </div>

    <div class="student-admin-card" style="padding:16px 20px;margin-bottom:16px;">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Cari Nama / NPM / Email</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Cari mahasiswa...">
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Fakultas</label>
                <select name="fakultas" class="form-select form-select-sm">
                    <option value="">Semua Fakultas</option>
                    <?php foreach ($fakultasOptions as $f): ?>
                        <option value="<?= htmlspecialchars($f) ?>" <?= ($filters['fakultas'] ?? '') === $f ? 'selected' : '' ?>><?= htmlspecialchars($f) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Jurusan</label>
                <select name="jurusan" class="form-select form-select-sm">
                    <option value="">Semua Jurusan</option>
                    <?php foreach ($jurusanOptions as $j): ?>
                        <option value="<?= htmlspecialchars($j) ?>" <?= ($filters['jurusan'] ?? '') === $j ? 'selected' : '' ?>><?= htmlspecialchars($j) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <?php foreach (['active', 'pending', 'rejected'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                <a href="/students" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="student-admin-card">
        <?php if (!empty($students)): ?>
            <div class="student-admin-table-scroll">
                <table class="student-admin-table">
                    <thead>
                        <tr>
                            <th><?= sort_link('nama', 'Nama', $sort, $dir, $queryParams) ?></th>
                            <th><?= sort_link('npm', 'NPM', $sort, $dir, $queryParams) ?></th>
                            <th><?= sort_link('fakultas', 'Fakultas / Jurusan', $sort, $dir, $queryParams) ?></th>
                            <th>Kontak</th>
                            <th><?= sort_link('status', 'Status', $sort, $dir, $queryParams) ?></th>
                            <th><?= sort_link('created_at', 'Terdaftar', $sort, $dir, $queryParams) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <?php $pillClass = $statusPills[strtolower($student['status'])] ?? 'status-pill-inactive'; ?>
                            <tr>
                                <td>
                                    <div class="student-admin-name">
                                        <div class="student-admin-avatar">
                                            <?php if (!empty($student['profile'])): ?>
                                                <img src="<?= htmlspecialchars($student['profile']) ?>"
                                                    alt="<?= htmlspecialchars($student['nama'] ?: $student['username']) ?>"
                                                    onerror="this.remove()">
                                            <?php endif; ?>
                                            <?= htmlspecialchars(mb_strtoupper(mb_substr($student['nama'] ?: $student['username'], 0, 1))) ?>
                                        </div>
                                        <div>
                                            <div><?= htmlspecialchars($student['nama'] ?: '-') ?></div>
                                            <div class="student-admin-sub"><?= htmlspecialchars($student['username']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($student['npm'] ?: '-') ?></td>
                                <td>
                                    <?= htmlspecialchars($student['fakultas'] ?: '-') ?>
                                    <div class="student-admin-sub"><?= htmlspecialchars($student['jurusan'] ?: '-') ?></div>
                                </td>
                                <td>
                                    <?= htmlspecialchars($student['email'] ?: '-') ?>
                                    <div class="student-admin-sub"><?= htmlspecialchars($student['no_hp'] ?: '-') ?></div>
                                </td>
                                <td>
                                    <?php if ($student['status'] !== ''): ?>
                                        <span class="status-pill <?= $pillClass ?>"><?= htmlspecialchars(ucfirst($student['status'])) ?></span>
                                    <?php else: ?>
                                        <span class="student-admin-sub">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $student['created_at'] ? htmlspecialchars(date('d M Y', strtotime($student['created_at']))) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3">
                <span class="text-muted small"><?= (int) $total ?> mahasiswa ditemukan</span>
                <?= pagination_links($page, $totalPages, $queryParams) ?>
            </div>
        <?php else: ?>
            <div class="student-admin-empty">
                <div class="student-admin-empty-icon">🎓</div>
                <p>Tidak ada mahasiswa yang cocok, atau belum ada yang terdaftar.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Data Mahasiswa';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
