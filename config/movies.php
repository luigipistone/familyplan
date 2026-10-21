<?php

$local = file_exists(base_path('config.local.php')) ? require base_path('config.local.php') : [];
$movies = $local['movies'] ?? [];

return [
    'tmdb' => [
        'api_key' => $movies['tmdb_api_key'] ?? env('TMDB_API_KEY'),
        'base_url' => 'https://api.themoviedb.org/3',
        'image_url' => 'https://image.tmdb.org/t/p',
    ],
];
