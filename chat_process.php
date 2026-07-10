<?php
session_start();

require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'mahasiswa';

/* =========================
   LOAD CHAT
   ========================= */
if (isset($_GET['load'])) {

    $receiver_id = (int) ($_GET['receiver_id'] ?? 0);

    $query = $mysqli->query("

        SELECT
            chats.*,
            users.nama
        FROM chats

        JOIN users
            ON users.id = chats.sender_id

        WHERE

        (
            sender_id = '$user_id'
            AND receiver_id = '$receiver_id'
        )

        OR

        (
            sender_id = '$receiver_id'
            AND receiver_id = '$user_id'
        )

        ORDER BY chats.created_at ASC

    ");

    while($row = $query->fetch_assoc()){

        $class =
            ($row['sender_id'] == $user_id)
            ? 'user'
            : 'konselor';

        echo '

        <div class="msg '.$class.'">

            <strong>
                '.htmlspecialchars($row['nama']).'
            </strong>

            <br>

            '.nl2br(htmlspecialchars($row['message'])).'

            <br><small>

                '.date(
                    'd/m/Y H:i',
                    strtotime($row['created_at'])
                ).'

            </small>

        </div>

        ';
    }

    exit;
}

/* =========================
   KIRIM PESAN
   ========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $message = trim($_POST['message'] ?? '');

    $receiver_id = (int) ($_POST['receiver_id'] ?? 0);

    if ($message != '') {

        $stmt = $mysqli->prepare("

            INSERT INTO chats
            (
                sender_id,
                receiver_id,
                message
            )

            VALUES (?, ?, ?)

        ");

        $stmt->bind_param(
            "iis",
            $user_id,
            $receiver_id,
            $message
        );

        $stmt->execute();
    }
}
?>