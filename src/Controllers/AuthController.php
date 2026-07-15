<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\UserRepository;
use App\Repositories\LookupRepository;

class AuthController
{
    private UserRepository $users;
    private LookupRepository $lookup;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->lookup = new LookupRepository();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // GET /login
    public function showLoginForm(Request $request): void
    {
        $successRegister = $_SESSION['successRegister'] ?? null;
        unset($_SESSION['successRegister']);

        Response::view('auth/login', ['title' => 'Login', 'successRegister' => $successRegister]);
    }

    // GET /register
    public function showRegisterForm(Request $request): void
    {
        $fakultasList = $this->lookup->getFakultas();
        $jurusanList = $fakultasList
            ? $this->lookup->getJurusanByFakultas((int) $fakultasList[0]['id'])
            : [];

        Response::view('auth/register', [
            'title' => 'Daftar Akun',
            'fakultasList' => $fakultasList,
            'jurusanList' => $jurusanList,
        ]);
    }

    // POST /register
    public function register(Request $request): void
    {
        $old = [
            'nama' => trim($request->post('nama', '')),
            'nama_lengkap' => trim($request->post('nama_lengkap', '')),
            'username' => trim($request->post('username', '')),
            'email' => trim($request->post('email', '')),
            'password' => $request->post('password', ''),
            'npm' => trim($request->post('npm', '')),
            'jenis_kelamin' => trim($request->post('jenis_kelamin', '')),
            'fakultas' => trim($request->post('fakultas', '')),
            'jurusan' => trim($request->post('jurusan', '')),
            'no_hp' => trim($request->post('no_hp', '')),
        ];

        $fakultasList = $this->lookup->getFakultas();
        $jurusanList = $old['fakultas'] !== ''
            ? $this->lookup->getJurusanByFakultas((int) $old['fakultas'])
            : [];

        $fakultasName = $old['fakultas'] !== '' ? $this->lookup->findFakultasName((int) $old['fakultas']) : null;
        $jurusanName = $old['jurusan'] !== '' ? $this->lookup->findJurusanName((int) $old['jurusan']) : null;

        $error = $this->validateRegistration($old, $fakultasName, $jurusanName);

        if ($error) {
            Response::view('auth/register', array_merge(array_diff_key($old, ['password' => '']), [
                'title' => 'Daftar Akun',
                'error' => $error,
                'fakultasList' => $fakultasList,
                'jurusanList' => $jurusanList,
            ]));
            return;
        }

        $hashed = password_hash($old['password'], PASSWORD_DEFAULT);
        $userId = $this->users->createPendingMahasiswa(
            $old['nama'],
            $old['nama_lengkap'],
            $old['username'],
            $old['email'],
            $hashed,
            $old['npm'],
            $old['jenis_kelamin'],
            $fakultasName,
            $jurusanName,
            $old['no_hp']
        );

        $this->notifyAdminsOfPendingRegistration($userId, $old, $fakultasName, $jurusanName);

        $_SESSION['successRegister'] = 'Registrasi berhasil! Akun Anda menunggu persetujuan Admin sebelum bisa digunakan untuk login.';
        Response::redirect('/login');
    }

    // Returns an error message, or null if every field is valid.
    private function validateRegistration(array $old, ?string $fakultasName, ?string $jurusanName): ?string
    {
        $required = ['nama', 'nama_lengkap', 'username', 'email', 'npm', 'jenis_kelamin', 'no_hp'];
        foreach ($required as $field) {
            if ($old[$field] === '') {
                return 'Mohon lengkapi semua kolom dengan benar (password minimal 8 karakter).';
            }
        }

        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || strlen($old['password']) < 8) {
            return 'Mohon lengkapi semua kolom dengan benar (password minimal 8 karakter).';
        }

        if ($fakultasName === null || $jurusanName === null) {
            return 'Fakultas atau jurusan tidak valid.';
        }

        if ($this->users->findByUsername($old['username']) || $this->users->findByEmail($old['email'])) {
            return 'Username atau email sudah terdaftar.';
        }

        if ($this->users->findByNpm($old['npm'])) {
            return 'NPM sudah terdaftar.';
        }

        return null;
    }

    // Best-effort: a failed notification email must never block registration
    // itself, since the account row is already committed at this point.
    private function notifyAdminsOfPendingRegistration(int $userId, array $old, ?string $fakultasName, ?string $jurusanName): void
    {
        require_once __DIR__ . '/../../config/send_email.php';

        $subject = 'Pendaftaran Mahasiswa Baru — Perlu Persetujuan';
        $approvalUrl = rtrim(env('APP_URL', ''), '/') . '/admin/approvals';
        $message = "Ada mahasiswa baru mendaftar dan menunggu persetujuan:\n\n"
            . "Nama: {$old['nama']}\n"
            . "NPM: {$old['npm']}\n"
            . "Email: {$old['email']}\n"
            . "Fakultas: {$fakultasName}\n"
            . "Jurusan: {$jurusanName}\n"
            . "Username: {$old['username']}\n\n"
            . "Tinjau dan setujui di: {$approvalUrl}";

        foreach ($this->users->allAdminEmails() as $adminEmail) {
            try {
                kirimEmail($adminEmail, $subject, $message);
            } catch (\Throwable $e) {
                error_log('notifyAdminsOfPendingRegistration failed for user ' . $userId . ': ' . $e->getMessage());
            }
        }
    }

    // POST /login
    public function login(Request $request): void
    {
        $username = trim($request->post('username', ''));
        $password = $request->post('password', '');

        $user = $this->users->findByUsername($username);

        // password_verify checks the submitted password against the bcrypt/argon2
        // hash stored in the DB — never compare plaintext passwords directly.
        if (!$user || !password_verify($password, $user->password)) {
            Response::view('auth/login', [
                'title' => 'Login',
                'error' => 'Username atau password salah.',
            ]);
            return;
        }

        // NULL/'active' both mean "allowed" — NULL covers accounts created
        // before the pending-approval workflow existed, so they aren't
        // retroactively locked out.
        $statusError = match ($user->status) {
            'pending' => 'Akun Anda masih menunggu persetujuan Admin.',
            'rejected' => 'Pendaftaran Anda ditolak oleh Admin.',
            default => null,
        };

        if ($statusError !== null) {
            Response::view('auth/login', [
                'title' => 'Login',
                'error' => $statusError,
            ]);
            return;
        }

        // Prevent session fixation: issue a fresh session ID after login
        session_regenerate_id(true);

        $_SESSION['user_id']  = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role']     = $user->role;

        Response::redirect('/dashboard');
    }

    // POST /logout
    public function logout(Request $request): void
    {
        $_SESSION = [];
        session_destroy();
        Response::redirect('/login');
    }
}
