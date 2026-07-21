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

    private const STUDENT_SORTABLE = [
        'tanggal'       => 'b.tanggal',
        'status'        => 'b.status',
        'konselor_nama' => 'u.nama',
    ];

    /**
     * A student's own bookings, filter/sort/paginated — backs /bookings. Includes the
     * counselor's display name, users.id (for the chat link), and the current
     * monitoring window (if any) joined in.
     * @param array $filters ['status'=>?]
     * @return array{items: array, total: int}
     */
    public function paginatedForStudent(int $userId, array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = ' WHERE b.user_id = ?';
        $params = [$userId];
        $types = 'i';

        if (!empty($filters['status'])) {
            $where .= ' AND b.status = ?';
            $params[] = $filters['status'];
            $types .= 's';
        }

        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) AS c FROM booking_konseling b JOIN konselor k ON k.konselor_id = b.konselor_id{$where}"
        );
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::STUDENT_SORTABLE[$sort] ?? 'b.created_at';
        $orderDir = $dir === 'asc' ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $perPage;

        $dataStmt = $this->db->prepare(
            "SELECT b.*, u.nama AS konselor_nama, u.id AS konselor_user_id, mp.end_date AS monitoring_end
             FROM booking_konseling b
             JOIN konselor k ON k.konselor_id = b.konselor_id
             JOIN users u ON u.id = k.user_id
             LEFT JOIN monitoring_periods mp ON mp.booking_id = b.booking_id
             {$where}
             ORDER BY {$orderCol} {$orderDir}
             LIMIT ? OFFSET ?"
        );
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        return ['items' => $this->hydrateAll($dataStmt->get_result()), 'total' => $total];
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

    private const QUEUE_SORTABLE = [
        'tanggal'      => 'b.tanggal',
        'status'       => 'b.status',
        'student_nama' => 'u.nama',
    ];

    // The konselor's booking queue: Pending, Confirmed ("On Progress"), and Completed
    // bookings, searchable/sortable/paginated. Cancelled/No Show bookings are left out
    // — they're closed out, nothing left to manage. Default sort ('queue') groups by
    // status priority then oldest-first within each group, same as the original
    // unpaginated ordering this replaces.
    // @return array{items: array, total: int}
    public function paginatedQueue(int $konselorId, array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = " WHERE b.konselor_id = ? AND b.status IN ('Pending', 'Confirmed', 'Completed')";
        $params = [$konselorId];
        $types = 'i';

        if (!empty($filters['search'])) {
            $where .= ' AND (u.nama LIKE ? OR u.npm LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like]);
            $types .= 'ss';
        }

        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) AS c FROM booking_konseling b JOIN users u ON u.id = b.user_id{$where}"
        );
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderDir = $dir === 'desc' ? 'DESC' : 'ASC';
        $orderBy = isset(self::QUEUE_SORTABLE[$sort])
            ? self::QUEUE_SORTABLE[$sort] . " {$orderDir}"
            : "FIELD(b.status, 'Pending', 'Confirmed', 'Completed'), b.created_at ASC";
        $offset = ($page - 1) * $perPage;

        $dataStmt = $this->db->prepare(
            "SELECT b.*, u.nama AS student_nama, u.npm AS student_npm, mp.end_date AS monitoring_end
             FROM booking_konseling b
             JOIN users u ON u.id = b.user_id
             LEFT JOIN monitoring_periods mp ON mp.booking_id = b.booking_id
             {$where}
             ORDER BY {$orderBy}
             LIMIT ? OFFSET ?"
        );
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        return ['items' => $this->hydrateAll($dataStmt->get_result()), 'total' => $total];
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
