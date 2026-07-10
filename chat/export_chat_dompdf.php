<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

/* ===========================
   LOAD DOMPDF (Composer)
   =========================== */
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$user_id = (int)$_SESSION['user_id'];
$chat_id = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : 0;

if ($chat_id <= 0) {
    die("Chat ID tidak valid.");
}

/* ===============================
   AMBIL INFO CHAT
   =============================== */
$qChat = $mysqli->prepare("
    SELECT c.id, c.created_at, 
           u.nama AS mahasiswa, 
           k.nama AS konselor
    FROM chats c
    JOIN users u ON u.id = c.user_id
    JOIN users k ON k.id = c.konselor_id
    WHERE c.id = ? AND (c.user_id = ? OR c.konselor_id = ?)
");
$qChat->bind_param("iii", $chat_id, $user_id, $user_id);
$qChat->execute();
$chat = $qChat->get_result()->fetch_assoc();

if (!$chat) {
    die("Chat tidak ditemukan atau akses ditolak.");
}

/* ===============================
   AMBIL PESAN CHAT
   =============================== */
$qMsg = $mysqli->prepare("
    SELECT sender_role, message, created_at
    FROM chat_messages
    WHERE chat_id = ?
    ORDER BY created_at ASC
");
$qMsg->bind_param("i", $chat_id);
$qMsg->execute();
$msgs = $qMsg->get_result();

/* ===============================
   HTML PDF TEMPLATE
   =============================== */
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
    color: #111;
}
h1 {
    text-align: center;
    color: #0ea5a4;
    margin-bottom: 5px;
}
.sub {
    text-align: center;
    font-size: 11px;
    color: #555;
    margin-bottom: 20px;
}
.chat-box {
    margin-bottom: 10px;
}
.msg {
    padding: 8px 10px;
    border-radius: 8px;
    margin-bottom: 8px;
    max-width: 85%;
}
.user {
    background: #e0f2fe;
}
.konselor {
    background: #dcfce7;
}
.role {
    font-weight: bold;
    font-size: 11px;
}
.time {
    font-size: 10px;
    color: #666;
    margin-top: 4px;
}
.footer {
    margin-top: 25px;
    font-size: 10px;
    color: #555;
}
</style>
</head>
<body>

<h1>Laporan Percakapan Konseling</h1>
<div class="sub">
Mahasiswa: <strong>'.htmlspecialchars($chat['mahasiswa']).'</strong><br>
Konselor: <strong>'.htmlspecialchars($chat['konselor']).'</strong><br>
Tanggal Mulai: '.$chat['created_at'].'
</div>
';

/* ===============================
   LOOP PESAN
   =============================== */
while ($m = $msgs->fetch_assoc()) {
    $role = ($m['sender_role'] === 'mahasiswa') ? 'Mahasiswa' : 'Konselor';
    $class = ($m['sender_role'] === 'mahasiswa') ? 'user' : 'konselor';

    $html .= '
    <div class="chat-box">
        <div class="msg '.$class.'">
            <div class="role">'.$role.'</div>
            '.nl2br(htmlspecialchars($m['message'])).'
            <div class="time">'.$m['created_at'].'</div>
        </div>
    </div>
    ';
}

$html .= '
<div class="footer">
Dokumen ini bersifat rahasia. Dilarang menyebarluaskan tanpa izin.
</div>

</body>
</html>
';

/* ===============================
   GENERATE PDF
   =============================== */
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = "chat_konseling_{$chat_id}.pdf";
$dompdf->stream($filename, ["Attachment" => true]);
exit;
