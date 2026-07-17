<?php

namespace App\Models;

class AssessmentChoice
{
    public int $id;
    public int $questionId;
    public int $orderNo;
    public string $label;
    public int $scoreValue;

    public function __construct(array $data)
    {
        $this->id         = (int) ($data['id'] ?? 0);
        $this->questionId = (int) ($data['question_id'] ?? 0);
        $this->orderNo    = (int) ($data['order_no'] ?? 0);
        $this->label      = $data['label'] ?? '';
        $this->scoreValue = (int) ($data['score_value'] ?? 0);
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'question_id' => $this->questionId,
            'order_no'    => $this->orderNo,
            'label'       => $this->label,
            'score_value' => $this->scoreValue,
        ];
    }
}
