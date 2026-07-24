<?php ob_start(); ?>

<div class="counselor-admin-page">
    <div class="page-head">
        <div>
            <h1>Edit Konselor</h1>
            <p>Ubah data akun dan profil konsultasi.</p>
        </div>
        <a href="/admin/counselors" class="btn-counselor-admin btn-counselor-admin-ghost">&larr; Kembali</a>
    </div>

    <div class="counselor-admin-detail">
        <?php if (empty($counselor['has_profile'])): ?>
            <div class="counselor-admin-alert" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;">
                Profil konselor ini belum lengkap. Isi NIP/NIK untuk melengkapi profil agar bisa diaktifkan/nonaktifkan.
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="counselor-admin-alert counselor-admin-alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/admin/counselors/<?= urlencode($counselor['counselor_id']) ?>" class="counselor-admin-form" enctype="multipart/form-data">

            <div class="field-row">
                <div class="field">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($counselor['name'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($counselor['username'] ?? '') ?>" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($counselor['email'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="password">Password Baru</label>
                    <input type="password" id="password" name="password">
                    <p class="field-hint">Kosongkan jika tidak ingin mengubah password.</p>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="registration_number">Nomor Registrasi</label>
                    <input type="text" id="registration_number" name="registration_number" value="<?= htmlspecialchars($counselor['registration_number'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="profession">Profesi</label>
                    <select id="profession" name="profession" required>
                        <?php $professionVal = $counselor['profession'] ?? ''; ?>
                        <?php $professionLabels = ['Psychologist' => 'Psikolog', 'Counselor' => 'Konselor', 'Psychiatrist' => 'Psikiater']; ?>
                        <option value="" disabled <?= $professionVal === '' ? 'selected' : '' ?>>Pilih profesi</option>
                        <?php foreach (['Psychologist', 'Counselor', 'Psychiatrist'] as $option): ?>
                            <option value="<?= $option ?>" <?= $professionVal === $option ? 'selected' : '' ?>><?= $professionLabels[$option] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="specialization">Spesialisasi</label>
                    <input type="text" id="specialization" name="specialization" value="<?= htmlspecialchars($counselor['specialization'] ?? '') ?>" placeholder="Mis. Konseling Akademik (opsional)">
                </div>
                <div class="field">
                    <label for="education">Pendidikan</label>
                    <input type="text" id="education" name="education" value="<?= htmlspecialchars($counselor['education'] ?? '') ?>" placeholder="Mis. S2 Psikologi (opsional)">
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="experience_years">Pengalaman (tahun)</label>
                    <input type="number" id="experience_years" name="experience_years" min="0" value="<?= htmlspecialchars((string) ($counselor['experience_years'] ?? 0)) ?>">
                </div>
                <div class="field">
                    <label for="languages">Bahasa</label>
                    <input type="text" id="languages" name="languages" value="<?= htmlspecialchars($counselor['languages'] ?? '') ?>" placeholder="Mis. Indonesia, Inggris (opsional)">
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="consultation_fee">Biaya Konsultasi (Rp)</label>
                    <input type="number" id="consultation_fee" name="consultation_fee" min="0" step="1000" value="<?= htmlspecialchars((string) ($counselor['consultation_fee'] ?? 0)) ?>">
                </div>
                <div class="field">
                    <label for="session_duration">Durasi Sesi (menit)</label>
                    <input type="number" id="session_duration" name="session_duration" min="1" value="<?= htmlspecialchars((string) ($counselor['session_duration'] ?? 60)) ?>">
                </div>
            </div>

            <div class="field">
                <label for="consultation_method">Metode Konsultasi</label>
                <select id="consultation_method" name="consultation_method">
                    <?php $metodeVal = $counselor['consultation_method'] ?? 'Online'; ?>
                    <?php foreach (['Online', 'Offline', 'Hybrid'] as $option): ?>
                        <option value="<?= $option ?>" <?= $metodeVal === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="biography">Biografi</label>
                <textarea id="biography" name="biography" placeholder="Ditampilkan di profil konselor (opsional)"><?= htmlspecialchars($counselor['biography'] ?? '') ?></textarea>
            </div>

            <div class="field">
                <?php $photo = profile_photo_url($counselor['profile_photo'] ?: $counselor['profile_image']); ?>
                <?php if ($photo): ?>
                    <div class="counselor-admin-avatar" style="width:56px;height:56px;font-size:1.2rem;margin-bottom:10px;">
                        <img src="<?= htmlspecialchars($photo) ?>" alt="" onerror="this.remove()">
                    </div>
                <?php endif; ?>
                <label for="photo">Foto Profil</label>
                <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                <p class="field-hint">JPG, PNG, atau WEBP, maksimal 2MB. Kosongkan untuk mempertahankan foto saat ini.</p>
            </div>

            <label class="counselor-admin-check">
                <input type="checkbox" name="is_active" value="1" <?= !isset($counselor['is_active']) || $counselor['is_active'] ? 'checked' : '' ?>>
                <span>Aktifkan konselor ini agar tampil di daftar konselor mahasiswa</span>
            </label>

            <div class="counselor-admin-form-actions">
                <button type="submit" class="btn-counselor-admin btn-counselor-admin-primary">Simpan Perubahan</button>
                <a href="/admin/counselors" class="btn-counselor-admin btn-counselor-admin-ghost">Batal</a>
            </div>

        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Edit Konselor';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../../layouts/index.php';
