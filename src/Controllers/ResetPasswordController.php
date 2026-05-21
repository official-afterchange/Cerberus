<?php

declare(strict_types=1);

namespace Afterchange\Template\Controllers;

use Afterchange\Template\Exceptions\AuthException;
use Afterchange\Template\Services\AuthService;
use Afterchange\Template\Utils\Translator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

/**
 * Handles password reset token validation and new password submission.
 */
final class ResetPasswordController extends Controller
{
    private AuthService $authService;
    private Translator $t;

    /**
     * Initializes the controller with its required dependencies.
     *
     * @param PhpRenderer $view        The template rendering engine.
     * @param AuthService $authService Handles token validation and password reset logic.
     * @param Translator  $t           Translation utility for user-facing messages.
     */
    public function __construct(PhpRenderer $view, AuthService $authService, Translator $t)
    {
        parent::__construct($view);
        $this->authService = $authService;
        $this->t = $t;
    }

    /**
     * Validates the reset token from the query string and renders the reset password form.
     *
     * @param Request  $request  The incoming HTTP request carrying the 'token' query parameter.
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments (unused).
     * @return Response          The rendered reset form, or a redirect to forgot-password if the token is invalid.
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $token = $queryParams['token'] ?? null;

        if (!$token || !$this->authService->validateResetToken($token)) {
            $this->flash('error', $this->t->trans('INVALID_RESET_TOKEN') ?? 'Invalid or expired reset token.');
            return $response->withHeader('Location', '/forgot-password')->withStatus(302);
        }

        return $this->render($response, 'auth/reset-password.phtml', ['token' => $token]);
    }

    /**
     * Validates the token and applies the new password if the token is still valid.
     *
     * @param Request  $request  The incoming HTTP request carrying the token and new password.
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments (unused).
     * @return Response          A redirect to login on success, or back to the reset form on failure.
     */
    public function reset(Request $request, Response $response, array $args): Response
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

        return $response->withHeader('Location', '/reset-password?token=' . \urlencode($token))->withStatus(302);
    }
}