<?php

namespace App\Core;

class Request
{
    private ?array $jsonBody = null;

    public function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /** Reads the raw JSON request body (cached after first call). Pass no key to get the whole decoded array. */
    public function json(?string $key = null, $default = null)
    {
        if ($this->jsonBody === null) {
            $decoded = json_decode(file_get_contents('php://input'), true);
            $this->jsonBody = is_array($decoded) ? $decoded : [];
        }

        return $key === null ? $this->jsonBody : ($this->jsonBody[$key] ?? $default);
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
