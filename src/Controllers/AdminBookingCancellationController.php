<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\BookingCancellationRequestRepository;
use App\Repositories\CounselingBookingRepository;
use App\Repositories\MonitoringPeriodRepository;
use App\Repositories\UserRepository;

// Admin review queue for student-initiated booking cancellations (see
// BookingController::cancel(), which files a request here instead of cancelling
// outright). Approving marks the booking Cancelled; rejecting restores whatever
// status it had before the request, so a rejected Confirmed booking keeps its
// chat/diary-share access intact.
class AdminBookingCancellationController
{
    private const PER_PAGE = 10;

    private BookingCancellationRequestRepository $requests;
    private CounselingBookingRepository $bookings;
    private MonitoringPeriodRepository $monitoring;
    private UserRepository $users;

    public function __construct()
    {
        AuthMiddleware::handle();

        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('Forbidden: admin only.');
        }

        $this->requests = new BookingCancellationRequestRepository();
        $this->bookings = new CounselingBookingRepository();
        $this->monitoring = new MonitoringPeriodRepository();
        $this->users = new UserRepository();
    }

    // GET /admin/booking-cancellations
    public function index(Request $request): void
    {
        $filters = ['search' => trim((string) $request->get('q', ''))];
        $sort = (string) $request->get('sort', 'requested_at');
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $page = max(1, (int) $request->get('page', 1));

        $result = $this->requests->paginatedPending($filters, $sort, $dir, $page, self::PER_PAGE);
        $totalPages = (int) max(1, ceil($result['total'] / self::PER_PAGE));

        Response::view('admin/booking-cancellations/index', [
            'title'      => 'Persetujuan Pembatalan Booking',
            'requests'   => $result['items'],
            'total'      => $result['total'],
            'page'       => $page,
            'totalPages' => $totalPages,
            'sort'       => $sort,
            'dir'        => $dir,
            'filters'    => $filters,
        ]);
    }

    // POST /admin/booking-cancellations/{id}/approve
    public function approve(Request $request, string $id): void
    {
        $cancelRequest = $this->requests->findPendingById((int) $id);

        if (!$cancelRequest) {
            $_SESSION['error'] = 'Permintaan tidak ditemukan atau sudah diproses.';
            Response::redirect('/admin/booking-cancellations');
            return;
        }

        $booking = $this->bookings->findById($cancelRequest->bookingId);

        $this->bookings->updateStatus($cancelRequest->bookingId, 'Cancelled');
        if ($booking && $cancelRequest->previousStatus === 'Confirmed') {
            $this->monitoring->endNowForBooking($cancelRequest->bookingId, $booking->counselorId);
        }
        $this->requests->approve($cancelRequest->id, (int) $_SESSION['user_id']);

        if ($booking) {
            $this->notifyStudent($booking->userId, true, null);
        }

        $_SESSION['success'] = 'Pembatalan booking disetujui.';
        Response::redirect('/admin/booking-cancellations');
    }

    // POST /admin/booking-cancellations/{id}/reject
    public function reject(Request $request, string $id): void
    {
        $cancelRequest = $this->requests->findPendingById((int) $id);

        if (!$cancelRequest) {
            $_SESSION['error'] = 'Permintaan tidak ditemukan atau sudah diproses.';
            Response::redirect('/admin/booking-cancellations');
            return;
        }

        $adminNotes = trim($request->post('admin_notes', '')) ?: null;
        $booking = $this->bookings->findById($cancelRequest->bookingId);

        $this->bookings->updateStatus($cancelRequest->bookingId, $cancelRequest->previousStatus);
        $this->requests->reject($cancelRequest->id, (int) $_SESSION['user_id'], $adminNotes);

        if ($booking) {
            $this->notifyStudent($booking->userId, false, $adminNotes);
        }

        $_SESSION['success'] = 'Permintaan pembatalan booking ditolak.';
        Response::redirect('/admin/booking-cancellations');
    }

    private function notifyStudent(int $userId, bool $approved, ?string $adminNotes): void
    {
        $student = $this->users->find($userId);
        if (!$student || $student->email === '') {
            return;
        }

        require_once __DIR__ . '/../../config/send_email.php';

        $bookingsUrl = rtrim(env('APP_URL', ''), '/') . '/bookings';

        if ($approved) {
            $subject = 'Pembatalan Booking Disetujui';
            $message = "Halo {$student->name},\n\n"
                . "Permintaan pembatalan booking konsultasi Anda telah disetujui oleh Admin. Booking tersebut kini berstatus Dibatalkan.\n\n"
                . "Lihat booking Anda di: {$bookingsUrl}";
        } else {
            $subject = 'Pembatalan Booking Ditolak';
            $message = "Halo {$student->name},\n\n"
                . "Permintaan pembatalan booking konsultasi Anda ditolak oleh Admin. Booking Anda tetap berjalan seperti semula.\n"
                . ($adminNotes ? "\nCatatan Admin: {$adminNotes}\n" : '')
                . "\nLihat booking Anda di: {$bookingsUrl}";
        }

        try {
            kirimEmail($student->email, $subject, $message);
        } catch (\Throwable $e) {
            error_log('AdminBookingCancellationController::notifyStudent failed for user ' . $userId . ': ' . $e->getMessage());
        }
    }
}
