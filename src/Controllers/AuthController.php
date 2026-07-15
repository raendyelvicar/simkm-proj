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
        Response::view('auth/login', ['title' => 'Login']);
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
        $name     = trim($request->post('name', ''));
        $username = trim($request->post('username', ''));
        $email    = trim($request->post('email', ''));
        $password = $request->post('password', '');

        if (!$name || !$username || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            Response::view('auth/register', [
                'title' => 'Daftar Akun',
                'error' => 'Mohon lengkapi semua kolom dengan benar (password minimal 8 karakter).',
            ]);
            return;
        }

        if ($this->users->findByUsername($username) || $this->users->findByEmail($email)) {
            Response::view('auth/register', [
                'title' => 'Daftar Akun',
                'error' => 'Username atau email sudah terdaftar.',
            ]);
            return;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $this->users->create($name, $username, $email, $hashed);

        Response::redirect('/login');
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
