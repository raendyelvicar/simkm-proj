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
        // TODO: add an UserRepository::updateProfile() method and validate input
        // before writing anything — this is a stub to wire the route first.
        Response::redirect('/profile');
    }
}
