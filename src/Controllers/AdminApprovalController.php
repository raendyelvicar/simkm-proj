<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\UserRepository;

// Admin review queue for self-registered mahasiswa accounts (status=pending).
// Approving/rejecting notifies the user by email; a failed email must never
// block the status change itself, since that's the record of truth.
class AdminApprovalController
{
    private const PER_PAGE = 10;

    private UserRepository $users;

    public function __construct()
    {
        AuthMiddleware::handle();

        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('Forbidden: admin only.');
        }

        $this->users = new UserRepository();
    }

    // GET /admin/approvals
    public function index(Request $request): void
    {
        $filters = [
            'search'   => trim((string) $request->get('q', '')),
            'fakultas' => $request->get('fakultas') ?: null,
        ];
        $sort = (string) $request->get('sort', 'created_at');
        $dir = $request->get('dir') === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int) $request->get('page', 1));

        $result = $this->users->paginatedPendingMahasiswa($filters, $sort, $dir, $page, self::PER_PAGE);
        $totalPages = (int) max(1, ceil($result['total'] / self::PER_PAGE));

        Response::view('admin/approvals/index', [
            'title'      => 'Persetujuan Akun',
            'pending'    => $result['items'],
            'total'      => $result['total'],
            'page'       => $page,
            'totalPages' => $totalPages,
            'sort'       => $sort,
            'dir'        => $dir,
            'filters'    => $filters,
        ]);
    }

    // POST /admin/approvals/{id}/approve
    public function approve(Request $request, string $id): void
    {
        $user = $this->users->find((int) $id);

        if (!$user || $user->status !== 'pending') {
            $_SESSION['error'] = 'Akun tidak ditemukan atau sudah diproses.';
            Response::redirect('/admin/approvals');
            return;
        }

        $this->users->approve((int) $id, (int) $_SESSION['user_id']);
        $this->notifyUser($user, true);

        $_SESSION['success'] = 'Akun ' . $user->nama . ' berhasil disetujui.';
        Response::redirect('/admin/approvals');
    }

    // POST /admin/approvals/{id}/reject
    public function reject(Request $request, string $id): void
    {
        $user = $this->users->find((int) $id);

        if (!$user || $user->status !== 'pending') {
            $_SESSION['error'] = 'Akun tidak ditemukan atau sudah diproses.';
            Response::redirect('/admin/approvals');
            return;
        }

        $this->users->reject((int) $id, (int) $_SESSION['user_id']);
        $this->notifyUser($user, false);

        $_SESSION['success'] = 'Akun ' . $user->nama . ' berhasil ditolak.';
        Response::redirect('/admin/approvals');
    }

    private function notifyUser(\App\Models\User $user, bool $approved): void
    {
        if ($user->email === '') {
            return;
        }

        require_once __DIR__ . '/../../config/send_email.php';

        $loginUrl = rtrim(env('APP_URL', ''), '/') . '/login';

        if ($approved) {
            $subject = 'Akun Anda Telah Disetujui';
            $message = "Halo {$user->nama},\n\n"
                . "Akun Anda di Sistem Informasi Manajemen Kesehatan Mental (SIMKM) telah disetujui oleh Admin.\n"
                . "Anda sekarang dapat login menggunakan username: {$user->username}\n\n"
                . "Login di: {$loginUrl}";
        } else {
            $subject = 'Pendaftaran Anda Ditolak';
            $message = "Halo {$user->nama},\n\n"
                . "Mohon maaf, pendaftaran akun Anda di SIMKM (username: {$user->username}) tidak disetujui oleh Admin.\n"
                . "Silakan hubungi Admin apabila Anda merasa ini keliru.";
        }

        try {
            kirimEmail($user->email, $subject, $message);
        } catch (\Throwable $e) {
            error_log('notifyUser (approval) failed for user ' . $user->id . ': ' . $e->getMessage());
        }
    }
}
