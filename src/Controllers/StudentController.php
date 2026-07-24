<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\UserRepository;

class StudentController
{
    private const PER_PAGE = 10;

    private UserRepository $users;

    public function __construct()
    {
        AuthMiddleware::handle();

        // This view exposes a roster of students, so it should be limited to
        // counselor/admin roles, not visible to a regular student account.
        if (($_SESSION['role'] ?? '') !== 'counselor' && ($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('Forbidden: counselor/admin only.');
        }

        $this->users = new UserRepository();
    }

    // GET /students
    public function index(Request $request): void
    {
        $filters = [
            'search'   => trim((string) $request->get('q', '')),
            'faculty' => $request->get('faculty') ?: null,
            'major'  => $request->get('major') ?: null,
            'status'   => $request->get('status') ?: null,
        ];
        $sort = (string) $request->get('sort', 'created_at');
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $page = max(1, (int) $request->get('page', 1));

        $result = $this->users->paginatedStudent($filters, $sort, $dir, $page, self::PER_PAGE);
        $totalPages = (int) max(1, ceil($result['total'] / self::PER_PAGE));

        Response::view('students/index', [
            'title'           => 'Data Student',
            'students'        => $result['items'],
            'total'           => $result['total'],
            'page'            => $page,
            'totalPages'      => $totalPages,
            'sort'            => $sort,
            'dir'             => $dir,
            'filters'         => $filters,
            'facultyOptions' => array_keys($this->users->countByFaculty()),
            'majorOptions'  => $this->users->distinctMajor(),
        ]);
    }
}
