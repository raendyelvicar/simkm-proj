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

    public function findByStudentNumber(string $student_number): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE student_number = ? LIMIT 1');
        $stmt->bind_param('s', $student_number);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new User($row) : null;
    }

    // Self-registration entry point: always role=student, status=pending —
    // the account only becomes usable once an admin approves it.
    public function createPendingStudent(
        string $name,
        string $nameLengkap,
        string $username,
        string $email,
        string $hashedPassword,
        string $student_number,
        string $gender,
        string $faculty,
        string $major,
        string $phoneNumber
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, full_name, username, email, password, student_number, gender, faculty, major, phone_number, role, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'student', 'pending', NOW())"
        );
        $stmt->bind_param(
            'ssssssssss',
            $name,
            $nameLengkap,
            $username,
            $email,
            $hashedPassword,
            $student_number,
            $gender,
            $faculty,
            $major,
            $phoneNumber
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

    /** @return array<string, int> faculty name => student count, for the staff dashboard distribution chart. */
    public function countByFaculty(): array
    {
        $result = $this->db->query(
            "SELECT faculty, COUNT(*) AS total FROM users
             WHERE role = 'student' AND faculty IS NOT NULL AND faculty != ''
             GROUP BY faculty ORDER BY total DESC"
        );

        $counts = [];
        while ($row = $result->fetch_assoc()) {
            $counts[$row['faculty']] = (int) $row['total'];
        }

        return $counts;
    }

    /** Distinct major values actually in use, optionally scoped to one faculty — for filter dropdowns. */
    public function distinctMajor(?string $faculty = null): array
    {
        if ($faculty) {
            $stmt = $this->db->prepare(
                "SELECT DISTINCT major FROM users
                 WHERE role = 'student' AND faculty = ? AND major IS NOT NULL AND major != ''
                 ORDER BY major"
            );
            $stmt->bind_param('s', $faculty);
        } else {
            $stmt = $this->db->prepare(
                "SELECT DISTINCT major FROM users
                 WHERE role = 'student' AND major IS NOT NULL AND major != ''
                 ORDER BY major"
            );
        }
        $stmt->execute();

        return array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'major');
    }

    public function countActiveStudent(): int
    {
        $result = $this->db->query("SELECT COUNT(*) AS c FROM users WHERE role = 'student' AND status = 'active'");

        return (int) ($result->fetch_assoc()['c'] ?? 0);
    }

    public function allAdminEmails(): array
    {
        $result = $this->db->query("SELECT email FROM users WHERE role = 'admin' AND email != ''");

        return $result ? array_column($result->fetch_all(MYSQLI_ASSOC), 'email') : [];
    }

    private const PENDING_SORTABLE = [
        'name'       => 'name',
        'created_at' => 'created_at',
    ];

    /**
     * Search/filter/sort/paginate over pending student registrations — backs /admin/approvals.
     * @param array $filters ['search'=>?, 'faculty'=>?]
     * @return array{items: array, total: int}
     */
    public function paginatedPendingStudent(array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = " WHERE role = 'student' AND status = 'pending'";
        $params = [];
        $types = '';

        if (!empty($filters['search'])) {
            $where .= ' AND (name LIKE ? OR student_number LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like]);
            $types .= 'ss';
        }
        if (!empty($filters['faculty'])) {
            $where .= ' AND faculty = ?';
            $params[] = $filters['faculty'];
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
        'name'       => 'name',
        'student_number'        => 'student_number',
        'faculty'   => 'faculty',
        'status'     => 'status',
        'created_at' => 'created_at',
    ];

    /**
     * Search/filter/sort/paginate over the student roster — backs /students.
     * @param array $filters ['search'=>?, 'faculty'=>?, 'major'=>?, 'status'=>?]
     * @return array{items: array, total: int}
     */
    public function paginatedStudent(array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = " WHERE role = 'student'";
        $params = [];
        $types = '';

        if (!empty($filters['search'])) {
            $where .= ' AND (name LIKE ? OR student_number LIKE ? OR email LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like, $like]);
            $types .= 'sss';
        }
        if (!empty($filters['faculty'])) {
            $where .= ' AND faculty = ?';
            $params[] = $filters['faculty'];
            $types .= 's';
        }
        if (!empty($filters['major'])) {
            $where .= ' AND major = ?';
            $params[] = $filters['major'];
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
        string $name,
        string $email,
        string $phoneNumber,
        string $gender,
        string $faculty,
        string $major,
        ?string $profileImage
    ): bool {
        if ($profileImage !== null) {
            $stmt = $this->db->prepare(
                'UPDATE users SET name = ?, email = ?, phone_number = ?, gender = ?, faculty = ?, major = ?, profile_image = ? WHERE id = ?'
            );
            $stmt->bind_param('sssssssi', $name, $email, $phoneNumber, $gender, $faculty, $major, $profileImage, $id);
        } else {
            $stmt = $this->db->prepare(
                'UPDATE users SET name = ?, email = ?, phone_number = ?, gender = ?, faculty = ?, major = ? WHERE id = ?'
            );
            $stmt->bind_param('ssssssi', $name, $email, $phoneNumber, $gender, $faculty, $major, $id);
        }

        return $stmt->execute();
    }

    // Used by the forgot-password flow once a reset token has been verified.
    public function updatePassword(int $id, string $hashedPassword): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->bind_param('si', $hashedPassword, $id);

        return $stmt->execute();
    }
}
