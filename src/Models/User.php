<?php

namespace App\Models;

class User
{
    public int $id;
    public string $nama;
    public string $username;
    public string $password;
    public string $npm;
    public string $jenisKelamin;
    public string $fakultas;
    public string $jurusan;
    public string $noHp;
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
        $this->nama         = $data['nama'] ?? '';
        $this->username     = $data['username'] ?? '';
        $this->password     = $data['password'] ?? '';
        $this->npm          = $data['npm'] ?? '';
        $this->jenisKelamin = $data['jenis_kelamin'] ?? '';
        $this->fakultas     = $data['fakultas'] ?? '';
        $this->jurusan      = $data['jurusan'] ?? '';
        $this->noHp         = $data['no_hp'] ?? '';
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
            'nama'          => $this->nama,
            'username'      => $this->username,
            'npm'           => $this->npm,
            'jenis_kelamin' => $this->jenisKelamin,
            'fakultas'      => $this->fakultas,
            'jurusan'       => $this->jurusan,
            'no_hp'         => $this->noHp,
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
