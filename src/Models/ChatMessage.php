<?php

namespace App\Models;

class ChatMessage
{
    public int $id;
    public int $userId;
    public ?int $senderId;
    public ?int $receiverId;
    public string $message;
    public string $createdAt;
    public bool $isRead;

    public function __construct(array $data)
    {
        $this->id         = (int) ($data['id'] ?? 0);
        $this->userId     = (int) ($data['user_id'] ?? 0);
        $this->senderId   = isset($data['sender_id']) ? (int) $data['sender_id'] : null;
        $this->receiverId = isset($data['receiver_id']) ? (int) $data['receiver_id'] : null;
        $this->message    = $data['message'] ?? '';
        $this->createdAt  = $data['created_at'] ?? '';
        $this->isRead     = (bool) ($data['is_read'] ?? false);
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'user_id'     => $this->userId,
            'sender_id'   => $this->senderId,
            'receiver_id' => $this->receiverId,
            'message'     => $this->message,
            'created_at'  => $this->createdAt,
            'is_read'     => $this->isRead,
        ];
    }
}
