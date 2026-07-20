#!/usr/bin/env php
<?php

// CLI migration runner. Applies any database/migrations/*.sql file not yet
// recorded in the schema_migrations table, in filename order (files are
// date-prefixed, so alphabetical == chronological). Safe to run on every
// deploy: already-applied files are skipped.
//
// First-time adoption on a database that was provisioned some other way
// (e.g. from database/mental_health_dump.sql, as this project's VPS setup
// was) needs a one-time baseline so this runner doesn't try to re-run
// CREATE TABLE statements against tables that already exist:
//
//   php bin/migrate.php --baseline
//
// This records every migration file currently on disk as already applied
// WITHOUT running them. Run it once, by hand, after confirming the database
// really does already contain everything up to that point. Any migration
// file added after that baseline runs normally on the next deploy.

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

function migrate_env(string $key, ?string $default = null): ?string
{
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
}

// mysqli throws exceptions by default since PHP 8.1; this script handles
// errors itself via return values / ->error, so switch back to that mode.
mysqli_report(MYSQLI_REPORT_OFF);

$host = migrate_env('DB_HOST', '127.0.0.1');
$username = migrate_env('DB_USERNAME', migrate_env('DB_USER', 'root'));
$password = migrate_env('DB_PASSWORD', '');
$database = migrate_env('DB_DATABASE', 'app');
$port = (int) migrate_env('DB_PORT', '3306');

$mysqli = null;
$maxAttempts = 30;
for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
    $mysqli = @new mysqli($host, $username, $password, $database, $port);
    if (!$mysqli->connect_error) {
        break;
    }
    fwrite(STDERR, "[migrate] waiting for database ({$attempt}/{$maxAttempts}): {$mysqli->connect_error}\n");
    $mysqli = null;
    sleep(2);
}

if (!$mysqli) {
    fwrite(STDERR, "[migrate] could not connect to database, aborting.\n");
    exit(1);
}

$mysqli->set_charset('utf8mb4');

$mysqli->query(
    'CREATE TABLE IF NOT EXISTS schema_migrations (
        migration VARCHAR(255) NOT NULL PRIMARY KEY,
        applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$migrationsDir = __DIR__ . '/../database/migrations';
$files = glob($migrationsDir . '/*.sql');
sort($files, SORT_STRING);

$applied = [];
$result = $mysqli->query('SELECT migration FROM schema_migrations');
while ($row = $result->fetch_assoc()) {
    $applied[$row['migration']] = true;
}

$baseline = in_array('--baseline', $argv, true);

if ($baseline) {
    $stmt = $mysqli->prepare('INSERT IGNORE INTO schema_migrations (migration) VALUES (?)');
    $count = 0;
    foreach ($files as $file) {
        $name = basename($file);
        if (isset($applied[$name])) {
            continue;
        }
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $count++;
        echo "[migrate] baselined {$name} (not executed)\n";
    }
    echo "[migrate] baseline complete: {$count} migration(s) marked as already applied.\n";
    exit(0);
}

$pending = array_values(array_filter($files, fn ($f) => !isset($applied[basename($f)])));

if (!$pending) {
    echo "[migrate] up to date, nothing to apply.\n";
    exit(0);
}

foreach ($pending as $file) {
    $name = basename($file);
    echo "[migrate] applying {$name}...\n";

    $sql = file_get_contents($file);

    if ($sql === false || trim($sql) === '') {
        fwrite(STDERR, "[migrate] FAILED {$name}: could not read file or file is empty\n");
        exit(1);
    }

    if (!$mysqli->multi_query($sql)) {
        fwrite(STDERR, "[migrate] FAILED {$name}: {$mysqli->error}\n");
        exit(1);
    }

    // Drain every result set multi_query queues up, so the next query() call
    // on this connection doesn't fail with "commands out of sync".
    do {
        if ($res = $mysqli->store_result()) {
            $res->free();
        }
        if ($mysqli->errno) {
            fwrite(STDERR, "[migrate] FAILED {$name}: {$mysqli->error}\n");
            exit(1);
        }
    } while ($mysqli->more_results() && $mysqli->next_result());

    $stmt = $mysqli->prepare('INSERT INTO schema_migrations (migration) VALUES (?)');
    $stmt->bind_param('s', $name);
    $stmt->execute();

    echo "[migrate] applied {$name}\n";
}

echo "[migrate] done — " . count($pending) . " migration(s) applied.\n";
