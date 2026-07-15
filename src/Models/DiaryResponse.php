<?php

namespace App\Models;

class DiaryResponse
{
    public int $id;
    public ?int $diaryId;
    public ?int $konselorId;
    public ?string $response;
    public string $createdAt;

    public function __construct(array $data)
    {
        $this->id         = (int) ($data['id'] ?? 0);
        $this->diaryId    = isset($data['diary_id']) ? (int) $data['diary_id'] : null;
        $this->konselorId = isset($data['konselor_id']) ? (int) $data['konselor_id'] : null;
        $this->response   = $data['response'] ?? null;
        $this->createdAt  = $data['created_at'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'diary_id'     => $this->diaryId,
            'konselor_id'  => $this->konselorId,
            'response'     => $this->response,
            'created_at'   => $this->createdAt,
        ];
    }
}
