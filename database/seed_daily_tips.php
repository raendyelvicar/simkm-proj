<?php

/**
 * Mengisi tabel `daily_tips` dengan 15 tips harian dummy berbahasa Indonesia
 * (judul singkat + isi 1-3 kalimat, sesuai gaya popup yang tampil ke mahasiswa
 * setelah login — lihat templates/dashboard/index.php).
 *
 * Penggunaan: php database/seed_daily_tips.php
 */

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

function seed_env(string $key, ?string $default = null): ?string
{
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mysqli = new mysqli(
    seed_env('DB_HOST', '127.0.0.1'),
    seed_env('DB_USERNAME', 'root'),
    seed_env('DB_PASSWORD', ''),
    seed_env('DB_DATABASE', 'mental_health'),
    (int) seed_env('DB_PORT', '3306')
);
$mysqli->set_charset('utf8mb4');

// Penulis tips dummy ini: akun konselor pertama di seed (lihat mental_health_dump.sql).
$authorId = (int) ($mysqli->query("SELECT id FROM users WHERE username = 'konselor01' LIMIT 1")->fetch_assoc()['id'] ?? 41);

$tips = [
    ['title' => 'Mulai Harimu dengan Napas Dalam', 'content' => 'Sebelum bangun dari tempat tidur, coba tarik napas dalam selama 4 detik, tahan 4 detik, lalu embuskan perlahan. Cara sederhana ini membantu menenangkan pikiran sebelum memulai aktivitas.'],
    ['title' => 'Istirahat Itu Produktif', 'content' => 'Berhenti sejenak bukan berarti malas. Otak yang beristirahat justru lebih siap untuk fokus kembali menyelesaikan tugas.'],
    ['title' => 'Tulis 3 Hal yang Kamu Syukuri', 'content' => 'Sebelum tidur malam ini, coba tuliskan tiga hal kecil yang membuatmu bersyukur hari ini. Kebiasaan sederhana ini terbukti membantu meningkatkan suasana hati.'],
    ['title' => 'Jangan Bandingkan Progresmu dengan Orang Lain', 'content' => 'Setiap orang punya jalan dan waktunya masing-masing. Fokus pada perkembangan dirimu sendiri, bukan pencapaian orang lain.'],
    ['title' => 'Tidur Cukup, Otak Lebih Jernih', 'content' => 'Usahakan tidur 7-8 jam setiap malam. Tidur yang cukup membantu kamu berpikir lebih jernih dan mengelola emosi dengan lebih baik.'],
    ['title' => 'Boleh Kok Bilang Tidak', 'content' => 'Kamu tidak harus menyanggupi semua permintaan orang lain. Menjaga batasan diri adalah bentuk kepedulian terhadap kesehatan mentalmu sendiri.'],
    ['title' => 'Gerak Sedikit, Mood Membaik', 'content' => 'Jalan kaki 10-15 menit di sekitar kos atau kampus bisa membantu menyegarkan pikiran dan memperbaiki suasana hati yang sedang buruk.'],
    ['title' => 'Batasi Waktu Scroll Media Sosial', 'content' => 'Coba beri jeda waktu tanpa media sosial hari ini, misalnya satu jam sebelum tidur. Pikiranmu berhak istirahat dari perbandingan sosial.'],
    ['title' => 'Curhat Bukan Tanda Lemah', 'content' => 'Berbagi cerita dengan teman, keluarga, atau konselor kampus adalah langkah berani, bukan tanda kelemahan. Kamu tidak harus menghadapi semuanya sendirian.'],
    ['title' => 'Rayakan Kemajuan Kecil', 'content' => 'Menyelesaikan satu bab bacaan atau bangun tepat waktu juga layak dirayakan. Kemajuan kecil tetaplah kemajuan.'],
    ['title' => 'Minum Air Putih yang Cukup', 'content' => 'Dehidrasi ringan bisa memengaruhi konsentrasi dan mood tanpa kamu sadari. Jangan lupa minum air putih yang cukup hari ini.'],
    ['title' => 'Satu Tugas dalam Satu Waktu', 'content' => 'Alih-alih memikirkan semua tugas sekaligus, coba fokus menyelesaikan satu hal dulu. Multitasking berlebihan justru bisa meningkatkan stres.'],
    ['title' => 'Maafkan Dirimu Sendiri', 'content' => 'Membuat kesalahan adalah bagian dari proses belajar. Bersikaplah pada dirimu sendiri seperti kamu bersikap pada teman baik yang sedang kesulitan.'],
    ['title' => 'Jaga Hubungan yang Menguatkan', 'content' => 'Luangkan waktu untuk orang-orang yang membuatmu merasa didukung dan nyaman menjadi diri sendiri. Hubungan yang sehat adalah bekal penting untuk kesehatan mental.'],
    ['title' => 'Kenali Tanda Tubuhmu Butuh Jeda', 'content' => 'Sakit kepala, mudah marah, atau sulit fokus bisa jadi sinyal tubuh butuh istirahat. Jangan abaikan, dengarkan dan beri dirimu waktu untuk pulih.'],
];

$existing = (int) ($mysqli->query('SELECT COUNT(*) AS c FROM daily_tips')->fetch_assoc()['c'] ?? 0);
if ($existing > 0) {
    echo "Catatan: tabel daily_tips sudah berisi {$existing} baris — tips baru akan ditambahkan di atasnya (tidak menghapus data lama).\n";
}

$stmt = $mysqli->prepare(
    'INSERT INTO daily_tips (title, content, is_active, created_by) VALUES (?, ?, 1, ?)'
);

$count = 0;
foreach ($tips as $t) {
    $stmt->bind_param('ssi', $t['title'], $t['content'], $authorId);
    $stmt->execute();
    $count++;
    echo "  + {$t['title']}\n";
}

echo "Selesai: {$count} tips harian berhasil ditambahkan.\n";
