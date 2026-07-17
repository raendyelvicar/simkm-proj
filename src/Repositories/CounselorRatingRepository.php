<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\RatingKonselor;
use mysqli;

class CounselorRatingRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    private const SELECT = "
        SELECT
            rk.*,

            bk.tanggal,
            bk.status AS booking_status,

            u.nama AS user_nama,
            u.email AS user_email,

            ku.nama AS konselor_nama,
            ku.email AS konselor_email

        FROM rating_konselor rk

        INNER JOIN booking_konseling bk
            ON bk.booking_id = rk.booking_id

        INNER JOIN users u
            ON u.id = rk.user_id

        INNER JOIN konselor k
            ON k.konselor_id = rk.konselor_id

        INNER JOIN users ku
            ON ku.id = k.user_id
    ";

    public function all(): array
    {
        $result = $this->db->query(
            self::SELECT .
                " ORDER BY rk.created_at DESC"
        );

        $items = [];

        while ($row = $result->fetch_assoc()) {
            $items[] = $this->hydrate($row);
        }

        return $items;
    }

    public function find(int $ratingId): ?array
    {
        $stmt = $this->db->prepare(
            self::SELECT .
                " WHERE rk.rating_id = ?
              LIMIT 1"
        );

        $stmt->bind_param("i", $ratingId);
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
                " WHERE rk.booking_id = ?
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
                " WHERE rk.konselor_id = ?
              ORDER BY rk.created_at DESC"
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
                " WHERE rk.user_id = ?
              ORDER BY rk.created_at DESC"
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

    public function create(RatingKonselor $rating): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO rating_konselor
            (
                booking_id,
                user_id,
                konselor_id,
                rating,
                komentar
            )
            VALUES
            (
                ?,?,?,?,?
            )"
        );

        $stmt->bind_param(
            "iiiis",
            $rating->bookingId,
            $rating->userId,
            $rating->konselorId,
            $rating->rating,
            $rating->komentar
        );

        $stmt->execute();

        return (int)$this->db->insert_id;
    }

    public function update(RatingKonselor $rating): void
    {
        $stmt = $this->db->prepare(
            "UPDATE rating_konselor
             SET
                rating=?,
                komentar=?
             WHERE rating_id=?"
        );

        $stmt->bind_param(
            "isi",
            $rating->rating,
            $rating->komentar,
            $rating->ratingId
        );

        $stmt->execute();
    }

    public function delete(int $ratingId): void
    {
        $stmt = $this->db->prepare(
            "DELETE
             FROM rating_konselor
             WHERE rating_id=?"
        );

        $stmt->bind_param("i", $ratingId);
        $stmt->execute();
    }

    public function existsByBooking(int $bookingId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1
             FROM rating_konselor
             WHERE booking_id=?
             LIMIT 1"
        );

        $stmt->bind_param("i", $bookingId);
        $stmt->execute();

        return (bool)$stmt
            ->get_result()
            ->fetch_row();
    }

    public function getAverageRating(int $konselorId): float
    {
        $stmt = $this->db->prepare(
            "SELECT
                IFNULL(AVG(rating),0) AS avg_rating
             FROM rating_konselor
             WHERE konselor_id=?"
        );

        $stmt->bind_param("i", $konselorId);
        $stmt->execute();

        $row = $stmt
            ->get_result()
            ->fetch_assoc();

        return (float)$row['avg_rating'];
    }

    public function getTotalRating(int $konselorId): int
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total
             FROM rating_konselor
             WHERE konselor_id=?"
        );

        $stmt->bind_param("i", $konselorId);
        $stmt->execute();

        $row = $stmt
            ->get_result()
            ->fetch_assoc();

        return (int)$row['total'];
    }

    public function getRatingSummary(int $konselorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total_rating,
                AVG(rating) AS average_rating,
                SUM(CASE WHEN rating=5 THEN 1 ELSE 0 END) AS star5,
                SUM(CASE WHEN rating=4 THEN 1 ELSE 0 END) AS star4,
                SUM(CASE WHEN rating=3 THEN 1 ELSE 0 END) AS star3,
                SUM(CASE WHEN rating=2 THEN 1 ELSE 0 END) AS star2,
                SUM(CASE WHEN rating=1 THEN 1 ELSE 0 END) AS star1
             FROM rating_konselor
             WHERE konselor_id=?"
        );

        $stmt->bind_param("i", $konselorId);
        $stmt->execute();

        return $stmt
            ->get_result()
            ->fetch_assoc();
    }

    private function hydrate(array $row): array
    {
        return array_merge(
            (new RatingKonselor($row))->toArray(),
            [
                'tanggal' => $row['tanggal'],
                'booking_status' => $row['booking_status'],

                'user_nama' => $row['user_nama'],
                'user_email' => $row['user_email'],

                'konselor_nama' => $row['konselor_nama'],
                'konselor_email' => $row['konselor_email']
            ]
        );
    }
}
