<?php
$queryParams = $_GET;
unset($queryParams['page']);
ob_start();
?>

<div class="tips-page">
    <div class="page-head">
        <div>
            <h1>💡 Tips Harian</h1>
            <p>Kelola tips yang ditampilkan sebagai popup untuk mahasiswa setelah login.</p>
        </div>
        <a href="/tips/create" class="btn-tips btn-tips-primary">+ Tambah Tips</a>
    </div>

    <div class="tips-card" style="padding:16px 20px;margin-bottom:16px;">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Cari Tips</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Cari judul/isi...">
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
                <a href="/tips" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="tips-card">
        <?php if (!empty($tips)): ?>
            <div class="tips-table-scroll">
                <table class="tips-table">
                    <thead>
                        <tr>
                            <th><?= sort_link('title', 'Judul Tips', $sort, $dir, $queryParams) ?></th>
                            <th>Isi Tips</th>
                            <th>Status</th>
                            <th><?= sort_link('created_at', 'Dibuat', $sort, $dir, $queryParams) ?></th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tips as $tip): ?>
                            <tr>
                                <td><?= htmlspecialchars($tip['title']) ?></td>
                                <td class="tips-content-snippet"><?= nl2br(htmlspecialchars($tip['content'])) ?></td>
                                <td>
                                    <?php if ($tip['is_active']): ?>
                                        <span class="status-pill status-pill-active">Aktif</span>
                                    <?php else: ?>
                                        <span class="status-pill status-pill-inactive">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars(date('d M Y', strtotime($tip['created_at']))) ?></td>
                                <td>
                                    <div class="tips-actions">
                                        <a href="/tips/<?= urlencode($tip['id']) ?>/edit" class="btn-tips btn-tips-ghost btn-tips-sm">Edit</a>
                                        <form method="post" action="/tips/<?= urlencode($tip['id']) ?>/delete"
                                            onsubmit="return confirm('Hapus tips ini?');" style="display:inline;">
                                            <button type="submit" class="btn-tips btn-tips-danger btn-tips-sm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3">
                <span class="text-muted small"><?= (int) $total ?> tips ditemukan</span>
                <?= pagination_links($page, $totalPages, $queryParams) ?>
            </div>
        <?php else: ?>
            <div class="tips-empty">
                <div class="tips-empty-icon">💡</div>
                <p>Tidak ada tips yang cocok, atau belum ada tips harian.</p>
                <a href="/tips/create" class="btn-tips btn-tips-primary">+ Tambah Tips</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Tips Harian';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
