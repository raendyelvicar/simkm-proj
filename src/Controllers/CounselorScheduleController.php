<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\CounselorRepository;
use App\Repositories\CounselorScheduleRepository;

// Counselor-only: view their own schedule and toggle slots active/inactive.
// Adding new slots is admin-only (see AdminScheduleController).
class CounselorScheduleController
{
    private const PER_PAGE = 10;

    private CounselorScheduleRepository $schedules;
    private int $counselorId;

    public function __construct()
    {
        AuthMiddleware::handle();

        if (($_SESSION['role'] ?? '') !== 'counselor') {
            http_response_code(403);
            exit('Forbidden: counselor only.');
        }

        $this->schedules = new CounselorScheduleRepository();

        $counselor = (new CounselorRepository())->find((int) $_SESSION['user_id']);
        $this->counselorId = (int) ($counselor['counselor_id'] ?? 0);

        if ($this->counselorId === 0) {
            $_SESSION['error'] = 'Lengkapi profil konselor kamu terlebih dahulu.';
            Response::redirect('/profile');
        }
    }

    // GET /schedule
    public function index(Request $request): void
    {
        $filters = [
            'date_from'    => $request->get('date_from') ?: null,
            'date_to'      => $request->get('date_to') ?: null,
            'is_active' => $request->get('is_active', ''),
        ];
        $sort = (string) $request->get('sort', 'date');
        $dir = $request->get('dir') === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int) $request->get('page', 1));

        $result = $this->schedules->paginatedByCounselorId($this->counselorId, $filters, $sort, $dir, $page, self::PER_PAGE);
        $totalPages = (int) max(1, ceil($result['total'] / self::PER_PAGE));

        Response::view('schedule/index', [
            'title'      => 'Schedule Konsultasi',
            'slots'      => $result['items'],
            'total'      => $result['total'],
            'page'       => $page,
            'totalPages' => $totalPages,
            'sort'       => $sort,
            'dir'        => $dir,
            'filters'    => $filters,
        ]);
    }

    // POST /schedule/{id}/toggle
    public function toggle(Request $request, string $id): void
    {
        $slot = $this->schedules->findOwned((int) $id, $this->counselorId);

        if ($slot) {
            $this->schedules->setActive((int) $id, !$slot->isActive);
        }

        Response::redirect('/schedule');
    }
}
