<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
$dbUser = $_ENV['DB_USER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? '';
$dbName = $_ENV['DB_NAME'] ?? 'template';

$testDb = $dbName . '_testing';

try {
    $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("DROP DATABASE IF EXISTS `$testDb`");
    $pdo->exec("CREATE DATABASE `$testDb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$testDb`");

    $sql = file_get_contents(__DIR__ . '/../database.sql');
    if ($sql) {
        $pdo->exec($sql);
    }
    
    $_ENV['DB_NAME'] = $testDb;
    \putenv("DB_NAME=$testDb");
} catch (PDOException $e) {
    die("Failed to setup test database: " . $e->getMessage() . "\n");
}
