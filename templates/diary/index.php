<?php
$queryParams = $_GET;
unset($queryParams['page']);
ob_start();
?>

<div class="diary-page">
    <div class="page-head">
        <div>
            <h1>Diary Saya</h1>
            <p>Catatan harian dan perjalanan mood kamu.</p>
        </div>
        <a href="/diary/create" class="btn-diary btn-diary-primary">+ Tulis Diary</a>
    </div>

    <div class="diary-card" style="padding:16px 20px;margin-bottom:16px;">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Cari Isi Diary</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Cari situasi/pikiran...">
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>
                <a href="/diary" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="diary-card">
        <?php if (!empty($entries)): ?>
            <table class="diary-table">
                <thead>
                    <tr>
                        <th><?= sort_link('entry_date', 'Tanggal', $sort, $dir, $queryParams) ?></th>
                        <th>Intensitas</th>
                        <th>Emosi</th>
                        <th>Ringkasan</th>
                        <th>Privasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                        <tr>
                            <td class="diary-date">
                                <?= htmlspecialchars($entry['entry_date'] ? date('d M Y', strtotime($entry['entry_date'])) : '-') ?>
                            </td>
                            <td>
                                <span class="diary-badge <?= diary_intensity_badge_class((int) ($entry['intensitas_emosi'] ?? 0)) ?>">
                                    <?= (int) ($entry['intensitas_emosi'] ?? 0) ?> / 5
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars(implode(', ', $entry['emosi_list'] ?? []) ?: '-') ?>
                            </td>
                            <td class="diary-snippet">
                                <?= htmlspecialchars(mb_substr($entry['situasi'] ?? '', 0, 80)) ?><?= mb_strlen($entry['situasi'] ?? '') > 80 ? '…' : '' ?>
                            </td>
                            <td>
                                <?= !empty($entry['is_private']) ? 'Private' : 'Dibagikan' ?>
                            </td>
                            <td>
                                <div class="diary-actions">
                                    <a href="/diary/<?= urlencode($entry['id']) ?>" class="btn-diary btn-diary-ghost btn-diary-sm">Lihat</a>
                                    <a href="/diary/<?= urlencode($entry['id']) ?>/edit" class="btn-diary btn-diary-ghost btn-diary-sm">Edit</a>
                                    <form method="post" action="/diary/<?= urlencode($entry['id']) ?>/delete"
                                        onsubmit="return confirm('Hapus diary ini?');" style="display:inline;">
                                        <button type="submit" class="btn-diary btn-diary-danger btn-diary-sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="d-flex justify-content-between align-items-center p-3">
                <span class="text-muted small"><?= (int) $total ?> diary ditemukan</span>
                <?= pagination_links($page, $totalPages, $queryParams) ?>
            </div>
        <?php else: ?>
            <div class="diary-empty">
                <div class="diary-empty-icon">📔</div>
                <p>Tidak ada diary yang cocok, atau belum ada diary. Yuk mulai menulis hari ini.</p>
                <a href="/diary/create" class="btn-diary btn-diary-primary">+ Tulis Diary</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Diary';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
