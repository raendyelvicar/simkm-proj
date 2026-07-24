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

    private const OWN_SORTABLE = [
        'entry_date' => 'entry_date',
    ];

    /**
     * Search/filter/sort/paginate a student's own diary entries — backs /diary.
     * @param array $filters ['search'=>?, 'date_from'=>?, 'date_to'=>?]
     * @return array{items: array, total: int}
     */
    public function paginatedByUserId(int $userId, array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = ' WHERE user_id = ?';
        $params = [$userId];
        $types = 'i';

        if (!empty($filters['search'])) {
            $where .= ' AND (situation LIKE ? OR initial_thoughts LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like]);
            $types .= 'ss';
        }
        if (!empty($filters['date_from'])) {
            $where .= ' AND entry_date >= ?';
            $params[] = $filters['date_from'];
            $types .= 's';
        }
        if (!empty($filters['date_to'])) {
            $where .= ' AND entry_date <= ?';
            $params[] = $filters['date_to'];
            $types .= 's';
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) AS c FROM diary_entries{$where}");
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::OWN_SORTABLE[$sort] ?? 'entry_date';
        $orderDir = $dir === 'asc' ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $perPage;

        $dataStmt = $this->db->prepare(
            "SELECT * FROM diary_entries{$where} ORDER BY {$orderCol} {$orderDir}, id DESC LIMIT ? OFFSET ?"
        );
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        $items = [];
        $result = $dataStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = new DiaryEntry($row);
        }

        return ['items' => $items, 'total' => $total];
    }

    public function find(int $id): ?DiaryEntry
    {
        $stmt = $this->db->prepare('SELECT * FROM diary_entries WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new DiaryEntry($row) : null;
    }

    private const SHARED_SORTABLE = [
        'entry_date'   => 'd.entry_date',
        'student_name' => 'u.name',
    ];

    // Counselor-side inbox: entries a student explicitly published to this counselor.
    // is_private=0 is redundant with shared_counselor_id being set (the app only ever
    // writes them together), but kept as a defensive filter.
    /**
     * Search/sort/paginate a counselor's shared-diary inbox — backs /shared-diaries.
     * @return array{items: array, total: int}
     */
    public function paginatedSharedWithCounselor(int $counselorId, array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = ' WHERE d.shared_counselor_id = ? AND d.is_private = 0';
        $params = [$counselorId];
        $types = 'i';

        if (!empty($filters['search'])) {
            $where .= ' AND (u.name LIKE ? OR u.student_number LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like]);
            $types .= 'ss';
        }

        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) AS c FROM diary_entries d INNER JOIN users u ON u.id = d.user_id{$where}"
        );
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::SHARED_SORTABLE[$sort] ?? 'd.entry_date';
        $orderDir = $dir === 'asc' ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $perPage;

        $dataStmt = $this->db->prepare(
            "SELECT d.*, u.name AS student_name, u.student_number AS student_number
             FROM diary_entries d
             INNER JOIN users u ON u.id = d.user_id
             {$where}
             ORDER BY {$orderCol} {$orderDir}, d.id DESC
             LIMIT ? OFFSET ?"
        );
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        $items = [];
        $result = $dataStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = $this->hydrateShared($row);
        }

        return ['items' => $items, 'total' => $total];
    }

    // A single shared entry, scoped to the counselor it was shared with — a counselor
    // must never be able to load another counselor's shared entry by guessing the id.
    public function findSharedEntry(int $id, int $counselorId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT d.*, u.name AS student_name, u.student_number AS student_number
             FROM diary_entries d
             INNER JOIN users u ON u.id = d.user_id
             WHERE d.id = ? AND d.shared_counselor_id = ? AND d.is_private = 0
             LIMIT 1'
        );
        $stmt->bind_param('ii', $id, $counselorId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? $this->hydrateShared($row) : null;
    }

    private function hydrateShared(array $row): array
    {
        return array_merge((new DiaryEntry($row))->toArray(), [
            'student_name' => $row['student_name'],
            'student_number'  => $row['student_number'],
        ]);
    }

    // Self Help > Gratitude & Self Reflection view: entries that actually carry
    // gratitude and/or self-reflection content, most recent first.
    public function findWithReflectionByUserId(int $userId, int $limit = 10): array
    {
        return array_slice(array_values(array_filter(
            $this->findByUserId($userId),
            fn (DiaryEntry $entry) => !empty($entry->gratitudeList) || !empty($entry->selfReflection)
        )), 0, $limit);
    }

    public function create(
        int $userId,
        string $entryDate,
        string $situation,
        string $initialThoughts,
        array $emotionsList,
        ?string $otherEmotions,
        int $emotionIntensity,
        array $physicalReactionsList,
        ?string $otherPhysicalReactions,
        string $behavior,
        ?string $selfReflection,
        array $gratitudeList,
        ?string $tomorrowPlan,
        bool $isPrivate,
        ?int $sharedCounselorId
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO diary_entries
            (user_id, entry_date, situation, initial_thoughts, emotions_list, other_emotions, emotion_intensity,
             physical_reactions_list, other_physical_reactions, behavior, self_reflection, gratitude_list,
             tomorrow_plan, is_private, shared_counselor_id, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );

        $emosiJson = json_encode($emotionsList);
        $reaksiJson = json_encode($physicalReactionsList);
        $gratitudeJson = json_encode($gratitudeList);
        $isPrivateInt = $isPrivate ? 1 : 0;

        $stmt->bind_param(
            'isssssissssssii',
            $userId,
            $entryDate,
            $situation,
            $initialThoughts,
            $emosiJson,
            $otherEmotions,
            $emotionIntensity,
            $reaksiJson,
            $otherPhysicalReactions,
            $behavior,
            $selfReflection,
            $gratitudeJson,
            $tomorrowPlan,
            $isPrivateInt,
            $sharedCounselorId
        );
        $stmt->execute();

        return (int) $this->db->insert_id;
    }

    public function update(
        int $id,
        string $entryDate,
        string $situation,
        string $initialThoughts,
        array $emotionsList,
        ?string $otherEmotions,
        int $emotionIntensity,
        array $physicalReactionsList,
        ?string $otherPhysicalReactions,
        string $behavior,
        ?string $selfReflection,
        array $gratitudeList,
        ?string $tomorrowPlan,
        bool $isPrivate,
        ?int $sharedCounselorId
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE diary_entries SET
                entry_date = ?, situation = ?, initial_thoughts = ?, emotions_list = ?, other_emotions = ?,
                emotion_intensity = ?, physical_reactions_list = ?, other_physical_reactions = ?, behavior = ?,
                self_reflection = ?, gratitude_list = ?, tomorrow_plan = ?, is_private = ?, shared_counselor_id = ?
             WHERE id = ?'
        );

        $emosiJson = json_encode($emotionsList);
        $reaksiJson = json_encode($physicalReactionsList);
        $gratitudeJson = json_encode($gratitudeList);
        $isPrivateInt = $isPrivate ? 1 : 0;

        $stmt->bind_param(
            'sssssissssssiii',
            $entryDate,
            $situation,
            $initialThoughts,
            $emosiJson,
            $otherEmotions,
            $emotionIntensity,
            $reaksiJson,
            $otherPhysicalReactions,
            $behavior,
            $selfReflection,
            $gratitudeJson,
            $tomorrowPlan,
            $isPrivateInt,
            $sharedCounselorId,
            $id
        );

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM diary_entries WHERE id = ?');
        $stmt->bind_param('i', $id);

        return $stmt->execute();
    }
}
