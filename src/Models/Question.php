<?php

namespace App\Models;

class Question
{
    public int $id;
    public string $question;
    public string $kategori;
    public int $bobotSkor;
    public string $tipePilihan;
    public string $createdAt;

    public function __construct(array $data)
    {
        $this->id          = (int) ($data['id'] ?? 0);
        $this->question    = $data['question'] ?? '';
        $this->kategori    = $data['kategori'] ?? '';
        $this->bobotSkor   = (int) ($data['bobot_skor'] ?? 0);
        $this->tipePilihan = $data['tipe_pilihan'] ?? 'Likert Scale';
        $this->createdAt   = $data['created_at'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'question'     => $this->question,
            'kategori'     => $this->kategori,
            'bobot_skor'   => $this->bobotSkor,
            'tipe_pilihan' => $this->tipePilihan,
            'created_at'   => $this->createdAt,
        ];
    }
}
