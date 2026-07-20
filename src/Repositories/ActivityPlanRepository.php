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

    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM self_help_activities WHERE user_id = ? ORDER BY planned_date DESC, id DESC'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $activities = [];
        while ($row = $result->fetch_assoc()) {
            $activities[] = new ActivityPlan($row);
        }

        return $activities;
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
