<?php

declare(strict_types=1);

namespace Afterchange\Template\Exceptions;

use Slim\Interfaces\ErrorRendererInterface;
use Throwable;

/**
 * Renders application errors as HTML using the PhpRenderer, with normalized HTTP status codes and translation keys.
 */
final class CustomErrorRenderer implements ErrorRendererInterface
{
    private \Slim\Views\PhpRenderer $view;

    /**
     * Initializes the renderer with the template engine.
     *
     * @param \Slim\Views\PhpRenderer $view The template rendering engine.
     */
    public function __construct(\Slim\Views\PhpRenderer $view)
    {
        $this->view = $view;
    }

    /**
     * Renders the error view for the given exception, normalizing the HTTP status code
     * and mapping known exception types to translation keys.
     *
     * @param Throwable $exception          The caught exception to render.
     * @param bool      $displayErrorDetails Whether to expose debug information in the view.
     * @return string                        The rendered HTML error page as a string.
     */
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        $code = $exception->getCode();
        if ($code < 100 || $code > 599) {
            $code = 500;
        }

        $messageKey = $exception->getMessage();

        if ($exception instanceof \Slim\Exception\HttpNotFoundException) {
            $code = 404;
            $messageKey = 'PAGE_NOT_FOUND';
        } elseif ($exception instanceof \Slim\Exception\HttpForbiddenException || $code === 403) {
            $code = 403;
            $messageKey = 'ACCESS_FORBIDDEN';
        }

        if ($code === 500) {
            $messageKey = 'INTERNAL_ERROR';
        }

        $data = [
            'code' => $code,
            'title' => 'TITLE_' . $messageKey,
            'message' => $messageKey,
            'debug' => $displayErrorDetails,
            'exception' => $exception,
            'csrf_token' => $_SESSION['csrf_token'] ?? null,
        ];

        $response = new \Slim\Psr7\Response();
        return (string) $this->view->render($response, 'error.phtml', $data)->getBody();
    }
}