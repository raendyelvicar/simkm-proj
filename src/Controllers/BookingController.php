<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\BookingCancellationRequestRepository;
use App\Repositories\CounselingBookingRepository;
use App\Repositories\CounselorRepository;
use App\Repositories\CounselorScheduleRepository;

// Student-facing: request a booking with a counselor, and manage the ones already sent.
// A booking must be confirmed by the counselor (see BookingQueueController) before chat unlocks.
class BookingController
{
    private const PER_PAGE = 10;

    private CounselingBookingRepository $bookings;
    private CounselorScheduleRepository $schedules;
    private CounselorRepository $counselors;
    private BookingCancellationRequestRepository $cancellationRequests;

    public function __construct()
    {
        AuthMiddleware::handle();

        if (($_SESSION['role'] ?? '') !== 'student') {
            http_response_code(403);
            exit('Forbidden: student only.');
        }

        $this->bookings = new CounselingBookingRepository();
        $this->schedules = new CounselorScheduleRepository();
        $this->counselors = new CounselorRepository();
        $this->cancellationRequests = new BookingCancellationRequestRepository();
    }

    // GET /bookings
    public function index(Request $request): void
    {
        $filters = ['status' => $request->get('status') ?: null];
        $sort = (string) $request->get('sort', 'date');
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $page = max(1, (int) $request->get('page', 1));

        $result = $this->bookings->paginatedForStudent((int) $_SESSION['user_id'], $filters, $sort, $dir, $page, self::PER_PAGE);
        $totalPages = (int) max(1, ceil($result['total'] / self::PER_PAGE));

        Response::view('booking/index', [
            'title'      => 'Booking Saya',
            'bookings'   => $result['items'],
            'total'      => $result['total'],
            'page'       => $page,
            'totalPages' => $totalPages,
            'sort'       => $sort,
            'dir'        => $dir,
            'filters'    => $filters,
        ]);
    }

    // GET /bookings/create/{counselorId} — $counselorId is the counselor's users.id.
    public function create(Request $request, string $counselorId): void
    {
        $counselor = $this->findBookableCounselorOr404($counselorId);
        if (!$counselor) {
            return;
        }

        Response::view('booking/create', [
            'title' => 'Ajukan Booking',
            'counselor' => $counselor,
            'slots' => $this->schedules->availableForBooking((int) $counselor['counselor_id']),
        ]);
    }

    // POST /bookings
    public function store(Request $request): void
    {
        $counselorId = (int) $request->post('counselor_id', 0);
        $counselor = $this->findBookableCounselorOr404((string) $counselorId);
        if (!$counselor) {
            return;
        }

        [$fields, $errors] = $this->validate($request, (int) $counselor['counselor_id']);

        if ($errors) {
            Response::view('booking/create', [
                'title' => 'Ajukan Booking',
                'counselor' => $counselor,
                'slots' => $this->schedules->availableForBooking((int) $counselor['counselor_id']),
                'errors' => $errors,
                'old' => $fields,
            ]);
            return;
        }

        $this->bookings->create(
            (int) $_SESSION['user_id'],
            (int) $counselor['counselor_id'],
            $fields['schedule_id'],
            $fields['date'],
            $fields['start_time'],
            $fields['end_time'],
            $fields['complaint']
        );

        $_SESSION['success'] = 'Booking berhasil diajukan. Menunggu konfirmasi counselor.';
        Response::redirect('/bookings');
    }

    // POST /bookings/{id}/cancel — no longer cancels outright: parks the booking in
    // 'Cancellation Requested' and files a request for Admin to approve or reject
    // (see AdminBookingCancellationController). Rejecting reverts the booking to
    // whatever status it had before (see BookingCancellationRequestRepository::create()).
    public function cancel(Request $request, string $id): void
    {
        $booking = $this->bookings->findOwnedByStudent((int) $id, (int) $_SESSION['user_id']);

        if ($booking && in_array($booking->status, ['Pending', 'Confirmed'], true)) {
            $reason = trim($request->post('reason', '')) ?: null;
            $this->cancellationRequests->create((int) $id, $booking->status, $reason);
            $this->bookings->updateStatus((int) $id, 'Cancellation Requested');
            $_SESSION['success'] = 'Permintaan pembatalan booking telah dikirim, menunggu persetujuan Admin.';
        }

        Response::redirect('/bookings');
    }

    // A counselor that exists and has a completed profile (counselor_id > 0) — the only kind bookable.
    private function findBookableCounselorOr404(string $counselorId): ?array
    {
        $counselor = $this->counselors->find((int) $counselorId);

        if (!$counselor || (int) $counselor['counselor_id'] === 0) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Konselor Tidak Ditemukan']);
            return null;
        }

        return $counselor;
    }

    // $scheduleId already carries its own date — re-validated defensively here since the
    // picker list on the form can go stale between page load and submit.
    private function validate(Request $request, int $counselorId): array
    {
        $scheduleId = (int) $request->post('schedule_id', 0);
        $complaint = trim($request->post('complaint', '')) ?: null;

        $fields = [
            'schedule_id' => $scheduleId,
            'date' => null,
            'start_time' => null,
            'end_time' => null,
            'complaint' => $complaint,
        ];

        $errors = [];

        $slot = $scheduleId ? $this->schedules->findOwned($scheduleId, $counselorId) : null;

        if (!$slot || !$slot->isActive) {
            $errors[] = 'Pilih jadwal konsultasi yang valid.';
        } elseif ($slot->date < date('Y-m-d')) {
            $errors[] = 'Jadwal yang dipilih sudah lewat, silakan pilih jadwal lain.';
        }

        if (!$errors && $this->bookings->hasOpenBooking((int) $_SESSION['user_id'], $counselorId)) {
            $errors[] = 'Kamu masih punya booking yang berjalan dengan konselor ini.';
        }

        if (!$errors && $slot && !$this->bookings->hasCapacity($scheduleId)) {
            $errors[] = 'Kuota jadwal ini sudah penuh, silakan pilih jadwal lain.';
        }

        if (!$errors && $slot) {
            $fields['date'] = $slot->date;
            $fields['start_time'] = $slot->jamMulai;
            $fields['end_time'] = $slot->jamSelesai;
        }

        return [$fields, $errors];
    }
}
