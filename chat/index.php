<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require __DIR__ . '/../config/db.php';

$role = $_SESSION['role'] ?? 'mahasiswa';
$user_id = (int)$_SESSION['user_id'];

// ambil konselor
$konselor = $mysqli->query("SELECT id, username FROM users WHERE role='konselor' LIMIT 1");
$k = $konselor->fetch_assoc();
$konselor_id = $k['id'];
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-3 w-100" style="background:#f8f9fa; min-height:100vh;">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-success text-white">
            <h5 class="mb-0">💬 Chat Konselor</h5>
        </div>

        <div class="card-body">

            <!-- CHAT BOX -->
            <div id="chatBox" style="height:400px; overflow-y:auto; background:#fff; padding:15px; border-radius:8px;">

                <?php
                $chat = $mysqli->query("
                SELECT c.*, u.username
                FROM chat_messages c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE 
                (c.user_id = $user_id AND c.receiver_id = $konselor_id)
                OR
                (c.user_id = $konselor_id AND c.receiver_id = $user_id)
                ORDER BY c.created_at ASC
                ");

                while($c = $chat->fetch_assoc()):
                ?>

                <div style="
                    margin-bottom:10px;
                    max-width:70%;
                    padding:10px;
                    border-radius:10px;
                    <?= $c['user_id'] == $user_id 
                        ? 'background:#0ea5a4;color:white;margin-left:auto;' 
                        : 'background:#e5e7eb;color:#111;' ?>
                ">
                    <strong style="font-size:12px;">
                        <?= $c['user_id'] == $user_id ? 'Saya' : 'Konselor' ?>
                    </strong>

                    <div><?= nl2br(htmlspecialchars($c['message'])) ?></div>

                    <small style="font-size:10px;">
                        <?= $c['created_at'] ?>
                    </small>
                </div>

                <?php endwhile; ?>

            </div>

            <!-- FORM -->
            <form method="POST" action="/AplikasiSkripsi/chat/send.php" class="d-flex gap-2 mt-3">

                <input type="hidden" name="receiver_id" value="<?= $konselor_id ?>">

                <input 
                    type="text" 
                    name="message" 
                    class="form-control" 
                    placeholder="Ketik pesan..." 
                    required
                >

                <button class="btn btn-success">Kirim</button>

            </form>

            <!-- BACK -->
            <a href="../dashboard_bootstrap/dashboard_bootstrap.php" class="btn btn-secondary mt-3">
                ⬅ Kembali
            </a>

        </div>
    </div>

</div>
</div>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>

<!-- AUTO SCROLL -->
<script>
let box = document.getElementById("chatBox");
box.scrollTop = box.scrollHeight;
</script>

<!-- AUTO REFRESH -->
<script>
setInterval(() => {
    location.reload();
}, 3000);
</script>