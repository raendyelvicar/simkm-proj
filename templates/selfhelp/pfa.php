<?php ob_start(); ?>

<div class="selfhelp-page">
    <div class="page-head">
        <div>
            <h1>🆘 Pertolongan Psikologis Pertama (PFA)</h1>
            <p>Langkah pertolongan psikologis awal untuk membantumu merasa lebih aman dan tenang saat ini.</p>
        </div>
        <a href="/self-help" class="btn btn-outline-secondary btn-sm">&larr; Kembali</a>
    </div>

    <div class="pfa-emergency-card mb-3">
        <h5 class="mb-2 text-danger">Jika kamu dalam bahaya atau memiliki pikiran untuk menyakiti diri sendiri</h5>
        <p class="mb-2">Segera hubungi salah satu kontak di bawah ini atau minta orang terdekat menemanimu. Kamu tidak sendirian.</p>
        <ul class="mb-3">
            <li><strong>119 ext. 8</strong> &mdash; Layanan Sehat Jiwa (SEJIWA), Kementerian Kesehatan RI</li>
            <li><strong>112</strong> &mdash; Nomor Darurat Nasional</li>
        </ul>
        <div class="d-flex gap-2 flex-wrap">
            <a href="/counselor" class="btn btn-danger btn-sm">Hubungi Konselor Kampus Sekarang</a>
        </div>
        <p class="text-muted small mt-2 mb-0">Catatan: pastikan nomor kontak di atas sudah diverifikasi ulang oleh pihak kampus/admin agar selalu akurat.</p>
    </div>

    <div class="assess-card assess-card-body mb-3">
        <h5 class="mb-3">Teknik Grounding 5-4-3-2-1</h5>
        <p class="text-muted small mb-3">Bantu mengalihkan pikiran dari kecemasan yang berlebihan dengan menyadari sekelilingmu melalui panca indra.</p>

        <div class="pfa-step"><strong>5</strong> &mdash; Sebutkan 5 hal yang bisa kamu <em>lihat</em> di sekitarmu.</div>
        <div class="pfa-step"><strong>4</strong> &mdash; Sebutkan 4 hal yang bisa kamu <em>sentuh</em> atau rasakan teksturnya.</div>
        <div class="pfa-step"><strong>3</strong> &mdash; Sebutkan 3 hal yang bisa kamu <em>dengar</em>.</div>
        <div class="pfa-step"><strong>2</strong> &mdash; Sebutkan 2 hal yang bisa kamu <em>cium</em> baunya.</div>
        <div class="pfa-step mb-0"><strong>1</strong> &mdash; Sebutkan 1 hal yang bisa kamu <em>rasakan</em> (kecap/rasa), atau satu hal baik tentang dirimu.</div>
    </div>

    <div class="assess-card assess-card-body mb-3">
        <h5 class="mb-3">Langkah PFA untuk Diri Sendiri</h5>

        <div class="pfa-step">
            <strong>1. Amati</strong>
            <p class="small text-muted mb-0">Sadari apa yang sedang kamu rasakan saat ini, di tubuh maupun pikiran, tanpa menghakimi diri sendiri.</p>
        </div>
        <div class="pfa-step">
            <strong>2. Tenangkan tubuh</strong>
            <p class="small text-muted mb-0">Tarik napas perlahan (coba <a href="/self-help/breathing">Latihan Pernapasan</a>), cari posisi duduk yang nyaman, dan pastikan kamu berada di tempat yang aman.</p>
        </div>
        <div class="pfa-step">
            <strong>3. Hubungi seseorang</strong>
            <p class="small text-muted mb-0">Hubungi orang yang kamu percaya &mdash; keluarga, teman, atau konselor kampus. Ceritakan apa yang kamu rasakan, sesederhana apa pun itu.</p>
        </div>
        <div class="pfa-step mb-0">
            <strong>4. Jauhkan diri dari hal yang berisiko</strong>
            <p class="small text-muted mb-0">Jika memungkinkan, jauhkan dirimu dari benda atau situasi yang berisiko, dan usahakan untuk tidak sendirian sampai kamu merasa lebih tenang.</p>
        </div>
    </div>

    <div class="assess-card assess-card-body">
        <p class="small text-muted mb-0">Halaman ini bukan pengganti diagnosis atau penanganan profesional. Jika kondisi berlangsung lama atau kamu merasa dalam bahaya, segera cari bantuan tenaga profesional atau layanan darurat.</p>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Pertolongan Psikologis Pertama';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
