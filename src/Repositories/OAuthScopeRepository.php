<?php

declare(strict_types=1);

namespace Afterchange\Template\Repositories;

use Afterchange\Template\Models\OAuthScope;
use PDO;

/**
 * Handles database queries for OAuth 2.0 permission scopes.
 */
final class OAuthScopeRepository extends Repository
{
    /**
     * Returns all scopes marked as default.
     *
     * @return OAuthScope[] An array of hydrated default scope models.
     */
    public function getDefaultScopes(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->getTable()} WHERE is_default = 1");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $scopes = [];
        foreach ($data as $row) {
            $scope = new OAuthScope();
            $scope->hydrate($row);
            $scopes[] = $scope;
        }

        return $scopes;
    }

    /**
     * Finds scopes matching the given list of scope name strings.
     *
     * @param string[] $scopeNames The scope identifiers to look up (e.g., ['openid', 'profile']).
     * @return OAuthScope[]        An array of hydrated scope models matching the provided names.
     */
    public function findByScopeNames(array $scopeNames): array
    {
        $placeholders = \implode(',', \array_fill(0, \count($scopeNames), '?'));
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->getTable()} WHERE scope IN ($placeholders)");
        $stmt->execute($scopeNames);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $scopes = [];
        foreach ($data as $row) {
            $scope = new OAuthScope();
            $scope->hydrate($row);
            $scopes[] = $scope;
        }

        return $scopes;
    }
}