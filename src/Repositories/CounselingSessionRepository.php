<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\SesiKonseling;
use mysqli;

class CounselingSessionRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    private const SELECT = "
        SELECT
            sk.*,

            bk.user_id,
            bk.konselor_id,
            bk.tanggal,
            bk.jam_mulai,
            bk.jam_selesai,
            bk.status AS booking_status,

            u.nama AS user_nama,
            u.email AS user_email,

            ku.nama AS konselor_nama,
            ku.email AS konselor_email

        FROM sesi_konseling sk

        INNER JOIN booking_konseling bk
            ON bk.booking_id = sk.booking_id

        INNER JOIN users u
            ON u.id = bk.user_id

        INNER JOIN konselor k
            ON k.konselor_id = bk.konselor_id

        INNER JOIN users ku
            ON ku.id = k.user_id
    ";

    public function all(): array
    {
        $result = $this->db->query(
            self::SELECT .
                " ORDER BY bk.tanggal DESC,
                     bk.jam_mulai DESC"
        );

        $items = [];

        while ($row = $result->fetch_assoc()) {
            $items[] = $this->hydrate($row);
        }

        return $items;
    }

    public function find(int $sesiId): ?array
    {
        $stmt = $this->db->prepare(
            self::SELECT .
                " WHERE sk.sesi_id = ?
              LIMIT 1"
        );

        $stmt->bind_param("i", $sesiId);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        return $row
            ? $this->hydrate($row)
            : null;
    }

    public function findByBooking(int $bookingId): ?array
    {
        $stmt = $this->db->prepare(
            self::SELECT .
                " WHERE sk.booking_id = ?
              LIMIT 1"
        );

        $stmt->bind_param("i", $bookingId);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        return $row
            ? $this->hydrate($row)
            : null;
    }

    public function findByCounselor(int $konselorId): array
    {
        $stmt = $this->db->prepare(
            self::SELECT .
                " WHERE bk.konselor_id = ?
              ORDER BY bk.tanggal DESC"
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

    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            self::SELECT .
                " WHERE bk.user_id = ?
              ORDER BY bk.tanggal DESC"
        );

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        $items = [];

        while ($row = $result->fetch_assoc()) {
            $items[] = $this->hydrate($row);
        }

        return $items;
    }

    public function create(SesiKonseling $session): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO sesi_konseling
            (
                booking_id,
                catatan_konselor,
                rekomendasi,
                tindak_lanjut,
                durasi,
                selesai_pada
            )
            VALUES
            (
                ?,?,?,?,?,?
            )"
        );

        $stmt->bind_param(
            "isssis",
            $session->bookingId,
            $session->catatanKonselor,
            $session->rekomendasi,
            $session->tindakLanjut,
            $session->durasi,
            $session->selesaiPada
        );

        $stmt->execute();

        return (int) $this->db->insert_id;
    }

    public function update(SesiKonseling $session): void
    {
        $stmt = $this->db->prepare(
            "UPDATE sesi_konseling
            SET

                catatan_konselor=?,
                rekomendasi=?,
                tindak_lanjut=?,
                durasi=?,
                selesai_pada=?

            WHERE sesi_id=?"
        );

        $stmt->bind_param(
            "sssisi",
            $session->catatanKonselor,
            $session->rekomendasi,
            $session->tindakLanjut,
            $session->durasi,
            $session->selesaiPada,
            $session->sesiId
        );

        $stmt->execute();
    }

    public function delete(int $sesiId): void
    {
        $stmt = $this->db->prepare(
            "DELETE
             FROM sesi_konseling
             WHERE sesi_id=?"
        );

        $stmt->bind_param(
            "i",
            $sesiId
        );

        $stmt->execute();
    }

    public function existsByBooking(int $bookingId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1
             FROM sesi_konseling
             WHERE booking_id=?
             LIMIT 1"
        );

        $stmt->bind_param(
            "i",
            $bookingId
        );

        $stmt->execute();

        return (bool) $stmt
            ->get_result()
            ->fetch_row();
    }

    public function completeSession(
        int $bookingId,
        string $catatan,
        ?string $rekomendasi,
        ?string $tindakLanjut,
        int $durasi
    ): void {

        $this->db->begin_transaction();

        try {

            $stmt = $this->db->prepare(
                "INSERT INTO sesi_konseling
                (
                    booking_id,
                    catatan_konselor,
                    rekomendasi,
                    tindak_lanjut,
                    durasi,
                    selesai_pada
                )
                VALUES
                (
                    ?,?,?,?,?,NOW()
                )"
            );

            $stmt->bind_param(
                "isssi",
                $bookingId,
                $catatan,
                $rekomendasi,
                $tindakLanjut,
                $durasi
            );

            $stmt->execute();

            $stmt = $this->db->prepare(
                "UPDATE booking_konseling
                 SET status='Completed'
                 WHERE booking_id=?"
            );

            $stmt->bind_param(
                "i",
                $bookingId
            );

            $stmt->execute();

            $this->db->commit();
        } catch (\mysqli_sql_exception $e) {

            $this->db->rollback();

            throw $e;
        }
    }

    private function hydrate(array $row): array
    {
        return array_merge(
            (new SesiKonseling($row))->toArray(),
            [

                'booking_status' => $row['booking_status'],

                'tanggal' => $row['tanggal'],

                'jam_mulai' => $row['jam_mulai'],

                'jam_selesai' => $row['jam_selesai'],

                'user_id' => $row['user_id'],

                'user_nama' => $row['user_nama'],

                'user_email' => $row['user_email'],

                'konselor_id' => $row['konselor_id'],

                'konselor_nama' => $row['konselor_nama'],

                'konselor_email' => $row['konselor_email']
            ]
        );
    }
}
