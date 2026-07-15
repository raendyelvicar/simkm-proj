<?php

namespace App\Models;

class DiaryEntry
{
    public int $id;
    public ?int $userId;
    public ?string $entryDate;
    public string $judul;
    public ?string $content;
    public string $moodLevel;
    public bool $isPrivate;
    public ?string $createdAt;

    public function __construct(array $data)
    {
        $this->id        = (int) ($data['id'] ?? 0);
        $this->userId    = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $this->entryDate = $data['entry_date'] ?? null;
        $this->judul     = $data['judul'] ?? '';
        $this->content   = $data['content'] ?? null;
        $this->moodLevel = $data['mood_level'] ?? '';
        $this->isPrivate = (bool) ($data['is_private'] ?? true);
        $this->createdAt = $data['created_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'user_id'    => $this->userId,
            'entry_date' => $this->entryDate,
            'judul'      => $this->judul,
            'content'    => $this->content,
            'mood_level' => $this->moodLevel,
            'is_private' => $this->isPrivate,
            'created_at' => $this->createdAt,
        ];
    }
}
