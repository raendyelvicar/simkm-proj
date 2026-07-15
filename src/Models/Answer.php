<?php

namespace App\Models;

class Answer
{
    public int $id;
    public ?int $userId;
    public ?int $questionId;
    public ?int $answerValue;

    public function __construct(array $data)
    {
        $this->id          = (int) ($data['id'] ?? 0);
        $this->userId      = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $this->questionId  = isset($data['question_id']) ? (int) $data['question_id'] : null;
        $this->answerValue = isset($data['answer_value']) ? (int) $data['answer_value'] : null;
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->userId,
            'question_id'   => $this->questionId,
            'answer_value'  => $this->answerValue,
        ];
    }
}
