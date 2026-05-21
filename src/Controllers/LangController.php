<?php

declare(strict_types=1);

namespace Afterchange\Template\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Handles application language switching.
 */
final class LangController extends Controller
{
    /**
     * Persists the selected language in the session and redirects back to the previous page.
     *
     * @param Request  $request  The incoming HTTP request (used to read the Referer header).
     * @param Response $response The HTTP response object.
     * @param array    $args     Route arguments, expects 'lang' (e.g., 'en', 'fr', 'es', 'de', 'ar').
     * @return Response          A redirect to the originating page.
     */
    public function switch(Request $request, Response $response, array $args): Response
    {
        $lang = $args['lang'] ?? 'en';

        if (\in_array($lang, ['en', 'fr', 'es', 'de', 'ar'], true)) {
            $_SESSION['lang'] = $lang;
        }

        $referer = $request->getHeaderLine('Referer') ?: '/';
        return $response->withHeader('Location', $referer)->withStatus(302);
    }
}