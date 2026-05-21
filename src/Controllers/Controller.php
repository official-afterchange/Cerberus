<?php

declare(strict_types=1);

namespace Afterchange\Template\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

/**
 * Base controller providing shared template rendering and flash messaging capabilities.
 */
abstract class Controller
{
    /**
     * The template engine instance.
     */
    protected PhpRenderer $view;

    /**
     * Initializes the controller with the required view renderer.
     *
     * @param PhpRenderer $view The template rendering engine.
     */
    public function __construct(PhpRenderer $view)
    {
        $this->view = $view;
    }

    /**
     * Stores a temporary flash message in the session.
     *
     * @param string $key     The identifier key (e.g., 'success', 'error').
     * @param string $message The message content to display.
     */
    protected function flash(string $key, string $message): void
    {
        $_SESSION["flash_$key"] = $message;
    }

    /**
     * Renders a view template, automatically injecting and clearing pending flash messages.
     *
     * @param Response $response The HTTP response object.
     * @param string   $template The path to the template file.
     * @param array    $data     Optional contextual data passed to the view.
     * @return Response          The rendered HTTP response.
     */
    protected function render(Response $response, string $template, array $data = []): Response
    {
        // Inject flash messages into the view data if not already explicitly provided
        $data['error'] = $data['error'] ?? ($_SESSION['flash_error'] ?? null);
        $data['success'] = $data['success'] ?? ($_SESSION['flash_success'] ?? null);

        // Clear flash messages from the session after consumption
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        return $this->view->render($response, $template, $data);
    }
}