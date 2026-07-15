<?php ob_start(); ?>

<div class="konselor-admin-page">
    <div class="page-head">
        <div>
            <h1>Tambah Konselor</h1>
            <p>Buat akun login sekaligus profil konsultasi konselor baru.</p>
        </div>
        <a href="/admin/counselors" class="btn-konselor-admin btn-konselor-admin-ghost">&larr; Kembali</a>
    </div>

    <div class="konselor-admin-detail">
        <?php if (!empty($errors)): ?>
            <div class="konselor-admin-alert konselor-admin-alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/admin/counselors" class="konselor-admin-form" enctype="multipart/form-data">

            <div class="field-row">
                <div class="field">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($old['nama'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($old['username'] ?? '') ?>" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <p class="field-hint">Minimal 8 karakter.</p>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="nip_nik">NIP / NIK</label>
                    <input type="text" id="nip_nik" name="nip_nik" value="<?= htmlspecialchars($old['nip_nik'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="spesialisasi">Spesialisasi</label>
                    <input type="text" id="spesialisasi" name="spesialisasi" value="<?= htmlspecialchars($old['spesialisasi'] ?? '') ?>" placeholder="Mis. Konseling Akademik (opsional)">
                </div>
            </div>

            <div class="field">
                <label for="jadwal_praktik">Jadwal Praktik</label>
                <input type="text" id="jadwal_praktik" name="jadwal_praktik" value="<?= htmlspecialchars($old['jadwal_praktik'] ?? '') ?>" placeholder="Mis. Senin-Jumat, 09.00-15.00 (opsional)">
            </div>

            <div class="field">
                <label for="biografi_singkat">Biografi Singkat</label>
                <textarea id="biografi_singkat" name="biografi_singkat" placeholder="Ditampilkan di profil konselor (opsional)"><?= htmlspecialchars($old['biografi_singkat'] ?? '') ?></textarea>
            </div>

            <div class="field">
                <label for="photo">Foto Profil</label>
                <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                <p class="field-hint">JPG, PNG, atau WEBP, maksimal 2MB (opsional).</p>
            </div>

            <label class="konselor-admin-check">
                <input type="checkbox" name="status_aktif" value="1" <?= !isset($old['status_aktif']) || $old['status_aktif'] ? 'checked' : '' ?>>
                <span>Aktifkan konselor ini agar tampil di daftar konselor mahasiswa</span>
            </label>

            <div class="konselor-admin-form-actions">
                <button type="submit" class="btn-konselor-admin btn-konselor-admin-primary">Simpan</button>
                <a href="/admin/counselors" class="btn-konselor-admin btn-konselor-admin-ghost">Batal</a>
            </div>

        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Tambah Konselor';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../../layouts/index.php';
