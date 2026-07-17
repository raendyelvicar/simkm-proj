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

    // All schedule slots for a konselor (konselor.konselor_id, not users.id).
    public function allByKonselorId(int $konselorId, bool $onlyActive = false): array
    {
        $sql = 'SELECT * FROM konselor_jadwal WHERE konselor_id = ?';
        if ($onlyActive) {
            $sql .= ' AND status_aktif = 1';
        }
        $sql .= ' ORDER BY tanggal, jam_mulai';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $konselorId);
        $stmt->execute();
        $result = $stmt->get_result();

        $slots = [];
        while ($row = $result->fetch_assoc()) {
            $slots[] = new KonselorJadwal($row);
        }

        return $slots;
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
