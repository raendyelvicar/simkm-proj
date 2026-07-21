<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\ActivityPlan;
use mysqli;

class ActivityPlanRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    private const SORTABLE = [
        'planned_date' => 'planned_date',
        'title'        => 'title',
        'status'       => 'status',
    ];

    /**
     * Filter/sort/paginate a student's own activity plans — backs /self-help/activities.
     * @param array $filters ['status'=>?]
     * @return array{items: array, total: int}
     */
    public function paginatedByUserId(int $userId, array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = ' WHERE user_id = ?';
        $params = [$userId];
        $types = 'i';

        if (!empty($filters['status'])) {
            $where .= ' AND status = ?';
            $params[] = $filters['status'];
            $types .= 's';
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) AS c FROM self_help_activities{$where}");
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::SORTABLE[$sort] ?? 'planned_date';
        $orderDir = $dir === 'asc' ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $perPage;

        $dataStmt = $this->db->prepare(
            "SELECT * FROM self_help_activities{$where} ORDER BY {$orderCol} {$orderDir}, id DESC LIMIT ? OFFSET ?"
        );
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        $items = [];
        $result = $dataStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = new ActivityPlan($row);
        }

        return ['items' => $items, 'total' => $total];
    }

    public function find(int $id): ?ActivityPlan
    {
        $stmt = $this->db->prepare('SELECT * FROM self_help_activities WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new ActivityPlan($row) : null;
    }

    public function create(int $userId, string $title, ?string $description, string $plannedDate, ?int $moodBefore): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO self_help_activities (user_id, title, description, planned_date, mood_before, status)
             VALUES (?, ?, ?, ?, ?, \'planned\')'
        );
        $stmt->bind_param('isssi', $userId, $title, $description, $plannedDate, $moodBefore);
        $stmt->execute();

        return (int) $this->db->insert_id;
    }

    public function complete(int $id, int $moodAfter): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE self_help_activities SET status = 'done', mood_after = ? WHERE id = ?"
        );
        $stmt->bind_param('ii', $moodAfter, $id);

        return $stmt->execute();
    }

    public function skip(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE self_help_activities SET status = 'skipped' WHERE id = ?");
        $stmt->bind_param('i', $id);

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM self_help_activities WHERE id = ?');
        $stmt->bind_param('i', $id);

        return $stmt->execute();
    }
}
