<?php
$statusPills = [
    'active'   => 'status-pill-active',
    'approved' => 'status-pill-active',
    'pending'  => 'status-pill-incomplete',
    'rejected' => 'status-pill-rejected',
];
$statusLabels = [
    'active'   => 'Aktif',
    'approved' => 'Disetujui',
    'pending'  => 'Menunggu',
    'rejected' => 'Ditolak',
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
                <select name="faculty" class="form-select form-select-sm">
                    <option value="">Semua Fakultas</option>
                    <?php foreach ($facultyOptions as $f): ?>
                        <option value="<?= htmlspecialchars($f) ?>" <?= ($filters['faculty'] ?? '') === $f ? 'selected' : '' ?>><?= htmlspecialchars($f) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Jurusan</label>
                <select name="major" class="form-select form-select-sm">
                    <option value="">Semua Jurusan</option>
                    <?php foreach ($majorOptions as $j): ?>
                        <option value="<?= htmlspecialchars($j) ?>" <?= ($filters['major'] ?? '') === $j ? 'selected' : '' ?>><?= htmlspecialchars($j) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <?php foreach (['active', 'pending', 'rejected'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= $statusLabels[$s] ?></option>
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
                            <th><?= sort_link('name', 'Nama', $sort, $dir, $queryParams) ?></th>
                            <th><?= sort_link('student_number', 'NPM', $sort, $dir, $queryParams) ?></th>
                            <th><?= sort_link('faculty', 'Fakultas / Jurusan', $sort, $dir, $queryParams) ?></th>
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
                                            <?php $photo = profile_photo_url($student['profile']); ?>
                                            <?php if ($photo): ?>
                                                <img src="<?= htmlspecialchars($photo) ?>"
                                                    alt="<?= htmlspecialchars($student['name'] ?: $student['username']) ?>"
                                                    onerror="this.remove()">
                                            <?php endif; ?>
                                            <?= htmlspecialchars(mb_strtoupper(mb_substr($student['name'] ?: $student['username'], 0, 1))) ?>
                                        </div>
                                        <div>
                                            <div><?= htmlspecialchars($student['name'] ?: '-') ?></div>
                                            <div class="student-admin-sub"><?= htmlspecialchars($student['username']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($student['student_number'] ?: '-') ?></td>
                                <td>
                                    <?= htmlspecialchars($student['faculty'] ?: '-') ?>
                                    <div class="student-admin-sub"><?= htmlspecialchars($student['major'] ?: '-') ?></div>
                                </td>
                                <td>
                                    <?= htmlspecialchars($student['email'] ?: '-') ?>
                                    <div class="student-admin-sub"><?= htmlspecialchars($student['phone_number'] ?: '-') ?></div>
                                </td>
                                <td>
                                    <?php if ($student['status'] !== ''): ?>
                                        <span class="status-pill <?= $pillClass ?>"><?= htmlspecialchars($statusLabels[strtolower($student['status'])] ?? ucfirst($student['status'])) ?></span>
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
