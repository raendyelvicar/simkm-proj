<?php

namespace App\Services;

use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;

/**
 * Single place that knows how to phrase every in-app notification the app sends,
 * one method per business event. Controllers call these instead of building
 * notification rows by hand, the same way email-sending is centralized behind
 * kirimEmail() in config/send_email.php. Never lets a notification failure bubble
 * up and break the request that triggered it — same "best effort" rule the
 * existing email notifiers follow (see AdminApprovalController::notifyUser).
 */
class NotificationService
{
    private NotificationRepository $notifications;
    private UserRepository $users;

    public function __construct()
    {
        $this->notifications = new NotificationRepository();
        $this->users = new UserRepository();
    }

    // --- Booking ---------------------------------------------------------

    public function bookingCreated(int $counselorUserId, string $studentName, string $date, string $startTime): void
    {
        $this->send(
            $counselorUserId,
            'booking',
            'Permintaan Booking Baru',
            "{$studentName} mengajukan booking konsultasi pada " . $this->formatDate($date) . " pukul {$startTime}.",
            '/booking-requests'
        );
    }

    public function bookingConfirmed(int $studentUserId, string $counselorName, string $date, string $startTime, int $monitoringDays): void
    {
        $this->send(
            $studentUserId,
            'booking',
            'Booking Dikonfirmasi',
            "Booking konsultasi kamu dengan {$counselorName} pada " . $this->formatDate($date) . " pukul {$startTime} telah dikonfirmasi. Monitoring aktif selama {$monitoringDays} hari.",
            '/bookings'
        );
    }

    public function bookingRejected(int $studentUserId, string $counselorName): void
    {
        $this->send(
            $studentUserId,
            'booking',
            'Booking Ditolak',
            "Booking konsultasi kamu dengan {$counselorName} ditolak oleh konselor.",
            '/bookings'
        );
    }

    public function bookingCompleted(int $studentUserId, string $counselorName): void
    {
        $this->send(
            $studentUserId,
            'booking',
            'Konsultasi Selesai',
            "Sesi konsultasi kamu dengan {$counselorName} telah ditandai selesai.",
            '/bookings'
        );
    }

    public function bookingNoShow(int $studentUserId, string $counselorName): void
    {
        $this->send(
            $studentUserId,
            'booking',
            'Booking Ditandai Tidak Hadir',
            "Kamu ditandai tidak hadir pada sesi konsultasi dengan {$counselorName}.",
            '/bookings'
        );
    }

    /** @param int[] $adminUserIds */
    public function bookingCancellationRequested(array $adminUserIds, string $studentName, string $date): void
    {
        $this->sendToMany(
            $adminUserIds,
            'verification',
            'Permintaan Pembatalan Booking',
            "{$studentName} mengajukan pembatalan booking konsultasi pada " . $this->formatDate($date) . ". Menunggu persetujuan Admin.",
            '/admin/booking-cancellations'
        );
    }

    public function bookingCancellationApproved(int $studentUserId): void
    {
        $this->send(
            $studentUserId,
            'booking',
            'Pembatalan Booking Disetujui',
            'Permintaan pembatalan booking konsultasi kamu telah disetujui Admin. Booking tersebut kini berstatus Dibatalkan.',
            '/bookings'
        );
    }

    public function bookingCancellationRejected(int $studentUserId, ?string $adminNotes): void
    {
        $message = 'Permintaan pembatalan booking konsultasi kamu ditolak Admin. Booking kamu tetap berjalan seperti semula.';
        if ($adminNotes) {
            $message .= " Catatan Admin: {$adminNotes}";
        }

        $this->send($studentUserId, 'booking', 'Pembatalan Booking Ditolak', $message, '/bookings');
    }

    // --- Self-assessment ---------------------------------------------------

    public function assessmentResultReady(int $studentUserId, int $sessionId): void
    {
        $this->send(
            $studentUserId,
            'assessment',
            'Hasil Self-Assessment Siap',
            'Hasil self-assessment (BDI-II & PWB) kamu sudah selesai dihitung dan bisa dilihat sekarang.',
            '/assessment/session/complete/' . $sessionId
        );
    }

    public function retakeGranted(int $studentUserId): void
    {
        $this->send(
            $studentUserId,
            'assessment',
            'Self-Assessment Ulang Diberikan',
            'Konselor kamu merekomendasikan kamu mengisi ulang self-assessment untuk memantau perkembangan kondisimu.',
            '/assessment/start'
        );
    }

    // --- Content & consultation ---------------------------------------------

    /** @param int[] $studentUserIds */
    public function articlePublished(array $studentUserIds, string $articleTitle, int $articleId): void
    {
        $this->sendToMany(
            $studentUserIds,
            'article',
            'Artikel Baru',
            "Artikel baru \"{$articleTitle}\" telah dipublikasikan.",
            '/article/' . $articleId
        );
    }

    public function diaryShared(int $counselorUserId, string $studentName, string $entryDate, int $diaryId): void
    {
        $this->send(
            $counselorUserId,
            'diary',
            'Diary Dibagikan',
            "{$studentName} membagikan diary tanggal " . $this->formatDate($entryDate) . ' kepada kamu.',
            '/shared-diaries/' . $diaryId
        );
    }

    public function newChatMessage(int $counselorUserId, string $studentName, int $studentUserId): void
    {
        $this->send(
            $counselorUserId,
            'chat',
            'Pesan Konsultasi Baru',
            "{$studentName} mengirim pesan baru di konsultasi.",
            '/consultations/' . $studentUserId
        );
    }

    /** The student-facing counterpart of newChatMessage() — a counselor replying in the chat. */
    public function chatReplyReceived(int $studentUserId, string $counselorName, int $counselorUserId): void
    {
        $this->send(
            $studentUserId,
            'chat',
            'Balasan Konselor',
            "{$counselorName} membalas pesan konsultasimu.",
            '/chat/' . $counselorUserId
        );
    }

    // --- Account verification ----------------------------------------------

    /** @param int[] $adminUserIds */
    public function accountPendingRegistration(array $adminUserIds, string $studentName): void
    {
        $this->sendToMany(
            $adminUserIds,
            'verification',
            'Pendaftaran Mahasiswa Baru',
            "{$studentName} mendaftar dan menunggu persetujuan akun.",
            '/admin/approvals'
        );
    }

    public function accountApproved(int $userId): void
    {
        $this->send(
            $userId,
            'verification',
            'Akun Disetujui',
            'Akun kamu telah disetujui Admin. Selamat datang di SIMKM!',
            '/dashboard'
        );
    }

    // --- internals -----------------------------------------------------

    /** Every admin's users.id — the recipient list for admin-facing verification queues. */
    public function adminUserIds(): array
    {
        return array_map(fn ($user) => $user->id, $this->users->allByRole('admin'));
    }

    private function send(int $userId, string $type, string $title, string $message, ?string $link): void
    {
        try {
            $this->notifications->create($userId, $type, $title, $message, $link);
        } catch (\Throwable $e) {
            error_log('NotificationService::send failed for user ' . $userId . ': ' . $e->getMessage());
        }
    }

    private function sendToMany(array $userIds, string $type, string $title, string $message, ?string $link): void
    {
        if (!$userIds) {
            return;
        }

        try {
            $this->notifications->createForUsers($userIds, $type, $title, $message, $link);
        } catch (\Throwable $e) {
            error_log('NotificationService::sendToMany failed: ' . $e->getMessage());
        }
    }

    private function formatDate(string $date): string
    {
        $timestamp = strtotime($date);

        return $timestamp ? date('d M Y', $timestamp) : $date;
    }
}
