<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\CounselorRepository;
use App\Repositories\KonselorJadwalRepository;

// Konselor-only: view their own schedule and toggle slots active/inactive.
// Adding new slots is admin-only (see AdminScheduleController).
class CounselorScheduleController
{
    private const PER_PAGE = 10;

    private KonselorJadwalRepository $jadwals;
    private int $konselorId;

    public function __construct()
    {
        AuthMiddleware::handle();

        if (($_SESSION['role'] ?? '') !== 'konselor') {
            http_response_code(403);
            exit('Forbidden: konselor only.');
        }

        $this->jadwals = new KonselorJadwalRepository();

        $counselor = (new CounselorRepository())->find((int) $_SESSION['user_id']);
        $this->konselorId = (int) ($counselor['konselor_id'] ?? 0);

        if ($this->konselorId === 0) {
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
            'status_aktif' => $request->get('status_aktif', ''),
        ];
        $sort = (string) $request->get('sort', 'tanggal');
        $dir = $request->get('dir') === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int) $request->get('page', 1));

        $result = $this->jadwals->paginatedByKonselorId($this->konselorId, $filters, $sort, $dir, $page, self::PER_PAGE);
        $totalPages = (int) max(1, ceil($result['total'] / self::PER_PAGE));

        Response::view('schedule/index', [
            'title'      => 'Jadwal Konsultasi',
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
        $slot = $this->jadwals->findOwned((int) $id, $this->konselorId);

        if ($slot) {
            $this->jadwals->setActive((int) $id, !$slot->statusAktif);
        }

        Response::redirect('/schedule');
    }
}
