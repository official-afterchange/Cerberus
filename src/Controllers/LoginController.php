<?php

declare(strict_types=1);

namespace Afterchange\Template\Controllers;

use Afterchange\Template\Exceptions\AuthException;
use Afterchange\Template\Services\AuthService;
use Afterchange\Template\Utils\ErrorCodes;
use Afterchange\Template\Utils\Translator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

/**
 * Handles user authentication: login, logout, and remember-me cookie management.
 */
final class LoginController extends Controller
{
    protected AuthService $authService;
    protected Translator $translator;

    /**
     * Initializes the controller with its required dependencies.
     *
     * @param PhpRenderer $view        The template rendering engine.
     * @param AuthService $authService Handles authentication logic and token management.
     * @param Translator  $translator  Translation utility for user-facing error messages.
     */
    public function __construct(PhpRenderer $view, AuthService $authService, Translator $translator)
    {
        parent::__construct($view);
        $this->authService = $authService;
        $this->translator = $translator;
    }

    /**
     * Renders the login form.
     *
     * @param Request  $request  The incoming HTTP request (reads the 'registered' query param).
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments (unused).
     * @return Response          The rendered login view.
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        return $this->render($response, 'auth/login.phtml', [
            'registered' => $request->getQueryParams()['registered'] ?? null
        ]);
    }

    /**
     * Processes login credentials, opens the user session, and handles remember-me persistence.
     *
     * @param Request  $request  The incoming HTTP request carrying the form data.
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments (unused).
     * @return Response          A redirect to the target page on success, or back to login on failure.
     */
    public function login(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        if (empty($data['username']) || empty($data['password'])) {
            $this->flash('error', $this->translator->trans(ErrorCodes::MISSING_FIELDS));
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        try {
            $user = $this->authService->login($data);

            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;

            if (!empty($data['remember_me'])) {
                $token = $this->authService->createRememberToken($user);
                $this->setRememberCookie($token);
            }

            $redirectUri = $data['redirect'] === '' ? '/profile' : $data['redirect'];

            return $response->withHeader('Location', $redirectUri)->withStatus(302);
        } catch (AuthException $e) {
            $this->flash('error', $this->translator->trans($e->getErrorCode()));
            return $response->withHeader('Location', '/login')->withStatus(302);
        } catch (\Exception $e) {
            $this->flash('error', $this->translator->trans(ErrorCodes::LOGIN_FAILED));
            return $response->withHeader('Location', '/login')->withStatus(302);
        }
    }

    /**
     * Destroys the user session, clears the remember-me token, and expires the cookie.
     *
     * @param Request  $request  The incoming HTTP request (unused).
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments (unused).
     * @return Response          A redirect to the login page.
     */
    public function logout(Request $request, Response $response, array $args): Response
    {
        if (!empty($_SESSION['user_id'])) {
            $user = clone $this->authService->currentUser();
            if ($user) {
                $this->authService->clearRememberToken($user);
            }
        }

        $_SESSION = [];
        \session_destroy();

        @\setcookie('remember_me', '', [
            'expires' => \time() - 3600,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        return $response->withHeader('Location', '/login')->withStatus(302);
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
            'expires' => \time() + (30 * 24 * 60 * 60), // 30 days
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}