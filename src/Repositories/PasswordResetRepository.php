<?php

namespace App\Repositories;

use App\Core\Database;
use mysqli;

class PasswordResetRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    // Wipes any earlier unused tokens for this user before issuing a new one, so only
    // the most recently requested reset link is ever valid.
    public function create(int $userId, string $tokenHash, string $expiresAt): void
    {
        $this->invalidateForUser($userId);

        $stmt = $this->db->prepare(
            'INSERT INTO password_reset_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)'
        );
        $stmt->bind_param('iss', $userId, $tokenHash, $expiresAt);
        $stmt->execute();
    }

    // Returns the token row only if it's still unused and unexpired.
    public function findValidByTokenHash(string $tokenHash): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM password_reset_tokens
             WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()
             LIMIT 1'
        );
        $stmt->bind_param('s', $tokenHash);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ?: null;
    }

    public function markUsed(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE password_reset_tokens SET used_at = NOW() WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    public function invalidateForUser(int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE password_reset_tokens SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
    }
}
