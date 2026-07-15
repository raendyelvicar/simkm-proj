<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? config('app.name')) ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>

<body>
    <header>
        <h1><?= htmlspecialchars(config('app.name')) ?></h1>
    </header>

    <main>
        <?= $content ?? '' ?>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?></p>
    </footer>

    <script src="/assets/js/app.js"></script>
</body>

</html>