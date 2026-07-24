<?php

namespace App\Models;

class DiaryEntry
{
    public int $id;
    public ?int $userId;
    public ?string $entryDate;

    // 1. Situation
    public string $situation;
    // 2. Pikiran Pertama (Automatic Thought)
    public string $initialThoughts;
    // 3. Emosi yang Dirasakan
    public array $emotionsList;
    public ?string $otherEmotions;
    public int $emotionIntensity;
    // 4. Reaksi Fisik
    public array $physicalReactionsList;
    public ?string $otherPhysicalReactions;
    // 5. Behavior
    public string $behavior;

    // Optional reflective sections
    public ?string $selfReflection;
    public array $gratitudeList;
    public ?string $tomorrowPlan;

    public bool $isPrivate;
    public ?int $sharedCounselorId;

    public ?string $createdAt;

    public function __construct(array $data)
    {
        $this->id                 = (int) ($data['id'] ?? 0);
        $this->userId              = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $this->entryDate           = $data['entry_date'] ?? null;

        $this->situation              = $data['situation'] ?? '';
        $this->initialThoughts          = $data['initial_thoughts'] ?? '';
        $this->emotionsList            = !empty($data['emotions_list']) ? (json_decode($data['emotions_list'], true) ?: []) : [];
        $this->otherEmotions         = $data['other_emotions'] ?? null;
        $this->emotionIntensity      = (int) ($data['emotion_intensity'] ?? 0);
        $this->physicalReactionsList      = !empty($data['physical_reactions_list']) ? (json_decode($data['physical_reactions_list'], true) ?: []) : [];
        $this->otherPhysicalReactions   = $data['other_physical_reactions'] ?? null;
        $this->behavior             = $data['behavior'] ?? '';

        $this->selfReflection       = $data['self_reflection'] ?? null;
        $this->gratitudeList        = !empty($data['gratitude_list']) ? (json_decode($data['gratitude_list'], true) ?: []) : [];
        $this->tomorrowPlan         = $data['tomorrow_plan'] ?? null;

        $this->isPrivate            = (bool) ($data['is_private'] ?? true);
        $this->sharedCounselorId     = isset($data['shared_counselor_id']) ? (int) $data['shared_counselor_id'] : null;

        $this->createdAt            = $data['created_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id'                    => $this->id,
            'user_id'               => $this->userId,
            'entry_date'            => $this->entryDate,
            'situation'               => $this->situation,
            'initial_thoughts'          => $this->initialThoughts,
            'emotions_list'            => $this->emotionsList,
            'other_emotions'         => $this->otherEmotions,
            'emotion_intensity'      => $this->emotionIntensity,
            'physical_reactions_list'     => $this->physicalReactionsList,
            'other_physical_reactions'  => $this->otherPhysicalReactions,
            'behavior'              => $this->behavior,
            'self_reflection'       => $this->selfReflection,
            'gratitude_list'        => $this->gratitudeList,
            'tomorrow_plan'         => $this->tomorrowPlan,
            'is_private'            => $this->isPrivate,
            'shared_counselor_id'    => $this->sharedCounselorId,
            'created_at'            => $this->createdAt,
        ];
    }
}
