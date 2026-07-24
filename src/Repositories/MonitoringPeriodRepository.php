<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\MonitoringPeriod;
use mysqli;

class MonitoringPeriodRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function create(int $bookingId, int $userId, int $counselorId, string $startDate, string $endDate): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO monitoring_periods (booking_id, user_id, counselor_id, start_date, end_date)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('iiiss', $bookingId, $userId, $counselorId, $startDate, $endDate);
        $stmt->execute();

        return (int) $this->db->insert_id;
    }

    // The chat + diary-share gate: true while this student-counselor pair has a window
    // covering today, from any of their (possibly several, over time) monitoring periods.
    public function hasActive(int $userId, int $counselorId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM monitoring_periods
             WHERE user_id = ? AND counselor_id = ? AND CURDATE() BETWEEN start_date AND end_date
             LIMIT 1'
        );
        $stmt->bind_param('ii', $userId, $counselorId);
        $stmt->execute();

        return (bool) $stmt->get_result()->fetch_row();
    }

    // counselor.counselor_id values currently monitoring this student — drives the counselor-list
    // "chat vs. book" button and the diary "share with" dropdown, without N+1 queries.
    public function activeCounselorIdsForStudent(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT counselor_id FROM monitoring_periods
             WHERE user_id = ? AND CURDATE() BETWEEN start_date AND end_date'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        return array_map('intval', array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'counselor_id'));
    }

    public function forBooking(int $bookingId): ?MonitoringPeriod
    {
        $stmt = $this->db->prepare('SELECT * FROM monitoring_periods WHERE booking_id = ? LIMIT 1');
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new MonitoringPeriod($row) : null;
    }

    // Counselor-initiated extension of an existing period, scoped to the owning counselor.
    public function extend(int $bookingId, int $counselorId, int $days): void
    {
        $stmt = $this->db->prepare(
            'UPDATE monitoring_periods SET end_date = DATE_ADD(end_date, INTERVAL ? DAY)
             WHERE booking_id = ? AND counselor_id = ?'
        );
        $stmt->bind_param('iii', $days, $bookingId, $counselorId);
        $stmt->execute();
    }

    // Caps the window to yesterday — used when a booking is marked Completed/No Show, so
    // access closes immediately (hasActive()'s BETWEEN is inclusive of end_date, so
    // end_date = today would still leave access open for the rest of today).
    public function endNowForBooking(int $bookingId, int $counselorId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE monitoring_periods SET end_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
             WHERE booking_id = ? AND counselor_id = ? AND end_date >= CURDATE()"
        );
        $stmt->bind_param('ii', $bookingId, $counselorId);
        $stmt->execute();
    }
}
