<?php

declare(strict_types=1);

namespace Afterchange\Template\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Exception\HttpForbiddenException;

class CsrfMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $method = $request->getMethod();

        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $data = $request->getParsedBody();
            $token = $data['csrf_token'] ?? $request->getHeaderLine('X-CSRF-TOKEN');

            if (empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
                throw new HttpForbiddenException($request, 'Invalid CSRF Token. Please refresh the page and try again.');
            }
        }

        return $handler->handle($request);
    }
}
