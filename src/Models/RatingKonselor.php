<?php

namespace App\Models;

class RatingKonselor
{
    public int $ratingId;
    public int $bookingId;
    public int $userId;
    public int $konselorId;

    public int $rating;

    public ?string $komentar;

    public ?string $createdAt;

    public function __construct(array $data)
    {
        $this->ratingId = (int)($data['rating_id'] ?? 0);
        $this->bookingId = (int)($data['booking_id'] ?? 0);
        $this->userId = (int)($data['user_id'] ?? 0);
        $this->konselorId = (int)($data['konselor_id'] ?? 0);
        $this->rating = (int)($data['rating'] ?? 0);
        $this->komentar = $data['komentar'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'rating_id' => $this->ratingId,
            'booking_id' => $this->bookingId,
            'user_id' => $this->userId,
            'konselor_id' => $this->konselorId,
            'rating' => $this->rating,
            'komentar' => $this->komentar,
            'created_at' => $this->createdAt
        ];
    }
}
