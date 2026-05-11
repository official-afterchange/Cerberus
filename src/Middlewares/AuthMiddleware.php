<?php

declare(strict_types=1);

namespace Afterchange\Template\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        if (empty($_SESSION['user_id'])) {
            
            $uri = $request->getUri();
            $path = $uri->getPath();
            $query = $uri->getQuery();

            $redirect = $path . ($query ? '?' . $query : '');

            $loginUrl = '/login?redirect=' . urlencode($redirect);
            
            $response = new SlimResponse();
            return $response->withHeader('Location', $loginUrl)->withStatus(302);
        }

        return $handler->handle($request);
    }
}
