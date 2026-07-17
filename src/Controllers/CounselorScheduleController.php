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
        Response::view('schedule/index', [
            'title' => 'Jadwal Konsultasi',
            'slots' => array_map(fn ($s) => $s->toArray(), $this->jadwals->allByKonselorId($this->konselorId)),
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
