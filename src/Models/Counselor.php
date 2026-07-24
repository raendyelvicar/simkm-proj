<?php

namespace App\Models;

class Counselor
{
    public int $counselorId;
    public int $userId;

    public string $registrationNumber;
    public string $profession;

    public ?string $specialization;
    public ?string $education;

    public int $experienceYears;

    public ?string $languages;

    public float $consultationFee;

    public int $durationSession;

    public string $consultationMethod;

    public ?string $profilePhoto;

    public ?string $biography;

    public bool $verificationStatus;

    public bool $isActive;

    public ?string $createdAt;

    public ?string $updatedAt;

    public function __construct(array $data)
    {
        $this->counselorId = (int)($data['counselor_id'] ?? 0);
        $this->userId = (int)($data['user_id'] ?? 0);

        $this->registrationNumber = $data['registration_number'] ?? '';
        $this->profession = $data['profession'] ?? '';

        $this->specialization = $data['specialization'] ?? null;
        $this->education = $data['education'] ?? null;

        $this->experienceYears = (int)($data['experience_years'] ?? 0);

        $this->languages = $data['languages'] ?? null;

        $this->consultationFee = (float)($data['consultation_fee'] ?? 0);

        $this->durationSession = (int)($data['session_duration'] ?? 60);

        $this->consultationMethod = $data['consultation_method'] ?? 'Online';

        $this->profilePhoto = $data['profile_photo'] ?? null;

        $this->biography = $data['biography'] ?? null;

        $this->verificationStatus = (bool)($data['verification_status'] ?? false);

        $this->isActive = (bool)($data['is_active'] ?? true);

        $this->createdAt = $data['created_at'] ?? null;

        $this->updatedAt = $data['updated_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'counselor_id' => $this->counselorId,
            'user_id' => $this->userId,
            'registration_number' => $this->registrationNumber,
            'profession' => $this->profession,
            'specialization' => $this->specialization,
            'education' => $this->education,
            'experience_years' => $this->experienceYears,
            'languages' => $this->languages,
            'consultation_fee' => $this->consultationFee,
            'session_duration' => $this->durationSession,
            'consultation_method' => $this->consultationMethod,
            'profile_photo' => $this->profilePhoto,
            'biography' => $this->biography,
            'verification_status' => $this->verificationStatus,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
