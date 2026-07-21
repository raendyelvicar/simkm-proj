<?php

/**
 * Test-data seeder covering three scenarios at once, spread across 6 different
 * konselor accounts:
 *
 *  - 20 fresh mahasiswa each get a 45-consecutive-day diary streak (structured
 *    entries, realistic varied content).
 *  - 15 fresh mahasiswa (a different pool from the diary group) each get 2
 *    Completed bookings + monitoring periods + sesi_konseling notes, round-robined
 *    across 6 konselor accounts.
 *  - Of those 15, the first 10 also get the full retake-gate story: a first
 *    self-assessment session (pre-dating their bookings), a konselor-granted
 *    retake recommendation on their first booking, and a second ("retake")
 *    session that consumes the grant — showing improvement over the first.
 *
 * Only touches mahasiswa accounts with zero existing diary/booking/assessment
 * history, so it's safe to re-run (it will simply pick a fresh/smaller pool, or
 * abort if fewer than 35 untouched mahasiswa remain).
 *
 * Usage: php database/seeders/seed_diary_booking_retake.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database;
use App\Repositories\AssessmentRetakeGrantRepository;
use App\Repositories\DiaryRepository;
use App\Repositories\MonitoringPeriodRepository;
use App\Repositories\SesiKonselingRepository;
use App\Services\AssessmentScoringService;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->safeLoad();

$db = Database::connection();
$scoring = new AssessmentScoringService();
$diaryRepo = new DiaryRepository();
$monitoringRepo = new MonitoringPeriodRepository();
$sesiRepo = new SesiKonselingRepository();
$grantRepo = new AssessmentRetakeGrantRepository();

const BDI2_RANGES = [
    'Minimal' => [0, 13],
    'Ringan'  => [14, 19],
    'Sedang'  => [20, 28],
    'Berat'   => [29, 63],
];
const PWB_RANGES = [
    'Rendah' => [30, 59],
    'Sedang' => [60, 79],
    'Tinggi' => [80, 98],
];
const PWB_DIM_MAX = 18;
const PWB_MAX = 108;

function distributeAcrossDimensions(int $total, int $count): array
{
    $remaining = $total;
    $scores = [];
    for ($i = 0; $i < $count; $i++) {
        $slotsLeft = $count - $i;
        $maxForThis = min(PWB_DIM_MAX, $remaining);
        $minForThis = max(0, $remaining - PWB_DIM_MAX * ($slotsLeft - 1));
        $score = $slotsLeft === 1 ? $remaining : random_int($minForThis, max($minForThis, $maxForThis));
        $scores[] = $score;
        $remaining -= $score;
    }

    return $scores;
}

/** Inserts one bdi2+pwb submission pair (backdated) and an assessment_sessions row linking them. Returns the session id. */
function seedAssessmentSession(
    mysqli $db,
    AssessmentScoringService $scoring,
    int $userId,
    string $pwbCategory,
    string $bdi2Category,
    string $submittedAt
): int {
    [$bdiMin, $bdiMax] = BDI2_RANGES[$bdi2Category];
    $bdi2Total = random_int($bdiMin, $bdiMax);

    $stmt = $db->prepare(
        'INSERT INTO assessment_submissions (user_id, type, total_score, max_score, category, category_percentage, dimension_scores, is_timed_out, submitted_at)
         VALUES (?, ?, ?, ?, ?, NULL, NULL, 0, ?)'
    );
    $type = 'bdi2';
    $max = 63;
    $stmt->bind_param('isiiss', $userId, $type, $bdi2Total, $max, $bdi2Category, $submittedAt);
    $stmt->execute();
    $bdi2Id = (int) $db->insert_id;

    [$pctMin, $pctMax] = PWB_RANGES[$pwbCategory];
    $percentage = random_int($pctMin, $pctMax);
    $pwbTotal = (int) round($percentage / 100 * PWB_MAX);

    $dims = array_keys(AssessmentScoringService::PWB_DIMENSIONS);
    $dimScores = distributeAcrossDimensions($pwbTotal, count($dims));
    $dimensionScores = [];
    foreach ($dims as $idx => $dimKey) {
        $dScore = $dimScores[$idx];
        $dPct = round($dScore / PWB_DIM_MAX * 100, 2);
        $dCategory = $scoring->pwbCategoryFromPercentage($dPct);
        $dimensionScores[$dimKey] = [
            'label' => AssessmentScoringService::PWB_DIMENSIONS[$dimKey], 'score' => $dScore,
            'max_score' => PWB_DIM_MAX, 'percentage' => $dPct, 'category' => $dCategory,
            'feedback' => $scoring->pwbDimensionFeedback($dimKey, $dCategory),
        ];
    }
    $dimensionJson = json_encode($dimensionScores);

    $stmt2 = $db->prepare(
        'INSERT INTO assessment_submissions (user_id, type, total_score, max_score, category, category_percentage, dimension_scores, is_timed_out, submitted_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?)'
    );
    $type = 'pwb';
    $maxPwb = PWB_MAX;
    $pctFloat = (float) $percentage;
    $stmt2->bind_param('isiisdss', $userId, $type, $pwbTotal, $maxPwb, $pwbCategory, $pctFloat, $dimensionJson, $submittedAt);
    $stmt2->execute();
    $pwbId = (int) $db->insert_id;

    $expiresAt = date('Y-m-d H:i:s', strtotime($submittedAt) + 2700);
    $stmt3 = $db->prepare(
        "INSERT INTO assessment_sessions (user_id, status, time_limit_seconds, started_at, expires_at, bdi2_submission_id, pwb_submission_id, finalized_at)
         VALUES (?, 'completed', 2700, ?, ?, ?, ?, ?)"
    );
    $stmt3->bind_param('issiis', $userId, $submittedAt, $expiresAt, $bdi2Id, $pwbId, $submittedAt);
    $stmt3->execute();

    return (int) $db->insert_id;
}

