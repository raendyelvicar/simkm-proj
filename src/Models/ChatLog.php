<?php

namespace App\Models;

class ChatLog
{
    public int $id;
    public ?int $userId;
    public ?string $sender;
    public ?string $message;

    public function __construct(array $data)
    {
        $this->id      = (int) ($data['id'] ?? 0);
        $this->userId  = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $this->sender  = $data['sender'] ?? null;
        $this->message = $data['message'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id'      => $this->id,
            'user_id' => $this->userId,
            'sender'  => $this->sender,
            'message' => $this->message,
        ];
    }
}
