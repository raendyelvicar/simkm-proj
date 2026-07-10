<?php
// assessment/form.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    header('Location: ../login.php');
    exit;
}
require '../config/db.php';
$res = $mysqli->query("SELECT * FROM questions ORDER BY id ASC");
$questions = [];
while($q = $res->fetch_assoc()) $questions[] = $q;
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $total = 0;
    foreach($questions as $q){
        $aid = isset($_POST['q'.$q['id']]) ? (int)$_POST['q'.$q['id']] : 0;
        $total += $aid;
        $mysqli->query("INSERT INTO answers (user_id,question_id,answer_value) VALUES ($user_id,".$q['id'].",$aid)");
    }
    $label = 'Normal';
    if ($total > 35) $label = 'Perlu Dukungan';
    elseif ($total > 20) $label = 'Waspada';
    $rec = '';
    if ($label == 'Perlu Dukungan') $rec = 'Disarankan mencari bantuan profesional di layanan kampus.';
    elseif ($label == 'Waspada') $rec = 'Perhatikan tanda-tanda stress dan coba teknik relaksasi.';
    else $rec = 'Pertahankan kebiasaan sehat dan monitor diri.';
    $mysqli->query("INSERT INTO assessment_results (user_id,assessment_date,result_summary) VALUES ($user_id,NOW(),$total,'$label','$rec')");
    header('Location: result.php');
    exit;
}
?>
<!doctype html><html><head><meta charset='utf-8'><title>Self-Assessment</title>
<link rel='stylesheet' href='../assets/css/style.css'>
</head><body>
<div class="container">
<h2>Self-Assessment</h2>
<form method="post">
<?php foreach($questions as $q): ?>
<div class="question">
<p><?php echo htmlspecialchars($q['question_text']); ?></p>
<label><input type="radio" name="q<?php echo $q['id']; ?>" value="1" required>1</label>
<label><input type="radio" name="q<?php echo $q['id']; ?>" value="2">2</label>
<label><input type="radio" name="q<?php echo $q['id']; ?>" value="3">3</label>
<label><input type="radio" name="q<?php echo $q['id']; ?>" value="4">4</label>
<label><input type="radio" name="q<?php echo $q['id']; ?>" value="5">5</label>
</div>
<?php endforeach; ?>
<br><button type="submit">Submit</button>
</form>
<p><a href="../diary/list.php">Kembali</a></p>
</div></body></html>
