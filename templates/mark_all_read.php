<?php
session_start();
require 'config/db.php';

$mysqli->query("
    UPDATE notifications 
    SET is_read = 1 
    WHERE user_id = ".$_SESSION['user_id']
);

header('Location: notifications.php');
exit;
