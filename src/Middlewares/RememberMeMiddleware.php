<?php

declare(strict_types=1);

namespace Afterchange\Template\Middlewares;

use Afterchange\Template\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class RememberMeMiddleware implements MiddlewareInterface
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function process(Request $request, Handler $handler): Response
    {
        if (empty($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
            $token = $_COOKIE['remember_me'];

            $user = $this->authService->loginFromToken($token);

            if ($user) {
                $_SESSION['user_id'] = $user->id;
                $newToken = $this->authService->createRememberToken($user);
                $this->setRememberCookie($newToken);
            } else {
                $this->clearRememberCookie();
            }
        }

        return $handler->handle($request);
    }

    private function setRememberCookie(string $token): void
    {
        setcookie('remember_me', $token, [
            'expires' => time() + (30 * 24 * 60 * 60),
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    private function clearRememberCookie(): void
    {
        setcookie('remember_me', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}
