<?php

return [
    'name'  => env('APP_NAME', 'SIMKM'),
    'env'   => env('APP_ENV', 'production'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'url'   => env('APP_URL', 'http://localhost'),
];
