<?php

declare(strict_types=1);

namespace Afterchange\Template\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class LangController extends Controller
{
    public function switch(Request $request, Response $response, array $args)
    {
        $lang = $args['lang'] ?? 'en';

        if (in_array($lang, ['en', 'fr', 'es', 'de', 'ar'])) {
            $_SESSION['lang'] = $lang;
        }

        $referer = $request->getHeaderLine('Referer') ?: '/';
        return $response->withHeader('Location', $referer)->withStatus(302);
    }
}
