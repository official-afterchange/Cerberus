<?php

declare(strict_types=1);

namespace Afterchange\Template\Controllers;

use Afterchange\Template\Services\AuthService;
use Afterchange\Template\Utils\Mailer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Views\PhpRenderer;
use Afterchange\Template\Utils\Translator;
use Symfony\Component\Mime\Email;

class ForgotPasswordController extends Controller
{
    private AuthService $authService;
    private LoggerInterface $logger;
    private Translator $t;
    private Mailer $m;

    public function __construct(PhpRenderer $view, AuthService $authService, LoggerInterface $logger, Translator $t, Mailer $m)
    {
        parent::__construct($view);
        $this->authService = $authService;
        $this->logger = $logger;
        $this->t = $t;
        $this->m = $m;
    }

    public function show(Request $request, Response $response, array $args)
    {
        return $this->render($response, 'auth/forgot-password.phtml');
    }

    public function sendLink(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';

        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $token = $this->authService->generateResetToken($email);

            if ($token) {
                $ssl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $resetLink = "$ssl://$host/reset-password?token=$token";

                $email = new Email()
                    ->from('noreply@localhost')
                    ->to($email)
                    ->subject('Reset Password')
                    ->text("Click the link to reset your password: $resetLink");

                $this->m->send($email);

                $this->logger->info("Password reset requested for $email. Reset link: $resetLink");
            }
        }

        $this->flash('success', $this->t->trans('RESET_LINK_SENT') ?? 'If an account exists, a reset link has been sent to your email.');
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}
