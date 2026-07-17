<?php

namespace App\Models;

class DailyTip
{
    public int $id;
    public string $title;
    public string $content;
    public bool $isActive;
    public int $createdBy;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(array $data)
    {
        $this->id        = (int) ($data['id'] ?? 0);
        $this->title     = $data['title'] ?? '';
        $this->content   = $data['content'] ?? '';
        $this->isActive  = (bool) ($data['is_active'] ?? true);
        $this->createdBy = (int) ($data['created_by'] ?? 0);
        $this->createdAt = $data['created_at'] ?? '';
        $this->updatedAt = $data['updated_at'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'content'    => $this->content,
            'is_active'  => $this->isActive,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
