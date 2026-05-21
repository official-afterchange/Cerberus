<?php

declare(strict_types=1);

namespace Afterchange\Template\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Renders the application home page.
 */
final class HomeController extends Controller
{
    /**
     * Displays the main landing view.
     *
     * @param Request  $request  The incoming HTTP request.
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments (unused).
     * @return Response          The rendered home page.
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        return $this->view->render($response, 'home.phtml');
    }
}