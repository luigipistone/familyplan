<?php

$local = file_exists(base_path('config.local.php')) ? require base_path('config.local.php') : [];
$push = $local['push'] ?? [];

return [
    'vapid' => [
        'subject' => $push['subject'] ?? ($local['app_url'] ?? env('APP_URL', 'http://localhost')),
        'public_key' => $push['public_key'] ?? env('VAPID_PUBLIC_KEY'),
        'private_key' => $push['private_key'] ?? env('VAPID_PRIVATE_KEY'),
    ],
];
