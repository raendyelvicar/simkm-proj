<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\CounselorRepository;
use App\Repositories\DiaryRepository;

// Konselor-side, read-only view of diary entries students have published to them
// (DiaryController::store()'s "Publish to Konselor" option). Mirrors ConsultationController's
// shape (list + detail, konselor-only), but there is no reply here yet.
class SharedDiaryController
{
    private DiaryRepository $diaries;
    private CounselorRepository $counselors;

    public function __construct()
    {
        AuthMiddleware::handle();

        if (($_SESSION['role'] ?? '') !== 'konselor') {
            http_response_code(403);
            exit('Forbidden: konselor only.');
        }

        $this->diaries = new DiaryRepository();
        $this->counselors = new CounselorRepository();
    }

    // GET /shared-diaries
    public function index(Request $request): void
    {
        Response::view('counselor/shared_diaries', [
            'title' => 'Diary Dibagikan',
            'entries' => $this->diaries->findSharedWithKonselor($this->currentKonselorId()),
        ]);
    }

    // GET /shared-diaries/{id}
    public function show(Request $request, string $id): void
    {
        $entry = $this->diaries->findSharedEntry((int) $id, $this->currentKonselorId());

        if (!$entry) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Diary Tidak Ditemukan']);
            return;
        }

        Response::view('counselor/shared_diary_detail', [
            'title' => 'Diary ' . ($entry['student_nama'] ?: 'Mahasiswa'),
            'entry' => $entry,
        ]);
    }

    // 0 when the logged-in konselor has no completed profile yet — findSharedWithKonselor/
    // findSharedEntry simply return nothing for that, since shared_konselor_id can never be 0.
    private function currentKonselorId(): int
    {
        $counselor = $this->counselors->find((int) $_SESSION['user_id']);

        return $counselor ? (int) $counselor['konselor_id'] : 0;
    }
}
