<?php
// layout/layout.php
session_start();

// Load config dan base_url
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$role = $_SESSION['role'] ?? '';
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
<script src="<?= $base_url ?>/assets/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base_url ?>/assets/js/chart.min.js"></script>
</head>
<body>

<div class="d-flex">

    <!-- Sidebar -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Content -->
    <div class="content-wrapper w-100 p-4">
        <?php if(isset($breadcrumb)): ?>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <?php foreach($breadcrumb as $name => $link): ?>
                <li class="breadcrumb-item <?php if($link=='#') echo 'active'; ?>">
                    <?php if($link != '#'): ?>
                    <a href="<?= $link ?>"><?= $name ?></a>
                    <?php else: ?>
                    <?= $name ?>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php endif; ?>

        <!-- Main content -->
        <div class="main-content">
            <?php if(isset($page_content)) echo $page_content; ?>
        </div>
    </div>

</div>

<script src="<?= $base_url ?>/assets/js/script.js"></script>
</body>
</html>