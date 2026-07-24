<?php

namespace App\Repositories;

use App\Core\Database;
use mysqli;

/**
 * Gate on retaking the combined self-assessment: a student's very first session is
 * always allowed; every session after that requires an unconsumed "retake grant" —
 * created only when a counselor explicitly recommends it while completing a booking
 * (see BookingQueueController::complete()). One grant = exactly one retake.
 */
class AssessmentRetakeGrantRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function grant(int $userId, int $bookingId, int $counselorId): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO assessment_retake_grants (user_id, booking_id, counselor_id) VALUES (?, ?, ?)'
        );
        $stmt->bind_param('iii', $userId, $bookingId, $counselorId);
        $stmt->execute();
    }

    public function hasUnconsumed(int $userId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM assessment_retake_grants WHERE user_id = ? AND consumed_at IS NULL LIMIT 1'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        return (bool) $stmt->get_result()->fetch_row();
    }

    /** The oldest unconsumed grant, with the recommending counselor's name — for the "start" screen message. */
    public function latestUnconsumed(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT g.*, u.name AS counselor_name
             FROM assessment_retake_grants g
             JOIN counselors k ON k.counselor_id = g.counselor_id
             JOIN users u ON u.id = k.user_id
             WHERE g.user_id = ? AND g.consumed_at IS NULL
             ORDER BY g.granted_at ASC LIMIT 1'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    // Consumes the oldest unconsumed grant for this user. Wrapped in a derived-table
    // subquery (MySQL doesn't allow selecting and updating the same table directly).
    public function consumeOldestForUser(int $userId, int $sessionId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE assessment_retake_grants
             SET consumed_at = NOW(), consumed_session_id = ?
             WHERE id = (
                 SELECT id FROM (
                     SELECT id FROM assessment_retake_grants
                     WHERE user_id = ? AND consumed_at IS NULL
                     ORDER BY granted_at ASC LIMIT 1
                 ) t
             )'
        );
        $stmt->bind_param('ii', $sessionId, $userId);
        $stmt->execute();
    }

    // True until the student has ever finished (completed or timed out) one combined
    // session — i.e. their very first attempt is always unlocked, no grant needed.
    public function isFirstAttempt(int $userId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS c FROM assessment_sessions WHERE user_id = ? AND status IN ('completed', 'timed_out')"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        return (int) ($stmt->get_result()->fetch_assoc()['c'] ?? 0) === 0;
    }

    public function canStartNewSession(int $userId): bool
    {
        return $this->isFirstAttempt($userId) || $this->hasUnconsumed($userId);
    }
}
