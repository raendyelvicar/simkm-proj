<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CounselorRepository;
use App\Repositories\MonitoringPeriodRepository;

class CounselorController
{
    private CounselorRepository $counselors;
    private MonitoringPeriodRepository $monitoring;

    public function __construct()
    {
        $this->counselors = new CounselorRepository();
        $this->monitoring = new MonitoringPeriodRepository();
    }

    // GET /counselor — public
    public function index(Request $request): void
    {
        $activeMonitoringKonselorIds = ($_SESSION['role'] ?? '') === 'mahasiswa'
            ? $this->monitoring->activeKonselorIdsForStudent((int) $_SESSION['user_id'])
            : [];

        Response::view('counselor/index', [
            'title' => 'Konselor',
            'counselors' => $this->counselors->all(),
            'activeMonitoringKonselorIds' => $activeMonitoringKonselorIds,
        ]);
    }

    // GET /counselor/{id} — public. $id is the counselor's users.id.
    public function show(Request $request, string $id): void
    {
        $counselor = $this->counselors->find((int) $id);

        if (!$counselor) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Konselor Tidak Ditemukan']);
            return;
        }

        $hasActiveMonitoring = ($_SESSION['role'] ?? '') === 'mahasiswa'
            && $this->monitoring->hasActive((int) $_SESSION['user_id'], (int) $counselor['konselor_id']);

        Response::view('counselor/show', [
            'title' => $counselor['nama'] ?: 'Detail Konselor',
            'counselor' => $counselor,
            'hasActiveMonitoring' => $hasActiveMonitoring,
        ]);
    }
}
