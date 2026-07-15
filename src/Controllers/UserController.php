<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\UserRepository;
use App\Services\MailService;

class UserController
{
    private UserRepository $users;
    private MailService $mailer;

    public function __construct()
    {
        $this->users  = new UserRepository();
        $this->mailer = new MailService();
    }

    // GET /users
    public function index(Request $request): void
    {
        $users = array_map(fn($u) => $u->toArray(), $this->users->all());
        Response::json(['data' => $users]);
    }

    public function demo(Request $request): void
    {
        // TODO: ArticleRepository::all()
        Response::view('examples/demo_page', [
            'title' => 'Contoh Halaman',
            'content' => include __DIR__ . '/../../templates/examples/demo_content.php',
        ]);
    }

    // GET /users/{id}
    public function show(Request $request, string $id): void
    {
        $user = $this->users->find((int) $id);

        if (!$user) {
            Response::json(['error' => 'User not found'], 404);
        }

        Response::json(['data' => $user->toArray()]);
    }

    // POST /users
    public function store(Request $request): void
    {
        $name     = trim($request->post('name', ''));
        $username     = trim($request->post('username', ''));
        $email    = trim($request->post('email', ''));
        $password = $request->post('password', '');

        if (!$name || !$username || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            Response::json(['error' => 'Invalid input'], 422);
        }

        if ($this->users->findByEmail($email)) {
            Response::json(['error' => 'Email already registered'], 409);
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $id = $this->users->create($username, $name, $email, $hashed);

        $this->mailer->sendWelcomeEmail($email, $name);

        Response::json(['data' => ['id' => $id]], 201);
    }
}
