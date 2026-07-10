<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

require '../config/db.php';

// Tambah artikel
if (isset($_POST['add'])) {
    $title = $mysqli->real_escape_string($_POST['title']);
    $content = $mysqli->real_escape_string($_POST['content']);

    $mysqli->query("INSERT INTO articles (title, content) VALUES ('$title','$content')");
    header("Location: manage_articles.php");
    exit();
}

// Hapus artikel
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $mysqli->query("DELETE FROM articles WHERE id=$id");
    header("Location: manage_articles.php");
    exit();
}

// Ambil data artikel
$result = $mysqli->query("SELECT * FROM articles ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kelola Artikel</title>

<style>
body{
    font-family: Arial;
    margin:0;
    background:#f4f6f9;
}
header{
    background:#0ea5a4;
    color:white;
    padding:15px;
}
.container{
    max-width:1000px;
    margin:20px auto;
    padding:20px;
}
.card{
    background:white;
    padding:15px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
    margin-bottom:20px;
}
input, textarea{
    width:100%;
    padding:10px;
    margin-top:10px;
    border-radius:6px;
    border:1px solid #ccc;
}
button{
    margin-top:10px;
    padding:10px 15px;
    border:none;
    background:#065f46;
    color:white;
    border-radius:6px;
    cursor:pointer;
}
table{
    width:100%;
    border-collapse:collapse;
}
table th, table td{
    border:1px solid #ddd;
    padding:10px;
}
table th{
    background:#eee;
}
a.btn{
    padding:6px 10px;
    background:red;
    color:white;
    text-decoration:none;
    border-radius:5px;
}
a.back{
    display:inline-block;
    margin-top:10px;
    text-decoration:none;
    color:#0ea5a4;
}
</style>

</head>
<body>

<header>
    <h2>📚 Kelola Artikel (Admin)</h2>
</header>

<div class="container">

    <!-- FORM TAMBAH -->
    <div class="card">
        <h3>Tambah Artikel</h3>
        <form method="POST">
            <input type="text" name="title" placeholder="Judul Artikel" required>
            <textarea name="content" rows="5" placeholder="Isi Artikel" required></textarea>
            <button name="add">Tambah</button>
        </form>
    </div>

    <!-- LIST ARTIKEL -->
    <div class="card">
        <h3>Daftar Artikel</h3>
        <table>
            <tr>
                <th>No</th>
                <th>Judul</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>

            <?php $no=1; while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a class="btn" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus artikel ini?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>

        </table>

        <a class="back" href="../dashboard.php">← Kembali ke Dashboard</a>
    </div>

</div>

</body>
</html>
