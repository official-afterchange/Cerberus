<?php

declare(strict_types=1);

namespace Afterchange\Template\Controllers;

use Afterchange\Template\Services\AuthService;
use Afterchange\Template\Utils\Mailer;
use Afterchange\Template\Utils\Translator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Views\PhpRenderer;
use Symfony\Component\Mime\Email;

/**
 * Handles password reset requests and reset link delivery by email.
 */
final class ForgotPasswordController extends Controller
{
    private AuthService $authService;
    private LoggerInterface $logger;
    private Translator $t;
    private Mailer $m;

    /**
     * Initializes the controller with its required dependencies.
     *
     * @param PhpRenderer     $view        The template rendering engine.
     * @param AuthService     $authService Handles token generation and account lookup.
     * @param LoggerInterface $logger      PSR-3 logger for audit and debug output.
     * @param Translator      $t           Translation utility for user-facing messages.
     * @param Mailer          $m           Mail dispatcher for sending the reset email.
     */
    public function __construct(PhpRenderer $view, AuthService $authService, LoggerInterface $logger, Translator $t, Mailer $m)
    {
        parent::__construct($view);
        $this->authService = $authService;
        $this->logger = $logger;
        $this->t = $t;
        $this->m = $m;
    }

    /**
     * Renders the forgot password form.
     *
     * @param Request  $request  The incoming HTTP request.
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments (unused).
     * @return Response          The rendered form view.
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        return $this->render($response, 'auth/forgot-password.phtml');
    }

    /**
     * Validates the submitted email and sends a password reset link if an account exists.
     *
     * @param Request  $request  The incoming HTTP request carrying the form data.
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments (unused).
     * @return Response          A redirect to the login page.
     */
    public function sendLink(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';

        if (!empty($email) && \filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            $token = $this->authService->generateResetToken($email);

            if ($token) {
                $ssl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $resetLink = "$ssl://$host/reset-password?token=$token";

                $emailMessage = new Email()
                    ->from('noreply@localhost')
                    ->to($email)
                    ->subject('Reset Password')
                    ->text("Click the link to reset your password: $resetLink");

                $this->m->send($emailMessage);

                $this->logger->info("Password reset requested for $email. Reset link: $resetLink");
            }
        }

        $this->flash('success', $this->t->trans('RESET_LINK_SENT') ?? 'If an account exists, a reset link has been sent to your email.');
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}