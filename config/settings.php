<?php

declare(strict_types=1);

use Afterchange\Template\Utils\Env;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

return [
    'app' => [
        'env' => Env::get('APP_ENV', 'production'),
        'debug' => Env::get('APP_DEBUG', false),
        'url' => Env::get('APP_URL', 'http://localhost'),
        'lang' => Env::get('APP_LANG', 'en'),
    ],
    'db' => [
        'host' => Env::get('DB_HOST', '127.0.0.1'),
        'port' => Env::get('DB_PORT', 3306),
        'name' => Env::get('DB_NAME', ''),
        'user' => Env::get('DB_USER', ''),
        'pass' => Env::get('DB_PASS', ''),
    ],
    'log' => [
        'path' => Env::get('LOG_PATH', __DIR__ . '/../storage/logs/app.log'),
    ],
    'email' => [
        'dsn' => Env::get('EMAIL_DSN', 'smtp://localhost:1025')
    ]
];
