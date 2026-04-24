<?php

declare(strict_types=1);

namespace Afterchange\Template\Exceptions;

use Slim\Interfaces\ErrorRendererInterface;
use Throwable;

class CustomErrorRenderer implements ErrorRendererInterface
{
    private \Slim\Views\PhpRenderer $view;

    public function __construct(\Slim\Views\PhpRenderer $view)
    {
        $this->view = $view;
    }

    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        $code = $exception->getCode();
        if ($code == 0) {
            $code = 500;
        }

        $template = 'errors/500.phtml';
        
        if ($exception instanceof \Slim\Exception\HttpNotFoundException) {
            $code = 404;
            $template = 'errors/404.phtml';
        } elseif ($exception instanceof \Slim\Exception\HttpForbiddenException || $code === 403) {
            $code = 403;
            $template = 'errors/403.phtml';
        }

        $title = $code . ' - Error';
        if ($code === 404) $title = '404 - Not Found';
        if ($code === 403) $title = '403 - Forbidden';

        $errorMessage = $displayErrorDetails ? $exception->getMessage() : 'An unexpected error occurred.';

        $data = [
            'title' => $title,
            'code' => $code,
            'message' => $errorMessage,
            'csrf_token' => $_SESSION['csrf_token'] ?? null,
        ];

        try {
            $response = new \Slim\Psr7\Response();
            $response = $this->view->render($response, $template, $data);
            return (string) $response->getBody();
        } catch (\Throwable $e) {
            return "<h1>$code Error</h1><p>" . htmlspecialchars($errorMessage) . "</p>";
        }
    }
}
