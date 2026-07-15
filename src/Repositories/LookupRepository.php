<?php

namespace App\Repositories;

use App\Core\Database;
use mysqli;

class LookupRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function getFakultas(): array
    {
        $result = $this->db->query('SELECT id, name FROM fakultas ORDER BY name');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getJurusanByFakultas(int $fakultasId): array
    {
        $stmt = $this->db->prepare('SELECT id, name FROM jurusan WHERE fakultas_id = ? ORDER BY name');
        $stmt->bind_param('i', $fakultasId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // The users table stores fakultas/jurusan as plain text, while the
    // register form submits their lookup ids — these resolve one to the other.
    public function findFakultasName(int $id): ?string
    {
        $stmt = $this->db->prepare('SELECT name FROM fakultas WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row['name'] ?? null;
    }

    public function findJurusanName(int $id): ?string
    {
        $stmt = $this->db->prepare('SELECT name FROM jurusan WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row['name'] ?? null;
    }
}
