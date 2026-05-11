<?php

declare(strict_types=1);

namespace Afterchange\Template\Controllers;

use Afterchange\Template\Services\AuthService;
use Afterchange\Template\Exceptions\AuthException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;
use Afterchange\Template\Utils\Translator;

class ResetPasswordController extends Controller
{
    private AuthService $authService;
    private Translator $t;

    public function __construct(PhpRenderer $view, AuthService $authService, Translator $t)
    {
        parent::__construct($view);
        $this->authService = $authService;
        $this->t = $t;
    }

    public function show(Request $request, Response $response, array $args)
    {
        $queryParams = $request->getQueryParams();
        $token = $queryParams['token'] ?? null;

        if (!$token || !$this->authService->validateResetToken($token)) {
            $this->flash('error', $this->t->trans('INVALID_RESET_TOKEN') ?? 'Invalid or expired reset token.');
            return $response->withHeader('Location', '/forgot-password')->withStatus(302);
        }

        return $this->render($response, 'auth/reset-password.phtml', ['token' => $token]);
    }

    public function reset(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();
        $token = $data['token'] ?? '';
        $newPassword = $data['password'] ?? '';

        $user = $this->authService->validateResetToken($token);

        if (!$user) {
            $this->flash('error', $this->t->trans('INVALID_RESET_TOKEN') ?? 'Invalid or expired reset token.');
            return $response->withHeader('Location', '/forgot-password')->withStatus(302);
        }

        try {
            if ($this->authService->resetPassword($user, $newPassword)) {
                $this->flash('success', $this->t->trans('PASSWORD_RESET_SUCCESS') ?? 'Password successfully reset. You can now login.');
                return $response->withHeader('Location', '/login')->withStatus(302);
            }
        } catch (AuthException $e) {
            $this->flash('error', $this->t->trans($e->getErrorCode()));
        }

        return $response->withHeader('Location', '/reset-password?token=' . urlencode($token))->withStatus(302);
    }
}
