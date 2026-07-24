<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\DailyTipRepository;

// Counselor-only CRUD over the shared pool of daily tips shown to student
// as a popup right after login.
class DailyTipController
{
    private const PER_PAGE = 10;

    private DailyTipRepository $tips;

    public function __construct()
    {
        AuthMiddleware::handle();

        if (($_SESSION['role'] ?? '') !== 'counselor') {
            http_response_code(403);
            exit('Forbidden: counselor only.');
        }

        $this->tips = new DailyTipRepository();
    }

    // GET /tips
    public function index(Request $request): void
    {
        $filters = [
            'search'    => trim((string) $request->get('q', '')),
            'is_active' => $request->get('is_active', ''),
        ];
        $sort = (string) $request->get('sort', 'created_at');
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $page = max(1, (int) $request->get('page', 1));

        $result = $this->tips->paginated($filters, $sort, $dir, $page, self::PER_PAGE);
        $totalPages = (int) max(1, ceil($result['total'] / self::PER_PAGE));

        Response::view('tips/index', [
            'title'      => 'Tips Harian',
            'tips'       => array_map(fn ($tip) => $tip->toArray(), $result['items']),
            'total'      => $result['total'],
            'page'       => $page,
            'totalPages' => $totalPages,
            'sort'       => $sort,
            'dir'        => $dir,
            'filters'    => $filters,
        ]);
    }

    // GET /tips/create
    public function create(Request $request): void
    {
        Response::view('tips/create', ['title' => 'Tambah Tips']);
    }

    // POST /tips
    public function store(Request $request): void
    {
        [$fields, $errors] = $this->validate($request);

        if ($errors) {
            Response::view('tips/create', [
                'title' => 'Tambah Tips',
                'errors' => $errors,
                'old' => $fields,
            ]);
            return;
        }

        $this->tips->create($fields['title'], $fields['content'], (int) $_SESSION['user_id'], $fields['is_active']);

        $_SESSION['success'] = 'Tips berhasil ditambahkan.';
        Response::redirect('/tips');
    }

    // GET /tips/{id}/edit
    public function edit(Request $request, string $id): void
    {
        $tip = $this->findOr404($id);
        if (!$tip) {
            return;
        }

        Response::view('tips/edit', [
            'title' => 'Edit Tips',
            'tip' => $tip->toArray(),
        ]);
    }

    // POST /tips/{id}
    public function update(Request $request, string $id): void
    {
        $tip = $this->findOr404($id);
        if (!$tip) {
            return;
        }

        [$fields, $errors] = $this->validate($request);

        if ($errors) {
            Response::view('tips/edit', [
                'title' => 'Edit Tips',
                'tip' => array_merge($tip->toArray(), $fields),
                'errors' => $errors,
            ]);
            return;
        }

        $this->tips->update((int) $id, $fields['title'], $fields['content'], $fields['is_active']);

        $_SESSION['success'] = 'Tips berhasil diperbarui.';
        Response::redirect('/tips');
    }

    // POST /tips/{id}/delete
    public function destroy(Request $request, string $id): void
    {
        $tip = $this->findOr404($id);
        if (!$tip) {
            return;
        }

        $this->tips->delete((int) $id);

        $_SESSION['success'] = 'Tips berhasil dihapus.';
        Response::redirect('/tips');
    }

    private function findOr404(string $id)
    {
        $tip = $this->tips->find((int) $id);

        if (!$tip) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Tips Tidak Ditemukan']);
            return null;
        }

        return $tip;
    }

    // Returns [fields, errors].
    private function validate(Request $request): array
    {
        $title = trim($request->post('title', ''));
        $content = trim($request->post('content', ''));
        $isActive = $request->post('is_active') !== null;

        $fields = [
            'title' => $title,
            'content' => $content,
            'is_active' => $isActive,
        ];

        $errors = [];

        if ($title === '') {
            $errors[] = 'Judul tips wajib diisi.';
        }

        if ($content === '') {
            $errors[] = 'Isi tips wajib diisi.';
        }

        return [$fields, $errors];
    }
}
