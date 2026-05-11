<?php

declare(strict_types=1);

namespace Afterchange\Template\Services;

use Afterchange\Template\Models\OAuthClient;
use Afterchange\Template\Repositories\OAuthClientRepository;
use Afterchange\Template\Repositories\OAuthScopeRepository;

class OAuthService extends Service
{
    /** @var OAuthClientRepository */
    protected OAuthClientRepository $oAuthClientRepository;

    /** @var OAuthScopeRepository */
    protected OAuthScopeRepository $oAuthScopeRepository;

    public function __construct(OAuthClientRepository $oAuthClientRepository, OAuthScopeRepository $oAuthScopeRepository)
    {
        $this->oAuthClientRepository = $oAuthClientRepository;
        $this->oAuthScopeRepository = $oAuthScopeRepository;
    }

    public function getClientById(string $clientId): ?OAuthClient
    {
        return $this->oAuthClientRepository->findByClientId($clientId);
    }

    public function isValidRedirectUri(OAuthClient $client, string $redirectUri): bool
    {
        return $this->oAuthClientRepository->isRedirectUriValid($client, $redirectUri);
    }

    public function getDefaultScopes(): array
    {
        return $this->oAuthScopeRepository->getDefaultScopes();
    }

    public function getScopeByNames(array $scopeNames): array
    {
        return $this->oAuthScopeRepository->findByScopeNames($scopeNames);
    }
}
