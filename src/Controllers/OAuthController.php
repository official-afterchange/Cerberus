<?php

declare(strict_types=1);

namespace Afterchange\Template\Controllers;

use Afterchange\Template\Exceptions\OAuthException;
use Afterchange\Template\Services\AuthService;
use Afterchange\Template\Services\OAuthService;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

/**
 * Handles the OAuth 2.0 authorization code flow: consent, token exchange, and user info.
 */
final class OAuthController extends Controller
{
    protected OAuthService $oAuthService;
    protected AuthService $authService;

    /**
     * Initializes the controller with its required dependencies.
     *
     * @param PhpRenderer  $view         The template rendering engine.
     * @param OAuthService $oAuthService Handles OAuth client, code, and token operations.
     * @param AuthService  $authService  Handles user authentication and session management.
     */
    public function __construct(PhpRenderer $view, OAuthService $oAuthService, AuthService $authService)
    {
        parent::__construct($view);
        $this->oAuthService = $oAuthService;
        $this->authService = $authService;
    }

    /**
     * Validates the OAuth request parameters and renders the user consent form.
     *
     * @param Request  $request  The incoming HTTP request carrying client_id, redirect_uri, scope, and state.
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments (unused).
     * @return Response          The rendered authorization consent view.
     * @throws Exception         If the client is not found or the redirect URI is invalid.
     * @throws OAuthException    If the response type or scope is unsupported.
     */
    public function showAuthorize(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $clientId = $queryParams['client_id'] ?? '';
        $redirectUri = $queryParams['redirect_uri'] ?? null;
        $state = $queryParams['state'] ?? null;

        $client = $this->oAuthService->getClientById($clientId);

        if ($client === null) {
            throw new Exception('CLIENT_NOT_FOUND', 400);
        }

        $isValidUri = $this->oAuthService->isValidRedirectUri($client, $redirectUri);

        if (!$isValidUri) {
            throw new Exception('INVALID_REDIRECT_URI', 400);
        }

        $responseType = $queryParams['response_type'] ?? null;
        if ($responseType !== 'code') {
            throw new OAuthException('unsupported_response_type', 'INVALID_RESPONSE_TYPE', $redirectUri, $state);
        }

        $scopeString = $queryParams['scope'] ?? null;

        $scopeNames = ($scopeString !== null && $scopeString !== '')
            ? \explode(' ', $scopeString)
            : [];

        if (!\in_array('openid', $scopeNames, true)) {
            throw new OAuthException('invalid_scope', 'INVALID_SCOPE', $redirectUri, $state);
        }

        $scopes = $this->oAuthService->getScopeByNames($scopeNames);

        return $this->view->render($response, 'oauth/authorize.phtml', [
            'clientId' => $client->id,
            'responseType' => $responseType,
            'scopes' => $scopes,
            'redirectUri' => $redirectUri,
            'state' => $state,
        ]);
    }

    /**
     * Processes user consent, generates an authorization code, and redirects to the client.
     *
     * @param Request  $request  The incoming HTTP request carrying client_id, redirect_uri, scope, and state.
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments (unused).
     * @return Response          A redirect to the client redirect URI with the authorization code.
     * @throws Exception         If the client is invalid, the redirect URI mismatches, or the user is unauthenticated.
     */
    public function authorize(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody() ?? [];
        $clientId = $data['client_id'] ?? '';
        $redirectUri = $data['redirect_uri'] ?? '';
        $scope = $data['scope'] ?? '';
        $state = $data['state'] ?? null;

        $client = $this->oAuthService->getClientById($clientId);
        if (!$client || !$this->oAuthService->isValidRedirectUri($client, $redirectUri)) {
            throw new Exception('INVALID_CLIENT_OR_REDIRECT_URI', 400);
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            throw new Exception('UNAUTHORIZED', 401);
        }

        $code = $this->oAuthService->generateAuthCode($client, $userId, $scope);

        $url = $redirectUri . '?code=' . \urlencode($code);
        if ($state) {
            $url .= '&state=' . \urlencode($state);
        }

        return $response->withHeader('Location', $url)->withStatus(302);
    }

    /**
     * Handles user denial of the consent form and redirects with an access_denied error.
     *
     * @param Request  $request  The incoming HTTP request carrying redirect_uri and state.
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments (unused).
     * @return Response          A redirect to the client redirect URI with an error parameter.
     * @throws Exception         If the redirect URI is missing.
     */
    public function deny(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody() ?? [];
        $redirectUri = $data['redirect_uri'] ?? '';
        $state = $data['state'] ?? null;

        if (empty($redirectUri)) {
            throw new Exception('MISSING_REDIRECT_URI', 400);
        }

        $url = $redirectUri . '?error=access_denied';
        if ($state) {
            $url .= '&state=' . \urlencode($state);
        }

        return $response->withHeader('Location', $url)->withStatus(302);
    }

    /**
     * Exchanges a valid authorization code for a Bearer access token.
     *
     * @param Request  $request  The incoming HTTP request carrying grant_type, code, client_id, and client_secret.
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments (unused).
     * @return Response          A JSON response containing the access token or an OAuth error.
     */
    public function token(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody() ?? [];
        $grantType = $data['grant_type'] ?? '';
        $code = $data['code'] ?? '';
        $clientId = $data['client_id'] ?? '';
        $clientSecret = $data['client_secret'] ?? '';

        if ($grantType !== 'authorization_code') {
            return $this->jsonResponse($response, ['error' => 'unsupported_grant_type'], 400);
        }

        $client = $this->oAuthService->getClientById($clientId);
        if (!$client || $client->client_secret !== $clientSecret) {
            return $this->jsonResponse($response, ['error' => 'invalid_client'], 401);
        }

        $codeData = $this->oAuthService->validateAndRevokeAuthCode($code, $client);
        if (!$codeData) {
            return $this->jsonResponse($response, ['error' => 'invalid_grant'], 400);
        }

        $accessToken = $this->oAuthService->generateAccessToken($client, (int)$codeData->user_id, (string)$codeData->scope);

        return $this->jsonResponse($response, [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ]);
    }

    /**
     * Returns the authenticated user's profile data from a valid Bearer token.
     *
     * @param Request  $request  The incoming HTTP request, expects an Authorization: Bearer header.
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments (unused).
     * @return Response          A JSON response with the user's sub, username, and email, or an error.
     */
    public function userinfo(Request $request, Response $response, array $args): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (empty($authHeader) || !\preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $this->jsonResponse($response, ['error' => 'invalid_token'], 401);
        }

        $token = $matches[1];
        $tokenData = $this->oAuthService->validateAccessToken($token);

        if (!$tokenData) {
            return $this->jsonResponse($response, ['error' => 'invalid_token'], 401);
        }

        return $this->jsonResponse($response, [
            'sub' => $tokenData['user_id'],
            'username' => $tokenData['username'],
            'email' => $tokenData['email']
        ]);
    }

    /**
     * Encodes data as JSON and writes it to the response body.
     *
     * @param Response $response The HTTP response object.
     * @param array    $data     The payload to serialize as JSON.
     * @param int      $status   The HTTP status code (default: 200).
     * @return Response          The response with JSON body and Content-Type header set.
     */
    private function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $payload = \json_encode($data, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}