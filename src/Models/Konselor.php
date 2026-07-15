<?php

namespace App\Models;

class Konselor
{
    public int $konselorId;
    public int $userId; // FK to users.id
    public string $nipNik;
    public ?string $spesialisasi;
    public ?string $jadwalPraktik;
    public bool $statusAktif;
    public ?string $biografiSingkat;

    public function __construct(array $data)
    {
        $this->konselorId      = (int) ($data['konselor_id'] ?? 0);
        $this->userId          = (int) ($data['id'] ?? 0); // note: column is `id` in this table, FK to users.id
        $this->nipNik          = $data['nip_nik'] ?? '';
        $this->spesialisasi    = $data['spesialisasi'] ?? null;
        $this->jadwalPraktik   = $data['jadwal_praktik'] ?? null;
        $this->statusAktif     = (bool) ($data['status_aktif'] ?? true);
        $this->biografiSingkat = $data['biografi_singkat'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'konselor_id'      => $this->konselorId,
            'id'               => $this->userId,
            'nip_nik'          => $this->nipNik,
            'spesialisasi'     => $this->spesialisasi,
            'jadwal_praktik'   => $this->jadwalPraktik,
            'status_aktif'     => $this->statusAktif,
            'biografi_singkat' => $this->biografiSingkat,
        ];
    }
}
