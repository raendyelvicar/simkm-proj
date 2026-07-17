<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Article;
use mysqli;

class ArticleRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(): array
    {
        $result = $this->db->query(
            'SELECT * FROM articles ORDER BY published_at DESC, id DESC'
        );

        $articles = [];
        while ($row = $result->fetch_assoc()) {
            $articles[] = new Article($row);
        }

        return $articles;
    }

    // Most recently published articles, for previews like the landing page's "Artikel Terbaru" section.
    public function latest(int $limit = 3): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM articles ORDER BY published_at DESC, id DESC LIMIT ?'
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $articles = [];
        while ($row = $result->fetch_assoc()) {
            $articles[] = new Article($row);
        }

        return $articles;
    }

    public function find(int $id): ?Article
    {
        $stmt = $this->db->prepare('SELECT * FROM articles WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new Article($row) : null;
    }

    public function create(int $userId, string $title, string $content, ?string $category, ?string $tags, ?string $image): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO articles (admin_id, user_id, title, content, category, tags, image, published_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->bind_param('iisssss', $userId, $userId, $title, $content, $category, $tags, $image);
        $stmt->execute();

        return (int) $this->db->insert_id;
    }

    public function update(int $id, string $title, string $content, ?string $category, ?string $tags, ?string $image): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE articles SET title = ?, content = ?, category = ?, tags = ?, image = ? WHERE id = ?'
        );
        $stmt->bind_param('sssssi', $title, $content, $category, $tags, $image, $id);

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM articles WHERE id = ?');
        $stmt->bind_param('i', $id);

        return $stmt->execute();
    }
}
