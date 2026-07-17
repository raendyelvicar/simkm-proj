<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\AssessmentRepository;
use App\Repositories\AssessmentSessionRepository;
use App\Repositories\SettingsRepository;
use App\Services\AssessmentScoringService;
use App\Support\AssessmentMeta;

/**
 * The combined, timed BDI-II+PWB fill-in flow: one continuous session covering
 * both instruments, answered one question at a time via AJAX, auto-finalized
 * (partial answers included) once the admin-configured time limit is reached.
 *
 * Expiry is always re-checked server-side (via AssessmentSessionRepository's
 * MySQL-clock-based remaining_seconds) at the top of every action here, so the
 * client-side countdown is a display convenience only, never the source of truth.
 */
class AssessmentSessionController
{
    private AssessmentSessionRepository $sessions;
    private AssessmentRepository $assessments;
    private AssessmentScoringService $scoring;
    private SettingsRepository $settings;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->sessions = new AssessmentSessionRepository();
        $this->assessments = new AssessmentRepository();
        $this->scoring = new AssessmentScoringService();
        $this->settings = new SettingsRepository();
    }

    // GET /assessment/start
    public function start(Request $request): void
    {
        $this->requireMahasiswa();

        Response::view('assessment/session_start', [
            'title'            => 'Mulai Self-Assessment',
            'meta'             => AssessmentMeta::META,
            'timeLimitMinutes' => $this->timeLimitMinutes(),
        ]);
    }

    // POST /assessment/session
    public function create(Request $request): void
    {
        $this->requireMahasiswa();
        $userId = (int) $_SESSION['user_id'];

        $active = $this->sessions->findActiveForUser($userId);
        if ($active) {
            $active = $this->finalizeIfExpired($active);
        }
        if ($active && $active['status'] === 'in_progress') {
            Response::redirect('/assessment/session');
            return;
        }

        $this->sessions->create($userId, $this->timeLimitMinutes() * 60);
        Response::redirect('/assessment/session');
    }

    // GET /assessment/session
    public function show(Request $request): void
    {
        $this->requireMahasiswa();
        $userId = (int) $_SESSION['user_id'];

        $session = $this->sessions->findActiveForUser($userId);
        if (!$session) {
            Response::redirect('/assessment/start');
            return;
        }

        $session = $this->finalizeIfExpired($session);
        if ($session['status'] !== 'in_progress') {
            Response::redirect('/assessment/session/complete/' . $session['id']);
            return;
        }

        Response::view('assessment/session', [
            'title'            => 'Isi Self-Assessment',
            'sessionId'        => (int) $session['id'],
            'remainingSeconds' => max(0, (int) $session['remaining_seconds']),
            'questions'        => $this->sessions->combinedQuestions(),
            'answers'          => $this->sessions->answersForSession((int) $session['id']),
        ]);
    }

    // GET /assessment/session/state
    public function state(Request $request): void
    {
        $this->requireMahasiswa();

        $session = $this->sessions->findActiveForUser((int) $_SESSION['user_id']);
        if (!$session) {
            Response::json(['status' => 'none', 'expired' => true, 'redirect' => '/assessment/start']);
            return;
        }

        $session = $this->finalizeIfExpired($session);

        Response::json([
            'status'            => $session['status'],
            'expired'           => $session['status'] !== 'in_progress',
            'remaining_seconds' => max(0, (int) $session['remaining_seconds']),
            'answers'           => $this->sessions->answersForSession((int) $session['id']),
            'redirect'          => $session['status'] !== 'in_progress'
                ? '/assessment/session/complete/' . $session['id']
                : null,
        ]);
    }

    // POST /assessment/session/answer
    public function answer(Request $request): void
    {
        $this->requireMahasiswa();
        $session = $this->requireActiveSessionJson();

        $session = $this->finalizeIfExpired($session);
        if ($session['status'] !== 'in_progress') {
            Response::json([
                'expired'  => true,
                'redirect' => '/assessment/session/complete/' . $session['id'],
            ]);
            return;
        }

        $questionId = (int) $request->json('question_id', 0);
        $choiceId = (int) $request->json('choice_id', 0);

        $choice = $this->findChoice($questionId, $choiceId);
        if (!$choice) {
            Response::json(['error' => 'Jawaban tidak valid.'], 422);
            return;
        }

        $sessionId = (int) $session['id'];
        $this->sessions->upsertAnswer($sessionId, $questionId, $choiceId, $choice['score_value']);
        $this->sessions->touchLastSeen($sessionId, $questionId);

        Response::json([
            'ok'                => true,
            'remaining_seconds' => max(0, (int) $session['remaining_seconds']),
            'answered_count'    => count($this->sessions->answersForSession($sessionId)),
        ]);
    }

    // POST /assessment/session/finish
    public function finish(Request $request): void
    {
        $this->requireMahasiswa();
        $session = $this->requireActiveSessionJson();

        if ($session['status'] === 'in_progress') {
            $status = (int) $session['remaining_seconds'] <= 0 ? 'timed_out' : 'completed';
            $session = $this->doFinalize($session, $status);
        }

        Response::json([
            'ok'       => true,
            'status'   => $session['status'],
            'redirect' => '/assessment/session/complete/' . $session['id'],
        ]);
    }

    // GET /assessment/session/complete/{id}
    public function complete(Request $request, string $id): void
    {
        $this->requireMahasiswa();
        $session = $this->sessions->find((int) $id);

        if (!$session || (int) $session['user_id'] !== (int) $_SESSION['user_id']) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Sesi Tidak Ditemukan']);
            return;
        }

        if ($session['status'] === 'in_progress') {
            $session = $this->finalizeIfExpired($session);
        }
        if ($session['status'] === 'in_progress') {
            Response::redirect('/assessment/session');
            return;
        }

        Response::view('assessment/session_complete', [
            'title'   => 'Assessment Selesai',
            'session' => $session,
            'meta'    => AssessmentMeta::META,
        ]);
    }

    /** Loads the caller's active session or halts the request with a JSON error. */
    private function requireActiveSessionJson(): array
    {
        $session = $this->sessions->findActiveForUser((int) $_SESSION['user_id']);
        if (!$session) {
            Response::json(['error' => 'Tidak ada sesi assessment yang aktif.', 'redirect' => '/assessment/start'], 404);
        }

        return $session;
    }

    private function findChoice(int $questionId, int $choiceId): ?array
    {
        foreach ($this->sessions->combinedQuestions() as $question) {
            if ($question['id'] !== $questionId) {
                continue;
            }
            foreach ($question['choices'] as $choice) {
                if ($choice['id'] === $choiceId) {
                    return $choice;
                }
            }
        }

        return null;
    }

    private function finalizeIfExpired(array $session): array
    {
        if ($session['status'] === 'in_progress' && (int) $session['remaining_seconds'] <= 0) {
            return $this->doFinalize($session, 'timed_out');
        }

        return $session;
    }

    /** Scores + saves both instruments from the session's draft answers, then marks the session finalized. Idempotent. */
    private function doFinalize(array $session, string $status): array
    {
        if ($session['status'] !== 'in_progress') {
            return $session;
        }

        $sessionId = (int) $session['id'];
        $userId = (int) $session['user_id'];
        $isTimedOut = $status === 'timed_out';

        $bdi2Answers = $this->sessions->answersForSessionByType($sessionId, 'bdi2');
        $bdi2Scored = $this->scoring->scoreBdi2($bdi2Answers);
        $bdi2Id = $this->assessments->createSubmission(
            $userId,
            'bdi2',
            $bdi2Scored['total_score'],
            $bdi2Scored['max_score'],
            $bdi2Scored['category'],
            null,
            [],
            $isTimedOut
        );
        $this->assessments->saveAnswers($bdi2Id, $bdi2Answers);

        $pwbAnswers = $this->sessions->answersForSessionByType($sessionId, 'pwb');
        $pwbScored = $this->scoring->scorePwb($pwbAnswers);
        $pwbId = $this->assessments->createSubmission(
            $userId,
            'pwb',
            $pwbScored['total_score'],
            $pwbScored['max_score'],
            $pwbScored['category'],
            $pwbScored['percentage'],
            $pwbScored['dimension_scores'],
            $isTimedOut
        );
        $this->assessments->saveAnswers($pwbId, $pwbScored['scored_answers']);

        $this->sessions->finalize($sessionId, $status, $bdi2Id, $pwbId);

        $session['status'] = $status;
        $session['bdi2_submission_id'] = $bdi2Id;
        $session['pwb_submission_id'] = $pwbId;

        return $session;
    }

    private function timeLimitMinutes(): int
    {
        $minutes = (int) $this->settings->get('assessment_time_limit_minutes', '45');

        return $minutes > 0 ? $minutes : 45;
    }

    private function requireMahasiswa(): void
    {
        if (($_SESSION['role'] ?? '') !== 'mahasiswa') {
            http_response_code(403);
            exit('Forbidden: hanya mahasiswa yang dapat mengisi assessment.');
        }
    }
}
