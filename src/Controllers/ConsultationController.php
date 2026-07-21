<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\ChatRepository;
use App\Repositories\UserRepository;

// Counselor-side inbox: lets a logged-in konselor see students who've
// messaged them and reply. Mirrors ChatController, which is the
// student-facing side of the same chat_messages table.
class ConsultationController
{
    private const PER_PAGE = 10;

    private ChatRepository $chats;
    private UserRepository $users;

    public function __construct()
    {
        AuthMiddleware::handle();

        if (($_SESSION['role'] ?? '') !== 'konselor') {
            http_response_code(403);
            exit('Forbidden: konselor only.');
        }

        $this->chats = new ChatRepository();
        $this->users = new UserRepository();
    }

    // GET /consultations
    //
    // threadsForCounselor() is an N+1-per-student query with no natural single-query
    // WHERE/LIMIT to push into (it starts from a DISTINCT over chat_messages, not a
    // students table) — bounded by "students who've ever messaged this konselor", not
    // unbounded message history, so search/sort/pagination are applied in PHP over the
    // already-materialized result rather than restructuring the query layer.
    public function index(Request $request): void
    {
        $search = trim((string) $request->get('q', ''));
        [$sort, $dir] = $this->parseSort((string) $request->get('sort', 'last_message_at:desc'));
        $page = max(1, (int) $request->get('page', 1));

        $threads = $this->chats->threadsForCounselor((int) $_SESSION['user_id']);

        if ($search !== '') {
            $threads = array_values(array_filter(
                $threads,
                fn ($t) => stripos($t['nama'] ?? '', $search) !== false
            ));
        }

        usort($threads, function ($a, $b) use ($sort, $dir) {
            $cmp = strcmp((string) ($a[$sort] ?? ''), (string) ($b[$sort] ?? ''));

            return $dir === 'asc' ? $cmp : -$cmp;
        });

        $total = count($threads);
        $totalPages = (int) max(1, ceil($total / self::PER_PAGE));
        $page = min($page, $totalPages);
        $pageThreads = array_slice($threads, ($page - 1) * self::PER_PAGE, self::PER_PAGE);

        Response::view('counselor/inbox', [
            'title'      => 'Konsultasi Masuk',
            'threads'    => $pageThreads,
            'total'      => $total,
            'page'       => $page,
            'totalPages' => $totalPages,
            'sort'       => $sort,
            'dir'        => $dir,
            'filters'    => ['search' => $search],
        ]);
    }

    private function parseSort(string $combined): array
    {
        [$sort, $dir] = array_pad(explode(':', $combined, 2), 2, 'desc');

        return [in_array($sort, ['last_message_at', 'nama'], true) ? $sort : 'last_message_at', $dir === 'asc' ? 'asc' : 'desc'];
    }

    // GET /consultations/{studentId}
    public function show(Request $request, string $studentId): void
    {
        $student = $this->findStudentOr404($studentId);
        if (!$student) {
            return;
        }

        $counselorId = (int) $_SESSION['user_id'];
        $this->chats->markRead((int) $studentId, $counselorId);

        $messages = $this->chats->conversation($counselorId, (int) $studentId);

        Response::view('counselor/thread', [
            'title' => 'Konsultasi dengan ' . ($student->nama ?: $student->username),
            'student' => $student->toArray(),
            'messages' => array_map(fn ($message) => $message->toArray(), $messages),
        ]);
    }

    // POST /consultations/{studentId}
    public function send(Request $request, string $studentId): void
    {
        $student = $this->users->find((int) $studentId);
        $message = trim($request->post('message', ''));

        if ($student && $message !== '') {
            $this->chats->send((int) $_SESSION['user_id'], (int) $studentId, $message);
        }

        Response::redirect('/consultations/' . $studentId);
    }

    // GET /consultations/{studentId}/messages?after={id} — polled from the page.
    public function messages(Request $request, string $studentId): void
    {
        $student = $this->users->find((int) $studentId);

        if (!$student) {
            Response::json(['messages' => []], 404);
            return;
        }

        $afterId = (int) $request->get('after', 0);
        $messages = $this->chats->conversationSince((int) $_SESSION['user_id'], (int) $studentId, $afterId);

        Response::json([
            'messages' => array_map(fn ($message) => $message->toArray(), $messages),
        ]);
    }

    private function findStudentOr404(string $studentId)
    {
        $student = $this->users->find((int) $studentId);

        if (!$student) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Mahasiswa Tidak Ditemukan']);
            return null;
        }

        return $student;
    }
}
