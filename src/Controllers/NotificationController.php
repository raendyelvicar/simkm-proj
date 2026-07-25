<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\NotificationRepository;

// Full notification history + the endpoints backing the bell dropdown rendered
// in templates/layouts/index.php (every logged-in page, any role).
class NotificationController
{
    private const PER_PAGE = 20;

    private NotificationRepository $notifications;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->notifications = new NotificationRepository();
    }

    // GET /notifications
    public function index(Request $request): void
    {
        $page = max(1, (int) $request->get('page', 1));
        $result = $this->notifications->paginatedForUser((int) $_SESSION['user_id'], $page, self::PER_PAGE);
        $totalPages = (int) max(1, ceil($result['total'] / self::PER_PAGE));

        Response::view('notifications/index', [
            'title'       => 'Notifikasi',
            'notifications' => $result['items'],
            'total'       => $result['total'],
            'page'        => $page,
            'totalPages'  => $totalPages,
        ]);
    }

    // GET /notifications/{id}/open — marks it read, then sends the user on to
    // wherever it points (or back to the list if it has no link).
    public function open(Request $request, string $id): void
    {
        $userId = (int) $_SESSION['user_id'];
        $notification = $this->notifications->findOwned((int) $id, $userId);

        if (!$notification) {
            Response::redirect('/notifications');
            return;
        }

        $this->notifications->markRead($notification->id, $userId);
        Response::redirect($notification->link ?: '/notifications');
    }

    // POST /notifications/read-all
    public function readAll(Request $request): void
    {
        $this->notifications->markAllRead((int) $_SESSION['user_id']);
        Response::redirect($request->post('redirect_to') ?: '/notifications');
    }

    // GET /notifications/unread-count — polled by the bell badge in the layout.
    public function unreadCount(Request $request): void
    {
        Response::json(['count' => $this->notifications->unreadCountForUser((int) $_SESSION['user_id'])]);
    }
}
