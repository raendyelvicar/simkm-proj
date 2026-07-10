<?php
session_start();
require '../config/db.php';

$user_id = $_SESSION['user_id'];
$message = $mysqli->real_escape_string($_POST['message']);
$receiver_id = (int)$_POST['receiver_id'];

$mysqli->query("
INSERT INTO chat_messages (user_id, receiver_id, message, created_at)
VALUES ($user_id, $receiver_id, '$message', NOW())
");

header("Location: chat.php?student_id=".$receiver_id);
exit;
