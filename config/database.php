<?php

use Pdo\Mysql;

$local = (env('APP_ENV') !== 'testing' && file_exists(base_path('config.local.php'))) ? require base_path('config.local.php') : [];
$db = $local['db'] ?? [];

return [
    'default' => env('DB_CONNECTION', 'mysql'),
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => $db['host'] ?? env('DB_HOST', '127.0.0.1'),
            'port' => $db['port'] ?? env('DB_PORT', '3306'),
            'database' => $db['database'] ?? env('DB_DATABASE', 'laravel'),
            'username' => $db['username'] ?? env('DB_USERNAME', 'root'),
            'password' => $db['password'] ?? env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') && env('MYSQL_ATTR_SSL_CA') ? [
                (defined('Pdo\\Mysql::ATTR_SSL_CA') ? Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
            ] : [],
        ],
    ],
    'migrations' => ['table' => 'migrations', 'update_date_on_publish' => true],
    'redis' => ['client' => env('REDIS_CLIENT', 'phpredis')],
];
