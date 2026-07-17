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
                    <label for="nomor_registrasi">Nomor Registrasi</label>
                    <input type="text" id="nomor_registrasi" name="nomor_registrasi" value="<?= htmlspecialchars($old['nomor_registrasi'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="profesi">Profesi</label>
                    <select id="profesi" name="profesi" required>
                        <?php $profesiOld = $old['profesi'] ?? ''; ?>
                        <option value="" disabled <?= $profesiOld === '' ? 'selected' : '' ?>>Pilih profesi</option>
                        <?php foreach (['Psikolog', 'Konselor', 'Psikiater'] as $option): ?>
                            <option value="<?= $option ?>" <?= $profesiOld === $option ? 'selected' : '' ?>><?= $option ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="spesialisasi">Spesialisasi</label>
                    <input type="text" id="spesialisasi" name="spesialisasi" value="<?= htmlspecialchars($old['spesialisasi'] ?? '') ?>" placeholder="Mis. Konseling Akademik (opsional)">
                </div>
                <div class="field">
                    <label for="pendidikan">Pendidikan</label>
                    <input type="text" id="pendidikan" name="pendidikan" value="<?= htmlspecialchars($old['pendidikan'] ?? '') ?>" placeholder="Mis. S2 Psikologi (opsional)">
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="pengalaman_tahun">Pengalaman (tahun)</label>
                    <input type="number" id="pengalaman_tahun" name="pengalaman_tahun" min="0" value="<?= htmlspecialchars((string) ($old['pengalaman_tahun'] ?? 0)) ?>">
                </div>
                <div class="field">
                    <label for="bahasa">Bahasa</label>
                    <input type="text" id="bahasa" name="bahasa" value="<?= htmlspecialchars($old['bahasa'] ?? '') ?>" placeholder="Mis. Indonesia, Inggris (opsional)">
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="biaya_konsultasi">Biaya Konsultasi (Rp)</label>
                    <input type="number" id="biaya_konsultasi" name="biaya_konsultasi" min="0" step="1000" value="<?= htmlspecialchars((string) ($old['biaya_konsultasi'] ?? 0)) ?>">
                </div>
                <div class="field">
                    <label for="durasi_sesi">Durasi Sesi (menit)</label>
                    <input type="number" id="durasi_sesi" name="durasi_sesi" min="1" value="<?= htmlspecialchars((string) ($old['durasi_sesi'] ?? 60)) ?>">
                </div>
            </div>

            <div class="field">
                <label for="metode_konsultasi">Metode Konsultasi</label>
                <select id="metode_konsultasi" name="metode_konsultasi">
                    <?php $metodeOld = $old['metode_konsultasi'] ?? 'Online'; ?>
                    <?php foreach (['Online', 'Offline', 'Hybrid'] as $option): ?>
                        <option value="<?= $option ?>" <?= $metodeOld === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="biografi">Biografi</label>
                <textarea id="biografi" name="biografi" placeholder="Ditampilkan di profil konselor (opsional)"><?= htmlspecialchars($old['biografi'] ?? '') ?></textarea>
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
