<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\CounselingBooking;
use mysqli;

class CounselingBookingRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function create(
        int $userId,
        int $counselorId,
        int $scheduleId,
        string $date,
        string $jamMulai,
        string $jamSelesai,
        ?string $complaint
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO counseling_bookings (user_id, counselor_id, schedule_id, date, start_time, end_time, complaint, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')"
        );
        $stmt->bind_param('iiissss', $userId, $counselorId, $scheduleId, $date, $jamMulai, $jamSelesai, $complaint);
        $stmt->execute();

        return (int) $this->db->insert_id;
    }

    // Unlike findOwnedByStudent/findOwnedByCounselor, this has no ownership check — only
    // for admin-side flows (e.g. AdminBookingCancellationController) that act on any booking.
    public function findById(int $bookingId): ?CounselingBooking
    {
        $stmt = $this->db->prepare('SELECT * FROM counseling_bookings WHERE booking_id = ? LIMIT 1');
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new CounselingBooking($row) : null;
    }

    public function findOwnedByStudent(int $bookingId, int $userId): ?CounselingBooking
    {
        $stmt = $this->db->prepare('SELECT * FROM counseling_bookings WHERE booking_id = ? AND user_id = ? LIMIT 1');
        $stmt->bind_param('ii', $bookingId, $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new CounselingBooking($row) : null;
    }

    public function findOwnedByCounselor(int $bookingId, int $counselorId): ?CounselingBooking
    {
        $stmt = $this->db->prepare('SELECT * FROM counseling_bookings WHERE booking_id = ? AND counselor_id = ? LIMIT 1');
        $stmt->bind_param('ii', $bookingId, $counselorId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new CounselingBooking($row) : null;
    }

    private const STUDENT_SORTABLE = [
        'date'       => 'b.date',
        'status'        => 'b.status',
        'counselor_name' => 'u.name',
    ];

    /**
     * A student's own bookings, filter/sort/paginated — backs /bookings. Includes the
     * counselor's display name, users.id (for the chat link), and the current
     * monitoring window (if any) joined in.
     * @param array $filters ['status'=>?]
     * @return array{items: array, total: int}
     */
    public function paginatedForStudent(int $userId, array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = ' WHERE b.user_id = ?';
        $params = [$userId];
        $types = 'i';

        if (!empty($filters['status'])) {
            $where .= ' AND b.status = ?';
            $params[] = $filters['status'];
            $types .= 's';
        }

        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) AS c FROM counseling_bookings b JOIN counselors k ON k.counselor_id = b.counselor_id{$where}"
        );
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::STUDENT_SORTABLE[$sort] ?? 'b.created_at';
        $orderDir = $dir === 'asc' ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $perPage;

        $dataStmt = $this->db->prepare(
            "SELECT b.*, u.name AS counselor_name, u.id AS konselor_user_id, mp.end_date AS monitoring_end
             FROM counseling_bookings b
             JOIN counselors k ON k.counselor_id = b.counselor_id
             JOIN users u ON u.id = k.user_id
             LEFT JOIN monitoring_periods mp ON mp.booking_id = b.booking_id
             {$where}
             ORDER BY {$orderCol} {$orderDir}
             LIMIT ? OFFSET ?"
        );
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        return ['items' => $this->hydrateAll($dataStmt->get_result()), 'total' => $total];
    }

    // Bookings for a counselor (counselor.counselor_id), with the student's display name/student_number joined in.
    public function forCounselor(int $counselorId, string $status): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, u.name AS student_name, u.student_number AS student_number
             FROM counseling_bookings b
             JOIN users u ON u.id = b.user_id
             WHERE b.counselor_id = ? AND b.status = ?
             ORDER BY b.created_at ASC'
        );
        $stmt->bind_param('is', $counselorId, $status);
        $stmt->execute();

        return $this->hydrateAll($stmt->get_result());
    }

    private const QUEUE_SORTABLE = [
        'date'      => 'b.date',
        'status'       => 'b.status',
        'student_name' => 'u.name',
    ];

    // The counselor's booking queue: Pending, Confirmed ("On Progress"), and Completed
    // bookings, searchable/sortable/paginated. Cancelled/No Show bookings are left out
    // — they're closed out, nothing left to manage. Default sort ('queue') groups by
    // status priority then oldest-first within each group, same as the original
    // unpaginated ordering this replaces.
    // @return array{items: array, total: int}
    public function paginatedQueue(int $counselorId, array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = " WHERE b.counselor_id = ? AND b.status IN ('Pending', 'Confirmed', 'Completed')";
        $params = [$counselorId];
        $types = 'i';

        if (!empty($filters['search'])) {
            $where .= ' AND (u.name LIKE ? OR u.student_number LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like]);
            $types .= 'ss';
        }

        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) AS c FROM counseling_bookings b JOIN users u ON u.id = b.user_id{$where}"
        );
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderDir = $dir === 'desc' ? 'DESC' : 'ASC';
        $orderBy = isset(self::QUEUE_SORTABLE[$sort])
            ? self::QUEUE_SORTABLE[$sort] . " {$orderDir}"
            : "FIELD(b.status, 'Pending', 'Confirmed', 'Completed'), b.created_at ASC";
        $offset = ($page - 1) * $perPage;

        $dataStmt = $this->db->prepare(
            "SELECT b.*, u.name AS student_name, u.student_number AS student_number, mp.end_date AS monitoring_end
             FROM counseling_bookings b
             JOIN users u ON u.id = b.user_id
             LEFT JOIN monitoring_periods mp ON mp.booking_id = b.booking_id
             {$where}
             ORDER BY {$orderBy}
             LIMIT ? OFFSET ?"
        );
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        return ['items' => $this->hydrateAll($dataStmt->get_result()), 'total' => $total];
    }

    public function updateStatus(int $bookingId, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE counseling_bookings SET status = ? WHERE booking_id = ?');
        $stmt->bind_param('si', $status, $bookingId);
        $stmt->execute();
    }

    // Dedupe guard: a student can't have more than one open request to the same counselor
    // at once. A booking stuck in 'Cancellation Requested' still counts as open — it isn't
    // actually cancelled until Admin approves it.
    public function hasOpenBooking(int $userId, int $counselorId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM counseling_bookings
             WHERE user_id = ? AND counselor_id = ? AND status IN ('Pending','Confirmed','Cancellation Requested') LIMIT 1"
        );
        $stmt->bind_param('ii', $userId, $counselorId);
        $stmt->execute();

        return (bool) $stmt->get_result()->fetch_row();
    }

    // A schedule_id now maps to exactly one date, so capacity is just "how many
    // non-cancelled bookings already claim this slot" vs. its quota.
    public function hasCapacity(int $scheduleId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT j.quota,
                    (SELECT COUNT(*) FROM counseling_bookings
                     WHERE schedule_id = j.schedule_id AND status <> 'Cancelled') AS booked
             FROM counselor_schedules j
             WHERE j.schedule_id = ?
             LIMIT 1"
        );
        $stmt->bind_param('i', $scheduleId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) {
            return false;
        }

        return (int) $row['booked'] < (int) $row['quota'];
    }

    private function hydrateAll(\mysqli_result $result): array
    {
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = array_merge((new CounselingBooking($row))->toArray(), array_filter([
                'counselor_name' => $row['counselor_name'] ?? null,
                'konselor_user_id' => isset($row['konselor_user_id']) ? (int) $row['konselor_user_id'] : null,
                'student_name' => $row['student_name'] ?? null,
                'student_number' => $row['student_number'] ?? null,
                'monitoring_end' => $row['monitoring_end'] ?? null,
            ], fn ($v) => $v !== null));
        }

        return $bookings;
    }
}
