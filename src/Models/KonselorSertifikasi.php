<?php

namespace App\Models;

class KonselorSertifikasi
{
    public int $sertifikasiId;

    public int $konselorId;

    public string $namaSertifikasi;

    public ?string $penerbit;

    public ?string $nomorSertifikat;

    public ?string $tahun;

    public ?string $createdAt;

    public function __construct(array $data)
    {
        $this->sertifikasiId = (int)($data['sertifikasi_id'] ?? 0);

        $this->konselorId = (int)($data['konselor_id'] ?? 0);

        $this->namaSertifikasi = $data['nama_sertifikasi'] ?? '';

        $this->penerbit = $data['penerbit'] ?? null;

        $this->nomorSertifikat = $data['nomor_sertifikat'] ?? null;

        $this->tahun = $data['tahun'] ?? null;

        $this->createdAt = $data['created_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'sertifikasi_id'   => $this->sertifikasiId,
            'konselor_id'      => $this->konselorId,
            'nama_sertifikasi' => $this->namaSertifikasi,
            'penerbit'         => $this->penerbit,
            'nomor_sertifikat' => $this->nomorSertifikat,
            'tahun'            => $this->tahun,
            'created_at'       => $this->createdAt,
        ];
    }
}
