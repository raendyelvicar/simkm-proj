<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\CounselorRepository;
use App\Repositories\CounselorScheduleRepository;

// Admin-only: add and manage a specific counselor's bookable schedule slots.
// Reachable from the "Schedule" link on that counselor's row in /admin/counselors.
class AdminScheduleController
{
    private const PER_PAGE = 10;

    private CounselorRepository $counselors;
    private CounselorScheduleRepository $schedules;

    public function __construct()
    {
        AuthMiddleware::handle();

        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('Forbidden: admin only.');
        }

        $this->counselors = new CounselorRepository();
        $this->schedules = new CounselorScheduleRepository();
    }

    // GET /admin/counselors/{id}/schedule — $id is the counselor's users.id.
    public function index(Request $request, string $id): void
    {
        $counselor = $this->findBookableCounselorOr404($id);
        if (!$counselor) {
            return;
        }

        Response::view('admin/counselors/schedule', array_merge(
            ['title' => 'Schedule ' . ($counselor['name'] ?: 'Counselor'), 'counselor' => $counselor],
            $this->scheduleViewData($request, (int) $counselor['counselor_id'])
        ));
    }

    // Shared by index() and store()'s error-redisplay branch.
    private function scheduleViewData(Request $request, int $counselorId): array
    {
        $filters = [
            'date_from'    => $request->get('date_from') ?: null,
            'date_to'      => $request->get('date_to') ?: null,
            'is_active' => $request->get('is_active', ''),
        ];
        $sort = (string) $request->get('sort', 'date');
        $dir = $request->get('dir') === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int) $request->get('page', 1));

        $result = $this->schedules->paginatedByCounselorId($counselorId, $filters, $sort, $dir, $page, self::PER_PAGE);
        $totalPages = (int) max(1, ceil($result['total'] / self::PER_PAGE));

        return [
            'slots'      => $result['items'],
            'total'      => $result['total'],
            'page'       => $page,
            'totalPages' => $totalPages,
            'sort'       => $sort,
            'dir'        => $dir,
            'filters'    => $filters,
        ];
    }

    // POST /admin/counselors/{id}/schedule
    public function store(Request $request, string $id): void
    {
        $counselor = $this->findBookableCounselorOr404($id);
        if (!$counselor) {
            return;
        }

        $date = trim($request->post('date', ''));
        $jamMulai = trim($request->post('start_time', ''));
        $jamSelesai = trim($request->post('end_time', ''));
        $quota = (int) $request->post('quota', 0);

        $errors = [];

        $parsedDate = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date) {
            $errors[] = 'Tanggal tidak valid.';
        } elseif ($parsedDate < new \DateTime('today')) {
            $errors[] = 'Tanggal tidak boleh di masa lalu.';
        }

        if ($jamMulai === '' || $jamSelesai === '' || $jamMulai >= $jamSelesai) {
            $errors[] = 'Jam mulai harus lebih awal dari jam selesai.';
        }
        if ($quota < 1) {
            $errors[] = 'Quota minimal 1.';
        }

        if ($errors) {
            Response::view('admin/counselors/schedule', array_merge(
                [
                    'title' => 'Schedule ' . ($counselor['name'] ?: 'Counselor'),
                    'counselor' => $counselor,
                    'errors' => $errors,
                    'old' => compact('date', 'jamMulai', 'jamSelesai', 'quota'),
                ],
                $this->scheduleViewData($request, (int) $counselor['counselor_id'])
            ));
            return;
        }

        $this->schedules->create((int) $counselor['counselor_id'], $date, $jamMulai, $jamSelesai, $quota);

        $_SESSION['success'] = 'Schedule berhasil ditambahkan.';
        Response::redirect('/admin/counselors/' . $id . '/schedule');
    }

    // POST /admin/counselors/{id}/schedule/{scheduleId}/toggle
    public function toggle(Request $request, string $id, string $scheduleId): void
    {
        $counselor = $this->findBookableCounselorOr404($id);
        if (!$counselor) {
            return;
        }

        $slot = $this->schedules->findOwned((int) $scheduleId, (int) $counselor['counselor_id']);

        if ($slot) {
            $this->schedules->setActive((int) $scheduleId, !$slot->isActive);
        }

        Response::redirect('/admin/counselors/' . $id . '/schedule');
    }

    // A counselor that exists and has a completed profile (counselor_id > 0) — the only kind schedulable.
    private function findBookableCounselorOr404(string $id): ?array
    {
        $counselor = $this->counselors->find((int) $id);

        if (!$counselor || (int) $counselor['counselor_id'] === 0) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Konselor Tidak Ditemukan']);
            return null;
        }

        return $counselor;
    }
}
