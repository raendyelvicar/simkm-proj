<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\CounselorRepository;
use App\Repositories\UserRepository;

// Admin CRUD over counselor accounts: creates the login (users row, role=counselor)
// together with its extended profile (counselor row), and edits/deactivates them.
class AdminCounselorController
{
    private const PER_PAGE = 10;

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
        $filters = [
            'search'       => trim((string) $request->get('q', '')),
            'profession'      => $request->get('profession') ?: null,
            'is_active' => $request->get('is_active', ''),
        ];
        $sort = (string) $request->get('sort', 'name');
        $dir = $request->get('dir') === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int) $request->get('page', 1));

        $result = $this->counselors->paginatedForAdmin($filters, $sort, $dir, $page, self::PER_PAGE);
        $totalPages = (int) max(1, ceil($result['total'] / self::PER_PAGE));

        Response::view('admin/counselors/index', [
            'title'       => 'Kelola Counselor',
            'counselors'  => $result['items'],
            'total'       => $result['total'],
            'page'        => $page,
            'totalPages'  => $totalPages,
            'sort'        => $sort,
            'dir'         => $dir,
            'filters'     => $filters,
        ]);
    }

    // GET /admin/counselors/create
    public function create(Request $request): void
    {
        Response::view('admin/counselors/create', ['title' => 'Tambah Counselor']);
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
                'title' => 'Tambah Counselor',
                'errors' => $errors,
                'old' => $fields,
            ]);
            return;
        }

        $userId = $this->counselors->createCounselor(
            $fields['name'],
            $fields['username'],
            $fields['email'],
            password_hash($fields['password'], PASSWORD_DEFAULT),
            $fields['registration_number'],
            $fields['profession'],
            $fields['specialization'],
            $fields['education'],
            $fields['experience_years'],
            $fields['languages'],
            $fields['consultation_fee'],
            $fields['session_duration'],
            $fields['consultation_method'],
            $fields['biography'],
            $fields['is_active'],
            $image
        );

        if ($image) {
            $this->counselors->updateUserProfileImage($userId, $image);
        }

        $_SESSION['success'] = 'Counselor berhasil ditambahkan.';
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
            'title' => 'Edit Counselor',
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

        $editingCounselorId = $counselor['has_profile'] ? (int) $counselor['counselor_id'] : null;
        [$fields, $errors] = $this->validate($request, (int) $id, $editingCounselorId);
        [$image, $imageError] = $this->handleImageUpload($request);

        if ($imageError) {
            $errors[] = $imageError;
        }

        if ($errors) {
            Response::view('admin/counselors/edit', [
                'title' => 'Edit Counselor',
                'counselor' => array_merge($counselor, $fields),
                'errors' => $errors,
            ]);
            return;
        }

        $this->counselors->updateUserBasic((int) $id, $fields['name'], $fields['username'], $fields['email']);

        if ($fields['password'] !== '') {
            $this->counselors->updateUserPassword((int) $id, password_hash($fields['password'], PASSWORD_DEFAULT));
        }

        if ($image) {
            $this->counselors->updateUserProfileImage((int) $id, $image);
        }

        $this->counselors->upsertProfile(
            (int) $id,
            $fields['registration_number'],
            $fields['profession'],
            $fields['specialization'],
            $fields['education'],
            $fields['experience_years'],
            $fields['languages'],
            $fields['consultation_fee'],
            $fields['session_duration'],
            $fields['consultation_method'],
            $fields['biography'],
            $fields['is_active'],
            $image
        );

        $_SESSION['success'] = 'Counselor berhasil diperbarui.';
        Response::redirect('/admin/counselors');
    }

    // POST /admin/counselors/{id}/status — soft delete (deactivate) / reactivate.
    // Only meaningful once a counselor profile row exists; a bare account must be
    // completed via the edit form first (it needs a registration_number to create that row).
    public function toggleStatus(Request $request, string $id): void
    {
        $counselor = $this->findOr404($id);
        if (!$counselor) {
            return;
        }

        if ($counselor['has_profile']) {
            $this->counselors->setActive((int) $counselor['counselor_id'], !$counselor['is_active']);
            $_SESSION['success'] = $counselor['is_active']
                ? 'Counselor berhasil dinonaktifkan.'
                : 'Counselor berhasil diaktifkan kembali.';
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

    private const PROFESSION_OPTIONS = ['Psychologist', 'Counselor', 'Psychiatrist'];
    private const METODE_OPTIONS = ['Online', 'Offline', 'Hybrid'];

    // Returns [fields, errors]. $editingUserId/$editingCounselorId are null when
    // creating, so the uniqueness checks don't collide with the record itself.
    private function validate(Request $request, ?int $editingUserId, ?int $editingCounselorId): array
    {
        $name = trim($request->post('name', ''));
        $username = trim($request->post('username', ''));
        $email = trim($request->post('email', ''));
        $password = $request->post('password', '');
        $registrationNumber = trim($request->post('registration_number', ''));
        $profession = trim($request->post('profession', ''));
        $specialization = trim($request->post('specialization', '')) ?: null;
        $education = trim($request->post('education', '')) ?: null;
        $experienceYears = (int) $request->post('experience_years', 0);
        $languages = trim($request->post('languages', '')) ?: null;
        $consultationFee = (float) $request->post('consultation_fee', 0);
        $durationSession = (int) $request->post('session_duration', 60);
        $consultationMethod = trim($request->post('consultation_method', 'Online'));
        $biography = trim($request->post('biography', '')) ?: null;
        $isActive = $request->post('is_active') !== null;

        $fields = [
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'registration_number' => $registrationNumber,
            'profession' => $profession,
            'specialization' => $specialization,
            'education' => $education,
            'experience_years' => $experienceYears,
            'languages' => $languages,
            'consultation_fee' => $consultationFee,
            'session_duration' => $durationSession,
            'consultation_method' => $consultationMethod,
            'biography' => $biography,
            'is_active' => $isActive,
        ];

        $errors = [];

        if ($name === '') {
            $errors[] = 'Nama wajib diisi.';
        }
        if ($username === '') {
            $errors[] = 'Username wajib diisi.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email tidak valid.';
        }
        if ($registrationNumber === '') {
            $errors[] = 'Nomor registrasi wajib diisi.';
        }
        if (!in_array($profession, self::PROFESSION_OPTIONS, true)) {
            $errors[] = 'Profesi wajib dipilih.';
        }
        if (!in_array($consultationMethod, self::METODE_OPTIONS, true)) {
            $errors[] = 'Metode konsultasi tidak valid.';
        }
        if ($experienceYears < 0) {
            $errors[] = 'Pengalaman tahun tidak boleh negatif.';
        }
        if ($consultationFee < 0) {
            $errors[] = 'Biaya konsultasi tidak boleh negatif.';
        }
        if ($durationSession <= 0) {
            $errors[] = 'Durasi sesi wajib diisi.';
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

        if ($registrationNumber !== '' && $this->counselors->registrationNumberExists($registrationNumber, $editingCounselorId)) {
            $errors[] = 'Nomor registrasi sudah digunakan konselor lain.';
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
