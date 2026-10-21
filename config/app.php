<?php

$local = file_exists(base_path('config.local.php')) ? require base_path('config.local.php') : [];

return [
    'name' => env('APP_NAME', 'FamilyPlan'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => $local['app_url'] ?? env('APP_URL', 'http://localhost'),
    'timezone' => 'Europe/Rome',
    'locale' => 'it',
    'fallback_locale' => 'it',
    'faker_locale' => 'it_IT',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [],
    'maintenance' => ['driver' => 'file', 'store' => 'database'],
];
