<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require 'config/db.php';

$username = htmlspecialchars($_SESSION['username']);

// Ambil semua artikel dari database
$articles = $mysqli->query("SELECT id, title, content, category, published_at FROM articles ORDER BY published_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Artikel Kesehatan Mental</title>
<style>
body {
  font-family: Arial, sans-serif;
  background: #f6f8fa;
  margin: 0; padding: 20px;
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

.article-card {
  background:#fff;
  padding:16px;
  border-radius:10px;
  box-shadow:0 4px 18px rgba(16,24,40,0.04);
  margin-bottom:18px;
}
.article-title { font-weight:700; font-size:18px; margin-bottom:4px; }
.article-meta { font-size:12px; color:#555; margin-bottom:8px; }
.article-link { color:#0ea5a4; text-decoration:none; font-weight:bold; }
.article-link:hover { text-decoration:underline; }
</style>
</head>
<body>

<a href=" /AplikasiSkripsi/redirect_dashboard.php" class="back-btn">⟵ Kembali ke Dashboard</a>

<h2>📰 Artikel Kesehatan Mental</h2>
<p>Halo, <strong><?= $username ?></strong>. Berikut beberapa artikel menarik:</p>

<?php if ($articles && $articles->num_rows > 0): ?>
  <?php while ($a = $articles->fetch_assoc()): ?>
    <div class="article-card">
      <div class="article-title"><?= htmlspecialchars($a['title']) ?></div>
      <div class="article-meta">
        Dipublikasikan: <?= htmlspecialchars($a['published_at']) ?>
        <?php if ($a['category']): ?> · Kategori: <?= htmlspecialchars($a['category']) ?><?php endif; ?>
      </div>
      <div><?= nl2br(htmlspecialchars(substr($a['content'], 0, 140))) ?>...</div>
      <a class="article-link" href="article_detail.php?id=<?= $a['id'] ?>">Baca Selengkapnya →</a>
    </div>
  <?php endwhile; ?>
<?php else: ?>
  <p>Tidak ada artikel ditemukan.</p>
<?php endif; ?>

</body>
</html>
