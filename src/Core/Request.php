<?php

namespace App\Core;

class Request
{
    public function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    public function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /** Returns the uploaded file array for $key, or null if no file was chosen. */
    public function file(string $key): ?array
    {
        $file = $_FILES[$key] ?? null;

        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        return $file;
    }

    public function all(): array
    {
        return $this->method() === 'POST' ? $_POST : $_GET;
    }

    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function header(string $key, $default = null)
    {
        $key = 'HTTP_' . str_replace('-', '_', strtoupper($key));
        return $_SERVER[$key] ?? $default;
    }
}
