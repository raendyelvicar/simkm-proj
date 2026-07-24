<?php
$statusColors = [
    'active'   => 'bg-success',
    'approved' => 'bg-success',
    'pending'  => 'bg-warning text-dark',
    'rejected' => 'bg-danger',
];
$statusColor = $statusColors[strtolower($user->status)] ?? 'bg-secondary';
$statusLabels = [
    'active'   => 'Aktif',
    'approved' => 'Disetujui',
    'pending'  => 'Menunggu',
    'rejected' => 'Ditolak',
];
$roleLabels = [
    'admin'     => 'Admin',
    'counselor' => 'Konselor',
    'student'   => 'Mahasiswa',
];
$isStudent = $user->role === 'student';

$hasPhoto = !empty($user->profile)
    && file_exists(__DIR__ . '/../../public/uploads/profile/' . $user->profile);

ob_start();
?>

<div class="card p-4">
    <nav class="mb-3">
        <small class="text-muted">Beranda / Profil</small>
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
                        <?= htmlspecialchars(strtoupper(substr($user->name ?: $user->username, 0, 1))) ?>
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
                <h4 class="mb-1"><?= htmlspecialchars($user->name ?: $user->username) ?></h4>
                <div class="text-muted mb-2">@<?= htmlspecialchars($user->username) ?></div>
                <span class="badge bg-primary"><?= htmlspecialchars($roleLabels[$user->role] ?? ucfirst($user->role)) ?></span>
                <?php if ($user->status !== ''): ?>
                    <span class="badge <?= $statusColor ?>"><?= htmlspecialchars($statusLabels[strtolower($user->status)] ?? ucfirst($user->status)) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <h6 class="text-muted text-uppercase mb-3" style="font-size:.75rem;letter-spacing:.05em;">Informasi Akun</h6>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Nama</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user->name) ?>" required>
            </div>
            <?php if ($isStudent): ?>
                <div class="col-md-6">
                    <label class="form-label">NPM</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user->student_number) ?>" disabled>
                </div>
            <?php endif; ?>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user->email) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">No. HP</label>
                <input type="text" name="phone_number" class="form-control" value="<?= htmlspecialchars($user->phoneNumber) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Jenis Kelamin</label>
                <select name="gender" class="form-select">
                    <option value="" <?= $user->gender === '' ? 'selected' : '' ?>>-</option>
                    <option value="Male" <?= $user->gender === 'Male' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="Female" <?= $user->gender === 'Female' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>
            <?php if ($isStudent): ?>
                <div class="col-md-6">
                    <label class="form-label">Fakultas</label>
                    <input type="text" name="faculty" class="form-control" value="<?= htmlspecialchars($user->faculty) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jurusan</label>
                    <input type="text" name="major" class="form-control" value="<?= htmlspecialchars($user->major) ?>">
                </div>
            <?php endif; ?>
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
