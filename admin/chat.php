<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require '../config/db.php';

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'mahasiswa';

/* =========================
   CARI KONSELOR
   ========================= */

$konselor = $mysqli->query("
    SELECT id, nama, username
    FROM users
    WHERE role = 'konselor'
    LIMIT 1
");

$dataKonselor = $konselor->fetch_assoc();

$receiver_id   = $dataKonselor['id'] ?? 0;
$receiver_nama = $dataKonselor['nama'] 
                ?? $dataKonselor['username'] 
                ?? 'Konselor';
?>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/header.php'; ?>

<div class="d-flex">

<?php include __DIR__ . '/../dashboard_bootstrap/layout/sidebar.php'; ?>

<div class="content-wrapper p-4 w-100"
     style="background:#f3f4f6; min-height:100vh;">

    <div class="container-fluid">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">

                <h5 class="mb-0">
                    💬 Konseling Chat
                </h5>

                <span class="badge bg-light text-dark">
                    Chat dengan <?= htmlspecialchars($receiver_nama) ?>
                </span>

            </div>

            <div class="card-body">

                <!-- ================= CHAT BOX ================= -->
                <div id="messages"
                     class="messages mb-3">
                </div>

                <!-- ================= FORM ================= -->
                <form id="chatForm">

                    <input type="hidden"
                           id="receiver_id"
                           value="<?= $receiver_id ?>">

                    <div class="input-group">

                        <input type="text"
                               id="msg"
                               class="form-control"
                               placeholder="Tulis pesan..."
                               required>

                        <button type="submit"
                                class="btn btn-primary">

                            Kirim

                        </button>

                    </div>

                </form>

                <div class="mt-3">

                    <a href="../dashboard_bootstrap/dashboard_bootstrap.php"
                       class="btn btn-secondary btn-sm">

                        ⬅ Kembali

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>
</div>

<style>

.messages{

    height:450px;

    overflow-y:auto;

    padding:15px;

    border:1px solid #dee2e6;

    border-radius:12px;

    background:#ffffff;

    display:flex;

    flex-direction:column;
}

.msg{

    padding:12px;

    margin:8px 0;

    border-radius:12px;

    max-width:75%;

    word-wrap:break-word;
}

.user{

    background:#d1fae5;

    align-self:flex-end;
}

.konselor{

    background:#e0e7ff;

    align-self:flex-start;
}

.msg strong{

    display:block;

    margin-bottom:4px;
}

.msg small{

    display:block;

    margin-top:6px;

    opacity:0.7;
}

</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

function loadMessages(){

    let receiver = $("#receiver_id").val();

    $.get(

        "chat_process.php?load=1&receiver_id=" + receiver,

        function(data){

            $("#messages").html(data);

            $("#messages").scrollTop(
                $("#messages")[0].scrollHeight
            );

        }

    );

}

$("#chatForm").on("submit", function(e){

    e.preventDefault();

    let text = $("#msg").val();

    let receiver = $("#receiver_id").val();

    if(text.trim() === ''){
        return;
    }

    $("#msg").val("");

    $.post(

        "chat_process.php",

        {

            message:text,

            receiver_id:receiver

        },

        function(){

            loadMessages();

        }

    );

});

setInterval(loadMessages, 2000);

loadMessages();

</script>

<?php include __DIR__ . '/../dashboard_bootstrap/layout/footer.php'; ?>