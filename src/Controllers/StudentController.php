<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\UserRepository;

class StudentController
{
    private UserRepository $users;

    public function __construct()
    {
        AuthMiddleware::handle();

        // This view exposes a roster of students, so it should be limited to
        // counselor/admin roles, not visible to a regular student account.
        if (($_SESSION['role'] ?? '') !== 'konselor' && ($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('Forbidden: konselor/admin only.');
        }

        $this->users = new UserRepository();
    }

    // GET /students
    public function index(Request $request): void
    {
        $students = array_map(fn ($student) => $student->toArray(), $this->users->allByRole('mahasiswa'));

        Response::view('students/index', ['title' => 'Data Mahasiswa', 'students' => $students]);
    }
}
