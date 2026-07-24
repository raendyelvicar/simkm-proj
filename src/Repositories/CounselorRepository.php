<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Counselor;
use mysqli;

class CounselorRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    private const SELECT = "
        SELECT
            u.id,
            u.name,
            u.username,
            u.email,
            u.profile_image,
            k.*
        FROM users u
        LEFT JOIN counselors k ON k.user_id = u.id
        WHERE u.role='counselor'
    ";

    public function all(bool $onlyActive = true): array
    {
        $sql = self::SELECT;
        if ($onlyActive) {
            $sql .= " AND (k.is_active = 1 OR k.counselor_id IS NULL)";
        }
        $sql .= " ORDER BY u.name";
        $result = $this->db->query($sql);

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $this->hydrate($row);
        }
        return $items;
    }

    private const PUBLIC_SORTABLE = [
        'name'             => 'u.name',
        'experience_years' => 'k.experience_years',
        'consultation_fee' => 'k.consultation_fee',
    ];

    /**
     * Search/filter/sort/paginate the public, active-only counselor directory — backs /counselor.
     * @param array $filters ['search'=>?, 'profession'=>?, 'consultation_method'=>?]
     * @return array{items: array, total: int}
     */
    public function paginatedActive(array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = ' AND (k.is_active = 1 OR k.counselor_id IS NULL)';
        $params = [];
        $types = '';

        if (!empty($filters['search'])) {
            $where .= ' AND (u.name LIKE ? OR k.specialization LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like]);
            $types .= 'ss';
        }
        if (!empty($filters['profession'])) {
            $where .= ' AND k.profession = ?';
            $params[] = $filters['profession'];
            $types .= 's';
        }
        if (!empty($filters['consultation_method'])) {
            $where .= ' AND k.consultation_method = ?';
            $params[] = $filters['consultation_method'];
            $types .= 's';
        }

        $countStmt = $this->db->prepare('SELECT COUNT(*) AS c FROM (' . self::SELECT . $where . ') x');
        if ($params) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::PUBLIC_SORTABLE[$sort] ?? 'u.name';
        $orderDir = $dir === 'desc' ? 'DESC' : 'ASC';
        $offset = ($page - 1) * $perPage;

        $dataStmt = $this->db->prepare(self::SELECT . $where . " ORDER BY {$orderCol} {$orderDir} LIMIT ? OFFSET ?");
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        $items = [];
        $result = $dataStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = $this->hydrate($row);
        }

        return ['items' => $items, 'total' => $total];
    }

    public function find(int $userId): ?array
    {
        $stmt = $this->db->prepare(self::SELECT . " AND u.id=? LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $this->hydrate($row) : null;
    }

    // Unlike find(), which looks up by users.id, this looks up by counselor.counselor_id
    // — the id other tables (counselor_ratings, counselor_schedules, diary_entries.shared_counselor_id) reference.
    public function findByCounselorId(int $counselorId): ?array
    {
        $stmt = $this->db->prepare(self::SELECT . " AND k.counselor_id=? LIMIT 1");
        $stmt->bind_param("i", $counselorId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $this->hydrate($row) : null;
    }

    private const ADMIN_SORTABLE = [
        'name'             => 'u.name',
        'profession'          => 'k.profession',
        'experience_years' => 'k.experience_years',
        'created_at'       => 'k.created_at',
    ];

    /**
     * Admin management: search/filter/sort/paginate over every counselor account
     * (active or not, profile-complete or not) — backs /admin/counselors.
     * @param array $filters ['search'=>?, 'profession'=>?, 'is_active'=>'1'|'0'|null]
     * @return array{items: array, total: int}
     */
    public function paginatedForAdmin(array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = '';
        $params = [];
        $types = '';

        if (!empty($filters['search'])) {
            $where .= ' AND (u.name LIKE ? OR k.registration_number LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like]);
            $types .= 'ss';
        }
        if (!empty($filters['profession'])) {
            $where .= ' AND k.profession = ?';
            $params[] = $filters['profession'];
            $types .= 's';
        }
        if (($filters['is_active'] ?? '') !== '') {
            $where .= ' AND k.is_active = ?';
            $params[] = (int) $filters['is_active'];
            $types .= 'i';
        }

        $countStmt = $this->db->prepare('SELECT COUNT(*) AS c FROM (' . self::SELECT . $where . ') x');
        if ($params) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::ADMIN_SORTABLE[$sort] ?? 'u.name';
        $orderDir = $dir === 'desc' ? 'DESC' : 'ASC';
        $offset = ($page - 1) * $perPage;

        $dataStmt = $this->db->prepare(self::SELECT . $where . " ORDER BY {$orderCol} {$orderDir} LIMIT ? OFFSET ?");
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        $items = [];
        $result = $dataStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = $this->hydrateAdmin($row);
        }

        return ['items' => $items, 'total' => $total];
    }

    public function findForAdmin(int $userId): ?array
    {
        $stmt = $this->db->prepare(self::SELECT . " AND u.id=? LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $this->hydrateAdmin($row) : null;
    }

    private function hydrateAdmin(array $row): array
    {
        return array_merge($this->hydrate($row), [
            'has_profile' => $row['counselor_id'] !== null,
        ]);
    }

    // Creates the login account (role=counselor) and its extended profile together.
    // Rolled back as one unit so a duplicate registration_number can't leave a bare,
    // profile-less user account behind.
    public function createCounselor(
        string $name,
        string $username,
        string $email,
        string $hashedPassword,
        string $registrationNumber,
        string $profession,
        ?string $specialization,
        ?string $education,
        int $experienceYears,
        ?string $languages,
        float $consultationFee,
        int $durationSession,
        string $consultationMethod,
        ?string $biography,
        bool $isActive,
        ?string $profilePhoto = null
    ): int {
        $this->db->begin_transaction();

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO users (name, username, email, password, role, status, created_at)
                 VALUES (?, ?, ?, ?, 'counselor', 'active', NOW())"
            );
            $stmt->bind_param('ssss', $name, $username, $email, $hashedPassword);
            $stmt->execute();
            $userId = (int) $this->db->insert_id;

            $counselor = new Counselor([
                'user_id' => $userId,
                'registration_number' => $registrationNumber,
                'profession' => $profession,
                'specialization' => $specialization,
                'education' => $education,
                'experience_years' => $experienceYears,
                'languages' => $languages,
                'consultation_fee' => $consultationFee,
                'session_duration' => $durationSession,
                'consultation_method' => $consultationMethod,
                'profile_photo' => $profilePhoto,
                'biography' => $biography,
                'verification_status' => true,
                'is_active' => $isActive,
            ]);
            $this->create($counselor);

            $this->db->commit();

            return $userId;
        } catch (\mysqli_sql_exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function updateUserBasic(int $userId, string $name, string $username, string $email): void
    {
        $stmt = $this->db->prepare('UPDATE users SET name = ?, username = ?, email = ? WHERE id = ?');
        $stmt->bind_param('sssi', $name, $username, $email, $userId);
        $stmt->execute();
    }

    public function updateUserPassword(int $userId, string $hashedPassword): void
    {
        $stmt = $this->db->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->bind_param('si', $hashedPassword, $userId);
        $stmt->execute();
    }

    public function updateUserProfileImage(int $userId, string $imagePath): void
    {
        $stmt = $this->db->prepare('UPDATE users SET profile_image = ? WHERE id = ?');
        $stmt->bind_param('si', $imagePath, $userId);
        $stmt->execute();
    }

    // Creates the counselor row if this user doesn't have one yet, otherwise updates it.
    public function upsertProfile(
        int $userId,
        string $registrationNumber,
        string $profession,
        ?string $specialization,
        ?string $education,
        int $experienceYears,
        ?string $languages,
        float $consultationFee,
        int $durationSession,
        string $consultationMethod,
        ?string $biography,
        bool $isActive,
        ?string $profilePhoto = null
    ): void {
        $stmt = $this->db->prepare('SELECT counselor_id, profile_photo FROM counselors WHERE user_id = ? LIMIT 1');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();

        $counselor = new Counselor([
            'counselor_id' => $existing['counselor_id'] ?? 0,
            'user_id' => $userId,
            'registration_number' => $registrationNumber,
            'profession' => $profession,
            'specialization' => $specialization,
            'education' => $education,
            'experience_years' => $experienceYears,
            'languages' => $languages,
            'consultation_fee' => $consultationFee,
            'session_duration' => $durationSession,
            'consultation_method' => $consultationMethod,
            'profile_photo' => $profilePhoto ?? ($existing['profile_photo'] ?? null),
            'biography' => $biography,
            'verification_status' => true,
            'is_active' => $isActive,
        ]);

        if ($existing) {
            $this->update($counselor);
        } else {
            $this->create($counselor);
        }
    }

    // Soft delete / reactivate — only meaningful once a counselor row exists.
    public function setActive(int $counselorId, bool $active): void
    {
        $status = $active ? 1 : 0;
        $stmt = $this->db->prepare('UPDATE counselors SET is_active = ? WHERE counselor_id = ?');
        $stmt->bind_param('ii', $status, $counselorId);
        $stmt->execute();
    }

    public function registrationNumberExists(string $nomor, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT 1 FROM counselors WHERE registration_number=? AND counselor_id<>? LIMIT 1");
            $stmt->bind_param("si", $nomor, $excludeId);
        } else {
            $stmt = $this->db->prepare("SELECT 1 FROM counselors WHERE registration_number=? LIMIT 1");
            $stmt->bind_param("s", $nomor);
        }
        $stmt->execute();
        return (bool)$stmt->get_result()->fetch_row();
    }

    public function create(Counselor $k): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO counselors
            (user_id,registration_number,profession,specialization,education,experience_years,languages,consultation_fee,session_duration,consultation_method,profile_photo,biography,verification_status,is_active)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        $ver = $k->verificationStatus ? 1 : 0;
        $aktif = $k->isActive ? 1 : 0;
        $stmt->bind_param(
            "issssisdisssii",
            $k->userId,
            $k->registrationNumber,
            $k->profession,
            $k->specialization,
            $k->education,
            $k->experienceYears,
            $k->languages,
            $k->consultationFee,
            $k->durationSession,
            $k->consultationMethod,
            $k->profilePhoto,
            $k->biography,
            $ver,
            $aktif
        );
        $stmt->execute();
        return (int)$this->db->insert_id;
    }

    public function update(Counselor $k): void
    {
        $stmt = $this->db->prepare(
            "UPDATE counselors SET
            registration_number=?, profession=?, specialization=?, education=?,
            experience_years=?, languages=?, consultation_fee=?, session_duration=?,
            consultation_method=?, profile_photo=?, biography=?,
            verification_status=?, is_active=?
            WHERE counselor_id=?"
        );
        $ver = $k->verificationStatus ? 1 : 0;
        $aktif = $k->isActive ? 1 : 0;
        $stmt->bind_param(
            "ssssisdisssiii",
            $k->registrationNumber,
            $k->profession,
            $k->specialization,
            $k->education,
            $k->experienceYears,
            $k->languages,
            $k->consultationFee,
            $k->durationSession,
            $k->consultationMethod,
            $k->profilePhoto,
            $k->biography,
            $ver,
            $aktif,
            $k->counselorId
        );
        $stmt->execute();
    }

    public function delete(int $counselorId): void
    {
        $stmt = $this->db->prepare("DELETE FROM counselors WHERE counselor_id=?");
        $stmt->bind_param("i", $counselorId);
        $stmt->execute();
    }

    private function hydrate(array $row): array
    {
        return array_merge((new Counselor($row))->toArray(), [
            'name' => $row['name'],
            'username' => $row['username'],
            'email' => $row['email'],
            'profile_image' => $row['profile_image']
        ]);
    }
}
