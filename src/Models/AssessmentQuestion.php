<?php

namespace App\Models;

class AssessmentQuestion
{
    public int $id;
    public string $type;
    public int $orderNo;
    public string $questionText;
    public ?string $dimension;
    public bool $isReverseScored;
    public string $createdAt;

    /** @var AssessmentChoice[] */
    public array $choices = [];

    public function __construct(array $data)
    {
        $this->id              = (int) ($data['id'] ?? 0);
        $this->type             = $data['type'] ?? '';
        $this->orderNo          = (int) ($data['order_no'] ?? 0);
        $this->questionText     = $data['question_text'] ?? '';
        $this->dimension        = $data['dimension'] ?? null;
        $this->isReverseScored  = (bool) ($data['is_reverse_scored'] ?? false);
        $this->createdAt        = $data['created_at'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'type'               => $this->type,
            'order_no'           => $this->orderNo,
            'question_text'      => $this->questionText,
            'dimension'          => $this->dimension,
            'is_reverse_scored'  => $this->isReverseScored,
            'created_at'         => $this->createdAt,
            'choices'            => array_map(fn (AssessmentChoice $c) => $c->toArray(), $this->choices),
        ];
    }
}
