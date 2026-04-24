<?php

declare(strict_types=1);

namespace Afterchange\Template\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class OAuthController extends Controller
{
    public function authorize(Request $request, Response $response, array $args) {}

    public function token(Request $request, Response $response, array $args) {}
}
