<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\ChatMessage;
use mysqli;

class ChatRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    // Full conversation between two users, oldest first.
    public function conversation(int $userA, int $userB): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM chat_messages
             WHERE (user_id = ? AND receiver_id = ?) OR (user_id = ? AND receiver_id = ?)
             ORDER BY created_at ASC, id ASC'
        );
        $stmt->bind_param('iiii', $userA, $userB, $userB, $userA);
        $stmt->execute();

        return $this->hydrateAll($stmt->get_result());
    }

    // Messages sent after $afterId — used to poll for new messages without reloading the page.
    public function conversationSince(int $userA, int $userB, int $afterId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM chat_messages
             WHERE ((user_id = ? AND receiver_id = ?) OR (user_id = ? AND receiver_id = ?))
                AND id > ?
             ORDER BY created_at ASC, id ASC'
        );
        $stmt->bind_param('iiiii', $userA, $userB, $userB, $userA, $afterId);
        $stmt->execute();

        return $this->hydrateAll($stmt->get_result());
    }

    public function send(int $senderId, int $receiverId, string $message): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO chat_messages (user_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())'
        );
        $stmt->bind_param('iis', $senderId, $receiverId, $message);
        $stmt->execute();

        return (int) $this->db->insert_id;
    }

    // Distinct students who have exchanged messages with this counselor, each with
    // their last message and unread count, most recently active first.
    public function threadsForCounselor(int $counselorId): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT IF(user_id = ?, receiver_id, user_id) AS student_id
             FROM chat_messages
             WHERE user_id = ? OR receiver_id = ?'
        );
        $stmt->bind_param('iii', $counselorId, $counselorId, $counselorId);
        $stmt->execute();
        $result = $stmt->get_result();

        $threads = [];
        while ($row = $result->fetch_assoc()) {
            $studentId = (int) $row['student_id'];
            if ($studentId !== $counselorId) {
                $threads[] = $this->threadSummary($counselorId, $studentId);
            }
        }

        usort($threads, fn ($a, $b) => strcmp($b['last_message_at'] ?? '', $a['last_message_at'] ?? ''));

        return $threads;
    }

    public function markRead(int $studentId, int $counselorId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE chat_messages SET is_read = 1 WHERE user_id = ? AND receiver_id = ? AND is_read = 0'
        );
        $stmt->bind_param('ii', $studentId, $counselorId);
        $stmt->execute();
    }

    private function threadSummary(int $counselorId, int $studentId): array
    {
        $stmt = $this->db->prepare('SELECT nama, profile_image AS profile FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc() ?: [];

        $stmt = $this->db->prepare(
            'SELECT message, created_at FROM chat_messages
             WHERE (user_id = ? AND receiver_id = ?) OR (user_id = ? AND receiver_id = ?)
             ORDER BY created_at DESC, id DESC LIMIT 1'
        );
        $stmt->bind_param('iiii', $studentId, $counselorId, $counselorId, $studentId);
        $stmt->execute();
        $last = $stmt->get_result()->fetch_assoc() ?: [];

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS unread FROM chat_messages WHERE user_id = ? AND receiver_id = ? AND is_read = 0'
        );
        $stmt->bind_param('ii', $studentId, $counselorId);
        $stmt->execute();
        $unread = (int) ($stmt->get_result()->fetch_assoc()['unread'] ?? 0);

        return [
            'student_id'      => $studentId,
            'nama'            => $user['nama'] ?? '',
            'profile'         => $user['profile'] ?? '',
            'last_message'    => $last['message'] ?? '',
            'last_message_at' => $last['created_at'] ?? '',
            'unread_count'    => $unread,
        ];
    }

    private function hydrateAll(\mysqli_result $result): array
    {
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = new ChatMessage($row);
        }

        return $messages;
    }
}
