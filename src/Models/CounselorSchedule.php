<?php

namespace App\Models;

class CounselorSchedule
{
    public int $scheduleId;
    public int $counselorId;

    public string $date;
    public string $jamMulai;
    public string $jamSelesai;

    public int $quota;

    public bool $isActive;

    public ?string $createdAt;

    public function __construct(array $data)
    {
        $this->scheduleId = (int)($data['schedule_id'] ?? 0);
        $this->counselorId = (int)($data['counselor_id'] ?? 0);

        $this->date = $data['date'] ?? '';
        $this->jamMulai = $data['start_time'] ?? '';
        $this->jamSelesai = $data['end_time'] ?? '';

        $this->quota = (int)($data['quota'] ?? 10);

        $this->isActive = (bool)($data['is_active'] ?? true);

        $this->createdAt = $data['created_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'schedule_id' => $this->scheduleId,
            'counselor_id' => $this->counselorId,
            'date' => $this->date,
            'start_time' => $this->jamMulai,
            'end_time' => $this->jamSelesai,
            'quota' => $this->quota,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
        ];
    }
}
