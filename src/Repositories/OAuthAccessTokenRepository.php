<?php

declare(strict_types=1);

namespace Afterchange\Template\Repositories;

use PDO;

/**
 * Handles database queries for OAuth 2.0 access tokens.
 */
final class OAuthAccessTokenRepository extends Repository
{
    /**
     * Finds an access token record joined with the associated user's profile data.
     *
     * @param string $token The raw access token string to look up.
     * @return array|null   The token and user data as an associative array, or null if not found.
     */
    public function findByToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare('SELECT oat.*, u.username, u.email FROM oauth_access_tokens oat JOIN users u ON oat.user_id = u.id WHERE oat.access_token = :access_token');
        $stmt->execute(['access_token' => $token]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ?: null;
    }
}