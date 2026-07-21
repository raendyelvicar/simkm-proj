<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\User;
use mysqli;

class UserRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function find(int $id): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new User($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new User($row) : null;
    }

    // Assumes a `username` column exists on the users table.
    // If you're logging in by email instead, just call findByEmail() from
    // AuthController rather than adding a separate username column.
    public function findByUsername(string $username): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new User($row) : null;
    }

    public function all(): array
    {
        $result = $this->db->query('SELECT * FROM users ORDER BY created_at DESC');
        $users = [];

        while ($row = $result->fetch_assoc()) {
            $users[] = new User($row);
        }

        return $users;
    }

    public function allByRole(string $role): array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE role = ? ORDER BY created_at DESC');
        $stmt->bind_param('s', $role);
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = new User($row);
        }

        return $users;
    }

    public function findByNpm(string $npm): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE npm = ? LIMIT 1');
        $stmt->bind_param('s', $npm);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new User($row) : null;
    }

    // Self-registration entry point: always role=mahasiswa, status=pending —
    // the account only becomes usable once an admin approves it.
    public function createPendingMahasiswa(
        string $nama,
        string $namaLengkap,
        string $username,
        string $email,
        string $hashedPassword,
        string $npm,
        string $jenisKelamin,
        string $fakultas,
        string $jurusan,
        string $noHp
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO users (nama, nama_lengkap, username, email, password, npm, jenis_kelamin, fakultas, jurusan, no_hp, role, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'mahasiswa', 'pending', NOW())"
        );
        $stmt->bind_param(
            'ssssssssss',
            $nama,
            $namaLengkap,
            $username,
            $email,
            $hashedPassword,
            $npm,
            $jenisKelamin,
            $fakultas,
            $jurusan,
            $noHp
        );
        $stmt->execute();

        return (int) $this->db->insert_id;
    }


    public function approve(int $id, int $approvedBy): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET status = 'active', approved_by = ?, approved_at = NOW() WHERE id = ? AND status = 'pending'"
        );
        $stmt->bind_param('ii', $approvedBy, $id);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    public function reject(int $id, int $approvedBy): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET status = 'rejected', approved_by = ?, approved_at = NOW() WHERE id = ? AND status = 'pending'"
        );
        $stmt->bind_param('ii', $approvedBy, $id);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    /** @return array<string, int> fakultas name => mahasiswa count, for the staff dashboard distribution chart. */
    public function countByFakultas(): array
    {
        $result = $this->db->query(
            "SELECT fakultas, COUNT(*) AS total FROM users
             WHERE role = 'mahasiswa' AND fakultas IS NOT NULL AND fakultas != ''
             GROUP BY fakultas ORDER BY total DESC"
        );

        $counts = [];
        while ($row = $result->fetch_assoc()) {
            $counts[$row['fakultas']] = (int) $row['total'];
        }

        return $counts;
    }

    /** Distinct jurusan values actually in use, optionally scoped to one fakultas — for filter dropdowns. */
    public function distinctJurusan(?string $fakultas = null): array
    {
        if ($fakultas) {
            $stmt = $this->db->prepare(
                "SELECT DISTINCT jurusan FROM users
                 WHERE role = 'mahasiswa' AND fakultas = ? AND jurusan IS NOT NULL AND jurusan != ''
                 ORDER BY jurusan"
            );
            $stmt->bind_param('s', $fakultas);
        } else {
            $stmt = $this->db->prepare(
                "SELECT DISTINCT jurusan FROM users
                 WHERE role = 'mahasiswa' AND jurusan IS NOT NULL AND jurusan != ''
                 ORDER BY jurusan"
            );
        }
        $stmt->execute();

        return array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'jurusan');
    }

    public function countActiveMahasiswa(): int
    {
        $result = $this->db->query("SELECT COUNT(*) AS c FROM users WHERE role = 'mahasiswa' AND status = 'active'");

        return (int) ($result->fetch_assoc()['c'] ?? 0);
    }

    public function allAdminEmails(): array
    {
        $result = $this->db->query("SELECT email FROM users WHERE role = 'admin' AND email != ''");

        return $result ? array_column($result->fetch_all(MYSQLI_ASSOC), 'email') : [];
    }

    private const PENDING_SORTABLE = [
        'nama'       => 'nama',
        'created_at' => 'created_at',
    ];

    /**
     * Search/filter/sort/paginate over pending mahasiswa registrations — backs /admin/approvals.
     * @param array $filters ['search'=>?, 'fakultas'=>?]
     * @return array{items: array, total: int}
     */
    public function paginatedPendingMahasiswa(array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = " WHERE role = 'mahasiswa' AND status = 'pending'";
        $params = [];
        $types = '';

        if (!empty($filters['search'])) {
            $where .= ' AND (nama LIKE ? OR npm LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like]);
            $types .= 'ss';
        }
        if (!empty($filters['fakultas'])) {
            $where .= ' AND fakultas = ?';
            $params[] = $filters['fakultas'];
            $types .= 's';
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) AS c FROM users{$where}");
        if ($params) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::PENDING_SORTABLE[$sort] ?? 'created_at';
        $orderDir = $dir === 'desc' ? 'DESC' : 'ASC';
        $offset = ($page - 1) * $perPage;

        $dataStmt = $this->db->prepare("SELECT * FROM users{$where} ORDER BY {$orderCol} {$orderDir} LIMIT ? OFFSET ?");
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        $items = [];
        $result = $dataStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = (new User($row))->toArray();
        }

        return ['items' => $items, 'total' => $total];
    }

    private const MAHASISWA_SORTABLE = [
        'nama'       => 'nama',
        'npm'        => 'npm',
        'fakultas'   => 'fakultas',
        'status'     => 'status',
        'created_at' => 'created_at',
    ];

    /**
     * Search/filter/sort/paginate over the mahasiswa roster — backs /students.
     * @param array $filters ['search'=>?, 'fakultas'=>?, 'jurusan'=>?, 'status'=>?]
     * @return array{items: array, total: int}
     */
    public function paginatedMahasiswa(array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = " WHERE role = 'mahasiswa'";
        $params = [];
        $types = '';

        if (!empty($filters['search'])) {
            $where .= ' AND (nama LIKE ? OR npm LIKE ? OR email LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like, $like]);
            $types .= 'sss';
        }
        if (!empty($filters['fakultas'])) {
            $where .= ' AND fakultas = ?';
            $params[] = $filters['fakultas'];
            $types .= 's';
        }
        if (!empty($filters['jurusan'])) {
            $where .= ' AND jurusan = ?';
            $params[] = $filters['jurusan'];
            $types .= 's';
        }
        if (!empty($filters['status'])) {
            $where .= ' AND status = ?';
            $params[] = $filters['status'];
            $types .= 's';
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) AS c FROM users{$where}");
        if ($params) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::MAHASISWA_SORTABLE[$sort] ?? 'created_at';
        $orderDir = $dir === 'asc' ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $perPage;

        $dataStmt = $this->db->prepare(
            "SELECT * FROM users{$where} ORDER BY {$orderCol} {$orderDir} LIMIT ? OFFSET ?"
        );
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        $items = [];
        $result = $dataStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = (new User($row))->toArray();
        }

        return ['items' => $items, 'total' => $total];
    }

    // $profileImage left null keeps the existing photo untouched.
    public function updateProfile(
        int $id,
        string $nama,
        string $email,
        string $noHp,
        string $jenisKelamin,
        string $fakultas,
        string $jurusan,
        ?string $profileImage
    ): bool {
        if ($profileImage !== null) {
            $stmt = $this->db->prepare(
                'UPDATE users SET nama = ?, email = ?, no_hp = ?, jenis_kelamin = ?, fakultas = ?, jurusan = ?, profile_image = ? WHERE id = ?'
            );
            $stmt->bind_param('sssssssi', $nama, $email, $noHp, $jenisKelamin, $fakultas, $jurusan, $profileImage, $id);
        } else {
            $stmt = $this->db->prepare(
                'UPDATE users SET nama = ?, email = ?, no_hp = ?, jenis_kelamin = ?, fakultas = ?, jurusan = ? WHERE id = ?'
            );
            $stmt->bind_param('ssssssi', $nama, $email, $noHp, $jenisKelamin, $fakultas, $jurusan, $id);
        }

        return $stmt->execute();
    }
}
