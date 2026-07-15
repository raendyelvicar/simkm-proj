<?php
require '../config/db.php';

$res = $mysqli->query("
SELECT c.*, u.username
FROM chat_messages c
LEFT JOIN users u ON c.user_id = u.id
ORDER BY created_at DESC
");
?>

<table border="1">
<tr><th>User</th><th>Pesan</th><th>Waktu</th></tr>

<?php while($r = $res->fetch_assoc()): ?>
<tr>
<td><?= $r['username'] ?></td>
<td><?= htmlspecialchars($r['message']) ?></td>
<td><?= $r['created_at'] ?></td>
</tr>
<?php endwhile; ?>
</table>
