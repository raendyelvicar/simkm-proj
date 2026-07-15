<?php ob_start(); ?>

<div class="chat-page">
    <a href="/counselor" class="chat-back">&larr; Kembali ke daftar konselor</a>

    <div class="chat-head">
        <div class="chat-head-info">
            <div class="counselor-avatar counselor-avatar-sm">
                <?php if (!empty($counselor['profile'])): ?>
                    <img src="<?= htmlspecialchars($counselor['profile']) ?>"
                        alt="<?= htmlspecialchars($counselor['nama']) ?>"
                        onerror="this.remove()">
                <?php endif; ?>
                <span class="counselor-avatar-initial"><?= htmlspecialchars(mb_strtoupper(mb_substr($counselor['nama'] !== '' ? $counselor['nama'] : '?', 0, 1))) ?></span>
            </div>
            <div>
                <h1><?= htmlspecialchars($counselor['nama'] !== '' ? $counselor['nama'] : 'Konselor') ?></h1>
                <?php if (!empty($counselor['spesialisasi'])): ?>
                    <span class="chat-subtitle"><?= htmlspecialchars($counselor['spesialisasi']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="chat-box" id="chatBox" data-counselor-id="<?= (int) $counselor['id'] ?>">
        <?php if (empty($messages)): ?>
            <p class="chat-empty">Belum ada percakapan. Mulai konsultasi dengan mengirim pesan di bawah.</p>
        <?php endif; ?>
        <?php foreach ($messages as $message): ?>
            <?php $mine = (int) $message['user_id'] === (int) $_SESSION['user_id']; ?>
            <div class="chat-bubble <?= $mine ? 'chat-bubble-mine' : 'chat-bubble-theirs' ?>" data-id="<?= (int) $message['id'] ?>">
                <div class="chat-bubble-text"><?= nl2br(htmlspecialchars($message['message'])) ?></div>
                <div class="chat-bubble-time"><?= htmlspecialchars($message['created_at'] ? date('d M Y H:i', strtotime($message['created_at'])) : '') ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="post" action="/chat/<?= (int) $counselor['id'] ?>" class="chat-form" id="chatForm">
        <input type="text" name="message" class="chat-input" placeholder="Ketik pesan..." autocomplete="off" required>
        <button type="submit" class="btn-counselor btn-counselor-primary">Kirim</button>
    </form>
</div>

<script>
(function () {
    var box = document.getElementById('chatBox');
    if (!box) {
        return;
    }
    box.scrollTop = box.scrollHeight;

    var counselorId = box.dataset.counselorId;
    var currentUserId = <?= (int) $_SESSION['user_id'] ?>;

    function lastMessageId() {
        var bubbles = box.querySelectorAll('.chat-bubble');
        if (!bubbles.length) {
            return 0;
        }
        return bubbles[bubbles.length - 1].dataset.id || 0;
    }

    function appendMessage(message) {
        var mine = String(message.user_id) === String(currentUserId);
        var bubble = document.createElement('div');
        bubble.className = 'chat-bubble ' + (mine ? 'chat-bubble-mine' : 'chat-bubble-theirs');
        bubble.dataset.id = message.id;

        var text = document.createElement('div');
        text.className = 'chat-bubble-text';
        text.textContent = message.message;

        var time = document.createElement('div');
        time.className = 'chat-bubble-time';
        time.textContent = message.created_at ? message.created_at : '';

        bubble.appendChild(text);
        bubble.appendChild(time);

        var empty = box.querySelector('.chat-empty');
        if (empty) {
            empty.remove();
        }

        box.appendChild(bubble);
        box.scrollTop = box.scrollHeight;
    }

    setInterval(function () {
        fetch('/chat/' + counselorId + '/messages?after=' + lastMessageId())
            .then(function (res) { return res.json(); })
            .then(function (data) {
                (data.messages || []).forEach(appendMessage);
            })
            .catch(function () {});
    }, 4000);
})();
</script>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Chat';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
