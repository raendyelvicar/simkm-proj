<?php

namespace App\Models;

class SesiKonseling
{
    public int $sesiId;
    public int $bookingId;

    public ?string $catatanKonselor;
    public ?string $rekomendasi;
    public ?string $tindakLanjut;
    public ?int $durasi;

    public ?string $selesaiPada;
    public ?string $createdAt;

    public function __construct(array $data)
    {
        $this->sesiId = (int) ($data['sesi_id'] ?? 0);
        $this->bookingId = (int) ($data['booking_id'] ?? 0);

        $this->catatanKonselor = $data['catatan_konselor'] ?? null;
        $this->rekomendasi = $data['rekomendasi'] ?? null;
        $this->tindakLanjut = $data['tindak_lanjut'] ?? null;
        $this->durasi = isset($data['durasi']) ? (int) $data['durasi'] : null;

        $this->selesaiPada = $data['selesai_pada'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'sesi_id' => $this->sesiId,
            'booking_id' => $this->bookingId,
            'catatan_konselor' => $this->catatanKonselor,
            'rekomendasi' => $this->rekomendasi,
            'tindak_lanjut' => $this->tindakLanjut,
            'durasi' => $this->durasi,
            'selesai_pada' => $this->selesaiPada,
            'created_at' => $this->createdAt,
        ];
    }
}
