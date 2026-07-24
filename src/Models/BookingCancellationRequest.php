<?php

namespace App\Models;

class BookingCancellationRequest
{
    public int $id;
    public int $bookingId;
    public string $previousStatus;
    public ?string $reason;
    public string $status;
    public ?string $adminNotes;
    public ?int $reviewedBy;
    public ?string $reviewedAt;
    public ?string $createdAt;

    public function __construct(array $data)
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->bookingId = (int) ($data['booking_id'] ?? 0);
        $this->previousStatus = $data['previous_status'] ?? 'Pending';
        $this->reason = $data['reason'] ?? null;
        $this->status = $data['status'] ?? 'Pending';
        $this->adminNotes = $data['admin_notes'] ?? null;
        $this->reviewedBy = isset($data['reviewed_by']) ? (int) $data['reviewed_by'] : null;
        $this->reviewedAt = $data['reviewed_at'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->bookingId,
            'previous_status' => $this->previousStatus,
            'reason' => $this->reason,
            'status' => $this->status,
            'admin_notes' => $this->adminNotes,
            'reviewed_by' => $this->reviewedBy,
            'reviewed_at' => $this->reviewedAt,
            'created_at' => $this->createdAt,
        ];
    }
}
