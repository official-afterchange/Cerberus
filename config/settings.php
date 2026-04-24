<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

return [
    'app' => [
        'env' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => \filter_var($_ENV['APP_DEBUG'] ?? false, \FILTER_VALIDATE_BOOLEAN),
        'url' => $_ENV['APP_URL'] ?? 'http://localhost',
        'lang' => $_ENV['APP_LANG'] ?? 'en',
    ],
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'name' => $_ENV['DB_NAME'] ?? '',
        'user' => $_ENV['DB_USER'] ?? '',
        'pass' => $_ENV['DB_PASS'] ?? '',
    ],
    'log' => [
        'path' => $_ENV['LOG_PATH'] ?? __DIR__ . '/../storage/logs/app.log',
    ],
    'email' => [
        'dsn' => $_ENV['EMAIL_DSN'] ?? 'smtp://localhost:1025'
    ]
];
