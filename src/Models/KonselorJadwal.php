<?php

namespace App\Models;

class KonselorJadwal
{
    public int $jadwalId;
    public int $konselorId;

    public string $hari;

    public string $jamMulai;

    public string $jamSelesai;

    public int $kuota;

    public bool $statusAktif;

    public ?string $createdAt;

    public function __construct(array $data)
    {
        $this->jadwalId = (int)($data['jadwal_id'] ?? 0);
        $this->konselorId = (int)($data['konselor_id'] ?? 0);
        $this->hari = $data['hari'] ?? '';
        $this->jamMulai = $data['jam_mulai'] ?? '';
        $this->jamSelesai = $data['jam_selesai'] ?? '';
        $this->kuota = (int)($data['kuota'] ?? 10);
        $this->statusAktif = (bool)($data['status_aktif'] ?? true);
        $this->createdAt = $data['created_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'jadwal_id' => $this->jadwalId,
            'konselor_id' => $this->konselorId,
            'hari' => $this->hari,
            'jam_mulai' => $this->jamMulai,
            'jam_selesai' => $this->jamSelesai,
            'kuota' => $this->kuota,
            'status_aktif' => $this->statusAktif,
            'created_at' => $this->createdAt
        ];
    }
}
