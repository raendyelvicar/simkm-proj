<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\UserRepository;

class ProfileController
{
    private UserRepository $users;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->users = new UserRepository();
    }

    // GET /profile
    public function show(Request $request): void
    {
        $user = $this->users->find((int) $_SESSION['user_id']);
        Response::view('profile/show', ['title' => 'Profil', 'user' => $user]);
    }

    // POST /profile
    public function update(Request $request): void
    {
        $userId = (int) $_SESSION['user_id'];
        $user = $this->users->find($userId);

        $nama = trim($request->post('nama', ''));
        $email = trim($request->post('email', ''));
        $noHp = trim($request->post('no_hp', ''));
        $jenisKelamin = trim($request->post('jenis_kelamin', ''));
        $fakultas = trim($request->post('fakultas', ''));
        $jurusan = trim($request->post('jurusan', ''));

        $errors = [];
        if ($nama === '') {
            $errors[] = 'Nama wajib diisi.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email tidak valid.';
        }

        [$photo, $photoError] = $this->handlePhotoUpload($request);
        if ($photoError) {
            $errors[] = $photoError;
        }

        if ($errors) {
            Response::view('profile/show', [
                'title' => 'Profil',
                'user' => $user,
                'errors' => $errors,
            ]);
            return;
        }

        $this->users->updateProfile($userId, $nama, $email, $noHp, $jenisKelamin, $fakultas, $jurusan, $photo);

        if ($photo !== null && $user->profile !== '') {
            $oldPath = __DIR__ . '/../../public/uploads/profile/' . $user->profile;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $_SESSION['success'] = 'Profil berhasil diperbarui.';
        Response::redirect('/profile');
    }

    private const ALLOWED_PHOTO_TYPES = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
    ];

    private const MAX_PHOTO_BYTES = 2 * 1024 * 1024;

    // Returns [filename|null, error|null]. Leaves the existing photo untouched when no file is chosen.
    private function handlePhotoUpload(Request $request): array
    {
        $file = $request->file('photo');

        if (!$file) {
            return [null, null];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [null, 'Gagal mengunggah foto.'];
        }

        if ($file['size'] > self::MAX_PHOTO_BYTES) {
            return [null, 'Ukuran foto maksimal 2MB.'];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = mime_content_type($file['tmp_name']);

        if (!isset(self::ALLOWED_PHOTO_TYPES[$ext]) || self::ALLOWED_PHOTO_TYPES[$ext] !== $mime) {
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

        return [$filename, null];
    }
}
