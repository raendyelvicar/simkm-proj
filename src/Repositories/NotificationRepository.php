<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Notification;
use mysqli;

class NotificationRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function create(int $userId, string $type, string $title, string $message, ?string $link = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('issss', $userId, $type, $title, $message, $link);
        $stmt->execute();

        return (int) $this->db->insert_id;
    }

    /** Same notification fanned out to several recipients at once (e.g. every admin). */
    public function createForUsers(array $userIds, string $type, string $title, string $message, ?string $link = null): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)'
        );
        foreach (array_unique(array_map('intval', $userIds)) as $userId) {
            $stmt->bind_param('issss', $userId, $type, $title, $message, $link);
            $stmt->execute();
        }
    }

    /** Newest-first notifications for the bell dropdown — intentionally unpaginated, capped by $limit. */
    public function recentForUser(int $userId, int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?'
        );
        $stmt->bind_param('ii', $userId, $limit);
        $stmt->execute();

        return $this->hydrateAll($stmt->get_result());
    }

    public function unreadCountForUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        return (int) ($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    }

    /** @return array{items: array, total: int} */
    public function paginatedForUser(int $userId, int $page, int $perPage): array
    {
        $countStmt = $this->db->prepare('SELECT COUNT(*) AS c FROM notifications WHERE user_id = ?');
        $countStmt->bind_param('i', $userId);
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?'
        );
        $stmt->bind_param('iii', $userId, $perPage, $offset);
        $stmt->execute();

        return ['items' => $this->hydrateAll($stmt->get_result()), 'total' => $total];
    }

    public function findOwned(int $id, int $userId): ?Notification
    {
        $stmt = $this->db->prepare('SELECT * FROM notifications WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->bind_param('ii', $id, $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new Notification($row) : null;
    }

    public function markRead(int $id, int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ii', $id, $userId);
        $stmt->execute();
    }

    public function markAllRead(int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
    }

    private function hydrateAll(\mysqli_result $result): array
    {
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = new Notification($row);
        }

        return $notifications;
    }
}
