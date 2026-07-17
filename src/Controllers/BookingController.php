<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\BookingKonselingRepository;
use App\Repositories\CounselorRepository;
use App\Repositories\KonselorJadwalRepository;

// Mahasiswa-facing: request a booking with a counselor, and manage the ones already sent.
// A booking must be confirmed by the counselor (see BookingQueueController) before chat unlocks.
class BookingController
{
    private BookingKonselingRepository $bookings;
    private KonselorJadwalRepository $jadwals;
    private CounselorRepository $counselors;

    public function __construct()
    {
        AuthMiddleware::handle();

        if (($_SESSION['role'] ?? '') !== 'mahasiswa') {
            http_response_code(403);
            exit('Forbidden: mahasiswa only.');
        }

        $this->bookings = new BookingKonselingRepository();
        $this->jadwals = new KonselorJadwalRepository();
        $this->counselors = new CounselorRepository();
    }

    // GET /bookings
    public function index(Request $request): void
    {
        Response::view('booking/index', [
            'title' => 'Booking Saya',
            'bookings' => $this->bookings->allForStudent((int) $_SESSION['user_id']),
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
            'slots' => $this->jadwals->availableForBooking((int) $counselor['konselor_id']),
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

        [$fields, $errors] = $this->validate($request, (int) $counselor['konselor_id']);

        if ($errors) {
            Response::view('booking/create', [
                'title' => 'Ajukan Booking',
                'counselor' => $counselor,
                'slots' => $this->jadwals->availableForBooking((int) $counselor['konselor_id']),
                'errors' => $errors,
                'old' => $fields,
            ]);
            return;
        }

        $this->bookings->create(
            (int) $_SESSION['user_id'],
            (int) $counselor['konselor_id'],
            $fields['jadwal_id'],
            $fields['tanggal'],
            $fields['jam_mulai'],
            $fields['jam_selesai'],
            $fields['keluhan']
        );

        $_SESSION['success'] = 'Booking berhasil diajukan. Menunggu konfirmasi konselor.';
        Response::redirect('/bookings');
    }

    // POST /bookings/{id}/cancel
    public function cancel(Request $request, string $id): void
    {
        $booking = $this->bookings->findOwnedByStudent((int) $id, (int) $_SESSION['user_id']);

        if ($booking && in_array($booking->status, ['Pending', 'Confirmed'], true)) {
            $this->bookings->updateStatus((int) $id, 'Cancelled');
            $_SESSION['success'] = 'Booking dibatalkan.';
        }

        Response::redirect('/bookings');
    }

    // A counselor that exists and has a completed profile (konselor_id > 0) — the only kind bookable.
    private function findBookableCounselorOr404(string $counselorId): ?array
    {
        $counselor = $this->counselors->find((int) $counselorId);

        if (!$counselor || (int) $counselor['konselor_id'] === 0) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Konselor Tidak Ditemukan']);
            return null;
        }

        return $counselor;
    }

    // $jadwalId already carries its own date — re-validated defensively here since the
    // picker list on the form can go stale between page load and submit.
    private function validate(Request $request, int $konselorId): array
    {
        $jadwalId = (int) $request->post('jadwal_id', 0);
        $keluhan = trim($request->post('keluhan', '')) ?: null;

        $fields = [
            'jadwal_id' => $jadwalId,
            'tanggal' => null,
            'jam_mulai' => null,
            'jam_selesai' => null,
            'keluhan' => $keluhan,
        ];

        $errors = [];

        $slot = $jadwalId ? $this->jadwals->findOwned($jadwalId, $konselorId) : null;

        if (!$slot || !$slot->statusAktif) {
            $errors[] = 'Pilih jadwal konsultasi yang valid.';
        } elseif ($slot->tanggal < date('Y-m-d')) {
            $errors[] = 'Jadwal yang dipilih sudah lewat, silakan pilih jadwal lain.';
        }

        if (!$errors && $this->bookings->hasOpenBooking((int) $_SESSION['user_id'], $konselorId)) {
            $errors[] = 'Kamu masih punya booking yang berjalan dengan konselor ini.';
        }

        if (!$errors && $slot && !$this->bookings->hasCapacity($jadwalId)) {
            $errors[] = 'Kuota jadwal ini sudah penuh, silakan pilih jadwal lain.';
        }

        if (!$errors && $slot) {
            $fields['tanggal'] = $slot->tanggal;
            $fields['jam_mulai'] = $slot->jamMulai;
            $fields['jam_selesai'] = $slot->jamSelesai;
        }

        return [$fields, $errors];
    }
}
