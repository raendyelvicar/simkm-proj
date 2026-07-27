<?php

/**
 * Test-data seeder for Laporan Diary (counselor view).
 *
 * A counselor's Laporan Diary only ever shows diary_entries rows where
 * shared_counselor_id = their own counselor_id AND is_private = 0 (see
 * ReportRepository::diaryRows() + ReportController::applyScope()). Regular
 * seeded diary streaks (seed_diary_booking_retake.php) are all private, so
 * counselors have nothing to see/export there. This seeder creates, for every
 * counselor account, a batch of structured diary entries explicitly shared
 * with that counselor so each one has data to view and export as PDF.
 *
 * Usage: php database/seeders/seed_diary_shared_counselors.php [entries_per_counselor]
 */

require __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database;
use App\Repositories\DiaryRepository;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->safeLoad();

$db = Database::connection();
$diaryRepo = new DiaryRepository();

$perCounselor = isset($argv[1]) ? max(1, (int) $argv[1]) : 8;

$counselors = $db->query(
    'SELECT k.counselor_id, u.name FROM counselors k JOIN users u ON u.id = k.user_id ORDER BY k.counselor_id'
)->fetch_all(MYSQLI_ASSOC);

if (!$counselors) {
    fwrite(STDERR, "No counselor accounts found. Aborting.\n");
    exit(1);
}

$students = $db->query(
    "SELECT id, name FROM users WHERE role = 'student' AND status = 'active' ORDER BY id"
)->fetch_all(MYSQLI_ASSOC);

$needed = count($counselors) * $perCounselor;
if (count($students) < 1) {
    fwrite(STDERR, "No active student accounts found. Aborting.\n");
    exit(1);
}

$situationPool = [
    'Tugas kuliah menumpuk dan deadline semakin dekat, membuat saya merasa tertekan.',
    'Bertengkar kecil dengan teman satu kelompok soal pembagian tugas.',
    'Presentasi di depan kelas berjalan kurang lancar dari yang saya harapkan.',
    'Menerima kabar baik dari dosen pembimbing tentang progres skripsi.',
    'Merasa kesepian karena teman dekat sedang sibuk dan jarang membalas pesan.',
    'Berhasil menyelesaikan satu bab skripsi lebih cepat dari target.',
    'Cemas menjelang ujian yang akan berlangsung minggu depan.',
    'Menghabiskan waktu bersama keluarga di akhir pekan, merasa lebih tenang.',
    'Kesulitan tidur karena memikirkan banyak tugas yang belum selesai.',
    'Mendapat pujian dari dosen atas hasil kerja kelompok.',
    'Merasa kewalahan mengatur waktu antara kuliah, organisasi, dan kehidupan pribadi.',
    'Konflik kecil dengan orang tua soal rencana setelah lulus kuliah.',
];
$pikiranPool = [
    'Saya pasti tidak akan bisa menyelesaikan semuanya tepat waktu.',
    'Mungkin saya memang kurang mampu dibanding teman-teman lain.',
    'Ini hanya masalah kecil, saya bisa memperbaikinya besok.',
    'Saya merasa didukung dan yakin bisa melewati ini.',
    'Kenapa semua orang sepertinya sibuk dan saya sendirian.',
    'Kerja keras saya akhirnya membuahkan hasil.',
];
$behaviorPool = [
    'Mencoba menenangkan diri dengan menarik napas dalam beberapa kali.',
    'Menunda pekerjaan dan bermain gawai lebih lama dari biasanya.',
    'Menghubungi teman untuk sekadar bercerita dan meminta pendapat.',
    'Melanjutkan mengerjakan tugas meski dengan perasaan berat.',
    'Beristirahat sejenak lalu melanjutkan aktivitas dengan lebih fokus.',
    'Menulis catatan kecil untuk merapikan pikiran yang berantakan.',
];
$reflectionPool = [
    'Saya belajar bahwa tidak semua hal harus selesai sempurna hari ini.',
    'Saya perlu lebih terbuka bercerita ke orang terdekat saat merasa berat.',
    'Ternyata istirahat sejenak membantu saya berpikir lebih jernih.',
    null,
];
$gratitudePool = [
    ['Kesehatan yang masih diberikan', 'Teman yang mau mendengarkan'],
    ['Kesempatan belajar hal baru hari ini'],
    ['Keluarga yang selalu mendukung', 'Cuaca yang cerah'],
    [],
];
$rencanaPool = [
    'Menyusun ulang jadwal belajar agar lebih realistis.',
    'Mengistirahatkan diri lebih awal malam ini.',
    'Menghubungi teman untuk belajar kelompok.',
    null,
];

$emotionPool = ['Sedih', 'Cemas', 'Marah', 'Kecewa', 'Takut', 'Malu', 'Bingung'];
$reaksiPool = ['Jantung berdebar', 'Sulit bernapas', 'Tegang', 'Sulit tidur', 'Pusing', 'Menangis'];

$studentIdx = 0;
$totalCreated = 0;

foreach ($counselors as $c) {
    $counselorId = (int) $c['counselor_id'];

    for ($i = 0; $i < $perCounselor; $i++) {
        $student = $students[$studentIdx % count($students)];
        $studentIdx++;

        $daysAgo = random_int(1, 45);
        $date = (new DateTime("-{$daysAgo} days"))->format('Y-m-d');
        $seed = (int) $student['id'] + $i + $counselorId;

        $emosi = array_slice($emotionPool, $seed % count($emotionPool), 2);
        if (count($emosi) < 2) {
            $emosi = array_merge($emosi, array_slice($emotionPool, 0, 2 - count($emosi)));
        }
        $reaksi = [$reaksiPool[$seed % count($reaksiPool)]];

        $diaryRepo->create(
            (int) $student['id'],
            $date,
            $situationPool[$seed % count($situationPool)],
            $pikiranPool[$seed % count($pikiranPool)],
            $emosi,
            null,
            1 + ($seed % 5),
            $reaksi,
            null,
            $behaviorPool[$seed % count($behaviorPool)],
            $reflectionPool[$seed % count($reflectionPool)],
            $gratitudePool[$seed % count($gratitudePool)],
            $rencanaPool[$seed % count($rencanaPool)],
            false,
            $counselorId
        );
        $totalCreated++;
    }

    echo "Shared {$perCounselor} diary entries with counselor {$c['name']} (counselor_id={$counselorId})\n";
}

echo "\nDone. Total shared diary entries created: {$totalCreated}\n";
