<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\KonselorJadwal;
use mysqli;

class CounselorScheduleRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    private const SELECT = "
        SELECT
            kj.*,
            k.user_id,
            u.nama,
            u.email
        FROM konselor_jadwal kj
        INNER JOIN konselor k
            ON k.konselor_id = kj.konselor_id
        INNER JOIN users u
            ON u.id = k.user_id
    ";

    public function all(): array
    {
        $result = $this->db->query(
            self::SELECT .
                " ORDER BY u.nama, FIELD(
                kj.hari,
                'Senin',
                'Selasa',
                'Rabu',
                'Kamis',
                'Jumat',
                'Sabtu',
                'Minggu'
            ), kj.jam_mulai"
        );

        $items = [];

        while ($row = $result->fetch_assoc()) {
            $items[] = $this->hydrate($row);
        }

        return $items;
    }

    public function find(int $jadwalId): ?array
    {
        $stmt = $this->db->prepare(
            self::SELECT .
                " WHERE kj.jadwal_id = ?
              LIMIT 1"
        );

        $stmt->bind_param("i", $jadwalId);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        return $row ? $this->hydrate($row) : null;
    }

    public function findByCounselor(int $konselorId): array
    {
        $stmt = $this->db->prepare(
            self::SELECT .
                " WHERE kj.konselor_id = ?
              ORDER BY FIELD(
                kj.hari,
                'Senin',
                'Selasa',
                'Rabu',
                'Kamis',
                'Jumat',
                'Sabtu',
                'Minggu'
              ),
              kj.jam_mulai"
        );

        $stmt->bind_param("i", $konselorId);
        $stmt->execute();

        $result = $stmt->get_result();

        $items = [];

        while ($row = $result->fetch_assoc()) {
            $items[] = $this->hydrate($row);
        }

        return $items;
    }

    public function findActiveByCounselor(int $konselorId): array
    {
        $stmt = $this->db->prepare(
            self::SELECT .
                " WHERE kj.konselor_id = ?
              AND kj.status_aktif = 1
              ORDER BY FIELD(
                kj.hari,
                'Senin',
                'Selasa',
                'Rabu',
                'Kamis',
                'Jumat',
                'Sabtu',
                'Minggu'
              ),
              kj.jam_mulai"
        );

        $stmt->bind_param("i", $konselorId);
        $stmt->execute();

        $result = $stmt->get_result();

        $items = [];

        while ($row = $result->fetch_assoc()) {
            $items[] = $this->hydrate($row);
        }

        return $items;
    }

    public function create(KonselorJadwal $jadwal): int
    {
        $status = $jadwal->statusAktif ? 1 : 0;

        $stmt = $this->db->prepare(
            "INSERT INTO konselor_jadwal
            (
                konselor_id,
                hari,
                jam_mulai,
                jam_selesai,
                kuota,
                status_aktif
            )
            VALUES
            (
                ?,?,?,?,?,?
            )"
        );

        $stmt->bind_param(
            "isssii",
            $jadwal->konselorId,
            $jadwal->hari,
            $jadwal->jamMulai,
            $jadwal->jamSelesai,
            $jadwal->kuota,
            $status
        );

        $stmt->execute();

        return (int)$this->db->insert_id;
    }

    public function update(KonselorJadwal $jadwal): void
    {
        $status = $jadwal->statusAktif ? 1 : 0;

        $stmt = $this->db->prepare(
            "UPDATE konselor_jadwal
            SET
                hari=?,
                jam_mulai=?,
                jam_selesai=?,
                kuota=?,
                status_aktif=?
            WHERE jadwal_id=?"
        );

        $stmt->bind_param(
            "sssiii",
            $jadwal->hari,
            $jadwal->jamMulai,
            $jadwal->jamSelesai,
            $jadwal->kuota,
            $status,
            $jadwal->jadwalId
        );

        $stmt->execute();
    }

    public function delete(int $jadwalId): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM konselor_jadwal
             WHERE jadwal_id=?"
        );

        $stmt->bind_param("i", $jadwalId);

        $stmt->execute();
    }

    public function setActive(int $jadwalId, bool $active): void
    {
        $status = $active ? 1 : 0;

        $stmt = $this->db->prepare(
            "UPDATE konselor_jadwal
             SET status_aktif=?
             WHERE jadwal_id=?"
        );

        $stmt->bind_param("ii", $status, $jadwalId);

        $stmt->execute();
    }

    private function hydrate(array $row): array
    {
        return array_merge(
            (new KonselorJadwal($row))->toArray(),
            [
                'nama' => $row['nama'],
                'email' => $row['email'],
                'user_id' => $row['user_id']
            ]
        );
    }
}
