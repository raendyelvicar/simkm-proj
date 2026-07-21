<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\SesiKonseling;
use mysqli;

class SesiKonselingRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findByBookingId(int $bookingId): ?SesiKonseling
    {
        $stmt = $this->db->prepare('SELECT * FROM sesi_konseling WHERE booking_id = ? LIMIT 1');
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new SesiKonseling($row) : null;
    }

    /** @return array<int, SesiKonseling> keyed by booking_id, for batch-joining onto a list of bookings. */
    public function findByBookingIds(array $bookingIds): array
    {
        if (!$bookingIds) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($bookingIds), '?'));
        $types = str_repeat('i', count($bookingIds));
        $stmt = $this->db->prepare("SELECT * FROM sesi_konseling WHERE booking_id IN ({$placeholders})");
        $stmt->bind_param($types, ...$bookingIds);
        $stmt->execute();
        $result = $stmt->get_result();

        $sesi = [];
        while ($row = $result->fetch_assoc()) {
            $sesi[(int) $row['booking_id']] = new SesiKonseling($row);
        }

        return $sesi;
    }

    // Written once, when a konselor marks a Confirmed booking Completed. Upsert so a
    // re-submit (e.g. editing notes shortly after) doesn't create a duplicate row —
    // booking_id is UNIQUE on this table.
    public function upsertForBooking(
        int $bookingId,
        ?string $catatanKonselor,
        ?string $rekomendasi,
        ?string $tindakLanjut
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO sesi_konseling (booking_id, catatan_konselor, rekomendasi, tindak_lanjut, selesai_pada)
             VALUES (?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
                catatan_konselor = VALUES(catatan_konselor),
                rekomendasi = VALUES(rekomendasi),
                tindak_lanjut = VALUES(tindak_lanjut),
                selesai_pada = VALUES(selesai_pada)'
        );
        $stmt->bind_param('isss', $bookingId, $catatanKonselor, $rekomendasi, $tindakLanjut);
        $stmt->execute();
    }
}
