<?php

namespace Afterchange\Template\Controllers;

use Afterchange\Template\Utils\Translator;
use Afterchange\Template\Exceptions\AuthException;
use Afterchange\Template\Utils\ErrorCodes;
use Afterchange\Template\Services\AuthService;
use Slim\Views\PhpRenderer;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class RegisterController extends Controller
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
        return $this->render($response, 'auth/register.phtml');
    }

    public function register(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();

        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            $this->flash('error', $this->translator->trans(ErrorCodes::MISSING_FIELDS));
            return $response->withHeader('Location', '/register')->withStatus(302);
        }

        try {
            $this->authService->register($data);
            $this->flash('success', "Inscription réussie ! Veuillez vous connecter.");
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
