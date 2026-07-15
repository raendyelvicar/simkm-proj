<?php

namespace App\Models;

class AssessmentResult
{
    public int $id;
    public ?int $userId;
    public ?string $username;
    public ?string $resultSummary;
    public string $assessmentDate;
    public string $createdAt;
    public string $tanggalTes;
    public int $totalSkor;
    public ?string $kesimpulan;
    public ?string $saranTindakan;
    public string $statusReview;

    public function __construct(array $data)
    {
        $this->id             = (int) ($data['id'] ?? 0);
        $this->userId         = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $this->username       = $data['username'] ?? null;
        $this->resultSummary  = $data['result_summary'] ?? null;
        $this->assessmentDate = $data['assessment_date'] ?? '';
        $this->createdAt      = $data['created_at'] ?? '';
        $this->tanggalTes     = $data['tanggal_tes'] ?? '';
        $this->totalSkor      = (int) ($data['total_skor'] ?? 0);
        $this->kesimpulan     = $data['kesimpulan'] ?? null;
        $this->saranTindakan  = $data['saran_tindakan'] ?? null;
        $this->statusReview   = $data['status_review'] ?? 'Belum Dilihat';
    }

    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'user_id'          => $this->userId,
            'username'         => $this->username,
            'result_summary'   => $this->resultSummary,
            'assessment_date'  => $this->assessmentDate,
            'created_at'       => $this->createdAt,
            'tanggal_tes'      => $this->tanggalTes,
            'total_skor'       => $this->totalSkor,
            'kesimpulan'       => $this->kesimpulan,
            'saran_tindakan'   => $this->saranTindakan,
            'status_review'    => $this->statusReview,
        ];
    }
}
