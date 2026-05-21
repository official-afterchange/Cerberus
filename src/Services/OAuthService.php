<?php

declare(strict_types=1);

namespace Afterchange\Template\Services;

use Afterchange\Template\Models\OAuthClient;
use Afterchange\Template\Models\OAuthScope;
use Afterchange\Template\Repositories\OAuthAccessTokenRepository;
use Afterchange\Template\Repositories\OAuthClientRepository;
use Afterchange\Template\Repositories\OAuthCodeRepository;
use Afterchange\Template\Repositories\OAuthScopeRepository;

/**
 * Orchestrates the OAuth 2.0 authorization code flow: client lookup, code generation, token exchange, and validation.
 */
final class OAuthService extends Service
{
    protected OAuthClientRepository $oAuthClientRepository;
    protected OAuthScopeRepository $oAuthScopeRepository;
    protected OAuthCodeRepository $oAuthCodeRepository;
    protected OAuthAccessTokenRepository $oAuthAccessTokenRepository;

    /**
     * Initializes the service with all required OAuth repositories.
     *
     * @param OAuthClientRepository      $oAuthClientRepository      Handles client and redirect URI lookups.
     * @param OAuthScopeRepository       $oAuthScopeRepository       Handles scope resolution.
     * @param OAuthCodeRepository        $oAuthCodeRepository        Handles authorization code persistence and revocation.
     * @param OAuthAccessTokenRepository $oAuthAccessTokenRepository Handles access token persistence and lookup.
     */
    public function __construct(
        OAuthClientRepository $oAuthClientRepository,
        OAuthScopeRepository $oAuthScopeRepository,
        OAuthCodeRepository $oAuthCodeRepository,
        OAuthAccessTokenRepository $oAuthAccessTokenRepository
    ) {
        $this->oAuthClientRepository = $oAuthClientRepository;
        $this->oAuthScopeRepository = $oAuthScopeRepository;
        $this->oAuthCodeRepository = $oAuthCodeRepository;
        $this->oAuthAccessTokenRepository = $oAuthAccessTokenRepository;
    }

    /**
     * Finds an OAuth client by its public client identifier.
     *
     * @param string $clientId The public client_id to look up.
     * @return OAuthClient|null The matching client model, or null if not found.
     */
    public function getClientById(string $clientId): ?OAuthClient
    {
        return $this->oAuthClientRepository->findByClientId($clientId);
    }

    /**
     * Checks whether a redirect URI is registered for the given client.
     *
     * @param OAuthClient $client      The client to validate against.
     * @param string      $redirectUri The redirect URI to check.
     * @return bool                    True if the URI is registered, false otherwise.
     */
    public function isValidRedirectUri(OAuthClient $client, string $redirectUri): bool
    {
        return $this->oAuthClientRepository->isRedirectUriValid($client, $redirectUri);
    }

    /**
     * Returns all scopes marked as default.
     *
     * @return OAuthScope[] An array of hydrated default scope models.
     */
    public function getDefaultScopes(): array
    {
        return $this->oAuthScopeRepository->getDefaultScopes();
    }

    /**
     * Resolves scope models from a list of scope name strings.
     *
     * @param string[] $scopeNames The scope identifiers to resolve (e.g., ['openid', 'profile']).
     * @return OAuthScope[]        An array of matching hydrated scope models.
     */
    public function getScopeByNames(array $scopeNames): array
    {
        return $this->oAuthScopeRepository->findByScopeNames($scopeNames);
    }

    /**
     * Generates a short-lived authorization code (valid 5 minutes) and persists it.
     *
     * @param OAuthClient $client   The client the code is issued for.
     * @param int         $userId   The ID of the user who granted consent.
     * @param string      $scopeStr Space-separated scope string to associate with the code.
     * @return string               The generated 64-character authorization code.
     */
    public function generateAuthCode(OAuthClient $client, int $userId, string $scopeStr = ''): string
    {
        $code = \bin2hex(\random_bytes(32)); // 64 chars
        $model = new \Afterchange\Template\Models\OAuthCode();
        $model->code = $code;
        $model->client_internal_id = $client->id;
        $model->user_id = $userId;
        $model->expires_at = \date('Y-m-d H:i:s', \time() + 300);
        $model->scope = $scopeStr;
        $this->oAuthCodeRepository->save($model);
        return $code;
    }

    /**
     * Validates an authorization code against the client and marks it as used if valid.
     *
     * @param string      $code   The authorization code to validate.
     * @param OAuthClient $client The client attempting to exchange the code.
     * @return \Afterchange\Template\Models\OAuthCode|null The revoked code model, or null if invalid or expired.
     */
    public function validateAndRevokeAuthCode(string $code, OAuthClient $client): ?\Afterchange\Template\Models\OAuthCode
    {
        $codeData = $this->oAuthCodeRepository->findByCode($code);

        if (!$codeData || $codeData->used === 1 || $codeData->client_internal_id !== $client->id) {
            return null;
        }

        if (\strtotime($codeData->expires_at) < \time()) {
            return null;
        }

        $codeData->used = 1;
        $this->oAuthCodeRepository->save($codeData);

        return $codeData;
    }

    /**
     * Generates a Bearer access token (valid 1 hour) and persists it.
     *
     * @param OAuthClient $client   The client the token is issued for.
     * @param int         $userId   The ID of the authenticated user.
     * @param string      $scopeStr Space-separated scope string to associate with the token.
     * @return string               The generated 128-character access token.
     */
    public function generateAccessToken(OAuthClient $client, int $userId, string $scopeStr = ''): string
    {
        $token = \bin2hex(\random_bytes(64)); // 128 chars
        $model = new \Afterchange\Template\Models\OAuthAccessToken();
        $model->access_token = $token;
        $model->client_internal_id = $client->id;
        $model->user_id = $userId;
        $model->expires_at = \date('Y-m-d H:i:s', \time() + 3600);
        $model->scope = $scopeStr;
        $this->oAuthAccessTokenRepository->save($model);
        return $token;
    }

    /**
     * Validates an access token and checks that it has not expired.
     *
     * @param string $token The raw Bearer token to validate.
     * @return array|null   The token and associated user data as an associative array, or null if invalid or expired.
     */
    public function validateAccessToken(string $token): ?array
    {
        $tokenData = $this->oAuthAccessTokenRepository->findByToken($token);

        if (!$tokenData || \strtotime($tokenData['expires_at']) < \time()) {
            return null;
        }

        return $tokenData;
    }
}