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

    // Konselor-side inbox: entries a student explicitly published to this konselor.
    // is_private=0 is redundant with shared_konselor_id being set (the app only ever
    // writes them together), but kept as a defensive filter.
    public function findSharedWithKonselor(int $konselorId): array
    {
        $stmt = $this->db->prepare(
            'SELECT d.*, u.nama AS student_nama, u.npm AS student_npm
             FROM diary_entries d
             INNER JOIN users u ON u.id = d.user_id
             WHERE d.shared_konselor_id = ? AND d.is_private = 0
             ORDER BY d.entry_date DESC, d.id DESC'
        );
        $stmt->bind_param('i', $konselorId);
        $stmt->execute();
        $result = $stmt->get_result();

        $entries = [];
        while ($row = $result->fetch_assoc()) {
            $entries[] = $this->hydrateShared($row);
        }

        return $entries;
    }

    // A single shared entry, scoped to the konselor it was shared with — a konselor
    // must never be able to load another konselor's shared entry by guessing the id.
    public function findSharedEntry(int $id, int $konselorId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT d.*, u.nama AS student_nama, u.npm AS student_npm
             FROM diary_entries d
             INNER JOIN users u ON u.id = d.user_id
             WHERE d.id = ? AND d.shared_konselor_id = ? AND d.is_private = 0
             LIMIT 1'
        );
        $stmt->bind_param('ii', $id, $konselorId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? $this->hydrateShared($row) : null;
    }

    private function hydrateShared(array $row): array
    {
        return array_merge((new DiaryEntry($row))->toArray(), [
            'student_nama' => $row['student_nama'],
            'student_npm'  => $row['student_npm'],
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
        string $situasi,
        string $pikiranAwal,
        array $emosiList,
        ?string $emosiLainnya,
        int $intensitasEmosi,
        array $reaksiFisikList,
        ?string $reaksiFisikLainnya,
        string $perilaku,
        ?string $selfReflection,
        array $gratitudeList,
        ?string $rencanaBesok,
        bool $isPrivate,
        ?int $sharedKonselorId
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO diary_entries
            (user_id, entry_date, situasi, pikiran_awal, emosi_list, emosi_lainnya, intensitas_emosi,
             reaksi_fisik_list, reaksi_fisik_lainnya, perilaku, self_reflection, gratitude_list,
             rencana_besok, is_private, shared_konselor_id, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );

        $emosiJson = json_encode($emosiList);
        $reaksiJson = json_encode($reaksiFisikList);
        $gratitudeJson = json_encode($gratitudeList);
        $isPrivateInt = $isPrivate ? 1 : 0;

        $stmt->bind_param(
            'isssssissssssii',
            $userId,
            $entryDate,
            $situasi,
            $pikiranAwal,
            $emosiJson,
            $emosiLainnya,
            $intensitasEmosi,
            $reaksiJson,
            $reaksiFisikLainnya,
            $perilaku,
            $selfReflection,
            $gratitudeJson,
            $rencanaBesok,
            $isPrivateInt,
            $sharedKonselorId
        );
        $stmt->execute();

        return (int) $this->db->insert_id;
    }

    public function update(
        int $id,
        string $entryDate,
        string $situasi,
        string $pikiranAwal,
        array $emosiList,
        ?string $emosiLainnya,
        int $intensitasEmosi,
        array $reaksiFisikList,
        ?string $reaksiFisikLainnya,
        string $perilaku,
        ?string $selfReflection,
        array $gratitudeList,
        ?string $rencanaBesok,
        bool $isPrivate,
        ?int $sharedKonselorId
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE diary_entries SET
                entry_date = ?, situasi = ?, pikiran_awal = ?, emosi_list = ?, emosi_lainnya = ?,
                intensitas_emosi = ?, reaksi_fisik_list = ?, reaksi_fisik_lainnya = ?, perilaku = ?,
                self_reflection = ?, gratitude_list = ?, rencana_besok = ?, is_private = ?, shared_konselor_id = ?
             WHERE id = ?'
        );

        $emosiJson = json_encode($emosiList);
        $reaksiJson = json_encode($reaksiFisikList);
        $gratitudeJson = json_encode($gratitudeList);
        $isPrivateInt = $isPrivate ? 1 : 0;

        $stmt->bind_param(
            'sssssissssssiii',
            $entryDate,
            $situasi,
            $pikiranAwal,
            $emosiJson,
            $emosiLainnya,
            $intensitasEmosi,
            $reaksiJson,
            $reaksiFisikLainnya,
            $perilaku,
            $selfReflection,
            $gratitudeJson,
            $rencanaBesok,
            $isPrivateInt,
            $sharedKonselorId,
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
