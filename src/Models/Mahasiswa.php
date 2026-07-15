<?php

namespace App\Models;

class Mahasiswa
{
    public int $idMahasiswa;
    public int $userId; // FK to users.id
    public string $npm;
    public string $programStudi;
    public string $fakultas;
    public string $jenisKelamin; // 'L' or 'P'
    public ?string $noHp;

    public function __construct(array $data)
    {
        $this->idMahasiswa  = (int) ($data['id_mahasiswa'] ?? 0);
        $this->userId       = (int) ($data['id'] ?? 0); // note: column is `id` in this table, FK to users.id
        $this->npm          = $data['npm'] ?? '';
        $this->programStudi = $data['program_studi'] ?? '';
        $this->fakultas     = $data['fakultas'] ?? '';
        $this->jenisKelamin = $data['jenis_kelamin'] ?? '';
        $this->noHp         = $data['no_hp'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id_mahasiswa'  => $this->idMahasiswa,
            'id'            => $this->userId,
            'npm'           => $this->npm,
            'program_studi' => $this->programStudi,
            'fakultas'      => $this->fakultas,
            'jenis_kelamin' => $this->jenisKelamin,
            'no_hp'         => $this->noHp,
        ];
    }
}
