<?php

use App\Core\Router;

require __DIR__ . '/../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load environment variables from .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Basic error display toggle based on APP_DEBUG
if (filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

$router = new Router();
require __DIR__ . '/../config/routes.php';

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
