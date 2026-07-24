<?php

namespace App\Models;

class CounselingBooking
{
    public int $bookingId;
    public int $userId;
    public int $counselorId;
    public ?int $scheduleId;

    public string $date;
    public string $jamMulai;
    public string $jamSelesai;

    public ?string $complaint;
    public string $status;

    public ?string $createdAt;

    public function __construct(array $data)
    {
        $this->bookingId = (int)($data['booking_id'] ?? 0);
        $this->userId = (int)($data['user_id'] ?? 0);
        $this->counselorId = (int)($data['counselor_id'] ?? 0);
        $this->scheduleId = isset($data['schedule_id']) ? (int) $data['schedule_id'] : null;

        $this->date = $data['date'] ?? '';
        $this->jamMulai = $data['start_time'] ?? '';
        $this->jamSelesai = $data['end_time'] ?? '';

        $this->complaint = $data['complaint'] ?? null;
        $this->status = $data['status'] ?? 'Pending';

        $this->createdAt = $data['created_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'booking_id' => $this->bookingId,
            'user_id' => $this->userId,
            'counselor_id' => $this->counselorId,
            'schedule_id' => $this->scheduleId,
            'date' => $this->date,
            'start_time' => $this->jamMulai,
            'end_time' => $this->jamSelesai,
            'complaint' => $this->complaint,
            'status' => $this->status,
            'created_at' => $this->createdAt,
        ];
    }
}
