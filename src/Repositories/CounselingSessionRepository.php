<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\CounselingSession;
use mysqli;

class CounselingSessionRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findByBookingId(int $bookingId): ?CounselingSession
    {
        $stmt = $this->db->prepare('SELECT * FROM counseling_sessions WHERE booking_id = ? LIMIT 1');
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new CounselingSession($row) : null;
    }

    /** @return array<int, CounselingSession> keyed by booking_id, for batch-joining onto a list of bookings. */
    public function findByBookingIds(array $bookingIds): array
    {
        if (!$bookingIds) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($bookingIds), '?'));
        $types = str_repeat('i', count($bookingIds));
        $stmt = $this->db->prepare("SELECT * FROM counseling_sessions WHERE booking_id IN ({$placeholders})");
        $stmt->bind_param($types, ...$bookingIds);
        $stmt->execute();
        $result = $stmt->get_result();

        $session = [];
        while ($row = $result->fetch_assoc()) {
            $session[(int) $row['booking_id']] = new CounselingSession($row);
        }

        return $session;
    }

    // Written once, when a counselor marks a Confirmed booking Completed. Upsert so a
    // re-submit (e.g. editing notes shortly after) doesn't create a duplicate row —
    // booking_id is UNIQUE on this table.
    public function upsertForBooking(
        int $bookingId,
        ?string $counselorNotes,
        ?string $recommendation,
        ?string $followUp
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO counseling_sessions (booking_id, counselor_notes, recommendation, follow_up, completed_at)
             VALUES (?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
                counselor_notes = VALUES(counselor_notes),
                recommendation = VALUES(recommendation),
                follow_up = VALUES(follow_up),
                completed_at = VALUES(completed_at)'
        );
        $stmt->bind_param('isss', $bookingId, $counselorNotes, $recommendation, $followUp);
        $stmt->execute();
    }
}
