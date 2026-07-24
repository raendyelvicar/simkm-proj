<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\CounselorRepository;
use App\Repositories\DiaryRepository;

// Counselor-side, read-only view of diary entries students have published to them
// (DiaryController::store()'s "Publish to Counselor" option). Mirrors ConsultationController's
// shape (list + detail, counselor-only), but there is no reply here yet.
class SharedDiaryController
{
    private const PER_PAGE = 10;

    private DiaryRepository $diaries;
    private CounselorRepository $counselors;

    public function __construct()
    {
        AuthMiddleware::handle();

        if (($_SESSION['role'] ?? '') !== 'counselor') {
            http_response_code(403);
            exit('Forbidden: counselor only.');
        }

        $this->diaries = new DiaryRepository();
        $this->counselors = new CounselorRepository();
    }

    // GET /shared-diaries
    public function index(Request $request): void
    {
        $filters = ['search' => trim((string) $request->get('q', ''))];
        [$sort, $dir] = $this->parseSort((string) $request->get('sort', 'entry_date:desc'));
        $page = max(1, (int) $request->get('page', 1));

        $result = $this->diaries->paginatedSharedWithCounselor($this->currentCounselorId(), $filters, $sort, $dir, $page, self::PER_PAGE);
        $totalPages = (int) max(1, ceil($result['total'] / self::PER_PAGE));

        Response::view('counselor/shared_diaries', [
            'title'      => 'Diary Dibagikan',
            'entries'    => $result['items'],
            'total'      => $result['total'],
            'page'       => $page,
            'totalPages' => $totalPages,
            'sort'       => $sort,
            'dir'        => $dir,
            'filters'    => $filters,
        ]);
    }

    private function parseSort(string $combined): array
    {
        [$sort, $dir] = array_pad(explode(':', $combined, 2), 2, 'desc');

        return [$sort, $dir === 'asc' ? 'asc' : 'desc'];
    }

    // GET /shared-diaries/{id}
    public function show(Request $request, string $id): void
    {
        $entry = $this->diaries->findSharedEntry((int) $id, $this->currentCounselorId());

        if (!$entry) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Diary Tidak Ditemukan']);
            return;
        }

        Response::view('counselor/shared_diary_detail', [
            'title' => 'Diary ' . ($entry['student_name'] ?: 'Student'),
            'entry' => $entry,
        ]);
    }

    // 0 when the logged-in counselor has no completed profile yet — findSharedWithCounselor/
    // findSharedEntry simply return nothing for that, since shared_counselor_id can never be 0.
    private function currentCounselorId(): int
    {
        $counselor = $this->counselors->find((int) $_SESSION['user_id']);

        return $counselor ? (int) $counselor['counselor_id'] : 0;
    }
}
