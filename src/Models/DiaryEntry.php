<?php

namespace App\Models;

class DiaryEntry
{
    public int $id;
    public ?int $userId;
    public ?string $entryDate;

    // 1. Situasi
    public string $situasi;
    // 2. Pikiran Pertama (Automatic Thought)
    public string $pikiranAwal;
    // 3. Emosi yang Dirasakan
    public array $emosiList;
    public ?string $emosiLainnya;
    public int $intensitasEmosi;
    // 4. Reaksi Fisik
    public array $reaksiFisikList;
    public ?string $reaksiFisikLainnya;
    // 5. Perilaku
    public string $perilaku;

    // Optional reflective sections
    public ?string $selfReflection;
    public array $gratitudeList;
    public ?string $rencanaBesok;

    public bool $isPrivate;
    public ?int $sharedKonselorId;

    public ?string $createdAt;

    public function __construct(array $data)
    {
        $this->id                 = (int) ($data['id'] ?? 0);
        $this->userId              = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $this->entryDate           = $data['entry_date'] ?? null;

        $this->situasi              = $data['situasi'] ?? '';
        $this->pikiranAwal          = $data['pikiran_awal'] ?? '';
        $this->emosiList            = !empty($data['emosi_list']) ? (json_decode($data['emosi_list'], true) ?: []) : [];
        $this->emosiLainnya         = $data['emosi_lainnya'] ?? null;
        $this->intensitasEmosi      = (int) ($data['intensitas_emosi'] ?? 0);
        $this->reaksiFisikList      = !empty($data['reaksi_fisik_list']) ? (json_decode($data['reaksi_fisik_list'], true) ?: []) : [];
        $this->reaksiFisikLainnya   = $data['reaksi_fisik_lainnya'] ?? null;
        $this->perilaku             = $data['perilaku'] ?? '';

        $this->selfReflection       = $data['self_reflection'] ?? null;
        $this->gratitudeList        = !empty($data['gratitude_list']) ? (json_decode($data['gratitude_list'], true) ?: []) : [];
        $this->rencanaBesok         = $data['rencana_besok'] ?? null;

        $this->isPrivate            = (bool) ($data['is_private'] ?? true);
        $this->sharedKonselorId     = isset($data['shared_konselor_id']) ? (int) $data['shared_konselor_id'] : null;

        $this->createdAt            = $data['created_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id'                    => $this->id,
            'user_id'               => $this->userId,
            'entry_date'            => $this->entryDate,
            'situasi'               => $this->situasi,
            'pikiran_awal'          => $this->pikiranAwal,
            'emosi_list'            => $this->emosiList,
            'emosi_lainnya'         => $this->emosiLainnya,
            'intensitas_emosi'      => $this->intensitasEmosi,
            'reaksi_fisik_list'     => $this->reaksiFisikList,
            'reaksi_fisik_lainnya'  => $this->reaksiFisikLainnya,
            'perilaku'              => $this->perilaku,
            'self_reflection'       => $this->selfReflection,
            'gratitude_list'        => $this->gratitudeList,
            'rencana_besok'         => $this->rencanaBesok,
            'is_private'            => $this->isPrivate,
            'shared_konselor_id'    => $this->sharedKonselorId,
            'created_at'            => $this->createdAt,
        ];
    }
}
