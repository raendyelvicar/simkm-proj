<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\KonselorSertifikasi;
use mysqli;

class CounselorCertificationRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    private const SELECT = "
        SELECT

            ks.*,

            k.user_id,

            u.nama,
            u.email

        FROM konselor_sertifikasi ks

        INNER JOIN konselor k
            ON k.konselor_id = ks.konselor_id

        INNER JOIN users u
            ON u.id = k.user_id
    ";

    public function all(): array
    {
        $result = $this->db->query(
            self::SELECT .
                " ORDER BY
                u.nama,
                ks.tahun DESC,
                ks.nama_sertifikasi"
        );

        $items = [];

        while ($row = $result->fetch_assoc()) {
            $items[] = $this->hydrate($row);
        }

        return $items;
    }

    public function find(int $sertifikasiId): ?array
    {
        $stmt = $this->db->prepare(
            self::SELECT .
                " WHERE ks.sertifikasi_id = ?
              LIMIT 1"
        );

        $stmt->bind_param("i", $sertifikasiId);

        $stmt->execute();

        $row = $stmt
            ->get_result()
            ->fetch_assoc();

        return $row
            ? $this->hydrate($row)
            : null;
    }

    public function findByCounselor(int $konselorId): array
    {
        $stmt = $this->db->prepare(
            self::SELECT .
                " WHERE ks.konselor_id = ?
              ORDER BY
                ks.tahun DESC,
                ks.nama_sertifikasi"
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

    public function create(KonselorSertifikasi $sertifikasi): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO konselor_sertifikasi
            (
                konselor_id,
                nama_sertifikasi,
                penerbit,
                nomor_sertifikat,
                tahun
            )
            VALUES
            (
                ?,?,?,?,?
            )"
        );

        $stmt->bind_param(
            "issss",
            $sertifikasi->konselorId,
            $sertifikasi->namaSertifikasi,
            $sertifikasi->penerbit,
            $sertifikasi->nomorSertifikat,
            $sertifikasi->tahun
        );

        $stmt->execute();

        return (int)$this->db->insert_id;
    }

    public function update(KonselorSertifikasi $sertifikasi): void
    {
        $stmt = $this->db->prepare(
            "UPDATE konselor_sertifikasi
             SET

                nama_sertifikasi=?,
                penerbit=?,
                nomor_sertifikat=?,
                tahun=?

             WHERE sertifikasi_id=?"
        );

        $stmt->bind_param(
            "ssssi",
            $sertifikasi->namaSertifikasi,
            $sertifikasi->penerbit,
            $sertifikasi->nomorSertifikat,
            $sertifikasi->tahun,
            $sertifikasi->sertifikasiId
        );

        $stmt->execute();
    }

    public function delete(int $sertifikasiId): void
    {
        $stmt = $this->db->prepare(
            "DELETE
             FROM konselor_sertifikasi
             WHERE sertifikasi_id=?"
        );

        $stmt->bind_param(
            "i",
            $sertifikasiId
        );

        $stmt->execute();
    }

    public function countByCounselor(int $konselorId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM konselor_sertifikasi
             WHERE konselor_id=?"
        );

        $stmt->bind_param(
            "i",
            $konselorId
        );

        $stmt->execute();

        $row = $stmt
            ->get_result()
            ->fetch_row();

        return (int)$row[0];
    }

    public function existsCertificateNumber(
        string $nomor,
        ?int $excludeId = null
    ): bool {

        if ($excludeId !== null) {

            $stmt = $this->db->prepare(
                "SELECT 1
                 FROM konselor_sertifikasi
                 WHERE nomor_sertifikat=?
                 AND sertifikasi_id<>?
                 LIMIT 1"
            );

            $stmt->bind_param(
                "si",
                $nomor,
                $excludeId
            );
        } else {

            $stmt = $this->db->prepare(
                "SELECT 1
                 FROM konselor_sertifikasi
                 WHERE nomor_sertifikat=?
                 LIMIT 1"
            );

            $stmt->bind_param(
                "s",
                $nomor
            );
        }

        $stmt->execute();

        return (bool)$stmt
            ->get_result()
            ->fetch_row();
    }

    private function hydrate(array $row): array
    {
        return array_merge(
            (new KonselorSertifikasi($row))->toArray(),
            [
                'user_id' => $row['user_id'],
                'nama' => $row['nama'],
                'email' => $row['email']
            ]
        );
    }
}
