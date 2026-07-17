<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\BookingKonselingRepository;
use App\Repositories\CounselorRepository;
use App\Repositories\MonitoringPeriodRepository;

// Konselor-only: review and respond to pending booking requests from students.
// Confirming a booking starts a monitoring period (see MonitoringPeriodRepository) —
// that's what unlocks chat + diary sharing for that student (see ChatController::hasAccess).
class BookingQueueController
{
    private const DEFAULT_DURATION_DAYS = 30;
    private const MIN_DAYS = 1;
    private const MAX_DAYS = 365;

    private BookingKonselingRepository $bookings;
    private MonitoringPeriodRepository $monitoring;
    private int $konselorId;

    public function __construct()
    {
        AuthMiddleware::handle();

        if (($_SESSION['role'] ?? '') !== 'konselor') {
            http_response_code(403);
            exit('Forbidden: konselor only.');
        }

        $this->bookings = new BookingKonselingRepository();
        $this->monitoring = new MonitoringPeriodRepository();

        $counselor = (new CounselorRepository())->find((int) $_SESSION['user_id']);
        $this->konselorId = (int) ($counselor['konselor_id'] ?? 0);

        if ($this->konselorId === 0) {
            $_SESSION['error'] = 'Lengkapi profil konselor kamu terlebih dahulu.';
            Response::redirect('/profile');
        }
    }

    // GET /booking-requests
    public function index(Request $request): void
    {
        Response::view('booking-requests/index', [
            'title' => 'Permintaan Booking',
            'bookings' => $this->bookings->forKonselorQueue($this->konselorId),
        ]);
    }

    // POST /booking-requests/{id}/confirm — also starts a monitoring period for a
    // counselor-chosen duration, which is the actual chat/diary-share gate.
    public function confirm(Request $request, string $id): void
    {
        $booking = $this->bookings->findOwnedByKonselor((int) $id, $this->konselorId);

        if ($booking && $booking->status === 'Pending') {
            $days = $this->clampDays($request->post('durasi_hari', self::DEFAULT_DURATION_DAYS));

            $this->bookings->updateStatus((int) $id, 'Confirmed');
            $this->monitoring->create(
                (int) $id,
                $booking->userId,
                $this->konselorId,
                date('Y-m-d'),
                date('Y-m-d', strtotime("+{$days} days"))
            );

            $_SESSION['success'] = "Booking dikonfirmasi. Monitoring aktif selama {$days} hari.";
        }

        Response::redirect('/booking-requests');
    }

    // POST /booking-requests/{id}/reject
    public function reject(Request $request, string $id): void
    {
        $booking = $this->bookings->findOwnedByKonselor((int) $id, $this->konselorId);

        if ($booking && $booking->status === 'Pending') {
            $this->bookings->updateStatus((int) $id, 'Cancelled');
            $_SESSION['success'] = 'Booking ditolak.';
        }

        Response::redirect('/booking-requests');
    }

    // POST /booking-requests/{id}/extend — pushes the monitoring window further out.
    public function extend(Request $request, string $id): void
    {
        $booking = $this->bookings->findOwnedByKonselor((int) $id, $this->konselorId);

        if ($booking && $booking->status === 'Confirmed') {
            $days = $this->clampDays($request->post('tambah_hari', 7));
            $this->monitoring->extend((int) $id, $this->konselorId, $days);
            $_SESSION['success'] = "Monitoring diperpanjang {$days} hari.";
        }

        Response::redirect('/booking-requests');
    }

    // POST /booking-requests/{id}/complete — closes out a confirmed session. This also
    // ends the monitoring period immediately, revoking chat/diary-share access for it.
    public function complete(Request $request, string $id): void
    {
        $booking = $this->bookings->findOwnedByKonselor((int) $id, $this->konselorId);

        if ($booking && $booking->status === 'Confirmed') {
            $this->bookings->updateStatus((int) $id, 'Completed');
            $this->monitoring->endNowForBooking((int) $id, $this->konselorId);
            $_SESSION['success'] = 'Booking ditandai selesai.';
        }

        Response::redirect('/booking-requests');
    }

    // POST /booking-requests/{id}/no-show — student never showed up; also ends monitoring.
    public function noShow(Request $request, string $id): void
    {
        $booking = $this->bookings->findOwnedByKonselor((int) $id, $this->konselorId);

        if ($booking && $booking->status === 'Confirmed') {
            $this->bookings->updateStatus((int) $id, 'No Show');
            $this->monitoring->endNowForBooking((int) $id, $this->konselorId);
            $_SESSION['success'] = 'Booking ditandai tidak hadir.';
        }

        Response::redirect('/booking-requests');
    }

    private function clampDays($value): int
    {
        $days = (int) $value;

        return max(self::MIN_DAYS, min(self::MAX_DAYS, $days ?: self::DEFAULT_DURATION_DAYS));
    }
}
