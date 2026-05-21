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
 * Handles new user registration and the registration form display.
 */
final class RegisterController extends Controller
{
    protected AuthService $authService;
    protected Translator $translator;

    /**
     * Initializes the controller with its required dependencies.
     *
     * @param PhpRenderer $view        The template rendering engine.
     * @param AuthService $authService Handles account creation logic.
     * @param Translator  $translator  Translation utility for user-facing error messages.
     */
    public function __construct(PhpRenderer $view, AuthService $authService, Translator $translator)
    {
        parent::__construct($view);
        $this->authService = $authService;
        $this->translator = $translator;
    }

    /**
     * Renders the registration form.
     *
     * @param Request  $request  The incoming HTTP request (unused).
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments (unused).
     * @return Response          The rendered registration view.
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        return $this->render($response, 'auth/register.phtml');
    }

    /**
     * Validates the submitted data and creates a new user account.
     *
     * @param Request  $request  The incoming HTTP request carrying username, email, and password.
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments (unused).
     * @return Response          A redirect to the login page on success, or back to the register form on failure.
     */
    public function register(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            $this->flash('error', $this->translator->trans(ErrorCodes::MISSING_FIELDS));
            return $response->withHeader('Location', '/register')->withStatus(302);
        }

        try {
            $this->authService->register($data);
            $this->flash('success', 'Inscription réussie ! Veuillez vous connecter.');
            return $response->withHeader('Location', '/login')->withStatus(302);
        } catch (AuthException $e) {
            $this->flash('error', $this->translator->trans($e->getErrorCode()));
            return $response->withHeader('Location', '/register')->withStatus(302);
        } catch (\Exception $e) {
            $this->flash('error', $this->translator->trans(ErrorCodes::REGISTRATION_FAILED));
            return $response->withHeader('Location', '/register')->withStatus(302);
        }
    }
}