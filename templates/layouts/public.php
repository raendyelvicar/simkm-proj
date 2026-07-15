<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? config('app.name');
$isLoggedIn = !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --surface: #ffffff;
            --border: #e5e7eb;
            --text: #1f2937;
            --muted: #6b7280;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f7fb;
            color: var(--text);
        }

        .public-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 16px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .public-header .brand {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text);
            text-decoration: none;
        }

        .public-header nav a {
            margin-left: 18px;
            color: var(--muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .public-header nav a:hover {
            color: var(--primary);
        }

        .public-main {
            max-width: 960px;
            margin: 0 auto;
            padding: 32px 20px 64px;
        }

        .public-footer {
            text-align: center;
            padding: 24px;
            color: var(--muted);
            font-size: 0.85rem;
            border-top: 1px solid var(--border);
        }

        @media (max-width: 640px) {
            .public-header {
                padding: 14px 20px;
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
    <?php if (!empty($extraStyles)): ?>
        <style><?= $extraStyles ?></style>
    <?php endif; ?>
</head>

<body>
    <header class="public-header">
        <a href="/" class="brand"><?= htmlspecialchars(config('app.name')) ?></a>
        <nav>
            <a href="/">Beranda</a>
            <a href="/article">Artikel</a>
            <?php if ($isLoggedIn): ?>
                <a href="/dashboard">Dashboard</a>
            <?php else: ?>
                <a href="/login">Login</a>
                <a href="/register">Daftar</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="public-main">
        <?= $content ?? '' ?>
    </main>

    <footer class="public-footer">
        &copy; <?= date('Y') ?> <?= htmlspecialchars(config('app.name')) ?>
    </footer>
</body>

</html>
