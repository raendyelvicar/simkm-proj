<?php
require '../config/db.php';

$start = $_POST['start_date'];
$end   = $_POST['end_date'];

$query = "
SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN total_skor < 50 THEN 1 ELSE 0 END) as ringan,
    SUM(CASE WHEN total_skor BETWEEN 50 AND 74 THEN 1 ELSE 0 END) as sedang,
    SUM(CASE WHEN total_skor >= 75 THEN 1 ELSE 0 END) as berat
FROM assessment_results
WHERE DATE(assessment_date) BETWEEN '$start' AND '$end'
";

$result = $mysqli->query($query)->fetch_assoc();

echo json_encode($result);