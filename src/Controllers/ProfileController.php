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
 * Handles display and update of the authenticated user's profile.
 */
final class ProfileController extends Controller
{
    protected AuthService $authService;
    protected Translator $translator;

    /**
     * Initializes the controller with its required dependencies.
     *
     * @param PhpRenderer $view        The template rendering engine.
     * @param AuthService $authService Handles user retrieval and profile updates.
     * @param Translator  $translator  Translation utility for user-facing error messages.
     */
    public function __construct(PhpRenderer $view, AuthService $authService, Translator $translator)
    {
        parent::__construct($view);
        $this->authService = $authService;
        $this->translator = $translator;
    }

    /**
     * Renders the profile page for the currently authenticated user.
     *
     * @param Request  $request  The incoming HTTP request (unused).
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments (unused).
     * @return Response          The rendered profile view.
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $user = $this->authService->currentUser();

        return $this->render($response, 'auth/profile.phtml', [
            'user' => $user
        ]);
    }

    /**
     * Processes the profile update form and persists changes for the authenticated user.
     *
     * @param Request  $request  The incoming HTTP request carrying the updated profile data.
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments (unused).
     * @return Response          A redirect to the profile page with a success or error flash message.
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        try {
            $this->authService->updateUser((int)$_SESSION['user_id'], $data);
            $this->flash('success', 'Profil mis à jour avec succès.');
            return $response->withHeader('Location', '/profile')->withStatus(302);
        } catch (AuthException $e) {
            $this->flash('error', $this->translator->trans($e->getErrorCode()));
            return $response->withHeader('Location', '/profile')->withStatus(302);
        } catch (\Exception $e) {
            $this->flash('error', $this->translator->trans(ErrorCodes::REGISTRATION_FAILED));
            return $response->withHeader('Location', '/profile')->withStatus(302);
        }
    }
}