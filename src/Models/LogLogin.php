<?php

namespace App\Models;

class LogLogin
{
    public int $id;
    public ?int $userId;
    public string $waktuLogin;
    public ?string $ipAddress;

    public function __construct(array $data)
    {
        $this->id         = (int) ($data['id'] ?? 0);
        $this->userId     = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $this->waktuLogin = $data['waktu_login'] ?? '';
        $this->ipAddress  = $data['ip_address'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'user_id'     => $this->userId,
            'waktu_login' => $this->waktuLogin,
            'ip_address'  => $this->ipAddress,
        ];
    }
}
