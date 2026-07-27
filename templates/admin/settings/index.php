<?php ob_start(); ?>

<div class="card p-4">
    <h3 class="h4 mb-1">⚙️ Pengaturan Sistem</h3>
    <p class="text-muted mb-4">Konfigurasi berlaku untuk sesi assessment yang baru dimulai — sesi yang sedang berjalan tidak terpengaruh.</p>

    <form method="post" action="/admin/settings" enctype="multipart/form-data" class="row g-3" style="max-width:420px;">
        <div class="col-12">
            <label for="assessment_time_limit_minutes" class="form-label">Batas Waktu Pengisian Assessment (menit)</label>
            <input type="number" min="1" max="240" name="assessment_time_limit_minutes" id="assessment_time_limit_minutes"
                class="form-control" value="<?= (int) $timeLimitMinutes ?>" required>
            <div class="form-text">Berlaku untuk satu sesi gabungan BDI-II + PWB (1–240 menit).</div>
        </div>
        <div class="col-12">
            <label for="report_default_counselor_id" class="form-label">Konselor Default untuk Laporan (Mengetahui)</label>
            <select name="report_default_counselor_id" id="report_default_counselor_id" class="form-select">
                <option value="">— Tidak diatur —</option>
                <?php foreach ($counselors as $c): ?>
                    <option value="<?= (int) $c['counselor_id'] ?>" <?= $defaultReportCounselorId === (int) $c['counselor_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Ditampilkan sebagai penanggung jawab ("Mengetahui") pada ekspor PDF laporan saat tidak ada konselor lain yang lebih relevan (mis. laporan admin, atau laporan mahasiswa yang belum pernah menjalani sesi konseling).</div>
        </div>

        <div class="col-12"><hr class="my-1"></div>
        <div class="col-12">
            <h6 class="mb-1">Kop Laporan (Header Organisasi)</h6>
            <p class="text-muted small mb-2">Ditampilkan di bagian atas setiap halaman dan ekspor PDF Laporan.</p>
        </div>

        <div class="col-12">
            <label for="org_name" class="form-label">Nama Organisasi</label>
            <input type="text" name="org_name" id="org_name" class="form-control"
                value="<?= htmlspecialchars($orgName) ?>" placeholder="Mis. Universitas ABC — Unit Layanan Kesehatan Mental">
        </div>
        <div class="col-12">
            <label for="org_address" class="form-label">Alamat</label>
            <input type="text" name="org_address" id="org_address" class="form-control"
                value="<?= htmlspecialchars($orgAddress) ?>" placeholder="Mis. Jl. Contoh No. 1, Kota, Provinsi">
        </div>
        <div class="col-12">
            <label for="org_phone" class="form-label">Nomor Telepon</label>
            <input type="text" name="org_phone" id="org_phone" class="form-control"
                value="<?= htmlspecialchars($orgPhone) ?>" placeholder="Mis. (021) 123-4567">
        </div>
        <div class="col-12">
            <label for="org_email" class="form-label">Email</label>
            <input type="email" name="org_email" id="org_email" class="form-control"
                value="<?= htmlspecialchars($orgEmail) ?>" placeholder="Mis. layanan@contoh.ac.id">
        </div>
        <div class="col-12">
            <label for="org_logo" class="form-label">Logo Organisasi</label>
            <?php if ($orgLogoPath): ?>
                <div class="mb-2">
                    <img src="<?= htmlspecialchars($orgLogoPath) ?>" alt="Logo organisasi" style="max-height:64px;max-width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:4px;">
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" name="remove_org_logo" value="1" class="form-check-input" id="remove_org_logo">
                    <label class="form-check-label" for="remove_org_logo">Hapus logo saat ini</label>
                </div>
            <?php endif; ?>
            <input type="file" name="org_logo" id="org_logo" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            <div class="form-text">JPG, PNG, atau WEBP, maksimal 2MB (opsional).</div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Pengaturan Sistem';
require __DIR__ . '/../../layouts/index.php';
