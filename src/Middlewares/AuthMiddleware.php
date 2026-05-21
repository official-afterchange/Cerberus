<?php

declare(strict_types=1);

namespace Afterchange\Template\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

/**
 * Protects routes by redirecting unauthenticated requests to the login page.
 */
final class AuthMiddleware implements MiddlewareInterface
{
    /**
     * Checks for an active session and either passes the request through or redirects to login,
     * preserving the originally requested URL as a redirect parameter.
     *
     * @param Request $request The incoming HTTP request.
     * @param Handler $handler The next middleware or route handler in the pipeline.
     * @return Response        The handler's response, or a 302 redirect to the login page.
     */
    public function process(Request $request, Handler $handler): Response
    {
        if (empty($_SESSION['user_id'])) {

            $uri = $request->getUri();
            $path = $uri->getPath();
            $query = $uri->getQuery();

            $redirect = $path . ($query ? '?' . $query : '');

            $loginUrl = '/login?redirect=' . \urlencode($redirect);

            $response = new SlimResponse();
            return $response->withHeader('Location', $loginUrl)->withStatus(302);
        }

        return $handler->handle($request);
    }
}