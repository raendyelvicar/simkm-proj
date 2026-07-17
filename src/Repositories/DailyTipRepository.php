<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\DailyTip;
use mysqli;

class DailyTipRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(): array
    {
        $result = $this->db->query('SELECT * FROM daily_tips ORDER BY created_at DESC');

        $tips = [];
        while ($row = $result->fetch_assoc()) {
            $tips[] = new DailyTip($row);
        }

        return $tips;
    }

    public function find(int $id): ?DailyTip
    {
        $stmt = $this->db->prepare('SELECT * FROM daily_tips WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new DailyTip($row) : null;
    }

    public function create(string $title, string $content, int $createdBy, bool $isActive): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO daily_tips (title, content, is_active, created_by) VALUES (?, ?, ?, ?)'
        );
        $activeInt = (int) $isActive;
        $stmt->bind_param('ssii', $title, $content, $activeInt, $createdBy);
        $stmt->execute();

        return (int) $this->db->insert_id;
    }

    public function update(int $id, string $title, string $content, bool $isActive): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE daily_tips SET title = ?, content = ?, is_active = ? WHERE id = ?'
        );
        $activeInt = (int) $isActive;
        $stmt->bind_param('sssii', $title, $content, $activeInt, $id);

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM daily_tips WHERE id = ?');
        $stmt->bind_param('i', $id);

        return $stmt->execute();
    }

    // Random active tip for the post-login popup shown to mahasiswa.
    public function randomActive(): ?DailyTip
    {
        $result = $this->db->query(
            'SELECT * FROM daily_tips WHERE is_active = 1 ORDER BY RAND() LIMIT 1'
        );
        $row = $result ? $result->fetch_assoc() : null;

        return $row ? new DailyTip($row) : null;
    }
}
