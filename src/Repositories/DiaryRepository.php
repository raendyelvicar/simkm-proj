<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\DiaryEntry;
use mysqli;

class DiaryRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM diary_entries WHERE user_id = ? ORDER BY entry_date DESC, id DESC'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $entries = [];
        while ($row = $result->fetch_assoc()) {
            $entries[] = new DiaryEntry($row);
        }

        return $entries;
    }

    public function find(int $id): ?DiaryEntry
    {
        $stmt = $this->db->prepare('SELECT * FROM diary_entries WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new DiaryEntry($row) : null;
    }

    public function create(
        int $userId,
        string $judul,
        string $moodLevel,
        string $content,
        bool $isPrivate,
        string $entryDate
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO diary_entries (user_id, entry_date, judul, mood_level, content, is_private, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );
        $isPrivateInt = $isPrivate ? 1 : 0;
        $stmt->bind_param('issssi', $userId, $entryDate, $judul, $moodLevel, $content, $isPrivateInt);
        $stmt->execute();

        return (int) $this->db->insert_id;
    }

    public function update(
        int $id,
        string $judul,
        string $moodLevel,
        string $content,
        bool $isPrivate,
        string $entryDate
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE diary_entries SET judul = ?, entry_date = ?, mood_level = ?, content = ?, is_private = ?
             WHERE id = ?'
        );
        $isPrivateInt = $isPrivate ? 1 : 0;
        $stmt->bind_param('ssssii', $judul, $entryDate, $moodLevel, $content, $isPrivateInt, $id);

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM diary_entries WHERE id = ?');
        $stmt->bind_param('i', $id);

        return $stmt->execute();
    }
}
