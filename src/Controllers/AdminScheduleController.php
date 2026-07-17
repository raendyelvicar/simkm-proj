<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\CounselorRepository;
use App\Repositories\KonselorJadwalRepository;

// Admin-only: add and manage a specific counselor's bookable schedule slots.
// Reachable from the "Jadwal" link on that counselor's row in /admin/counselors.
class AdminScheduleController
{
    private CounselorRepository $counselors;
    private KonselorJadwalRepository $jadwals;

    public function __construct()
    {
        AuthMiddleware::handle();

        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('Forbidden: admin only.');
        }

        $this->counselors = new CounselorRepository();
        $this->jadwals = new KonselorJadwalRepository();
    }

    // GET /admin/counselors/{id}/schedule — $id is the counselor's users.id.
    public function index(Request $request, string $id): void
    {
        $counselor = $this->findBookableCounselorOr404($id);
        if (!$counselor) {
            return;
        }

        Response::view('admin/counselors/schedule', [
            'title' => 'Jadwal ' . ($counselor['nama'] ?: 'Konselor'),
            'counselor' => $counselor,
            'slots' => array_map(fn ($s) => $s->toArray(), $this->jadwals->allByKonselorId((int) $counselor['konselor_id'])),
        ]);
    }

    // POST /admin/counselors/{id}/schedule
    public function store(Request $request, string $id): void
    {
        $counselor = $this->findBookableCounselorOr404($id);
        if (!$counselor) {
            return;
        }

        $tanggal = trim($request->post('tanggal', ''));
        $jamMulai = trim($request->post('jam_mulai', ''));
        $jamSelesai = trim($request->post('jam_selesai', ''));
        $kuota = (int) $request->post('kuota', 0);

        $errors = [];

        $date = \DateTime::createFromFormat('Y-m-d', $tanggal);
        if (!$date || $date->format('Y-m-d') !== $tanggal) {
            $errors[] = 'Tanggal tidak valid.';
        } elseif ($date < new \DateTime('today')) {
            $errors[] = 'Tanggal tidak boleh di masa lalu.';
        }

        if ($jamMulai === '' || $jamSelesai === '' || $jamMulai >= $jamSelesai) {
            $errors[] = 'Jam mulai harus lebih awal dari jam selesai.';
        }
        if ($kuota < 1) {
            $errors[] = 'Kuota minimal 1.';
        }

        if ($errors) {
            Response::view('admin/counselors/schedule', [
                'title' => 'Jadwal ' . ($counselor['nama'] ?: 'Konselor'),
                'counselor' => $counselor,
                'slots' => array_map(fn ($s) => $s->toArray(), $this->jadwals->allByKonselorId((int) $counselor['konselor_id'])),
                'errors' => $errors,
                'old' => compact('tanggal', 'jamMulai', 'jamSelesai', 'kuota'),
            ]);
            return;
        }

        $this->jadwals->create((int) $counselor['konselor_id'], $tanggal, $jamMulai, $jamSelesai, $kuota);

        $_SESSION['success'] = 'Jadwal berhasil ditambahkan.';
        Response::redirect('/admin/counselors/' . $id . '/schedule');
    }

    // POST /admin/counselors/{id}/schedule/{jadwalId}/toggle
    public function toggle(Request $request, string $id, string $jadwalId): void
    {
        $counselor = $this->findBookableCounselorOr404($id);
        if (!$counselor) {
            return;
        }

        $slot = $this->jadwals->findOwned((int) $jadwalId, (int) $counselor['konselor_id']);

        if ($slot) {
            $this->jadwals->setActive((int) $jadwalId, !$slot->statusAktif);
        }

        Response::redirect('/admin/counselors/' . $id . '/schedule');
    }

    // A counselor that exists and has a completed profile (konselor_id > 0) — the only kind schedulable.
    private function findBookableCounselorOr404(string $id): ?array
    {
        $counselor = $this->counselors->find((int) $id);

        if (!$counselor || (int) $counselor['konselor_id'] === 0) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Konselor Tidak Ditemukan']);
            return null;
        }

        return $counselor;
    }
}
