<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentChoice;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentSubmission;
use App\Services\AssessmentScoringService;
use mysqli;

class AssessmentRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * All questions for a type ('bdi2'|'pwb'), ordered, each with its choices loaded.
     *
     * @return AssessmentQuestion[]
     */
    public function questionsForType(string $type): array
    {
        $stmt = $this->db->prepare('SELECT * FROM assessment_questions WHERE type = ? ORDER BY order_no ASC');
        $stmt->bind_param('s', $type);
        $stmt->execute();
        $result = $stmt->get_result();

        $questions = [];
        $questionIds = [];
        while ($row = $result->fetch_assoc()) {
            $question = new AssessmentQuestion($row);
            $questions[$question->id] = $question;
            $questionIds[] = $question->id;
        }

        if (!$questionIds) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
        $types = str_repeat('i', count($questionIds));
        $stmt = $this->db->prepare(
            "SELECT * FROM assessment_choices WHERE question_id IN ({$placeholders}) ORDER BY question_id ASC, order_no ASC"
        );
        $stmt->bind_param($types, ...$questionIds);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $choice = new AssessmentChoice($row);
            if (isset($questions[$choice->questionId])) {
                $questions[$choice->questionId]->choices[] = $choice;
            }
        }

        return array_values($questions);
    }

    public function createSubmission(
        int $userId,
        string $type,
        int $totalScore,
        int $maxScore,
        string $category,
        ?float $percentage,
        array $dimensionScores,
        bool $isTimedOut = false
    ): int {
        $dimensionJson = $dimensionScores ? json_encode($dimensionScores) : null;
        $timedOut = $isTimedOut ? 1 : 0;

        $stmt = $this->db->prepare(
            'INSERT INTO assessment_submissions
                (user_id, type, total_score, max_score, category, category_percentage, dimension_scores, is_timed_out, submitted_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->bind_param('isiisdsi', $userId, $type, $totalScore, $maxScore, $category, $percentage, $dimensionJson, $timedOut);
        $stmt->execute();

        return (int) $this->db->insert_id;
    }

    /** @param array<int, array{question_id:int, choice_id:int, score_value:int}> $answers */
    public function saveAnswers(int $submissionId, array $answers): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO assessment_answers (submission_id, question_id, choice_id, score_value) VALUES (?, ?, ?, ?)'
        );

        foreach ($answers as $answer) {
            $questionId = (int) $answer['question_id'];
            $choiceId = (int) $answer['choice_id'];
            $scoreValue = (int) $answer['score_value'];
            $stmt->bind_param('iiii', $submissionId, $questionId, $choiceId, $scoreValue);
            $stmt->execute();
        }
    }

    public function latestForUser(int $userId, string $type): ?AssessmentSubmission
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM assessment_submissions WHERE user_id = ? AND type = ? ORDER BY submitted_at DESC LIMIT 1'
        );
        $stmt->bind_param('is', $userId, $type);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new AssessmentSubmission($row) : null;
    }

    public function findSubmission(int $id): ?AssessmentSubmission
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, u.nama FROM assessment_submissions s
             JOIN users u ON u.id = s.user_id
             WHERE s.id = ? LIMIT 1'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? new AssessmentSubmission($row) : null;
    }

    /**
     * Answers for a submission, each with its question text and chosen label attached
     * (for the result page and PDF export).
     */
    public function answersForSubmission(int $submissionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.score_value, q.order_no, q.question_text, q.dimension, c.label
             FROM assessment_answers a
             JOIN assessment_questions q ON q.id = a.question_id
             JOIN assessment_choices c ON c.id = a.choice_id
             WHERE a.submission_id = ?
             ORDER BY q.order_no ASC'
        );
        $stmt->bind_param('i', $submissionId);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** @return array<string, int> category label => count of latest submissions per student */
    public function countsByCategory(string $type): array
    {
        $stmt = $this->db->prepare(
            'SELECT category, COUNT(*) AS total FROM assessment_submissions WHERE type = ? GROUP BY category'
        );
        $stmt->bind_param('s', $type);
        $stmt->execute();
        $result = $stmt->get_result();

        $counts = [];
        while ($row = $result->fetch_assoc()) {
            $counts[$row['category']] = (int) $row['total'];
        }

        return $counts;
    }

    /** Most recent submissions in the given severity categories, across all students — admin/konselor dashboard. */
    public function recentByCategories(array $categories, int $limit = 5): array
    {
        if (empty($categories)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($categories), '?'));
        $types = str_repeat('s', count($categories)) . 'i';
        $params = [...$categories, $limit];

        $sql = "
        SELECT
            s.*,
            u.nama
        FROM assessment_submissions s
        INNER JOIN (
            SELECT
                user_id,
                MAX(submitted_at) AS latest_submission
            FROM assessment_submissions
            WHERE category IN ($placeholders)
            GROUP BY user_id
        ) latest
            ON latest.user_id = s.user_id
           AND latest.latest_submission = s.submitted_at
        INNER JOIN users u
            ON u.id = s.user_id
        ORDER BY s.submitted_at DESC
        LIMIT ?
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $this->hydrateAll($stmt->get_result());
    }

    private const STAFF_HISTORY_SORTABLE = [
        'nama'               => 'nama',
        'fakultas'           => 'fakultas',
        'last_submitted_at'  => 'last_submitted_at',
        'total_submissions'  => 'total_submissions',
    ];

    /**
     * Mahasiswa grouped by user with their assessment summary — backs the staff
     * "Riwayat Assessment" list (one row per student instead of per submission).
     * @return array{items: array, total: int}
     */
    public function studentAssessmentSummaries(array $filters, int $page, int $perPage, string $sort = 'last_submitted_at', string $dir = 'desc'): array
    {
        [$where, $params, $types] = $this->buildStudentSummaryWhere($filters);

        $base = "SELECT * FROM (
            SELECT u.id, u.nama, u.npm, u.fakultas, u.jurusan,
                (SELECT COUNT(*) FROM assessment_submissions s WHERE s.user_id = u.id) AS total_submissions,
                (SELECT MAX(submitted_at) FROM assessment_submissions s WHERE s.user_id = u.id) AS last_submitted_at,
                (SELECT category FROM assessment_submissions s WHERE s.user_id = u.id AND s.type = 'bdi2' ORDER BY submitted_at DESC LIMIT 1) AS latest_bdi2_category,
                (SELECT category FROM assessment_submissions s WHERE s.user_id = u.id AND s.type = 'pwb' ORDER BY submitted_at DESC LIMIT 1) AS latest_pwb_category
            FROM users u
            WHERE u.role = 'mahasiswa'
        ) t WHERE total_submissions > 0" . $where;

        $countStmt = $this->db->prepare("SELECT COUNT(*) AS c FROM ({$base}) x");
        if ($params) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::STAFF_HISTORY_SORTABLE[$sort] ?? 'last_submitted_at';
        $orderDir = $dir === 'asc' ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $perPage;
        $dataStmt = $this->db->prepare($base . " ORDER BY {$orderCol} {$orderDir} LIMIT ? OFFSET ?");
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        return [
            'items' => $dataStmt->get_result()->fetch_all(MYSQLI_ASSOC),
            'total' => $total,
        ];
    }

    /** @return array{0: string, 1: array, 2: string} [WHERE fragment (leading " AND ..."), params, mysqli bind_param type string] */
    private function buildStudentSummaryWhere(array $filters): array
    {
        $where = '';
        $params = [];
        $types = '';

        if (!empty($filters['search'])) {
            $where .= ' AND (nama LIKE ? OR npm LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params[] = $like;
            $params[] = $like;
            $types .= 'ss';
        }
        if (!empty($filters['fakultas'])) {
            $where .= ' AND fakultas = ?';
            $params[] = $filters['fakultas'];
            $types .= 's';
        }
        if (!empty($filters['jurusan'])) {
            $where .= ' AND jurusan = ?';
            $params[] = $filters['jurusan'];
            $types .= 's';
        }
        if (!empty($filters['bdi2_category'])) {
            $where .= ' AND latest_bdi2_category = ?';
            $params[] = $filters['bdi2_category'];
            $types .= 's';
        }
        if (!empty($filters['pwb_category'])) {
            $where .= ' AND latest_pwb_category = ?';
            $params[] = $filters['pwb_category'];
            $types .= 's';
        }

        return [$where, $params, $types];
    }

    private const SUBMISSIONS_SORTABLE = [
        'submitted_at' => 'submitted_at',
        'type'         => 'type',
        'category'     => 'category',
        'total_score'  => 'total_score',
    ];

    /**
     * One student's submissions, filterable by type/category, sortable, paginated —
     * backs both the staff-only per-student detail page and the mahasiswa's own
     * "Riwayat Assessment" page (userId scoped to $_SESSION['user_id'] in that case).
     * @return array{items: AssessmentSubmission[], total: int}
     */
    public function submissionsForUserFiltered(int $userId, array $filters, int $page, int $perPage, string $sort = 'submitted_at', string $dir = 'desc'): array
    {
        $where = ' WHERE user_id = ?';
        $params = [$userId];
        $types = 'i';

        if (!empty($filters['type'])) {
            $where .= ' AND type = ?';
            $params[] = $filters['type'];
            $types .= 's';
        }
        if (!empty($filters['category'])) {
            $where .= ' AND category = ?';
            $params[] = $filters['category'];
            $types .= 's';
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) AS c FROM assessment_submissions{$where}");
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);

        $orderCol = self::SUBMISSIONS_SORTABLE[$sort] ?? 'submitted_at';
        $orderDir = $dir === 'asc' ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $perPage;
        $dataStmt = $this->db->prepare("SELECT * FROM assessment_submissions{$where} ORDER BY {$orderCol} {$orderDir} LIMIT ? OFFSET ?");
        $dataParams = [...$params, $perPage, $offset];
        $dataStmt->bind_param($types . 'ii', ...$dataParams);
        $dataStmt->execute();

        return [
            'items' => $this->hydrateAll($dataStmt->get_result()),
            'total' => $total,
        ];
    }

    /** @return array<string, float> dimension key => campus-wide average score (out of PWB_DIMENSION_MAX_SCORE) */
    public function pwbDimensionAverages(): array
    {
        $dimensions = array_keys(AssessmentScoringService::PWB_DIMENSIONS);
        $selects = array_map(
            fn($dim) => "AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(dimension_scores, '$.{$dim}.score')) AS DECIMAL(6,2))) AS `{$dim}`",
            $dimensions
        );

        $result = $this->db->query('SELECT ' . implode(', ', $selects) . " FROM assessment_submissions WHERE type = 'pwb'");
        $row = $result->fetch_assoc() ?: [];

        $averages = [];
        foreach ($dimensions as $dim) {
            $averages[$dim] = round((float) ($row[$dim] ?? 0), 2);
        }

        return $averages;
    }

    /** @return array<int, array{order_no:int, question_text:string, avg_score:float}> campus-wide average per BDI-II item */
    public function bdi2ItemAverages(): array
    {
        $stmt = $this->db->prepare(
            "SELECT q.order_no, q.question_text, AVG(a.score_value) AS avg_score
             FROM assessment_answers a
             JOIN assessment_questions q ON q.id = a.question_id
             WHERE q.type = 'bdi2'
             GROUP BY q.order_no, q.question_text
             ORDER BY q.order_no ASC"
        );
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'order_no'      => (int) $row['order_no'],
                'question_text' => $row['question_text'],
                'avg_score'     => round((float) $row['avg_score'], 2),
            ];
        }

        return $items;
    }

    /** Students who answered BDI-II item 9 (suicidal ideation) with a nonzero score, most recent first. */
    public function flaggedForSuicidalIdeation(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT s.id, s.total_score, s.max_score, s.category, s.submitted_at, u.nama, a.score_value AS item_score
             FROM assessment_answers a
             JOIN assessment_questions q ON q.id = a.question_id
             JOIN assessment_submissions s ON s.id = a.submission_id
             JOIN users u ON u.id = s.user_id
             WHERE q.type = 'bdi2' AND q.order_no = 9 AND a.score_value > 0
             ORDER BY s.submitted_at DESC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** @return array{participants:int, total_submissions:int, timeout_rate:float} */
    public function participationStats(): array
    {
        $participants = (int) ($this->db->query(
            'SELECT COUNT(DISTINCT user_id) AS c FROM assessment_submissions'
        )->fetch_assoc()['c'] ?? 0);

        $row = $this->db->query('SELECT AVG(is_timed_out) AS rate, COUNT(*) AS total FROM assessment_submissions')->fetch_assoc();

        return [
            'participants'      => $participants,
            'total_submissions' => (int) ($row['total'] ?? 0),
            'timeout_rate'      => round((float) ($row['rate'] ?? 0) * 100, 1),
        ];
    }

    /** @return AssessmentSubmission[] */
    private function hydrateAll(\mysqli_result $result): array
    {
        $submissions = [];
        while ($row = $result->fetch_assoc()) {
            $submissions[] = new AssessmentSubmission($row);
        }

        return $submissions;
    }
}
