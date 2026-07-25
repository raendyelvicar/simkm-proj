<?php

/**
 * Fills the empty `self_help_activities` table with dummy "Rencana Aktivitas
 * Positif" plans so Laporan > Aktivitas Self Help has something to show for
 * every role (admin sees everyone, a counselor sees only their own students
 * via ReportRepository::counselorStudentIds(), a student sees only their own).
 *
 * Student pool: every student who already has counseling_bookings (so each
 * counselor's own Laporan Self Help view has data too) plus a batch of extra
 * active students with no self-help history yet, for a broader admin-level view.
 *
 * Each student gets 3-7 activities spread over the last ~40 days (mostly
 * 'done'/'skipped', a realistic mood_before -> mood_after lift on completed
 * ones) plus one or two still 'planned' for today/the near future.
 *
 * Safe to re-run: skips any student who already has rows in self_help_activities.
 *
 * Usage: php database/seeders/seed_self_help_activities.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->safeLoad();

$db = Database::connection();

$activityPool = [
    ['title' => 'Latihan Pernapasan 5 Menit', 'description' => 'Duduk tenang dan melakukan teknik box breathing untuk meredakan ketegangan.'],
    ['title' => 'Menulis Jurnal Gratitude', 'description' => 'Menuliskan tiga hal yang disyukuri hari ini sebelum tidur.'],
    ['title' => 'Jalan Kaki Santai', 'description' => 'Berjalan kaki 15 menit di sekitar kos/kampus untuk menyegarkan pikiran.'],
    ['title' => 'Meditasi Singkat', 'description' => 'Meditasi duduk 10 menit dengan fokus pada napas dan sensasi tubuh.'],
    ['title' => 'Menghubungi Teman Dekat', 'description' => 'Mengobrol dengan teman dekat untuk sekadar bercerita dan melepas penat.'],
    ['title' => 'Membaca Buku Ringan', 'description' => 'Membaca beberapa halaman buku non-kuliah sebagai jeda dari layar.'],
    ['title' => 'Tidur Lebih Awal', 'description' => 'Mencoba tidur sebelum jam 11 malam untuk memperbaiki kualitas istirahat.'],
    ['title' => 'Latihan Grounding 5-4-3-2-1', 'description' => 'Menyadari lingkungan sekitar lewat panca indra saat merasa cemas.'],
    ['title' => 'Me Time Tanpa Gawai', 'description' => 'Meluangkan 30 menit tanpa membuka media sosial atau gawai.'],
    ['title' => 'Mendengarkan Musik Relaksasi', 'description' => 'Mendengarkan playlist musik tenang sambil merebahkan diri sejenak.'],
    ['title' => 'Berkebun / Merawat Tanaman', 'description' => 'Menyiram dan merawat tanaman di kos sebagai pengalih pikiran yang menenangkan.'],
    ['title' => 'Journaling Refleksi Diri', 'description' => 'Menuliskan perasaan dan pikiran hari ini secara bebas tanpa disensor.'],
    ['title' => 'Menonton Film/Series Favorit', 'description' => 'Menonton satu episode series favorit sebagai bentuk istirahat mental.'],
    ['title' => 'Olahraga Ringan di Kamar', 'description' => 'Melakukan peregangan atau olahraga ringan 10-15 menit.'],
    ['title' => 'Merapikan Kamar/Meja Belajar', 'description' => 'Merapikan ruang belajar agar pikiran ikut terasa lebih tertata.'],
];

// ---- 1. Build the student pool ----
$withBookings = array_column(
    $db->query('SELECT DISTINCT user_id FROM counseling_bookings')->fetch_all(MYSQLI_ASSOC),
    'user_id'
);
$withBookings = array_map('intval', $withBookings);

$existing = array_map(
    'intval',
    array_column($db->query('SELECT DISTINCT user_id FROM self_help_activities')->fetch_all(MYSQLI_ASSOC), 'user_id')
);

$priorityPool = array_values(array_diff($withBookings, $existing));

$excludeIds = array_unique(array_merge($withBookings, $existing)) ?: [0];
$placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
$stmt = $db->prepare("SELECT id FROM users WHERE role = 'student' AND status = 'active' AND id NOT IN ({$placeholders}) ORDER BY id LIMIT 20");
$stmt->bind_param(str_repeat('i', count($excludeIds)), ...$excludeIds);
$stmt->execute();
$extraPool = array_map('intval', array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'id'));

$studentIds = array_merge($priorityPool, $extraPool);

if (!$studentIds) {
    echo "Nothing to do: every candidate student already has self_help_activities rows.\n";
    exit(0);
}

// ---- 2. Insert activities per student ----
$insertStmt = $db->prepare(
    'INSERT INTO self_help_activities
        (user_id, title, description, planned_date, mood_before, mood_after, status, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
);

$totalInserted = 0;

foreach ($studentIds as $userId) {
    $activityCount = 3 + ($userId % 5); // 3-7 activities per student

    for ($i = 0; $i < $activityCount; $i++) {
        $seed = $userId * 31 + $i;
        $activity = $activityPool[$seed % count($activityPool)];

        // Last one or two activities per student are still upcoming ("planned");
        // the rest are backdated over the last ~40 days.
        $isUpcoming = $i >= $activityCount - 1 && ($seed % 3 === 0);
        $daysOffset = $isUpcoming ? -random_int(0, 3) : random_int(1, 40);
        $plannedDate = (new DateTime("-{$daysOffset} days"))->format('Y-m-d');

        $moodBefore = 1 + ($seed % 5);

        if ($isUpcoming) {
            $status = 'planned';
            $moodAfter = null;
        } else {
            $roll = $seed % 20;
            // ~60% done, ~25% skipped, ~15% left dangling as 'planned' (never followed up).
            $status = $roll < 12 ? 'done' : ($roll < 17 ? 'skipped' : 'planned');
            $moodAfter = $status === 'done' ? max(1, min(5, $moodBefore + (($seed % 4 === 0) ? -1 : 1))) : null;
        }

        $createdAt = $plannedDate . ' ' . sprintf('%02d:%02d:00', 7 + ($seed % 12), ($seed * 7) % 60);
        $updatedAt = $status === 'planned' ? $createdAt : $createdAt;

        $insertStmt->bind_param(
            'isssiisss',
            $userId,
            $activity['title'],
            $activity['description'],
            $plannedDate,
            $moodBefore,
            $moodAfter,
            $status,
            $createdAt,
            $updatedAt
        );
        $insertStmt->execute();
        $totalInserted++;
    }
}

echo "Done: {$totalInserted} self_help_activities inserted across " . count($studentIds) . " students ";
echo '(' . count($priorityPool) . " with existing bookings, " . count($extraPool) . " extra).\n";
