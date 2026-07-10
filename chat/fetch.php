<?php
session_start();
require '../config/db.php';

$user_id = (int)$_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'konselor' && isset($_GET['student_id'])) {

    $student_id = (int)$_GET['student_id'];

    $q = $mysqli->query("
        SELECT c.*, u.username
        FROM chat_messages c
        LEFT JOIN users u ON c.user_id = u.id
        WHERE 
            (c.user_id = $student_id AND c.receiver_id = $user_id)
            OR
            (c.user_id = $user_id AND c.receiver_id = $student_id)
        ORDER BY c.created_at ASC
    ");

} else {

    $q = $mysqli->query("
        SELECT c.*, u.username
        FROM chat_messages c
        LEFT JOIN users u ON c.user_id = u.id
        WHERE (c.user_id = $user_id OR c.receiver_id = $user_id)
        ORDER BY c.created_at ASC
    ");
}

$data = [];
while($row = $q->fetch_assoc()){
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
