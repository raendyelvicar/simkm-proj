<?php
function addNotification($mysqli, $user_id, $message) {
    $stmt = $mysqli->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    $stmt->close();
}
?>