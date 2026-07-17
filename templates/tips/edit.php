<?php ob_start(); ?>

<div class="card p-4" style="max-width:640px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="h4 mb-0">Edit Tips</h3>
        <a href="/tips" class="btn btn-outline-secondary btn-sm">&larr; Kembali</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/tips/<?= urlencode($tip['id']) ?>">
        <div class="mb-3">
            <label for="title" class="form-label">Judul Tips</label>
            <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($tip['title'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Isi Tips</label>
            <textarea id="content" name="content" class="form-control" rows="4" required><?= htmlspecialchars($tip['content'] ?? '') ?></textarea>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input"
                <?= !empty($tip['is_active']) ? 'checked' : '' ?>>
            <label for="is_active" class="form-check-label">Aktifkan tips ini agar tampil ke mahasiswa</label>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="/tips" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Edit Tips';
require __DIR__ . '/../layouts/index.php';
