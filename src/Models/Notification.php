<?php

namespace App\Models;

class Notification
{
    public int $id;
    public int $userId;
    public string $type;
    public string $title;
    public string $message;
    public ?string $link;
    public bool $isRead;
    public string $createdAt;

    public function __construct(array $data)
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->userId = (int) ($data['user_id'] ?? 0);
        $this->type = $data['type'] ?? 'general';
        $this->title = $data['title'] ?? '';
        $this->message = $data['message'] ?? '';
        $this->link = $data['link'] ?? null;
        $this->isRead = (bool) ($data['is_read'] ?? false);
        $this->createdAt = $data['created_at'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'link' => $this->link,
            'is_read' => $this->isRead,
            'created_at' => $this->createdAt,
        ];
    }
}
