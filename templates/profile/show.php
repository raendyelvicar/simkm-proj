<?php
$statusColors = [
    'active'   => 'bg-success',
    'approved' => 'bg-success',
    'pending'  => 'bg-warning text-dark',
    'rejected' => 'bg-danger',
];
$statusColor = $statusColors[strtolower($user->status)] ?? 'bg-secondary';

$hasPhoto = !empty($user->profile)
    && file_exists(__DIR__ . '/../../public/uploads/profile/' . $user->profile);

ob_start();
?>

<div class="card p-4">
    <nav class="mb-3">
        <small class="text-muted">Home / Profil</small>
    </nav>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/profile" enctype="multipart/form-data">
        <div class="d-flex flex-wrap align-items-center gap-4 pb-4 mb-4 border-bottom">
            <div class="position-relative" style="width:96px;height:96px;">
                <?php if ($hasPhoto): ?>
                    <img id="photoPreview" src="/uploads/profile/<?= htmlspecialchars($user->profile) ?>"
                        alt="Foto profil" class="rounded-circle"
                        style="width:96px;height:96px;object-fit:cover;">
                <?php else: ?>
                    <div id="photoPreview"
                        class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                        style="width:96px;height:96px;font-size:2.2rem;font-weight:600;">
                        <?= htmlspecialchars(strtoupper(substr($user->nama ?: $user->username, 0, 1))) ?>
                    </div>
                <?php endif; ?>

                <label for="photoInput"
                    class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 p-0 d-flex align-items-center justify-content-center"
                    style="width:30px;height:30px;" title="Ubah foto">
                    ✎
                </label>
                <input type="file" name="photo" id="photoInput" accept=".jpg,.jpeg,.png,.webp" class="d-none">
            </div>

            <div>
                <h4 class="mb-1"><?= htmlspecialchars($user->nama ?: $user->username) ?></h4>
                <div class="text-muted mb-2">@<?= htmlspecialchars($user->username) ?></div>
                <span class="badge bg-primary"><?= htmlspecialchars(ucfirst($user->role)) ?></span>
                <?php if ($user->status !== ''): ?>
                    <span class="badge <?= $statusColor ?>"><?= htmlspecialchars(ucfirst($user->status)) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <h6 class="text-muted text-uppercase mb-3" style="font-size:.75rem;letter-spacing:.05em;">Informasi Akun</h6>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Nama</label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user->nama) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">NPM</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user->npm) ?>" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user->email) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">No. HP</label>
                <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($user->noHp) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-select">
                    <option value="" <?= $user->jenisKelamin === '' ? 'selected' : '' ?>>-</option>
                    <option value="Laki-laki" <?= $user->jenisKelamin === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="Perempuan" <?= $user->jenisKelamin === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Fakultas</label>
                <input type="text" name="fakultas" class="form-control" value="<?= htmlspecialchars($user->fakultas) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Jurusan</label>
                <input type="text" name="jurusan" class="form-control" value="<?= htmlspecialchars($user->jurusan) ?>">
            </div>
            <?php if ($user->createdAt !== ''): ?>
                <div class="col-md-6">
                    <label class="form-label">Bergabung Sejak</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars(date('d M Y', strtotime($user->createdAt))) ?>" disabled>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('photoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) {
                return;
            }

            const preview = document.getElementById('photoPreview');
            const url = URL.createObjectURL(file);
            const img = document.createElement('img');
            img.id = 'photoPreview';
            img.src = url;
            img.alt = 'Foto profil';
            img.className = 'rounded-circle';
            img.style.cssText = 'width:96px;height:96px;object-fit:cover;';
            preview.replaceWith(img);
        });
    });
</script>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Profil';
require __DIR__ . '/../layouts/index.php';
