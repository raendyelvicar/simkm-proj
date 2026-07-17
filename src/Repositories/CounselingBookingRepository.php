<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\BookingKonseling;
use mysqli;

class CounselingBookingRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    private const SELECT = "
        SELECT
            bk.*,

            u.nama AS user_nama,
            u.email AS user_email,

            ku.nama AS konselor_nama,
            ku.email AS konselor_email,

            kj.hari,
            kj.kuota

        FROM booking_konseling bk

        INNER JOIN users u
            ON u.id = bk.user_id

        INNER JOIN konselor k
            ON k.konselor_id = bk.konselor_id

        INNER JOIN users ku
            ON ku.id = k.user_id

        LEFT JOIN konselor_jadwal kj
            ON kj.jadwal_id = bk.jadwal_id
    ";

    public function all(): array
    {
        $result = $this->db->query(
            self::SELECT .
                " ORDER BY bk.tanggal DESC,
                     bk.jam_mulai ASC"
        );

        $items = [];

        while ($row = $result->fetch_assoc()) {
            $items[] = $this->hydrate($row);
        }

        return $items;
    }

    public function find(int $bookingId): ?array
    {
        $stmt = $this->db->prepare(
            self::SELECT .
                " WHERE bk.booking_id = ?
              LIMIT 1"
        );

        $stmt->bind_param("i", $bookingId);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        return $row
            ? $this->hydrate($row)
            : null;
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

    public function create(BookingKonseling $booking): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO booking_konseling
            (
                user_id,
                konselor_id,
                jadwal_id,
                tanggal,
                jam_mulai,
                jam_selesai,
                keluhan,
                status
            )
            VALUES
            (
                ?,?,?,?,?,?,?,?
            )"
        );

        $stmt->bind_param(
            "iiisssss",
            $booking->userId,
            $booking->konselorId,
            $booking->jadwalId,
            $booking->tanggal,
            $booking->jamMulai,
            $booking->jamSelesai,
            $booking->keluhan,
            $booking->status
        );

        $stmt->execute();

        return (int)$this->db->insert_id;
    }

    public function update(BookingKonseling $booking): void
    {
        $stmt = $this->db->prepare(
            "UPDATE booking_konseling
            SET

                jadwal_id=?,
                tanggal=?,
                jam_mulai=?,
                jam_selesai=?,
                keluhan=?,
                status=?

            WHERE booking_id=?"
        );

        $stmt->bind_param(
            "isssssi",
            $booking->jadwalId,
            $booking->tanggal,
            $booking->jamMulai,
            $booking->jamSelesai,
            $booking->keluhan,
            $booking->status,
            $booking->bookingId
        );

        $stmt->execute();
    }

    public function updateStatus(
        int $bookingId,
        string $status
    ): void {

        $stmt = $this->db->prepare(
            "UPDATE booking_konseling
             SET status=?
             WHERE booking_id=?"
        );

        $stmt->bind_param(
            "si",
            $status,
            $bookingId
        );

        $stmt->execute();
    }

    public function delete(int $bookingId): void
    {
        $stmt = $this->db->prepare(
            "DELETE
             FROM booking_konseling
             WHERE booking_id=?"
        );

        $stmt->bind_param(
            "i",
            $bookingId
        );

        $stmt->execute();
    }

    public function existsScheduleConflict(
        int $konselorId,
        string $tanggal,
        string $jamMulai,
        string $jamSelesai,
        ?int $excludeBookingId = null
    ): bool {

        if ($excludeBookingId !== null) {

            $stmt = $this->db->prepare(
                "SELECT 1

                 FROM booking_konseling

                 WHERE konselor_id=?
                 AND tanggal=?
                 AND booking_id<>?

                 AND status IN
                 (
                    'Pending',
                    'Confirmed'
                 )

                 AND
                 (
                    jam_mulai < ?
                    AND
                    jam_selesai > ?
                 )

                 LIMIT 1"
            );

            $stmt->bind_param(
                "issss",
                $konselorId,
                $tanggal,
                $excludeBookingId,
                $jamSelesai,
                $jamMulai
            );
        } else {

            $stmt = $this->db->prepare(
                "SELECT 1

                 FROM booking_konseling

                 WHERE konselor_id=?
                 AND tanggal=?

                 AND status IN
                 (
                    'Pending',
                    'Confirmed'
                 )

                 AND
                 (
                    jam_mulai < ?
                    AND
                    jam_selesai > ?
                 )

                 LIMIT 1"
            );

            $stmt->bind_param(
                "isss",
                $konselorId,
                $tanggal,
                $jamSelesai,
                $jamMulai
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
            (new BookingKonseling($row))->toArray(),
            [

                'user_nama' => $row['user_nama'],

                'user_email' => $row['user_email'],

                'konselor_nama' => $row['konselor_nama'],

                'konselor_email' => $row['konselor_email'],

                'hari' => $row['hari'],

                'kuota' => $row['kuota']
            ]
        );
    }
}
