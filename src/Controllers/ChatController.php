<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\ChatRepository;
use App\Repositories\CounselorRepository;
use App\Repositories\MonitoringPeriodRepository;

class ChatController
{
    private ChatRepository $chats;
    private CounselorRepository $counselors;
    private MonitoringPeriodRepository $monitoring;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->chats = new ChatRepository();
        $this->counselors = new CounselorRepository();
        $this->monitoring = new MonitoringPeriodRepository();
    }

    // GET /chat/{counselorId}
    public function show(Request $request, string $counselorId): void
    {
        $counselor = $this->findCounselorOr404($counselorId);
        if (!$counselor) {
            return;
        }

        if (!$this->hasAccess($counselor)) {
            $_SESSION['error'] = 'Ajukan booking dan tunggu konfirmasi konselor sebelum memulai chat.';
            Response::redirect('/counselor/' . $counselorId);
            return;
        }

        $messages = $this->chats->conversation((int) $_SESSION['user_id'], $counselor['user_id']);

        Response::view('chat/index', [
            'title' => 'Chat dengan ' . ($counselor['nama'] ?: 'Konselor'),
            'counselor' => $counselor,
            'messages' => array_map(fn($message) => $message->toArray(), $messages),
        ]);
    }

    // POST /chat/{counselorId}
    public function send(Request $request, string $counselorId): void
    {
        $counselor = $this->counselors->find((int) $counselorId);
        $message = trim($request->post('message', ''));

        if ($counselor && $this->hasAccess($counselor) && $message !== '') {
            $this->chats->send((int) $_SESSION['user_id'], $counselor['user_id'], $message);
        }

        Response::redirect('/chat/' . $counselorId);
    }

    // GET /chat/{counselorId}/messages?after={id} — polled from the page to fetch new messages.
    public function messages(Request $request, string $counselorId): void
    {
        $counselor = $this->counselors->find((int) $counselorId);

        if (!$counselor) {
            Response::json(['messages' => []], 404);
            return;
        }

        if (!$this->hasAccess($counselor)) {
            Response::json(['messages' => []], 403);
            return;
        }

        $afterId = (int) $request->get('after', 0);
        $messages = $this->chats->conversationSince((int) $_SESSION['user_id'], $counselor['user_id'], $afterId);

        Response::json([
            'messages' => array_map(fn($message) => $message->toArray(), $messages),
        ]);
    }

    private function findCounselorOr404(string $counselorId): ?array
    {
        $counselor = $this->counselors->find((int) $counselorId);

        if (!$counselor) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Konselor Tidak Ditemukan']);
            return null;
        }

        return $counselor;
    }

    // True while this counselor has an active monitoring period with the logged-in student.
    private function hasAccess(array $counselor): bool
    {
        if ((int) $counselor['konselor_id'] === 0) {
            return false;
        }

        return $this->monitoring->hasActive((int) $_SESSION['user_id'], (int) $counselor['konselor_id']);
    }
}
