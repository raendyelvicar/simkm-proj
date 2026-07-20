<?php

namespace App\Models;

class ActivityPlan
{
    public int $id;
    public int $userId;
    public string $title;
    public ?string $description;
    public ?string $plannedDate;
    public ?int $moodBefore;
    public ?int $moodAfter;
    public string $status;
    public ?string $createdAt;

    public function __construct(array $data)
    {
        $this->id           = (int) ($data['id'] ?? 0);
        $this->userId        = (int) ($data['user_id'] ?? 0);
        $this->title         = $data['title'] ?? '';
        $this->description   = $data['description'] ?? null;
        $this->plannedDate   = $data['planned_date'] ?? null;
        $this->moodBefore    = isset($data['mood_before']) ? (int) $data['mood_before'] : null;
        $this->moodAfter     = isset($data['mood_after']) ? (int) $data['mood_after'] : null;
        $this->status        = $data['status'] ?? 'planned';
        $this->createdAt     = $data['created_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->userId,
            'title'         => $this->title,
            'description'   => $this->description,
            'planned_date'  => $this->plannedDate,
            'mood_before'   => $this->moodBefore,
            'mood_after'    => $this->moodAfter,
            'status'        => $this->status,
            'created_at'    => $this->createdAt,
        ];
    }
}
