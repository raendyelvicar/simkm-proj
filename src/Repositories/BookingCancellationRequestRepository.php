<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\BookingCancellationRequest;
use mysqli;

// Backs the student "batalkan booking" -> Admin approval flow. A request row always
// carries the booking's previous_status so a rejection can put the booking back exactly
// where it was (see AdminBookingCancellationController::reject()).
class BookingCancellationRequestRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function create(int $bookingId, string $previousStatus, ?string $reason): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO booking_cancellation_requests (booking_id, previous_status, reason) VALUES (?, ?, ?)'
        );
        $stmt->bind_param('iss', $bookingId, $previousStatus, $reason);
        $stmt->execute();

        return (int) $this->db->insert_id;
    }

    public function hasPendingForBooking(int $bookingId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM booking_cancellation_requests WHERE booking_id = ? AND status = 'Pending' LIMIT 1"
        );
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();

        return (bool) $stmt->get_result()->fetch_row();
    }

    public function findPendingById(int $id): ?BookingCancellationRequest
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM booking_cancellation_requests WHERE id = ? AND status = 'Pending' LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new BookingCancellationRequest($row) : null;
    }

    private const SORTABLE = [
        'requested_at' => 'r.created_at',
        'date'         => 'b.date',
        'student_name' => 'su.name',
    ];

    /**
     * Pending cancellation requests awaiting Admin review — backs /admin/booking-cancellations.
     * @param array $filters ['search'=>?]
     * @return array{items: array, total: int}
     */
    public function paginatedPending(array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = " WHERE r.status = 'Pending'";
        $params = [];
        $types = '';

        if (!empty($filters['search'])) {
            $where .= ' AND (su.name LIKE ? OR su.student_number LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like]);
            $types .= 'ss';
        }

        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) AS c
             FROM booking_cancellation_requests r
             JOIN counseling_bookings b ON b.booking_id = r.booking_id
             JOIN users su ON su.id = b.user_id
             {$where}"
        );
        if ($params) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::SORTABLE[$sort] ?? 'r.created_at';
        $orderDir = $dir === 'asc' ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $perPage;

        $dataStmt = $this->db->prepare(
            "SELECT r.*, b.date, b.start_time, b.end_time,
                    su.name AS student_name, su.student_number,
                    ku.name AS counselor_name
             FROM booking_cancellation_requests r
             JOIN counseling_bookings b ON b.booking_id = r.booking_id
             JOIN users su ON su.id = b.user_id
             JOIN counselors k ON k.counselor_id = b.counselor_id
             JOIN users ku ON ku.id = k.user_id
             {$where}
             ORDER BY {$orderCol} {$orderDir}
             LIMIT ? OFFSET ?"
        );
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        $items = [];
        $result = $dataStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = array_merge((new BookingCancellationRequest($row))->toArray(), [
                'date' => $row['date'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'student_name' => $row['student_name'],
                'student_number' => $row['student_number'],
                'counselor_name' => $row['counselor_name'],
            ]);
        }

        return ['items' => $items, 'total' => $total];
    }

    public function approve(int $id, int $adminId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE booking_cancellation_requests SET status = 'Approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?"
        );
        $stmt->bind_param('ii', $adminId, $id);
        $stmt->execute();
    }

    public function reject(int $id, int $adminId, ?string $adminNotes): void
    {
        $stmt = $this->db->prepare(
            "UPDATE booking_cancellation_requests SET status = 'Rejected', admin_notes = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?"
        );
        $stmt->bind_param('sii', $adminNotes, $adminId, $id);
        $stmt->execute();
    }
}
