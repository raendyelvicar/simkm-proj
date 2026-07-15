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

    // Accounts awaiting admin review — always mahasiswa, since konselor/admin
    // accounts are created directly with status=active by an admin.
    public function allPendingMahasiswa(): array
    {
        $result = $this->db->query(
            "SELECT * FROM users WHERE role = 'mahasiswa' AND status = 'pending' ORDER BY created_at ASC"
        );

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = new User($row);
        }

        return $users;
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

    public function allAdminEmails(): array
    {
        $result = $this->db->query("SELECT email FROM users WHERE role = 'admin' AND email != ''");

        return $result ? array_column($result->fetch_all(MYSQLI_ASSOC), 'email') : [];
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
