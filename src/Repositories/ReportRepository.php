<?php

namespace App\Repositories;

use App\Core\Database;
use mysqli;

/**
 * Cross-domain aggregate queries backing the 8 Laporan report pages. Returns raw
 * associative arrays (not hydrated models) since every report joins several domains
 * together — the same shape AssessmentRepository::studentAssessmentSummaries() uses.
 *
 * Every report method expects $filters['date_from']/$filters['date_to'] to already be
 * concrete 'Y-m-d' strings (LaporanController fills in sane defaults before calling in),
 * so every date predicate here is a plain, always-present "BETWEEN ? AND ?" — no
 * conditional param counting needed. Role scoping is expressed via either
 * $filters['user_id'] (exact single student) or $filters['student_ids'] (konselor's
 * bimbingan list; omit both for admin's unrestricted view).
 */
class LaporanRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    // The "bimbingan" scope decision: union of students with an active/past monitoring
    // window and students with any booking history, for this konselor.
    public function konselorStudentIds(int $konselorId): array
    {
        $stmt = $this->db->prepare(
            'SELECT user_id FROM monitoring_periods WHERE konselor_id = ?
             UNION
             SELECT user_id FROM booking_konseling WHERE konselor_id = ?'
        );
        $stmt->bind_param('ii', $konselorId, $konselorId);
        $stmt->execute();

        return array_map('intval', array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'user_id'));
    }

    // --- Laporan 1: Riwayat Self Assessment ---------------------------------------

    /**
     * One row per BDI-II+PWB session (a session always creates exactly one of each,
     * back-to-back — see AssessmentSessionController::finish()), paired by chronological
     * order within each student. Risk label/recommendation is computed by the caller via
     * AssessmentScoringService::combinedLevel() — this method only assembles the pairs.
     */
    public function selfAssessmentSessions(array $filters): array
    {
        [$where, $params, $types] = $this->merge(
            '',
            [],
            '',
            $this->scopeWhere($filters, 's.user_id')
        );
        [$where, $params, $types] = $this->merge($where, $params, $types, $this->dateWhere($filters, 's.submitted_at'));
        [$where, $params, $types] = $this->merge($where, $params, $types, $this->searchWhere($filters, 'u.nama', 'u.npm'));

        $sql = "SELECT s.*, u.nama, u.npm
                FROM assessment_submissions s
                JOIN users u ON u.id = s.user_id
                WHERE u.role = 'mahasiswa'{$where}
                ORDER BY s.user_id ASC, s.submitted_at ASC";

        $stmt = $this->db->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();

        return $this->pairSessionsByUser($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    }

    private function pairSessionsByUser(array $rows): array
    {
        $byUser = [];
        foreach ($rows as $row) {
            $byUser[(int) $row['user_id']][$row['type']][] = $row;
        }

        $sessions = [];
        foreach ($byUser as $userId => $byType) {
            $bdi2 = $byType['bdi2'] ?? [];
            $pwb = $byType['pwb'] ?? [];
            $count = max(count($bdi2), count($pwb));

            for ($i = 0; $i < $count; $i++) {
                $anchor = $bdi2[$i] ?? $pwb[$i];
                $sessions[] = [
                    'user_id'  => $userId,
                    'nama'     => $anchor['nama'],
                    'npm'      => $anchor['npm'],
                    'tanggal'  => $bdi2[$i]['submitted_at'] ?? $pwb[$i]['submitted_at'] ?? null,
                    'bdi2'     => $bdi2[$i] ?? null,
                    'pwb'      => $pwb[$i] ?? null,
                ];
            }
        }

        usort($sessions, fn ($a, $b) => strcmp($a['tanggal'] ?? '', $b['tanggal'] ?? ''));

        return $sessions;
    }

    // --- Laporan 2: Diary -----------------------------------------------------------

    /**
     * filters: user_id (mahasiswa's own) | shared_konselor_id (konselor's shared inbox,
     * privacy-preserving per the existing DiaryRepository::findSharedWithKonselor rule)
     * | neither (admin, optionally narrowed by user_id for a single-student drill-down).
     */
    public function diaryRows(array $filters): array
    {
        $where = ' WHERE 1=1';
        $params = [];
        $types = '';

        if (!empty($filters['shared_konselor_id'])) {
            $where .= ' AND d.shared_konselor_id = ? AND d.is_private = 0';
            $params[] = (int) $filters['shared_konselor_id'];
            $types .= 'i';
        }

        [$where, $params, $types] = $this->merge($where, $params, $types, $this->scopeWhere($filters, 'd.user_id'));
        [$where, $params, $types] = $this->merge($where, $params, $types, $this->dateWhere($filters, 'd.entry_date'));
        [$where, $params, $types] = $this->merge($where, $params, $types, $this->searchWhere($filters, 'u.nama', 'u.npm'));

        $sql = "SELECT d.*, u.nama AS student_nama, u.npm AS student_npm
                FROM diary_entries d
                JOIN users u ON u.id = d.user_id
                {$where}
                ORDER BY d.entry_date DESC, d.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // --- Laporan 3: Aktivitas Self Help ----------------------------------------------

    public function selfHelpRows(array $filters): array
    {
        [$where, $params, $types] = $this->merge('', [], '', $this->scopeWhere($filters, 'a.user_id'));
        [$where, $params, $types] = $this->merge($where, $params, $types, $this->dateWhere($filters, 'a.planned_date'));
        [$where, $params, $types] = $this->merge($where, $params, $types, $this->searchWhere($filters, 'u.nama', 'u.npm'));

        $sql = "SELECT a.*, u.nama AS student_nama, u.npm AS student_npm
                FROM self_help_activities a
                JOIN users u ON u.id = a.user_id
                WHERE 1=1{$where}
                ORDER BY a.planned_date DESC, a.id DESC";

        $stmt = $this->db->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // --- Laporan 4: Konseling ---------------------------------------------------------

    /** filters: user_id (mahasiswa) | konselor_id (konselor, exact) | neither (admin). */
    public function konselingRows(array $filters): array
    {
        $where = ' WHERE 1=1';
        $params = [];
        $types = '';

        if (!empty($filters['user_id'])) {
            $where .= ' AND b.user_id = ?';
            $params[] = (int) $filters['user_id'];
            $types .= 'i';
        }
        if (!empty($filters['konselor_id'])) {
            $where .= ' AND b.konselor_id = ?';
            $params[] = (int) $filters['konselor_id'];
            $types .= 'i';
        }
        if (!empty($filters['status'])) {
            $where .= ' AND b.status = ?';
            $params[] = $filters['status'];
            $types .= 's';
        }
        if (!empty($filters['konselor_search'])) {
            $where .= ' AND ku.nama LIKE ?';
            $params[] = '%' . $filters['konselor_search'] . '%';
            $types .= 's';
        }

        [$where, $params, $types] = $this->merge($where, $params, $types, $this->dateWhere($filters, 'b.tanggal'));
        [$where, $params, $types] = $this->merge($where, $params, $types, $this->searchWhere($filters, 'su.nama', 'su.npm'));

        $sql = "SELECT b.*, su.nama AS student_nama, su.npm AS student_npm,
                       ku.nama AS konselor_nama, k.konselor_id,
                       sk.catatan_konselor, sk.rekomendasi, sk.tindak_lanjut, sk.selesai_pada
                FROM booking_konseling b
                JOIN users su ON su.id = b.user_id
                JOIN konselor k ON k.konselor_id = b.konselor_id
                JOIN users ku ON ku.id = k.user_id
                LEFT JOIN sesi_konseling sk ON sk.booking_id = b.booking_id
                {$where}
                ORDER BY b.tanggal DESC, b.booking_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // --- Laporan 5/8 shared: latest risk category per student -----------------------

    /**
     * Each in-scope mahasiswa's latest BDI-II + PWB category as of the given date range
     * (defaults to "ever" when the caller passes a wide range). Rows without both an
     * assessment of each type are dropped — the caller turns the pair into a
     * combinedLevel() risk bucket via AssessmentScoringService.
     */
    public function latestRiskCategories(array $filters): array
    {
        [$scopeWhere, $scopeParams, $scopeTypes] = $this->merge(
            '',
            [],
            '',
            $this->scopeWhere($filters, 'u.id')
        );
        [$scopeWhere, $scopeParams, $scopeTypes] = $this->merge(
            $scopeWhere,
            $scopeParams,
            $scopeTypes,
            $this->searchWhere($filters, 'u.nama', 'u.npm')
        );

        $sql = "SELECT u.id, u.nama, u.npm, u.fakultas,
                    (SELECT category FROM assessment_submissions s WHERE s.user_id = u.id AND s.type = 'bdi2'
                        AND DATE(s.submitted_at) BETWEEN ? AND ? ORDER BY s.submitted_at DESC LIMIT 1) AS bdi2_category,
                    (SELECT category FROM assessment_submissions s WHERE s.user_id = u.id AND s.type = 'pwb'
                        AND DATE(s.submitted_at) BETWEEN ? AND ? ORDER BY s.submitted_at DESC LIMIT 1) AS pwb_category
                FROM users u
                WHERE u.role = 'mahasiswa'{$scopeWhere}";

        $from = $filters['date_from'];
        $to = $filters['date_to'];
        $params = [$from, $to, $from, $to, ...$scopeParams];
        $types = 'ssss' . $scopeTypes;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return array_values(array_filter($rows, fn ($r) => $r['bdi2_category'] && $r['pwb_category']));
    }

    // --- Laporan 7: Evaluasi Keterlibatan Mahasiswa ----------------------------------

    public function engagementRows(array $filters): array
    {
        [$scopeWhere, $scopeParams, $scopeTypes] = $this->merge(
            '',
            [],
            '',
            $this->scopeWhere($filters, 'u.id')
        );
        [$scopeWhere, $scopeParams, $scopeTypes] = $this->merge(
            $scopeWhere,
            $scopeParams,
            $scopeTypes,
            $this->searchWhere($filters, 'u.nama', 'u.npm')
        );
        $from = $filters['date_from'];
        $to = $filters['date_to'];

        $sql = "SELECT u.id, u.nama, u.npm,
                    (SELECT COUNT(*) FROM assessment_submissions s WHERE s.user_id = u.id AND DATE(s.submitted_at) BETWEEN ? AND ?) AS assessment_count,
                    (SELECT COUNT(*) FROM diary_entries d WHERE d.user_id = u.id AND DATE(d.entry_date) BETWEEN ? AND ?) AS diary_count,
                    (SELECT COUNT(*) FROM self_help_activities a WHERE a.user_id = u.id AND DATE(a.planned_date) BETWEEN ? AND ?) AS selfhelp_count,
                    (SELECT COUNT(*) FROM booking_konseling b WHERE b.user_id = u.id AND DATE(b.tanggal) BETWEEN ? AND ?) AS booking_count,
                    (SELECT COUNT(*) FROM booking_konseling b WHERE b.user_id = u.id AND b.status = 'Completed' AND DATE(b.tanggal) BETWEEN ? AND ?) AS completed_konseling_count
                FROM users u
                WHERE u.role = 'mahasiswa'{$scopeWhere}";

        $params = array_merge([$from, $to, $from, $to, $from, $to, $from, $to, $from, $to], $scopeParams);
        $types = 'ssssssssss' . $scopeTypes;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return array_values(array_filter(
            $rows,
            fn ($r) => ($r['assessment_count'] + $r['diary_count'] + $r['selfhelp_count'] + $r['booking_count']) > 0
        ));
    }

    // --- Laporan 8: Evaluasi Keterlibatan Konselor (Konselor Activity) --------------

    /**
     * One row per Completed session in range, with the konselor and student's fakultas
     * attached. The caller aggregates (counts, unique students, top fakultas) per
     * konselor_id, and separately looks up each involved student's risk bucket via
     * latestRiskCategories() to find the konselor's most common "case" category.
     */
    public function konselorActivitySessions(array $filters): array
    {
        $where = " AND b.status = 'Completed' AND DATE(b.tanggal) BETWEEN ? AND ?";
        $params = [$filters['date_from'], $filters['date_to']];
        $types = 'ss';

        if (!empty($filters['konselor_id'])) {
            $where .= ' AND k.konselor_id = ?';
            $params[] = (int) $filters['konselor_id'];
            $types .= 'i';
        }

        $sql = "SELECT k.konselor_id, ku.nama AS konselor_nama, k.spesialisasi,
                       b.booking_id, b.user_id, su.fakultas
                FROM booking_konseling b
                JOIN konselor k ON k.konselor_id = b.konselor_id
                JOIN users ku ON ku.id = k.user_id
                JOIN users su ON su.id = b.user_id
                WHERE 1=1{$where}";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // --- Shared WHERE-fragment builders ----------------------------------------------

    /** @return array{0:string,1:array,2:string} leading-" AND ..." fragment, params, mysqli bind types */
    private function scopeWhere(array $filters, string $col): array
    {
        if (!empty($filters['user_id'])) {
            return [" AND {$col} = ?", [(int) $filters['user_id']], 'i'];
        }

        if (array_key_exists('student_ids', $filters) && $filters['student_ids'] !== null) {
            $ids = array_map('intval', $filters['student_ids']) ?: [0];
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            return [" AND {$col} IN ({$placeholders})", $ids, str_repeat('i', count($ids))];
        }

        return ['', [], ''];
    }

    /** @return array{0:string,1:array,2:string} leading-" AND ..." fragment, params, mysqli bind types */
    private function dateWhere(array $filters, string $col): array
    {
        return [" AND DATE({$col}) BETWEEN ? AND ?", [$filters['date_from'], $filters['date_to']], 'ss'];
    }

    /** @return array{0:string,1:array,2:string} leading-" AND ..." fragment, params, mysqli bind types */
    private function searchWhere(array $filters, string $nameCol, string $npmCol): array
    {
        if (empty($filters['search'])) {
            return ['', [], ''];
        }

        $like = '%' . $filters['search'] . '%';

        return [" AND ({$nameCol} LIKE ? OR {$npmCol} LIKE ?)", [$like, $like], 'ss'];
    }

    /** @return array{0:string,1:array,2:string} */
    private function merge(string $where, array $params, string $types, array $fragment): array
    {
        return [$where . $fragment[0], array_merge($params, $fragment[1]), $types . $fragment[2]];
    }
}
