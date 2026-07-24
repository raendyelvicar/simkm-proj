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

    public function getFaculty(): array
    {
        $result = $this->db->query('SELECT id, name FROM faculties ORDER BY name');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getMajorByFaculty(int $facultyId): array
    {
        $stmt = $this->db->prepare('SELECT id, name FROM majors WHERE faculty_id = ? ORDER BY name');
        $stmt->bind_param('i', $facultyId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // The users table stores faculty/major as plain text, while the
    // register form submits their lookup ids — these resolve one to the other.
    public function findFacultyName(int $id): ?string
    {
        $stmt = $this->db->prepare('SELECT name FROM faculties WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row['name'] ?? null;
    }

    public function findMajorName(int $id): ?string
    {
        $stmt = $this->db->prepare('SELECT name FROM majors WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row['name'] ?? null;
    }
}