// ---- 1. Pick fresh candidate pools (no existing diary/booking/assessment history) ----
$usedRows = $db->query(
    "SELECT DISTINCT user_id FROM (
        SELECT user_id FROM diary_entries
        UNION SELECT user_id FROM booking_konseling
        UNION SELECT user_id FROM assessment_submissions
    ) x"
)->fetch_all(MYSQLI_ASSOC);
$usedIds = array_map('intval', array_column($usedRows, 'user_id')) ?: [0];

$placeholders = implode(',', array_fill(0, count($usedIds), '?'));
$stmt = $db->prepare("SELECT id, nama FROM users WHERE role = 'mahasiswa' AND status = 'active' AND id NOT IN ({$placeholders}) ORDER BY id LIMIT 35");
$stmt->bind_param(str_repeat('i', count($usedIds)), ...$usedIds);
$stmt->execute();
$pool = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (count($pool) < 35) {
    fwrite(STDERR, 'Only ' . count($pool) . " fresh mahasiswa available; need 35 (20 diary + 15 booking). Aborting.\n");
    exit(1);
}

$diaryUsers = array_slice($pool, 0, 20);
$bookingUsers = array_slice($pool, 20, 15);
$retakeUsers = array_slice($bookingUsers, 0, 10);
$retakeUserIds = array_column($retakeUsers, 'id');

// ---- 2. Pick 6 konselor ----
$konselors = $db->query(
    'SELECT k.konselor_id, u.nama FROM konselor k JOIN users u ON u.id = k.user_id ORDER BY k.konselor_id LIMIT 6'
)->fetch_all(MYSQLI_ASSOC);

if (count($konselors) < 6) {
    fwrite(STDERR, 'Only ' . count($konselors) . " konselor accounts exist; need 6. Aborting.\n");
    exit(1);
}

echo "Konselor used: " . implode(', ', array_column($konselors, 'nama')) . "\n\n";

