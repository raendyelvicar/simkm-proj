<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\ActivityPlanRepository;
use App\Repositories\AssessmentRepository;
use App\Repositories\DiaryRepository;
use App\Services\AssessmentScoringService;

// Self Help hub for student: the concrete pages behind the "Self Help" /
// "Self Help + PFA" recommendations produced by AssessmentScoringService::combinedLevel().
class SelfHelpController
{
    private const ACTIVITIES_PER_PAGE = 10;

    private ActivityPlanRepository $activities;
    private DiaryRepository $diaries;
    private AssessmentRepository $assessments;
    private AssessmentScoringService $scoring;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->activities = new ActivityPlanRepository();
        $this->diaries = new DiaryRepository();
        $this->assessments = new AssessmentRepository();
        $this->scoring = new AssessmentScoringService();
    }

    // GET /self-help
    public function index(Request $request): void
    {
        Response::view('selfhelp/index', [
            'title' => 'Self Help',
            'combined' => $this->latestCombinedLevel(),
        ]);
    }

    // GET /self-help/breathing
    public function breathing(Request $request): void
    {
        Response::view('selfhelp/breathing', [
            'title' => 'Latihan Pernapasan',
        ]);
    }

    // GET /self-help/gratitude
    public function gratitude(Request $request): void
    {
        $userId = (int) $_SESSION['user_id'];

        Response::view('selfhelp/gratitude', [
            'title' => 'Gratitude & Self Reflection',
            'entries' => array_map(fn ($e) => $e->toArray(), $this->diaries->findWithReflectionByUserId($userId)),
        ]);
    }

    // GET /self-help/pfa
    public function pfa(Request $request): void
    {
        Response::view('selfhelp/pfa', [
            'title' => 'Psychological First Aid',
        ]);
    }

    // GET /self-help/activities
    public function activities(Request $request): void
    {
        $userId = (int) $_SESSION['user_id'];

        $filters = ['status' => $request->get('status') ?: null];
        $sort = (string) $request->get('sort', 'planned_date');
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $page = max(1, (int) $request->get('page', 1));

        $result = $this->activities->paginatedByUserId($userId, $filters, $sort, $dir, $page, self::ACTIVITIES_PER_PAGE);
        $totalPages = (int) max(1, ceil($result['total'] / self::ACTIVITIES_PER_PAGE));

        Response::view('selfhelp/activities/index', [
            'title'      => 'Rencana Aktivitas Positif',
            'items'      => array_map(fn ($a) => $a->toArray(), $result['items']),
            'total'      => $result['total'],
            'page'       => $page,
            'totalPages' => $totalPages,
            'sort'       => $sort,
            'dir'        => $dir,
            'filters'    => $filters,
        ]);
    }

    // GET /self-help/activities/create
    public function createActivity(Request $request): void
    {
        Response::view('selfhelp/activities/create', [
            'title' => 'Tambah Aktivitas',
        ]);
    }

    // POST /self-help/activities
    public function storeActivity(Request $request): void
    {
        [$fields, $errors] = $this->validateActivity($request);

        if ($errors) {
            Response::view('selfhelp/activities/create', [
                'title' => 'Tambah Aktivitas',
                'errors' => $errors,
                'old' => $fields,
            ]);
            return;
        }

        $this->activities->create(
            (int) $_SESSION['user_id'],
            $fields['title'],
            $fields['description'],
            $fields['planned_date'],
            $fields['mood_before']
        );

        $_SESSION['success'] = 'Aktivitas berhasil ditambahkan.';
        Response::redirect('/self-help/activities');
    }

    // Returns [fields, errors].
    private function validateActivity(Request $request): array
    {
        $title = trim($request->post('title', ''));
        $description = trim($request->post('description', '')) ?: null;
        $plannedDate = trim($request->post('planned_date', '')) ?: date('Y-m-d');
        $moodBeforeRaw = $request->post('mood_before', '');
        $moodBefore = $moodBeforeRaw !== '' ? max(1, min(5, (int) $moodBeforeRaw)) : null;

        $fields = [
            'title' => $title,
            'description' => $description,
            'planned_date' => $plannedDate,
            'mood_before' => $moodBefore,
        ];

        $errors = [];
        if ($title === '') {
            $errors[] = 'Nama aktivitas wajib diisi.';
        }

        return [$fields, $errors];
    }

    // POST /self-help/activities/{id}/complete
    public function completeActivity(Request $request, string $id): void
    {
        $activity = $this->findOwnedActivity((int) $id);
        if (!$activity) {
            Response::redirect('/self-help/activities');
            return;
        }

        $moodAfter = max(1, min(5, (int) $request->post('mood_after', 3)));
        $this->activities->complete($activity->id, $moodAfter);

        $_SESSION['success'] = 'Aktivitas ditandai selesai.';
        Response::redirect('/self-help/activities');
    }

    // POST /self-help/activities/{id}/skip
    public function skipActivity(Request $request, string $id): void
    {
        $activity = $this->findOwnedActivity((int) $id);
        if ($activity) {
            $this->activities->skip($activity->id);
        }

        Response::redirect('/self-help/activities');
    }

    // POST /self-help/activities/{id}/delete
    public function destroyActivity(Request $request, string $id): void
    {
        $activity = $this->findOwnedActivity((int) $id);
        if ($activity) {
            $this->activities->delete($activity->id);
            $_SESSION['success'] = 'Aktivitas berhasil dihapus.';
        }

        Response::redirect('/self-help/activities');
    }

    private function findOwnedActivity(int $id)
    {
        $activity = $this->activities->find($id);

        if (!$activity || $activity->userId !== (int) $_SESSION['user_id']) {
            return null;
        }

        return $activity;
    }

    // Same PWB + BDI-II combination AssessmentController::index() uses, so the hub
    // can highlight the feature that matches the user's current recommended level.
    private function latestCombinedLevel(): ?array
    {
        $userId = (int) $_SESSION['user_id'];
        $latestBdi2 = $this->assessments->latestForUser($userId, 'bdi2');
        $latestPwb = $this->assessments->latestForUser($userId, 'pwb');

        if (!$latestBdi2 || !$latestPwb) {
            return null;
        }

        return $this->scoring->combinedLevel($latestPwb->category, $latestBdi2->category);
    }
}
