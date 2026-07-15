<?php

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    function config(string $key, $default = null)
    {
        static $configs = [];

        [$file, $item] = array_pad(explode('.', $key, 2), 2, null);

        if (!isset($configs[$file])) {
            $path = __DIR__ . "/../../config/{$file}.php";
            $configs[$file] = file_exists($path) ? require $path : [];
        }

        return $item !== null ? ($configs[$file][$item] ?? $default) : $configs[$file];
    }
}

if (!function_exists('mood_meta')) {
    function mood_meta(string $mood): array
    {
        $map = [
            'Sangat Senang' => ['emoji' => '😄', 'slug' => 'sangat-senang'],
            'Senang'        => ['emoji' => '🙂', 'slug' => 'senang'],
            'Netral'        => ['emoji' => '😐', 'slug' => 'netral'],
            'Sedih'         => ['emoji' => '🙁', 'slug' => 'sedih'],
            'Sangat Buruk'  => ['emoji' => '😞', 'slug' => 'sangat-buruk'],
        ];

        return $map[$mood] ?? ['emoji' => '•', 'slug' => 'netral'];
    }
}

if (!function_exists('dd')) {
    function dd(...$vars)
    {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        exit;
    }
}