// ================= Scenario 1: 20 students, 45-day diary streak =================
$situasiPool = [
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
];
$pikiranPool = [
    'Saya pasti tidak akan bisa menyelesaikan semuanya tepat waktu.',
    'Mungkin saya memang kurang mampu dibanding teman-teman lain.',
    'Ini hanya masalah kecil, saya bisa memperbaikinya besok.',
    'Saya merasa didukung dan yakin bisa melewati ini.',
    'Kenapa semua orang sepertinya sibuk dan saya sendirian.',
    'Kerja keras saya akhirnya membuahkan hasil.',
];
$perilakuPool = [
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

$diaryStart = new DateTime('-44 days');
$diaryCount = 0;

foreach ($diaryUsers as $u) {
    for ($d = 0; $d < 45; $d++) {
        $date = (clone $diaryStart)->modify("+{$d} days")->format('Y-m-d');
        $seed = ($u['id'] + $d);

        $emosi = array_slice($emotionPool, $seed % count($emotionPool), 2);
        if (count($emosi) < 2) {
            $emosi = array_merge($emosi, array_slice($emotionPool, 0, 2 - count($emosi)));
        }
        $reaksi = [$reaksiPool[$seed % count($reaksiPool)]];

        $diaryRepo->create(
            (int) $u['id'],
            $date,
            $situasiPool[$seed % count($situasiPool)],
            $pikiranPool[$seed % count($pikiranPool)],
            $emosi,
            null,
            1 + ($seed % 5),
            $reaksi,
            null,
            $perilakuPool[$seed % count($perilakuPool)],
            $reflectionPool[$seed % count($reflectionPool)],
            $gratitudePool[$seed % count($gratitudePool)],
            $rencanaPool[$seed % count($rencanaPool)],
            true,
            null
        );
        $diaryCount++;
    }
    echo "Diary streak seeded for {$u['nama']} (45 entries, {$diaryStart->format('d M Y')} onward)\n";
}
echo "\nTotal diary entries inserted: {$diaryCount}\n\n";

// ================= Scenario 2 + 3: bookings (+ retake for first 10) =================
$keluhanPool = [
    'Merasa cemas berlebihan menjelang ujian dan sulit berkonsentrasi.',
    'Kesulitan mengatur waktu antara kuliah dan kegiatan organisasi.',
    'Merasa tertekan dengan progres skripsi yang lambat.',
    'Mengalami kesulitan tidur beberapa minggu terakhir.',
    'Merasa kurang percaya diri saat berinteraksi dengan teman baru.',
];
$catatanPool = [
    'Mahasiswa menunjukkan keterbukaan yang baik selama sesi berlangsung.',
    'Progres cukup baik, mahasiswa mulai menerapkan teknik relaksasi yang diajarkan.',
    'Mahasiswa masih memerlukan pendampingan lanjutan untuk manajemen stres.',
];
$rekomendasiPool = [
    'Melanjutkan latihan pernapasan dan menjaga pola tidur.',
    'Mencoba menuliskan diary harian untuk mengenali pemicu stres.',
    'Menjaga komunikasi terbuka dengan keluarga/teman terdekat.',
];
$tindakLanjutPool = [
    'Jadwalkan sesi lanjutan dalam 2 minggu.',
    'Pantau perkembangan melalui diary yang dibagikan.',
    '',
];

// Initial risk (moderate-to-high) vs. retake risk (improved) target pairs, cycled.
$initialTargets = [
    ['Sedang', 'Sedang'], ['Rendah', 'Sedang'], ['Sedang', 'Berat'], ['Rendah', 'Berat'], ['Sedang', 'Ringan'],
    ['Rendah', 'Sedang'], ['Sedang', 'Berat'], ['Rendah', 'Ringan'], ['Sedang', 'Sedang'], ['Rendah', 'Berat'],
];
$retakeTargets = [
    ['Tinggi', 'Ringan'], ['Sedang', 'Minimal'], ['Sedang', 'Ringan'], ['Sedang', 'Sedang'], ['Tinggi', 'Minimal'],
    ['Tinggi', 'Ringan'], ['Sedang', 'Ringan'], ['Tinggi', 'Minimal'], ['Tinggi', 'Ringan'], ['Sedang', 'Minimal'],
];

$bookingIndex = 0;
foreach ($bookingUsers as $i => $u) {
    $userId = (int) $u['id'];
    $isRetakeUser = in_array($userId, $retakeUserIds, true);

    $bookingIds = [];
    for ($b = 0; $b < 2; $b++) {
        $konselor = $konselors[$bookingIndex % 6];
        $daysAgo = 60 - ($b * 20) - random_int(0, 5); // first booking ~55-60d ago, second ~35-40d ago
        $tanggal = (new DateTime("-{$daysAgo} days"))->format('Y-m-d');
        $jamMulai = '10:00:00';
        $jamSelesai = '10:45:00';
        $keluhan = $keluhanPool[($userId + $b) % count($keluhanPool)];

        $stmt = $db->prepare(
            "INSERT INTO booking_konseling (user_id, konselor_id, jadwal_id, tanggal, jam_mulai, jam_selesai, keluhan, status, created_at)
             VALUES (?, ?, NULL, ?, ?, ?, ?, 'Completed', ?)"
        );
        $createdAt = $tanggal . ' 09:00:00';
        $stmt->bind_param('iisssss', $userId, $konselor['konselor_id'], $tanggal, $jamMulai, $jamSelesai, $keluhan, $createdAt);
        $stmt->execute();
        $bookingId = (int) $db->insert_id;
        $bookingIds[] = ['id' => $bookingId, 'konselor_id' => (int) $konselor['konselor_id'], 'tanggal' => $tanggal];

        $monitoringRepo->create(
            $bookingId,
            $userId,
            (int) $konselor['konselor_id'],
            $tanggal,
            (new DateTime($tanggal))->modify('+7 days')->format('Y-m-d')
        );

        $sesiRepo->upsertForBooking(
            $bookingId,
            $catatanPool[($userId + $b) % count($catatanPool)],
            $rekomendasiPool[($userId + $b) % count($rekomendasiPool)],
            $tindakLanjutPool[($userId + $b) % count($tindakLanjutPool)] ?: null
        );

        $bookingIndex++;
    }

    if ($isRetakeUser) {
        $idx = array_search($userId, $retakeUserIds, true);
        [$firstPwb, $firstBdi2] = $initialTargets[$idx];
        [$retakePwb, $retakeBdi2] = $retakeTargets[$idx];

        $firstDate = (new DateTime($bookingIds[0]['tanggal']))->modify('-5 days')->format('Y-m-d H:i:s');
        $firstSessionId = seedAssessmentSession($db, $scoring, $userId, $firstPwb, $firstBdi2, $firstDate);

        // Konselor recommends a retake while completing the first booking.
        $grantRepo->grant($userId, $bookingIds[0]['id'], $bookingIds[0]['konselor_id']);

        $retakeDate = (new DateTime($bookingIds[1]['tanggal']))->modify('+3 days')->format('Y-m-d H:i:s');
        $retakeSessionId = seedAssessmentSession($db, $scoring, $userId, $retakePwb, $retakeBdi2, $retakeDate);
        $grantRepo->consumeOldestForUser($userId, $retakeSessionId);

        $firstLevel = $scoring->combinedLevel($firstPwb, $firstBdi2);
        $retakeLevel = $scoring->combinedLevel($retakePwb, $retakeBdi2);
        echo sprintf(
            "%-20s 2 booking w/ %s | first: Level %d (%s) -> retake: Level %d (%s)\n",
            $u['nama'],
            $konselors[($bookingIndex - 2) % 6]['nama'],
            $firstLevel['level'],
            $firstLevel['risk_label'],
            $retakeLevel['level'],
            $retakeLevel['risk_label']
        );
    } else {
        echo sprintf("%-20s 2 booking selesai (tanpa retake assessment)\n", $u['nama']);
    }
}

echo "\nDone.\n";
echo '- Diary streak: ' . count($diaryUsers) . " mahasiswa x 45 hari\n";
echo '- Booking (2x, Completed): ' . count($bookingUsers) . " mahasiswa across 6 konselor\n";
echo '- Retake assessment: ' . count($retakeUsers) . " mahasiswa\n";
