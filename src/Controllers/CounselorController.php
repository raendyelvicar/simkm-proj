<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CounselorRepository;
use App\Repositories\MonitoringPeriodRepository;

class CounselorController
{
    private const PER_PAGE = 9;

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

        $filters = [
            'search'            => trim((string) $request->get('q', '')),
            'profesi'           => $request->get('profesi') ?: null,
            'metode_konsultasi' => $request->get('metode_konsultasi') ?: null,
        ];
        [$sort, $dir] = $this->parseSort((string) $request->get('sort', 'nama:asc'));
        $page = max(1, (int) $request->get('page', 1));

        $result = $this->counselors->paginatedActive($filters, $sort, $dir, $page, self::PER_PAGE);
        $totalPages = (int) max(1, ceil($result['total'] / self::PER_PAGE));

        Response::view('counselor/index', [
            'title'                       => 'Konselor',
            'counselors'                  => $result['items'],
            'total'                       => $result['total'],
            'page'                        => $page,
            'totalPages'                  => $totalPages,
            'sort'                        => $sort,
            'dir'                         => $dir,
            'filters'                     => $filters,
            'activeMonitoringKonselorIds' => $activeMonitoringKonselorIds,
        ]);
    }

    private function parseSort(string $combined): array
    {
        [$sort, $dir] = array_pad(explode(':', $combined, 2), 2, 'asc');

        return [$sort, $dir === 'desc' ? 'desc' : 'asc'];
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
