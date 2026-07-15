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
}
