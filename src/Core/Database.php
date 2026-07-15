<?php

namespace App\Core;

use mysqli;

class Database
{
    private static ?mysqli $instance = null;

    private static function env(string $key, ?string $default = null): ?string
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
    }

    public static function connection(): mysqli
    {
        if (self::$instance === null) {
            $host = self::env('DB_HOST', '127.0.0.1');
            $username = self::env('DB_USERNAME', self::env('DB_USER', 'root'));
            $password = self::env('DB_PASSWORD', '');
            $database = self::env('DB_DATABASE', 'app');
            $port = (int) (self::env('DB_PORT', '3306'));

            error_log("Database connection variables - Host: $host, Username: $username, Database: $database, Port: $port");

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            try {
                self::$instance = new mysqli(
                    $host,
                    $username,
                    $password,
                    $database,
                    $port
                );
                self::$instance->set_charset('utf8mb4');
            } catch (\mysqli_sql_exception $e) {
                error_log($e->getMessage());
                http_response_code(500);
                die($e->getMessage());
            }
        }

        return self::$instance;
    }
}
