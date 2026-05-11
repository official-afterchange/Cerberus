<?php

declare(strict_types=1);

namespace Afterchange\Template\Controllers;

use Afterchange\Template\Exceptions\AuthException;
use Afterchange\Template\Services\AuthService;
use Afterchange\Template\Utils\ErrorCodes;
use Afterchange\Template\Utils\Translator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

class ProfileController extends Controller
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
        $user = $this->authService->currentUser();

        return $this->render($response, 'auth/profile.phtml', [
            'user' => $user
        ]);
    }

    public function update(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();

        try {
            $this->authService->updateUser((int)$_SESSION['user_id'], $data);
            $this->flash('success', "Profil mis à jour avec succès.");
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
