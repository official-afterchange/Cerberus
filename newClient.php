<?php

// 1. Chargement manuel du .env (Simple & efficace)
$env = [];
$lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    list($name, $value) = explode('=', $line, 2);
    $env[trim($name)] = trim($value);
}

// 2. Connexion PDO utilisant les variables du .env
try {
    $dsn = "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_NAME']};charset=utf8mb4";
    $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    die("❌ Erreur connexion : " . $e->getMessage() . "\n");
}

// 3. Récupération des arguments CLI
if ($argc < 3) {
    echo "Usage: php create_client.php [client_id] [nom_du_client] [redirect_uri]\n";
    echo "Ex: php create_client.php launcher_dev 'Launcher Butterfly' butterfly://auth\n";
    exit;
}

$clientId    = $argv[1];
$clientName  = $argv[2];
$redirectUri = $argv[3] ?? null;

// Génération d'un secret robuste
$rawSecret = bin2hex(random_bytes(20)); // ex: 40 caractères hex
$hashedSecret = password_hash($rawSecret, PASSWORD_BCRYPT);

try {
    $pdo->beginTransaction();

    // Insertion du client
    $stmt = $pdo->prepare("INSERT INTO oauth_clients (client_id, client_secret, name, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$clientId, $hashedSecret, $clientName, "Client créé via CLI"]);
    
    $internalId = $pdo->lastInsertId();

    // Insertion de la redirection autorisée
    if ($redirectUri) {
        $stmt = $pdo->prepare("INSERT INTO oauth_client_redirects (client_internal_id, redirect_uri) VALUES (?, ?)");
        $stmt->execute([$internalId, $redirectUri]);
    }

    $pdo->commit();

    echo "\n🚀 Client enregistré avec succès dans la base '{$env['DB_NAME']}'\n";
    echo "--------------------------------------------------\n";
    echo "CLIENT_ID     : " . $clientId . "\n";
    echo "CLIENT_SECRET : " . $rawSecret . "  <-- (À COPIER MAINTENANT !)\n";
    echo "REDIRECT_URI  : " . ($redirectUri ?? 'Aucune') . "\n";
    echo "--------------------------------------------------\n\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}