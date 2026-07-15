<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\CounselorRepository;
use App\Repositories\UserRepository;

// Admin CRUD over konselor accounts: creates the login (users row, role=konselor)
// together with its extended profile (konselor row), and edits/deactivates them.
class AdminCounselorController
{
    private CounselorRepository $counselors;
    private UserRepository $users;

    public function __construct()
    {
        AuthMiddleware::handle();

        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('Forbidden: admin only.');
        }

        $this->counselors = new CounselorRepository();
        $this->users = new UserRepository();
    }

    // GET /admin/counselors
    public function index(Request $request): void
    {
        Response::view('admin/counselors/index', [
            'title' => 'Kelola Konselor',
            'counselors' => $this->counselors->allForAdmin(),
        ]);
    }

    // GET /admin/counselors/create
    public function create(Request $request): void
    {
        Response::view('admin/counselors/create', ['title' => 'Tambah Konselor']);
    }

    // POST /admin/counselors
    public function store(Request $request): void
    {
        [$fields, $errors] = $this->validate($request, null, null);
        [$image, $imageError] = $this->handleImageUpload($request);

        if ($imageError) {
            $errors[] = $imageError;
        }

        if ($errors) {
            Response::view('admin/counselors/create', [
                'title' => 'Tambah Konselor',
                'errors' => $errors,
                'old' => $fields,
            ]);
            return;
        }

        $userId = $this->counselors->createCounselor(
            $fields['nama'],
            $fields['username'],
            $fields['email'],
            password_hash($fields['password'], PASSWORD_DEFAULT),
            $fields['nip_nik'],
            $fields['spesialisasi'],
            $fields['jadwal_praktik'],
            $fields['biografi_singkat'],
            $fields['status_aktif']
        );

        if ($image) {
            $this->counselors->updateUserProfileImage($userId, $image);
        }

        Response::redirect('/admin/counselors');
    }

    // GET /admin/counselors/{id}/edit
    public function edit(Request $request, string $id): void
    {
        $counselor = $this->findOr404($id);
        if (!$counselor) {
            return;
        }

        Response::view('admin/counselors/edit', [
            'title' => 'Edit Konselor',
            'counselor' => $counselor,
        ]);
    }

    // POST /admin/counselors/{id}
    public function update(Request $request, string $id): void
    {
        $counselor = $this->findOr404($id);
        if (!$counselor) {
            return;
        }

        $editingKonselorId = $counselor['has_profile'] ? (int) $counselor['konselor_id'] : null;
        [$fields, $errors] = $this->validate($request, (int) $id, $editingKonselorId);
        [$image, $imageError] = $this->handleImageUpload($request);

        if ($imageError) {
            $errors[] = $imageError;
        }

        if ($errors) {
            Response::view('admin/counselors/edit', [
                'title' => 'Edit Konselor',
                'counselor' => array_merge($counselor, $fields),
                'errors' => $errors,
            ]);
            return;
        }

        $this->counselors->updateUserBasic((int) $id, $fields['nama'], $fields['username'], $fields['email']);

        if ($fields['password'] !== '') {
            $this->counselors->updateUserPassword((int) $id, password_hash($fields['password'], PASSWORD_DEFAULT));
        }

        if ($image) {
            $this->counselors->updateUserProfileImage((int) $id, $image);
        }

        $this->counselors->upsertProfile(
            (int) $id,
            $fields['nip_nik'],
            $fields['spesialisasi'],
            $fields['jadwal_praktik'],
            $fields['biografi_singkat'],
            $fields['status_aktif']
        );

        Response::redirect('/admin/counselors');
    }

    // POST /admin/counselors/{id}/status — soft delete (deactivate) / reactivate.
    // Only meaningful once a konselor profile row exists; a bare account must be
    // completed via the edit form first (it needs a nip_nik to create that row).
    public function toggleStatus(Request $request, string $id): void
    {
        $counselor = $this->findOr404($id);
        if (!$counselor) {
            return;
        }

        if ($counselor['has_profile']) {
            $this->counselors->setActive((int) $counselor['konselor_id'], !$counselor['status_aktif']);
        }

        Response::redirect('/admin/counselors');
    }

    private function findOr404(string $id): ?array
    {
        $counselor = $this->counselors->findForAdmin((int) $id);

        if (!$counselor) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Konselor Tidak Ditemukan']);
            return null;
        }

        return $counselor;
    }

    // Returns [fields, errors]. $editingUserId/$editingKonselorId are null when
    // creating, so the uniqueness checks don't collide with the record itself.
    private function validate(Request $request, ?int $editingUserId, ?int $editingKonselorId): array
    {
        $nama = trim($request->post('nama', ''));
        $username = trim($request->post('username', ''));
        $email = trim($request->post('email', ''));
        $password = $request->post('password', '');
        $nipNik = trim($request->post('nip_nik', ''));
        $spesialisasi = trim($request->post('spesialisasi', '')) ?: null;
        $jadwalPraktik = trim($request->post('jadwal_praktik', '')) ?: null;
        $biografiSingkat = trim($request->post('biografi_singkat', '')) ?: null;
        $statusAktif = $request->post('status_aktif') !== null;

        $fields = [
            'nama' => $nama,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'nip_nik' => $nipNik,
            'spesialisasi' => $spesialisasi,
            'jadwal_praktik' => $jadwalPraktik,
            'biografi_singkat' => $biografiSingkat,
            'status_aktif' => $statusAktif,
        ];

        $errors = [];

        if ($nama === '') {
            $errors[] = 'Nama wajib diisi.';
        }
        if ($username === '') {
            $errors[] = 'Username wajib diisi.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email tidak valid.';
        }
        if ($nipNik === '') {
            $errors[] = 'NIP/NIK wajib diisi.';
        }
        if ($editingUserId === null && strlen($password) < 8) {
            $errors[] = 'Password minimal 8 karakter.';
        }
        if ($editingUserId !== null && $password !== '' && strlen($password) < 8) {
            $errors[] = 'Password baru minimal 8 karakter.';
        }

        $existingUsername = $username !== '' ? $this->users->findByUsername($username) : null;
        if ($existingUsername && $existingUsername->id !== $editingUserId) {
            $errors[] = 'Username sudah digunakan.';
        }

        $existingEmail = $email !== '' ? $this->users->findByEmail($email) : null;
        if ($existingEmail && $existingEmail->id !== $editingUserId) {
            $errors[] = 'Email sudah digunakan.';
        }

        if ($nipNik !== '' && $this->counselors->nipNikExists($nipNik, $editingKonselorId)) {
            $errors[] = 'NIP/NIK sudah digunakan konselor lain.';
        }

        return [$fields, $errors];
    }

    private const ALLOWED_IMAGE_TYPES = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
    ];

    private const MAX_IMAGE_BYTES = 2 * 1024 * 1024;

    // Returns [publicPath|null, error|null]. Leaves any existing photo untouched
    // when no file is chosen — mirrors ArticleController's upload handling.
    private function handleImageUpload(Request $request): array
    {
        $file = $request->file('photo');

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

        $dir = __DIR__ . '/../../public/uploads/profile';
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return [null, 'Gagal mengunggah foto.'];
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            return [null, 'Gagal mengunggah foto.'];
        }

        return ['/uploads/profile/' . $filename, null];
    }
}
