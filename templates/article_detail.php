<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require 'config/db.php';

if (!isset($_GET['id'])) {
    die("Artikel tidak ditemukan.");
}

$id = (int) $_GET['id'];

// Ambil artikel berdasarkan ID
$stmt = $mysqli->prepare("SELECT title, content, category, published_at FROM articles WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Artikel tidak ditemukan.");
}

$article = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($article['title']) ?></title>
<style>
body {
  font-family: Arial, sans-serif;
  background:#f6f8fa;
  margin:0; padding:20px;
}
.back-btn {
  display:inline-block;
  margin-bottom:18px;
  padding:8px 14px;
  background:#0ea5a4;
  color:#fff;
  border-radius:8px;
  text-decoration:none;
  font-weight:bold;
}
.back-btn:hover { background:#0b807f; }

.article-box {
  background:#fff;
  padding:20px;
  border-radius:10px;
  box-shadow:0 4px 18px rgba(16,24,40,0.05);
  max-width:850px;
}
.title { font-size:24px; font-weight:700; margin-bottom:6px; }
.meta { font-size:12px; color:#555; margin-bottom:14px; }
.content { font-size:15px; line-height:1.6; white-space:pre-line; }
</style>
</head>
<body>

<a href="articles.php" class="back-btn">⟵ Kembali ke Artikel</a>

<div class="article-box">
  <div class="title"><?= htmlspecialchars($article['title']) ?></div>
  <div class="meta">
    Dipublikasikan: <?= htmlspecialchars($article['published_at']) ?>
    <?php if ($article['category']): ?> · Kategori: <?= htmlspecialchars($article['category']) ?><?php endif; ?>
  </div>
  <div class="content">
    <?= nl2br(htmlspecialchars($article['content'])) ?>
  </div>
</div>

</body>
</html>
