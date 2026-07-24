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
        $facultyList = $this->lookup->getFaculty();
        $majorList = $facultyList
            ? $this->lookup->getMajorByFaculty((int) $facultyList[0]['id'])
            : [];

        Response::view('auth/register', [
            'title' => 'Daftar Akun',
            'facultyList' => $facultyList,
            'majorList' => $majorList,
        ]);
    }

    // POST /register
    public function register(Request $request): void
    {
        $old = [
            'name' => trim($request->post('name', '')),
            'full_name' => trim($request->post('full_name', '')),
            'username' => trim($request->post('username', '')),
            'email' => trim($request->post('email', '')),
            'password' => $request->post('password', ''),
            'student_number' => trim($request->post('student_number', '')),
            'gender' => trim($request->post('gender', '')),
            'faculty' => trim($request->post('faculty', '')),
            'major' => trim($request->post('major', '')),
            'phone_number' => trim($request->post('phone_number', '')),
        ];

        $facultyList = $this->lookup->getFaculty();
        $majorList = $old['faculty'] !== ''
            ? $this->lookup->getMajorByFaculty((int) $old['faculty'])
            : [];

        $facultyName = $old['faculty'] !== '' ? $this->lookup->findFacultyName((int) $old['faculty']) : null;
        $majorName = $old['major'] !== '' ? $this->lookup->findMajorName((int) $old['major']) : null;

        $error = $this->validateRegistration($old, $facultyName, $majorName);

        if ($error) {
            Response::view('auth/register', array_merge(array_diff_key($old, ['password' => '']), [
                'title' => 'Daftar Akun',
                'error' => $error,
                'facultyList' => $facultyList,
                'majorList' => $majorList,
            ]));
            return;
        }

        $hashed = password_hash($old['password'], PASSWORD_DEFAULT);
        $userId = $this->users->createPendingStudent(
            $old['name'],
            $old['full_name'],
            $old['username'],
            $old['email'],
            $hashed,
            $old['student_number'],
            $old['gender'],
            $facultyName,
            $majorName,
            $old['phone_number']
        );

        $this->notifyAdminsOfPendingRegistration($userId, $old, $facultyName, $majorName);

        $_SESSION['successRegister'] = 'Registrasi berhasil! Akun Anda menunggu persetujuan Admin sebelum bisa digunakan untuk login.';
        Response::redirect('/login');
    }

    // Returns an error message, or null if every field is valid.
    private function validateRegistration(array $old, ?string $facultyName, ?string $majorName): ?string
    {
        $required = ['name', 'full_name', 'username', 'email', 'student_number', 'gender', 'phone_number'];
        foreach ($required as $field) {
            if ($old[$field] === '') {
                return 'Mohon lengkapi semua kolom dengan benar (password minimal 8 karakter).';
            }
        }

        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || strlen($old['password']) < 8) {
            return 'Mohon lengkapi semua kolom dengan benar (password minimal 8 karakter).';
        }

        if ($facultyName === null || $majorName === null) {
            return 'Fakultas atau jurusan tidak valid.';
        }

        if ($this->users->findByUsername($old['username']) || $this->users->findByEmail($old['email'])) {
            return 'Username atau email sudah terdaftar.';
        }

        if ($this->users->findByStudentNumber($old['student_number'])) {
            return 'NPM sudah terdaftar.';
        }

        return null;
    }

    // Best-effort: a failed notification email must never block registration
    // itself, since the account row is already committed at this point.
    private function notifyAdminsOfPendingRegistration(int $userId, array $old, ?string $facultyName, ?string $majorName): void
    {
        require_once __DIR__ . '/../../config/send_email.php';

        $subject = 'Pendaftaran Mahasiswa Baru — Perlu Persetujuan';
        $approvalUrl = rtrim(env('APP_URL', ''), '/') . '/admin/approvals';
        $message = "Ada mahasiswa baru mendaftar dan menunggu persetujuan:\n\n"
            . "Name: {$old['name']}\n"
            . "NPM: {$old['student_number']}\n"
            . "Email: {$old['email']}\n"
            . "Faculty: {$facultyName}\n"
            . "Major: {$majorName}\n"
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

        if ($user->role === 'student') {
            $_SESSION['show_daily_tip'] = true;
        }

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
