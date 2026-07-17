<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\BookingKonseling;
use mysqli;

class BookingKonselingRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function create(
        int $userId,
        int $konselorId,
        int $jadwalId,
        string $tanggal,
        string $jamMulai,
        string $jamSelesai,
        ?string $keluhan
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO booking_konseling (user_id, konselor_id, jadwal_id, tanggal, jam_mulai, jam_selesai, keluhan, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')"
        );
        $stmt->bind_param('iiissss', $userId, $konselorId, $jadwalId, $tanggal, $jamMulai, $jamSelesai, $keluhan);
        $stmt->execute();

        return (int) $this->db->insert_id;
    }

    public function findOwnedByStudent(int $bookingId, int $userId): ?BookingKonseling
    {
        $stmt = $this->db->prepare('SELECT * FROM booking_konseling WHERE booking_id = ? AND user_id = ? LIMIT 1');
        $stmt->bind_param('ii', $bookingId, $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new BookingKonseling($row) : null;
    }

    public function findOwnedByKonselor(int $bookingId, int $konselorId): ?BookingKonseling
    {
        $stmt = $this->db->prepare('SELECT * FROM booking_konseling WHERE booking_id = ? AND konselor_id = ? LIMIT 1');
        $stmt->bind_param('ii', $bookingId, $konselorId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new BookingKonseling($row) : null;
    }

    // A student's own bookings, with the counselor's display name, users.id (for the chat
    // link), and the current monitoring window (if any) joined in.
    public function allForStudent(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, u.nama AS konselor_nama, u.id AS konselor_user_id, mp.end_date AS monitoring_end
             FROM booking_konseling b
             JOIN konselor k ON k.konselor_id = b.konselor_id
             JOIN users u ON u.id = k.user_id
             LEFT JOIN monitoring_periods mp ON mp.booking_id = b.booking_id
             WHERE b.user_id = ?
             ORDER BY b.created_at DESC'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        return $this->hydrateAll($stmt->get_result());
    }

    // Bookings for a konselor (konselor.konselor_id), with the student's display name/npm joined in.
    public function forKonselor(int $konselorId, string $status): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, u.nama AS student_nama, u.npm AS student_npm
             FROM booking_konseling b
             JOIN users u ON u.id = b.user_id
             WHERE b.konselor_id = ? AND b.status = ?
             ORDER BY b.created_at ASC'
        );
        $stmt->bind_param('is', $konselorId, $status);
        $stmt->execute();

        return $this->hydrateAll($stmt->get_result());
    }

    // The konselor's booking queue: Pending, Confirmed ("On Progress"), and Completed
    // bookings in one list, grouped by status then oldest-first within each group.
    // Cancelled/No Show bookings are left out — they're closed out, nothing left to manage.
    public function forKonselorQueue(int $konselorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT b.*, u.nama AS student_nama, u.npm AS student_npm, mp.end_date AS monitoring_end
             FROM booking_konseling b
             JOIN users u ON u.id = b.user_id
             LEFT JOIN monitoring_periods mp ON mp.booking_id = b.booking_id
             WHERE b.konselor_id = ? AND b.status IN ('Pending', 'Confirmed', 'Completed')
             ORDER BY FIELD(b.status, 'Pending', 'Confirmed', 'Completed'), b.created_at ASC"
        );
        $stmt->bind_param('i', $konselorId);
        $stmt->execute();

        return $this->hydrateAll($stmt->get_result());
    }

    public function updateStatus(int $bookingId, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE booking_konseling SET status = ? WHERE booking_id = ?');
        $stmt->bind_param('si', $status, $bookingId);
        $stmt->execute();
    }

    // Dedupe guard: a student can't have more than one open request to the same counselor at once.
    public function hasOpenBooking(int $userId, int $konselorId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM booking_konseling
             WHERE user_id = ? AND konselor_id = ? AND status IN ('Pending','Confirmed') LIMIT 1"
        );
        $stmt->bind_param('ii', $userId, $konselorId);
        $stmt->execute();

        return (bool) $stmt->get_result()->fetch_row();
    }

    // A jadwal_id now maps to exactly one date, so capacity is just "how many
    // non-cancelled bookings already claim this slot" vs. its kuota.
    public function hasCapacity(int $jadwalId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT j.kuota,
                    (SELECT COUNT(*) FROM booking_konseling
                     WHERE jadwal_id = j.jadwal_id AND status <> 'Cancelled') AS booked
             FROM konselor_jadwal j
             WHERE j.jadwal_id = ?
             LIMIT 1"
        );
        $stmt->bind_param('i', $jadwalId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) {
            return false;
        }

        return (int) $row['booked'] < (int) $row['kuota'];
    }

    private function hydrateAll(\mysqli_result $result): array
    {
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = array_merge((new BookingKonseling($row))->toArray(), array_filter([
                'konselor_nama' => $row['konselor_nama'] ?? null,
                'konselor_user_id' => isset($row['konselor_user_id']) ? (int) $row['konselor_user_id'] : null,
                'student_nama' => $row['student_nama'] ?? null,
                'student_npm' => $row['student_npm'] ?? null,
                'monitoring_end' => $row['monitoring_end'] ?? null,
            ], fn ($v) => $v !== null));
        }

        return $bookings;
    }
}
