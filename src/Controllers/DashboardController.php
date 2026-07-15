<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;

class DashboardController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    // GET /dashboard
    public function index(Request $request): void
    {
        Response::view('dashboard/index', [
            'title'    => 'Dashboard',
            'username' => $_SESSION['username'] ?? '',
            'role'     => $_SESSION['role'] ?? '',
        ]);
    }
}
