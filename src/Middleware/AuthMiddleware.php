<?php

namespace App\Middleware;

use App\Core\Response;

class AuthMiddleware
{
    public static function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            Response::redirect('/login');
        }
    }
}
