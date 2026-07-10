<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require '../config/db.php';

$user_id = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);

$chat = $mysqli->query("
    SELECT c.*, u.username 
    FROM chat_messages c
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.user_id = '$user_id'
    ORDER BY c.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Chat</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body { font-family: Arial; background:#f3f4f6; margin:0; }
.header { background:#0ea5a4;color:white;padding:15px; }
.container { max-width:850px;margin:25px auto;background:white;
    padding:20px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.1);}
table { width:100%;border-collapse:collapse;margin-top:15px; }
th, td { padding:12px;border-bottom:1px solid #ddd;text-align:left; }
.back-btn { display:inline-block;padding:8px 14px;background:#065f46;
    color:white;text-decoration:none;border-radius:6px; }
.empty { text-align:center;padding:25px;font-size:15px;color:#555; }
</style>

</head>
<body>

<div class="header">
    <h2>📜 Riwayat Chat</h2>
</div>

<div class="container">

    <a href=" /AplikasiSkripsi/redirect_dashboard.php" class="btn">🔙 Kembali ke Dashboard</a>

    <h3>Riwayat Chat: <?= $username ?></h3>

    <?php if ($chat->num_rows > 0): ?>
    <table>
        <tr>
            <th>Pesan</th>
            <th>Waktu</th>
        </tr>

        <?php while($c = $chat->fetch_assoc()): ?>
        <tr>
            <td><?= nl2br(htmlspecialchars($c['message'])) ?></td>
            <td><?= $c['created_at'] ?></td>
        </tr>
        <?php endwhile; ?>

    </table>

    <?php else: ?>
        <div class="empty">Belum ada riwayat chat.</div>
    <?php endif; ?>

</div>

</body>
</html>
