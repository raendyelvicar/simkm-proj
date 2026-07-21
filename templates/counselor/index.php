<?php
$queryParams = $_GET;
unset($queryParams['page']);
ob_start();
?>

<div class="counselor-page">
    <div class="page-head">
        <div>
            <h1>Konselor</h1>
            <p>Pilih konselor untuk memulai konsultasi kesehatan mental.</p>
        </div>
    </div>

    <div class="counselor-card" style="padding:16px 20px;margin-bottom:20px;">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Cari Konselor</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Cari nama/spesialisasi...">
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Profesi</label>
                <select name="profesi" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <?php foreach (['Psikolog', 'Konselor', 'Psikiater'] as $p): ?>
                        <option value="<?= $p ?>" <?= ($filters['profesi'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Metode</label>
                <select name="metode_konsultasi" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <?php foreach (['Online', 'Offline', 'Hybrid'] as $m): ?>
                        <option value="<?= $m ?>" <?= ($filters['metode_konsultasi'] ?? '') === $m ? 'selected' : '' ?>><?= $m ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Urutkan</label>
                <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?= sort_options(['nama' => 'Nama', 'pengalaman_tahun' => 'Pengalaman', 'biaya_konsultasi' => 'Biaya'], $sort, $dir) ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>
                <a href="/counselor" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <?php if (!empty($counselors)): ?>
        <div class="counselor-grid">
            <?php foreach ($counselors as $counselor): ?>
                <div class="counselor-card">
                    <div class="counselor-avatar">
                        <?php $photo = $counselor['foto_profil'] ?: $counselor['profile_image']; ?>
                        <?php if (!empty($photo)): ?>
                            <img src="<?= htmlspecialchars($photo) ?>"
                                alt="<?= htmlspecialchars($counselor['nama']) ?>"
                                onerror="this.remove()">
                        <?php endif; ?>
                        <span class="counselor-avatar-initial"><?= htmlspecialchars(mb_strtoupper(mb_substr($counselor['nama'] !== '' ? $counselor['nama'] : '?', 0, 1))) ?></span>
                    </div>

                    <div class="counselor-card-body">
                        <h2><?= htmlspecialchars($counselor['nama'] !== '' ? $counselor['nama'] : 'Konselor') ?></h2>

                        <?php if (!empty($counselor['profesi'])): ?>
                            <span class="category-pill"><?= htmlspecialchars($counselor['profesi']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($counselor['spesialisasi'])): ?>
                            <span class="category-pill"><?= htmlspecialchars($counselor['spesialisasi']) ?></span>
                        <?php endif; ?>

                        <?php if (!empty($counselor['biografi'])): ?>
                            <p class="counselor-bio"><?= htmlspecialchars(substr($counselor['biografi'], 0, 110)) ?>&hellip;</p>
                        <?php endif; ?>

                        <?php if (!empty($counselor['metode_konsultasi'])): ?>
                            <div class="counselor-meta">💻 <?= htmlspecialchars($counselor['metode_konsultasi']) ?> &middot; <?= (int) $counselor['durasi_sesi'] ?> menit</div>
                        <?php endif; ?>
                        <!-- <?php if (!empty($counselor['biaya_konsultasi'])): ?>
                            <div class="counselor-meta">💳 Rp<?= number_format((float) $counselor['biaya_konsultasi'], 0, ',', '.') ?></div>
                        <?php endif; ?> -->

                        <div class="counselor-actions">
                            <a href="/counselor/<?= urlencode($counselor['user_id']) ?>" class="btn-counselor btn-counselor-ghost">Lihat Profil</a>
                            <?php if (in_array((int) $counselor['konselor_id'], $activeMonitoringKonselorIds ?? [], true)): ?>
                                <a href="/chat/<?= urlencode($counselor['user_id']) ?>" class="btn-counselor btn-counselor-primary">💬 Konsultasi</a>
                            <?php elseif (($_SESSION['role'] ?? '') === 'mahasiswa'): ?>
                                <a href="/bookings/create/<?= urlencode($counselor['user_id']) ?>" class="btn-counselor btn-counselor-primary">📅 Booking</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="text-muted small"><?= (int) $total ?> konselor ditemukan</span>
            <?= pagination_links($page, $totalPages, $queryParams) ?>
        </div>
    <?php else: ?>
        <div class="counselor-empty">
            <div class="counselor-empty-icon">🧑‍⚕️</div>
            <p>Tidak ada konselor yang cocok, atau belum ada konselor yang tersedia saat ini.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Konselor';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
