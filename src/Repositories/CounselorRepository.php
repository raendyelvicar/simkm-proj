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

    // Anyone with a konselor account, left-joined with their extended profile
    // (spesialisasi/bio/jadwal) so a counselor is discoverable as soon as
    // their account exists, even before an admin fills in the konselor row.
    // Returned as arrays (Konselor::toArray() merged with the user fields
    // the views need).
    private const SELECT = "
        SELECT u.id, u.nama, u.email, u.profile_image AS profile,
               k.konselor_id, k.nip_nik, k.spesialisasi, k.jadwal_praktik,
               k.biografi_singkat, k.status_aktif
        FROM users u
        LEFT JOIN konselor k ON k.id = u.id
        WHERE u.role = 'konselor'
          AND (k.status_aktif IS NULL OR k.status_aktif = 1)
    ";

    public function all(): array
    {
        $result = $this->db->query(self::SELECT . ' ORDER BY u.nama ASC');

        $counselors = [];
        while ($row = $result->fetch_assoc()) {
            $counselors[] = $this->hydrate($row);
        }

        return $counselors;
    }

    // $userId is users.id — the same id used as chat_messages.receiver_id.
    public function find(int $userId): ?array
    {
        $stmt = $this->db->prepare(self::SELECT . ' AND u.id = ? LIMIT 1');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? $this->hydrate($row) : null;
    }

    private function hydrate(array $row): array
    {
        return array_merge((new Konselor($row))->toArray(), [
            'nama'    => $row['nama'] ?? '',
            'profile' => $row['profile'] ?? '',
            'email'   => $row['email'] ?? '',
        ]);
    }

    // --- Admin management: sees every konselor account, active or not ---

    private const ADMIN_SELECT = "
        SELECT u.id, u.nama, u.username, u.email, u.profile_image AS profile,
               k.konselor_id, k.nip_nik, k.spesialisasi, k.jadwal_praktik,
               k.biografi_singkat, k.status_aktif
        FROM users u
        LEFT JOIN konselor k ON k.id = u.id
        WHERE u.role = 'konselor'
    ";

    public function allForAdmin(): array
    {
        $result = $this->db->query(self::ADMIN_SELECT . ' ORDER BY u.nama ASC');

        $counselors = [];
        while ($row = $result->fetch_assoc()) {
            $counselors[] = $this->hydrateAdmin($row);
        }

        return $counselors;
    }

    public function findForAdmin(int $userId): ?array
    {
        $stmt = $this->db->prepare(self::ADMIN_SELECT . ' AND u.id = ? LIMIT 1');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? $this->hydrateAdmin($row) : null;
    }

    private function hydrateAdmin(array $row): array
    {
        return array_merge($this->hydrate($row), [
            'username'    => $row['username'] ?? '',
            'has_profile' => $row['konselor_id'] !== null,
        ]);
    }

    public function nipNikExists(string $nipNik, ?int $excludeKonselorId = null): bool
    {
        if ($excludeKonselorId !== null) {
            $stmt = $this->db->prepare('SELECT 1 FROM konselor WHERE nip_nik = ? AND konselor_id != ? LIMIT 1');
            $stmt->bind_param('si', $nipNik, $excludeKonselorId);
        } else {
            $stmt = $this->db->prepare('SELECT 1 FROM konselor WHERE nip_nik = ? LIMIT 1');
            $stmt->bind_param('s', $nipNik);
        }
        $stmt->execute();

        return (bool) $stmt->get_result()->fetch_row();
    }

    // Creates the login account (role=konselor) and its extended profile together.
    // Rolled back as one unit so a duplicate nip_nik can't leave a bare,
    // profile-less user account behind.
    public function createCounselor(
        string $nama,
        string $username,
        string $email,
        string $hashedPassword,
        string $nipNik,
        ?string $spesialisasi,
        ?string $jadwalPraktik,
        ?string $biografiSingkat,
        bool $statusAktif
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

            $statusInt = $statusAktif ? 1 : 0;
            $stmt = $this->db->prepare(
                'INSERT INTO konselor (id, nip_nik, spesialisasi, jadwal_praktik, status_aktif, biografi_singkat)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param('isssis', $userId, $nipNik, $spesialisasi, $jadwalPraktik, $statusInt, $biografiSingkat);
            $stmt->execute();

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
        string $nipNik,
        ?string $spesialisasi,
        ?string $jadwalPraktik,
        ?string $biografiSingkat,
        bool $statusAktif
    ): void {
        $statusInt = $statusAktif ? 1 : 0;

        $stmt = $this->db->prepare('SELECT konselor_id FROM konselor WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();

        if ($existing) {
            $konselorId = (int) $existing['konselor_id'];
            $stmt = $this->db->prepare(
                'UPDATE konselor SET nip_nik = ?, spesialisasi = ?, jadwal_praktik = ?, biografi_singkat = ?, status_aktif = ?
                 WHERE konselor_id = ?'
            );
            $stmt->bind_param('ssssii', $nipNik, $spesialisasi, $jadwalPraktik, $biografiSingkat, $statusInt, $konselorId);
        } else {
            $stmt = $this->db->prepare(
                'INSERT INTO konselor (id, nip_nik, spesialisasi, jadwal_praktik, status_aktif, biografi_singkat)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param('isssis', $userId, $nipNik, $spesialisasi, $jadwalPraktik, $statusInt, $biografiSingkat);
        }

        $stmt->execute();
    }

    // Soft delete / reactivate — only meaningful once a konselor row exists.
    public function setActive(int $konselorId, bool $active): void
    {
        $status = $active ? 1 : 0;
        $stmt = $this->db->prepare('UPDATE konselor SET status_aktif = ? WHERE konselor_id = ?');
        $stmt->bind_param('ii', $status, $konselorId);
        $stmt->execute();
    }
}
