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

        if ($code === 500)
            $messageKey = 'INTERNAL_ERROR';


        $data = [
            'code' => $code,
            'title' => 'TITLE_' . $messageKey,
            'message' => $messageKey,
            'debug' => $displayErrorDetails,
            'csrf_token' => $_SESSION['csrf_token'] ?? null,
        ];

        $response = new \Slim\Psr7\Response();
        return (string) $this->view->render($response, 'error.phtml', $data)->getBody();
    }
}
