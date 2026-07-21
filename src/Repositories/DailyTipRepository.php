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

    private const SORTABLE = [
        'title'      => 'title',
        'created_at' => 'created_at',
    ];

    /**
     * Search/sort/paginate the daily-tips pool — backs /tips.
     * @return array{items: array, total: int}
     */
    public function paginated(array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = ' WHERE 1=1';
        $params = [];
        $types = '';

        if (!empty($filters['search'])) {
            $where .= ' AND (title LIKE ? OR content LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like]);
            $types .= 'ss';
        }
        if (($filters['is_active'] ?? '') !== '') {
            $where .= ' AND is_active = ?';
            $params[] = (int) $filters['is_active'];
            $types .= 'i';
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) AS c FROM daily_tips{$where}");
        if ($params) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::SORTABLE[$sort] ?? 'created_at';
        $orderDir = $dir === 'asc' ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $perPage;

        $dataStmt = $this->db->prepare("SELECT * FROM daily_tips{$where} ORDER BY {$orderCol} {$orderDir} LIMIT ? OFFSET ?");
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        $items = [];
        $result = $dataStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = new DailyTip($row);
        }

        return ['items' => $items, 'total' => $total];
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
