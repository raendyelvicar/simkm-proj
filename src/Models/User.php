<?php

namespace App\Models;

class User
{
    public int $id;
    public string $name;
    public string $fullName;
    public string $username;
    public string $password;
    public string $student_number;
    public string $gender;
    public string $faculty;
    public string $major;
    public string $phoneNumber;
    public string $email;
    public string $role;
    public string $profile;
    public string $status;
    public ?int $approvedBy;
    public ?string $approvedAt;
    public string $createdAt;

    public function __construct(array $data)
    {
        $this->id           = (int) ($data['id'] ?? 0);
        $this->name         = $data['name'] ?? '';
        $this->fullName     = $data['full_name'] ?? '';
        $this->username     = $data['username'] ?? '';
        $this->password     = $data['password'] ?? '';
        $this->student_number          = $data['student_number'] ?? '';
        $this->gender = $data['gender'] ?? '';
        $this->faculty     = $data['faculty'] ?? '';
        $this->major      = $data['major'] ?? '';
        $this->phoneNumber         = $data['phone_number'] ?? '';
        $this->email        = $data['email'] ?? '';
        $this->role         = $data['role'] ?? '';
        $this->profile      = $data['profile_image'] ?? '';
        $this->status       = $data['status'] ?? '';
        $this->approvedBy   = isset($data['approved_by']) ? (int) $data['approved_by'] : null;
        $this->approvedAt   = $data['approved_at'] ?? null;
        $this->createdAt    = $data['created_at'] ?? '';
    }

    /**
     * Safe for API responses / views — password is intentionally excluded.
     */
    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'full_name'     => $this->fullName,
            'username'      => $this->username,
            'student_number'           => $this->student_number,
            'gender' => $this->gender,
            'faculty'      => $this->faculty,
            'major'       => $this->major,
            'phone_number'         => $this->phoneNumber,
            'email'         => $this->email,
            'role'          => $this->role,
            'profile'       => $this->profile,
            'status'        => $this->status,
            'approved_by'   => $this->approvedBy,
            'approved_at'   => $this->approvedAt,
            'created_at'    => $this->createdAt,
        ];
    }
}
