<?php

namespace App\Models;

class Article
{
    public int $id;
    public int $adminId;
    public string $title;
    public string $content;
    public ?string $category;
    public ?string $image;
    public ?string $tags;
    public ?string $publishedAt;
    public string $createdAt;
    public int $userId;

    public function __construct(array $data)
    {
        $this->id          = (int) ($data['id'] ?? 0);
        $this->adminId     = (int) ($data['admin_id'] ?? 0);
        $this->title       = $data['title'] ?? '';
        $this->content     = $data['content'] ?? '';
        $this->category    = $data['category'] ?? null;
        $this->image       = $data['image'] ?? null;
        $this->tags        = $data['tags'] ?? null;
        $this->publishedAt = $data['published_at'] ?? null;
        $this->createdAt   = $data['created_at'] ?? '';
        $this->userId      = (int) ($data['user_id'] ?? 0);
    }

    /** Tags are stored as a comma-separated string; split for display. */
    public function tagsList(): array
    {
        if (!$this->tags) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $this->tags)));
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'admin_id'     => $this->adminId,
            'title'        => $this->title,
            'content'      => $this->content,
            'category'     => $this->category,
            'image'        => $this->image,
            'tags'         => $this->tags,
            'tags_list'    => $this->tagsList(),
            'published_at' => $this->publishedAt,
            'created_at'   => $this->createdAt,
            'user_id'      => $this->userId,
        ];
    }
}
