<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];
$port = $_ENV['DB_PORT'];

$dsn = "mysql:host=$host;port=$port;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $sqlFile = __DIR__ . '/database.sql';
    if (!file_exists($sqlFile)) {
        die("Le fichier database.sql est introuvable.\n");
    }
    
    $pdo->exec("DROP DATABASE IF EXISTS " . $_ENV['DB_NAME']);
    $pdo->exec("CREATE DATABASE " . $_ENV['DB_NAME']);
    $pdo->exec("USE " . $_ENV['DB_NAME']);

    $sqlScript = file_get_contents($sqlFile);
    $pdo->exec($sqlScript);
    echo "Fichier database.sql exécuté avec succès (la base a été recréée).\n";
    
    $pdo->exec("USE " . $_ENV['DB_NAME']);
    
    $password = password_hash('password123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
    $stmt->execute([
        'username' => 'admin',
        'email' => 'admin@admin.com',
        'password' => $password
    ]);
    
    $userId = $pdo->lastInsertId();
    echo "Utilisateur de base inséré avec succès ! (ID: $userId)\n";
    echo "---------------------------------\n";
    echo "Email: admin@admin.com\n";
    echo "Mot de passe: password123\n";
    echo "---------------------------------\n";

} catch (PDOException $e) {
    echo "Erreur PDO: " . $e->getMessage() . "\n";
}
