<?php

namespace App\Models;

class MonitoringPeriod
{
    public int $monitoringId;
    public int $bookingId;
    public int $userId;
    public int $konselorId;

    public string $startDate;
    public string $endDate;

    public ?string $createdAt;

    public function __construct(array $data)
    {
        $this->monitoringId = (int)($data['monitoring_id'] ?? 0);
        $this->bookingId = (int)($data['booking_id'] ?? 0);
        $this->userId = (int)($data['user_id'] ?? 0);
        $this->konselorId = (int)($data['konselor_id'] ?? 0);

        $this->startDate = $data['start_date'] ?? '';
        $this->endDate = $data['end_date'] ?? '';

        $this->createdAt = $data['created_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'monitoring_id' => $this->monitoringId,
            'booking_id' => $this->bookingId,
            'user_id' => $this->userId,
            'konselor_id' => $this->konselorId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'created_at' => $this->createdAt,
        ];
    }
}
