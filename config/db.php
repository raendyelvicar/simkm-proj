<?php
// config/db.php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'mental_health';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}
?>