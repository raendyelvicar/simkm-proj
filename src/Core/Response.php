<?php

namespace App\Core;

class Response
{
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Render a template file with the given data.
     * Templates are plain PHP files under /templates.
     */
    public static function view(string $template, array $data = []): void
    {
        extract($data);
        $path = __DIR__ . '/../../templates/' . $template . '.php';

        if (!file_exists($path)) {
            http_response_code(500);
            echo "Template not found: {$template}";
            exit;
        }

        require $path;
    }
}
