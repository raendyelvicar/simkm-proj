<?php

namespace App\Repositories;

use App\Core\Database;
use mysqli;

/**
 * Backs the combined, timed BDI-II+PWB session flow (AssessmentSessionController).
 * Expiry is always computed by MySQL (TIMESTAMPDIFF against NOW()) so the PHP
 * process clock is never consulted — avoids clock-skew bugs between requests/servers.
 */
class AssessmentSessionRepository
{
    private mysqli $db;
    private AssessmentRepository $assessments;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->assessments = new AssessmentRepository();
    }

    public function findActiveForUser(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *, TIMESTAMPDIFF(SECOND, NOW(), expires_at) AS remaining_seconds
             FROM assessment_sessions
             WHERE user_id = ? AND status = 'in_progress'
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ?: null;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT *, TIMESTAMPDIFF(SECOND, NOW(), expires_at) AS remaining_seconds
             FROM assessment_sessions WHERE id = ? LIMIT 1'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ?: null;
    }

    public function create(int $userId, int $timeLimitSeconds): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO assessment_sessions (user_id, status, time_limit_seconds, started_at, expires_at)
             VALUES (?, 'in_progress', ?, NOW(), NOW() + INTERVAL ? SECOND)"
        );
        $stmt->bind_param('iii', $userId, $timeLimitSeconds, $timeLimitSeconds);
        $stmt->execute();

        return (int) $this->db->insert_id;
    }

    /**
     * All 39 questions (BDI-II 1-21, then PWB 1-18) in the fixed order a session
     * presents them, each tagged with a session-wide global_index (0-based).
     */
    public function combinedQuestions(): array
    {
        $questions = [];
        $index = 0;

        foreach (['bdi2', 'pwb'] as $type) {
            foreach ($this->assessments->questionsForType($type) as $question) {
                $data = $question->toArray();
                $data['global_index'] = $index++;
                $questions[] = $data;
            }
        }

        return $questions;
    }

    public function upsertAnswer(int $sessionId, int $questionId, int $choiceId, int $scoreValue): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO assessment_session_answers (session_id, question_id, choice_id, score_value)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE choice_id = VALUES(choice_id), score_value = VALUES(score_value)'
        );
        $stmt->bind_param('iiii', $sessionId, $questionId, $choiceId, $scoreValue);
        $stmt->execute();
    }

    /** @return array<int, int> question_id => choice_id, for resume/state payloads */
    public function answersForSession(int $sessionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT question_id, choice_id FROM assessment_session_answers WHERE session_id = ?'
        );
        $stmt->bind_param('i', $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();

        $answers = [];
        while ($row = $result->fetch_assoc()) {
            $answers[(int) $row['question_id']] = (int) $row['choice_id'];
        }

        return $answers;
    }

    /**
     * Session's draft answers for one instrument, shaped exactly like the array
     * AssessmentScoringService::scoreBdi2()/scorePwb() expects.
     */
    public function answersForSessionByType(int $sessionId, string $type): array
    {
        $stmt = $this->db->prepare(
            'SELECT sa.question_id, sa.choice_id, sa.score_value, q.dimension, q.is_reverse_scored
             FROM assessment_session_answers sa
             JOIN assessment_questions q ON q.id = sa.question_id
             WHERE sa.session_id = ? AND q.type = ?'
        );
        $stmt->bind_param('is', $sessionId, $type);
        $stmt->execute();
        $result = $stmt->get_result();

        $answers = [];
        while ($row = $result->fetch_assoc()) {
            $answers[] = [
                'question_id'       => (int) $row['question_id'],
                'choice_id'         => (int) $row['choice_id'],
                'score_value'       => (int) $row['score_value'],
                'dimension'         => $row['dimension'],
                'is_reverse_scored' => (bool) $row['is_reverse_scored'],
            ];
        }

        return $answers;
    }

    public function touchLastSeen(int $sessionId, int $questionId): void
    {
        $stmt = $this->db->prepare('UPDATE assessment_sessions SET last_seen_question_id = ? WHERE id = ?');
        $stmt->bind_param('ii', $questionId, $sessionId);
        $stmt->execute();
    }

    /** Idempotent: only takes effect while the session is still in_progress. */
    public function finalize(int $sessionId, string $status, int $bdi2SubmissionId, int $pwbSubmissionId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE assessment_sessions
             SET status = ?, bdi2_submission_id = ?, pwb_submission_id = ?, finalized_at = NOW()
             WHERE id = ? AND status = 'in_progress'"
        );
        $stmt->bind_param('siii', $status, $bdi2SubmissionId, $pwbSubmissionId, $sessionId);
        $stmt->execute();
    }
}
