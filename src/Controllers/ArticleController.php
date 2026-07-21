<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\ArticleRepository;

class ArticleController
{
    private const PER_PAGE = 9;

    private ArticleRepository $articles;

    public function __construct()
    {
        $this->articles = new ArticleRepository();
    }

    // GET /article — public, everyone can view
    public function index(Request $request): void
    {
        $filters = [
            'search'   => trim((string) $request->get('q', '')),
            'category' => $request->get('category') ?: null,
        ];
        [$sort, $dir] = $this->parseSort((string) $request->get('sort', 'published_at:desc'));
        $page = max(1, (int) $request->get('page', 1));

        $result = $this->articles->paginated($filters, $sort, $dir, $page, self::PER_PAGE);
        $totalPages = (int) max(1, ceil($result['total'] / self::PER_PAGE));

        Response::view('article/index', [
            'title'             => 'Artikel',
            'articles'          => array_map(fn ($article) => $article->toArray(), $result['items']),
            'total'             => $result['total'],
            'page'              => $page,
            'totalPages'        => $totalPages,
            'sort'              => $sort,
            'dir'               => $dir,
            'filters'           => $filters,
            'categoryOptions'   => $this->articles->distinctCategories(),
        ]);
    }

    // The sort dropdown posts a single combined "column:dir" value (see sort_options()
    // in src/Helpers/functions.php) since a card grid has no <th> to attach a link to.
    private function parseSort(string $combined): array
    {
        [$sort, $dir] = array_pad(explode(':', $combined, 2), 2, 'desc');

        return [$sort, $dir === 'asc' ? 'asc' : 'desc'];
    }

    // GET /article/{id} — public
    public function show(Request $request, string $id): void
    {
        $article = $this->articles->find((int) $id);

        if (!$article) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Artikel Tidak Ditemukan']);
            return;
        }

        Response::view('article/show', [
            'title' => $article->title,
            'article' => $article->toArray(),
        ]);
    }

    // GET /article/create — requires login
    public function create(Request $request): void
    {
        AuthMiddleware::handle();

        Response::view('article/create', ['title' => 'Tulis Artikel']);
    }

    // POST /article — requires login
    public function store(Request $request): void
    {
        AuthMiddleware::handle();

        [$title, $content, $category, $tags, $errors] = $this->validate($request);
        [$image, $imageError] = $this->handleImageUpload($request);

        if ($imageError) {
            $errors[] = $imageError;
        }

        if ($errors) {
            Response::view('article/create', [
                'title' => 'Tulis Artikel',
                'errors' => $errors,
                'old' => compact('title', 'content', 'category', 'tags'),
            ]);
            return;
        }

        $id = $this->articles->create((int) $_SESSION['user_id'], $title, $content, $category, $tags, $image);

        $_SESSION['success'] = 'Artikel berhasil dipublikasikan.';
        Response::redirect('/article/' . $id);
    }

    // GET /article/{id}/edit — must verify the article belongs to $_SESSION['user_id']
    public function edit(Request $request, string $id): void
    {
        AuthMiddleware::handle();

        $article = $this->findOwnedArticle((int) $id);

        if (!$article) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Artikel Tidak Ditemukan']);
            return;
        }

        Response::view('article/edit', [
            'title' => 'Edit Artikel',
            'article' => $article->toArray(),
        ]);
    }

    // POST /article/{id} — must verify ownership before updating
    public function update(Request $request, string $id): void
    {
        AuthMiddleware::handle();

        $article = $this->findOwnedArticle((int) $id);

        if (!$article) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Artikel Tidak Ditemukan']);
            return;
        }

        [$title, $content, $category, $tags, $errors] = $this->validate($request);
        [$uploadedImage, $imageError] = $this->handleImageUpload($request);

        if ($imageError) {
            $errors[] = $imageError;
        }

        if ($errors) {
            Response::view('article/edit', [
                'title' => 'Edit Artikel',
                'article' => array_merge($article->toArray(), compact('title', 'content', 'category', 'tags')),
                'errors' => $errors,
            ]);
            return;
        }

        $image = $uploadedImage ?? $article->image;

        $this->articles->update((int) $id, $title, $content, $category, $tags, $image);

        $_SESSION['success'] = 'Artikel berhasil diperbarui.';
        Response::redirect('/article/' . $id);
    }

    // POST /article/{id}/delete — must verify ownership before deleting
    public function destroy(Request $request, string $id): void
    {
        AuthMiddleware::handle();

        if ($this->findOwnedArticle((int) $id)) {
            $this->articles->delete((int) $id);
            $_SESSION['success'] = 'Artikel berhasil dihapus.';
        }

        Response::redirect('/article');
    }

    private function findOwnedArticle(int $id)
    {
        $article = $this->articles->find($id);

        if (!$article || $article->userId !== (int) $_SESSION['user_id']) {
            return null;
        }

        return $article;
    }

    private function validate(Request $request): array
    {
        $title = trim($request->post('title', ''));
        $content = trim($request->post('content', ''));
        $category = trim($request->post('category', '')) ?: null;
        $tags = $this->normalizeTags($request->post('tags', ''));

        $errors = [];
        if ($title === '') {
            $errors[] = 'Judul wajib diisi.';
        }
        if ($content === '') {
            $errors[] = 'Isi artikel wajib diisi.';
        }

        return [$title, $content, $category, $tags, $errors];
    }

    // Tags are stored as a normalized comma-separated string (mirrors the existing free-text category field).
    private function normalizeTags(string $raw): ?string
    {
        $tags = array_filter(array_map('trim', explode(',', $raw)));

        return $tags ? implode(', ', $tags) : null;
    }

    private const ALLOWED_IMAGE_TYPES = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
    ];

    private const MAX_IMAGE_BYTES = 2 * 1024 * 1024;

    // Returns [publicPath|null, error|null]. Leaves any existing image untouched when no file is chosen.
    private function handleImageUpload(Request $request): array
    {
        $file = $request->file('image');

        if (!$file) {
            return [null, null];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [null, 'Gagal mengunggah foto.'];
        }

        if ($file['size'] > self::MAX_IMAGE_BYTES) {
            return [null, 'Ukuran foto maksimal 2MB.'];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = mime_content_type($file['tmp_name']);

        if (!isset(self::ALLOWED_IMAGE_TYPES[$ext]) || self::ALLOWED_IMAGE_TYPES[$ext] !== $mime) {
            return [null, 'Foto harus berformat JPG, PNG, atau WEBP.'];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return [null, 'Gagal mengunggah foto.'];
        }

        $dir = __DIR__ . '/../../public/uploads/articles';
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return [null, 'Gagal mengunggah foto.'];
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            return [null, 'Gagal mengunggah foto.'];
        }

        return ['/uploads/articles/' . $filename, null];
    }
}
