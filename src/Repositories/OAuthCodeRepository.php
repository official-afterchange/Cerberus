<?php

declare(strict_types=1);

namespace Afterchange\Template\Repositories;

use PDO;

/**
 * Handles database queries for OAuth 2.0 authorization codes.
 */
final class OAuthCodeRepository extends Repository
{
    /**
     * Finds an authorization code record by its code string.
     *
     * @param string $code The authorization code to look up.
     * @return \Afterchange\Template\Models\OAuthCode|null The hydrated model, or null if not found.
     */
    public function findByCode(string $code): ?\Afterchange\Template\Models\OAuthCode
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->getTable()} WHERE code = :code");
        $stmt->execute(['code' => $code]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $model = new \Afterchange\Template\Models\OAuthCode();
        $model->hydrate($data);
        return $model;
    }

    /**
     * Marks an authorization code as used, preventing it from being exchanged again.
     *
     * @param int $id The internal ID of the authorization code to revoke.
     * @return bool   True on success, false on failure.
     */
    public function revokeCode(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE oauth_codes SET used = 1 WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}