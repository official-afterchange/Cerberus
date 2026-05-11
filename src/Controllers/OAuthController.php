<?php

declare(strict_types=1);

namespace Afterchange\Template\Controllers;

use Afterchange\Template\Services\AuthService;
use Afterchange\Template\Services\OAuthService;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

class OAuthController extends Controller
{
    protected OAuthService $oAuthService;
    protected AuthService $authService;

    public function __construct(PhpRenderer $view, OAuthService $oAuthService, AuthService $authService)
    {
        parent::__construct($view);
        $this->oAuthService = $oAuthService;
        $this->authService = $authService;
    }

    public function showAuthorize(Request $request, Response $response, array $args)
    {
        $queryParams = $request->getQueryParams();
        $clientId = $queryParams['client_id'] ?? '';

        $client = $this->oAuthService->getClientById($clientId);

        if ($client === null)
            throw new Exception('CLIENT_NOT_FOUND', 400);

        $isValidUri = $this->oAuthService->isValidRedirectUri($client, $queryParams['redirect_uri'] ?? null);

        if (!$isValidUri)
            throw new Exception('INVALID_REDIRECT_URI', 400);

        $responseType = $queryParams['response_type'] ?? null;
        if ($responseType !== 'code')
            throw new Exception('INVALID_RESPONSE_TYPE', 400);

        $scopeString = $queryParams['scope'] ?? null;

        $scopes = [];
        if ($scopeString === null || $scopeString === '') {
            $scopes = $this->oAuthService->getDefaultScopes();
        } else {
            $scopeNames = explode(' ', $scopeString);
            $scopes = $this->oAuthService->getScopeByNames($scopeNames);
        }

        return $this->view->render($response, 'oauth/authorize.phtml', [
            'clientId' => $client->id,
            'responseType' => $responseType,
            'scopes' => $scopes,
            'redirectUri' => $queryParams['redirect_uri']
        ]);
    }

    public function authorize(Request $request, Response $response, array $args)
    {
        //checker si il accepte ou si il refuse puis rediriger

        //Si refuser
        //$req_redirect_uri . "?error=access_denied&state=" . $req_state
    
        //si accepter

        //crer un code avec une expiration de 5 min
        //inserer le code dnas la table oauth_codes

        //puis rediriger vers l'url de redirection
        //$req_redirect_uri . "?code=" . $code . "&state=" . $req_state
    }

    public function token(Request $request, Response $response, array $args)
    {
        
    }

    public function userinfo(Request $request, Response $response, array $args)
    {

    }
}
