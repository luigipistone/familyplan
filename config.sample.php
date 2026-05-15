<?php
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'familyplan',
        'user' => 'familyplan_user',
        'pass' => 'change-me',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'FamilyPlan',
        'base_url' => 'http://localhost:8000',
        'session_name' => 'familyplan_session',
        'password_reset_token_minutes' => 30,
        'csrf_header' => 'HTTP_X_CSRF_TOKEN',
        'debug' => false,
    ],
    'mail' => [
        'from' => 'no-reply@example.com',
        'log_file' => __DIR__ . '/password-reset.log',
    ],
];
