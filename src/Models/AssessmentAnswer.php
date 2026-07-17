<?php

namespace App\Models;

class AssessmentAnswer
{
    public int $id;
    public int $submissionId;
    public int $questionId;
    public int $choiceId;
    public int $scoreValue;

    public function __construct(array $data)
    {
        $this->id           = (int) ($data['id'] ?? 0);
        $this->submissionId = (int) ($data['submission_id'] ?? 0);
        $this->questionId   = (int) ($data['question_id'] ?? 0);
        $this->choiceId     = (int) ($data['choice_id'] ?? 0);
        $this->scoreValue   = (int) ($data['score_value'] ?? 0);
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'submission_id' => $this->submissionId,
            'question_id'   => $this->questionId,
            'choice_id'     => $this->choiceId,
            'score_value'   => $this->scoreValue,
        ];
    }
}
