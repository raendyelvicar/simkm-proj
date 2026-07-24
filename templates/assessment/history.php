<?php
$queryParams = $_GET;
unset($queryParams['page']);
ob_start();
?>

<div class="assess-page">
    <div class="page-head">
        <div>
            <h1>Riwayat Assessment</h1>
            <p><?= !empty($isStaff) ? 'Riwayat hasil assessment seluruh mahasiswa, dikelompokkan per mahasiswa.' : 'Riwayat hasil assessment kamu.' ?></p>
        </div>
        <a href="/assessment" class="btn btn-outline-secondary btn-sm">&larr; Kembali</a>
    </div>

    <?php if (!empty($isStaff)): ?>

        <div class="assess-card assess-card-body mb-3">
            <form method="get" action="/assessment/history" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Cari Nama / NPM</label>
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
                    <label class="form-label small text-muted mb-1">Kategori BDI-II</label>
                    <select name="bdi2_category" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <?php foreach (['Minimal', 'Ringan', 'Sedang', 'Berat'] as $c): ?>
                            <option value="<?= $c ?>" <?= ($filters['bdi2_category'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small text-muted mb-1">Kategori PWB</label>
                    <select name="pwb_category" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <?php foreach (['Tinggi', 'Sedang', 'Rendah'] as $c): ?>
                            <option value="<?= $c ?>" <?= ($filters['pwb_category'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                    <a href="/assessment/history" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="assess-card assess-card-body">
            <?php if (empty($students)): ?>
                <p class="text-muted mb-0">Tidak ada mahasiswa yang cocok dengan pencarian/filter.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table assess-table align-middle">
                        <thead>
                            <tr>
                                <th><?= sort_link('name', 'Mahasiswa', $sort, $dir, $queryParams) ?></th>
                                <th><?= sort_link('faculty', 'Fakultas / Jurusan', $sort, $dir, $queryParams) ?></th>
                                <th>BDI-II Terakhir</th>
                                <th>PWB Terakhir</th>
                                <th><?= sort_link('total_submissions', 'Total Pengiriman', $sort, $dir, $queryParams) ?></th>
                                <th><?= sort_link('last_submitted_at', 'Terakhir Mengisi', $sort, $dir, $queryParams) ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $s): ?>
                                <tr>
                                    <td>
                                        <div><?= htmlspecialchars($s['name'] ?: '-') ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($s['student_number'] ?: '-') ?></div>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($s['faculty'] ?: '-') ?>
                                        <div class="text-muted small"><?= htmlspecialchars($s['major'] ?: '-') ?></div>
                                    </td>
                                    <td>
                                        <?php if ($s['latest_bdi2_category']): ?>
                                            <span class="assess-badge <?= assessment_badge_class($s['latest_bdi2_category']) ?>"><?= htmlspecialchars($s['latest_bdi2_category']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">Belum ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($s['latest_pwb_category']): ?>
                                            <span class="assess-badge <?= assessment_badge_class($s['latest_pwb_category']) ?>"><?= htmlspecialchars($s['latest_pwb_category']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">Belum ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= (int) $s['total_submissions'] ?></td>
                                    <td><?= $s['last_submitted_at'] ? htmlspecialchars(date('d M Y H:i', strtotime($s['last_submitted_at']))) : '-' ?></td>
                                    <td><a href="/assessment/history/student/<?= (int) $s['id'] ?>" class="btn btn-sm btn-outline-primary">Lihat Riwayat</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="text-muted small"><?= (int) $total ?> mahasiswa ditemukan</span>
                    <?= pagination_links($page, $totalPages, $queryParams) ?>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <div class="assess-card assess-card-body mb-3">
            <form method="get" action="/assessment/history" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small text-muted mb-1">Jenis</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <?php foreach ($meta as $key => $m): ?>
                            <option value="<?= $key ?>" <?= $type === $key ? 'selected' : '' ?>><?= htmlspecialchars($m['short_title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                </div>
            </form>
        </div>

        <div class="assess-card assess-card-body">
            <?php if (empty($submissions)): ?>
                <p class="text-muted mb-0">Belum ada riwayat assessment.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table assess-table align-middle">
                        <thead>
                            <tr>
                                <th><?= sort_link('type', 'Jenis', $sort, $dir, $queryParams) ?></th>
                                <th><?= sort_link('submitted_at', 'Tanggal', $sort, $dir, $queryParams) ?></th>
                                <th><?= sort_link('total_score', 'Skor', $sort, $dir, $queryParams) ?></th>
                                <th><?= sort_link('category', 'Kategori', $sort, $dir, $queryParams) ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($meta[$s['type']]['short_title'] ?? $s['type']) ?></td>
                                    <td><?= htmlspecialchars(date('d M Y H:i', strtotime($s['submitted_at']))) ?></td>
                                    <td><?= (int) $s['total_score'] ?> / <?= (int) $s['max_score'] ?></td>
                                    <td>
                                        <span class="assess-badge <?= assessment_badge_class($s['category']) ?>"><?= htmlspecialchars($s['category']) ?></span>
                                        <?php if (!empty($s['is_timed_out'])): ?>
                                            <span class="assess-badge assess-badge-gray">Waktu Habis</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/assessment/result/<?= (int) $s['id'] ?>" class="btn btn-sm btn-outline-primary">Lihat</a>
                                        <a href="/assessment/history/<?= (int) $s['id'] ?>/pdf" class="btn btn-sm btn-outline-secondary">PDF</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="text-muted small"><?= (int) $total ?> pengiriman ditemukan</span>
                    <?= pagination_links($page, $totalPages, $queryParams) ?>
                </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Riwayat Assessment';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
