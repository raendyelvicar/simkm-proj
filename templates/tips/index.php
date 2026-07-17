<?php ob_start(); ?>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="h4 mb-1">💡 Tips Harian</h3>
            <p class="text-muted mb-0">Kelola tips yang ditampilkan sebagai popup untuk mahasiswa setelah login.</p>
        </div>
        <a href="/tips/create" class="btn btn-primary">+ Tambah Tips</a>
    </div>

    <?php if (!empty($tips)): ?>
        <div class="table-responsive">
            <table class="table align-middle">
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
                            <td style="max-width:420px;"><?= nl2br(htmlspecialchars($tip['content'])) ?></td>
                            <td>
                                <?php if ($tip['is_active']): ?>
                                    <span class="badge text-bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars(date('d M Y', strtotime($tip['created_at']))) ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="/tips/<?= urlencode($tip['id']) ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="post" action="/tips/<?= urlencode($tip['id']) ?>/delete"
                                        onsubmit="return confirm('Hapus tips ini?');" style="display:inline;">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-2" style="font-size:2rem;">💡</div>
            <p class="text-muted">Belum ada tips harian.</p>
            <a href="/tips/create" class="btn btn-primary">+ Tambah Tips</a>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Tips Harian';
require __DIR__ . '/../layouts/index.php';
