<?php ob_start(); ?>

<div class="assess-page">
    <div class="page-head">
        <div>
            <h1>🔒 Assessment Terkunci</h1>
            <p>Kamu sudah pernah mengisi self-assessment sebelumnya.</p>
        </div>
        <a href="/assessment" class="btn btn-outline-secondary btn-sm">&larr; Kembali</a>
    </div>

    <div class="assess-card assess-card-body">
        <div class="alert alert-warning mb-4">
            Untuk menjaga keakuratan pemantauan kondisimu, pengisian self-assessment berikutnya
            hanya dapat dilakukan setelah kamu berkonsultasi dan mendapatkan rekomendasi dari
            konselor kampus.
        </div>

        <h5 class="mb-2">Langkah selanjutnya</h5>
        <ol class="mb-4">
            <li>Ajukan booking konseling dengan salah satu konselor kampus.</li>
            <li>Ikuti sesi konsultasi hingga selesai.</li>
            <li>Jika konselor merekomendasikan, kamu akan bisa mengisi assessment kembali.</li>
        </ol>

        <div class="d-flex gap-2 flex-wrap">
            <a href="/counselor" class="btn btn-primary">Cari Konselor</a>
            <a href="/bookings" class="btn btn-outline-secondary">Lihat Booking Saya</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Assessment Terkunci';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
