<?php

namespace App\Services;

/**
 * Pure bucketing logic for Laporan Evaluasi Keterlibatan Student's "Status Keaktifan"
 * column. No DB access — same shape as AssessmentScoringService.
 */
class EngagementScoringService
{
    // Thresholds are on total actions (assessment + diary + self-help + booking counts)
    // within the report's selected date range. Tunable — adjust here if these buckets
    // feel too coarse/fine once there's real usage data to calibrate against.
    private const SANGAT_AKTIF_MIN = 10;
    private const AKTIF_MIN = 4;

    public function status(int $totalActions): string
    {
        if ($totalActions >= self::SANGAT_AKTIF_MIN) {
            return 'Sangat Aktif';
        }
        if ($totalActions >= self::AKTIF_MIN) {
            return 'Aktif';
        }

        return 'Kurang Aktif';
    }

    public function badgeClass(string $status): string
    {
        return match ($status) {
            'Sangat Aktif' => 'assess-badge-green',
            'Aktif'        => 'assess-badge-yellow',
            default        => 'assess-badge-red',
        };
    }
}
