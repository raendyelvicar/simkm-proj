<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\KonselorJadwal;
use mysqli;

class KonselorJadwalRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    private const SORTABLE = [
        'tanggal'    => 'tanggal',
        'jam_mulai'  => 'jam_mulai',
        'status_aktif' => 'status_aktif',
    ];

    /**
     * Search/filter/sort/paginate a konselor's schedule slots — backs both /schedule
     * (konselor's own view) and /admin/counselors/{id}/schedule (admin view).
     * @param array $filters ['date_from'=>?, 'date_to'=>?, 'status_aktif'=>'1'|'0'|null]
     * @return array{items: array, total: int}
     */
    public function paginatedByKonselorId(int $konselorId, array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = ' WHERE konselor_id = ?';
        $params = [$konselorId];
        $types = 'i';

        if (!empty($filters['date_from'])) {
            $where .= ' AND tanggal >= ?';
            $params[] = $filters['date_from'];
            $types .= 's';
        }
        if (!empty($filters['date_to'])) {
            $where .= ' AND tanggal <= ?';
            $params[] = $filters['date_to'];
            $types .= 's';
        }
        if (($filters['status_aktif'] ?? '') !== '') {
            $where .= ' AND status_aktif = ?';
            $params[] = (int) $filters['status_aktif'];
            $types .= 'i';
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) AS c FROM konselor_jadwal{$where}");
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::SORTABLE[$sort] ?? 'tanggal';
        $orderDir = $dir === 'desc' ? 'DESC' : 'ASC';
        $offset = ($page - 1) * $perPage;

        $dataStmt = $this->db->prepare(
            "SELECT * FROM konselor_jadwal{$where} ORDER BY {$orderCol} {$orderDir}, jam_mulai ASC LIMIT ? OFFSET ?"
        );
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        $items = [];
        $result = $dataStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = (new KonselorJadwal($row))->toArray();
        }

        return ['items' => $items, 'total' => $total];
    }

    // The booking picker's data source: active, upcoming (or today), and still with
    // room — a slot with sisa_kuota <= 0 is fully booked and shouldn't be offered.
    public function availableForBooking(int $konselorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT j.*, j.kuota - COALESCE(bk.taken, 0) AS sisa_kuota
             FROM konselor_jadwal j
             LEFT JOIN (
                 SELECT jadwal_id, COUNT(*) AS taken FROM booking_konseling
                 WHERE status <> 'Cancelled' GROUP BY jadwal_id
             ) bk ON bk.jadwal_id = j.jadwal_id
             WHERE j.konselor_id = ? AND j.status_aktif = 1 AND j.tanggal >= CURDATE()
               AND j.kuota - COALESCE(bk.taken, 0) > 0
             ORDER BY j.tanggal, j.jam_mulai"
        );
        $stmt->bind_param('i', $konselorId);
        $stmt->execute();
        $result = $stmt->get_result();

        $slots = [];
        while ($row = $result->fetch_assoc()) {
            $slots[] = array_merge((new KonselorJadwal($row))->toArray(), [
                'sisa_kuota' => (int) $row['sisa_kuota'],
            ]);
        }

        return $slots;
    }

    // Looked up by jadwal_id but scoped to the owning konselor, so a konselor can't touch another's slot.
    public function findOwned(int $jadwalId, int $konselorId): ?KonselorJadwal
    {
        $stmt = $this->db->prepare('SELECT * FROM konselor_jadwal WHERE jadwal_id = ? AND konselor_id = ? LIMIT 1');
        $stmt->bind_param('ii', $jadwalId, $konselorId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new KonselorJadwal($row) : null;
    }

    public function create(int $konselorId, string $tanggal, string $jamMulai, string $jamSelesai, int $kuota): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO konselor_jadwal (konselor_id, tanggal, jam_mulai, jam_selesai, kuota, status_aktif)
             VALUES (?, ?, ?, ?, ?, 1)'
        );
        $stmt->bind_param('isssi', $konselorId, $tanggal, $jamMulai, $jamSelesai, $kuota);
        $stmt->execute();

        return (int) $this->db->insert_id;
    }

    public function setActive(int $jadwalId, bool $active): void
    {
        $status = $active ? 1 : 0;
        $stmt = $this->db->prepare('UPDATE konselor_jadwal SET status_aktif = ? WHERE jadwal_id = ?');
        $stmt->bind_param('ii', $status, $jadwalId);
        $stmt->execute();
    }
}
