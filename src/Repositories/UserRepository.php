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

    public function create(string $name, string $username, string $email, string $hashedPassword): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, username, email, password, created_at) VALUES (?, ?, ?, ?, NOW())'
        );
        $stmt->bind_param('ssss', $name, $username, $email, $hashedPassword);
        $stmt->execute();

        return (int) $this->db->insert_id;
    }
}
