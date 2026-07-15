<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\DiaryRepository;

class DiaryController
{
    private const MOODS = ['Sangat Buruk', 'Sedih', 'Netral', 'Senang', 'Sangat Senang'];

    private DiaryRepository $diaries;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->diaries = new DiaryRepository();
    }

    // GET /diary — list only the logged-in user's own entries
    public function index(Request $request): void
    {
        $entries = $this->diaries->findByUserId((int) $_SESSION['user_id']);

        Response::view('diary/index', [
            'title' => 'Diary',
            'entries' => array_map(fn ($entry) => $entry->toArray(), $entries),
        ]);
    }

    // GET /diary/create
    public function create(Request $request): void
    {
        Response::view('diary/create', [
            'title' => 'Tulis Diary',
            'moods' => self::MOODS,
        ]);
    }

    // POST /diary
    public function store(Request $request): void
    {
        [$judul, $moodLevel, $content, $entryDate, $isPrivate, $errors] = $this->validate($request);

        if ($errors) {
            Response::view('diary/create', [
                'title' => 'Tulis Diary',
                'moods' => self::MOODS,
                'errors' => $errors,
                'old' => compact('judul', 'moodLevel', 'content', 'entryDate', 'isPrivate'),
            ]);
            return;
        }

        $this->diaries->create((int) $_SESSION['user_id'], $judul, $moodLevel, $content, $isPrivate, $entryDate);

        Response::redirect('/diary');
    }

    // GET /diary/{id} — must verify the entry belongs to $_SESSION['user_id']
    public function show(Request $request, string $id): void
    {
        $entry = $this->findOwnedEntry((int) $id);

        if (!$entry) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Diary Tidak Ditemukan']);
            return;
        }

        Response::view('diary/show', [
            'title' => 'Detail Diary',
            'entry' => $entry->toArray(),
        ]);
    }

    // GET /diary/{id}/edit — must verify the entry belongs to $_SESSION['user_id']
    public function edit(Request $request, string $id): void
    {
        $entry = $this->findOwnedEntry((int) $id);

        if (!$entry) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Diary Tidak Ditemukan']);
            return;
        }

        Response::view('diary/edit', [
            'title' => 'Edit Diary',
            'entry' => $entry->toArray(),
            'moods' => self::MOODS,
        ]);
    }

    // POST /diary/{id} — must verify ownership before updating
    public function update(Request $request, string $id): void
    {
        $entry = $this->findOwnedEntry((int) $id);

        if (!$entry) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Diary Tidak Ditemukan']);
            return;
        }

        [$judul, $moodLevel, $content, $entryDate, $isPrivate, $errors] = $this->validate($request);

        if ($errors) {
            Response::view('diary/edit', [
                'title' => 'Edit Diary',
                'entry' => array_merge($entry->toArray(), compact('judul', 'moodLevel', 'content', 'entryDate', 'isPrivate')),
                'moods' => self::MOODS,
                'errors' => $errors,
            ]);
            return;
        }

        $this->diaries->update((int) $id, $judul, $moodLevel, $content, $isPrivate, $entryDate);

        Response::redirect('/diary/' . $id);
    }

    // POST /diary/{id}/delete — must verify ownership before deleting
    public function destroy(Request $request, string $id): void
    {
        if ($this->findOwnedEntry((int) $id)) {
            $this->diaries->delete((int) $id);
        }

        Response::redirect('/diary');
    }

    private function findOwnedEntry(int $id)
    {
        $entry = $this->diaries->find($id);

        if (!$entry || $entry->userId !== (int) $_SESSION['user_id']) {
            return null;
        }

        return $entry;
    }

    private function validate(Request $request): array
    {
        $judul = trim($request->post('judul', ''));
        $moodLevel = trim($request->post('mood_level', ''));
        $content = trim($request->post('content', ''));
        $entryDate = trim($request->post('entry_date', '')) ?: date('Y-m-d');
        $isPrivate = $request->post('is_private') !== null;

        $errors = [];
        if ($judul === '') {
            $errors[] = 'Judul wajib diisi.';
        }
        if (!in_array($moodLevel, self::MOODS, true)) {
            $errors[] = 'Mood tidak valid.';
        }
        if ($content === '') {
            $errors[] = 'Isi diary wajib diisi.';
        }

        return [$judul, $moodLevel, $content, $entryDate, $isPrivate, $errors];
    }
}
