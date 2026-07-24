<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\CounselorSchedule;
use mysqli;

class CounselorScheduleRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    private const SORTABLE = [
        'date'    => 'date',
        'start_time'  => 'start_time',
        'is_active' => 'is_active',
    ];

    /**
     * Search/filter/sort/paginate a counselor's schedule slots — backs both /schedule
     * (counselor's own view) and /admin/counselors/{id}/schedule (admin view).
     * @param array $filters ['date_from'=>?, 'date_to'=>?, 'is_active'=>'1'|'0'|null]
     * @return array{items: array, total: int}
     */
    public function paginatedByCounselorId(int $counselorId, array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = ' WHERE counselor_id = ?';
        $params = [$counselorId];
        $types = 'i';

        if (!empty($filters['date_from'])) {
            $where .= ' AND date >= ?';
            $params[] = $filters['date_from'];
            $types .= 's';
        }
        if (!empty($filters['date_to'])) {
            $where .= ' AND date <= ?';
            $params[] = $filters['date_to'];
            $types .= 's';
        }
        if (($filters['is_active'] ?? '') !== '') {
            $where .= ' AND is_active = ?';
            $params[] = (int) $filters['is_active'];
            $types .= 'i';
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) AS c FROM counselor_schedules{$where}");
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::SORTABLE[$sort] ?? 'date';
        $orderDir = $dir === 'desc' ? 'DESC' : 'ASC';
        $offset = ($page - 1) * $perPage;

        $dataStmt = $this->db->prepare(
            "SELECT * FROM counselor_schedules{$where} ORDER BY {$orderCol} {$orderDir}, start_time ASC LIMIT ? OFFSET ?"
        );
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        $items = [];
        $result = $dataStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = (new CounselorSchedule($row))->toArray();
        }

        return ['items' => $items, 'total' => $total];
    }

    // The booking picker's data source: active, upcoming (or today), and still with
    // room — a slot with remaining_quota <= 0 is fully booked and shouldn't be offered.
    public function availableForBooking(int $counselorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT j.*, j.quota - COALESCE(bk.taken, 0) AS remaining_quota
             FROM counselor_schedules j
             LEFT JOIN (
                 SELECT schedule_id, COUNT(*) AS taken FROM counseling_bookings
                 WHERE status <> 'Cancelled' GROUP BY schedule_id
             ) bk ON bk.schedule_id = j.schedule_id
             WHERE j.counselor_id = ? AND j.is_active = 1 AND j.date >= CURDATE()
               AND j.quota - COALESCE(bk.taken, 0) > 0
             ORDER BY j.date, j.start_time"
        );
        $stmt->bind_param('i', $counselorId);
        $stmt->execute();
        $result = $stmt->get_result();

        $slots = [];
        while ($row = $result->fetch_assoc()) {
            $slots[] = array_merge((new CounselorSchedule($row))->toArray(), [
                'remaining_quota' => (int) $row['remaining_quota'],
            ]);
        }

        return $slots;
    }

    // Looked up by schedule_id but scoped to the owning counselor, so a counselor can't touch another's slot.
    public function findOwned(int $scheduleId, int $counselorId): ?CounselorSchedule
    {
        $stmt = $this->db->prepare('SELECT * FROM counselor_schedules WHERE schedule_id = ? AND counselor_id = ? LIMIT 1');
        $stmt->bind_param('ii', $scheduleId, $counselorId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new CounselorSchedule($row) : null;
    }

    public function create(int $counselorId, string $date, string $jamMulai, string $jamSelesai, int $quota): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO counselor_schedules (counselor_id, date, start_time, end_time, quota, is_active)
             VALUES (?, ?, ?, ?, ?, 1)'
        );
        $stmt->bind_param('isssi', $counselorId, $date, $jamMulai, $jamSelesai, $quota);
        $stmt->execute();

        return (int) $this->db->insert_id;
    }

    public function setActive(int $scheduleId, bool $active): void
    {
        $status = $active ? 1 : 0;
        $stmt = $this->db->prepare('UPDATE counselor_schedules SET is_active = ? WHERE schedule_id = ?');
        $stmt->bind_param('ii', $status, $scheduleId);
        $stmt->execute();
    }
}
