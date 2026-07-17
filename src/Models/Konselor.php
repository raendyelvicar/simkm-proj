<?php

namespace App\Models;

class Konselor
{
    public int $konselorId;
    public int $userId;

    public string $nomorRegistrasi;
    public string $profesi;

    public ?string $spesialisasi;
    public ?string $pendidikan;

    public int $pengalamanTahun;

    public ?string $bahasa;

    public float $biayaKonsultasi;

    public int $durasiSesi;

    public string $metodeKonsultasi;

    public ?string $fotoProfil;

    public ?string $biografi;

    public bool $statusVerifikasi;

    public bool $statusAktif;

    public ?string $createdAt;

    public ?string $updatedAt;

    public function __construct(array $data)
    {
        $this->konselorId = (int)($data['konselor_id'] ?? 0);
        $this->userId = (int)($data['user_id'] ?? 0);

        $this->nomorRegistrasi = $data['nomor_registrasi'] ?? '';
        $this->profesi = $data['profesi'] ?? '';

        $this->spesialisasi = $data['spesialisasi'] ?? null;
        $this->pendidikan = $data['pendidikan'] ?? null;

        $this->pengalamanTahun = (int)($data['pengalaman_tahun'] ?? 0);

        $this->bahasa = $data['bahasa'] ?? null;

        $this->biayaKonsultasi = (float)($data['biaya_konsultasi'] ?? 0);

        $this->durasiSesi = (int)($data['durasi_sesi'] ?? 60);

        $this->metodeKonsultasi = $data['metode_konsultasi'] ?? 'Online';

        $this->fotoProfil = $data['foto_profil'] ?? null;

        $this->biografi = $data['biografi'] ?? null;

        $this->statusVerifikasi = (bool)($data['status_verifikasi'] ?? false);

        $this->statusAktif = (bool)($data['status_aktif'] ?? true);

        $this->createdAt = $data['created_at'] ?? null;

        $this->updatedAt = $data['updated_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'konselor_id' => $this->konselorId,
            'user_id' => $this->userId,
            'nomor_registrasi' => $this->nomorRegistrasi,
            'profesi' => $this->profesi,
            'spesialisasi' => $this->spesialisasi,
            'pendidikan' => $this->pendidikan,
            'pengalaman_tahun' => $this->pengalamanTahun,
            'bahasa' => $this->bahasa,
            'biaya_konsultasi' => $this->biayaKonsultasi,
            'durasi_sesi' => $this->durasiSesi,
            'metode_konsultasi' => $this->metodeKonsultasi,
            'foto_profil' => $this->fotoProfil,
            'biografi' => $this->biografi,
            'status_verifikasi' => $this->statusVerifikasi,
            'status_aktif' => $this->statusAktif,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
