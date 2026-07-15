<?php
$statusPills = [
    'active'   => 'status-pill-active',
    'approved' => 'status-pill-active',
    'pending'  => 'status-pill-incomplete',
    'rejected' => 'status-pill-rejected',
];

ob_start();
?>

<div class="student-admin-page">
    <div class="page-head">
        <div>
            <h1>🎓 Data Mahasiswa</h1>
            <p>Daftar mahasiswa yang terdaftar di sistem.</p>
        </div>
        <span class="student-admin-count"><?= count($students) ?> Terdaftar</span>
    </div>

    <div class="student-admin-card">
        <?php if (!empty($students)): ?>
            <div class="student-admin-table-scroll">
                <table class="student-admin-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>NPM</th>
                            <th>Fakultas / Jurusan</th>
                            <th>Kontak</th>
                            <th>Status</th>
                            <th>Terdaftar</th>
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
        <?php else: ?>
            <div class="student-admin-empty">
                <div class="student-admin-empty-icon">🎓</div>
                <p>Belum ada mahasiswa yang terdaftar.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Data Mahasiswa';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
