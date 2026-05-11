<?php

declare(strict_types=1);

namespace Afterchange\Template\Repositories;

use Afterchange\Template\Models\OAuthScope;
use PDO;

class OAuthScopeRepository extends Repository
{
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

    public function findByScopeNames(array $scopeNames): array
    {
        $placeholders = implode(',', array_fill(0, count($scopeNames), '?'));
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
