<?php

namespace Afterchange\Template\Controllers;

use Afterchange\Template\Exceptions\AuthException;
use Afterchange\Template\Services\AuthService;
use Afterchange\Template\Utils\ErrorCodes;
use Afterchange\Template\Utils\Translator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

class LoginController extends Controller
{
    protected AuthService $authService;
    protected Translator $translator;

    public function __construct(PhpRenderer $view, AuthService $authService, Translator $translator)
    {
        parent::__construct($view);
        $this->authService = $authService;
        $this->translator = $translator;
    }

    public function show(Request $request, Response $response, array $args)
    {
        return $this->render($response, 'auth/login.phtml', [
            'registered' => $request->getQueryParams()['registered'] ?? null
        ]);
    }

    public function login(Request $request, Response $response, array $args)
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
            
            error_log("Login OK for user: " . $user->username);

            if (!empty($data['remember_me'])) {
                $token = $this->authService->createRememberToken($user);
                $this->setRememberCookie($token);
            }

            return $response->withHeader('Location', '/profile')->withStatus(302);
        } catch (AuthException $e) {
            $this->flash('error', $this->translator->trans($e->getErrorCode()));
            return $response->withHeader('Location', '/login')->withStatus(302);
        } catch (\Exception $e) {
            $this->flash('error', $this->translator->trans(ErrorCodes::LOGIN_FAILED));
            return $response->withHeader('Location', '/login')->withStatus(302);
        }
    }

    public function logout(Request $request, Response $response, array $args)
    {
        if (!empty($_SESSION['user_id'])) {
            $user = clone $this->authService->currentUser();
            if ($user) {
                $this->authService->clearRememberToken($user);
            }
        }

        session_destroy();
        
        setcookie('remember_me', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    private function setRememberCookie(string $token): void
    {
        setcookie('remember_me', $token, [
            'expires' => time() + (30 * 24 * 60 * 60), // 30 days
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}
