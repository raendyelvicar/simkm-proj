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
 * concrete 'Y-m-d' strings (ReportController fills in sane defaults before calling in),
 * so every date predicate here is a plain, always-present "BETWEEN ? AND ?" — no
 * conditional param counting needed. Role scoping is expressed via either
 * $filters['user_id'] (exact single student) or $filters['student_ids'] (counselor's
 * bimbingan list; omit both for admin's unrestricted view).
 */
class ReportRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    // The "bimbingan" scope decision: union of students with an active/past monitoring
    // window and students with any booking history, for this counselor.
    public function counselorStudentIds(int $counselorId): array
    {
        $stmt = $this->db->prepare(
            'SELECT user_id FROM monitoring_periods WHERE counselor_id = ?
             UNION
             SELECT user_id FROM counseling_bookings WHERE counselor_id = ?'
        );
        $stmt->bind_param('ii', $counselorId, $counselorId);
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
        [$where, $params, $types] = $this->merge($where, $params, $types, $this->searchWhere($filters, 'u.name', 'u.student_number'));

        $sql = "SELECT s.*, u.name, u.student_number
                FROM assessment_submissions s
                JOIN users u ON u.id = s.user_id
                WHERE u.role = 'student'{$where}
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
                    'name'     => $anchor['name'],
                    'student_number'      => $anchor['student_number'],
                    'date'  => $bdi2[$i]['submitted_at'] ?? $pwb[$i]['submitted_at'] ?? null,
                    'bdi2'     => $bdi2[$i] ?? null,
                    'pwb'      => $pwb[$i] ?? null,
                ];
            }
        }

        usort($sessions, fn ($a, $b) => strcmp($a['date'] ?? '', $b['date'] ?? ''));

        return $sessions;
    }

    // --- Laporan 2: Diary -----------------------------------------------------------

    /**
     * filters: user_id (student's own) | shared_counselor_id (counselor's shared inbox,
     * privacy-preserving per the existing DiaryRepository::findSharedWithCounselor rule)
     * | neither (admin, optionally narrowed by user_id for a single-student drill-down).
     */
    public function diaryRows(array $filters): array
    {
        $where = ' WHERE 1=1';
        $params = [];
        $types = '';

        if (!empty($filters['shared_counselor_id'])) {
            $where .= ' AND d.shared_counselor_id = ? AND d.is_private = 0';
            $params[] = (int) $filters['shared_counselor_id'];
            $types .= 'i';
        }

        [$where, $params, $types] = $this->merge($where, $params, $types, $this->scopeWhere($filters, 'd.user_id'));
        [$where, $params, $types] = $this->merge($where, $params, $types, $this->dateWhere($filters, 'd.entry_date'));
        [$where, $params, $types] = $this->merge($where, $params, $types, $this->searchWhere($filters, 'u.name', 'u.student_number'));

        $sql = "SELECT d.*, u.name AS student_name, u.student_number AS student_number
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
        [$where, $params, $types] = $this->merge($where, $params, $types, $this->searchWhere($filters, 'u.name', 'u.student_number'));

        $sql = "SELECT a.*, u.name AS student_name, u.student_number AS student_number
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

    /** filters: user_id (student) | counselor_id (counselor, exact) | neither (admin). */
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
        if (!empty($filters['counselor_id'])) {
            $where .= ' AND b.counselor_id = ?';
            $params[] = (int) $filters['counselor_id'];
            $types .= 'i';
        }
        if (!empty($filters['status'])) {
            $where .= ' AND b.status = ?';
            $params[] = $filters['status'];
            $types .= 's';
        }
        if (!empty($filters['konselor_search'])) {
            $where .= ' AND ku.name LIKE ?';
            $params[] = '%' . $filters['konselor_search'] . '%';
            $types .= 's';
        }

        [$where, $params, $types] = $this->merge($where, $params, $types, $this->dateWhere($filters, 'b.date'));
        [$where, $params, $types] = $this->merge($where, $params, $types, $this->searchWhere($filters, 'su.name', 'su.student_number'));

        $sql = "SELECT b.*, su.name AS student_name, su.student_number AS student_number,
                       ku.name AS counselor_name, k.counselor_id,
                       sk.counselor_notes, sk.recommendation, sk.follow_up, sk.completed_at
                FROM counseling_bookings b
                JOIN users su ON su.id = b.user_id
                JOIN counselors k ON k.counselor_id = b.counselor_id
                JOIN users ku ON ku.id = k.user_id
                LEFT JOIN counseling_sessions sk ON sk.booking_id = b.booking_id
                {$where}
                ORDER BY b.date DESC, b.booking_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // --- Laporan 5/8 shared: latest risk category per student -----------------------

    /**
     * Each in-scope student's latest BDI-II + PWB category as of the given date range
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
            $this->searchWhere($filters, 'u.name', 'u.student_number')
        );

        $sql = "SELECT u.id, u.name, u.student_number, u.faculty,
                    (SELECT category FROM assessment_submissions s WHERE s.user_id = u.id AND s.type = 'bdi2'
                        AND DATE(s.submitted_at) BETWEEN ? AND ? ORDER BY s.submitted_at DESC LIMIT 1) AS bdi2_category,
                    (SELECT category FROM assessment_submissions s WHERE s.user_id = u.id AND s.type = 'pwb'
                        AND DATE(s.submitted_at) BETWEEN ? AND ? ORDER BY s.submitted_at DESC LIMIT 1) AS pwb_category
                FROM users u
                WHERE u.role = 'student'{$scopeWhere}";

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

    // --- Laporan 7: Evaluasi Keterlibatan Student ----------------------------------

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
            $this->searchWhere($filters, 'u.name', 'u.student_number')
        );
        $from = $filters['date_from'];
        $to = $filters['date_to'];

        $sql = "SELECT u.id, u.name, u.student_number,
                    (SELECT COUNT(*) FROM assessment_submissions s WHERE s.user_id = u.id AND DATE(s.submitted_at) BETWEEN ? AND ?) AS assessment_count,
                    (SELECT COUNT(*) FROM diary_entries d WHERE d.user_id = u.id AND DATE(d.entry_date) BETWEEN ? AND ?) AS diary_count,
                    (SELECT COUNT(*) FROM self_help_activities a WHERE a.user_id = u.id AND DATE(a.planned_date) BETWEEN ? AND ?) AS selfhelp_count,
                    (SELECT COUNT(*) FROM counseling_bookings b WHERE b.user_id = u.id AND DATE(b.date) BETWEEN ? AND ?) AS booking_count,
                    (SELECT COUNT(*) FROM counseling_bookings b WHERE b.user_id = u.id AND b.status = 'Completed' AND DATE(b.date) BETWEEN ? AND ?) AS completed_counseling_count
                FROM users u
                WHERE u.role = 'student'{$scopeWhere}";

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

    // --- Laporan 8: Evaluasi Keterlibatan Counselor (Counselor Activity) --------------

    /**
     * One row per Completed session in range, with the counselor and student's faculty
     * attached. The caller aggregates (counts, unique students, top faculty) per
     * counselor_id, and separately looks up each involved student's risk bucket via
     * latestRiskCategories() to find the counselor's most common "case" category.
     */
    public function counselorActivitySessions(array $filters): array
    {
        $where = " AND b.status = 'Completed' AND DATE(b.date) BETWEEN ? AND ?";
        $params = [$filters['date_from'], $filters['date_to']];
        $types = 'ss';

        if (!empty($filters['counselor_id'])) {
            $where .= ' AND k.counselor_id = ?';
            $params[] = (int) $filters['counselor_id'];
            $types .= 'i';
        }

        $sql = "SELECT k.counselor_id, ku.name AS counselor_name, k.specialization,
                       b.booking_id, b.user_id, su.faculty
                FROM counseling_bookings b
                JOIN counselors k ON k.counselor_id = b.counselor_id
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
    private function searchWhere(array $filters, string $nameCol, string $student_numberCol): array
    {
        if (empty($filters['search'])) {
            return ['', [], ''];
        }

        $like = '%' . $filters['search'] . '%';

        return [" AND ({$nameCol} LIKE ? OR {$student_numberCol} LIKE ?)", [$like, $like], 'ss'];
    }

    /** @return array{0:string,1:array,2:string} */
    private function merge(string $where, array $params, string $types, array $fragment): array
    {
        return [$where . $fragment[0], array_merge($params, $fragment[1]), $types . $fragment[2]];
    }
}
