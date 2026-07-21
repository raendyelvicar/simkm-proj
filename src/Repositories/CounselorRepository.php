<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Konselor;
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
            u.nama,
            u.username,
            u.email,
            u.profile_image,
            k.*
        FROM users u
        LEFT JOIN konselor k ON k.user_id = u.id
        WHERE u.role='konselor'
    ";

    public function all(bool $onlyActive = true): array
    {
        $sql = self::SELECT;
        if ($onlyActive) {
            $sql .= " AND (k.status_aktif = 1 OR k.konselor_id IS NULL)";
        }
        $sql .= " ORDER BY u.nama";
        $result = $this->db->query($sql);

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $this->hydrate($row);
        }
        return $items;
    }

    private const PUBLIC_SORTABLE = [
        'nama'             => 'u.nama',
        'pengalaman_tahun' => 'k.pengalaman_tahun',
        'biaya_konsultasi' => 'k.biaya_konsultasi',
    ];

    /**
     * Search/filter/sort/paginate the public, active-only konselor directory — backs /counselor.
     * @param array $filters ['search'=>?, 'profesi'=>?, 'metode_konsultasi'=>?]
     * @return array{items: array, total: int}
     */
    public function paginatedActive(array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = ' AND (k.status_aktif = 1 OR k.konselor_id IS NULL)';
        $params = [];
        $types = '';

        if (!empty($filters['search'])) {
            $where .= ' AND (u.nama LIKE ? OR k.spesialisasi LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like]);
            $types .= 'ss';
        }
        if (!empty($filters['profesi'])) {
            $where .= ' AND k.profesi = ?';
            $params[] = $filters['profesi'];
            $types .= 's';
        }
        if (!empty($filters['metode_konsultasi'])) {
            $where .= ' AND k.metode_konsultasi = ?';
            $params[] = $filters['metode_konsultasi'];
            $types .= 's';
        }

        $countStmt = $this->db->prepare('SELECT COUNT(*) AS c FROM (' . self::SELECT . $where . ') x');
        if ($params) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::PUBLIC_SORTABLE[$sort] ?? 'u.nama';
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

    // Unlike find(), which looks up by users.id, this looks up by konselor.konselor_id
    // — the id other tables (rating_konselor, konselor_jadwal, diary_entries.shared_konselor_id) reference.
    public function findByKonselorId(int $konselorId): ?array
    {
        $stmt = $this->db->prepare(self::SELECT . " AND k.konselor_id=? LIMIT 1");
        $stmt->bind_param("i", $konselorId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $this->hydrate($row) : null;
    }

    private const ADMIN_SORTABLE = [
        'nama'             => 'u.nama',
        'profesi'          => 'k.profesi',
        'pengalaman_tahun' => 'k.pengalaman_tahun',
        'created_at'       => 'k.created_at',
    ];

    /**
     * Admin management: search/filter/sort/paginate over every konselor account
     * (active or not, profile-complete or not) — backs /admin/counselors.
     * @param array $filters ['search'=>?, 'profesi'=>?, 'status_aktif'=>'1'|'0'|null]
     * @return array{items: array, total: int}
     */
    public function paginatedForAdmin(array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = '';
        $params = [];
        $types = '';

        if (!empty($filters['search'])) {
            $where .= ' AND (u.nama LIKE ? OR k.nomor_registrasi LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like]);
            $types .= 'ss';
        }
        if (!empty($filters['profesi'])) {
            $where .= ' AND k.profesi = ?';
            $params[] = $filters['profesi'];
            $types .= 's';
        }
        if (($filters['status_aktif'] ?? '') !== '') {
            $where .= ' AND k.status_aktif = ?';
            $params[] = (int) $filters['status_aktif'];
            $types .= 'i';
        }

        $countStmt = $this->db->prepare('SELECT COUNT(*) AS c FROM (' . self::SELECT . $where . ') x');
        if ($params) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::ADMIN_SORTABLE[$sort] ?? 'u.nama';
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
            'has_profile' => $row['konselor_id'] !== null,
        ]);
    }

    // Creates the login account (role=konselor) and its extended profile together.
    // Rolled back as one unit so a duplicate nomor_registrasi can't leave a bare,
    // profile-less user account behind.
    public function createCounselor(
        string $nama,
        string $username,
        string $email,
        string $hashedPassword,
        string $nomorRegistrasi,
        string $profesi,
        ?string $spesialisasi,
        ?string $pendidikan,
        int $pengalamanTahun,
        ?string $bahasa,
        float $biayaKonsultasi,
        int $durasiSesi,
        string $metodeKonsultasi,
        ?string $biografi,
        bool $statusAktif,
        ?string $fotoProfil = null
    ): int {
        $this->db->begin_transaction();

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO users (nama, username, email, password, role, status, created_at)
                 VALUES (?, ?, ?, ?, 'konselor', 'active', NOW())"
            );
            $stmt->bind_param('ssss', $nama, $username, $email, $hashedPassword);
            $stmt->execute();
            $userId = (int) $this->db->insert_id;

            $konselor = new Konselor([
                'user_id' => $userId,
                'nomor_registrasi' => $nomorRegistrasi,
                'profesi' => $profesi,
                'spesialisasi' => $spesialisasi,
                'pendidikan' => $pendidikan,
                'pengalaman_tahun' => $pengalamanTahun,
                'bahasa' => $bahasa,
                'biaya_konsultasi' => $biayaKonsultasi,
                'durasi_sesi' => $durasiSesi,
                'metode_konsultasi' => $metodeKonsultasi,
                'foto_profil' => $fotoProfil,
                'biografi' => $biografi,
                'status_verifikasi' => true,
                'status_aktif' => $statusAktif,
            ]);
            $this->create($konselor);

            $this->db->commit();

            return $userId;
        } catch (\mysqli_sql_exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function updateUserBasic(int $userId, string $nama, string $username, string $email): void
    {
        $stmt = $this->db->prepare('UPDATE users SET nama = ?, username = ?, email = ? WHERE id = ?');
        $stmt->bind_param('sssi', $nama, $username, $email, $userId);
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

    // Creates the konselor row if this user doesn't have one yet, otherwise updates it.
    public function upsertProfile(
        int $userId,
        string $nomorRegistrasi,
        string $profesi,
        ?string $spesialisasi,
        ?string $pendidikan,
        int $pengalamanTahun,
        ?string $bahasa,
        float $biayaKonsultasi,
        int $durasiSesi,
        string $metodeKonsultasi,
        ?string $biografi,
        bool $statusAktif,
        ?string $fotoProfil = null
    ): void {
        $stmt = $this->db->prepare('SELECT konselor_id, foto_profil FROM konselor WHERE user_id = ? LIMIT 1');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();

        $konselor = new Konselor([
            'konselor_id' => $existing['konselor_id'] ?? 0,
            'user_id' => $userId,
            'nomor_registrasi' => $nomorRegistrasi,
            'profesi' => $profesi,
            'spesialisasi' => $spesialisasi,
            'pendidikan' => $pendidikan,
            'pengalaman_tahun' => $pengalamanTahun,
            'bahasa' => $bahasa,
            'biaya_konsultasi' => $biayaKonsultasi,
            'durasi_sesi' => $durasiSesi,
            'metode_konsultasi' => $metodeKonsultasi,
            'foto_profil' => $fotoProfil ?? ($existing['foto_profil'] ?? null),
            'biografi' => $biografi,
            'status_verifikasi' => true,
            'status_aktif' => $statusAktif,
        ]);

        if ($existing) {
            $this->update($konselor);
        } else {
            $this->create($konselor);
        }
    }

    // Soft delete / reactivate — only meaningful once a konselor row exists.
    public function setActive(int $konselorId, bool $active): void
    {
        $status = $active ? 1 : 0;
        $stmt = $this->db->prepare('UPDATE konselor SET status_aktif = ? WHERE konselor_id = ?');
        $stmt->bind_param('ii', $status, $konselorId);
        $stmt->execute();
    }

    public function nomorRegistrasiExists(string $nomor, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT 1 FROM konselor WHERE nomor_registrasi=? AND konselor_id<>? LIMIT 1");
            $stmt->bind_param("si", $nomor, $excludeId);
        } else {
            $stmt = $this->db->prepare("SELECT 1 FROM konselor WHERE nomor_registrasi=? LIMIT 1");
            $stmt->bind_param("s", $nomor);
        }
        $stmt->execute();
        return (bool)$stmt->get_result()->fetch_row();
    }

    public function create(Konselor $k): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO konselor
            (user_id,nomor_registrasi,profesi,spesialisasi,pendidikan,pengalaman_tahun,bahasa,biaya_konsultasi,durasi_sesi,metode_konsultasi,foto_profil,biografi,status_verifikasi,status_aktif)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        $ver = $k->statusVerifikasi ? 1 : 0;
        $aktif = $k->statusAktif ? 1 : 0;
        $stmt->bind_param(
            "issssisdisssii",
            $k->userId,
            $k->nomorRegistrasi,
            $k->profesi,
            $k->spesialisasi,
            $k->pendidikan,
            $k->pengalamanTahun,
            $k->bahasa,
            $k->biayaKonsultasi,
            $k->durasiSesi,
            $k->metodeKonsultasi,
            $k->fotoProfil,
            $k->biografi,
            $ver,
            $aktif
        );
        $stmt->execute();
        return (int)$this->db->insert_id;
    }

    public function update(Konselor $k): void
    {
        $stmt = $this->db->prepare(
            "UPDATE konselor SET
            nomor_registrasi=?, profesi=?, spesialisasi=?, pendidikan=?,
            pengalaman_tahun=?, bahasa=?, biaya_konsultasi=?, durasi_sesi=?,
            metode_konsultasi=?, foto_profil=?, biografi=?,
            status_verifikasi=?, status_aktif=?
            WHERE konselor_id=?"
        );
        $ver = $k->statusVerifikasi ? 1 : 0;
        $aktif = $k->statusAktif ? 1 : 0;
        $stmt->bind_param(
            "ssssisdisssiii",
            $k->nomorRegistrasi,
            $k->profesi,
            $k->spesialisasi,
            $k->pendidikan,
            $k->pengalamanTahun,
            $k->bahasa,
            $k->biayaKonsultasi,
            $k->durasiSesi,
            $k->metodeKonsultasi,
            $k->fotoProfil,
            $k->biografi,
            $ver,
            $aktif,
            $k->konselorId
        );
        $stmt->execute();
    }

    public function delete(int $konselorId): void
    {
        $stmt = $this->db->prepare("DELETE FROM konselor WHERE konselor_id=?");
        $stmt->bind_param("i", $konselorId);
        $stmt->execute();
    }

    private function hydrate(array $row): array
    {
        return array_merge((new Konselor($row))->toArray(), [
            'nama' => $row['nama'],
            'username' => $row['username'],
            'email' => $row['email'],
            'profile_image' => $row['profile_image']
        ]);
    }
}
