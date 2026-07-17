<?php ob_start(); ?>

<div class="tips-page">
    <div class="page-head">
        <div>
            <h1>💡 Tips Harian</h1>
            <p>Kelola tips yang ditampilkan sebagai popup untuk mahasiswa setelah login.</p>
        </div>
        <a href="/tips/create" class="btn-tips btn-tips-primary">+ Tambah Tips</a>
    </div>

    <div class="tips-card">
        <?php if (!empty($tips)): ?>
            <div class="tips-table-scroll">
                <table class="tips-table">
                    <thead>
                        <tr>
                            <th>Judul Tips</th>
                            <th>Isi Tips</th>
                            <th>Status</th>
                            <th>Dibuat</th>
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
        <?php else: ?>
            <div class="tips-empty">
                <div class="tips-empty-icon">💡</div>
                <p>Belum ada tips harian.</p>
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
