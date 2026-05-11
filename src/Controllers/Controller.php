<?php

declare(strict_types=1);

namespace Afterchange\Template\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

abstract class Controller
{
    protected PhpRenderer $view;

    public function __construct(PhpRenderer $view)
    {
        $this->view = $view;
    }

    protected function flash(string $key, string $message): void
    {
        $_SESSION["flash_$key"] = $message;
    }

    protected function render(Response $response, string $template, array $data = []): Response
    {
        $data['error'] = $data['error'] ?? ($_SESSION['flash_error'] ?? null);
        $data['success'] = $data['success'] ?? ($_SESSION['flash_success'] ?? null);

        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        return $this->view->render($response, $template, $data);
    }
}
