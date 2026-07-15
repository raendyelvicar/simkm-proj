<?php

namespace App\Models;

class Notification
{
    public int $id;
    public int $userId;
    public string $message;
    public bool $isRead;
    public string $createdAt;

    public function __construct(array $data)
    {
        $this->id        = (int) ($data['id'] ?? 0);
        $this->userId    = (int) ($data['user_id'] ?? 0);
        $this->message   = $data['message'] ?? '';
        $this->isRead    = (bool) ($data['is_read'] ?? false);
        $this->createdAt = $data['created_at'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'user_id'    => $this->userId,
            'message'    => $this->message,
            'is_read'    => $this->isRead,
            'created_at' => $this->createdAt,
        ];
    }
}
