<?php

$local = file_exists(base_path('config.local.php')) ? require base_path('config.local.php') : [];
$weather = $local['weather'] ?? [];

return [
    'google' => [
        'api_key' => $weather['google_api_key'] ?? env('GOOGLE_WEATHER_API_KEY'),
    ],
];
