<?php

declare(strict_types=1);

namespace Afterchange\Template\Exceptions;

use Afterchange\Template\Utils\OAuthRedirectBuilder;
use Psr\Http\Message\ResponseInterface;
use Slim\Handlers\ErrorHandler;

/**
 * Extends Slim's error handler to intercept OAuth exceptions and redirect with a proper error response.
 */
final class CustomErrorHandler extends ErrorHandler
{
    /**
     * Handles OAuth exceptions by redirecting to the client URI with an error payload,
     * falling back to the default Slim error response for all other exceptions.
     *
     * @return ResponseInterface A 302 redirect for OAuth errors, or the default error response.
     */
    protected function respond(): ResponseInterface
    {
        $exception = $this->exception;

        if ($exception instanceof OAuthException && $exception->getRedirectUri() !== null) {
            $redirectTo = OAuthRedirectBuilder::build(
                redirectUri: $exception->getRedirectUri(),
                error: $exception->getOAuthError(),
                state: $exception->getState(),
            );

            return $this->responseFactory
                ->createResponse(302)
                ->withHeader('Location', $redirectTo);
        }

        return parent::respond();
    }
}