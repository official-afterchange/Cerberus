<?php

declare(strict_types=1);

namespace Afterchange\Template\Middlewares;

use Afterchange\Template\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * Restores a user session from a remember-me cookie if no active session exists.
 */
final class RememberMeMiddleware implements MiddlewareInterface
{
    private AuthService $authService;

    /**
     * Initializes the middleware with the authentication service.
     *
     * @param AuthService $authService Handles token validation and session restoration.
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Attempts to authenticate the user from a remember-me cookie when no session is active.
     * On success, reopens the session and rotates the token. On failure, expires the cookie.
     *
     * @param Request $request The incoming HTTP request.
     * @param Handler $handler The next middleware or route handler in the pipeline.
     * @return Response        The handler's response.
     */
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

    /**
     * Sets a secure remember-me cookie valid for 30 days.
     *
     * @param string $token The opaque token to persist in the cookie.
     * @return void
     */
    private function setRememberCookie(string $token): void
    {
        \setcookie('remember_me', $token, [
            'expires' => \time() + (30 * 24 * 60 * 60),
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    /**
     * Expires the remember-me cookie, effectively removing it from the client.
     *
     * @return void
     */
    private function clearRememberCookie(): void
    {
        \setcookie('remember_me', '', [
            'expires' => \time() - 3600,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}