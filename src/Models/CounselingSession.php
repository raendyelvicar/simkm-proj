<?php

namespace App\Models;

class CounselingSession
{
    public int $sessionId;
    public int $bookingId;

    public ?string $counselorNotes;
    public ?string $recommendation;
    public ?string $followUp;
    public ?int $duration;

    public ?string $completedAt;
    public ?string $createdAt;

    public function __construct(array $data)
    {
        $this->sessionId = (int) ($data['session_id'] ?? 0);
        $this->bookingId = (int) ($data['booking_id'] ?? 0);

        $this->counselorNotes = $data['counselor_notes'] ?? null;
        $this->recommendation = $data['recommendation'] ?? null;
        $this->followUp = $data['follow_up'] ?? null;
        $this->duration = isset($data['duration']) ? (int) $data['duration'] : null;

        $this->completedAt = $data['completed_at'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'session_id' => $this->sessionId,
            'booking_id' => $this->bookingId,
            'counselor_notes' => $this->counselorNotes,
            'recommendation' => $this->recommendation,
            'follow_up' => $this->followUp,
            'duration' => $this->duration,
            'completed_at' => $this->completedAt,
            'created_at' => $this->createdAt,
        ];
    }
}
