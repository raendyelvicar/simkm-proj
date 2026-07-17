<?php

namespace App\Models;

class AssessmentSubmission
{
    public int $id;
    public int $userId;
    public string $type;
    public int $totalScore;
    public int $maxScore;
    public string $category;
    public ?float $categoryPercentage;
    public array $dimensionScores;
    public bool $isTimedOut;
    public string $submittedAt;

    // Only present when hydrated from a JOIN with users (history/report views).
    public ?string $userName;

    public function __construct(array $data)
    {
        $this->id                 = (int) ($data['id'] ?? 0);
        $this->userId              = (int) ($data['user_id'] ?? 0);
        $this->type                 = $data['type'] ?? '';
        $this->totalScore           = (int) ($data['total_score'] ?? 0);
        $this->maxScore             = (int) ($data['max_score'] ?? 0);
        $this->category             = $data['category'] ?? '';
        $this->categoryPercentage   = isset($data['category_percentage']) ? (float) $data['category_percentage'] : null;
        $this->dimensionScores      = !empty($data['dimension_scores']) ? (json_decode($data['dimension_scores'], true) ?: []) : [];
        $this->isTimedOut           = (bool) ($data['is_timed_out'] ?? false);
        $this->submittedAt          = $data['submitted_at'] ?? '';
        $this->userName             = $data['nama'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id'                   => $this->id,
            'user_id'              => $this->userId,
            'type'                 => $this->type,
            'total_score'          => $this->totalScore,
            'max_score'            => $this->maxScore,
            'category'             => $this->category,
            'category_percentage'  => $this->categoryPercentage,
            'dimension_scores'     => $this->dimensionScores,
            'is_timed_out'         => $this->isTimedOut,
            'submitted_at'         => $this->submittedAt,
            'nama'                 => $this->userName,
        ];
    }
}
