<?php

declare(strict_types=1);

namespace Afterchange\Template\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Exception\HttpForbiddenException;

/**
 * Guards state-mutating requests against CSRF attacks by validating a session-bound token.
 */
final class CsrfMiddleware implements MiddlewareInterface
{
    /**
     * Ensures a CSRF token exists in the session, then validates it on state-mutating requests.
     * The token is accepted from either the request body or the X-CSRF-TOKEN header.
     *
     * @param Request $request The incoming HTTP request.
     * @param Handler $handler The next middleware or route handler in the pipeline.
     * @return Response        The handler's response if the token is valid.
     * @throws HttpForbiddenException If the token is missing or does not match the session value.
     */
    public function process(Request $request, Handler $handler): Response
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = \bin2hex(\random_bytes(32));
        }

        $method = $request->getMethod();

        if (\in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            $data = $request->getParsedBody();
            $token = $data['csrf_token'] ?? $request->getHeaderLine('X-CSRF-TOKEN');

            if (empty($token) || !\hash_equals($_SESSION['csrf_token'], $token)) {
                throw new HttpForbiddenException($request, 'Invalid CSRF Token. Please refresh the page and try again.');
            }
        }

        return $handler->handle($request);
    }
}