<?php

namespace App\Models;

class BookingKonseling
{
    public int $bookingId;
    public int $userId;
    public int $konselorId;
    public ?int $jadwalId;

    public string $tanggal;
    public string $jamMulai;
    public string $jamSelesai;

    public ?string $keluhan;
    public string $status;

    public ?string $createdAt;

    public function __construct(array $data)
    {
        $this->bookingId = (int)($data['booking_id'] ?? 0);
        $this->userId = (int)($data['user_id'] ?? 0);
        $this->konselorId = (int)($data['konselor_id'] ?? 0);
        $this->jadwalId = isset($data['jadwal_id']) ? (int) $data['jadwal_id'] : null;

        $this->tanggal = $data['tanggal'] ?? '';
        $this->jamMulai = $data['jam_mulai'] ?? '';
        $this->jamSelesai = $data['jam_selesai'] ?? '';

        $this->keluhan = $data['keluhan'] ?? null;
        $this->status = $data['status'] ?? 'Pending';

        $this->createdAt = $data['created_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'booking_id' => $this->bookingId,
            'user_id' => $this->userId,
            'konselor_id' => $this->konselorId,
            'jadwal_id' => $this->jadwalId,
            'tanggal' => $this->tanggal,
            'jam_mulai' => $this->jamMulai,
            'jam_selesai' => $this->jamSelesai,
            'keluhan' => $this->keluhan,
            'status' => $this->status,
            'created_at' => $this->createdAt,
        ];
    }
}
