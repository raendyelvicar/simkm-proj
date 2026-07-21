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

    private const SORTABLE = [
        'title'         => 'title',
        'published_at'  => 'published_at',
    ];

    /**
     * Search/filter/sort/paginate published articles — backs /article.
     * @param array $filters ['search'=>?, 'category'=>?]
     * @return array{items: array, total: int}
     */
    public function paginated(array $filters, string $sort, string $dir, int $page, int $perPage): array
    {
        $where = ' WHERE 1=1';
        $params = [];
        $types = '';

        if (!empty($filters['search'])) {
            $where .= ' AND (title LIKE ? OR content LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like]);
            $types .= 'ss';
        }
        if (!empty($filters['category'])) {
            $where .= ' AND category = ?';
            $params[] = $filters['category'];
            $types .= 's';
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) AS c FROM articles{$where}");
        if ($params) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::SORTABLE[$sort] ?? 'published_at';
        $orderDir = $dir === 'asc' ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $perPage;

        $dataStmt = $this->db->prepare("SELECT * FROM articles{$where} ORDER BY {$orderCol} {$orderDir}, id DESC LIMIT ? OFFSET ?");
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        $items = [];
        $result = $dataStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = new Article($row);
        }

        return ['items' => $items, 'total' => $total];
    }

    /** Distinct non-empty category values in use — for the filter dropdown. */
    public function distinctCategories(): array
    {
        $result = $this->db->query("SELECT DISTINCT category FROM articles WHERE category IS NOT NULL AND category != '' ORDER BY category");

        return array_column($result->fetch_all(MYSQLI_ASSOC), 'category');
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
