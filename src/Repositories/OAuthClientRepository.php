<?php

declare(strict_types=1);

namespace Afterchange\Template\Repositories;

use Afterchange\Template\Models\OAuthClient;
use Afterchange\Template\Models\OAuthClientRedirect;
use PDO;

class OAuthClientRepository extends Repository
{
    public function findByClientId(string $clientId): ?OAuthClient
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->getTable()} WHERE client_id = :client_id");
        $stmt->execute(['client_id' => $clientId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $client = new OAuthClient();
        $client->hydrate($data);

        return $client;
    }

    public function isRedirectUriValid(OAuthClient $client, string $redirectUri): bool
    {
        $stmt = $this->pdo->prepare("SELECT * FROM oauth_client_redirects WHERE client_internal_id = :client_internal_id AND redirect_uri = :redirect_uri");
        $stmt->execute(['client_internal_id' => $client->id, 'redirect_uri' => $redirectUri]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return false;
        }

        return true;
    }
}
