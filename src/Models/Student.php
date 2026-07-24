<?php

namespace App\Models;

class Student
{
    public int $idStudent;
    public int $userId; // FK to users.id
    public string $student_number;
    public string $studyProgram;
    public string $faculty;
    public string $gender; // 'L' or 'P'
    public ?string $phoneNumber;

    public function __construct(array $data)
    {
        $this->idStudent  = (int) ($data['student_id'] ?? 0);
        $this->userId       = (int) ($data['id'] ?? 0); // note: column is `id` in this table, FK to users.id
        $this->student_number          = $data['student_number'] ?? '';
        $this->studyProgram = $data['study_program'] ?? '';
        $this->faculty     = $data['faculty'] ?? '';
        $this->gender = $data['gender'] ?? '';
        $this->phoneNumber         = $data['phone_number'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'student_id'  => $this->idStudent,
            'id'            => $this->userId,
            'student_number'           => $this->student_number,
            'study_program' => $this->studyProgram,
            'faculty'      => $this->faculty,
            'gender' => $this->gender,
            'phone_number'         => $this->phoneNumber,
        ];
    }
}
