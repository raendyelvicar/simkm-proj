<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['konselor','admin'])) {
    header('Location: ../login.php');
    exit;
}

require '../config/db.php';

$user_id = (int)$_SESSION['user_id'];
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<style>

.chat-user{
    background:#f8fafc;
    border-radius:12px;
    padding:10px;
    margin-bottom:10px;
}

.chat-me{
    background:#198754;
    color:white;
    border-radius:12px;
    padding:10px;
    margin-bottom:10px;
}

.chat-box{
    height:450px;
    overflow-y:auto;
    background:#f1f5f9;
    border-radius:12px;
    padding:15px;
}

</style>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100">

<h4 class="mb-3">💬 Chat Konseling</h4>

<!-- PILIH USER CHAT -->
<form method="GET" class="mb-3">

<select name="student_id"
        class="form-control"
        onchange="this.form.submit()">

<option value="">
    -- Pilih Pengguna --
</option>

<?php

// ================= LIST USER =================
// Admin bisa chat ke mahasiswa + konselor
// Konselor bisa chat ke mahasiswa + admin

if($_SESSION['role'] == 'admin'){

    $students = $mysqli->query("
        SELECT id, username, role
        FROM users
        WHERE id != '$user_id'
        AND role IN ('mahasiswa','konselor')
        ORDER BY role ASC, username ASC
    ");

} else {

    $students = $mysqli->query("
        SELECT id, username, role
        FROM users
        WHERE id != '$user_id'
        AND role IN ('mahasiswa','admin')
        ORDER BY role ASC, username ASC
    ");

}

while($s = $students->fetch_assoc()):

?>

<option value="<?= $s['id'] ?>"
<?= (isset($_GET['student_id']) && $_GET['student_id']==$s['id']) ? 'selected' : '' ?>>

<?= htmlspecialchars($s['username']) ?>

(
<?= ucfirst($s['role']) ?>
)

</option>

<?php endwhile; ?>

</select>

</form>

<!-- CHAT BOX -->
<div class="card shadow-sm">
<div class="card-body chat-box" id="chatBox">

<?php
if(isset($_GET['student_id'])):

$student_id = (int)$_GET['student_id'];

$chat = $mysqli->query("

SELECT 
    c.*,
    u.username,
    u.role

FROM chat_messages c

LEFT JOIN users u
ON c.user_id = u.id

WHERE

(
    c.user_id = '$student_id'
    AND c.receiver_id = '$user_id'
)

OR

(
    c.user_id = '$user_id'
    AND c.receiver_id = '$student_id'
)

ORDER BY c.created_at ASC

");

while($c = $chat->fetch_assoc()):
?>

<div class="
<?= $c['user_id']==$user_id
    ? 'chat-me text-end'
    : 'chat-user'
?>">

<strong>

<?php if($c['user_id']==$user_id): ?>

    Saya

<?php else: ?>

    <?= htmlspecialchars($c['username']) ?>

    <small class="text-muted">
        (<?= ucfirst($c['role']) ?>)
    </small>

<?php endif; ?>

</strong>
<br>

<?= nl2br(htmlspecialchars($c['message'])) ?>

<div style="font-size:10px;">
<?= $c['created_at'] ?>
</div>

</div>

<?php endwhile; ?>

<?php else: ?>
<p class="text-muted">Pilih mahasiswa untuk mulai chat</p>
<?php endif; ?>

</div>
</div>

<!-- FORM KIRIM -->
<?php if(isset($_GET['student_id'])): ?>
<form method="POST" action="send.php" class="mt-3 d-flex gap-2">

<input type="hidden" name="receiver_id" value="<?= $student_id ?>">

<input type="text" name="message" class="form-control" placeholder="Ketik pesan..." required>

<button type="submit" class="btn btn-success">Kirim</button>

</form>
<?php endif; ?>

<br>
<a href="../dashboard_bootstrap/dashboard_bootstrap.php" class="btn btn-secondary">⬅ Kembali</a>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>

<script>
let chatBox = document.getElementById("chatBox");

function refreshChat(){
    if(!chatBox) return;
    fetch("fetch_chat.php?student_id=<?= isset($student_id)?$student_id:'' ?>")
    .then(res=>res.text())
    .then(html=>{
        chatBox.innerHTML = html;
        chatBox.scrollTop = chatBox.scrollHeight;
    });
}

setInterval(refreshChat, 5000);
</script>